<?php
session_start();

// Проверяем, что пользователь авторизован и имеет права администратора
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header('Location: sign_in1.php'); // Перенаправляем на страницу входа
    exit;
}

include 'db_connect.php';  // Подключение к базе данных

// Обработка удаления неактивных игр
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_inactive_rooms'])) {
    $dateThreshold = $_POST['date_threshold'];
    pg_query($db_connection, "SELECT s335200.delete_inactive_rooms('$dateThreshold')");
    $message = 'Неактивные игры удалены';
}

// Обработка удаления устаревших токенов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_expired_tokens'])) {
    $dateThreshold = $_POST['date_threshold'];
    pg_query($db_connection, "SELECT s335200.delete_expired_tokens('$dateThreshold')");
    $message = 'Устаревшие токены удалены';
}

// Обработка активации/деактивации пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user_status'])) {
    $user_login = $_POST['user_login'];
    $status = $_POST['status'];
    
    if ($status === 'activate') {
        pg_query($db_connection, "UPDATE Users SET active = NOW() WHERE login = '$user_login'");
        $status_message = "Пользователь $user_login активирован";
    } else {
        pg_query($db_connection, "UPDATE Users SET active = NULL WHERE login = '$user_login'");
        $status_message = "Пользователь $user_login деактивирован";
    }
}

// Получение списка пользователей
$result_users = pg_query($db_connection, "SELECT login, active FROM Users");
$users = pg_fetch_all($result_users);

// Получение списка игр (комнат)
$result_rooms = pg_query($db_connection, "SELECT id, seats, time_to_move FROM Rooms");
$rooms = pg_fetch_all($result_rooms);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="css/general.css" />
    <link rel="stylesheet" href="css/admin.css" />
</head>
<body>
    <div class="container">
        <h1>Панель администратора</h1>
        
        <!-- Сообщение об успехе или ошибке -->
        <?php if (!empty($message)) { ?>
            <p class="message"><?php echo $message; ?></p>
        <?php } ?>
        <?php if (!empty($status_message)) { ?>
            <p class="message"><?php echo $status_message; ?></p>
        <?php } ?>

        <!-- Удаление неактивных игр -->
        <h2>Удалить неактивные игры</h2>
        <form method="POST">
            <label for="date_threshold">Выберите дату (удаляются игры с момента бездействия с этой даты):</label>
            <input type="date" name="date_threshold" required />
            <button type="submit" name="delete_inactive_rooms">Удалить неактивные игры</button>
        </form>

        <!-- Удаление устаревших токенов -->
        <h2>Удалить устаревшие токены</h2>
        <form method="POST">
            <label for="date_threshold">Выберите дату (удаляются токены, не использованные с этой даты):</label>
            <input type="date" name="date_threshold" required />
            <button type="submit" name="delete_expired_tokens">Удалить устаревшие токены</button>
        </form>

        <hr>

        <!-- Управление пользователями -->
        <h2>Управление пользователями</h2>
        <table>
            <tr>
                <th>Логин</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($users as $user) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['login']); ?></td>
                    <td><?php echo $user['active'] ? 'Активен' : 'Не активен'; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_login" value="<?php echo htmlspecialchars($user['login']); ?>">
                            <input type="hidden" name="status" value="<?php echo $user['active'] ? 'deactivate' : 'activate'; ?>">
                            <button type="submit" name="toggle_user_status">
                                <?php echo $user['active'] ? 'Деактивировать' : 'Активировать'; ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <hr>

        <!-- Управление играми -->
        <h2>Управление играми</h2>
        <table>
            <tr>
                <th>ID комнаты</th>
                <th>Число мест</th>
                <th>Время до хода</th>
            </tr>
            <?php foreach ($rooms as $room) { ?>
                <tr>
                    <td><?php echo $room['id']; ?></td>
                    <td><?php echo $room['seats']; ?></td>
                    <td><?php echo $room['time_to_move']; ?></td>
                </tr>
            <?php } ?>
        </table>

        <p><a href="sign_in1.php">Выход</a></p>
    </div>
</body>
</html>
