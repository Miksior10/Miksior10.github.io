<?php
session_start();
require_once 'config.php';

// Pobierz liczbę produktów w koszyku jeśli użytkownik jest zalogowany
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $cart_count = $pdo->query("SELECT SUM(quantity) FROM cart_items WHERE user_id = " . $_SESSION['user_id'])->fetchColumn() ?: 0;
}

// Pobieranie produktów z bazy danych
$sql = "SELECT * FROM products LIMIT 6";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sklep Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Sklep Online</div>
            <ul>
                <li><a href="index.php" class="active">Strona główna</a></li>
                <li><a href="products.php">Produkty</a></li>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Logowanie</a></li>
                    <li><a href="register.php">Rejestracja</a></li>
                <?php else: ?>
                    <li>
                        <a href="cart.php" class="cart-link">
                            Koszyk
                            <span class="cart-count" <?php echo $cart_count == 0 ? 'style="display:none;"' : ''; ?>>
                                <?php echo $cart_count; ?>
                            </span>
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

    <main>
        <div class="container">
            <h1>Witaj w naszym sklepie!</h1>
            
            <section class="featured-products">
                <h2>Polecane produkty</h2>
                <div class="products-grid">
                    <?php if($products): ?>
                        <?php foreach($products as $product): ?>
                            <div class="product-card">
                                <?php if($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <img src="images/default-product.jpg" alt="Domyślne zdjęcie produktu">
                                <?php endif; ?>
                                <div class="product-info">
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <p class="product-price"><?php echo number_format($product['price'], 2); ?> PLN</p>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="add-to-cart-button">
                                            Dodaj do koszyka
                                        </button>
                                    <?php else: ?>
                                        <p class="login-prompt">Zaloguj się, aby dodać do koszyka</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-products">Brak dostępnych produktów</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Sklep Online</p>
            <?php if(!isset($_SESSION['is_admin'])): ?>
                <a href="admin_login.php" class="admin-login-link">Panel Administracyjny</a>
            <?php endif; ?>
        </div>
    </footer>

    <script>
    function addToCart(productId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Aktualizuj licznik koszyka
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cartCount;
                    cartCount.style.display = data.cartCount > 0 ? 'inline' : 'none';
                    cartCount.classList.add('updated');
                    setTimeout(() => cartCount.classList.remove('updated'), 300);
                }
            }
        })
        .catch(error => console.error('Błąd:', error));
    }
    </script>
</body>
</html>