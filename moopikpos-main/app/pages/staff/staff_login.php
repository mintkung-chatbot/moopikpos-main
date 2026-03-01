<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';

if (staff_is_logged_in()) {
    header('Location: ' . auth_url('staff_requests.php'));
    exit;
}

$errorMessage = '';
$username = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errorMessage = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่านให้ครบถ้วน';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password, role, name FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        $isPasswordValid = false;
        if ($user) {
            $storedPassword = (string) $user['password'];
            $isPasswordValid = ($storedPassword === $password) || password_verify($password, $storedPassword);
        }

        if (!$user || !$isPasswordValid || !in_array($user['role'], ['staff', 'admin'], true)) {
            $errorMessage = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        } else {
            staff_login_user($user);
            header('Location: ' . auth_url('staff_requests.php'));
            exit;
        }
    }
}

function esc($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบพนักงานหน้าร้าน</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Prompt', sans-serif; min-height: 100vh; background: linear-gradient(135deg, #f5f8ff 0%, #d6ebff 100%); display: flex; align-items: center; }
        .login-card { max-width: 440px; margin: 0 auto; border: none; border-radius: 16px; box-shadow: 0 18px 40px rgba(13, 110, 253, 0.15); }
        .login-badge { width: 64px; height: 64px; border-radius: 16px; display: grid; place-items: center; background: #0d6efd; color: #fff; font-size: 1.4rem; }
    </style>
</head>
<body>
    <div class="container px-3">
        <div class="card login-card">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="login-badge"><i class="fa-solid fa-cash-register"></i></div>
                    <div>
                        <h1 class="h4 fw-bold mb-1">เข้าสู่ระบบพนักงาน</h1>
                        <p class="text-muted mb-0">สำหรับรับออเดอร์หน้าร้าน</p>
                    </div>
                </div>

                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger py-2"><?php echo esc($errorMessage); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo esc(auth_url('staff_login.php')); ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <input id="username" name="username" type="text" class="form-control" value="<?php echo esc($username); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <input id="password" name="password" type="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-right-to-bracket me-1"></i>เข้าสู่ระบบ</button>
                </form>

                <a href="<?php echo esc(auth_url('index.php')); ?>" class="btn btn-link w-100 text-decoration-none mt-2">กลับหน้าเลือกโหมดระบบ</a>
            </div>
        </div>
    </div>
</body>
</html>
