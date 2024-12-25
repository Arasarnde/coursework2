// === Глобальные переменные ===
const timerElement = document.getElementById('timer');
let timerInterval;

const gameBoard = document.getElementById('gameBoard');
let selectedPirate = null;
let selectedShip = null;
let selectedPirateId = null; 

// Функция запуска таймера
function startTimer(timeLeft) {
    if (timerInterval) clearInterval(timerInterval);

    // Проверяем, что timeLeft — это число
    if (typeof timeLeft !== 'number' || isNaN(timeLeft)) {
        console.error('Ошибка: timeLeft не является числом!', timeLeft);
        return;  // Если это не число, прерываем выполнение функции
    }

    timerInterval = setInterval(() => {
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            handleTimeOut();
            window.onload = updateGameState;
            return;
        }

        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        timeLeft--;
    }, 1000);
}


function handleTimeOut() {
    showError('Время истекло! Передача хода следующему игроку.');
}

// Функция для обработки выбора пирата
function handlePirateSelection(pirate) {
    // Убираем выделение с предыдущего пирата, если он был
    if (selectedPirate) {
        selectedPirate.classList.remove('selected-pirate');
    }

    if (selectedShip) {
        selectedShip.classList.remove('selected-ship');
    }
    
    // Сохраняем ID выбранного пирата
    selectedPirateId = pirate.dataset.pirateIndex;

    // Сохраняем ссылку на выбранного пирата
    selectedPirate = pirate;
    
    // Добавляем класс выделения для текущего пирата
    selectedPirate.classList.add('selected-pirate');

    const pirateCell = selectedPirate.closest('.cell');

    if (pirateCell) {
        const pirateCellIndex = parseInt(pirateCell.dataset.cellIndex, 10);
        highlightAvailableCells(pirateCellIndex);
    }
}

function highlightAvailableCells(pirateCellIndex) {
    const cellsToHighlight = [
        pirateCellIndex + 1, pirateCellIndex + 10, pirateCellIndex + 12,
        pirateCellIndex - 10, pirateCellIndex - 12, pirateCellIndex - 1,
        pirateCellIndex + 11, pirateCellIndex - 11
    ];

    clearCellHighlights();

    cellsToHighlight.forEach(index => {
        const cell = document.querySelector(`.cell[data-cell-index="${index}"]`);
        if (cell && !cell.classList.contains('for-ship')) {
            cell.classList.add('highlight-cell');
            cell.addEventListener('click', handleCellClick);
        }
    });
}

function resetHighlights() {
    clearCellHighlights();
}

function handleCellClick(event) {
    const targetCell = event.currentTarget;
    movePirateToCell(targetCell);
}

function movePirateToCell(cell) {

    if (!selectedPirate) return;

    const pirateId = selectedPirateId;
    const targetCellIndex = parseInt(cell.dataset.cellIndex);

    fetch(`php/make_move_by_pirate.php?token=${token}&pirate_id=${pirateId}&target_cell=${targetCellIndex}`)
        .then(response => {
            if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
        .then(data => {
            if (data.success) {
                // window.onload = updateGameState;
                selectedPirate = null;
                const gameState = data.game_state;
                let parsedGameState;
                try {
                    parsedGameState = JSON.parse(gameState.make_move_by_pirate);  // Преобразуем строку JSON в объект
                } catch (error) {
                    console.error("Ошибка при разборе JSON:", error);
                    return;  // Если ошибка парсинга, не продолжаем выполнение
                }

                if (parsedGameState.success) {
                    // Извлекаем данные о комнате
                    const gameData = parsedGameState.game_state || {};  // Получаем данные о комнате
                    //console.log('GameData',gameData);
                    const roomData = gameData.game_state || {};  // Получаем данные о комнате

                    //console.log('RoomData',roomData);
                    if (gameData.winner) {
                        showWinnerModal(gameData.winner.player_id);
                    }
                    startTimer(-roomData.time_diff_seconds);
                    createBoard(roomData); // Функция для обновления доски игры
                    updatePlayersInfo(roomData); // Обновление информации о игроках
                    highlightActivePlayer(roomData); // Выделение активного игрока
                } else {
                    showError(parsedGameState.result_message);
                }
            } else {
                //console.log(data);
                console.error('Ошибка: ', data.error);
            }
        })
        .catch(error => {
            console.error('Ошибка запроса: ', error);
        });
}

// Функция для показа уведомления
function showError(message) {
    // Создаем элемент для сообщения об ошибке
    const notification = document.createElement('div');
    notification.classList.add('error-notification');
    notification.innerText = message;

    // Добавляем элемент на страницу
    document.body.appendChild(notification);

    // Устанавливаем таймер на 30 секунд для удаления уведомления
    setTimeout(() => {
        notification.remove();
    }, 30000);
}


// Функция для обработки выбора корабля
function handleShipSelection(shipElement) {
    if (selectedPirate) {
        showError('Корабль не может быть выбран, пока выбран пират');
        return; // Если пират выбран, не даем выбрать корабль
    }

    // Убираем выделение с предыдущего корабля, если он был
    if (selectedShip) {
        selectedShip.classList.remove('selected-ship');
    }

    // Сохраняем ссылку на выбранный корабль
    selectedShip = shipElement;
    
    // Добавляем класс выделения для текущего корабля
    selectedShip.classList.add('selected-ship');

    const shipCell = selectedShip.closest('.cell');
    if (shipCell) {
        const shipCellIndex = parseInt(shipCell.dataset.cellIndex, 10);
        highlightAvailableMoveCells(shipCellIndex);
    }
}

// === Функция для подсветки доступных клеток для перемещения корабля ===
function highlightAvailableMoveCells(shipCellIndex) {
    // Получаем соседние клетки для движения корабля
    const cellsToHighlightV = [
        shipCellIndex + 11,
        shipCellIndex - 11
    ];

    const cellsToHighlightG = [
        shipCellIndex + 1, 
        shipCellIndex - 1
    ];


    // Очищаем старые выделения
    clearCellHighlights();

    if (shipCellIndex >= 10 && shipCellIndex <= 112) {
        // Подсвечиваем клетки, которые можно выбрать для перемещения
        cellsToHighlightV.forEach(index => {
            const cell = document.querySelector(`.cell[data-cell-index="${index}"]`);
            if (cell && !cell.classList.contains('ship') && cell.classList.contains('for-ship') && !cell.classList.contains('highlighted-for-ship')) {
                cell.classList.add('highlighted-for-ship');
                cell.addEventListener('click', handleCellClickForShip);
            }
        });
    } else {
        cellsToHighlightG.forEach(index => {
            const cell = document.querySelector(`.cell[data-cell-index="${index}"]`);
            if (cell && !cell.classList.contains('ship') && cell.classList.contains('for-ship') && !cell.classList.contains('highlighted-for-ship')) {
                cell.classList.add('highlighted-for-ship');
                cell.addEventListener('click', handleCellClickForShip);
            }
        });
    }
}

// Функция для сброса подсветки клеток
function resetShipHighlights() {
    clearCellHighlights();
}

// Функция для обработки клика по клетке с кораблем
function handleCellClickForShip(event) {
    const targetCell = event.currentTarget;
    moveShipToCell(targetCell);
}

// Функция для очистки выделений 
function clearCellHighlights() {
    const highlightedCells = document.querySelectorAll('.cell.for-ship.highlighted-for-ship');
    highlightedCells.forEach(cell => {
        cell.classList.remove('highlighted-for-ship');
        cell.removeEventListener('click', handleCellClickForShip);
    });

    document.querySelectorAll('.cell').forEach(cell => {
        cell.classList.remove('highlight-cell');
        cell.removeEventListener('click', handleCellClick);
    });
}


// Функция для обновления состояния игры с сервера
function updateGameState() {

    fetch(`php/game_update.php?token=${token}&id_room=${idRoom}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновление состояния игры
                const gameState = data.game_state;
                let parsedGameState;
                try {
                    parsedGameState = JSON.parse(gameState.game_control);  // Преобразуем строку JSON в объект
                } catch (error) {
                    console.error("Ошибка при разборе JSON:", error);
                    return;  // Если ошибка парсинга, не продолжаем выполнение
                }

                if (parsedGameState.winner) {
                    showWinnerModal(parsedGameState.winner.player_id);
                }
                // Извлекаем данные о комнате
                const roomData = parsedGameState.room || {};  // Получаем данные о комнате

                startTimer(-roomData.time_diff_seconds);
                createBoard(roomData); // Функция для обновления доски игры
                updatePlayersInfo(roomData); // Обновление информации о игроках
                highlightActivePlayer(roomData); // Выделение активного игрока

                selectedPirate = null;
            } else {
                console.error('Ошибка: ', data.message);
            }
        })
        .catch(error => {
            console.error('Ошибка запроса: ', error);
        });
}

function createBoard(roomData) {
    const openCells = roomData.open_cells || [];  // Открытые клетки
    const pirates = roomData.pirates || [];  // Пираты в комнате
    const goldOnCells = roomData.gold_on_cells || [];  // Монеты на клетках

    gameBoard.innerHTML = ''; // Очищаем игровое поле

    const totalCells = 11 * 11;
    // Создаем клетки
    for (let i = 1; i < totalCells+1; i++) {
        const cell = document.createElement('div');
        cell.classList.add('cell');
        cell.setAttribute('data-cell-index', i);

        // Добавляем клетки с кораблями
        const cellData = openCells.find(cell => cell.cell_number === i);
        if (cellData) {
            if (cellData.cell_name === 'корабль') {
                cell.classList.add('ship');
            }

            if (cellData.cell_name === 'пустая') {
                cell.classList.add('for-ship');
            }

            if (cellData.cell_name === 'не существует' ) { 
                cell.classList.add('empty');
            }

            if (cellData.cell_name === 'крокодил') {
                cell.classList.add('crocodile');
            }

            if (cellData.cell_name === 'капкан') {
                cell.classList.add('trap');
            }

            if (cellData.cell_name === '1 монета' || cellData.cell_name === '2 монеты' || cellData.cell_name === '3 монеты' || cellData.cell_name === '4 монеты' || cellData.cell_name === '5 монет') {
                cell.classList.add('chest');
            }

            if (cellData.cell_name === 'бочка') {
                cell.classList.add('barrel');
            }

            if (cellData.cell_name === 'обычная') {
                cell.classList.add('regular');
            }

            if (cellData.cell_name === 'воздушный шар') {
                cell.classList.add('balloon');
            }

            if (cellData.cell_name === 'крепость') {
                cell.classList.add('fortress');
            }
        }

        const cellGold = goldOnCells.filter(gold => gold.cell_number === i);
        if (cellGold.length > 0) {
            // Считаем количество монет на клетке
            const coinCount = cellGold.length;
            // Создаем элемент монеты
            const coinElement = document.createElement('div');
            coinElement.classList.add('coin');
            coinElement.textContent = coinCount;  // Устанавливаем количество монет
            cell.appendChild(coinElement);
        }

        // Добавляем пиратов в клетки
        const cellPirates = pirates.filter(pirate => pirate.cell_number === i);
        if (cellPirates.length > 0) {
            const piratesContainer = createPiratesContainer(cell);
            cellPirates.forEach(pirate => {
                const pirateElement = document.createElement('div');
                pirateElement.classList.add('pirate', pirate.player_id);
                pirateElement.setAttribute('data-pirate-index', pirate.pirate_id);
                if (!pirate.move_available) pirateElement.style.opacity = '0.5';
                // Добавляем обработчик для выделения пирата
                pirateElement.addEventListener('click', (event) => {
                    // Прекращаем распространение события, чтобы не сработал обработчик для клетки
                    event.stopPropagation();
                    handlePirateSelection(pirateElement); // Выделяем пирата
                });
                piratesContainer.appendChild(pirateElement);
            });
        }

        // Проверка для установки класса 'empty' на определенные клетки
        const shipPositions = [6, 56, 66, 116];
        const cellIndex = parseInt(cell.dataset.cellIndex, 10);
        if (shipPositions.includes(cellIndex)) {
            if (!cell.classList.contains('ship') && !cell.classList.contains('for-ship')) {
                cell.classList.add('empty');
            } else {
                cell.classList.remove('empty');
            }
        }

        gameBoard.appendChild(cell);  // Добавляем клетку в игровое поле
    }
}


// Создание контейнера для пиратов
function createPiratesContainer(cell) {
    const piratesContainer = document.createElement('div');
    piratesContainer.classList.add('pirates');
    cell.appendChild(piratesContainer);
    return piratesContainer;
}

function moveShipToCell(cell) {
    // Получаем индекс клетки, в которую игрок хочет переместить корабль
    const targetCellIndex = parseInt(cell.dataset.cellIndex, 10);

    const playerId = selectedShip.getAttribute('data-player-id'); // Извлекаем player-id из клетки
    console.log(playerId);
    if (!playerId) {
        console.error('Ошибка: не найден player-id для корабля!');
        return;
    }


    // Отправляем запрос на сервер для перемещения корабля
    fetch(`php/move_ship_and_pirates.php?token=${token}&player_id=${playerId}&target_cell=${targetCellIndex}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const gameState = data.game_state;
                let parsedGameState;
                try {
                    parsedGameState = JSON.parse(gameState.move_ship_and_pirates);  // Преобразуем строку JSON в объект
                } catch (error) {
                    console.error("Ошибка при разборе JSON:", error);
                    return;  // Если ошибка парсинга, не продолжаем выполнение
                }

                console.log('parsedGameState',parsedGameState);

                if (parsedGameState.success) {
                    // Извлекаем данные о комнате
                    const gameData = parsedGameState.game_state || {};  // Получаем данные о комнате
                    console.log('GameData',gameData);
                    const roomData = gameData.game_state || {};  // Получаем данные о комнате

                    console.log('RoomData',roomData);
                    if (roomData.winner) {
                        showWinnerModal(roomData.winner.player_id);
                    }
                    startTimer(-roomData.time_diff_seconds);
                    createBoard(roomData); // Функция для обновления доски игры
                    updatePlayersInfo(roomData); // Обновление информации о игроках
                    highlightActivePlayer(roomData); // Выделение активного игрока
                    // selectedPirate = null;
                } else {
                    showError(parsedGameState.result_message);
                }
            } else {
                console.error('Ошибка: ', data.error);
            }
        })
        .catch(error => {
            console.error('Ошибка запроса: ', error);
        });
}

// Массив с аватарами
const avatars = {
    'white': 'images/player1-avatar.png',
    'black': 'images/player2-avatar.png',
    'blue': 'images/player3-avatar.png',
    'red': 'images/player4-avatar.png'
};

// Массив с позициями игроков на доске
const positionClasses = {
    'white': 'player1', // Клетка 5 -> player1
    'black': 'player2', // Клетка 56 -> player2
    'blue': 'player3',
    'red': 'player4',
};

// Функция для создания объекта игрока с позицией в зависимости от его корабля
function createPlayerObject(player) {
    //const avatarIndex = openCells.indexOf(player.ship_position) % avatars.length; // Индекс для аватара
    const avatarSrc = avatars[player.color];

    // Сопоставляем позицию корабля с классом для расположения игрока
    const positionClass = positionClasses[player.color];

    return {
        player_id: player.player_id,
        login: player.login,
        coins: player.coins,
        color: player.color,
        ship_position: player.ship_position,
        avatarSrc: avatarSrc,
        positionClass: positionClass, // Класс для позиционирования игрока
        colorClass: `player-${player.color}`  // Цвет игрока
    };
}

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

function updatePlayersInfo(roomData) {
    const players = roomData.players || []; // Игроки в комнате
    const pirates = roomData.pirates || []; // Пираты в комнате
    const openCells = roomData.open_cells || []; // Клетки с кораблями в комнате
    const gameBoard = document.querySelector('.game-board-wrapper'); // Ссылка на элемент доски игры

    // Создаем массив объектов игроков
    const playerObjects = players.map(player => createPlayerObject(player));

    // Обновляем или создаем новые элементы для игроков
    playerObjects.forEach((playerObj) => {
        const playerElement = document.querySelector(`#player${playerObj.player_id}`);
        //console.log(playerElement);

        // Если игрок уже существует на странице, обновляем его информацию
        if (playerElement) {
            playerElement.querySelector('.money').textContent = `${playerObj.coins} ${pluralForm(playerObj.coins, ['монета', 'монеты', 'монет'])}`;
        } else {
            // Если игрока нет на странице, создаем новый элемент
            const newPlayerElement = document.createElement('div');
            newPlayerElement.classList.add('player');
            newPlayerElement.id = `player${playerObj.player_id}`;

            // Добавляем информацию об игроке
            newPlayerElement.innerHTML = `
                <img src="${playerObj.avatarSrc}" alt="player${playerObj.player_id}">
                <div class="player-info">
                    <p class="name">${playerObj.login}</p>
                    <p class="money">${playerObj.coins} ${pluralForm(playerObj.coins, ['монета', 'монеты', 'монет'])}</p>
                </div>
            `;

            // Добавляем новую позицию игрока
            newPlayerElement.classList.add(playerObj.positionClass);
            gameBoard.appendChild(newPlayerElement);
        }

        // Покраска клеток корабля (красим только клетки, принадлежащие игроку)
        openCells.forEach(cell => {
            if (cell.cell_name === 'корабль' && cell.cell_number === playerObj.ship_position) {
                const cellElement = document.querySelector(`[data-cell-index="${cell.cell_number}"]`);
                if (cellElement) {
                    cellElement.setAttribute('data-player-id', playerObj.player_id); // Добавляем атрибут player-id
                    cellElement.classList.add(playerObj.colorClass); // Добавляем цвет клетки
                    cellElement.addEventListener('click', (event) => {
                        event.stopPropagation(); // Предотвращаем выделение пирата при клике на корабль
                        handleShipSelection(cellElement); // Выделяем корабль
                    });        
                }
            }
        });

        // Покраска пиратов (теперь добавляем класс только для пиратов, принадлежащих игроку)
        pirates.forEach(pirate => {
            if (pirate.player_id === playerObj.player_id) {
                const pirateElement = document.querySelector(`[data-pirate-index="${pirate.pirate_id}"]`);
                if (pirateElement) {
                    pirateElement.classList.add(playerObj.colorClass); // Добавляем цвет пирату
                }
            }
        });
    });
}


// Подсветка активного игрока
function highlightActivePlayer(roomData) {
    const playerId = roomData.current_player || [];  // Игроки в комнате

    document.querySelectorAll('.player').forEach(player => {
        player.classList.remove('active');
    });
    const activePlayer = document.querySelector(`#player${playerId}`);
    if (activePlayer) {
        activePlayer.classList.add('active');
    }
}

// Обновление состояния игры каждую секунду
setInterval(updateGameState, 5000);
window.onload = updateGameState;
