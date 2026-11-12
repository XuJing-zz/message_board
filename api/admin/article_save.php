<?php
require_once __DIR__ . '/../_common.php';

$admin = api_admin();
need_post();

$in      = body();
$id      = (int)($in['id'] ?? 0);
$title   = trim($in['title'] ?? '');
$cover   = trim($in['cover'] ?? '');
$content = trim($in['content'] ?? '');
$status  = $in['status'] ?? 'draft';

if ($title === '')                     fail('标题不能为空');
if (mb_strlen($title, 'UTF-8') > 200)  fail('标题最多 200 字');
if ($content === '')                   fail('正文不能为空');
if (!in_array($status, ['published', 'draft'], true)) fail('状态只能是 published 或 draft');

if ($id > 0) {
    // 修改
    $stmt = mysqli_prepare($connect,
        'UPDATE articles SET title = ?, cover = ?, content = ?, status = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'ssssi', $title, $cover, $content, $status, $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        // 可能内容完全相同，确认文章是否存在
        $chk = mysqli_prepare($connect, 'SELECT id FROM articles WHERE id = ?');
        mysqli_stmt_bind_param($chk, 'i', $id);
        mysqli_stmt_execute($chk);
        if (!mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
            fail('文章不存在', 404, 404);
        }
    }

    ok(['id' => $id], '修改成功');
}

// 新增
$stmt = mysqli_prepare($connect,
    'INSERT INTO articles (author_id, title, cover, content, status) VALUES (?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'issss', $admin['id'], $title, $cover, $content, $status);
mysqli_stmt_execute($stmt);

ok(['id' => (int)mysqli_insert_id($connect)], '新增成功');