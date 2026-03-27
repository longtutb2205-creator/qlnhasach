<?php

namespace App\Core;

/**
 * Auth — Quản lý session, đăng nhập / đăng xuất, phân quyền
 */
class Auth
{
    /**
     * Khởi động session (gọi 1 lần ở index.php)
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,       // Đổi true nếu dùng HTTPS
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    /**
     * Lưu thông tin user vào session sau khi đăng nhập thành công.
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true); // Chống session fixation
        $_SESSION['auth_user'] = [
            'id'       => $user['id'],
            'ten'      => $user['ten'],
            'email'    => $user['email'],
            'role'     => $user['role'],
            'avatar'   => $user['avatar'] ?? null,
        ];
    }

    /**
     * Xoá session — đăng xuất
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Kiểm tra đã đăng nhập chưa
     */
    public static function check(): bool
    {
        return isset($_SESSION['auth_user']);
    }

    /**
     * Lấy thông tin user hiện tại (hoặc một field cụ thể)
     */
    public static function user(?string $field = null): mixed
    {
        if (!self::check()) return null;
        if ($field) return $_SESSION['auth_user'][$field] ?? null;
        return $_SESSION['auth_user'];
    }

    /**
     * Lấy role của user hiện tại
     */
    public static function role(): ?string
    {
        return self::user('role');
    }

    /**
     * Kiểm tra user có role cho phép không
     * $roles có thể là string hoặc array
     */
    public static function hasRole(string|array $roles): bool
    {
        $userRole = self::role();
        if (!$userRole) return false;

        $roles = (array) $roles;
        return in_array($userRole, $roles, true);
    }

    /**
     * Bắt buộc đăng nhập — redirect nếu chưa đăng nhập
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
    }

    /**
     * Bắt buộc có role nhất định — redirect nếu không đủ quyền
     */
    public static function requireRole(string|array $roles): void
    {
        self::requireLogin();

        if (!self::hasRole($roles)) {
            http_response_code(403);
            require VIEW_PATH . '/errors/403.php';
            exit;
        }
    }

    // ─── Flash message ────────────────────────────────────────

    /**
     * Đặt flash message (success | error | warning | info)
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Lấy và xoá flash message
     */
    public static function getFlash(string $type): ?string
    {
        $msg = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $msg;
    }

    /**
     * Kiểm tra có flash message không
     */
    public static function hasFlash(string $type): bool
    {
        return isset($_SESSION['flash'][$type]);
    }
}