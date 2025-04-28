<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>

<?php include 'templates/header.php'; ?>

    <div class="main-content">
        <h2>Добро пожаловать в ProjectAir!</h2>
        <p>Система бронирования авиабилетов</p>

        <div class="features">
            <div class="feature">
                <h3>Новости</h3>
                <p>Последние обновления и акции</p>
                <a href="pages/news.php">Подробнее</a>
            </div>

            <div class="feature">
                <h3>Рейсы</h3>
                <p>Поиск доступных рейсов</p>
                <a href="pages/flights.php">Найти рейс</a>
            </div>
        </div>
    </div>

<?php include 'templates/footer.php'; ?>