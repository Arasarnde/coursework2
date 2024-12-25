<?php
session_start();

// Проверяем, есть ли данные пользователя в сессии
if (!isset($_SESSION['token'])) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    header('Location: sign_in1.php');
    exit;
}

$user = $_SESSION['user'] ?? null;
$login = $user ? htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8') : 'Гость';

$rooms = $_SESSION['rooms'] ?? [];

function pluralForm($number, $forms) {
    $number = abs($number) % 100;
    $n1 = $number % 10;

    if ($number > 10 && $number < 20) {
        return $forms[2];
    }
    if ($n1 > 1 && $n1 < 5) {
        return $forms[1];
    }
    if ($n1 == 1) {
        return $forms[0];
    }
    return $forms[2];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/general.css" />
    <link rel="stylesheet" href="css/game_list.css" />
    <link rel="stylesheet" href="css/info.css" />
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <title>Список игр</title>
</head>
<body>
    <div class="main">
        <div class="welcome">
            <h1>Здравствуйте, <?= $login ?>!</h1>
            <button class="usual-button" id="openModal">СОЗДАТЬ ИГРУ</button>
        </div>
        <h2>Список игр</h2>
        <?php if (!empty($rooms)): ?>
            <div class="list_of_rooms">
            <?php foreach ($rooms as $room): 
                    $id_room = $room['room_id'];
                    $seats = $room['seats'];
                    $time_to_move = $room['time_to_move'];
                    $user_count = $room['players_count'];
                    ?>
                    <div class="room" data-id="<?= $room['room_id'] ?>">
                        <h3>Игра <?= htmlspecialchars($id_room, ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>Необходимо игроков: <?= htmlspecialchars($seats, ENT_QUOTES, 'UTF-8') ?></p>
                        <p>Время на ход: 
                            <?= htmlspecialchars($time_to_move, ENT_QUOTES, 'UTF-8') ?> 
                            <?= htmlspecialchars(pluralForm($time_to_move, ['секунда', 'секунды', 'секунд']), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p>Игроков в комнате: <?= htmlspecialchars($user_count, ENT_QUOTES, 'UTF-8') ?></p>
                        <button 
                            class="usual-button join-room" 
                            data-id="<?= htmlspecialchars($id_room, ENT_QUOTES, 'UTF-8') ?>">
                            ПРИСОЕДИНИТЬСЯ
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Нет доступных комнат.</p>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <p style="color: red;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>

    <div id="modalOverlay" class="modal-overlay"></div>
    <div id="createRoomModal" class="modal" data-error="<?= isset($message) ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : '' ?>">
        <h2>Создать игру</h2>
        <form id="createRoomForm">
            <input class="usual-input" type="number" name="seats" placeholder="Количество мест" required>
            <input class="usual-input" type="number" name="time_to_move" placeholder="Время на ход (в секундах)" required>
            <div class="buttons-modal">
                <button class="usual-button" type="submit">СОЗДАТЬ</button>
                <button class="usual-button" id="closeModal">ОТМЕНА</button>
            </div>
        </form>
        <p id="errorMessage">Нет ошибок</p>
    </div>

    <button class="button-outline" onclick="document.location='php/logout.php'">ВЫЙТИ</button>

    <?php include 'info.html'; ?>
    <script src="js/info-modal.js"></script>
    <script src="js/create_room.js"></script>
    <script src="js/update_rooms.js"></script>
</body>
</html>
