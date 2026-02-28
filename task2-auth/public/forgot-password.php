<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Vechisss\Ctlp\Auth\UserAuth;
use Vechisss\Ctlp\Utils\Csrf;

$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
        $message = '请求无效，请重新提交';
        $isError = true;
    } else {
        $email = $_POST['email'] ?? '';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['REQUEST_URI'] ?? '/');
        $baseUrl = $scheme . '://' . $host . $path;

        [$ok, $msg] = UserAuth::requestPasswordReset($email, $baseUrl);
        $message = $msg;
        $isError = !$ok;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>找回密码 - CTLP</title>
    <link rel="icon" href="assets/images/icon.png" type="image/png">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">找回密码</h1>
        <p class="auth-desc">输入注册邮箱，我们将发送重置链接到您的邮箱。</p>

        <?php if ($message !== ''): ?>
            <p class="msg <?= $isError ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="forgot-password.php">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="email">邮箱</label>
                <input id="email" type="email" name="email" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="your@email.com">
            </div>
            <div class="row">
                <button type="submit" class="btn btn-primary">发送重置链接</button>
            </div>
        </form>

        <div class="auth-links">
            <a href="login.php">返回登录</a>
        </div>
    </div>
</body>
</html>
