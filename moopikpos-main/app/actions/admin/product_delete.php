<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // (ทางเลือก) ลบรูปภาพออกจากโฟลเดอร์ด้วยเพื่อไม่ให้รก
    /*
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetch();
    if($img && file_exists('uploads/' . $img['image_url'])){
        unlink('uploads/' . $img['image_url']);
    }
    */

    // ลบข้อมูลจากฐานข้อมูล
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: admin_products.php");
exit;
?>