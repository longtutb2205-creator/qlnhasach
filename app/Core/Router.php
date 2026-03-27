<?php

namespace App\Core;

/**
 * Router — Điều hướng URL đến đúng Controller::action
 *
 * URL pattern:  /module/action/param1/param2
 * Ví dụ:        /sach/index          → SachController::index()
 *               /sach/edit/5         → SachController::edit(5)
 *               /hoa-don/create      → HoaDonController::create()
 */
class Router
{
    private array $routes = [];

    // ─── Đăng ký route ────────────────────────────────────────

    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void
    {
        // Chuyển :param thành regex group
        $pattern = preg_replace('#:([a-zA-Z0-9_]+)#', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = compact('method', 'pattern', 'handler', 'path');
    }

    // ─── Dispatch ─────────────────────────────────────────────

    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Bỏ base path (thư mục con nếu có)
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $uri      = '/' . ltrim(substr($requestUri, strlen($basePath)), '/');
        $uri      = $uri === '' ? '/' : $uri;

        // Tìm route khớp
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod && !($requestMethod === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === $route['method'])) {
                if ($route['method'] !== $requestMethod) continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // bỏ full match
                $this->callHandler($route['handler'], $matches);
                return;
            }
        }

        // Không tìm thấy route → 404
        $this->notFound();
    }

    // ─── Gọi Controller::action ───────────────────────────────

    private function callHandler(string $handler, array $params): void
    {
        [$controllerName, $action] = explode('@', $handler);

        $class = "App\\Controllers\\{$controllerName}";

        if (!class_exists($class)) {
            $this->notFound("Controller [{$controllerName}] không tồn tại.");
            return;
        }

        $controller = new $class();

        if (!method_exists($controller, $action)) {
            $this->notFound("Action [{$action}] không tồn tại trong {$controllerName}.");
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    // ─── Fallback ─────────────────────────────────────────────

    private function notFound(string $message = 'Trang không tồn tại.'): void
    {
        http_response_code(404);
        echo "<h2>404 - {$message}</h2>";
    }
}