<?php
/**
 * 极简数据库门面（模仿框架里的 Db::）
 * 用法：
 *   require 'db.php';              // 已有连接 $connect
 *   Db::setConnection($connect);
 *   Db::fetchAll('SELECT ... WHERE id = ?', [$id]);
 */
class Db
{
    private static ?mysqli $conn = null;

    /** 注入一个已经建好的 mysqli 连接（复用 db.php 里的 $connect） */
    public static function setConnection(mysqli $conn): void
    {
        self::$conn = $conn;
        mysqli_set_charset(self::$conn, 'utf8mb4');
    }

    public static function conn(): mysqli
    {
        if (self::$conn === null) {
            throw new RuntimeException('数据库未初始化，请先调用 Db::setConnection()');
        }
        return self::$conn;
    }

    /** 执行任意 SQL（带参数），返回 stmt */
    public static function run(string $sql, array $params = []): mysqli_stmt
    {
        $stmt = mysqli_prepare(self::conn(), $sql);
        if (!$stmt) {
            throw new RuntimeException('SQL 准备失败：' . mysqli_error(self::conn()));
        }

        if ($params) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) {
                    $types .= 'i';
                } elseif (is_float($p)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        if (!mysqli_stmt_execute($stmt)) {
            throw new RuntimeException('SQL 执行失败：' . mysqli_stmt_error($stmt));
        }

        return $stmt;
    }

    /** 查多行：Db::fetchAll('SELECT * FROM message WHERE user_id = ?', [$id]) */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $res  = mysqli_stmt_get_result(self::run($sql, $params));
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /** 查一行：Db::fetchOne('SELECT * FROM users WHERE id = ?', [$id]) */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result(self::run($sql, $params)));
        return $row ?: null;
    }

    /** 查单个值：Db::value('SELECT COUNT(*) c FROM likes WHERE message_id = ?', [$id]) */
    public static function value(string $sql, array $params = [])
    {
        $row = self::fetchOne($sql, $params);
        return $row ? array_values($row)[0] : null;
    }

    /** 增删改，返回影响行数 */
    public static function execute(string $sql, array $params = []): int
    {
        return mysqli_stmt_affected_rows(self::run($sql, $params));
    }

    /** 上一条 INSERT 的自增 id */
    public static function lastId(): int
    {
        return (int)mysqli_insert_id(self::conn());
    }
}