<?php

namespace App\Models;

/**
 * KhachHangModel — Quản lý khách hàng
 */
class KhachHangModel extends BaseModel
{
    protected string $table = 'khach_hang';

    protected array $fillable = [
        'ten', 'dien_thoai', 'email', 'dia_chi', 'loai', 'diem_tich_luy'
    ];

    /**
     * Tìm kiếm khách hàng
     */
    public function timKiem(string $keyword): array
    {
        return $this->search($keyword, ['ten', 'dien_thoai', 'email']);
    }

    /**
     * Lấy khách hàng kèm tổng số hóa đơn & tổng chi tiêu
     */
    public function allWithThongKe(): array
    {
        return $this->raw("
            SELECT kh.*,
                   COUNT(hd.id)          AS tong_hoa_don,
                   COALESCE(SUM(hd.tien_can_tra), 0) AS tong_chi_tieu
            FROM khach_hang kh
            LEFT JOIN hoa_don hd ON hd.khach_hang_id = kh.id AND hd.trang_thai = 'hoan_tat'
            GROUP BY kh.id
            ORDER BY tong_chi_tieu DESC
        ");
    }

    /**
     * Cộng điểm tích lũy
     */
    public function congDiem(int $id, int $diem): bool
    {
        return $this->rawExec(
            "UPDATE khach_hang SET diem_tich_luy = diem_tich_luy + ? WHERE id = ?",
            [$diem, $id]
        ) > 0;
    }

    /**
     * Cập nhật loại khách hàng tự động theo điểm
     */
    public function capNhatLoai(int $id): void
    {
        $kh = $this->find($id);
        if (!$kh) return;

        $loai = match(true) {
            $kh['diem_tich_luy'] >= 500 => 'vip',
            $kh['diem_tich_luy'] >= 100 => 'thuong_xuyen',
            default                     => 'moi',
        };
        $this->update($id, ['loai' => $loai]);
    }

    /**
     * Lịch sử mua hàng của khách
     */
    public function lichSuMuaHang(int $khachHangId): array
    {
        return $this->raw("
            SELECT hd.*, hd.ma_hoa_don,
                   COUNT(ct.id) AS so_quyen_sach
            FROM hoa_don hd
            LEFT JOIN chi_tiet_hoa_don ct ON ct.hoa_don_id = hd.id
            WHERE hd.khach_hang_id = ?
            GROUP BY hd.id
            ORDER BY hd.created_at DESC
        ", [$khachHangId]);
    }

    /**
     * Lấy theo loại
     */
    public function byLoai(string $loai): array
    {
        return $this->where('loai', $loai, 'ten');
    }
}