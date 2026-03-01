<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
require __DIR__ . '/../../config/customer_db.php';

ensure_customers_table($pdo);

if (customer_is_logged_in()) {
    header('Location: ' . auth_url('customer_menu.php'));
    exit;
}

$errorMessage = '';
$phone = trim($_POST['phone'] ?? '');
$phoneLast4 = trim($_POST['phone_last4'] ?? '');

function normalize_phone_digits($value)
{
    return preg_replace('/\D+/', '', (string) $value);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $phoneDigitsInput = normalize_phone_digits($phone);
    $last4Input = preg_replace('/\D+/', '', $phoneLast4);

    if ($phoneDigitsInput === '' || $last4Input === '') {
        $errorMessage = 'กรุณากรอกเบอร์โทรศัพท์และ 4 ตัวท้ายให้ครบถ้วน';
    } elseif (strlen($last4Input) !== 4) {
        $errorMessage = '4 ตัวท้ายของเบอร์โทรต้องมี 4 หลัก';
    } else {
        $stmt = $pdo->query('SELECT * FROM customers ORDER BY id DESC');
        $customers = $stmt->fetchAll();

        $matchedCustomer = null;
        foreach ($customers as $candidate) {
            $candidatePhoneDigits = normalize_phone_digits($candidate['phone'] ?? '');
            if ($candidatePhoneDigits === $phoneDigitsInput) {
                $matchedCustomer = $candidate;
                break;
            }
        }

        if (!$matchedCustomer) {
            $errorMessage = 'เบอร์โทรศัพท์หรือ 4 ตัวท้ายไม่ถูกต้อง';
        } else {
            $candidateLast4 = substr(normalize_phone_digits($matchedCustomer['phone']), -4);

            if ($candidateLast4 !== $last4Input) {
                $errorMessage = 'เบอร์โทรศัพท์หรือ 4 ตัวท้ายไม่ถูกต้อง';
            } else {
                customer_login_user($matchedCustomer);
                header('Location: ' . auth_url('customer_menu.php'));
                exit;
            }
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
    <title>เข้าสู่ระบบลูกค้า</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{font-family:'Prompt',sans-serif;background:linear-gradient(135deg,#f5f8ff 0%,#d6ebff 100%);min-height:100vh;display:flex;align-items:center;}
        .card-login{max-width:460px;margin:20px auto;border:0;border-radius:16px;box-shadow:0 18px 40px rgba(13,110,253,.15);}
    </style>
</head>
<body>
<div class="container">
    <div class="card card-login">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 fw-bold mb-2">เข้าสู่ระบบลูกค้า</h1>
            <p class="text-muted mb-4">เข้าสู่ระบบด้วยเบอร์โทรศัพท์ และ 4 ตัวท้ายของเบอร์</p>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
                <div class="alert alert-success py-2">สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ</div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-danger py-2"><?php echo esc($errorMessage); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo esc(auth_url('customer_login.php')); ?>">
                <div class="mb-3">
                    <label class="form-label">เบอร์โทรศัพท์</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo esc($phone); ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">4 ตัวท้ายของเบอร์โทรศัพท์</label>
                    <input type="password" class="form-control" name="phone_last4" maxlength="4" inputmode="numeric" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">เข้าสู่ระบบ</button>
            </form>

            <div class="mt-3 d-flex gap-2">
                <a href="<?php echo esc(auth_url('customer_register.php')); ?>" class="btn btn-outline-success w-100">สมัครสมาชิกใหม่</a>
                <a href="<?php echo esc(auth_url('index.php')); ?>" class="btn btn-outline-secondary w-100">กลับหน้าหลัก</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
