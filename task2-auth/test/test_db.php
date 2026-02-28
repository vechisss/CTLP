<?php
// test_db.php - 放在项目根目录
try {
    // 直接使用硬编码测试
    $dsn = 'mysql:host=127.0.0.1;dbname=ctlp;charset=utf8mb4';
    $username = 'ctlp';
    $password = '123456'; // 使用你的实际密码
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "数据库连接成功！\n";
    
    // 查询用户
    $stmt = $pdo->query("SELECT 1");
    var_dump($stmt->fetch());
    
} catch (PDOException $e) {
    echo "连接失败: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
}