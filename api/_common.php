<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function ok($data = null, string $msg = 'ok'): void
{
    echo json_encode(['code' => 0, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(string $msg = 'error', int $code = 1, int $http = 200): void
{
    http_response_code($http);
    echo json_encode(['code' => $code, 'msg' => $msg, 'data' => null], JSON_UNESCAPED_UNICODE);
    exit;
}

function body(): array
{
    $raw = file_get_contents('php://input');
    $ct  = $_SERVER['CONTENT_TYPE'] ?? '';
    if ($raw !== '' && str_contains($ct, 'application/json')) {
        $j = json_decode($raw, true);
        return is_array($j) ? $j : [];
    }
    return $_POST;
}

function need_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        fail('请用 POST 请求', 405, 405);
    }
}

/** 必须登录（API 版） */
function api_login(): array
{
    $u = current_user();
    if (!$u) {
        fail('请先登录', 401, 401);
    }
    return $u;
}

/** 必须是管理员 */
function api_admin(): array
{
    $u = api_login();
    if (($u['role'] ?? '') !== 'admin') {
        fail('需要管理员权限', 403, 403);
    }
    return $u;
}

/** 分页参数 */
function page_params(int $defaultSize = 10): array
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $size = (int)($_GET['size'] ?? $defaultSize);
    $size = $size < 1 ? $defaultSize : min(50, $size);
    return [$page, $size, ($page - 1) * $size];
}