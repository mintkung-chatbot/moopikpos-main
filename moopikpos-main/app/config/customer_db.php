<?php

if (!function_exists('ensure_customers_table')) {
    function ensure_customers_table(PDO $pdo)
    {
        $sql = "CREATE TABLE IF NOT EXISTS `customers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `first_name` varchar(100) NOT NULL,
            `last_name` varchar(100) NOT NULL,
            `nickname` varchar(100) DEFAULT NULL,
            `phone` varchar(30) NOT NULL,
            `shipping_address` text DEFAULT NULL,
            `shipping_latitude` decimal(10,7) DEFAULT NULL,
            `shipping_longitude` decimal(10,7) DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $pdo->exec($sql);

        $columns = $pdo->query('SHOW COLUMNS FROM customers')->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_map(static function ($column) {
            return $column['Field'] ?? '';
        }, $columns);

        if (!in_array('shipping_latitude', $columnNames, true)) {
            $pdo->exec('ALTER TABLE customers ADD COLUMN shipping_latitude decimal(10,7) DEFAULT NULL AFTER shipping_address');
        }

        if (!in_array('shipping_longitude', $columnNames, true)) {
            $pdo->exec('ALTER TABLE customers ADD COLUMN shipping_longitude decimal(10,7) DEFAULT NULL AFTER shipping_latitude');
        }
    }
}
