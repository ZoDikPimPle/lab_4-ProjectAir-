<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Добавляем старт сессии в самое начало
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $captcha = $_POST['captcha'];

    // Проверка совпадения паролей
    if ($password !== $password_confirm) {
        $error = "Пароли не совпадают!";
    }
    // Проверка капчи
    elseif (!isset($_SESSION['captcha']) || $captcha !== $_SESSION['captcha']) {
        $error = "Неверная капча!";
    } else {
        try {
            // Хеширование пароля
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $password_hash]);

            $_SESSION['success'] = "Регистрация успешна! Теперь войдите в систему.";
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $error = "Ошибка регистрации: " . (str_contains($e->getMessage(), 'Duplicate entry') ?
                    "Пользователь с таким email уже существует" :
                    $e->getMessage());
        }
    }
}

// Генерация новой капчи только если она еще не установлена
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = generate_captcha();
}
?>

<?php include '../templates/header.php'; ?>

    <div class="auth-form">
        <h2>Регистрация</h2>

        <!-- Вывод ошибок -->
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email:</label>
                <input type="email" name="email" required value="<?= $_POST['email'] ?? '' ?>">
            </div>

            <div class="input-group">
                <label>Пароль:</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Повторите пароль:</label>
                <input type="password" name="password_confirm" required>
            </div>

            <div class="input-group captcha-group">
                <label>Введите капчу: <span class="captcha-code"><?= $_SESSION['captcha'] ?></span></label>
                <input type="text" name="captcha" required>
            </div>

            <button type="submit" class="btn">Зарегистрироваться</button>
        </form>

        <div class="auth-link">
            Уже есть аккаунт? <a href="login.php">Войдите</a>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>