<?php

namespace App\Models;

/**
 * TacGiaModel — Tác giả sách
 */
class TacGiaModel extends BaseModel
{
    protected string $table = 'tac_gia';
    protected array $fillable = ['ten', 'quoc_tich', 'tieu_su'];

    public function allWithSoLuong(): array
    {
        return $this->raw("
            SELECT tg.*, COUNT(s.id) AS so_luong_sach
            FROM tac_gia tg
            LEFT JOIN sach s ON s.tac_gia_id = tg.id
            GROUP BY tg.id
            ORDER BY tg.ten
        ");
    }

    public function timKiem(string $keyword): array
    {
        return $this->search($keyword, ['ten', 'quoc_tich']);
    }
}