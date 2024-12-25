<?php 
session_start();

// Проверка авторизации по токену
if (!isset($_SESSION['token'])) {
    header('Location: sign_in1.php');
    exit;
}

$token = $_SESSION['token'];

// Проверяем, что информация о комнате и игроках сохранена в сессии
if (!isset($_SESSION['room']) || !is_array($_SESSION['room'])) {
    echo 'Вы не находитесь в этой комнате, перенаправляем на страницу входа...';
    header('Location: sign_in1.php');
    exit;
}

$players = $_SESSION['room']['players'] ?? [];
$room = $_SESSION['room'] ?? []; // Получение информации о комнате из сессии
$cells = $_SESSION['room']['open_cells'] ?? [];

$message = ""; // По умолчанию ошибок нет
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра - Комната <?= htmlspecialchars($room['room_id'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="css/general.css">
    <link rel="stylesheet" href="css/play.css">
    <link rel="stylesheet" href="css/info.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
</head>
<body>
    <header>
        <?php include 'info.html'; ?>
        <script src="js/info-modal.js"></script>
        <div class="button-outline" id="timer">00:00</div>
        <button class="button-outline" id="openExitModal">ВЫЙТИ</button>
    </header>
    <main>
        <div class="game-board-wrapper">
            <div class="game-board" id="gameBoard"></div>

            <?php if (!empty($players)): ?>
                <script>
                    const playersData = <?php echo json_encode($players); ?>;
                </script>
            <?php else: ?>
                <p>Нет доступных игроков в&nbsp;комнате.</p>
            <?php endif; ?>

        </div>

        <div id="exitModal" class="modal">
            <div class="modal-content">
                <h2>Вы&nbsp;уверены, что хотите выйти из&nbsp;игры?</h2>
                <div class="exit_buttons">
                    <button class="usual-button" id="leaveGameBtn">Выйти из&nbsp;игры</button>
                    <button class="usual-button" id="backToGameListBtn">К&nbsp;списку игр</button>
                    <button class="usual-button" class="close" id="closeModal">Отмена</button>
                </div>
            </div>
        </div>

        <div id="winnerModal" style="display: none;">
            <div class="modal-content">
                <h2 id="winnerMessage">Поздравляем, вы&nbsp;победили!</h2>
                <div class="exit_buttons">
                    <button class="usual-button" id="closeWinnerModalBtn">Закрыть</button>
                </div>
            </div>
        </div>


    </main>
    <script>
        const token = '<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>';
        const idRoom = '<?= isset($room['room_id']) ? htmlspecialchars($room['room_id'], ENT_QUOTES, 'UTF-8') : '' ?>';
        const players = <?php echo json_encode($players); ?>; 
    </script>
    <script src="js/play.js"></script>
    <script  src="js/exit_game.js"></script>
</body>
</html>
