<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vechisss\Ctlp\Auth\UserAuth;

session_start();

$message = '';
$isError = false;
$step = UserAuth::hasPendingVerification() ? 'verify' : 'form';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - CTLP</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 360px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .msg { padding: 0.5rem; margin-bottom: 1rem; border-radius: 4px; }
        .msg.error { background: #fdd; color: #c00; }
        .msg.success { background: #dfd; color: #060; }
        label { display: block; margin-top: 0.75rem; margin-bottom: 0.25rem; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;
        }
        button { margin-top: 1rem; padding: 0.5rem 1rem; cursor: pointer; border-radius: 4px; border: 1px solid #666; }
        button.primary { background: #07c; color: #fff; border-color: #07c; }
        .row { margin-top: 0.5rem; }
        a { color: #07c; }
    </style>
</head>
<body>
    <h1>注册</h1>

    <?php if ($message !== ''): ?>
        <p class="msg <?= $isError ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="register.php">
        <?php if (!$showCodeStep): ?>
            <label for="email">邮箱</label>
            <input id="email" type="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <label for="password">密码（至少 8 位，含字母和数字）</label>
            <input id="password" type="password" name="password" required>
            <input type="hidden" name="action" value="send_code">
            <div class="row">
                <button type="submit" class="primary">发送验证码</button>
            </div>
        <?php else: ?>
            <label for="code">验证码</label>
            <input id="code" type="text" name="code" required maxlength="4" pattern="[0-9]{4}" placeholder="4 位数字"
                   autocomplete="one-time-code">
            <input type="hidden" name="action" value="register">
            <div class="row">
                <button type="submit" class="primary">注册</button>
            </div>
        <?php endif; ?>
    </form>

    <p class="row"><a href="login.php">已有账号？去登录</a></p>
</body>
</html>
