<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$me   = current_user();
$myId = $me['id'] ?? 0;

$stmt = mysqli_prepare($connect, "
    SELECT m.id, m.user_id, m.content, m.created_at, u.username,
           (SELECT COUNT(*) FROM likes l  WHERE l.message_id = m.id) AS like_count,
           (SELECT COUNT(*) FROM likes l2 WHERE l2.message_id = m.id AND l2.user_id = ?) AS liked
    FROM message m
    JOIN users u ON u.id = m.user_id
    ORDER BY m.id DESC
");
if (!$stmt) {
    die('SQL 错误：' . mysqli_error($connect));
}
mysqli_stmt_bind_param($stmt, 'i', $myId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>留言板</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="logo" href="index.php">💬 留言板</a>
        <nav>
                <a class="btn ghost" href="article_list.php">文章</a>
            <?php if ($me): ?>
                <span class="user">👤 <?= htmlspecialchars($me['username'], ENT_QUOTES, 'UTF-8') ?></span>
                <a class="btn ghost" href="logout.php">退出</a>
            <?php else: ?>
                <a class="btn ghost" href="login.php">登录</a>
                <a class="btn primary" href="register.php">注册</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="wrap">

    <?php if ($me): ?>
    <section class="card">
        <h2>写留言</h2>
        <form action="save.php" method="post" class="write-form">
            <textarea name="content" rows="4" placeholder="说点什么…" maxlength="500"></textarea>
            <button class="btn primary" type="submit">发布</button>
        </form>
    </section>
    <?php else: ?>
    <section class="card login-tip">
        <p>登录后才能发留言和点赞</p>
        <a class="btn primary" href="login.php">去登录</a>
    </section>
    <?php endif; ?>

    <section class="list">
        <h2>全部留言</h2>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <p class="muted">还没有留言，来写第一条吧。</p>
        <?php else: ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <article class="card msg" id="msg<?= (int)$row['id'] ?>">
                <div class="msg-head">
                    <span class="avatar"><?= htmlspecialchars(mb_substr($row['username'], 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <time><?= htmlspecialchars($row['created_at']) ?></time>
                </div>

                <p class="msg-body"><?= nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8')) ?></p>

                <div class="msg-foot">
                    <?php if ($me): ?>
                        <form action="like.php" method="post" class="like-form">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" class="like-btn <?= $row['liked'] ? 'liked' : '' ?>">
                                <?= $row['liked'] ? '♥' : '♡' ?> <?= (int)$row['like_count'] ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="like-btn disabled">♡ <?= (int)$row['like_count'] ?></span>
                    <?php endif; ?>

                    <?php if ($me && (int)$row['user_id'] === (int)$me['id']): ?>
                        <a class="del" href="delete.php?id=<?= (int)$row['id'] ?>"
                           onclick="return confirm('确定删除这条留言？')">删除</a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endwhile; ?>
        <?php endif; ?>
    </section>

</main>

</body>
</html>