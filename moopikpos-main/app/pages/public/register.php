<?php
require __DIR__ . '/../../config/db.php';

$errors = [];
$successMessage = '';

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$role = $_POST['role'] ?? 'staff';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($name === '') {
        $errors[] = 'กรุณากรอกชื่อผู้ใช้';
    }

    if ($username === '') {
        $errors[] = 'กรุณากรอก Username';
    }

    if (!in_array($role, ['admin', 'staff'], true)) {
        $errors[] = 'สิทธิ์ผู้ใช้งานไม่ถูกต้อง';
    }

    if (strlen($password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'ยืนยันรหัสผ่านไม่ตรงกัน';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $errors[] = 'Username นี้ถูกใช้งานแล้ว กรุณาเลือกใหม่';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare('INSERT INTO users (username, password, role, name) VALUES (?, ?, ?, ?)');
            $insert->execute([$username, $hashedPassword, $role, $name]);

            $successMessage = 'สมัครสมาชิกสำเร็จ! สามารถเข้าสู่ระบบแอดมินได้ทันที';
            $name = '';
            $username = '';
            $role = 'staff';
        }
    }
}

function esc($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url($path)
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = rtrim(dirname($scriptName), '/');
    if ($basePath === '' || $basePath === '.') {
        $basePath = '';
    }

    return $basePath . '/' . ltrim((string) $path, '/');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครบัญชีผู้ใช้งาน</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; min-height: 100vh; background: linear-gradient(135deg, #f5f7fa 0%, #dbe7ff 100%); display: flex; align-items: center; }
        .register-card { max-width: 520px; margin: 0 auto; border: none; border-radius: 16px; box-shadow: 0 18px 45px rgba(13, 110, 253, 0.15); }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="card register-card">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 fw-bold mb-2">สมัครบัญชีผู้ใช้งาน</h1>
                <p class="text-muted mb-4">สร้างบัญชีสำหรับเข้าใช้งานระบบร้านอาหาร</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo esc($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($successMessage !== ''): ?>
                    <div class="alert alert-success py-2"><?php echo esc($successMessage); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo esc(app_url('register.php')); ?>">
                    <div class="mb-3">
                        <label class="form-label">ชื่อที่แสดง</label>
                        <input type="text" name="name" class="form-control" value="<?php echo esc($name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo esc($username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สิทธิ์ผู้ใช้งาน</label>
                        <select name="role" class="form-select" required>
                            <option value="staff" <?php echo $role === 'staff' ? 'selected' : ''; ?>>พนักงาน (Staff)</option>
                            <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>ผู้ดูแลระบบ (Admin)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">รหัสผ่าน</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ยืนยันรหัสผ่าน</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">สมัครบัญชี</button>
                </form>

                <div class="mt-3 d-flex gap-2">
                    <a href="<?php echo esc(app_url('admin_login.php')); ?>" class="btn btn-outline-success w-100">ไปหน้าเข้าสู่ระบบแอดมิน</a>
                    <a href="<?php echo esc(app_url('index.php')); ?>" class="btn btn-outline-secondary w-100">กลับหน้าหลัก</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
