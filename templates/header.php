<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авиаперевозки - Авиабилеты</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="container">
        <a href="/" class="logo">Авиаперевозки</a>
        <nav>
            <a href="/">Главная</a>
            <a href="/pages/news.php">Новости</a>
            <a href="/pages/flights.php" class="active">Рейсы</a>
            <?php if(is_logged_in()): ?>
                <a href="/pages/account.php">Кабинет</a>
                <a href="/includes/logout.php">Выйти</a>
            <?php else: ?>
                <a href="/pages/login.php">Вход</a>
                <a href="/pages/register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">