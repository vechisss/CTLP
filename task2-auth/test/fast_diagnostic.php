<?php
// fast_diagnostic.php
echo "=== 快速诊断 ===\n\n";

// 1. 检查网络基础
echo "1. 基础网络测试:\n";
$ping = exec('ping -n 1 smtp.163.com', $pingOutput, $pingResult);
if ($pingResult === 0) {
    echo "   ✓ 可以 ping 通 smtp.163.com\n";
} else {
    echo "   ✗ 无法 ping 通 smtp.163.com (可能被防火墙阻止)\n";
}

// 2. DNS 解析
echo "\n2. DNS 解析:\n";
$ip = gethostbyname('smtp.163.com');
if ($ip !== 'smtp.163.com') {
    echo "   ✓ smtp.163.com 解析到: $ip\n";
} else {
    echo "   ✗ DNS 解析失败\n";
}

// 3. 端口连通性测试
echo "\n3. 端口连通性测试:\n";
$ports = [25, 465, 587];
foreach ($ports as $port) {
    $connection = @fsockopen('smtp.163.com', $port, $errno, $errstr, 3);
    if ($connection) {
        echo "   ✓ 端口 $port: 可连接\n";
        fclose($connection);
    } else {
        echo "   ✗ 端口 $port: $errstr ($errno)\n";
    }
}

// 4. 防火墙状态
echo "\n4. Windows 防火墙:\n";
exec('netsh advfirewall show currentprofile', $fwOutput);
foreach ($fwOutput as $line) {
    if (strpos($line, 'State') !== false) {
        echo "   " . trim($line) . "\n";
    }
}

// 5. PHP 配置
echo "\n5. PHP 配置:\n";
echo "   PHP 版本: " . phpversion() . "\n";
echo "   OpenSSL: " . (extension_loaded('openssl') ? '已加载' : '未加载') . "\n";
echo "   allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'On' : 'Off') . "\n";