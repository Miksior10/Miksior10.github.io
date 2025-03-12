<?php
session_start();
require_once 'config.php';

// Sprawdzenie czy użytkownik jest adminem
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

// Obsługa akcji
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'clear_orders':
                $pdo->exec("DELETE FROM cart_items");
                $pdo->exec("DELETE FROM orders");
                $success = "Wyczyszczono zamówienia";
                break;
            
            case 'clear_products':
                $pdo->exec("DELETE FROM cart_items");
                $pdo->exec("DELETE FROM products");
                $success = "Wyczyszczono produkty";
                break;
            
            case 'clear_users':
                $pdo->exec("DELETE FROM cart_items");
                $pdo->exec("DELETE FROM orders");
                $pdo->exec("DELETE FROM users WHERE is_admin = FALSE");
                $success = "Wyczyszczono użytkowników";
                break;
            
            case 'clear_all':
                $pdo->exec("DELETE FROM cart_items");
                $pdo->exec("DELETE FROM orders");
                $pdo->exec("DELETE FROM products");
                $pdo->exec("DELETE FROM users WHERE is_admin = FALSE");
                $success = "Wyczyszczono całą bazę danych";
                break;
        }
    }
}

// Pobieranie statystyk
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = FALSE")->fetchColumn(),
    'products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-panel">
            <div class="admin-header">
                <h2>Panel Administracyjny</h2>
                <a href="logout.php" class="admin-logout">Wyloguj</a>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="stats-container">
                <h3>Statystyki</h3>
                <div class="stats-grid">
                    <div class="stat-box">
                        <span class="stat-label">Użytkownicy:</span>
                        <span class="stat-value"><?php echo $stats['users']; ?></span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-label">Produkty:</span>
                        <span class="stat-value"><?php echo $stats['products']; ?></span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-label">Zamówienia:</span>
                        <span class="stat-value"><?php echo $stats['orders']; ?></span>
                    </div>
                </div>
            </div>

            <div class="admin-actions">
                <h3>Zarządzanie bazą danych</h3>
                <form method="POST" class="admin-form">
                    <button type="submit" name="action" value="clear_orders" class="admin-button">Wyczyść zamówienia</button>
                    <button type="submit" name="action" value="clear_products" class="admin-button">Wyczyść produkty</button>
                    <button type="submit" name="action" value="clear_users" class="admin-button">Wyczyść użytkowników</button>
                    <button type="submit" name="action" value="clear_all" class="admin-button danger">Wyczyść wszystko</button>
                </form>
            </div>

            <a href="index.php" class="back-button">← Powrót do strony głównej</a>
        </div>
    </div>
</body>
</html> 