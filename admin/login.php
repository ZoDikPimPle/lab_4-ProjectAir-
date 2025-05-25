<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Для примера - хардкод админских данных
    $admin_user = 'admin';
    $admin_pass = '12345'; // обязательно хешировать в реальном проекте

    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['role'] = 'admin';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Вход в админку</title>
</head>
<body>
<h2>Авторизация администратора</h2>
<?php if ($error): ?>
    <p style="color:red;"><?=htmlspecialchars($error)?></p>
<?php endif; ?>
<form method="POST">
    <label>Логин:<br />
        <input type="text" name="username" required />
    </label><br /><br />
    <label>Пароль:<br />
        <input type="password" name="password" required />
    </label><br /><br />
    <button type="submit">Войти</button>
</form>
</body>
</html>
