<?php

namespace App\Models;

/**
 * ThanhToanModel — Giao dịch thanh toán
 */
class ThanhToanModel extends BaseModel
{
    protected string $table = 'thanh_toan';

    protected array $fillable = [
        'hoa_don_id', 'so_tien', 'phuong_thuc',
        'trang_thai', 'ma_giao_dich', 'thoi_gian_tt'
    ];

    /**
     * Lấy thanh toán theo hóa đơn
     */
    public function findByHoaDon(int $hoaDonId): array|false
    {
        return $this->findBy('hoa_don_id', $hoaDonId);
    }

    /**
     * Xác nhận thanh toán thành công
     */
    public function xacNhan(int $id, ?string $maGiaoDich = null): bool
    {
        return $this->update($id, [
            'trang_thai'   => 'thanh_cong',
            'ma_giao_dich' => $maGiaoDich,
            'thoi_gian_tt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Thống kê theo phương thức thanh toán
     */
    public function thongKePhuongThuc(string $tuNgay, string $denNgay): array
    {
        return $this->raw("
            SELECT phuong_thuc,
                   COUNT(*) AS so_giao_dich,
                   SUM(so_tien) AS tong_tien
            FROM thanh_toan
            WHERE trang_thai = 'thanh_cong'
              AND DATE(thoi_gian_tt) BETWEEN ? AND ?
            GROUP BY phuong_thuc
        ", [$tuNgay, $denNgay]);
    }
}