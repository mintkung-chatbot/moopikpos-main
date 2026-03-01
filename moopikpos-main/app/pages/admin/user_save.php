<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // เช็คก่อนว่า Username นี้มีคนใช้หรือยัง (ป้องกันชื่อซ้ำ)
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $checkStmt->execute([$username, $id ?: 0]);
    if ($checkStmt->rowCount() > 0) {
        header('Location: ' . auth_url('admin_user.php?error=duplicate'));
        exit();
    }

    if (!empty($id)) {
        // กรณีมี ID แปลว่าเป็นการ "แก้ไขข้อมูล"
        if (!empty($password)) {
            // ถ้ามีการพิมพ์รหัสผ่านใหม่มา ให้อัปเดตรหัสผ่านด้วย
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->execute([$name, $username, $password, $role, $id]);
        } else {
            // ถ้าเว้นว่างรหัสผ่าน แปลว่าแก้ไขแค่ชื่อหรือตำแหน่ง
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, role = ? WHERE id = ?");
            $stmt->execute([$name, $username, $role, $id]);
        }
    } else {
        // กรณีไม่มี ID แปลว่าเป็นการ "เพิ่มผู้ใช้ใหม่"
        $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $username, $password, $role]);
    }
    
    // กลับไปหน้าจัดการพนักงาน
    header('Location: ' . auth_url('admin_user.php?success=1'));
    exit();
}