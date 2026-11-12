<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($username === '')                      $errors[] = '用户名不能为空';
    if (mb_strlen($username, 'UTF-8') > 20)    $errors[] = '用户名不能超过 20 个字符';
    if ($password === '')                      $errors[] = '密码不能为空';
    if (mb_strlen($password, 'UTF-8') < 6)     $errors[] = '密码至少 6 位';
    if ($password !== $password2)              $errors[] = '两次密码输入不一致';

    if (!$errors) {
        $stmt = mysqli_prepare($connect, 'SELECT id FROM users WHERE username = ?');
        if (!$stmt) {
            die('SQL 错误：' . mysqli_error($connect));
        }
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
            $errors[] = '这个用户名已经被注册了';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($connect,
            'INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        if (!$stmt) {
            die('SQL 错误：' . mysqli_error($connect));
        }
        $role = 'user';
        mysqli_stmt_bind_param($stmt, 'sss', $username, $hash, $role);
        mysqli_stmt_execute($stmt);

        $_SESSION['user'] = [
            'id'       => (int)mysqli_insert_id($connect),
            'username' => $username,
            'role'     => 'user',
        ];

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><meta charset="utf-8"><title>注册</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="wrap">
    <div class="card auth-card">
        <h2>注册</h2>

        <?php foreach ($errors as $e): ?>
            <p class="error"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>

        <form method="post">
            <label>用户名
                <input type="text" name="username" maxlength="20"
                       value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>密码（至少 6 位）
                <input type="password" name="password">
            </label>
            <label>确认密码
                <input type="password" name="password2">
            </label>
            <button class="btn primary" type="submit">注册</button>
        </form>

        <p class="muted">已有账号？<a href="login.php">去登录</a></p>
    </div>
</div>
</body>
</html>