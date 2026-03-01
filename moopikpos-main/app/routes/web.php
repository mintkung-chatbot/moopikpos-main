<?php

return [
    'GET' => [
        '/' => __DIR__ . '/../pages/public/index.php',
        '/web.php' => __DIR__ . '/../pages/public/index.php',
        '/index.php' => __DIR__ . '/../pages/public/index.php',
        '/register.php' => __DIR__ . '/../pages/public/register.php',
        
        // PWA Files
        '/manifest.json' => __DIR__ . '/../../manifest.json',
        '/sw.js' => __DIR__ . '/../../sw.js',
        
        '/customer_menu.php' => __DIR__ . '/../pages/customer/customer_menu.php',
        '/customer_login.php' => __DIR__ . '/../pages/customer/customer_login.php',
        '/customer_register.php' => __DIR__ . '/../pages/customer/customer_register.php',
        '/customer_logout.php' => __DIR__ . '/../pages/customer/customer_logout.php',
        '/customer_orders.php' => __DIR__ . '/../pages/customer/customer_orders.php',

        '/staff_order.php' => __DIR__ . '/../pages/staff/staff_order.php',
        '/staff_requests.php' => __DIR__ . '/../pages/staff/staff_requests.php',
        '/staff_login.php' => __DIR__ . '/../pages/staff/staff_login.php',
        '/staff_logout.php' => __DIR__ . '/../pages/staff/staff_logout.php',
        '/staff_tables.php' => __DIR__ . '/../pages/staff/staff_tables.php',

        '/chef_login.php' => __DIR__ . '/../pages/chef/chef_login.php',
        '/chef_logout.php' => __DIR__ . '/../pages/chef/chef_logout.php',
        '/chef_kitchen.php' => __DIR__ . '/../pages/chef/chef_kitchen.php',

        '/kitchen_queue.php' => __DIR__ . '/../pages/kitchen/kitchen_queue.php',
        '/kitchen_api.php' => __DIR__ . '/../api/kitchen_api.php',
        '/api/check_new_orders.php' => __DIR__ . '/../api/check_new_orders.php',
        '/api/check_customer_orders.php' => __DIR__ . '/../api/check_customer_orders.php',
        '/api/check_ready_orders.php' => __DIR__ . '/../api/check_ready_orders.php',
        

        '/admin_dashboard.php' => __DIR__ . '/../pages/admin/admin_dashboard.php',
        '/admin_login.php' => __DIR__ . '/../pages/admin/admin_login.php',
        '/admin_logout.php' => __DIR__ . '/../pages/admin/admin_logout.php',
        '/admin_history.php' => __DIR__ . '/../pages/admin/admin_history.php',
        '/admin_products.php' => __DIR__ . '/../pages/admin/admin_products.php',
        '/admin_user.php' => __DIR__ . '/../pages/admin/admin_user.php',
        '/product_form.php' => __DIR__ . '/../pages/admin/product_form.php',
        '/admin_categories.php' => __DIR__ . '/../pages/admin/admin_categories.php',
        '/admin_expenses.php' => __DIR__ . '/../pages/admin/admin_expenses.php',

        '/product_delete.php' => __DIR__ . '/../actions/admin/product_delete.php',
        '/product_action.php' => __DIR__ . '/../actions/admin/product_action.php',
        '/print_receipt.php' => __DIR__ . '/../pages/shared/print_receipt.php',

        '/db.php' => __DIR__ . '/../config/db.php',
        '/(ห้ามลบ)migrate.php' => __DIR__ . '/../../migrate.php',
        
    ],

    'POST' => [
        '/admin_login.php' => __DIR__ . '/../pages/admin/admin_login.php',
        '/staff_login.php' => __DIR__ . '/../pages/staff/staff_login.php',
        '/chef_login.php' => __DIR__ . '/../pages/chef/chef_login.php',
        '/register.php' => __DIR__ . '/../pages/public/register.php',
        '/customer_login.php' => __DIR__ . '/../pages/customer/customer_login.php',
        '/customer_register.php' => __DIR__ . '/../pages/customer/customer_register.php',
        '/process_order.php' => __DIR__ . '/../actions/staff/process_order.php',
        '/staff_request_update.php' => __DIR__ . '/../actions/staff/staff_request_update.php',
        '/customer_order_submit.php' => __DIR__ . '/../actions/customer/process_customer_order.php',
        '/product_save.php' => __DIR__ . '/../actions/admin/product_save.php',
        '/kitchen_api.php' => __DIR__ . '/../api/kitchen_api.php',
        '/process_payment.php' => __DIR__ . '/../pages/staff/process_payment.php',
        '/expense_save.php' => __DIR__ . '/../pages/admin/expense_save.php',
        '/expense_delete.php' => __DIR__ . '/../pages/admin/expense_delete.php',
        '/category_save.php' => __DIR__ . '/../pages/admin/categories_save.php',
        '/category_delete.php' => __DIR__ . '/../pages/admin/category_delete.php',
        '/user_save.php' => __DIR__ . '/../pages/admin/user_save.php',
        '/user_delete.php' => __DIR__ . '/../pages/admin/user_delete.php',

    ],
];
