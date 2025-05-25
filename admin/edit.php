<?php
require_once 'db.php';
include 'header.php';

$flight_number = $_GET['flight_number'] ?? null;
$edit_mode = $flight_number !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $fn = $_POST['flight_number'];
    $dep_time = $_POST['departure_time'];
    $arr_time = $_POST['arrival_time'];
    $dep_airport = $_POST['departure_airport'];
    $arr_airport = $_POST['arrival_airport'];
    $status = $_POST['status'];
    $aircraft_code = $_POST['aircraft_type_code'];

    if ($edit_mode) {
        // Обновление записи
        $stmt = $pdo->prepare("UPDATE flights SET departure_time=?, arrival_time=?, departure_airport=?, arrival_airport=?, status=?, aircraft_type_code=? WHERE flight_number=?");
        $stmt->execute([$dep_time, $arr_time, $dep_airport, $arr_airport, $status, $aircraft_code, $fn]);
        echo "<p style='color:green;'>Рейс обновлён.</p>";
    } else {
        // Вставка новой записи
        $stmt = $pdo->prepare("INSERT INTO flights (flight_number, departure_time, arrival_time, departure_airport, arrival_airport, status, aircraft_type_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$fn, $dep_time, $arr_time, $dep_airport, $arr_airport, $status, $aircraft_code]);
            echo "<p style='color:green;'>Рейс добавлен.</p>";
            $edit_mode = true;
            $flight_number = $fn;
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Ошибка при добавлении: " . $e->getMessage() . "</p>";
        }
    }
}

// Получаем данные рейса если редактируем
if ($edit_mode) {
    $stmt = $pdo->prepare("SELECT * FROM flights WHERE flight_number = ?");
    $stmt->execute([$flight_number]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $flight = null;
}

// Получаем данные для селектов
$airports = $pdo->query("SELECT airport_code, city FROM airports ORDER BY city")->fetchAll(PDO::FETCH_ASSOC);
$statuses = $pdo->query("SELECT status_code FROM status_flight ORDER BY status_code")->fetchAll(PDO::FETCH_COLUMN);
$aircraft_types = $pdo->query("SELECT type_code, name FROM aircraft_types ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2><?= $edit_mode ? "Редактировать рейс: " . htmlspecialchars($flight_number) : "Добавить новый рейс" ?></h2>

<form method="POST">
    <label>Номер рейса:<br />
        <input type="text" name="flight_number" required value="<?= $flight['flight_number'] ?? '' ?>" <?= $edit_mode ? 'readonly' : '' ?> />
    </label><br /><br />

    <label>Время вылета:<br />
        <input type="datetime-local" name="departure_time" required value="<?= $flight ? date('Y-m-d\TH:i', strtotime($flight['departure_time'])) : '' ?>" />
    </label><br /><br />

    <label>Время прибытия:<br />
        <input type="datetime-local" name="arrival_time" required value="<?= $flight ? date('Y-m-d\TH:i', strtotime($flight['arrival_time'])) : '' ?>" />
    </label><br /><br />

    <label>Аэропорт вылета:<br />
        <select name="departure_airport" required>
            <option value="">-- Выберите --</option>
            <?php foreach($airports as $airport): ?>
                <option value="<?=htmlspecialchars($airport['airport_code'])?>" <?= $flight && $flight['departure_airport'] == $airport['airport_code'] ? 'selected' : '' ?>>
                    <?=htmlspecialchars($airport['city'])?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br /><br />

    <label>Аэропорт прибытия:<br />
        <select name="arrival_airport" required>
            <option value="">-- Выберите --</option>
            <?php foreach($airports as $airport): ?>
                <option value="<?=htmlspecialchars($airport['airport_code'])?>" <?= $flight && $flight['arrival_airport'] == $airport['airport_code'] ? 'selected' : '' ?>>
                    <?=htmlspecialchars($airport['city'])?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br /><br />

    <label>Статус рейса:<br />
        <select name="status" required>
            <option value="">-- Выберите --</option>
            <?php foreach($statuses as $status_code): ?>
                <option value="<?=htmlspecialchars($status_code)?>" <?= $flight && $flight['status'] == $status_code ? 'selected' : '' ?>>
                    <?=htmlspecialchars($status_code)?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br /><br />

    <label>Тип самолёта:<br />
        <select name="aircraft_type_code" required>
            <option value="">-- Выберите --</option>
            <?php foreach($aircraft_types as $ac): ?>
                <option value="<?=htmlspecialchars($ac['type_code'])?>" <?= $flight && $flight['aircraft_type_code'] == $ac['type_code'] ? 'selected' : '' ?>>
                    <?=htmlspecialchars($ac['name'])?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br /><br />

    <button type="submit"><?= $edit_mode ? "Обновить" : "Добавить" ?></button>
</form>

<?php include 'footer.php'; ?>
