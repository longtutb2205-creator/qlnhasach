<?php

namespace App\Models;

use App\Core\Database;

/**
 * PhieuNhapModel — Phiếu nhập kho
 */
class PhieuNhapModel extends BaseModel
{
    protected string $table = 'phieu_nhap';

    protected array $fillable = [
        'ma_phieu', 'nha_cung_cap_id', 'nhan_vien_id',
        'tong_tien', 'trang_thai', 'ghi_chu'
    ];

    /**
     * Sinh mã phiếu nhập: PN + ngày + STT
     */
    public function sinhMaPhieu(): string
    {
        $prefix = 'PN' . date('Ymd');
        $last = $this->rawOne(
            "SELECT ma_phieu FROM phieu_nhap WHERE ma_phieu LIKE ? ORDER BY id DESC LIMIT 1",
            ["{$prefix}%"]
        );
        $stt = $last ? (int) substr($last['ma_phieu'], -3) + 1 : 1;
        return $prefix . str_pad($stt, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Danh sách phiếu nhập kèm tên NCC và nhân viên
     */
    public function allWithRelations(): array
    {
        return $this->raw("
            SELECT pn.*, ncc.ten AS ten_ncc, nv.ho_ten AS ten_nhan_vien
            FROM phieu_nhap pn
            LEFT JOIN nha_cung_cap ncc ON ncc.id = pn.nha_cung_cap_id
            LEFT JOIN nhan_vien    nv  ON nv.id  = pn.nhan_vien_id
            ORDER BY pn.id DESC
        ");
    }

    /**
     * Chi tiết phiếu nhập kèm danh sách sách
     */
    public function findWithChiTiet(int $id): array|false
    {
        $pn = $this->rawOne("
            SELECT pn.*, ncc.ten AS ten_ncc, nv.ho_ten AS ten_nhan_vien
            FROM phieu_nhap pn
            LEFT JOIN nha_cung_cap ncc ON ncc.id = pn.nha_cung_cap_id
            LEFT JOIN nhan_vien    nv  ON nv.id  = pn.nhan_vien_id
            WHERE pn.id = ?
        ", [$id]);

        if (!$pn) return false;

        $pn['chi_tiet'] = $this->raw("
            SELECT ct.*, s.ten_sach, s.isbn
            FROM chi_tiet_phieu_nhap ct
            JOIN sach s ON s.id = ct.sach_id
            WHERE ct.phieu_nhap_id = ?
        ", [$id]);

        return $pn;
    }

    /**
     * Hoàn tất phiếu nhập → cập nhật tồn kho
     */
    public function hoanTat(int $id): bool
    {
        try {
            Database::beginTransaction();

            $this->update($id, ['trang_thai' => 'hoan_tat']);

            // Cộng số lượng vào kho
            $chiTiet = $this->raw(
                "SELECT * FROM chi_tiet_phieu_nhap WHERE phieu_nhap_id = ?",
                [$id]
            );
            foreach ($chiTiet as $item) {
                $this->rawExec(
                    "UPDATE sach SET so_luong_ton = so_luong_ton + ? WHERE id = ?",
                    [$item['so_luong'], $item['sach_id']]
                );
            }

            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollback();
            error_log("[PhieuNhapModel::hoanTat] " . $e->getMessage());
            return false;
        }
    }
}