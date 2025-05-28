<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['flight_numbers']) || empty($data['new_status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    // Подготавливаем IN-условие для массового обновления
    $placeholders = rtrim(str_repeat('?,', count($data['flight_numbers'])), ',');

    $sql = "UPDATE flights SET status = ? WHERE flight_number IN ($placeholders)";
    $stmt = $pdo->prepare($sql);

    // Первый параметр - новый статус, остальные - номера рейсов
    $params = array_merge([$data['new_status']], $data['flight_numbers']);

    $success = $stmt->execute($params);

    echo json_encode([
        'success' => $success,
        'updated_count' => $stmt->rowCount()
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}