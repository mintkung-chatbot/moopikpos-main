<?php
require __DIR__ . '/../../config/admin_auth.php';

admin_logout_user();
header('Location: ' . auth_url('admin_login.php'));
exit;
