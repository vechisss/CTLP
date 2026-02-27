<?php

declare(strict_types=1);

namespace Vechisss\Ctlp\Auth;

use Vechisss\Ctlp\Database\Connection;
use Vechisss\Ctlp\Utils\Mailer;
use Vechisss\Ctlp\Utils\Validator;

/**
 * 用户注册与验证码逻辑。
 */
final class UserAuth
{
    private const SESSION_EMAIL = 'register_email';
    private const SESSION_PASSWORD_HASH = 'register_password_hash';
    private const SESSION_CODE = 'register_code';
    private const SESSION_EXPIRES = 'register_expires';
    private const CODE_VALID_SECONDS = 300; // 5 分钟

    /**
     * 登录：校验邮箱与密码，成功则写入会话。
     *
     * @param string $email
     * @param string $password
     * @return array{0: bool, 1: string} [是否成功, 提示信息]
     */
    public static function attemptLogin(string $email, string $password): array
    {
        $email = Validator::cleanString($email);
        if (!Validator::isValidEmail($email)) {
            return [false, '邮箱格式不正确'];
        }

        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return [false, '邮箱或密码错误'];
        }
        if (!password_verify($password, $row['password'])) {
            return [false, '邮箱或密码错误'];
        }

        SessionManager::login((int) $row['id']);
        return [true, ''];
    }

    /**
     * 请求发送验证码：校验邮箱与密码、唯一性，生成 4 位验证码写入 Session 并发送邮件。
     *
     * @param string $email
     * @param string $password
     * @return array{0: bool, 1: string} [是否成功, 提示信息]
     */
    public static function requestVerificationCode(string $email, string $password): array
    {
        $email = Validator::cleanString($email);
        $password = $password; // 不清除空格，密码可含空格

        if (!Validator::isValidEmail($email)) {
            return [false, '邮箱格式不正确'];
        }
        if (!Validator::isStrongPassword($password)) {
            return [false, '密码至少 8 位且需同时包含字母和数字'];
        }

        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return [false, '该邮箱已注册'];
        }

        $code = (string) random_int(1000, 9999);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[self::SESSION_EMAIL] = $email;
        $_SESSION[self::SESSION_PASSWORD_HASH] = password_hash($password, PASSWORD_DEFAULT);
        $_SESSION[self::SESSION_CODE] = $code;
        $_SESSION[self::SESSION_EXPIRES] = time() + self::CODE_VALID_SECONDS;

        if (!Mailer::sendVerificationCode($email, $code)) {
            return [false, '验证码发送失败，请稍后重试'];
        }

        return [true, '验证码已发送到您的邮箱'];
    }

    /**
     * 提交注册：校验 Session 中的验证码，写入用户并重定向到登录页。
     * 依赖 Session 中 requestVerificationCode 写入的 email / password_hash / code / expires。
     *
     * @param string $code 用户填写的验证码
     * @return array{0: bool, 1: string} [是否成功, 提示信息]
     */
    public static function register(string $code): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_SESSION[self::SESSION_EMAIL] ?? null;
        $passwordHash = $_SESSION[self::SESSION_PASSWORD_HASH] ?? null;
        $storedCode = $_SESSION[self::SESSION_CODE] ?? null;
        $expires = (int) ($_SESSION[self::SESSION_EXPIRES] ?? 0);

        if (!$email || !$passwordHash || $storedCode === null) {
            return [false, '请先获取验证码'];
        }
        if (time() > $expires) {
            self::clearRegisterSession();
            return [false, '验证码已过期，请重新获取'];
        }
        if (!hash_equals($storedCode, trim($code))) {
            return [false, '验证码错误'];
        }

        $pdo = Connection::get();
        $stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$email, $passwordHash]);

        self::clearRegisterSession();
        return [true, ''];
    }

    // ---------- 找回密码 ----------

    private const RESET_VALID_SECONDS = 3600; // 1 小时

    /**
     * 请求重置密码：根据邮箱生成 64 位 Token 与过期时间写入 DB，并发送重置链接邮件。
     *
     * @param string $email   用户邮箱
     * @param string $baseUrl 当前站点 public 目录的完整 URL（不含末尾斜杠），用于拼重置链接
     * @return array{0: bool, 1: string} [是否成功, 提示信息]
     */
    public static function requestPasswordReset(string $email, string $baseUrl): array
    {
        $email = Validator::cleanString($email);
        if (!Validator::isValidEmail($email)) {
            return [false, '邮箱格式不正确'];
        }

        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return [false, '该邮箱未注册'];
        }

        $token = bin2hex(random_bytes(32)); // 64 位
        $expires = date('Y-m-d H:i:s', time() + self::RESET_VALID_SECONDS);
        $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
        $stmt->execute([$token, $expires, $row['id']]);

        $resetUrl = rtrim($baseUrl, '/') . '/reset-password.php?token=' . urlencode($token);
        if (!Mailer::sendPasswordResetLink($email, $resetUrl)) {
            return [false, '邮件发送失败，请稍后重试'];
        }

        return [true, '重置链接已发送到您的邮箱，请查收'];
    }

    /**
     * 校验重置 Token 是否合法且未过期，返回对应用户 id，否则返回 null。
     *
     * @return int|null 用户 id 或 null
     */
    public static function validateResetToken(string $token): ?int
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }
        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : null;
    }

    /**
     * 使用 Token 设置新密码并销毁 Token。
     *
     * @param string $token        URL 中的 token
     * @param string $newPassword  新密码
     * @return array{0: bool, 1: string} [是否成功, 提示信息]
     */
    public static function resetPassword(string $token, string $newPassword): array
    {
        $userId = self::validateResetToken($token);
        if ($userId === null) {
            return [false, '链接无效或已过期，请重新申请找回密码'];
        }
        if (!Validator::isStrongPassword($newPassword)) {
            return [false, '密码至少 8 位且需同时包含字母和数字'];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo = Connection::get();
        $stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        $stmt->execute([$hash, $userId]);

        return [true, ''];
    }
    public static function hasPendingVerification(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $email = $_SESSION[self::SESSION_EMAIL] ?? null;
        $expires = (int) ($_SESSION[self::SESSION_EXPIRES] ?? 0);
        return $email !== null && time() <= $expires;
    }

    private static function clearRegisterSession(): void
    {
        unset(
            $_SESSION[self::SESSION_EMAIL],
            $_SESSION[self::SESSION_PASSWORD_HASH],
            $_SESSION[self::SESSION_CODE],
            $_SESSION[self::SESSION_EXPIRES]
        );
    }
}
