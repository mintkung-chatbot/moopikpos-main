<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารจัดการร้านอาหาร - Restaurant POS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            /* ปรับสีพื้นหลังให้ดูสดใสและเข้ากับธีมร้านอาหาร */
            background: linear-gradient(135deg, #fff1eb 0%, #ace0f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Prompt', sans-serif;
        }
        .system-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            overflow: hidden;
            /* สไตล์ Glassmorphism */
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }
        .system-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: rgba(255, 255, 255, 0.9);
        }
        .icon-box {
            font-size: 3.5rem;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        .system-card:hover .icon-box {
            transform: scale(1.15) rotate(5deg);
        }
        .card-title {
            font-weight: 700;
            font-size: 1.25rem;
        }
        .hero-title h1 {
            font-weight: 800;
            color: #2c3e50;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.05);
        }
        .section-header {
            border-left: 5px solid;
            padding-left: 15px;
            border-radius: 2px;
        }
        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 10px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="hero-title text-center">
            <h1>🍽️ Restaurant POS System</h1>
            <p class="lead text-muted">ระบบบริหารจัดการร้านอาหารแบบครบวงจร</p>
        </div>

        <!-- ============ ส่วนพนักงาน ============ -->
        <div class="mb-5">
            <div class="section-header mb-3">
                <h4 class="fw-bold text-primary"><i class="fas fa-users me-2"></i>ส่วนพนักงาน</h4>
                <p class="text-muted small mb-0">สำหรับพนักงานหน้าร้านและพนักงานครัว</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <a href="staff_login.php" class="text-decoration-none">
                        <div class="card system-card h-100 p-4 text-center">
                            <div class="card-body">
                                <div class="icon-box text-primary">
                                    <i class="fas fa-tablet-alt"></i>
                                </div>
                                <h5 class="card-title text-dark">พนักงานหน้าร้าน</h5>
                                <p class="card-text text-muted small">รับออเดอร์ สั่งอาหาร และเช็คบิล</p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <button class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-1"></i>เข้าสู่ระบบ</button>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 col-lg-4">
                    <a href="chef_login.php" class="text-decoration-none">
                        <div class="card system-card h-100 p-4 text-center" style="background: linear-gradient(135deg, #fff5f0 0%, #ffe6cc 100%);">
                            <div class="card-body">
                                <div class="icon-box" style="color: #ff6f00;">
                                    <i class="fas fa-hat-chef"></i>
                                </div>
                                <h5 class="card-title text-dark">เชฟ/พนักงานครัว</h5>
                                <p class="card-text text-muted small">คิวครัว ระบบอัพเดทสถานะ</p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <button class="btn w-100" style="background-color: #ff6f00; color: white; border-color: #ff6f00;">
                                    <i class="fas fa-sign-in-alt me-1"></i>เข้าสู่ระบบครัว
                                </button>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 col-lg-4">
                    <a href="kitchen_queue.php" class="text-decoration-none">
                        <div class="card system-card h-100 p-4 text-center border-warning">
                            <div class="card-body">
                                <div class="icon-box text-warning">
                                    <i class="fas fa-fire-alt"></i>
                                </div>
                                <h5 class="card-title text-dark">จอแสดงคิวครัว (KDS)</h5>
                                <p class="card-text text-muted small">ดูรายการคิวอาหาร (ไม่ต้องล็อกอิน)</p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <button class="btn btn-outline-warning w-100"><i class="fas fa-tv me-1"></i>เปิดจอคิว</button>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <!-- ============ ส่วนผู้จัดการ ============ -->
        <div class="mb-5">
            <div class="section-header mb-3">
                <h4 class="fw-bold text-success"><i class="fas fa-user-tie me-2"></i>ส่วนผู้จัดการ</h4>
                <p class="text-muted small mb-0">สำหรับเจ้าของร้านและผู้ดูแลระบบ</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 mx-auto">
                    <a href="admin_login.php" class="text-decoration-none">
                        <div class="card system-card h-100 p-4 text-center border-success">
                            <div class="card-body">
                                <div class="icon-box text-success">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h5 class="card-title text-dark">ผู้จัดการ / เจ้าของร้าน</h5>
                                <p class="card-text text-muted small">จัดการเมนู ดูยอดขาย ค่าใช้จ่าย และพนักงาน</p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <button class="btn btn-success w-100"><i class="fas fa-sign-in-alt me-1"></i>จัดการหลังร้าน</button>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <!-- ============ ส่วนลูกค้า ============ -->
        <div class="mb-5">
            <div class="section-header mb-3">
                <h4 class="fw-bold text-secondary"><i class="fas fa-mobile-alt me-2"></i>ส่วนลูกค้า</h4>
                <p class="text-muted small mb-0">สั่งอาหารออนไลน์ผ่านมือถือ</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 mx-auto">
                    <a href="customer_login.php" class="text-decoration-none">
                        <div class="card system-card h-100 p-4 text-center bg-light border-secondary">
                            <div class="card-body">
                                <div class="icon-box text-secondary">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <h5 class="card-title text-dark">ลูกค้า (สแกน QR Code)</h5>
                                <p class="card-text text-muted small">สั่งอาหารผ่านเว็บ ติดตามออเดอร์</p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <button class="btn btn-secondary w-100"><i class="fas fa-utensils me-1"></i>สั่งอาหาร</button>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <hr class="my-4">

        <!-- Footer -->
        <div class="text-center text-muted small py-4">
            <div class="mb-3">
                <a href="register.php" class="btn btn-sm btn-dark"><i class="fas fa-user-plus me-1"></i>สมัครบัญชีผู้ใช้งาน</a>
            </div>
            <p class="mb-1">&copy; <?php echo date("Y"); ?> IT Gen Restaurant Project. All rights reserved.</p>
            <p class="mb-0">พัฒนาโดย: [ชื่อ-นามสกุล ของคุณ]</p>
        </div>
    </div>

</body>
</html>