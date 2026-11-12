<?php
// 复制本文件为 db.php，然后填入你自己的数据库配置
$connect = mysqli_connect('127.0.0.1', '你的用户名', '你的密码', 'message_board', 3306);

if (!$connect) {
    die('数据库连接失败：' . mysqli_connect_error());
}

mysqli_set_charset($connect, 'utf8mb4');