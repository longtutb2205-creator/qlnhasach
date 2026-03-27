<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database — PDO Singleton
 * Dùng chung 1 kết nối trong toàn bộ request.
 */
class Database
{
    private static ?PDO $instance = null;

    // Không cho khởi tạo từ bên ngoài
    private function __construct() {}
    private function __clone() {}

    /**
     * Trả về instance PDO duy nhất.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::connect();
        }
        return self::$instance;
    }

    /**
     * Tạo kết nối PDO với cấu hình từ config.php
     */
    private static function connect(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST, DB_PORT, DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
        ];

        try {
            return new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die('<b>Lỗi kết nối database:</b> ' . $e->getMessage());
            }
            die('Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.');
        }
    }

    /**
     * Shortcut: chuẩn bị và thực thi câu query, trả về PDOStatement.
     * Dùng trong BaseModel.
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Bắt đầu transaction
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }

    /**
     * Lấy ID của bản ghi vừa INSERT
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
}