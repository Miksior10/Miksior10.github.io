<?php
session_start();
require_once 'config.php';

// Sprawdź czy istnieje już użytkownik (dla pierwszego admina)
$check_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$is_first_user = ($check_users == 0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Walidacja danych
    $errors = [];
    
    // Walidacja emaila
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Nieprawidłowy format adresu email";
    }

    // Walidacja numeru telefonu (9 cyfr)
    if (!preg_match("/^[0-9]{9}$/", $phone)) {
        $errors[] = "Nieprawidłowy format numeru telefonu (wymagane 9 cyfr)";
    }

    if (empty($errors)) {
        try {
            // Jeśli to pierwszy użytkownik, będzie adminem
            $is_admin = $is_first_user ? TRUE : FALSE;
            
            $sql = "INSERT INTO users (username, email, phone, password, is_admin) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email, $phone, $password, $is_admin]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = $is_admin;
            
            if ($is_admin) {
                header("Location: admin_panel.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } catch (PDOException $e) {
            $errors[] = "Błąd podczas rejestracji. Spróbuj ponownie.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_first_user ? 'Rejestracja Administratora' : 'Rejestracja'; ?> - Sklep Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <a href="index.php" class="back-button">← Powrót do strony głównej</a>
            <h2><?php echo $is_first_user ? 'Rejestracja Administratora' : 'Rejestracja'; ?></h2>
            
            <?php if ($is_first_user): ?>
                <div class="admin-notice">
                    Tworzenie pierwszego konta administratora systemu
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php" class="auth-form">
                <div class="form-group">
                    <label for="username">Nazwa użytkownika:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group required">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group required">
                    <label for="phone">Numer telefonu:</label>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{9}" placeholder="123456789" required>
                    <small>Format: 9 cyfr bez spacji i myślników</small>
                </div>
                <div class="form-group">
                    <label for="password">Hasło:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="auth-button <?php echo $is_first_user ? 'admin-register-button' : ''; ?>">
                    <?php echo $is_first_user ? 'Utwórz konto administratora' : 'Zarejestruj się'; ?>
                </button>
            </form>
            <div class="auth-links">
                <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
            </div>
        </div>
    </div>
</body>
</html> 