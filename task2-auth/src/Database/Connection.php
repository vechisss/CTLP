<?php

declare(strict_types=1);

namespace Vechisss\Ctlp\Database;

use PDO;
use PDOException;

/**
 * 数据库连接单例，基于 PDO，配置从 .env 读取。
 */
final class Connection
{
    private static ?PDO $instance = null;

    /** 禁止外部实例化 */
    private function __construct()
    {
    }

    /** 禁止克隆 */
    private function __clone()
    {
    }

    /**
     * 获取 PDO 连接单例。
     * 从项目根目录的 .env 读取 DB_HOST, DB_NAME, DB_USER, DB_PASS。
     *
     * @return PDO
     * @throws PDOException 连接或配置错误时抛出
     */
    public static function get(): PDO
    {
        if (self::$instance === null) {
            self::loadEnv();
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                self::env('DB_HOST', '127.0.0.1'),
                self::env('DB_NAME', 'ctlp')
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$instance = new PDO(
                $dsn,
                self::env('DB_USER', 'root'),
                self::env('DB_PASS', ''),
                $options
            );
        }

        return self::$instance;
    }

    /**
     * 加载 .env 到 getenv() / $_ENV（仅加载一次）。
     */
    private static function loadEnv(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $envPath = $projectRoot . DIRECTORY_SEPARATOR . '.env';

        if (!is_file($envPath)) {
            return;
        }

        if (class_exists(\Dotenv\Dotenv::class)) {
            $dotenv = \Dotenv\Dotenv::createMutable($projectRoot);
            $dotenv->safeLoad();
        }
    }

    private static function env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        return is_string($value) ? $value : $default;
    }
}
