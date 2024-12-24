<?php
session_start();

include 'db_connect.php';

$pdo = new PDO($dsn, $db_user, $db_password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Получаем данные из POST-запроса
$login = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;

// Проверка обязательных полей
if (empty($login) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Логин и пароль обязательны для ввода.']);
    exit;
}

// Вызов функции register_user
$stmt = $pdo->prepare('SELECT s335200.register_user(:login, :password)');
$stmt->execute(['login' => $login, 'password' => $password]);
$result = $stmt->fetchColumn();

// Декодирование JSON-ответа
$response = json_decode($result, true);

if ($response && isset($response['result_message']) && $response['result_message'] === 'Ваш аккаунт успешно создан!') {
    // Сохранение данных в сессии
    $_SESSION['token'] = $response['token'];
    $_SESSION['user'] = ['login' => $login];
    $_SESSION['rooms'] = $response['rooms'];

    // Возвращение успешного ответа
    echo json_encode([
        'success' => true,
        'message' => 'Ваш аккаунт успешно создан!',
        'token' => $response['token'],
        'rooms' => $response['rooms']
    ]);
} else {
    // Возвращение сообщения об ошибке
    echo json_encode([
        'success' => false,
        'message' => $response['result_message'] ?? 'Ошибка при регистрации.'
    ]);
}

?>
