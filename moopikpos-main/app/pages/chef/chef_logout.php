<?php
require __DIR__ . '/../../config/admin_auth.php';

chef_logout_user();
header('Location: ' . auth_url('chef_login.php'));
exit;
