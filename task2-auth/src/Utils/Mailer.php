<?php

declare(strict_types=1);

namespace Vechisss\Ctlp\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * 邮件发送封装，基于 PHPMailer，SMTP 配置从 .env 读取。
 */
final class Mailer
{
    /** 项目根目录（task2-auth） */
    private static ?string $projectRoot = null;

    /**
     * 发送验证码邮件。
     *
     * @param string $email 收件人邮箱
     * @param string $code  验证码
     * @return bool 是否发送成功
     */
    public static function sendVerificationCode(string $email, string $code): bool
    {
        self::ensureEnvLoaded();
        $mail = new PHPMailer(true);
        try {
            self::configureSmtp($mail);
            $mail->setFrom(self::env('SMTP_FROM', ''), self::env('SMTP_FROM_NAME', 'CTLP'));
            $mail->addAddress($email);
            $mail->Subject = '您的验证码 - CTLP';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Body    = "您的验证码为：{$code}，请勿泄露。如非本人操作请忽略。";
            $mail->AltBody = "您的验证码为：{$code}，请勿泄露。如非本人操作请忽略。";
            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            return false;
        }
    }

    private static function ensureEnvLoaded(): void
    {
        if (self::$projectRoot !== null) {
            return;
        }
        self::$projectRoot = dirname(__DIR__, 2);
        $envPath = self::$projectRoot . DIRECTORY_SEPARATOR . '.env';
        if (is_file($envPath) && class_exists(\Dotenv\Dotenv::class)) {
            $dotenv = \Dotenv\Dotenv::createMutable(self::$projectRoot);
            $dotenv->safeLoad();
        }
    }

    private static function env(string $key, string $default = ''): string
    {
        $v = getenv($key);
        if ($v !== false && $v !== '') {
            return $v;
        }
        $v = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        return is_string($v) ? $v : $default;
    }

    private static function configureSmtp(PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Host       = self::env('SMTP_HOST', 'localhost');
        $mail->Port       = (int) self::env('SMTP_PORT', '25');
        $mail->SMTPAuth   = true;
        $mail->Username   = self::env('SMTP_USER', '');
        $mail->Password   = self::env('SMTP_PASS', '');
        $secure           = self::env('SMTP_SECURE', '');
        if ($secure === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($secure === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = false;
        }
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];
    }
}
