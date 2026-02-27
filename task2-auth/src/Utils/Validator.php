<?php

declare(strict_types=1);

namespace Vechisss\Ctlp\Utils;

/**
 * 输入校验与清理工具（静态方法）。
 */
final class Validator
{
    /** 密码最小长度 */
    private const PASSWORD_MIN_LENGTH = 8;

    /**
     * 校验邮箱格式。
     *
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 校验密码强度：至少 8 位，且同时包含数字与字母。
     *
     * @param string $password
     * @return bool
     */
    public static function isStrongPassword(string $password): bool
    {
        if (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            return false;
        }
        $hasLetter = preg_match('/[a-zA-Z]/', $password) === 1;
        $hasDigit = preg_match('/[0-9]/', $password) === 1;
        return $hasLetter && $hasDigit;
    }

    /**
     * 清理输入字符串：去除首尾空白，并规范内部空白与编码。
     *
     * @param string $input
     * @return string
     */
    public static function cleanString(string $input): string
    {
        $trimmed = trim($input);
        $normalized = preg_replace('/\s+/u', ' ', $trimmed);
        return $normalized !== null ? $normalized : $trimmed;
    }
}
