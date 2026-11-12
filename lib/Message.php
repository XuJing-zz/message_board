<?php
require_once __DIR__ . '/Db.php';

/**
 * 留言模型：所有对 message 表的操作都收在这里
 * 页面/接口只调用模型，不直接写 SQL
 */
class Message
{
    /** 留言列表（带作者名、赞数、我是否赞过） */
    public static function all(int $myId = 0): array
    {
        return Db::fetchAll(
            "SELECT m.id, m.user_id, m.content, m.created_at, u.username,
                    (SELECT COUNT(*) FROM likes l  WHERE l.message_id = m.id) AS like_count,
                    (SELECT COUNT(*) FROM likes l2 WHERE l2.message_id = m.id AND l2.user_id = ?) AS liked
             FROM message m
             JOIN users u ON u.id = m.user_id
             ORDER BY m.id DESC",
            [$myId]
        );
    }

    /** 发一条留言，返回新 id */
    public static function create(int $userId, string $content): int
    {
        Db::execute(
            'INSERT INTO message (user_id, content) VALUES (?, ?)',
            [$userId, $content]
        );
        return Db::lastId();
    }

    /** 删除自己的留言，返回是否真的删到了 */
    public static function delete(int $id, int $userId): bool
    {
        return Db::execute(
            'DELETE FROM message WHERE id = ? AND user_id = ?',
            [$id, $userId]
        ) > 0;
    }

    /** 点赞 / 取消点赞，返回切换后的状态 */
    public static function toggleLike(int $messageId, int $userId): bool
    {
        $exists = Db::value(
            'SELECT id FROM likes WHERE message_id = ? AND user_id = ?',
            [$messageId, $userId]
        );

        if ($exists) {
            Db::execute('DELETE FROM likes WHERE id = ?', [$exists]);
            return false;
        }

        Db::execute(
            'INSERT INTO likes (user_id, message_id) VALUES (?, ?)',
            [$userId, $messageId]
        );
        return true;
    }

    /** 统计 */
    public static function count(): int
    {
        return (int)Db::value('SELECT COUNT(*) FROM message');
    }
}