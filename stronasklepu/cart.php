<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Pobierz zawartość koszyka
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image_url 
    FROM cart_items c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Oblicz sumę
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Pobierz liczbę produktów w koszyku
$cart_count = $pdo->query("SELECT SUM(quantity) FROM cart_items WHERE user_id = " . $_SESSION['user_id'])->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koszyk - Sklep Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Nawigacja -->
    <header>
        <nav>
            <div class="logo">Sklep Online</div>
            <ul>
                <li><a href="index.php">Strona główna</a></li>
                <li><a href="products.php">Produkty</a></li>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Logowanie</a></li>
                    <li><a href="register.php">Rejestracja</a></li>
                <?php else: ?>
                    <li>
                        <a href="cart.php" class="cart-link">
                            Koszyk
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="orders.php">Historia zamówień</a></li>
                    <li><a href="logout.php">Wyloguj</a></li>
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="admin_panel.php" class="admin-link">Panel Admina</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Twój koszyk</h1>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Twój koszyk jest pusty</p>
                <a href="products.php" class="btn">Przejdź do sklepu</a>
            </div>
        <?php else: ?>
            <!-- Przenieś sumę na górę -->
            <div class="cart-summary-top">
                <div class="total">
                    <span>Wartość koszyka:</span>
                    <span class="total-amount"><?php echo number_format($total, 2); ?> PLN</span>
                </div>
                <div class="cart-actions">
                    <a href="checkout.php" class="btn btn-primary">Przejdź do kasy</a>
                </div>
            </div>

            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="item-image">
                        
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="item-price"><?php echo number_format($item['price'], 2); ?> PLN</p>
                            
                            <div class="quantity-controls">
                                <form action="update_cart.php" method="POST" class="quantity-form">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <?php if ($item['quantity'] > 1): ?>
                                        <button type="submit" name="action" value="decrease" class="quantity-btn">-</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="decrease" class="quantity-btn" 
                                                onclick="return confirm('Czy na pewno chcesz usunąć ten produkt z koszyka?')">-</button>
                                    <?php endif; ?>
                                    <span class="quantity"><?php echo $item['quantity']; ?></span>
                                    <button type="submit" name="action" value="increase" class="quantity-btn">+</button>
                                </form>
                                
                                <form action="remove_from_cart.php" method="POST" class="remove-form">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="remove-btn" 
                                            onclick="return confirm('Czy na pewno chcesz usunąć ten produkt z koszyka?')">
                                        Usuń
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="item-total">
                            <?php echo number_format($item['price'] * $item['quantity'], 2); ?> PLN
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Dolna sekcja z przyciskami -->
                <div class="cart-bottom-actions">
                    <a href="products.php" class="btn btn-secondary">Kontynuuj zakupy</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 