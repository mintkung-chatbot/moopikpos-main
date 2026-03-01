<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
// เช็คสิทธิ์พนักงาน (ถ้ามีฟังก์ชันเช็ค)
if (function_exists('staff_require_login')) {
    staff_require_login();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $amount_received = $_POST['amount_received'] ?? null;

    // ดึงข้อมูลออเดอร์เพื่อเช็คยอดรวม
    $stmt = $pdo->prepare("SELECT total_price, table_no FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if ($order) {
        $total_price = $order['total_price'];
        $change_amount = 0;

        if ($payment_method === 'cash') {
            // กรณีเงินสด
            $amount_received = (float)$amount_received;
            if ($amount_received < $total_price) {
                // เงินไม่พอ (กันเหนียวเผื่อหลุด validation หน้าเว็บ)
                header('Location: ' . auth_url('staff_requests.php?error=insufficient_amount'));
                exit;
            }
            $change_amount = $amount_received - $total_price;
        } else {
            // กรณีโอนเงิน (ยอดรับ = ยอดรวม, เงินทอน = 0)
            $amount_received = $total_price;
            $change_amount = 0;
        }

        // อัปเดตตาราง orders
        $updateStmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'paid', 
                payment_method = ?, 
                amount_received = ?, 
                change_amount = ? 
            WHERE id = ?
        ");
        $updateStmt->execute([$payment_method, $amount_received, $change_amount, $order_id]);

        // อัปเดตสถานะโต๊ะให้ว่าง (เฉพาะออเดอร์ที่มีโต๊ะและไม่ใช่ WEB)
        if (!empty($order['table_no']) && strpos($order['table_no'], 'WEB-') === false) {
            $freeTable = $pdo->prepare("UPDATE tables SET status = 'available' WHERE table_no = ?");
            $freeTable->execute([$order['table_no']]);
        }

        // กลับไปหน้าที่ส่งมาพร้อมแจ้งเตือนว่าสำเร็จ
        header("Location: " . $_SERVER['HTTP_REFERER'] . (strpos($_SERVER['HTTP_REFERER'], '?') !== false ? '&' : '?') . "paid=1");
        exit;
    }
} else {
    header('Location: ' . auth_url('staff_requests.php'));
    exit;
}