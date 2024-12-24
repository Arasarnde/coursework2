<?php
session_start();

// Проверяем, есть ли данные пользователя в сессии
if (!isset($_SESSION['token'])) {
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

require 'db_connect.php'; 

$token = $_SESSION['token'];

try {
    // Выполняем запрос к базе данных
    $stmt = $pdo->prepare('SELECT s335200.get_rooms_list(:token)');
    $stmt->execute(['token' => $token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        throw new Exception('Не удалось получить данные о комнатах.');
    }

    // Декодируем JSON, полученный от функции
    // Мы предполагаем, что в 'get_rooms_list' возвращается уже строка JSON
    $rooms = json_decode($result['get_rooms_list'], true); // Используем правильный индекс

    if ($rooms === null) {
        throw new Exception('Ошибка декодирования JSON: ' . json_last_error_msg());
    }

    // Сохраняем результат в сессии
    $_SESSION['rooms'] = $rooms['rooms'];

    // Возвращаем результат в формате JSON
    header('Content-Type: application/json');
    echo json_encode($rooms); // Выводим весь массив данных в формате JSON

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
