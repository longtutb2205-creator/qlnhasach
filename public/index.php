<?php

/**
 * Entry Point — Tất cả request đều đi qua đây
 */

declare(strict_types=1);

// ─── 1. Load config ───────────────────────────────────────────
require_once dirname(__DIR__) . '/config/config.php';

// ─── 2. Autoloader (PSR-4 thủ công, không cần Composer) ──────
spl_autoload_register(function (string $class): void {
    // App\Core\Database  →  app/Core/Database.php
    // App\Controllers\SachController  →  app/Controllers/SachController.php
    // App\Models\SachModel  →  app/Models/SachModel.php
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = ROOT_PATH . '/app/' . $relative . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// ─── 3. Khởi động session ────────────────────────────────────
// Dùng fully-qualified class name thay vì use (tránh lỗi resolve trước autoloader)
\App\Core\Auth::start();

// ─── 4. Khai báo routes ──────────────────────────────────────
$router = new \App\Core\Router();

// Auth
$router->get( '/auth/login',   'AuthController@loginForm');
$router->post('/auth/login',   'AuthController@login');
$router->get( '/auth/logout',  'AuthController@logout');

// Dashboard
$router->get('/',              'DashboardController@index');
$router->get('/dashboard',     'DashboardController@index');

// Sách
$router->get( '/sach',             'SachController@index');
$router->get( '/sach/create',      'SachController@create');
$router->post('/sach/create',      'SachController@store');
$router->get( '/sach/edit/:id',    'SachController@edit');
$router->post('/sach/edit/:id',    'SachController@update');
$router->post('/sach/delete/:id',  'SachController@delete');
$router->get( '/sach/detail/:id',  'SachController@detail');

// Khách hàng
$router->get( '/khach-hang',              'KhachHangController@index');
$router->get( '/khach-hang/create',       'KhachHangController@create');
$router->post('/khach-hang/create',       'KhachHangController@store');
$router->get( '/khach-hang/edit/:id',     'KhachHangController@edit');
$router->post('/khach-hang/edit/:id',     'KhachHangController@update');
$router->post('/khach-hang/delete/:id',   'KhachHangController@delete');

// Nhân viên
$router->get( '/nhan-vien',              'NhanVienController@index');
$router->get( '/nhan-vien/create',       'NhanVienController@create');
$router->post('/nhan-vien/create',       'NhanVienController@store');
$router->get( '/nhan-vien/edit/:id',     'NhanVienController@edit');
$router->post('/nhan-vien/edit/:id',     'NhanVienController@update');
$router->post('/nhan-vien/delete/:id',   'NhanVienController@delete');

// Hóa đơn
$router->get( '/hoa-don',              'HoaDonController@index');
$router->get( '/hoa-don/create',       'HoaDonController@create');
$router->post('/hoa-don/create',       'HoaDonController@store');
$router->get( '/hoa-don/detail/:id',   'HoaDonController@detail');
$router->get( '/hoa-don/print/:id',    'HoaDonController@print');
$router->post('/hoa-don/cancel/:id',   'HoaDonController@cancel');

// Kho
$router->get( '/kho',                  'KhoController@index');
$router->get( '/kho/nhap',             'KhoController@nhap');
$router->post('/kho/nhap',             'KhoController@storeNhap');
$router->get( '/kho/xuat',             'KhoController@xuat');
$router->post('/kho/xuat',             'KhoController@storeXuat');
$router->get( '/kho/kiem-ke',          'KhoController@kiemKe');

// Nhà cung cấp
$router->get( '/nha-cung-cap',             'NhaCungCapController@index');
$router->get( '/nha-cung-cap/create',      'NhaCungCapController@create');
$router->post('/nha-cung-cap/create',      'NhaCungCapController@store');
$router->get( '/nha-cung-cap/edit/:id',    'NhaCungCapController@edit');
$router->post('/nha-cung-cap/edit/:id',    'NhaCungCapController@update');

// Báo cáo
$router->get('/bao-cao/doanh-thu',    'BaoCaoController@doanhThu');
$router->get('/bao-cao/ton-kho',      'BaoCaoController@tonKho');
$router->get('/bao-cao/khach-hang',   'BaoCaoController@khachHang');

// Tài khoản
$router->get( '/tai-khoan',              'TaiKhoanController@index');
$router->get( '/tai-khoan/create',       'TaiKhoanController@create');
$router->post('/tai-khoan/create',       'TaiKhoanController@store');
$router->post('/tai-khoan/lock/:id',     'TaiKhoanController@lock');
$router->post('/tai-khoan/delete/:id',   'TaiKhoanController@delete');

// ─── 5. Chạy router ──────────────────────────────────────────
$router->dispatch();