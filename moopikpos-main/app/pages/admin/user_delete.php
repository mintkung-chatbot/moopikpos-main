<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // ดึงข้อมูลมาเช็คก่อน เพื่อป้องกันการเผลอลบบัญชี admin หลักของร้าน
    $check = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $check->execute([$id]);
    $user = $check->fetch();

    if ($user && $user['username'] !== 'admin') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header('Location: ' . auth_url('admin_user.php?deleted=1'));
exit();