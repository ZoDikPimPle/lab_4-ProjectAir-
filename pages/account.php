<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Получаем данные профиля пользователя
$profile = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$profile->execute([$_SESSION['user_id']]);
$profile = $profile->fetch();

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

// Обработка формы профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $full_name = trim($_POST['full_name']);
        $birth_date = $_POST['birth_date'];
        $passport_series = trim($_POST['passport_series']);
        $passport_number = trim($_POST['passport_number']);
        $passport_issued_by = trim($_POST['passport_issued_by']);
        $passport_issue_date = $_POST['passport_issue_date'];

        // Проверка обязательных полей
        if (empty($full_name) || empty($birth_date) || empty($passport_series) ||
            empty($passport_number) || empty($passport_issued_by) || empty($passport_issue_date)) {
            throw new Exception("Все поля обязательны для заполнения");
        }

        // Проверка даты рождения
        if (strtotime($birth_date) > time()) {
            throw new Exception("Дата рождения не может быть в будущем");
        }

        // Проверка даты выдачи паспорта
        if (strtotime($passport_issue_date) > time()) {
            throw new Exception("Дата выдачи паспорта не может быть в будущем");
        }

        // Обновление профиля
        $stmt = $pdo->prepare("
            INSERT INTO user_profiles 
            (user_id, full_name, phone, birth_date, passport_series, passport_number, passport_issued_by, passport_issue_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            full_name = VALUES(full_name),
            phone = VALUES(phone),
            birth_date = VALUES(birth_date),
            passport_series = VALUES(passport_series),
            passport_number = VALUES(passport_number),
            passport_issued_by = VALUES(passport_issued_by),
            passport_issue_date = VALUES(passport_issue_date)
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $full_name,
            $profile['phone'] ?? null,
            $birth_date,
            $passport_series,
            $passport_number,
            $passport_issued_by,
            $passport_issue_date
        ]);

        $success = "Профиль успешно обновлен";
// Обновляем данные профиля
        $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $profile = $stmt->fetch();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

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
            <div class="alert alert-success">Оплата прошла успешно!</div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2>Мой профиль</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="full_name">ФИО</label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                       value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="birth_date">Дата рождения</label>
                                <input type="date" class="form-control" id="birth_date" name="birth_date"
                                       value="<?= htmlspecialchars($profile['birth_date'] ?? '') ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="passport_series">Серия паспорта</label>
                                    <input type="text" class="form-control" id="passport_series" name="passport_series"
                                           value="<?= htmlspecialchars($profile['passport_series'] ?? '') ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="passport_number">Номер паспорта</label>
                                    <input type="text" class="form-control" id="passport_number" name="passport_number"
                                           value="<?= htmlspecialchars($profile['passport_number'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="passport_issued_by">Кем выдан</label>
                                <input type="text" class="form-control" id="passport_issued_by" name="passport_issued_by"
                                       value="<?= htmlspecialchars($profile['passport_issued_by'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="passport_issue_date">Дата выдачи</label>
                                <input type="date" class="form-control" id="passport_issue_date" name="passport_issue_date"
                                       value="<?= htmlspecialchars($profile['passport_issue_date'] ?? '') ?>" required>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">Сохранить</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Мои бронирования</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <div class="alert alert-info">У вас нет бронирований</div>
                        <?php else: ?>
                            <form method="POST" class="mb-3">
                                <button type="submit" name="pay_all" class="btn btn-success">Оплатить все билеты разом</button>
                            </form>

                            <?php foreach ($bookings as $booking): ?>
                                <div class="card mb-3">
                                    <div class="card-header d-flex justify-content-between">
                                        <span>Рейс <?= $booking['flight_number'] ?></span>
                                        <span class="badge <?= $booking['status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $booking['status'] === 'paid' ? 'Оплачено' : 'Ожидает оплаты' ?>
                                    </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="route mb-2">
                                            <strong><?= $booking['departure_airport'] ?></strong>
                                            <span class="text-muted"><?= date('d.m.Y H:i', strtotime($booking['departure_time'])) ?></span>
                                            <span>→</span>
                                            <strong><?= $booking['arrival_airport'] ?></strong>
                                            <span class="text-muted"><?= date('d.m.Y H:i', strtotime($booking['arrival_time'])) ?></span>
                                        </div>
                                        <div class="text-muted">
                                            Забронировано: <?= date('d.m.Y H:i', strtotime($booking['booking_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>