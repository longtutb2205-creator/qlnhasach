<?php

namespace App\Models;

/**
 * ChiTietHoaDonModel — Các dòng sách trong hóa đơn
 */
class ChiTietHoaDonModel extends BaseModel
{
    protected string $table = 'chi_tiet_hoa_don';

    protected array $fillable = [
        'hoa_don_id', 'sach_id', 'so_luong', 'don_gia', 'thanh_tien'
    ];

    /**
     * Lấy chi tiết theo hóa đơn
     */
    public function byHoaDon(int $hoaDonId): array
    {
        return $this->raw("
            SELECT ct.*, s.ten_sach, s.isbn, s.hinh_anh
            FROM chi_tiet_hoa_don ct
            JOIN sach s ON s.id = ct.sach_id
            WHERE ct.hoa_don_id = ?
        ", [$hoaDonId]);
    }

    /**
     * Thêm nhiều dòng cùng lúc (batch insert)
     */
    public function createBatch(int $hoaDonId, array $items): bool
    {
        if (empty($items)) return false;

        $placeholders = implode(', ', array_fill(0, count($items), '(?, ?, ?, ?)'));
        $params = [];
        foreach ($items as $item) {
            $params[] = $hoaDonId;
            $params[] = $item['sach_id'];
            $params[] = $item['so_luong'];
            $params[] = $item['don_gia'];
        }

        return $this->rawExec(
            "INSERT INTO chi_tiet_hoa_don (hoa_don_id, sach_id, so_luong, don_gia)
             VALUES {$placeholders}",
            $params
        ) > 0;
    }

    /**
     * Top sách bán chạy
     */
    public function topSachBanChay(int $limit = 10): array
    {
        return $this->raw("
            SELECT s.id, s.ten_sach, s.hinh_anh,
                   SUM(ct.so_luong)    AS tong_ban,
                   SUM(ct.thanh_tien)  AS tong_doanh_thu
            FROM chi_tiet_hoa_don ct
            JOIN sach s ON s.id = ct.sach_id
            JOIN hoa_don hd ON hd.id = ct.hoa_don_id AND hd.trang_thai = 'hoan_tat'
            GROUP BY s.id
            ORDER BY tong_ban DESC
            LIMIT ?
        ", [$limit]);
    }
}