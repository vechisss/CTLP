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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>重置密码 - CTLP</title>
    <link rel="icon" href="assets/images/icon.png" type="image/png">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">重置密码</h1>
        <p class="auth-desc">请设置新密码（至少 8 位，含字母和数字）。</p>

        <?php if ($message !== ''): ?>
            <p class="msg <?= $isError ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($valid): ?>
            <form method="post" action="reset-password.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label for="password">新密码</label>
                    <div class="password-wrap">
                        <input id="password" type="password" name="password" required minlength="8" placeholder="请设置新密码">
                        <button type="button" class="pwd-toggle" aria-label="显示密码">显示</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password2">确认新密码</label>
                    <div class="password-wrap">
                        <input id="password2" type="password" name="password2" required minlength="8" placeholder="再次输入新密码">
                        <button type="button" class="pwd-toggle" aria-label="显示密码">显示</button>
                    </div>
                </div>
                <div class="row">
                    <button type="submit" class="btn btn-primary">提交</button>
                </div>
            </form>
        <?php else: ?>
            <div class="auth-links">
                <a href="forgot-password.php">重新申请找回密码</a>
            </div>
        <?php endif; ?>

        <div class="auth-links">
            <a href="login.php">返回登录</a>
        </div>
    </div>
    <script src="assets/js/auth.js"></script>
</body>
</html>
