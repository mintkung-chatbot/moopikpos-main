<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
staff_require_login();

require __DIR__ . '/staff_layout.php';

$table_filter = $_GET['table'] ?? null;

$sql = "SELECT id, order_type, table_no, customer_name, total_price, status, payment_status, payment_method, order_time
        FROM orders
        WHERE status != 'completed'";

$params = [];

if ($table_filter) {
    $sql .= " AND table_no = ?";
    $params[] = $table_filter;
} else {
    // ถ้าไม่ระบุโต๊ะ ให้แสดงเฉพาะของ WEB (หรือจะปรับให้โชว์ทั้งหมดก็ได้ตามต้องการ)
    // แต่เดิมคือ show web-only, เพื่อไม่ให้กระทบ flow เดิม ผมจะคง logic เดิมไว้ถ้าไม่เลือกโต๊ะ
    // หรือถ้าอยากให้เห็นทั้งหมดก็ลบเงื่อนไขนี้ทิ้ง
    // $sql .= " AND table_no LIKE 'WEB-%'"; <-- อันเก่า
    // เพื่อให้เห็นภาพรวม ผมจะเปลี่ยนเป็นแสดงทั้งหมดที่ยังไม่เสร็จ (active orders)
    // หรือจะคงไว้แค่ WEB ก็ได้ แต่นี่คือหน้า "คำขอ"
}

$sql .= " ORDER BY order_time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$itemsByOrder = [];
if (!empty($requests)) {
    $orderIds = array_map(static function ($row) {
        return (int) $row['id'];
    }, $requests);

    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $itemSql = "SELECT oi.order_id, oi.quantity, oi.note, oi.price, p.name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id IN ($placeholders)
                ORDER BY oi.id ASC";
    $stmt = $pdo->prepare($itemSql);
    $stmt->execute($orderIds);

    foreach ($stmt->fetchAll() as $item) {
        $orderId = (int) $item['order_id'];
        if (!isset($itemsByOrder[$orderId])) {
            $itemsByOrder[$orderId] = [];
        }
        $itemsByOrder[$orderId][] = $item;
    }
}

function esc($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function status_badge($status)
{
    if ($status === 'pending') {
        return '<span class="badge bg-warning text-dark">รอคิว</span>';
    }
    if ($status === 'cooking') {
        return '<span class="badge bg-primary">กำลังปรุง</span>';
    }
    if ($status === 'ready') {
        return '<span class="badge bg-success">พร้อมเสิร์ฟ/รับ</span>';
    }
    return '<span class="badge bg-secondary">' . esc($status) . '</span>';
}

$extraHead = '<style>
.request-card{border:0;border-radius:14px;box-shadow:0 4px 14px rgba(0,0,0,.06);} 
.request-meta{font-size:.9rem;color:#6b7280;}
.request-items li{padding:6px 0;border-bottom:1px dashed #e5e7eb;}
.request-items li:last-child{border-bottom:0;}
</style>';

if ($table_filter) {
    $pageTitle = "รายการออเดอร์ โต๊ะ " . esc($table_filter);
    $pageDesc = "รายการอาหารที่สั่งสำหรับโต๊ะ " . esc($table_filter);
} else {
    $pageTitle = "รายการออเดอร์ทั้งหมด";
    $pageDesc = "รวมทุกออเดอร์ (ทานที่ร้าน, กลับบ้าน, เดลิเวอรี่)";
}

staff_layout_start($pageTitle, $pageTitle, $pageDesc, $extraHead);
?>

<div class="container-fluid px-3 px-md-4 pb-4">
    <?php if (isset($_GET['created']) && $_GET['created'] === '1'): ?>
        <div class="alert alert-success py-2"><i class="fa-solid fa-circle-check"></i> สร้างออเดอร์ใหม่สำเร็จ!</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <div class="alert alert-success py-2">อัปเดตสถานะออเดอร์เรียบร้อยแล้ว</div>
    <?php endif; ?>
    <?php if (isset($_GET['paid']) && $_GET['paid'] === '1'): ?>
        <div class="alert alert-success py-2"><i class="fa-solid fa-circle-check"></i> บันทึกการชำระเงินเรียบร้อยแล้ว!</div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="card request-card">
            <div class="card-body text-center py-5 text-muted">
                <i class="fa-regular fa-face-smile-beam fa-2x mb-2"></i>
                <h5 class="mb-1">ยังไม่มีคำขอใหม่จากลูกค้า</h5>
                <small>หน้านี้จะแสดงเฉพาะออเดอร์เว็บที่ยังไม่ completed</small>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($requests as $order): ?>
                <?php $orderId = (int) $order['id']; ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="card request-card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-1">ออเดอร์ #<?php echo $orderId; ?></h5>
                                    <div class="request-meta">
                                        <?php 
                                            if ($order['order_type'] === 'delivery') echo '🛵 จัดส่ง (Delivery)';
                                            elseif ($order['order_type'] === 'takeaway') echo '🥡 กลับบ้าน (Takeaway)';
                                            else echo '🍽️ ทานที่ร้าน (Dine-in)';
                                        ?>
                                        • <?php echo esc($order['table_no']); ?>
                                    </div>
                                </div>
                                <?php echo status_badge($order['status']); ?>
                            </div>

                            <div class="mb-2 request-meta">
                                <div><strong>ลูกค้า:</strong> <?php echo esc($order['customer_name']); ?></div>
                                <div><strong>เวลา:</strong> <?php echo esc($order['order_time']); ?></div>
                                <div><strong>ยอดรวม:</strong> <?php echo number_format((float) $order['total_price'], 2); ?> ฿</div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold mb-1">รายการอาหาร</div>
                                <ul class="list-unstyled request-items mb-0">
                                    <?php foreach (($itemsByOrder[$orderId] ?? []) as $item): ?>
                                        <li>
                                            x<?php echo (int) $item['quantity']; ?> <?php echo esc($item['name']); ?>
                                            <span class="text-muted">(<?php echo number_format((float) $item['price'], 2); ?>)</span>
                                            <?php if (!empty($item['note'])): ?>
                                                <div class="small text-danger">โน้ต: <?php echo esc($item['note']); ?></div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="mt-auto border-top pt-3">
                                <form method="POST" action="<?php echo esc(staff_url('staff_request_update.php')); ?>" class="mb-3">
                                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                                    <div class="input-group">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>รอคิว</option>
                                            <option value="cooking" <?php echo $order['status'] === 'cooking' ? 'selected' : ''; ?>>กำลังปรุง</option>
                                            <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>พร้อมเสิร์ฟ/รับ</option>
                                            <option value="completed">ปิดงาน (completed)</option>
                                        </select>
                                        <button class="btn btn-primary btn-sm" type="submit">อัปเดตสถานะ</button>
                                    </div>
                                </form>

                                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                                    <?php if ($order['payment_status'] === 'unpaid'): ?>
                                        <span class="text-danger fw-bold"><i class="fa-solid fa-circle-xmark"></i> ยังไม่ชำระเงิน</span>
                                        <button type="button" class="btn btn-success btn-sm" 
                                            onclick="openPaymentModal(<?= $orderId ?>, <?= $order['total_price'] ?>)">
                                            <i class="fa-solid fa-money-bill-wave"></i> คิดเงิน
                                        </button>
                                    <?php else: ?>
                                        <span class="text-success fw-bold">
                                            <i class="fa-solid fa-circle-check"></i> ชำระแล้ว (<?= $order['payment_method'] === 'cash' ? 'เงินสด' : 'โอนเงิน' ?>)
                                        </span>
                                        <a href="../shared/print_receipt.php?order_id=<?= $orderId ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="fa-solid fa-print"></i> พิมพ์ใบเสร็จ
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo esc(staff_url('process_payment.php')); ?>" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">ชำระเงิน - ออเดอร์ #<span id="pay_order_id_display"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="pay_order_id">
                    
                    <div class="text-center mb-4">
                        <h4 class="mb-1 text-muted">ยอดรวมที่ต้องชำระ</h4>
                        <h1 class="text-danger fw-bold"><span id="pay_total_price_display">0.00</span> ฿</h1>
                        <input type="hidden" id="pay_total_price" value="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ช่องทางการชำระเงิน</label>
                        <select class="form-select form-select-lg" name="payment_method" id="payment_method" onchange="togglePaymentMethod()">
                            <option value="cash">เงินสด (Cash)</option>
                            <option value="transfer">โอนเงิน/สแกน QR (Transfer)</option>
                        </select>
                    </div>

                    <div id="cash_section">
                        <div class="mb-3">
                            <label class="form-label fw-bold">รับเงินมา (บาท)</label>
                            <input type="number" step="0.01" name="amount_received" id="amount_received" class="form-control form-control-lg text-end" placeholder="กรอกจำนวนเงินที่รับมา" onkeyup="calculateChange()">
                        </div>
                        
                        <div class="p-3 bg-light rounded border text-center">
                            <h5 class="mb-1 text-muted">เงินทอน</h5>
                            <h2 class="text-success fw-bold mb-0"><span id="change_display">0.00</span> ฿</h2>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success btn-lg px-4" id="btn_submit_payment" disabled>ยืนยันการรับเงิน</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันจัดการ Modal คิดเงิน
function openPaymentModal(orderId, totalPrice) {
    document.getElementById('pay_order_id').value = orderId;
    document.getElementById('pay_order_id_display').innerText = orderId;
    document.getElementById('pay_total_price').value = totalPrice;
    document.getElementById('pay_total_price_display').innerText = parseFloat(totalPrice).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    // รีเซ็ตฟอร์มทุกครั้งที่เปิดใหม่
    document.getElementById('payment_method').value = 'cash';
    document.getElementById('amount_received').value = '';
    document.getElementById('change_display').innerText = '0.00';
    togglePaymentMethod();
    
    var myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    myModal.show();
}

function togglePaymentMethod() {
    var method = document.getElementById('payment_method').value;
    var cashSection = document.getElementById('cash_section');
    var btnSubmit = document.getElementById('btn_submit_payment');
    
    if (method === 'transfer') {
        cashSection.style.display = 'none';
        btnSubmit.disabled = false; // ถ้าโอนเงิน กดยืนยันได้เลยไม่ต้องคำนวณเงินทอน
    } else {
        cashSection.style.display = 'block';
        calculateChange(); // คำนวณใหม่ เผื่อเงินที่กรอกไว้ไม่พอ
    }
}

function calculateChange() {
    var total = parseFloat(document.getElementById('pay_total_price').value) || 0;
    var received = parseFloat(document.getElementById('amount_received').value) || 0;
    var btnSubmit = document.getElementById('btn_submit_payment');
    
    var change = received - total;
    if (change >= 0 && received > 0) {
        document.getElementById('change_display').innerText = change.toLocaleString('en-US', {minimumFractionDigits: 2});
        btnSubmit.disabled = false;
    } else {
        document.getElementById('change_display').innerText = 'เงินไม่พอ';
        btnSubmit.disabled = true;
    }
}

// ... (เก็บโค้ด Auto-polling แจ้งเตือนเสียงและ Toast ของเดิมของคุณไว้ด้านล่างนี้ได้เลยครับ) ...
(function() {
    let lastCheckTime = Math.floor(Date.now() / 1000);
    let pollInterval = 5000;
    // ... โค้ดแจ้งเตือนเดิม ...
})();
</script>

<?php staff_layout_end(); ?>