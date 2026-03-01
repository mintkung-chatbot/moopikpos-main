<?php
require __DIR__ . '/../../config/admin_auth.php';

customer_logout_user();
header('Location: ' . auth_url('customer_login.php'));
exit;
