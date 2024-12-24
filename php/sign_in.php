<?php
session_start();

include 'db_connect.php';

// Получаем данные из POST-запроса
$login = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;

// Проверка обязательных полей
if (empty($login) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Логин и пароль обязательны для ввода.']);
    exit;
}

// Вызов функции auth_user
$stmt = $pdo->prepare('SELECT s335200.auth_user(:login, :password)');
$stmt->execute(['login' => $login, 'password' => $password]);
$result = $stmt->fetchColumn();

// Декодирование JSON-ответа
$response = json_decode($result, true);

if ($response && isset($response['result_message']) && $response['result_message'] === 'Вы успешно вошли!') {
    // Сохранение данных в сессии
    $_SESSION['token'] = $response['token'];
    $_SESSION['user'] = ['login' => $login];
    $_SESSION['rooms'] = $response['rooms'];

    // Возвращение успешного ответа
    echo json_encode([
        'success' => true,
        'message' => 'Вы успешно вошли!',
        'token' => $response['token'],
        'rooms' => $response['rooms']
    ]);
} else {
    // Возвращение сообщения об ошибке
    echo json_encode([
        'success' => false,
        'message' => $response['result_message'] ?? 'Неверный логин или пароль.'
    ]);
}
?>
