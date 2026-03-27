<?php
// Load .env file
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// ─── Helper ───────────────────────────────────────────────────
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ─── App ──────────────────────────────────────────────────────
define('APP_NAME',  env('APP_NAME',  'Quản Lý Nhà Sách'));
define('APP_URL',   env('APP_URL',   'http://localhost/quan-ly-nha-sach/public'));
define('APP_ENV',   env('APP_ENV',   'production'));
define('APP_DEBUG', env('APP_DEBUG', false));

// ─── Database ─────────────────────────────────────────────────
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'quan_ly_nha_sach'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// ─── Session ──────────────────────────────────────────────────
define('SESSION_NAME',     env('SESSION_NAME',     'nha_sach_session'));
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 7200));

// ─── Paths ────────────────────────────────────────────────────
define('ROOT_PATH',  dirname(__DIR__));
define('APP_PATH',   ROOT_PATH . '/app');
define('VIEW_PATH',  APP_PATH  . '/Views');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// ─── Roles ────────────────────────────────────────────────────
define('ROLE_QUAN_LY',    'quan_ly');
define('ROLE_BAN_HANG',   'ban_hang');
define('ROLE_KHO',        'kho');

// ─── Error reporting ──────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}