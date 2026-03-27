<?php

namespace App\Core;

/**
 * Response — Helper cho redirect và JSON response
 */
class Response
{
    /**
     * Redirect đến URL khác
     *
     * Ví dụ: Response::redirect('/sach')
     *         Response::redirect('/sach', ['success' => 'Thêm thành công'])
     */
    public static function redirect(string $url, array $flash = []): never
    {
        foreach ($flash as $key => $message) {
            $_SESSION['flash'][$key] = $message;
        }

        // Thêm base URL nếu url bắt đầu bằng /
        if (str_starts_with($url, '/')) {
            $base = rtrim(APP_URL, '/');
            $url  = $base . $url;
        }

        header("Location: {$url}");
        exit;
    }

    /**
     * Trả về JSON response
     *
     * Ví dụ: Response::json(['success' => true, 'data' => $data])
     *         Response::json(['error' => 'Not found'], 404)
     */
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Trả về JSON thành công
     */
    public static function success(mixed $data = null, string $message = 'Thành công', int $status = 200): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Trả về JSON lỗi
     */
    public static function error(string $message = 'Có lỗi xảy ra', int $status = 400, mixed $errors = null): never
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    /**
     * Trả về 404
     */
    public static function notFound(string $message = 'Không tìm thấy trang'): never
    {
        http_response_code(404);
        $view = VIEW_PATH . '/errors/404.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo "<h1>404 — {$message}</h1>";
        }
        exit;
    }

    /**
     * Lấy flash message và xóa khỏi session
     *
     * Ví dụ trong View: Response::flash('success')
     */
    public static function flash(string $key): string
    {
        $msg = $_SESSION['flash'][$key] ?? '';
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    /**
     * Kiểm tra có flash message không
     */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }
}