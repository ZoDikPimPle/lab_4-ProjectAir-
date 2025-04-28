<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Пример данных (вообще нужно из бд подтягивать, но сделал пока так
$news = [
    [
        'id' => 1,
        'title' => 'Новый рейс Москва — Сочи',
        'date' => '2025-05-15',
        'content' => 'С 1 июня открывается новый ежедневный рейс из Шереметьево в Сочи. Время в пути — 2 часа 20 минут.',
        'image' => 'news1.jpg' // пока без фото
    ],
    [
        'id' => 2,
        'title' => 'Скидки 20% на раннее бронирование',
        'date' => '2025-05-10',
        'content' => 'Забронируйте билеты до 30 июня и получите скидку 20% на все рейсы эконом-класса.',
        'image' => 'news2.jpg'
    ],
    [
        'id' => 3,
        'title' => 'Обновление парка самолетов',
        'date' => '2025-05-05',
        'content' => 'В этом году наша авиакомпания получит 5 новых лайнеров Airbus A320neo.',
        'image' => 'news3.jpg'
    ]
];
?>

<?php include '../templates/header.php'; ?>

    <div class="container">
        <h1>Новости</h1>

        <div class="news-grid">
            <?php foreach ($news as $item): ?>
                <div class="news-card">
                    <?php if ($item['image']): ?>
                        <div class="news-image">
                            <img src="/assets/images/<?= $item['image'] ?>" alt="<?= $item['title'] ?>">
                        </div>
                    <?php endif; ?>

                    <div class="news-content">
                        <h3><?= $item['title'] ?></h3>
                        <div class="news-meta">
                            <span class="date"><?= date('d.m.Y', strtotime($item['date'])) ?></span>
                        </div>
                        <p><?= $item['content'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>