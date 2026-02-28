<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vechisss\Ctlp\Auth\SessionManager;

session_start();

SessionManager::logout();

header('Location: login.php');
exit;
