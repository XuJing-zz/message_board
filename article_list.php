<?php
// 兼容旧链接：首页已改为文章列表
$qs = $_SERVER['QUERY_STRING'] ?? '';
header('Location: index.php' . ($qs !== '' ? '?' . $qs : ''));
exit;