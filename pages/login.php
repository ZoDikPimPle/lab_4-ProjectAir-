<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        header("Location: ../index.php");
        exit();
    } else {
        $error = "Неверный email или пароль!";
    }
}
?>

<?php include '../templates/header.php'; ?>

    <div class="auth-form">
        <h2>Вход в систему</h2>
        <?php if (isset($_GET['registered'])): ?>
            <div class="success">Регистрация успешна! Теперь войдите.</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
<br>
            <label>Пароль:</label>
            <input type="password" name="password" required>
<br>
            <button type="submit">Войти</button>
        </form>
    </div>

<?php include '../templates/footer.php'; ?>