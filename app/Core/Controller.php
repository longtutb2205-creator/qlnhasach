<?php

namespace App\Core;

/**
 * Controller — Base class cho tất cả Controllers
 */
abstract class Controller
{
    /**
     * Render một View file, truyền data vào
     *
     * @param string $view   Đường dẫn tương đối trong Views/, dùng dấu chấm
     *                       Ví dụ: 'sach.index'  → Views/sach/index.php
     *                               'layouts.header' → Views/layouts/header.php
     * @param array  $data   Dữ liệu sẽ được extract thành biến trong View
     * @param bool   $layout Có wrap trong layout (header/footer) không
     */
    protected function view(string $view, array $data = [], bool $layout = true): void
    {
        // Chuyển dot-notation sang đường dẫn file
        $viewPath = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            die("View không tồn tại: {$viewPath}");
        }

        // extract array thành biến, tránh ghi đè biến nội bộ
        extract($data, EXTR_PREFIX_SAME, 'data');

        if ($layout) {
            require VIEW_PATH . '/layouts/header.php';
            require $viewPath;
            require VIEW_PATH . '/layouts/footer.php';
        } else {
            require $viewPath;
        }
    }

    /**
     * Trả về JSON response (dùng cho AJAX)
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect đến URL khác
     */
    protected function redirect(string $path): void
    {
        // Nếu path bắt đầu bằng http thì redirect thẳng
        if (str_starts_with($path, 'http')) {
            header("Location: {$path}");
        } else {
            header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
        }
        exit;
    }

    /**
     * Lấy dữ liệu từ POST, có sanitize cơ bản
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    /**
     * Kiểm tra request method
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Lấy tham số từ URL (GET param)
     */
    protected function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Shortcut: bắt buộc đăng nhập
     */
    protected function requireLogin(): void
    {
        Auth::requireLogin();
    }

    /**
     * Shortcut: bắt buộc có role
     */
    protected function requireRole(string|array $roles): void
    {
        Auth::requireRole($roles);
    }
}