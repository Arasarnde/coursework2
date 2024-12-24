<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Смена пароля</title>
    <link rel="stylesheet" href="css/general.css" />
    <link rel="stylesheet" href="css/sign_in.css" />
</head>
<body>
    <div class="container">
        <h1>Смена пароля</h1>
        <p><a href="sign_in1.php">Назад к&nbsp;входу</a></p>
        <form id="changePasswordForm">
            <input class="usual-input" type="text" name="login" placeholder="Логин" required>
            <input class="usual-input" type="password" name="old_password" placeholder="Старый пароль" required>
            <input class="usual-input" type="password" name="new_password" placeholder="Новый пароль" required>
            <button class="usual-button" type="submit">Сменить пароль</button>
        </form>
        <p id="message"></p>
    </div>

    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const login = document.querySelector('[name="login"]').value;
            const old_password = document.querySelector('[name="old_password"]').value;
            const new_password = document.querySelector('[name="new_password"]').value;
            const messageDiv = document.getElementById('message');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/change_password.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    messageDiv.textContent = response.message || '';

                    if (response.success) {
                        // Успешная смена пароля
                        messageDiv.style.color = 'green';
                    } else {
                        // Ошибка при смене пароля
                        messageDiv.style.color = 'red';
                    }
                }
            };

            xhr.send(`login=${encodeURIComponent(login)}&old_password=${encodeURIComponent(old_password)}&new_password=${encodeURIComponent(new_password)}`);
        });
    </script>
</body>
</html>
