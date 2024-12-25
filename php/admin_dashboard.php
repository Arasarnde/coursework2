<?php
session_start();

// Проверка роли администратора
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Если пользователь не администратор, перенаправляем его на главную страницу
    header('Location: ../index.html');
    exit;
}

// Извлекаем токен из сессии
$userToken = isset($_SESSION['token']) ? $_SESSION['token'] : null;
if (!$userToken) {
    // Если токен отсутствует, перенаправляем
    header('Location: ../index.html');
    exit;
}

// Подключение к базе данных
include 'db_connect.php';

// Функции для работы с базой данных
function deleteToken($userLogin, $userToken) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT delete_token(:user_login, :user_token)");
    $stmt->execute([
        'user_login' => $userLogin,
        'user_token' => $userToken
    ]);
}

function deleteGame($roomId, $userToken) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT delete_game(:room_id, :user_token)");
    $stmt->execute([
        'room_id' => $roomId,
        'user_token' => $userToken
    ]);
}

function addUserToRoom($userLogin, $roomId, $userToken) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT add_user_to_room(:user_login, :room_id, :user_token)");
    $stmt->execute([
        'user_login' => $userLogin,
        'room_id' => $roomId,
        'user_token' => $userToken
    ]);
}

function removeUserFromRoom($userLogin, $roomId, $userToken) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT remove_user_from_room(:user_login, :room_id, :user_token)");
    $stmt->execute([
        'user_login' => $userLogin,
        'room_id' => $roomId,
        'user_token' => $userToken
    ]);
}

// Обработка действий пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_token'])) {
        $userLogin = $_POST['delete_token'];
        deleteToken($userLogin, $userToken);
        echo "<p>Токен пользователя удален.</p>";
    }

    if (isset($_POST['delete_game'])) {
        $roomId = $_POST['delete_game'];
        deleteGame($roomId, $userToken);
        echo "<p>Игра удалена.</p>";
    }

    if (isset($_POST['add_user_to_room'])) {
        $userLogin = $_POST['user_login'];
        $roomId = $_POST['room_id'];
        addUserToRoom($userLogin, $roomId, $userToken);
        echo "<p>Пользователь добавлен в комнату.</p>";
    }

    if (isset($_POST['remove_user_from_room'])) {
        $userLogin = $_POST['user_login'];
        $roomId = $_POST['room_id'];
        removeUserFromRoom($userLogin, $roomId, $userToken);
        echo "<p>Пользователь удален из комнаты.</p>";
    }
}

// Получаем актуальные данные после выполнения действий
$stmt = $pdo->query("SELECT user_login, created_at FROM session_tokens");
$users = $stmt->fetchAll();

$stmt = $pdo->query("SELECT id, seats, time_to_move, (select count(*) as count_players from users_in_the_rooms where id_room=id) FROM Rooms");
$games = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="container">

    <h1>Панель администратора</h1>

    <!-- Управление токенами пользователей -->
    <h2>Управление токенами пользователей</h2>
    <table>
        <thead>
            <tr>
                <th>Логин</th>
                <th>Дата генерации</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['user_login']); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                    <td>
                        <form method="post" action="admin_dashboard.php">
                            <input type="hidden" name="delete_token" value="<?php echo $user['user_login']; ?>">
                            <button type="submit">Удалить токен</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Управление играми -->
    <h2>Управление играми</h2>
    <table>
        <thead>
            <tr>
                <th>ID комнаты</th>
                <th>Места</th>
                <th>Время на ход</th>
                <th>Игроков в комнате</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($games as $game): ?>
                <tr>
                    <td><?php echo htmlspecialchars($game['id']); ?></td>
                    <td><?php echo htmlspecialchars($game['seats']); ?></td>
                    <td><?php echo htmlspecialchars($game['time_to_move']); ?></td>
                    <td><?php echo htmlspecialchars($game['count_players']); ?></td>
                    <td>
                        <form method="post" action="admin_dashboard.php">
                            <input type="hidden" name="delete_game" value="<?php echo $game['id']; ?>">
                            <button type="submit">Удалить игру</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Управление пользователями в комнатах -->
    <h2>Управление пользователями в комнатах</h2>
    
    <!-- Добавление пользователя в комнату -->
    <form method="post" action="admin_dashboard.php">
        <label for="user_login">Логин пользователя:</label>
        <input type="text" name="user_login" id="user_login" required>

        <label for="room_id">ID комнаты:</label>
        <input type="number" name="room_id" id="room_id" required><br>

        <button type="submit" name="add_user_to_room">Добавить пользователя в комнату</button>
    </form>

    <!-- Удаление пользователя из комнаты -->
    <form method="post" action="admin_dashboard.php">
        <label for="remove_user_login">Логин пользователя для удаления:</label>
        <input type="text" name="user_login" id="remove_user_login" required>

        <label for="remove_room_id">ID комнаты для удаления:</label>
        <input type="number" name="room_id" id="remove_room_id" required><br>

        <button type="submit" name="remove_user_from_room">Удалить пользователя из комнаты</button>
    </form>

    <!-- Кнопка выхода -->
    <form method="post" action="logout.php" class="logout">
        <button type="submit">Выйти</button>
    </form>

</div>

</body>
</html>
