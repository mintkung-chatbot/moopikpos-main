<?php
// API: Check for orders ready to serve (status = ready)
// Returns: {ready_orders, pending_count, ready_count}
// Used by staff to get real-time notifications

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/admin_auth.php';

// Require staff login
if (!staff_is_logged_in()) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

try {
    $lastCheckTime = isset($_GET['last_check']) ? intval($_GET['last_check']) : 0;
    
    $pdo = db_connect();
    
    // Get all orders for manual staff ordering (non-WEB orders)
    // This excludes customer web orders which go to staff_requests.php
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            table_no, 
            customer_name,
            status,
            created_at,
            UNIX_TIMESTAMP(updated_at) as updated_time
        FROM orders 
        WHERE table_no NOT LIKE 'WEB-%' 
          AND status IN ('pending', 'cooking', 'ready')
        ORDER BY created_at ASC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Separate by status
    $pending_orders = [];
    $cooking_orders = [];
    $ready_orders = [];
    $new_ready = [];
    
    foreach ($orders as $order) {
        $orderId = (int) $order['id'];
        $status = (string) $order['status'];
        $updatedTime = (int) ($order['updated_time'] ?? 0);
        
        $orderData = [
            'id' => $orderId,
            'table_no' => $order['table_no'],
            'customer_name' => $order['customer_name'],
            'status' => $status,
            'created_at' => $order['created_at']
        ];
        
        if ($status === 'pending') {
            $pending_orders[] = $orderData;
        } elseif ($status === 'cooking') {
            $cooking_orders[] = $orderData;
        } elseif ($status === 'ready') {
            $ready_orders[] = $orderData;
            // Mark as new if updated after last check
            if ($updatedTime > $lastCheckTime) {
                $new_ready[] = $orderData;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'pending_orders' => $pending_orders,
        'cooking_orders' => $cooking_orders,
        'ready_orders' => $ready_orders,
        'new_ready_orders' => $new_ready,
        'pending_count' => count($pending_orders),
        'cooking_count' => count($cooking_orders),
        'ready_count' => count($ready_orders),
        'new_ready_count' => count($new_ready),
        'current_time' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
