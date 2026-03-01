<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name']);

    if (!empty($id)) {
        // กรณีมี ID แปลว่าเป็นการ "แก้ไข"
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    } else {
        // กรณีไม่มี ID แปลว่าเป็นการ "เพิ่มใหม่"
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    
    // กลับไปหน้าจัดการหมวดหมู่
    header('Location: ' . auth_url('admin_categories.php?success=1'));
    exit();
}