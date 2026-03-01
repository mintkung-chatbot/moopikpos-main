<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

// ค่าเริ่มต้น (กรณีเพิ่มใหม่)
$data = [
    'id' => '', 'name' => '', 'description' => '', 
    'price' => '', 'stock_qty' => 100, 'category_id' => '', 
    'image_url' => '', 'status' => 'active'
];
$title = "เพิ่มเมนูใหม่";

// กรณีแก้ไข (ดึงข้อมูลเก่ามาใส่)
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $data = $stmt->fetch();
    $title = "แก้ไขเมนู: " . $data['name'];
}

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

require __DIR__ . '/admin_layout.php';

$extraHead = '<style>.preview-image{height:100px;border-radius:10px;object-fit:cover;}</style>';

admin_layout_start(
    $title,
    'products',
    $title,
    'กรอกข้อมูลเมนูและบันทึกเพื่ออัปเดตรายการสินค้า',
    '<a href="' . admin_escape(admin_url('admin_products.php')) . '" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>กลับไปหน้ารายการ</a>',
    $extraHead
);
?>

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="admin-surface p-4 p-md-5">
            <form action="<?php echo admin_escape(admin_url('product_save.php')); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                <input type="hidden" name="old_image" value="<?php echo $data['image_url']; ?>">

                <div class="mb-3">
                    <label class="form-label">ชื่อเมนู</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $data['name']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">รายละเอียด/รสชาติ</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo $data['description']; ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">หมวดหมู่</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- เลือกหมวดหมู่ --</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($data['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo $c['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">ราคา (บาท)</label>
                        <input type="number" name="price" class="form-control" value="<?php echo $data['price']; ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">สต็อก (จาน)</label>
                        <input type="number" name="stock_qty" class="form-control" value="<?php echo $data['stock_qty']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">รูปภาพ</label>
                    <?php if ($data['image_url']): ?>
                        <div class="mb-2">
                            <img src="<?php echo strpos($data['image_url'], 'http') === 0 ? $data['image_url'] : 'uploads/' . $data['image_url']; ?>" class="preview-image">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control">
                    <small class="text-muted">รองรับไฟล์ .jpg, .png</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">สถานะการขาย</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo ($data['status'] == 'active') ? 'selected' : ''; ?>>พร้อมขาย (Active)</option>
                        <option value="out_of_stock" <?php echo ($data['status'] == 'out_of_stock') ? 'selected' : ''; ?>>ของหมด (Out of Stock)</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4">บันทึกข้อมูล</button>
                    <a href="<?php echo admin_escape(admin_url('admin_products.php')); ?>" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php admin_layout_end(); ?>