<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// 删除
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = mysqli_prepare($connect, 'DELETE FROM article_comments WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    header('Location: comments.php');
    exit;
}

$sql = "SELECT c.id, c.content, c.created_at, u.username, a.title AS article_title
        FROM article_comments c
        JOIN users    u ON u.id = c.user_id
        JOIN articles a ON a.id = c.article_id
        ORDER BY c.id DESC LIMIT 100";
$res = mysqli_query($connect, $sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><meta charset="utf-8"><title>留言管理</title><link rel="stylesheet" href="../style.css"></head>
<body>
<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="logo" href="index.php">🛠 管理后台</a>
        <nav>
            <a class="btn ghost" href="index.php">文章管理</a>
            <a class="btn ghost" href="comments.php">留言管理</a>
            <a class="btn ghost" href="../index.php">前台</a>
            <a class="btn ghost" href="logout.php">退出</a>
        </nav>
    </div>
</header>

<main class="wrap">
    <div class="card">
        <h2>留言管理</h2>
        <table class="admin-table">
            <tr>
                <th width="50">ID</th>
                <th width="160">文章</th>
                <th width="90">用户</th>
                <th>内容</th>
                <th width="150">时间</th>
                <th width="60">操作</th>
            </tr>
            <?php if (!$res || mysqli_num_rows($res) === 0): ?>
                <tr><td colspan="6" class="muted">还没有留言</td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?= (int)$row['id'] ?></td>
                    <td><?= htmlspecialchars($row['article_title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8')) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><a class="del" href="comments.php?delete=<?= (int)$row['id'] ?>"
                           onclick="return confirm('确定删除这条留言？')">删除</a></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>
    </div>
</main>
</body>
</html>