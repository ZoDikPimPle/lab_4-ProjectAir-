<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Фильтрация рейсов
$where = [];
$params = [];

if (!empty($_GET['departure'])) {
    $where[] = "departure_airport = ?";
    $params[] = $_GET['departure'];
}

if (!empty($_GET['arrival'])) {
    $where[] = "arrival_airport = ?";
    $params[] = $_GET['arrival'];
}

if (!empty($_GET['date'])) {
    $where[] = "DATE(departure_time) = ?";
    $params[] = $_GET['date'];
}

$sql = "SELECT * FROM flights";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY departure_time";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$flights = $stmt->fetchAll();

// Получаем список аэропортов для фильтров
$airports = $pdo->query("SELECT * FROM airports")->fetchAll();
?>

<?php include '../templates/header.php'; ?>

    <div class="container">
        <h1>Поиск рейсов</h1>

        <!-- Фильтры -->
        <div class="filters-card">
            <form method="GET">
                <div class="filter-row">
                    <div class="input-group">
                        <label>Откуда</label>
                        <select name="departure">
                            <option value="">Все аэропорты</option>
                            <?php foreach ($airports as $airport): ?>
                                <option value="<?= $airport['airport_code'] ?>" <?= isset($_GET['departure']) && $_GET['departure'] == $airport['airport_code'] ? 'selected' : '' ?>>
                                    <?= $airport['city'] ?> (<?= $airport['airport_code'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Куда</label>
                        <select name="arrival">
                            <option value="">Все аэропорты</option>
                            <?php foreach ($airports as $airport): ?>
                                <option value="<?= $airport['airport_code'] ?>" <?= isset($_GET['arrival']) && $_GET['arrival'] == $airport['airport_code'] ? 'selected' : '' ?>>
                                    <?= $airport['city'] ?> (<?= $airport['airport_code'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Дата вылета</label>
                        <input type="date" name="date" value="<?= $_GET['date'] ?? '' ?>">
                    </div>
                </div>

                <button type="submit" class="btn">Найти рейсы</button>
            </form>
        </div>

        <!-- Список рейсов -->
        <div class="flights-list">
            <?php if (empty($flights)): ?>
                <div class="no-results">Рейсов не найдено</div>
            <?php else: ?>
                <?php foreach ($flights as $flight): ?>
                    <div class="flight-card">
                        <div class="flight-header">
                            <span class="flight-number">Рейс <?= $flight['flight_number'] ?></span>
                            <span class="flight-status <?= strtolower($flight['status']) ?>">
                            <?= $pdo->query("SELECT status_description FROM status_flight WHERE status_code = '{$flight['status']}'")->fetchColumn() ?>
                        </span>
                        </div>

                        <div class="flight-body">
                            <div class="flight-route">
                                <div class="airport">
                                    <span class="code"><?= $flight['departure_airport'] ?></span>
                                    <span class="time"><?= date('H:i', strtotime($flight['departure_time'])) ?></span>
                                </div>

                                <div class="flight-duration">
                                    <div class="line"></div>
                                    <span><?= getFlightDuration($flight['departure_time'], $flight['arrival_time']) ?></span>
                                </div>

                                <div class="airport">
                                    <span class="code"><?= $flight['arrival_airport'] ?></span>
                                    <span class="time"><?= date('H:i', strtotime($flight['arrival_time'])) ?></span>
                                </div>
                            </div>

                            <div class="flight-footer">
                                <div class="flight-aircraft">
                                    <?= $pdo->query("SELECT name FROM aircraft_types WHERE type_code = '{$flight['aircraft_type_code']}'")->fetchColumn() ?>
                                </div>

                                <?php if (is_logged_in()): ?>
                                    <a href="booking.php?flight=<?= $flight['flight_number'] ?>" class="btn">Забронировать</a>
                                <?php else: ?>
                                    <button class="btn" onclick="alert('Для бронирования войдите в систему')">Забронировать</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>