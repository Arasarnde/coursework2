<?php
session_start();

include 'db_connect.php';  // Подключение к базе данных

// Получаем данные из POST-запроса
$login = $_POST['login'] ?? null;
$old_password = $_POST['old_password'] ?? null;
$new_password = $_POST['new_password'] ?? null;

// Проверка обязательных полей
if (empty($login) || empty($old_password) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Логин, старый и новый пароли обязательны для ввода.']);
    exit;
}

// Вызов функции для смены пароля
$stmt = $pdo->prepare('SELECT s335200.change_password(:login, :old_password, :new_password)');
$stmt->execute(['login' => $login, 'old_password' => $old_password, 'new_password' => $new_password]);
$result = $stmt->fetchColumn();

// Декодирование JSON-ответа
$response = json_decode($result, true);

if ($response && isset($response['result_message']) && $response['result_message'] === 'Пароль успешно изменен!') {
    // Возвращение успешного ответа
    echo json_encode([
        'success' => true,
        'message' => 'Пароль успешно изменен!'
    ]);
} else {
    // Возвращение сообщения об ошибке
    echo json_encode([
        'success' => false,
        'message' => $response['result_message'] ?? 'Ошибка при изменении пароля.'
    ]);
}
?>
