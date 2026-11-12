<?php
function insertMessage(mysqli $connect, int $userId, string $content): int
{
    $stmt = mysqli_prepare($connect,
        'INSERT INTO message (user_id, content) VALUES (?, ?)');

    if (!$stmt) {
        die('准备失败：' . mysqli_error($connect));
    }

    mysqli_stmt_bind_param($stmt, 'is', $userId, $content);

    if (!mysqli_stmt_execute($stmt)) {
        die('执行失败：' . mysqli_stmt_error($stmt));
    }

    return (int)mysqli_insert_id($connect);
}