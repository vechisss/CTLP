<?php
// test_mail.php - 测试163邮箱SMTP
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


// 配置
$config = [
    'host' => 'smtp.163.com',
    'port' => 465,
    'secure' => 'ssl',
    'user' =>  'vechisss@163.com',
    'pass' =>  'HUeCcXHtxDeaZNmn',
    'from' => 'vechisss@163.com',
    'from_name' => 'CTLP'
];

echo "=== 163邮箱SMTP测试 ===\n\n";
echo "配置信息:\n";
echo "Host: {$config['host']}\n";
echo "Port: {$config['port']}\n";
echo "Secure: {$config['secure']}\n";
echo "User: {$config['user']}\n";
echo "Pass: " . substr($config['pass'], 0, 4) . "****\n";
echo "From: {$config['from']}\n\n";

// 创建 PHPMailer 实例
$mail = new PHPMailer(true);

try {
    // 开启调试模式（查看详细错误）
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // 改为 DEBUG_CONNECTION 可以看到更详细的信息
    $mail->Debugoutput = function($str, $level) {
        echo "SMTP Debug: $str\n";
    };
    
    // 服务器设置
    $mail->isSMTP();
    $mail->Host       = $config['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['user'];
    $mail->Password   = $config['pass'];
    $mail->SMTPSecure = $config['secure']; // tls 或 ssl
    $mail->Port       = $config['port'];
    
    // 额外选项（解决一些常见问题）
    $mail->SMTPAutoTLS = true;
    $mail->CharSet = 'UTF-8';
    
    // 发送方
    $mail->setFrom($config['from'], $config['from_name']);
    
    // 接收方（测试用，改成你自己的邮箱）
    $mail->addAddress('vechisss@163.com', '自己'); // 先发给自己测试
    
    // 内容
    $mail->isHTML(true);
    $mail->Subject = '=?UTF-8?B?' . base64_encode('163邮箱SMTP测试') . '?='; // 解决中文标题乱码
    $mail->Body    = '<h2>测试邮件</h2><p>如果你收到这封邮件，说明SMTP配置正确！</p><p>发送时间: ' . date('Y-m-d H:i:s') . '</p>';
    $mail->AltBody = '测试邮件，发送时间: ' . date('Y-m-d H:i:s');
    
    echo "正在发送邮件...\n";
    $mail->send();
    echo "\n✓ 邮件发送成功！\n";
    
} catch (Exception $e) {
    echo "\n✗ 邮件发送失败\n";
    echo "错误信息: " . $mail->ErrorInfo . "\n\n";
    
    // 常见错误诊断
    $error = $mail->ErrorInfo;
    
    if (strpos($error, 'authenticate') !== false) {
        echo "可能原因: 用户名或授权码错误\n";
        echo "解决方案: 检查 SMTP_USER 和 SMTP_PASS，确保使用的是授权码而不是登录密码\n";
    } elseif (strpos($error, 'Connection refused') !== false) {
        echo "可能原因: 端口被防火墙阻止或端口配置错误\n";
        echo "解决方案: 尝试使用 465/SSL 或 587/TLS\n";
    } elseif (strpos($error, 'certificate') !== false) {
        echo "可能原因: SSL证书问题\n";
        echo "解决方案: 尝试添加以下代码:\n";
        echo "\$mail->SMTPOptions = array(\n";
        echo "    'ssl' => array(\n";
        echo "        'verify_peer' => false,\n";
        echo "        'verify_peer_name' => false,\n";
        echo "        'allow_self_signed' => true\n";
        echo "    )\n";
        echo ");\n";
    } elseif (strpos($error, 'timed out') !== false) {
        echo "可能原因: 网络连接超时\n";
        echo "解决方案: 检查防火墙设置或更换网络\n";
    }
}