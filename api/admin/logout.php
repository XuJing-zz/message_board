<?php
require_once __DIR__ . '/../_common.php';

$_SESSION = [];
session_destroy();

ok(null, '已退出登录');