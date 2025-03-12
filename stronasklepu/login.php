<?php
session_start();
require_once 'config.php';

// Sprawdź czy istnieje jakikolwiek użytkownik
$check_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

if ($check_users == 0) {
    header("Location: register.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_type = $_POST['login_type'];
    $login_value = $_POST['login_value'];
    $password = $_POST['password'];

    // Wybór odpowiedniej kolumny do logowania
    switch($login_type) {
        case 'email':
            $sql = "SELECT * FROM users WHERE email = ?";
            break;
        case 'phone':
            $sql = "SELECT * FROM users WHERE phone = ?";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$login_value]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        if ($user['is_admin']) {
            header("Location: admin_panel.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Nieprawidłowe dane logowania";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - Sklep Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <a href="index.php" class="back-button">← Powrót do strony głównej</a>
            <h2>Logowanie</h2>
            
            <?php if ($check_users == 0): ?>
                <div class="info-message">
                    Nie ma jeszcze żadnego konta. Zarejestruj się jako administrator.
                </div>
                <div class="auth-buttons">
                    <a href="register.php" class="auth-button register-button">Zarejestruj się jako Admin</a>
                </div>
            <?php else: ?>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="login.php" class="auth-form">
                    <div class="form-group">
                        <label for="login_type">Wybierz sposób logowania:</label>
                        <select id="login_type" name="login_type" class="login-type-select" onchange="updateLoginPlaceholder()" required>
                            <option value="email">Email</option>
                            <option value="phone">Numer telefonu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="login_value">Login:</label>
                        <input type="text" id="login_value" name="login_value" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Hasło:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="auth-button">Zaloguj się</button>
                </form>
                <div class="auth-links">
                    <p>Nie masz jeszcze konta? <a href="register.php">Zarejestruj się</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function updateLoginPlaceholder() {
        const loginType = document.getElementById('login_type').value;
        const loginInput = document.getElementById('login_value');
        
        if (loginType === 'email') {
            loginInput.type = 'email';
            loginInput.placeholder = 'Wprowadź adres email';
        } else if (loginType === 'phone') {
            loginInput.type = 'tel';
            loginInput.placeholder = 'Wprowadź numer telefonu (9 cyfr)';
            loginInput.pattern = '[0-9]{9}';
        }
    }

    // Wywołaj funkcję przy załadowaniu strony
    updateLoginPlaceholder();
    </script>
</body>
</html> 