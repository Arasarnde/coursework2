<?php
session_start();

// Проверяем, что пользователь авторизован
if (!isset($_SESSION['token'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован.']);
    exit;
}

include 'db_connect.php';

// Получаем данные из POST-запроса
$seats = $_POST['seats'] ?? null;
$time_to_move = $_POST['time_to_move'] ?? null;

// Проверка обязательных полей
if (empty($seats) || empty($time_to_move)) {
    echo json_encode(['success' => false, 'message' => 'Количество мест и время на ход обязательны для ввода.']);
    exit;
}

// Вызов функции create_room
$stmt = $pdo->prepare('SELECT s335200.create_room(:seats, :time_to_move)');
$stmt->execute(['seats' => $seats, 'time_to_move' => $time_to_move]);
$result = $stmt->fetchColumn();

// Декодирование JSON-ответа
$response = json_decode($result, true);

if ($response && isset($response['result_message']) && $response['result_message'] === 'Комната успешно создана!') {
    // Обновление списка комнат в сессии
    $_SESSION['rooms'] = $response['rooms'];

    // Возвращение успешного ответа
    echo json_encode([
        'success' => true,
        'message' => 'Комната успешно создана!',
        'rooms' => $response['rooms']
    ]);
} else {
    // Возвращение сообщения об ошибке
    echo json_encode([
        'success' => false,
        'message' => $response['result_message'] ?? 'Ошибка при создании комнаты.'
    ]);
}
?>
