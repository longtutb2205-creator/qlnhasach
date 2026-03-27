<?php

namespace App\Models;

use App\Core\Database;
use PDOException;

/**
 * BaseModel — Lớp cha cho tất cả Models
 * Cung cấp CRUD dùng chung qua PDO
 */
abstract class BaseModel
{
    // Tên bảng trong DB — override ở class con
    protected string $table = '';

    // Khóa chính — mặc định 'id'
    protected string $primaryKey = 'id';

    // Các cột được phép ghi (mass assignment) — override ở class con
    protected array $fillable = [];

    // ─── SELECT ───────────────────────────────────────────────

    /**
     * Lấy tất cả bản ghi
     */
    public function all(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        return Database::query(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$dir}"
        )->fetchAll();
    }

    /**
     * Lấy một bản ghi theo ID
     */
    public function find(int $id): array|false
    {
        return Database::query(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        )->fetch();
    }

    /**
     * Lấy một bản ghi theo điều kiện
     */
    public function findBy(string $column, mixed $value): array|false
    {
        return Database::query(
            "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1",
            [$value]
        )->fetch();
    }

    /**
     * Lấy nhiều bản ghi theo điều kiện
     */
    public function where(string $column, mixed $value, string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        return Database::query(
            "SELECT * FROM {$this->table} WHERE {$column} = ? ORDER BY {$orderBy} {$dir}",
            [$value]
        )->fetchAll();
    }

    /**
     * Tìm kiếm theo từ khóa trên nhiều cột
     */
    public function search(string $keyword, array $columns): array
    {
        if (empty($columns)) return [];

        $conditions = implode(' OR ', array_map(fn($col) => "{$col} LIKE ?", $columns));
        $params     = array_fill(0, count($columns), "%{$keyword}%");

        return Database::query(
            "SELECT * FROM {$this->table} WHERE {$conditions}",
            $params
        )->fetchAll();
    }

    /**
     * Đếm tổng số bản ghi
     */
    public function count(string $column = '*', mixed $value = null): int
    {
        if ($value !== null) {
            $result = Database::query(
                "SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} = ?",
                [$value]
            )->fetch();
        } else {
            $result = Database::query(
                "SELECT COUNT(*) as total FROM {$this->table}"
            )->fetch();
        }
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Phân trang
     */
    public function paginate(int $page = 1, int $perPage = 15, string $orderBy = 'id', string $dir = 'DESC'): array
    {
        $dir    = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;
        $total  = $this->count();
        $data   = Database::query(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$dir} LIMIT ? OFFSET ?",
            [$perPage, $offset]
        )->fetchAll();

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    // ─── INSERT ───────────────────────────────────────────────

    /**
     * Thêm bản ghi mới — chỉ lấy các cột trong $fillable
     */
    public function create(array $data): int|false
    {
        $data = $this->filterFillable($data);
        if (empty($data)) return false;

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        try {
            Database::query(
                "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
                array_values($data)
            );
            return (int) Database::lastInsertId();
        } catch (PDOException $e) {
            $this->logError(__METHOD__, $e);
            return false;
        }
    }

    // ─── UPDATE ───────────────────────────────────────────────

    /**
     * Cập nhật bản ghi theo ID
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        if (empty($data)) return false;

        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $params = [...array_values($data), $id];

        try {
            $stmt = Database::query(
                "UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = ?",
                $params
            );
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logError(__METHOD__, $e);
            return false;
        }
    }

    // ─── DELETE ───────────────────────────────────────────────

    /**
     * Xóa bản ghi theo ID
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = Database::query(
                "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
                [$id]
            );
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logError(__METHOD__, $e);
            return false;
        }
    }

    // ─── Raw Query ────────────────────────────────────────────

    /**
     * Thực thi câu query tùy ý, trả về mảng kết quả
     */
    protected function raw(string $sql, array $params = []): array
    {
        return Database::query($sql, $params)->fetchAll();
    }

    /**
     * Thực thi câu query tùy ý, trả về 1 dòng
     */
    protected function rawOne(string $sql, array $params = []): array|false
    {
        return Database::query($sql, $params)->fetch();
    }

    /**
     * Thực thi INSERT/UPDATE/DELETE, trả về số dòng ảnh hưởng
     */
    protected function rawExec(string $sql, array $params = []): int
    {
        return Database::query($sql, $params)->rowCount();
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Lọc chỉ giữ lại các cột trong $fillable
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Ghi lỗi ra error_log
     */
    protected function logError(string $method, PDOException $e): void
    {
        error_log("[{$method}] DB Error: " . $e->getMessage());
    }
}