<?php
require_once __DIR__ . '/../_common.php';

api_admin();

[$page, $size, $offset] = page_params(20);

$articleId = (int)($_GET['article_id'] ?? 0);
$where  = $articleId > 0 ? 'WHERE c.article_id = ?' : '';
$params = $articleId > 0 ? [$articleId] : [];
$types  = $articleId > 0 ? 'i' : '';

$stmt = mysqli_prepare($connect, "SELECT COUNT(*) AS c FROM article_comments c $where");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

$sql = "SELECT c.id, c.article_id, c.content, c.created_at,
               u.username, a.title AS article_title
        FROM article_comments c
        JOIN users    u ON u.id = c.user_id
        JOIN articles a ON a.id = c.article_id
        $where
        ORDER BY c.id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
$bindTypes  = $types . 'ii';
$bindParams = array_merge($params, [$size, $offset]);
mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindParams);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']         = (int)$row['id'];
    $row['article_id'] = (int)$row['article_id'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);