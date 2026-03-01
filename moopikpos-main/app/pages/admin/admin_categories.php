<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();
require __DIR__ . '/admin_layout.php';

// ดึงข้อมูลหมวดหมู่ทั้งหมด
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

admin_layout_start(
    'จัดการหมวดหมู่',
    'categories',
    'จัดการหมวดหมู่อาหาร',
    'เพิ่ม แก้ไข และลบหมวดหมู่เมนู'
);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>จัดการหมวดหมู่อาหาร</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openAddModal()">
            + เพิ่มหมวดหมู่
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="10%">ID</th>
                        <th>ชื่อหมวดหมู่</th>
                        <th width="20%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $cat): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                onclick="openEditModal(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>')">
                                แก้ไข
                            </button>
                                     <a href="<?= admin_escape(admin_url('category_delete.php')) ?>?id=<?= $cat['id'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('แน่ใจหรือไม่ที่จะลบหมวดหมู่นี้? (สินค้าในหมวดหมู่นี้จะไม่ถูกลบ แต่หมวดหมู่จะว่างเปล่า)')">
                               ลบ
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= admin_escape(admin_url('category_save.php')) ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">เพิ่มหมวดหมู่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="cat_id">
                    <div class="mb-3">
                        <label class="form-label">ชื่อหมวดหมู่</label>
                        <input type="text" name="name" id="cat_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').innerText = 'เพิ่มหมวดหมู่';
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_name').value = '';
    var myModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    myModal.show();
}

function openEditModal(id, name) {
    document.getElementById('modalTitle').innerText = 'แก้ไขหมวดหมู่';
    document.getElementById('cat_id').value = id;
    document.getElementById('cat_name').value = name;
    var myModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    myModal.show();
}
</script>

<?php admin_layout_end(); ?>