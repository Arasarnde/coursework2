<?php

session_start();

// Проверка, что пользователь авторизован
if (!isset($_SESSION['token'])) {
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

require 'db_connect.php'; // Подключение к базе данных

// Получаем параметры из запроса
$token = $_GET['token'] ?? null;
$playerId = (int)$_GET['player_id'] ?? null;
$targetCell = (int)$_GET['target_cell'] ?? null;

// Проверка на наличие всех необходимых данных
if (!$token || !$playerId || !$targetCell) {
    // Если не все параметры переданы, выводим ошибку в формате JSON
    echo json_encode(['success' => false, 'error' => 'Некорректные данные запроса.']);
    exit;
}

try {
    // Подготовка SQL-запроса
    $stmt = $pdo->prepare('SELECT s335200.move_ship_and_pirates(:token, :player_id, :target_cell)');
    $stmt->execute(['token' => $token, 'player_id' => $playerId, 'target_cell' => $targetCell]);

    // Получение результата
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Проверка на ошибки в результате
    if (!$result) {
        // Если нет результата, отправляем ошибку
        echo json_encode(['success' => false, 'error' => 'Ошибка выполнения запроса на сервере']);
        exit;
    }

    // Логируем результат для отладки (это не будет отправляться в ответ)
    error_log("Результат запроса: " . print_r($result, true));

    // Отправляем результат в формате JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'game_state' => $result]);

} catch (Exception $e) {
    // Логируем ошибку SQL
    error_log("Ошибка SQL: " . $e->getMessage());
    
    // Отправляем ошибку в формате JSON
    echo json_encode(['success' => false, 'error' => 'Ошибка на сервере']);
    exit;
}
?>
