<?php

namespace App\Models;

/**
 * TheLoaiModel — Thể loại sách
 */
class TheLoaiModel extends BaseModel
{
    protected string $table = 'the_loai';
    protected array $fillable = ['ten', 'mo_ta'];

    public function allWithSoLuong(): array
    {
        return $this->raw("
            SELECT tl.*, COUNT(s.id) AS so_luong_sach
            FROM the_loai tl
            LEFT JOIN sach s ON s.the_loai_id = tl.id
            GROUP BY tl.id
            ORDER BY tl.ten
        ");
    }
}