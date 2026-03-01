<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
admin_require_login();
auth_start_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
    $item_names = $_POST['item_name'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    $total_prices = $_POST['total_price'] ?? [];
    
    // ดึง ID ของแอดมินที่กำลัง Login อยู่ (ถ้ามี session แล้วใช้ session, ถ้าไม่มีรับจาก hidden input ก่อน)
    $recorded_by = $_SESSION['admin_user']['id'] ?? $_POST['recorded_by'] ?? null;

    $stmt = $pdo->prepare(" 
        INSERT INTO expenses (expense_date, item_name, quantity, unit, total_price, recorded_by) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $savedCount = 0;
    foreach ($item_names as $index => $itemNameRaw) {
        $item_name = trim((string) $itemNameRaw);
        $quantity = isset($quantities[$index]) && $quantities[$index] !== '' ? (float) $quantities[$index] : 0;
        $unit = trim((string) ($units[$index] ?? ''));
        $total_price = isset($total_prices[$index]) ? (float) $total_prices[$index] : 0;

        if ($item_name === '' || $total_price <= 0) {
            continue;
        }

        $stmt->execute([$expense_date, $item_name, $quantity, $unit, $total_price, $recorded_by]);
        $savedCount++;
    }

    if ($savedCount === 0) {
        header('Location: ' . auth_url('admin_expenses.php?error=empty'));
        exit();
    }
    
    header('Location: ' . auth_url('admin_expenses.php?success=1'));
    exit();
}