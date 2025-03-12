<?php
session_start();
require_once 'config.php';

// Włącz wyświetlanie błędów (tylko do debugowania)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    header("Location: admin_panel.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Usuwamy białe znaki
    $password = trim($_POST['password']); // Usuwamy białe znaki

    // Debugowanie - zapisz do pliku log.txt
    file_put_contents('log.txt', "Próba logowania - Username: " . $username . "\n", FILE_APPEND);

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // Debugowanie - zapisz wynik zapytania
    file_put_contents('log.txt', "Znaleziono użytkownika: " . ($admin ? "TAK" : "NIE") . "\n", FILE_APPEND);

    if ($admin && password_verify($password, $admin['password']) && $admin['is_admin']) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['is_admin'] = true;
        header("Location: admin_panel.php");
        exit();
    } else {
        $error = "Nieprawidłowa nazwa użytkownika lub hasło administratora";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admina - Logowanie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <a href="index.php" class="back-button">← Powrót do strony głównej</a>
            <h2>Panel Admina - Logowanie</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="admin_login.php" class="auth-form">
                <div class="form-group">
                    <label for="username">Nazwa użytkownika:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Hasło:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="auth-button">Zaloguj się jako Admin</button>
            </form>
            <div class="admin-info">
                <p>Dane logowania administratora:</p>
                <p>Login: <strong>admin</strong></p>
                <p>Hasło: <strong>admin</strong></p>
            </div>
        </div>
    </div>
</body>
</html> 