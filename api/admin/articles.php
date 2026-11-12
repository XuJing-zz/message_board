<?php
require_once __DIR__ . '/../_common.php';

api_admin();

[$page, $size, $offset] = page_params();

$status  = $_GET['status'] ?? '';
$keyword = trim($_GET['keyword'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($status === 'published' || $status === 'draft') {
    $where[]  = 'a.status = ?';
    $params[] = $status;
    $types   .= 's';
}
if ($keyword !== '') {
    $where[]  = 'a.title LIKE ?';
    $params[] = '%' . $keyword . '%';
    $types   .= 's';
}
$w = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// 总数
$stmt = mysqli_prepare($connect, "SELECT COUNT(*) AS c FROM articles a $w");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

// 列表
$sql = "SELECT a.id, a.title, a.cover, a.status, a.created_at, a.updated_at,
               u.username AS author,
               (SELECT COUNT(*) FROM article_likes    l WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM article_comments c WHERE c.article_id = a.id) AS comment_count
        FROM articles a
        JOIN users u ON u.id = a.author_id
        $w
        ORDER BY a.id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
$bindTypes  = $types . 'ii';
$bindParams = array_merge($params, [$size, $offset]);
mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindParams);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']            = (int)$row['id'];
    $row['like_count']    = (int)$row['like_count'];
    $row['comment_count'] = (int)$row['comment_count'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);