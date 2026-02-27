<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vechisss\Ctlp\Auth\UserAuth;

session_start();

$token = trim($_GET['token'] ?? '');
$valid = $token !== '' && UserAuth::validateResetToken($token) !== null;

$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($password !== $password2) {
        $message = '两次输入的密码不一致';
        $isError = true;
    } else {
        [$ok, $msg] = UserAuth::resetPassword($token, $password);
        if ($ok) {
            $_SESSION['flash_message'] = '密码已重置，请使用新密码登录';
            $_SESSION['flash_success'] = true;
            header('Location: login.php');
            exit;
        }
        $message = $msg;
        $isError = true;
    }
    $valid = UserAuth::validateResetToken($token) !== null;
}

if (!$valid && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $message = '链接无效或已过期，请重新申请找回密码';
    $isError = true;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重置密码 - CTLP</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 360px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .msg { padding: 0.5rem; margin-bottom: 1rem; border-radius: 4px; }
        .msg.error { background: #fdd; color: #c00; }
        .msg.success { background: #dfd; color: #060; }
        label { display: block; margin-top: 0.75rem; margin-bottom: 0.25rem; }
        input[type="password"], input[type="hidden"] { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        input[type="hidden"] { width: auto; }
        button { margin-top: 1rem; padding: 0.5rem 1rem; cursor: pointer; border-radius: 4px; border: 1px solid #666; }
        button.primary { background: #07c; color: #fff; border-color: #07c; }
        .row { margin-top: 0.5rem; }
        a { color: #07c; }
    </style>
</head>
<body>
    <h1>重置密码</h1>

    <?php if ($message !== ''): ?>
        <p class="msg <?= $isError ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($valid): ?>
        <p>请设置您的新密码（至少 8 位，含字母和数字）。</p>
        <form method="post" action="reset-password.php">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label for="password">新密码</label>
            <input id="password" type="password" name="password" required minlength="8">
            <label for="password2">确认新密码</label>
            <input id="password2" type="password" name="password2" required minlength="8">
            <div class="row">
                <button type="submit" class="primary">提交</button>
            </div>
        </form>
    <?php else: ?>
        <p class="row"><a href="forgot-password.php">重新申请找回密码</a></p>
    <?php endif; ?>

    <p class="row"><a href="login.php">返回登录</a></p>
</body>
</html>
