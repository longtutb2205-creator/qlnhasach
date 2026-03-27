<?php

namespace App\Models;

/**
 * GioHangModel — Giỏ hàng tạm thời
 */
class GioHangModel extends BaseModel
{
    protected string $table = 'gio_hang';
    protected array $fillable = ['khach_hang_id', 'session_id'];

    /**
     * Lấy giỏ hàng kèm danh sách sách
     */
    public function findWithChiTiet(int $id): array|false
    {
        $gh = $this->find($id);
        if (!$gh) return false;

        $gh['items'] = $this->raw("
            SELECT ghct.*, s.ten_sach, s.hinh_anh, s.gia_ban, s.so_luong_ton
            FROM gio_hang_chi_tiet ghct
            JOIN sach s ON s.id = ghct.sach_id
            WHERE ghct.gio_hang_id = ?
        ", [$id]);

        $gh['tong_tien'] = array_sum(array_column($gh['items'], 'thanh_tien'));

        return $gh;
    }

    /**
     * Thêm sách vào giỏ (nếu đã có thì cộng số lượng)
     */
    public function themSach(int $gioHangId, int $sachId, int $soLuong, float $gia): bool
    {
        $existing = $this->rawOne(
            "SELECT * FROM gio_hang_chi_tiet WHERE gio_hang_id = ? AND sach_id = ?",
            [$gioHangId, $sachId]
        );

        if ($existing) {
            return $this->rawExec(
                "UPDATE gio_hang_chi_tiet SET so_luong = so_luong + ?, gia = ?
                 WHERE gio_hang_id = ? AND sach_id = ?",
                [$soLuong, $gia, $gioHangId, $sachId]
            ) > 0;
        }

        return $this->rawExec(
            "INSERT INTO gio_hang_chi_tiet (gio_hang_id, sach_id, so_luong, gia)
             VALUES (?, ?, ?, ?)",
            [$gioHangId, $sachId, $soLuong, $gia]
        ) > 0;
    }

    /**
     * Xóa sách khỏi giỏ
     */
    public function xoaSach(int $gioHangId, int $sachId): bool
    {
        return $this->rawExec(
            "DELETE FROM gio_hang_chi_tiet WHERE gio_hang_id = ? AND sach_id = ?",
            [$gioHangId, $sachId]
        ) > 0;
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function xoaToan(int $gioHangId): bool
    {
        return $this->rawExec(
            "DELETE FROM gio_hang_chi_tiet WHERE gio_hang_id = ?",
            [$gioHangId]
        ) > 0;
    }
}