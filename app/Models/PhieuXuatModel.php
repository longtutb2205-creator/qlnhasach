<?php

namespace App\Models;

use App\Core\Database;

/**
 * PhieuXuatModel — Phiếu xuất kho
 */
class PhieuXuatModel extends BaseModel
{
    protected string $table = 'phieu_xuat';

    protected array $fillable = [
        'ma_phieu', 'nhan_vien_id', 'ly_do', 'trang_thai', 'ghi_chu'
    ];

    /**
     * Sinh mã phiếu xuất: PX + ngày + STT
     */
    public function sinhMaPhieu(): string
    {
        $prefix = 'PX' . date('Ymd');
        $last = $this->rawOne(
            "SELECT ma_phieu FROM phieu_xuat WHERE ma_phieu LIKE ? ORDER BY id DESC LIMIT 1",
            ["{$prefix}%"]
        );
        $stt = $last ? (int) substr($last['ma_phieu'], -3) + 1 : 1;
        return $prefix . str_pad($stt, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Danh sách phiếu xuất kèm tên nhân viên
     */
    public function allWithRelations(): array
    {
        return $this->raw("
            SELECT px.*, nv.ho_ten AS ten_nhan_vien
            FROM phieu_xuat px
            LEFT JOIN nhan_vien nv ON nv.id = px.nhan_vien_id
            ORDER BY px.id DESC
        ");
    }

    /**
     * Chi tiết phiếu xuất kèm danh sách sách
     */
    public function findWithChiTiet(int $id): array|false
    {
        $px = $this->rawOne("
            SELECT px.*, nv.ho_ten AS ten_nhan_vien
            FROM phieu_xuat px
            LEFT JOIN nhan_vien nv ON nv.id = px.nhan_vien_id
            WHERE px.id = ?
        ", [$id]);

        if (!$px) return false;

        $px['chi_tiet'] = $this->raw("
            SELECT ct.*, s.ten_sach, s.isbn, s.so_luong_ton
            FROM chi_tiet_phieu_xuat ct
            JOIN sach s ON s.id = ct.sach_id
            WHERE ct.phieu_xuat_id = ?
        ", [$id]);

        return $px;
    }

    /**
     * Hoàn tất phiếu xuất → trừ tồn kho
     */
    public function hoanTat(int $id): bool
    {
        try {
            Database::beginTransaction();

            $this->update($id, ['trang_thai' => 'hoan_tat']);

            $chiTiet = $this->raw(
                "SELECT * FROM chi_tiet_phieu_xuat WHERE phieu_xuat_id = ?",
                [$id]
            );
            foreach ($chiTiet as $item) {
                $this->rawExec(
                    "UPDATE sach SET so_luong_ton = so_luong_ton - ? WHERE id = ? AND so_luong_ton >= ?",
                    [$item['so_luong'], $item['sach_id'], $item['so_luong']]
                );
            }

            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollback();
            error_log("[PhieuXuatModel::hoanTat] " . $e->getMessage());
            return false;
        }
    }
}