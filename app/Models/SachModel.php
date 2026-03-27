<?php

namespace App\Models;

/**
 * SachModel — Quản lý sách
 */
class SachModel extends BaseModel
{
    protected string $table = 'sach';

    protected array $fillable = [
        'isbn', 'ten_sach', 'tac_gia_id', 'the_loai_id', 'nxb_id',
        'nam_xuat_ban', 'gia_ban', 'gia_nhap', 'so_luong_ton',
        'mo_ta', 'hinh_anh', 'trang_thai'
    ];

    /**
     * Lấy tất cả sách kèm tên tác giả, thể loại, NXB
     */
    public function allWithRelations(): array
    {
        return $this->raw("
            SELECT s.*,
                   tg.ten  AS ten_tac_gia,
                   tl.ten  AS ten_the_loai,
                   nxb.ten AS ten_nxb
            FROM sach s
            LEFT JOIN tac_gia      tg  ON tg.id  = s.tac_gia_id
            LEFT JOIN the_loai     tl  ON tl.id  = s.the_loai_id
            LEFT JOIN nha_xuat_ban nxb ON nxb.id = s.nxb_id
            ORDER BY s.id DESC
        ");
    }

    /**
     * Chi tiết 1 sách kèm relations
     */
    public function findWithRelations(int $id): array|false
    {
        return $this->rawOne("
            SELECT s.*,
                   tg.ten  AS ten_tac_gia,
                   tl.ten  AS ten_the_loai,
                   nxb.ten AS ten_nxb
            FROM sach s
            LEFT JOIN tac_gia      tg  ON tg.id  = s.tac_gia_id
            LEFT JOIN the_loai     tl  ON tl.id  = s.the_loai_id
            LEFT JOIN nha_xuat_ban nxb ON nxb.id = s.nxb_id
            WHERE s.id = ?
        ", [$id]);
    }

    /**
     * Tìm kiếm sách theo từ khóa
     */
    public function timKiem(string $keyword): array
    {
        return $this->raw("
            SELECT s.*, tg.ten AS ten_tac_gia, tl.ten AS ten_the_loai
            FROM sach s
            LEFT JOIN tac_gia  tg ON tg.id = s.tac_gia_id
            LEFT JOIN the_loai tl ON tl.id = s.the_loai_id
            WHERE s.ten_sach  LIKE ?
               OR s.isbn      LIKE ?
               OR tg.ten      LIKE ?
            ORDER BY s.ten_sach
        ", ["%{$keyword}%", "%{$keyword}%", "%{$keyword}%"]);
    }

    /**
     * Sách sắp hết hàng (tồn kho <= ngưỡng)
     */
    public function sắpHetHang(int $nguong = 5): array
    {
        return $this->raw("
            SELECT * FROM sach
            WHERE so_luong_ton <= ? AND trang_thai != 'ngung_ban'
            ORDER BY so_luong_ton ASC
        ", [$nguong]);
    }

    /**
     * Cập nhật tồn kho (cộng/trừ)
     */
    public function capNhatTonKho(int $id, int $soLuong, string $loai = 'cong'): bool
    {
        $op = $loai === 'tru' ? '-' : '+';
        return $this->rawExec(
            "UPDATE sach SET so_luong_ton = so_luong_ton {$op} ? WHERE id = ?",
            [$soLuong, $id]
        ) > 0;
    }

    /**
     * Lấy sách theo thể loại
     */
    public function byTheLoai(int $theLoaiId): array
    {
        return $this->where('the_loai_id', $theLoaiId, 'ten_sach');
    }

    /**
     * Phân trang kèm relations
     */
    public function paginateWithRelations(int $page = 1, int $perPage = 15, string $keyword = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = '';
        $params = [];

        if ($keyword !== '') {
            $where  = "WHERE s.ten_sach LIKE ? OR s.isbn LIKE ? OR tg.ten LIKE ?";
            $params = ["%{$keyword}%", "%{$keyword}%", "%{$keyword}%"];
        }

        $countSql = "SELECT COUNT(*) as total FROM sach s
                     LEFT JOIN tac_gia tg ON tg.id = s.tac_gia_id {$where}";
        $total = (int) ($this->rawOne($countSql, $params)['total'] ?? 0);

        $data = $this->raw("
            SELECT s.*, tg.ten AS ten_tac_gia, tl.ten AS ten_the_loai, nxb.ten AS ten_nxb
            FROM sach s
            LEFT JOIN tac_gia      tg  ON tg.id  = s.tac_gia_id
            LEFT JOIN the_loai     tl  ON tl.id  = s.the_loai_id
            LEFT JOIN nha_xuat_ban nxb ON nxb.id = s.nxb_id
            {$where}
            ORDER BY s.id DESC
            LIMIT ? OFFSET ?
        ", [...$params, $perPage, $offset]);

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}