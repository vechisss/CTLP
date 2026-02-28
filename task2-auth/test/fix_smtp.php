<?php
// fix_smtp.php - 修正版
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    // 开启详细调试（重要！）
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // 使用 DEBUG_SERVER 而不是 DEBUG_CONNECTION
    $mail->Debugoutput = function($str, $level) {
        echo "PHPMailer Debug: $str\n";
    };
    
    // 基础设置
    $mail->isSMTP();
    $mail->Host       = 'smtp.163.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'vechisss@163.com';
    $mail->Password   = 'HUeCcXHtxDeaZNmn';  // 授权码
    
    // 关键修复：明确指定加密方式和端口
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // 强制使用 STARTTLS
    $mail->Port       = 587;
    
    // 重要：禁用自动 TLS（防止协议协商失败）
    $mail->SMTPAutoTLS = false;
    
    // 超时设置
    $mail->Timeout = 30;
    $mail->SMTPKeepAlive = true;
    
    // Windows 特定设置
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    
    // 发件人
    $mail->setFrom('vechisss@163.com', 'CTLP');
    $mail->addAddress('vechisss@163.com');  // 先发给自己
    
    $mail->Subject = '=?UTF-8?B?' . base64_encode('163邮箱测试') . '?=';
    $mail->Body = '<h3>测试成功</h3><p>如果你看到这封邮件，SMTP配置正确！</p>';
    
    echo "正在发送邮件...\n";
    $mail->send();
    echo "✓ 邮件发送成功！\n";
    
} catch (Exception $e) {
    echo "✗ 发送失败: " . $mail->ErrorInfo . "\n\n";
    
    // 如果 TLS 587 失败，尝试 SSL 465
    echo "尝试 SSL 465 配置...\n";
    
    try {
        $mail2 = new PHPMailer(true);
        $mail2->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail2->Debugoutput = function($str, $level) {
            echo "PHPMailer Debug: $str\n";
        };
        
        $mail2->isSMTP();
        $mail2->Host = 'smtp.163.com';
        $mail2->SMTPAuth = true;
        $mail2->Username = 'vechisss@163.com';
        $mail2->Password = 'HUeCcXHtxDeaZNmn';
        $mail2->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // SSL
        $mail2->Port = 465;
        $mail2->SMTPAutoTLS = false;
        $mail2->Timeout = 30;
        $mail2->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        $mail2->setFrom('vechisss@163.com', 'CTLP');
        $mail2->addAddress('vechisss@163.com');
        $mail2->Subject = '=?UTF-8?B?' . base64_encode('163邮箱测试(SSL)') . '?=';
        $mail2->Body = 'SSL 465 端口测试成功';
        
        $mail2->send();
        echo "✓ SSL 465 发送成功！\n";
        
        // 提示更新 .env
        echo "\n✅ 发现 SSL 465 可用！请更新你的 .env 文件：\n";
        echo "SMTP_PORT=465\n";
        echo "SMTP_SECURE=ssl\n";
        
    } catch (Exception $e2) {
        echo "✗ SSL 465 也失败: " . $mail2->ErrorInfo . "\n";
    }
}