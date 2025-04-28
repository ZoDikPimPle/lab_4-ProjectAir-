<?php
function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function generate_captcha() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    return substr(str_shuffle($chars), 0, 5);
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_user() {
    return is_logged_in() && $_SESSION['role'] === 'user';
}

function getFlightDuration($departure, $arrival) {
    $diff = strtotime($arrival) - strtotime($departure);
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    return $hours . 'ч ' . $minutes . 'м';
}

function calculate_ticket_price($flight_number, $seat_number) {
    global $pdo;

    // Базовая цена рейса (можно добавить сложную логику)
    $base_price = 5000;

    // Премиум-цена для первых рядов
    $row = intval(substr($seat_number, 0, -1));
    if ($row <= 3) {
        $base_price += 2000;
    }

    // Проверка статуса рейса
    $status = $pdo->query("SELECT status FROM flights WHERE flight_number = '$flight_number'")->fetchColumn();
    if ($status === 'Delayed') {
        $base_price *= 0.9; // Скидка 10% при задержке
    }

    return $base_price;
}

?>