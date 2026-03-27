<?php

namespace App\Models;

/**
 * NhanVienModel — Quản lý nhân viên
 */
class NhanVienModel extends BaseModel
{
    protected string $table = 'nhan_vien';

    protected array $fillable = [
        'user_id', 'ho_ten', 'ngay_sinh', 'gioi_tinh',
        'dien_thoai', 'email', 'dia_chi', 'chuc_vu',
        'ngay_vao_lam', 'luong_co_ban'
    ];

    /**
     * Lấy tất cả nhân viên kèm thông tin tài khoản
     */
    public function allWithUser(): array
    {
        return $this->raw("
            SELECT nv.*, u.email AS email_login, u.role, u.trang_thai AS trang_thai_tk
            FROM nhan_vien nv
            LEFT JOIN users u ON u.id = nv.user_id
            ORDER BY nv.id DESC
        ");
    }

    /**
     * Chi tiết nhân viên kèm tài khoản
     */
    public function findWithUser(int $id): array|false
    {
        return $this->rawOne("
            SELECT nv.*, u.email AS email_login, u.role, u.trang_thai AS trang_thai_tk
            FROM nhan_vien nv
            LEFT JOIN users u ON u.id = nv.user_id
            WHERE nv.id = ?
        ", [$id]);
    }

    /**
     * Tìm kiếm nhân viên
     */
    public function timKiem(string $keyword): array
    {
        return $this->search($keyword, ['ho_ten', 'dien_thoai', 'email', 'chuc_vu']);
    }

    /**
     * Lấy nhân viên theo user_id
     */
    public function findByUserId(int $userId): array|false
    {
        return $this->findBy('user_id', $userId);
    }
}