<?php

require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE flights 
        SET departure_time = ?, 
            arrival_time = ?, 
            departure_airport = ?, 
            arrival_airport = ?, 
            status = ?, 
            aircraft_type_code = ?
        WHERE flight_number = ?
    ");

    $success = $stmt->execute([
        $data['departure_time'],
        $data['arrival_time'],
        $data['departure_airport'],
        $data['arrival_airport'],
        $data['status'],
        $data['aircraft_type_code'],
        $data['flight_number']
    ]);

    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}