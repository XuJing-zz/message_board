<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$me   = current_user();
$page = max(1, (int)($_GET['page'] ?? 1));
$size = 6;
$offset = ($page - 1) * $size;

$stmt = mysqli_prepare($connect, "SELECT COUNT(*) AS c FROM articles WHERE status = 'published'");
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];
$pages = max(1, (int)ceil($total / $size));

$sql = "SELECT a.id, a.title, a.cover, a.created_at, u.username AS author,
               (SELECT COUNT(*) FROM article_likes    l WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM article_comments c WHERE c.article_id = a.id) AS comment_count
        FROM articles a
        JOIN users u ON u.id = a.author_id
        WHERE a.status = 'published'
        ORDER BY a.id DESC
        LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $size, $offset);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>文章</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="logo" href="article_list.php">📄 文章</a>
        <nav>
            <a class="btn ghost" href="index.php">留言板</a>
            <?php if ($me): ?>
                <span class="user">👤 <?= htmlspecialchars($me['username'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (($me['role'] ?? '') === 'admin'): ?><a class="btn ghost" href="admin/index.php">管理后台</a><?php endif; ?>
                <a class="btn ghost" href="logout.php">退出</a>
            <?php else: ?>
                <a class="btn ghost" href="login.php">登录</a>
                <a class="btn primary" href="register.php">注册</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="wrap">
    <h2>全部文章（共 <?= $total ?> 篇）</h2>

    <?php if (mysqli_num_rows($res) === 0): ?>
        <div class="card"><p class="muted">还没有已发布的文章。</p></div>
    <?php else: ?>
        <?php while ($a = mysqli_fetch_assoc($res)): ?>
        <article class="card">
            <h3 class="art-title">
                <a href="article_detail.php?id=<?= (int)$a['id'] ?>">
                    <?= htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </h3>
            <?php if ($a['cover']): ?>
                <img class="art-cover" src="<?= htmlspecialchars($a['cover'], ENT_QUOTES, 'UTF-8') ?>" alt="">
            <?php endif; ?>
            <div class="msg-foot">
                <span class="muted"><?= htmlspecialchars($a['author']) ?> · <?= htmlspecialchars($a['created_at']) ?></span>
                <span class="like-btn disabled">♡ <?= (int)$a['like_count'] ?></span>
                <span class="like-btn disabled">💬 <?= (int)$a['comment_count'] ?></span>
                <a class="del" href="article_detail.php?id=<?= (int)$a['id'] ?>">阅读全文 →</a>
            </div>
        </article>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
    <div class="pager">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a class="<?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</main>

</body>
</html>