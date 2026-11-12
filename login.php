<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = '用户名和密码不能为空';
    } else {
        $stmt = mysqli_prepare($connect,
            'SELECT id, username, password FROM users WHERE username = ?');
        if (!$stmt) {
            die('SQL 错误：' . mysqli_error($connect));
        }
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'       => (int)$user['id'],
                'username' => $user['username'],
            ];
            header('Location: index.php');
            exit;
        }

        $errors[] = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><meta charset="utf-8"><title>登录</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="wrap">
    <div class="card auth-card">
        <h2>登录</h2>

        <?php foreach ($errors as $e): ?>
            <p class="error"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>

        <form method="post">
            <label>用户名
                <input type="text" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>密码
                <input type="password" name="password">
            </label>
            <button class="btn primary" type="submit">登录</button>
        </form>

        <p class="muted">还没有账号？<a href="register.php">去注册</a></p>
    </div>
</div>
</body>
</html>