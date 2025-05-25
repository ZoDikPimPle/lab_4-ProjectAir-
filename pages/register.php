<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();

// Пользовательский обработчик ошибок
function userErrorHandler($errno, $errstr, $errfile, $errline) {
    $dt = date("Y-m-d H:i:s");
    $error_message = "[$dt] Ошибка [$errno] $errstr в $errfile на строке $errline\n";
    error_log($error_message, 3, "error_log.txt"); // Логирование ошибки в файл
    echo "<div class='error-message'>Произошла ошибка. Пожалуйста, попробуйте позже.</div>";
}

// Установить пользовательский обработчик ошибок
set_error_handler("userErrorHandler");

// Пользовательский обработчик исключений
function exceptionHandler($exception) {
    $dt = date("Y-m-d H:i:s");
    $error_message = "[$dt] Исключение: " . $exception->getMessage() . " в " . $exception->getFile() . " на строке " . $exception->getLine() . "\n";
    error_log($error_message, 3, "error_log.txt"); // Логирование исключения в файл
    echo "<div class='error-message'>Произошла ошибка. Пожалуйста, попробуйте позже.</div>";
}

// Установить пользовательский обработчик исключений
set_exception_handler("exceptionHandler");

// Если пользователь уже авторизован, перенаправляем его
if (isset($_SESSION['user_id'])) {
    header("Location: /dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    $captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';

    // Проверка на пустые поля
    if (empty($email) || empty($password) || empty($password_confirm) || empty($captcha)) {
        $error = "Все поля обязательны для заполнения!";
    }
    // Проверка валидности email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный формат email!";
    }
    // Проверка длины пароля
    elseif (strlen($password) < 8) {
        $error = "Пароль должен содержать минимум 8 символов!";
    }
    // Проверка совпадения паролей
    elseif ($password !== $password_confirm) {
        $error = "Пароли не совпадают!";
    }
    // Проверка капчи
    elseif (!isset($_SESSION['captcha']) || $captcha !== $_SESSION['captcha']) {
        $error = "Неверная капча!";
    } else {
        try {
            // Проверяем, существует ли уже пользователь с таким email
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Пользователь с таким email уже зарегистрирован!";
            } else {
                // Хеширование пароля
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->execute([$email, $password_hash]);

                $_SESSION['success'] = "Регистрация успешна! Теперь войдите в систему.";
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            // Логирование ошибки
            error_log("Ошибка регистрации: " . $e->getMessage());

            // Проверка на фатальные ошибки
            if ($e->getCode() == 'HY000') { // Пример кода фатальной ошибки
                $error = "Произошла фатальная ошибка. Пожалуйста, свяжитесь с администратором.";
            } else {
                $error = "Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.";
            }
        }
    }
}

// Генерация новой капчи только если она еще не установлена
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = generate_captcha();
}

// Функция для защиты от SQL-инъекций
//function clean_input($data) {
//    $data = trim($data);
//    $data = stripslashes($data);
//    $data = htmlspecialchars($data);
//    return $data;
//}
//
//// Функция для генерации капчи
//function generate_captcha() {
//    return substr(md5(uniqid(rand(), true)), 0, 6);
//}
//?>

<?php include '../templates/header.php'; ?>

<div class="auth-form">
    <h2>Регистрация</h2>

    <!-- Вывод сообщений об успехе/ошибке -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Email:</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="input-group">
            <label>Пароль:</label>
            <input type="password" name="password" required minlength="8">
            <small class="hint">Минимум 8 символов</small>
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
