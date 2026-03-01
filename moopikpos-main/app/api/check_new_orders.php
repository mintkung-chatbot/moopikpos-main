<?php
// API: Check for new customer web orders (WEB-*)
// Returns: {new_count, pending_orders}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/admin_auth.php';

// Require staff login
if (!staff_is_logged_in()) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

try {
    // Get timestamp from client (to detect new orders since last check)
    $lastCheckTime = isset($_GET['last_check']) ? intval($_GET['last_check']) : 0;
    
    // Query pending/cooking orders (WEB-* only) created/updated recently
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            table_no, 
            customer_name, 
            status,
            created_at,
            UNIX_TIMESTAMP(created_at) as created_time
        FROM orders 
        WHERE table_no LIKE 'WEB-%' 
          AND status IN ('pending', 'cooking')
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count new orders (created after last_check timestamp)
    $new_count = 0;
    $new_orders = [];
    
    foreach ($orders as $order) {
        if ($order['created_time'] > $lastCheckTime) {
            $new_count++;
            $new_orders[] = [
                'id' => $order['id'],
                'table_no' => $order['table_no'],
                'customer_name' => $order['customer_name'],
                'status' => $order['status'],
                'created_at' => $order['created_at']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'new_count' => $new_count,
        'pending_count' => count(array_filter($orders, fn($o) => $o['status'] === 'pending')),
        'new_orders' => $new_orders,
        'current_time' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
