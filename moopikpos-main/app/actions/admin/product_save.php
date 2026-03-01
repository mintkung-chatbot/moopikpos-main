<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

$id = $_POST['id'];
$name = $_POST['name'];
$desc = $_POST['description'];
$price = $_POST['price'];
$stock = $_POST['stock_qty'];
$cat_id = $_POST['category_id'];
$status = $_POST['status'];
$image_url = $_POST['old_image']; // ค่าเริ่มต้นใช้รูปเดิม

// จัดการอัปโหลดรูปภาพ
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $dir = "uploads/";
    if (!file_exists($dir)) mkdir($dir, 0777, true); // สร้างโฟลเดอร์ถ้ายังไม่มี

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $new_name = time() . "_" . rand(1000,9999) . "." . $ext; // ตั้งชื่อไฟล์ใหม่กันซ้ำ
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $new_name)) {
        $image_url = $new_name;
    }
}

if (empty($id)) {
    // --- เพิ่มใหม่ ---
    $sql = "INSERT INTO products (name, description, price, stock_qty, category_id, image_url, status) VALUES (?,?,?,?,?,?,?)";
    $pdo->prepare($sql)->execute([$name, $desc, $price, $stock, $cat_id, $image_url, $status]);
} else {
    // --- แก้ไข ---
    $sql = "UPDATE products SET name=?, description=?, price=?, stock_qty=?, category_id=?, image_url=?, status=? WHERE id=?";
    $pdo->prepare($sql)->execute([$name, $desc, $price, $stock, $cat_id, $image_url, $status, $id]);
}

header("Location: admin_products.php");
?>