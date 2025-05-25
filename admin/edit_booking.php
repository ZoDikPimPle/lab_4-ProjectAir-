<?php
require_once 'db.php';
include 'header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID брони не указан");
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $stmt->execute([$status, $id]);
    echo "<p style='color:green;'>Бронирование обновлено.</p>";
}

// Получаем данные брони
$stmt = $pdo->prepare("
    SELECT b.*, u.email
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    WHERE booking_id = ?
");
$stmt->execute([$id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Бронирование не найдено");
}

?>

<h2>Редактировать бронирование #<?=htmlspecialchars($id)?></h2>
<p>Пользователь: <?=htmlspecialchars($booking['email'])?></p>
<p>Рейс: <?=htmlspecialchars($booking['flight_number'])?></p>
<p>Дата брони: <?=htmlspecialchars($booking['booking_date'])?></p>

<form method="POST">
    <label>Статус:<br />
        <input type="text" name="status" value="<?=htmlspecialchars($booking['status'])?>" required />
    </label><br /><br />
    <button type="submit">Сохранить</button>
</form>

<?php include 'footer.php'; ?>
