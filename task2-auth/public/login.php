<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

session_start();

$message = $_SESSION['flash_message'] ?? '';
$isSuccess = !empty($_SESSION['flash_success']);
unset($_SESSION['flash_message'], $_SESSION['flash_success']);
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
        a { color: #07c; }
    </style>
</head>
<body>
    <h1>登录</h1>
    <?php if ($message !== ''): ?>
        <p class="msg <?= $isSuccess ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <p>登录功能将在下一步实现。</p>
    <p><a href="register.php">去注册</a></p>
</body>
</html>
