<?php
require_once __DIR__ . '/../_common.php';

$u = api_login();

ok([
    'id'       => (int)$u['id'],
    'username' => $u['username'],
    'role'     => $u['role'] ?? 'user',
]);