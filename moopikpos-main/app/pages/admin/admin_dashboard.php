<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();
require __DIR__ . '/admin_layout.php';

// -------------------------------------------------------------
// 1. คำนวณข้อมูลของ "วันนี้"
// -------------------------------------------------------------
// ยอดขายวันนี้ (เฉพาะที่ชำระเงินแล้ว)
$stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders WHERE payment_status = 'paid' AND DATE(order_time) = CURDATE()");
$sales_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// รายจ่ายวันนี้
$stmt = $pdo->query("SELECT SUM(total_price) as total FROM expenses WHERE expense_date = CURDATE()");
$expenses_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// กำไรวันนี้
$profit_today = $sales_today - $expenses_today;

// -------------------------------------------------------------
// 2. คำนวณข้อมูล "รวมทั้งหมด" (All-time)
// -------------------------------------------------------------
// ยอดขายรวม
$stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders WHERE payment_status = 'paid'");
$sales_all = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// รายจ่ายรวม
$stmt = $pdo->query("SELECT SUM(total_price) as total FROM expenses");
$expenses_all = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// กำไรรวม
$profit_all = $sales_all - $expenses_all;

// -------------------------------------------------------------
// 3. ดึงรายการขายล่าสุด 5 รายการ
// -------------------------------------------------------------
$stmt = $pdo->query("SELECT id, table_no, total_price, order_time, payment_method FROM orders WHERE payment_status = 'paid' ORDER BY order_time DESC LIMIT 5");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

admin_layout_start(
    'Dashboard',
    'dashboard',
    'ภาพรวมระบบ (Dashboard)',
    'สรุปยอดขาย รายจ่าย และกำไร/ขาดทุน'
);
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4"><i class="fa-solid fa-chart-line"></i> ภาพรวมระบบ (Dashboard)</h2>

    <h5 class="text-secondary mb-3">ยอดประจำวัน (Today)</h5>
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="fa-solid fa-hand-holding-dollar"></i> ยอดขายวันนี้</h6>
                    <h2 class="mb-0"><?= number_format($sales_today, 2) ?> ฿</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-danger text-white shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="fa-solid fa-file-invoice-dollar"></i> รายจ่าย/ซื้อของวันนี้</h6>
                    <h2 class="mb-0"><?= number_format($expenses_today, 2) ?> ฿</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card <?= $profit_today >= 0 ? 'bg-primary' : 'bg-warning text-dark' ?> text-white shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="fa-solid fa-coins"></i> กำไรสุทธิวันนี้</h6>
                    <h2 class="mb-0"><?= number_format($profit_today, 2) ?> ฿</h2>
                    <?php if($profit_today < 0): ?>
                        <small>(ขาดทุน)</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <h5 class="text-secondary mb-3 mt-4">ยอดรวมสะสมทั้งหมด (All Time)</h5>
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body text-success">
                    <h6 class="card-title">ยอดขายรวมทั้งหมด</h6>
                    <h3 class="mb-0"><?= number_format($sales_all, 2) ?> ฿</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-body text-danger">
                    <h6 class="card-title">รายจ่ายรวมทั้งหมด</h6>
                    <h3 class="mb-0"><?= number_format($expenses_all, 2) ?> ฿</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card <?= $profit_all >= 0 ? 'border-primary text-primary' : 'border-warning text-warning' ?> shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">กำไรสะสมทั้งหมด</h6>
                    <h3 class="mb-0"><?= number_format($profit_all, 2) ?> ฿</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="fa-solid fa-clock-rotate-left"></i> รายการขายล่าสุด 5 อันดับแรก
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ออเดอร์ ID</th>
                                <th>โต๊ะ/ช่องทาง</th>
                                <th>เวลาที่สั่ง</th>
                                <th>ช่องทางชำระเงิน</th>
                                <th class="text-end">ยอดเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recent_orders)): ?>
                                <tr><td colspan="5" class="text-center py-3 text-muted">ยังไม่มีรายการขายที่ชำระเงินแล้ว</td></tr>
                            <?php else: ?>
                                <?php foreach($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['table_no']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['order_time'])) ?></td>
                                    <td>
                                        <?php if($order['payment_method'] === 'cash'): ?>
                                            <span class="badge bg-secondary">เงินสด</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">โอนเงิน</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-success fw-bold">+<?= number_format($order['total_price'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php admin_layout_end(); ?>