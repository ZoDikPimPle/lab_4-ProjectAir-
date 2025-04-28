<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Проверка наличия номера рейса
if (!isset($_GET['flight'])) {
    header("Location: flights.php");
    exit();
}

$flight_number = $_GET['flight'];
$flight = $pdo->prepare("SELECT * FROM flights WHERE flight_number = ?");
$flight->execute([$flight_number]);
$flight = $flight->fetch();

if (!$flight) {
    header("Location: flights.php");
    exit();
}

// Получаем тип самолета для отображения схемы мест
$aircraft = $pdo->prepare("SELECT * FROM aircraft_types WHERE type_code = ?");
$aircraft->execute([$flight['aircraft_type_code']]);
$aircraft = $aircraft->fetch();

// Бронирование
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_name = clean_input($_POST['passenger_name']);
    $seat_number = clean_input($_POST['seat_number']);

    try {
        $pdo->beginTransaction();

        // Создаем пассажира
        $stmt = $pdo->prepare("INSERT INTO passengers (full_name, flight_number) VALUES (?, ?)");
        $stmt->execute([$passenger_name, $flight_number]);
        $passenger_id = $pdo->lastInsertId();

        // Создаем билет
        $stmt = $pdo->prepare("INSERT INTO tickets (passenger_id, seat_number, price) VALUES (?, ?, ?)");
        $price = calculate_ticket_price($flight_number, $seat_number); // Функция расчета цены
        $stmt->execute([$passenger_id, $seat_number, $price]);
        $ticket_id = $pdo->lastInsertId();

        // Создаем запись о бронировании
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, flight_number, status) VALUES (?, ?, 'confirmed')");
        $stmt->execute([$_SESSION['user_id'], $flight_number]);

        $pdo->commit();

        header("Location: payment.php?ticket_id=" . $ticket_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Ошибка бронирования: " . $e->getMessage();
    }
}

// Получаем занятые места
$taken_seats = $pdo->query("SELECT seat_number FROM tickets WHERE passenger_id IN (SELECT passenger_id FROM passengers WHERE flight_number = '$flight_number')")
    ->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include '../templates/header.php'; ?>

    <div class="container">
        <h1>Бронирование рейса <?= $flight_number ?></h1>

        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="booking-card">
            <div class="flight-summary">
                <div class="route">
                    <span class="airport"><?= $flight['departure_airport'] ?></span>
                    <span class="time"><?= date('H:i', strtotime($flight['departure_time'])) ?></span>
                    →
                    <span class="airport"><?= $flight['arrival_airport'] ?></span>
                    <span class="time"><?= date('H:i', strtotime($flight['arrival_time'])) ?></span>
                </div>
                <div class="aircraft">Самолет: <?= $aircraft['name'] ?> (вместимость: <?= $aircraft['max_capacity'] ?> мест)</div>
            </div>

            <form method="POST">
                <div class="input-group">
                    <label>ФИО пассажира</label>
                    <input type="text" name="passenger_name" required>
                </div>

                <div class="seat-selection">
                    <h3>Выберите место</h3>
                    <div class="seats-grid">
                        <?php for ($row = 1; $row <= ceil($aircraft['max_capacity'] / 6); $row++): ?>
                            <div class="seat-row">
                                <?php for ($col = 1; $col <= 6; $col++): ?>
                                    <?php
                                    $seat_number = $row . chr(64 + $col);
                                    $is_taken = in_array($seat_number, $taken_seats);
                                    ?>
                                    <input
                                        type="radio"
                                        name="seat_number"
                                        id="seat-<?= $seat_number ?>"
                                        value="<?= $seat_number ?>"
                                        <?= $is_taken ? 'disabled' : '' ?>
                                        required
                                    >
                                    <label
                                        for="seat-<?= $seat_number ?>"
                                        class="seat <?= $is_taken ? 'taken' : '' ?>"
                                    >
                                        <?= $seat_number ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <button type="submit" class="btn">Забронировать</button>
            </form>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>