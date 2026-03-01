<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
staff_require_login();

// รับข้อมูล JSON จาก Input Hidden
$cart_json = $_POST['cart_data'] ?? '';
$order_type = $_POST['order_type'] ?? '';
$table_no = trim((string) ($_POST['table_no'] ?? ''));
$customer_info = trim((string) ($_POST['customer_info'] ?? ''));
$staff_user_id = staff_current_user_id();

if (!in_array($order_type, ['dine_in', 'takeaway'], true)) {
    $order_type = 'dine_in';
}

if ($order_type === 'dine_in' && $table_no === '') {
    header('Location: ' . auth_url('staff_order.php?error=missing_table'));
    exit;
}

if ($order_type === 'takeaway') {
    $table_no = null;
}

// แปลง JSON กลับเป็น Array ของ PHP
$cart_items = json_decode($cart_json, true);

// ถ้าไม่มีสินค้า ให้เด้งกลับ
if (empty($cart_items)) {
    header('Location: ' . auth_url('staff_order.php?error=empty'));
    exit;
}

$total_price = 0;

try {
    $pdo->beginTransaction();

    // 1. สร้าง Order
    $sql_order = "INSERT INTO orders (user_id, order_type, table_no, customer_name, total_price, status, order_time) 
                  VALUES (?, ?, ?, ?, 0, 'pending', NOW())";
    $stmt = $pdo->prepare($sql_order);
    $stmt->execute([$staff_user_id, $order_type, $table_no, $customer_info]);
    $order_id = $pdo->lastInsertId();

    // 2. วนลูปบันทึกรายการสินค้า
    foreach ($cart_items as $item) {
        $product_id = $item['id'];
        $qty = $item['qty'];
        $price = $item['price']; // ราคานี้รวมท็อปปิ้งมาแล้วจาก JS
        
        // รวม Option และ Note เข้าด้วยกันเพื่อบันทึกในช่อง note
        // ตัวอย่าง: "เผ็ดมาก, ไข่ดาว (Note: ไม่ใส่ผงชูรส)"
        $full_note = $item['options'];
        if (!empty($item['note'])) {
            $full_note .= " (" . $item['note'] . ")";
        }

        $line_total = $price * $qty;
        $total_price += $line_total;

        // บันทึกลงตาราง order_items
        $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, note, price) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $pdo->prepare($sql_item);
        $stmt_item->execute([$order_id, $product_id, $qty, $full_note, $price]);

        // 3. ตัดสต็อก (Option เสริม: ถ้าจะตัดไข่ดาวด้วย ต้องเขียน Logic เพิ่มตรงนี้)
        // เบื้องต้นตัดสต็อกเมนูหลักก่อน
        $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?")->execute([$qty, $product_id]);
        
        // ส่วนตัดสต็อกวัตถุดิบ (Logic Recipe เดิมที่คุณมี)
        $stmt_recipe = $pdo->prepare("SELECT * FROM product_recipes WHERE product_id = ?");
        $stmt_recipe->execute([$product_id]);
        while($recipe = $stmt_recipe->fetch()){
             $used = $recipe['quantity_used'] * $qty;
             $pdo->prepare("UPDATE ingredients SET stock_qty = stock_qty - ? WHERE id = ?")
                 ->execute([$used, $recipe['ingredient_id']]);
        }
    }

    // อัปเดตราคารวม
    $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?")->execute([$total_price, $order_id]);
    
    // อัปเดตสถานะโต๊ะทันที (ถ้าเป็น Dine-in)
    if ($order_type === 'dine_in' && $table_no) {
        $pdo->prepare("UPDATE tables SET status = 'occupied' WHERE table_no = ?")->execute([$table_no]);
        $pdo->commit();
        // Redirect ไปหน้าดู order ของโต๊ะนั้น
        header('Location: ' . auth_url('staff_requests.php?table=' . urlencode($table_no) . '&created=1'));
    } else {
        $pdo->commit();
        // Redirect ไปหน้าดู order ทั้งหมด (หรือหน้า Order success)
        // เพื่อให้ flow ต่อเนื่อง พนักงานน่าจะอยากเห็นคิวที่เพิ่งสร้าง
        header('Location: ' . auth_url('staff_requests.php?created=1')); 
    }
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>