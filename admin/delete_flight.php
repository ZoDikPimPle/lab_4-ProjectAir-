<?php
require_once 'db.php';

$flight_number = $_GET['flight_number'] ?? null;
if ($flight_number) {
    // Удаляем рейс
    $stmt = $pdo->prepare("DELETE FROM flights WHERE flight_number = ?");
    $stmt->execute([$flight_number]);
}

header("Location: flights.php");
exit;
