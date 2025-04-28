<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_GET['ticket_id'])) {
    header("Location: flights.php");
    exit();
}

$ticket_id = $_GET['ticket_id'];
$ticket = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
$ticket->execute([$ticket_id]);
$ticket = $ticket->fetch();

if (!$ticket) {
    header("Location: flights.php");
    exit();
}

// Имитация оплаты
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("UPDATE bookings SET status = 'paid' WHERE flight_number = (SELECT flight_number FROM passengers WHERE passenger_id = ?)")
        ->execute([$ticket['passenger_id']]);

    header("Location: account.php?payment=success");
    exit();
}
?>

<?php include '../templates/header.php'; ?>

    <div class="container">
        <div class="payment-card">
            <h2>Оплата билета</h2>
            <div class="ticket-info">
                <p><strong>Номер билета:</strong> <?= $ticket_id ?></p>
                <p><strong>Место:</strong> <?= $ticket['seat_number'] ?></p>
                <p><strong>Цена:</strong> <?= number_format($ticket['price'], 2) ?> ₽</p>
            </div>

            <form method="POST">
                <div class="input-group">
                    <label>Номер карты</label>
                    <input type="text" placeholder="0000 0000 0000 0000" required>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label>Срок действия</label>
                        <input type="text" placeholder="MM/YY" required>
                    </div>

                    <div class="input-group">
                        <label>CVV</label>
                        <input type="text" placeholder="123" required>
                    </div>
                </div>

                <button type="submit" class="btn">Оплатить</button>
            </form>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>