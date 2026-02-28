<?php

declare(strict_types=1);

namespace Vechisss\Ctlp\Utils;

/**
 * CSRF Token 生成与校验，用于表单防跨站请求伪造。
 */
final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    /**
     * 获取当前 CSRF Token（若不存在则生成并写入 Session）。
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * 校验提交的 token 是否与 Session 中一致（常量时间比较）。
     *
     * @param string $token 用户提交的 csrf_token
     */
    public static function validate(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $stored = $_SESSION[self::SESSION_KEY] ?? '';
        return is_string($stored) && $token !== '' && hash_equals($stored, $token);
    }

    /**
     * 输出隐藏域 HTML，用于表单内嵌。
     */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
