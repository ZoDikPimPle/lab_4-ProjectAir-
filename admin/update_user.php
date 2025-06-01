<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    // Обновляем основную информацию пользователя
    $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ? WHERE user_id = ?");
    $stmt->execute([$data['email'], $data['role'], $data['user_id']]);

    // Обновляем профиль пользователя
    $stmt = $pdo->prepare("
        INSERT INTO user_profiles (user_id, full_name, phone, passport_series, passport_number, birth_date)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            full_name = VALUES(full_name),
            phone = VALUES(phone),
            passport_series = VALUES(passport_series),
            passport_number = VALUES(passport_number),
            birth_date = VALUES(birth_date)
    ");

    // Преобразуем дату рождения из формата дд.мм.гггг в формат гггг-мм-дд для SQL
    $birthDate = null;
    if (!empty($data['birth_date'])) {
        $dateParts = explode('.', $data['birth_date']);
        if (count($dateParts) == 3) {
            $birthDate = sprintf('%04d-%02d-%02d', $dateParts[2], $dateParts[1], $dateParts[0]);
        }
    }

    $stmt->execute([
        $data['user_id'],
        $data['full_name'] ?? null,
        $data['phone'] ?? null,
        $data['passport_series'] ?? null,
        $data['passport_number'] ?? null,
        $birthDate
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
