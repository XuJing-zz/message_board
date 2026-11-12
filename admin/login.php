<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($connect,
        'SELECT id, username, password, role FROM users WHERE username = ?');
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] !== 'admin') {
            $error = '该账号不是管理员';
        } else {
            $_SESSION['user'] = [
                'id'       => (int)$user['id'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ];
            header('Location: index.php');
            exit;
        }
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><meta charset="utf-8"><title>后台登录</title><link rel="stylesheet" href="../style.css"></head>
<body>
<div class="wrap">
    <div class="card auth-card">
        <h2>后台登录</h2>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <form method="post">
            <label>管理员账号
                <input type="text" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>密码
                <input type="password" name="password">
            </label>
            <button class="btn primary" type="submit">登录</button>
        </form>
        <p class="muted">默认账号 admin / 123456</p>
        <p class="muted"><a href="../index.php">← 回到前台</a></p>
    </div>
</div>
</body>
</html>