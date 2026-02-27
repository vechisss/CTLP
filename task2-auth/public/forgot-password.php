<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vechisss\Ctlp\Auth\UserAuth;

session_start();

$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['REQUEST_URI'] ?? '/');
    $baseUrl = $scheme . '://' . $host . $path;

    [$ok, $msg] = UserAuth::requestPasswordReset($email, $baseUrl);
    $message = $msg;
    $isError = !$ok;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>找回密码 - CTLP</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 360px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .msg { padding: 0.5rem; margin-bottom: 1rem; border-radius: 4px; }
        .msg.error { background: #fdd; color: #c00; }
        .msg.success { background: #dfd; color: #060; }
        label { display: block; margin-top: 0.75rem; margin-bottom: 0.25rem; }
        input[type="email"] { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 1rem; padding: 0.5rem 1rem; cursor: pointer; border-radius: 4px; border: 1px solid #666; }
        button.primary { background: #07c; color: #fff; border-color: #07c; }
        .row { margin-top: 0.5rem; }
        a { color: #07c; }
    </style>
</head>
<body>
    <h1>找回密码</h1>
    <p>输入注册邮箱，我们将发送重置密码链接到您的邮箱。</p>

    <?php if ($message !== ''): ?>
        <p class="msg <?= $isError ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="forgot-password.php">
        <label for="email">邮箱</label>
        <input id="email" type="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <div class="row">
            <button type="submit" class="primary">发送重置链接</button>
        </div>
    </form>

    <p class="row"><a href="login.php">返回登录</a></p>
</body>
</html>
