<?php

namespace App\Models;

use App\Core\Database;

/**
 * HoaDonModel — Hóa đơn bán hàng
 */
class HoaDonModel extends BaseModel
{
    protected string $table = 'hoa_don';

    protected array $fillable = [
        'ma_hoa_don', 'khach_hang_id', 'nhan_vien_id',
        'tong_tien', 'giam_gia', 'tien_can_tra', 'trang_thai', 'ghi_chu'
    ];

    /**
     * Sinh mã hóa đơn tự động: HD + ngày + số thứ tự
     * VD: HD20260327001
     */
    public function sinhMaHoaDon(): string
    {
        $prefix = 'HD' . date('Ymd');
        $last = $this->rawOne(
            "SELECT ma_hoa_don FROM hoa_don WHERE ma_hoa_don LIKE ? ORDER BY id DESC LIMIT 1",
            ["{$prefix}%"]
        );
        $stt = $last ? (int) substr($last['ma_hoa_don'], -3) + 1 : 1;
        return $prefix . str_pad($stt, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Lấy tất cả hóa đơn kèm tên khách hàng, nhân viên
     */
    public function allWithRelations(): array
    {
        return $this->raw("
            SELECT hd.*,
                   kh.ten  AS ten_khach_hang,
                   nv.ho_ten AS ten_nhan_vien
            FROM hoa_don hd
            LEFT JOIN khach_hang kh ON kh.id = hd.khach_hang_id
            LEFT JOIN nhan_vien  nv ON nv.id = hd.nhan_vien_id
            ORDER BY hd.id DESC
        ");
    }

    /**
     * Chi tiết hóa đơn kèm danh sách sách đã mua
     */
    public function findWithChiTiet(int $id): array|false
    {
        $hd = $this->rawOne("
            SELECT hd.*, kh.ten AS ten_khach_hang, kh.dien_thoai,
                   nv.ho_ten AS ten_nhan_vien
            FROM hoa_don hd
            LEFT JOIN khach_hang kh ON kh.id = hd.khach_hang_id
            LEFT JOIN nhan_vien  nv ON nv.id = hd.nhan_vien_id
            WHERE hd.id = ?
        ", [$id]);

        if (!$hd) return false;

        $hd['chi_tiet'] = $this->raw("
            SELECT ct.*, s.ten_sach, s.isbn, s.hinh_anh
            FROM chi_tiet_hoa_don ct
            JOIN sach s ON s.id = ct.sach_id
            WHERE ct.hoa_don_id = ?
        ", [$id]);

        return $hd;
    }

    /**
     * Phân trang có lọc theo trạng thái
     */
    public function paginateWithFilter(int $page = 1, int $perPage = 15, string $trangThai = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = $trangThai ? 'WHERE hd.trang_thai = ?' : '';
        $params = $trangThai ? [$trangThai] : [];

        $total = (int) ($this->rawOne(
            "SELECT COUNT(*) as total FROM hoa_don hd {$where}", $params
        )['total'] ?? 0);

        $data = $this->raw("
            SELECT hd.*, kh.ten AS ten_khach_hang, nv.ho_ten AS ten_nhan_vien
            FROM hoa_don hd
            LEFT JOIN khach_hang kh ON kh.id = hd.khach_hang_id
            LEFT JOIN nhan_vien  nv ON nv.id = hd.nhan_vien_id
            {$where}
            ORDER BY hd.id DESC LIMIT ? OFFSET ?
        ", [...$params, $perPage, $offset]);

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Doanh thu theo khoảng ngày
     */
    public function doanhThu(string $tuNgay, string $denNgay): array
    {
        return $this->raw("
            SELECT DATE(created_at) AS ngay,
                   COUNT(*)         AS so_hoa_don,
                   SUM(tien_can_tra) AS doanh_thu
            FROM hoa_don
            WHERE trang_thai = 'hoan_tat'
              AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY ngay
        ", [$tuNgay, $denNgay]);
    }

    /**
     * Hủy hóa đơn
     */
    public function huy(int $id): bool
    {
        return $this->update($id, ['trang_thai' => 'huy']);
    }
}