<?php

namespace App\Models;

/**
 * NhaCungCapModel — Nhà cung cấp sách
 */
class NhaCungCapModel extends BaseModel
{
    protected string $table = 'nha_cung_cap';

    protected array $fillable = [
        'ten', 'dia_chi', 'dien_thoai', 'email', 'nguoi_lien_he'
    ];

    public function timKiem(string $keyword): array
    {
        return $this->search($keyword, ['ten', 'dien_thoai', 'nguoi_lien_he']);
    }

    /**
     * NCC kèm số phiếu nhập đã thực hiện
     */
    public function allWithThongKe(): array
    {
        return $this->raw("
            SELECT ncc.*, COUNT(pn.id) AS so_phieu_nhap,
                   COALESCE(SUM(pn.tong_tien), 0) AS tong_gia_tri
            FROM nha_cung_cap ncc
            LEFT JOIN phieu_nhap pn ON pn.nha_cung_cap_id = ncc.id
                                    AND pn.trang_thai = 'hoan_tat'
            GROUP BY ncc.id
            ORDER BY ncc.ten
        ");
    }
}