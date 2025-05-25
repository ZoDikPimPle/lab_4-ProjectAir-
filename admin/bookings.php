<?php
require_once 'db.php';
include 'header.php';

// Получаем список бронирований с пользователями и рейсами
$stmt = $pdo->query("
    SELECT b.booking_id, u.email, b.flight_number, b.booking_date, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    ORDER BY b.booking_date DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Бронирования</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID брони</th>
        <th>Пользователь (email)</th>
        <th>Номер рейса</th>
        <th>Дата брони</th>
        <th>Статус</th>
        <th>Действия</th>
    </tr>
    <?php foreach($bookings as $b): ?>
        <tr>
            <td><?=htmlspecialchars($b['booking_id'])?></td>
            <td><?=htmlspecialchars($b['email'])?></td>
            <td><?=htmlspecialchars($b['flight_number'])?></td>
            <td><?=htmlspecialchars($b['booking_date'])?></td>
            <td><?=htmlspecialchars($b['status'])?></td>
            <td>
                <a href="edit_booking.php?id=<?=urlencode($b['booking_id'])?>">Редактировать</a> |
                <a href="delete_booking.php?id=<?=urlencode($b['booking_id'])?>" onclick="return confirm('Удалить бронирование?');">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include 'footer.php'; ?>
