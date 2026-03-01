<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
require __DIR__ . '/../../config/customer_db.php';

ensure_customers_table($pdo);
customer_require_login();

function app_url($path)
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = rtrim(dirname($scriptName), '/');
    if ($basePath === '' || $basePath === '.') {
        $basePath = '';
    }

    return $basePath . '/' . ltrim((string) $path, '/');
}

$cart_json = $_POST['cart_data'] ?? '';
$order_type = $_POST['order_type'] ?? 'takeaway';
$customer_address = trim($_POST['customer_address'] ?? '');
$customer_latitude = trim($_POST['customer_latitude'] ?? '');
$customer_longitude = trim($_POST['customer_longitude'] ?? '');

$customer = customer_current_user();
$customer_first_name = trim((string) ($customer['first_name'] ?? ''));
$customer_last_name = trim((string) ($customer['last_name'] ?? ''));
$customer_nickname = trim((string) ($customer['nickname'] ?? ''));
$customer_phone = trim((string) ($customer['phone'] ?? ''));
$default_address = trim((string) ($customer['shipping_address'] ?? ''));
$default_latitude = $customer['shipping_latitude'] ?? null;
$default_longitude = $customer['shipping_longitude'] ?? null;
$customer_id = (int) ($customer['id'] ?? 0);

if ($customer_address === '') {
    $customer_address = $default_address;
}

if ($customer_latitude === '' && $default_latitude !== null) {
    $customer_latitude = (string) $default_latitude;
}

if ($customer_longitude === '' && $default_longitude !== null) {
    $customer_longitude = (string) $default_longitude;
}

$cart_items = json_decode($cart_json, true);

if ($customer_first_name === '' || $customer_last_name === '' || $customer_phone === '' || $customer_id <= 0) {
    header('Location: ' . app_url('customer_menu.php?error=empty'));
    exit;
}

if (!in_array($order_type, ['takeaway', 'delivery'], true)) {
    $order_type = 'takeaway';
}

if ($order_type === 'delivery' && $customer_address === '') {
    header('Location: ' . app_url('customer_menu.php?error=empty'));
    exit;
}

if (empty($cart_items) || !is_array($cart_items)) {
    header('Location: ' . app_url('customer_menu.php?error=empty'));
    exit;
}

$total_price = 0;

try {
    $pdo->beginTransaction();

    $customer_full_name = trim($customer_first_name . ' ' . $customer_last_name);
    if ($customer_nickname !== '') {
        $customer_full_name .= ' (' . $customer_nickname . ')';
    }

    $customer_display = $customer_full_name . ' | ' . $customer_phone;
    $table_no = $order_type === 'delivery' ? 'WEB-DELIVERY C' . $customer_id : 'WEB-PICKUP C' . $customer_id;

    $sql_order = "INSERT INTO orders (user_id, order_type, table_no, customer_name, total_price, status, order_time) VALUES (NULL, ?, ?, ?, 0, 'pending', NOW())";
    $stmt = $pdo->prepare($sql_order);
    $stmt->execute([$order_type, $table_no, $customer_display]);
    $order_id = $pdo->lastInsertId();

    foreach ($cart_items as $item) {
        $product_id = (int) ($item['id'] ?? 0);
        $qty = (int) ($item['qty'] ?? 0);
        $price = (float) ($item['price'] ?? 0);

        if ($product_id <= 0 || $qty <= 0 || $price < 0) {
            continue;
        }

        $note_parts = [];
        if ($order_type === 'delivery') {
            $note_parts[] = 'ที่อยู่จัดส่ง: ' . $customer_address;
            if (is_numeric($customer_latitude) && is_numeric($customer_longitude)) {
                $note_parts[] = 'GPS: ' . number_format((float) $customer_latitude, 7, '.', '') . ',' . number_format((float) $customer_longitude, 7, '.', '');
            }
        }

        $full_note = implode(' | ', $note_parts);

        $line_total = $price * $qty;
        $total_price += $line_total;

        $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, note, price) VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $pdo->prepare($sql_item);
        $stmt_item->execute([$order_id, $product_id, $qty, $full_note, $price]);

        $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?")->execute([$qty, $product_id]);

        $stmt_recipe = $pdo->prepare("SELECT * FROM product_recipes WHERE product_id = ?");
        $stmt_recipe->execute([$product_id]);
        while ($recipe = $stmt_recipe->fetch()) {
            $used = $recipe['quantity_used'] * $qty;
            $pdo->prepare("UPDATE ingredients SET stock_qty = stock_qty - ? WHERE id = ?")->execute([$used, $recipe['ingredient_id']]);
        }
    }

    if ($total_price <= 0) {
        $pdo->rollBack();
        header('Location: ' . app_url('customer_menu.php?error=empty'));
        exit;
    }

    $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?")->execute([$total_price, $order_id]);

    $pdo->commit();
    header('Location: ' . app_url('customer_menu.php?status=success&order=' . $order_id));
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    echo 'Error: ' . $e->getMessage();
}
