<?php
require __DIR__ . '/../../config/admin_auth.php';

staff_logout_user();
header('Location: ' . auth_url('staff_login.php'));
exit;
