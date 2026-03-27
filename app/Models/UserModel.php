<?php

namespace App\Models;

/**
 * UserModel — Tài khoản người dùng & phân quyền
 */
class UserModel extends BaseModel
{
    protected string $table = 'users';

    protected array $fillable = [
        'ten', 'email', 'mat_khau', 'role', 'trang_thai', 'avatar'
    ];

    /**
     * Tìm user theo email (dùng cho đăng nhập)
     */
    public function findByEmail(string $email): array|false
    {
        return $this->findBy('email', $email);
    }

    /**
     * Lấy danh sách user kèm thông tin nhân viên liên kết
     */
    public function allWithNhanVien(): array
    {
        return $this->raw("
            SELECT u.*, nv.ho_ten, nv.chuc_vu
            FROM users u
            LEFT JOIN nhan_vien nv ON nv.user_id = u.id
            ORDER BY u.id DESC
        ");
    }

    /**
     * Khóa / mở khóa tài khoản
     */
    public function setTrangThai(int $id, string $trangThai): bool
    {
        return $this->update($id, ['trang_thai' => $trangThai]);
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword(int $id, string $hashedPassword): bool
    {
        return $this->update($id, ['mat_khau' => $hashedPassword]);
    }

    /**
     * Lấy user theo role
     */
    public function byRole(string $role): array
    {
        return $this->where('role', $role, 'ten');
    }
}