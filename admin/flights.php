<?php
require_once 'db.php';
include 'header.php';

// Получаем список рейсов с информацией из связанных таблиц
$stmt = $pdo->query("
    SELECT f.flight_number, f.departure_time, f.arrival_time,
           da.city AS departure_city, aa.city AS arrival_city,
           f.status, at.name AS aircraft_name
    FROM flights f
    JOIN airports da ON f.departure_airport = da.airport_code
    JOIN airports aa ON f.arrival_airport = aa.airport_code
    JOIN aircraft_types at ON f.aircraft_type_code = at.type_code
    ORDER BY f.departure_time DESC
");
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Рейсы</h2>
<a href="edit.php">Добавить новый рейс</a>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Номер рейса</th>
        <th>Вылет</th>
        <th>Прибытие</th>
        <th>Город вылета</th>
        <th>Город прибытия</th>
        <th>Статус</th>
        <th>Тип самолёта</th>
        <th>Действия</th>
    </tr>
    <?php foreach($flights as $flight): ?>
        <tr>
            <td><?=htmlspecialchars($flight['flight_number'])?></td>
            <td><?=htmlspecialchars($flight['departure_time'])?></td>
            <td><?=htmlspecialchars($flight['arrival_time'])?></td>
            <td><?=htmlspecialchars($flight['departure_city'])?></td>
            <td><?=htmlspecialchars($flight['arrival_city'])?></td>
            <td><?=htmlspecialchars($flight['status'])?></td>
            <td><?=htmlspecialchars($flight['aircraft_name'])?></td>
            <td>
                <a href="edit.php?flight_number=<?=urlencode($flight['flight_number'])?>">Редактировать</a> |
                <a href="delete_flight.php?flight_number=<?=urlencode($flight['flight_number'])?>" onclick="return confirm('Удалить рейс?');">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include 'footer.php'; ?>
