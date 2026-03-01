<?php
if (!function_exists('admin_escape')) {
    function admin_escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('admin_base_path')) {
    function admin_base_path()
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(dirname($scriptName), '/');

        if ($basePath === '' || $basePath === '.') {
            return '';
        }

        return $basePath;
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path)
    {
        $normalizedPath = '/' . ltrim((string) $path, '/');
        return admin_base_path() . $normalizedPath;
    }
}

if (!function_exists('admin_layout_start')) {
    function admin_layout_start($title, $activeMenu, $pageHeading, $pageSubheading = '', $headerActions = '', $extraHead = '')
    {
        $menus = [
            ['key' => 'dashboard', 'label' => 'ภาพรวม', 'icon' => 'fa-chart-line', 'url' => 'admin_dashboard.php'],
            ['key' => 'history', 'label' => 'ประวัติยอดขาย', 'icon' => 'fa-clock-rotate-left', 'url' => 'admin_history.php'],
            ['key' => 'products', 'label' => 'จัดการเมนู', 'icon' => 'fa-utensils', 'url' => 'admin_products.php'],
            ['key' => 'categories', 'label' => 'หมวดหมู่', 'icon' => 'fa-tags', 'url' => 'admin_categories.php'],
            ['key' => 'users', 'label' => 'พนักงาน', 'icon' => 'fa-users', 'url' => 'admin_user.php'],
            ['key' => 'expenses', 'label' => 'บันทึกรายจ่าย', 'icon' => 'fa-money-bill-transfer', 'url' => 'admin_expenses.php'],
        ];

        $safeTitle = admin_escape($title);
        $safeHeading = admin_escape($pageHeading);
        $safeSubheading = admin_escape($pageSubheading);

        echo '<!DOCTYPE html>';
        echo '<html lang="th">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . $safeTitle . '</title>';
        echo '<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
        echo '<style>';
        echo 'body{font-family:\'Prompt\',sans-serif;background:#f8f9fa;color:#1f2937;}';
        echo '.admin-shell{display:flex;min-height:100vh;}';
        echo '.admin-sidebar{width:260px;background:#fff;border-right:none;box-shadow:4px 0 24px rgba(0,0,0,0.03);padding:22px 18px;position:sticky;top:0;height:100vh;display:flex;flex-direction:column;z-index:10;}';
        echo '.admin-brand{font-size:1.15rem;font-weight:700;color:#0d6efd;text-decoration:none;display:flex;align-items:center;gap:12px;margin-bottom:30px;padding:0 10px;}';
        echo '.admin-brand i {font-size:1.4rem;}';
        echo '.admin-nav{display:flex;flex-direction:column;gap:8px;}';
        echo '.admin-nav-link{display:flex;align-items:center;gap:12px;border-radius:12px;padding:12px 16px;text-decoration:none;color:#6b7280;font-weight:500;transition:all 0.3s ease;}';
        echo '.admin-nav-link:hover{background:#f1f5f9;color:#0d6efd;transform:translateX(5px);}';
        echo '.admin-nav-link.active{background:linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);color:#fff;box-shadow:0 6px 15px rgba(13,110,253,.25);}';
        echo '.admin-sidebar-footer{margin-top:auto;}';
        echo '.admin-main{flex:1;padding:24px 32px;}';
        echo '.admin-topbar{background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.03);padding:16px 24px;display:flex;justify-content:space-between;gap:18px;align-items:center;margin-bottom:24px;position:sticky;top:15px;z-index:5;}';
        echo '.admin-page-title{margin:0;font-size:1.4rem;font-weight:700;color:#111827;}';
        echo '.admin-page-subtitle{margin:4px 0 0;color:#6b7280;font-size:.95rem;}';
        echo '.admin-content{display:flex;flex-direction:column;gap:20px;}';
        echo '.admin-surface{background:#fff;border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.03);padding:24px;}';
        echo '@media (max-width: 991px){.admin-shell{flex-direction:column;}.admin-sidebar{position:relative;height:auto;width:100%;box-shadow:0 4px 12px rgba(0,0,0,0.05);}.admin-main{padding:16px;}.admin-topbar{position:static;flex-direction:column;align-items:flex-start;}}';
        echo '</style>';
        foreach ($menus as $menu) {
            $activeClass = $menu['key'] === $activeMenu ? ' active' : '';
            echo '<a class="admin-nav-link' . $activeClass . '" href="' . admin_escape(admin_url($menu['url'])) . '">';
            echo '<i class="fa-solid ' . admin_escape($menu['icon']) . '"></i>';
            echo '<span>' . admin_escape($menu['label']) . '</span>';
            echo '</a>';
        }

        echo '</nav>';
        echo '<div class="admin-sidebar-footer">';
        echo '<a class="admin-nav-link text-danger" href="' . admin_escape(admin_url('admin_logout.php')) . '"><i class="fa-solid fa-right-from-bracket"></i><span>ออกจากระบบ</span></a>';
        echo '</div>';
        echo '</aside>';
        echo '<main class="admin-main">';
        echo '<header class="admin-topbar">';
        echo '<div>';
        echo '<h1 class="admin-page-title">' . $safeHeading . '</h1>';
        if ($safeSubheading !== '') {
            echo '<p class="admin-page-subtitle">' . $safeSubheading . '</p>';
        }
        echo '</div>';
        echo '<div class="d-flex gap-2 align-items-center flex-wrap">';
        echo '<span class="badge bg-light text-dark border"><i class="fa-regular fa-calendar me-1"></i>' . date('d/m/Y') . '</span>';
        echo $headerActions;
        echo '</div>';
        echo '</header>';
        echo '<section class="admin-content">';
    }
}

if (!function_exists('admin_layout_end')) {
    function admin_layout_end($extraScripts = '')
    {
        echo '</section>';
        echo '</main>';
        echo '</div>';
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
        echo $extraScripts;
        echo '</body>';
        echo '</html>';
    }
}
