<?php

declare(strict_types=1);

namespace Vechisss\Ctlp\Auth;

/**
 * 会话管理：登录状态与要求登录重定向。
 */
final class SessionManager
{
    private const SESSION_USER_ID = 'user_id';

    /**
     * 标记用户已登录。
     *
     * @param int $user_id users 表主键
     */
    public static function login(int $user_id): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[self::SESSION_USER_ID] = $user_id;
    }

    /**
     * 是否已登录。
     */
    public static function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION[self::SESSION_USER_ID])
            && is_numeric($_SESSION[self::SESSION_USER_ID]);
    }

    /**
     * 获取当前登录用户 ID，未登录返回 null。
     *
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        $id = $_SESSION[self::SESSION_USER_ID];
        return is_int($id) ? $id : (int) $id;
    }

    /**
     * 未登录则重定向到登录页并结束脚本。
     * 应在输出任何内容前调用。
     *
     * @param string $loginUrl 登录页 URL，默认 login.php
     */
    public static function requireLogin(string $loginUrl = 'login.php'): void
    {
        if (self::isLoggedIn()) {
            return;
        }
        if (headers_sent()) {
            echo '<p>请先<a href="' . htmlspecialchars($loginUrl) . '">登录</a>。</p>';
            exit;
        }
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . $loginUrl);
        exit;
    }

    /**
     * 登出并清除会话中的用户 ID。
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION[self::SESSION_USER_ID]);
    }
}
