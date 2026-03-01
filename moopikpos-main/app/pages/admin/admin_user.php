<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();
require __DIR__ . '/admin_layout.php';

// ดึงข้อมูลพนักงานทั้งหมด
$stmt = $pdo->query("SELECT id, username, role, name, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminUsers = array_values(array_filter($users, static function ($user) {
    return ($user['role'] ?? '') === 'admin';
}));

$staffUsers = array_values(array_filter($users, static function ($user) {
    return ($user['role'] ?? '') === 'staff';
}));

$chefUsers = array_values(array_filter($users, static function ($user) {
    return ($user['role'] ?? '') === 'chef';
}));

admin_layout_start(
    'จัดการพนักงาน',
    'users',
    'จัดการพนักงาน (Users)',
    'เพิ่ม แก้ไข ลบ และกำหนดสิทธิ์ผู้ใช้งาน',
);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fa-solid fa-users"></i> จัดการพนักงาน (Users)</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddModal()">
            + เพิ่มพนักงาน
        </button>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success py-2">บันทึกข้อมูลสำเร็จ!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-warning py-2">ลบบัญชีผู้ใช้เรียบร้อยแล้ว!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
        <div class="alert alert-danger py-2">เกิดข้อผิดพลาด: ชื่อผู้ใช้งาน (Username) นี้มีในระบบแล้ว!</div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="text-danger small">ผู้ดูแลระบบ (Admin)</div>
                    <div class="h3 mb-0 fw-bold"><?= count($adminUsers) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="text-primary small">พนักงานหน้าร้าน (Staff)</div>
                    <div class="h3 mb-0 fw-bold"><?= count($staffUsers) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="text-warning small">พนักงานครัว (Chef)</div>
                    <div class="h3 mb-0 fw-bold\"><?= count($chefUsers) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-danger text-white fw-semibold">ส่วนผู้ดูแลระบบ (Admin)</div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ชื่อผู้ใช้งาน (Username)</th>
                        <th>ตำแหน่ง (Role)</th>
                        <th width="20%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($adminUsers)): ?>
                        <tr><td colspan="5" class="text-center text-muted">ยังไม่มีผู้ใช้ตำแหน่ง Admin</td></tr>
                    <?php endif; ?>
                    <?php foreach($adminUsers as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td>
                            <span class="badge bg-danger">Admin (ผู้จัดการ)</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>', '<?= htmlspecialchars($user['username']) ?>', '<?= $user['role'] ?>')">
                                <i class="fa-solid fa-pen-to-square"></i> แก้ไข
                            </button>
                                     <a href="<?= admin_escape(admin_url('user_delete.php')) ?>?id=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-danger <?= ($user['username'] === 'admin') ? 'disabled' : '' ?>" 
                               onclick="return confirm('แน่ใจหรือไม่ที่จะลบบัญชีนี้? (จะไม่สามารถเข้าสู่ระบบได้อีก)')">
                               <i class="fa-solid fa-trash"></i> ลบ
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <small class="text-danger">* หมายเหตุ: บัญชี admin หลักไม่สามารถลบได้</small>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white fw-semibold">ส่วนพนักงานหน้าร้าน (Staff)</div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ชื่อผู้ใช้งาน (Username)</th>
                        <th>ตำแหน่ง (Role)</th>
                        <th width="20%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staffUsers)): ?>
                        <tr><td colspan="5" class="text-center text-muted">ยังไม่มีผู้ใช้ตำแหน่ง Staff</td></tr>
                    <?php endif; ?>
                    <?php foreach($staffUsers as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td>
                            <span class="badge bg-primary">Staff (พนักงาน)</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>', '<?= htmlspecialchars($user['username']) ?>', '<?= $user['role'] ?>')">
                                <i class="fa-solid fa-pen-to-square"></i> แก้ไข
                            </button>
                                     <a href="<?= admin_escape(admin_url('user_delete.php')) ?>?id=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-danger <?= ($user['username'] === 'admin') ? 'disabled' : '' ?>" 
                               onclick="return confirm('แน่ใจหรือไม่ที่จะลบบัญชีนี้? (จะไม่สามารถเข้าสู่ระบบได้อีก)')">
                               <i class="fa-solid fa-trash"></i> ลบ
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark fw-semibold">ส่วนพนักงานครัว (Chef)</div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ชื่อผู้ใช้งาน (Username)</th>
                        <th>ตำแหน่ง (Role)</th>
                        <th width="20%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($chefUsers)): ?>
                        <tr><td colspan="5" class="text-center text-muted">ยังไม่มีผู้ใช้ตำแหน่ง Chef</td></tr>
                    <?php endif; ?>
                    <?php foreach($chefUsers as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td>
                            <span class="badge bg-warning text-dark">Chef (เชฟ)</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>', '<?= htmlspecialchars($user['username']) ?>', '<?= $user['role'] ?>')">
                                <i class="fa-solid fa-pen-to-square"></i> แก้ไข
                            </button>
                                     <a href="<?= admin_escape(admin_url('user_delete.php')) ?>?id=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-danger <?= ($user['username'] === 'admin') ? 'disabled' : '' ?>" 
                               onclick="return confirm('แน่ใจหรือไม่ที่จะลบบัญชีนี้? (จะไม่สามารถเข้าสู่ระบบได้อีก)')">
                               <i class="fa-solid fa-trash"></i> ลบ
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= admin_escape(admin_url('user_save.php')) ?>" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">เพิ่มพนักงาน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล (Name)</label>
                        <input type="text" name="name" id="user_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ใช้งาน (Username) ไว้สำหรับ Login</label>
                        <input type="text" name="username" id="user_username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่าน (Password)</label>
                        <input type="password" name="password" id="user_password" class="form-control">
                        <small class="text-muted" id="password_hint">ใส่รหัสผ่านใหม่ (หากแก้ไขแล้วเว้นว่างไว้ รหัสเดิมจะไม่เปลี่ยน)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">ตำแหน่ง (Role)</label>
                        <select name="role" id="user_role" class="form-select" required>
                            <option value="staff">Staff (พนักงานหน้าร้าน)</option>
                            <option value="admin">Admin (ผู้จัดการ)</option>
                            <option value="chef">Chef (เชฟ)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').innerText = 'เพิ่มพนักงานใหม่';
    document.getElementById('user_id').value = '';
    document.getElementById('user_name').value = '';
    document.getElementById('user_username').value = '';
    document.getElementById('user_password').value = '';
    document.getElementById('user_password').required = true; // บังคับกรอกรหัสถ้าเพิ่มใหม่
    document.getElementById('user_role').value = 'staff';
    document.getElementById('password_hint').style.display = 'none';
    
    var myModal = new bootstrap.Modal(document.getElementById('userModal'));
    myModal.show();
}

function openEditModal(id, name, username, role) {
    document.getElementById('modalTitle').innerText = 'แก้ไขข้อมูลพนักงาน';
    document.getElementById('user_id').value = id;
    document.getElementById('user_name').value = name;
    document.getElementById('user_username').value = username;
    document.getElementById('user_password').value = '';
    document.getElementById('user_password').required = false; // ไม่บังคับกรอกรหัสถ้าแค่แก้ไขชื่อ
    document.getElementById('user_role').value = role;
    document.getElementById('password_hint').style.display = 'block';
    
    var myModal = new bootstrap.Modal(document.getElementById('userModal'));
    myModal.show();
}
</script>

<?php admin_layout_end(); ?>