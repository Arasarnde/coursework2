<?php
session_start();

// Проверка авторизации по токену
if (!isset($_SESSION['token'])) {
    header('Location: ../sign_in1.php');
    exit;
}

$token = $_SESSION['token'];

// Подключение к базе данных через отдельный скрипт
require 'db_connect.php'; // Убедитесь, что путь к файлу верный

// Получение id_room из URL
$idRoom = $_GET['id_room'] ?? null;
if (!$idRoom || !is_numeric($idRoom)) {
    die("Некорректный ID комнаты.");
}

try {
    // Вызов функции user_enter_room
    $stmt = $pdo->prepare('SELECT s335200.user_enter_room(:token, :id_room) AS result');
    $stmt->execute(['token' => $token, 'id_room' => $idRoom]);

    // Получение результата функции
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['result'])) {
        $response = json_decode($result['result'], true);

        if (isset($response['room']) && $response['room'] !== null && $response['success']) {
            // Сохраняем информацию о игроках и комнате в сессии
            $_SESSION['room'] = $response['room'];

            // Успешное добавление в комнату, перенаправление на play.php
            header("Location: ../play.php?id_room=$idRoom");
            exit;
        } else {
            // Ошибка при добавлении в комнату, отображаем сообщение
            header("Location: ../game_list.php");
            exit;
        }
    } else {
        header("Location: ../game_list.php");
        exit;
    }
} catch (PDOException $e) {
    die("Ошибка при вызове функции: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
