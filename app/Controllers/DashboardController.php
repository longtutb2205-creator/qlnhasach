<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

/**
 * DashboardController — Trang tổng quan
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        Middleware::auth();

        // Sau này có thể lấy thống kê từ Model
        // $sachModel     = new \App\Models\SachModel();
        // $hoaDonModel   = new \App\Models\HoaDonModel();
        // $khachHangModel = new \App\Models\KhachHangModel();

        $this->view('dashboard/index', [
            'title'         => 'Tổng quan',
            // 'tong_sach'     => $sachModel->count(),
            // 'tong_hoa_don'  => $hoaDonModel->count(),
            // 'tong_khach'    => $khachHangModel->count(),
        ]);
    }
}