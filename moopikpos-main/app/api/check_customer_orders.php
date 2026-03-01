<?php
// API: Check customer order statuses for real-time updates
// Returns: {orders with status_changed flag}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/admin_auth.php';

$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$lastCheckTime = isset($_GET['last_check']) ? intval($_GET['last_check']) : 0;

if ($customer_id <= 0) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid customer_id']));
}

try {
    $pdo = db_connect();
    
    // Query all orders for this customer
    $sql = "SELECT id, order_type, table_no, status, created_at, updated_at,
                   UNIX_TIMESTAMP(created_at) as created_time,
                   UNIX_TIMESTAMP(updated_at) as updated_time
            FROM orders 
            WHERE table_no LIKE 'WEB-%C" . $customer_id . "'
            ORDER BY created_at DESC";
    
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    foreach ($orders as $order) {
        $orderId = (int) $order['id'];
        $updatedTime = (int) ($order['updated_time'] ?? 0);
        $statusChanged = $updatedTime > $lastCheckTime;
        
        $results[] = [
            'id' => $orderId,
            'table_no' => $order['table_no'],
            'status' => $order['status'],
            'status_changed' => $statusChanged,
            'updated_at' => $order['updated_at'],
            'current_time' => time()
        ];
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $results,
        'current_time' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
