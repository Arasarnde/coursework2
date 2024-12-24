<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="css/general.css" />
    <link rel="stylesheet" href="css/sign_in.css" />
    <link rel="stylesheet" href="css/info.css" />
</head>
<body>
    <div class="container">
        <h1>РЕГИСТРАЦИЯ</h1>
        <p>Уже есть аккаунт? <a href="sign_in1.php">Войти</a></p>
        <form id="registrationForm">
            <input class="usual-input" id="login" type="text" name="login" placeholder="Логин" required>
            <input class="usual-input" id="password" type="password" name="password" placeholder="Пароль" required>
            <button class="usual-button" type="submit">ЗАРЕГИСТРИРОВАТЬСЯ</button>
        </form>
        <p id="message"></p>
    </div>
    <?php include 'info.html'; ?>
    <script src="js/info-modal.js"></script>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', function (event) {
            event.preventDefault();
            const login = document.getElementById('login').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/sign_up.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    messageDiv.textContent = response.message || '';

                    if (response.success) {
                        // Перенаправление на страницу списка игр
                        window.location.href = 'game_list.php';
                    }
                }
            };

            xhr.send(`login=${encodeURIComponent(login)}&password=${encodeURIComponent(password)}`);
        });
    </script>
</body>
</html>
