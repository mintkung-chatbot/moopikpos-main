<?php
require 'db.php'; // ดึงคอนเนคชันฐานข้อมูล

echo "<div style='font-family: sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>";
echo "<h2>🛠️ ระบบจัดการฐานข้อมูล (Migration)</h2>";
echo "<ul>";

try {
    // 1. สร้างตาราง Users (พนักงาน/เจ้าของ)
    $pdo->exec("CREATE TABLE `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
        `name` varchar(100) NOT NULL,
        `created_at` datetime DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>users</b> สำเร็จ</li>";

    // 1.1 สร้างตาราง Customers (ลูกค้าสมัครผ่านเว็บ)
    $pdo->exec("CREATE TABLE `customers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `first_name` varchar(100) NOT NULL,
        `last_name` varchar(100) NOT NULL,
        `nickname` varchar(100) DEFAULT NULL,
        `phone` varchar(30) NOT NULL,
        `shipping_address` text DEFAULT NULL,
        `created_at` datetime DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>customers</b> สำเร็จ</li>";

    // 2. สร้างตาราง Categories (หมวดหมู่)
    $pdo->exec("CREATE TABLE `categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>categories</b> สำเร็จ</li>";

    // 3. สร้างตาราง Ingredients (วัตถุดิบ)
    $pdo->exec("CREATE TABLE `ingredients` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `stock_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
        `unit` varchar(20) NOT NULL,
        `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>ingredients</b> สำเร็จ</li>";

    // 4. สร้างตาราง Products (เมนูอาหาร) พร้อมระบบ Soft Delete (is_deleted)
    $pdo->exec("CREATE TABLE `products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `category_id` int(11) DEFAULT NULL,
        `name` varchar(100) NOT NULL,
        `description` text DEFAULT NULL,
        `price` decimal(10,2) NOT NULL DEFAULT 0.00,
        `image_url` varchar(255) DEFAULT NULL,
        `status` enum('active','out_of_stock') NOT NULL DEFAULT 'active',
        `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=ปกติ, 1=ถูกลบ',
        `stock_qty` int(11) NOT NULL DEFAULT 0,
        `created_at` datetime DEFAULT current_timestamp(),
        `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>products</b> สำเร็จ</li>";

    // 5. สร้างตาราง Product Recipes (สูตรอาหาร)
    $pdo->exec("CREATE TABLE `product_recipes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `ingredient_id` int(11) NOT NULL,
        `quantity_used` decimal(10,2) NOT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_recipe_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_recipe_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>product_recipes</b> สำเร็จ</li>";

    // 6. สร้างตาราง Orders (ออเดอร์) *เพิ่มระบบชำระเงิน เงินสด/โอน และเงินทอน*
    $pdo->exec("CREATE TABLE `orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL COMMENT 'พนักงานที่เปิดบิล',
        `order_type` enum('dine_in','takeaway','delivery') NOT NULL DEFAULT 'dine_in',
        `table_no` varchar(20) DEFAULT NULL,
        `customer_name` varchar(100) DEFAULT NULL,
        `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
        `status` enum('pending','cooking','ready','completed') NOT NULL DEFAULT 'pending' COMMENT 'สถานะครัว',
        `payment_status` enum('unpaid','paid','cancelled') NOT NULL DEFAULT 'unpaid' COMMENT 'สถานะการจ่ายเงิน',
        `payment_method` enum('cash','transfer') DEFAULT NULL COMMENT 'cash=เงินสด, transfer=โอนเงิน',
        `amount_received` decimal(10,2) DEFAULT NULL COMMENT 'ยอดเงินที่รับมาจากลูกค้า (กรณีเงินสด)',
        `change_amount` decimal(10,2) DEFAULT NULL COMMENT 'เงินทอน (กรณีเงินสด)',
        `order_time` datetime DEFAULT current_timestamp(),
        `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>orders</b> สำเร็จ (รองรับการชำระเงินและเงินทอน)</li>";

    // 7. สร้างตาราง Order Items (รายการอาหารในบิล)
    $pdo->exec("CREATE TABLE `order_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `product_id` int(11) DEFAULT NULL,
        `quantity` int(11) NOT NULL DEFAULT 1,
        `note` text DEFAULT NULL,
        `price` decimal(10,2) NOT NULL COMMENT 'ราคา ณ วันที่ซื้อ',
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>order_items</b> สำเร็จ</li>";

    // 8. สร้างตาราง Shifts (ระบบเปิด-ปิดกะลิ้นชักเงินสด) - *เพิ่มเข้ามาใหม่*
    $pdo->exec("CREATE TABLE `shifts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL COMMENT 'พนักงานที่เปิดกะ',
        `opening_amount` decimal(10,2) NOT NULL COMMENT 'ยอดเงินทอนตั้งต้นที่นับได้',
        `opening_details` text DEFAULT NULL COMMENT 'เก็บข้อมูล JSON จำนวนแบงค์และเหรียญตอนเปิด',
        `closing_amount` decimal(10,2) DEFAULT NULL COMMENT 'ยอดเงินสดที่ระบบคำนวณให้ตอนปิดกะ',
        `actual_closing_amount` decimal(10,2) DEFAULT NULL COMMENT 'ยอดเงินสดที่พนักงานนับได้จริงตอนปิดกะ',
        `closing_details` text DEFAULT NULL COMMENT 'เก็บข้อมูล JSON จำนวนแบงค์และเหรียญตอนปิด',
        `status` enum('open','closed') NOT NULL DEFAULT 'open',
        `opened_at` datetime DEFAULT current_timestamp(),
        `closed_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_shift_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>shifts</b> สำเร็จ (ระบบเปิด-ปิดกะลิ้นชักเงินสด)</li>";

    // 9. สร้างตาราง Expenses (บันทึกรายจ่าย)
    $pdo->exec("CREATE TABLE `expenses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `expense_date` date NOT NULL,
        `item_name` varchar(255) NOT NULL,
        `quantity` decimal(10,2) DEFAULT NULL,
        `unit` varchar(50) DEFAULT NULL,
        `total_price` decimal(10,2) NOT NULL,
        `recorded_by` int(11) DEFAULT NULL,
        `created_at` datetime DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `fk_expense_user` (`recorded_by`),
        CONSTRAINT `fk_expense_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>expenses</b> สำเร็จ (บันทึกรายจ่าย)</li>";

    // 10. สร้างตาราง Tables (จัดการโต๊ะ)
    $pdo->exec("CREATE TABLE `tables` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `table_no` varchar(20) NOT NULL UNIQUE,
        `status` enum('available','occupied') NOT NULL DEFAULT 'available',
        `created_at` datetime DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "<li>✅ สร้างตาราง <b>tables</b> สำเร็จ (จัดการโต๊ะ)</li>";

    // 🔔 Create Trigger: Auto-update updated_at when status changes
    $pdo->exec("CREATE TRIGGER `update_order_timestamp` BEFORE UPDATE ON `orders` 
        FOR EACH ROW 
        BEGIN
            IF NEW.status != OLD.status THEN
                SET NEW.updated_at = NOW();
            END IF;
        END");
    echo "<li>✅ สร้าง <b>Trigger</b> สำเร็จ (auto-update updated_at เมื่อเปลี่ยน status)</li>";

    // 9. เพิ่มข้อมูลตั้งต้น (Seed Data)
    $pdo->exec("INSERT INTO `users` (`username`, `password`, `role`, `name`) VALUES
        ('admin', '123456', 'admin', 'ผู้จัดการร้าน'),
        ('staff', '123456', 'staff', 'พนักงานแคชเชียร์')");

    $pdo->exec("INSERT INTO `categories` (`id`, `name`) VALUES
        (1, 'อาหารจานเดียว'), (2, 'เมนูเส้น/ก๋วยเตี๋ยว'), (3, 'ของทานเล่น'), (4, 'เครื่องดื่ม')");

    $pdo->exec("INSERT INTO `tables` (`table_no`, `status`) VALUES
        ('T01', 'available'), ('T02', 'available'), ('T03', 'available'), ('T04', 'available'), ('T05', 'available')");

    echo "<li style='color: blue;'>🌱 เพิ่มข้อมูลตั้งต้น (Admin, หมวดหมู่, โต๊ะ) เรียบร้อยแล้ว</li>";

    echo "</ul>";
    echo "<h3 style='color: green;'>🎉 Migration เสร็จสมบูรณ์! ฐานข้อมูลพร้อมใช้งานแล้ว</h3>";
    echo "<p><a href='index.php' style='padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>กลับไปหน้าหลัก (Index)</a></p>";

} catch (PDOException $e) {
    // กรณีเกิด Error
    echo "</ul>";
    echo "<h3 style='color: red;'>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</h3>";
    echo "<p>หมายเหตุ: โปรดตรวจสอบว่าคุณได้ลบ Database เก่าและสร้างใหม่ใน phpMyAdmin เรียบร้อยแล้วจริงๆ</p>";
}

echo "</div>";
?>