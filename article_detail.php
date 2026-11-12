<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$me   = current_user();
$myId = $me['id'] ?? 0;
$id   = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: article_list.php');
    exit;
}

$sql = "SELECT a.*, u.username AS author,
               (SELECT COUNT(*) FROM article_likes    l WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM article_likes   l2 WHERE l2.article_id = a.id AND l2.user_id = ?) AS liked
        FROM articles a
        JOIN users u ON u.id = a.author_id
        WHERE a.id = ? AND a.status = 'published'";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $myId, $id);
mysqli_stmt_execute($stmt);
$a = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$a) {
    http_response_code(404);
    echo '文章不存在或未发布。 <a href="article_list.php">返回列表</a>';
    exit;
}

// 留言列表
$stmt = mysqli_prepare($connect,
    'SELECT c.id, c.content, c.created_at, u.username
     FROM article_comments c JOIN users u ON u.id = c.user_id
     WHERE c.article_id = ? ORDER BY c.id DESC');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$comments = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="logo" href="article_list.php">📄 文章</a>
        <nav>
            <a class="btn ghost" href="article_list.php">← 返回列表</a>
            <?php if ($me): ?>
                <span class="user">👤 <?= htmlspecialchars($me['username'], ENT_QUOTES, 'UTF-8') ?></span>
                <a class="btn ghost" href="logout.php">退出</a>
            <?php else: ?>
                <a class="btn ghost" href="login.php">登录</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="wrap">
    <article class="card">
        <h2 class="art-title"><?= htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="muted"><?= htmlspecialchars($a['author']) ?> · <?= htmlspecialchars($a['created_at']) ?></p>

        <?php if ($a['cover']): ?>
            <img class="art-cover" src="<?= htmlspecialchars($a['cover'], ENT_QUOTES, 'UTF-8') ?>" alt="">
        <?php endif; ?>

        <div class="art-content"><?= nl2br(htmlspecialchars($a['content'], ENT_QUOTES, 'UTF-8')) ?></div>

        <div class="msg-foot" id="like">
            <?php if ($me): ?>
                <form action="article_like.php" method="post" class="like-form">
                    <input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>">
                    <input type="hidden" name="back" value="article_detail.php?id=<?= (int)$a['id'] ?>">
                    <button type="submit" class="like-btn <?= $a['liked'] ? 'liked' : '' ?>">
                        <?= $a['liked'] ? '♥' : '♡' ?> <?= (int)$a['like_count'] ?>
                    </button>
                </form>
            <?php else: ?>
                <span class="like-btn disabled">♡ <?= (int)$a['like_count'] ?></span>
            <?php endif; ?>
        </div>
    </article>

    <section class="card">
        <h3>留言（<?= mysqli_num_rows($comments) ?>）</h3>

        <?php if ($me): ?>
            <form action="article_comment.php" method="post" class="write-form">
                <input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>">
                <input type="hidden" name="back" value="article_detail.php?id=<?= (int)$a['id'] ?>">
                <textarea name="content" rows="3" placeholder="写下你的留言…" maxlength="500"></textarea>
                <button class="btn primary" type="submit">发表留言</button>
            </form>
        <?php else: ?>
            <p class="muted">登录后才能留言。<a href="login.php">去登录</a></p>
        <?php endif; ?>

        <div class="comment-list">
            <?php if (mysqli_num_rows($comments) === 0): ?>
                <p class="muted">还没有留言，来抢沙发。</p>
            <?php else: ?>
                <?php while ($c = mysqli_fetch_assoc($comments)): ?>
                <div class="comment">
                    <div class="msg-head">
                        <strong><?= htmlspecialchars($c['username']) ?></strong>
                        <time><?= htmlspecialchars($c['created_at']) ?></time>
                    </div>
                    <p><?= nl2br(htmlspecialchars($c['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

</body>
</html>