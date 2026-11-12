<?php
// 演示：页面 → 模型(Message) → 数据层(Db)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/Db.php';
require_once __DIR__ . '/lib/Message.php';

// 把已有连接注入 Db
Db::setConnection($connect);

$me    = current_user();
$myId  = $me['id'] ?? 0;
$list  = Message::all($myId);
$total = Message::count();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Db 演示</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="wrap">
    <h2>Db 类演示（共 <?= $total ?> 条留言）</h2>
    <p class="muted">这一页没有写一句 SQL，全部通过 Message::all() → Db::fetchAll() 完成。</p>

    <?php if (!$list): ?>
        <p class="muted">还没有留言。</p>
    <?php else: ?>
        <?php foreach ($list as $row): ?>
            <article class="card msg">
                <div class="msg-head">
                    <strong><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <time><?= htmlspecialchars($row['created_at']) ?></time>
                </div>
                <p class="msg-body"><?= nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                <div class="msg-foot"><span class="like-btn disabled">♡ <?= (int)$row['like_count'] ?></span></div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="index.php">← 回首页</a></p>
</main>
</body>
</html>