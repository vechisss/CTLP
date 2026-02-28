<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vechisss\Ctlp\Auth\SessionManager;

session_start();

// 未登录则重定向到登录页
SessionManager::requireLogin('login.php');

// 已登录：展示欢迎页（不跳转）
$welcomePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'welcome.html';
if (is_file($welcomePath)) {
    readfile($welcomePath);
} else {
    echo '<!DOCTYPE html><html><body><h1>欢迎登录</h1><p><a href="../../task1-ui/index.html">进入任务一</a> | <a href="logout.php">退出登录</a></p></body></html>';
}
