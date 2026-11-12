<?php
require_once __DIR__ . '/_common.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $articleId = (int)($_GET['article_id'] ?? 0);
    if ($articleId <= 0) fail('缺少 article_id');

    $stmt = mysqli_prepare($connect,
        'SELECT c.id, c.content, c.created_at, u.username
         FROM article_comments c
         JOIN users u ON u.id = c.user_id
         WHERE c.article_id = ?
         ORDER BY c.id DESC');
    mysqli_stmt_bind_param($stmt, 'i', $articleId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $list = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $row['id'] = (int)$row['id'];
        $list[] = $row;
    }

    ok(['list' => $list, 'total' => count($list)]);
}

if ($method === 'POST') {
    $user = api_login();
    $in   = body();

    $articleId = (int)($in['article_id'] ?? 0);
    $content   = trim($in['content'] ?? '');

    if ($articleId <= 0)                    fail('缺少 article_id');
    if ($content === '')                    fail('留言不能为空');
    if (mb_strlen($content, 'UTF-8') > 500) fail('留言最多 500 字');

    $stmt = mysqli_prepare($connect,
        "SELECT id FROM articles WHERE id = ? AND status = 'published'");
    mysqli_stmt_bind_param($stmt, 'i', $articleId);
    mysqli_stmt_execute($stmt);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
        fail('文章不存在或未发布', 404, 404);
    }

    $stmt = mysqli_prepare($connect,
        'INSERT INTO article_comments (article_id, user_id, content) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'iis', $articleId, $user['id'], $content);
    mysqli_stmt_execute($stmt);

    ok(['id' => (int)mysqli_insert_id($connect)], '留言成功');
}

fail('不支持的请求方法', 405, 405);