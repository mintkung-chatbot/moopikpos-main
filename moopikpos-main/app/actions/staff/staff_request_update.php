<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
staff_require_login();

$orderId = (int) ($_POST['order_id'] ?? 0);
$status = trim((string) ($_POST['status'] ?? ''));

$allowedStatuses = ['pending', 'cooking', 'ready', 'completed'];

if ($orderId > 0 && in_array($status, $allowedStatuses, true)) {
    // อัปเดตสถานะออเดอร์ (ไม่จำกัดเฉพาะ WEB แล้ว)
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status, $orderId]);
    
    // ดึง table_no กลับมาเพื่อ redirect ถูกหน้า (ถ้ามี)
    $stmtTbl = $pdo->prepare("SELECT table_no FROM orders WHERE id = ?");
    $stmtTbl->execute([$orderId]);
    $tbl = $stmtTbl->fetchColumn();
    
    if ($tbl && strpos($tbl, 'WEB-') === false) {
         header('Location: ' . auth_url('staff_requests.php?updated=1&table=' . urlencode($tbl)));
         exit;
    }
}

header('Location: ' . auth_url('staff_requests.php?updated=1'));
exit;
