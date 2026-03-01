<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

// ดึงข้อมูลสินค้า + ชื่อหมวดหมู่
$sql = "SELECT p.*, c.name as cat_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$products = $pdo->query($sql)->fetchAll();

require __DIR__ . '/admin_layout.php';

$extraHead = '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 10px; }
.table-card { border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
.table thead th { background-color: #f1f3f5; color: #666; font-weight: 600; border: none; padding: 15px; }
.table tbody td { vertical-align: middle; padding: 15px; border-bottom: 1px solid #f1f1f1; }
.status-toggle { cursor: pointer; font-size: 1.8rem; line-height: 1; transition: 0.2s; }
.status-toggle:hover { opacity: 0.8; }
.badge-cat { font-weight: 400; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; }
.cat-1 { background-color: #e3f2fd; color: #0d47a1; }
.cat-2 { background-color: #f3e5f5; color: #4a148c; }
.cat-3 { background-color: #e0f2f1; color: #004d40; }
.cat-4 { background-color: #fff3e0; color: #e65100; }
</style>';

admin_layout_start(
    'จัดการเมนูอาหาร',
    'products',
    'รายการสินค้าทั้งหมด',
    'เพิ่ม แก้ไข เปิด/ปิดการขาย และลบเมนูจากหน้าจอเดียว',
    '<a href="' . admin_escape(admin_url('product_form.php')) . '" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>เพิ่มเมนูใหม่</a>',
    $extraHead
);
?>

<div class="card table-card bg-white">
    <div class="table-responsive">
        <table class="table mb-0 table-hover">
            <thead>
                <tr>
                    <th class="ps-4">รูปภาพ</th>
                    <th>ชื่อเมนู</th>
                    <th>หมวดหมู่</th>
                    <th class="text-center">ราคา</th>
                    <th class="text-center">สต็อก</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-end pe-4">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td class="ps-4">
                        <img src="<?php echo strpos($p['image_url'], 'http') === 0 ? $p['image_url'] : 'uploads/' . $p['image_url']; ?>" class="product-img shadow-sm">
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?php echo $p['name']; ?></div>
                        <small class="text-muted text-truncate d-block" style="max-width: 200px; font-size: 0.8rem;"><?php echo $p['description']; ?></small>
                    </td>
                    <td>
                        <span class="badge badge-cat cat-<?php echo ($p['category_id'] % 4) + 1; ?>"><?php echo $p['cat_name']; ?></span>
                    </td>
                    <td class="text-center fw-bold text-primary"><?php echo number_format($p['price']); ?></td>
                    <td class="text-center">
                        <?php if ($p['stock_qty'] < 20): ?>
                            <span class="text-danger fw-bold"><?php echo $p['stock_qty']; ?></span>
                        <?php else: ?>
                            <span class="text-success"><?php echo $p['stock_qty']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($p['status'] == 'active'): ?>
                            <a href="<?php echo admin_escape(admin_url('product_action.php')); ?>?action=toggle&id=<?php echo $p['id']; ?>" class="text-success status-toggle" title="เปิดขายอยู่ (กดเพื่อปิด)">
                                <i class="fas fa-toggle-on"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo admin_escape(admin_url('product_action.php')); ?>?action=toggle&id=<?php echo $p['id']; ?>" class="text-secondary status-toggle" title="ปิดการขาย (กดเพื่อเปิด)">
                                <i class="fas fa-toggle-off"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?php echo admin_escape(admin_url('product_form.php')); ?>?id=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm rounded-3 shadow-sm text-white"><i class="fas fa-pen"></i></a>
                        <button onclick="confirmDelete(<?php echo $p['id']; ?>)" class="btn btn-danger btn-sm rounded-3 shadow-sm"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$productActionUrl = admin_url('product_action.php');
$extraScripts = '<script>
function confirmDelete(id) {
    Swal.fire({
        title: "ยืนยันการลบ?",
        text: "หากลบแล้วกู้คืนไม่ได้นะ!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "ลบเลย",
        cancelButtonText: "ยกเลิก"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = ' . json_encode($productActionUrl) . ' + `?action=delete&id=${id}`;
        }
    })
}
</script>';

admin_layout_end($extraScripts);
?>