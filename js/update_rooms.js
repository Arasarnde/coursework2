document.addEventListener("DOMContentLoaded", () => {
    function pluralForm(number, forms) {
        number = Math.abs(number) % 100;
        let n1 = number % 10;

        if (number > 10 && number < 20) {
            return forms[2];
        }
        if (n1 > 1 && n1 < 5) {
            return forms[1];
        }
        if (n1 === 1) {
            return forms[0];
        }
        return forms[2];
    }

    // Функция для получения списка комнат с сервера
    function fetchRooms() {
        fetch('php/update_game_list.php')  
            .then(response => response.json())
            .then(data => {
                if (data.rooms) {
                    //console.log('Ошибка: ' + JSON.stringify(data.rooms, null, 2));
                    updateRoomList(data.rooms); 
                } else if (data.error) {
                    console.log('Ошибка: ' + data.error);
                }
            })
            .catch(error => {
                console.log('Ошибка при получении данных:', error);
            });
    }

    // Функция для обновления списка комнат
    function updateRoomList(rooms) {
        //console.log('Обновление DOM. Количество комнат:', rooms.length);
        const roomsListElement = document.querySelector('.list_of_rooms');
        if (!roomsListElement) {
            console.error('Элемент списка комнат не найден.');
            return;
        }

        const existingRoomIds = new Set();

        // Обрабатываем комнаты, которые уже есть на странице
        const currentRooms = roomsListElement.querySelectorAll('.room');
        currentRooms.forEach(roomElement => {
            const roomId = roomElement.dataset.id;
            if (roomId) {
                existingRoomIds.add(roomId);
            }
        });

        // Обновляем или добавляем новые комнаты
        rooms.forEach(room => {
            const roomId = String(room.room_id);
            if (!existingRoomIds.has(roomId)) {
                console.log('Добавление новой комнаты:', room);
                // Добавляем новую комнату
                const roomElement = document.createElement('div');
                roomElement.classList.add('room');
                roomElement.dataset.id = room.room_id;
                roomElement.innerHTML = `
                    <h3>Игра ${room.room_id}</h3>
                    <p>Необходимо игроков: ${room.seats}</p>
                    <p>Время на ход: ${room.time_to_move}  ${pluralForm(room.time_to_move, ['секунда', 'секунды', 'секунд'])}</p>
                    <p>Игроков в комнате: ${room.players_count}</p>
                    <button class="usual-button join-room" data-id="${room.room_id}">ПРИСОЕДИНИТЬСЯ</button>
                `;
                roomsListElement.appendChild(roomElement);
            } else {
                // Обновляем количество игроков в существующей комнате
                const roomElement = roomsListElement.querySelector(`[data-id="${roomId}"]`);
                if (roomElement) {
                    const playersCountElement = roomElement.querySelector('p:nth-child(4)');
                    if (playersCountElement) {
                        playersCountElement.textContent = `Игроков в комнате: ${room.players_count}`;
                    }
                }
            }
        });

        // Удаляем комнаты, которые больше не существуют
        currentRooms.forEach(roomElement => {
            const roomId = roomElement.dataset.id;
            if (!rooms.some(room => String(room.room_id) === roomId)) {
                roomsListElement.removeChild(roomElement);
            }
        });

        //console.log(rooms.length + ' ' + existingRoomIds.size);
        //if (rooms.length != existingRoomIds.size) {
            //window.location.reload(); // Перезагрузка страницы
        //    return; 
        //}
    }

    fetchRooms();
    setInterval(() => {
        console.log('Обновление списка комнат...');
        fetchRooms();
    }, 5000);  // Обновление каждые 5 секунд

});
