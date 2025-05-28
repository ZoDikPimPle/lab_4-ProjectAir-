<?php
require_once 'db.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    header('Location: users.php');
    exit;
}

try {
    // Удаляем связанные записи сначала (если есть внешние ключи)
    $pdo->beginTransaction();

    // Удаляем профиль пользователя
    $stmt = $pdo->prepare("DELETE FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Удаляем пользователя
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $pdo->commit();

    header('Location: users.php');
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Ошибка при удалении пользователя: " . $e->getMessage());
}
?>