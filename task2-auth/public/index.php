<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vechisss\Ctlp\Auth\SessionManager;

session_start();

// 未登录则重定向到登录页
SessionManager::requireLogin('login.php');

// 登录成功后跳转到任务一界面（task1-ui 与 task2-auth 同级）
$task1Url = '../../task1-ui/index.html';
header('Location: ' . $task1Url);
exit;
