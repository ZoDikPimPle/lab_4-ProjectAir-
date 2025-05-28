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
        INSERT INTO user_profiles (user_id, full_name, phone) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            full_name = VALUES(full_name),
            phone = VALUES(phone)
    ");
    $stmt->execute([
        $data['user_id'],
        $data['full_name'] ?? null,
        $data['phone'] ?? null
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>