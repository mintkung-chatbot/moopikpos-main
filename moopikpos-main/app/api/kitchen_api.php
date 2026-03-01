<?php
require __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// --- ส่วนที่ 1: อัปเดตสถานะ (เมื่อกดปุ่ม) ---
if ($action == 'update_status') {
    $id = $_POST['id'];
    $current_status = $_POST['status'];
    
    // Logic เปลี่ยนสถานะตามลำดับ: pending -> cooking -> ready -> completed
    $next_status = 'completed';
    if ($current_status == 'pending') $next_status = 'cooking';
    else if ($current_status == 'cooking') $next_status = 'ready';
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$next_status, $id]);
    
    echo json_encode(['success' => $result]);
    exit;
}

// --- ส่วนที่ 2: ดึงข้อมูลออเดอร์ (สำหรับแสดงผล) ---
if ($action == 'fetch_orders') {
    // ดึงเฉพาะออเดอร์ที่ยังไม่เสร็จ (ไม่เอา completed)
    $sql = "SELECT * FROM orders WHERE status != 'completed' ORDER BY order_time ASC";
    $orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    $data = [];
    foreach ($orders as $order) {
        // ดึงรายการอาหารในออเดอร์นั้นๆ
        $sql_items = "SELECT oi.*, p.name, p.image_url 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = ?";
        $stmt_items = $pdo->prepare($sql_items);
        $stmt_items->execute([$order['id']]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        $order['items'] = $items;
        $order['time_ago'] = time_elapsed_string($order['order_time']);
        $data[] = $order;
    }
    
    echo json_encode($data);
    exit;
}

// ฟังก์ชันคำนวณเวลาถอยหลัง (เช่น "5 นาทีที่แล้ว")
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = array(
        'y' => 'ปี', 'm' => 'เดือน', 'w' => 'สัปดาห์',
        'd' => 'วัน', 'h' => 'ชม.', 'i' => 'นาที', 's' => 'วิ'
    );
    foreach ($string as $k => &$v) {
        $value = ($k === 'w') ? $weeks : $diff->$k;
        if ($value) {
            $v = $value . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . 'ที่แล้ว' : 'เมื่อกี้';
}
?>