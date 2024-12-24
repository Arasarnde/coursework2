<?php
session_start();

// Проверка, что пользователь авторизован
if (!isset($_SESSION['token'])) {
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

require 'db_connect.php'; // Подключение к базе данных

$token = $_GET['token'] ?? null;
$idRoom = $_GET['id_room'] ?? null;

// Проверка наличия комнаты и игроков в сессии
if (!isset($_SESSION['room']) || !is_array($_SESSION['room'])) {
    echo json_encode(['success' => false, 'message' => 'Комната не найдена']);
    exit;
}

$idRoom = $_SESSION['room']['room_id'] ?? null;
if (!$idRoom) {
    echo json_encode(['success' => false, 'message' => 'ID комнаты не найден']);
    exit;
}

// Подключение к базе данных (обнови параметры подключения под свою базу данных)
try {
    // Выполнение запроса к базе данных
    $stmt = $pdo->prepare('SELECT s335200.game_control(:token, :id_room)');
    $stmt->execute(['token' => $token, 'id_room' => $idRoom]);
    
    // Получение результата
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['success' => true, 'game_state' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка получения данных']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>
