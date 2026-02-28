<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Vechisss\Ctlp\Auth\SessionManager;

SessionManager::logout();

header('Location: login.php');
exit;
