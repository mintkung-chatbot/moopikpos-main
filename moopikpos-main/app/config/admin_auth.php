<?php

if (!function_exists('auth_start_session')) {
    function auth_start_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('auth_base_path')) {
    function auth_base_path()
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(dirname($scriptName), '/');

        if ($basePath === '' || $basePath === '.') {
            return '';
        }

        return $basePath;
    }
}

if (!function_exists('auth_url')) {
    function auth_url($path)
    {
        return auth_base_path() . '/' . ltrim((string) $path, '/');
    }
}

if (!function_exists('admin_is_logged_in')) {
    function admin_is_logged_in()
    {
        auth_start_session();
        return isset($_SESSION['admin_user']) && is_array($_SESSION['admin_user']);
    }
}

if (!function_exists('admin_login_user')) {
    function admin_login_user(array $user)
    {
        auth_start_session();
        session_regenerate_id(true);

        $_SESSION['admin_user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
    }
}

if (!function_exists('admin_logout_user')) {
    function admin_logout_user()
    {
        auth_start_session();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}

if (!function_exists('admin_require_login')) {
    function admin_require_login()
    {
        if (!admin_is_logged_in()) {
            header('Location: ' . auth_url('admin_login.php'));
            exit;
        }
    }
}

if (!function_exists('admin_current_user_name')) {
    function admin_current_user_name()
    {
        auth_start_session();
        return $_SESSION['admin_user']['name'] ?? 'ผู้ดูแลระบบ';
    }
}

if (!function_exists('staff_is_logged_in')) {
    function staff_is_logged_in()
    {
        auth_start_session();
        return isset($_SESSION['staff_user']) && is_array($_SESSION['staff_user']);
    }
}

if (!function_exists('staff_login_user')) {
    function staff_login_user(array $user)
    {
        auth_start_session();
        session_regenerate_id(true);

        $_SESSION['staff_user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
    }
}

if (!function_exists('staff_logout_user')) {
    function staff_logout_user()
    {
        auth_start_session();
        unset($_SESSION['staff_user']);
    }
}

if (!function_exists('staff_require_login')) {
    function staff_require_login()
    {
        if (!staff_is_logged_in()) {
            header('Location: ' . auth_url('staff_login.php'));
            exit;
        }
    }
}

if (!function_exists('staff_current_user_name')) {
    function staff_current_user_name()
    {
        auth_start_session();
        return $_SESSION['staff_user']['name'] ?? 'พนักงาน';
    }
}

if (!function_exists('staff_current_user_id')) {
    function staff_current_user_id()
    {
        auth_start_session();
        return $_SESSION['staff_user']['id'] ?? null;
    }
}

if (!function_exists('customer_is_logged_in')) {
    function customer_is_logged_in()
    {
        auth_start_session();
        return isset($_SESSION['customer_user']) && is_array($_SESSION['customer_user']);
    }
}

if (!function_exists('customer_login_user')) {
    function customer_login_user(array $customer)
    {
        auth_start_session();
        session_regenerate_id(true);

        $_SESSION['customer_user'] = [
            'id' => $customer['id'],
            'username' => $customer['username'],
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name'],
            'nickname' => $customer['nickname'],
            'phone' => $customer['phone'],
            'shipping_address' => $customer['shipping_address'],
            'shipping_latitude' => $customer['shipping_latitude'] ?? null,
            'shipping_longitude' => $customer['shipping_longitude'] ?? null,
        ];
    }
}

if (!function_exists('customer_logout_user')) {
    function customer_logout_user()
    {
        auth_start_session();
        unset($_SESSION['customer_user']);
    }
}

if (!function_exists('customer_require_login')) {
    function customer_require_login()
    {
        if (!customer_is_logged_in()) {
            header('Location: ' . auth_url('customer_login.php'));
            exit;
        }
    }
}

if (!function_exists('customer_current_user')) {
    function customer_current_user()
    {
        auth_start_session();
        return $_SESSION['customer_user'] ?? null;
    }
}

// ================ Chef/Kitchen ================

if (!function_exists('chef_is_logged_in')) {
    function chef_is_logged_in()
    {
        auth_start_session();
        return isset($_SESSION['chef_user']) && is_array($_SESSION['chef_user']);
    }
}

if (!function_exists('chef_login_user')) {
    function chef_login_user(array $user)
    {
        auth_start_session();
        session_regenerate_id(true);

        $_SESSION['chef_user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
    }
}

if (!function_exists('chef_logout_user')) {
    function chef_logout_user()
    {
        auth_start_session();
        unset($_SESSION['chef_user']);
    }
}

if (!function_exists('chef_require_login')) {
    function chef_require_login()
    {
        if (!chef_is_logged_in()) {
            header('Location: ' . auth_url('chef_login.php'));
            exit;
        }
    }
}

if (!function_exists('chef_current_user_name')) {
    function chef_current_user_name()
    {
        auth_start_session();
        return $_SESSION['chef_user']['name'] ?? 'เชฟ';
    }
}

if (!function_exists('chef_current_user_id')) {
    function chef_current_user_id()
    {
        auth_start_session();
        return $_SESSION['chef_user']['id'] ?? null;
    }
}
