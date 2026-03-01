<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
require __DIR__ . '/../../config/customer_db.php';

ensure_customers_table($pdo);
customer_require_login();

$customer = customer_current_user();
$customer_id = (int) ($customer['id'] ?? 0);

function esc($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function auth_url($path)
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = rtrim(dirname($scriptName), '/');
    if ($basePath === '' || $basePath === '.') {
        $basePath = '';
    }
    return $basePath . '/' . ltrim((string) $path, '/');
}

// Fetch all orders for this customer
$sql = "SELECT id, order_type, table_no, customer_name, total_price, status, payment_status, order_time
        FROM orders
        WHERE table_no LIKE 'WEB-%C" . $customer_id . "' 
        ORDER BY order_time DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$itemsByOrder = [];
if (!empty($orders)) {
    $orderIds = array_map(static function ($row) {
        return (int) $row['id'];
    }, $orders);

    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $itemSql = "SELECT oi.order_id, oi.quantity, oi.note, oi.price, p.name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id IN ($placeholders)
                ORDER BY oi.id ASC";
    $itemStmt = $pdo->prepare($itemSql);
    $itemStmt->execute($orderIds);

    foreach ($itemStmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $orderId = (int) $item['order_id'];
        if (!isset($itemsByOrder[$orderId])) {
            $itemsByOrder[$orderId] = [];
        }
        $itemsByOrder[$orderId][] = $item;
    }
}

function status_badge($status)
{
    if ($status === 'pending') {
        return '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-start"></i> รอคิว</span>';
    }
    if ($status === 'cooking') {
        return '<span class="badge bg-info text-white"><i class="fas fa-fire"></i> กำลังปรุง</span>';
    }
    if ($status === 'ready') {
        return '<span class="badge bg-success"><i class="fas fa-check-circle"></i> พร้อมเสิร์ฟ/รับ</span>';
    }
    return '<span class="badge bg-secondary"><i class="fas fa-checkmark-all"></i> เสร็จสิ้น</span>';
}

function status_color($status)
{
    if ($status === 'pending') return '#ffc107';
    if ($status === 'cooking') return '#0dcaf0';
    if ($status === 'ready') return '#198754';
    return '#6c757d';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">
    
    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MooPik Order">
    <meta name="theme-color" content="#dc3545">
    <meta name="description" content="MooPik POS - สถานะออเดอร์">
    
    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="<?php echo auth_url('manifest.json'); ?>">
    <link rel="apple-touch-icon" href="<?php echo auth_url('app/assets/icons/icon-192x192.png'); ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo auth_url('app/assets/icons/icon-192x192.png'); ?>">
    
    <title>สถานะออเดอร์ของฉัน</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #f5f8ff 0%, #d6ebff 100%);
            min-height: 100vh;
            padding: 20px 0;
            padding-bottom: 90px;
        }
        
        .header-section {
            background: white;
            border-bottom: 3px solid #0d6efd;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(13, 110, 253, 0.1);
        }

        .customer-info {
            color: #495057;
            font-size: 0.95rem;
        }

        .order-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 5px solid #ddd;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .order-card.pending { border-left-color: #ffc107; }
        .order-card.cooking { border-left-color: #0dcaf0; }
        .order-card.ready { border-left-color: #198754; }
        .order-card.completed { border-left-color: #6c757d; }

        .order-header {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: #212529;
        }

        .order-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        /* Mobile Bottom Navigation */
        @media (max-width: 768px) {
            .header-section { margin-bottom: 15px; padding: 15px 0; }
            body { padding-bottom: 80px; }
            .mobile-bottom-nav { display: flex !important; }
        }
        .mobile-bottom-nav {
            display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 1050;
            background: #ffffff; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            padding: 8px 0; padding-bottom: max(8px, env(safe-area-inset-bottom));
        }
        .mobile-bottom-nav .nav-container {
            display: flex; justify-content: space-around; align-items: center; width: 100%;
        }
        .mobile-bottom-nav .nav-item {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-decoration: none; color: #6b7280; transition: all 0.2s; padding: 6px 12px; border-radius: 8px;
        }
        .mobile-bottom-nav .nav-item i { font-size: 20px; margin-bottom: 4px; }
        .mobile-bottom-nav .nav-item span { font-size: 11px; font-weight: 500; }
        .mobile-bottom-nav .nav-item:hover, .mobile-bottom-nav .nav-item.active {
            color: #dc3545; background: rgba(220, 53, 69, 0.1);
        }
        .mobile-bottom-nav .nav-item.active i { transform: scale(1.1); }
    </style>
</head>

        .order-body {
            padding: 20px;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-group {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1rem;
            color: #212529;
            font-weight: 500;
        }

        .items-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .items-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 10px;
            color: #212529;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.95rem;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-name {
            flex: 1;
            color: #212529;
            font-weight: 500;
        }

        .item-qty {
            color: #6c757d;
            margin: 0 15px;
            min-width: 40px;
            text-align: center;
        }

        .item-price {
            color: #0d6efd;
            font-weight: 600;
            min-width: 70px;
            text-align: right;
        }

        .item-note {
            color: #dc3545;
            font-size: 0.8rem;
            margin-left: 10px;
            display: block;
            margin-top: 5px;
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
        }

        .total-price {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .total-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0d6efd;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 1.3rem;
            color: #495057;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .empty-text {
            color: #6c757d;
            margin-bottom: 30px;
        }

        .update-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            background: #198754;
            color: white;
            border-radius: 8px;
            font-size: 0.85rem;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            z-index: 1000;
        }

        .update-indicator.show {
            opacity: 1;
        }

        .refresh-hint {
            color: #6c757d;
            font-size: 0.8rem;
            text-align: center;
            margin-top: 30px;
            font-style: italic;
        }

        .nav-back {
            display: inline-block;
            margin-bottom: 20px;
        }

        .nav-back a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-back a:hover {
            color: #0a58ca;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="nav-back">
                <a href="<?php echo esc(auth_url('customer_menu.php')); ?>">
                    <i class="fas fa-arrow-left"></i> กลับไปเมนูอาหาร
                </a>
            </div>
            <h1 class="mb-2"><i class="fas fa-receipt"></i> สถานะออเดอร์ของฉัน</h1>
            <p class="customer-info mb-0">
                <i class="fas fa-user"></i> <?php echo esc($customer['first_name'] . ' ' . ($customer['last_name'] ?? '')); ?> | 
                <i class="fas fa-phone"></i> <?php echo esc($customer['phone']); ?>
            </p>
        </div>
    </div>

    <div class="container">
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h2 class="empty-title">ยังไม่มีออเดอร์</h2>
                <p class="empty-text">คุณยังไม่มีออเดอร์ใดๆ สั่งอาหารกันเลย!</p>
                <a href="<?php echo esc(auth_url('customer_menu.php')); ?>" class="btn btn-primary">
                    <i class="fas fa-hamburger"></i> ดูเมนู
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $orderId = (int) $order['id'];
                        $status = (string) ($order['status'] ?? 'pending');
                        $orderTime = date('d/m/Y H:i', strtotime($order['order_time'] ?? 'now'));
                        $orderNumber = 'ORD-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
                        ?>
                        <div class="order-card <?php echo esc($status); ?>" id="order-<?php echo $orderId; ?>">
                            <div class="order-header">
                                <div>
                                    <div class="order-number"><?php echo esc($orderNumber); ?></div>
                                    <div class="order-time"><i class="fas fa-clock"></i> <?php echo esc($orderTime); ?></div>
                                </div>
                                <div>
                                    <?php echo status_badge($status); ?>
                                </div>
                            </div>

                            <div class="order-body">
                                <div class="order-info">
                                    <div class="info-group">
                                        <div class="info-label">ประเภทการสั่ง</div>
                                        <div class="info-value">
                                            <?php 
                                            $typeText = ($order['order_type'] ?? 'takeaway') === 'delivery' ? '🚗 ส่งอาหาร' : '🏪 รับด้วยตนเอง';
                                            echo esc($typeText);
                                            ?>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">หมายเลขโต๊ะ</div>
                                        <div class="info-value"><?php echo esc($order['table_no']); ?></div>
                                    </div>
                                    <div class="info-group">
                                        <div class="info-label">สถานะการชำระเงิน</div>
                                        <div class="info-value">
                                            <?php 
                                            $payStatus = ($order['payment_status'] ?? 'unpaid') === 'paid' ? '✓ ชำระแล้ว' : 'รอชำระ';
                                            echo esc($payStatus);
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="items-section">
                                    <div class="items-title"><i class="fas fa-list"></i> รายการสั่งอาหาร</div>
                                    <?php foreach (($itemsByOrder[$orderId] ?? []) as $item): ?>
                                        <div class="item-row">
                                            <div class="item-name">
                                                <?php echo esc($item['name']); ?>
                                                <?php if (!empty($item['note'])): ?>
                                                    <span class="item-note">โน้ต: <?php echo esc($item['note']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="item-qty">x<?php echo (int) $item['quantity']; ?></div>
                                            <div class="item-price"><?php echo number_format((float) $item['price'], 2); ?> ฿</div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="order-footer">
                                    <div></div>
                                    <div class="total-price">
                                        <div class="total-label">ยอดรวม</div>
                                        <div class="total-amount"><?php echo number_format((float) $order['total_price'], 2); ?> ฿</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <p class="refresh-hint">💡 หน้านี้จะอัปเดตอัตโนมัติทุก 5 วินาที</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="update-indicator" id="update-indicator">
        <i class="fas fa-sync-alt"></i> อัปเดตสถานะล่าสุด
    </div>

    <!-- Auto-polling for order status updates -->
    <script>
    (function() {
        let lastUpdateTime = 0;
        let pollInterval = 5000; // 5 seconds
        
        function formatTime(timestamp) {
            const date = new Date(timestamp * 1000);
            return date.toLocaleDateString('th-TH', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function showUpdateIndicator() {
            const indicator = document.getElementById('update-indicator');
            indicator.classList.add('show');
            setTimeout(() => {
                indicator.classList.remove('show');
            }, 2000);
        }
        
        function pollOrderStatus() {
            const apiUrl = '<?php echo auth_url("api/check_customer_orders.php"); ?>';
            fetch(apiUrl + `?customer_id=<?php echo $customer_id; ?>&last_check=${lastUpdateTime}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('✅ Customer order status:', data); // Debug
                    if (data.success && data.orders && data.orders.length > 0) {
                        let updated = false;
                        
                        data.orders.forEach(order => {
                            const orderId = order.id;
                            const currentTime = order.current_time || Math.floor(Date.now() / 1000);
                            
                            // Update only if status changed
                            if (order.status_changed) {
                                updated = true;
                                console.log(`Order ${orderId} status changed to ${order.status}`);
                            }
                        });
                        
                        if (updated) {
                            showUpdateIndicator();
                            // Reload page to show updated statuses
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                        
                        if (data.current_time) {
                            lastUpdateTime = data.current_time;
                        }
                    }
                })
                .catch(error => {
                    console.error('❌ Customer poll error:', error.message);
                    console.error('Full error:', error);
                });
        }
        
        // Start polling every 5 seconds
        setInterval(pollOrderStatus, pollInterval);
        
        // Initial check when page loads
        lastUpdateTime = Math.floor(Date.now() / 1000);
    })();
    </script>
    
    <!-- Mobile Bottom Navigation (Customer) -->
    <nav class="mobile-bottom-nav">
        <div class="nav-container">
            <a href="<?php echo auth_url('customer_menu.php'); ?>" class="nav-item">
                <i class="fa-solid fa-utensils"></i>
                <span>เมนูอาหาร</span>
            </a>
            <a href="#" class="nav-item" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>ตะกร้า</span>
            </a>
            <a href="<?php echo auth_url('customer_orders.php'); ?>" class="nav-item active">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>ประวัติ</span>
            </a>
            <a href="<?php echo auth_url('customer_logout.php'); ?>" class="nav-item">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>ออกจากระบบ</span>
            </a>
        </div>
    </nav>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ("serviceWorker" in navigator) {
            window.addEventListener("load", () => {
                navigator.serviceWorker.register("<?php echo auth_url('sw.js'); ?>")
                    .then(reg => console.log("SW registered:", reg.scope))
                    .catch(err => console.log("SW registration failed:", err));
            });
        }
    </script>
</body>
</html>
