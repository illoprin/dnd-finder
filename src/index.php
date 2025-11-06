<?php
try {
    $pdo = new PDO(
        'mysql:host=mysql;dbname=dndfinder',
        'dndfinder_user',
        'dndfinder_password'
    );
    echo "✅ Успешное подключение к MySQL!<br>";
    
    // Простой запрос для проверки
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "Версия MySQL: " . $version['version'] . "<br>";
    
} catch (PDOException $e) {
    echo "❌ Ошибка подключения: " . $e->getMessage() . "<br>";
}

echo "✅ PHP работает корректно!<br>";
echo "✅ Nginx раздает статику!<br>";
?>