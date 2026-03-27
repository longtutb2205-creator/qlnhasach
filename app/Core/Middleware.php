<?php

namespace App\Core;

/**
 * Middleware — Kiểm tra quyền trước khi vào Controller action
 */
class Middleware
{
    /**
     * Yêu cầu đăng nhập
     */
    public static function auth(): void
    {
        if (!Auth::check()) {
            Response::redirect('/auth/login');
        }
    }

    /**
     * Yêu cầu một hoặc nhiều role cụ thể
     * Ví dụ: Middleware::role('quan_ly')
     *         Middleware::role('quan_ly', 'ban_hang')
     */
    public static function role(string ...$roles): void
    {
        self::auth();

        $userRole = Auth::user()['role'] ?? '';

        if (!in_array($userRole, $roles, true)) {
            http_response_code(403);
            // Hiển thị trang 403 nếu có, không thì redirect về dashboard
            $view = VIEW_PATH . '/errors/403.php';
            if (file_exists($view)) {
                require $view;
            } else {
                Response::redirect('/dashboard');
            }
            exit;
        }
    }

    /**
     * Yêu cầu là quản lý
     */
    public static function quanLy(): void
    {
        self::role(ROLE_QUAN_LY);
    }

    /**
     * Yêu cầu là quản lý hoặc bán hàng
     */
    public static function banHang(): void
    {
        self::role(ROLE_QUAN_LY, ROLE_BAN_HANG);
    }

    /**
     * Yêu cầu là quản lý hoặc kho
     */
    public static function kho(): void
    {
        self::role(ROLE_QUAN_LY, ROLE_KHO);
    }
}