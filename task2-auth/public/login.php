<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vechisss\Ctlp\Auth\SessionManager;
use Vechisss\Ctlp\Auth\UserAuth;

session_start();

// 已登录则直接去任务一界面
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - CTLP</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; max-width: 360px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .msg { padding: 0.5rem; margin-bottom: 1rem; border-radius: 4px; }
        .msg.error { background: #fdd; color: #c00; }
        .msg.success { background: #dfd; color: #060; }
        label { display: block; margin-top: 0.75rem; margin-bottom: 0.25rem; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;
        }
        button { margin-top: 1rem; padding: 0.5rem 1rem; cursor: pointer; border-radius: 4px; border: 1px solid #666; }
        button.primary { background: #07c; color: #fff; border-color: #07c; }
        .row { margin-top: 0.5rem; }
        a { color: #07c; }
    </style>
</head>
<body>
    <h1>登录</h1>

    <?php if ($message !== ''): ?>
        <p class="msg <?= $isSuccess ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="email">邮箱</label>
        <input id="email" type="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label for="password">密码</label>
        <input id="password" type="password" name="password" required>
        <div class="row">
            <button type="submit" class="primary">登录</button>
        </div>
    </form>

    <p class="row"><a href="register.php">没有账号？去注册</a></p>
</body>
</html>
