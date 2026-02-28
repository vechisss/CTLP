<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Vechisss\Ctlp\Auth\UserAuth;
use Vechisss\Ctlp\Utils\Csrf;

$message = '';
$isError = false;
$step = UserAuth::hasPendingVerification() ? 'verify' : 'form';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
        $message = '请求无效，请重新提交';
        $isError = true;
        $step = isset($_POST['action']) && $_POST['action'] === 'register' ? 'verify' : 'form';
    } else {
        $action = $_POST['action'] ?? '';

    if ($action === 'send_code') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        [$ok, $msg] = UserAuth::requestVerificationCode($email, $password);
        $message = $msg;
        $isError = !$ok;
        if ($ok) {
            header('Location: register.php?sent=1');
            exit;
        }
        $step = 'form';
    } elseif ($action === 'register') {
        $code = $_POST['code'] ?? '';
        [$ok, $msg] = UserAuth::register($code);
        if ($ok) {
            $_SESSION['flash_message'] = '注册成功，请登录';
            $_SESSION['flash_success'] = true;
            header('Location: login.php');
            exit;
        }
        $message = $msg;
        $isError = true;
        $step = 'verify';
    }
    }
}

if (isset($_GET['sent']) && UserAuth::hasPendingVerification()) {
    $message = '验证码已发送到您的邮箱，请填写验证码完成注册';
    $isError = false;
    $step = 'verify';
}

$showCodeStep = $step === 'verify';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>注册 - CTLP</title>
    <link rel="icon" href="assets/images/icon.png" type="image/png">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">注册</h1>
        <p class="auth-desc"><?= $showCodeStep ? '请输入邮箱中收到的验证码。' : '填写邮箱与密码，我们将发送验证码到您的邮箱。' ?></p>

        <?php if ($message !== ''): ?>
            <p class="msg <?= $isError ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="register.php">
            <?= Csrf::field() ?>
            <?php if (!$showCodeStep): ?>
                <div class="form-group">
                    <label for="email">邮箱</label>
                    <input id="email" type="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="your@email.com">
                </div>
                <div class="form-group">
                    <label for="password">密码（至少 8 位，含字母和数字）</label>
                    <div class="password-wrap">
                        <input id="password" type="password" name="password" required placeholder="请设置密码">
                        <button type="button" class="pwd-toggle" aria-label="显示密码">显示</button>
                    </div>
                </div>
                <input type="hidden" name="action" value="send_code">
                <div class="row">
                    <button type="submit" class="btn btn-primary">发送验证码</button>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="code">验证码</label>
                    <input id="code" type="text" name="code" required maxlength="4" pattern="[0-9]{4}"
                           placeholder="4 位数字" autocomplete="one-time-code">
                </div>
                <input type="hidden" name="action" value="register">
                <div class="row">
                    <button type="submit" class="btn btn-primary">完成注册</button>
                </div>
            <?php endif; ?>
        </form>

        <div class="auth-links">
            <a href="login.php">已有账号？去登录</a>
        </div>
    </div>
    <script src="assets/js/auth.js"></script>
</body>
</html>
