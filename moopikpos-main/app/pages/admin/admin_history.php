<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();

// 1. รับค่าเดือน/ปี ที่เลือก (ถ้าไม่เลือก ให้เป็นเดือนปัจจุบัน)
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$view_date = isset($_GET['view_date']) ? $_GET['view_date'] : null;

// 2. ดึงยอดขายรวมประจำเดือน
$stmt = $pdo->prepare("SELECT SUM(total_price) as total, COUNT(*) as count FROM orders 
                       WHERE DATE_FORMAT(order_time, '%Y-%m') = ? AND status = 'completed'");
$stmt->execute([$selected_month]);
$month_summary = $stmt->fetch();

// 3. ดึงยอดขายรายวัน (เพื่อมาทำกราฟ และ ลิสต์รายการ)
$sql_daily = "SELECT DATE(order_time) as sale_date, SUM(total_price) as total, COUNT(*) as orders 
              FROM orders 
              WHERE DATE_FORMAT(order_time, '%Y-%m') = ? AND status = 'completed'
              GROUP BY DATE(order_time) 
              ORDER BY sale_date DESC";
$stmt = $pdo->prepare($sql_daily);
$stmt->execute([$selected_month]);
$daily_sales = $stmt->fetchAll();

// 4. ถ้ามีการเลือกวันที่ (view_date) ให้ดึงรายการเมนูที่ขายได้ในวันนั้น
$sold_items = [];
if ($view_date) {
    $sql_items = "SELECT p.name, p.image_url, SUM(oi.quantity) as qty, SUM(oi.price * oi.quantity) as total_revenue
                  FROM order_items oi
                  JOIN orders o ON oi.order_id = o.id
                  JOIN products p ON oi.product_id = p.id
                  WHERE DATE(o.order_time) = ? AND o.status = 'completed'
                  GROUP BY p.id
                  ORDER BY qty DESC";
    $stmt = $pdo->prepare($sql_items);
    $stmt->execute([$view_date]);
    $sold_items = $stmt->fetchAll();
}

// เตรียมข้อมูลกราฟ
$chart_labels = [];
$chart_data = [];
// ต้องเรียงวันที่จากน้อยไปมากสำหรับกราฟ
$graph_data = array_reverse($daily_sales); 
foreach ($graph_data as $day) {
    $chart_labels[] = date('d/m', strtotime($day['sale_date']));
    $chart_data[] = $day['total'];
}

require __DIR__ . '/admin_layout.php';

$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.card-stat { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.date-row { cursor: pointer; transition: 0.2s; }
.date-row:hover { background-color: #e9ecef; }
.date-row.active { background-color: #e3f2fd; border-left: 5px solid #0d6efd; }
.menu-img-sm { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; }
</style>';

admin_layout_start(
    'ประวัติยอดขาย - รายเดือน',
    'history',
    'ประวัติยอดขาย',
    'ตรวจสอบยอดขายรายวันและเมนูที่ขายได้ในแต่ละวัน',
    '',
    $extraHead
);
?>

<div class="row align-items-center g-3">
    <div class="col-md-4">
        <form method="GET" class="d-flex gap-2">
            <input type="month" name="month" class="form-control" value="<?php echo $selected_month; ?>" onchange="this.form.submit()">
        </form>
    </div>
    <div class="col-md-8 text-md-end">
        <div class="d-inline-block bg-white px-4 py-2 rounded-pill shadow-sm me-md-3 mb-2 mb-md-0">
            <small class="text-muted">ยอดขายรวมเดือนนี้</small>
            <h4 class="mb-0 text-success fw-bold"><?php echo number_format($month_summary['total'], 0); ?> ฿</h4>
        </div>
        <div class="d-inline-block bg-white px-4 py-2 rounded-pill shadow-sm">
            <small class="text-muted">จำนวนออเดอร์</small>
            <h4 class="mb-0 text-primary fw-bold"><?php echo number_format($month_summary['count']); ?> รายการ</h4>
        </div>
    </div>
</div>

<div class="admin-surface p-3 p-md-4">
    <h5 class="fw-bold text-muted mb-3"><i class="fas fa-chart-area"></i> แนวโน้มยอดขายรายวัน</h5>
    <canvas id="monthlyChart" height="80"></canvas>
</div>

<div class="row">
    <div class="col-md-5 mb-3 mb-md-0">
        <div class="card card-stat h-100">
            <div class="card-header bg-white fw-bold py-3">
                <i class="fas fa-calendar-alt text-warning"></i> เลือกวันที่ดูรายละเอียด
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($daily_sales as $day): 
                        $is_active = ($view_date == $day['sale_date']) ? 'active' : '';
                    ?>
                    <a href="?month=<?php echo $selected_month; ?>&view_date=<?php echo $day['sale_date']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center date-row <?php echo $is_active; ?>">
                        <div>
                            <span class="fw-bold"><?php echo date('d/m/Y', strtotime($day['sale_date'])); ?></span>
                            <br><small class="text-muted"><?php echo $day['orders']; ?> ออเดอร์</small>
                        </div>
                        <span class="badge bg-success rounded-pill" style="font-size: 0.9rem;"><?php echo number_format($day['total']); ?> ฿</span>
                    </a>
                    <?php endforeach; ?>

                    <?php if (empty($daily_sales)): ?>
                        <div class="p-4 text-center text-muted">ไม่พบยอดขายในเดือนนี้</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-stat h-100">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between">
                <span>
                    <i class="fas fa-utensils text-danger"></i> รายการอาหารที่ขายได้
                    <?php if ($view_date) echo " (" . date('d/m/Y', strtotime($view_date)) . ")"; ?>
                </span>
                <?php if ($view_date): ?>
                    <span class="badge bg-primary">รวม <?php echo count($sold_items); ?> เมนู</span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (!$view_date): ?>
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5 text-muted opacity-50">
                        <i class="fas fa-hand-pointer fa-3x mb-3"></i>
                        <h5>เลือกวันที่จากตารางฝั่งซ้าย</h5>
                        <p>เพื่อดูว่าวันนั้นขายเมนูอะไรไปบ้าง</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">เมนู</th>
                                    <th class="text-center">จำนวน (จาน)</th>
                                    <th class="text-end pe-3">ยอดขาย (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sold_items as $item): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo strpos($item['image_url'], 'http') === 0 ? $item['image_url'] : 'uploads/' . $item['image_url']; ?>" class="menu-img-sm me-2">
                                            <span><?php echo $item['name']; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="fw-bold fs-5"><?php echo $item['qty']; ?></span></td>
                                    <td class="text-end pe-3 fw-bold text-success"><?php echo number_format($item['total_revenue']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script>
const ctx = document.getElementById("monthlyChart");
new Chart(ctx, {
    type: "line",
    data: {
        labels: ' . json_encode($chart_labels) . ',
        datasets: [{
            label: "ยอดขาย (บาท)",
            data: ' . json_encode($chart_data) . ',
            borderColor: "#0d6efd",
            backgroundColor: "rgba(13, 110, 253, 0.1)",
            borderWidth: 2,
            fill: true,
            tension: 0.3,
            pointRadius: 4,
            pointBackgroundColor: "#fff",
            pointBorderColor: "#0d6efd"
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
            x: { grid: { display: false } }
        }
    }
});
</script>';

admin_layout_end($extraScripts);
?>