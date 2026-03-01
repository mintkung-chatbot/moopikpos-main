<?php
if (!function_exists('chef_escape')) {
    function chef_escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('chef_base_path')) {
    function chef_base_path()
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(dirname($scriptName), '/');

        if ($basePath === '' || $basePath === '.') {
            return '';
        }

        return $basePath;
    }
}

if (!function_exists('chef_url')) {
    function chef_url($path)
    {
        $normalizedPath = '/' . ltrim((string) $path, '/');
        return chef_base_path() . $normalizedPath;
    }
}

if (!function_exists('chef_layout_start')) {
    function chef_layout_start($title, $pageHeading, $pageSubheading = '', $extraHead = '')
    {
        $safeTitle = chef_escape($title);
        $safeHeading = chef_escape($pageHeading);
        $safeSubheading = chef_escape($pageSubheading);

        $currentPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
        $isKitchenPage = $currentPage === 'chef_kitchen.php';

        echo '<!DOCTYPE html>';
        echo '<html lang="th">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">';
        
        // PWA Meta Tags
        echo '<meta name="mobile-web-app-capable" content="yes">';
        echo '<meta name="apple-mobile-web-app-capable" content="yes">';
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">';
        echo '<meta name="apple-mobile-web-app-title" content="MooPik Chef">';
        echo '<meta name="theme-color" content="#ff6f00">';
        echo '<meta name="description" content="MooPik POS - ระบบคิวครัว">';
        
        // PWA Manifest & Icons
        echo '<link rel="manifest" href="' . chef_base_path() . '/manifest.json">';
        echo '<link rel="apple-touch-icon" href="' . chef_base_path() . '/app/assets/icons/icon-192x192.png">';
        echo '<link rel="icon" type="image/png" sizes="192x192" href="' . chef_base_path() . '/app/assets/icons/icon-192x192.png">';
        
        echo '<title>' . $safeTitle . '</title>';
        echo '<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
        echo '<style>';
        echo 'body{font-family:\'Prompt\',sans-serif;background:#212529;color:#fff;padding-bottom:70px;}';
        echo '.chef-topbar{position:sticky;top:0;z-index:1030;background:#343a40;border-bottom:2px solid #ff6f00;padding:12px 0;}';
        echo '.chef-shell{padding:18px 0 0;}';
        echo '.chef-title{font-size:1.3rem;font-weight:600;margin:0;color:#fff;}';
        echo '.chef-subtitle{margin:2px 0 0;color:#adb5bd;font-size:.9rem;}';
        
        // Mobile Bottom Navigation CSS
        echo '@media (max-width: 768px) {';
        echo '  .chef-topbar, .container-fluid > .d-flex.gap-2 { display: none !important; }';
        echo '  body { padding-bottom: 80px; }';
        echo '  .mobile-bottom-nav { display: flex !important; }';
        echo '}';
        echo '.mobile-bottom-nav {';
        echo '  display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 1050;';
        echo '  background: #343a40; box-shadow: 0 -2px 10px rgba(0,0,0,0.3);';
        echo '  padding: 8px 0; padding-bottom: max(8px, env(safe-area-inset-bottom));';
        echo '}';
        echo '.mobile-bottom-nav .nav-container {';
        echo '  display: flex; justify-content: space-around; align-items: center; width: 100%;';
        echo '}';
        echo '.mobile-bottom-nav .nav-item {';
        echo '  display: flex; flex-direction: column; align-items: center; justify-content: center;';
        echo '  text-decoration: none; color: #adb5bd; transition: all 0.2s; padding: 6px 12px; border-radius: 8px;';
        echo '}';
        echo '.mobile-bottom-nav .nav-item i { font-size: 20px; margin-bottom: 4px; }';
        echo '.mobile-bottom-nav .nav-item span { font-size: 11px; font-weight: 500; }';
        echo '.mobile-bottom-nav .nav-item:hover, .mobile-bottom-nav .nav-item.active {';
        echo '  color: #ff6f00; background: rgba(255, 111, 0, 0.15);';
        echo '}';
        echo '.mobile-bottom-nav .nav-item.active i { transform: scale(1.1); }';
        echo '</style>';
        echo $extraHead;
        echo '</head>';
        echo '<body>';
        echo '<header class="chef-topbar">';
        echo '<div class="container-fluid px-3 px-md-4 d-flex justify-content-between align-items-center">';
        echo '<div>';
        echo '<h1 class="chef-title"><i class="fa-solid fa-fire-burner text-warning me-2"></i>' . $safeHeading . '</h1>';
        if ($safeSubheading !== '') {
            echo '<p class="chef-subtitle">' . $safeSubheading . '</p>';
        }
        echo '</div>';
        echo '<div class="d-flex align-items-center gap-2">';
        echo '<span class="badge bg-warning text-dark border"><i class="fa-solid fa-hat-chef me-1"></i>' . chef_escape(function_exists('chef_current_user_name') ? chef_current_user_name() : 'เชฟ') . '</span>';
        echo '<a class="btn btn-sm btn-outline-danger" href="' . chef_escape(chef_url('chef_logout.php')) . '"><i class="fa-solid fa-right-from-bracket me-1"></i>ออกจากระบบ</a>';
        echo '</div>';
        echo '</div>';
        echo '</header>';
        echo '<div class="container-fluid px-3 px-md-4 mb-2 mt-2">';
        echo '<div class="d-flex gap-2 flex-wrap">';
        echo '<a class="btn btn-sm ' . ($isKitchenPage ? 'btn-warning' : 'btn-outline-warning') . '" href="' . chef_escape(chef_url('chef_kitchen.php')) . '" style="' . ($isKitchenPage ? 'background-color:#ff6f00;border-color:#ff6f00;color:white;' : 'color:#ff6f00;border-color:#ff6f00;') . '"><i class="fa-solid fa-fire me-1"></i>คิวครัว</a>';
        echo '</div>';
        echo '</div>';
        echo '<main class="chef-shell">';
    }
}

if (!function_exists('chef_layout_end')) {
    function chef_layout_end($extraScripts = '')
    {
        $currentPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
        $isKitchenPage = $currentPage === 'chef_kitchen.php';
        
        echo '</main>';
        
        // Mobile Bottom Navigation (Chef)
        echo '<nav class="mobile-bottom-nav">';
        echo '<div class="nav-container">';
        echo '<a href="' . chef_escape(chef_url('chef_kitchen.php')) . '" class="nav-item ' . ($isKitchenPage ? 'active' : '') . '">';
        echo '<i class="fa-solid fa-fire"></i>';
        echo '<span>คิวครัว</span>';
        echo '</a>';
        echo '<a href="' . chef_escape(chef_url('chef_logout.php')) . '" class="nav-item">';
        echo '<i class="fa-solid fa-right-from-bracket"></i>';
        echo '<span>ออกจากระบบ</span>';
        echo '</a>';
        echo '</div>';
        echo '</nav>';
        
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
        
        // PWA Service Worker Registration
        echo '<script>';
        echo 'if ("serviceWorker" in navigator) {';
        echo '  window.addEventListener("load", () => {';
        echo '    navigator.serviceWorker.register("' . chef_base_path() . '/sw.js")';
        echo '      .then(reg => console.log("SW registered:", reg.scope))';
        echo '      .catch(err => console.log("SW registration failed:", err));';
        echo '  });';
        echo '}';
        echo '</script>';
        
        echo $extraScripts;
        echo '</body>';
        echo '</html>';
    }
}
