<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
require __DIR__ . '/../../config/customer_db.php';

ensure_customers_table($pdo);

if (customer_is_logged_in()) {
    header('Location: ' . auth_url('customer_menu.php'));
    exit;
}

$errors = [];
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$nickname = trim($_POST['nickname'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$shippingAddress = trim($_POST['shipping_address'] ?? '');
$shippingLatitude = trim($_POST['shipping_latitude'] ?? '');
$shippingLongitude = trim($_POST['shipping_longitude'] ?? '');

function normalize_phone_digits($value)
{
    return preg_replace('/\D+/', '', (string) $value);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $phoneDigits = normalize_phone_digits($phone);

    if ($firstName === '' || $lastName === '' || $phone === '' || $shippingAddress === '') {
        $errors[] = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบ';
    }

    if (strlen($phoneDigits) < 8) {
        $errors[] = 'กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง';
    }

    if (empty($errors)) {
        $existingCustomers = $pdo->query('SELECT phone FROM customers')->fetchAll();
        $phoneExists = false;
        foreach ($existingCustomers as $row) {
            if (normalize_phone_digits($row['phone'] ?? '') === $phoneDigits) {
                $phoneExists = true;
                break;
            }
        }

        if ($phoneExists) {
            $errors[] = 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว';
        } else {
            $autoUsername = 'c' . $phoneDigits;
            $pinLast4 = substr($phoneDigits, -4);
            $hash = password_hash($pinLast4, PASSWORD_DEFAULT);
            $lat = is_numeric($shippingLatitude) ? (float) $shippingLatitude : null;
            $lng = is_numeric($shippingLongitude) ? (float) $shippingLongitude : null;
            $insert = $pdo->prepare('INSERT INTO customers (username, password, first_name, last_name, nickname, phone, shipping_address, shipping_latitude, shipping_longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insert->execute([$autoUsername, $hash, $firstName, $lastName, $nickname, $phone, $shippingAddress, $lat, $lng]);

            header('Location: ' . auth_url('customer_login.php?registered=1'));
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
    <title>สมัครสมาชิกลูกค้า</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{font-family:'Prompt',sans-serif;background:linear-gradient(135deg,#f5f8ff 0%,#e6f0ff 100%);min-height:100vh;display:flex;align-items:center;}
        .card-register{max-width:650px;margin:20px auto;border:0;border-radius:16px;box-shadow:0 18px 45px rgba(13,110,253,.15);}
    </style>
</head>
<body>
<div class="container">
    <div class="card card-register">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 fw-bold mb-2">สมัครสมาชิกลูกค้า</h1>
            <p class="text-muted mb-4">สั่งอาหารผ่านเว็บได้ทันทีหลังสมัคร (ใช้เบอร์โทร + 4 ตัวท้ายในการเข้าสู่ระบบ)</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo esc($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo esc(auth_url('customer_register.php')); ?>">
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">ชื่อจริง</label>
                        <input type="text" class="form-control" name="first_name" value="<?php echo esc($firstName); ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" name="last_name" value="<?php echo esc($lastName); ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">ชื่อเล่น</label>
                        <input type="text" class="form-control" name="nickname" value="<?php echo esc($nickname); ?>">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">เบอร์ติดต่อ</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo esc($phone); ?>" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">ที่อยู่จัดส่ง</label>
                        <textarea class="form-control" name="shipping_address" rows="2" required><?php echo esc($shippingAddress); ?></textarea>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="captureGps()">ใช้ตำแหน่งปัจจุบัน (GPS)</button>
                            <small id="gpsStatus" class="text-muted">ไม่บังคับ สามารถข้ามได้</small>
                        </div>
                        <input type="hidden" name="shipping_latitude" id="shippingLatitude" value="<?php echo esc($shippingLatitude); ?>">
                        <input type="hidden" name="shipping_longitude" id="shippingLongitude" value="<?php echo esc($shippingLongitude); ?>">
                    </div>
                </div>
                <button class="btn btn-primary w-100" type="submit">สมัครสมาชิก</button>
            </form>

            <div class="mt-3 d-flex gap-2">
                <a href="<?php echo esc(auth_url('customer_login.php')); ?>" class="btn btn-outline-success w-100">เข้าสู่ระบบลูกค้า</a>
                <a href="<?php echo esc(auth_url('index.php')); ?>" class="btn btn-outline-secondary w-100">กลับหน้าหลัก</a>
            </div>
        </div>
    </div>
</div>
<script>
function captureGps() {
    const statusEl = document.getElementById('gpsStatus');
    if (!navigator.geolocation) {
        statusEl.textContent = 'เบราว์เซอร์นี้ไม่รองรับ GPS';
        statusEl.className = 'text-danger';
        return;
    }

    statusEl.textContent = 'กำลังดึงพิกัด...';
    statusEl.className = 'text-primary';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            document.getElementById('shippingLatitude').value = position.coords.latitude.toFixed(7);
            document.getElementById('shippingLongitude').value = position.coords.longitude.toFixed(7);
            statusEl.textContent = `บันทึกพิกัดแล้ว (${position.coords.latitude.toFixed(5)}, ${position.coords.longitude.toFixed(5)})`;
            statusEl.className = 'text-success';
        },
        () => {
            statusEl.textContent = 'ไม่สามารถดึงพิกัดได้ กรุณาอนุญาตตำแหน่งหรือใช้ที่อยู่ข้อความแทน';
            statusEl.className = 'text-danger';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}
</script>
</body>
</html>
