<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($id) {
    if ($action == 'delete') {
        // ลบข้อมูล
        // หมายเหตุ: ในระบบจริงอาจจะแค่เปลี่ยน status เป็น 'deleted' แทนการลบจริง (Soft Delete)
        // แต่ในโปรเจกต์นี้ลบจริงเลยเพื่อให้ง่ายต่อการจัดการ
        
        // (Optional) ลบรูปภาพออกจากเซิฟเวอร์ด้วย
        /*
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetchColumn();
        if ($img && file_exists("uploads/$img") && strpos($img, 'http') === false) {
            unlink("uploads/$img");
        }
        */

        // ต้องลบ recipes ที่ผูกอยู่ก่อน (ถ้ามี constraint) หรือใช้ ON DELETE CASCADE ใน SQL
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    } 
    elseif ($action == 'toggle') {
        // สลับสถานะ Active <-> Out of Stock
        $stmt = $pdo->prepare("SELECT status FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();
        
        $new_status = ($current == 'active') ? 'out_of_stock' : 'active';
        
        $pdo->prepare("UPDATE products SET status = ? WHERE id = ?")->execute([$new_status, $id]);
    }
}

header("Location: admin_products.php");
?>