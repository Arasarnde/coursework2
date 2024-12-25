document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById('createRoomModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const errorMessage = document.getElementById('errorMessage');

    // Проверка на ошибку из PHP
    const error = modal.getAttribute('data-error');
    if (error) {
        errorMessage.textContent = error; // Устанавливаем текст ошибки
        errorMessage.style.color = 'red'; // Показываем ошибку
        modal.style.display = 'flex'; // Открываем модальное окно
        modalOverlay.style.display = 'block'; // Показываем затемнение
    }

    // Открытие модального окна
    openModalBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
        modalOverlay.style.display = 'block';
        errorMessage.style.color = 'var(--container-bg-color)'; // Скрываем старые ошибки при новом открытии
    });

    // Закрытие модального окна
    closeModalBtn.addEventListener('click', (event) => {
        event.preventDefault();
        modal.style.display = 'none';
        modalOverlay.style.display = 'none';
        errorMessage.style.color = 'var(--container-bg-color)';
    });

    window.addEventListener('click', (event) => {
        if (event.target === modalOverlay) {
            modal.style.display = 'none';
            modalOverlay.style.display = 'none';
        }
    });

    // Обработка создания комнаты через AJAX
    const createRoomForm = document.getElementById('createRoomForm');
    createRoomForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const seats = createRoomForm.elements['seats'].value;
        const time_to_move = createRoomForm.elements['time_to_move'].value;
        const errorMessage = document.getElementById('errorMessage');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/create_room.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);

                if (response.success) {
                    window.location.reload(); // Перезагрузка страницы
                } else {
                    // Показываем сообщение об ошибке
                    errorMessage.textContent = response.message || 'Произошла ошибка. Попробуйте снова.';
                    errorMessage.style.color = 'red';
                }
            }
        };

        xhr.send(`seats=${encodeURIComponent(seats)}&time_to_move=${encodeURIComponent(time_to_move)}`);
    });

    const roomsList = document.querySelector('.list_of_rooms');

    if (roomsList) {
        roomsList.addEventListener('click', (event) => {
            if (event.target.classList.contains('join-room')) {
                const roomId = event.target.dataset.id;
                if (roomId) {
                    window.location.href = `php/game.php?id_room=${roomId}`;
                }
            }
        });
    } else {
        console.error('Элемент .list_of_rooms не найден.');
    }

});