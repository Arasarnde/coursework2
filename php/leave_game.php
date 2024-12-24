<?php
session_start();

// Проверка авторизации по токену
if (!isset($_SESSION['token'])) {
    echo json_encode(['success' => false, 'message' => 'Вы не авторизованы']);
    exit;
}

$token = $_SESSION['token'];

// Подключение к базе данных через отдельный скрипт
require 'db_connect.php';

// Получение id_room из POST-запроса
$idRoom = (int)$_POST['id_room'] ?? null;
if (!$idRoom || !is_numeric($idRoom)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный ID комнаты']);
    exit;
}

try {
    // Вызов функции remove_player_from_game
    $stmt = $pdo->prepare('SELECT s335200.remove_player_from_game(:token, :id_room) AS result');
    $stmt->execute(['token' => $token, 'id_room' => $idRoom]);

    // Получение результата функции
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['result'])) {
        $response = json_decode($result['result'], true);

        if ($response['success']) {
            // Сохраняем информацию о комнатах в сессии
            $_SESSION['rooms'] = $response['rooms'];

            echo json_encode(['success' => true, 'message' => 'Вы успешно покинули игру']);
        } else {
            echo json_encode(['success' => false, 'message' => $response['message']]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Неизвестная ошибка при выходе из игры.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')]);
}
?>
