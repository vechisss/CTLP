<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vechisss\Ctlp\Auth\SessionManager;
use Vechisss\Ctlp\Auth\UserAuth;

session_start();

// 已登录则直接去首页（欢迎页）
if (SessionManager::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = $_SESSION['flash_message'] ?? '';
$isSuccess = !empty($_SESSION['flash_success']);
unset($_SESSION['flash_message'], $_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    [$ok, $msg] = UserAuth::attemptLogin($email, $password);
    if ($ok) {
        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    }
    $message = $msg;
    $isSuccess = false;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登录 - CTLP</title>
    <link rel="icon" href="assets/images/icon.png" type="image/png">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">登录</h1>
        <p class="auth-desc">使用邮箱与密码登录 CTLP。</p>

        <?php if ($message !== ''): ?>
            <p class="msg <?= $isSuccess ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="email">邮箱</label>
                <input id="email" type="email" name="email" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <div class="password-wrap">
                    <input id="password" type="password" name="password" required placeholder="请输入密码">
                    <button type="button" class="pwd-toggle" aria-label="显示密码">显示</button>
                </div>
            </div>
            <div class="row">
                <button type="submit" class="btn btn-primary">登录</button>
            </div>
        </form>

        <div class="auth-links">
            <a href="register.php">没有账号？去注册</a>
            <span>·</span>
            <a href="forgot-password.php">忘记密码？</a>
        </div>
    </div>
    <script src="assets/js/auth.js"></script>
</body>
</html>
