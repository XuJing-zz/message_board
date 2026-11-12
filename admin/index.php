<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$page   = max(1, (int)($_GET['page'] ?? 1));
$size   = 10;
$offset = ($page - 1) * $size;
$status = $_GET['status'] ?? '';

$where  = [];
$params = [];
$types  = '';
if ($status === 'published' || $status === 'draft') {
    $where[]  = 'a.status = ?';
    $params[] = $status;
    $types   .= 's';
}
$w = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = mysqli_prepare($connect, "SELECT COUNT(*) AS c FROM articles a $w");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];
$pages = max(1, (int)ceil($total / $size));

$sql = "SELECT a.id, a.title, a.status, a.created_at, u.username AS author,
               (SELECT COUNT(*) FROM article_likes    l WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM article_comments c WHERE c.article_id = a.id) AS comment_count
        FROM articles a JOIN users u ON u.id = a.author_id
        $w ORDER BY a.id DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, $types . 'ii', ...array_merge($params, [$size, $offset]));
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><meta charset="utf-8"><title>文章管理</title><link rel="stylesheet" href="../style.css"></head>
<body>
<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="logo" href="index.php">🛠 管理后台</a>
        <nav>
            <a class="btn ghost" href="index.php">文章管理</a>
            <a class="btn ghost" href="comments.php">留言管理</a>
            <a class="btn ghost" href="../index.php">前台</a>
            <span class="user">👤 <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
            <a class="btn ghost" href="logout.php">退出</a>
        </nav>
    </div>
</header>

<main class="wrap">
    <div class="card">
        <div class="admin-head">
            <h2>文章管理（共 <?= $total ?> 篇）</h2>
            <a class="btn primary" href="edit.php">+ 新增文章</a>
        </div>

        <form method="get" class="filter">
            <select name="status">
                <option value="">全部状态</option>
                <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>已发布</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>草稿</option>
            </select>
            <button class="btn ghost" type="submit">筛选</button>
        </form>

        <table class="admin-table">
            <tr>
                <th width="50">ID</th>
                <th>标题</th>
                <th width="80">状态</th>
                <th width="80">作者</th>
                <th width="70">点赞</th>
                <th width="70">留言</th>
                <th width="150">时间</th>
                <th width="120">操作</th>
            </tr>
            <?php if (mysqli_num_rows($res) === 0): ?>
            <tr><td colspan="8" class="muted">还没有文章</td></tr>
            <?php else: ?>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="tag <?= $row['status'] ?>"><?= $row['status'] === 'published' ? '已发布' : '草稿' ?></span></td>
                <td><?= htmlspecialchars($row['author']) ?></td>
                <td><?= (int)$row['like_count'] ?></td>
                <td><?= (int)$row['comment_count'] ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <a class="del" href="edit.php?id=<?= (int)$row['id'] ?>">编辑</a>
                    <a class="del" href="article_delete.php?id=<?= (int)$row['id'] ?>"
                       onclick="return confirm('确定删除这篇？')">删除</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php endif; ?>
        </table>

        <?php if ($pages > 1): ?>
        <div class="pager">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>"
                   href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>