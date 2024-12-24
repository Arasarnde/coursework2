<?php
// db_connect.php

// Настройки подключения к базе данных
$host = 'pg'; // Хост базы данных
$db = 'studs'; // Имя базы данных
$db_user = 's335200'; // Имя пользователя
$db_password = 'M3USaAqhE4gWEygo'; // Пароль

// DSN (Data Source Name) для подключения к PostgreSQL
$dsn = "pgsql:host=$host;port=5432;dbname=$db;";

try {
    // Создаем подключение с использованием PDO
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Обработка ошибок
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Получение данных в виде ассоциативных массивов
        PDO::ATTR_EMULATE_PREPARES => false // Использование настоящих подготовленных выражений
    ]);
} catch (PDOException $e) {
    // Ошибка подключения
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Успешное подключение
// Здесь можно добавить дополнительные настройки или логику, если необходимо

?>
