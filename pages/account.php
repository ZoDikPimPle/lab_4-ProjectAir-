<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Получаем бронирования пользователя
$bookings = $pdo->prepare("
    SELECT b.*, f.departure_time, f.arrival_time, f.departure_airport, f.arrival_airport
    FROM bookings b
    JOIN flights f ON b.flight_number = f.flight_number
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$bookings->execute([$_SESSION['user_id']]);
$bookings = $bookings->fetchAll();

// Обработка оплаты всех билетов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_all'])) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE bookings SET status = 'paid' WHERE user_id = ? AND status = 'pending'");
        $stmt->execute([$_SESSION['user_id']]);

        $pdo->commit();

        header("Location: account.php?payment=success");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Ошибка оплаты: " . $e->getMessage());
        $error = "Произошла ошибка при оплате. Пожалуйста, попробуйте позже.";
    }
}
?>

<?php include '../templates/header.php'; ?>

<div class="container">
    <h1>Личный кабинет</h1>

    <?php if (isset($_GET['payment']) && $_GET['payment'] === 'success'): ?>
        <div class="success">Оплата прошла успешно!</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <div class="bookings-list">
        <h2>Мои бронирования</h2>

        <?php if (empty($bookings)): ?>
            <div class="no-bookings">У вас нет бронирований</div>
        <?php else: ?>
            <form method="POST">
                <button type="submit" name="pay_all" class="btn">Оплатить все билеты разом</button>
            </form>

            <?php foreach ($bookings as $booking): ?>
                <div class="booking-item">
                    <div class="booking-header">
                        <span class="flight-number">Рейс <?= $booking['flight_number'] ?></span>
                        <span class="status <?= $booking['status'] ?>"><?= $booking['status'] === 'paid' ? 'Оплачено' : 'Ожидает оплаты' ?></span>
                    </div>

                    <div class="booking-body">
                        <div class="route">
                            <span class="airport"><?= $booking['departure_airport'] ?></span>
                            <span class="time"><?= date('d.m.Y H:i', strtotime($booking['departure_time'])) ?></span>
                            →
                            <span class="airport"><?= $booking['arrival_airport'] ?></span>
                            <span class="time"><?= date('d.m.Y H:i', strtotime($booking['arrival_time'])) ?></span>
                        </div>

                        <div class="booking-date">
                            Забронировано: <?= date('d.m.Y H:i', strtotime($booking['booking_date'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

