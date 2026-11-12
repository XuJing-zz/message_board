<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$id      = (int)($_GET['id'] ?? 0);
$title   = '';
$cover   = '';
$content = '';
$status  = 'draft';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id'] ?? 0);
    $title   = trim($_POST['title'] ?? '');
    $cover   = trim($_POST['cover'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status  = $_POST['status'] ?? 'draft';

    if ($title === '')   $error = '标题不能为空';
    if ($content === '') $error = '正文不能为空';

    if (!$error) {
        if ($id > 0) {
            $stmt = mysqli_prepare($connect,
                'UPDATE articles SET title = ?, cover = ?, content = ?, status = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'ssssi', $title, $cover, $content, $status, $id);
            mysqli_stmt_execute($stmt);
        } else {
            $stmt = mysqli_prepare($connect,
                'INSERT INTO articles (author_id, title, cover, content, status) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'issss', $_SESSION['user']['id'], $title, $cover, $content, $status);
            mysqli_stmt_execute($stmt);
        }
        header('Location: index.php');
        exit;
    }
} elseif ($id > 0) {
    $stmt = mysqli_prepare($connect, 'SELECT * FROM articles WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($row) {
        $title   = $row['title'];
        $cover   = $row['cover'];
        $content = $row['content'];
        $status  = $row['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><meta charset="utf-8"><title><?= $id ? '编辑文章' : '新增文章' ?></title><link rel="stylesheet" href="../style.css"></head>
<body>
<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="logo" href="index.php">🛠 管理后台</a>
        <nav><a class="btn ghost" href="index.php">← 返回列表</a></nav>
    </div>
</header>

<main class="wrap">
    <div class="card">
        <h2><?= $id ? '编辑文章' : '新增文章' ?></h2>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <form method="post" class="write-form">
            <input type="hidden" name="id" value="<?= $id ?>">

            <label>标题
                <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" maxlength="200">
            </label>

            <label>封面地址（可空）
                <input type="text" name="cover" value="<?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" placeholder="https://...">
            </label>

            <label>正文
                <textarea name="content" rows="12"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>状态
                <select name="status">
                    <option value="draft"     <?= $status === 'draft' ? 'selected' : '' ?>>草稿</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>已发布</option>
                </select>
            </label>

            <button class="btn primary" type="submit">保存</button>
        </form>
    </div>
</main>
</body>
</html>