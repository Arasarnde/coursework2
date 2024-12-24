// Открыть модальное окно
document.getElementById('openExitModal').addEventListener('click', function() {
    document.getElementById('exitModal').style.display = 'block';
});

// Закрыть модальное окно при клике на крестик
document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('exitModal').style.display = 'none';
});

// Закрыть модальное окно при клике вне его
window.addEventListener('click', function(event) {
    const modal = document.getElementById('exitModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});

// Переход к списку игр
document.getElementById('backToGameListBtn').addEventListener('click', function() {
    window.location.href = 'game_list.php';  // Перенаправление на страницу списка игр
});

// Выйти из игры и удалить игрока из комнаты
document.getElementById('leaveGameBtn').addEventListener('click', function() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'php/leave_game.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    window.location.href = 'game_list.php';  // Перенаправление после успешного выхода
                } else {
                    alert(response.message);
                }
            } catch (e) {
                alert('Ошибка при обработке ответа сервера.');
            }
        }
    };
    xhr.send(`id_room=${encodeURIComponent(idRoom)}`);
});

let winnerPlayer = null;

// Функция для отображения модального окна
function showWinnerModal(winnerName) {
    if (winnerPlayer === null) {
        winnerPlayer = winnerName;
    }
    const modal = document.getElementById('winnerModal');
    const winnerMessage = document.getElementById('winnerMessage');

    // Обновляем текст в модальном окне
    winnerMessage.innerHTML = `Игра закончилась. ${winnerPlayer} победил!`;

    modal.style.display = 'flex'; // Показываем окно
}

// Закрытие модального окна при нажатии на кнопку
document.getElementById('closeWinnerModalBtn').addEventListener('click', function() {
    const modal = document.getElementById('winnerModal');
    modal.style.display = 'none'; // Закрываем окно

    // Автоматический выход из игры после победы
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'php/leave_game.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    window.location.href = 'game_list.php';  // Перенаправление после выхода
                } else {
                    alert(response.message);
                }
            } catch (e) {
                alert('Ошибка при обработке ответа сервера.');
            }
        }
    };
    xhr.send(`id_room=${encodeURIComponent(idRoom)}`);
});
