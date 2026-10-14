<?php

/**
 * Wifi-Otantik — SaaS hotspot + PPPoE multi-tenant for PHPNuxBill.
 */

if (!class_exists('Tenant')) {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . 'Tenant.php';
}
if (!class_exists('OtantikCaptive')) {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . 'OtantikCaptive.php';
}
if (!class_exists('OtantikAuth')) {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . 'OtantikAuth.php';
}
if (!class_exists('OtantikReseller')) {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . 'OtantikReseller.php';
}
if (!class_exists('OtantikErp')) {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . 'OtantikErp.php';
}
if (!class_exists('OtantikWhatsApp')) {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . 'OtantikWhatsApp.php';
}
try {
    Tenant::ensure_schema();
} catch (Throwable $e) {
}

register_menu('Tableau de bord', true, 'wifi_otantik_home', 'AFTER_DASHBOARD', 'ion ion-home', '', 'green', ['Admin']);
register_menu('Mes Wifi Zone', true, 'wifi_otantik_routers', 'AFTER_DASHBOARD', 'ion ion-wifi', '', 'blue', ['Admin']);
register_menu('Supervision', true, 'wifi_otantik_monitor', 'AFTER_DASHBOARD', 'ion ion-ios-pulse', '', 'yellow', ['Admin']);
register_menu('Journal MikroTik', true, 'wifi_otantik_mk_logs', 'AFTER_DASHBOARD', 'ion ion-ios-paper', '', 'blue', ['Admin']);
register_menu('Carte', true, 'wifi_otantik_map', 'AFTER_DASHBOARD', 'ion ion-android-pin', '', 'blue', ['Admin']);
register_menu('Messages portail', true, 'wifi_otantik_inbox', 'AFTER_DASHBOARD', 'ion ion-ios-email', '', 'orange', ['Admin']);
register_menu('Mes tarifs', true, 'wifi_otantik_plans', 'AFTER_DASHBOARD', 'ion ion-pricetag', '', 'aqua', ['Admin']);
register_menu('Abonnés', true, 'wifi_otantik_customers', 'AFTER_DASHBOARD', 'ion ion-person-stalker', '', 'blue', ['Admin']);
register_menu('Mes tickets', true, 'wifi_otantik_tickets', 'AFTER_DASHBOARD', 'ion ion-ios-pricetags', '', 'yellow', ['Admin']);
register_menu('Revendeurs', true, 'wifi_otantik_resellers', 'AFTER_DASHBOARD', 'ion ion-briefcase', '', 'orange', ['Admin']);
register_menu('Retraits', true, 'wifi_otantik_wallet', 'AFTER_DASHBOARD', 'ion ion-cash', '', 'green', ['Admin']);
register_menu('ERP', true, 'wifi_otantik_erp', 'AFTER_DASHBOARD', 'ion ion-stats-bars', '', 'purple', ['Admin', 'SuperAdmin']);
register_menu('Profil', true, 'wifi_otantik_profile', 'AFTER_DASHBOARD', 'ion ion-person', '', 'aqua', ['Admin']);
register_menu('Clients', true, 'wifi_otantik_tenants', 'AFTER_DASHBOARD', 'ion ion-person-stalker', '', 'purple', ['SuperAdmin']);
register_menu('Paiements', true, 'wifi_otantik_pg', 'AFTER_DASHBOARD', 'ion ion-card', '', 'green', ['SuperAdmin']);
register_menu('Journal WhatsApp', true, 'wifi_otantik_wa_logs', 'AFTER_DASHBOARD', 'ion ion-chatbubbles', '', 'green', ['SuperAdmin']);
register_menu('Boîte WhatsApp', true, 'wifi_otantik_wa_inbox', 'AFTER_DASHBOARD', 'ion ion-chatbox-working', '', 'green', ['SuperAdmin']);
register_menu('Routeurs SaaS', true, 'wifi_otantik_all_routers', 'AFTER_DASHBOARD', 'ion ion-network', '', 'blue', ['SuperAdmin']);
register_menu('Ventes SaaS', true, 'wifi_otantik_all_sales', 'AFTER_DASHBOARD', 'ion ion-social-usd', '', 'yellow', ['SuperAdmin']);
register_menu('Connexions', true, 'wifi_otantik_login_logs', 'AFTER_DASHBOARD', 'ion ion-log-in', '', 'blue', ['SuperAdmin']);

function wifi_otantik_app_url()
{
    return rtrim(Tenant::url_business(''), '/');
}

function wifi_otantik_msg_page($title, $text, $cta = 'Retour', $url = '', $kind = 'info')
{
    global $ui, $config;
    if ($url === '') {
        $url = class_exists('Tenant') ? Tenant::url_business('/connexion') : '/connexion';
    }
    if ($ui) {
        $ui->assign('msg_title', $title);
        $ui->assign('msg_text', $text);
        $ui->assign('msg_cta', $cta);
        $ui->assign('msg_url', $url);
        $ui->assign('msg_kind', $kind);
        $ui->assign('_c', $config ?? []);
        $ui->assign('app_url', defined('APP_URL') ? APP_URL : rtrim($url, '/'));
        $ui->display('otantik_message.tpl');
        exit;
    }
    header('Location: ' . $url);
    exit;
}

function wifi_otantik_pay_url($slug)
{
    return wifi_otantik_portal_buy_url($slug);
}

function wifi_otantik_portal_buy_url($slug)
{
    return Tenant::url_portal('/wifi/' . rawurlencode((string)$slug) . '/login.html');
}

function wifi_otantik_portal_file($name)
{
    $f = strtolower(basename((string)$name));
    return $f !== '' && in_array($f, OtantikCaptive::files(), true);
}

function wifi_otantik_remember_zone($slug)
{
    $slug = trim((string)$slug);
    if ($slug === '' || wifi_otantik_portal_file($slug) || !preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
        return;
    }
    $_COOKIE['wo_zone'] = $slug;
    if (!headers_sent()) {
        @setcookie('wo_zone', $slug, [
            'expires' => time() + 2592000,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
}

function wifi_otantik_cookie_opts($expTs, $httpOnly = false)
{
    $expTs = (int)$expTs;
    if ($expTs < time() + 3600) {
        $expTs = time() + 7 * 86400;
    }
    if ($expTs > time() + 30 * 86400) {
        $expTs = time() + 30 * 86400;
    }
    return [
        'expires' => $expTs,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
        'httponly' => (bool)$httpOnly,
        'samesite' => 'Lax',
    ];
}

function wifi_otantik_ticket_remember($phone, $user, $pass, $expTs = 0, $slug = '', $plan = '')
{
    $phone = wifi_otantik_phone($phone);
    $user = trim((string)$user);
    $pass = trim((string)$pass);
    if ($user === '') {
        return;
    }
    if ((int)$expTs < time() + 60) {
        $expTs = time() + 7 * 86400;
    }
    $data = [
        'phone' => $phone,
        'user' => $user,
        'pass' => $pass !== '' ? $pass : $user,
        'exp' => (int)$expTs,
        'slug' => preg_replace('/[^A-Za-z0-9_-]/', '', (string)$slug),
        'plan' => (string)$plan,
    ];
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION['wo_ticket'] = $data;
    $raw = rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    if (!headers_sent()) {
        @setcookie('wo_ticket', $raw, wifi_otantik_cookie_opts($expTs, true));
        if ($phone !== '') {
            @setcookie('wo_hs_phone', $phone, wifi_otantik_cookie_opts($expTs, false));
        }
        @setcookie('wo_hs_code', $user, wifi_otantik_cookie_opts($expTs, false));
    }
    $_COOKIE['wo_ticket'] = $raw;
    if ($phone !== '') {
        $_COOKIE['wo_hs_phone'] = $phone;
    }
    $_COOKIE['wo_hs_code'] = $user;
}

function wifi_otantik_ticket_decode_cookie()
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (!empty($_SESSION['wo_ticket']['user'])) {
        return $_SESSION['wo_ticket'];
    }
    $raw = (string)($_COOKIE['wo_ticket'] ?? '');
    if ($raw !== '') {
        $b64 = strtr($raw, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }
        $d = json_decode((string)base64_decode($b64), true);
        if (is_array($d) && !empty($d['user'])) {
            return $d;
        }
    }
    $user = trim((string)($_COOKIE['wo_hs_code'] ?? ''));
    $phone = wifi_otantik_phone($_COOKIE['wo_hs_phone'] ?? '');
    if ($user === '' && $phone === '') {
        return [];
    }
    return [
        'phone' => $phone,
        'user' => $user,
        'pass' => $user,
        'exp' => 0,
        'slug' => wifi_otantik_slug_from_referer(),
        'plan' => '',
    ];
}

function wifi_otantik_code_active($user)
{
    $user = trim((string)$user);
    if ($user === '') {
        return null;
    }
    try {
        $row = ORM::for_table('tbl_user_recharges')
            ->where('username', $user)
            ->where('status', 'on')
            ->order_by_desc('id')
            ->find_one();
    } catch (Throwable $e) {
        return null;
    }
    if (!$row) {
        return null;
    }
    $info = wifi_otantik_expiry_info($row);
    if (!empty($info['expired']) || !empty($info['none'])) {
        return null;
    }
    return $row;
}

function wifi_otantik_slug_from_path($path)
{
    $path = (string)$path;
    if (preg_match('#/(?:wifi|hotspot|z)/([A-Za-z0-9_-]+)(?:/|$)#', $path, $m) && !wifi_otantik_portal_file($m[1])) {
        return $m[1];
    }
    return '';
}

function wifi_otantik_slug_from_referer()
{
    $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
    if ($ref !== '') {
        $query = [];
        parse_str((string)(parse_url($ref, PHP_URL_QUERY) ?? ''), $query);
        if (!empty($query['z']) && !wifi_otantik_portal_file($query['z'])) {
            $z = trim((string)$query['z']);
            if (preg_match('/^[A-Za-z0-9_-]+$/', $z)) {
                return $z;
            }
        }
        $fromPath = wifi_otantik_slug_from_path((string)(parse_url($ref, PHP_URL_PATH) ?? ''));
        if ($fromPath !== '') {
            return $fromPath;
        }
    }
    $c = trim((string)($_COOKIE['wo_zone'] ?? ''));
    if ($c !== '' && !wifi_otantik_portal_file($c) && preg_match('/^[A-Za-z0-9_-]+$/', $c)) {
        return $c;
    }
    return '';
}

function wifi_otantik_find_router_by_slug($slug)
{
    $slug = trim((string)$slug);
    if ($slug === '' || wifi_otantik_portal_file($slug)) {
        return null;
    }
    $router = ORM::for_table('tbl_routers')->where('slug', $slug)->find_one();
    if ($router) {
        return $router;
    }
    $decoded = rawurldecode($slug);
    if ($decoded !== $slug) {
        return ORM::for_table('tbl_routers')->where('slug', $decoded)->find_one();
    }
    return null;
}

function wifi_otantik_portal_bounce($file)
{
    $file = strtolower(basename((string)$file));
    if (!wifi_otantik_portal_file($file)) {
        $file = 'login.html';
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    $f = json_encode($file, JSON_UNESCAPED_SLASHES);
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Wifi-Otantik</title></head><body>';
    echo '<script>(function(){';
    echo 'function go(z){z=String(z||"").replace(/[^A-Za-z0-9_-]/g,"");if(!z||/^(login|ticket|chat|apropos|services|status|logout|error|alogin|md5)$/.test(z))return false;location.replace("/wifi/"+z+"/"+' . $f . '+location.search);return true;}';
    echo 'var c=(document.cookie.match(/(?:^|; )wo_zone=([^;]*)/)||[])[1];if(c&&go(decodeURIComponent(c)))return;';
    echo 'try{if(go(sessionStorage.getItem("wo_zone")))return;}catch(e){}';
    echo 'var m=(document.referrer||"").match(/\\/(?:wifi|hotspot|z)\\/([A-Za-z0-9_-]+)(?:\\/|$|\\?)/);if(m&&go(m[1]))return;';
    echo 'document.body.textContent="Zone introuvable";})();</script>';
    echo '<p>Recherche de la zone…</p></body></html>';
    exit;
}

function wifi_otantik_mac_norm($raw)
{
    $s = strtoupper(trim(urldecode((string)$raw)));
    if ($s === '' || strpos($s, '$') !== false) {
        return '';
    }
    $hex = preg_replace('/[^0-9A-F]/', '', $s);
    if (strlen($hex) !== 12 || $hex === '000000000000' || $hex === 'FFFFFFFFFFFF') {
        return '';
    }
    return implode(':', str_split($hex, 2));
}

function wifi_otantik_mac_remember($mac)
{
    $mac = wifi_otantik_mac_norm($mac);
    if ($mac === '') {
        return '';
    }
    $_COOKIE['wo_hs_mac'] = $mac;
    if (!headers_sent()) {
        @setcookie('wo_hs_mac', $mac, wifi_otantik_cookie_opts(time() + 2 * 86400, false));
    }
    return $mac;
}

function wifi_otantik_mac_from_request()
{
    $mac = wifi_otantik_mac_norm(_post('mac') ?: (_get('mac') ?: ($_COOKIE['wo_hs_mac'] ?? '')));
    if ($mac !== '') {
        wifi_otantik_mac_remember($mac);
    }
    return $mac;
}

function wifi_otantik_origin_base()
{
    $ips = function_exists('wifi_otantik_walled_ips') ? wifi_otantik_walled_ips() : ['83.147.38.107'];
    $ip = trim((string)($ips[0] ?? '83.147.38.107'));
    if ($ip === '') {
        $ip = '83.147.38.107';
    }
    return 'http://' . $ip;
}

function wifi_otantik_pay_public_root()
{
    $host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
    if ($host !== '' && strpos($host, 'otantik.cm') !== false) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        return ($https ? 'https' : 'http') . '://' . $host;
    }
    // Never surface the raw server IP in a link a visitor will click: this
    // function's callers (checkout links) must always resolve to a real
    // domain, even when the file itself was fetched by the router via IP.
    return 'https://wifi.otantik.cm';
}

function wifi_otantik_checkout_url($slug, $planId = 0, $withMac = true)
{
    $path = '/wifi/' . rawurlencode((string)$slug) . '/buy';
    if ((int)$planId > 0) {
        $path .= '/' . (int)$planId;
    }
    $url = wifi_otantik_pay_public_root() . $path;
    if ($withMac) {
        $mac = function_exists('wifi_otantik_mac_from_request') ? wifi_otantik_mac_from_request() : '';
        if ($mac !== '') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'mac=' . rawurlencode($mac);
        }
    }
    return $url;
}

function wifi_otantik_connectik_url($slug = '', $username = '')
{
    $path = '/connectik';
    if ($slug !== '') {
        $path .= '/' . rawurlencode((string)$slug);
        if ($username !== '') {
            $path .= '/r/' . rawurlencode((string)$username);
        }
    } else {
        $path .= '/';
    }
    return Tenant::url_portal($path);
}

function wifi_otantik_pppoe_recharge_url($slug, $username)
{
    return wifi_otantik_connectik_url($slug, $username);
}

function wifi_otantik_pay_back($router, $kind = 'Hotspot', $user = '')
{
    if (strtoupper((string)$kind) === 'PPPOE') {
        return wifi_otantik_connectik_url($router['slug'] ?? '', $user);
    }
    return wifi_otantik_portal_buy_url($router['slug'] ?? '');
}

function wifi_otantik_phone($phone)
{
    $p = preg_replace('/\D+/', '', (string)$phone);
    if (strpos($p, '00') === 0) {
        $p = substr($p, 2);
    }
    $guard = 0;
    while ($p !== '' && $guard++ < 8) {
        $len = strlen($p);
        if ($len === 9 && $p[0] === '6') {
            break;
        }
        if (strpos($p, '237') === 0 && $len > 9) {
            $p = substr($p, 3);
            continue;
        }
        if (strpos($p, '228') === 0 && $len > 9) {
            $p = substr($p, 3);
            continue;
        }
        if (strpos($p, '0') === 0) {
            $p = substr($p, 1);
            continue;
        }
        break;
    }
    return $p;
}

function wifi_otantik_phone_ok($phone)
{
    $p = wifi_otantik_phone($phone);
    return strlen($p) === 9 && $p[0] === '6';
}

function wifi_otantik_detect_service($phone)
{
    $p = wifi_otantik_phone($phone);
    // ART 9-digit plan + Google libphonenumber carrier/en/237.txt
    // Orange: 64, 655-659, 686-689, 69. MTN: 650-654, 67, 680-683.
    if (preg_match('/^(69|65[5-9]|64|68[6-9])/', $p)) {
        return 'ORANGE';
    }
    if (preg_match('/^(67|65[0-4]|68[0-3])/', $p)) {
        return 'MTN';
    }
    return '';
}

function wifi_otantik_pick_service($phone, $posted = '')
{
    $posted = strtoupper(trim((string)$posted));
    if ($posted === 'MTN' || $posted === 'ORANGE') {
        $p = wifi_otantik_phone($phone);
        if (strlen($p) === 9 && $p[0] === '6') {
            return $posted;
        }
    }
    return wifi_otantik_detect_service($phone);
}

function wifi_otantik_pay_ussd($phone, $service = '')
{
    $svc = strtoupper(trim((string)$service));
    if ($svc !== 'MTN' && $svc !== 'ORANGE') {
        $svc = wifi_otantik_detect_service($phone);
    }
    if ($svc === 'ORANGE') {
        return ['op' => 'ORANGE', 'label' => 'Orange Money', 'code' => '#150*50#'];
    }
    return ['op' => 'MTN', 'label' => 'MTN MoMo', 'code' => '*126#'];
}

function wifi_otantik_phone_fmt($phone)
{
    $p = wifi_otantik_phone($phone);
    if (strlen($p) === 9) {
        return '+237 ' . substr($p, 0, 3) . ' ' . substr($p, 3, 2) . ' ' . substr($p, 5, 2) . ' ' . substr($p, 7, 2);
    }
    $raw = trim((string)$phone);
    return $raw !== '' ? $raw : '—';
}

function wifi_otantik_find_login_user($login)
{
    $login = trim((string)$login);
    if ($login === '') {
        return null;
    }
    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        return ORM::for_table('tbl_users')->where('email', strtolower($login))->find_one() ?: null;
    }
    $phone = wifi_otantik_phone($login);
    if (strlen($phone) >= 8) {
        $u = ORM::for_table('tbl_users')->where('phone', $phone)->find_one();
        if ($u) {
            return $u;
        }
        $u = ORM::for_table('tbl_users')->where('phone', '237' . $phone)->find_one();
        if ($u) {
            return $u;
        }
        try {
            $t = ORM::for_table('tbl_otantik_tenants')->where('phone', $phone)->find_one();
            if ($t) {
                $u = ORM::for_table('tbl_users')->find_one((int)$t['user_id']);
                if ($u) {
                    return $u;
                }
            }
        } catch (Throwable $e) {
        }
    }
    return ORM::for_table('tbl_users')->where('username', $login)->find_one() ?: null;
}

function wifi_otantik_make_username($email, $phone = '')
{
    $base = preg_replace('/[^a-z0-9]+/', '', strtolower((string)strstr($email, '@', true)));
    if (strlen($base) < 3) {
        $p = wifi_otantik_phone($phone);
        $base = $p !== '' ? ('wo' . $p) : ('wo' . substr(md5($email . microtime()), 0, 8));
    }
    $user = substr($base, 0, 40);
    $i = 1;
    while (ORM::for_table('tbl_users')->where('username', $user)->find_one()) {
        $user = substr($base, 0, 36) . $i;
        $i++;
    }
    return $user;
}

function wifi_otantik_ref_normalize($code)
{
    $code = trim((string)$code);
    $code = ltrim($code, '@');
    $code = preg_replace('/\s+/', '', $code);
    if (function_exists('mb_substr')) {
        $code = mb_substr($code, 0, 64, 'UTF-8');
    } else {
        $code = substr($code, 0, 64);
    }
    return $code;
}

function wifi_otantik_ref_raw()
{
    foreach (['ref', 'referral', 'parrain'] as $k) {
        $v = wifi_otantik_ref_normalize(_get($k) ?: (_post($k) ?: ''));
        if ($v !== '') {
            return $v;
        }
    }
    if (!empty($_SESSION['wo_ref'])) {
        $v = wifi_otantik_ref_normalize($_SESSION['wo_ref']);
        if ($v !== '') {
            return $v;
        }
    }
    if (!empty($_COOKIE['wo_ref'])) {
        $v = wifi_otantik_ref_normalize($_COOKIE['wo_ref']);
        if ($v !== '') {
            return $v;
        }
    }
    return '';
}

function wifi_otantik_ref_remember($code)
{
    $code = wifi_otantik_ref_normalize($code);
    if ($code === '') {
        return '';
    }
    $_SESSION['wo_ref'] = $code;
    $_COOKIE['wo_ref'] = $code;
    if (!headers_sent()) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        setcookie('wo_ref', $code, [
            'expires' => time() + 30 * 86400,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    return $code;
}

function wifi_otantik_find_referrer($code)
{
    $code = wifi_otantik_ref_normalize($code);
    if ($code === '') {
        return null;
    }
    $u = ORM::for_table('tbl_users')->where('username', $code)->find_one();
    if ($u) {
        return $u;
    }
    try {
        $u = ORM::for_table('tbl_users')
            ->where_raw('LOWER(username) = ?', [strtolower($code)])
            ->find_one();
        if ($u) {
            return $u;
        }
    } catch (Throwable $e) {
    }
    if (preg_match('/^[0-9]+$/', $code)) {
        $u = ORM::for_table('tbl_users')->find_one((int)$code);
        if ($u) {
            return $u;
        }
    }
    return null;
}

function wifi_otantik_ref_resolve($code, $exceptUserId = 0)
{
    $u = wifi_otantik_find_referrer($code);
    if (!$u) {
        return '';
    }
    if ($exceptUserId && (int)$u['id'] === (int)$exceptUserId) {
        return '';
    }
    return (string)$u['username'];
}

function wifi_otantik_ref_capture()
{
    $ok = wifi_otantik_ref_resolve(wifi_otantik_ref_raw());
    if ($ok !== '') {
        wifi_otantik_ref_remember($ok);
    }
    return $ok;
}

function wifi_otantik_ref_matches($referredBy, $admin)
{
    $rb = trim((string)$referredBy);
    if ($rb === '') {
        return false;
    }
    $user = (string)($admin['username'] ?? '');
    $id = (string)(int)($admin['id'] ?? 0);
    return strcasecmp($rb, $user) === 0 || $rb === $id;
}

function wifi_otantik_assign_auth($ui)
{
    global $config;
    $ref = wifi_otantik_ref_capture();
    $signup = Tenant::url_business('/inscription');
    if ($ref !== '') {
        $signup .= '?ref=' . rawurlencode($ref);
    }
    $ui->assign('google_client_id', OtantikAuth::google_client_id());
    $ui->assign('google_url', Tenant::url_business('/?_route=plugin/wifi_otantik_google'));
    $ui->assign('url_login', Tenant::url_business('/connexion'));
    $ui->assign('url_signup', $signup);
    $ui->assign('url_forgot', Tenant::url_business('/mot-de-passe'));
    $ui->assign('url_help', Tenant::url_business('/aide'));
    $ui->assign('url_reseller', Tenant::url_business('/revendeur'));
    $ui->assign('ref', $ref);
    $ui->assign('ref_valid', $ref !== '');
    $ui->assign('_c', $config);
    $ui->assign('app_url', defined('APP_URL') ? APP_URL : Tenant::url_business(''));
    $ui->assign('csrf_token', '');
}

function wifi_otantik_avatar_url($admin)
{
    $id = (int)($admin['id'] ?? 0);
    $rel = 'ui/ui_custom/images/avatars/' . $id . '.jpg';
    $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if ($id > 0 && is_file($root)) {
        return rtrim(wifi_otantik_app_url(), '/') . '/' . $rel . '?t=' . filemtime($root);
    }
    return rtrim(wifi_otantik_app_url(), '/') . '/ui/ui_custom/images/avatar-default.png';
}

function wifi_otantik_save_avatar($admin, $file)
{
    $id = (int)($admin['id'] ?? 0);
    if ($id < 1) {
        return 'Session expirée';
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return 'Téléversement impossible';
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return 'Photo trop lourde (max. 2 Mo)';
    }
    $info = @getimagesize($file['tmp_name']);
    $okTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
    if (defined('IMAGETYPE_WEBP')) {
        $okTypes[] = IMAGETYPE_WEBP;
    }
    if (!$info || !in_array((int)$info[2], $okTypes, true)) {
        return 'Utilisez une image JPG, PNG ou WEBP';
    }
    $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'avatars';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return 'Dossier photo inaccessible';
    }
    $dest = $dir . DIRECTORY_SEPARATOR . $id . '.jpg';
    $src = null;
    if (function_exists('imagecreatetruecolor')) {
        if ((int)$info[2] === IMAGETYPE_JPEG) {
            $src = @imagecreatefromjpeg($file['tmp_name']);
        } elseif ((int)$info[2] === IMAGETYPE_PNG) {
            $src = @imagecreatefrompng($file['tmp_name']);
        } elseif ((int)$info[2] === IMAGETYPE_GIF) {
            $src = @imagecreatefromgif($file['tmp_name']);
        } elseif (defined('IMAGETYPE_WEBP') && (int)$info[2] === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($file['tmp_name']);
        }
    }
    if ($src) {
        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $sx = (int)(($w - $side) / 2);
        $sy = (int)(($h - $side) / 2);
        $size = 400;
        $dst = imagecreatetruecolor($size, $size);
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $size, $size, $side, $side);
        imagejpeg($dst, $dest, 86);
        imagedestroy($dst);
        imagedestroy($src);
    } elseif (!@move_uploaded_file($file['tmp_name'], $dest)) {
        return 'Enregistrement de la photo impossible';
    }
    try {
        $u = ORM::for_table('tbl_users')->find_one($id);
        if ($u) {
            $u->photo = 'avatars/' . $id . '.jpg';
            $u->save();
        }
    } catch (Throwable $e) {
    }
    return '';
}

function wifi_otantik_portal_logo_preview($router)
{
    $path = OtantikCaptive::custom_logo_path($router);
    if ($path === '' || !is_file($path)) {
        return '';
    }
    $id = (int)($router['id'] ?? 0);
    return rtrim(wifi_otantik_app_url(), '/') . '/ui/ui_custom/images/portals/' . $id . '.png?t=' . filemtime($path);
}

function wifi_otantik_clear_portal_logo($router)
{
    $path = OtantikCaptive::custom_logo_path($router);
    if ($path !== '' && is_file($path)) {
        @unlink($path);
    }
    $router->portal_logo = '';
}

function wifi_otantik_save_portal_logo($router, $file)
{
    $id = (int)($router['id'] ?? 0);
    if ($id < 1) {
        return 'Zone introuvable';
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || empty($file['tmp_name'])) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'Téléversement du logo impossible';
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return 'Logo trop lourd (max. 2 Mo)';
    }
    $info = @getimagesize($file['tmp_name']);
    $okTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
    if (defined('IMAGETYPE_WEBP')) {
        $okTypes[] = IMAGETYPE_WEBP;
    }
    if (!$info || !in_array((int)$info[2], $okTypes, true)) {
        return 'Utilisez une image JPG, PNG ou WEBP';
    }
    $dir = OtantikCaptive::logos_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return 'Dossier logo inaccessible';
    }
    $dest = OtantikCaptive::custom_logo_path($router);
    $src = null;
    if (function_exists('imagecreatetruecolor')) {
        if ((int)$info[2] === IMAGETYPE_JPEG) {
            $src = @imagecreatefromjpeg($file['tmp_name']);
        } elseif ((int)$info[2] === IMAGETYPE_PNG) {
            $src = @imagecreatefrompng($file['tmp_name']);
        } elseif (defined('IMAGETYPE_WEBP') && (int)$info[2] === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($file['tmp_name']);
        }
    }
    if ($src) {
        $w = imagesx($src);
        $h = imagesy($src);
        $max = 512;
        $scale = min(1, $max / max($w, $h, 1));
        $nw = max(1, (int)round($w * $scale));
        $nh = max(1, (int)round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
        imagealphablending($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagepng($dst, $dest, 6);
        imagedestroy($dst);
        imagedestroy($src);
    } elseif (!@move_uploaded_file($file['tmp_name'], $dest)) {
        return 'Enregistrement du logo impossible';
    }
    $router->portal_logo = $id . '.png';
    return '';
}

function wifi_otantik_clear_portal_pic($router, $stem)
{
    $id = (int)($router['id'] ?? 0);
    $stem = preg_replace('/[^a-z0-9-]/', '', strtolower((string)$stem));
    if ($id < 1 || $stem === '') {
        return;
    }
    $dir = OtantikCaptive::logos_dir();
    foreach (['.jpg', '.jpeg', '.png', '.webp'] as $ext) {
        $p = $dir . DIRECTORY_SEPARATOR . $id . '-' . $stem . $ext;
        if (is_file($p)) {
            @unlink($p);
        }
    }
}

function wifi_otantik_save_portal_pic($router, $file, $stem)
{
    $id = (int)($router['id'] ?? 0);
    $stem = preg_replace('/[^a-z0-9-]/', '', strtolower((string)$stem));
    if ($id < 1 || $stem === '') {
        return 'Zone introuvable';
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || empty($file['tmp_name'])) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'Téléversement de l’image impossible';
    }
    if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
        return 'Image trop lourde (max. 3 Mo)';
    }
    $info = @getimagesize($file['tmp_name']);
    $okTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
    if (defined('IMAGETYPE_WEBP')) {
        $okTypes[] = IMAGETYPE_WEBP;
    }
    if (!$info || !in_array((int)$info[2], $okTypes, true)) {
        return 'Utilisez une image JPG, PNG ou WEBP';
    }
    $dir = OtantikCaptive::logos_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return 'Dossier image inaccessible';
    }
    wifi_otantik_clear_portal_pic($router, $stem);
    $dest = $dir . DIRECTORY_SEPARATOR . $id . '-' . $stem . '.jpg';
    $src = null;
    if (function_exists('imagecreatetruecolor')) {
        if ((int)$info[2] === IMAGETYPE_JPEG) {
            $src = @imagecreatefromjpeg($file['tmp_name']);
        } elseif ((int)$info[2] === IMAGETYPE_PNG) {
            $src = @imagecreatefrompng($file['tmp_name']);
        } elseif (defined('IMAGETYPE_WEBP') && (int)$info[2] === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($file['tmp_name']);
        }
    }
    if ($src) {
        $w = imagesx($src);
        $h = imagesy($src);
        $max = 1400;
        $scale = min(1, $max / max($w, $h, 1));
        $nw = max(1, (int)round($w * $scale));
        $nh = max(1, (int)round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        $bg = imagecolorallocate($dst, 0, 40, 88);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $bg);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagejpeg($dst, $dest, 82);
        imagedestroy($dst);
        imagedestroy($src);
    } elseif (!@move_uploaded_file($file['tmp_name'], $dest)) {
        return 'Enregistrement de l’image impossible';
    }
    return '';
}

function wifi_otantik_clear_portal_audio($router)
{
    $path = OtantikCaptive::custom_audio_path($router);
    if ($path !== '' && is_file($path)) {
        @unlink($path);
    }
}

function wifi_otantik_save_portal_audio($router, $file)
{
    $id = (int)($router['id'] ?? 0);
    if ($id < 1) {
        return 'Zone introuvable';
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || empty($file['tmp_name'])) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'Téléversement de l’audio impossible';
    }
    if (($file['size'] ?? 0) > 1536 * 1024) {
        return 'Fichier audio trop lourd (max. 1,5 Mo)';
    }
    $mime = '';
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = (string)finfo_file($fi, $file['tmp_name']);
        finfo_close($fi);
    }
    $ext = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($mime, ['audio/mpeg', 'audio/mp3'], true) || $ext !== 'mp3') {
        return 'Utilisez un fichier MP3';
    }
    $dir = OtantikCaptive::audio_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return 'Dossier audio inaccessible';
    }
    $dest = OtantikCaptive::custom_audio_path($router);
    if (!@move_uploaded_file($file['tmp_name'], $dest)) {
        return 'Enregistrement de l’audio impossible';
    }
    return '';
}

function wifi_otantik_kyc_label($status)
{
    $map = [
        'none' => 'Non soumis',
        'pending' => 'En attente',
        'verified' => 'Vérifié',
        'rejected' => 'Refusé',
    ];
    $s = strtolower(trim((string)$status));
    return $map[$s] ?? $map['none'];
}

function wifi_otantik_kyc_chip($status)
{
    $s = strtolower(trim((string)$status));
    if ($s === 'verified') {
        return 'ok';
    }
    if ($s === 'pending') {
        return 'wait';
    }
    return 'no';
}

function wifi_otantik_kyc_root()
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'kyc';
}

function wifi_otantik_kyc_path($userId, $kind)
{
    $kind = preg_replace('/[^a-z]/', '', strtolower((string)$kind));
    return wifi_otantik_kyc_root() . DIRECTORY_SEPARATOR . (int)$userId . DIRECTORY_SEPARATOR . $kind . '.jpg';
}

function wifi_otantik_kyc_url($userId, $kind)
{
    $path = wifi_otantik_kyc_path($userId, $kind);
    if (!is_file($path)) {
        return '';
    }
    return getUrl('plugin/wifi_otantik_kyc_file/' . (int)$userId . '/' . $kind) . '&t=' . filemtime($path);
}

function wifi_otantik_kyc_docs($userId)
{
    $labels = [
        'front' => 'Recto (CNI / passeport)',
        'back' => 'Verso (CNI)',
        'selfie' => 'Selfie tenant la pièce',
    ];
    $out = [];
    foreach ($labels as $kind => $label) {
        $url = wifi_otantik_kyc_url($userId, $kind);
        $out[] = [
            'kind' => $kind,
            'field' => 'kyc_' . $kind,
            'label' => $label,
            'url' => $url,
            'has' => $url !== '',
        ];
    }
    return $out;
}

function wifi_otantik_save_kyc_image($userId, $file, $kind)
{
    $userId = (int)$userId;
    $kind = preg_replace('/[^a-z]/', '', strtolower((string)$kind));
    if ($userId < 1 || !in_array($kind, ['front', 'back', 'selfie'], true)) {
        return 'Document invalide';
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || empty($file['tmp_name'])) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'Téléversement impossible';
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return 'Fichier trop lourd (max. 5 Mo)';
    }
    $info = @getimagesize($file['tmp_name']);
    $okTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
    if (defined('IMAGETYPE_WEBP')) {
        $okTypes[] = IMAGETYPE_WEBP;
    }
    if (!$info || !in_array((int)$info[2], $okTypes, true)) {
        return 'Utilisez une image JPG, PNG ou WEBP';
    }
    $dir = wifi_otantik_kyc_root() . DIRECTORY_SEPARATOR . $userId;
    if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
        return 'Dossier KYC inaccessible';
    }
    $dest = $dir . DIRECTORY_SEPARATOR . $kind . '.jpg';
    $src = null;
    if (function_exists('imagecreatetruecolor')) {
        if ((int)$info[2] === IMAGETYPE_JPEG) {
            $src = @imagecreatefromjpeg($file['tmp_name']);
        } elseif ((int)$info[2] === IMAGETYPE_PNG) {
            $src = @imagecreatefrompng($file['tmp_name']);
        } elseif ((int)$info[2] === IMAGETYPE_GIF) {
            $src = @imagecreatefromgif($file['tmp_name']);
        } elseif (defined('IMAGETYPE_WEBP') && (int)$info[2] === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($file['tmp_name']);
        }
    }
    if ($src) {
        $w = imagesx($src);
        $h = imagesy($src);
        $max = 1600;
        $scale = min(1, $max / max($w, $h, 1));
        $nw = max(1, (int)round($w * $scale));
        $nh = max(1, (int)round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagejpeg($dst, $dest, 84);
        imagedestroy($dst);
        imagedestroy($src);
    } elseif (!@move_uploaded_file($file['tmp_name'], $dest)) {
        return 'Enregistrement du document impossible';
    }
    return '';
}

function wifi_otantik_kyc_file()
{
    global $admin, $routes;
    _admin();
    $userId = (int)($routes[2] ?? 0);
    $kind = preg_replace('/[^a-z]/', '', strtolower((string)($routes[3] ?? '')));
    $isOwner = $admin && (int)$admin['id'] === $userId;
    $isPlatform = $admin && Tenant::is_platform($admin);
    if ($userId < 1 || !in_array($kind, ['front', 'back', 'selfie'], true) || (!$isOwner && !$isPlatform)) {
        http_response_code(403);
        exit;
    }
    $path = wifi_otantik_kyc_path($userId, $kind);
    if (!is_file($path)) {
        http_response_code(404);
        exit;
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: private, max-age=120');
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}

function wifi_otantik_service($service)
{
    $s = strtoupper(trim((string)$service));
    if (in_array($s, ['MOMO', 'MOBILE_MONEY', 'MTN MOMO', 'MTN'], true)) {
        return 'MTN';
    }
    if (in_array($s, ['ORANGE MONEY', 'OM', 'ORANGE'], true)) {
        return 'ORANGE';
    }
    return $s;
}

function wifi_otantik_plan_kind($plan)
{
    $t = strtoupper(trim((string)(is_array($plan) || $plan instanceof ArrayAccess ? ($plan['type'] ?? '') : $plan)));
    return $t === 'PPPOE' ? 'PPPOE' : 'Hotspot';
}

function wifi_otantik_limit_uptime($plan)
{
    $n = max(1, (int)$plan['validity']);
    $u = (string)($plan['validity_unit'] ?? 'Hrs');
    if ($u === 'Days') {
        return $n . 'd';
    }
    if ($u === 'Mins') {
        return $n . 'm';
    }
    return $n . 'h';
}

function wifi_otantik_limit_bytes($plan)
{
    $n = (int)($plan['data_limit'] ?? 0);
    if ($n <= 0) {
        return 0;
    }
    $u = strtoupper((string)($plan['data_unit'] ?? 'MB'));
    if ($u === 'GB') {
        return $n * 1024 * 1024 * 1024;
    }
    return $n * 1024 * 1024;
}

function wifi_otantik_burst_mbps($rate, $mult)
{
    $rate = max(1, (int)$rate);
    $burst = (int)round($rate * (float)$mult);
    if ($burst <= $rate) {
        $burst = $rate + 1;
    }
    return $burst;
}

function wifi_otantik_burst_string($up, $down, $preset)
{
    $preset = strtolower(trim((string)$preset));
    $profiles = [
        'leger' => [1.5, 8],
        'standard' => [2.0, 8],
        'fort' => [3.0, 10],
    ];
    if (!isset($profiles[$preset])) {
        return '';
    }
    $up = max(1, (int)$up);
    $down = max(1, (int)$down);
    $mult = $profiles[$preset][0];
    $time = (int)$profiles[$preset][1];
    $bu = wifi_otantik_burst_mbps($up, $mult);
    $bd = wifi_otantik_burst_mbps($down, $mult);
    return $bu . 'M/' . $bd . 'M ' . $up . 'M/' . $down . 'M ' . $time . '/' . $time;
}

function wifi_otantik_burst_label($burst, $up = 0, $down = 0)
{
    $burst = trim((string)$burst);
    if ($burst === '') {
        return '';
    }
    if (preg_match('/^(\d+)M\/(\d+)M\s+(\d+)M\/(\d+)M\s+(\d+)\//', $burst, $m)) {
        $cirUp = max(1, (int)($m[3] ?: $up));
        $cirDown = max(1, (int)($m[4] ?: $down));
        $ratio = max(((int)$m[1]) / $cirUp, ((int)$m[2]) / $cirDown);
        $time = (int)$m[5];
        if ($time >= 10 && $ratio >= 2.5) {
            return 'Fort';
        }
        if ($ratio >= 1.8) {
            return 'Standard';
        }
        if ($ratio >= 1.2) {
            return 'Léger';
        }
    }
    return 'Oui';
}

function wifi_otantik_burst_preset($label)
{
    $l = mb_strtolower(trim((string)$label), 'UTF-8');
    if ($l === 'fort') {
        return 'fort';
    }
    if ($l === 'standard') {
        return 'standard';
    }
    if ($l === 'léger' || $l === 'leger') {
        return 'leger';
    }
    return 'none';
}

function wifi_otantik_plan_post()
{
    $name = trim((string)_post('name_plan'));
    $price = (int)_post('price');
    $validity = max(1, (int)_post('validity'));
    $unit = _post('validity_unit') ?: 'Hrs';
    if (!in_array($unit, ['Mins', 'Hrs', 'Days'], true)) {
        $unit = 'Hrs';
    }
    $down = max(1, (int)_post('rate_down'));
    $up = max(1, (int)(_post('rate_up') ?: $down));
    $dataLimit = max(0, (int)_post('data_limit'));
    $dataUnit = _post('data_unit') === 'GB' ? 'GB' : 'MB';
    $shared = max(1, (int)(_post('shared_users') ?: 1));
    return [
        'name' => $name,
        'price' => $price,
        'validity' => $validity,
        'unit' => $unit,
        'down' => $down,
        'up' => $up,
        'data_limit' => $dataLimit,
        'data_unit' => $dataUnit,
        'shared' => $shared,
        'burst' => wifi_otantik_burst_string($up, $down, _post('burst_preset')),
    ];
}

function wifi_otantik_save_bw($bw, $in, $routerName)
{
    if (trim((string)($bw->name_bw ?? '')) === '') {
        $bw->name_bw = $in['name'] . '-' . $routerName;
    }
    $bw->rate_down = $in['down'];
    $bw->rate_down_unit = 'Mbps';
    $bw->rate_up = $in['up'];
    $bw->rate_up_unit = 'Mbps';
    $bw->burst = $in['burst'];
    $bw->save();
    return $bw;
}

function wifi_otantik_save_plan_row($p, $bw, $in, $kind, $router)
{
    $p->name_plan = $in['name'];
    $p->id_bw = $bw->id();
    $p->price = (string)$in['price'];
    $p->price_old = '';
    $p->type = $kind;
    $p->typebp = $in['data_limit'] > 0 ? 'Limited' : 'Unlimited';
    $p->limit_type = $in['data_limit'] > 0 ? 'Data_Limit' : 'Time_Limit';
    $p->time_limit = $in['validity'];
    $p->time_unit = $in['unit'] === 'Days' ? 'Hrs' : $in['unit'];
    $p->data_limit = $in['data_limit'];
    $p->data_unit = $in['data_unit'];
    $p->validity = $in['validity'];
    $p->validity_unit = $in['unit'];
    if ($in['unit'] === 'Days') {
        $p->time_limit = $in['validity'] * 24;
        $p->time_unit = 'Hrs';
    }
    $p->shared_users = $kind === 'Hotspot' ? $in['shared'] : 1;
    $p->routers = $router['name'];
    $p->is_radius = 0;
    if ($kind === 'PPPOE') {
        $p->pool = wifi_otantik_ensure_pool_row($router)['name'];
    }
    $p->enabled = 1;
    $p->prepaid = 'yes';
    $p->device = $kind === 'PPPOE' ? 'MikrotikPppoe' : 'MikrotikHotspot';
    $p->save();
    return $p;
}

function wifi_otantik_plans_with_bw($plans)
{
    $ids = [];
    foreach ($plans as $p) {
        $id = (int)$p['id_bw'];
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    $map = [];
    if ($ids) {
        foreach (ORM::for_table('tbl_bandwidth')->where_in('id', array_values($ids))->find_many() as $bw) {
            $map[(int)$bw['id']] = $bw;
        }
    }
    $planIds = [];
    foreach ($plans as $p) {
        $planIds[] = (int)$p['id'];
    }
    $hasHistory = [];
    if ($planIds) {
        foreach (ORM::for_table('tbl_user_recharges')->select('plan_id')->where_in('plan_id', $planIds)->distinct()->find_many() as $h) {
            $hasHistory[(int)$h['plan_id']] = true;
        }
    }
    $out = [];
    foreach ($plans as $p) {
        $row = method_exists($p, 'as_array') ? $p->as_array() : (array)$p;
        $bw = $map[(int)($row['id_bw'] ?? 0)] ?? null;
        $row['bw_down'] = $bw ? (int)$bw['rate_down'] : 0;
        $row['bw_up'] = $bw ? (int)$bw['rate_up'] : 0;
        $row['bw_down_unit'] = $bw ? (string)($bw['rate_down_unit'] ?: 'Mbps') : 'Mbps';
        $row['bw_up_unit'] = $bw ? (string)($bw['rate_up_unit'] ?: 'Mbps') : 'Mbps';
        $row['bw_burst'] = $bw ? trim((string)$bw['burst']) : '';
        $row['burst_label'] = wifi_otantik_burst_label($row['bw_burst'], $row['bw_up'], $row['bw_down']);
        $row['burst_preset'] = wifi_otantik_burst_preset($row['burst_label']);
        $row['has_history'] = isset($hasHistory[(int)$row['id']]);
        $out[] = $row;
    }
    return $out;
}

function wifi_otantik_ros_from_mins($mins)
{
    $mins = max(1, min(10080, (int)$mins));
    if ($mins % 1440 === 0) {
        return ((int)($mins / 1440)) . 'd';
    }
    if ($mins % 60 === 0) {
        return ((int)($mins / 60)) . 'h';
    }
    if ($mins > 60) {
        return intdiv($mins, 60) . 'h' . ($mins % 60) . 'm';
    }
    return $mins . 'm';
}

function wifi_otantik_trial_label($mins)
{
    $mins = max(0, (int)$mins);
    if ($mins >= 1440 && $mins % 1440 === 0) {
        $d = (int)($mins / 1440);
        return $d . ' jour' . ($d > 1 ? 's' : '');
    }
    if ($mins >= 60 && $mins % 60 === 0) {
        $h = (int)($mins / 60);
        return $h . ' heure' . ($h > 1 ? 's' : '');
    }
    return $mins . ' min';
}

function wifi_otantik_trial_rate($router)
{
    $up = max(1, (int)($router['trial_up'] ?? 1));
    $down = max(1, (int)($router['trial_down'] ?? 1));
    return $up . 'M/' . $down . 'M';
}

function wifi_otantik_push_trial($router)
{
    try {
        $client = wifi_otantik_mk_client($router);
        $on = (int)($router['trial_on'] ?? 0) === 1;
        $name = 'wo-trial';
        $rate = wifi_otantik_trial_rate($router);
        $uptime = wifi_otantik_ros_from_mins($router['trial_mins'] ?? 15);
        $poolName = wifi_otantik_hs_pool_spec($router)['name'];
        $pid = wifi_otantik_ros_find_id($client, '/ip/hotspot/user/profile/print', $name);
        // trial-uptime (set below) is only a lifetime quota per MAC on the
        // hotspot profile - the length of each individual trial session is
        // governed by session-timeout on this user profile instead. Without
        // setting it here, a session just runs until the router's own
        // profile default (often 30m), regardless of the configured trial length.
        if ($pid) {
            $set = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/profile/set');
            $set->setArgument('numbers', $pid);
            $set->setArgument('shared-users', '1');
            $set->setArgument('rate-limit', $rate);
            $set->setArgument('address-pool', $poolName);
            $set->setArgument('session-timeout', $uptime);
            $client->sendSync($set);
        } else {
            $add = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/profile/add');
            $add->setArgument('name', $name);
            $add->setArgument('shared-users', '1');
            $add->setArgument('rate-limit', $rate);
            $add->setArgument('address-pool', $poolName);
            $add->setArgument('session-timeout', $uptime);
            $client->sendSync($add);
        }
        // Resolve the profile actually bound to the live hotspot server -
        // never guess/grab an unrelated profile (e.g. the router's factory "default"),
        // since that would misconfigure trial on a profile nobody serves the portal from.
        $hid = null;
        $hsPrint = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/print');
        foreach ($client->sendSync($hsPrint) as $hsRow) {
            $liveProfile = (string)$hsRow->getProperty('profile');
            if ($liveProfile !== '') {
                $hid = wifi_otantik_ros_find_id($client, '/ip/hotspot/profile/print', $liveProfile);
            }
            break;
        }
        if (!$hid) {
            $hid = wifi_otantik_ros_find_id($client, '/ip/hotspot/profile/print', 'hs-otantik');
        }
        if (!$hid) {
            throw new RuntimeException('Profil hotspot introuvable sur le MikroTik');
        }
        $set = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/profile/set');
        $set->setArgument('numbers', $hid);
        if ($on) {
            $set->setArgument('login-by', 'cookie,http-chap,http-pap,trial');
            $set->setArgument('trial-user-profile', $name);
        } else {
            $set->setArgument('login-by', 'cookie,http-chap,http-pap');
            $set->setArgument('trial-user-profile', '');
        }
        $client->sendSync($set);
        $uptimeVal = $on ? $uptime : '0s';
        try {
            $u = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/profile/set');
            $u->setArgument('numbers', $hid);
            $u->setArgument('trial-uptime', $uptimeVal);
            $client->sendSync($u);
        } catch (Throwable $e2) {
            $u2 = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/profile/set');
            $u2->setArgument('numbers', $hid);
            $u2->setArgument('trial-uptime-limit', $uptimeVal);
            $client->sendSync($u2);
        }
        wifi_otantik_mk_log($router, 'other', 'wo-trial', 1, $on ? 'Essai gratuit activé' : 'Essai gratuit désactivé');
    } catch (Throwable $e) {
        wifi_otantik_mk_log($router, 'other', 'wo-trial', 0, $e->getMessage());
        throw $e;
    }
}

function wifi_otantik_rate_limit($plan)
{
    $bw = ORM::for_table('tbl_bandwidth')->find_one($plan['id_bw']);
    if (!$bw) {
        return '1M/1M';
    }
    $up = max(1, (int)$bw['rate_up']);
    $down = max(1, (int)$bw['rate_down']);
    $uu = ($bw['rate_up_unit'] ?? 'Mbps') === 'Kbps' ? 'K' : 'M';
    $du = ($bw['rate_down_unit'] ?? 'Mbps') === 'Kbps' ? 'K' : 'M';
    $rate = $up . $uu . '/' . $down . $du;
    $burst = trim((string)($bw['burst'] ?? ''));
    if ($burst !== '') {
        $rate .= ' ' . $burst;
    }
    return $rate;
}

function wifi_otantik_expire_at($plan)
{
    $n = max(1, (int)$plan['validity']);
    $u = (string)($plan['validity_unit'] ?? 'Hrs');
    $sec = $n * 3600;
    if ($u === 'Mins') {
        $sec = $n * 60;
    } elseif ($u === 'Days') {
        $sec = $n * 86400;
    }
    return time() + $sec;
}

function wifi_otantik_mk_client($router, $timeout = 8, $strict = false)
{
    global $DEVICE_PATH;
    require_once $DEVICE_PATH . DIRECTORY_SEPARATOR . 'MikrotikHotspot.php';
    $ip = (string)$router['ip_address'];
    $user = (string)$router['username'];
    $pass = (string)$router['password'];
    $port = 8728;
    if (strpos($ip, ':') !== false) {
        $parts = explode(':', $ip, 2);
        $ip = $parts[0];
        $port = (int)$parts[1] ?: 8728;
    }
    try {
        return new \PEAR2\Net\RouterOS\Client($ip, $user, $pass, $port, false, max(1, (int)$timeout));
    } catch (Throwable $e) {
        if ($strict) {
            throw $e;
        }
        $hs = new MikrotikHotspot();
        return $hs->getClient($router['ip_address'], $router['username'], $router['password']);
    }
}

function wifi_otantik_mk_log($router, $action, $target, $ok, $message)
{
    try {
        if (!$router) {
            return;
        }
        $row = ORM::for_table('tbl_otantik_mk_logs')->create();
        $row->owner_id = (int)($router['owner_id'] ?? 0);
        $row->router_id = (int)($router['id'] ?? 0);
        $row->router_name = (string)($router['name'] ?? '');
        $row->action = substr(preg_replace('/[^a-z_]/', '', strtolower((string)$action)) ?: 'other', 0, 32);
        $row->target = mb_substr((string)$target, 0, 128);
        $row->message = mb_substr((string)$message, 0, 500);
        $row->ok = $ok ? 1 : 0;
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
    } catch (Throwable $e) {
    }
}

function wifi_otantik_mk_action_label($action)
{
    $map = [
        'push_user' => 'Compte poussé',
        'remove_user' => 'Compte retiré',
        'push_plan' => 'Tarif poussé',
        'push_captive' => 'Portail captif',
        'walled_garden' => 'Walled garden',
        'expire' => 'Expiration',
        'kick' => 'Déconnexion',
        'bypass' => 'Accès MAC',
        'quota' => 'Volume épuisé',
        'other' => 'Écriture',
    ];
    $a = (string)$action;
    return $map[$a] ?? $a;
}

function wifi_otantik_mk_logs_query($admin, $q = '')
{
    $query = ORM::for_table('tbl_otantik_mk_logs');
    if (!Tenant::is_platform($admin)) {
        $query->where('owner_id', Tenant::id($admin));
    }
    wifi_otantik_where_search($query, ['router_name', 'action', 'target', 'message'], $q);
    return $query;
}

function wifi_otantik_mk_logs_rows($admin, $limit = 12, $offset = 0, $q = '')
{
    $out = [];
    try {
        $query = wifi_otantik_mk_logs_query($admin, $q)->order_by_desc('id')->limit(max(1, min(300, (int)$limit)));
        if ((int)$offset > 0) {
            $query->offset((int)$offset);
        }
        foreach ($query->find_array() as $r) {
            $ts = strtotime((string)$r['created_at']);
            $out[] = [
                'id' => (int)$r['id'],
                'router_name' => (string)$r['router_name'],
                'action' => (string)$r['action'],
                'action_label' => wifi_otantik_mk_action_label($r['action']),
                'target' => (string)$r['target'],
                'message' => (string)$r['message'],
                'ok' => (int)$r['ok'] === 1,
                'created_at' => (string)$r['created_at'],
                'ago' => wifi_otantik_ago($ts ?: 0),
            ];
        }
    } catch (Throwable $e) {
    }
    return $out;
}

function wifi_otantik_expiry_board($admin, $soonLimit = 12, $expiredLimit = 8)
{
    $empty = ['soon' => [], 'expired' => []];
    $names = Tenant::router_names($admin);
    if (!$names) {
        return $empty;
    }
    try {
        $rows = ORM::for_table('tbl_user_recharges')
            ->where_in('routers', $names)
            ->order_by_desc('id')
            ->limit(500)
            ->find_array();
    } catch (Throwable $e) {
        return $empty;
    }
    $now = time();
    $soonHorizon = $now + 86400;
    $expiredSince = $now - (2 * 86400);
    $soonAll = [];
    $expired = [];
    $seenSoon = [];
    $seenExp = [];
    foreach ($rows as $row) {
        $info = wifi_otantik_expiry_info($row);
        if (!empty($info['none'])) {
            continue;
        }
        $user = (string)($row['username'] ?? '');
        if ($user === '') {
            continue;
        }
        $endTs = (int)($info['end_ts'] ?? 0);
        $isOff = !empty($info['expired']) || (string)($row['status'] ?? '') === 'off';
        $item = [
            'username' => $user,
            'zone' => (string)($row['routers'] ?? ''),
            'plan' => (string)($row['namebp'] ?? $row['plan_name'] ?? ''),
            'label' => (string)($info['label'] ?? ''),
            'tone' => (string)($info['tone'] ?? 'off'),
            'end_label' => (string)($info['end_label'] ?? ''),
            'end_ts' => $endTs,
            'expired' => $isOff,
        ];
        if ($isOff) {
            if ($endTs >= $expiredSince && empty($seenExp[$user])) {
                $seenExp[$user] = true;
                $expired[] = $item;
            }
            continue;
        }
        if (empty($seenSoon[$user])) {
            $seenSoon[$user] = true;
            $soonAll[] = $item;
        }
    }
    usort($soonAll, function ($a, $b) {
        return ((int)$a['end_ts']) <=> ((int)$b['end_ts']);
    });
    $near = [];
    foreach ($soonAll as $item) {
        if ((int)$item['end_ts'] > 0 && (int)$item['end_ts'] <= $soonHorizon) {
            $near[] = $item;
        }
    }
    $soon = $near ? $near : $soonAll;
    if (count($soon) < 5) {
        $soon = $soonAll;
    }
    usort($expired, function ($a, $b) {
        return ((int)$b['end_ts']) <=> ((int)$a['end_ts']);
    });
    return [
        'soon' => array_slice($soon, 0, max(1, (int)$soonLimit)),
        'expired' => array_slice($expired, 0, max(1, (int)$expiredLimit)),
    ];
}

function wifi_otantik_pppoe_pool_spec($router)
{
    $id = max(1, (int)$router['id']);
    $oct = ($id % 250) + 1;
    return [
        'name' => 'wo-pppoe-' . $id,
        'local' => '10.20.' . $oct . '.1',
        'ranges' => '10.20.' . $oct . '.10-10.20.' . $oct . '.250',
        'net' => '10.20.' . $oct . '.0/24',
    ];
}

function wifi_otantik_ensure_pool_row($router)
{
    $spec = wifi_otantik_pppoe_pool_spec($router);
    $row = ORM::for_table('tbl_pool')->where('pool_name', $spec['name'])->find_one();
    if (!$row) {
        $row = ORM::for_table('tbl_pool')->create();
        $row->pool_name = $spec['name'];
        $row->local_ip = $spec['local'];
        $row->range_ip = $spec['ranges'];
        $row->routers = $router['name'];
        $row->save();
    }
    return $spec;
}

function wifi_otantik_ros_find_id($client, $path, $name)
{
    $print = new \PEAR2\Net\RouterOS\Request($path);
    $print->setQuery(\PEAR2\Net\RouterOS\Query::where('name', $name));
    return $client->sendSync($print)->getProperty('.id');
}

function wifi_otantik_push_plan_mikrotik($router, $plan, $prevName = '')
{
    $name = (string)$plan['name_plan'];
    $kind = wifi_otantik_plan_kind($plan);
    try {
        $client = wifi_otantik_mk_client($router);
        $rate = wifi_otantik_rate_limit($plan);
        $lookup = trim((string)$prevName) !== '' ? (string)$prevName : $name;
        if ($kind === 'PPPOE') {
            $spec = wifi_otantik_ensure_pool_row($router);
            $poolId = wifi_otantik_ros_find_id($client, '/ip/pool/print', $spec['name']);
            if (!$poolId) {
                $add = new \PEAR2\Net\RouterOS\Request('/ip/pool/add');
                $add->setArgument('name', $spec['name']);
                $add->setArgument('ranges', $spec['ranges']);
                $client->sendSync($add);
            }
            $pid = wifi_otantik_ros_find_id($client, '/ppp/profile/print', $lookup);
            if ($pid) {
                $set = new \PEAR2\Net\RouterOS\Request('/ppp/profile/set');
                $set->setArgument('numbers', $pid);
                if ($lookup !== $name) {
                    $set->setArgument('name', $name);
                }
                $set->setArgument('local-address', $spec['local']);
                $set->setArgument('remote-address', $spec['name']);
                $set->setArgument('rate-limit', $rate);
                $set->setArgument('session-timeout', wifi_otantik_limit_uptime($plan));
                $client->sendSync($set);
            } else {
                $add = new \PEAR2\Net\RouterOS\Request('/ppp/profile/add');
                $add->setArgument('name', $name);
                $add->setArgument('local-address', $spec['local']);
                $add->setArgument('remote-address', $spec['name']);
                $add->setArgument('rate-limit', $rate);
                $add->setArgument('session-timeout', wifi_otantik_limit_uptime($plan));
                $client->sendSync($add);
            }
            wifi_otantik_mk_log($router, 'push_plan', $name, 1, 'Profil PPPoE');
            return;
        }
        $shared = max(1, (int)($plan['shared_users'] ?? 1));
        $poolName = wifi_otantik_hs_pool_spec($router)['name'];
        $pid = wifi_otantik_ros_find_id($client, '/ip/hotspot/user/profile/print', $lookup);
        if ($pid) {
            $set = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/profile/set');
            $set->setArgument('numbers', $pid);
            if ($lookup !== $name) {
                $set->setArgument('name', $name);
            }
            $set->setArgument('shared-users', (string)$shared);
            $set->setArgument('rate-limit', $rate);
            $set->setArgument('address-pool', $poolName);
            $set->setArgument('session-timeout', wifi_otantik_limit_uptime($plan));
            $client->sendSync($set);
        } else {
            $add = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/profile/add');
            $add->setArgument('name', $name);
            $add->setArgument('shared-users', (string)$shared);
            $add->setArgument('rate-limit', $rate);
            $add->setArgument('address-pool', $poolName);
            $add->setArgument('session-timeout', wifi_otantik_limit_uptime($plan));
            $client->sendSync($add);
        }
        wifi_otantik_mk_log($router, 'push_plan', $name, 1, 'Profil hotspot');
    } catch (Throwable $e) {
        wifi_otantik_mk_log($router, 'push_plan', $name, 0, $e->getMessage());
        throw $e;
    }
}

function wifi_otantik_ros_duration_seconds($s)
{
    $s = trim((string)$s);
    if ($s === '' || $s[0] === '$') {
        return 0;
    }
    if (ctype_digit($s)) {
        return (int)$s;
    }
    $n = 0;
    if (preg_match('/(\d+)w/', $s, $m)) {
        $n += (int)$m[1] * 604800;
    }
    if (preg_match('/(\d+)d/', $s, $m)) {
        $n += (int)$m[1] * 86400;
    }
    if (preg_match('/(\d+):(\d+):(\d+)/', $s, $m)) {
        $n += (int)$m[1] * 3600 + (int)$m[2] * 60 + (int)$m[3];
    } else {
        if (preg_match('/(\d+)h/', $s, $m)) {
            $n += (int)$m[1] * 3600;
        }
        if (preg_match('/(\d+)m(?!s)/', $s, $m)) {
            $n += (int)$m[1] * 60;
        }
        if (preg_match('/(\d+)s/', $s, $m)) {
            $n += (int)$m[1];
        }
    }
    return $n;
}

function wifi_otantik_push_user_mikrotik($router, $plan, $user, $pass, $comment)
{
    $kind = wifi_otantik_plan_kind($plan);
    try {
        wifi_otantik_push_plan_mikrotik($router, $plan);
        $client = wifi_otantik_mk_client($router);
        if ($kind === 'PPPOE') {
            $id = wifi_otantik_ros_find_id($client, '/ppp/secret/print', $user);
            if ($id) {
                $set = new \PEAR2\Net\RouterOS\Request('/ppp/secret/set');
                $set->setArgument('numbers', $id);
                $set->setArgument('password', $pass);
                $set->setArgument('profile', $plan['name_plan']);
                $set->setArgument('service', 'pppoe');
                $set->setArgument('comment', $comment);
                $client->sendSync($set);
            } else {
                $add = new \PEAR2\Net\RouterOS\Request('/ppp/secret/add');
                $add->setArgument('name', $user);
                $add->setArgument('password', $pass);
                $add->setArgument('profile', $plan['name_plan']);
                $add->setArgument('service', 'pppoe');
                $add->setArgument('comment', $comment);
                $client->sendSync($add);
            }
            try {
                $act = new \PEAR2\Net\RouterOS\Request('/ppp/active/print');
                $act->setQuery(\PEAR2\Net\RouterOS\Query::where('name', $user));
                $aid = $client->sendSync($act)->getProperty('.id');
                if ($aid) {
                    $rm = new \PEAR2\Net\RouterOS\Request('/ppp/active/remove');
                    $rm->setArgument('numbers', $aid);
                    $client->sendSync($rm);
                }
            } catch (Throwable $e) {
            }
            wifi_otantik_mk_log($router, 'push_user', $user, 1, 'PPPoE · ' . (string)($plan['name_plan'] ?? ''));
            return;
        }
        $newUptimeSec = wifi_otantik_ros_duration_seconds(wifi_otantik_limit_uptime($plan));
        $newBytes = wifi_otantik_limit_bytes($plan);
        // Delegates to a standalone, independently-tested implementation
        // (system/otantik_hotspot_write.php) that fixes a confirmed bug in
        // the inline version this replaced: RouterOS rejects an empty
        // limit-bytes-total string when limit-uptime is set in the same
        // /set command, and PEAR2's sendSync() does not throw on that
        // !trap - so a renewal could silently fail to actually update the
        // user while everything downstream believed it had succeeded.
        require_once __DIR__ . '/../otantik_hotspot_write.php';
        $ip = (string)$router['ip_address'];
        $port = 8728;
        if (strpos($ip, ':') !== false) {
            [$ip, $portStr] = explode(':', $ip, 2);
            $port = (int)$portStr ?: 8728;
        }
        $result = otantik_write_hotspot_user(
            $ip,
            $port,
            (string)$router['username'],
            (string)$router['password'],
            (string)$plan['name_plan'],
            $user,
            $pass,
            $comment,
            $newUptimeSec,
            $newBytes
        );
        if (!$result['ok']) {
            throw new \RuntimeException($result['error'] ?? 'unknown error writing hotspot user');
        }
        wifi_otantik_mk_log($router, 'push_user', $user, 1, 'Hotspot · ' . (string)($plan['name_plan'] ?? '') . ' (' . $result['action'] . ')');
    } catch (Throwable $e) {
        wifi_otantik_mk_log($router, 'push_user', $user, 0, $e->getMessage());
        throw $e;
    }
}

function wifi_otantik_ros_ids($client, $path, $field, $value)
{
    $ids = [];
    try {
        $print = new \PEAR2\Net\RouterOS\Request($path);
        $print->setQuery(\PEAR2\Net\RouterOS\Query::where($field, $value));
        $resp = $client->sendSync($print);
        foreach ($resp as $row) {
            $id = $row->getProperty('.id');
            if ($id) {
                $ids[] = $id;
            }
        }
        if (!$ids) {
            $id = $resp->getProperty('.id');
            if ($id) {
                $ids[] = $id;
            }
        }
    } catch (Throwable $e) {
    }
    return array_values(array_unique($ids));
}

function wifi_otantik_ros_remove_ids($client, $path, $ids)
{
    foreach ($ids as $id) {
        try {
            $rm = new \PEAR2\Net\RouterOS\Request($path);
            $rm->setArgument('numbers', $id);
            $client->sendSync($rm);
        } catch (Throwable $e) {
        }
    }
}

function wifi_otantik_remove_user_mikrotik($router, $user, $kind = 'Hotspot', $logAction = 'remove_user')
{
    $user = (string)$user;
    $action = $logAction ?: 'remove_user';
    if ($user === '') {
        return;
    }
    try {
        $client = wifi_otantik_mk_client($router);
        if (strtoupper((string)$kind) === 'PPPOE') {
            wifi_otantik_ros_remove_ids($client, '/ppp/active/remove', wifi_otantik_ros_ids($client, '/ppp/active/print', 'name', $user));
            wifi_otantik_ros_remove_ids($client, '/ppp/secret/remove', wifi_otantik_ros_ids($client, '/ppp/secret/print', 'name', $user));
        } else {
            wifi_otantik_ros_remove_ids($client, '/ip/hotspot/active/remove', wifi_otantik_ros_ids($client, '/ip/hotspot/active/print', 'user', $user));
            wifi_otantik_ros_remove_ids($client, '/ip/hotspot/user/remove', wifi_otantik_ros_ids($client, '/ip/hotspot/user/print', 'name', $user));
        }
        if (strtoupper((string)$kind) !== 'PPPOE') {
            wifi_otantik_mac_bypass_remove($router, $user);
        }
        wifi_otantik_mk_log($router, $action, $user, 1, strtoupper((string)$kind) === 'PPPOE' ? 'PPPoE retiré' : 'Hotspot retiré');
    } catch (Throwable $e) {
        wifi_otantik_mk_log($router, $action, $user, 0, $e->getMessage());
        throw $e;
    }
}

function wifi_otantik_mac_bypass_comment($user)
{
    $user = preg_replace('/[^A-Za-z0-9._-]/', '', (string)$user);
    return 'wo-byp:' . substr($user, 0, 40);
}

function wifi_otantik_mac_bypass_mark($mac)
{
    return 'wo' . strtolower(str_replace(':', '', wifi_otantik_mac_norm($mac)));
}

function wifi_otantik_mac_bypass_qname($mac)
{
    return 'wo-byp-' . strtolower(str_replace(':', '', wifi_otantik_mac_norm($mac)));
}

function wifi_otantik_mac_bypass_rate($plan)
{
    $raw = wifi_otantik_rate_limit($plan);
    $parts = preg_split('/\s+/', trim((string)$raw));
    $limit = (string)($parts[0] ?? '1M/1M');
    if (!preg_match('/^\d+[KkMm]\/\d+[KkMm]$/', $limit)) {
        $limit = '1M/1M';
    }
    return $limit;
}

function wifi_otantik_mac_bypass_comment_user($comment)
{
    $comment = trim((string)$comment);
    if (strpos($comment, 'wo-byp:') !== 0) {
        return '';
    }
    return substr($comment, 7);
}

function wifi_otantik_mac_from_trx($trx)
{
    if (!$trx) {
        return '';
    }
    try {
        $decoded = json_decode((string)($trx['pg_request'] ?? ''), true);
        if (is_array($decoded)) {
            $mac = wifi_otantik_mac_norm($decoded['mac'] ?? '');
            if ($mac !== '') {
                return $mac;
            }
        }
    } catch (Throwable $e) {
    }
    try {
        $row = ORM::for_table('tbl_otantik_mac_bypass')->where('trx_id', (int)$trx['id'])->order_by_desc('id')->find_one();
        if ($row) {
            return wifi_otantik_mac_norm($row['mac'] ?? '');
        }
    } catch (Throwable $e) {
    }
    return '';
}

function wifi_otantik_mac_bypass_save_pending($router, $user, $mac, $trxId = 0)
{
    $mac = wifi_otantik_mac_norm($mac);
    $user = (string)$user;
    if ($mac === '' || $user === '' || !$router) {
        return;
    }
    try {
        $row = ORM::for_table('tbl_otantik_mac_bypass')
            ->where('router_id', (int)$router['id'])
            ->where('mac', $mac)
            ->find_one();
        if (!$row) {
            $row = ORM::for_table('tbl_otantik_mac_bypass')->create();
            $row->router_id = (int)$router['id'];
            $row->created_at = date('Y-m-d H:i:s');
        }
        $row->username = $user;
        $row->mac = $mac;
        $row->trx_id = (int)$trxId;
        $row->status = 'pending';
        $row->save();
    } catch (Throwable $e) {
    }
}

function wifi_otantik_mac_bypass_remove_mk($client, $user, $mac = '')
{
    $comment = wifi_otantik_mac_bypass_comment($user);
    $ids = wifi_otantik_ros_ids($client, '/ip/hotspot/ip-binding/print', 'comment', $comment);
    $mac = wifi_otantik_mac_norm($mac);
    if ($mac !== '') {
        $ids = array_merge($ids, wifi_otantik_ros_ids($client, '/ip/hotspot/ip-binding/print', 'mac-address', $mac));
        wifi_otantik_ros_remove_ids($client, '/queue/simple/remove', wifi_otantik_ros_ids($client, '/queue/simple/print', 'name', wifi_otantik_mac_bypass_qname($mac)));
        wifi_otantik_ros_remove_ids($client, '/ip/hotspot/host/remove', wifi_otantik_ros_ids($client, '/ip/hotspot/host/print', 'mac-address', $mac));
    }
    wifi_otantik_ros_remove_ids($client, '/ip/hotspot/ip-binding/remove', array_values(array_unique($ids)));
    if ($comment !== 'wo-byp:') {
        wifi_otantik_ros_remove_ids($client, '/queue/simple/remove', wifi_otantik_ros_ids($client, '/queue/simple/print', 'comment', $comment));
        wifi_otantik_ros_remove_ids($client, '/ip/firewall/mangle/remove', wifi_otantik_ros_ids($client, '/ip/firewall/mangle/print', 'comment', $comment));
    }
}

function wifi_otantik_mac_bypass_remove($router, $user, $mac = '')
{
    $user = (string)$user;
    if (!$router || $user === '') {
        return;
    }
    $mac = wifi_otantik_mac_norm($mac);
    try {
        $q = ORM::for_table('tbl_otantik_mac_bypass')->where('router_id', (int)$router['id'])->where('username', $user);
        foreach ($q->find_many() as $row) {
            if ($mac === '') {
                $mac = wifi_otantik_mac_norm($row['mac'] ?? '');
            }
            $row->status = 'off';
            $row->save();
        }
    } catch (Throwable $e) {
    }
    $client = wifi_otantik_mk_client($router);
    wifi_otantik_mac_bypass_remove_mk($client, $user, $mac);
}

function wifi_otantik_mac_bypass_ip($client, $mac)
{
    try {
        $print = new \PEAR2\Net\RouterOS\Request('/ip/dhcp-server/lease/print');
        $print->setQuery(\PEAR2\Net\RouterOS\Query::where('mac-address', $mac));
        foreach ($client->sendSync($print) as $row) {
            $ip = trim((string)$row->getProperty('address'));
            if ($ip !== '') {
                return $ip;
            }
        }
    } catch (Throwable $e) {
    }
    try {
        $print = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/host/print');
        $print->setQuery(\PEAR2\Net\RouterOS\Query::where('mac-address', $mac));
        foreach ($client->sendSync($print) as $row) {
            $ip = trim((string)$row->getProperty('address'));
            if ($ip === '') {
                $ip = trim((string)$row->getProperty('to-address'));
            }
            if ($ip !== '') {
                return $ip;
            }
        }
    } catch (Throwable $e) {
    }
    return '';
}

/**
 * Log the phone into the hotspot (authorized session) so Internet works AND
 * MikroTik status/login pages keep working. MAC "bypassed" bindings skip the
 * hotspot servlet entirely — that broke /status and otantik.net portal access.
 */
function wifi_otantik_hs_force_login($router, $user, $pass, $mac = '', $trx = null)
{
    $user = (string)$user;
    $pass = (string)$pass;
    if (!$router || $user === '' || $pass === '') {
        return false;
    }
    $mac = wifi_otantik_mac_norm($mac);
    if ($mac === '') {
        $mac = wifi_otantik_mac_from_trx($trx);
    }
    if ($mac === '') {
        $mac = wifi_otantik_mac_from_request();
    }
    if ($mac === '') {
        wifi_otantik_mk_log($router, 'login', $user, 0, 'MAC introuvable pour auto-login hotspot');
        return false;
    }
    try {
        $client = wifi_otantik_mk_client($router);
        // Drop any prior bypass for this MAC — otherwise servlet/status stay dark.
        try {
            wifi_otantik_mac_bypass_remove_mk($client, $user, $mac);
        } catch (Throwable $e) {
        }
        try {
            ORM::for_table('tbl_otantik_mac_bypass')
                ->where('router_id', (int)$router['id'])
                ->where('mac', $mac)
                ->delete_many();
        } catch (Throwable $e) {
        }
        $ip = wifi_otantik_mac_bypass_ip($client, $mac);
        if ($ip === '') {
            wifi_otantik_mk_log($router, 'login', $user, 0, 'IP introuvable pour MAC ' . $mac);
            return false;
        }
        $login = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/active/login');
        $login->setArgument('user', $user);
        $login->setArgument('password', $pass);
        $login->setArgument('ip', $ip);
        $login->setArgument('mac-address', $mac);
        $client->sendSync($login);
        wifi_otantik_mk_log($router, 'login', $user, 1, 'Auto-login hotspot ' . $ip);
        return true;
    } catch (Throwable $e) {
        wifi_otantik_mk_log($router, 'login', $user, 0, $e->getMessage());
        return false;
    }
}

function wifi_otantik_mac_bypass_apply($router, $plan, $user, $mac)
{
    $mac = wifi_otantik_mac_norm($mac);
    $user = (string)$user;
    if (!$router || $user === '' || $mac === '') {
        return false;
    }
    if (wifi_otantik_plan_kind($plan) === 'PPPOE') {
        return false;
    }
    $rate = wifi_otantik_mac_bypass_rate($plan);
    $comment = wifi_otantik_mac_bypass_comment($user);
    $mark = wifi_otantik_mac_bypass_mark($mac);
    $qname = wifi_otantik_mac_bypass_qname($mac);
    $client = wifi_otantik_mk_client($router);
    // A MikroTik simple queue's packet-marks parameter is not reliably
    // enforced without a target address to attach it to - confirmed live:
    // the mangle rules below were marking real traffic (non-zero counters),
    // but bandwidth was never actually capped until a target was added.
    // Scope it to the client's current DHCP-leased IP, same mechanism
    // MikroTik hotspot itself uses for its own per-user queues; fall back
    // to the whole hotspot subnet (still narrowed by packet-marks) if no
    // lease is found yet.
    $ip = wifi_otantik_mac_bypass_ip($client, $mac);
    $target = $ip !== '' ? $ip . '/32' : wifi_otantik_hs_pool_spec($router)['net'];
    wifi_otantik_mac_bypass_remove_mk($client, $user, $mac);
    $add = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/ip-binding/add');
    $add->setArgument('mac-address', $mac);
    $add->setArgument('type', 'bypassed');
    $add->setArgument('comment', $comment);
    $client->sendSync($add);
    foreach (['src-mac-address', 'dst-mac-address'] as $field) {
        $conn = new \PEAR2\Net\RouterOS\Request('/ip/firewall/mangle/add');
        $conn->setArgument('chain', 'forward');
        $conn->setArgument($field, $mac);
        $conn->setArgument('action', 'mark-connection');
        $conn->setArgument('new-connection-mark', $mark);
        $conn->setArgument('passthrough', 'yes');
        $conn->setArgument('comment', $comment);
        $client->sendSync($conn);
    }
    $pkt = new \PEAR2\Net\RouterOS\Request('/ip/firewall/mangle/add');
    $pkt->setArgument('chain', 'forward');
    $pkt->setArgument('connection-mark', $mark);
    $pkt->setArgument('action', 'mark-packet');
    $pkt->setArgument('new-packet-mark', $mark);
    $pkt->setArgument('passthrough', 'yes');
    $pkt->setArgument('comment', $comment);
    $client->sendSync($pkt);
    $qadd = new \PEAR2\Net\RouterOS\Request('/queue/simple/add');
    $qadd->setArgument('name', $qname);
    $qadd->setArgument('target', $target);
    $qadd->setArgument('packet-marks', $mark);
    $qadd->setArgument('max-limit', $rate);
    $qadd->setArgument('comment', $comment);
    $client->sendSync($qadd);
    try {
        wifi_otantik_ros_remove_ids($client, '/ip/hotspot/host/remove', wifi_otantik_ros_ids($client, '/ip/hotspot/host/print', 'mac-address', $mac));
        wifi_otantik_ros_remove_ids($client, '/ip/hotspot/active/remove', wifi_otantik_ros_ids($client, '/ip/hotspot/active/print', 'mac-address', $mac));
    } catch (Throwable $e) {
    }
    try {
        $row = ORM::for_table('tbl_otantik_mac_bypass')
            ->where('router_id', (int)$router['id'])
            ->where('mac', $mac)
            ->find_one();
        if (!$row) {
            $row = ORM::for_table('tbl_otantik_mac_bypass')->create();
            $row->router_id = (int)$router['id'];
            $row->created_at = date('Y-m-d H:i:s');
        }
        $row->username = $user;
        $row->mac = $mac;
        $row->rate = $rate;
        try {
            $row->bytes_limit = (int)wifi_otantik_limit_bytes($plan);
        } catch (Throwable $e) {
        }
        $row->status = 'on';
        $row->save();
    } catch (Throwable $e) {
    }
    // Optional per-zone setting: once this device is bound, take the portal
    // login out of play for this code too - otherwise someone else could
    // still type it into the portal on a different device, since a bypassed
    // client never shows up as an active hotspot session for shared-users
    // to block. Off by default; disabling this account never breaks the
    // portal feature itself, only this one already-bypassed code.
    if ((int)($router['mac_bypass_strict'] ?? 0) === 1) {
        try {
            $uid = wifi_otantik_ros_find_id($client, '/ip/hotspot/user/print', $user);
            if ($uid) {
                $dis = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/set');
                $dis->setArgument('numbers', $uid);
                $dis->setArgument('disabled', 'yes');
                $client->sendSync($dis);
            }
        } catch (Throwable $e) {
        }
    }
    wifi_otantik_mk_log($router, 'bypass', $user, 1, $mac . ' · ' . $rate);
    return true;
}

function wifi_otantik_mac_bypass_try($router, $plan, $user, $mac = '', $trx = null)
{
    if (!$router || wifi_otantik_plan_kind($plan) === 'PPPOE') {
        return;
    }
    $mac = wifi_otantik_mac_norm($mac);
    if ($mac === '') {
        $mac = wifi_otantik_mac_from_trx($trx);
    }
    if ($mac === '') {
        $mac = wifi_otantik_mac_from_request();
    }
    if ($mac === '') {
        try {
            $row = ORM::for_table('tbl_otantik_mac_bypass')
                ->where('router_id', (int)$router['id'])
                ->where('username', (string)$user)
                ->order_by_desc('id')
                ->find_one();
            if ($row) {
                $mac = wifi_otantik_mac_norm($row['mac'] ?? '');
            }
        } catch (Throwable $e) {
        }
    }
    if ($mac === '') {
        // Silent no-op used to be indistinguishable from "never attempted" -
        // log it so a purchase that didn't carry a resolvable MAC (e.g. the
        // buy link wasn't opened through the router's own captive portal
        // redirect) shows up in Journal MikroTik instead of looking like
        // nothing happened at all.
        wifi_otantik_mk_log($router, 'bypass', $user, 0, 'MAC introuvable - achat pas passé par le portail du routeur ?');
        return;
    }
    try {
        wifi_otantik_mac_bypass_apply($router, $plan, $user, $mac);
    } catch (Throwable $e) {
        wifi_otantik_mk_log($router, 'bypass', $user, 0, $e->getMessage());
    }
}

function wifi_otantik_queue_bytes_total($raw)
{
    $raw = trim((string)$raw);
    if ($raw === '') {
        return 0;
    }
    $parts = preg_split('/[\/,]/', $raw);
    $n = 0;
    foreach ($parts as $p) {
        $n += (int)preg_replace('/[^0-9]/', '', (string)$p);
    }
    return $n;
}

function wifi_otantik_mac_bypass_quota_cut($router, $user, $mac)
{
    $user = (string)$user;
    if (!$router || $user === '') {
        return;
    }
    wifi_otantik_mac_bypass_remove($router, $user, $mac);
    try {
        $client = wifi_otantik_mk_client($router);
        $id = wifi_otantik_ros_find_id($client, '/ip/hotspot/user/print', $user);
        if ($id) {
            $set = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/set');
            $set->setArgument('numbers', $id);
            $set->setArgument('disabled', 'yes');
            $client->sendSync($set);
        }
        wifi_otantik_ros_remove_ids($client, '/ip/hotspot/active/remove', wifi_otantik_ros_ids($client, '/ip/hotspot/active/print', 'user', $user));
        $mac = wifi_otantik_mac_norm($mac);
        if ($mac !== '') {
            wifi_otantik_ros_remove_ids($client, '/ip/hotspot/host/remove', wifi_otantik_ros_ids($client, '/ip/hotspot/host/print', 'mac-address', $mac));
        }
    } catch (Throwable $e) {
        wifi_otantik_mk_log($router, 'quota', $user, 0, $e->getMessage());
        throw $e;
    }
    try {
        foreach (ORM::for_table('tbl_otantik_mac_bypass')->where('router_id', (int)$router['id'])->where('username', $user)->find_many() as $row) {
            $row->status = 'quota';
            $row->save();
        }
    } catch (Throwable $e) {
    }
    wifi_otantik_mk_log($router, 'quota', $user, 1, 'Volume épuisé, temps encore valable');
}

function wifi_otantik_cron_quota_mac_bypass()
{
    $n = 0;
    $routers = [];
    try {
        $rows = ORM::for_table('tbl_otantik_mac_bypass')->where('status', 'on')->find_many();
    } catch (Throwable $e) {
        wifi_otantik_cron_log('quota list: ' . $e->getMessage());
        return 0;
    }
    foreach ($rows as $row) {
        $limit = (int)($row['bytes_limit'] ?? 0);
        $rid = (int)$row['router_id'];
        if ($rid < 1) {
            continue;
        }
        if (empty($routers[$rid])) {
            $routers[$rid] = ORM::for_table('tbl_routers')->find_one($rid);
        }
        $router = $routers[$rid];
        if (!$router) {
            continue;
        }
        $mac = wifi_otantik_mac_norm($row['mac'] ?? '');
        $user = (string)$row['username'];
        $qname = $mac !== '' ? wifi_otantik_mac_bypass_qname($mac) : '';
        try {
            $client = wifi_otantik_mk_client($router, 4);
            $used = 0;
            $qid = '';
            $qtarget = '';
            foreach (wifi_otantik_ros_rows($client, '/queue/simple/print', 400, 'name,bytes,comment,target,.id') as $q) {
                $match = ($qname !== '' && (string)($q['name'] ?? '') === $qname)
                    || wifi_otantik_mac_bypass_comment_user($q['comment'] ?? '') === $user;
                if (!$match) {
                    continue;
                }
                $used = wifi_otantik_queue_bytes_total($q['bytes'] ?? '');
                $qid = (string)($q['.id'] ?? '');
                $qtarget = (string)($q['target'] ?? '');
                break;
            }
            // A DHCP renewal can hand the same MAC a different IP mid-plan,
            // which would silently strand the queue on a stale target and
            // let the client run unthrottled - keep it pinned to whatever
            // IP the MAC currently holds.
            if ($qid !== '' && $mac !== '') {
                $ip = wifi_otantik_mac_bypass_ip($client, $mac);
                if ($ip !== '' && strpos($qtarget, $ip . '/32') === false) {
                    try {
                        $retarget = new \PEAR2\Net\RouterOS\Request('/queue/simple/set');
                        $retarget->setArgument('numbers', $qid);
                        $retarget->setArgument('target', $ip . '/32');
                        $client->sendSync($retarget);
                    } catch (Throwable $e3) {
                    }
                }
            }
            if ($limit < 1 || $used < $limit) {
                continue;
            }
            wifi_otantik_mac_bypass_quota_cut($router, $user, $mac);
            $n++;
            wifi_otantik_cron_log('quota ' . $user . ' used=' . $used . ' limit=' . $limit . ' @' . $router['name']);
        } catch (Throwable $e) {
            wifi_otantik_cron_log('quota ' . $user . ': ' . $e->getMessage());
        }
    }
    return $n;
}

function wifi_otantik_recharge_is_expired($row)
{
    $exp = trim((string)($row['expiration'] ?? '') . ' ' . (string)($row['time'] ?? '00:00:00'));
    $ts = strtotime($exp);
    return $ts !== false && $ts <= time();
}

function wifi_otantik_remain_label($seconds)
{
    if ($seconds <= 0) {
        return 'Expiré';
    }
    $d = (int)floor($seconds / 86400);
    $h = (int)floor(($seconds % 86400) / 3600);
    $m = (int)floor(($seconds % 3600) / 60);
    if ($d > 0) {
        return $h > 0 ? ($d . 'j ' . $h . 'h') : ($d . 'j');
    }
    if ($h > 0) {
        return $m > 0 ? ($h . 'h ' . $m . 'm') : ($h . 'h');
    }
    if ($m < 1) {
        return '1m';
    }
    return $m . 'm';
}

function wifi_otantik_expiry_info($row = null)
{
    $circ = round(2 * M_PI * 18, 2);
    $ticket = '';
    if ($row) {
        $ticket = (string)($row['namebp'] ?? $row['plan_name'] ?? $row['name_plan'] ?? '');
    }
    $base = [
        'pct' => 0,
        'offset' => $circ,
        'label' => 'Expiré',
        'tone' => 'off',
        'ticket' => $ticket,
        'expired' => true,
        'none' => true,
        'end_ts' => 0,
        'end_label' => '',
        'left' => 0,
    ];
    if (!$row) {
        $base['label'] = '—';
        $base['ticket'] = '';
        return $base;
    }
    $endRaw = trim((string)($row['expiration'] ?? ''));
    $endTime = trim((string)($row['time'] ?? '00:00:00')) ?: '00:00:00';
    $startRaw = trim((string)($row['recharged_on'] ?? ''));
    $startTime = trim((string)($row['recharged_time'] ?? '00:00:00')) ?: '00:00:00';
    $endTs = $endRaw !== '' ? strtotime($endRaw . ' ' . $endTime) : false;
    $startTs = $startRaw !== '' ? strtotime($startRaw . ' ' . $startTime) : false;
    if ($endTs === false || $startTs === false || $endTs <= $startTs) {
        return $base;
    }
    $now = time();
    $total = $endTs - $startTs;
    $left = $endTs - $now;
    $base['none'] = false;
    $base['end_ts'] = $endTs;
    $base['end_label'] = date('d/m H:i', $endTs);
    $base['left'] = $left;
    if ($left <= 0 || (string)($row['status'] ?? '') === 'off') {
        $base['label'] = 'Expiré';
        return $base;
    }
    $pct = (int)round(100 * $left / $total);
    if ($pct < 0) {
        $pct = 0;
    }
    if ($pct > 100) {
        $pct = 100;
    }
    $tone = 'ok';
    if ($pct < 20) {
        $tone = 'warn';
    } elseif ($pct < 50) {
        $tone = 'mid';
    }
    return [
        'pct' => $pct,
        'offset' => round($circ * (1 - $pct / 100), 2),
        'label' => wifi_otantik_remain_label($left),
        'tone' => $tone,
        'ticket' => $ticket,
        'expired' => false,
        'none' => false,
        'end_ts' => $endTs,
        'end_label' => date('d/m H:i', $endTs),
        'left' => $left,
    ];
}

function wifi_otantik_cron_log($msg)
{
    $line = date('c') . ' ' . $msg . "\n";
    echo $line;
    @file_put_contents('/tmp/otantik-cron.log', $line, FILE_APPEND);
}

function wifi_otantik_cron_expire_recharges()
{
    $n = 0;
    try {
        $rows = ORM::for_table('tbl_user_recharges')->where('status', 'on')->find_many();
    } catch (Throwable $e) {
        wifi_otantik_cron_log('recharges: ' . $e->getMessage());
        return 0;
    }
    foreach ($rows as $row) {
        if (!wifi_otantik_recharge_is_expired($row)) {
            continue;
        }
        $user = (string)$row['username'];
        $kind = wifi_otantik_plan_kind($row);
        $router = ORM::for_table('tbl_routers')->where('name', $row['routers'])->find_one();
        if ($router && $user !== '') {
            try {
                wifi_otantik_remove_user_mikrotik($router, $user, $kind, 'expire');
            } catch (Throwable $e) {
                wifi_otantik_cron_log('expire ' . $user . ' @' . $row['routers'] . ': ' . $e->getMessage());
                continue;
            }
        }
        $row->status = 'off';
        $row->save();
        $n++;
        wifi_otantik_cron_log('expired ' . $user . ' ' . $kind . ' @' . $row['routers']);
    }
    return $n;
}

function wifi_otantik_cron_kick_stale_sessions()
{
    $valid = [];
    try {
        foreach (ORM::for_table('tbl_user_recharges')->select('username')->select('routers')->select('expiration')->select('time')->where('status', 'on')->find_array() as $row) {
            if (wifi_otantik_recharge_is_expired($row)) {
                continue;
            }
            $valid[(string)$row['routers']][(string)$row['username']] = true;
        }
    } catch (Throwable $e) {
        wifi_otantik_cron_log('valid list: ' . $e->getMessage());
        return 0;
    }
    $ours = [];
    try {
        foreach (ORM::for_table('tbl_user_recharges')->select('username')->select('routers')->select('type')->find_array() as $row) {
            $ours[(string)$row['routers']][(string)$row['username']] = strtoupper((string)$row['type']) === 'PPPOE' ? 'PPPOE' : 'Hotspot';
        }
    } catch (Throwable $e) {
        return 0;
    }
    $kicked = 0;
    $routers = ORM::for_table('tbl_routers')->where('enabled', '1')->find_many();
    foreach ($routers as $router) {
        $rname = (string)$router['name'];
        try {
            $client = wifi_otantik_mk_client($router, 4);
        } catch (Throwable $e) {
            wifi_otantik_cron_log('router ' . $rname . ' offline: ' . $e->getMessage());
            continue;
        }
        foreach ([['print' => '/ip/hotspot/active/print', 'remove' => '/ip/hotspot/active/remove', 'field' => 'user', 'kind' => 'Hotspot'], ['print' => '/ppp/active/print', 'remove' => '/ppp/active/remove', 'field' => 'name', 'kind' => 'PPPOE']] as $spec) {
            try {
                $req = new \PEAR2\Net\RouterOS\Request($spec['print']);
                $req->setArgument('.proplist', '.id,' . $spec['field']);
                $resp = $client->sendSync($req);
            } catch (Throwable $e) {
                continue;
            }
            $seen = [];
            foreach ($resp as $item) {
                $uname = (string)$item->getProperty($spec['field']);
                $id = $item->getProperty('.id');
                if ($uname === '' || !$id) {
                    continue;
                }
                if (!empty($valid[$rname][$uname])) {
                    continue;
                }
                if (empty($ours[$rname][$uname])) {
                    continue;
                }
                try {
                    $rm = new \PEAR2\Net\RouterOS\Request($spec['remove']);
                    $rm->setArgument('numbers', $id);
                    $client->sendSync($rm);
                    $kicked++;
                } catch (Throwable $e) {
                }
                if (empty($seen[$uname])) {
                    $seen[$uname] = true;
                    try {
                        wifi_otantik_remove_user_mikrotik($router, $uname, $ours[$rname][$uname] ?? $spec['kind'], 'kick');
                    } catch (Throwable $e) {
                    }
                    wifi_otantik_cron_log('kicked stale ' . $uname . ' ' . $spec['kind'] . ' @' . $rname);
                }
            }
        }
    }
    return $kicked;
}

function wifi_otantik_cron_sweep_mac_bypass()
{
    $valid = [];
    try {
        foreach (ORM::for_table('tbl_user_recharges')->select('username')->select('routers')->select('expiration')->select('time')->where('status', 'on')->find_array() as $row) {
            if (wifi_otantik_recharge_is_expired($row)) {
                continue;
            }
            if (strtoupper((string)($row['type'] ?? '')) === 'PPPOE') {
                continue;
            }
            $valid[(string)$row['routers']][(string)$row['username']] = true;
        }
    } catch (Throwable $e) {
        wifi_otantik_cron_log('bypass valid: ' . $e->getMessage());
        return 0;
    }
    $n = 0;
    $routers = ORM::for_table('tbl_routers')->where('enabled', '1')->find_many();
    foreach ($routers as $router) {
        $rname = (string)$router['name'];
        try {
            $stale = [];
            try {
                foreach (ORM::for_table('tbl_otantik_mac_bypass')->where('router_id', (int)$router['id'])->where('status', 'on')->find_many() as $row) {
                    $u = (string)$row['username'];
                    if ($u === '' || empty($valid[$rname][$u])) {
                        $stale[$u] = wifi_otantik_mac_norm($row['mac'] ?? '');
                    }
                }
            } catch (Throwable $e) {
            }
            $client = wifi_otantik_mk_client($router, 4);
            foreach (wifi_otantik_ros_rows($client, '/ip/hotspot/ip-binding/print', 400, '.id,mac-address,comment,type') as $bind) {
                $u = wifi_otantik_mac_bypass_comment_user($bind['comment'] ?? '');
                if ($u === '') {
                    continue;
                }
                if (!empty($valid[$rname][$u])) {
                    continue;
                }
                $stale[$u] = wifi_otantik_mac_norm($bind['mac-address'] ?? ($stale[$u] ?? ''));
            }
            foreach ($stale as $u => $mac) {
                try {
                    wifi_otantik_mac_bypass_remove($router, $u, $mac);
                    $n++;
                    wifi_otantik_cron_log('bypass swept ' . $u . ' @' . $rname);
                } catch (Throwable $e) {
                    wifi_otantik_cron_log('bypass sweep ' . $u . ' @' . $rname . ': ' . $e->getMessage());
                }
            }
        } catch (Throwable $e) {
            wifi_otantik_cron_log('bypass router ' . $rname . ': ' . $e->getMessage());
        }
    }
    return $n;
}

function wifi_otantik_reconcile_log($trxId, $ownerId, $action, $ok, $message)
{
    try {
        $row = ORM::for_table('tbl_otantik_reconcile_logs')->create();
        $row->trx_id = (int)$trxId;
        $row->owner_id = (int)$ownerId;
        $row->action = substr((string)$action, 0, 32);
        $row->ok = $ok ? 1 : 0;
        $row->message = mb_substr((string)$message, 0, 255);
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
    } catch (Throwable $e) {
    }
}

/**
 * Auto-heals the exact "paid, never provisioned" failure confirmed live on
 * 2026-10-03 (Otantik Hub / fleur-joli) - but ONLY for sales paid within the
 * last hour. Older gaps (an already-expired access window, or a deliberate
 * exception like a historical ledger restore that was never meant to issue
 * a fresh ticket - see trx 1360) are left for a human to review on the
 * Reconciliation page and are never auto-touched, so this can't silently
 * hand out tickets for abandoned or intentionally-unfulfilled old sales.
 */
function wifi_otantik_cron_reconcile()
{
    $totalGaps = 0;
    $fixed = 0;
    $failed = 0;
    try {
        $all = wifi_otantik_reconcile_gaps_query(['user_type' => 'SuperAdmin', 'id' => 0, 'root' => 0], 14)->find_many();
        $totalGaps = count($all);
        $cutoff = strtotime('-60 minutes');
        $attempts = 0;
        // A hung RouterOS connection can tie up this slot for its whole
        // timeout - cap attempts per run so one flaky router can't crowd
        // out the rest of this cron cycle (expire/kick/bypass/WhatsApp).
        $maxAttempts = 5;
        foreach ($all as $t) {
            if ($attempts >= $maxAttempts) {
                break;
            }
            $paidTs = strtotime((string)$t['paid_date']);
            if ($paidTs === false || $paidTs < $cutoff) {
                continue;
            }
            $attempts++;
            $router = ORM::for_table('tbl_routers')->where('name', (string)$t['routers'])->find_one();
            $ownerId = $router ? (int)$router['owner_id'] : 0;
            $res = wifi_otantik_reconcile_reprovision((int)$t['id']);
            if ($res === true) {
                $fixed++;
                wifi_otantik_reconcile_log((int)$t['id'], $ownerId, 'auto_fix', 1, 'Ticket créé et poussé sur le routeur');
            } else {
                $failed++;
                wifi_otantik_reconcile_log((int)$t['id'], $ownerId, 'auto_fix', 0, (string)$res);
            }
        }
    } catch (Throwable $e) {
        wifi_otantik_cron_log('reconcile: ' . $e->getMessage());
    }
    return ['total' => $totalGaps, 'fixed' => $fixed, 'failed' => $failed];
}

function wifi_otantik_cron()
{
    global $CACHE_PATH;
    $dir = !empty($CACHE_PATH) ? $CACHE_PATH : '/tmp';
    $lockFile = rtrim($dir, '/\\') . '/otantik_expire.lock';
    $fp = @fopen($lockFile, 'c');
    if ($fp === false || !flock($fp, LOCK_EX | LOCK_NB)) {
        wifi_otantik_cron_log('already running');
        return;
    }
    try {
        $exp = wifi_otantik_cron_expire_recharges();
        $quota = wifi_otantik_cron_quota_mac_bypass();
        $kick = wifi_otantik_cron_kick_stale_sessions();
        $byp = wifi_otantik_cron_sweep_mac_bypass();
        $wa = ['soon' => 0, 'now' => 0, 'down' => 0];
        if (class_exists('OtantikWhatsApp')) {
            try {
                $wa = OtantikWhatsApp::cron();
            } catch (Throwable $e) {
                wifi_otantik_cron_log('whatsapp ' . $e->getMessage());
            }
        }
        $recon = wifi_otantik_cron_reconcile();
        wifi_otantik_cron_log('done expired=' . $exp . ' quota=' . $quota . ' kicked=' . $kick . ' bypass=' . $byp
            . ' wa_soon=' . (int)$wa['soon'] . ' wa_now=' . (int)$wa['now'] . ' wa_down=' . (int)$wa['down']
            . ' reconcile_gaps=' . (int)$recon['total'] . ' reconcile_fixed=' . (int)$recon['fixed'] . ' reconcile_failed=' . (int)$recon['failed']);
    } catch (Throwable $e) {
        wifi_otantik_cron_log('fatal ' . $e->getMessage());
    }
    flock($fp, LOCK_UN);
    fclose($fp);
}

function wifi_otantik_user_msg($raw, $kind = 'pay')
{
    $fallbacks = [
        'pay' => 'Le paiement n’a pas abouti. Réessayez.',
        'withdraw' => 'Le virement n’a pas abouti. Réessayez plus tard.',
        'network' => 'Le paiement est indisponible pour le moment. Réessayez dans un instant.',
    ];
    $fb = $fallbacks[$kind] ?? $fallbacks['pay'];
    $s = trim((string)$raw);
    if ($s === '') {
        return $fb;
    }
    if (preg_match('/mesomb|fapshi|hachther|webhook|sdk|api\b|http\b|curl|ssl|application key|access key|secret key|payout|direct.?pay|passerelle/i', $s)) {
        return $fb;
    }
    if (strlen($s) > 180) {
        return $fb;
    }
    return $s;
}

function wifi_otantik_wa_remember_phone($phone)
{
    $phone = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : preg_replace('/\D+/', '', (string)$phone);
    if (strlen($phone) < 9) {
        return;
    }
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION['wo_pay_phone'] = $phone;
}

function wifi_otantik_wa_last_phone()
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $p = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($_SESSION['wo_pay_phone'] ?? '') : '';
    if (strlen($p) >= 9) {
        return $p;
    }
    return function_exists('wifi_otantik_phone') ? wifi_otantik_phone($_COOKIE['wo_hs_phone'] ?? '') : '';
}

function wifi_otantik_wa_ticket($cust, $plan, $router, $user, $pass, $kind = 'Hotspot')
{
    if (!class_exists('OtantikWhatsApp')) {
        return;
    }
    try {
        OtantikWhatsApp::notify_ticket($cust, $plan, $router, $user, $pass, $kind);
    } catch (Throwable $e) {
    }
}

function wifi_otantik_record_recharge($cust, $plan, $router, $user, $method = 'Mobile Money', $trx = null, $issuedBy = 0, $recharged = '')
{
    $durationSec = wifi_otantik_expire_at($plan) - time();
    $startTs = $recharged !== '' ? (strtotime($recharged) ?: time()) : time();
    $exp = $startTs + $durationSec;
    try {
        foreach (ORM::for_table('tbl_user_recharges')->where('username', $user)->where('status', 'on')->find_many() as $old) {
            $old->status = 'off';
            $old->save();
        }
    } catch (Throwable $e) {
    }
    try {
        $row = ORM::for_table('tbl_user_recharges')->create();
        $row->customer_id = (int)$cust['id'];
        $row->username = $user;
        $row->plan_id = (int)$plan['id'];
        $row->namebp = $plan['name_plan'];
        $row->recharged_on = date('Y-m-d', $startTs);
        $row->recharged_time = date('H:i:s', $startTs);
        $row->expiration = date('Y-m-d', $exp);
        $row->time = date('H:i:s', $exp);
        $row->status = 'on';
        $row->method = $method ?: 'Mobile Money';
        $row->routers = $router['name'];
        $row->type = wifi_otantik_plan_kind($plan);
        // Reused stock column (previously always == router owner_id and
        // never read anywhere): now the id of whoever actually issued this
        // recharge (owner or a staff/reseller sub-account), so the tenant
        // can see which reseller created a given ticket. Falls back to the
        // router owner for self-checkout/API recharges, where there's no
        // staff actor.
        $row->admin_id = $issuedBy > 0 ? $issuedBy : (int)$router['owner_id'];
        // Links back to the payment transaction (when there is a real one -
        // free/credit recharges pass null) so a later date correction on
        // this recharge can also correct the transaction's date, keeping
        // the revenue chart/ERP ledger/wallet history in sync.
        $row->trx_id = $trx ? (int)$trx->id() : 0;
        $row->save();
    } catch (Throwable $e) {
    }
}

function wifi_otantik_gateways()
{
    return ['mesomb', 'fapshi', 'manual'];
}

function wifi_otantik_pay_method_label($method)
{
    $m = strtoupper(trim((string)$method));
    $map = [
        'CASH' => 'Espèces',
        'ORANGE' => 'Orange Money',
        'MTN' => 'MTN MoMo',
        'CREDIT' => 'Points',
        'RESELLER' => 'Revendeur',
        'MESOMB' => 'Mobile Money',
        'FAPSHI' => 'Mobile Money',
    ];
    if (isset($map[$m])) {
        return $map[$m];
    }
    if ($m === '') {
        return '—';
    }
    return (string)$method;
}

function wifi_otantik_receipt_number($trx)
{
    $existing = trim((string)($trx['trx_invoice'] ?? ''));
    if ($existing !== '') {
        return $existing;
    }
    $id = is_object($trx) ? (int)$trx->id() : (int)($trx['id'] ?? 0);
    $ts = strtotime((string)($trx['paid_date'] ?? $trx['created_date'] ?? ''));
    $year = $ts ? date('Y', $ts) : date('Y');
    return 'WO-' . $year . '-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}

function wifi_otantik_receipt_ensure($trx)
{
    if (!$trx || (int)$trx['status'] !== 2) {
        return '';
    }
    $no = trim((string)($trx['trx_invoice'] ?? ''));
    if ($no !== '') {
        return $no;
    }
    $no = wifi_otantik_receipt_number($trx);
    try {
        $trx->trx_invoice = $no;
        $trx->save();
    } catch (Throwable $e) {
    }
    return $no;
}

function wifi_otantik_receipt_can_view($trx, $admin)
{
    if (!$trx || !in_array((string)$trx['gateway'], wifi_otantik_gateways(), true)) {
        return false;
    }
    if (Tenant::is_platform($admin)) {
        return true;
    }
    $names = Tenant::router_names($admin);
    return $names && in_array((string)$trx['routers'], $names, true);
}

function wifi_otantik_receipt_payload($trx)
{
    $parts = explode(':', (string)($trx['payment_channel'] ?? ''), 2);
    $code = trim((string)($parts[0] ?? ''));
    $pass = trim((string)($parts[1] ?? ''));
    $kind = 'Hotspot';
    try {
        $meta = json_decode((string)($trx['pg_request'] ?? ''), true);
        if (is_array($meta) && !empty($meta['kind'])) {
            $kind = strtoupper((string)$meta['kind']) === 'PPPOE' ? 'PPPoE' : 'Hotspot';
        }
    } catch (Throwable $e) {
    }
    if ($kind === 'Hotspot' && (int)($trx['plan_id'] ?? 0) > 0) {
        try {
            $plan = ORM::for_table('tbl_plans')->find_one((int)$trx['plan_id']);
            if ($plan && strtoupper((string)$plan['type']) === 'PPPOE') {
                $kind = 'PPPoE';
            }
        } catch (Throwable $e) {
        }
    }
    return [
        'id' => (int)$trx['id'],
        'number' => wifi_otantik_receipt_ensure($trx),
        'date' => (string)($trx['paid_date'] ?: $trx['created_date']),
        'phone' => (string)$trx['username'],
        'plan' => (string)$trx['plan_name'],
        'zone' => (string)$trx['routers'],
        'amount' => (float)$trx['price'],
        'method' => wifi_otantik_pay_method_label($trx['payment_method'] ?? ''),
        'method_raw' => (string)($trx['payment_method'] ?? ''),
        'gateway' => (string)$trx['gateway'],
        'code' => $code,
        'pass' => $pass,
        'kind' => $kind,
        'gateway_trx' => (string)($trx['gateway_trx_id'] ?? ''),
        'tone' => wifi_otantik_receipt_tone($trx['payment_method'] ?? ''),
    ];
}

function wifi_otantik_receipt_tone($methodRaw)
{
    $m = strtoupper(trim((string)$methodRaw));
    if ($m === 'MTN') {
        return 'mtn';
    }
    if ($m === 'ORANGE') {
        return 'orange';
    }
    if ($m === 'CASH') {
        return 'cash';
    }
    return 'mm';
}

function wifi_otantik_receipt_qr_text(array $receipt, $company = 'Wifi-Otantik')
{
    $lines = [
        (string)$company,
        'Reçu ' . ($receipt['number'] ?? ''),
        (int)($receipt['amount'] ?? 0) . ' FCFA · ' . ($receipt['method'] ?? ''),
        'Zone: ' . ($receipt['zone'] ?? ''),
        'Forfait: ' . ($receipt['plan'] ?? ''),
    ];
    if (!empty($receipt['phone'])) {
        $lines[] = 'Client: ' . $receipt['phone'];
    }
    if (!empty($receipt['code'])) {
        $label = (($receipt['kind'] ?? '') === 'PPPoE') ? 'Identifiant' : 'Code Wi-Fi';
        $lines[] = $label . ': ' . $receipt['code'];
        if (!empty($receipt['pass']) && ($receipt['kind'] ?? '') === 'PPPoE') {
            $lines[] = 'Mot de passe: ' . $receipt['pass'];
        }
    }
    if (!empty($receipt['gateway_trx'])) {
        $lines[] = 'Ref: ' . $receipt['gateway_trx'];
    }
    return implode("\n", $lines);
}

function wifi_otantik_receipt_qr_url(array $receipt, $company = 'Wifi-Otantik')
{
    $text = wifi_otantik_receipt_qr_text($receipt, $company);
    return 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=6&ecc=M&data=' . rawurlencode($text);
}

function wifi_otantik_geo($lat, $lng)
{
    $lat = trim(str_replace(',', '.', (string)$lat));
    $lng = trim(str_replace(',', '.', (string)$lng));
    if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
        return null;
    }
    $la = (float)$lat;
    $ln = (float)$lng;
    if (abs($la) > 90 || abs($ln) > 180) {
        return null;
    }
    return [$la, $ln];
}

function wifi_otantik_geo_jitter($lat, $lng, $seed)
{
    $h = abs(crc32((string)$seed));
    $dlat = (($h % 80) - 40) / 12000;
    $dlng = (((int)($h / 80) % 80) - 40) / 12000;
    return [$lat + $dlat, $lng + $dlng];
}

function wifi_otantik_apply_customer_geo($cust, $lat, $lng)
{
    $g = wifi_otantik_geo($lat, $lng);
    if (!$g || !$cust) {
        return;
    }
    try {
        $cust->latitude = (string)$g[0];
        $cust->longitude = (string)$g[1];
    } catch (Throwable $e) {
    }
}

function wifi_otantik_map_points($admin)
{
    $owners = [];
    if (Tenant::is_platform($admin)) {
        foreach (ORM::for_table('tbl_users')->find_many() as $u) {
            $owners[(int)$u['id']] = trim((string)($u['fullname'] ?: $u['username']));
        }
    }
    $zoneGeo = [];
    $zones = [];
    foreach (Tenant::routers_query($admin)->find_many() as $r) {
        $g = wifi_otantik_geo($r['latitude'] ?? '', $r['longitude'] ?? '');
        $name = (string)$r['name'];
        if ($g) {
            $zoneGeo[$name] = $g;
        }
        $st = strtolower((string)($r['status'] ?? ''));
        $zones[] = [
            'id' => (int)$r['id'],
            'key' => 'zone-' . (int)$r['id'],
            'kind' => 'zone',
            'name' => $name,
            'ssid' => (string)($r['ssid'] ?? ''),
            'status' => $st === 'online' ? 'online' : 'offline',
            'owner' => $owners[(int)($r['owner_id'] ?? 0)] ?? '',
            'url' => getUrl('plugin/wifi_otantik_routers/edit/') . (int)$r['id'],
            'address' => (string)($r['location_address'] ?? ''),
            'lat' => $g ? $g[0] : null,
            'lng' => $g ? $g[1] : null,
            'precise' => $g ? 1 : 0,
        ];
    }
    $active = [];
    try {
        $names = Tenant::router_names($admin);
        $rq = ORM::for_table('tbl_user_recharges')->where('status', 'on')->order_by_desc('id');
        if (!Tenant::is_platform($admin)) {
            if (!$names) {
                $rq = null;
            } else {
                $rq->where_in('routers', $names);
            }
        }
        if ($rq) {
            foreach ($rq->find_many() as $row) {
                if (wifi_otantik_recharge_is_expired($row)) {
                    continue;
                }
                $u = (string)$row['username'];
                if ($u !== '' && empty($active[$u])) {
                    $active[$u] = $row;
                }
            }
        }
    } catch (Throwable $e) {
    }
    $cq = ORM::for_table('tbl_customers')->where('service_type', 'PPPOE')->order_by_desc('id')->limit(500);
    if (!Tenant::is_platform($admin)) {
        $cq->where('created_by', Tenant::id($admin));
    }
    $clients = [];
    foreach ($cq->find_many() as $c) {
        $uname = (string)$c['username'];
        $zone = '';
        try {
            $zone = (string)($c['routers'] ?? '');
        } catch (Throwable $e) {
        }
        $rech = $active[$uname] ?? null;
        if ($rech && (string)$rech['routers'] !== '') {
            $zone = (string)$rech['routers'];
        }
        $g = wifi_otantik_geo($c['latitude'] ?? '', $c['longitude'] ?? '');
        $precise = $g ? 1 : 0;
        if (!$g && $zone !== '' && !empty($zoneGeo[$zone])) {
            $g = wifi_otantik_geo_jitter($zoneGeo[$zone][0], $zoneGeo[$zone][1], 'pppoe-' . (int)$c['id']);
        }
        $on = $rech ? 1 : 0;
        $clients[] = [
            'id' => (int)$c['id'],
            'key' => 'pppoe-' . (int)$c['id'],
            'kind' => 'pppoe',
            'name' => $uname,
            'phone' => (string)($c['phonenumber'] ?? ''),
            'zone' => $zone,
            'plan' => $rech ? (string)($rech['namebp'] ?? '') : '',
            'status' => $on ? 'online' : 'offline',
            'owner' => $owners[(int)($c['created_by'] ?? 0)] ?? '',
            'url' => getUrl('plugin/wifi_otantik_customers/pin/') . (int)$c['id'],
            'lat' => $g ? $g[0] : null,
            'lng' => $g ? $g[1] : null,
            'precise' => $precise,
        ];
    }
    return ['zones' => $zones, 'clients' => $clients];
}

function wifi_otantik_rand_code($len = 8)
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    $max = strlen($alphabet) - 1;
    for ($i = 0; $i < $len; $i++) {
        $out .= $alphabet[random_int(0, $max)];
    }
    return $out;
}

function wifi_otantik_french_words($kind = 'all')
{
    $adj = [
        'bleu', 'vert', 'rouge', 'noir', 'blanc', 'rose', 'gris', 'dore',
        'petit', 'grand', 'doux', 'chaud', 'froid', 'beau', 'joli', 'sage',
        'fort', 'vif', 'calme', 'clair',
    ];
    $nom = [
        'lune', 'mer', 'ciel', 'feu', 'pain', 'lait', 'cafe', 'miel',
        'chat', 'pont', 'rue', 'parc', 'toit', 'sac', 'cle', 'moto',
        'bus', 'taxi', 'velo', 'papa', 'maman', 'bebe', 'ami', 'fleur',
        'arbre', 'pluie', 'vent', 'neige', 'sable', 'cacao', 'coco', 'mais',
        'pagne', 'plage', 'lac', 'mont', 'roi', 'dame', 'soleil', 'maison',
        'porte', 'lampe', 'livre', 'radio', 'table', 'route', 'banane', 'mangue',
    ];
    if ($kind === 'adj') {
        return $adj;
    }
    if ($kind === 'nom') {
        return $nom;
    }
    return array_values(array_unique(array_merge($adj, $nom)));
}

function wifi_otantik_pick_french($kind = 'all', $exclude = [])
{
    $list = array_values(array_diff(wifi_otantik_french_words($kind), $exclude));
    if (!$list) {
        $list = wifi_otantik_french_words($kind);
    }
    return $list[random_int(0, count($list) - 1)];
}

function wifi_otantik_login_taken($code)
{
    return (bool)ORM::for_table('tbl_customers')->where('username', $code)->find_one();
}

function wifi_otantik_login_is_memorable($user)
{
    $user = strtolower(trim((string)$user));
    return $user !== '' && preg_match('/^[a-z]+(?:-[a-z]+(?:-[0-9]{2})?)?$/', $user);
}

function wifi_otantik_new_memorable_login()
{
    $words = wifi_otantik_french_words('all');
    shuffle($words);
    foreach ($words as $word) {
        if (!wifi_otantik_login_taken($word)) {
            return $word;
        }
    }
    for ($i = 0; $i < 40; $i++) {
        $a = wifi_otantik_pick_french('all');
        $b = wifi_otantik_pick_french('all', [$a]);
        $code = $a . '-' . $b;
        if ($i >= 20) {
            $code .= '-' . random_int(10, 99);
        }
        if (!wifi_otantik_login_taken($code)) {
            return $code;
        }
    }
    return wifi_otantik_pick_french('nom') . random_int(10, 99);
}

function wifi_otantik_hs_login_for_phone($phone, $router = null)
{
    $existing = strlen((string)$phone) >= 9 ? wifi_otantik_hs_find_customer($phone, $router) : null;
    if ($existing && wifi_otantik_login_is_memorable($existing['username'])) {
        return (string)$existing['username'];
    }
    $user = wifi_otantik_new_memorable_login();
    if ($existing) {
        $existing->username = $user;
        $existing->password = $user;
        try {
            $existing->save();
        } catch (Throwable $e) {
        }
    }
    return $user;
}

function wifi_otantik_hs_ref_defaults()
{
    return [
        'help' => "Pendant le paiement, touchez Affiliation et saisissez le numéro d’un autre utilisateur. Après un achat réussi, ce numéro reçoit des points. Entrez votre propre numéro pour voir vos points, puis choisissez si vous voulez les utiliser pour payer le forfait.",
        'rules' => "• Vous ne pouvez pas vous parrainer vous-même.\n• Les points sont crédités après un paiement réussi.\n• Vous décidez d’utiliser vos points ou de payer en Mobile Money.\n• Le nombre de points offerts et le coût d’un forfait en points sont définis par l’exploitant.",
    ];
}

function wifi_otantik_hs_ref_cfg($router)
{
    $on = ((string)($router['portal_ref_on'] ?? '1')) !== '0';
    $points = (int)($router['portal_ref_points'] ?? 0);
    if ($points < 1) {
        $points = 50;
    }
    if ($points > 10000) {
        $points = 10000;
    }
    $bundle = (int)($router['portal_ref_bundle_pts'] ?? 0);
    if ($bundle < 1) {
        $bundle = 100;
    }
    if ($bundle > 100000) {
        $bundle = 100000;
    }
    $def = wifi_otantik_hs_ref_defaults();
    $help = trim((string)($router['portal_ref_help'] ?? ''));
    $rules = trim((string)($router['portal_ref_rules'] ?? ''));
    return [
        'on' => $on,
        'points' => $points,
        'bundle_pts' => $bundle,
        'help' => $help !== '' ? $help : $def['help'],
        'rules' => $rules !== '' ? $rules : $def['rules'],
        'discount' => 0,
        'reward' => $points,
    ];
}

function wifi_otantik_hs_code_clean($code)
{
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$code));
}

function wifi_otantik_hs_share_ensure($cust)
{
    if (!$cust) {
        return '';
    }
    $have = wifi_otantik_hs_code_clean($cust['wo_share_code'] ?? '');
    if (strlen($have) >= 4) {
        return $have;
    }
    try {
        do {
            $code = strtoupper(wifi_otantik_pick_french('nom') . random_int(10, 99));
        } while (ORM::for_table('tbl_customers')->where('wo_share_code', $code)->find_one());
        $cust->wo_share_code = $code;
        $cust->save();
        return $code;
    } catch (Throwable $e) {
        return '';
    }
}

function wifi_otantik_hs_find_customer($phone, $router = null)
{
    $phone = wifi_otantik_phone($phone);
    if (strlen($phone) < 9) {
        return null;
    }
    $cust = ORM::for_table('tbl_customers')->where('username', 'w' . $phone)->find_one();
    if ($cust) {
        return $cust;
    }
    $q = ORM::for_table('tbl_customers')->where('phonenumber', $phone);
    if ($router && !empty($router['name'])) {
        try {
            $q->where('routers', $router['name']);
        } catch (Throwable $e) {
        }
    }
    return $q->order_by_desc('id')->find_one();
}

function wifi_otantik_hs_points($cust)
{
    if (!$cust) {
        return 0;
    }
    return (int)round((float)($cust['wo_credit'] ?? 0));
}

function wifi_otantik_hs_ensure_phone_customer($phone, $router)
{
    $phone = wifi_otantik_phone($phone);
    if (strlen($phone) < 9 || !$router) {
        return null;
    }
    $cust = wifi_otantik_hs_find_customer($phone, $router);
    if ($cust) {
        return $cust;
    }
    $user = 'w' . $phone;
    return wifi_otantik_upsert_wifi_customer($user, $user, $phone, 'Hotspot', $router);
}

function wifi_otantik_pay_points_json($router)
{
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $phone = wifi_otantik_phone(_get('phone') ?: '');
    $cfg = wifi_otantik_hs_ref_cfg($router);
    $points = 0;
    if (strlen($phone) >= 9) {
        $cust = wifi_otantik_hs_find_customer($phone, $router);
        $points = wifi_otantik_hs_points($cust);
    }
    echo json_encode([
        'ok' => strlen($phone) >= 9,
        'phone' => $phone,
        'points' => $points,
        'bundle_pts' => (int)$cfg['bundle_pts'],
        'award' => (int)$cfg['points'],
    ]);
    exit;
}

function wifi_otantik_hs_find_ref($code, $router, $buyerPhone)
{
    $code = wifi_otantik_hs_code_clean($code);
    if (strlen($code) < 4) {
        return null;
    }
    try {
        $c = ORM::for_table('tbl_customers')->where('wo_share_code', $code)->find_one();
    } catch (Throwable $e) {
        return null;
    }
    if (!$c) {
        return null;
    }
    $owner = (int)($router['owner_id'] ?? 0);
    $sameOwner = (int)($c['created_by'] ?? 0) === $owner;
    $sameZone = trim((string)($c['routers'] ?? '')) === trim((string)($router['name'] ?? ''));
    if (!$sameOwner && !$sameZone) {
        return null;
    }
    if (wifi_otantik_phone($c['phonenumber'] ?? '') === wifi_otantik_phone($buyerPhone)) {
        return null;
    }
    if ((string)$c['username'] === 'w' . wifi_otantik_phone($buyerPhone)) {
        return null;
    }
    return $c;
}

function wifi_otantik_hs_cheapest_plan($router)
{
    $rows = ORM::for_table('tbl_plans')->where('type', 'Hotspot')->where('routers', $router['name'])->where('enabled', 1)->order_by_asc('price')->find_many();
    return $rows ? $rows[0] : null;
}

function wifi_otantik_hs_add_credit($cust, $amount)
{
    if (!$cust || $amount < 1) {
        return;
    }
    try {
        $cust->wo_credit = round((float)($cust['wo_credit'] ?? 0) + $amount, 2);
        $cust->save();
    } catch (Throwable $e) {
    }
}

function wifi_otantik_hs_take_credit($cust, $amount)
{
    if (!$cust || $amount < 1) {
        return 0;
    }
    $have = (float)($cust['wo_credit'] ?? 0);
    $use = min($have, $amount);
    if ($use < 1) {
        return 0;
    }
    try {
        $cust->wo_credit = round($have - $use, 2);
        $cust->save();
        return (int)round($use);
    } catch (Throwable $e) {
        return 0;
    }
}

function wifi_otantik_pay_show_ticket($router, $plan, $user, $pass, $kind, $cust = null, $extra = [])
{
    global $ui, $config;
    $share = '';
    $credit = 0.0;
    if ($cust && $kind !== 'PPPOE') {
        $share = wifi_otantik_hs_share_ensure($cust);
        try {
            $fresh = ORM::for_table('tbl_customers')->find_one((int)$cust['id']);
            if ($fresh) {
                $credit = (float)($fresh['wo_credit'] ?? 0);
            }
        } catch (Throwable $e) {
        }
    }
    $phone = '';
    if ($cust) {
        $phone = wifi_otantik_phone($cust['phonenumber'] ?? '');
    }
    $expTs = $plan ? wifi_otantik_expire_at($plan) : (time() + 7 * 86400);
    wifi_otantik_ticket_remember(
        $phone,
        $user,
        $pass,
        $expTs,
        (string)($router['slug'] ?? ''),
        (string)($plan['name_plan'] ?? '')
    );
    // Always use the hotspot gateway IP for post-pay login — never a public
    // brand domain (otantik.net → GoDaddy) that phones resolve off-LAN.
    $lan = wifi_otantik_hs_portal_host($router);
    $authMode = (string)($extra['auth_mode'] ?? '');
    $expTs = $plan ? wifi_otantik_expire_at($plan) : (time() + 7 * 86400);
    $expLabel = date('d/m/Y H:i', $expTs);
    $leftSecs = max(0, $expTs - time());
    $leftLabel = function_exists('wifi_otantik_remain_label')
        ? wifi_otantik_remain_label($leftSecs)
        : ($leftSecs . 's');
    $statusLan = 'http://' . $lan . '/status';
    $slug = (string)($router['slug'] ?? '');
    $statusUrl = class_exists('Tenant')
        ? Tenant::url_portal('/wifi/' . rawurlencode($slug) . '/status.html')
        : (wifi_otantik_pay_public_root() . '/wifi/' . rawurlencode($slug) . '/status.html');
    $statusUrl .= (strpos($statusUrl, '?') === false ? '?' : '&') . 'u=' . rawurlencode((string)$user);
    if ($authMode === 'login') {
        $loginUrl = $statusLan;
    } else {
        $loginUrl = 'http://' . $lan . '/login?username=' . rawurlencode((string)$user)
            . '&password=' . rawurlencode((string)$pass)
            . '&dst=' . rawurlencode($statusLan) . '&popup=false';
    }
    wifi_otantik_pay_shell($router, $kind);
    $ui->assign('_title', 'Paiement réussi');
    $ui->assign('router', $router);
    $ui->assign('plan', $plan);
    $ui->assign('plan_kind', $kind);
    $ui->assign('wifi_user', $user);
    $ui->assign('wifi_pass', $pass);
    $ui->assign('ticket_phone', $phone);
    $ui->assign('share_code', $share);
    $ui->assign('hs_credit', $credit);
    $ui->assign('paid_discount', (int)($extra['discount'] ?? 0));
    $ui->assign('credit_used', (int)($extra['credit_used'] ?? 0));
    $ui->assign('login_url', $loginUrl);
    $ui->assign('status_url', $statusUrl);
    $ui->assign('auth_mode', $authMode);
    $ui->assign('plan_expires', $expLabel);
    $ui->assign('plan_left', $leftLabel);
    try {
        $ui->clearCompiledTemplate('otantik_voucher.tpl');
    } catch (Throwable $e) {
    }
    try {
        $ui->display('otantik_voucher.tpl');
    } catch (Throwable $e) {
        $u = htmlspecialchars((string)$user, ENT_QUOTES, 'UTF-8');
        $p = htmlspecialchars((string)$pass, ENT_QUOTES, 'UTF-8');
        $planName = htmlspecialchars((string)($plan['name_plan'] ?? ''), ENT_QUOTES, 'UTF-8');
        $login = htmlspecialchars((string)$loginUrl, ENT_QUOTES, 'UTF-8');
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Paiement réussi</title></head><body style="font-family:sans-serif;padding:24px;text-align:center">'
            . '<h2>Paiement réussi</h2><p>Forfait <b>' . $planName . '</b></p>'
            . '<p>Code Wi-Fi : <b style="font-size:22px">' . $u . '</b></p>'
            . ($kind === 'PPPOE' ? '<p>Mot de passe : <b>' . $p . '</b></p>' : '')
            . '<p><a href="' . $login . '">Se connecter au Wi-Fi</a></p></body></html>';
    }
}

function wifi_otantik_manual_login($kind, $phone, $customUser)
{
    $customUser = preg_replace('/[^A-Za-z0-9._-]/', '', (string)$customUser);
    if (strlen($customUser) >= 3) {
        return $customUser;
    }
    if ($kind === 'PPPOE') {
        if (strlen((string)$phone) >= 9) {
            return 'p' . $phone;
        }
        $prefix = 'p';
        do {
            $user = $prefix . strtolower(wifi_otantik_rand_code(7));
        } while (ORM::for_table('tbl_customers')->where('username', $user)->find_one());
        return $user;
    }
    return wifi_otantik_hs_login_for_phone($phone, null);
}

function wifi_otantik_customer_has_col($name)
{
    static $cols = null;
    if ($cols === null) {
        $cols = [];
        try {
            foreach (ORM::get_db()->query('SHOW COLUMNS FROM `tbl_customers`')->fetchAll(PDO::FETCH_ASSOC) as $c) {
                $cols[strtolower((string)$c['Field'])] = true;
            }
        } catch (Throwable $e) {
        }
    }
    return isset($cols[strtolower((string)$name)]);
}

function wifi_otantik_upsert_wifi_customer($user, $pass, $phone, $kind, $router, $keepPass = false)
{
    $cust = ORM::for_table('tbl_customers')->where('username', $user)->find_one();
    $label = $phone !== '' ? $phone : $user;
    if (!$cust) {
        $cust = ORM::for_table('tbl_customers')->create();
        $cust->username = $user;
        $cust->password = $pass;
        $cust->fullname = $label;
        $cust->phonenumber = $phone;
        $cust->email = $label . '@' . strtolower($kind) . '.otantik';
        $cust->service_type = $kind;
        $cust->status = 'Active';
        $cust->created_by = (int)$router['owner_id'];
        $cust->created_at = date('Y-m-d H:i:s');
        if (wifi_otantik_customer_has_col('routers')) {
            $cust->routers = $router['name'];
        }
        if (wifi_otantik_customer_has_col('pppoe_username')) {
            $cust->pppoe_username = $kind === 'PPPOE' ? $user : '';
            $cust->pppoe_password = $kind === 'PPPOE' ? $pass : '';
        }
        wifi_otantik_apply_customer_geo($cust, _post('latitude'), _post('longitude'));
        wifi_otantik_apply_customer_address($cust);
        $cust->save();
        return $cust;
    }
    if (!$keepPass) {
        $cust->password = $pass;
    }
    $cust->service_type = $kind;
    if ($phone !== '') {
        $cust->phonenumber = $phone;
    }
    if (wifi_otantik_customer_has_col('routers')) {
        $cust->routers = $router['name'];
    }
    if ($kind === 'PPPOE' && wifi_otantik_customer_has_col('pppoe_username')) {
        $cust->pppoe_username = $user;
        if (!$keepPass) {
            $cust->pppoe_password = $pass;
        }
    }
    wifi_otantik_apply_customer_geo($cust, _post('latitude'), _post('longitude'));
    wifi_otantik_apply_customer_address($cust);
    $cust->save();
    return $cust;
}

function wifi_otantik_apply_customer_address($cust)
{
    if (!$cust || !wifi_otantik_customer_has_col('location_address') || _post('location_address') === null) {
        return;
    }
    try {
        $cust->location_address = mb_substr(trim((string)_post('location_address')), 0, 255);
    } catch (Throwable $e) {
    }
}

function wifi_otantik_customer_router($cust, $admin)
{
    $name = '';
    try {
        $name = (string)($cust['routers'] ?? '');
    } catch (Throwable $e) {
    }
    if ($name !== '') {
        $r = ORM::for_table('tbl_routers')->where('name', $name)->find_one();
        if ($r && Tenant::owns_router($r, $admin)) {
            return $r;
        }
    }
    try {
        $rech = ORM::for_table('tbl_user_recharges')->where('username', (string)$cust['username'])->order_by_desc('id')->find_one();
        if ($rech) {
            $r = ORM::for_table('tbl_routers')->where('name', (string)$rech['routers'])->find_one();
            if ($r && Tenant::owns_router($r, $admin)) {
                return $r;
            }
        }
    } catch (Throwable $e) {
    }
    $names = Tenant::router_names($admin);
    if (count($names) === 1) {
        $r = ORM::for_table('tbl_routers')->where('name', $names[0])->find_one();
        if ($r) {
            return $r;
        }
    }
    return null;
}

/**
 * Credits the tenant's withdrawable wallet ("Solde à retirer") for a sale
 * that just got marked paid, and syncs it into the ERP ledger for reporting
 * - the single place this happens, used by every path that can confirm a
 * sale (online Mobile Money, and manual/cash sales recorded by staff for
 * either Hotspot or PPPoE), so "solde" always reflects total revenue owed,
 * not just money that happened to land at the payment gateway. Idempotent
 * per transaction via tbl_otantik_ledger's trx_id - safe to call more than
 * once for the same $trx (e.g. a retried request) without double-crediting.
 */
function wifi_otantik_credit_wallet_for_sale($trx, $router)
{
    $amount = (int)round((float)$trx['price']);
    if ($amount <= 0 || !$router) {
        return;
    }
    $exists = false;
    try {
        $exists = (bool)ORM::for_table('tbl_otantik_ledger')->where('trx_id', (int)$trx->id())->find_one();
    } catch (Throwable $e) {
    }
    if (!$exists) {
        $owner = Tenant::tenant_row((int)$router['owner_id']);
        if ($owner) {
            $split = Tenant::split($amount, $owner);
            $owner->wallet = (float)$owner['wallet'] + $split['net'];
            $owner->save();
            try {
                $led = ORM::for_table('tbl_otantik_ledger')->create();
                $led->owner_id = (int)$owner['user_id'];
                $led->trx_id = (int)$trx->id();
                $led->gross = $split['gross'];
                $led->rate = $split['rate'];
                $led->fee = $split['fee'];
                $led->net = $split['net'];
                $led->created_at = date('Y-m-d H:i:s');
                $led->save();
            } catch (Throwable $e) {
            }
        }
    }
    if (class_exists('OtantikErp')) {
        try {
            OtantikErp::sync_payment($trx);
        } catch (Throwable $e) {
        }
    }
}

/**
 * Keeps a payment transaction's recorded date - and everything derived from
 * it (the ERP ledger flow entry, the commission ledger row) - in sync with a
 * later correction to its linked recharge's date, so the revenue chart,
 * Rapports, and wallet history don't keep showing a sale under its original
 * date after an admin corrects when it actually happened.
 */
function wifi_otantik_sync_trx_date($trxId, $newDateTime)
{
    $trxId = (int)$trxId;
    if ($trxId < 1) {
        return;
    }
    try {
        $trx = ORM::for_table('tbl_payment_gateway')->find_one($trxId);
        if ($trx) {
            $trx->paid_date = $newDateTime;
            $trx->created_date = $newDateTime;
            $trx->save();
        }
    } catch (Throwable $e) {
    }
    try {
        $flow = ORM::for_table('tbl_otantik_erp_flow')->where('ref_type', 'payment')->where('ref_id', $trxId)->find_one();
        if ($flow) {
            $flow->occurred_at = $newDateTime;
            $flow->save();
        }
    } catch (Throwable $e) {
    }
    try {
        $led = ORM::for_table('tbl_otantik_ledger')->where('trx_id', $trxId)->find_one();
        if ($led) {
            $led->created_at = $newDateTime;
            $led->save();
        }
    } catch (Throwable $e) {
    }
}

function wifi_otantik_admin_recharge_pppoe($admin, $cust)
{
    $back = getUrl('plugin/wifi_otantik_customers');
    $form = getUrl('plugin/wifi_otantik_customers/recharge/' . (int)$cust['id']);
    $router = wifi_otantik_customer_router($cust, $admin);
    if (!$router) {
        r2($back, 'e', 'Wifi Zone introuvable pour cette ligne');
    }
    $plans = ORM::for_table('tbl_plans')->where('type', 'PPPOE')->where('routers', $router['name'])->where('enabled', 1)->order_by_asc('price')->find_many();
    if (_post('recharge_pppoe')) {
        $plan = Tenant::require_plan((int)_post('plan_id'), $admin);
        if (wifi_otantik_plan_kind($plan) !== 'PPPOE' || (string)$plan['routers'] !== (string)$router['name']) {
            r2($form, 'e', 'Forfait PPPoE invalide pour cette zone');
        }
        $user = (string)$cust['username'];
        $pass = (string)($cust['password'] ?? '');
        if ($pass === '' && wifi_otantik_customer_has_col('pppoe_password')) {
            $pass = (string)($cust['pppoe_password'] ?? '');
        }
        if ($pass === '') {
            r2($form, 'e', 'Mot de passe de la ligne introuvable. Recréez la ligne.');
        }
        $paid = (int)_post('paid') === 1;
        $phone = wifi_otantik_phone($cust['phonenumber'] ?? '');
        $amount = (int)round((float)$plan['price']);
        $trx = ORM::for_table('tbl_payment_gateway')->create();
        $trx->username = $phone !== '' ? $phone : $user;
        $trx->user_id = (int)$cust['id'];
        $trx->gateway = 'manual';
        $trx->plan_id = (int)$plan['id'];
        $trx->plan_name = $plan['name_plan'];
        $trx->routers_id = (int)$router['id'];
        $trx->routers = $router['name'];
        $trx->price = (string)$amount;
        $trx->payment_method = 'CASH';
        $trx->payment_channel = substr($user . ':' . $pass, 0, 128);
        $trx->pg_url_payment = 'manual-recharge';
        $trx->created_date = date('Y-m-d H:i:s');
        $trx->expired_date = date('Y-m-d H:i:s', time() + 86400 * 30);
        $trx->status = $paid ? 2 : 1;
        if ($paid) {
            $trx->paid_date = date('Y-m-d H:i:s');
        }
        $trx->gateway_trx_id = 'CASH-R-' . date('YmdHis');
        $trx->save();
        if ($paid) {
            wifi_otantik_credit_wallet_for_sale($trx, $router);
        }
        $pushError = '';
        try {
            wifi_otantik_push_user_mikrotik($router, $plan, $user, $pass, 'otantik recharge ' . ($phone ?: $user));
            wifi_otantik_record_recharge($cust, $plan, $router, $user, $paid ? 'Espèces' : 'Crédit', $trx, (int)$admin['id']);
            wifi_otantik_wa_ticket($cust, $plan, $router, $user, $pass, 'PPPOE');
        } catch (Throwable $e) {
            $pushError = $e->getMessage();
        }
        $msg = $paid ? 'Ligne rechargée et ajoutée au chiffre d’affaires' : 'Ligne rechargée (non payé, hors CA)';
        if ($pushError !== '') {
            r2($back, 'w', $msg . '. MikroTik : ' . $pushError);
        }
        r2($back, 's', $msg);
    }
    wifi_otantik_ui('plugin/otantik_recharge.tpl', 'Recharger PPPoE', [
        'cust' => $cust,
        'router' => $router,
        'plans' => $plans,
    ]);
}

function wifi_otantik_mark_manual_paid($id, $admin, $back)
{
    $trx = ORM::for_table('tbl_payment_gateway')->find_one((int)$id);
    if (!$trx || (string)$trx['gateway'] !== 'manual') {
        r2($back, 'e', 'Vente introuvable');
    }
    $router = ORM::for_table('tbl_routers')->find_one((int)$trx['routers_id']);
    if (!$router || !Tenant::owns_router($router, $admin)) {
        r2($back, 'e', 'Vente introuvable');
    }
    if ((int)$trx['status'] === 2) {
        r2($back, 's', 'Cette vente est déjà payée');
    }
    $trx->status = 2;
    $trx->paid_date = date('Y-m-d H:i:s');
    $trx->payment_method = 'CASH';
    $trx->save();
    wifi_otantik_receipt_ensure($trx);
    wifi_otantik_credit_wallet_for_sale($trx, $router);
    r2($back, 's', 'Vente ajoutée au chiffre d’affaires');
}

function wifi_otantik_issue_manual($admin)
{
    $back = getUrl('plugin/wifi_otantik_tickets');
    $router = Tenant::require_router((int)_post('router_id'), $admin);
    $plan = Tenant::require_plan((int)_post('plan_id'), $admin);
    if ((string)$plan['routers'] !== (string)$router['name']) {
        r2($back, 'e', 'Ce forfait n’appartient pas à cette zone');
    }
    $kind = wifi_otantik_plan_kind($plan);
    $want = strtoupper(trim((string)_post('kind')));
    if ($want === 'PPPOE' && $kind !== 'PPPOE') {
        r2($back, 'e', 'Choisissez un forfait PPPoE');
    }
    if ($want !== 'PPPOE' && $kind === 'PPPOE') {
        r2($back, 'e', 'Choisissez un forfait hotspot');
    }
    $phone = wifi_otantik_phone(_post('phone'));
    $user = wifi_otantik_manual_login($kind, $phone, _post('username'));
    $pass = trim((string)_post('password'));
    if ($kind !== 'PPPOE') {
        $pass = $user;
    } elseif (strlen($pass) < 4) {
        $pass = strlen($phone) >= 9
            ? substr(preg_replace('/[^A-Za-z0-9]/', '', $phone . 'Ot'), 0, 8)
            : wifi_otantik_rand_code(8);
    }
    $paid = (int)_post('paid') === 1;
    // Optional backdated recharge date (e.g. entering a PPPoE line that was
    // actually sold earlier) - blank falls back to now, same as before.
    $rechD = wifi_otantik_ymd(_post('recharged_on'));
    $rechT = trim((string)_post('recharged_time')) ?: '00:00:00';
    $recharged = $rechD !== '' ? ($rechD . ' ' . $rechT) : '';
    $cust = wifi_otantik_upsert_wifi_customer($user, $pass, $phone, $kind, $router);
    $amount = (int)round((float)$plan['price']);
    $trx = ORM::for_table('tbl_payment_gateway')->create();
    $trx->username = $phone !== '' ? $phone : $user;
    $trx->user_id = (int)$cust['id'];
    $trx->gateway = 'manual';
    $trx->plan_id = (int)$plan['id'];
    $trx->plan_name = $plan['name_plan'];
    $trx->routers_id = (int)$router['id'];
    $trx->routers = $router['name'];
    $trx->price = (string)$amount;
    $trx->payment_method = 'CASH';
    $trx->payment_channel = $user . ':' . $pass;
    $trx->pg_url_payment = 'manual';
    $trx->created_date = $recharged !== '' ? $recharged : date('Y-m-d H:i:s');
    $trx->expired_date = date('Y-m-d H:i:s', time() + 86400 * 30);
    $trx->status = $paid ? 2 : 1;
    if ($paid) {
        $trx->paid_date = $recharged !== '' ? $recharged : date('Y-m-d H:i:s');
    }
    $trx->gateway_trx_id = 'CASH-' . date('YmdHis');
    $trx->save();
    $receiptId = 0;
    if ($paid) {
        wifi_otantik_receipt_ensure($trx);
        $receiptId = (int)$trx->id();
        wifi_otantik_credit_wallet_for_sale($trx, $router);
    }
    $pushError = '';
    try {
        wifi_otantik_push_user_mikrotik($router, $plan, $user, $pass, 'otantik manual ' . ($phone ?: $user));
        wifi_otantik_record_recharge($cust, $plan, $router, $user, $paid ? 'Espèces' : 'Crédit', $trx, (int)$admin['id'], $recharged);
        wifi_otantik_wa_ticket($cust, $plan, $router, $user, $pass, $kind);
    } catch (Throwable $e) {
        $pushError = $e->getMessage();
    }
    $_SESSION['wo_issued'] = [
        'kind' => $kind,
        'user' => $user,
        'pass' => $pass,
        'plan' => (string)$plan['name_plan'],
        'zone' => (string)$router['name'],
        'paid' => $paid ? 1 : 0,
        'price' => $amount,
        'push_error' => $pushError,
        'receipt_id' => $receiptId,
    ];
    $label = $kind === 'PPPOE' ? 'Ligne PPPoE créée' : 'Ticket créé';
    $msg = $paid ? ($label . ' et ajouté au chiffre d’affaires') : ($label . ' (non payé, hors CA)');
    if ($pushError !== '') {
        r2($back, 'w', $msg . '. MikroTik : ' . $pushError);
    }
    r2($back, 's', $msg);
}

function wifi_otantik_hs_is_private_ipv4($ip)
{
    $ip = trim((string)$ip);
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }
    return (bool)!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

/**
 * Host used in phone redirects after payment (login + status).
 * Always a private hotspot gateway IP. Brand names in lan_host (otantik.net,
 * marcher.net) resolve on public DNS to parking pages (404) once the phone
 * leaves captive DNS — never put them in Location / href after MoMo.
 */
function wifi_otantik_hs_portal_host($router)
{
    $lan = trim((string)($router['lan_host'] ?? ''));
    $lan = preg_replace('#^https?://#i', '', $lan);
    $lan = rtrim(preg_replace('/[\/:].*$/', '', $lan), '/');
    if (wifi_otantik_hs_is_private_ipv4($lan)) {
        return $lan;
    }
    $slug = strtolower(trim((string)($router['slug'] ?? '')));
    $known = [
        'otantik-hub' => '10.8.192.1',
        'otantik-hub-nkoabang' => '10.5.53.1',
    ];
    if (isset($known[$slug])) {
        return $known[$slug];
    }
    $pool = wifi_otantik_hs_pool_spec($router);
    return (string)($pool['local'] ?? '10.0.0.1');
}

/** Public Wifi-Otantik hostname for MikroTik fetch / walled-garden (never raw VPS IP in UI). */
function wifi_otantik_public_host()
{
    global $config;
    $h = trim((string)($config['otantik_host_wifi'] ?? ''));
    $h = preg_replace('#^https?://#i', '', $h);
    $h = rtrim(preg_replace('/[\/:].*$/', '', $h), '/');
    if ($h === '' || filter_var($h, FILTER_VALIDATE_IP)) {
        $h = 'wifi.otantik.cm';
    }
    return strtolower($h);
}

function wifi_otantik_hs_pool_spec($router)
{
    $id = max(1, (int)$router['id']);
    // Each router gets its own /18 (~16k usable addresses) so a busy WISP
    // site never runs out of DHCP leases the way a /24 (254 addrs) did.
    // Slots are laid out as four /18s per second-octet value, cycling
    // through 10.5.x - 10.19.x (skips 10.20.x, used by the PPPoE pool) so
    // up to 60 routers still get non-overlapping ranges, same as before.
    $slot = $id % 60;
    $octB = 5 + intdiv($slot, 4);
    $octCBase = ($slot % 4) * 64;
    $host = strtolower(trim((string)($router['lan_host'] ?? '')));
    $host = preg_replace('#^https?://#', '', $host);
    $host = preg_replace('/[\/:].*$/', '', $host);
    $host = preg_replace('/[^a-z0-9.-]/', '', $host);
    if ($host === '' || filter_var($host, FILTER_VALIDATE_IP)) {
        $host = 'hotspot.lan';
    }
    return [
        'name' => 'hs-pool',
        'local' => '10.' . $octB . '.' . $octCBase . '.1',
        'ranges' => '10.' . $octB . '.' . $octCBase . '.10-10.' . $octB . '.' . ($octCBase + 63) . '.250',
        'net' => '10.' . $octB . '.' . $octCBase . '.0/18',
        'mask' => 18,
        'dhcp' => 'dhcp-hs',
        'server' => 'hotspot-otantik',
        'profile' => 'hs-otantik',
        'dns' => $host,
    ];
}

function wifi_otantik_rsc_lan_detect($prefIface, $ssid = '')
{
    $pref = wifi_otantik_ros_str(trim((string)$prefIface));
    $ssid = trim((string)$ssid);
    if ($ssid === '') {
        $ssid = 'Otantik-WiFi';
    }
    $ssidEsc = wifi_otantik_ros_str(substr($ssid, 0, 32));
    return <<<RSC
:local hsIface "{$pref}"
:if (\$hsIface = "") do={
    :if ([:len [/interface bridge find name="bridge-hotspot"]] > 0) do={
        :set hsIface "bridge-hotspot"
    }
}
:if (\$hsIface = "") do={
    :if ([:len [/interface bridge find name="hs-bridge"]] > 0) do={
        :set hsIface "hs-bridge"
    }
}
:if (\$hsIface = "") do={
    :if ([:len [/interface wireless find]] > 0) do={
        :set hsIface [/interface wireless get [:pick [/interface wireless find] 0] name]
    }
}
:if (\$hsIface = "") do={
    :if ([:len [/interface bridge find name="bridge"]] > 0) do={
        :set hsIface "bridge"
    } else={
        :if ([:len [/interface bridge find]] > 0) do={
            :set hsIface [/interface bridge get [:pick [/interface bridge find] 0] name]
        }
    }
}
:if (\$hsIface = "") do={
    :foreach i in=[/interface ethernet find] do={
        :local dn ""
        :do { :set dn [/interface ethernet get \$i default-name] } on-error={}
        :if ((\$hsIface = "") && (\$dn != "ether1")) do={
            :set hsIface [/interface ethernet get \$i name]
        }
    }
}
:if (\$hsIface = "") do={
    :do {
        :if ([:len [/interface wireless find]] > 0) do={
            :local wIface [/interface wireless get [:pick [/interface wireless find] 0] name]
            :if ([:len [/interface wireless security-profiles find name="wo-open"]] = 0) do={
                /interface wireless security-profiles add name=wo-open mode=none
            }
            /interface wireless set [find name=\$wIface] mode=ap-bridge ssid="{$ssidEsc}" security-profile=wo-open disabled=no
            :set hsIface \$wIface
        }
    } on-error={}
}
:if (\$hsIface = "") do={
    :put "WARNING: no LAN or WiFi interface found - hotspot/DHCP were NOT created. Free up an ethernet port (not ether1) or a WiFi radio, then re-run provisioning."
}
:put ("LAN / hotspot / PPPoE interface: " . \$hsIface)

RSC;
}

function wifi_otantik_rsc_pppoe($routerId)
{
    $r = ORM::for_table('tbl_routers')->find_one($routerId);
    if (!$r) {
        return '';
    }
    $spec = wifi_otantik_pppoe_pool_spec($r);
    $pref = wifi_otantik_ros_str(trim((string)($r['pppoe_interface'] ?? '')));
    $pool = wifi_otantik_ros_str($spec['name']);
    $local = wifi_otantik_ros_str($spec['local']);
    $ranges = wifi_otantik_ros_str($spec['ranges']);
    $net = wifi_otantik_ros_str($spec['net']);
    return <<<RSC
:put "Configuring PPPoE (pool, profile, server)..."
:local pppoeIface "{$pref}"
:if (\$pppoeIface = "") do={ :set pppoeIface \$hsIface }
:if ([:len [/ip pool find name="{$pool}"]] = 0) do={
    /ip pool add name="{$pool}" ranges={$ranges}
} else={
    /ip pool set [find name="{$pool}"] ranges={$ranges}
}
:if ([:len [/ip address find comment="wifi-otantik-pppoe"]] = 0) do={
    :if (\$pppoeIface != "") do={
        :do { /ip address add address={$local}/24 interface=\$pppoeIface comment=wifi-otantik-pppoe } on-error={}
    }
} else={
    :do { /ip address set [find comment="wifi-otantik-pppoe"] address={$local}/24 } on-error={}
}
:if ([:len [/ip firewall nat find comment="wifi-otantik-pppoe-masq"]] = 0) do={
    /ip firewall nat add chain=srcnat src-address={$net} action=masquerade comment=wifi-otantik-pppoe-masq
}
:if ([:len [/ppp profile find name="pppoe-otantik"]] = 0) do={
    :do { /ppp profile add name=pppoe-otantik local-address={$local} remote-address="{$pool}" dns-server=8.8.8.8,1.1.1.1 only-one=yes } on-error={}
} else={
    :do { /ppp profile set [find name="pppoe-otantik"] local-address={$local} remote-address="{$pool}" dns-server=8.8.8.8,1.1.1.1 } on-error={}
}
:if (\$pppoeIface != "") do={
    :do {
        :if ([:len [/interface pppoe-server server find service-name="pppoe-otantik"]] = 0) do={
            :do {
                /interface pppoe-server server add service-name=pppoe-otantik interface=\$pppoeIface authentication=chap,mschap1,mschap2 default-profile=pppoe-otantik one-session-per-host=yes disabled=no
            } on-error={
                /interface pppoe-server server add service-name=pppoe-otantik interface=\$pppoeIface default-profile=pppoe-otantik disabled=no
            }
        } else={
            :do { /interface pppoe-server server set [find service-name="pppoe-otantik"] interface=\$pppoeIface default-profile=pppoe-otantik disabled=no } on-error={}
        }
    } on-error={ :put "PPPoE server skipped (package pppoe?)" }
} else={
    :put "PPPoE server skipped: no LAN interface"
}

RSC;
}

function wifi_otantik_set_config($setting, $value)
{
    $row = ORM::for_table('tbl_appconfig')->where('setting', $setting)->find_one();
    if (!$row) {
        $row = ORM::for_table('tbl_appconfig')->create();
        $row->setting = $setting;
    }
    $row->value = $value;
    $row->save();
}

function wifi_otantik_mesomb()
{
    global $config, $PAYMENTGATEWAY_PATH;
    $sdk = $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . 'mesomb-sdk' . DIRECTORY_SEPARATOR . 'autoload.php';
    if (!is_file($sdk)) {
        throw new Exception('Paiement indisponible');
    }
    require_once $sdk;
    if (!empty($config['mesomb_api_base'])) {
        MeSomb\MeSomb::$apiBase = rtrim($config['mesomb_api_base'], '/');
    }
    return new MeSomb\Operation\PaymentOperation(
        $config['mesomb_application_key'] ?? '',
        $config['mesomb_access_key'] ?? '',
        $config['mesomb_secret_key'] ?? ''
    );
}

function wifi_otantik_pg()
{
    global $admin, $config;
    _admin();
    if (!Tenant::is_platform($admin)) {
        r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
    }
    if (_post('save_pg')) {
        $pg = strtolower(trim((string)_post('otantik_pg')));
        if ($pg !== 'fapshi') {
            $pg = 'mesomb';
        }
        $pairs = [
            'otantik_pg' => $pg,
            'mesomb_application_key' => trim((string)_post('mesomb_application_key')),
            'mesomb_access_key' => trim((string)_post('mesomb_access_key')),
            'mesomb_api_base' => rtrim(trim((string)_post('mesomb_api_base')), '/') ?: 'https://mesomb.hachther.com',
            'mesomb_withdraw_fee' => (string)round(max(0, min(20, (float)str_replace(',', '.', (string)_post('mesomb_withdraw_fee')))), 2),
            'fapshi_api_user' => trim((string)_post('fapshi_api_user')),
            'fapshi_env' => in_array(_post('fapshi_env'), ['live', 'sandbox', 'auto'], true) ? _post('fapshi_env') : 'live',
            'fapshi_payout_api_user' => trim((string)_post('fapshi_payout_api_user')),
        ];
        $pairs['mesomb_secret_key'] = OtantikPay::keep_secret(_post('mesomb_secret_key'), OtantikPay::cfg('mesomb_secret_key'));
        $pairs['fapshi_api_key'] = OtantikPay::keep_secret(_post('fapshi_api_key'), OtantikPay::cfg('fapshi_api_key'));
        $pairs['fapshi_webhook_secret'] = OtantikPay::keep_secret(_post('fapshi_webhook_secret'), OtantikPay::cfg('fapshi_webhook_secret'));
        $pairs['fapshi_payout_api_key'] = OtantikPay::keep_secret(_post('fapshi_payout_api_key'), OtantikPay::cfg('fapshi_payout_api_key'));
        $pairs['otantik_wa_phone_id'] = preg_replace('/\D+/', '', (string)_post('otantik_wa_phone_id'));
        $pairs['otantik_wa_verify'] = trim((string)_post('otantik_wa_verify'));
        $pairs['otantik_wa_token'] = OtantikPay::keep_secret(_post('otantik_wa_token'), (string)($config['otantik_wa_token'] ?? ''));
        $pairs['otantik_wa_enabled'] = _post('otantik_wa_enabled') ? '1' : '0';
        foreach ($pairs as $k => $v) {
            wifi_otantik_set_config($k, $v);
            $config[$k] = $v;
        }
        r2(getUrl('plugin/wifi_otantik_pg'), 's', 'Paiements enregistrés');
    }
    if (_post('wa_test') && class_exists('OtantikWhatsApp')) {
        $ok = OtantikWhatsApp::send_tpl(_post('wa_test_phone'), 'wo_hello', ['client', 'Wifi-Otantik'], [], 'test', 0, 20);
        r2(
            getUrl('plugin/wifi_otantik_wa_logs'),
            $ok ? 's' : 'e',
            $ok ? 'Message WhatsApp de test envoyé.' : ('WhatsApp : ' . (OtantikWhatsApp::$lastError !== '' ? OtantikWhatsApp::$lastError : 'échec'))
        );
    }
    wifi_otantik_ui('plugin/otantik_gateways.tpl', 'Paiements', [
        'pg_active' => OtantikPay::active(),
        'pg_label' => OtantikPay::label(),
        'pg_health' => OtantikPay::health(),
        'pg_webhook' => OtantikPay::webhook_url(),
        'mesomb_application_key' => OtantikPay::cfg('mesomb_application_key'),
        'mesomb_access_key' => OtantikPay::cfg('mesomb_access_key'),
        'mesomb_secret_mask' => OtantikPay::mask(OtantikPay::cfg('mesomb_secret_key')),
        'mesomb_api_base' => OtantikPay::cfg('mesomb_api_base') ?: 'https://mesomb.hachther.com',
        'mesomb_withdraw_fee' => OtantikPay::withdraw_fee_pct(),
        'fapshi_api_user' => OtantikPay::cfg('fapshi_api_user'),
        'fapshi_api_key_mask' => OtantikPay::mask(OtantikPay::cfg('fapshi_api_key')),
        'fapshi_env' => OtantikPay::cfg('fapshi_env') ?: 'live',
        'fapshi_webhook_secret_mask' => OtantikPay::mask(OtantikPay::cfg('fapshi_webhook_secret')),
        'fapshi_payout_api_user' => OtantikPay::cfg('fapshi_payout_api_user'),
        'fapshi_payout_api_key_mask' => OtantikPay::mask(OtantikPay::cfg('fapshi_payout_api_key')),
        'wa_ready' => class_exists('OtantikWhatsApp') && OtantikWhatsApp::ready(),
        'wa_enabled' => (string)($config['otantik_wa_enabled'] ?? '1') === '1',
        'wa_hook' => class_exists('OtantikWhatsApp') ? OtantikWhatsApp::hook_url() : '',
        'otantik_wa_phone_id' => (string)($config['otantik_wa_phone_id'] ?? ''),
        'otantik_wa_verify' => (string)($config['otantik_wa_verify'] ?? ''),
        'otantik_wa_token_mask' => OtantikPay::mask((string)($config['otantik_wa_token'] ?? '')),
        'wa_catalog' => class_exists('OtantikWhatsApp') ? implode(', ', array_keys(OtantikWhatsApp::catalog())) : '',
        'wa_last_error' => (string)($config['otantik_wa_last_error'] ?? ''),
        'wa_last_at' => (string)($config['otantik_wa_last_at'] ?? ''),
    ]);
}

function wifi_otantik_login_logs()
{
    global $admin, $routes;
    _admin();
    if (!Tenant::is_platform($admin)) {
        r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
    }
    Tenant::ensure_schema();

    if (($routes[2] ?? '') === 'disconnect') {
        $uid = (int)($routes[3] ?? 0);
        $u = ORM::for_table('tbl_users')->find_one($uid);
        if (!$u) {
            r2(getUrl('plugin/wifi_otantik_login_logs'), 'e', 'Compte introuvable');
        }
        if (empty($_POST)) {
            r2(getUrl('plugin/wifi_otantik_login_logs'), 'e', 'Confirmation requise');
        }
        $u->force_logout_at = date('Y-m-d H:i:s');
        $u->save();
        r2(getUrl('plugin/wifi_otantik_login_logs'), 's', $u['username'] . ' a été déconnecté - une nouvelle connexion sera nécessaire.');
    }

    $qStr = wifi_otantik_q();
    $total = 0;
    try {
        $countQ = ORM::for_table('tbl_otantik_login_logs');
        wifi_otantik_where_search($countQ, ['username', 'ip', 'device_label', 'status', 'reason'], $qStr);
        $total = (int)$countQ->count();
    } catch (Throwable $e) {
    }
    $pager = wifi_otantik_pager($total);
    $rows = [];
    try {
        $list = ORM::for_table('tbl_otantik_login_logs');
        wifi_otantik_where_search($list, ['username', 'ip', 'device_label', 'status', 'reason'], $qStr);
        $list->order_by_desc('id');
        foreach (wifi_otantik_paged($list, $pager)->find_array() as $r) {
            $ts = strtotime((string)$r['created_at']);
            $rows[] = [
                'id' => (int)$r['id'],
                'user_id' => (int)$r['user_id'],
                'username' => (string)$r['username'],
                'status' => (string)$r['status'],
                'reason' => (string)$r['reason'],
                'ip' => (string)$r['ip'],
                'device_label' => (string)$r['device_label'],
                'created_at' => (string)$r['created_at'],
                'ago' => wifi_otantik_ago($ts ?: 0),
            ];
        }
    } catch (Throwable $e) {
    }
    wifi_otantik_ui('plugin/otantik_login_logs.tpl', 'Connexions', [
        'rows' => $rows,
        'filter_q' => $qStr,
        'pager' => $pager,
    ]);
}

function wifi_otantik_wa_logs()
{
    global $admin, $config;
    _admin();
    if (!Tenant::is_platform($admin)) {
        r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
    }
    Tenant::ensure_schema();
    $qStr = wifi_otantik_q();
    $total = 0;
    try {
        $countQ = ORM::for_table('tbl_otantik_wa_logs');
        wifi_otantik_where_search($countQ, ['phone', 'tpl', 'kind', 'detail', 'direction'], $qStr);
        $total = (int)$countQ->count();
    } catch (Throwable $e) {
    }
    $pager = wifi_otantik_pager($total);
    $rows = [];
    try {
        $list = ORM::for_table('tbl_otantik_wa_logs');
        wifi_otantik_where_search($list, ['phone', 'tpl', 'kind', 'detail', 'direction'], $qStr);
        $list->order_by_desc('id');
        foreach (wifi_otantik_paged($list, $pager)->find_array() as $r) {
            $kind = (string)($r['kind'] ?? '');
            $tpl = (string)($r['tpl'] ?? '');
            $dir = (string)($r['direction'] ?? 'out');
            $label = class_exists('OtantikWhatsApp') ? OtantikWhatsApp::tpl_label($tpl) : $tpl;
            if ($kind === 'in') {
                $label = 'Message reçu';
            } elseif ($kind === 'session') {
                $label = 'Réponse (fenêtre 24 h)';
            }
            $ts = strtotime((string)$r['created_at']);
            $rows[] = [
                'id' => (int)$r['id'],
                'direction' => $dir,
                'kind' => $kind,
                'tpl' => $tpl,
                'label' => $label,
                'phone' => (string)$r['phone'],
                'ok' => (int)$r['ok'] === 1,
                'detail' => (string)$r['detail'],
                'created_at' => (string)$r['created_at'],
                'ago' => function_exists('wifi_otantik_ago') ? wifi_otantik_ago($ts ?: 0) : '',
            ];
        }
    } catch (Throwable $e) {
    }
    wifi_otantik_ui('plugin/otantik_wa_logs.tpl', 'Journal WhatsApp', [
        'rows' => $rows,
        'filter_q' => $qStr,
        'pager' => $pager,
        'wa_ready' => class_exists('OtantikWhatsApp') && OtantikWhatsApp::ready(),
        'wa_last_error' => (string)($config['otantik_wa_last_error'] ?? ''),
        'wa_last_at' => (string)($config['otantik_wa_last_at'] ?? ''),
    ]);
}

/**
 * Phone numbers a tenant is allowed to see/message in the shared WhatsApp
 * inbox - everyone shares one Meta Business number, so this is the only
 * thing keeping a premium tenant's conversations to their own customers.
 * Returns null for the platform account (no filter - sees everything).
 */
function wifi_otantik_wa_scope_phones($admin)
{
    if (Tenant::is_platform($admin)) {
        return null;
    }
    $oid = Tenant::id($admin);
    $phones = [];
    try {
        foreach (ORM::for_table('tbl_customers')->select('phonenumber')->where('created_by', $oid)->find_array() as $c) {
            $p = preg_replace('/\D+/', '', (string)$c['phonenumber']);
            if ($p !== '') {
                $phones[$p] = true;
            }
        }
    } catch (Throwable $e) {
    }
    try {
        $names = Tenant::router_names($admin);
        if ($names) {
            foreach (ORM::for_table('tbl_payment_gateway')->select('username')->where_in('routers', $names)->find_array() as $t) {
                $p = preg_replace('/\D+/', '', (string)$t['username']);
                if ($p !== '') {
                    $phones[$p] = true;
                }
            }
        }
    } catch (Throwable $e) {
    }
    return array_keys($phones);
}

/**
 * Best display name for a WhatsApp conversation: prefer a known customer's
 * full name, then the ticket/voucher code from their most recent sale (the
 * memorable code like "fleur-joli", not the phone used to pay - see
 * wifi_otantik_dedupe_recent()), falling back to the bare phone number.
 */
function wifi_otantik_wa_avatar($name, $phone)
{
    $letter = '';
    foreach (preg_split('//u', trim((string)$name), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
        if (preg_match('/[\p{L}\p{N}]/u', $ch)) {
            $letter = mb_strtoupper($ch);
            break;
        }
    }
    if ($letter === '') {
        $letter = '#';
    }
    $palette = ['wo-wa-c1', 'wo-wa-c2', 'wo-wa-c3', 'wo-wa-c4', 'wo-wa-c5', 'wo-wa-c6', 'wo-wa-c7', 'wo-wa-c8'];
    $sum = 0;
    foreach (str_split((string)$phone) as $ch) {
        $sum += (int)$ch;
    }
    return ['letter' => $letter, 'color' => $palette[$sum % count($palette)]];
}

function wifi_otantik_wa_conv_name($phone)
{
    try {
        $cust = ORM::for_table('tbl_customers')->where('phonenumber', $phone)->find_one();
        if ($cust) {
            $full = trim((string)($cust['fullname'] ?? ''));
            if ($full !== '') {
                return $full;
            }
        }
    } catch (Throwable $e) {
    }
    // Their own WhatsApp display name, captured free from the webhook -
    // the best fallback when we have no name on file for this customer.
    if (class_exists('OtantikWhatsApp')) {
        $waName = OtantikWhatsApp::contact_name($phone);
        if ($waName !== '') {
            return $waName;
        }
    }
    try {
        if (isset($cust) && $cust && (string)$cust['username'] !== '') {
            return (string)$cust['username'];
        }
    } catch (Throwable $e) {
    }
    try {
        $trx = ORM::for_table('tbl_payment_gateway')->where('username', $phone)->order_by_desc('id')->find_one();
        if ($trx) {
            $rechByTrx = ORM::for_table('tbl_user_recharges')->where('trx_id', (int)$trx['id'])->find_one();
            if ($rechByTrx && (string)$rechByTrx['username'] !== '') {
                return (string)$rechByTrx['username'];
            }
        }
    } catch (Throwable $e) {
    }
    return $phone;
}

/**
 * The WhatsApp 24-hour customer service window: a free-form reply is only
 * allowed within 24h of the customer's last inbound message: outside that,
 * only an approved template can re-open the conversation.
 */
function wifi_otantik_wa_window($phone)
{
    try {
        $last = ORM::for_table('tbl_otantik_wa_logs')
            ->where('phone', $phone)
            ->where('direction', 'in')
            ->order_by_desc('created_at')
            ->find_one();
    } catch (Throwable $e) {
        $last = null;
    }
    if (!$last) {
        return ['open' => false, 'remaining' => 0, 'last_in_at' => ''];
    }
    $deadline = strtotime((string)$last['created_at']) + 86400;
    $remaining = $deadline - time();
    return [
        'open' => $remaining > 0,
        'remaining' => max(0, $remaining),
        'last_in_at' => (string)$last['created_at'],
    ];
}

/** Shared shape for a single tbl_otantik_wa_logs row, used by the initial thread render and the send/poll AJAX endpoints so they stay in sync. */
function wifi_otantik_wa_thread_row($r)
{
    $kind = (string)($r['kind'] ?? '');
    $tpl = (string)($r['tpl'] ?? '');
    if ($kind === 'in') {
        $label = 'Message reçu';
    } elseif ($kind === 'session') {
        $label = 'Réponse (fenêtre 24 h)';
    } else {
        $label = class_exists('OtantikWhatsApp') ? OtantikWhatsApp::tpl_label($tpl) : $tpl;
    }
    $ts = strtotime((string)($r['created_at'] ?? ''));
    return [
        'id' => (int)($r['id'] ?? 0),
        'direction' => (string)($r['direction'] ?? 'out'),
        'label' => $label,
        'detail' => (string)($r['detail'] ?? ''),
        'ok' => (int)($r['ok'] ?? 0) === 1,
        'created_at' => (string)($r['created_at'] ?? ''),
        'day' => $ts ? date('Y-m-d', $ts) : '',
        'time' => $ts ? date('H:i', $ts) : '',
    ];
}

/**
 * One page of conversations (grouped by phone, most recent first), scoped
 * the same way the thread/send actions already are. Shared by the initial
 * page render and the "charger plus" AJAX endpoint so both agree on paging.
 */
function wifi_otantik_wa_convo_page($scope, $offset, $limit, $sel, $search = '')
{
    $convos = [];
    $hasMore = false;
    try {
        $q = ORM::for_table('tbl_otantik_wa_logs')
            ->select('phone')
            ->select_expr('MAX(created_at)', 'last_at')
            ->select_expr('MAX(id)', 'last_id')
            ->group_by('phone')
            ->order_by_desc('last_at');
        if ($scope !== null) {
            $q->where_in('phone', $scope ?: ['__none__']);
        }
        if ($search !== '') {
            $q->where_like('phone', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%');
        }
        $rows = $q->offset(max(0, (int)$offset))->limit((int)$limit + 1)->find_array();
        $hasMore = count($rows) > (int)$limit;
        $rows = array_slice($rows, 0, (int)$limit);
        $lastIds = array_map('intval', array_column($rows, 'last_id'));
        $previews = [];
        if ($lastIds) {
            foreach (ORM::for_table('tbl_otantik_wa_logs')->where_in('id', $lastIds)->find_array() as $p) {
                $previews[(int)$p['id']] = $p;
            }
        }
        foreach ($rows as $r) {
            $p = $previews[(int)$r['last_id']] ?? null;
            $phone = (string)$r['phone'];
            $name = wifi_otantik_wa_conv_name($phone);
            $avatar = wifi_otantik_wa_avatar($name, $phone);
            $convos[] = [
                'phone' => $phone,
                'name' => $name,
                'avatar_letter' => $avatar['letter'],
                'avatar_color' => $avatar['color'],
                'ago' => function_exists('wifi_otantik_ago') ? wifi_otantik_ago(strtotime((string)$r['last_at']) ?: 0) : (string)$r['last_at'],
                'preview' => $p ? mb_substr((string)$p['detail'], 0, 60) : '',
                'direction' => $p ? (string)$p['direction'] : 'out',
                'active' => $sel !== '' && $sel === $phone,
            ];
        }
    } catch (Throwable $e) {
    }
    return ['rows' => $convos, 'has_more' => $hasMore];
}

function wifi_otantik_wa_inbox()
{
    global $admin, $config, $routes;
    _admin();
    Tenant::require_can('network', $admin);
    Tenant::ensure_schema();
    if (!class_exists('OtantikWhatsApp') || !OtantikWhatsApp::premium_has_access($admin)) {
        wifi_otantik_ui('plugin/otantik_wa_locked.tpl', 'WhatsApp Premium', []);
        return;
    }

    $scope = wifi_otantik_wa_scope_phones($admin);
    $pageSize = 40;

    if ((string)($routes[2] ?? '') === 'more') {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $offset = (int)_get('offset');
        $search = preg_replace('/\D+/', '', (string)_get('q'));
        $sel = preg_replace('/\D+/', '', (string)_get('phone'));
        $page = wifi_otantik_wa_convo_page($scope, $offset, $pageSize, $sel, $search);
        echo json_encode(['ok' => true, 'rows' => $page['rows'], 'has_more' => $page['has_more']]);
        exit;
    }

    if ((string)($routes[2] ?? '') === 'poll') {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $phone = preg_replace('/\D+/', '', (string)_get('phone'));
        $afterId = (int)_get('after');
        if ($phone === '' || ($scope !== null && !in_array($phone, $scope, true))) {
            echo json_encode(['ok' => false, 'rows' => [], 'window' => ['open' => false, 'remaining' => 0, 'last_in_at' => '']]);
            exit;
        }
        $rows = [];
        try {
            $q = ORM::for_table('tbl_otantik_wa_logs')->where('phone', $phone);
            if ($afterId > 0) {
                $q->where_gt('id', $afterId);
            }
            foreach ($q->order_by_asc('id')->limit(100)->find_array() as $r) {
                $rows[] = wifi_otantik_wa_thread_row($r);
            }
        } catch (Throwable $e) {
        }
        echo json_encode(['ok' => true, 'rows' => $rows, 'window' => wifi_otantik_wa_window($phone)]);
        exit;
    }

    if ((string)($routes[2] ?? '') === 'send') {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $phone = preg_replace('/\D+/', '', (string)_post('wa_phone'));
        $text = trim((string)_post('wa_text'));
        if ($phone === '' || $text === '' || !class_exists('OtantikWhatsApp')) {
            echo json_encode(['ok' => false, 'error' => 'Message vide.', 'row' => null, 'window' => wifi_otantik_wa_window($phone)]);
            exit;
        }
        if ($scope !== null && !in_array($phone, $scope, true)) {
            echo json_encode(['ok' => false, 'error' => 'Ce numéro n’est associé à aucun de vos clients.', 'row' => null, 'window' => wifi_otantik_wa_window($phone)]);
            exit;
        }
        $ok = OtantikWhatsApp::send_session($phone, $text);
        $row = null;
        try {
            $last = ORM::for_table('tbl_otantik_wa_logs')->where('phone', $phone)->order_by_desc('id')->find_one();
            if ($last) {
                $row = wifi_otantik_wa_thread_row($last);
            }
        } catch (Throwable $e) {
        }
        echo json_encode([
            'ok' => $ok,
            'error' => $ok ? '' : (OtantikWhatsApp::$lastError !== '' ? OtantikWhatsApp::$lastError : 'Erreur inconnue'),
            'row' => $row,
            'window' => wifi_otantik_wa_window($phone),
        ]);
        exit;
    }

    if (_post('wa_start') && class_exists('OtantikWhatsApp')) {
        $phone = preg_replace('/\D+/', '', (string)_post('wa_phone'));
        if ($phone === '' || strlen($phone) < 8) {
            r2(getUrl('plugin/wifi_otantik_wa_inbox'), 'e', 'Numéro invalide');
        }
        if ($scope !== null && !in_array($phone, $scope, true)) {
            r2(getUrl('plugin/wifi_otantik_wa_inbox'), 'e', 'Ce numéro n’est associé à aucun de vos clients');
        }
        $ok = OtantikWhatsApp::send_tpl($phone, 'wo_hello', ['client', 'Wifi-Otantik'], [], 'inbox_start', 0, 0);
        r2(
            getUrl('plugin/wifi_otantik_wa_inbox') . '&phone=' . rawurlencode($phone),
            $ok ? 's' : 'e',
            $ok ? 'Conversation démarrée.' : ('Échec : ' . (OtantikWhatsApp::$lastError !== '' ? OtantikWhatsApp::$lastError : 'erreur'))
        );
    }

    if (_post('wa_send') && class_exists('OtantikWhatsApp')) {
        $phone = preg_replace('/\D+/', '', (string)_post('wa_phone'));
        $text = trim((string)_post('wa_text'));
        if ($phone !== '' && $text !== '' && ($scope === null || in_array($phone, $scope, true))) {
            $ok = OtantikWhatsApp::send_session($phone, $text);
            r2(
                getUrl('plugin/wifi_otantik_wa_inbox') . '&phone=' . rawurlencode($phone),
                $ok ? 's' : 'e',
                $ok ? 'Message envoyé.' : ('Échec : ' . (OtantikWhatsApp::$lastError !== '' ? OtantikWhatsApp::$lastError : 'erreur'))
            );
        }
    }
    if (_post('wa_reopen') && class_exists('OtantikWhatsApp')) {
        $phone = preg_replace('/\D+/', '', (string)_post('wa_phone'));
        if ($phone !== '' && ($scope === null || in_array($phone, $scope, true))) {
            $ok = OtantikWhatsApp::send_tpl($phone, 'wo_hello', ['client', 'Wifi-Otantik'], [], 'inbox_reopen', 0, 0);
            r2(
                getUrl('plugin/wifi_otantik_wa_inbox') . '&phone=' . rawurlencode($phone),
                $ok ? 's' : 'e',
                $ok ? 'Modèle wo_hello envoyé.' : ('Échec : ' . (OtantikWhatsApp::$lastError !== '' ? OtantikWhatsApp::$lastError : 'erreur'))
            );
        }
    }

    $sel = preg_replace('/\D+/', '', (string)_get('phone'));
    if ($sel !== '' && $scope !== null && !in_array($sel, $scope, true)) {
        $sel = '';
    }

    $page = wifi_otantik_wa_convo_page($scope, 0, $pageSize, $sel);
    $convos = $page['rows'];
    // The selected conversation might sit beyond the first page (e.g. a
    // direct link, or reached via "charger plus") - make sure it's always
    // represented at the top of the list rather than missing entirely.
    if ($sel !== '' && !array_filter($convos, function ($c) use ($sel) {
        return $c['phone'] === $sel;
    })) {
        $selPage = wifi_otantik_wa_convo_page($scope, 0, 1, $sel, $sel);
        if ($selPage['rows']) {
            array_unshift($convos, $selPage['rows'][0]);
        }
    }

    $thread = [];
    $selName = $sel;
    if ($sel !== '') {
        $selName = wifi_otantik_wa_conv_name($sel);
        try {
            foreach (ORM::for_table('tbl_otantik_wa_logs')->where('phone', $sel)->order_by_asc('created_at')->limit(200)->find_array() as $r) {
                $thread[] = wifi_otantik_wa_thread_row($r);
            }
        } catch (Throwable $e) {
        }
    }

    $selAvatar = $sel !== '' ? wifi_otantik_wa_avatar($selName, $sel) : ['letter' => '', 'color' => 'wo-wa-c1'];
    $lastRow = $thread ? $thread[count($thread) - 1] : null;
    wifi_otantik_ui('plugin/otantik_wa_inbox.tpl', 'Boîte de réception WhatsApp', [
        'convos' => $convos,
        'convos_has_more' => $page['has_more'],
        'convos_page_size' => $pageSize,
        'sel' => $sel,
        'sel_name' => $selName,
        'sel_avatar_letter' => $selAvatar['letter'],
        'sel_avatar_color' => $selAvatar['color'],
        'thread' => $thread,
        'thread_last_id' => $lastRow ? $lastRow['id'] : 0,
        'thread_last_day' => $lastRow ? $lastRow['day'] : '',
        'wa_window_json' => json_encode($sel !== '' ? wifi_otantik_wa_window($sel) : null),
        'wa_ready' => class_exists('OtantikWhatsApp') && OtantikWhatsApp::ready(),
        'wa_last_error' => (string)($config['otantik_wa_last_error'] ?? ''),
        'is_platform' => Tenant::is_platform($admin),
    ]);
}

function wifi_otantik_pg_hook()
{
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? ''));
    if ($method === 'GET' || $method === 'HEAD') {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'ok';
        exit;
    }
    if ($method !== 'POST') {
        http_response_code(405);
        echo 'method';
        exit;
    }
    $raw = (string)file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }
    $secret = OtantikPay::cfg('fapshi_webhook_secret');
    $fapshiHdr = (string)($_SERVER['HTTP_X_WH_SECRET'] ?? '');
    $looksFapshi = $fapshiHdr !== '' || isset($payload['transId']);
    if ($looksFapshi && $secret !== '') {
        if (!hash_equals($secret, $fapshiHdr)) {
            http_response_code(403);
            echo 'forbidden';
            exit;
        }
    }
    try {
        OtantikPay::handle_webhook($payload);
    } catch (Throwable $e) {
        http_response_code(500);
        echo 'error';
        exit;
    }
    echo 'ok';
    exit;
}

function wifi_otantik_pg_finish_withdraw($row)
{
    if (!$row || strtoupper((string)$row['status']) !== 'PENDING') {
        return false;
    }
    $tenant = Tenant::tenant_row((int)$row['owner_id']);
    $amount = (float)$row['amount'];
    if (!$tenant || $amount > (float)$tenant['wallet']) {
        $row->status = 'FAILED';
        $row->message = $tenant ? 'Solde insuffisant au moment du virement' : 'Compte introuvable';
        $row->save();
        if (class_exists('OtantikWhatsApp')) {
            try {
                OtantikWhatsApp::notify_withdraw($row['receiver'], $amount, 'FAILED', (string)($row['mesomb_ref'] ?? ''));
            } catch (Throwable $e) {
            }
        }
        return false;
    }
    $tenant->wallet = (float)$tenant['wallet'] - $amount;
    $tenant->save();
    $row->status = 'SUCCESS';
    $row->save();
    if (class_exists('OtantikErp')) {
        OtantikErp::sync_withdrawal($row);
    }
    if (class_exists('OtantikWhatsApp')) {
        try {
            OtantikWhatsApp::notify_withdraw($row['receiver'], $amount, 'SUCCESS', (string)($row['mesomb_ref'] ?? ''));
        } catch (Throwable $e) {
        }
    }
    return true;
}

function wifi_otantik_wg($args)
{
    $bin = '/usr/local/sbin/otantik-wg';
    $cmd = 'sudo -n ' . escapeshellarg($bin);
    foreach ($args as $a) {
        $cmd .= ' ' . escapeshellarg((string)$a);
    }
    $out = [];
    $code = 0;
    exec($cmd . ' 2>&1', $out, $code);
    return [$code, trim(implode("\n", $out))];
}

function wifi_otantik_ppp($args)
{
    $bin = '/usr/local/sbin/otantik-ppp';
    $cmd = 'sudo -n ' . escapeshellarg($bin);
    foreach ($args as $a) {
        $cmd .= ' ' . escapeshellarg((string)$a);
    }
    $out = [];
    $code = 0;
    exec($cmd . ' 2>&1', $out, $code);
    return [$code, trim(implode("\n", $out))];
}

function wifi_otantik_next_ppp_ip()
{
    Tenant::ensure_schema();
    $used = [];
    foreach (ORM::for_table('tbl_otantik_peers')->find_many() as $p) {
        if (!empty($p['ppp_ip'])) {
            $used[$p['ppp_ip']] = true;
        }
    }
    for ($i = 1; $i < 65000; $i++) {
        $a = (int)floor($i / 254);
        $b = ($i % 254) + 1;
        if ($b === 0) {
            continue;
        }
        $ip = '10.89.' . $a . '.' . $b;
        if (empty($used[$ip])) {
            return $ip;
        }
    }
    throw new Exception('Plus d\'IP L2TP disponibles');
}

function wifi_otantik_ensure_ppp($peer)
{
    if (!$peer) {
        return $peer;
    }
    if (trim((string)$peer['ppp_user']) !== '' && trim((string)$peer['ppp_ip']) !== '') {
        return $peer;
    }
    $peer->ppp_user = 'wo' . (int)$peer['router_id'];
    $peer->ppp_pass = bin2hex(random_bytes(8));
    $peer->ppp_ip = wifi_otantik_next_ppp_ip();
    $peer->save();
    wifi_otantik_ppp(['add', $peer['ppp_user'], $peer['ppp_pass'], $peer['ppp_ip']]);
    return $peer;
}

function wifi_otantik_next_ip()
{
    Tenant::ensure_schema();
    $used = [];
    foreach (ORM::for_table('tbl_otantik_peers')->find_many() as $p) {
        $used[$p['wg_ip']] = true;
    }
    for ($i = 2; $i < 65000; $i++) {
        $a = (int)floor($i / 254);
        $b = ($i % 254) + 1;
        if ($b === 0) {
            continue;
        }
        $ip = '10.88.' . $a . '.' . $b;
        if (empty($used[$ip])) {
            return $ip;
        }
    }
    throw new Exception('Plus d\'IP WireGuard disponibles');
}

function wifi_otantik_dedupe_recent($rows)
{
    $seen = [];
    $out = [];
    $issuerNames = [];
    foreach ($rows as $r) {
        $key = strtolower((string)$r['username'] . '|' . $r['plan_name'] . '|' . $r['routers'] . '|' . (int)$r['status']);
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $ts = strtotime($r['paid_date'] ?: $r['created_date']);
        // tbl_payment_gateway.username is the phone number used to pay, not
        // the memorable ticket/voucher code shown to the customer - that
        // lives on the linked recharge row (created once provisioning
        // succeeds). Falls back to the phone when no recharge exists yet
        // (pending/failed sale, or one still waiting on reconciliation).
        $ticket = '';
        $issuer = '';
        try {
            $rech = ORM::for_table('tbl_user_recharges')->where('trx_id', (int)$r['id'])->find_one();
            if ($rech) {
                $ticket = (string)$rech['username'];
                $aid = (int)$rech['admin_id'];
                if ($aid > 0) {
                    if (!array_key_exists($aid, $issuerNames)) {
                        $u = ORM::for_table('tbl_users')->find_one($aid);
                        $issuerNames[$aid] = $u ? ((string)$u['fullname'] ?: (string)$u['username']) : '';
                    }
                    $issuer = $issuerNames[$aid];
                }
            }
        } catch (Throwable $e) {
        }
        $out[] = [
            'username' => $r['username'],
            'ticket' => $ticket !== '' ? $ticket : (string)$r['username'],
            'issuer' => $issuer,
            'routers' => $r['routers'],
            'plan_name' => $r['plan_name'],
            'payment_method' => $r['payment_method'],
            'price' => $r['price'],
            'status' => (int)$r['status'],
            'ago' => wifi_otantik_ago($ts),
        ];
        if (count($out) >= 8) {
            break;
        }
    }
    return $out;
}

function wifi_otantik_pay_busy($phone, $planId, $routerName)
{
    $since = date('Y-m-d H:i:s', time() - 90);
    $row = ORM::for_table('tbl_payment_gateway')
        ->where_in('gateway', ['mesomb', 'fapshi'])
        ->where('username', $phone)
        ->where('plan_id', (int)$planId)
        ->where('routers', $routerName)
        ->where_gte('created_date', $since)
        ->order_by_desc('id')
        ->find_one();
    if (!$row) {
        return '';
    }
    $st = (int)$row['status'];
    if ($st === 2) {
        return 'Ce forfait vient d’être payé pour ce numéro.';
    }
    if ($st === 1) {
        return 'Paiement déjà en cours. Patientez un instant.';
    }
    return 'Dernière tentative trop récente. Réessayez dans une minute.';
}

function wifi_otantik_initials($admin)
{
    $n = trim((string)($admin['fullname'] ?? $admin['username'] ?? 'WO'));
    $parts = preg_split('/\s+/', $n);
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return strtoupper(substr($n, 0, 2));
}

function wifi_otantik_range()
{
    $r = _get('range') ?: 'year';
    return in_array($r, ['today', 'week', 'month', 'year'], true) ? $r : 'year';
}

function wifi_otantik_range_start($range)
{
    if ($range === 'week') {
        return date('Y-m-d 00:00:00', strtotime('monday this week'));
    }
    if ($range === 'month') {
        return date('Y-m-01 00:00:00');
    }
    if ($range === 'year') {
        return date('Y-01-01 00:00:00');
    }
    return date('Y-m-d 00:00:00');
}

function wifi_otantik_sales_query($admin)
{
    $names = Tenant::router_names($admin);
    $q = ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways());
    if ($names) {
        $q->where_in('routers', $names);
    } elseif (!Tenant::is_platform($admin)) {
        $q->where('routers', '__none__');
    }
    return $q;
}

function wifi_otantik_customers_query($admin)
{
    $q = ORM::for_table('tbl_customers');
    if (!Tenant::is_platform($admin)) {
        $q->where('created_by', Tenant::id($admin));
    }
    return $q;
}

function wifi_otantik_clients_in_range($admin, $a, $b)
{
    return (int)wifi_otantik_customers_query($admin)->where_gte('created_at', $a)->where_lt('created_at', $b)->count();
}

function wifi_otantik_client_growth($admin, $withSeries = true)
{
    $out = [
        'clients_total' => 0,
        'clients_today' => 0,
        'clients_week' => 0,
        'clients_prev_week' => 0,
        'clients_month' => 0,
        'clients_prev_month' => 0,
        'clients_delta' => 0,
        'clients_delta_month' => 0,
        'clients_series' => [],
        'clients_week_bars' => [],
        'clients_month_bars' => [],
    ];
    try {
        $out['clients_total'] = (int)wifi_otantik_customers_query($admin)->count();
        $todayStart = date('Y-m-d 00:00:00');
        $weekStart = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $prevWeekStart = date('Y-m-d 00:00:00', strtotime('-14 days'));
        $monthStart = date('Y-m-01 00:00:00');
        $prevMonthStart = date('Y-m-01 00:00:00', strtotime('first day of -1 month'));
        $out['clients_today'] = wifi_otantik_clients_in_range($admin, $todayStart, date('Y-m-d 00:00:00', strtotime('+1 day')));
        $out['clients_week'] = (int)wifi_otantik_customers_query($admin)->where_gte('created_at', $weekStart)->count();
        $out['clients_prev_week'] = wifi_otantik_clients_in_range($admin, $prevWeekStart, $weekStart);
        $out['clients_month'] = (int)wifi_otantik_customers_query($admin)->where_gte('created_at', $monthStart)->count();
        $out['clients_prev_month'] = wifi_otantik_clients_in_range($admin, $prevMonthStart, $monthStart);
        $prev = (int)$out['clients_prev_week'];
        $week = (int)$out['clients_week'];
        $out['clients_delta'] = $prev > 0 ? round((($week - $prev) / $prev) * 100, 1) : ($week > 0 ? 100 : 0);
        $pm = (int)$out['clients_prev_month'];
        $cm = (int)$out['clients_month'];
        $out['clients_delta_month'] = $pm > 0 ? round((($cm - $pm) / $pm) * 100, 1) : ($cm > 0 ? 100 : 0);
        if ($withSeries) {
            $mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime('-' . $i . ' days'));
                $a = $d . ' 00:00:00';
                $b = date('Y-m-d', strtotime($d . ' +1 day'));
                $n = wifi_otantik_clients_in_range($admin, $a, $b . ' 00:00:00');
                $out['clients_series'][] = $n;
                $out['clients_week_bars'][] = [
                    'label' => date('d', strtotime($d)) . ' ' . $mois[(int)date('n', strtotime($d)) - 1],
                    'from' => $d,
                    'to' => $b,
                    'n' => $n,
                    'title' => date('d', strtotime($d)) . ' ' . $mois[(int)date('n', strtotime($d)) - 1] . ' ' . date('Y', strtotime($d)),
                ];
            }
            for ($i = 11; $i >= 0; $i--) {
                $ts = strtotime('first day of -' . $i . ' month');
                $a = date('Y-m-01', $ts);
                $b = date('Y-m-01', strtotime('+1 month', $ts));
                $n = wifi_otantik_clients_in_range($admin, $a . ' 00:00:00', $b . ' 00:00:00');
                $out['clients_month_bars'][] = [
                    'label' => $mois[(int)date('n', $ts) - 1],
                    'from' => $a,
                    'to' => $b,
                    'n' => $n,
                    'title' => $mois[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts),
                ];
            }
        }
    } catch (Throwable $e) {
    }
    return $out;
}

function wifi_otantik_grow_bars($series, $baseUrl)
{
    $series = array_values((array)$series);
    if (!$series) {
        return '<div class="wo-chart-hint">Les inscriptions s’afficheront ici</div>';
    }
    $max = 1;
    foreach ($series as $s) {
        $max = max($max, (int)($s['n'] ?? 0));
    }
    $html = '<div class="wo-bars" role="list">';
    foreach ($series as $s) {
        $n = (int)($s['n'] ?? 0);
        $pct = $n > 0 ? max(8, round(($n / $max) * 100)) : 3;
        $href = $baseUrl . '&from=' . rawurlencode((string)$s['from']) . '&to=' . rawurlencode((string)$s['to']);
        $title = (string)($s['title'] ?? $s['label']) . ' · ' . $n . ' abonné' . ($n > 1 ? 's' : '');
        $html .= '<a class="wo-bar-col" role="listitem" href="' . htmlspecialchars($href) . '" title="' . htmlspecialchars($title) . '">';
        $html .= '<span class="wo-bar-track"><i style="height:' . $pct . '%"></i></span>';
        $html .= '<b>' . $n . '</b>';
        $html .= '<small>' . htmlspecialchars((string)$s['label']) . '</small>';
        $html .= '</a>';
    }
    return $html . '</div>';
}

function wifi_otantik_ymd($s)
{
    $s = trim((string)$s);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
        return '';
    }
    $ts = strtotime($s . ' 00:00:00');
    return $ts ? date('Y-m-d', $ts) : '';
}

function wifi_otantik_q($key = 'q')
{
    return trim((string)_get($key));
}

function wifi_otantik_where_search($query, array $columns, $q)
{
    $q = trim((string)$q);
    if ($q === '' || !$columns) {
        return $query;
    }
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    $parts = [];
    $args = [];
    foreach ($columns as $col) {
        $parts[] = $col . ' LIKE ?';
        $args[] = $like;
    }
    return $query->where_raw('(' . implode(' OR ', $parts) . ')', $args);
}

function wifi_otantik_current_route()
{
    global $routes;
    $bits = [];
    for ($i = 1; $i < 8; $i++) {
        if (!isset($routes[$i]) || (string)$routes[$i] === '') {
            break;
        }
        $bits[] = $routes[$i];
    }
    return $bits ? ('plugin/' . implode('/', $bits)) : 'plugin/wifi_otantik_home';
}

function wifi_otantik_pager($total, $page = null, $per = 20, $baseUrl = '', $param = 'page')
{
    $total = max(0, (int)$total);
    $per = max(1, min(100, (int)$per));
    $pages = $total > 0 ? (int)ceil($total / $per) : 1;
    $page = $page === null ? (int)_get($param) : (int)$page;
    $page = max(1, $page);
    if ($page > $pages) {
        $page = $pages;
    }
    $offset = ($page - 1) * $per;
    $from = $total ? ($offset + 1) : 0;
    $to = min($total, $offset + $per);
    $param = preg_replace('/[^a-z0-9_]/i', '', (string)$param) ?: 'page';
    $keep = [];
    foreach ($_GET as $k => $v) {
        if ($k === '_route' || $k === $param || is_array($v)) {
            continue;
        }
        $keep[(string)$k] = (string)$v;
    }
    $route = wifi_otantik_current_route();
    if ($baseUrl === '') {
        $baseUrl = getUrl($route);
    }
    foreach ($keep as $k => $v) {
        if ($v === '') {
            continue;
        }
        $baseUrl .= ((strpos($baseUrl, '?') === false) ? '?' : '&') . rawurlencode($k) . '=' . rawurlencode($v);
    }
    $sep = (strpos($baseUrl, '?') === false) ? '?' : '&';
    $nums = [];
    $start = max(1, $page - 2);
    $end = min($pages, $page + 2);
    if ($start > 1) {
        $nums[] = 1;
        if ($start > 2) {
            $nums[] = 0;
        }
    }
    for ($i = $start; $i <= $end; $i++) {
        $nums[] = $i;
    }
    if ($end < $pages) {
        if ($end < $pages - 1) {
            $nums[] = 0;
        }
        $nums[] = $pages;
    }
    $formKeep = $keep;
    unset($formKeep['q'], $formKeep['st'], $formKeep['ty'], $formKeep['bq']);
    return [
        'total' => $total,
        'page' => $page,
        'pages' => $pages,
        'per' => $per,
        'offset' => $offset,
        'from' => $from,
        'to' => $to,
        'param' => $param,
        'url' => $baseUrl,
        'sep' => $sep,
        'nums' => $nums,
        'show' => $total > 0,
        'route' => $route,
        'keep' => $formKeep,
        'prev' => max(1, $page - 1),
        'next' => min($pages, $page + 1),
    ];
}

function wifi_otantik_paged($query, array $pager)
{
    return $query->limit((int)$pager['per'])->offset((int)$pager['offset']);
}

function wifi_otantik_spark_svg($vals)
{
    $vals = array_values(array_map('floatval', (array)$vals));
    if (!$vals) {
        $vals = [0];
    }
    $w = 132;
    $h = 28;
    $n = count($vals);
    $max = max(1, max($vals));
    $gap = 2.2;
    $bw = max(3, ($w - $gap * ($n - 1)) / $n);
    $html = '<svg class="wo-spark" viewBox="0 0 ' . $w . ' ' . $h . '" width="100%" height="' . $h . '" preserveAspectRatio="none" aria-hidden="true">';
    foreach ($vals as $i => $v) {
        $bh = max(2, ($v / $max) * ($h - 2));
        $x = round($i * ($bw + $gap), 1);
        $y = round($h - $bh, 1);
        $html .= '<rect x="' . $x . '" y="' . $y . '" width="' . round($bw, 1) . '" height="' . round($bh, 1) . '" rx="1.6" fill="#f05018"/>';
    }
    return $html . '</svg>';
}

function wifi_otantik_dashboard_stats($admin)
{
    $range = wifi_otantik_range();
    $start = wifi_otantik_range_start($range);
    $monthStart = date('Y-m-01 00:00:00');
    $todayStart = date('Y-m-d 00:00:00');
    $todayRev = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where_gte('paid_date', $todayStart)->sum('price');
    $todayCount = (int)wifi_otantik_sales_query($admin)->where('status', 2)->where_gte('paid_date', $todayStart)->count();
    $monthRev = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where_gte('paid_date', $monthStart)->sum('price');
    $rangeRev = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where_gte('paid_date', $start)->sum('price');
    $wdSum = (float)ORM::for_table('tbl_otantik_withdrawals')->where('owner_id', Tenant::id($admin))->where_gte('created_at', $start)->sum('amount');
    $labels = [];
    $salesSeries = [];
    $wdSeries = [];
    if ($range === 'today') {
        for ($h = 0; $h < 24; $h += 3) {
            $labels[] = sprintf('%02dh', $h);
            $a = date('Y-m-d ') . sprintf('%02d:00:00', $h);
            $b = date('Y-m-d ') . sprintf('%02d:00:00', min(24, $h + 3));
            $sq = wifi_otantik_sales_query($admin)->where('status', 2)->where_gte('paid_date', $a);
            $wq = ORM::for_table('tbl_otantik_withdrawals')->where('owner_id', Tenant::id($admin))->where_gte('created_at', $a);
            if ($h + 3 < 24) {
                $sq->where_lt('paid_date', $b);
                $wq->where_lt('created_at', $b);
            }
            $salesSeries[] = (float)$sq->sum('price');
            $wdSeries[] = (float)$wq->sum('amount');
        }
    } else {
        $days = $range === 'week' ? 7 : max(1, (int)date('j'));
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime('-' . $i . ' days'));
            $labels[] = date('d/m', strtotime($d));
            $a = $d . ' 00:00:00';
            $b = date('Y-m-d 00:00:00', strtotime($d . ' +1 day'));
            $salesSeries[] = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where_gte('paid_date', $a)->where_lt('paid_date', $b)->sum('price');
            $wdSeries[] = (float)ORM::for_table('tbl_otantik_withdrawals')->where('owner_id', Tenant::id($admin))->where_gte('created_at', $a)->where_lt('created_at', $b)->sum('amount');
        }
    }
    $byZone = [];
    foreach (Tenant::router_names($admin) as $name) {
        $byZone[] = [
            'zone' => $name,
            'total' => (float)ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways())->where('status', 2)->where('routers', $name)->where_gte('paid_date', $start)->sum('price'),
        ];
    }
    $names = Tenant::router_names($admin);
    $recent = $names
        ? ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways())->where_in('routers', $names)->order_by_expr('COALESCE(paid_date, created_date) DESC')->limit(40)->find_many()
        : [];
    return array_merge([
        'range' => $range,
        'today_rev' => $todayRev,
        'today_count' => $todayCount,
        'month_rev' => $monthRev,
        'range_rev' => $rangeRev,
        'wd_sum' => $wdSum,
        'chart_labels' => $labels,
        'chart_sales' => $salesSeries,
        'chart_wd' => $wdSeries,
        'by_zone' => $byZone,
        'recent' => $recent,
    ], wifi_otantik_client_growth($admin, true));
}

function wifi_otantik_ui($tpl, $title, $vars = [])
{
    global $ui, $admin, $config, $routes;
    Tenant::ensure_schema();
    $ui->assign('_title', $title);
    $ui->assign('_admin', $admin);
    $ui->assign('_system_menu', 'plugin');
    $ui->assign('otantik_is_platform', Tenant::is_platform($admin));
    $ui->assign('otantik_can_dashboard', Tenant::can('dashboard', $admin));
    $ui->assign('otantik_can_sales', Tenant::can('sales', $admin));
    $ui->assign('otantik_can_network', Tenant::can('network', $admin));
    $ui->assign('otantik_can_finance_view', Tenant::can('finance_view', $admin));
    $ui->assign('otantik_can_finance_manage', Tenant::can('finance_manage', $admin));
    $ui->assign('otantik_can_staff_manage', Tenant::can('staff_manage', $admin));
    $ui->assign('otantik_nav', $routes[1] ?? 'wifi_otantik_home');
    $ui->assign('otantik_initials', wifi_otantik_initials($admin ?: []));
    $ui->assign('otantik_range', wifi_otantik_range());
    $pendingPeer = $admin ? (int)ORM::for_table('tbl_otantik_peers')->where('owner_id', Tenant::id($admin))->where('status', 'pending')->count() : 0;
    $pendingKyc = 0;
    if ($admin && Tenant::is_platform($admin)) {
        try {
            $pendingKyc = (int)ORM::for_table('tbl_otantik_tenants')->where('kyc_status', 'pending')->count();
        } catch (Throwable $e) {
        }
    }
    $ui->assign('otantik_kyc_pending', $pendingKyc);
    $pendingInbox = 0;
    if ($admin) {
        try {
            $pendingInbox = (int)ORM::for_table('tbl_otantik_inbox')->where('owner_id', Tenant::id($admin))->where('seen', 0)->count();
        } catch (Throwable $e) {
        }
    }
    $ui->assign('otantik_inbox_pending', $pendingInbox);
    $ui->assign('otantik_notify', $pendingPeer + $pendingKyc + $pendingInbox);
    $alerts = [];
    if ($admin) {
        foreach (ORM::for_table('tbl_otantik_peers')->where('owner_id', Tenant::id($admin))->order_by_desc('id')->limit(12)->find_many() as $p) {
            $st = strtolower(trim((string)($p['status'] ?? '')));
            // Completed routers are normal — not alerts. Only pending / provisioning
            // / offline need attention in the bell panel.
            if ($st === '' || $st === 'completed' || $st === 'ok') {
                continue;
            }
            $rr = ORM::for_table('tbl_routers')->find_one($p['router_id']);
            $label = 'Hors ligne';
            $tone = 'no';
            if ($st === 'pending' || strpos($st, 'configur') === 0 || strpos($st, 'setting_') === 0) {
                $label = ($st === 'pending') ? 'À connecter' : 'Connexion en cours…';
                $tone = 'wait';
            }
            $alerts[] = [
                'name' => $rr ? $rr['name'] : ('Zone #' . $p['router_id']),
                'status' => $st,
                'label' => $label,
                'tone' => $tone,
                'href' => $rr ? (getUrl('plugin/wifi_otantik_routers/script/') . (int)$rr['id']) : getUrl('plugin/wifi_otantik_routers'),
                'seen' => (string)($p['last_seen'] ?? ''),
            ];
        }
    }
    $ui->assign('otantik_alerts', $alerts);
    $tForRate = null;
    if ($admin) {
        try {
            $tForRate = Tenant::tenant_row(Tenant::id($admin));
        } catch (Throwable $e) {
        }
    }
    $ui->assign('otantik_commission', Tenant::effective_commission($tForRate));
    $ui->assign('otantik_keep', 100 - Tenant::effective_commission($tForRate));
    $ui->assign('otantik_erp_on', class_exists('OtantikErp') && OtantikErp::has_access($admin));
    $ui->assign('otantik_wa_on', class_exists('OtantikWhatsApp') && OtantikWhatsApp::premium_has_access($admin));
    $ex = Tenant::split(1000, $tForRate);
    $ui->assign('otantik_example_net', $ex['net']);
    $ui->assign('otantik_example_fee', $ex['fee']);
    $wa = preg_replace('/\D+/', '', (string)($config['otantik_whatsapp'] ?? ''));
    $ui->assign('otantik_wa', $wa !== '' ? ('https://wa.me/' . $wa) : 'https://wa.me/?text=' . rawurlencode('Bonjour Wifi-Otantik'));
    $ui->assign('otantik_recover', Tenant::url_portal('/recuperer'));
    $avatarAdmin = $admin ?: [];
    $fresh = null;
    if (!empty($admin['id'])) {
        try {
            $fresh = ORM::for_table('tbl_users')->find_one((int)$admin['id']);
        } catch (Throwable $e) {
        }
    }
    $fullName = trim((string)($fresh ? ($fresh['fullname'] ?? '') : ($admin['fullname'] ?? '')));
    $userName = trim((string)($fresh ? ($fresh['username'] ?? '') : ($admin['username'] ?? '')));
    $emailName = trim((string)($fresh ? ($fresh['email'] ?? '') : ($admin['email'] ?? '')));
    $ui->assign('otantik_avatar', wifi_otantik_avatar_url($fresh ?: $avatarAdmin));
    $ui->assign('otantik_user_name', $fullName !== '' ? $fullName : ($userName !== '' ? $userName : 'Mon profil'));
    $phoneSrc = '';
    if ($admin) {
        try {
            $trow = Tenant::tenant_row(Tenant::id($admin));
            $phoneSrc = (string)($trow['phone'] ?? '');
        } catch (Throwable $e) {
        }
        if ($phoneSrc === '') {
            $phoneSrc = (string)($fresh ? ($fresh['phone'] ?? '') : ($admin['phone'] ?? ''));
        }
    }
    $phoneFmt = wifi_otantik_phone_fmt($phoneSrc);
    if ($phoneFmt === '—' || $phoneFmt === '-') {
        $phoneFmt = $emailName !== '' ? $emailName : $userName;
    }
    $ui->assign('otantik_phone', $phoneFmt !== '' ? $phoneFmt : 'Compléter le profil');
    $ui->assign('otantik_phone_raw', wifi_otantik_phone($phoneSrc));
    $zoneCount = 0;
    if ($admin) {
        try {
            $zoneCount = (int)ORM::for_table('tbl_routers')->where('owner_id', Tenant::id($admin))->count();
        } catch (Throwable $e) {
        }
    }
    $ui->assign('otantik_zone_count', $zoneCount);
    $ui->assign('app_url', defined('APP_URL') ? APP_URL : wifi_otantik_app_url());
    foreach ($vars as $k => $v) {
        $ui->assign($k, $v);
    }
    $ui->display(str_replace('plugin/', '', $tpl));
}

function wifi_otantik_ago($ts)
{
    if (!$ts) {
        return '—';
    }
    $d = time() - (int)$ts;
    if ($d < 60) {
        return 'À l’instant';
    }
    if ($d < 3600) {
        return floor($d / 60) . ' min';
    }
    if ($d < 86400) {
        return floor($d / 3600) . ' h';
    }
    return date('d/m H:i', $ts);
}

function wifi_otantik_area_svg($sales)
{
    $sales = array_values(array_map('floatval', (array)$sales));
    if (count($sales) < 2) {
        $sales = [0, 0];
    }
    $w = 640;
    $h = 168;
    $max = max(1, max($sales));
    $step = $w / (count($sales) - 1);
    $pts = [];
    foreach ($sales as $i => $v) {
        $pts[] = [round($i * $step, 1), round($h - 18 - ($v / $max) * ($h - 40), 1)];
    }
    $line = '';
    foreach ($pts as $i => $p) {
        $line .= ($i ? ' L' : 'M') . $p[0] . ' ' . $p[1];
    }
    $last = $pts[count($pts) - 1];
    $fill = $line . ' L' . $last[0] . ' ' . $h . ' L0 ' . $h . ' Z';
    return '<svg class="wo-svg-chart" viewBox="0 0 ' . $w . ' ' . $h . '" width="100%" height="' . $h . '" preserveAspectRatio="none" aria-hidden="true">'
        . '<defs><linearGradient id="woAreaG" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#002858" stop-opacity=".38"/><stop offset="100%" stop-color="#002858" stop-opacity="0"/></linearGradient></defs>'
        . '<path d="' . $fill . '" fill="url(#woAreaG)"/>'
        . '<path d="' . $line . '" fill="none" stroke="#002858" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>'
        . '</svg>';
}

/** Barres empilées : Connexion (bleu) + Autres (orange), hauteur = total du jour. */
function wifi_otantik_nice_axis_max($value, $divisions = 4)
{
    $value = max(1.0, (float)$value);
    $rough = $value / $divisions;
    $mag = pow(10, floor(log10($rough)));
    $norm = $rough / $mag;
    if ($norm <= 1) {
        $step = 1 * $mag;
    } elseif ($norm <= 2) {
        $step = 2 * $mag;
    } elseif ($norm <= 5) {
        $step = 5 * $mag;
    } else {
        $step = 10 * $mag;
    }
    return ceil($value / $step) * $step;
}

/**
 * The combined transaction log behind every revenue figure in this plugin:
 * historical ledger rows (tbl_transactions) plus live ERP-flow entries
 * strictly after the import cutover (same sources and cutover logic as
 * OtantikErp::tx_wifi_sum), voided rows included but flagged. Shared by
 * the revenue-chart drill-down and the Rapports page. $from/$to are
 * 'Y-m-d H:i:s' strings, $to exclusive.
 */
function wifi_otantik_ledger_rows($admin, $from, $to)
{
    $names = Tenant::is_platform($admin) ? null : (Tenant::router_names($admin) ?: ['__none__']);
    $routersLabel = $names ? implode(', ', $names) : '';
    $rows = [];

    $voidedTx = class_exists('OtantikErp') ? array_flip(OtantikErp::voided_ref_ids('transaction')) : [];
    $voidedFlow = class_exists('OtantikErp') ? array_flip(OtantikErp::voided_ref_ids('erp_flow')) : [];
    $voidReasons = [];
    try {
        foreach (ORM::for_table('tbl_otantik_voids')->find_many() as $v) {
            $voidReasons[$v['source'] . ':' . $v['ref_id']] = (string)$v['reason'];
        }
    } catch (Throwable $e) {
    }

    try {
        $q = ORM::for_table('tbl_transactions');
        if ($names !== null) {
            $q->where_in('routers', $names);
        }
        $q->where_raw('CONCAT(recharged_on, " ", recharged_time) >= ?', [$from]);
        $q->where_raw('CONCAT(recharged_on, " ", recharged_time) < ?', [$to]);
        foreach ($q->find_many() as $t) {
            $id = (int)$t['id'];
            $rows[] = [
                'id' => $id,
                'source' => 'transaction',
                'when' => (string)$t['recharged_on'] . ' ' . (string)$t['recharged_time'],
                'who' => (string)$t['username'],
                'label' => (string)$t['plan_name'],
                'method' => (string)$t['method'],
                'type' => (string)$t['type'],
                'routers' => (string)$t['routers'],
                'amount' => (float)$t['price'],
                'voided' => isset($voidedTx[$id]),
                'void_reason' => $voidReasons['transaction:' . $id] ?? '',
            ];
        }
    } catch (Throwable $e) {
    }

    try {
        $cutover = class_exists('OtantikErp') ? OtantikErp::ledger_cutover($admin) : null;
        $oid = Tenant::is_platform($admin) ? null : Tenant::id($admin);
        $qw = ORM::for_table('tbl_otantik_erp_flow')->where('direction', 'in');
        if ($cutover && $cutover >= $from) {
            $qw->where_gt('occurred_at', $cutover);
        } else {
            $qw->where_gte('occurred_at', $from);
        }
        $qw->where_lt('occurred_at', $to);
        if ($oid !== null) {
            $qw->where('owner_id', (int)$oid);
        }
        foreach ($qw->find_many() as $f) {
            $meta = json_decode((string)$f['meta'], true) ?: [];
            $id = (int)$f['id'];
            $rows[] = [
                'id' => $id,
                'source' => 'erp_flow',
                'when' => (string)$f['occurred_at'],
                'who' => (string)($meta['phone'] ?? $meta['client'] ?? ''),
                'label' => (string)$f['label'],
                'method' => (string)($f['method'] ?: ($f['source'] === 'wifi_sale' ? 'Vente WiFi' : 'Autre service')),
                'type' => $f['source'] === 'wifi_sale' ? 'Hotspot' : 'Service',
                'routers' => $routersLabel,
                'amount' => (float)$f['amount'],
                'voided' => isset($voidedFlow[$id]),
                'void_reason' => $voidReasons['erp_flow:' . $id] ?? '',
            ];
        }
    } catch (Throwable $e) {
    }

    usort($rows, function ($a, $b) {
        return strcmp($a['when'], $b['when']);
    });

    return $rows;
}

/**
 * Drill-down for the revenue chart: lists every individual transaction
 * that sums to a given bar's total. Called via AJAX from the chart -
 * reachable at plugin/wifi_otantik_rev_transactions&from=...&to=...
 * (both 'Y-m-d H:i:s', $to exclusive). Always responds with JSON.
 */
function wifi_otantik_rev_transactions()
{
    global $admin;
    _admin();
    Tenant::require_can('dashboard', $admin);
    header('Content-Type: application/json; charset=utf-8');

    $from = (string)_get('from');
    $to = (string)_get('to');
    $dtPattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
    if (!preg_match($dtPattern, $from) || !preg_match($dtPattern, $to)) {
        echo json_encode(['ok' => false, 'message' => 'Période invalide']);
        exit;
    }

    $rows = wifi_otantik_ledger_rows($admin, $from, $to);

    $total = 0.0;
    $active = 0;
    foreach ($rows as $r) {
        if (!$r['voided']) {
            $total += $r['amount'];
            $active++;
        }
    }

    echo json_encode(['ok' => true, 'count' => $active, 'total' => $total, 'rows' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Soft-deletes ("voids") one transaction or ERP-flow row from the revenue
 * drill-down. Never deletes the underlying row - records who/when/why in
 * tbl_otantik_voids, which every revenue calculation excludes. POST only.
 * Reachable at plugin/wifi_otantik_rev_void.
 */
function wifi_otantik_rev_void()
{
    global $admin;
    _admin();
    Tenant::require_can('finance_manage', $admin);
    header('Content-Type: application/json; charset=utf-8');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        echo json_encode(['ok' => false, 'message' => 'Méthode invalide']);
        exit;
    }

    $source = (string)_post('source');
    $refId = (int)_post('ref_id');
    $reason = trim((string)_post('reason'));
    if (!in_array($source, ['transaction', 'erp_flow'], true) || $refId < 1 || $reason === '') {
        echo json_encode(['ok' => false, 'message' => 'Requête invalide - un motif est requis']);
        exit;
    }

    $ownerId = Tenant::is_platform($admin) ? null : Tenant::id($admin);
    $amount = 0.0;
    $belongsOwnerId = null;

    if ($source === 'transaction') {
        $t = ORM::for_table('tbl_transactions')->find_one($refId);
        if (!$t) {
            echo json_encode(['ok' => false, 'message' => 'Transaction introuvable']);
            exit;
        }
        $router = ORM::for_table('tbl_routers')->where('name', $t['routers'])->find_one();
        $belongsOwnerId = $router ? (int)$router['owner_id'] : null;
        $amount = (float)$t['price'];
    } else {
        $f = ORM::for_table('tbl_otantik_erp_flow')->find_one($refId);
        if (!$f) {
            echo json_encode(['ok' => false, 'message' => 'Entrée introuvable']);
            exit;
        }
        $belongsOwnerId = (int)$f['owner_id'];
        $amount = (float)$f['amount'];
    }

    if ($ownerId !== null && $belongsOwnerId !== $ownerId) {
        echo json_encode(['ok' => false, 'message' => 'Accès refusé']);
        exit;
    }

    if (!class_exists('OtantikErp')) {
        echo json_encode(['ok' => false, 'message' => 'Module ERP indisponible']);
        exit;
    }
    OtantikErp::ensure_void_schema();

    $existing = ORM::for_table('tbl_otantik_voids')->where('source', $source)->where('ref_id', $refId)->find_one();
    if ($existing) {
        echo json_encode(['ok' => true, 'message' => 'Déjà annulée', 'already' => true]);
        exit;
    }

    $row = ORM::for_table('tbl_otantik_voids')->create();
    $row->owner_id = $belongsOwnerId ?: 0;
    $row->source = $source;
    $row->ref_id = $refId;
    $row->amount = $amount;
    $row->reason = mb_substr($reason, 0, 255);
    $row->voided_by = Tenant::id($admin);
    $row->voided_at = date('Y-m-d H:i:s');
    $row->save();

    echo json_encode(['ok' => true, 'message' => 'Transaction annulée']);
    exit;
}

/**
 * Reports page: filters (date/time range, type, plan, method, router) +
 * pie/line charts + a transaction table, over the same combined ledger as
 * the revenue chart (wifi_otantik_ledger_rows). Reachable at
 * plugin/wifi_otantik_reports. Data loads via AJAX (wifi_otantik_reports_data)
 * so this controller just renders the shell with sane defaults.
 */
function wifi_otantik_reports()
{
    global $admin;
    _admin();
    Tenant::require_can('finance_view', $admin);

    $sd = _get('sd') ?: date('Y-m-d', strtotime('-29 days'));
    $ed = _get('ed') ?: date('Y-m-d');

    wifi_otantik_ui('plugin/otantik_reports.tpl', 'Rapports', [
        'default_sd' => $sd,
        'default_ed' => $ed,
        'data_url_js' => json_encode(getUrl('plugin/wifi_otantik_reports_data'), JSON_UNESCAPED_SLASHES),
    ]);
}

/**
 * AJAX data for the Rapports page. GET params: sd, ed ('Y-m-d'), ts, te
 * ('H:i:s', default full day), and repeatable tps[]/plns[]/mts[]/rts[] to
 * filter by type/plan/method/router. Always responds with JSON. Voided
 * rows are excluded entirely (a report is about realized revenue).
 */
function wifi_otantik_reports_data()
{
    global $admin;
    _admin();
    Tenant::require_can('finance_view', $admin);
    header('Content-Type: application/json; charset=utf-8');

    $sd = (string)_get('sd');
    $ed = (string)_get('ed');
    $ts = (string)(_get('ts') ?: '00:00:00');
    $te = (string)(_get('te') ?: '23:59:59');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sd) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ed)
        || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $ts) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $te)) {
        echo json_encode(['ok' => false, 'message' => 'Période invalide']);
        exit;
    }
    $from = "$sd $ts";
    $to = date('Y-m-d H:i:s', strtotime("$ed $te") + 1);

    $all = wifi_otantik_ledger_rows($admin, $from, $to);
    $all = array_values(array_filter($all, function ($r) {
        return !$r['voided'];
    }));

    $distinct = function ($rows, $field) {
        $seen = [];
        foreach ($rows as $r) {
            $v = (string)$r[$field];
            if ($v !== '') {
                $seen[$v] = true;
            }
        }
        $vals = array_keys($seen);
        sort($vals);
        return $vals;
    };
    $options = [
        'types' => $distinct($all, 'type'),
        'plans' => $distinct($all, 'label'),
        'methods' => $distinct($all, 'method'),
        'routers' => $distinct($all, 'routers'),
    ];

    // _get()/safedata() calls trim() internally, which errors on an array -
    // these are repeatable query params (tps[]=...&tps[]=...), so read them
    // straight from $_GET and sanitize as plain strings ourselves. They're
    // only ever compared in-memory (in_array below), never used in SQL.
    $arrParam = function ($name) {
        $v = $_GET[$name] ?? [];
        if (!is_array($v)) {
            return [];
        }
        return array_values(array_filter(array_map(function ($x) {
            return trim((string)$x);
        }, $v), function ($x) {
            return $x !== '';
        }));
    };
    $selected = [
        'type' => $arrParam('tps'),
        'label' => $arrParam('plns'),
        'method' => $arrParam('mts'),
        'routers' => $arrParam('rts'),
    ];
    $filtered = array_values(array_filter($all, function ($r) use ($selected) {
        foreach ($selected as $field => $vals) {
            if ($vals && !in_array((string)$r[$field], $vals, true)) {
                return false;
            }
        }
        return true;
    }));

    $countBy = function ($rows, $field) {
        $counts = [];
        foreach ($rows as $r) {
            $v = (string)$r[$field] !== '' ? (string)$r[$field] : '(non renseigné)';
            $counts[$v] = ($counts[$v] ?? 0) + 1;
        }
        arsort($counts);
        return ['labels' => array_keys($counts), 'data' => array_values($counts)];
    };

    $timelineSums = [];
    foreach ($filtered as $r) {
        $day = substr($r['when'], 0, 10);
        $timelineSums[$day] = ($timelineSums[$day] ?? 0) + $r['amount'];
    }
    ksort($timelineSums);

    $rowsSorted = $filtered;
    usort($rowsSorted, function ($a, $b) {
        return strcmp($b['when'], $a['when']);
    });
    $rowsTotal = count($rowsSorted);
    $rowsCapped = array_slice($rowsSorted, 0, 1000);

    $totalAmount = 0.0;
    foreach ($filtered as $r) {
        $totalAmount += $r['amount'];
    }

    echo json_encode([
        'ok' => true,
        'options' => $options,
        'charts' => [
            'type' => $countBy($filtered, 'type'),
            'plan' => $countBy($filtered, 'label'),
            'method' => $countBy($filtered, 'method'),
            'router' => $countBy($filtered, 'routers'),
        ],
        'timeline' => ['labels' => array_keys($timelineSums), 'data' => array_values($timelineSums)],
        'rows' => $rowsCapped,
        'rows_total' => $rowsTotal,
        'total_amount' => $totalAmount,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function wifi_otantik_rev_dual_bars(array $series)
{
    $series = array_values($series);
    if (!$series) {
        return '<div class="wo-chart-hint">Les revenus s’afficheront ici</div>';
    }

    // Robust scale: a single one-off outlier (e.g. a big service invoice)
    // must not crush every other bar down to a sliver. Cap the visual scale
    // at 4x the median non-zero bar; bars beyond that are capped and marked
    // with a small overflow indicator, but their real total is still shown
    // in the label and tooltip - only the bar height is clamped.
    $totals = [];
    foreach ($series as $s) {
        $totals[] = max(0.0, (float)($s['wifi'] ?? 0) + (float)($s['other'] ?? 0));
    }
    $nonZero = array_values(array_filter($totals, function ($v) {
        return $v > 0;
    }));
    sort($nonZero);
    $cnt = count($nonZero);
    $median = $cnt ? $nonZero[(int)floor(($cnt - 1) / 2)] : 0.0;
    $trueMax = $totals ? max($totals) : 1.0;
    $cap = $median > 0 ? $median * 4 : $trueMax;
    $scaleMax = ($median > 0 && $trueMax > $cap) ? $cap : max(1.0, $trueMax);
    $axisMax = wifi_otantik_nice_axis_max($scaleMax, 4);

    $n = count($series);
    $W = 900;
    $H = 190;
    $padL = 56;
    $padR = 8;
    $padT = 20;
    $padB = 26;
    $plotW = $W - $padL - $padR;
    $plotH = $H - $padT - $padB;
    $slot = $plotW / $n;
    $barW = min(46, $slot * 0.52);
    $labelAll = $n <= 8;
    $baseY = $padT + $plotH;

    $best = 0.0;
    foreach ($totals as $t) {
        $best = max($best, $t);
    }

    $svg = '<svg class="wo-rev-svg" viewBox="0 0 ' . $W . ' ' . $H . '" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Revenus par periode">';

    for ($g = 0; $g <= 4; $g++) {
        $v = $axisMax * $g / 4;
        $y = $baseY - ($v / $axisMax) * $plotH;
        $svg .= '<line x1="' . $padL . '" x2="' . ($W - $padR) . '" y1="' . round($y, 1) . '" y2="' . round($y, 1) . '" class="wo-rev-grid"/>';
        $svg .= '<text x="' . ($padL - 8) . '" y="' . round($y + 4, 1) . '" class="wo-rev-axis" text-anchor="end">' . number_format($v, 0, ',', ' ') . '</text>';
    }

    foreach ($series as $i => $s) {
        $wifi = max(0.0, (float)($s['wifi'] ?? 0));
        $other = max(0.0, (float)($s['other'] ?? 0));
        $tot = $wifi + $other;
        $overflow = $tot > $axisMax;
        $cx = $padL + $slot * $i + $slot / 2;
        $x = $cx - $barW / 2;

        $wifiCapped = min($wifi, $axisMax);
        $otherCapped = min($other, max(0.0, $axisMax - $wifiCapped));
        $wifiH = ($wifiCapped / $axisMax) * $plotH;
        $otherH = ($otherCapped / $axisMax) * $plotH;
        $gap = ($wifiH > 0 && $otherH > 0) ? 2 : 0;
        $wifiY = $baseY - $wifiH;
        $otherY = $wifiY - $gap - $otherH;

        $label = htmlspecialchars((string)($s['label'] ?? ''));

        $svg .= '<g class="wo-rev-bar-g">';
        if ($otherH > 0) {
            $svg .= '<rect class="wo-rev-rect other" data-fy="' . round($otherY, 1) . '" data-fh="' . round($otherH, 1) . '" x="' . round($x, 1) . '" y="' . $baseY . '" width="' . round($barW, 1) . '" height="0" rx="4"/>';
        }
        if ($wifiH > 0) {
            $svg .= '<rect class="wo-rev-rect wifi" data-fy="' . round($wifiY, 1) . '" data-fh="' . round($wifiH, 1) . '" x="' . round($x, 1) . '" y="' . $baseY . '" width="' . round($barW, 1) . '" height="0" rx="4"/>';
        }
        if ($tot <= 0) {
            $svg .= '<rect class="wo-rev-rect empty" x="' . round($x, 1) . '" y="' . ($baseY - 4) . '" width="' . round($barW, 1) . '" height="4" rx="2"/>';
        }
        if ($overflow) {
            $svg .= '<text x="' . round($cx, 1) . '" y="' . round($padT - 8, 1) . '" text-anchor="middle" class="wo-rev-overflow">&#9650;</text>';
        }
        if ($tot > 0 && ($labelAll || $tot >= $best)) {
            $topY = $overflow ? ($padT - 16) : (($otherH > 0 ? $otherY : $wifiY) - 8);
            $svg .= '<text x="' . round($cx, 1) . '" y="' . round($topY, 1) . '" text-anchor="middle" class="wo-rev-val' . ($tot >= $best ? ' strong' : '') . '">' . number_format($tot, 0, ',', ' ') . '</text>';
        }
        $svg .= '<text x="' . round($cx, 1) . '" y="' . ($H - 8) . '" text-anchor="middle" class="wo-rev-xlabel">' . $label . '</text>';
        $svg .= '<rect class="wo-rev-hit" x="' . round($padL + $slot * $i, 1) . '" y="' . $padT . '" width="' . round($slot, 1) . '" height="' . $plotH . '" fill="transparent"'
            . ' data-label="' . $label . '" data-wifi="' . round($wifi) . '" data-other="' . round($other) . '" data-total="' . round($tot) . '"'
            . ' data-from="' . htmlspecialchars((string)($s['from'] ?? '')) . '" data-to="' . htmlspecialchars((string)($s['to'] ?? '')) . '"/>';
        $svg .= '</g>';
    }
    $svg .= '</svg>';

    $revTxUrl = getUrl('plugin/wifi_otantik_rev_transactions');
    $revVoidUrl = getUrl('plugin/wifi_otantik_rev_void');
    $js = '<script>(function(){'
        . 'var wrap=document.currentScript.parentElement;'
        . 'var svg=wrap.querySelector(".wo-rev-svg");'
        . 'var tip=wrap.querySelector(".wo-rev-tooltip");'
        . 'var revTxUrl=' . json_encode($revTxUrl) . ';'
        . 'var revVoidUrl=' . json_encode($revVoidUrl) . ';'
        . 'var rects=svg.querySelectorAll(".wo-rev-rect[data-fy]");'
        . 'requestAnimationFrame(function(){requestAnimationFrame(function(){'
        . 'rects.forEach(function(r,i){r.style.transition="y .6s cubic-bezier(.2,.8,.2,1) "+(i*0.025)+"s, height .6s cubic-bezier(.2,.8,.2,1) "+(i*0.025)+"s";'
        . 'r.setAttribute("y",r.getAttribute("data-fy"));r.setAttribute("height",r.getAttribute("data-fh"));});'
        . '});});'
        . 'function fmt(n){return new Intl.NumberFormat("fr-FR").format(Math.round(n))+" FCFA";}'
        . 'svg.querySelectorAll(".wo-rev-hit").forEach(function(hit){'
        . 'hit.addEventListener("pointermove",function(ev){'
        . 'var wifi=+hit.getAttribute("data-wifi"),other=+hit.getAttribute("data-other"),tot=+hit.getAttribute("data-total");'
        . 'var val=document.createElement("div");val.className="tt-value";val.textContent=fmt(tot);'
        . 'var sub=document.createElement("div");sub.className="tt-label";'
        . 'sub.textContent=hit.getAttribute("data-label")+(other>0?(" · Connexion "+fmt(wifi)+" · Autres "+fmt(other)):"");'
        . 'tip.innerHTML="";tip.appendChild(val);tip.appendChild(sub);tip.hidden=false;'
        . 'var b=wrap.getBoundingClientRect(),tw=tip.offsetWidth,th=tip.offsetHeight;'
        . 'var left=Math.min(ev.clientX-b.left+12,b.width-tw-6);var top=Math.max(ev.clientY-b.top-th-12,0);'
        . 'tip.style.left=left+"px";tip.style.top=top+"px";'
        . '});'
        . 'hit.addEventListener("pointerleave",function(){tip.hidden=true;});'
        . 'hit.style.cursor="pointer";'
        . 'hit.addEventListener("click",function(){'
        . 'var from=hit.getAttribute("data-from"),to=hit.getAttribute("data-to"),label=hit.getAttribute("data-label");'
        . 'if(!from||!to||!window.Swal){return;}'
        . 'openTxModal(from,to,label);'
        . '});'
        . '});'
        . 'function voidRow(r,onDone){'
        . 'Swal.fire({title:"Annuler cette transaction ?",input:"text",inputLabel:"Motif (obligatoire)",inputPlaceholder:"ex: doublon, erreur de saisie...",showCancelButton:true,confirmButtonText:"Annuler la transaction",cancelButtonText:"Fermer",confirmButtonColor:"#b91c1c",inputValidator:function(v){if(!v||!v.trim()){return "Merci d\'indiquer un motif";}}}).then(function(res){'
        . 'if(!res.isConfirmed){onDone(false);return;}'
        . 'fetch(revVoidUrl,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded"},'
        . 'body:"source="+encodeURIComponent(r.source)+"&ref_id="+r.id+"&reason="+encodeURIComponent(res.value)})'
        . '.then(function(rr){return rr.json();}).then(function(vd){'
        . 'if(vd.ok){onDone(true);}else{Swal.fire({icon:"error",title:"Erreur",text:vd.message||"Échec de l\'annulation"});onDone(false);}'
        . '}).catch(function(){Swal.fire({icon:"error",title:"Erreur",text:"Échec de l\'annulation"});onDone(false);});'
        . '});'
        . '}'
        . 'function openTxModal(from,to,label){'
        . 'Swal.fire({title:label,html:"Chargement...",width:680,didOpen:function(){Swal.showLoading();}});'
        . 'fetch(revTxUrl+"&from="+encodeURIComponent(from)+"&to="+encodeURIComponent(to),{credentials:"same-origin"})'
        . '.then(function(r){return r.json();}).then(function(d){'
        . 'var box=document.createElement("div");box.style.textAlign="left";box.style.maxHeight="55vh";box.style.overflowY="auto";'
        . 'if(!d.ok||!d.rows||!d.rows.length){'
        . 'var p=document.createElement("p");p.textContent="Aucune transaction sur cette période.";box.appendChild(p);'
        . '}else{'
        . 'var table=document.createElement("table");table.style.width="100%";table.style.fontSize="13px";table.style.borderCollapse="collapse";'
        . 'var thead=document.createElement("tr");'
        . '["Date","Client","Détail","Montant",""].forEach(function(h,i){var th=document.createElement("th");th.textContent=h;th.style.textAlign=(i===3?"right":(i===4?"center":"left"));th.style.padding="6px 8px";th.style.borderBottom="2px solid #e2e8f0";th.style.color="#64748b";th.style.fontSize="11px";th.style.textTransform="uppercase";thead.appendChild(th);});'
        . 'table.appendChild(thead);'
        . 'd.rows.forEach(function(r){'
        . 'var tr=document.createElement("tr");'
        . 'if(r.voided){tr.style.opacity="0.5";}'
        . 'var tds=[r.when,r.who||"-",[r.label,r.method].filter(Boolean).join(" · "),fmt(r.amount)];'
        . 'tds.forEach(function(v,i){var td=document.createElement("td");td.textContent=v;if(r.voided&&i<4){td.style.textDecoration="line-through";}td.style.padding="6px 8px";td.style.borderBottom="1px solid #eef1f6";td.style.textAlign=(i===3?"right":"left");if(i===3){td.style.fontWeight="600";}tr.appendChild(td);});'
        . 'var tdA=document.createElement("td");tdA.style.padding="6px 8px";tdA.style.borderBottom="1px solid #eef1f6";tdA.style.textAlign="center";'
        . 'if(r.voided){'
        . 'var tag=document.createElement("span");tag.textContent="Annulée";tag.title=r.void_reason||"";tag.style.fontSize="11px";tag.style.color="#94a3b8";tdA.appendChild(tag);'
        . '}else{'
        . 'var btn=document.createElement("button");btn.type="button";btn.textContent="✕";btn.title="Annuler cette transaction";'
        . 'btn.style.border="none";btn.style.background="#fee2e2";btn.style.color="#b91c1c";btn.style.borderRadius="6px";btn.style.width="24px";btn.style.height="24px";btn.style.cursor="pointer";btn.style.fontSize="12px";'
        . 'btn.addEventListener("click",function(){voidRow(r,function(changed){if(changed){openTxModal(from,to,label);}});});'
        . 'tdA.appendChild(btn);'
        . '}'
        . 'tr.appendChild(tdA);'
        . 'table.appendChild(tr);'
        . '});'
        . 'box.appendChild(table);'
        . 'var foot=document.createElement("p");foot.style.marginTop="10px";foot.style.fontWeight="700";foot.style.textAlign="right";'
        . 'foot.textContent=d.count+" transaction(s) active(s) · Total "+fmt(d.total);box.appendChild(foot);'
        . '}'
        . 'Swal.hideLoading();Swal.update({html:box});'
        . '}).catch(function(){Swal.hideLoading();Swal.update({html:"Erreur de chargement."});});'
        . '}'
        . '})();</script>';

    return '<div class="wo-rev-wrap">' . $svg . '<div class="wo-rev-tooltip" hidden></div>' . $js . '</div>';
}

function wifi_otantik_donut_svg($mtn, $orange, $other)
{
    $mtn = (float)$mtn;
    $orange = (float)$orange;
    $other = (float)$other;
    $tot = $mtn + $orange + $other;
    $r = 52;
    $c = 2 * M_PI * $r;
    $cx = 90;
    $cy = 90;
    $ring = function ($val, $color, $offset) use ($c, $cx, $cy, $r, $tot) {
        if ($tot <= 0 || $val <= 0) {
            return '';
        }
        $len = $c * ($val / $tot);
        $gap = max(0, $c - $len);
        return '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="none" stroke="' . $color . '" stroke-width="16" stroke-linecap="round" '
            . 'stroke-dasharray="' . round($len, 1) . ' ' . round($gap, 1) . '" stroke-dashoffset="' . round($offset, 1) . '" transform="rotate(-90 ' . $cx . ' ' . $cy . ')"/>';
    };
    $off = 0;
    $html = '<svg class="wo-donut" viewBox="0 0 180 180" width="180" height="180" aria-hidden="true">';
    $html .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="none" stroke="#e2e8f0" stroke-width="16"/>';
    if ($tot > 0) {
        $html .= $ring($mtn, '#facc15', $off);
        $off -= $c * ($mtn / $tot);
        $html .= $ring($orange, '#f05018', $off);
        $off -= $c * ($orange / $tot);
        $html .= $ring($other, '#94a3b8', $off);
    }
    $html .= '<text x="' . $cx . '" y="' . ($cy - 4) . '" text-anchor="middle" font-size="22" font-weight="800" fill="#002858">' . ($tot > 0 ? number_format($tot, 0, ',', ' ') : '0') . '</text>';
    $html .= '<text x="' . $cx . '" y="' . ($cy + 16) . '" text-anchor="middle" font-size="11" fill="#64748b">FCFA</text>';
    $html .= '</svg>';
    return $html;
}

function wifi_otantik_home()
{
    global $admin;
    _admin();
    Tenant::require_can('dashboard', $admin);
    $tenant = Tenant::tenant_row(Tenant::id($admin));
    $routers = Tenant::routers_query($admin)->find_many();
    $online = 0;
    foreach ($routers as $r) {
        if (($r['status'] ?? '') === 'Online') {
            $online++;
        }
    }
    $stats = wifi_otantik_dashboard_stats($admin);
    $stats['chart_labels_json'] = json_encode(array_values($stats['chart_labels']));
    $stats['chart_sales_json'] = json_encode(array_values($stats['chart_sales']));
    $stats['chart_wd_json'] = json_encode(array_values($stats['chart_wd']));
    $stats['by_zone_json'] = json_encode(array_values($stats['by_zone']));
    $yestA = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $yestB = date('Y-m-d 00:00:00');
    $yestRev = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where_gte('paid_date', $yestA)->where_lt('paid_date', $yestB)->sum('price');
    $todayRev = (float)$stats['today_rev'];
    $delta = $yestRev > 0 ? round((($todayRev - $yestRev) / $yestRev) * 100, 1) : ($todayRev > 0 ? 100 : 0);
    // Fapshi-gateway rows never carry a payment_method tag (verified: 613
    // rows, all '') and mix both carriers with no way to tell them apart -
    // without this they silently fell into the "cash" bucket instead. Split
    // evenly between MTN/Orange rather than guessing which is which.
    $rangeStartMtn = wifi_otantik_range_start($stats['range']);
    $mtnTagged = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where('payment_method', 'MTN')->where_gte('paid_date', $rangeStartMtn)->sum('price');
    $orangeTagged = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where('payment_method', 'ORANGE')->where_gte('paid_date', $rangeStartMtn)->sum('price');
    $fapshiUntagged = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where('gateway', 'fapshi')->where_gte('paid_date', $rangeStartMtn)->sum('price');
    $mtn = $mtnTagged + $fapshiUntagged / 2;
    $orange = $orangeTagged + $fapshiUntagged / 2;
    $peerOk = 0;
    $peerWait = 0;
    $tunWg = 0;
    $tunL2 = 0;
    foreach (ORM::for_table('tbl_otantik_peers')->where('owner_id', Tenant::id($admin))->find_many() as $p) {
        if (($p['status'] ?? '') === 'completed') {
            $peerOk++;
        } else {
            $peerWait++;
        }
        $rt = ORM::for_table('tbl_routers')->find_one($p['router_id']);
        if ($rt && wifi_otantik_tunnel($rt, $p)['method'] === 'l2tp') {
            $tunL2++;
        } else {
            $tunWg++;
        }
    }
    $hour = (int)date('G');
    $greet = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $first = explode(' ', trim((string)($admin['fullname'] ?: $admin['username'])));
    $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $recentUi = wifi_otantik_dedupe_recent($stats['recent']);
    $tunPct = ($peerOk + $peerWait) > 0 ? round(100 * $peerOk / ($peerOk + $peerWait)) : 0;
    // Revenue correction must run before the live-AJAX branch below (which
    // exits early) - it used to run after, so the periodic auto-refresh
    // always reported the raw gateway-only figure (near-empty) and clobbered
    // the correct value the page loaded with, causing "CA réel" to flicker
    // back down a few seconds after load.
    $erpOn = class_exists('OtantikErp') && OtantikErp::has_access($admin);
    $caWifiToday = (float)$stats['today_rev'];
    $caWifiMonth = (float)$stats['month_rev'];
    $caWifiRange = (float)$stats['range_rev'];
    $caOtherToday = 0.0;
    $caOtherMonth = 0.0;
    $caOtherRange = 0.0;
    // Backfilling the wifi-sale ledger (tbl_otantik_erp_flow) feeds the
    // revenue bar chart below - this must run for every tenant, not just
    // ERP Premium ones, so everyone sees the same chart the platform does.
    // The wifi/other split further down stays Premium-gated: that's the
    // actual paid feature (tracking non-wifi income), not the chart itself.
    if (class_exists('OtantikErp')) {
        try {
            OtantikErp::backfill($admin);
        } catch (Throwable $e) {
        }
    }
    if ($erpOn) {
        try {
            $splitToday = OtantikErp::revenue_split($admin, 'today');
            $splitMonth = OtantikErp::revenue_split($admin, 'month');
            $splitRange = OtantikErp::revenue_split($admin, $stats['range']);
            $caWifiToday = (float)$splitToday['wifi'];
            $caOtherToday = (float)$splitToday['other'];
            $caWifiMonth = (float)$splitMonth['wifi'];
            $caOtherMonth = (float)$splitMonth['other'];
            $caWifiRange = (float)$splitRange['wifi'];
            $caOtherRange = (float)$splitRange['other'];
            $stats['today_rev'] = (float)$splitToday['total'];
            $stats['month_rev'] = (float)$splitMonth['total'];
            $stats['range_rev'] = (float)$splitRange['total'];
        } catch (Throwable $e) {
        }
    }
    $live = [
        'wallet' => (float)($tenant['wallet'] ?? 0),
        'today_count' => (int)$stats['today_count'],
        'today_rev' => (float)$stats['today_rev'],
        'month_rev' => (float)$stats['month_rev'],
        'range_rev' => (float)$stats['range_rev'],
        'delta_today' => $delta,
        'mtn_rev' => $mtn,
        'orange_rev' => $orange,
        'cash_rev' => max(0, (float)$stats['range_rev'] - $mtn - $orange),
        'peer_ok' => $peerOk,
        'peer_wait' => $peerWait,
        'tun_pct' => $tunPct,
        'online_count' => $online,
        'router_count' => count($routers),
        'recent' => $recentUi,
        'now' => date('H:i:s'),
        'tun_wg' => $tunWg,
        'tun_l2' => $tunL2,
        'clients_total' => (int)($stats['clients_total'] ?? 0),
        'clients_today' => (int)($stats['clients_today'] ?? 0),
        'clients_week' => (int)($stats['clients_week'] ?? 0),
        'clients_month' => (int)($stats['clients_month'] ?? 0),
        'clients_delta' => (float)($stats['clients_delta'] ?? 0),
        'clients_delta_month' => (float)($stats['clients_delta_month'] ?? 0),
    ];
    if (_get('live') === '1') {
        $growth = wifi_otantik_client_growth($admin, false);
        $live['clients_total'] = (int)$growth['clients_total'];
        $live['clients_today'] = (int)$growth['clients_today'];
        $live['clients_week'] = (int)$growth['clients_week'];
        $live['clients_month'] = (int)$growth['clients_month'];
        $live['clients_delta'] = (float)$growth['clients_delta'];
        $live['clients_delta_month'] = (float)$growth['clients_delta_month'];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($live);
        exit;
    }
    $expireBoard = wifi_otantik_expiry_board($admin, 200, 100);
    $mkLogsUi = wifi_otantik_mk_logs_rows($admin, 8);
    $rsCount = 0;
    $rsWallet = 0.0;
    try {
        $rsCount = (int)ORM::for_table('tbl_otantik_resellers')->where('owner_id', Tenant::id($admin))->count();
        $rsWallet = (float)ORM::for_table('tbl_otantik_resellers')->where('owner_id', Tenant::id($admin))->sum('wallet');
    } catch (Throwable $e) {
    }
    $revBarsHtml = '';
    $chartSvg = wifi_otantik_area_svg($stats['chart_sales']);
    // Same bar chart for every role/tenant, Premium or not - only the
    // wifi/other split legend above stays Premium-exclusive.
    if (class_exists('OtantikErp')) {
        try {
            $revBarsHtml = wifi_otantik_rev_dual_bars(OtantikErp::revenue_bar_series($admin, $stats['range']));
        } catch (Throwable $e) {
        }
    }
    wifi_otantik_ui('plugin/otantik_home.tpl', 'Tableau de bord', array_merge($stats, [
        'tenant' => $tenant,
        'routers' => $routers,
        'online_count' => $online,
        'router_count' => count($routers),
        'open_modal' => _get('new') === '1',
        'delta_today' => $delta,
        'mtn_rev' => $mtn,
        'orange_rev' => $orange,
        'other_rev' => max(0, $caWifiRange - $mtn - $orange),
        'peer_ok' => $peerOk,
        'peer_wait' => $peerWait,
        'tun_pct' => $tunPct,
        'tun_wg' => $tunWg,
        'tun_l2' => $tunL2,
        'greet' => $greet,
        'first_name' => $first[0],
        'recent_ui' => $recentUi,
        'clock_weekday' => $days[(int)date('w')],
        'clock_day' => date('d'),
        'clock_month' => $months[(int)date('n') - 1] . ' ' . date('Y'),
        'live_url' => getUrl('plugin/wifi_otantik_home') . '&live=1',
        'chart_svg' => $chartSvg,
        'rev_bars_html' => $revBarsHtml,
        'ca_pro' => $erpOn,
        'ca_wifi_today' => $caWifiToday,
        'ca_other_today' => $caOtherToday,
        'ca_wifi_month' => $caWifiMonth,
        'ca_other_month' => $caOtherMonth,
        'ca_wifi_range' => $caWifiRange,
        'ca_other_range' => $caOtherRange,
        'donut_svg' => wifi_otantik_donut_svg($mtn, $orange, max(0, $caWifiRange - $mtn - $orange)),
        'clients_week_html' => wifi_otantik_grow_bars($stats['clients_week_bars'] ?? [], getUrl('plugin/wifi_otantik_customers')),
        'clients_month_html' => wifi_otantik_grow_bars($stats['clients_month_bars'] ?? [], getUrl('plugin/wifi_otantik_customers')),
        'expire_soon' => $expireBoard['soon'],
        'expire_recent' => $expireBoard['expired'],
        'mk_logs_ui' => $mkLogsUi,
        'reseller_count' => $rsCount,
        'reseller_wallets' => $rsWallet,
    ]));
}

function wifi_otantik_routers()
{
    global $admin, $routes;
    // Reached on this router's own dedicated port, a different origin than
    // the admin panel entirely (see wifi_otantik_router_webfig) - it has no
    // admin session cookie to check, so it must come before _admin() and
    // use its own token-based auth instead, not be gated by it.
    if (($routes[2] ?? '') === 'webfig_port') {
        $rid = (int)(_get('rid') ?: 0);
        $p = trim((string)(_get('p') ?: ''), '/');
        wifi_otantik_router_webfig($rid, $p === '' ? [] : explode('/', $p), '');
        return;
    }
    _admin();
    Tenant::require_can('network', $admin);
    $act = $routes[2] ?? '';
    if ($act === 'new') {
        wifi_otantik_ui('plugin/otantik_zone_new.tpl', 'Nouvelle Wifi Zone');
        return;
    }
    if ($act === 'add' && _post('name')) {
        wifi_otantik_router_create();
        return;
    }
    if ($act === 'api' && _post('id')) {
        $r = Tenant::require_router((int)_post('id'), $admin);
        $r->username = trim(_post('username')) ?: 'admin';
        if (trim(_post('password')) !== '') {
            $r->password = _post('password');
        }
        if (_post('pppoe_interface') !== null) {
            $r->pppoe_interface = trim(_post('pppoe_interface'));
        }
        if (_post('ssid') !== null) {
            $r->ssid = trim(_post('ssid'));
        }
        if (_post('lan_host') !== null) {
            $r->lan_host = trim(_post('lan_host'));
        }
        if (_post('assistance_contact') !== null) {
            $r->assistance_contact = trim(_post('assistance_contact'));
        }
        if (_post('zone_note') !== null) {
            $r->zone_note = trim(_post('zone_note'));
        }
        if (_post('mac_bypass_strict') !== null) {
            $r->mac_bypass_strict = (int)_post('mac_bypass_strict') === 1 ? 1 : 0;
        }
        $ros = trim((string)_post('ros_version'));
        if (in_array($ros, ['6', '7', 'auto'], true)) {
            $r->ros_version = $ros;
        }
        if (_post('latitude') !== null) {
            $r->latitude = trim(_post('latitude'));
        }
        if (_post('longitude') !== null) {
            $r->longitude = trim(_post('longitude'));
        }
        if (_post('location_address') !== null) {
            $r->location_address = mb_substr(trim((string)_post('location_address')), 0, 255);
        }
        $r->save();
        $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', $r['id'])->find_one();
        wifi_otantik_apply_ros_ip($r, $peer);
        r2(getUrl('plugin/wifi_otantik_routers'), 's', 'Identifiants et version RouterOS enregistrés');
    }
    if ($act === 'edit') {
        $r = Tenant::require_router((int)($routes[3] ?? 0), $admin);
        wifi_otantik_ui('plugin/otantik_zone_edit.tpl', 'Réglages zone', [
            'router' => $r,
            'pay_url' => wifi_otantik_portal_buy_url($r['slug']),
            'pppoe_url' => wifi_otantik_connectik_url($r['slug']),
            'webfig_url' => 'https://mk' . (int)$r['id'] . '.otantik.cm/?token=' . wifi_otantik_webfig_token_issue((int)$r['id']),
        ]);
        return;
    }
    if ($act === 'del') {
        $r = Tenant::require_router((int)($routes[3] ?? 0), $admin);
        $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', $r['id'])->find_one();
        if ($peer) {
            wifi_otantik_wg(['del', $peer['public_key']]);
            $peer->delete();
        }
        ORM::for_table('tbl_plans')->where('routers', $r['name'])->delete_many();
        $r->delete();
        r2(getUrl('plugin/wifi_otantik_routers'), 's', 'Routeur supprimé');
    }
    $routers = Tenant::routers_query($admin)->find_many();
    $peers = [];
    foreach (ORM::for_table('tbl_otantik_peers')->where('owner_id', Tenant::is_platform($admin) ? ($routes[2] ?? Tenant::id($admin)) : Tenant::id($admin))->find_many() as $p) {
        $peers[(int)$p['router_id']] = $p;
    }
    if (Tenant::is_platform($admin)) {
        $peers = [];
        foreach (ORM::for_table('tbl_otantik_peers')->find_many() as $p) {
            $peers[(int)$p['router_id']] = $p;
        }
    }
    $scriptId = ($routes[2] === 'script') ? (int)($routes[3] ?? 0) : 0;
    $scriptRouter = $scriptId ? Tenant::require_router($scriptId, $admin) : null;
    $tunnels = [];
    foreach ($routers as $r) {
        $tunnels[(int)$r['id']] = wifi_otantik_tunnel($r, $peers[(int)$r['id']] ?? null);
    }
    $scriptPeer = $scriptRouter ? ($peers[(int)$scriptRouter['id']] ?? null) : null;
    $payUrls = [];
    $pppoeUrls = [];
    foreach ($routers as $r) {
        $payUrls[(int)$r['id']] = wifi_otantik_portal_buy_url($r['slug']);
        $pppoeUrls[(int)$r['id']] = wifi_otantik_connectik_url($r['slug']);
    }
    wifi_otantik_ui('plugin/otantik_routers.tpl', 'Mes routeurs', [
        'routers' => $routers,
        'peers' => $peers,
        'tunnels' => $tunnels,
        'pay_urls' => $payUrls,
        'pppoe_urls' => $pppoeUrls,
        'script_id' => $scriptId,
        'script_router' => $scriptRouter,
        'connect' => $scriptRouter ? wifi_otantik_tunnel($scriptRouter, $scriptPeer) : null,
        'rsc' => $scriptRouter ? wifi_otantik_connect_script($scriptRouter) : '',
        'rsc6' => $scriptRouter ? wifi_otantik_rsc_ros6((int)$scriptRouter['id']) : '',
        'rsc7' => $scriptRouter ? wifi_otantik_rsc((int)$scriptRouter['id']) : '',
        'bootstrap' => ($scriptRouter && $scriptRouter['slug']) ? wifi_otantik_bootstrap_cmd($scriptRouter['slug'], wifi_otantik_ros_choice($scriptRouter)) : '',
    ]);
}

function wifi_otantik_webfig_token_issue($routerId)
{
    try {
        ORM::for_table('tbl_otantik_webfig_tokens')->where_lt('expires_at', date('Y-m-d H:i:s'))->delete_many();
    } catch (Throwable $e) {
    }
    $token = bin2hex(random_bytes(32));
    $row = ORM::for_table('tbl_otantik_webfig_tokens')->create();
    $row->token = $token;
    $row->router_id = (int)$routerId;
    $row->expires_at = date('Y-m-d H:i:s', time() + 7200);
    $row->created_at = date('Y-m-d H:i:s');
    $row->save();
    return $token;
}

function wifi_otantik_webfig_token_valid($token, $routerId)
{
    $token = trim((string)$token);
    if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        return false;
    }
    $row = ORM::for_table('tbl_otantik_webfig_tokens')
        ->where('token', $token)
        ->where('router_id', (int)$routerId)
        ->where_gt('expires_at', date('Y-m-d H:i:s'))
        ->find_one();
    return (bool)$row;
}

/**
 * Best-effort reverse proxy onto a router's own WebFig, reached over its
 * WireGuard tunnel IP (never reachable from the admin's browser directly -
 * only this server, sitting on the same tunnel network, can reach it).
 * Served on its own dedicated port with no path prefix, so WebFig's own
 * root-relative references (in any syntactic form - attributes, CSS, JS)
 * resolve correctly by themselves; no content rewriting needed.
 *
 * This runs on a different origin than the admin panel (a raw IP:port, not
 * business.otantik.cm - Cloudflare only proxies a handful of fixed ports,
 * so this can't sit behind the same hostname/tunnel), so the admin's normal
 * session cookie never reaches it. Auth is instead a short-lived signed
 * token handed off once from the admin panel link, then carried forward as
 * a cookie scoped to this origin for the rest of the browsing session.
 * Cookies to the router itself are kept server-side (a per-router curl
 * cookiejar) and never reach the browser either way.
 */
function wifi_otantik_router_webfig($routerId, $subParts, $prefixOverride = null)
{
    // php.ini's default 30s max_execution_time would fatal this script
    // before curl's own (longer) timeout below ever gets a chance to return
    // - WebFig's long-poll /jsproxy calls legitimately run past 30s.
    @set_time_limit(85);
    $router = ORM::for_table('tbl_routers')->find_one($routerId);
    if (!$router) {
        http_response_code(404);
        echo 'Routeur introuvable.';
        exit;
    }
    $cookieName = 'wo_wf_auth_' . (int)$routerId;
    $authed = wifi_otantik_webfig_token_valid($_COOKIE[$cookieName] ?? '', $routerId);
    if (!$authed) {
        $qtoken = trim((string)(_get('token') ?: ''));
        if (wifi_otantik_webfig_token_valid($qtoken, $routerId)) {
            if (!headers_sent()) {
                setcookie($cookieName, $qtoken, time() + 7200, '/', '', false, true);
            }
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            $qs = preg_replace('/(^|&)token=[^&]*/', '', $qs);
            $qs = trim((string)$qs, '&');
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . ($qs !== '' ? '?' . $qs : ''));
            exit;
        }
        http_response_code(403);
        echo 'Session expirée. Rouvrez ce lien depuis le panneau d’administration.';
        exit;
    }
    $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', (int)$router['id'])->find_one();
    $wgIp = trim((string)($peer['wg_ip'] ?? ''));
    if ($wgIp === '') {
        http_response_code(502);
        echo 'Routeur non lié (pas de tunnel WireGuard).';
        exit;
    }
    // A real URL path (not this app's usual ?_route= query-string scheme) so
    // that plain relative references inside WebFig's own CSS/JS (no leading
    // slash, e.g. url(logo.svg)) resolve against this path like a browser
    // normally would - they'd otherwise resolve against the site root and
    // 404, since a query string plays no part in relative-URL resolution.
    // When reached via this router's own dedicated port (prefixOverride =
    // ''), there's no prefix at all needed or possible to inject correctly -
    // WebFig thinks it's served at a real root, so every rewrite below
    // becomes a harmless no-op and every reference just resolves on its own.
    $prefix = $prefixOverride !== null ? $prefixOverride : ('/mkwf/' . (int)$router['id']);
    $sub = implode('/', array_map('rawurldecode', $subParts));
    $qs = trim((string)($_SERVER['QUERY_STRING'] ?? ''));
    $target = 'http://' . $wgIp . '/' . $sub . ($qs !== '' ? '?' . $qs : '');
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $body = ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') ? file_get_contents('php://input') : null;
    $headers = [];
    foreach (['Content-Type', 'Accept', 'X-Requested-With', 'Authorization'] as $h) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $h));
        if ($h === 'Content-Type') {
            $key = 'CONTENT_TYPE';
        }
        if (!empty($_SERVER[$key])) {
            $headers[] = $h . ': ' . $_SERVER[$key];
        }
    }
    $cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wo_webfig_' . (int)$router['id'] . '_' . md5((string)session_id()) . '.txt';
    $ch = curl_init($target);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        // WebFig keeps a /jsproxy POST open for tens of seconds at a time to
        // long-poll for live menu/traffic updates - a short timeout here cuts
        // those off mid-flight, which the client treats as a dropped
        // connection and eventually logs itself out over (looks like the UI
        // being stuck on "Connecting" from the user's side). Kept under the
        // pool's request_terminate_timeout (90s) so PHP-FPM never kills the
        // worker before curl has a chance to return on its own.
        CURLOPT_TIMEOUT => 80,
        // Some bundled WebFig assets are stored compressed on the router and
        // it 406s any request that doesn't advertise a supported encoding -
        // real browsers always send Accept-Encoding automatically, but curl
        // sends none unless told to, so without this every proxied request
        // looked "encoding-less" to the router. Letting curl negotiate and
        // auto-decompress also means the body we forward is always already
        // plain, so we never need to (and don't) relay Content-Encoding back.
        CURLOPT_ENCODING => '',
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $resp = curl_exec($ch);
    if ($resp === false) {
        http_response_code(502);
        echo 'Routeur injoignable via le tunnel : ' . htmlspecialchars(curl_error($ch));
        curl_close($ch);
        exit;
    }
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $rawHeaders = substr($resp, 0, $headerSize);
    $respBody = substr($resp, $headerSize);
    $contentType = '';
    foreach (explode("\r\n", $rawHeaders) as $line) {
        if (stripos($line, 'Content-Type:') === 0) {
            $contentType = trim(substr($line, 13));
        }
        if (stripos($line, 'Location:') === 0) {
            $loc = trim(substr($line, 9));
            if (strpos($loc, '/') === 0 && strpos($loc, $prefix) !== 0) {
                header('Location: ' . $prefix . $loc);
            }
        }
    }
    curl_close($ch);
    http_response_code($status ?: 502);
    if ($contentType !== '') {
        header('Content-Type: ' . $contentType);
    }
    // The browser has no reason to know this proxy's rewriting logic just
    // changed - without this it can keep serving an old cached copy of
    // WebFig's own JS/CSS from before a fix here, indefinitely.
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    $isRewritable = preg_match('#^(text/html|text/css|application/javascript|text/javascript)#i', $contentType);
    if ($isRewritable) {
        $respBody = preg_replace(
            [
                '#(href|src|action)=(["\'])/#i',
                '#url\((["\']?)/#i',
                '#(`)/#',
                '#((?:window\.)?location(?:\.href)?\s*=\s*)(["\'])/#i',
            ],
            [
                '$1=$2' . $prefix . '/',
                'url($1' . $prefix . '/',
                '$1' . $prefix . '/',
                '$1$2' . $prefix . '/',
            ],
            $respBody
        );
    }
    echo $respBody;
    exit;
}

function wifi_otantik_router_create()
{
    global $admin;
    $name = trim(_post('name'));
    if (strlen($name) < 3) {
        r2(getUrl('plugin/wifi_otantik_routers'), 'e', 'Nom trop court');
    }
    if (ORM::for_table('tbl_routers')->where('name', $name)->find_one()) {
        r2(getUrl('plugin/wifi_otantik_routers'), 'e', 'Ce nom existe déjà');
    }
    $ip = wifi_otantik_next_ip();
    list($code, $keys) = wifi_otantik_wg(['genkey']);
    if ($code !== 0) {
        r2(getUrl('plugin/wifi_otantik_routers'), 'e', 'WireGuard: ' . $keys);
    }
    $parts = explode("\n", $keys);
    $priv = trim($parts[0] ?? '');
    $pub = trim($parts[1] ?? '');
    if ($priv === '' || $pub === '') {
        r2(getUrl('plugin/wifi_otantik_routers'), 'e', 'Génération des clés WireGuard impossible');
    }
    list($c2, $out2) = wifi_otantik_wg(['add', $pub, $ip]);
    if ($c2 !== 0) {
        r2(getUrl('plugin/wifi_otantik_routers'), 'e', 'Peer WG: ' . $out2);
    }
    $d = ORM::for_table('tbl_routers')->create();
    $d->name = $name;
    $d->ip_address = $ip;
    $d->username = 'admin';
    $d->password = '';
    $d->description = 'wifi-otantik';
    $d->enabled = _post('zone_active') === '0' ? 0 : 1;
    $d->status = 'Offline';
    $d->owner_id = Tenant::id($admin);
    $d->slug = Tenant::slugify($name);
    $d->lan_host = trim(_post('lan_host'));
    $d->ssid = trim(_post('ssid'));
    $d->assistance_contact = trim(_post('assistance_contact'));
    $d->latitude = trim(_post('latitude'));
    $d->longitude = trim(_post('longitude'));
    $d->location_address = mb_substr(trim((string)_post('location_address')), 0, 255);
    $d->zone_note = trim(_post('zone_note') ?: _post('description'));
    $d->pppoe_interface = trim(_post('pppoe_interface'));
    $ros = trim((string)_post('ros_version'));
    $d->ros_version = in_array($ros, ['6', '7', 'auto'], true) ? $ros : 'auto';
    $d->save();
    $peer = ORM::for_table('tbl_otantik_peers')->create();
    $peer->router_id = $d->id();
    $peer->owner_id = Tenant::id($admin);
    $peer->wg_ip = $ip;
    $peer->public_key = $pub;
    $peer->private_key = $priv;
    $peer->status = 'pending';
    $peer->created_at = date('Y-m-d H:i:s');
    $peer->save();
    wifi_otantik_ensure_ppp($peer);
    wifi_otantik_apply_ros_ip($d, $peer);
    r2(getUrl('plugin/wifi_otantik_routers/script/') . $d->id(), 's', 'Zone créée. Collez la commande correspondant à votre RouterOS.');
}

function wifi_otantik_ros_str($s)
{
    return str_replace(['\\', '"'], ['\\\\', '\\"'], (string)$s);
}

function wifi_otantik_walled_hosts()
{
    $hosts = [
        Tenant::host_portal(),
        Tenant::host_wifi(),
        Tenant::host_business(),
        '*.otantik.cm',
        'mesomb.hachther.com',
        '*.mesomb.hachther.com',
        'mesomb.com',
        '*.mesomb.com',
        'fapshi.com',
        '*.fapshi.com',
        'live.fapshi.com',
        'sandbox.fapshi.com',
        'wa.me',
        'api.whatsapp.com',
        '*.whatsapp.com',
        'fonts.gstatic.com',
        'api.telegram.org',
        'telegram.org',
        'web.telegram.org',
        '*.telegram.org',
        't.me',
        'intergram.xyz',
        'www.intergram.xyz',
        '*.intergram.xyz',
        'unpkg.com',
        '*.unpkg.com',
    ];
    $out = [];
    foreach ($hosts as $h) {
        $h = strtolower(trim((string)$h));
        if ($h !== '' && !in_array($h, $out, true)) {
            $out[] = $h;
        }
    }
    return $out;
}

function wifi_otantik_walled_ips()
{
    return ['83.147.38.107'];
}

function wifi_otantik_rsc_walled_garden()
{
    $out = '';
    foreach (wifi_otantik_walled_hosts() as $h) {
        $h = wifi_otantik_ros_str($h);
        $out .= ':do {' . "\n";
        $out .= '    :if ([:len [/ip hotspot walled-garden find dst-host="' . $h . '"]] = 0) do={' . "\n";
        $out .= '        /ip hotspot walled-garden add action=allow comment=wifi-otantik dst-host="' . $h . '"' . "\n";
        $out .= "    }\n";
        $out .= "} on-error={}\n";
    }
    foreach (wifi_otantik_walled_ips() as $ip) {
        $ip = wifi_otantik_ros_str($ip);
        $out .= ':do {' . "\n";
        $out .= '    :if ([:len [/ip hotspot walled-garden ip find dst-address="' . $ip . '"]] = 0) do={' . "\n";
        $out .= '        /ip hotspot walled-garden ip add action=accept comment=wifi-otantik dst-address="' . $ip . '"' . "\n";
        $out .= "    }\n";
        $out .= "} on-error={}\n";
    }
    return $out;
}

function wifi_otantik_rsc_hotspot($r, $rosVersion = '7')
{
    $spec = wifi_otantik_hs_pool_spec($r);
    $pref = trim((string)($r['pppoe_interface'] ?? ''));
    $ssid = trim((string)($r['ssid'] ?? ''));
    if ($ssid === '') {
        $name = trim((string)($r['name'] ?? ''));
        $ssid = $name !== '' ? $name . ' WiFi' : '';
    }
    $detect = wifi_otantik_rsc_lan_detect($pref, $ssid);
    $wg = wifi_otantik_rsc_walled_garden();
    $pool = wifi_otantik_ros_str($spec['name']);
    $gw = wifi_otantik_ros_str($spec['local']);
    $ranges = wifi_otantik_ros_str($spec['ranges']);
    $net = wifi_otantik_ros_str($spec['net']);
    $dhcp = wifi_otantik_ros_str($spec['dhcp']);
    $server = wifi_otantik_ros_str($spec['server']);
    $profile = wifi_otantik_ros_str($spec['profile']);
    $dns = wifi_otantik_ros_str($spec['dns']);
    $mask = (int)($spec['mask'] ?? 24);
    $out = <<<RSC
:put "Creating full hotspot (pool, DHCP, server)..."
{$detect}
:if ([:len [/ip pool find name="{$pool}"]] = 0) do={
    /ip pool add name="{$pool}" ranges={$ranges}
} else={
    /ip pool set [find name="{$pool}"] ranges={$ranges}
}
:if (\$hsIface != "") do={
    :if ([:len [/ip address find comment="wifi-otantik-hs"]] = 0) do={
        :do { /ip address add address={$gw}/{$mask} interface=\$hsIface comment=wifi-otantik-hs } on-error={}
    } else={
        :do { /ip address set [find comment="wifi-otantik-hs"] address={$gw}/{$mask} interface=\$hsIface } on-error={}
    }
    :if ([:len [/ip dhcp-server find interface=\$hsIface]] = 0) do={
        :do { /ip dhcp-server add name="{$dhcp}" interface=\$hsIface address-pool="{$pool}" lease-time=1h disabled=no } on-error={}
    } else={
        :do { /ip dhcp-server set [find interface=\$hsIface] address-pool="{$pool}" disabled=no } on-error={}
    }
}
:if ([:len [/ip dhcp-server network find address="{$net}"]] = 0) do={
    :do { /ip dhcp-server network add address={$net} gateway={$gw} dns-server={$gw} comment=wifi-otantik-hs } on-error={}
} else={
    :do { /ip dhcp-server network set [find address="{$net}"] gateway={$gw} dns-server={$gw} } on-error={}
}
:if ([:len [/ip firewall nat find comment="wifi-otantik-hs-masq"]] = 0) do={
    /ip firewall nat add chain=srcnat src-address={$net} action=masquerade comment=wifi-otantik-hs-masq
}
:if ([:len [/ip firewall filter find comment="wifi-otantik-hs-no-fasttrack"]] = 0) do={
    :do {
        /ip firewall filter add chain=forward src-address={$net} action=accept comment=wifi-otantik-hs-no-fasttrack place-before=0
        /ip firewall filter add chain=forward dst-address={$net} action=accept comment=wifi-otantik-hs-no-fasttrack place-before=0
    } on-error={}
} else={
    :do { /ip firewall filter move [find comment="wifi-otantik-hs-no-fasttrack"] destination=0 } on-error={}
}
:do { /ip dns set allow-remote-requests=yes servers=1.1.1.1,8.8.8.8 } on-error={}
:if ([:len [/ip dns static find comment="wifi-otantik-hs"]] = 0) do={
    :do { /ip dns static add name="{$dns}" address={$gw} comment=wifi-otantik-hs } on-error={}
}
:if ([:len [/ip hotspot profile find name="{$profile}"]] = 0) do={
    :do { /ip hotspot profile add name={$profile} hotspot-address={$gw} dns-name="" html-directory=hotspot login-by=cookie,http-chap,http-pap } on-error={}
} else={
    :do { /ip hotspot profile set [find name="{$profile}"] hotspot-address={$gw} dns-name="" html-directory=hotspot } on-error={}
}
:do { /ip hotspot user profile set [find default=yes] address-pool="{$pool}" } on-error={}
:if (\$hsIface != "") do={
    :if ([:len [/ip hotspot find]] = 0) do={
        :do { /ip hotspot add name={$server} interface=\$hsIface address-pool="{$pool}" profile={$profile} disabled=no } on-error={}
    } else={
        :do { /ip hotspot set [:pick [/ip hotspot find] 0] interface=\$hsIface address-pool="{$pool}" profile={$profile} disabled=no } on-error={}
    }
}
:do {
    :if ([:len [/ip hotspot walled-garden ip find comment="wifi-otantik-winbox"]] = 0) do={
        /ip hotspot walled-garden ip add action=accept protocol=tcp dst-port=8291 comment=wifi-otantik-winbox
        /ip hotspot walled-garden ip add action=accept protocol=tcp dst-port=8728 comment=wifi-otantik-api
        /ip hotspot walled-garden ip add action=accept protocol=tcp dst-port=22 comment=wifi-otantik-ssh
    }
} on-error={}
:put "Installing walled garden..."
{$wg}
:do { /ip hotspot profile set [find name="{$profile}"] html-directory=hotspot } on-error={}

RSC;
    if ((int)($r['trial_on'] ?? 0) === 1) {
        $rate = wifi_otantik_trial_rate($r);
        $uptime = wifi_otantik_ros_from_mins($r['trial_mins'] ?? 15);
        // RouterOS 6 and 7 use different property names for this; the
        // wrong one is a *parse-time* rejection of the whole /import, not
        // a runtime error, so :do{}on-error{} can't catch it - the correct
        // name must be chosen here, before the script is generated.
        $trialUptimeProp = ($rosVersion === '6') ? 'trial-uptime-limit' : 'trial-uptime';
        $out .= <<<RSC
:put "Enabling hotspot trial..."
:if ([:len [/ip hotspot user profile find name="wo-trial"]] = 0) do={
    :do { /ip hotspot user profile add name=wo-trial shared-users=1 rate-limit="{$rate}" address-pool="{$pool}" session-timeout={$uptime} } on-error={}
} else={
    :do { /ip hotspot user profile set [find name="wo-trial"] shared-users=1 rate-limit="{$rate}" address-pool="{$pool}" session-timeout={$uptime} } on-error={}
}
:do { /ip hotspot profile set [find name="{$profile}"] login-by=cookie,http-chap,http-pap,trial trial-user-profile=wo-trial {$trialUptimeProp}={$uptime} } on-error={}

RSC;
    }
    return $out;
}

function wifi_otantik_rsc_captive($r)
{
    $slug = rawurlencode((string)$r['slug']);
    $host = wifi_otantik_public_host();
    $out = ":put \"Installing captive portal files...\"\n";
    $out .= ":delay 2s\n";
    foreach (OtantikCaptive::files() as $f) {
        $path = '/hotspot/' . $slug . '/' . $f;
        $url = 'https://' . $host . $path;
        $dst = 'hotspot/' . $f;
        $fEsc = wifi_otantik_ros_str($f);
        $urlEsc = wifi_otantik_ros_str($url);
        $pathEsc = wifi_otantik_ros_str($path);
        $dstEsc = wifi_otantik_ros_str($dst);
        $out .= ':put "  ' . $fEsc . "\"\n";
        $out .= ':do {' . "\n";
        $out .= '    /tool fetch url="' . $urlEsc . '" mode=https check-certificate=no dst-path="' . $dstEsc . '"' . "\n";
        $out .= '} on-error={' . "\n";
        $out .= '    :do { /tool fetch url="http://' . wifi_otantik_ros_str($host) . $pathEsc . '" mode=http dst-path="' . $dstEsc . '" } on-error={ :put "  skip ' . $fEsc . '" }' . "\n";
        $out .= "}\n";
    }
    $out .= ':do { /ip hotspot profile set [find name="hs-otantik"] html-directory=hotspot } on-error={}' . "\n";
    // Never strip trial here: reinstalling captive HTML used to reset login-by
    // without "trial", so "Essai gratuit" looked like an invalid ticket code.
    if ((int)($r['trial_on'] ?? 0) === 1) {
        $uptime = wifi_otantik_ros_from_mins($r['trial_mins'] ?? 15);
        $rate = wifi_otantik_trial_rate($r);
        $pool = wifi_otantik_ros_str(wifi_otantik_hs_pool_spec($r)['name']);
        $out .= ':if ([:len [/ip hotspot user profile find name="wo-trial"]] = 0) do={' . "\n";
        $out .= '    :do { /ip hotspot user profile add name=wo-trial shared-users=1 rate-limit="' . $rate . '" address-pool="' . $pool . '" session-timeout=' . $uptime . ' } on-error={}' . "\n";
        $out .= '} else={' . "\n";
        $out .= '    :do { /ip hotspot user profile set [find name="wo-trial"] shared-users=1 rate-limit="' . $rate . '" address-pool="' . $pool . '" session-timeout=' . $uptime . ' } on-error={}' . "\n";
        $out .= '}' . "\n";
        $out .= ':do { /ip hotspot profile set [find name="hs-otantik"] login-by=cookie,http-chap,http-pap,trial trial-user-profile=wo-trial } on-error={}' . "\n";
        $out .= ':do { /ip hotspot profile set [find name="hs-otantik"] trial-uptime=' . $uptime . ' } on-error={' . "\n";
        $out .= '    :do { /ip hotspot profile set [find name="hs-otantik"] trial-uptime-limit=' . $uptime . ' } on-error={}' . "\n";
        $out .= '}' . "\n";
    } else {
        $out .= ':do { /ip hotspot profile set [find name="hs-otantik"] login-by=cookie,http-chap,http-pap } on-error={}' . "\n";
    }
    $out .= ':put "Captive portal installed."' . "\n";
    return $out;
}

function wifi_otantik_ros_choice($router)
{
    $v = strtolower(trim((string)($router['ros_version'] ?? 'auto')));
    if ($v === '6' || $v === '7') {
        return $v;
    }
    $ip = (string)($router['ip_address'] ?? '');
    if (strpos($ip, '10.89.') === 0) {
        return '6';
    }
    if (strpos($ip, '10.88.') === 0) {
        return '7';
    }
    return 'auto';
}

function wifi_otantik_tunnel($router, $peer = null)
{
    if (!$peer && !empty($router['id'])) {
        $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', $router['id'])->find_one();
    }
    $ros = wifi_otantik_ros_choice($router);
    $wgIp = $peer ? (string)$peer['wg_ip'] : (string)($router['ip_address'] ?? '');
    $l2Ip = $peer ? trim((string)$peer['ppp_ip']) : '';
    $method = 'wg';
    if ($ros === '6') {
        $method = 'l2tp';
    } elseif ($ros === 'auto' && strpos((string)($router['ip_address'] ?? ''), '10.89.') === 0) {
        $method = 'l2tp';
    }
    $slug = (string)($router['slug'] ?? '');
    return [
        'ros' => $ros,
        'method' => $method,
        'label' => $method === 'l2tp' ? 'RouterOS 6 · L2TP/IPsec' : 'RouterOS 7 · WireGuard',
        'short' => $method === 'l2tp' ? 'L2TP' : 'WG',
        'ip' => ($method === 'l2tp' && $l2Ip !== '') ? $l2Ip : $wgIp,
        'wg_ip' => $wgIp,
        'l2tp_ip' => $l2Ip,
        'bootstrap' => wifi_otantik_bootstrap_cmd($slug, $ros),
        'bootstrap6' => wifi_otantik_bootstrap_cmd($slug, '6'),
        'bootstrap7' => wifi_otantik_bootstrap_cmd($slug, '7'),
        'bootstrap_auto' => wifi_otantik_bootstrap_cmd($slug, 'auto'),
    ];
}

function wifi_otantik_connect_script($router)
{
    $ros = wifi_otantik_ros_choice($router);
    if ($ros === '6') {
        return wifi_otantik_rsc_ros6((int)$router['id']);
    }
    if ($ros === '7') {
        return wifi_otantik_rsc((int)$router['id']);
    }
    return wifi_otantik_rsc_boot((int)$router['id']);
}

function wifi_otantik_apply_ros_ip($router, $peer = null)
{
    if (!$peer) {
        $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', $router['id'])->find_one();
    }
    if (!$peer) {
        return $router;
    }
    $ros = wifi_otantik_ros_choice($router);
    if ($ros === '6' && trim((string)$peer['ppp_ip']) !== '') {
        $router->ip_address = $peer['ppp_ip'];
        $router->save();
    } elseif ($ros === '7' && trim((string)$peer['wg_ip']) !== '') {
        $router->ip_address = $peer['wg_ip'];
        $router->save();
    }
    return $router;
}

function wifi_otantik_bootstrap_cmd($slug, $ros = 'auto')
{
    $host = wifi_otantik_public_host();
    $ros = (string)$ros;
    if ($ros === '6') {
        $path = '/provision/' . rawurlencode($slug) . '.ros6.rsc';
        $dst = 'wo.rsc';
    } elseif ($ros === '7') {
        $path = '/provision/' . rawurlencode($slug) . '.rsc';
        $dst = 'wo.rsc';
    } else {
        $path = '/provision/' . rawurlencode($slug) . '.boot.rsc';
        $dst = 'wo-boot.rsc';
    }
    $url = 'https://' . $host . $path;
    // Prefer domain URL (not raw VPS IP) in the command shown to operators.
    return '/tool fetch url="' . $url . '" mode=https check-certificate=no dst-path=' . $dst
        . '; :delay 2s'
        . '; :do { /system scheduler remove [find name=wo-setup] } on-error={}'
        . '; :do { /system script remove [find name=wo-setup] } on-error={}'
        . '; /system script add name=wo-setup source={/system scheduler remove [find name=wo-setup]; /import file-name=' . $dst . '; /system script remove [find name=wo-setup]}'
        . '; /system scheduler add name=wo-setup interval=5s on-event=wo-setup';
}

function wifi_otantik_provision_slug()
{
    if (!empty($_GET['slug'])) {
        $s = trim((string)$_GET['slug']);
        $s = preg_replace('/\.(boot|ros6)\.rsc$/i', '', $s);
        return preg_replace('/\.rsc$/i', '', $s);
    }
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    if (preg_match('#/provision/([A-Za-z0-9_-]+)#', $path, $m)) {
        return $m[1];
    }
    global $routes;
    $s = (string)($routes[1] ?? '');
    $s = preg_replace('/\.(boot|ros6)\.rsc$/i', '', $s);
    return preg_replace('/\.rsc$/i', '', $s);
}

function wifi_otantik_provision_kind()
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $part = (string)($_GET['kind'] ?? '');
    if ($part === '') {
        global $routes;
        $part = (string)($routes[1] ?? $uri);
    }
    if (preg_match('/\.boot\.rsc/i', $part) || preg_match('/\.boot\.rsc/i', $uri)) {
        return 'boot';
    }
    if (preg_match('/\.ros6\.rsc/i', $part) || preg_match('/\.ros6\.rsc/i', $uri)) {
        return 'ros6';
    }
    return 'ros7';
}

function wifi_otantik_provision_status_code()
{
    $st = '';
    if (!empty($_GET['s'])) {
        $st = (string)$_GET['s'];
    } else {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        if (preg_match('#/status/([A-Za-z0-9_]+)#', $path, $m)) {
            $st = $m[1];
        } else {
            global $routes;
            if (($routes[2] ?? '') === 'status') {
                $st = (string)($routes[3] ?? 'ok');
            }
        }
    }
    $st = preg_replace('/[^a-z0-9_]+/', '', $st);
    if ($st === '') {
        $st = 'ok';
    }
    return substr($st, 0, 48);
}

function wifi_otantik_rsc_file()
{
    Tenant::ensure_schema();
    $slug = wifi_otantik_provision_slug();
    if ($slug === '') {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "# missing slug\n";
        exit;
    }
    $r = ORM::for_table('tbl_routers')->where('slug', $slug)->find_one();
    if (!$r) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "# router not found\n";
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: inline; filename="wifi-otantik.rsc"');
    $kind = wifi_otantik_provision_kind();
    if ($kind === 'boot') {
        echo wifi_otantik_rsc_boot((int)$r['id']);
    } elseif ($kind === 'ros6') {
        echo wifi_otantik_rsc_ros6((int)$r['id']);
    } else {
        echo wifi_otantik_rsc((int)$r['id']);
    }
    exit;
}

function wifi_otantik_provision_ping()
{
    Tenant::ensure_schema();
    $slug = wifi_otantik_provision_slug();
    $st = wifi_otantik_provision_status_code();
    $r = ORM::for_table('tbl_routers')->where('slug', $slug)->find_one();
    if ($r) {
        $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', $r['id'])->find_one();
        if ($peer) {
            // Intermediate steps (setting_identity, configuring_*) must NOT
            // overwrite the peer with a raw English token that then sticks in
            // the notification bell forever. Only "completed" flips the zone online.
            if ($st === 'completed') {
                $peer->status = 'completed';
                // Confirmed back online - clear the "already alerted" marker
                // so the next outage gets a fresh WhatsApp notice instead of
                // staying silent forever after the first one.
                if (class_exists('OtantikWhatsApp')) {
                    OtantikWhatsApp::zone_alert_clear((int)$r['id']);
                }
            } elseif ((string)$peer['status'] !== 'completed') {
                $peer->status = 'pending';
            }
            $peer->last_seen = date('Y-m-d H:i:s');
            $peer->save();
        }
    }
    header('Content-Type: text/plain');
    echo 'ok';
    exit;
}

function wifi_otantik_rsc_boot($routerId)
{
    $r = ORM::for_table('tbl_routers')->find_one($routerId);
    if (!$r) {
        return '';
    }
    $slug = $r['slug'];
    $host = wifi_otantik_public_host();
    return <<<RSC
# Wifi-Otantik bootstrap ROS6/ROS7
:local v [:pick [/system resource get version] 0 1]
:if (\$v = "7") do={
    /tool fetch url="https://{$host}/provision/{$slug}.rsc" mode=https check-certificate=no dst-path=wo.rsc
} else={
    /tool fetch url="https://{$host}/provision/{$slug}.ros6.rsc" mode=https check-certificate=no dst-path=wo.rsc
}
:delay 3s
:do { /system scheduler remove [find name=wo-setup] } on-error={}
:do { /system script remove [find name=wo-setup] } on-error={}
/system script add name=wo-setup source={/system scheduler remove [find name=wo-setup]; /import file-name=wo.rsc; /system script remove [find name=wo-setup]}
/system scheduler add name=wo-setup interval=5s on-event=wo-setup
:put "Wifi-Otantik: import programme. WinBox peut se couper, le script continue sur le routeur."
RSC;
}

function wifi_otantik_rsc_ros6($routerId)
{
    global $config;
    $r = ORM::for_table('tbl_routers')->find_one($routerId);
    if (!$r) {
        return '';
    }
    $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', $r['id'])->find_one();
    if (!$peer) {
        return '';
    }
    $peer = wifi_otantik_ensure_ppp($peer);
    $psk = wifi_otantik_ros_str($config['otantik_l2tp_psk'] ?? '');
    $host = wifi_otantik_ros_str($config['otantik_l2tp_endpoint'] ?? '83.147.38.107');
    $user = wifi_otantik_ros_str($peer['ppp_user']);
    $pass = wifi_otantik_ros_str($peer['ppp_pass']);
    $name = wifi_otantik_ros_str($r['name']);
    $slug = wifi_otantik_ros_str($r['slug']);
    $pay = wifi_otantik_pay_url($r['slug']);
    $pub = wifi_otantik_public_host();
    $statusHttp = 'https://' . $pub . '/provision/' . rawurlencode($r['slug']) . '/status/';
    $hotspotRsc = wifi_otantik_rsc_hotspot($r, '6');
    $pppoeRsc = wifi_otantik_rsc_pppoe((int)$r['id']);
    $captiveRsc = wifi_otantik_rsc_captive($r);
    $date = date('Y-m-d H:i:s');
    return <<<RSC
# ==================================================
# Wifi-Otantik - Provision RouterOS 6 (L2TP/IPsec)
# Zone : {$name}
# Genere le : {$date}
# ==================================================
:put "==== Wifi-Otantik ROS6 START ===="
:put "WinBox peut se couper: le script tourne en local (scheduler)."
:local shortname "{$name}"
:local l2tpName "l2tp-otantik"
:local l2tpServer "{$host}"
:local l2tpUser "{$user}"
:local l2tpPass "{$pass}"
:local l2tpPsk "{$psk}"
:local statusURL "{$statusHttp}"
:local payUrl "{$pay}"

/ip dns set servers=1.1.1.1,8.8.8.8
/ip dns cache flush

:put "Setting identity..."
:do { /tool fetch mode=https check-certificate=no url=(\$statusURL . "setting_identity") keep-result=no } on-error={}
/system identity set name="\$shortname"

:put "Configuring L2TP..."
:do { /tool fetch mode=https check-certificate=no url=(\$statusURL . "configuring_l2tp") keep-result=no } on-error={}
:do { /ip ipsec proposal set [find default=yes] enc-algorithms=aes-128-cbc auth-algorithms=sha1 pfs-group=modp1024 } on-error={}
:if ([:len [/interface l2tp-client find name="\$l2tpName"]] = 0) do={
    /interface l2tp-client add name="\$l2tpName" connect-to="\$l2tpServer" user="\$l2tpUser" password="\$l2tpPass" use-ipsec=yes ipsec-secret="\$l2tpPsk" profile=default add-default-route=no disabled=no
} else={
    /interface l2tp-client set [find name="\$l2tpName"] connect-to="\$l2tpServer" user="\$l2tpUser" password="\$l2tpPass" use-ipsec=yes ipsec-secret="\$l2tpPsk" profile=default add-default-route=no disabled=no
}

:put "Configuring firewall..."
:if ([:len [/ip firewall filter find comment="wifi-otantik-mgmt"]] = 0) do={
    :do { /ip firewall filter add chain=input src-address=10.89.0.1 action=accept comment=wifi-otantik-mgmt place-before=0 } on-error={
        /ip firewall filter add chain=input src-address=10.89.0.1 action=accept comment=wifi-otantik-mgmt
    }
} else={
    /ip firewall filter set [find comment="wifi-otantik-mgmt"] src-address=10.89.0.1 action=accept
}
:if ([:len [/ip firewall mangle find comment="wifi-otantik-mss"]] = 0) do={
    /ip firewall mangle add chain=forward action=change-mss new-mss=1360 passthrough=yes protocol=tcp tcp-flags=syn out-interface="\$l2tpName" comment=wifi-otantik-mss
}

:put "Enabling API..."
/ip service set api disabled=no port=8728

{$hotspotRsc}
{$captiveRsc}
{$pppoeRsc}
:put "Setting clock..."
/system ntp client set enabled=yes primary-ntp=1.1.1.1 secondary-ntp=8.8.8.8
:do { /system clock set time-zone-name=Africa/Douala } on-error={}

:put ("==== Wifi-Otantik ROS6 COMPLETED. Achat: " . \$payUrl . " ====")
:do { /tool fetch mode=https check-certificate=no url=(\$statusURL . "completed") keep-result=no } on-error={}
RSC;
}

function wifi_otantik_rsc($routerId)
{
    global $config;
    $r = ORM::for_table('tbl_routers')->find_one($routerId);
    if (!$r) {
        return '';
    }
    $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', $r['id'])->find_one();
    if (!$peer) {
        return '';
    }
    $endpoint = $config['otantik_wg_endpoint'] ?? '83.147.38.107:51820';
    $serverPub = trim($config['otantik_wg_server_public'] ?? '');
    $pay = wifi_otantik_pay_url($r['slug']);
    $priv = wifi_otantik_ros_str($peer['private_key']);
    $ip = $peer['wg_ip'];
    $ep = explode(':', $endpoint, 2);
    $epHost = $ep[0];
    $epPort = $ep[1] ?? '51820';
    $name = wifi_otantik_ros_str($r['name']);
    $slug = wifi_otantik_ros_str($r['slug']);
    $statusUrl = Tenant::url_wifi('/provision/' . rawurlencode($r['slug']) . '/status/');
    $hotspotRsc = wifi_otantik_rsc_hotspot($r, '7');
    $pppoeRsc = wifi_otantik_rsc_pppoe((int)$r['id']);
    $captiveRsc = wifi_otantik_rsc_captive($r);
    $date = date('Y-m-d H:i:s');
    return <<<RSC
# ==================================================
# Wifi-Otantik - Provision automatique
# Zone : {$name}
# Genere le : {$date}
# ==================================================
:put "==== Wifi-Otantik Provision START ===="
:put "WinBox peut se couper: le script tourne en local (scheduler)."

# ---- VARIABLES ----
:local shortname "{$name}"
:local slug "{$slug}"
:local wgName "wg-otantik"
:local wgListenPort 13231
:local wgPrivateKey "{$priv}"
:local wgServerPublicKey "{$serverPub}"
:local wgServerIP "{$epHost}"
:local wgServerPort {$epPort}
:local wgNetwork "10.88.0.0/16"
:local wgLocalIP "{$ip}/32"
:local wgServerTunnelIP "10.88.0.1/32"
:local statusURL "{$statusUrl}"
:local payUrl "{$pay}"

# ---- DNS ----
/ip dns set servers=1.1.1.1,8.8.8.8
/ip dns cache flush

# ---- IDENTITE ----
:put "Setting identity..."
:do { /tool fetch mode=https check-certificate=no url=(\$statusURL . "setting_identity") keep-result=no } on-error={}
/system identity set name="\$shortname"

# ---- WIREGUARD ----
:put "Configuring WireGuard..."
:do { /tool fetch mode=https check-certificate=no url=(\$statusURL . "configuring_wireguard") keep-result=no } on-error={}
:if ([:len [/interface wireguard find name="\$wgName"]] = 0) do={
    /interface wireguard add name="\$wgName" listen-port=\$wgListenPort private-key="\$wgPrivateKey" mtu=1420 disabled=no
} else={
    /interface wireguard set [find name="\$wgName"] listen-port=\$wgListenPort private-key="\$wgPrivateKey" mtu=1420 disabled=no
}
:if ([:len [/ip address find interface="\$wgName"]] = 0) do={
    /ip address add address="\$wgLocalIP" interface="\$wgName" comment=wifi-otantik
} else={
    /ip address set [find interface="\$wgName"] address="\$wgLocalIP"
}
:if ([:len [/interface wireguard peers find interface="\$wgName"]] = 0) do={
    /interface wireguard peers add interface="\$wgName" public-key="\$wgServerPublicKey" endpoint-address="\$wgServerIP" endpoint-port=\$wgServerPort allowed-address="\$wgNetwork" persistent-keepalive=25s
} else={
    /interface wireguard peers set [find interface="\$wgName"] public-key="\$wgServerPublicKey" endpoint-address="\$wgServerIP" endpoint-port=\$wgServerPort allowed-address="\$wgNetwork" persistent-keepalive=25s
}
:if ([:len [/ip route find dst-address="\$wgNetwork" gateway="\$wgName"]] = 0) do={
    /ip route add dst-address="\$wgNetwork" gateway="\$wgName" comment="wifi-otantik-wg"
}

# ---- FIREWALL ----
:put "Configuring firewall..."
:do { /tool fetch mode=https check-certificate=no url=(\$statusURL . "configuring_firewall") keep-result=no } on-error={}
:if ([:len [/ip firewall filter find comment="wifi-otantik-wg-input"]] = 0) do={
    /ip firewall filter add chain=input protocol=udp dst-port=\$wgListenPort action=accept comment=wifi-otantik-wg-input
}
:if ([:len [/ip firewall filter find comment="wifi-otantik-mgmt"]] = 0) do={
    /ip firewall filter add chain=input src-address="\$wgServerTunnelIP" action=accept comment=wifi-otantik-mgmt place-before=0
} else={
    /ip firewall filter move [find comment=wifi-otantik-mgmt] destination=0
}
:if ([:len [/ip firewall filter find comment="wifi-otantik-icmp"]] = 0) do={
    /ip firewall filter add chain=input protocol=icmp in-interface="\$wgName" action=accept comment=wifi-otantik-icmp place-before=1
}
:if ([:len [/ip firewall mangle find comment="wifi-otantik-mss"]] = 0) do={
    /ip firewall mangle add chain=forward action=change-mss new-mss=clamp-to-pmtu passthrough=yes protocol=tcp tcp-flags=syn out-interface="\$wgName" comment=wifi-otantik-mss
}

# ---- API ----
:put "Enabling API..."
/ip service set api disabled=no port=8728

# ---- HOTSPOT + WALLED GARDEN ----
:do { /tool fetch mode=https check-certificate=no url=(\$statusURL . "configuring_walled_garden") keep-result=no } on-error={}
{$hotspotRsc}
{$captiveRsc}
{$pppoeRsc}
# ---- HORLOGE ----
:put "Setting clock..."
/system clock set time-zone-name=Africa/Douala
/system ntp client set enabled=yes
:if ([:len [/system ntp client servers find address="0.pool.ntp.org"]] = 0) do={
    /system ntp client servers add address=0.pool.ntp.org
}

# ---- FIN ----
:put ("==== Wifi-Otantik COMPLETED. Achat: " . \$payUrl . " ====")
:do { /tool fetch mode=https check-certificate=no url=(\$statusURL . "completed") keep-result=no } on-error={}
RSC;
}

function wifi_otantik_plans_go($type, $msg, $zoneId = 0)
{
    $z = (int)$zoneId;
    if ($z < 1) {
        $z = (int)_post('router_id');
    }
    $url = getUrl('plugin/wifi_otantik_plans');
    if ($z > 0) {
        $url .= '&zone=' . $z;
    }
    r2($url, $type, $msg);
}

function wifi_otantik_plans()
{
    global $admin, $routes, $DEVICE_PATH;
    _admin();
    Tenant::require_can('sales', $admin);
    $act = $routes[2] ?? '';
    $routers = Tenant::routers_query($admin)->find_many();
    if ($act === 'add' && _post('name_plan')) {
        $router = Tenant::require_router((int)_post('router_id'), $admin);
        $in = wifi_otantik_plan_post();
        $kind = wifi_otantik_plan_kind(_post('plan_type') ?: 'Hotspot');
        if ($in['name'] === '' || ($kind === 'PPPOE' && $in['price'] < 100) || ($kind !== 'PPPOE' && $in['price'] < 0)) {
            wifi_otantik_plans_go('e', 'Nom ou prix invalide');
        }
        $dup = ORM::for_table('tbl_plans')->where('name_plan', $in['name'])->where('routers', $router['name'])->where('type', $kind)->where('enabled', 1)->find_one();
        if ($dup) {
            wifi_otantik_plans_go('e', 'Ce tarif existe déjà sur cette zone');
        }
        $bw = ORM::for_table('tbl_bandwidth')->create();
        wifi_otantik_save_bw($bw, $in, $router['name']);
        $p = ORM::for_table('tbl_plans')->create();
        wifi_otantik_save_plan_row($p, $bw, $in, $kind, $router);
        try {
            wifi_otantik_push_plan_mikrotik($router, $p);
        } catch (Throwable $e) {
            wifi_otantik_plans_go('w', 'Tarif enregistré. Profil MikroTik: ' . $e->getMessage() . ' (API / tunnel ?)');
        }
        wifi_otantik_plans_go('s', 'Tarif créé');
    }
    if ($act === 'edit') {
        $p = Tenant::require_plan((int)($routes[3] ?? 0), $admin);
        $router = ORM::for_table('tbl_routers')->where('name', $p['routers'])->find_one();
        if (!$router) {
            r2(getUrl('plugin/wifi_otantik_plans'), 'e', 'Zone introuvable pour ce tarif');
        }
        Tenant::require_router((int)$router['id'], $admin);
        $kind = wifi_otantik_plan_kind($p);
        if (_post('name_plan')) {
            $in = wifi_otantik_plan_post();
            if ($in['name'] === '' || ($kind === 'PPPOE' && $in['price'] < 100) || ($kind !== 'PPPOE' && $in['price'] < 0)) {
                r2(getUrl('plugin/wifi_otantik_plans/edit/' . (int)$p['id']), 'e', 'Nom ou prix invalide');
            }
            $dup = ORM::for_table('tbl_plans')->where('name_plan', $in['name'])->where('routers', $router['name'])->where('type', $kind)->where('enabled', 1)->where_not_equal('id', (int)$p['id'])->find_one();
            if ($dup) {
                r2(getUrl('plugin/wifi_otantik_plans/edit/' . (int)$p['id']), 'e', 'Ce nom existe déjà sur cette zone');
            }
            $prevName = (string)$p['name_plan'];
            $bw = ORM::for_table('tbl_bandwidth')->find_one((int)$p['id_bw']);
            if (!$bw) {
                $bw = ORM::for_table('tbl_bandwidth')->create();
            }
            wifi_otantik_save_bw($bw, $in, $router['name']);
            wifi_otantik_save_plan_row($p, $bw, $in, $kind, $router);
            try {
                wifi_otantik_push_plan_mikrotik($router, $p, $prevName);
            } catch (Throwable $e) {
                wifi_otantik_plans_go('w', 'Tarif enregistré. Profil MikroTik: ' . $e->getMessage() . ' (API / tunnel ?)', (int)$router['id']);
            }
            wifi_otantik_plans_go('s', 'Tarif mis à jour', (int)$router['id']);
        }
        $row = wifi_otantik_plans_with_bw([$p])[0];
        wifi_otantik_ui('plugin/otantik_plan_edit.tpl', 'Modifier le tarif', [
            'p' => $row,
            'router' => $router,
            'kind' => $kind,
        ]);
        return;
    }
    if ($act === 'del') {
        $p = Tenant::require_plan((int)($routes[3] ?? 0), $admin);
        $zoneRouter = ORM::for_table('tbl_routers')->where('name', $p['routers'])->find_one();
        $zoneId = $zoneRouter ? (int)$zoneRouter['id'] : 0;
        $hasHistory = (bool)ORM::for_table('tbl_user_recharges')->where('plan_id', (int)$p['id'])->find_one();
        if ($hasHistory) {
            $p->enabled = 0;
            $p->save();
            wifi_otantik_plans_go('s', 'Tarif désactivé (des clients l\'ont déjà utilisé, il est conservé pour l\'historique)', $zoneId);
        } else {
            $p->delete();
            wifi_otantik_plans_go('s', 'Tarif supprimé', $zoneId);
        }
    }
    if ($act === 'trial') {
        $router = Tenant::require_router((int)_post('router_id'), $admin);
        $n = max(1, (int)_post('validity'));
        $unit = _post('validity_unit') ?: 'Mins';
        if (!in_array($unit, ['Mins', 'Hrs', 'Days'], true)) {
            $unit = 'Mins';
        }
        $mins = $n;
        if ($unit === 'Hrs') {
            $mins = $n * 60;
        } elseif ($unit === 'Days') {
            $mins = $n * 1440;
        }
        $router->trial_on = (int)_post('trial_on') === 1 ? 1 : 0;
        $router->trial_mins = max(1, min(10080, $mins));
        $router->trial_down = max(1, (int)_post('rate_down'));
        $router->trial_up = max(1, (int)(_post('rate_up') ?: _post('rate_down')));
        $router->save();
        try {
            wifi_otantik_push_trial($router);
        } catch (Throwable $e) {
            wifi_otantik_plans_go('w', 'Essai enregistré. MikroTik: ' . $e->getMessage() . ' (API / tunnel ?)');
        }
        wifi_otantik_plans_go('s', $router->trial_on ? 'Essai gratuit activé sur cette zone' : 'Essai gratuit désactivé');
    }
    $names = Tenant::router_names($admin);
    $plans = $names ? ORM::for_table('tbl_plans')->where_in('type', ['Hotspot', 'PPPOE'])->where_in('routers', $names)->order_by_desc('id')->find_many() : [];
    $plans = wifi_otantik_plans_with_bw($plans);
    $hotspot = [];
    $pppoe = [];
    foreach ($plans as $p) {
        if (wifi_otantik_plan_kind($p) === 'PPPOE') {
            $pppoe[] = $p;
        } else {
            $hotspot[] = $p;
        }
    }
    $trialCards = [];
    foreach ($routers as $r) {
        if ((int)($r['trial_on'] ?? 0) === 1) {
            $trialCards[] = [
                'name' => (string)$r['name'],
                'label' => wifi_otantik_trial_label($r['trial_mins'] ?? 0),
                'down' => (int)($r['trial_down'] ?? 1),
                'up' => (int)($r['trial_up'] ?? 1),
            ];
        }
    }
    $selZone = (int)_get('zone');
    if ($selZone < 1 && $routers) {
        $selZone = (int)$routers[0]['id'];
    }
    wifi_otantik_ui('plugin/otantik_plans.tpl', 'Mes tarifs', [
        'plans' => $plans,
        'hotspot_plans' => $hotspot,
        'pppoe_plans' => $pppoe,
        'routers' => $routers,
        'trial_cards' => $trialCards,
        'sel_zone' => $selZone,
        'sel_zone_from_url' => (int)_get('zone') > 0,
    ]);
}

function wifi_otantik_export_fmt($v)
{
    return OtantikExport::fmt($v);
}

function wifi_otantik_pay_status_label($st)
{
    $st = (int)$st;
    if ($st === 2) {
        return 'Payé';
    }
    if ($st === 1) {
        return 'Attente';
    }
    return 'Échec';
}

function wifi_otantik_wd_status_label($st)
{
    $st = strtoupper((string)$st);
    if ($st === 'SUCCESS') {
        return 'Réussi';
    }
    if ($st === 'FAILED') {
        return 'Échoué';
    }
    return 'En cours';
}

function wifi_otantik_export_payments($query, $title, $filename, $fmt)
{
    $headers = ['#', 'Date', 'Tél / identifiant', 'Forfait', 'Zone', 'Montant', 'Moyen', 'Statut'];
    $rows = [];
    foreach ($query->order_by_desc('id')->limit(5000)->find_many() as $r) {
        $rows[] = [
            (string)$r['id'],
            (string)($r['paid_date'] ?: $r['created_date']),
            (string)$r['username'],
            (string)$r['plan_name'],
            (string)$r['routers'],
            (string)$r['price'],
            (string)($r['payment_method'] ?: $r['gateway']),
            wifi_otantik_pay_status_label($r['status']),
        ];
    }
    OtantikExport::send($fmt, $filename, $title, $headers, $rows);
}

function wifi_otantik_customers()
{
    global $admin, $routes, $config;
    _admin();
    Tenant::require_can('sales', $admin);
    $act = $routes[2] ?? '';
    $names = Tenant::router_names($admin);
    if ($act === 'export') {
        $fmt = wifi_otantik_export_fmt($routes[3] ?? 'csv');
        $from = wifi_otantik_ymd(_get('from'));
        $to = wifi_otantik_ymd(_get('to'));
        $q = ORM::for_table('tbl_customers')->order_by_desc('id')->limit(5000);
        if (!Tenant::is_platform($admin)) {
            $q->where('created_by', Tenant::id($admin));
        }
        if ($from !== '') {
            $q->where_gte('created_at', $from . ' 00:00:00');
        }
        if ($to !== '') {
            $q->where_lt('created_at', $to . ' 00:00:00');
        }
        $active = [];
        try {
            $owned = Tenant::router_names($admin);
            if (Tenant::is_platform($admin) || $owned) {
                $rq = ORM::for_table('tbl_user_recharges')->where('status', 'on')->order_by_desc('id');
                if (!Tenant::is_platform($admin) && $owned) {
                    $rq->where_in('routers', $owned);
                }
                foreach ($rq->find_many() as $r) {
                    $u = (string)$r['username'];
                    if ($u !== '' && empty($active[$u])) {
                        $active[$u] = $r;
                    }
                }
            }
        } catch (Throwable $e) {
        }
        $out = [];
        foreach ($q->find_many() as $c) {
            $uname = (string)$c['username'];
            $rech = $active[$uname] ?? null;
            $ring = wifi_otantik_expiry_info($rech);
            $zone = $rech ? (string)$rech['routers'] : (string)($c['routers'] ?? '');
            $st = !empty($ring['none']) ? 'Aucun ticket' : (!empty($ring['expired']) ? 'Expiré' : 'Actif');
            $out[] = [
                $uname,
                $zone !== '' ? $zone : '—',
                (string)$c['phonenumber'],
                (string)($c['created_at'] ?? ''),
                strtoupper((string)$c['service_type']) === 'PPPOE' ? 'PPPoE' : 'Hotspot',
                !empty($ring['none']) ? '' : (string)$ring['ticket'],
                (string)($ring['end_label'] ?: ($ring['label'] ?? '')),
                $st,
            ];
        }
        OtantikExport::send($fmt, 'abonnes', 'Abonnés', [
            'Identifiant', 'Zone', 'Téléphone', 'Inscrit', 'Service', 'Ticket', 'Expiration', 'Statut',
        ], $out);
        return;
    }
    if ($act === 'pin') {
        $cust = ORM::for_table('tbl_customers')->find_one((int)($routes[3] ?? 0));
        if (!$cust || (!Tenant::is_platform($admin) && (int)$cust['created_by'] !== Tenant::id($admin))) {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'Abonné introuvable');
        }
        if (strtoupper((string)$cust['service_type']) !== 'PPPOE') {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'La position se définit pour les lignes PPPoE');
        }
        if (_post('save_pin')) {
            wifi_otantik_apply_customer_geo($cust, _post('latitude'), _post('longitude'));
            wifi_otantik_apply_customer_address($cust);
            $cust->save();
            r2(getUrl('plugin/wifi_otantik_map') . '&focus=pppoe-' . (int)$cust['id'], 's', 'Position enregistrée');
        }
        wifi_otantik_ui('plugin/otantik_pin.tpl', 'Position PPPoE', [
            'cust' => $cust,
        ]);
        return;
    }
    if ($act === 'kick') {
        $cust = ORM::for_table('tbl_customers')->find_one((int)($routes[3] ?? 0));
        if ($cust && (Tenant::is_platform($admin) || (int)$cust['created_by'] === Tenant::id($admin))) {
            $rech = null;
            try {
                $rech = ORM::for_table('tbl_user_recharges')->where('username', $cust['username'])->where('status', 'on')->order_by_desc('id')->find_one();
            } catch (Throwable $e) {
            }
            $plan = $rech ? ORM::for_table('tbl_plans')->find_one($rech['plan_id']) : null;
            $router = $rech ? ORM::for_table('tbl_routers')->where('name', $rech['routers'])->find_one() : null;
            if (!$plan) {
                $plan = ['type' => $cust['service_type'] ?: 'Hotspot', 'name_plan' => ''];
            }
            if (!$router && $names) {
                $router = ORM::for_table('tbl_routers')->where('name', $names[0])->find_one();
            }
            if ($router && Tenant::owns_router($router, $admin)) {
                try {
                    $client = wifi_otantik_mk_client($router);
                    $path = wifi_otantik_plan_kind($plan) === 'PPPOE' ? '/ppp/active/print' : '/ip/hotspot/active/print';
                    $field = wifi_otantik_plan_kind($plan) === 'PPPOE' ? 'name' : 'user';
                    $actq = new \PEAR2\Net\RouterOS\Request($path);
                    $actq->setQuery(\PEAR2\Net\RouterOS\Query::where($field, $cust['username']));
                    $aid = $client->sendSync($actq)->getProperty('.id');
                    if ($aid) {
                        $rm = new \PEAR2\Net\RouterOS\Request(wifi_otantik_plan_kind($plan) === 'PPPOE' ? '/ppp/active/remove' : '/ip/hotspot/active/remove');
                        $rm->setArgument('numbers', $aid);
                        $client->sendSync($rm);
                        wifi_otantik_mk_log($router, 'kick', (string)$cust['username'], 1, 'Session active coupée');
                    } else {
                        wifi_otantik_mk_log($router, 'kick', (string)$cust['username'], 1, 'Aucune session active');
                    }
                } catch (Throwable $e) {
                    wifi_otantik_mk_log($router, 'kick', (string)$cust['username'], 0, $e->getMessage());
                    r2(getUrl('plugin/wifi_otantik_customers'), 'e', $e->getMessage());
                }
            }
        }
        r2(getUrl('plugin/wifi_otantik_customers'), 's', 'Session déconnectée');
    }
    if ($act === 'delete') {
        $cust = ORM::for_table('tbl_customers')->find_one((int)($routes[3] ?? 0));
        if (!$cust || (!Tenant::is_platform($admin) && (int)$cust['created_by'] !== Tenant::id($admin))) {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'Abonné introuvable');
        }
        if (empty($_POST)) {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'Confirmation requise');
        }
        // Financial history must survive a mistaken-entry cleanup - only a
        // customer who was never actually charged can be deleted by a tenant.
        // The platform SuperAdmin can override this for support cases, but
        // even then only the customer PROFILE is removed below - the
        // underlying payment/ERP records are never touched, so revenue
        // reporting stays accurate regardless of who deleted the profile.
        $paidCount = (int)ORM::for_table('tbl_payment_gateway')->where('user_id', (int)$cust['id'])->where('status', 2)->count();
        if ($paidCount > 0 && !Tenant::is_platform($admin)) {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'Suppression impossible : cet abonné a déjà ' . $paidCount . ' paiement(s) enregistré(s). L’historique financier doit être conservé.');
        }
        $kind = strtoupper((string)$cust['service_type']) === 'PPPOE' ? 'PPPOE' : 'Hotspot';
        $rech = ORM::for_table('tbl_user_recharges')->where('customer_id', (int)$cust['id'])->order_by_desc('id')->find_one();
        $routerName = $rech ? (string)$rech['routers'] : (string)($cust['routers'] ?? '');
        $router = $routerName !== '' ? ORM::for_table('tbl_routers')->where('name', $routerName)->find_one() : null;
        if (!$router && $names) {
            $router = ORM::for_table('tbl_routers')->where('name', $names[0])->find_one();
        }
        if ($router && Tenant::owns_router($router, $admin)) {
            wifi_otantik_remove_user_mikrotik($router, (string)$cust['username'], $kind, 'delete_customer');
        }
        try {
            foreach (ORM::for_table('tbl_user_recharges')->where('customer_id', (int)$cust['id'])->find_many() as $r) {
                $r->delete();
            }
        } catch (Throwable $e) {
        }
        $cust->delete();
        r2(getUrl('plugin/wifi_otantik_customers'), 's', 'Abonné supprimé');
    }
    if ($act === 'edit_recharge') {
        $cust = ORM::for_table('tbl_customers')->find_one((int)($routes[3] ?? 0));
        if (!$cust || (!Tenant::is_platform($admin) && (int)$cust['created_by'] !== Tenant::id($admin))) {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'Abonné introuvable');
        }
        $back = getUrl('plugin/wifi_otantik_customers');
        $rech = ORM::for_table('tbl_user_recharges')->where('customer_id', (int)$cust['id'])->order_by_desc('id')->find_one();
        if (!$rech) {
            r2($back, 'e', 'Aucune recharge trouvée pour cet abonné');
        }
        if (_post('save_dates')) {
            $startD = wifi_otantik_ymd(_post('recharged_on'));
            $startT = trim((string)_post('recharged_time')) ?: '00:00:00';
            $endD = wifi_otantik_ymd(_post('expiration'));
            $endT = trim((string)_post('expiration_time')) ?: '00:00:00';
            if ($startD === '' || $endD === '') {
                r2(getUrl('plugin/wifi_otantik_customers/edit_recharge/') . $cust['id'], 'e', 'Dates invalides');
            }
            $startTs = strtotime($startD . ' ' . $startT);
            $endTs = strtotime($endD . ' ' . $endT);
            if ($endTs <= $startTs) {
                r2(getUrl('plugin/wifi_otantik_customers/edit_recharge/') . $cust['id'], 'e', 'La date d’expiration doit être après la date de recharge');
            }
            $rech->recharged_on = $startD;
            $rech->recharged_time = $startT;
            $rech->expiration = $endD;
            $rech->time = $endT;
            $rech->save();
            if ((int)($rech['trx_id'] ?? 0) > 0) {
                wifi_otantik_sync_trx_date((int)$rech['trx_id'], $startD . ' ' . $startT);
            }
            if (_post('sync_registration')) {
                $cust->created_at = $startD . ' ' . $startT;
                $cust->save();
            }
            r2($back, 's', 'Dates de recharge mises à jour');
        }
        wifi_otantik_ui('plugin/otantik_edit_recharge.tpl', 'Modifier les dates', [
            'cust' => $cust,
            'rech' => $rech,
        ]);
        return;
    }
    if ($act === 'recharge') {
        $cust = ORM::for_table('tbl_customers')->find_one((int)($routes[3] ?? 0));
        if (!$cust || (!Tenant::is_platform($admin) && (int)$cust['created_by'] !== Tenant::id($admin))) {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'Abonné introuvable');
        }
        if (strtoupper((string)$cust['service_type']) !== 'PPPOE') {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'La recharge interne est pour les lignes PPPoE');
        }
        wifi_otantik_admin_recharge_pppoe($admin, $cust);
        return;
    }
    if ($act === 'wa') {
        $cust = ORM::for_table('tbl_customers')->find_one((int)($routes[3] ?? 0));
        if (!$cust || (!Tenant::is_platform($admin) && (int)$cust['created_by'] !== Tenant::id($admin))) {
            r2(getUrl('plugin/wifi_otantik_customers'), 'e', 'Abonné introuvable');
        }
        if (_post('wa_tpl') && class_exists('OtantikWhatsApp')) {
            $tpl = trim((string)_post('wa_tpl'));
            $ok = OtantikWhatsApp::send_customer_tpl($cust, $tpl);
            r2(
                getUrl('plugin/wifi_otantik_customers/wa/') . (int)$cust['id'],
                $ok ? 's' : 'e',
                $ok ? 'Modèle envoyé.' : ('Échec : ' . (OtantikWhatsApp::$lastError !== '' ? OtantikWhatsApp::$lastError : 'erreur'))
            );
        }
        wifi_otantik_ui('plugin/otantik_customer_wa.tpl', 'Envoyer un modèle WhatsApp', [
            'cust' => $cust,
            'wa_ready' => class_exists('OtantikWhatsApp') && OtantikWhatsApp::ready(),
            'wa_catalog' => class_exists('OtantikWhatsApp') ? OtantikWhatsApp::customer_catalog() : [],
            'wa_last_error' => (string)($config['otantik_wa_last_error'] ?? ''),
        ]);
        return;
    }
    $from = wifi_otantik_ymd(_get('from'));
    $to = wifi_otantik_ymd(_get('to'));
    $qStr = wifi_otantik_q();
    $ty = (string)_get('ty');
    $st = (string)_get('st');
    $owned = Tenant::router_names($admin);
    $onList = [];
    $hadList = [];
    if (in_array($st, ['on', 'off', 'none'], true) && (Tenant::is_platform($admin) || $owned)) {
        $onUsers = [];
        $hadUsers = [];
        try {
            $rq = ORM::for_table('tbl_user_recharges');
            if (!Tenant::is_platform($admin) && $owned) {
                $rq->where_in('routers', $owned);
            }
            foreach ($rq->select('username')->select('status')->find_many() as $r) {
                $u = (string)$r['username'];
                if ($u === '') {
                    continue;
                }
                $hadUsers[$u] = true;
                if ((string)$r['status'] === 'on') {
                    $onUsers[$u] = true;
                }
            }
        } catch (Throwable $e) {
        }
        $onList = array_keys($onUsers);
        foreach ($hadUsers as $u => $_) {
            if (empty($onUsers[$u])) {
                $hadList[] = $u;
            }
        }
    }
    $makeCust = function () use ($admin, $from, $to, $qStr, $ty, $st, $onList, $hadList) {
        $q = ORM::for_table('tbl_customers');
        if (!Tenant::is_platform($admin)) {
            $q->where('created_by', Tenant::id($admin));
        }
        if ($from !== '') {
            $q->where_gte('created_at', $from . ' 00:00:00');
        }
        if ($to !== '') {
            $q->where_lt('created_at', $to . ' 00:00:00');
        }
        if ($ty === 'PPPOE' || $ty === 'Hotspot') {
            $q->where('service_type', $ty);
        }
        wifi_otantik_where_search($q, ['username', 'phonenumber', 'fullname'], $qStr);
        if ($st === 'on') {
            $onList ? $q->where_in('username', $onList) : $q->where_raw('1=0');
        } elseif ($st === 'off') {
            $hadList ? $q->where_in('username', $hadList) : $q->where_raw('1=0');
        } elseif ($st === 'none') {
            $skip = array_values(array_unique(array_merge($onList, $hadList)));
            if ($skip) {
                $q->where_raw('username NOT IN (' . implode(',', array_fill(0, count($skip), '?')) . ')', $skip);
            }
        }
        return $q;
    };
    $total = (int)$makeCust()->count();
    $pager = wifi_otantik_pager($total);
    $rows = wifi_otantik_paged($makeCust()->order_by_desc('id'), $pager)->find_many();
    $filterLabel = '';
    if ($from !== '' || $to !== '') {
        $a = $from !== '' ? date('d/m/Y', strtotime($from)) : '…';
        $bShow = $to !== '' ? date('d/m/Y', strtotime($to . ' -1 day')) : '…';
        $filterLabel = $from !== '' && $to !== '' && $to === date('Y-m-d', strtotime($from . ' +1 day'))
            ? ('Inscrits le ' . $a)
            : ('Inscrits du ' . $a . ' au ' . $bShow);
    }
    $active = [];
    try {
        $owned = Tenant::router_names($admin);
        if (Tenant::is_platform($admin) || $owned) {
            $rq = ORM::for_table('tbl_user_recharges')->where('status', 'on')->order_by_desc('id');
            if (!Tenant::is_platform($admin)) {
                $rq->where_in('routers', $owned);
            }
            foreach ($rq->find_many() as $r) {
                $u = (string)$r['username'];
                if ($u !== '' && empty($active[$u])) {
                    $active[$u] = $r;
                }
            }
        }
    } catch (Throwable $e) {
    }
    $slugs = [];
    $ownedNames = [];
    foreach (Tenant::routers_query($admin)->find_many() as $rr) {
        $slugs[(string)$rr['name']] = (string)$rr['slug'];
        $ownedNames[] = (string)$rr['name'];
    }
    $zoneOf = [];
    foreach ($active as $uname => $rech) {
        $zoneOf[$uname] = (string)$rech['routers'];
    }
    if ($ownedNames) {
        foreach (ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways())->where_in('routers', $ownedNames)->order_by_desc('id')->limit(400)->find_many() as $pg) {
            $u = (string)$pg['username'];
            if ($u !== '' && empty($zoneOf[$u])) {
                $zoneOf[$u] = (string)$pg['routers'];
            }
            $ch = (string)($pg['payment_channel'] ?? '');
            if (strpos($ch, ':') !== false) {
                $login = explode(':', $ch, 2)[0];
                if ($login !== '' && empty($zoneOf[$login])) {
                    $zoneOf[$login] = (string)$pg['routers'];
                }
            }
        }
    }
    $share = [];
    $zones = [];
    foreach ($rows as $c) {
        $uname = (string)$c['username'];
        $phone = (string)$c['phonenumber'];
        $zone = $zoneOf[$uname] ?? ($zoneOf[$phone] ?? '');
        if ($zone === '') {
            try {
                $zone = (string)($c['routers'] ?? '');
            } catch (Throwable $e) {
            }
        }
        $zones[(int)$c['id']] = $zone !== '' ? $zone : '—';
        $rech = $active[$uname] ?? null;
        $slug = $rech ? ($slugs[(string)$rech['routers']] ?? '') : ($zone !== '' ? ($slugs[$zone] ?? '') : '');
        if ($slug === '' && $slugs) {
            $slug = (string)reset($slugs);
        }
        if ($slug === '') {
            $share[(int)$c['id']] = '';
            continue;
        }
        if (strtoupper((string)$c['service_type']) === 'PPPOE') {
            $share[(int)$c['id']] = wifi_otantik_pppoe_recharge_url($slug, $c['username']);
        } else {
            $share[(int)$c['id']] = wifi_otantik_portal_buy_url($slug);
        }
    }
    $rings = [];
    foreach ($rows as $c) {
        $uname = (string)$c['username'];
        $rech = $active[$uname] ?? null;
        $rings[(int)$c['id']] = wifi_otantik_expiry_info($rech);
    }
    wifi_otantik_ui('plugin/otantik_customers.tpl', 'Abonnés', [
        'rows' => $rows,
        'active' => $active,
        'share' => $share,
        'zones' => $zones,
        'rings' => $rings,
        'router_names' => $ownedNames,
        'filter_label' => $filterLabel,
        'filter_from' => $from,
        'filter_to' => $to,
        'filter_q' => $qStr,
        'filter_st' => $st,
        'filter_ty' => $ty,
        'pager' => $pager,
        'export_csv' => getUrl('plugin/wifi_otantik_customers/export/csv') . ($from !== '' ? '&from=' . rawurlencode($from) : '') . ($to !== '' ? '&to=' . rawurlencode($to) : ''),
        'export_pdf' => getUrl('plugin/wifi_otantik_customers/export/pdf') . ($from !== '' ? '&from=' . rawurlencode($from) : '') . ($to !== '' ? '&to=' . rawurlencode($to) : ''),
    ]);
}

function wifi_otantik_sales()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('sales', $admin);
    if (($routes[2] ?? '') === 'paid') {
        wifi_otantik_mark_manual_paid($routes[3] ?? 0, $admin, getUrl('plugin/wifi_otantik_sales'));
        return;
    }
    if (($routes[2] ?? '') === 'export') {
        $fmt = wifi_otantik_export_fmt($routes[3] ?? 'csv');
        $q = ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways());
        $names = Tenant::router_names($admin);
        if ($names) {
            $q->where_in('routers', $names);
        } else {
            $q->where_raw('1=0');
        }
        wifi_otantik_export_payments($q, 'Mes ventes', 'ventes', $fmt);
        return;
    }
    $names = Tenant::router_names($admin);
    $qStr = wifi_otantik_q();
    $st = (string)_get('st');
    $ty = (string)_get('ty');
    $rows = [];
    $total = 0;
    $pager = wifi_otantik_pager(0);
    if ($names) {
        $make = function () use ($names, $qStr, $st, $ty) {
            $q = ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways())->where_in('routers', $names);
            if ($st === '0' || $st === '1' || $st === '2') {
                $q->where('status', (int)$st);
            }
            if (in_array($ty, ['MTN', 'ORANGE', 'CASH'], true)) {
                $q->where('payment_method', $ty);
            }
            wifi_otantik_where_search($q, ['username', 'plan_name', 'routers', 'payment_method'], $qStr);
            return $q;
        };
        $pager = wifi_otantik_pager((int)$make()->count());
        $rows = wifi_otantik_paged($make()->order_by_desc('id'), $pager)->find_many();
        $total = (float)ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways())->where('status', 2)->where_in('routers', $names)->sum('price');
    }
    wifi_otantik_ui('plugin/otantik_sales.tpl', 'Mes ventes', [
        'rows' => $rows,
        'total' => $total,
        'sales_tabs' => true,
        'filter_q' => $qStr,
        'filter_st' => $st,
        'filter_ty' => $ty,
        'pager' => $pager,
        'export_csv' => getUrl('plugin/wifi_otantik_sales/export/csv'),
        'export_pdf' => getUrl('plugin/wifi_otantik_sales/export/pdf'),
    ]);
}

function wifi_otantik_receipts()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('sales', $admin);
    $action = (string)($routes[2] ?? '');
    if ($action === 'edit') {
        // Editing a settled sale's amount/description touches money already
        // credited to the wallet and already synced into the ERP ledger -
        // reserved for the platform SuperAdmin, same override precedent as
        // deleting a customer with payment history.
        if (!Tenant::is_platform($admin)) {
            r2(getUrl('plugin/wifi_otantik_receipts'), 'e', 'Réservé à l’administrateur de la plateforme');
        }
        $trx = ORM::for_table('tbl_payment_gateway')->find_one((int)($routes[3] ?? 0));
        if (!$trx || (int)$trx['status'] !== 2) {
            r2(getUrl('plugin/wifi_otantik_receipts'), 'e', 'Reçu introuvable');
        }
        $back = getUrl('plugin/wifi_otantik_receipts/view/') . (int)$trx['id'];
        if (_post('save_receipt')) {
            $newPrice = (int)round((float)_post('price'));
            if ($newPrice < 0) {
                r2($back, 'e', 'Montant invalide');
            }
            $oldPrice = (int)round((float)$trx['price']);
            $trx->username = trim((string)_post('client')) ?: $trx['username'];
            $trx->plan_name = trim((string)_post('plan_name')) ?: $trx['plan_name'];
            $trx->price = (string)$newPrice;
            $trx->save();
            if ($newPrice !== $oldPrice) {
                $router = ORM::for_table('tbl_routers')->find_one((int)$trx['routers_id']);
                $owner = $router ? Tenant::tenant_row((int)$router['owner_id']) : null;
                $led = ORM::for_table('tbl_otantik_ledger')->where('trx_id', (int)$trx['id'])->find_one();
                if ($owner && $led) {
                    $newSplit = Tenant::split($newPrice, $owner);
                    $delta = (float)$newSplit['net'] - (float)$led['net'];
                    $owner->wallet = (float)$owner['wallet'] + $delta;
                    $owner->save();
                    $led->gross = $newSplit['gross'];
                    $led->rate = $newSplit['rate'];
                    $led->fee = $newSplit['fee'];
                    $led->net = $newSplit['net'];
                    $led->save();
                }
                try {
                    $flow = ORM::for_table('tbl_otantik_erp_flow')->where('ref_type', 'payment')->where('ref_id', (int)$trx['id'])->find_one();
                    if ($flow) {
                        $flow->amount = $newPrice;
                        $flow->label = (string)$trx['plan_name'];
                        $flow->save();
                    }
                } catch (Throwable $e) {
                }
            }
            r2($back, 's', 'Reçu mis à jour');
        }
        wifi_otantik_ui('plugin/otantik_receipt_edit.tpl', 'Modifier le reçu', [
            'trx' => $trx,
        ]);
        return;
    }
    if ($action === 'delete') {
        if (!Tenant::is_platform($admin)) {
            r2(getUrl('plugin/wifi_otantik_receipts'), 'e', 'Réservé à l’administrateur de la plateforme');
        }
        $trx = ORM::for_table('tbl_payment_gateway')->find_one((int)($routes[3] ?? 0));
        if (!$trx) {
            r2(getUrl('plugin/wifi_otantik_receipts'), 'e', 'Reçu introuvable');
        }
        if (empty($_POST)) {
            r2(getUrl('plugin/wifi_otantik_receipts/view/') . (int)$trx['id'], 'e', 'Confirmation requise');
        }
        $trxId = (int)$trx['id'];
        $router = ORM::for_table('tbl_routers')->find_one((int)$trx['routers_id']);
        $owner = $router ? Tenant::tenant_row((int)$router['owner_id']) : null;
        // Reverse exactly what this sale added, so removing the record can
        // never leave money in the wallet with nothing behind it.
        try {
            $led = ORM::for_table('tbl_otantik_ledger')->where('trx_id', $trxId)->find_one();
            if ($led && $owner) {
                $owner->wallet = (float)$owner['wallet'] - (float)$led['net'];
                $owner->save();
                $led->delete();
            }
        } catch (Throwable $e) {
        }
        try {
            $flow = ORM::for_table('tbl_otantik_erp_flow')->where('ref_type', 'payment')->where('ref_id', $trxId)->find_one();
            if ($flow) {
                $flow->delete();
            }
        } catch (Throwable $e) {
        }
        try {
            foreach (ORM::for_table('tbl_user_recharges')->where('trx_id', $trxId)->find_many() as $r) {
                $r->trx_id = 0;
                $r->save();
            }
        } catch (Throwable $e) {
        }
        $trx->delete();
        r2(getUrl('plugin/wifi_otantik_receipts'), 's', 'Reçu et vente supprimés, solde ajusté');
    }
    if ($action === 'view' || $action === 'print') {
        $trx = ORM::for_table('tbl_payment_gateway')->find_one((int)($routes[3] ?? 0));
        if (!$trx || (int)$trx['status'] !== 2 || !wifi_otantik_receipt_can_view($trx, $admin)) {
            r2(getUrl('plugin/wifi_otantik_receipts'), 'e', 'Reçu introuvable');
        }
        $receipt = wifi_otantik_receipt_payload($trx);
        $company = 'Wifi-Otantik';
        try {
            $router = ORM::for_table('tbl_routers')->find_one((int)$trx['routers_id']);
            if ($router) {
                $owner = Tenant::tenant_row((int)$router['owner_id']);
                if ($owner && trim((string)($owner['business_name'] ?? '')) !== '') {
                    $company = trim((string)$owner['business_name']);
                }
            }
        } catch (Throwable $e) {
        }
        wifi_otantik_ui('plugin/otantik_receipt_view.tpl', 'Reçu ' . $receipt['number'], [
            'receipt' => $receipt,
            'receipt_company' => $company,
            'receipt_qr_url' => wifi_otantik_receipt_qr_url($receipt, $company),
            'print_mode' => $action === 'print',
            'sales_tabs' => true,
        ]);
        return;
    }

    $names = Tenant::router_names($admin);
    $qStr = wifi_otantik_q();
    $ty = (string)_get('ty');
    $rows = [];
    $total = 0.0;
    $pager = wifi_otantik_pager(0);
    $cards = [];
    if ($names || Tenant::is_platform($admin)) {
        $make = function () use ($admin, $names, $qStr, $ty) {
            $q = ORM::for_table('tbl_payment_gateway')
                ->where_in('gateway', wifi_otantik_gateways())
                ->where('status', 2);
            if (!Tenant::is_platform($admin)) {
                if ($names) {
                    $q->where_in('routers', $names);
                } else {
                    $q->where_raw('1=0');
                }
            }
            if (in_array($ty, ['MTN', 'ORANGE', 'CASH'], true)) {
                $q->where('payment_method', $ty);
            }
            wifi_otantik_where_search($q, ['username', 'plan_name', 'routers', 'payment_method', 'trx_invoice'], $qStr);
            return $q;
        };
        $pager = wifi_otantik_pager((int)$make()->count());
        $rows = wifi_otantik_paged($make()->order_by_desc('id'), $pager)->find_many();
        $sumQ = ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways())->where('status', 2);
        if (!Tenant::is_platform($admin)) {
            $sumQ->where_in('routers', $names ?: ['__none__']);
        }
        $total = (float)$sumQ->sum('price');
        foreach ($rows as $r) {
            $cards[] = wifi_otantik_receipt_payload($r);
        }
    }
    wifi_otantik_ui('plugin/otantik_receipts.tpl', 'Reçus', [
        'rows' => $cards,
        'total' => $total,
        'sales_tabs' => true,
        'filter_q' => $qStr,
        'filter_ty' => $ty,
        'pager' => $pager,
        'count_paid' => (int)($pager['total'] ?? 0),
    ]);
}

function wifi_otantik_wd_max($wallet, $service, array $bals)
{
    $wallet = (int)floor(max(0, (float)$wallet));
    $service = strtoupper((string)$service);
    $liq = 0;
    if (empty($bals['split'])) {
        $liq = (int)floor((float)($bals['total'] ?? 0));
        if ($liq < 1 && ($bals['error'] ?? '') !== '') {
            return $wallet;
        }
    } else {
        $liq = (int)floor((float)($bals[$service] ?? 0));
    }
    $net = OtantikPay::withdraw_net_from_gross($liq);
    return min($wallet, $net);
}

function wifi_otantik_withdraw_guard($tenant, $amount, $phone)
{
    $amount = (int)round((float)$amount);
    $phone = wifi_otantik_phone($phone);
    $service = wifi_otantik_detect_service($phone);
    if ($amount < 500 || strlen($phone) < 9) {
        return [false, 'Montant ou numéro invalide', $service, []];
    }
    if ($service === '') {
        return [false, 'Numéro MTN MoMo ou Orange Money requis', '', []];
    }
    $wallet = (float)($tenant['wallet'] ?? 0);
    if ($amount > $wallet) {
        return [false, 'Votre caisse est insuffisante (' . (int)$wallet . ' F).', $service, []];
    }
    $bals = OtantikPay::payout_balances();
    $max = wifi_otantik_wd_max($wallet, $service, $bals);
    if ($amount > $max) {
        $label = $service === 'ORANGE' ? 'Orange Money' : 'MTN MoMo';
        return [false, 'Vous ne pouvez pas retirer ' . $amount . ' F vers ' . $label . ' pour le moment. Maximum : ' . $max . ' F. Les virements MTN et Orange sont séparés.', $service, $bals];
    }
    return [true, '', $service, $bals];
}

function wifi_otantik_wallet()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('finance_manage', $admin);
    if (($routes[2] ?? '') === 'export') {
        $fmt = wifi_otantik_export_fmt($routes[3] ?? 'csv');
        $headers = ['Date', 'Opérateur', 'Téléphone', 'Motif', 'Montant', 'Statut'];
        $out = [];
        foreach (ORM::for_table('tbl_otantik_withdrawals')->where('owner_id', Tenant::id($admin))->order_by_desc('id')->limit(5000)->find_many() as $w) {
            $out[] = [
                (string)$w['created_at'],
                (string)$w['service'],
                (string)$w['receiver'],
                (string)($w['reason'] ?? ''),
                (string)$w['amount'],
                wifi_otantik_wd_status_label($w['status']),
            ];
        }
        OtantikExport::send($fmt, 'retraits', 'Retraits', $headers, $out);
        return;
    }
    $tenant = Tenant::tenant_row(Tenant::id($admin));
    $u = ORM::for_table('tbl_users')->find_one($admin['id']);
    $profileEmail = strtolower(trim((string)($u['email'] ?? $admin['email'] ?? '')));
    $withdrawEmailOwner = (int)($admin['root'] ?? 0) === 0 || Tenant::is_platform($admin);
    if ($withdrawEmailOwner && _post('save_withdraw_email')) {
        $we = strtolower(trim((string)_post('withdraw_email')));
        if ($we !== '' && !filter_var($we, FILTER_VALIDATE_EMAIL)) {
            r2(getUrl('plugin/wifi_otantik_wallet'), 'e', 'Email invalide');
        }
        $tenant->withdraw_email = $we;
        $tenant->save();
        r2(getUrl('plugin/wifi_otantik_wallet'), 's', $we !== '' ? 'Email de retrait enregistré' : 'Email de retrait retiré - le code ira au profil de la personne qui retire');
    }
    // Every withdrawal code goes to this address once the owner sets one,
    // regardless of which staff member (with finance_manage) actually
    // clicks "Retirer" - keeps a real approval gate over payouts even when
    // several people can initiate one. Falls back to the initiator's own
    // profile email until the owner configures it.
    $email = trim((string)($tenant['withdraw_email'] ?? '')) !== '' ? strtolower(trim((string)$tenant['withdraw_email'])) : $profileEmail;
    $split = Tenant::split(1000, $tenant);
    if (_post('withdraw_code')) {
        if ($email === '') {
            r2(getUrl('plugin/wifi_otantik_wallet'), 'e', $withdrawEmailOwner ? 'Configurez l’email de retrait ci-dessous, ou un email dans Profil.' : 'Aucun email de retrait configuré - demandez au propriétaire de l’ajouter, ou ajoutez le vôtre dans Profil.');
        }
        list($ok, $payload, $msg) = OtantikAuth::consume($email, 'withdraw', _post('withdraw_code'));
        if (!$ok) {
            r2(getUrl('plugin/wifi_otantik_wallet') . '&confirm=1', 'e', $msg);
        }
        $_POST['withdraw_amount'] = $payload['amount'] ?? 0;
        $_POST['withdraw_phone'] = $payload['phone'] ?? '';
        $_POST['withdraw_reason'] = $payload['reason'] ?? ($_SESSION['wo_wd_reason'] ?? '');
        wifi_otantik_do_withdraw($admin, $tenant);
        return;
    }
    if (_post('withdraw_amount')) {
        $amount = (int)round((float)_post('withdraw_amount'));
        $phone = wifi_otantik_phone(_post('withdraw_phone') ?: $tenant['phone']);
        $reason = trim((string)_post('withdraw_reason'));
        if ($email === '') {
            r2(getUrl('plugin/wifi_otantik_wallet'), 'e', $withdrawEmailOwner ? 'Configurez l’email de retrait ci-dessous, ou un email dans Profil.' : 'Aucun email de retrait configuré - demandez au propriétaire de l’ajouter, ou ajoutez le vôtre dans Profil.');
        }
        if ($reason === '') {
            r2(getUrl('plugin/wifi_otantik_wallet') . '&new=1', 'e', 'Indiquez le motif du retrait (comptabilité).');
        }
        list($okWd, $wdMsg, $service) = wifi_otantik_withdraw_guard($tenant, $amount, $phone);
        if (!$okWd) {
            r2(getUrl('plugin/wifi_otantik_wallet') . '&new=1', 'e', $wdMsg);
        }
        list($sent, $hint) = OtantikAuth::issue($email, 'withdraw', [
            'amount' => $amount,
            'phone' => $phone,
            'service' => $service,
            'reason' => $reason,
        ]);
        if (!$sent) {
            r2(getUrl('plugin/wifi_otantik_wallet') . '&new=1', 'e', $hint);
        }
        $_SESSION['wo_wd_email'] = $email;
        $_SESSION['wo_wd_amount'] = $amount;
        $_SESSION['wo_wd_reason'] = $reason;
        r2(getUrl('plugin/wifi_otantik_wallet') . '&confirm=1', 's', 'Code envoyé à ' . $hint);
    }
    $qStr = wifi_otantik_q();
    $makeWd = function () use ($admin, $qStr) {
        $q = ORM::for_table('tbl_otantik_withdrawals')->where('owner_id', Tenant::id($admin));
        wifi_otantik_where_search($q, ['service', 'receiver', 'status', 'amount'], $qStr);
        return $q;
    };
    $pager = wifi_otantik_pager((int)$makeWd()->count());
    $wd = wifi_otantik_paged($makeWd()->order_by_desc('id'), $pager)->find_many();
    $ownerId = Tenant::id($admin);
    $wdTab = _get('wd') === 'year' ? 'year' : 'week';
    $wdFrom = $wdTab === 'year'
        ? date('Y-01-01 00:00:00')
        : wifi_otantik_range_start('week');
    $wdRangeAmt = 0.0;
    $wdRangeCount = 0;
    $wdPendingAmt = 0.0;
    $wdPendingCount = 0;
    try {
        $wdRangeAmt = (float)ORM::for_table('tbl_otantik_withdrawals')
            ->where('owner_id', $ownerId)->where('status', 'SUCCESS')->where_gte('created_at', $wdFrom)->sum('amount');
        $wdRangeCount = (int)ORM::for_table('tbl_otantik_withdrawals')
            ->where('owner_id', $ownerId)->where('status', 'SUCCESS')->where_gte('created_at', $wdFrom)->count();
        $wdPendingAmt = (float)ORM::for_table('tbl_otantik_withdrawals')
            ->where('owner_id', $ownerId)->where('status', 'PENDING')->sum('amount');
        $wdPendingCount = (int)ORM::for_table('tbl_otantik_withdrawals')
            ->where('owner_id', $ownerId)->where('status', 'PENDING')->count();
    } catch (Throwable $e) {
    }
    $commission = Tenant::effective_commission($tenant);
    // The real MeSomb/Orange float balance and per-network max are no longer
    // shown proactively on this page - they only surface as part of the
    // error message wifi_otantik_withdraw_guard() returns when a withdrawal
    // actually fails for insufficient funds, so tenants don't see the
    // platform's exact liquidity position.
    wifi_otantik_ui('plugin/otantik_wallet.tpl', 'Retraits', [
        'tenant' => $tenant,
        'withdrawals' => $wd,
        'filter_q' => $qStr,
        'pager' => $pager,
        'wd_tab' => $wdTab,
        'wd_range_amt' => $wdRangeAmt,
        'wd_range_count' => $wdRangeCount,
        'wd_pending_amt' => $wdPendingAmt,
        'wd_pending_count' => $wdPendingCount,
        'commission_rate' => $commission,
        'open_withdraw' => _get('new') === '1',
        'confirm_wd' => _get('confirm') === '1',
        'wd_email_hint' => OtantikAuth::hint($email),
        'wd_amount' => (int)($_SESSION['wo_wd_amount'] ?? 0),
        'split' => $split,
        'notify_email' => $email,
        'withdraw_email' => (string)($tenant['withdraw_email'] ?? ''),
        'withdraw_email_owner' => $withdrawEmailOwner,
        'export_csv' => getUrl('plugin/wifi_otantik_wallet/export/csv'),
        'export_pdf' => getUrl('plugin/wifi_otantik_wallet/export/pdf'),
    ]);
}

function wifi_otantik_do_withdraw($admin, $tenant)
{
    $amount = (int)round((float)_post('withdraw_amount'));
    $phone = wifi_otantik_phone(_post('withdraw_phone') ?: $tenant['phone']);
    list($okWd, $wdMsg, $service) = wifi_otantik_withdraw_guard($tenant, $amount, $phone);
    if (!$okWd) {
        r2(getUrl('plugin/wifi_otantik_wallet'), 'e', $wdMsg);
    }
    $reason = trim((string)_post('withdraw_reason'));
    if ($reason === '') {
        $reason = trim((string)($_SESSION['wo_wd_reason'] ?? ''));
    }
    if ($reason === '') {
        $reason = 'Retrait wallet';
    }
    unset($_SESSION['wo_wd_reason'], $_SESSION['wo_wd_amount'], $_SESSION['wo_wd_email']);
    $row = ORM::for_table('tbl_otantik_withdrawals')->create();
    $row->owner_id = (int)$admin['id'];
    $row->receiver = $phone;
    $row->amount = $amount;
    $row->service = $service;
    $row->status = 'PENDING';
    $row->reason = $reason;
    $row->created_at = date('Y-m-d H:i:s');
    $row->save();
    try {
        $resp = OtantikPay::payout($amount, $phone, $service, 'WD-' . $row->id(), [
            'first_name' => $admin['fullname'] ?: 'Client',
            'last_name' => 'Otantik',
        ]);
        $row->mesomb_ref = $resp['reference'] ?: '';
        $row->message = $resp['message'] ?: '';
        $row->save();
        if (!empty($resp['ok'])) {
            wifi_otantik_pg_finish_withdraw($row);
            r2(getUrl('plugin/wifi_otantik_wallet'), 's', 'Retrait réussi');
        }
        if (!empty($resp['pending'])) {
            r2(getUrl('plugin/wifi_otantik_wallet'), 's', 'Retrait en cours. Validez sur le téléphone si demandé.');
        }
        $row->status = 'FAILED';
        $row->save();
        if (class_exists('OtantikWhatsApp')) {
            try {
                OtantikWhatsApp::notify_withdraw($phone, $amount, 'FAILED', (string)($row['mesomb_ref'] ?? ''));
            } catch (Throwable $e) {
            }
        }
        r2(getUrl('plugin/wifi_otantik_wallet'), 'e', wifi_otantik_user_msg($resp['message'] ?? '', 'withdraw'));
    } catch (Exception $e) {
        $row->status = 'FAILED';
        $row->message = $e->getMessage();
        $row->save();
        if (class_exists('OtantikWhatsApp')) {
            try {
                OtantikWhatsApp::notify_withdraw($phone, $amount, 'FAILED', (string)($row['mesomb_ref'] ?? ''));
            } catch (Throwable $e2) {
            }
        }
        r2(getUrl('plugin/wifi_otantik_wallet'), 'e', wifi_otantik_user_msg($e->getMessage(), 'withdraw'));
    }
}

function wifi_otantik_tickets()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('sales', $admin);
    if (($routes[2] ?? '') === 'paid') {
        wifi_otantik_mark_manual_paid($routes[3] ?? 0, $admin, getUrl('plugin/wifi_otantik_tickets'));
        return;
    }
    if (($routes[2] ?? '') === 'export') {
        $fmt = wifi_otantik_export_fmt($routes[3] ?? 'csv');
        $names = Tenant::router_names($admin);
        $headers = ['Identifiant', 'Zone', 'Forfait', 'Type', 'Début', 'Expiration', 'Statut'];
        $out = [];
        if ($names) {
            try {
                $trows = ORM::for_table('tbl_user_recharges')->where_in('routers', $names)->order_by_desc('id')->limit(5000)->find_many();
            } catch (Throwable $e) {
                $trows = [];
            }
            foreach ($trows as $r) {
                $ring = wifi_otantik_expiry_info($r);
                $kind = strtoupper((string)$r['type']) === 'PPPOE' ? 'PPPoE' : 'Hotspot';
                $st = (empty($ring['expired']) && (string)$r['status'] === 'on') ? 'Actif' : 'Expiré';
                $out[] = [
                    (string)$r['username'],
                    (string)$r['routers'],
                    (string)$r['namebp'],
                    $kind,
                    trim((string)$r['recharged_on'] . ' ' . (string)$r['recharged_time']),
                    (string)($ring['end_label'] ?: ($ring['label'] ?? '')),
                    $st,
                ];
            }
        }
        OtantikExport::send($fmt, 'tickets', 'Mes tickets', $headers, $out);
        return;
    }
    if (_post('create_ticket')) {
        wifi_otantik_issue_manual($admin);
        return;
    }
    $names = Tenant::router_names($admin);
    $qStr = wifi_otantik_q();
    $st = (string)_get('st');
    $ty = (string)_get('ty');
    $rows = [];
    $rings = [];
    $pager = wifi_otantik_pager(0);
    if ($names) {
        try {
            $make = function () use ($names, $qStr, $st, $ty) {
                $q = ORM::for_table('tbl_user_recharges')->where_in('routers', $names);
                if ($st === 'on') {
                    $q->where('status', 'on');
                } elseif ($st === 'off') {
                    $q->where_not_equal('status', 'on');
                }
                if ($ty === 'PPPOE' || $ty === 'Hotspot') {
                    $q->where('type', $ty);
                }
                wifi_otantik_where_search($q, ['username', 'routers', 'namebp'], $qStr);
                return $q;
            };
            $pager = wifi_otantik_pager((int)$make()->count());
            $rows = wifi_otantik_paged($make()->order_by_desc('id'), $pager)->find_many();
        } catch (Throwable $e) {
            $rows = [];
        }
        foreach ($rows as $row) {
            $rings[(int)$row['id']] = wifi_otantik_expiry_info($row);
        }
    }
    $rechargeIds = [];
    $pppoeUsers = [];
    foreach ($rows as $row) {
        if (strtoupper((string)$row['type']) === 'PPPOE') {
            $pppoeUsers[] = (string)$row['username'];
        }
    }
    if ($pppoeUsers) {
        try {
            foreach (ORM::for_table('tbl_customers')->where_in('username', array_values(array_unique($pppoeUsers)))->find_many() as $c) {
                $rechargeIds[(string)$c['username']] = (int)$c['id'];
            }
        } catch (Throwable $e) {
        }
    }
    // admin_id on tbl_user_recharges is who actually issued it (owner or a
    // staff/reseller sub-account) - resolve to names so the tenant can see
    // which reseller created each ticket.
    $issuers = [];
    try {
        $issuerIds = array_values(array_unique(array_filter(array_map(function ($r) {
            return (int)$r['admin_id'];
        }, $rows))));
        if ($issuerIds) {
            foreach (ORM::for_table('tbl_users')->where_in('id', $issuerIds)->find_many() as $u) {
                $issuers[(int)$u['id']] = (string)$u['fullname'] ?: (string)$u['username'];
            }
        }
    } catch (Throwable $e) {
    }
    $routers = Tenant::routers_query($admin)->find_many();
    $routerIds = [];
    foreach ($routers as $r) {
        $routerIds[(string)$r['name']] = (int)$r['id'];
    }
    $plans = $names
        ? ORM::for_table('tbl_plans')->where_in('type', ['Hotspot', 'PPPOE'])->where_in('routers', $names)->where('enabled', 1)->order_by_asc('price')->find_many()
        : [];
    $planJs = [];
    foreach ($plans as $p) {
        $zoneName = trim((string)$p['routers']);
        $planJs[] = [
            'id' => (int)$p['id'],
            'name' => (string)$p['name_plan'],
            'type' => wifi_otantik_plan_kind($p),
            'routers' => $zoneName,
            'router_id' => $routerIds[$zoneName] ?? 0,
            'price' => (int)round((float)$p['price']),
        ];
    }
    $issued = $_SESSION['wo_issued'] ?? null;
    if (isset($_SESSION['wo_issued'])) {
        unset($_SESSION['wo_issued']);
    }
    wifi_otantik_ui('plugin/otantik_tickets.tpl', 'Mes tickets', [
        'rows' => $rows,
        'rings' => $rings,
        'issuers' => $issuers,
        'routers' => $routers,
        'plans_json' => json_encode($planJs),
        'issued' => $issued,
        'recharge_ids' => $rechargeIds,
        'filter_q' => $qStr,
        'filter_st' => $st,
        'filter_ty' => $ty,
        'pager' => $pager,
        'export_csv' => getUrl('plugin/wifi_otantik_tickets/export/csv'),
        'export_pdf' => getUrl('plugin/wifi_otantik_tickets/export/pdf'),
    ]);
}

function wifi_otantik_docs()
{
    global $admin;
    _admin();
    Tenant::require_can('finance_view', $admin);
    wifi_otantik_ui('plugin/otantik_docs.tpl', 'Documentation', []);
}

function wifi_otantik_profile()
{
    global $admin;
    _admin();
    Tenant::ensure_schema();
    $tenant = Tenant::tenant_row(Tenant::id($admin));
    $tab = _get('tab') ?: 'overview';
    $photoNote = '';
    if (_post('email_code')) {
        $pending = strtolower(trim((string)($_SESSION['wo_email_pending'] ?? '')));
        if ($pending === '' || !filter_var($pending, FILTER_VALIDATE_EMAIL)) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit', 'e', 'Demandez d’abord un code pour cette adresse.');
        }
        list($ok, $payload, $msg) = OtantikAuth::consume($pending, 'email', _post('email_code'));
        if (!$ok) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit&verify=1', 'e', $msg);
        }
        $newMail = strtolower(trim((string)($payload['email'] ?? $pending)));
        $u = ORM::for_table('tbl_users')->find_one($admin['id']);
        if ($u && filter_var($newMail, FILTER_VALIDATE_EMAIL)) {
            $u->email = $newMail;
            $u->save();
        }
        unset($_SESSION['wo_email_pending']);
        r2(getUrl('plugin/wifi_otantik_profile') . '&tab=overview', 's', 'Adresse email confirmée');
    }
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $photoNote = wifi_otantik_save_avatar($admin, $_FILES['avatar']);
        if ($photoNote !== '') {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=' . urlencode($tab), 'e', $photoNote);
        }
        if (!_post('save_profile')) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=' . urlencode($tab), 's', 'Photo de profil mise à jour');
        }
    }
    if (_post('save_profile')) {
        $u = ORM::for_table('tbl_users')->find_one($admin['id']);
        $first = trim(_post('firstname'));
        $last = trim(_post('lastname'));
        $full = trim($first . ' ' . $last);
        if ($full === '') {
            $full = trim(_post('fullname') ?: ($u ? $u['fullname'] : ''));
        }
        if ($u) {
            $u->fullname = $full ?: $u['fullname'];
            $u->phone = wifi_otantik_phone(_post('phone') ?: $u['phone']);
            try {
                $u->city = trim(_post('city'));
            } catch (Throwable $e) {
            }
            $newMail = strtolower(trim((string)_post('email')));
            $oldMail = strtolower(trim((string)($u['email'] ?? '')));
            if ($newMail !== '' && $newMail !== $oldMail) {
                if (!filter_var($newMail, FILTER_VALIDATE_EMAIL)) {
                    r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit', 'e', 'Indiquez un email du type vous@gmail.com');
                }
                $dup = ORM::for_table('tbl_users')->where('email', $newMail)->where_not_equal('id', (int)$u['id'])->find_one();
                if ($dup) {
                    r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit', 'e', 'Cet email a déjà un compte');
                }
                $u->save();
                $tenant->phone = wifi_otantik_phone(_post('phone') ?: $tenant['phone']);
                $tenant->country = trim(_post('country') ?: 'Cameroun');
                try {
                    $tenant->city = trim(_post('city'));
                    $tenant->address = trim(_post('address'));
                } catch (Throwable $e) {
                }
                try {
                    $waNotify = trim((string)_post('wa_notify_phone'));
                    $tenant->wa_notify_phone = $waNotify !== '' ? wifi_otantik_phone($waNotify) : '';
                } catch (Throwable $e) {
                }
                $tenant->save();
                list($sent, $hint) = OtantikAuth::issue($newMail, 'email', [
                    'uid' => (int)$u['id'],
                    'email' => $newMail,
                ]);
                if (!$sent) {
                    r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit', 'e', $hint);
                }
                $_SESSION['wo_email_pending'] = $newMail;
                r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit&verify=1', 's', 'Un code a été envoyé à ' . $hint . ' pour confirmer l’adresse.');
            }
            $u->email = $newMail !== '' ? $newMail : $u['email'];
            $u->save();
        }
        $tenant->phone = wifi_otantik_phone(_post('phone') ?: $tenant['phone']);
        $tenant->country = trim(_post('country') ?: 'Cameroun');
        try {
            $tenant->city = trim(_post('city'));
            $tenant->address = trim(_post('address'));
        } catch (Throwable $e) {
        }
        try {
            $waNotify = trim((string)_post('wa_notify_phone'));
            $tenant->wa_notify_phone = $waNotify !== '' ? wifi_otantik_phone($waNotify) : '';
        } catch (Throwable $e) {
        }
        $tenant->save();
        r2(getUrl('plugin/wifi_otantik_profile') . '&tab=overview', 's', 'Profil mis à jour');
    }
    $withdrawEmailOwner = (int)($admin['root'] ?? 0) === 0 || Tenant::is_platform($admin);
    if (_post('save_withdraw_email')) {
        if (!$withdrawEmailOwner) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit', 'e', 'Seul le propriétaire du compte peut modifier cet email.');
        }
        $wEmail = strtolower(trim((string)_post('withdraw_email')));
        if ($wEmail !== '' && !filter_var($wEmail, FILTER_VALIDATE_EMAIL)) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit', 'e', 'Indiquez un email valide, ou laissez vide.');
        }
        $tenant->withdraw_email = $wEmail;
        $tenant->save();
        r2(getUrl('plugin/wifi_otantik_profile') . '&tab=edit', 's', $wEmail !== '' ? 'Email de retrait enregistré.' : 'Email de retrait retiré - les codes partiront sur l’email de la personne qui retire.');
    }
    if (_post('save_password')) {
        $u = ORM::for_table('tbl_users')->find_one($admin['id']);
        $cur = _post('current_password');
        $n1 = _post('new_password');
        $n2 = _post('new_password2');
        if (!$u || !Password::_verify($cur, $u['password'])) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=security', 'e', 'Mot de passe actuel incorrect');
        }
        if (strlen($n1) < 6 || $n1 !== $n2) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=security', 'e', 'Nouveau mot de passe invalide');
        }
        $u->password = Password::_crypt($n1);
        $u->save();
        r2(getUrl('plugin/wifi_otantik_profile') . '&tab=security', 's', 'Mot de passe modifié');
    }
    if (_post('save_kyc')) {
        if (($tenant['kyc_status'] ?? '') === 'verified') {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=kyc', 'e', 'Identité déjà vérifiée');
        }
        $type = trim((string)_post('kyc_id_type'));
        if (!in_array($type, ['cni', 'passport', 'nui'], true)) {
            $type = 'cni';
        }
        $num = trim((string)_post('kyc_id_number'));
        if ($num === '') {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=kyc', 'e', 'Indiquez le numéro de pièce');
        }
        $uid = (int)$admin['id'];
        foreach (['front' => 'kyc_front', 'back' => 'kyc_back', 'selfie' => 'kyc_selfie'] as $kind => $field) {
            if (!empty($_FILES[$field]['tmp_name'])) {
                $err = wifi_otantik_save_kyc_image($uid, $_FILES[$field], $kind);
                if ($err !== '') {
                    r2(getUrl('plugin/wifi_otantik_profile') . '&tab=kyc', 'e', $err);
                }
            }
        }
        if (!is_file(wifi_otantik_kyc_path($uid, 'front')) || !is_file(wifi_otantik_kyc_path($uid, 'selfie'))) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=kyc', 'e', 'Ajoutez le recto de la pièce et un selfie');
        }
        if ($type === 'cni' && !is_file(wifi_otantik_kyc_path($uid, 'back'))) {
            r2(getUrl('plugin/wifi_otantik_profile') . '&tab=kyc', 'e', 'Ajoutez aussi le verso de la CNI');
        }
        $tenant->kyc_id_type = $type;
        $tenant->kyc_id_number = $num;
        $tenant->kyc_status = 'pending';
        try {
            $tenant->kyc_note = '';
            $tenant->kyc_submitted_at = date('Y-m-d H:i:s');
        } catch (Throwable $e) {
        }
        $tenant->save();
        r2(getUrl('plugin/wifi_otantik_profile') . '&tab=kyc', 's', 'Dossier envoyé. Vérification sous 24–48 h.');
    }
    $full = trim((string)($admin['fullname'] ?? ''));
    $parts = preg_split('/\s+/', $full, 2);
    $firstname = $parts[0] ?? '';
    $lastname = $parts[1] ?? '';
    $kycStatus = (string)($tenant['kyc_status'] ?? 'none');
    wifi_otantik_ui('plugin/otantik_profile.tpl', 'Gestion de profil', [
        'tenant' => $tenant,
        'tab' => $tab,
        'user' => $admin,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'phone_fmt' => wifi_otantik_phone_fmt($tenant['phone'] ?: ($admin['phone'] ?? '')),
        'phone_raw' => wifi_otantik_phone($tenant['phone'] ?: ($admin['phone'] ?? '')),
        'kyc_docs' => wifi_otantik_kyc_docs((int)$admin['id']),
        'kyc_locked' => $kycStatus === 'verified',
        'kyc_label' => wifi_otantik_kyc_label($kycStatus),
        'kyc_chip' => wifi_otantik_kyc_chip($kycStatus),
        'email_verify' => _get('verify') === '1' && !empty($_SESSION['wo_email_pending']),
        'email_pending_hint' => OtantikAuth::hint((string)($_SESSION['wo_email_pending'] ?? '')),
        'withdraw_email' => (string)($tenant['withdraw_email'] ?? ''),
        'withdraw_email_owner' => $withdrawEmailOwner,
    ]);
}

function wifi_otantik_kyc()
{
    _admin();
    r2(getUrl('plugin/wifi_otantik_profile') . '&tab=kyc');
}

function wifi_otantik_referrals()
{
    global $admin;
    _admin();
    Tenant::require_can('finance_view', $admin);
    $code = (string)$admin['username'];
    $link = Tenant::url_business('/inscription?ref=' . rawurlencode($code));
    $tenant = Tenant::tenant_row(Tenant::id($admin));
    $keep = (float)(100 - Tenant::effective_commission($tenant));
    $rate = Tenant::effective_commission($tenant);
    $split = Tenant::split(1000, $tenant);
    $full = trim((string)($admin['fullname'] ?? ''));
    $first = $full !== '' ? preg_split('/\s+/', $full)[0] : $code;
    $kycStatus = (string)($tenant['kyc_status'] ?? 'none');
    $filleuls = [];
    $count = 0;
    $qStr = wifi_otantik_q();
    $pager = wifi_otantik_pager(0);
    try {
        $uid = (string)(int)$admin['id'];
        $user = (string)$admin['username'];
        $makeRef = function () use ($uid, $user) {
            return ORM::for_table('tbl_otantik_tenants')
                ->where_raw('(referred_by = ? OR referred_by = ?)', [$user, $uid]);
        };
        $count = (int)$makeRef()->count();
        $pager = wifi_otantik_pager($count);
        $rows = wifi_otantik_paged($makeRef()->order_by_desc('id'), $pager)->find_many();
        foreach ($rows as $r) {
            $u = ORM::for_table('tbl_users')->find_one((int)$r['user_id']);
            $name = trim((string)($u['fullname'] ?? ''));
            if ($name === '') {
                $name = trim((string)($u['username'] ?? 'Exploitant'));
            }
            if ($qStr !== '' && stripos($name, $qStr) === false) {
                continue;
            }
            $created = (string)($r['created_at'] ?? '');
            $initial = function_exists('mb_substr')
                ? mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8')
                : strtoupper(substr($name, 0, 1));
            $filleuls[] = [
                'name' => $name,
                'initial' => $initial !== '' ? $initial : 'W',
                'when' => $created !== '' ? date('d/m/Y', strtotime($created)) : '',
            ];
        }
    } catch (Throwable $e) {
    }
    $shareText = 'Rejoins Wifi-Otantik : tu connectes ton MikroTik, tu vends du Wi-Fi (hotspot + PPPoE) et tu gardes '
        . (int)$keep . '% de chaque ticket sur ton portefeuille. Mon lien : ' . $link;
    $pitchCards = [
        [
            'kicker' => 'Le pitch 15 s',
            'title' => 'Ils gardent ' . (int)$keep . ' %',
            'body' => 'Sur 1 000 FCFA de ticket, ' . number_format((float)$split['net'], 0, ',', ' ')
                . ' FCFA tombent sur le portefeuille. Le reste, ' . (int)$rate . ' %, c’est le tarif plateforme.',
            'copy' => 'Je vends du Wi-Fi avec Wifi-Otantik. ' . (int)$keep
                . '% de chaque ticket sur mon MoMo. Inscris-toi ici : ' . $link,
        ],
        [
            'kicker' => 'Ce qu’ils lancent',
            'title' => 'Wifi Zone clé en main',
            'body' => 'Hotspot, PPPoE, portail captif, tickets et retraits MTN / Orange. Un compte, un MikroTik, ça vend.',
            'copy' => 'Wifi-Otantik : tu branches ton MikroTik, tu crées tes tarifs, tes clients paient en MoMo. Lien : ' . $link,
        ],
        [
            'kicker' => 'Votre rôle',
            'title' => 'Vous êtes le parrain',
            'body' => 'Chaque inscription avec votre lien est marquée à votre nom. Vous construisez un réseau d’exploitants.',
            'copy' => 'Ouvre ton espace Wifi-Otantik avec mon code ' . $code . ' : ' . $link,
        ],
    ];
    wifi_otantik_ui('plugin/otantik_referrals.tpl', 'Parrainages', [
        'code' => $code,
        'link' => $link,
        'count' => $count,
        'filleuls' => $filleuls,
        'filter_q' => $qStr,
        'pager' => $pager,
        'first_name' => $first,
        'wallet' => (float)($tenant['wallet'] ?? 0),
        'keep_pct' => $keep,
        'fee_pct' => $rate,
        'example_net' => (float)$split['net'],
        'kyc_status' => $kycStatus,
        'kyc_label' => wifi_otantik_kyc_label($kycStatus),
        'kyc_chip' => wifi_otantik_kyc_chip($kycStatus),
        'share_text' => $shareText,
        'pitch_cards' => $pitchCards,
        'wa_share' => 'https://wa.me/?text=' . rawurlencode($shareText),
        'tg_share' => 'https://t.me/share/url?url=' . rawurlencode($link) . '&text=' . rawurlencode($shareText),
        'fb_share' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($link),
    ]);
}

function wifi_otantik_alerts()
{
    _admin();
    r2(getUrl('plugin/wifi_otantik_home'));
}

function wifi_otantik_platform()
{
    _admin();
    r2(getUrl('plugin/wifi_otantik_tenants'));
}

/**
 * Everything a tenant "owns", gathered once so both the confirmation
 * screen (counts) and the actual delete (below) agree on scope.
 */
function wifi_otantik_tenant_scope($ownerId)
{
    $ownerId = (int)$ownerId;
    $routerIds = [];
    $routerNames = [];
    foreach (ORM::for_table('tbl_routers')->where('owner_id', $ownerId)->find_many() as $r) {
        $routerIds[] = (int)$r['id'];
        $routerNames[] = (string)$r['name'];
    }
    $customerIds = [];
    foreach (ORM::for_table('tbl_customers')->where('created_by', $ownerId)->find_many() as $c) {
        $customerIds[] = (int)$c['id'];
    }
    $resellerIds = [];
    foreach (ORM::for_table('tbl_otantik_resellers')->where('owner_id', $ownerId)->find_many() as $rs) {
        $resellerIds[] = (int)$rs['id'];
    }
    $staffIds = [];
    foreach (ORM::for_table('tbl_users')->where('root', $ownerId)->find_many() as $s) {
        $staffIds[] = (int)$s['id'];
    }
    return [
        'router_ids' => $routerIds,
        'router_names' => $routerNames,
        'customer_ids' => $customerIds,
        'reseller_ids' => $resellerIds,
        'staff_ids' => $staffIds,
    ];
}

function wifi_otantik_tenant_impact($ownerId)
{
    $ownerId = (int)$ownerId;
    $scope = wifi_otantik_tenant_scope($ownerId);
    $routerNames = $scope['router_names'];
    $paidCount = 0;
    $salesTotal = 0.0;
    if ($routerNames) {
        $paidCount = (int)ORM::for_table('tbl_payment_gateway')->where_in('routers', $routerNames)->where('status', 2)->count();
        $salesTotal = (float)ORM::for_table('tbl_payment_gateway')->where_in('routers', $routerNames)->where('status', 2)->sum('price');
    }
    return [
        'routers' => count($scope['router_ids']),
        'customers' => count($scope['customer_ids']),
        'staff' => count($scope['staff_ids']),
        'resellers' => count($scope['reseller_ids']),
        'paid_count' => $paidCount,
        'sales_total' => $salesTotal,
        'erp_docs' => $routerNames ? (int)ORM::for_table('tbl_otantik_erp_docs')->where('owner_id', $ownerId)->count() : 0,
    ];
}

/**
 * Permanently removes a tenant/business account and every row that
 * belongs to it - SuperAdmin only, and irreversible. Financial history
 * is NOT preserved here (unlike the single-customer delete guard) since
 * the whole account is going away, not just one customer profile.
 */
function wifi_otantik_delete_tenant($ownerId)
{
    $ownerId = (int)$ownerId;
    $tenant = ORM::for_table('tbl_otantik_tenants')->where('user_id', $ownerId)->find_one();
    $owner = ORM::for_table('tbl_users')->find_one($ownerId);
    if (!$tenant || !$owner) {
        return 'Client introuvable';
    }
    $scope = wifi_otantik_tenant_scope($ownerId);
    $routerIds = $scope['router_ids'];
    $routerNames = $scope['router_names'];
    $customerIds = $scope['customer_ids'];
    $resellerIds = $scope['reseller_ids'];
    $staffIds = $scope['staff_ids'];

    $db = ORM::get_db();
    $db->beginTransaction();
    try {
        // ERP files: delete DB rows + underlying uploads before dropping the owner storage dir.
        foreach (ORM::for_table('tbl_otantik_erp_files')->where('owner_id', $ownerId)->find_many() as $f) {
            $f->delete();
        }
        ORM::for_table('tbl_otantik_erp_docs')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_erp_assets')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_erp_flow')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_erp_expenses')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_erp_recurring')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_voids')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_ledger')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_books')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_withdrawals')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_hs_refs')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_inbox')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_mk_logs')->where('owner_id', $ownerId)->delete_many();
        ORM::for_table('tbl_otantik_peers')->where('owner_id', $ownerId)->delete_many();

        if ($resellerIds) {
            ORM::for_table('tbl_otantik_reseller_zones')->where_in('reseller_id', $resellerIds)->delete_many();
        }
        if ($routerIds) {
            ORM::for_table('tbl_otantik_reseller_zones')->where_in('router_id', $routerIds)->delete_many();
            ORM::for_table('tbl_otantik_mac_bypass')->where_in('router_id', $routerIds)->delete_many();
            ORM::for_table('tbl_otantik_webfig_tokens')->where_in('router_id', $routerIds)->delete_many();
        }
        ORM::for_table('tbl_otantik_resellers')->where('owner_id', $ownerId)->delete_many();

        if ($customerIds) {
            ORM::for_table('tbl_user_recharges')->where_in('customer_id', $customerIds)->delete_many();
        }
        if ($routerNames) {
            ORM::for_table('tbl_user_recharges')->where_in('routers', $routerNames)->delete_many();
            ORM::for_table('tbl_payment_gateway')->where_in('routers', $routerNames)->delete_many();
            ORM::for_table('tbl_transactions')->where_in('routers', $routerNames)->delete_many();
            ORM::for_table('tbl_plans')->where_in('routers', $routerNames)->delete_many();
            ORM::for_table('tbl_pool')->where_in('routers', $routerNames)->delete_many();
        }
        ORM::for_table('tbl_customers')->where('created_by', $ownerId)->delete_many();
        ORM::for_table('tbl_routers')->where('owner_id', $ownerId)->delete_many();
        if ($staffIds) {
            ORM::for_table('tbl_users')->where_in('id', $staffIds)->delete_many();
        }
        $tenant->delete();
        $owner->delete();
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        return 'Échec de la suppression : ' . $e->getMessage();
    }

    // Filesystem cleanup - best effort, outside the DB transaction.
    try {
        foreach ($routerIds as $rid) {
            $fakeRouter = ['id' => $rid];
            $logo = OtantikCaptive::custom_logo_path($fakeRouter);
            if ($logo !== '' && is_file($logo)) {
                @unlink($logo);
            }
            $audio = OtantikCaptive::custom_audio_path($fakeRouter);
            if ($audio !== '' && is_file($audio)) {
                @unlink($audio);
            }
        }
        $kycDir = wifi_otantik_kyc_root() . DIRECTORY_SEPARATOR . $ownerId;
        if (is_dir($kycDir)) {
            wifi_otantik_rrmdir($kycDir);
        }
        $erpDir = OtantikErp::storage_dir($ownerId);
        if (is_dir($erpDir)) {
            wifi_otantik_rrmdir($erpDir);
        }
    } catch (Throwable $e) {
    }

    return true;
}

function wifi_otantik_rrmdir($dir)
{
    if (!is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? wifi_otantik_rrmdir($path) : @unlink($path);
    }
    @rmdir($dir);
}

function wifi_otantik_tenants()
{
    global $admin, $config, $routes;
    _admin();
    if (!Tenant::is_platform($admin)) {
        r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
    }
    Tenant::ensure_schema();
    if (_post('kyc_action') && _post('kyc_tenant_id')) {
        $t = ORM::for_table('tbl_otantik_tenants')->find_one((int)_post('kyc_tenant_id'));
        $act = (string)_post('kyc_action');
        if ($t && in_array($act, ['verified', 'rejected'], true)) {
            $t->kyc_status = $act;
            try {
                $t->kyc_note = trim((string)_post('kyc_note'));
            } catch (Throwable $e) {
            }
            $t->save();
            r2(getUrl('plugin/wifi_otantik_tenants/kyc/' . (int)$t['id']), 's', $act === 'verified' ? 'Identité validée' : 'Dossier refusé');
        }
    }
    if (($routes[2] ?? '') === 'kyc') {
        $t = ORM::for_table('tbl_otantik_tenants')->find_one((int)($routes[3] ?? 0));
        if (!$t) {
            r2(getUrl('plugin/wifi_otantik_tenants'), 'e', 'Client introuvable');
        }
        $u = ORM::for_table('tbl_users')->find_one((int)$t['user_id']);
        $kycStatus = (string)($t['kyc_status'] ?? 'none');
        wifi_otantik_ui('plugin/otantik_kyc_review.tpl', 'Vérification KYC', [
            'row' => $t,
            'user' => $u,
            'kyc_docs' => wifi_otantik_kyc_docs((int)$t['user_id']),
            'kyc_label' => wifi_otantik_kyc_label($kycStatus),
            'kyc_chip' => wifi_otantik_kyc_chip($kycStatus),
            'phone_fmt' => wifi_otantik_phone_fmt($t['phone'] ?: ($u['phone'] ?? '')),
        ]);
        return;
    }
    if (($routes[2] ?? '') === 'delete') {
        $t = ORM::for_table('tbl_otantik_tenants')->find_one((int)($routes[3] ?? 0));
        if (!$t) {
            r2(getUrl('plugin/wifi_otantik_tenants'), 'e', 'Client introuvable');
        }
        $u = ORM::for_table('tbl_users')->find_one((int)$t['user_id']);
        if (!$u) {
            r2(getUrl('plugin/wifi_otantik_tenants'), 'e', 'Client introuvable');
        }
        if ((int)$t['user_id'] === (int)$admin['id']) {
            r2(getUrl('plugin/wifi_otantik_tenants'), 'e', 'Impossible de supprimer le compte avec lequel vous êtes connecté.');
        }
        if (_post('confirm_delete')) {
            $typed = trim((string)_post('confirm_username'));
            if ($typed === '' || strcasecmp($typed, (string)$u['username']) !== 0) {
                r2(getUrl('plugin/wifi_otantik_tenants/delete/') . (int)$t['id'], 'e', 'Le nom d’utilisateur saisi ne correspond pas - suppression annulée.');
            }
            $res = wifi_otantik_delete_tenant((int)$t['user_id']);
            if ($res === true) {
                r2(getUrl('plugin/wifi_otantik_tenants'), 's', 'Client "' . $u['username'] . '" et toutes ses données ont été supprimés définitivement.');
            }
            r2(getUrl('plugin/wifi_otantik_tenants/delete/') . (int)$t['id'], 'e', (string)$res);
        }
        wifi_otantik_ui('plugin/otantik_tenant_delete.tpl', 'Supprimer un client', [
            'row' => $t,
            'user' => $u,
            'impact' => wifi_otantik_tenant_impact((int)$t['user_id']),
        ]);
        return;
    }
    if (_post('save_platform')) {
        $pct = (float)str_replace(',', '.', (string)_post('commission'));
        if ($pct < 0) {
            $pct = 0;
        }
        if ($pct > 90) {
            $pct = 90;
        }
        wifi_otantik_set_config('otantik_commission', (string)$pct);
        $g = trim((string)_post('google_client_id'));
        wifi_otantik_set_config('otantik_google_client_id', $g);
        $config['otantik_commission'] = (string)$pct;
        $config['otantik_google_client_id'] = $g;
        $mailProvider = strtolower(trim((string)_post('mail_provider')));
        if ($mailProvider !== 'gmail') {
            $mailProvider = 'other';
        }
        if ($mailProvider === 'gmail') {
            $from = strtolower(trim((string)_post('mail_from')));
            $fromName = trim((string)_post('mail_from_name'));
            $user = $from;
            $host = 'smtp.gmail.com';
            $port = '587';
            $secure = 'tls';
            $reply = '';
            $pass = OtantikPay::keep_secret(_post('smtp_pass'), $config['smtp_pass'] ?? '');
        } else {
            $from = strtolower(trim((string)_post('mail_from_other')));
            $fromName = trim((string)_post('mail_from_name_other'));
            $user = trim((string)_post('smtp_user'));
            $host = trim((string)_post('smtp_host'));
            $port = (string)max(1, min(65535, (int)_post('smtp_port') ?: 587));
            $secure = in_array(_post('smtp_ssltls'), ['tls', 'ssl', 'none'], true) ? _post('smtp_ssltls') : 'tls';
            $reply = trim((string)_post('mail_reply_to'));
            $pass = OtantikPay::keep_secret(_post('smtp_pass_other'), $config['smtp_pass'] ?? '');
        }
        $pass = preg_replace('/\s+/', '', (string)$pass);
        $mailPairs = [
            'otantik_mail_provider' => $mailProvider,
            'mail_from' => $from,
            'mail_from_name' => $fromName,
            'mail_reply_to' => $reply,
            'smtp_host' => $host,
            'smtp_port' => $port,
            'smtp_user' => $user,
            'smtp_ssltls' => $secure,
            'smtp_pass' => $pass,
        ];
        foreach ($mailPairs as $k => $v) {
            wifi_otantik_set_config($k, $v);
            $config[$k] = $v;
        }
        if (_post('mail_test')) {
            $testTo = OtantikAuth::mail_from();
            if (!filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
                r2(getUrl('plugin/wifi_otantik_tenants'), 'e', 'Indiquez l’adresse Gmail d’envoi pour tester.');
            }
            if ($pass === '') {
                r2(getUrl('plugin/wifi_otantik_tenants'), 'e', 'Collez le mot de passe d’application (16 caractères) puis relancez le test.');
            }
            $ok = OtantikAuth::mail(
                $testTo,
                'Test Wifi-Otantik',
                "Ceci est un message de test. L’envoi des codes fonctionne.\n",
                [
                    'title' => 'L’envoi fonctionne',
                    'lead' => 'Ceci est un message de test. Les codes d’inscription, de retrait et de mot de passe partiront dans ce même format, avec en-tête, logo et pied de page.',
                ]
            );
            r2(getUrl('plugin/wifi_otantik_tenants'), $ok ? 's' : 'e', $ok
                ? ('Réglages enregistrés. Un message a été envoyé à ' . $testTo . '. Ouvrez cette boîte et les spams.')
                : ('Réglages enregistrés, mais le test n’est pas parti. ' . OtantikAuth::mail_hint()));
        }
        r2(getUrl('plugin/wifi_otantik_tenants'), 's', 'Réglages enregistrés');
    }
    if (_post('tenant_id') && isset($_POST['commission_rate'])) {
        $t = ORM::for_table('tbl_otantik_tenants')->find_one((int)_post('tenant_id'));
        if ($t) {
            $pct = (float)str_replace(',', '.', (string)_post('commission_rate'));
            if ($pct < 0) {
                $pct = 0;
            }
            if ($pct > 90) {
                $pct = 90;
            }
            $t->commission_rate = $pct;
            $t->save();
            r2(getUrl('plugin/wifi_otantik_tenants'), 's', 'Réduction du client mise à jour');
        }
    }
    if (_post('tenant_id') && isset($_POST['referred_by'])) {
        $t = ORM::for_table('tbl_otantik_tenants')->find_one((int)_post('tenant_id'));
        if ($t) {
            $raw = trim((string)_post('referred_by'));
            $resolved = $raw === '' ? '' : wifi_otantik_ref_resolve($raw, (int)$t['user_id']);
            if ($raw !== '' && $resolved === '') {
                r2(getUrl('plugin/wifi_otantik_tenants'), 'e', 'Parrain introuvable');
            }
            $t->referred_by = $resolved;
            $t->save();
            r2(getUrl('plugin/wifi_otantik_tenants'), 's', $resolved !== '' ? 'Parrain enregistré' : 'Parrain retiré');
        }
    }
    if (_post('tenant_id') && isset($_POST['wa_enabled_toggle'])) {
        $t = ORM::for_table('tbl_otantik_tenants')->find_one((int)_post('tenant_id'));
        if ($t) {
            $t->wa_enabled = _post('wa_enabled') === '1' ? 1 : 0;
            if (_post('wa_until') !== '') {
                $t->wa_until = date('Y-m-d 23:59:59', strtotime((string)_post('wa_until')));
            } elseif (!_post('wa_enabled')) {
                $t->wa_until = null;
            }
            $t->save();
            r2(getUrl('plugin/wifi_otantik_tenants'), 's', 'Module WhatsApp mis à jour');
        }
    }
    $qStr = wifi_otantik_q();
    $st = (string)_get('st');
    $ty = (string)_get('ty');
    $makeTen = function () use ($qStr, $st, $ty) {
        $q = ORM::for_table('tbl_otantik_tenants');
        if ($st === 'Active' || $st === 'Inactive') {
            $q->where('status', $st);
        }
        if (in_array($ty, ['pending', 'verified', 'rejected', 'none'], true)) {
            if ($ty === 'none') {
                $q->where_raw("(IFNULL(kyc_status,'') = '' OR kyc_status = 'none')");
            } else {
                $q->where('kyc_status', $ty);
            }
        }
        if ($qStr !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $qStr) . '%';
            $ids = [];
            try {
                foreach (ORM::for_table('tbl_users')->where_raw('(username LIKE ? OR fullname LIKE ?)', [$like, $like])->find_many() as $u) {
                    $ids[] = (int)$u['id'];
                }
            } catch (Throwable $e) {
            }
            if ($ids) {
                $q->where_raw('(phone LIKE ? OR user_id IN (' . implode(',', array_fill(0, count($ids), '?')) . '))', array_merge([$like], $ids));
            } else {
                $q->where_like('phone', $like);
            }
        }
        return $q;
    };
    $tenantCount = (int)ORM::for_table('tbl_otantik_tenants')->count();
    $pager = wifi_otantik_pager((int)$makeTen()->count());
    $rows = wifi_otantik_paged($makeTen()->order_by_desc('id'), $pager)->find_many();
    $users = [];
    $uids = [];
    foreach ($rows as $t) {
        $uids[] = (int)$t['user_id'];
    }
    if ($uids) {
        foreach (ORM::for_table('tbl_users')->where_in('id', $uids)->find_many() as $u) {
            $users[(int)$u['id']] = $u;
        }
    }
    $kycLabels = [];
    $kycChips = [];
    foreach ($rows as $t) {
        $st = (string)($t['kyc_status'] ?? 'none');
        $kycLabels[(int)$t['id']] = wifi_otantik_kyc_label($st);
        $kycChips[(int)$t['id']] = wifi_otantik_kyc_chip($st);
    }
    $profit = 0;
    $profitToday = 0;
    try {
        $profit = (float)ORM::for_table('tbl_otantik_ledger')
            ->where_raw("IFNULL(kind,'') NOT IN ('sale','topup','seed','adjust','refund')")
            ->sum('fee');
        $profitToday = (float)ORM::for_table('tbl_otantik_ledger')
            ->where_raw("IFNULL(kind,'') NOT IN ('sale','topup','seed','adjust','refund')")
            ->where_gte('created_at', date('Y-m-d 00:00:00'))
            ->sum('fee');
    } catch (Throwable $e) {
    }
    $kycPending = 0;
    try {
        $kycPending = (int)ORM::for_table('tbl_otantik_tenants')->where('kyc_status', 'pending')->count();
    } catch (Throwable $e) {
    }
    $mailProv = (string)($config['otantik_mail_provider'] ?? '');
    $smtpHost = (string)($config['smtp_host'] ?? '');
    if ($mailProv === 'gmail' || $mailProv === 'other') {
        $mailProviderUi = $mailProv;
    } else {
        $mailProviderUi = ($smtpHost === '' || strcasecmp($smtpHost, 'smtp.gmail.com') === 0) ? 'gmail' : 'other';
    }
    wifi_otantik_ui('plugin/otantik_tenants.tpl', 'Clients', [
        'rows' => $rows,
        'users' => $users,
        'kyc_labels' => $kycLabels,
        'kyc_chips' => $kycChips,
        'commission' => Tenant::default_commission(),
        'google_client_id' => OtantikAuth::google_client_id(),
        'mail_ready' => OtantikAuth::mail_ready(),
        'mail_provider' => $mailProviderUi,
        'mail_from' => (string)($config['mail_from'] ?? ''),
        'mail_from_name' => (string)($config['mail_from_name'] ?? ''),
        'mail_reply_to' => (string)($config['mail_reply_to'] ?? ''),
        'smtp_host' => (string)($config['smtp_host'] ?? ''),
        'smtp_port' => (string)($config['smtp_port'] ?? '587'),
        'smtp_user' => (string)($config['smtp_user'] ?? ''),
        'smtp_pass_mask' => OtantikPay::mask((string)($config['smtp_pass'] ?? '')),
        'smtp_ssltls' => (string)($config['smtp_ssltls'] ?? 'tls'),
        'profit' => $profit,
        'profit_today' => $profitToday,
        'wallet_sum' => (float)ORM::for_table('tbl_otantik_tenants')->sum('wallet'),
        'tenant_count' => $tenantCount,
        'kyc_pending' => $kycPending,
        'filter_q' => $qStr,
        'filter_st' => $st,
        'filter_ty' => $ty,
        'pager' => $pager,
    ]);
}

function wifi_otantik_all_routers()
{
    global $admin;
    _admin();
    if (!Tenant::is_platform($admin)) {
        r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
    }
    $qStr = wifi_otantik_q();
    $st = (string)_get('st');
    $ty = (string)_get('ty');
    $makeRt = function () use ($qStr, $st) {
        $q = ORM::for_table('tbl_routers');
        if ($st === '1' || $st === '0') {
            $q->where('enabled', $st);
        }
        wifi_otantik_where_search($q, ['name', 'ssid', 'slug', 'ip_address'], $qStr);
        return $q;
    };
    $pager = wifi_otantik_pager((int)$makeRt()->count());
    $routers = wifi_otantik_paged($makeRt()->order_by_desc('id'), $pager)->find_many();
    $peers = [];
    $rids = [];
    $oids = [];
    foreach ($routers as $r) {
        $rids[] = (int)$r['id'];
        $oids[] = (int)$r['owner_id'];
    }
    if ($rids) {
        foreach (ORM::for_table('tbl_otantik_peers')->where_in('router_id', $rids)->find_many() as $p) {
            $peers[(int)$p['router_id']] = $p;
        }
    }
    $owners = [];
    if ($oids) {
        foreach (ORM::for_table('tbl_users')->where_in('id', array_values(array_unique($oids)))->find_many() as $u) {
            $owners[(int)$u['id']] = $u['username'];
        }
    }
    $tunnels = [];
    foreach ($routers as $r) {
        $tunnels[(int)$r['id']] = wifi_otantik_tunnel($r, $peers[(int)$r['id']] ?? null);
    }
    wifi_otantik_ui('plugin/otantik_all_routers.tpl', 'Tous les routeurs', [
        'routers' => $routers,
        'peers' => $peers,
        'owners' => $owners,
        'tunnels' => $tunnels,
        'filter_q' => $qStr,
        'filter_st' => $st,
        'filter_ty' => $ty,
        'pager' => $pager,
    ]);
}

function wifi_otantik_all_sales()
{
    global $admin, $routes;
    _admin();
    if (!Tenant::is_platform($admin)) {
        r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
    }
    if (($routes[2] ?? '') === 'export') {
        $fmt = wifi_otantik_export_fmt($routes[3] ?? 'csv');
        $q = ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways());
        wifi_otantik_export_payments($q, 'Toutes les ventes', 'toutes-les-ventes', $fmt);
        return;
    }
    $qStr = wifi_otantik_q();
    $st = (string)_get('st');
    $ty = (string)_get('ty');
    $make = function () use ($qStr, $st, $ty) {
        $q = ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways());
        if ($st === '0' || $st === '1' || $st === '2') {
            $q->where('status', (int)$st);
        }
        if (in_array($ty, ['MTN', 'ORANGE', 'CASH'], true)) {
            $q->where('payment_method', $ty);
        }
        wifi_otantik_where_search($q, ['username', 'plan_name', 'routers', 'payment_method'], $qStr);
        return $q;
    };
    $pager = wifi_otantik_pager((int)$make()->count());
    $rows = wifi_otantik_paged($make()->order_by_desc('id'), $pager)->find_many();
    wifi_otantik_ui('plugin/otantik_sales.tpl', 'Toutes les ventes', [
        'rows' => $rows,
        'total' => (float)ORM::for_table('tbl_payment_gateway')->where_in('gateway', wifi_otantik_gateways())->where('status', 2)->sum('price'),
        'sales_tabs' => false,
        'filter_q' => $qStr,
        'filter_st' => $st,
        'filter_ty' => $ty,
        'pager' => $pager,
        'export_csv' => getUrl('plugin/wifi_otantik_all_sales/export/csv'),
        'export_pdf' => getUrl('plugin/wifi_otantik_all_sales/export/pdf'),
    ]);
}

function wifi_otantik_signup()
{
    global $ui, $config;
    $ref = wifi_otantik_ref_capture();
    if (Tenant::is_wifi_host() || Tenant::is_portal_host()) {
        $to = '/inscription';
        if ($ref !== '') {
            $to .= '?ref=' . rawurlencode($ref);
        }
        header('Location: ' . Tenant::url_business($to));
        exit;
    }
    if (Admin::getID()) {
        header('Location: ' . Tenant::url_business('/?_route=plugin/wifi_otantik_home'));
        exit;
    }
    $otpStep = false;
    $otpHint = '';
    if (_post('signup_code') && !empty($_SESSION['wo_signup_email'])) {
        list($ok, $payload, $msg) = OtantikAuth::consume($_SESSION['wo_signup_email'], 'signup', _post('signup_code'));
        if (!$ok) {
            $otpStep = true;
            $otpHint = OtantikAuth::hint($_SESSION['wo_signup_email']);
            $ui->assign('notify', $msg);
            $ui->assign('notify_t', 'e');
        } else {
            if (empty($payload['ref'])) {
                $payload['ref'] = $ref;
            }
            wifi_otantik_create_admin($payload, 'Compte vérifié. Bienvenue sur Wifi-Otantik');
            return;
        }
    } elseif (_post('password') && (_post('email') || _post('username'))) {
        $user = trim(_post('username'));
        $pass = _post('password');
        $full = trim(_post('fullname'));
        $phone = wifi_otantik_phone(_post('phone'));
        $email = strtolower(trim(_post('email')));
        $back = Tenant::url_business('/inscription' . ($ref !== '' ? '?ref=' . rawurlencode($ref) : ''));
        if (strlen($pass) < 6 || strlen($phone) < 9 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            r2($back, 'e', 'Téléphone, email et mot de passe sont requis');
        }
        if ($user !== '' && ORM::for_table('tbl_users')->where('username', $user)->find_one()) {
            r2($back, 'e', 'Ce nom d\'utilisateur existe déjà');
        }
        if (ORM::for_table('tbl_users')->where('email', $email)->find_one()) {
            r2($back, 'e', 'Cet email a déjà un compte');
        }
        if ($user === '') {
            $user = wifi_otantik_make_username($email, $phone);
        }
        $payload = [
            'username' => $user,
            'password' => $pass,
            'fullname' => $full,
            'phone' => $phone,
            'email' => $email,
            'ref' => wifi_otantik_ref_resolve($ref),
        ];
        list($sent, $hint) = OtantikAuth::issue($email, 'signup', $payload);
        if (!$sent) {
            r2($back, 'e', $hint);
        }
        $_SESSION['wo_signup_email'] = $email;
        $otpStep = true;
        $otpHint = $hint;
        $ui->assign('notify', 'Code envoyé à ' . $hint);
        $ui->assign('notify_t', 's');
    }
    $ui->assign('_title', 'Créer un compte');
    $ui->assign('company', $config['CompanyName'] ?? 'Wifi-Otantik');
    $ui->assign('otp_step', $otpStep);
    $ui->assign('otp_hint', $otpHint);
    wifi_otantik_assign_auth($ui);
    $ui->display('otantik_signup.tpl');
}

function wifi_otantik_create_admin($payload, $msg)
{
    try {
        $email = strtolower(trim((string)($payload['email'] ?? '')));
        $user = trim((string)($payload['username'] ?? ''));
        if ($user === '') {
            $user = wifi_otantik_make_username($email, $payload['phone'] ?? '');
        }
        $d = ORM::for_table('tbl_users')->create();
        $d->username = $user;
        $d->fullname = trim((string)($payload['fullname'] ?? $user));
        $d->password = Password::_crypt((string)($payload['password'] ?? bin2hex(random_bytes(8))));
        $d->phone = wifi_otantik_phone($payload['phone'] ?? '');
        $d->email = $email;
        $d->user_type = 'Admin';
        $d->status = 'Active';
        $d->root = 0;
        $d->creationdate = date('Y-m-d H:i:s');
        try {
            if (!empty($payload['google_id'])) {
                $d->google_id = $payload['google_id'];
            }
        } catch (Throwable $e) {
        }
        $d->save();
        $uid = (int)$d->id();
        $t = Tenant::tenant_row($uid);
        $t->phone = wifi_otantik_phone($payload['phone'] ?? '');
        $t->commission_rate = 0; // nouvel admin = 0 % jusqu’à réglage SuperAdmin
        $resolved = wifi_otantik_ref_resolve((string)($payload['ref'] ?? ''), $uid);
        if ($resolved === '') {
            $resolved = wifi_otantik_ref_resolve(wifi_otantik_ref_raw(), $uid);
        }
        if ($resolved !== '') {
            $t->referred_by = $resolved;
        }
        $t->save();
        OtantikAuth::finish_login($uid, $msg);
    } catch (Throwable $e) {
        @file_put_contents('/tmp/otantik-ex.txt', date('c') . " signup " . $e->getMessage() . "\n", FILE_APPEND);
        r2(getUrl('plugin/wifi_otantik_signup'), 'e', 'Impossible de créer le compte. Réessayez.');
    }
}

function wifi_otantik_login()
{
    global $ui, $config;
    wifi_otantik_ref_capture();
    if (Tenant::is_wifi_host() || Tenant::is_portal_host()) {
        header('Location: ' . Tenant::url_business('/connexion'));
        exit;
    }
    if (Admin::getID()) {
        r2(Tenant::landing_url());
    }
    if (_post('login') && _post('password')) {
        $d = wifi_otantik_find_login_user(_post('login'));
        $pass = (string)_post('password');
        if (!$d || !Password::_verify($pass, $d['password'])) {
            if ($d) {
                OtantikAuth::log_login((int)$d['id'], (string)$d['username'], 'failed', 'Mot de passe incorrect');
            }
            r2(Tenant::url_business('/connexion'), 'e', 'Téléphone, email ou mot de passe incorrect');
        }
        if (($d['status'] ?? 'Active') === 'Inactive') {
            OtantikAuth::log_login((int)$d['id'], (string)$d['username'], 'failed', 'Compte inactif');
            r2(Tenant::url_business('/connexion'), 'e', 'Compte inactif');
        }
        OtantikAuth::finish_login((int)$d['id']);
        return;
    }
    $ui->assign('_title', 'Connexion');
    wifi_otantik_assign_auth($ui);
    $ui->display('admin/login.tpl');
}

function wifi_otantik_forgot()
{
    global $ui;
    if (Tenant::is_wifi_host() || Tenant::is_portal_host()) {
        header('Location: ' . Tenant::url_business('/mot-de-passe'));
        exit;
    }
    if (Admin::getID()) {
        r2(Tenant::landing_url());
    }
    $otpStep = false;
    $otpHint = '';
    if (_post('reset_code') && _post('new_password') && !empty($_SESSION['wo_reset_email'])) {
        $n1 = (string)_post('new_password');
        $n2 = (string)_post('new_password2');
        if (strlen($n1) < 6 || $n1 !== $n2) {
            $otpStep = true;
            $otpHint = OtantikAuth::hint($_SESSION['wo_reset_email']);
            $ui->assign('notify', 'Les mots de passe ne correspondent pas (6 caractères min.)');
            $ui->assign('notify_t', 'e');
        } else {
            list($ok, $payload, $msg) = OtantikAuth::consume($_SESSION['wo_reset_email'], 'reset', _post('reset_code'));
            if (!$ok) {
                $otpStep = true;
                $otpHint = OtantikAuth::hint($_SESSION['wo_reset_email']);
                $ui->assign('notify', $msg);
                $ui->assign('notify_t', 'e');
            } else {
                $u = ORM::for_table('tbl_users')->find_one((int)($payload['uid'] ?? 0));
                if (!$u) {
                    r2(Tenant::url_business('/mot-de-passe'), 'e', 'Compte introuvable');
                }
                $u->password = Password::_crypt($n1);
                $u->save();
                unset($_SESSION['wo_reset_email']);
                r2(Tenant::url_business('/connexion'), 's', 'Mot de passe mis à jour. Connectez-vous.');
            }
        }
    } elseif (_post('login')) {
        $d = wifi_otantik_find_login_user(_post('login'));
        $email = $d ? strtolower(trim((string)$d['email'])) : '';
        if (!$d || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            r2(Tenant::url_business('/mot-de-passe'), 'e', 'Aucun compte avec un email pour ce téléphone / cette adresse');
        }
        list($sent, $hint) = OtantikAuth::issue($email, 'reset', ['uid' => (int)$d['id']]);
        if (!$sent) {
            r2(Tenant::url_business('/mot-de-passe'), 'e', $hint);
        }
        $_SESSION['wo_reset_email'] = $email;
        $otpStep = true;
        $otpHint = $hint;
        $ui->assign('notify', 'Code envoyé à ' . $hint);
        $ui->assign('notify_t', 's');
    }
    $ui->assign('_title', 'Mot de passe oublié');
    $ui->assign('otp_step', $otpStep);
    $ui->assign('otp_hint', $otpHint);
    wifi_otantik_assign_auth($ui);
    $ui->display('otantik_forgot.tpl');
}

function wifi_otantik_help()
{
    global $ui, $config;
    $ui->assign('_title', 'Aide');
    $ui->assign('company', $config['CompanyName'] ?? 'Wifi-Otantik');
    wifi_otantik_assign_auth($ui);
    $ui->display('otantik_help.tpl');
}

function wifi_otantik_google()
{
    wifi_otantik_ref_capture();
    if (Tenant::is_wifi_host() || Tenant::is_portal_host()) {
        header('Location: ' . Tenant::url_business('/connexion'));
        exit;
    }
    if (Admin::getID()) {
        r2(Tenant::landing_url());
    }
    $cred = (string)(_post('credential') ?: '');
    list($g, $err) = OtantikAuth::google_user($cred);
    if (!$g) {
        r2(Tenant::url_business('/connexion'), 'e', $err);
    }
    $u = null;
    try {
        $u = ORM::for_table('tbl_users')->where('google_id', $g['sub'])->find_one();
    } catch (Throwable $e) {
    }
    if (!$u) {
        $u = ORM::for_table('tbl_users')->where('email', $g['email'])->find_one();
    }
    if ($u) {
        try {
            if (empty($u['google_id']) && !empty($g['sub'])) {
                $u->google_id = $g['sub'];
                $u->save();
            }
        } catch (Throwable $e) {
        }
        OtantikAuth::finish_login((int)$u['id'], 'Connecté avec Google');
        return;
    }
    wifi_otantik_create_admin([
        'email' => $g['email'],
        'fullname' => $g['name'],
        'google_id' => $g['sub'],
        'phone' => '',
        'password' => bin2hex(random_bytes(12)),
        'ref' => wifi_otantik_ref_capture(),
    ], 'Compte Google créé. Complétez votre téléphone dans Profil pour les retraits.');
}

/**
 * Assigns the same header/logo/theme/nav vars the buy page uses, so the
 * wait/fail/ticket pages that follow it in the checkout flow render inside
 * the same app shell instead of looking like separate, unrelated pages.
 */
function wifi_otantik_pay_shell($router, $channel = 'Hotspot')
{
    global $ui, $config;
    // iOS Safari caches/bfcaches dynamic pages far more aggressively than
    // Android Chrome when no explicit no-store header is sent - this was
    // the likely cause of the wait/fail/success pages looking "stuck" or
    // stale specifically on iPhone.
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
    $slug = (string)($router['slug'] ?? '');
    $cfg = OtantikCaptive::cfg($router);
    $ui->assign('channel', $channel);
    $ui->assign('connectik_home', wifi_otantik_connectik_url());
    $ui->assign('company', $config['CompanyName'] ?? 'Wifi-Otantik');
    $ui->assign('app_url', defined('APP_URL') ? APP_URL : '');
    $ui->assign('kit_url', OtantikCaptive::kit_url());
    $ui->assign('kit_logo', OtantikCaptive::logo_abs_url($router));
    $ui->assign('kit_title', $cfg['title']);
    $ui->assign('hs_theme_css', OtantikCaptive::theme_css($cfg));
    $ui->assign('hs_home', $channel === 'PPPOE' ? wifi_otantik_connectik_url($slug) : wifi_otantik_portal_buy_url($slug));
    $ui->assign('hs_ticket', Tenant::url_portal('/hotspot/' . rawurlencode($slug) . '/ticket.html'));
    $ui->assign('recover_url', Tenant::url_portal('/recuperer'));
}

function wifi_otantik_pay()
{
    global $ui, $config, $DEVICE_PATH;
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
    Tenant::ensure_schema();
    $slug = trim(_get('z') ?: (_get('zone') ?: ''));
    if ($slug === '' || (function_exists('wifi_otantik_portal_file') && wifi_otantik_portal_file($slug))) {
        $fromRef = function_exists('wifi_otantik_slug_from_referer') ? wifi_otantik_slug_from_referer() : '';
        if ($fromRef !== '') {
            $slug = $fromRef;
        }
    }
    $prefKind = strtoupper(trim(_get('kind') ?: ''));
    $prefUser = trim(_get('u') ?: '');
    $prefPhone = wifi_otantik_phone(_get('phone') ?: '');
    $prefPlan = (int)(_get('pid') ?: 0);
    if ($prefPlan < 1) {
        $prefPlan = (int)($GLOBALS['routes'][3] ?? 0);
    }
    if ($prefUser !== '' && $prefKind === '') {
        $prefKind = 'PPPOE';
    }
    $isPppoe = ($prefKind === 'PPPOE');
    if ($slug === '' && $isPppoe) {
        wifi_otantik_connectik_index();
        return;
    }
    if ($slug === '') {
        die('Zone introuvable');
    }
    $router = function_exists('wifi_otantik_find_router_by_slug')
        ? wifi_otantik_find_router_by_slug($slug)
        : ORM::for_table('tbl_routers')->where('slug', $slug)->where('enabled', 1)->find_one();
    if ($router && (int)$router['enabled'] !== 1) {
        $router = null;
    }
    if (!$router) {
        die('Zone introuvable');
    }
    if (_get('wo') === 'points') {
        wifi_otantik_pay_points_json($router);
        return;
    }
    if (_get('wo') === 'wait' || _get('wo') === 'paystat') {
        wifi_otantik_pay_wait($router);
        return;
    }
    $wantType = $isPppoe ? 'PPPOE' : 'Hotspot';
    if (_get('wo') === 'fail') {
        wifi_otantik_pay_fail_page($router, _get('why') ?: 'unknown', $wantType);
        return;
    }
    $plans = ORM::for_table('tbl_plans')->where('type', $wantType)->where('routers', $router['name'])->where('enabled', 1)->find_many();
    if (_post('plan_id')) {
        wifi_otantik_pay_collect($router, $plans, $wantType);
        return;
    }
    $notify = '';
    if ($ui && method_exists($ui, 'getTemplateVars')) {
        $notify = trim((string)$ui->getTemplateVars('notify'));
    } elseif ($ui && method_exists($ui, 'get_template_vars')) {
        $notify = trim((string)$ui->get_template_vars('notify'));
    }
    $wantCheckout = $isPppoe || $prefPlan > 0 || _get('wochk') === '1' || $notify !== '';
    if (!$wantCheckout) {
        OtantikCaptive::serve($router, 'login.html', false);
        return;
    }
    $sel = null;
    foreach ($plans as $p) {
        if ((int)$p['id'] === $prefPlan) {
            $sel = $p;
            break;
        }
    }
    $cfg = OtantikCaptive::cfg($router);
    $ui->assign('_title', $router['name']);
    $ui->assign('router', $router);
    $ui->assign('plans', $plans);
    $ui->assign('hotspot_plans', $isPppoe ? [] : $plans);
    $ui->assign('pppoe_plans', $isPppoe ? $plans : []);
    $ui->assign('sel_plan', $sel);
    $ui->assign('pref_user', $prefUser);
    $ui->assign('pref_kind', $isPppoe ? 'PPPOE' : 'Hotspot');
    $ui->assign('pref_phone', $prefPhone);
    $ui->assign('pref_plan', $prefPlan);
    $ui->assign('hs_mac', wifi_otantik_mac_from_request());
    $ui->assign('channel', $isPppoe ? 'PPPOE' : 'Hotspot');
    $ui->assign('pay_action', $isPppoe ? wifi_otantik_connectik_url($slug, $prefUser) : wifi_otantik_checkout_url($slug));
    $ui->assign('connectik_home', wifi_otantik_connectik_url());
    $ui->assign('company', $config['CompanyName'] ?? 'Wifi-Otantik');
    $ui->assign('login_url', 'http://' . wifi_otantik_hs_portal_host($router) . '/login');
    $ui->assign('kit_url', OtantikCaptive::kit_url());
    $ui->assign('kit_logo', OtantikCaptive::logo_abs_url($router));
    $ui->assign('kit_title', $cfg['title']);
    $ui->assign('hs_theme_css', OtantikCaptive::theme_css($cfg));
    $ui->assign('hs_accent', $cfg['color']);
    $ui->assign('hs_navy', $cfg['navy']);
    $ui->assign('hs_home', wifi_otantik_portal_buy_url($slug));
    $ui->assign('hs_ticket', Tenant::url_portal('/hotspot/' . rawurlencode($slug) . '/ticket.html'));
    $ui->assign('recover_url', Tenant::url_portal('/recuperer'));
    $refCfg = wifi_otantik_hs_ref_cfg($router);
    $ui->assign('ref_on', !$isPppoe && !empty($refCfg['on']));
    $ui->assign('pref_ref', wifi_otantik_phone(_get('ref') ?: (_post('ref_phone') ?: '')));
    $ui->assign('ref_points', (int)$refCfg['points']);
    $ui->assign('ref_bundle_pts', (int)$refCfg['bundle_pts']);
    $ui->assign('ref_help', (string)$refCfg['help']);
    $ui->assign('ref_rules', (string)$refCfg['rules']);
    $ui->assign('points_url', wifi_otantik_checkout_url($slug) . (strpos(wifi_otantik_checkout_url($slug), '?') === false ? '?' : '&') . 'wo=points');
    $ui->compile_check = true;
    try {
        $ui->clearCompiledTemplate('otantik_pay.tpl');
    } catch (Throwable $e) {
    }
    $ui->display('otantik_pay.tpl');
}

function wifi_otantik_pay_fail_msg($why)
{
    $why = trim((string)$why);
    $map = [
        'plan' => 'Forfait invalide',
        'hotspot' => 'Ce forfait n’est pas PPPoE',
        'pppoe' => 'Les forfaits PPPoE sont sur Connectik',
        'phone' => 'Numéro camerounais invalide (6XX XXX XXX)',
        'amount' => 'Numéro ou montant invalide',
        'operator' => 'Numéro MTN MoMo ou Orange Money requis',
        'busy' => 'Paiement déjà en cours. Patientez un instant.',
        'user' => 'Identifiant PPPoE requis',
        'nouser' => 'Identifiant introuvable. Vérifiez votre login.',
        'refused' => 'Paiement refusé',
        'notfound' => 'Paiement introuvable',
        'network' => 'Paiement indisponible. Réessayez.',
        'unknown' => 'Paiement impossible',
    ];
    return $map[$why] ?? ($why !== '' ? $why : $map['unknown']);
}

function wifi_otantik_pay_fail_url($router, $kind = 'Hotspot', $why = 'unknown', $user = '')
{
    $info = wifi_otantik_pay_why($why);
    $kind = strtoupper((string)$kind) === 'PPPOE' ? 'PPPOE' : 'Hotspot';
    $slug = rawurlencode((string)($router['slug'] ?? ''));
    $whyEnc = rawurlencode($info['code']);
    if ($kind === 'PPPOE') {
        $base = rtrim(wifi_otantik_connectik_url($router['slug'] ?? '', $user), '/');
        return $base . '/fail/' . $whyEnc;
    }
    return wifi_otantik_pay_public_root() . '/wifi/' . $slug . '/fail/' . $whyEnc;
}

function wifi_otantik_pay_fail_go($router, $kind, $why, $user = '')
{
    wifi_otantik_http_go(wifi_otantik_pay_fail_url($router, $kind, $why, $user));
}

function wifi_otantik_http_go($url)
{
    $url = trim((string)$url);
    if ($url === '') {
        $url = '/';
    }
    if (!headers_sent()) {
        header('Location: ' . $url, true, 302);
    }
    $safe = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url='
        . $safe . '"><title>Redirection</title></head><body><p><a href="' . $safe . '">Continuer</a></p></body></html>';
    exit;
}

function wifi_otantik_pay_why($code)
{
    $raw = strtolower(trim((string)$code));
    $c = preg_replace('/[^a-z0-9_-]+/', '', $raw);
    $all = [
        'refused' => ['Paiement refusé', 'Votre opérateur a refusé le paiement. Vérifiez le solde MoMo / Orange Money, puis réessayez.'],
        'cancelled' => ['Paiement annulé', 'La confirmation a été annulée sur le téléphone. Relancez l’achat quand vous êtes prêt.'],
        'expired' => ['Délai dépassé', 'Le délai de confirmation a expiré. Aucun ticket n’a été créé. Relancez un nouveau paiement.'],
        'busy' => ['Paiement déjà en cours', 'Un paiement est déjà en attente pour ce numéro. Composez *126# (MTN MoMo) ou #150*50# (Orange Money) pour vérifier ou confirmer la demande, puis réessayez dans quelques minutes.'],
        'phone' => ['Numéro invalide', 'Entrez un numéro camerounais au format 6XX XXX XXX (MTN ou Orange).'],
        'operator' => ['Opérateur non reconnu', 'Utilisez un numéro MTN MoMo ou Orange Money pour payer.'],
        'plan' => ['Forfait invalide', 'Ce forfait n’est plus disponible. Choisissez un autre forfait.'],
        'pppoe' => ['Mauvais canal', 'Les forfaits internet maison se paient sur Connectik, pas depuis le portail Wi-Fi.'],
        'hotspot' => ['Mauvais canal', 'Ce forfait Wi-Fi se paie depuis le portail de la zone, pas sur Connectik.'],
        'notfound' => ['Paiement introuvable', 'Cette session de paiement n’existe plus. Recommencez l’achat.'],
        'network' => ['Réseau indisponible', 'Impossible de joindre le service de paiement. Réessayez dans un instant.'],
        'amount' => ['Montant invalide', 'Le montant du forfait n’est pas valide. Choisissez un autre forfait.'],
        'user' => ['Identifiant requis', 'Saisissez votre identifiant internet maison pour recharger.'],
        'nouser' => ['Identifiant introuvable', 'Aucun abonnement ne correspond à cet identifiant. Vérifiez le login.'],
        'credit' => ['Crédit insuffisant', 'Vos points ne couvrent pas ce forfait. Choisissez un forfait moins cher, ou payez en Mobile Money.'],
        'unknown' => ['Paiement non abouti', 'Le paiement n’a pas abouti. Aucun ticket n’a été créé. Vous pouvez réessayer.'],
    ];
    if (!isset($all[$c])) {
        if (preg_match('/insuffisant|insufficient|solde|balance|refus|declin|fail|échou/', $raw)) {
            $c = 'refused';
        } elseif (preg_match('/annul|cancel/', $raw)) {
            $c = 'cancelled';
        } elseif (preg_match('/expir|timeout|délai/', $raw)) {
            $c = 'expired';
        } elseif (preg_match('/busy|en cours|already/', $raw)) {
            $c = 'busy';
        } elseif (preg_match('/numéro|phone|invalide/', $raw)) {
            $c = 'phone';
        } elseif (preg_match('/mtn|orange|opérateur|operator/', $raw)) {
            $c = 'operator';
        } elseif (preg_match('/réseau|network|unreachable|connexion/', $raw)) {
            $c = 'network';
        } elseif (preg_match('/forfait|plan/', $raw)) {
            $c = 'plan';
        } elseif (preg_match('/introuvable|not found/', $raw)) {
            $c = 'notfound';
        } else {
            $c = 'unknown';
        }
    }
    $row = $all[$c] ?? $all['unknown'];
    return ['code' => $c, 'title' => $row[0], 'text' => $row[1]];
}

function wifi_otantik_pay_fail_page($router, $why, $kind = 'Hotspot')
{
    global $ui, $config;
    $info = wifi_otantik_pay_why($why);
    $kind = strtoupper((string)$kind) === 'PPPOE' ? 'PPPOE' : 'Hotspot';
    if (class_exists('OtantikWhatsApp')) {
        try {
            OtantikWhatsApp::notify_pay_help(wifi_otantik_wa_last_phone(), $router, $info['code'], $kind);
        } catch (Throwable $e) {
        }
    }
    $home = $kind === 'PPPOE'
        ? wifi_otantik_connectik_url($router['slug'] ?? '')
        : wifi_otantik_portal_buy_url($router['slug'] ?? '');
    $retry = $kind === 'PPPOE'
        ? wifi_otantik_connectik_url($router['slug'] ?? '', _get('u'))
        : wifi_otantik_checkout_url($router['slug'] ?? '');
    wifi_otantik_pay_shell($router, $kind);
    $ui->assign('_title', $info['title']);
    $ui->assign('fail_title', $info['title']);
    $ui->assign('fail_text', $info['text']);
    $ui->assign('pay_retry', $retry);
    $ui->assign('pay_home', $home);
    try {
        $ui->clearCompiledTemplate('otantik_pay_fail.tpl');
    } catch (Throwable $e) {
    }
    $ui->display('otantik_pay_fail.tpl');
    exit;
}

function wifi_otantik_connectik_index()
{
    global $ui, $config;
    $zones = [];
    foreach (ORM::for_table('tbl_routers')->where('enabled', 1)->order_by_asc('name')->find_many() as $r) {
        $n = (int)ORM::for_table('tbl_plans')->where('type', 'PPPOE')->where('routers', $r['name'])->where('enabled', 1)->count();
        if ($n > 0) {
            $zones[] = [
                'name' => $r['name'],
                'slug' => $r['slug'],
                'url' => wifi_otantik_connectik_url($r['slug']),
                'plans' => $n,
            ];
        }
    }
    $ui->assign('_title', 'Connectik');
    $ui->assign('zones', $zones);
    $ui->assign('company', $config['CompanyName'] ?? 'Wifi-Otantik');
    $ui->display('otantik_connectik.tpl');
}

function wifi_otantik_pay_collect($router, $plans, $channel = '')
{
    global $ui, $config, $DEVICE_PATH;
    $planId = (int)_post('plan_id');
    $plan = null;
    foreach ($plans as $p) {
        if ((int)$p['id'] === $planId) {
            $plan = $p;
            break;
        }
    }
    $channel = strtoupper($channel ?: (_get('kind') ?: 'Hotspot'));
    $pppoeUser = trim((string)_post('pppoe_username'));
    if (!$plan) {
        wifi_otantik_pay_fail_go($router, $channel, 'plan', $pppoeUser);
    }
    $kind = wifi_otantik_plan_kind($plan);
    if ($channel === 'PPPOE' && $kind !== 'PPPOE') {
        wifi_otantik_pay_fail_go($router, $channel, 'hotspot', $pppoeUser);
    }
    if ($channel !== 'PPPOE' && $kind === 'PPPOE') {
        wifi_otantik_pay_fail_go($router, $channel, 'pppoe', $pppoeUser);
    }
    $phone = wifi_otantik_phone(_post('phone'));
    wifi_otantik_wa_remember_phone($phone);
    $service = wifi_otantik_pick_service($phone, _post('service'));
    $price = (int)round((float)$plan['price']);
    if (!wifi_otantik_phone_ok($phone) || ($kind !== 'Hotspot' && $price < 100) || $price < 0) {
        wifi_otantik_pay_fail_go($router, $channel, !wifi_otantik_phone_ok($phone) ? 'phone' : 'amount', $pppoeUser);
    }
    if ($service === '' && $kind !== 'Hotspot') {
        wifi_otantik_pay_fail_go($router, $channel, 'operator', $pppoeUser);
    }
    $busy = wifi_otantik_pay_busy($phone, (int)$plan['id'], $router['name']);
    if ($busy !== '') {
        wifi_otantik_pay_fail_go($router, $channel, 'busy', $pppoeUser);
    }
    $user = 'p' . $phone;
    $pass = $user;
    $loginUser = trim(_post('pppoe_username'));
    if ($kind === 'PPPOE') {
        $loginUser = preg_replace('/[^A-Za-z0-9._-]/', '', $loginUser);
        if (strlen($loginUser) < 3) {
            wifi_otantik_pay_fail_go($router, $channel, 'user', $pppoeUser);
        }
        $cust = ORM::for_table('tbl_customers')->where('username', $loginUser)->find_one();
        if (!$cust && wifi_otantik_customer_has_col('pppoe_username')) {
            $cust = ORM::for_table('tbl_customers')->where('pppoe_username', $loginUser)->find_one();
        }
        if (!$cust) {
            wifi_otantik_pay_fail_go($router, $channel, 'nouser', $pppoeUser);
        }
        $user = (string)($cust['username'] ?: $loginUser);
        $pass = (string)($cust['password'] ?? '');
        if ($pass === '' && wifi_otantik_customer_has_col('pppoe_password')) {
            $pass = (string)($cust['pppoe_password'] ?? '');
        }
        if ($pass === '') {
            wifi_otantik_pay_fail_go($router, $channel, 'nouser', $pppoeUser);
        }
        $cust = wifi_otantik_upsert_wifi_customer($user, $pass, $phone, $kind, $router, true);
    } else {
        $user = wifi_otantik_hs_login_for_phone($phone, $router);
        $pass = $user;
        $cust = wifi_otantik_upsert_wifi_customer($user, $pass, $phone, $kind, $router);
    }
    $discount = 0;
    $reward = 0;
    $refCust = null;
    $refPhone = '';
    $refCfg = wifi_otantik_hs_ref_cfg($router);
    if ($kind !== 'PPPOE' && !empty($refCfg['on'])) {
        $refPhone = wifi_otantik_phone(_post('ref_phone') ?: '');
        if (strlen($refPhone) >= 9 && $refPhone !== $phone) {
            $refCust = true;
            $reward = (int)$refCfg['points'];
        }
    }
    $due = max(0, $price - $discount);
    $haveCredit = ($kind !== 'PPPOE') ? wifi_otantik_hs_points($cust) : 0;
    $wantPoints = $kind !== 'PPPOE' && (int)_post('use_points') === 1;
    $creditUsed = 0;
    if ($wantPoints) {
        if ($due > 0 && $haveCredit >= $due) {
            $creditUsed = $due;
        } else {
            wifi_otantik_pay_fail_go($router, $channel, 'credit', $pppoeUser);
        }
    }
    $amount = max(0, $due - $creditUsed);
    if ($amount > 0 && $amount < 100) {
        $amount = 100;
        $creditUsed = max(0, $due - $amount);
    }
    if ($amount > 0 && $service === '') {
        wifi_otantik_pay_fail_go($router, $channel, 'operator', $pppoeUser);
    }
    $trx = ORM::for_table('tbl_payment_gateway')->create();
    $trx->username = $phone;
    $trx->user_id = (int)$cust['id'];
    $trx->gateway = $amount > 0 ? OtantikPay::active() : 'credit';
    $trx->plan_id = (int)$plan['id'];
    $trx->plan_name = $plan['name_plan'];
    $trx->routers_id = (int)$router['id'];
    $trx->routers = $router['name'];
    $trx->price = (string)($amount > 0 ? $amount : $creditUsed);
    $trx->payment_method = $amount > 0 ? $service : 'CREDIT';
    $trx->payment_channel = substr($user . ':' . $pass, 0, 128);
    $trx->pg_url_payment = $refPhone !== '' ? ('ref:' . $refPhone) : 'internal';
    $trx->created_date = date('Y-m-d H:i:s');
    $trx->expired_date = date('Y-m-d H:i:s', time() + 3600);
    $trx->status = 1;
    $trx->save();
    try {
        $trx->pg_request = json_encode([
            'credit_used' => $creditUsed,
            'discount' => $discount,
            'reward' => $reward,
            'ref_phone' => $refPhone,
            'kind' => $kind,
            'amount' => $amount,
            'mac' => wifi_otantik_mac_from_request(),
        ]);
        $trx->save();
        if ($kind !== 'PPPOE') {
            wifi_otantik_mac_bypass_save_pending($router, $user, wifi_otantik_mac_from_request(), (int)$trx->id());
        }
    } catch (Throwable $e) {
    }
    try {
        if ($amount > 0) {
            $resp = OtantikPay::collect($amount, $phone, $service, 'WIFI-' . $trx->id(), [
                'first_name' => $phone,
                'last_name' => $router['name'],
                'user_id' => 'c' . (int)$cust['id'],
            ]);
            $trx->pg_paid_response = json_encode($resp);
            $trx->gateway_trx_id = $resp['reference'] ?: '';
            $trx->save();
            if (!empty($resp['pending'])) {
                wifi_otantik_http_go(wifi_otantik_pay_wait_url($router, $trx->id(), $kind));
            }
            if (empty($resp['ok'])) {
                $trx->status = 3;
                $trx->save();
                wifi_otantik_pay_fail_go($router, $kind, wifi_otantik_pay_why($resp['message'] ?: 'refused')['code'], $pppoeUser);
            }
        } else {
            $trx->pg_paid_response = json_encode(['ok' => true, 'credit' => $creditUsed]);
        }
        wifi_otantik_pay_finish($trx, $router, $plan, $user, $pass, $kind, $cust, [
            'amount' => $amount,
            'credit_used' => $creditUsed,
            'discount' => $discount,
            'reward' => $reward,
            'ref_phone' => $refPhone,
        ], true);
        return;
    } catch (Exception $e) {
        $alreadyPaid = (int)$trx['status'] === 2 || trim((string)($trx['paid_date'] ?? '')) !== '';
        if ($alreadyPaid) {
            try {
                wifi_otantik_pay_show_ticket($router, $plan, $user, $pass, $kind, $cust, [
                    'discount' => $discount,
                    'credit_used' => $creditUsed,
                    'auth_mode' => 'bypass',
                ]);
            } catch (Throwable $e2) {
            }
            return;
        }
        $trx->status = 3;
        $trx->pg_paid_response = $e->getMessage();
        $trx->save();
        wifi_otantik_pay_fail_go($router, $kind, wifi_otantik_pay_why($e->getMessage() ?: 'network')['code'], $pppoeUser);
    }
}

function wifi_otantik_pay_wait_url($router, $trxId, $kind = 'Hotspot')
{
    $kind = strtoupper((string)$kind) === 'PPPOE' ? 'PPPOE' : 'Hotspot';
    $tid = (int)$trxId;
    $slug = rawurlencode((string)($router['slug'] ?? ''));
    if ($kind === 'PPPOE') {
        return rtrim(wifi_otantik_connectik_url($router['slug'] ?? ''), '/') . '/wait/' . $tid;
    }
    return wifi_otantik_pay_public_root() . '/wifi/' . $slug . '/wait/' . $tid;
}

function wifi_otantik_pay_wait($router)
{
    global $ui, $config;
    $id = (int)(_get('tid') ?: 0);
    $trx = $id ? ORM::for_table('tbl_payment_gateway')->find_one($id) : null;
    $json = _get('wo') === 'paystat';
    $kind = strtoupper((string)(_get('kind') ?: 'Hotspot'));
    $back = wifi_otantik_pay_back($router, $kind);
    if (!$trx || (int)$trx['routers_id'] !== (int)$router['id']) {
        if ($json) {
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json');
            echo json_encode(['status' => 'fail']);
            exit;
        }
        wifi_otantik_pay_fail_go($router, $kind, 'notfound');
    }
    if ((int)$trx['status'] === 1 && trim((string)$trx['gateway_trx_id']) !== '') {
        try {
            $st = OtantikPay::status($trx['gateway_trx_id']);
            if (!empty($st['ok'])) {
                wifi_otantik_pay_resume($trx, false);
                $trx = ORM::for_table('tbl_payment_gateway')->find_one($id);
            } elseif (empty($st['pending'])) {
                $trx->status = 3;
                $trx->pg_paid_response = json_encode($st);
                $trx->save();
            }
        } catch (Throwable $e) {
        }
    }
    if ($json) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        $s = (int)$trx['status'];
        echo json_encode(['status' => $s === 2 ? 'paid' : ($s === 3 ? 'fail' : 'pending')]);
        exit;
    }
    if ((int)$trx['status'] === 2) {
        wifi_otantik_pay_resume($trx, true);
        return;
    }
    if ((int)$trx['status'] === 3) {
        $failMsg = 'refused';
        $resp = json_decode((string)($trx['pg_paid_response'] ?? ''), true);
        if (is_array($resp) && !empty($resp['message'])) {
            $failMsg = (string)$resp['message'];
        }
        wifi_otantik_wa_remember_phone($trx['username'] ?? '');
        wifi_otantik_pay_fail_go($router, $kind, $failMsg);
    }
    $wait = wifi_otantik_pay_wait_url($router, $id, $kind);
    $stat = preg_replace('#/wait/(\d+)/?$#', '/wait/$1/stat', rtrim($wait, '/'));
    if ($stat === $wait) {
        $stat = $wait . (strpos($wait, '?') === false ? '?' : '&') . 'wo=paystat';
    }
    wifi_otantik_pay_shell($router, $kind);
    $ui->assign('_title', 'Paiement en cours');
    $ussd = wifi_otantik_pay_ussd($trx['username'], $trx['payment_method']);
    $ui->assign('pay_wait_phone', wifi_otantik_phone_fmt($trx['username']));
    $ui->assign('pay_ussd_op', $ussd['op']);
    $ui->assign('pay_ussd_code', $ussd['code']);
    $ui->assign('pay_ussd_label', $ussd['label']);
    $ui->assign('pay_stat_url', $stat);
    $ui->assign('pay_back', $back);
    $ui->assign('pay_fail', wifi_otantik_pay_fail_url($router, $kind, 'refused'));
    $ui->display('otantik_pay_wait.tpl');
}

function wifi_otantik_pay_meta($trx)
{
    $meta = [];
    try {
        $decoded = json_decode((string)($trx['pg_request'] ?? ''), true);
        if (is_array($decoded)) {
            $meta = $decoded;
        }
    } catch (Throwable $e) {
    }
    $ref = (string)($trx['pg_url_payment'] ?? '');
    if (strpos($ref, 'ref:') === 0 && empty($meta['ref_phone'])) {
        $meta['ref_phone'] = substr($ref, 4);
    }
    $parts = explode(':', (string)$trx['payment_channel'], 2);
    return [
        'credit_used' => (int)($meta['credit_used'] ?? 0),
        'discount' => (int)($meta['discount'] ?? 0),
        'reward' => (int)($meta['reward'] ?? 0),
        'ref_phone' => (string)($meta['ref_phone'] ?? ''),
        'kind' => (string)($meta['kind'] ?? ''),
        'amount' => (int)($meta['amount'] ?? (int)$trx['price']),
        'user' => (string)($parts[0] ?? ''),
        'pass' => (string)($parts[1] ?? ''),
    ];
}

function wifi_otantik_pay_resume($trx, $display = true)
{
    if (!$trx) {
        return false;
    }
    $url = (string)$trx['pg_url_payment'];
    if (strpos($url, 'reseller-topup:') === 0) {
        if ((int)$trx['status'] === 2) {
            return true;
        }
        if ((int)$trx['status'] !== 1) {
            return false;
        }
        $trx->status = 2;
        $trx->paid_date = date('Y-m-d H:i:s');
        $trx->save();
        $rid = (int)substr($url, strlen('reseller-topup:'));
        $dup = false;
        try {
            $dup = (bool)ORM::for_table('tbl_otantik_books')->where('trx_id', (int)$trx['id'])->find_one();
        } catch (Throwable $e) {
        }
        if (!$dup) {
            $rs = ORM::for_table('tbl_otantik_resellers')->find_one($rid);
            if ($rs) {
                $amount = (int)$trx['price'];
                $owner = Tenant::tenant_row((int)$rs['owner_id']);
                $split = Tenant::split($amount, $owner);
                if ($owner && $split['net'] > 0) {
                    $owner->wallet = (float)$owner['wallet'] + $split['net'];
                    $owner->save();
                }
                OtantikReseller::credit($rs, $amount, 'self_topup', [
                    'client_amount' => $split['net'],
                    'commission' => $split['fee'],
                    'rate' => $split['rate'],
                    'trx_id' => (int)$trx['id'],
                    'ledger' => true,
                    'note' => 'Recharge Mobile Money ' . $trx['payment_method'] . ' · net ' . (int)$split['net'] . ' F après ' . (int)$split['rate'] . ' %',
                ]);
            }
        }
        return true;
    }
    $meta = wifi_otantik_pay_meta($trx);
    $router = ORM::for_table('tbl_routers')->find_one((int)$trx['routers_id']);
    if (!$router && !empty($trx['routers'])) {
        $router = ORM::for_table('tbl_routers')->where('name', $trx['routers'])->find_one();
    }
    $plan = ORM::for_table('tbl_plans')->find_one((int)$trx['plan_id']);
    $cust = (int)$trx['user_id'] > 0 ? ORM::for_table('tbl_customers')->find_one((int)$trx['user_id']) : null;
    if (!$router || !$plan) {
        return false;
    }
    $kind = $meta['kind'] !== '' ? $meta['kind'] : wifi_otantik_plan_kind($plan);
    $user = $meta['user'] !== '' ? $meta['user'] : ((string)$cust['username'] ?? '');
    $pass = $meta['pass'] !== '' ? $meta['pass'] : ((string)$cust['password'] ?? $user);
    if ((int)$trx['status'] === 2) {
        if ($display) {
            wifi_otantik_pay_show_ticket($router, $plan, $user, $pass, $kind, $cust, [
                'discount' => $meta['discount'],
                'credit_used' => $meta['credit_used'],
                'auth_mode' => 'bypass',
            ]);
            wifi_otantik_flush_response();
            wifi_otantik_pay_apply_mikrotik(
                $router,
                $plan,
                $user,
                $pass,
                $kind,
                $cust,
                $trx,
                (int)($trx['price'] ?? 0)
            );
        }
        return true;
    }
    if ((int)$trx['status'] !== 1) {
        return false;
    }
    return wifi_otantik_pay_finish($trx, $router, $plan, $user, $pass, $kind, $cust, $meta, $display);
}

function wifi_otantik_pay_finish($trx, $router, $plan, $user, $pass, $kind, $cust, $opts, $display = true)
{
    global $ui;
    if ((int)$trx['status'] === 2) {
        if ($display) {
            wifi_otantik_pay_show_ticket($router, $plan, $user, $pass, $kind, $cust, [
                'discount' => (int)($opts['discount'] ?? 0),
                'credit_used' => (int)($opts['credit_used'] ?? 0),
                'auth_mode' => 'bypass',
            ]);
            wifi_otantik_flush_response();
            wifi_otantik_pay_apply_mikrotik($router, $plan, $user, $pass, $kind, $cust, $trx, (int)($opts['amount'] ?? 0));
        }
        return true;
    }
    $amount = (int)($opts['amount'] ?? 0);
    $creditUsed = (int)($opts['credit_used'] ?? 0);
    $discount = (int)($opts['discount'] ?? 0);
    $reward = (int)($opts['reward'] ?? 0);
    $refPhone = (string)($opts['ref_phone'] ?? '');
    $phone = (string)$trx['username'];
    $trx->status = 2;
    $trx->paid_date = date('Y-m-d H:i:s');
    $trx->save();
    wifi_otantik_receipt_ensure($trx);
    if ($creditUsed > 0 && $cust) {
        wifi_otantik_hs_take_credit($cust, $creditUsed);
    }
    if ($refPhone !== '' && $reward > 0) {
        $refCust = wifi_otantik_hs_ensure_phone_customer($refPhone, $router);
        if ($refCust && (!$cust || (int)$refCust['id'] !== (int)$cust['id'])) {
            wifi_otantik_hs_add_credit($refCust, $reward);
            try {
                $log = ORM::for_table('tbl_otantik_hs_refs')->create();
                $log->owner_id = (int)$router['owner_id'];
                $log->router_id = (int)$router['id'];
                $log->trx_id = (int)$trx->id();
                $log->buyer_phone = $phone;
                $log->ref_code = '';
                $log->discount = $discount;
                $log->reward = $reward;
                $log->created_at = date('Y-m-d H:i:s');
                try {
                    $log->ref_phone = $refPhone;
                    $log->points = $reward;
                } catch (Throwable $e) {
                }
                $log->save();
            } catch (Throwable $e) {
            }
        }
    }
    wifi_otantik_credit_wallet_for_sale($trx, $router);
    // Poll/API path: mark paid quickly and return. Ticket page will push + bypass.
    if (!$display) {
        return true;
    }
    // Direct pay (points etc.): show ticket immediately, then MikroTik work.
    wifi_otantik_pay_show_ticket($router, $plan, $user, $pass, $kind, $cust, [
        'discount' => $discount,
        'credit_used' => $creditUsed,
        'auth_mode' => 'bypass',
    ]);
    wifi_otantik_flush_response();
    wifi_otantik_pay_apply_mikrotik($router, $plan, $user, $pass, $kind, $cust, $trx, $amount);
    return true;
}

/** Push hotspot user + intentional MAC bypass (wo-byp:*) after the HTTP response. */
function wifi_otantik_pay_apply_mikrotik($router, $plan, $user, $pass, $kind, $cust, $trx, $amount)
{
    global $ui;
    $phone = (string)($trx['username'] ?? '');
    try {
        wifi_otantik_push_user_mikrotik($router, $plan, $user, $pass, 'otantik ' . $phone);
        wifi_otantik_record_recharge($cust, $plan, $router, $user, $amount > 0 ? 'Mobile Money' : 'Points', $trx);
        wifi_otantik_wa_ticket($cust, $plan, $router, $user, $pass, $kind);
    } catch (Throwable $e) {
        if ($ui) {
            try {
                $ui->assign('push_error', $e->getMessage());
            } catch (Throwable $e2) {
            }
        }
    }
    if (strtoupper((string)$kind) === 'PPPOE') {
        return;
    }
    try {
        wifi_otantik_mac_bypass_try($router, $plan, $user, '', $trx);
    } catch (Throwable $e) {
    }
}

/** Let the browser paint before slow MikroTik work continues. */
function wifi_otantik_flush_response()
{
    try {
        if (function_exists('session_write_close')) {
            @session_write_close();
        }
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
        @flush();
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }
    } catch (Throwable $e) {
    }
}

function wifi_otantik_pay_row_for_phone($phone)
{
    $phone = wifi_otantik_phone($phone);
    if ($phone === '') {
        return null;
    }
    try {
        return ORM::for_table('tbl_payment_gateway')
            ->where_in('gateway', ['mesomb', 'fapshi', 'credit', 'manual'])
            ->where('username', $phone)
            ->where('status', 2)
            ->order_by_desc('id')
            ->find_one();
    } catch (Throwable $e) {
        return null;
    }
}

function wifi_otantik_recover_fill_from_row($row)
{
    $creds = ['username' => '', 'password' => ''];
    $planKind = 'Hotspot';
    $router = null;
    $planName = '';
    if (!$row) {
        return [$creds, $planKind, $router, $planName];
    }
    $parts = explode(':', (string)$row['payment_channel'], 2);
    $creds = ['username' => trim((string)($parts[0] ?? '')), 'password' => trim((string)($parts[1] ?? ''))];
    if ($creds['password'] === '' && $creds['username'] !== '') {
        $creds['password'] = $creds['username'];
    }
    $pl = !empty($row['plan_id']) ? ORM::for_table('tbl_plans')->find_one($row['plan_id']) : null;
    if ($pl) {
        $planKind = wifi_otantik_plan_kind($pl);
        $planName = (string)$pl['name_plan'];
    } elseif (!empty($row['plan_name'])) {
        $planName = (string)$row['plan_name'];
    }
    $router = ORM::for_table('tbl_routers')->find_one((int)$row['routers_id']);
    if (!$router && !empty($row['routers'])) {
        $router = ORM::for_table('tbl_routers')->where('name', $row['routers'])->find_one();
    }
    return [$creds, $planKind, $router, $planName];
}

function wifi_otantik_recover()
{
    global $ui, $config;
    Tenant::ensure_schema();
    $saved = wifi_otantik_ticket_decode_cookie();
    $posted = trim((string)_post('phone'));
    $phone = wifi_otantik_phone($posted !== '' ? $posted : (_get('phone') ?: ($saved['phone'] ?? '')));
    $codeIn = preg_replace('/[^A-Za-z0-9._-]/', '', (string)(_get('code') ?: (_post('code') ?: ($saved['user'] ?? ''))));
    $row = $phone !== '' ? wifi_otantik_pay_row_for_phone($phone) : null;
    list($creds, $planKind, $router, $planName) = wifi_otantik_recover_fill_from_row($row);
    if ($creds['username'] === '' && $codeIn !== '') {
        $creds = ['username' => $codeIn, 'password' => (string)($saved['pass'] ?? $codeIn)];
    }
    $rech = wifi_otantik_code_active($creds['username']);
    if (!$rech && $codeIn !== '' && $codeIn !== $creds['username']) {
        $rech = wifi_otantik_code_active($codeIn);
        if ($rech) {
            $creds['username'] = $codeIn;
            if ($creds['password'] === '') {
                $creds['password'] = (string)($saved['pass'] ?? $codeIn);
            }
        }
    }
    if ($rech && !$router && !empty($rech['routers'])) {
        $router = ORM::for_table('tbl_routers')->where('name', $rech['routers'])->find_one();
        $planName = $planName !== '' ? $planName : (string)($rech['namebp'] ?? '');
        $planKind = wifi_otantik_plan_kind($rech);
    }
    $ticketOk = (bool)$rech && $creds['username'] !== '';
    if ($ticketOk) {
        $expTs = 0;
        $info = wifi_otantik_expiry_info($rech);
        if (!empty($info['end_ts'])) {
            $expTs = (int)$info['end_ts'];
        }
        wifi_otantik_ticket_remember(
            $phone,
            $creds['username'],
            $creds['password'] !== '' ? $creds['password'] : $creds['username'],
            $expTs,
            (string)($router['slug'] ?? ($saved['slug'] ?? '')),
            $planName !== '' ? $planName : (string)($rech['namebp'] ?? '')
        );
        if ($planName === '') {
            $planName = (string)($rech['namebp'] ?? '');
        }
    }
    $cust = $phone !== '' ? wifi_otantik_hs_find_customer($phone, $router) : null;
    if (!$cust && $ticketOk) {
        try {
            $cust = ORM::for_table('tbl_customers')->where('username', $creds['username'])->find_one();
        } catch (Throwable $e) {
        }
    }
    $share = $cust ? wifi_otantik_hs_share_ensure($cust) : '';
    $credit = $cust ? (float)($cust['wo_credit'] ?? 0) : 0;
    $cheap = $router ? wifi_otantik_hs_cheapest_plan($router) : null;
    $canCredit = $cust && $router && $cheap && $credit >= (float)$cheap['price'] && $planKind !== 'PPPOE';
    $creditNotify = '';
    if ($phone !== '' && _post('use_credit')) {
        if (!$canCredit) {
            $creditNotify = 'Crédit insuffisant pour le plus petit forfait de cette zone.';
        } else {
            $user = (string)($cust['username'] ?: ('w' . $phone));
            $pass = $user;
            $used = wifi_otantik_hs_take_credit($cust, (float)$cheap['price']);
            if ($used < 1) {
                $creditNotify = 'Crédit insuffisant. Le ticket n’a pas été créé.';
            } else {
                try {
                    $trx = ORM::for_table('tbl_payment_gateway')->create();
                    $trx->username = $phone;
                    $trx->user_id = (int)$cust['id'];
                    $trx->gateway = 'credit';
                    $trx->plan_id = (int)$cheap['id'];
                    $trx->plan_name = $cheap['name_plan'];
                    $trx->routers_id = (int)$router['id'];
                    $trx->routers = $router['name'];
                    $trx->price = (string)$used;
                    $trx->payment_method = 'CREDIT';
                    $trx->payment_channel = $user . ':' . $pass;
                    $trx->pg_url_payment = 'credit';
                    $trx->created_date = date('Y-m-d H:i:s');
                    $trx->paid_date = date('Y-m-d H:i:s');
                    $trx->expired_date = date('Y-m-d H:i:s', time() + 3600);
                    $trx->status = 2;
                    $trx->save();
                } catch (Throwable $e) {
                }
                try {
                    wifi_otantik_push_user_mikrotik($router, $cheap, $user, $pass, 'otantik ' . $phone);
                    wifi_otantik_record_recharge($cust, $cheap, $router, $user, 'Crédit', $trx ?? null);
                    wifi_otantik_wa_ticket($cust, $cheap, $router, $user, $pass, 'Hotspot');
                } catch (Throwable $e) {
                    $ui->assign('push_error', $e->getMessage());
                }
                wifi_otantik_pay_show_ticket($router, $cheap, $user, $pass, 'Hotspot', $cust, ['credit_used' => $used]);
                return;
            }
        }
    }
    $slug = trim((string)($router['slug'] ?? ''));
    if ($slug === '') {
        $slug = (string)($saved['slug'] ?? wifi_otantik_slug_from_referer());
    }
    $cfg = $router ? OtantikCaptive::cfg($router) : ['title' => 'Wifi-Otantik', 'color' => '#f05018', 'navy' => '#002858'];
    $hsHome = $slug !== '' ? wifi_otantik_portal_buy_url($slug) : Tenant::url_portal('/');
    $hsTicket = $slug !== '' ? Tenant::url_portal('/hotspot/' . rawurlencode($slug) . '/ticket.html') : $hsHome;
    $buyUrl = $slug !== '' ? wifi_otantik_checkout_url($slug) : $hsTicket;
    $looked = $posted !== '' || trim((string)_get('phone')) !== '' || trim((string)_get('code')) !== '';
    $ui->assign('_title', 'Mon code');
    $ui->assign('phone', $phone);
    $ui->assign('row', $ticketOk ? ($row ?: $rech) : null);
    $ui->assign('creds', $creds);
    $ui->assign('plan_kind', $planKind);
    $ui->assign('plan_name', $planName);
    $ui->assign('ticket_ok', $ticketOk);
    $ui->assign('ticket_expired', !$ticketOk && ($looked || $codeIn !== '' || $phone !== ''));
    $ui->assign('not_found', $looked && !$ticketOk && !$row && !$cust && !$rech);
    $ui->assign('credit_notify', $creditNotify);
    $ui->assign('share_code', $share);
    $ui->assign('hs_credit', $credit);
    $ui->assign('can_credit', $canCredit);
    $ui->assign('cheap_plan', $cheap);
    $ui->assign('company', $config['CompanyName'] ?? 'Wifi-Otantik');
    $ui->assign('app_url', defined('APP_URL') ? APP_URL : Tenant::url_portal(''));
    $ui->assign('kit_url', OtantikCaptive::kit_url());
    $ui->assign('kit_logo', $router ? OtantikCaptive::logo_abs_url($router) : (defined('APP_URL') ? rtrim(APP_URL, '/') . '/ui/ui_custom/images/logo.png' : ''));
    $ui->assign('kit_title', $cfg['title'] ?? 'Wifi-Otantik');
    $ui->assign('hs_theme_css', OtantikCaptive::theme_css($cfg));
    $ui->assign('hs_home', $hsHome);
    $ui->assign('hs_ticket', $hsTicket);
    $ui->assign('buy_url', $buyUrl);
    $ui->assign('recover_url', Tenant::url_portal('/recuperer'));
    $ui->assign('show_lookup', !$ticketOk || $posted !== '');
    $lan = wifi_otantik_hs_portal_host($router);
    $hsLogin = 'http://' . $lan . '/login';
    if ($ticketOk && $planKind !== 'PPPOE' && $creds['username'] !== '') {
        $u = (string)$creds['username'];
        $p = (string)($creds['password'] !== '' ? $creds['password'] : $u);
        $hsLogin .= '?username=' . rawurlencode($u) . '&password=' . rawurlencode($p);
    }
    $ui->assign('hs_login_url', $hsLogin);
    exit;
}

function wifi_otantik_hs_lookup()
{
    Tenant::ensure_schema();
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        exit;
    }
    $slug = trim((string)(_get('z') ?: ($GLOBALS['routes'][1] ?? '')));
    if (function_exists('wifi_otantik_portal_file') && wifi_otantik_portal_file($slug)) {
        $slug = wifi_otantik_slug_from_referer();
    }
    $router = $slug !== '' ? wifi_otantik_find_router_by_slug($slug) : null;
    $phone = wifi_otantik_phone(_get('phone') ?: (_post('phone') ?: ''));
    if (!$router) {
        echo json_encode(['ok' => false, 'error' => 'Zone introuvable.']);
        exit;
    }
    if (strlen($phone) < 9) {
        echo json_encode(['ok' => false, 'error' => 'Entrez un numéro camerounais (6XX XXX XXX).']);
        exit;
    }
    $row = wifi_otantik_pay_row_for_phone($phone);
    list($creds, $planKind, $r2, $planName) = wifi_otantik_recover_fill_from_row($row);
    if ($r2 && empty($router['id'])) {
        $router = $r2;
    }
    $rech = wifi_otantik_code_active($creds['username'] ?? '');
    if (!$rech && !empty($creds['username'])) {
        try {
            $rech = ORM::for_table('tbl_user_recharges')->where('username', $creds['username'])->order_by_desc('id')->find_one();
        } catch (Throwable $e) {
        }
    }
    $code = trim((string)($creds['username'] ?? ''));
    $cust = wifi_otantik_hs_find_customer($phone, $router);
    if (!$cust && $code !== '') {
        try {
            $cust = ORM::for_table('tbl_customers')->where('username', $code)->find_one();
        } catch (Throwable $e) {
        }
    }
    $credit = $cust ? wifi_otantik_hs_points($cust) : 0;
    $cheap = wifi_otantik_hs_cheapest_plan($router);
    $canCredit = $cust && $cheap && $credit >= (int)round((float)$cheap['price']) && $planKind !== 'PPPOE';
    $ticketOk = $code !== '' && (bool)$rech && function_exists('wifi_otantik_expiry_info')
        ? empty(wifi_otantik_expiry_info($rech)['expired']) && empty(wifi_otantik_expiry_info($rech)['none'])
        : ($code !== '' && (bool)$row);
    if ($rech && function_exists('wifi_otantik_expiry_info')) {
        $info = wifi_otantik_expiry_info($rech);
        $ticketOk = $code !== '' && empty($info['expired']) && empty($info['none']);
        if ($planName === '') {
            $planName = (string)($info['ticket'] ?? ($rech['namebp'] ?? ''));
        }
    }
    if (!$ticketOk) {
        echo json_encode([
            'ok' => false,
            'credit' => $credit,
            'can_credit' => $canCredit,
            'error' => $code !== '' ? 'Ce ticket n’est plus valide. Achetez un forfait, ou utilisez votre crédit s’il est suffisant.' : 'Aucun code trouvé pour ce numéro.',
        ]);
        exit;
    }
    echo json_encode([
        'ok' => true,
        'code' => $code,
        'plan' => $planName,
        'credit' => $credit,
        'can_credit' => $canCredit,
    ]);
    exit;
}

function wifi_otantik_ros_args($path, $args = [])
{
    $req = new \PEAR2\Net\RouterOS\Request($path);
    foreach ((array)$args as $k => $v) {
        $req->setArgument((string)$k, (string)$v);
    }
    return $req;
}

function wifi_otantik_ros_count($client, $path)
{
    try {
        $n = 0;
        $resp = $client->sendSync(wifi_otantik_ros_args($path, ['.proplist' => '.id']));
        foreach ($resp as $item) {
            // The response stream includes a trailing !done marker with no
            // .id property - counting every iterated item (as this used to,
            // via two branches that both just did $n++) inflated every count
            // by exactly one. Verified live: hotspot showed 6 for 5 real
            // sessions, PPPoE showed 3 for 2. Only count real rows.
            if ($item->getProperty('.id') !== null) {
                $n++;
            }
            if ($n >= 400) {
                break;
            }
        }
        return $n;
    } catch (Throwable $e) {
    }
    return -1;
}

function wifi_otantik_ros_rows($client, $path, $limit = 40, $props = '')
{
    $out = [];
    try {
        $args = [];
        if ($props !== '') {
            $args['.proplist'] = $props;
        }
        $resp = $client->sendSync(wifi_otantik_ros_args($path, $args));
        foreach ($resp as $item) {
            $row = [];
            foreach ($item as $k => $v) {
                $ks = (string)$k;
                if ($ks === '' || $ks[0] === '!') {
                    continue;
                }
                $row[$ks] = is_scalar($v) ? (string)$v : $v;
            }
            if ($row) {
                $out[] = $row;
            }
            if (count($out) >= $limit) {
                break;
            }
        }
        if (!$out) {
            $one = [];
            foreach ($resp as $k => $v) {
                $ks = (string)$k;
                if ($ks !== '' && $ks[0] !== '!') {
                    $one[$ks] = is_scalar($v) ? (string)$v : $v;
                }
            }
            if (!$one) {
                foreach (['uptime', 'cpu-load', 'free-memory', 'total-memory', 'board-name', 'version', 'architecture-name', 'cpu', 'cpu-count', 'cpu-frequency', 'free-hdd-space', 'total-hdd-space', 'platform', 'build-time', 'write-sect-total', 'write-sect-since-reboot', 'cpu-load'] as $k) {
                    $v = $resp->getProperty($k);
                    if ($v !== null && $v !== '') {
                        $one[$k] = (string)$v;
                    }
                }
            }
            if ($one) {
                $out[] = $one;
            }
        }
    } catch (Throwable $e) {
    }
    return $out;
}

function wifi_otantik_bytes($n)
{
    return wifi_otantik_mb($n);
}

function wifi_otantik_mb($n, $decimals = 1)
{
    $n = (float)$n;
    if ($n <= 0) {
        return '0 Mo';
    }
    $mb = $n / 1048576;
    if ($mb < 0.05) {
        return round($n / 1024, 1) . ' Ko';
    }
    if ($mb >= 1024) {
        return round($mb / 1024, 2) . ' Go';
    }
    return round($mb, $mb >= 10 ? 0 : $decimals) . ' Mo';
}

function wifi_otantik_resource_view($res)
{
    $freeMem = (float)($res['free-memory'] ?? 0);
    $totalMem = (float)($res['total-memory'] ?? 0);
    $freeDisk = (float)($res['free-hdd-space'] ?? 0);
    $totalDisk = (float)($res['total-hdd-space'] ?? 0);
    $usedMem = $totalMem > 0 ? max(0, $totalMem - $freeMem) : 0;
    $usedDisk = $totalDisk > 0 ? max(0, $totalDisk - $freeDisk) : 0;
    return [
        'platform' => (string)($res['platform'] ?? ''),
        'board' => (string)($res['board-name'] ?? ''),
        'arch' => (string)($res['architecture-name'] ?? ''),
        'version' => (string)($res['version'] ?? ''),
        'uptime' => (string)($res['uptime'] ?? ''),
        'cpu' => trim((string)($res['cpu-count'] ?? '') . ' × ' . (string)($res['cpu-frequency'] ?? '') . ' MHz'),
        'cpu_load' => (string)($res['cpu-load'] ?? ''),
        'ram' => wifi_otantik_mb($usedMem) . ' / ' . wifi_otantik_mb($totalMem),
        'ram_free' => wifi_otantik_mb($freeMem),
        'disk' => wifi_otantik_mb($usedDisk) . ' / ' . wifi_otantik_mb($totalDisk),
        'disk_free' => wifi_otantik_mb($freeDisk),
    ];
}

function wifi_otantik_session_remaining($username, $routerName)
{
    if ($username === '' || $routerName === '') {
        return '';
    }
    try {
        $rc = ORM::for_table('tbl_user_recharges')
            ->where('username', $username)
            ->where('status', 'on')
            ->where('routers', $routerName)
            ->order_by_desc('id')
            ->find_one();
        if ($rc) {
            $info = wifi_otantik_expiry_info($rc);
            if (empty($info['none'])) {
                return (string)($info['label'] ?? '');
            }
        }
    } catch (Throwable $e) {
    }
    return '';
}

function wifi_otantik_session_view($rows, $userKey, $usageMap = null, $routerName = '')
{
    $out = [];
    foreach ((array)$rows as $r) {
        $user = (string)($r[$userKey] ?? $r['name'] ?? '');
        $rxB = $r['bytes-in'] ?? null;
        $txB = $r['bytes-out'] ?? null;
        // /ppp/active/print has no bytes-in/bytes-out property at all (verified
        // live - RouterOS only exposes limit-bytes-in/out there, both usually
        // 0). Real PPPoE traffic lives on the dynamic <pppoe-USERNAME>
        // interface instead; $usageMap carries that, keyed by username.
        if (($rxB === null || $txB === null) && $usageMap !== null && isset($usageMap[$user])) {
            $rxB = $usageMap[$user]['rx'] ?? $rxB;
            $txB = $usageMap[$user]['tx'] ?? $txB;
        }
        $out[] = [
            'user' => $user,
            'address' => (string)($r['address'] ?? ''),
            'uptime' => (string)($r['uptime'] ?? ''),
            'mac' => (string)($r['mac-address'] ?? $r['caller-id'] ?? ''),
            'rx' => wifi_otantik_mb($rxB ?? 0),
            'tx' => wifi_otantik_mb($txB ?? 0),
            'remaining' => wifi_otantik_session_remaining($user, $routerName),
        ];
    }
    return $out;
}

/**
 * PPPoE traffic by username, read from the dynamic <pppoe-USERNAME> server
 * interface RouterOS creates per active session - /ppp/active/print itself
 * carries no traffic counters. $rows is the raw /ppp/active/print result.
 */
function wifi_otantik_pppoe_iface_usage($client, $rows)
{
    $names = [];
    foreach ((array)$rows as $r) {
        $n = (string)($r['name'] ?? '');
        if ($n !== '') {
            $names[$n] = true;
        }
    }
    if (!$names) {
        return [];
    }
    $map = [];
    try {
        $ifaces = wifi_otantik_ros_rows($client, '/interface/print', 200, 'name,type,rx-byte,tx-byte');
        foreach ($ifaces as $i) {
            $iname = (string)($i['name'] ?? '');
            if (preg_match('/^<pppoe-(.+)>$/', $iname, $m) && isset($names[$m[1]])) {
                $map[$m[1]] = ['rx' => $i['rx-byte'] ?? 0, 'tx' => $i['tx-byte'] ?? 0];
            }
        }
    } catch (Throwable $e) {
    }
    return $map;
}

function wifi_otantik_traffic_view($client)
{
    $out = [];
    foreach (wifi_otantik_ros_rows($client, '/interface/print', 24, 'name,running,type,rx-byte,tx-byte') as $i) {
        $name = (string)($i['name'] ?? '');
        if ($name === '' || strpos($name, 'loopback') !== false) {
            continue;
        }
        $rx = (float)($i['rx-byte'] ?? 0);
        $tx = (float)($i['tx-byte'] ?? 0);
        $out[] = [
            'name' => $name,
            'running' => (string)($i['running'] ?? ''),
            'type' => (string)($i['type'] ?? ''),
            'rx' => wifi_otantik_mb($rx),
            'tx' => wifi_otantik_mb($tx),
            'rx_b' => $rx,
            'tx_b' => $tx,
        ];
    }
    return $out;
}

function wifi_otantik_logs_view($client)
{
    $rows = wifi_otantik_ros_rows($client, '/log/print', 40, 'time,topics,message');
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'time' => (string)($r['time'] ?? ''),
            'topics' => (string)($r['topics'] ?? ''),
            'message' => (string)($r['message'] ?? ''),
        ];
    }
    return array_reverse($out);
}

function wifi_otantik_health_pick($rows)
{
    $temp = '';
    $volt = '';
    foreach ($rows as $r) {
        $name = strtolower((string)($r['name'] ?? ''));
        $val = (string)($r['value'] ?? '');
        if ($name === 'temperature' || $name === 'cpu-temperature' || isset($r['temperature'])) {
            $temp = $val !== '' ? $val : (string)($r['temperature'] ?? '');
        }
        if ($name === 'voltage' || isset($r['voltage'])) {
            $volt = $val !== '' ? $val : (string)($r['voltage'] ?? '');
        }
    }
    if ($temp !== '' && strpos($temp, '°') === false && is_numeric($temp)) {
        $temp .= ' °C';
    }
    if ($volt !== '' && strpos($volt, 'V') === false && is_numeric($volt)) {
        $volt .= ' V';
    }
    return [$temp, $volt];
}

function wifi_otantik_hs_plans()
{
    Tenant::ensure_schema();
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        exit;
    }
    $slug = trim((string)(_get('z') ?: ($GLOBALS['routes'][1] ?? '')));
    if (function_exists('wifi_otantik_portal_file') && wifi_otantik_portal_file($slug)) {
        $slug = wifi_otantik_slug_from_referer();
    }
    $router = $slug !== '' ? wifi_otantik_find_router_by_slug($slug) : null;
    if (!$router) {
        echo json_encode(['ok' => false, 'html' => '']);
        exit;
    }
    echo json_encode([
        'ok' => true,
        'html' => OtantikCaptive::plans_markup($router),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function wifi_otantik_hs()
{
    Tenant::ensure_schema();
    $slug = trim((string)(_get('z') ?: ($GLOBALS['routes'][1] ?? '')));
    $file = trim((string)(_get('f') ?: ($GLOBALS['routes'][2] ?? '')));
    if (wifi_otantik_portal_file($slug)) {
        if ($file === '' || $file === 'login.html' || wifi_otantik_portal_file($file)) {
            $file = strtolower(basename($slug));
        }
        $slug = wifi_otantik_slug_from_referer();
    }
    if ($file === '') {
        $file = 'login.html';
    }
    if ($slug === '') {
        $slug = wifi_otantik_slug_from_path((string)($_SERVER['REQUEST_URI'] ?? ''));
    }
    if ($slug === '') {
        wifi_otantik_portal_bounce($file);
        return;
    }
    $router = wifi_otantik_find_router_by_slug($slug);
    if (!$router) {
        wifi_otantik_portal_bounce($file);
        return;
    }
    wifi_otantik_remember_zone((string)$router['slug']);
    // Was: preview also turned on whenever the request's Host header was
    // any *.otantik.cm subdomain ($onName). Since the whole platform - the
    // real customer-facing hotspot file endpoint included - lives on
    // *.otantik.cm, this made ordinary customer traffic render as an admin
    // preview: hosted_vars() then stripped every $(...) RouterOS template
    // variable to '' (including $(mac-esc) on the buy link, which is why
    // post-payment auto-login could never find a MAC) and injected the
    // 'bleu-lune' demo ticket code into the login form. Drive preview only
    // from the explicit ?preview=1 the admin dashboard iframe passes.
    OtantikCaptive::serve($router, $file, _get('preview') === '1');
}

function wifi_otantik_hs_contact_reply($ok, $error = '')
{
    $ajax = ((string)_post('ajax') === '1') || ((string)_get('ajax') === '1');
    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
    $wantJson = $ajax || (strpos($accept, 'application/json') !== false && strpos($accept, 'text/html') === false);
    if ($wantJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    $title = $ok ? 'Message envoyé' : 'Envoi impossible';
    $text = $ok
        ? 'Nous avons bien reçu votre demande. Nous vous répondrons par appel ou WhatsApp.'
        : ($error !== '' ? $error : 'Le message n’a pas pu partir.');
    $h = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $p = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'
        . $h . '</title></head><body style="font-family:sans-serif;background:#002858;color:#fff;padding:24px;text-align:center">'
        . '<div style="max-width:420px;margin:40px auto;background:#fff;color:#002858;border-radius:16px;padding:24px">'
        . '<h1 style="font-size:22px;margin:0 0 8px">' . $h . '</h1>'
        . '<p style="color:#64748b;line-height:1.45">' . $p . '</p>'
        . '<p><a href="javascript:history.back()" style="color:#f05018;font-weight:700">Retour au contact</a></p>'
        . '</div></body></html>';
    exit;
}

function wifi_otantik_hs_contact()
{
    Tenant::ensure_schema();
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        exit;
    }
    $slug = trim((string)(_get('z') ?: ($GLOBALS['routes'][1] ?? '')));
    if (function_exists('wifi_otantik_portal_file') && wifi_otantik_portal_file($slug)) {
        $slug = wifi_otantik_slug_from_referer();
    }
    $router = $slug !== '' ? wifi_otantik_find_router_by_slug($slug) : null;
    if (!$router) {
        wifi_otantik_hs_contact_reply(false, 'Zone introuvable');
    }
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0');
    $bucket = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wo-tg-' . md5($ip . '|' . $slug);
    $n = 0;
    $now = time();
    if (is_file($bucket)) {
        $n = (int)file_get_contents($bucket);
        if ($now - filemtime($bucket) > 3600) {
            $n = 0;
        }
    }
    if ($n >= 8) {
        wifi_otantik_hs_contact_reply(false, 'Trop de messages. Réessayez plus tard.');
    }
    $name = trim((string)(_post('name') ?: ''));
    $phone = trim((string)(_post('phone') ?: (_post('email') ?: '')));
    $message = trim((string)(_post('message') ?: (_post('mesage') ?: '')));
    if (strlen($message) < 3) {
        wifi_otantik_hs_contact_reply(false, 'Écrivez un court message.');
    }
    $cfg = OtantikCaptive::cfg($router);
    $text = "Wifi-Otantik — " . $cfg['title'] . "\n"
        . "Nom : " . ($name !== '' ? $name : '—') . "\n"
        . "Tél : " . ($phone !== '' ? $phone : '—') . "\n"
        . "Message : " . $message;
    @file_put_contents($bucket, (string)($n + 1));
    try {
        $row = ORM::for_table('tbl_otantik_inbox')->create();
        $row->owner_id = (int)($router['owner_id'] ?? 0);
        $row->router_id = (int)($router['id'] ?? 0);
        $row->router_name = (string)($router['name'] ?? '');
        $row->name = mb_substr($name, 0, 128);
        $row->phone = mb_substr($phone, 0, 64);
        $row->message = mb_substr($message, 0, 2000);
        $row->seen = 0;
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
    } catch (Throwable $e) {
        wifi_otantik_hs_contact_reply(false, 'Impossible d’enregistrer le message.');
    }
    if ($cfg['tg']) {
        OtantikCaptive::telegram($cfg['tg_token'], $cfg['tg_chat'], $text);
    }
    if (class_exists('OtantikWhatsApp')) {
        try {
            OtantikWhatsApp::notify_contact($phone, $router);
        } catch (Throwable $e) {
        }
    }
    wifi_otantik_hs_contact_reply(true);
}

function wifi_otantik_hs_session_payload($router, $user, $mac = '', $bindMac = true)
{
    $user = trim((string)$user);
    if ($user === '' || strlen($user) > 64) {
        return ['ok' => false];
    }
    $mac = $mac !== '' ? wifi_otantik_mac_norm($mac) : '';
    $row = ORM::for_table('tbl_user_recharges')->where('username', $user)->order_by_desc('id')->find_one();
    $info = wifi_otantik_expiry_info($row);
    $price = 0;
    $planName = (string)($info['ticket'] ?? '');
    $plan = null;
    if ($row) {
        if ((int)($row['plan_id'] ?? 0) > 0) {
            $plan = ORM::for_table('tbl_plans')->find_one((int)$row['plan_id']);
        }
        if (!$plan && $planName !== '') {
            $plan = ORM::for_table('tbl_plans')->where('name_plan', $planName)->find_one();
        }
        if ($plan) {
            $price = (int)round((float)$plan['price']);
            if ($planName === '') {
                $planName = (string)$plan['name_plan'];
            }
        }
        if ($bindMac && $plan && $mac !== '' && stripos($user, 'T-') !== 0 && empty($info['expired']) && empty($info['none'])
            && wifi_otantik_plan_kind($plan) !== 'PPPOE'
        ) {
            $already = ORM::for_table('tbl_otantik_mac_bypass')
                ->where('router_id', (int)$router['id'])
                ->where('mac', $mac)
                ->where('username', $user)
                ->where('status', 'on')
                ->find_one();
            if (!$already) {
                wifi_otantik_mac_bypass_try($router, $plan, $user, $mac);
            }
        }
    }
    if (stripos($user, 'T-') === 0) {
        $price = 0;
        if ($planName === '' || $planName === '—') {
            $planName = 'Essai gratuit';
        }
    }
    $leftPct = (int)($info['pct'] ?? 0);
    $usedPct = empty($info['expired']) && empty($info['none']) ? max(0, min(100, 100 - $leftPct)) : ($info['expired'] ? 100 : 0);
    $exp = '—';
    if ($row && trim((string)$row['expiration']) !== '') {
        $exp = trim((string)$row['expiration'] . ' ' . (string)($row['time'] ?? ''));
    }
    return [
        'ok' => true,
        'user' => $user,
        'plan' => $planName !== '' ? $planName : '—',
        'remaining' => $info['label'],
        'expires' => $exp,
        'pct' => $usedPct,
        'left_pct' => $leftPct,
        'left' => (int)($info['left'] ?? 0),
        'price' => $price,
        'price_label' => $price < 1 ? '0 F' : (number_format($price, 0, ',', ' ') . ' F'),
        'active' => empty($info['expired']) && empty($info['none']),
        'spent' => ($price < 1 ? '0 F' : (number_format($price, 0, ',', ' ') . ' F')),
    ];
}

function wifi_otantik_hs_session()
{
    Tenant::ensure_schema();
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $slug = trim((string)(_get('z') ?: ($GLOBALS['routes'][1] ?? '')));
    if (function_exists('wifi_otantik_portal_file') && wifi_otantik_portal_file($slug)) {
        $slug = wifi_otantik_slug_from_referer();
    }
    $router = $slug !== '' ? wifi_otantik_find_router_by_slug($slug) : null;
    $user = trim((string)(_get('u') ?: ''));
    if (!$router || $user === '' || strlen($user) > 64) {
        echo json_encode(['ok' => false]);
        exit;
    }
    echo json_encode(
        wifi_otantik_hs_session_payload($router, $user, _get('mac') ?: '', true),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function wifi_otantik_push_walled_garden($client, $router = null)
{
    $added = 0;
    $haveHost = [];
    try {
        $print = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/walled-garden/print');
        foreach ($client->sendSync($print) as $row) {
            $h = strtolower(trim((string)$row->getProperty('dst-host')));
            if ($h !== '') {
                $haveHost[$h] = true;
            }
        }
    } catch (Throwable $e) {
    }
    foreach (wifi_otantik_walled_hosts() as $h) {
        if (isset($haveHost[strtolower($h)])) {
            continue;
        }
        try {
            $add = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/walled-garden/add');
            $add->setArgument('dst-host', $h);
            $add->setArgument('action', 'allow');
            $add->setArgument('comment', 'wifi-otantik');
            $client->sendSync($add);
            $added++;
        } catch (Throwable $e) {
        }
    }
    $haveIp = [];
    try {
        $print = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/walled-garden/ip/print');
        foreach ($client->sendSync($print) as $row) {
            $ip = trim((string)$row->getProperty('dst-address'));
            if ($ip !== '') {
                $haveIp[$ip] = true;
            }
        }
    } catch (Throwable $e) {
    }
    foreach (wifi_otantik_walled_ips() as $ip) {
        if (isset($haveIp[$ip])) {
            continue;
        }
        try {
            $add = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/walled-garden/ip/add');
            $add->setArgument('dst-address', $ip);
            $add->setArgument('action', 'accept');
            $add->setArgument('comment', 'wifi-otantik');
            $client->sendSync($add);
            $added++;
        } catch (Throwable $e) {
        }
    }
    if ($router) {
        wifi_otantik_mk_log($router, 'walled_garden', '', 1, $added > 0 ? ($added . ' entrée(s) ajoutée(s)') : 'Déjà à jour');
    }
}

function wifi_otantik_push_captive($router)
{
    try {
        $client = wifi_otantik_mk_client($router, 20);
        $slug = rawurlencode((string)$router['slug']);
        $n = 0;
        foreach (OtantikCaptive::files() as $f) {
            $req = new \PEAR2\Net\RouterOS\Request('/tool/fetch');
            $req->setArgument('url', 'https://' . wifi_otantik_public_host() . '/hotspot/' . $slug . '/' . $f);
            $req->setArgument('mode', 'https');
            try {
                $req->setArgument('check-certificate', 'no');
            } catch (Throwable $e) {
            }
            $req->setArgument('dst-path', 'hotspot/' . $f);
            $client->sendSync($req);
            $n++;
            usleep(200000);
        }
        wifi_otantik_push_walled_garden($client, $router);
        // Re-apply trial flags: older captive installs wiped login-by=…,trial.
        try {
            wifi_otantik_push_trial($router);
        } catch (Throwable $e) {
        }
        wifi_otantik_mk_log($router, 'push_captive', (string)$router['slug'], 1, $n . ' fichier(s) envoyé(s)');
        return true;
    } catch (Throwable $e) {
        wifi_otantik_mk_log($router, 'push_captive', (string)($router['slug'] ?? ''), 0, $e->getMessage());
        throw $e;
    }
}

function wifi_otantik_captive()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('network', $admin);
    Tenant::ensure_schema();
    $routers = Tenant::routers_query($admin)->find_many();
    $id = (int)($routes[2] ?? 0);
    if ($id < 1 && $routers) {
        $id = (int)$routers[0]['id'];
    }
    $router = $id ? Tenant::require_router($id, $admin) : null;
    if ($router && !empty($_POST)) {
        $back = getUrl('plugin/wifi_otantik_captive/') . $router['id'];
        $router->portal_title = mb_substr(trim((string)_post('portal_title')), 0, 128);
        $router->portal_welcome = mb_substr(trim((string)_post('portal_welcome')), 0, 512);
        $router->portal_buy_label = mb_substr(trim((string)_post('portal_buy_label')), 0, 64);
        $router->portal_phone = mb_substr(trim((string)_post('portal_phone')), 0, 64);
        $router->portal_whatsapp = mb_substr(trim((string)_post('portal_whatsapp')), 0, 64);
        $router->portal_email = mb_substr(trim((string)_post('portal_email')), 0, 128);
        $defColors = OtantikCaptive::default_colors();
        if (_post('portal_reset_colors')) {
            $router->portal_color = '';
            $router->portal_navy = '';
        } else {
            $c = OtantikCaptive::hex((string)_post('portal_color'), $defColors['accent']);
            $n = OtantikCaptive::hex((string)_post('portal_navy'), $defColors['navy']);
            $router->portal_color = ($c === $defColors['accent']) ? '' : $c;
            $router->portal_navy = ($n === $defColors['navy']) ? '' : $n;
        }
        $router->tg_chat_id = mb_substr(trim((string)_post('tg_chat_id')), 0, 64);
        $token = trim((string)_post('tg_bot_token'));
        if ($token !== '') {
            $router->tg_bot_token = mb_substr($token, 0, 128);
        }
        if (_post('clear_tg')) {
            $router->tg_bot_token = '';
            $router->tg_chat_id = '';
        }
        $slides = [];
        for ($i = 1; $i <= 3; $i++) {
            $slides[] = [
                'title' => mb_substr(trim((string)_post('slide_title_' . $i)), 0, 64),
                'text' => mb_substr(trim((string)_post('slide_text_' . $i)), 0, 128),
            ];
        }
        $router->portal_slides = json_encode($slides, JSON_UNESCAPED_UNICODE);
        $router->portal_help_kicker = mb_substr(trim((string)_post('portal_help_kicker')), 0, 64);
        $router->portal_help_title = mb_substr(trim((string)_post('portal_help_title')), 0, 64);
        $router->portal_help_text = mb_substr(trim((string)_post('portal_help_text')), 0, 512);
        $router->portal_help_btn = mb_substr(trim((string)_post('portal_help_btn')), 0, 64);
        $router->portal_web = mb_substr(trim((string)_post('portal_web')), 0, 255);
        $router->portal_facebook = mb_substr(trim((string)_post('portal_facebook')), 0, 255);
        $router->portal_contact_intro = mb_substr(trim((string)_post('portal_contact_intro')), 0, 255);
        $router->portal_wa_text = mb_substr(trim((string)_post('portal_wa_text')), 0, 255);
        $router->portal_wa_on = _post('portal_wa_on') ? 1 : 0;
        $router->portal_services_on = _post('portal_services_on') ? 1 : 0;
        $router->portal_ref_on = _post('portal_ref_on') ? 1 : 0;
        $router->portal_ref_points = max(1, min(10000, (int)_post('portal_ref_points')));
        $router->portal_ref_bundle_pts = max(1, min(100000, (int)_post('portal_ref_bundle_pts')));
        $router->portal_ref_help = mb_substr(trim((string)_post('portal_ref_help')), 0, 2000);
        $router->portal_ref_rules = mb_substr(trim((string)_post('portal_ref_rules')), 0, 2000);
        $router->portal_ref_discount = 0;
        $router->portal_ref_reward = (int)$router->portal_ref_points;
        $sv = [];
        for ($i = 1; $i <= 4; $i++) {
            $sv[] = [
                'title' => mb_substr(trim((string)_post('svc_title_' . $i)), 0, 64),
                'text' => mb_substr(trim((string)_post('svc_text_' . $i)), 0, 255),
            ];
        }
        $router->portal_services = json_encode($sv, JSON_UNESCAPED_UNICODE);
        $logoErr = '';
        $uploaded = !empty($_FILES['portal_logo']['tmp_name'])
            && (int)($_FILES['portal_logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
        if ($uploaded) {
            $logoErr = wifi_otantik_save_portal_logo($router, $_FILES['portal_logo']);
        } elseif (_post('clear_logo')) {
            wifi_otantik_clear_portal_logo($router);
        }
        if (!empty($_FILES['portal_bundle']['tmp_name']) && (int)($_FILES['portal_bundle']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $e = wifi_otantik_save_portal_pic($router, $_FILES['portal_bundle'], 'bundle');
            if ($e !== '') {
                $logoErr = $logoErr !== '' ? $logoErr : $e;
            }
        } elseif (_post('clear_bundle')) {
            wifi_otantik_clear_portal_pic($router, 'bundle');
        }
        for ($i = 1; $i <= 3; $i++) {
            $key = 'slide_img_' . $i;
            if (!empty($_FILES[$key]['tmp_name']) && (int)($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $e = wifi_otantik_save_portal_pic($router, $_FILES[$key], 'slide-' . $i);
                if ($e !== '') {
                    $logoErr = $logoErr !== '' ? $logoErr : $e;
                }
            } elseif (_post('clear_slide_' . $i)) {
                wifi_otantik_clear_portal_pic($router, 'slide-' . $i);
            }
        }
        $audioMode = trim((string)_post('portal_audio_mode'));
        $router->portal_audio_mode = in_array($audioMode, ['off', 'tts', 'upload'], true) ? $audioMode : 'off';
        $router->portal_audio_text = mb_substr(trim((string)_post('portal_audio_text')), 0, 1500);
        if (!empty($_FILES['portal_audio']['tmp_name']) && (int)($_FILES['portal_audio']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $e = wifi_otantik_save_portal_audio($router, $_FILES['portal_audio']);
            if ($e !== '') {
                $logoErr = $logoErr !== '' ? $logoErr : $e;
            }
        } elseif (_post('clear_audio')) {
            wifi_otantik_clear_portal_audio($router);
        }
        $router->save();
        if (_post('test_tg')) {
            list($ok, $msg) = OtantikCaptive::telegram(
                (string)$router['tg_bot_token'],
                (string)$router['tg_chat_id'],
                'Wifi-Otantik : test portail captif « ' . $router['name'] . ' ».'
            );
            r2($back, $ok ? 's' : 'e', $ok ? 'Réglages enregistrés. Message Telegram envoyé.' : ('Enregistré, mais ' . $msg));
        }
        $ajax = (string)_post('ajax') === '1';
        $reply = function ($ok, $msg) use ($ajax, $back) {
            if ($ajax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => (bool)$ok, 'message' => (string)$msg], JSON_UNESCAPED_UNICODE);
                exit;
            }
            r2($back, $ok ? 's' : 'e', $msg);
        };
        $note = 'Portail captif enregistré.';
        if ($logoErr !== '') {
            $reply(false, 'Enregistré, mais un fichier : ' . $logoErr);
        }
        if (_post('save_push')) {
            try {
                @set_time_limit(180);
                wifi_otantik_push_captive($router);
                $note = 'Enregistré et envoyé au MikroTik.';
            } catch (Throwable $e) {
                $reply(false, 'Enregistré, mais le MikroTik est injoignable. Recollez le script de connexion ou réessayez une fois le tunnel en ligne.');
            }
        }
        $reply(true, $note);
    }
    $cfg = $router ? OtantikCaptive::cfg($router) : [];
    if ($cfg) {
        $ref = wifi_otantik_hs_ref_cfg($router);
        $cfg['ref_points'] = $ref['points'];
        $cfg['ref_bundle_pts'] = $ref['bundle_pts'];
        $cfg['ref_help'] = $ref['help'];
        $cfg['ref_rules'] = $ref['rules'];
        $cfg['ref_on'] = $ref['on'];
    }
    wifi_otantik_ui('plugin/otantik_captive.tpl', 'Portail captif', [
        'routers' => $routers,
        'router' => $router,
        'cfg' => $cfg,
        'preview_url' => $router ? Tenant::url_business('/hotspot/' . rawurlencode($router['slug']) . '/login.html?preview=1') : '',
        'logo_url' => $router ? wifi_otantik_portal_logo_preview($router) : '',
        'bundle_url' => $router ? OtantikCaptive::pic_preview_url($router, 'bundle') : '',
        'bundle_custom' => $router ? OtantikCaptive::has_custom_pic($router, 'bundle') : false,
        'slide_urls' => $router ? [
            1 => OtantikCaptive::pic_preview_url($router, 'slide-1'),
            2 => OtantikCaptive::pic_preview_url($router, 'slide-2'),
            3 => OtantikCaptive::pic_preview_url($router, 'slide-3'),
        ] : [],
        'slide_custom' => $router ? [
            1 => OtantikCaptive::has_custom_pic($router, 'slide-1'),
            2 => OtantikCaptive::has_custom_pic($router, 'slide-2'),
            3 => OtantikCaptive::has_custom_pic($router, 'slide-3'),
        ] : [],
        'push_files' => OtantikCaptive::files(),
        'push_files_json' => json_encode(array_values(OtantikCaptive::files()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'audio_url' => $router ? OtantikCaptive::audio_public_url($router) : '',
        'audio_has_file' => $router ? OtantikCaptive::has_custom_audio($router) : false,
    ]);
}

function wifi_otantik_inbox()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('network', $admin);
    Tenant::ensure_schema();
    $oid = Tenant::id($admin);
    $id = (int)($routes[2] ?? 0);
    if ($id > 0) {
        $row = ORM::for_table('tbl_otantik_inbox')->where('owner_id', $oid)->find_one($id);
        if ($row) {
            $row->seen = 1;
            $row->save();
        }
        r2(getUrl('plugin/wifi_otantik_inbox'), 's', 'Message marqué comme lu.');
    }
    if (($routes[2] ?? '') === 'mark_all' || _get('mark_all') === '1' || _post('mark_all_read')) {
        try {
            ORM::raw_execute(
                'UPDATE tbl_otantik_inbox SET seen = 1 WHERE owner_id = ? AND seen = 0',
                [$oid]
            );
        } catch (Throwable $e) {
            foreach (ORM::for_table('tbl_otantik_inbox')->where('owner_id', $oid)->where('seen', 0)->find_many() as $row) {
                $row->seen = 1;
                $row->save();
            }
        }
        r2(getUrl('plugin/wifi_otantik_inbox'), 's', 'Tous les messages sont marqués comme lus.');
    }
    if (_post('clear_inbox')) {
        ORM::for_table('tbl_otantik_inbox')->where('owner_id', $oid)->delete_many();
        r2(getUrl('plugin/wifi_otantik_inbox'), 's', 'Boîte vidée.');
    }
    $qStr = wifi_otantik_q();
    $makeIn = function () use ($oid, $qStr) {
        $q = ORM::for_table('tbl_otantik_inbox')->where('owner_id', $oid);
        wifi_otantik_where_search($q, ['router_name', 'name', 'phone', 'message'], $qStr);
        return $q;
    };
    $pager = wifi_otantik_pager((int)$makeIn()->count());
    $rows = wifi_otantik_paged($makeIn()->order_by_desc('id'), $pager)->find_many();
    wifi_otantik_ui('plugin/otantik_inbox.tpl', 'Messages du portail', [
        'rows' => $rows,
        'filter_q' => $qStr,
        'pager' => $pager,
    ]);
}

function wifi_otantik_mk_logs()
{
    global $admin;
    _admin();
    Tenant::require_can('network', $admin);
    Tenant::ensure_schema();
    $qStr = wifi_otantik_q();
    $total = 0;
    try {
        $total = (int)wifi_otantik_mk_logs_query($admin, $qStr)->count();
    } catch (Throwable $e) {
    }
    $pager = wifi_otantik_pager($total);
    wifi_otantik_ui('plugin/otantik_mk_logs.tpl', 'Journal MikroTik', [
        'rows' => wifi_otantik_mk_logs_rows($admin, $pager['per'], $pager['offset'], $qStr),
        'filter_q' => $qStr,
        'pager' => $pager,
    ]);
}

function wifi_otantik_map()
{
    global $admin;
    _admin();
    Tenant::require_can('network', $admin);
    $data = wifi_otantik_map_points($admin);
    $placed = 0;
    foreach (array_merge($data['zones'], $data['clients']) as $p) {
        if ($p['lat'] !== null && $p['lng'] !== null) {
            $placed++;
        }
    }
    wifi_otantik_ui('plugin/otantik_map.tpl', Tenant::is_platform($admin) ? 'Carte réseau' : 'Carte', [
        'points_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
        'zone_count' => count($data['zones']),
        'pppoe_count' => count($data['clients']),
        'placed_count' => $placed,
        'focus_json' => json_encode(trim((string)_get('focus'))),
        'is_platform_map' => Tenant::is_platform($admin),
    ]);
}

/**
 * Reconciliation: find paid sales (tbl_payment_gateway status=2) that never
 * got a tbl_user_recharges row - the exact failure mode confirmed live on
 * 2026-10-03 (Otantik Hub / "fleur-joli") where the router push threw mid
 * way through wifi_otantik_pay_apply_mikrotik() and silently skipped both
 * the recharge record and the WhatsApp ticket, leaving a paid customer with
 * nothing. A missing recharge row is a reliable signal on its own because
 * push + record_recharge are sequential in one try block there - if the
 * push throws, the recharge is never created either.
 */
function wifi_otantik_reconcile_gaps_query($admin, $days = 14)
{
    $since = date('Y-m-d H:i:s', strtotime('-' . max(1, (int)$days) . ' days'));
    $q = ORM::for_table('tbl_payment_gateway')
        ->where('status', 2)
        ->where_gte('paid_date', $since)
        ->where_not_like('pg_url_payment', 'reseller-topup:%')
        ->where_raw('NOT EXISTS (SELECT 1 FROM tbl_user_recharges WHERE tbl_user_recharges.trx_id = tbl_payment_gateway.id)');
    if (!Tenant::is_platform($admin)) {
        $names = Tenant::router_names($admin) ?: ['__none__'];
        $q->where_in('routers', $names);
    }
    return $q->order_by_desc('id');
}

function wifi_otantik_reconcile_reprovision($trxId)
{
    $trx = ORM::for_table('tbl_payment_gateway')->find_one($trxId);
    if (!$trx || (int)$trx['status'] !== 2) {
        return 'Transaction introuvable ou non payée';
    }
    $already = ORM::for_table('tbl_user_recharges')->where('trx_id', $trxId)->find_one();
    if ($already) {
        return true;
    }
    $router = ORM::for_table('tbl_routers')->find_one((int)$trx['routers_id']);
    if (!$router && !empty($trx['routers'])) {
        $router = ORM::for_table('tbl_routers')->where('name', $trx['routers'])->find_one();
    }
    $plan = ORM::for_table('tbl_plans')->find_one((int)$trx['plan_id']);
    if (!$router || !$plan) {
        return 'Zone ou tarif introuvable pour cette vente';
    }
    $meta = wifi_otantik_pay_meta($trx);
    $cust = (int)$trx['user_id'] > 0 ? ORM::for_table('tbl_customers')->find_one((int)$trx['user_id']) : null;
    $kind = $meta['kind'] !== '' ? $meta['kind'] : wifi_otantik_plan_kind($plan);
    $user = $meta['user'] !== '' ? $meta['user'] : ((string)($cust['username'] ?? ''));
    $pass = $meta['pass'] !== '' ? $meta['pass'] : ((string)($cust['password'] ?? $user));
    if ($user === '') {
        return 'Identifiant client introuvable pour cette vente';
    }
    wifi_otantik_pay_apply_mikrotik($router, $plan, $user, $pass, $kind, $cust, $trx, (int)$trx['price']);
    $now = ORM::for_table('tbl_user_recharges')->where('trx_id', $trxId)->find_one();
    return $now ? true : 'Le routeur n’a pas répondu - réessayez dans un instant';
}

function wifi_otantik_reconcile()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('network', $admin);
    $act = (string)($routes[2] ?? '');

    if ($act === 'reprovision' && (int)($routes[3] ?? 0) > 0) {
        if (empty($_POST)) {
            r2(getUrl('plugin/wifi_otantik_reconcile'), 'e', 'Confirmation requise');
        }
        $trxId = (int)$routes[3];
        $trx = ORM::for_table('tbl_payment_gateway')->find_one($trxId);
        if ($trx && !Tenant::is_platform($admin) && !in_array((string)$trx['routers'], Tenant::router_names($admin), true)) {
            r2(getUrl('plugin/wifi_otantik_reconcile'), 'e', 'Vente introuvable');
        }
        $res = wifi_otantik_reconcile_reprovision($trxId);
        r2(getUrl('plugin/wifi_otantik_reconcile'), $res === true ? 's' : 'e', $res === true ? 'Ticket créé et poussé sur le routeur' : (string)$res);
    }

    if ($act === 'check_router' && (int)($routes[3] ?? 0) > 0) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        @set_time_limit(12);
        $rid = (int)$routes[3];
        $router = ORM::for_table('tbl_routers')->find_one($rid);
        if (!$router || (!Tenant::is_platform($admin) && !Tenant::owns_router($router, $admin))) {
            echo json_encode(['ok' => false, 'error' => 'Zone introuvable']);
            exit;
        }
        $missing = [];
        $checked = 0;
        try {
            $client = wifi_otantik_mk_client($router, 5, true);
            $hsUsers = [];
            foreach (wifi_otantik_ros_rows($client, '/ip/hotspot/user/print', 2000, 'name') as $row) {
                $hsUsers[(string)$row['name']] = true;
            }
            $pppUsers = [];
            foreach (wifi_otantik_ros_rows($client, '/ppp/secret/print', 2000, 'name') as $row) {
                $pppUsers[(string)$row['name']] = true;
            }
            $active = ORM::for_table('tbl_user_recharges')
                ->where('routers', (string)$router['name'])
                ->where('status', 'on')
                ->where_gte('expiration', date('Y-m-d'))
                ->order_by_desc('id')
                ->limit(300)
                ->find_many();
            foreach ($active as $r) {
                $checked++;
                $isPppoe = strtoupper((string)$r['type']) === 'PPPOE';
                $present = $isPppoe ? isset($pppUsers[(string)$r['username']]) : isset($hsUsers[(string)$r['username']]);
                if (!$present) {
                    $missing[] = [
                        'recharge_id' => (int)$r['id'],
                        'trx_id' => (int)$r['trx_id'],
                        'username' => (string)$r['username'],
                        'plan' => (string)$r['namebp'],
                        'expiration' => (string)$r['expiration'],
                        'kind' => $isPppoe ? 'PPPoE' : 'Hotspot',
                    ];
                }
            }
            echo json_encode(['ok' => true, 'checked' => $checked, 'missing' => $missing]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'error' => 'Routeur injoignable : ' . $e->getMessage()]);
        }
        exit;
    }

    $gaps = [];
    $gapTotal = 0.0;
    foreach (wifi_otantik_reconcile_gaps_query($admin)->limit(200)->find_many() as $t) {
        $gapTotal += (float)$t['price'];
        $gaps[] = [
            'id' => (int)$t['id'],
            'username' => (string)$t['username'],
            'price' => (float)$t['price'],
            'plan_name' => (string)$t['plan_name'],
            'routers' => (string)$t['routers'],
            'payment_method' => (string)$t['payment_method'],
            'paid_date' => (string)$t['paid_date'],
        ];
    }
    $routers = Tenant::routers_query($admin)->find_many();

    $logQ = ORM::for_table('tbl_otantik_reconcile_logs')->order_by_desc('id')->limit(30);
    if (!Tenant::is_platform($admin)) {
        $logQ->where('owner_id', Tenant::id($admin));
    }
    $logs = [];
    foreach ($logQ->find_many() as $l) {
        $t = ORM::for_table('tbl_payment_gateway')->find_one((int)$l['trx_id']);
        $logs[] = [
            'created_at' => (string)$l['created_at'],
            'ok' => (int)$l['ok'] === 1,
            'action' => (string)$l['action'],
            'message' => (string)$l['message'],
            'username' => $t ? (string)$t['username'] : ('#' . (int)$l['trx_id']),
        ];
    }

    wifi_otantik_ui('plugin/otantik_reconcile.tpl', 'Réconciliation', [
        'gaps' => $gaps,
        'gap_total' => $gapTotal,
        'routers' => $routers,
        'logs' => $logs,
    ]);
}

function wifi_otantik_monitor()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('network', $admin);
    $act = (string)($routes[2] ?? '');
    if ($act === 'live') {
        wifi_otantik_monitor_live((int)($routes[3] ?? 0));
        return;
    }
    $routers = Tenant::routers_query($admin)->find_many();
    $states = [];
    foreach ($routers as $r) {
        $states[(int)$r['id']] = wifi_otantik_router_link($r);
    }
    $id = (int)$act;
    if ($id < 1 && $routers) {
        $id = (int)$routers[0]['id'];
        foreach ($routers as $r) {
            if (empty($states[(int)$r['id']]['offline'])) {
                $id = (int)$r['id'];
                break;
            }
        }
    }
    $router = $id ? Tenant::require_router($id, $admin) : null;
    $link = $router ? ($states[(int)$router['id']] ?? wifi_otantik_router_link($router)) : ['code' => '', 'label' => '', 'offline' => false];
    wifi_otantik_ui('plugin/otantik_monitor.tpl', 'Supervision MikroTik', [
        'routers' => $routers,
        'router' => $router,
        'router_states' => $states,
        'router_link' => $link,
    ]);
}

function wifi_otantik_mon_down_file($id)
{
    return rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'wo-mon-down-' . (int)$id;
}

function wifi_otantik_mon_mark_down($id)
{
    @file_put_contents(wifi_otantik_mon_down_file($id), (string)time());
}

function wifi_otantik_mon_clear_down($id)
{
    $f = wifi_otantik_mon_down_file($id);
    if (is_file($f)) {
        @unlink($f);
    }
}

function wifi_otantik_mon_is_down($id, $ttl = 45)
{
    $f = wifi_otantik_mon_down_file($id);
    return is_file($f) && (time() - (int)@filemtime($f) < $ttl);
}

function wifi_otantik_touch_router_status($router, $online)
{
    $want = $online ? 'Online' : 'Offline';
    if ((string)($router['status'] ?? '') === $want) {
        return;
    }
    try {
        $row = ORM::for_table('tbl_routers')->find_one((int)$router['id']);
        if ($row) {
            $row->status = $want;
            $row->save();
        }
    } catch (Throwable $e) {
    }
}

function wifi_otantik_router_link($router)
{
    $id = (int)($router['id'] ?? 0);
    $peer = null;
    try {
        $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', $id)->find_one();
    } catch (Throwable $e) {
    }
    $st = $peer ? strtolower((string)($peer['status'] ?? '')) : '';
    $seen = $peer ? (string)($peer['last_seen'] ?? '') : '';
    if ($st === '' || $st === 'pending') {
        return [
            'code' => 'pending',
            'offline' => true,
            'label' => 'Routeur non connecté. Collez le script dans Winbox, puis revenez ici.',
            'seen' => $seen,
        ];
    }
    if (wifi_otantik_mon_is_down($id)) {
        return [
            'code' => 'offline',
            'offline' => true,
            'label' => 'Routeur hors ligne. Aucune donnée n’est demandée tant qu’il ne répond pas.',
            'seen' => $seen,
        ];
    }
    return [
        'code' => 'online',
        'offline' => false,
        'label' => '',
        'seen' => $seen,
    ];
}

function wifi_otantik_mon_offline_payload($link)
{
    return [
        'ok' => false,
        'offline' => true,
        'status' => (string)($link['code'] ?? 'offline'),
        'error' => (string)($link['label'] ?? 'Routeur hors ligne'),
        'at' => date('H:i:s'),
        'hotspot' => [],
        'pppoe' => [],
        'traffic' => [],
        'caps' => [],
    ];
}

function wifi_otantik_mon_cache_file($id, $tab)
{
    return rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'wo-mon-' . (int)$id . '-' . preg_replace('/[^a-z]/', '', (string)$tab) . '.json';
}

function wifi_otantik_mon_cache_read($id, $tab, $ttl = 5)
{
    $f = wifi_otantik_mon_cache_file($id, $tab);
    if (!is_file($f) || time() - (int)@filemtime($f) > $ttl) {
        return null;
    }
    $raw = @file_get_contents($f);
    if ($raw === false || $raw === '') {
        return null;
    }
    $j = json_decode($raw, true);
    return is_array($j) ? $j : null;
}

function wifi_otantik_mon_cache_write($id, $tab, $payload)
{
    @file_put_contents(wifi_otantik_mon_cache_file($id, $tab), json_encode($payload), LOCK_EX);
}

function wifi_otantik_monitor_live($id)
{
    global $admin;
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    @set_time_limit(14);
    try {
        $router = Tenant::require_router($id, $admin);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'offline' => true, 'error' => 'Zone introuvable']);
        exit;
    }
    $link = wifi_otantik_router_link($router);
    if (!empty($link['offline'])) {
        echo json_encode(wifi_otantik_mon_offline_payload($link));
        exit;
    }
    $tab = preg_replace('/[^a-z]/', '', strtolower(_get('tab') ?: 'hotspot'));
    $ttl = $tab === 'traffic' ? 3 : 5;
    $cached = wifi_otantik_mon_cache_read($id, $tab, $ttl);
    if ($cached && empty($cached['offline'])) {
        $cached['cached'] = true;
        echo json_encode($cached);
        exit;
    }
    $lockPath = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'wo-mon-' . (int)$id . '.lock';
    $lock = @fopen($lockPath, 'c');
    $got = $lock && flock($lock, LOCK_EX | LOCK_NB);
    if (!$got) {
        $stale = wifi_otantik_mon_cache_read($id, $tab, 30);
        if ($stale && empty($stale['offline'])) {
            $stale['cached'] = true;
            echo json_encode($stale);
            if ($lock) {
                fclose($lock);
            }
            exit;
        }
        echo json_encode(wifi_otantik_mon_offline_payload([
            'code' => 'busy',
            'label' => 'Routeur occupé, nouvel essai dans un instant',
        ]));
        if ($lock) {
            fclose($lock);
        }
        exit;
    }
    $cached = wifi_otantik_mon_cache_read($id, $tab, $ttl);
    if ($cached && empty($cached['offline'])) {
        flock($lock, LOCK_UN);
        fclose($lock);
        $cached['cached'] = true;
        echo json_encode($cached);
        exit;
    }
    try {
        // A 2s connect timeout with no retry was too tight for a router
        // reached over a tunnel - any brief latency spike threw, and one
        // throw triggered mon_mark_down()'s full lockout even though the
        // router was reachable moments later. One retry before giving up
        // fixes false "hors ligne" without weakening real-down detection.
        $client = null;
        $connectErr = null;
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $client = wifi_otantik_mk_client($router, 4, true);
                break;
            } catch (Throwable $ce) {
                $connectErr = $ce;
                $client = null;
            }
        }
        if ($client === null) {
            throw $connectErr ?: new RuntimeException('Connexion impossible');
        }
        $resProps = 'uptime,cpu-load,free-memory,total-memory,board-name,version,architecture-name,cpu,cpu-count,cpu-frequency,free-hdd-space,total-hdd-space,platform,build-time';
        $resource = wifi_otantik_ros_rows($client, '/system/resource/print', 1, $resProps);
        $hsCount = -1;
        $pppCount = -1;
        $hs = [];
        $ppp = [];
        $traffic = [];
        $extra = [];
        $health = [];
        $hsProps = 'user,address,uptime,mac-address,bytes-in,bytes-out';
        $pppProps = 'name,address,uptime,caller-id,bytes-in,bytes-out';
        if ($tab === 'hotspot') {
            $raw = wifi_otantik_ros_rows($client, '/ip/hotspot/active/print', 400, $hsProps);
            $hsCount = count($raw);
            $hs = wifi_otantik_session_view(array_slice($raw, 0, 80), 'user', null, (string)($router['name'] ?? ''));
            $pppCount = wifi_otantik_ros_count($client, '/ppp/active/print');
        } elseif ($tab === 'pppoe') {
            $raw = wifi_otantik_ros_rows($client, '/ppp/active/print', 400, $pppProps);
            $pppCount = count($raw);
            $pppUsage = wifi_otantik_pppoe_iface_usage($client, $raw);
            $ppp = wifi_otantik_session_view(array_slice($raw, 0, 80), 'name', $pppUsage, (string)($router['name'] ?? ''));
            $hsCount = wifi_otantik_ros_count($client, '/ip/hotspot/active/print');
        } elseif ($tab === 'traffic') {
            $traffic = wifi_otantik_traffic_view($client);
            $hsCount = wifi_otantik_ros_count($client, '/ip/hotspot/active/print');
            $pppCount = wifi_otantik_ros_count($client, '/ppp/active/print');
        } elseif ($tab === 'wireless') {
            $extra = wifi_otantik_ros_rows($client, '/interface/wireless/print', 16, 'name,ssid,disabled,running');
            if (!$extra) {
                $extra = wifi_otantik_ros_rows($client, '/interface/wifi/print', 16, 'name,ssid,disabled,running');
            }
        } elseif ($tab === 'dhcp') {
            $extra = wifi_otantik_ros_rows($client, '/ip/dhcp-server/lease/print', 60, 'address,mac-address,status,host-name');
        } elseif ($tab === 'queues') {
            $extra = wifi_otantik_ros_rows($client, '/queue/simple/print', 20, 'name,target,max-limit,rate');
            foreach ($extra as &$q) {
                if (empty($q['rate']) && !empty($q['max-limit'])) {
                    $q['rate'] = $q['max-limit'];
                }
            }
            unset($q);
        } elseif ($tab === 'logs') {
            $extra = wifi_otantik_logs_view($client);
        }
        if ($tab !== 'traffic' && $tab !== 'logs') {
            $health = wifi_otantik_ros_rows($client, '/system/health/print', 12);
        }
        list($temp, $volt) = wifi_otantik_health_pick($health);
        if ($hsCount < 0) {
            $hsCount = wifi_otantik_ros_count($client, '/ip/hotspot/active/print');
        }
        if ($pppCount < 0) {
            $pppCount = wifi_otantik_ros_count($client, '/ppp/active/print');
        }
        if ($hsCount < 0) {
            $hsCount = count($hs);
        }
        if ($pppCount < 0) {
            $pppCount = count($ppp);
        }
        $payload = [
            'ok' => true,
            'offline' => false,
            'status' => 'online',
            'at' => date('H:i:s'),
            'ts' => time(),
            'resource' => $resource[0] ?? [],
            'caps' => wifi_otantik_resource_view($resource[0] ?? []),
            'temperature' => $temp,
            'voltage' => $volt,
            'hotspot_count' => $hsCount,
            'pppoe_count' => $pppCount,
            'hotspot_shown' => count($hs),
            'pppoe_shown' => count($ppp),
            'hotspot' => $hs,
            'pppoe' => $ppp,
            'traffic' => $traffic,
            'wireless' => $tab === 'wireless' ? $extra : [],
            'dhcp' => $tab === 'dhcp' ? $extra : [],
            'queues' => $tab === 'queues' ? $extra : [],
            'logs' => $tab === 'logs' ? $extra : [],
        ];
        wifi_otantik_mon_clear_down($id);
        wifi_otantik_touch_router_status($router, true);
        wifi_otantik_mon_cache_write($id, $tab, $payload);
        echo json_encode($payload);
    } catch (Throwable $e) {
        wifi_otantik_mon_mark_down($id);
        wifi_otantik_touch_router_status($router, false);
        echo json_encode(wifi_otantik_mon_offline_payload([
            'code' => 'offline',
            'label' => 'Routeur hors ligne. Impossible de joindre le MikroTik — aucune donnée n’est demandée.',
        ]));
    }
    flock($lock, LOCK_UN);
    fclose($lock);
    exit;
}

if (function_exists('register_hook')) {
    register_hook('cronjob', 'wifi_otantik_cron');
}

function wifi_otantik_resellers()
{
    OtantikReseller::admin();
}

function wifi_otantik_revendeur()
{
    OtantikReseller::portal();
}

/**
 * Staff sub-accounts: separate logins for employees, each with a role
 * (Gerant/Vendeur/Technicien/Comptable) that limits what they can see and
 * do via Tenant::can()/require_can(). Owner-only (finance_manage). Each
 * created account gets root = the owner's own tenant id, which is what
 * Tenant::id() resolves so the sub-account sees the SAME routers/wallet/
 * revenue as the owner, never a tenant of its own.
 */
function wifi_otantik_staff()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('staff_manage', $admin);
    $ownerId = Tenant::id($admin);
    $back = getUrl('plugin/wifi_otantik_staff');
    $act = (string)($routes[2] ?? '');
    $roles = ['Gerant' => 'Gérant', 'Vendeur' => 'Vendeur', 'Technicien' => 'Technicien', 'Comptable' => 'Comptable'];

    if ($act === 'create' && !empty($_POST)) {
        $username = trim((string)_post('username'));
        $fullname = trim((string)_post('fullname'));
        $phone = wifi_otantik_phone((string)_post('phone'));
        $email = strtolower(trim((string)_post('email')));
        $password = (string)_post('password');
        $role = (string)_post('role');
        if ($username === '' || $fullname === '' || strlen($password) < 6 || !isset($roles[$role])) {
            r2($back, 'e', 'Champs invalides (mot de passe : 6 caractères min., rôle requis)');
        }
        if (ORM::for_table('tbl_users')->where('username', $username)->find_one()) {
            r2($back, 'e', 'Ce nom d’utilisateur existe déjà');
        }
        $d = ORM::for_table('tbl_users')->create();
        $d->username = $username;
        $d->fullname = $fullname;
        $d->password = Password::_crypt($password);
        $d->phone = $phone;
        $d->email = $email;
        $d->user_type = $role;
        $d->status = 'Active';
        $d->root = $ownerId;
        $d->creationdate = date('Y-m-d H:i:s');
        $d->save();
        r2($back, 's', 'Compte créé pour ' . $username);
    }

    if ($act === 'toggle') {
        $sid = (int)($routes[3] ?? 0);
        $s = $sid ? ORM::for_table('tbl_users')->find_one($sid) : null;
        if ($s && (int)$s['root'] === $ownerId) {
            $s->status = (string)$s['status'] === 'Active' ? 'Inactive' : 'Active';
            $s->save();
            r2($back, 's', 'Statut mis à jour pour ' . $s['username']);
        }
        r2($back, 'e', 'Compte introuvable');
    }

    $staff = ORM::for_table('tbl_users')->where('root', $ownerId)->order_by_desc('id')->find_many();
    wifi_otantik_ui('plugin/otantik_staff.tpl', 'Équipe', [
        'staff' => $staff,
        'roles' => $roles,
    ]);
}

function wifi_otantik_asset_categories()
{
    return ['Équipements de Transmission', 'Équipements Réseau', "Équipements d'Alimentation", 'Infrastructure', 'Équipements Informatiques', 'Autres'];
}

function wifi_otantik_asset_statuses()
{
    return ['Actif', 'Inactif', 'En Maintenance', 'Mis au rebut'];
}

function wifi_otantik_asset_conditions()
{
    return ['Excellent', 'Bon', 'Passable', 'Mauvais'];
}

function wifi_otantik_asset_ref_suggest($category, $ownerId)
{
    $map = [
        'Équipements de Transmission' => 'TRX',
        'Équipements Réseau' => 'NET',
        "Équipements d'Alimentation" => 'PWR',
        'Infrastructure' => 'INF',
        'Équipements Informatiques' => 'IT',
        'Autres' => 'AST',
    ];
    $prefix = $map[$category] ?? 'AST';
    for ($i = 0; $i < 5; $i++) {
        $ref = $prefix . '-' . date('ymd') . '-' . str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $exists = ORM::for_table('tbl_otantik_erp_assets')->where('owner_id', (int)$ownerId)->where('ref', $ref)->find_one();
        if (!$exists) {
            return $ref;
        }
    }
    return $prefix . '-' . time();
}

function wifi_otantik_erp()
{
    global $admin, $routes;
    _admin();
    Tenant::require_can('finance_view', $admin);
    Tenant::ensure_schema();
    $act = (string)($routes[2] ?? '');
    $id = (int)($routes[3] ?? 0);

    if ($act === 'enable' && Tenant::is_platform($admin) && _post('tenant_id')) {
        $tid = (int)_post('tenant_id');
        // Le formulaire envoie la clé primaire du tenant, pas son user_id.
        $t = ORM::for_table('tbl_otantik_tenants')->find_one($tid);
        if ($t) {
            $t->erp_enabled = _post('erp_enabled') === '1' ? 1 : 0;
            if (_post('erp_until') !== '') {
                $t->erp_until = date('Y-m-d 23:59:59', strtotime((string)_post('erp_until')));
            } elseif (!_post('erp_enabled')) {
                $t->erp_until = null;
            }
            $t->save();
            r2(getUrl('plugin/wifi_otantik_tenants'), 's', 'Module ERP mis à jour');
        }
    }

    if (!OtantikErp::has_access($admin)) {
        wifi_otantik_ui('plugin/otantik_erp_locked.tpl', 'ERP Premium', ['erp_locked' => true]);
        return;
    }

    $oid = Tenant::id($admin);
    if ($oid > 0 && !Tenant::is_platform($admin)) {
        OtantikErp::run_recurring($oid);
    }

    $backExpenses = getUrl('plugin/wifi_otantik_erp/expenses');
    $backDocs = getUrl('plugin/wifi_otantik_erp/documents');
    $backHome = getUrl('plugin/wifi_otantik_erp');

    if ($act === 'sync') {
        $n = OtantikErp::backfill($admin);
        r2($backHome, 's', 'Synchronisation terminée · ' . (int)$n . ' opération(s)');
    }

    if ($act === 'income' && _post('income_label')) {
        list($ok, $msg) = OtantikErp::add_other_income($oid, [
            'label' => _post('income_label'),
            'amount' => _post('income_amount'),
            'date' => _post('income_date'),
            'category' => _post('income_category'),
            'method' => _post('income_method'),
            'note' => _post('income_note'),
        ]);
        r2($backHome . ($ok ? '' : ''), $ok ? 's' : 'e', $msg);
    }

    if ($act === 'edit_income' && $id > 0) {
        $flow = ORM::for_table('tbl_otantik_erp_flow')->find_one($id);
        if (!$flow || (string)$flow['source'] !== 'other_service' || (!Tenant::is_platform($admin) && (int)$flow['owner_id'] !== $oid)) {
            r2($backHome, 'e', 'Entrée introuvable');
        }
        $backFlow = getUrl('plugin/wifi_otantik_erp/flow');
        if (_post('save_income')) {
            $amount = (float)_post('income_amount');
            $label = trim((string)_post('income_label'));
            if ($amount <= 0 || $label === '') {
                r2(getUrl('plugin/wifi_otantik_erp/edit_income/') . $id, 'e', 'Montant ou libellé invalide');
            }
            $dateRaw = trim((string)_post('income_date'));
            $ts = $dateRaw !== '' ? strtotime($dateRaw) : false;
            if ($ts === false) {
                r2(getUrl('plugin/wifi_otantik_erp/edit_income/') . $id, 'e', 'Date invalide');
            }
            $flow->label = $label;
            $flow->amount = $amount;
            $flow->category = trim((string)_post('income_category')) ?: 'services';
            $flow->method = trim((string)_post('income_method')) ?: 'Espèces';
            $flow->occurred_at = date('Y-m-d', $ts) . ' ' . date('H:i:s');
            $meta = json_decode((string)$flow['meta'], true);
            if (!is_array($meta)) {
                $meta = [];
            }
            $meta['note'] = trim((string)_post('income_note'));
            $flow->meta = json_encode($meta, JSON_UNESCAPED_UNICODE);
            $flow->save();
            r2($backFlow, 's', 'Entrée mise à jour');
        }
        wifi_otantik_ui('plugin/otantik_erp_income_edit.tpl', 'Modifier autre revenu', [
            'flow' => $flow,
        ]);
        return;
    }

    if ($act === 'delete_income' && $id > 0) {
        $flow = ORM::for_table('tbl_otantik_erp_flow')->find_one($id);
        if (!$flow || (string)$flow['source'] !== 'other_service' || (!Tenant::is_platform($admin) && (int)$flow['owner_id'] !== $oid)) {
            r2($backHome, 'e', 'Entrée introuvable');
        }
        if (empty($_POST)) {
            r2(getUrl('plugin/wifi_otantik_erp/flow'), 'e', 'Confirmation requise');
        }
        $flow->delete();
        r2(getUrl('plugin/wifi_otantik_erp/flow'), 's', 'Entrée supprimée');
    }

    if ($act === 'expense' && _post('expense_title')) {
        $amt = (float)_post('expense_amount');
        if ($amt <= 0) {
            r2($backExpenses, 'e', 'Montant invalide');
        }
        $dateRaw = trim((string)_post('expense_date'));
        $dateTs = $dateRaw !== '' ? strtotime($dateRaw) : time();
        if ($dateTs === false) {
            r2($backExpenses, 'e', 'Date de dépense invalide');
        }
        $expenseDate = date('Y-m-d', $dateTs);
        $expenseWhen = $expenseDate . ' ' . date('H:i:s');
        $exp = ORM::for_table('tbl_otantik_erp_expenses')->create();
        $exp->owner_id = $oid;
        $exp->number = OtantikErp::next_expense_number($oid);
        $exp->title = trim((string)_post('expense_title'));
        $exp->category = trim((string)_post('expense_category')) ?: 'charges';
        $exp->amount = $amt;
        $exp->vendor = trim((string)_post('expense_vendor'));
        $exp->payment_method = trim((string)_post('expense_payment_method'));
        $exp->note = trim((string)_post('expense_note'));
        $exp->status = 'paid';
        $exp->source_type = 'manual';
        $exp->source_id = 0;
        $exp->expense_date = $expenseDate;
        $exp->paid_at = $expenseWhen;
        $exp->created_at = date('Y-m-d H:i:s');
        $exp->save();
        $flow = ORM::for_table('tbl_otantik_erp_flow')->create();
        $flow->owner_id = $oid;
        $flow->direction = 'out';
        $flow->source = 'expense';
        $flow->amount = $amt;
        $flow->label = $exp->title;
        $flow->category = $exp->category;
        $flow->ref_type = 'expense';
        $flow->ref_id = (int)$exp->id();
        $flow->expense_id = (int)$exp->id();
        $flow->method = $exp->payment_method;
        $flow->occurred_at = $expenseWhen;
        $flow->created_at = date('Y-m-d H:i:s');
        $flow->save();
        if (!empty($_FILES['expense_files']['name'])) {
            $errors = OtantikErp::save_uploads($oid, 'expense', (int)$exp->id(), $_FILES['expense_files']);
            if ($errors) {
                r2(getUrl('plugin/wifi_otantik_erp/report/' . (int)$exp->id()), 'e', 'Rapport créé, mais pièce : ' . implode(' · ', $errors));
            }
        }
        r2(getUrl('plugin/wifi_otantik_erp/report/' . (int)$exp->id()), 's', 'Rapport de dépense créé · ' . $exp->number);
    }

    if ($act === 'expense_file' && $id > 0 && !empty($_FILES['expense_files']['name'])) {
        $exp = OtantikErp::expense_owned($id, $admin);
        if (!$exp) {
            r2($backExpenses, 'e', 'Dépense introuvable');
        }
        $errors = OtantikErp::save_uploads((int)$exp['owner_id'], 'expense', $id, $_FILES['expense_files']);
        r2(
            getUrl('plugin/wifi_otantik_erp/report/' . $id),
            $errors ? 'e' : 's',
            $errors ? implode(' · ', $errors) : 'Pièce(s) justificative(s) ajoutée(s)'
        );
    }

    if ($act === 'recurring' && _post('rec_title')) {
        $amt = (float)_post('rec_amount');
        if ($amt <= 0) {
            r2($backExpenses, 'e', 'Montant invalide');
        }
        $rec = ORM::for_table('tbl_otantik_erp_recurring')->create();
        $rec->owner_id = $oid;
        $rec->title = trim((string)_post('rec_title'));
        $rec->category = trim((string)_post('rec_category')) ?: 'fixe';
        $rec->amount = $amt;
        $rec->day_of_month = max(1, min(28, (int)_post('rec_day')));
        $rec->active = 1;
        $rec->created_at = date('Y-m-d H:i:s');
        $rec->save();
        r2($backExpenses, 's', 'Charge récurrente active');
    }

    if ($act === 'recurring_toggle' && $id > 0) {
        $rec = ORM::for_table('tbl_otantik_erp_recurring')->find_one($id);
        if ($rec && (Tenant::is_platform($admin) || (int)$rec['owner_id'] === $oid)) {
            $rec->active = (int)$rec['active'] === 1 ? 0 : 1;
            $rec->save();
            r2($backExpenses, 's', $rec->active ? 'Charge réactivée' : 'Charge désactivée');
        }
        r2($backExpenses, 'e', 'Charge introuvable');
    }

    if ($act === 'edit_expense' && $id > 0) {
        $exp = OtantikErp::expense_owned($id, $admin);
        if (!$exp) {
            r2($backExpenses, 'e', 'Dépense introuvable');
        }
        if (_post('save_expense')) {
            $amt = (float)_post('expense_amount');
            $title = trim((string)_post('expense_title'));
            if ($amt <= 0 || $title === '') {
                r2(getUrl('plugin/wifi_otantik_erp/edit_expense/') . $id, 'e', 'Montant ou intitulé invalide');
            }
            $dateRaw = trim((string)_post('expense_date'));
            $dateTs = $dateRaw !== '' ? strtotime($dateRaw) : false;
            if ($dateTs === false) {
                r2(getUrl('plugin/wifi_otantik_erp/edit_expense/') . $id, 'e', 'Date invalide');
            }
            $expenseDate = date('Y-m-d', $dateTs);
            $expenseWhen = $expenseDate . ' ' . date('H:i:s');
            $exp->title = $title;
            $exp->category = trim((string)_post('expense_category')) ?: 'charges';
            $exp->amount = $amt;
            $exp->vendor = trim((string)_post('expense_vendor'));
            $exp->payment_method = trim((string)_post('expense_payment_method'));
            $exp->note = trim((string)_post('expense_note'));
            $exp->expense_date = $expenseDate;
            $exp->paid_at = $expenseWhen;
            $exp->save();
            $flow = ORM::for_table('tbl_otantik_erp_flow')->where('ref_type', 'expense')->where('ref_id', $id)->find_one();
            if ($flow) {
                $flow->label = $exp->title;
                $flow->amount = $amt;
                $flow->category = $exp->category;
                $flow->method = $exp->payment_method;
                $flow->occurred_at = $expenseWhen;
                $flow->save();
            }
            r2($backExpenses, 's', 'Dépense mise à jour');
        }
        wifi_otantik_ui('plugin/otantik_erp_expense_edit.tpl', 'Modifier une dépense', [
            'expense' => $exp,
            'files' => OtantikErp::files_for('expense', $id),
        ]);
        return;
    }

    if ($act === 'delete_expense' && $id > 0) {
        $exp = OtantikErp::expense_owned($id, $admin);
        if (!$exp) {
            r2($backExpenses, 'e', 'Dépense introuvable');
        }
        if (empty($_POST)) {
            r2($backExpenses, 'e', 'Confirmation requise');
        }
        $flow = ORM::for_table('tbl_otantik_erp_flow')->where('ref_type', 'expense')->where('ref_id', $id)->find_one();
        if ($flow) {
            $flow->delete();
        }
        foreach (OtantikErp::files_for('expense', $id) as $f) {
            $fRow = ORM::for_table('tbl_otantik_erp_files')->find_one($f['id']);
            if ($fRow) {
                $path = OtantikErp::file_path($fRow);
                if (is_file($path)) {
                    @unlink($path);
                }
                $fRow->delete();
            }
        }
        $number = (string)$exp['number'];
        $exp->delete();
        r2($backExpenses, 's', 'Dépense supprimée · ' . $number);
    }

    if ($act === 'export' && in_array(($routes[3] ?? ''), ['expenses', 'assets'], true)) {
        $what = $routes[3];
        $fmt = wifi_otantik_export_fmt($routes[4] ?? 'csv');
        if ($what === 'expenses') {
            $q = ORM::for_table('tbl_otantik_erp_expenses')->order_by_desc('paid_at');
            if (!Tenant::is_platform($admin)) {
                $q->where('owner_id', $oid);
            }
            $out = [];
            foreach ($q->find_many() as $e) {
                $out[] = [
                    (string)$e['number'],
                    (string)($e['expense_date'] ?: substr((string)$e['paid_at'], 0, 10)),
                    (string)$e['title'],
                    (string)$e['category'],
                    (string)$e['vendor'],
                    (string)$e['payment_method'],
                    (string)$e['amount'],
                    (string)$e['note'],
                ];
            }
            OtantikExport::send($fmt, 'depenses', 'Dépenses', [
                'N°', 'Date', 'Intitulé', 'Catégorie', 'Fournisseur', 'Paiement', 'Montant (FCFA)', 'Note',
            ], $out);
            return;
        }
        $q = ORM::for_table('tbl_otantik_erp_assets')->order_by_desc('id');
        if (!Tenant::is_platform($admin)) {
            $q->where('owner_id', $oid);
        }
        $out = [];
        foreach ($q->find_many() as $a) {
            $out[] = [
                (string)$a['ref'],
                (string)$a['name'],
                (string)$a['category'],
                (string)$a['brand_model'],
                (string)$a['status'],
                (string)$a['asset_condition'],
                (string)$a['cost'],
                (string)$a['purchase_date'],
                (string)$a['note'],
            ];
        }
        OtantikExport::send($fmt, 'actifs', 'Actifs', [
            'Réf.', 'Désignation', 'Catégorie', 'Marque / Modèle', 'Statut', 'État', 'Coût (FCFA)', 'Date d\'achat', 'Note',
        ], $out);
        return;
    }

    if ($act === 'document' && _post('doc_kind')) {
        $kind = in_array(_post('doc_kind'), ['quote', 'invoice'], true) ? _post('doc_kind') : 'quote';
        $lines = OtantikErp::parse_lines();
        if (!$lines) {
            r2($backDocs, 'e', 'Ajoutez au moins une ligne de prestation');
        }
        $sub = OtantikErp::lines_total($lines);
        $tax = max(0, (float)_post('doc_tax'));
        $doc = ORM::for_table('tbl_otantik_erp_docs')->create();
        $doc->owner_id = $oid;
        $doc->kind = $kind;
        $doc->number = OtantikErp::next_doc_number($oid, $kind);
        $doc->client_name = trim((string)_post('client_name'));
        $doc->client_phone = trim((string)_post('client_phone'));
        $doc->client_email = trim((string)_post('client_email'));
        $doc->subject = trim((string)_post('doc_subject'));
        $doc->lines_json = json_encode($lines, JSON_UNESCAPED_UNICODE);
        $doc->subtotal = $sub;
        $doc->tax = $tax;
        $doc->total = $sub + $tax;
        $doc->status = 'draft';
        $issueRaw = trim((string)_post('issue_date'));
        $doc->issue_date = $issueRaw !== '' ? date('Y-m-d', strtotime($issueRaw)) : date('Y-m-d');
        $vu = trim((string)_post('valid_until'));
        $doc->valid_until = $vu !== '' ? date('Y-m-d', strtotime($vu)) : null;
        $doc->created_at = date('Y-m-d H:i:s');
        $doc->updated_at = date('Y-m-d H:i:s');
        $doc->save();
        r2(getUrl('plugin/wifi_otantik_erp/doc/' . (int)$doc->id()), 's', 'Document créé · ' . $doc->number);
    }

    if ($act === 'doc_update' && $id > 0) {
        $doc = OtantikErp::doc_owned($id, $admin);
        if (!$doc) {
            r2($backDocs, 'e', 'Document introuvable');
        }
        $lines = OtantikErp::parse_lines();
        if (!$lines) {
            r2(getUrl('plugin/wifi_otantik_erp/doc/' . $id), 'e', 'Ajoutez au moins une ligne de prestation');
        }
        $sub = OtantikErp::lines_total($lines);
        $tax = max(0, (float)_post('doc_tax'));
        $doc->client_name = trim((string)_post('client_name'));
        $doc->client_phone = trim((string)_post('client_phone'));
        $doc->client_email = trim((string)_post('client_email'));
        $doc->subject = trim((string)_post('doc_subject'));
        $doc->lines_json = json_encode($lines, JSON_UNESCAPED_UNICODE);
        $doc->subtotal = $sub;
        $doc->tax = $tax;
        $doc->total = $sub + $tax;
        $issueRaw = trim((string)_post('issue_date'));
        $doc->issue_date = $issueRaw !== '' ? date('Y-m-d', strtotime($issueRaw)) : $doc['issue_date'];
        $vu = trim((string)_post('valid_until'));
        $doc->valid_until = $vu !== '' ? date('Y-m-d', strtotime($vu)) : null;
        $doc->updated_at = date('Y-m-d H:i:s');
        $doc->save();
        // A paid document's total already hit the books as a single ERP flow
        // entry (no wallet involved, unlike a sale) - keep that entry's
        // amount/label in sync so editing a paid invoice doesn't leave the
        // revenue report showing the old total.
        if ((string)$doc['status'] === 'paid') {
            try {
                $flow = ORM::for_table('tbl_otantik_erp_flow')->where('ref_type', 'invoice')->where('ref_id', $id)->find_one();
                if ($flow) {
                    $flow->amount = $doc['total'];
                    $flow->label = trim((string)$doc['subject']) !== '' ? (string)$doc['subject'] : ('Facture ' . $doc['number']);
                    $flow->save();
                }
            } catch (Throwable $e) {
            }
        }
        r2(getUrl('plugin/wifi_otantik_erp/doc/' . $id), 's', 'Document mis à jour · ' . $doc->number);
    }

    if ($act === 'doc_delete' && $id > 0) {
        $doc = OtantikErp::doc_owned($id, $admin);
        if (!$doc) {
            r2($backDocs, 'e', 'Document introuvable');
        }
        if (empty($_POST)) {
            r2(getUrl('plugin/wifi_otantik_erp/doc/' . $id), 'e', 'Confirmation requise');
        }
        // A paid document's only financial trace is a single ERP flow entry
        // (no wallet involvement, unlike a sale) - remove it too so deleting
        // the document doesn't leave a phantom income line behind.
        if ((string)$doc['status'] === 'paid') {
            try {
                $flow = ORM::for_table('tbl_otantik_erp_flow')->where('ref_type', 'invoice')->where('ref_id', $id)->find_one();
                if ($flow) {
                    $flow->delete();
                }
            } catch (Throwable $e) {
            }
        }
        $doc->delete();
        r2($backDocs, 's', 'Document supprimé');
    }

    if ($act === 'doc_status' && $id > 0 && _post('status')) {
        $doc = OtantikErp::doc_owned($id, $admin);
        if (!$doc) {
            r2($backDocs, 'e', 'Document introuvable');
        }
        $st = (string)_post('status');
        $allowed = ['draft', 'sent', 'accepted', 'paid', 'cancelled'];
        if (!in_array($st, $allowed, true)) {
            r2(getUrl('plugin/wifi_otantik_erp/doc/' . $id), 'e', 'Statut invalide');
        }
        $doc->status = $st;
        if ($st === 'paid') {
            $doc->paid_at = date('Y-m-d H:i:s');
            if ((string)$doc['kind'] === 'quote') {
                // Convertir devis → facture payée
                $inv = ORM::for_table('tbl_otantik_erp_docs')->create();
                $inv->owner_id = (int)$doc['owner_id'];
                $inv->kind = 'invoice';
                $inv->number = OtantikErp::next_doc_number((int)$doc['owner_id'], 'invoice');
                $inv->client_name = $doc['client_name'];
                $inv->client_phone = $doc['client_phone'];
                $inv->client_email = $doc['client_email'];
                $inv->subject = $doc['subject'];
                $inv->lines_json = $doc['lines_json'];
                $inv->subtotal = $doc['subtotal'];
                $inv->tax = $doc['tax'];
                $inv->total = $doc['total'];
                $inv->status = 'paid';
                $inv->issue_date = date('Y-m-d');
                $inv->paid_at = date('Y-m-d H:i:s');
                $inv->created_at = date('Y-m-d H:i:s');
                $inv->updated_at = date('Y-m-d H:i:s');
                $inv->save();
                $doc->status = 'accepted';
                $doc->updated_at = date('Y-m-d H:i:s');
                $doc->save();
                OtantikErp::sync_invoice_paid($inv);
                r2(getUrl('plugin/wifi_otantik_erp/doc/' . (int)$inv->id()), 's', 'Devis accepté · facture ' . $inv->number . ' encaissée');
            }
            OtantikErp::sync_invoice_paid($doc);
        }
        $doc->updated_at = date('Y-m-d H:i:s');
        $doc->save();
        r2(getUrl('plugin/wifi_otantik_erp/doc/' . $id), 's', 'Statut mis à jour');
    }

    if ($act === 'doc_to_invoice' && $id > 0) {
        $doc = OtantikErp::doc_owned($id, $admin);
        if (!$doc || (string)$doc['kind'] !== 'quote') {
            r2($backDocs, 'e', 'Devis introuvable');
        }
        $inv = ORM::for_table('tbl_otantik_erp_docs')->create();
        $inv->owner_id = (int)$doc['owner_id'];
        $inv->kind = 'invoice';
        $inv->number = OtantikErp::next_doc_number((int)$doc['owner_id'], 'invoice');
        $inv->client_name = $doc['client_name'];
        $inv->client_phone = $doc['client_phone'];
        $inv->client_email = $doc['client_email'];
        $inv->subject = $doc['subject'];
        $inv->lines_json = $doc['lines_json'];
        $inv->subtotal = $doc['subtotal'];
        $inv->tax = $doc['tax'];
        $inv->total = $doc['total'];
        $inv->status = 'sent';
        $inv->issue_date = date('Y-m-d');
        $inv->created_at = date('Y-m-d H:i:s');
        $inv->updated_at = date('Y-m-d H:i:s');
        $inv->save();
        $doc->status = 'accepted';
        $doc->updated_at = date('Y-m-d H:i:s');
        $doc->save();
        r2(getUrl('plugin/wifi_otantik_erp/doc/' . (int)$inv->id()), 's', 'Facture créée · ' . $inv->number);
    }

    if ($act === 'file' && $id > 0) {
        $f = ORM::for_table('tbl_otantik_erp_files')->find_one($id);
        if (!$f || (!Tenant::is_platform($admin) && (int)$f['owner_id'] !== $oid)) {
            r2($backHome, 'e', 'Fichier introuvable');
        }
        $path = OtantikErp::file_path($f);
        if (!is_file($path)) {
            r2($backHome, 'e', 'Fichier manquant sur le disque');
        }
        $mime = (string)$f['mime'] ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename((string)$f['filename']) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    if ($act === 'report' && $id > 0) {
        $expense = OtantikErp::expense_owned($id, $admin);
        if (!$expense) {
            r2($backExpenses, 'e', 'Rapport de dépense introuvable');
        }
        if (trim((string)($expense['number'] ?? '')) === '') {
            $expense->number = OtantikErp::next_expense_number((int)$expense['owner_id']);
            $expense->expense_date = (string)($expense['expense_date'] ?? '') !== ''
                ? $expense['expense_date']
                : substr((string)$expense['paid_at'], 0, 10);
            $expense->save();
        }
        $payload = OtantikErp::expense_payload($expense);
        $print = ($routes[4] ?? '') === 'print';
        wifi_otantik_ui('plugin/otantik_erp_expense_view.tpl', 'Rapport ' . $payload['number'], [
            'erp_nav' => 'expenses',
            'expense' => $payload,
            'print_mode' => $print,
        ]);
        return;
    }

    if ($act === 'doc' && $id > 0) {
        $doc = OtantikErp::doc_owned($id, $admin);
        if (!$doc) {
            r2($backDocs, 'e', 'Document introuvable');
        }
        $payload = OtantikErp::doc_payload($doc);
        $print = ($routes[4] ?? '') === 'print';
        wifi_otantik_ui('plugin/otantik_erp_doc_view.tpl', $payload['kind_label'] . ' ' . $payload['number'], [
            'erp_nav' => 'documents',
            'doc' => $payload,
            'print_mode' => $print,
        ]);
        return;
    }

    $range = in_array(_get('range'), ['today', 'week', 'month', 'year'], true) ? _get('range') : 'month';

    if ($act === 'expenses') {
        $q = ORM::for_table('tbl_otantik_erp_expenses')->order_by_desc('paid_at')->limit(100);
        $rq = ORM::for_table('tbl_otantik_erp_recurring')->order_by_asc('day_of_month');
        if (!Tenant::is_platform($admin)) {
            $q->where('owner_id', $oid);
            $rq->where('owner_id', $oid);
        }
        $expenses = $q->find_many();
        $expFiles = [];
        foreach ($expenses as $e) {
            if (trim((string)($e['number'] ?? '')) === '') {
                $e->number = OtantikErp::next_expense_number((int)$e['owner_id']);
                $e->expense_date = (string)($e['expense_date'] ?? '') !== ''
                    ? $e['expense_date']
                    : substr((string)$e['paid_at'], 0, 10);
                $e->save();
            }
            $expFiles[(int)$e['id']] = OtantikErp::files_for('expense', (int)$e['id']);
        }
        wifi_otantik_ui('plugin/otantik_erp_expenses.tpl', 'Dépenses ERP', [
            'erp_nav' => 'expenses',
            'expenses' => $expenses,
            'expense_files' => $expFiles,
            'recurring' => $rq->find_many(),
            'export_csv' => getUrl('plugin/wifi_otantik_erp/export/expenses/csv'),
            'export_pdf' => getUrl('plugin/wifi_otantik_erp/export/expenses/pdf'),
        ]);
        return;
    }

    if ($act === 'flow') {
        $q = ORM::for_table('tbl_otantik_erp_flow')->order_by_desc('occurred_at')->limit(200);
        if (!Tenant::is_platform($admin)) {
            $q->where('owner_id', $oid);
        }
        wifi_otantik_ui('plugin/otantik_erp_flow.tpl', 'Trésorerie ERP', [
            'erp_nav' => 'flow',
            'flows' => $q->find_many(),
        ]);
        return;
    }

    if ($act === 'documents') {
        $q = ORM::for_table('tbl_otantik_erp_docs')->order_by_desc('id')->limit(100);
        if (!Tenant::is_platform($admin)) {
            $q->where('owner_id', $oid);
        }
        wifi_otantik_ui('plugin/otantik_erp_documents.tpl', 'Devis & factures', [
            'erp_nav' => 'documents',
            'docs' => $q->find_many(),
        ]);
        return;
    }

    if ($act === 'assets') {
        $q = ORM::for_table('tbl_otantik_erp_assets')->order_by_desc('id')->limit(500);
        if (!Tenant::is_platform($admin)) {
            $q->where('owner_id', $oid);
        }
        $assets = $q->find_many();
        $totalValue = 0.0;
        $byCategory = [];
        $byStatus = [];
        foreach ($assets as $a) {
            $cost = (float)$a['cost'];
            $totalValue += $cost;
            $cat = (string)$a['category'] ?: 'Autres';
            if (!isset($byCategory[$cat])) {
                $byCategory[$cat] = ['category' => $cat, 'count' => 0, 'value' => 0.0];
            }
            $byCategory[$cat]['count']++;
            $byCategory[$cat]['value'] += $cost;
            $st = (string)$a['status'] ?: 'Actif';
            if (!isset($byStatus[$st])) {
                $byStatus[$st] = ['status' => $st, 'count' => 0];
            }
            $byStatus[$st]['count']++;
        }
        $totalCount = count($assets);
        foreach ($byCategory as &$c) {
            $c['avg'] = $c['count'] > 0 ? $c['value'] / $c['count'] : 0;
        }
        unset($c);
        foreach ($byStatus as &$s) {
            $s['pct'] = $totalCount > 0 ? round(($s['count'] / $totalCount) * 100, 1) : 0;
        }
        unset($s);
        uasort($byCategory, function ($x, $y) {
            return $y['value'] <=> $x['value'];
        });
        wifi_otantik_ui('plugin/otantik_erp_assets.tpl', 'Actifs', [
            'erp_nav' => 'assets',
            'assets' => $assets,
            'asset_total_value' => $totalValue,
            'asset_total_count' => $totalCount,
            'asset_in_service' => $byStatus['Actif']['count'] ?? 0,
            'asset_by_category' => array_values($byCategory),
            'asset_by_status' => array_values($byStatus),
            'asset_categories' => wifi_otantik_asset_categories(),
            'asset_statuses' => wifi_otantik_asset_statuses(),
            'asset_conditions' => wifi_otantik_asset_conditions(),
            'export_csv' => getUrl('plugin/wifi_otantik_erp/export/assets/csv'),
            'export_pdf' => getUrl('plugin/wifi_otantik_erp/export/assets/pdf'),
        ]);
        return;
    }

    if ($act === 'asset' && _post('asset_name')) {
        $cost = (float)_post('asset_cost');
        if ($cost < 0) {
            r2(getUrl('plugin/wifi_otantik_erp/assets'), 'e', 'Coût invalide');
        }
        $category = in_array(_post('asset_category'), wifi_otantik_asset_categories(), true) ? _post('asset_category') : 'Autres';
        $status = in_array(_post('asset_status'), wifi_otantik_asset_statuses(), true) ? _post('asset_status') : 'Actif';
        $condition = in_array(_post('asset_condition'), wifi_otantik_asset_conditions(), true) ? _post('asset_condition') : 'Bon';
        $ref = trim((string)_post('asset_ref'));
        if ($ref === '') {
            $ref = wifi_otantik_asset_ref_suggest($category, $oid);
        } else {
            $dup = ORM::for_table('tbl_otantik_erp_assets')->where('owner_id', $oid)->where('ref', $ref)->find_one();
            if ($dup) {
                r2(getUrl('plugin/wifi_otantik_erp/assets'), 'e', 'Cette référence existe déjà : ' . $ref);
            }
        }
        $dateRaw = trim((string)_post('asset_purchase_date'));
        $purchaseDate = $dateRaw !== '' ? wifi_otantik_ymd($dateRaw) : date('Y-m-d');
        $a = ORM::for_table('tbl_otantik_erp_assets')->create();
        $a->owner_id = $oid;
        $a->ref = $ref;
        $a->name = trim((string)_post('asset_name'));
        $a->category = $category;
        $a->brand_model = trim((string)_post('asset_brand_model'));
        $a->status = $status;
        $a->asset_condition = $condition;
        $a->cost = $cost;
        $a->purchase_date = $purchaseDate;
        $a->note = trim((string)_post('asset_note'));
        $a->created_at = date('Y-m-d H:i:s');
        $a->updated_at = date('Y-m-d H:i:s');
        $a->save();
        r2(getUrl('plugin/wifi_otantik_erp/assets'), 's', 'Actif ajouté · ' . $ref);
    }

    if ($act === 'edit_asset' && $id > 0) {
        $a = ORM::for_table('tbl_otantik_erp_assets')->find_one($id);
        if (!$a || (!Tenant::is_platform($admin) && (int)$a['owner_id'] !== $oid)) {
            r2(getUrl('plugin/wifi_otantik_erp/assets'), 'e', 'Actif introuvable');
        }
        if (_post('save_asset')) {
            $cost = (float)_post('asset_cost');
            if ($cost < 0) {
                r2(getUrl('plugin/wifi_otantik_erp/edit_asset/') . $id, 'e', 'Coût invalide');
            }
            $category = in_array(_post('asset_category'), wifi_otantik_asset_categories(), true) ? _post('asset_category') : 'Autres';
            $status = in_array(_post('asset_status'), wifi_otantik_asset_statuses(), true) ? _post('asset_status') : 'Actif';
            $condition = in_array(_post('asset_condition'), wifi_otantik_asset_conditions(), true) ? _post('asset_condition') : 'Bon';
            $ref = trim((string)_post('asset_ref'));
            if ($ref === '') {
                r2(getUrl('plugin/wifi_otantik_erp/edit_asset/') . $id, 'e', 'La référence est obligatoire');
            }
            if ($ref !== (string)$a['ref']) {
                $dup = ORM::for_table('tbl_otantik_erp_assets')->where('owner_id', (int)$a['owner_id'])->where('ref', $ref)->where_not_equal('id', $id)->find_one();
                if ($dup) {
                    r2(getUrl('plugin/wifi_otantik_erp/edit_asset/') . $id, 'e', 'Cette référence existe déjà : ' . $ref);
                }
            }
            $dateRaw = trim((string)_post('asset_purchase_date'));
            $a->ref = $ref;
            $a->name = trim((string)_post('asset_name'));
            $a->category = $category;
            $a->brand_model = trim((string)_post('asset_brand_model'));
            $a->status = $status;
            $a->asset_condition = $condition;
            $a->cost = $cost;
            $a->purchase_date = $dateRaw !== '' ? wifi_otantik_ymd($dateRaw) : $a['purchase_date'];
            $a->note = trim((string)_post('asset_note'));
            $a->updated_at = date('Y-m-d H:i:s');
            $a->save();
            r2(getUrl('plugin/wifi_otantik_erp/assets'), 's', 'Actif mis à jour');
        }
        wifi_otantik_ui('plugin/otantik_erp_asset_edit.tpl', 'Modifier un actif', [
            'a' => $a,
            'asset_categories' => wifi_otantik_asset_categories(),
            'asset_statuses' => wifi_otantik_asset_statuses(),
            'asset_conditions' => wifi_otantik_asset_conditions(),
        ]);
        return;
    }

    if ($act === 'delete_asset' && $id > 0) {
        $a = ORM::for_table('tbl_otantik_erp_assets')->find_one($id);
        if (!$a || (!Tenant::is_platform($admin) && (int)$a['owner_id'] !== $oid)) {
            r2(getUrl('plugin/wifi_otantik_erp/assets'), 'e', 'Actif introuvable');
        }
        if (empty($_POST)) {
            r2(getUrl('plugin/wifi_otantik_erp/assets'), 'e', 'Confirmation requise');
        }
        $ref = (string)$a['ref'];
        $a->delete();
        r2(getUrl('plugin/wifi_otantik_erp/assets'), 's', 'Actif supprimé · ' . $ref);
    }

    OtantikErp::backfill($admin);
    $dash = OtantikErp::dashboard($admin, $range);
    $dash['chart_svg'] = OtantikErp::chart_svg($dash['series']);
    $dash['series_json'] = json_encode($dash['series'], JSON_UNESCAPED_UNICODE);
    wifi_otantik_ui('plugin/otantik_erp_home.tpl', 'ERP Otantik', array_merge($dash, [
        'erp_nav' => 'home',
    ]));
}
