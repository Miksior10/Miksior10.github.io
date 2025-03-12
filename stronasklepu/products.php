<?php
session_start();
require_once 'config.php';

// Obsługa filtrowania i sortowania
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999;

// Przygotowanie zapytania SQL
$sql = "SELECT * FROM products WHERE name LIKE :search AND price BETWEEN :min_price AND :max_price";

// Dodanie sortowania
switch($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    default:
        $sql .= " ORDER BY name ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':search' => '%' . $search . '%',
    ':min_price' => $min_price,
    ':max_price' => $max_price
]);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produkty - Sklep Online</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: #2ecc71;
            color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transform: translateX(120%);
            transition: transform 0.3s ease;
            z-index: 1000;
            font-weight: bold;
        }

        .notification.show {
            transform: translateX(0);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Sklep Online</div>
            <ul>
                <li><a href="index.php">Strona główna</a></li>
                <li><a href="products.php" class="active">Produkty</a></li>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Logowanie</a></li>
                    <li><a href="register.php">Rejestracja</a></li>
                <?php else: ?>
                    <li><a href="cart.php">Koszyk</a></li>
                    <li><a href="orders.php">Historia zamówień</a></li>
                    <li><a href="logout.php">Wyloguj</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="products-header">
                <h1>Wszystkie produkty</h1>
                <div class="filters">
                    <form method="GET" class="filter-form">
                        <input type="text" name="search" placeholder="Szukaj produktów..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                        
                        <div class="price-filters">
                            <input type="number" name="min_price" placeholder="Cena min" 
                                   value="<?php echo $min_price ?: ''; ?>" class="price-input">
                            <input type="number" name="max_price" placeholder="Cena max" 
                                   value="<?php echo $max_price != 999999 ? $max_price : ''; ?>" class="price-input">
                        </div>

                        <select name="sort" class="sort-select">
                            <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Nazwa A-Z</option>
                            <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Nazwa Z-A</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Cena rosnąco</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Cena malejąco</option>
                        </select>

                        <button type="submit" class="filter-button">Filtruj</button>
                    </form>
                </div>
            </div>

            <div class="products-grid">
                <?php if($products): ?>
                    <?php foreach($products as $product): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price"><?php echo number_format($product['price'], 2); ?> PLN</p>
                            <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="add-to-cart-btn">Dodaj do koszyka</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-products">Nie znaleziono produktów spełniających kryteria.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Sklep Online</p>
    </footer>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'added_to_cart'): ?>
        <div class="alert success">
            Produkt został dodany do koszyka!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert error">
            <?php
            switch($_GET['error']) {
                case 'add_to_cart_failed':
                    echo 'Wystąpił błąd podczas dodawania do koszyka.';
                    break;
                case 'product_not_found':
                    echo 'Produkt nie został znaleziony.';
                    break;
                default:
                    echo 'Wystąpił nieznany błąd.';
            }
            ?>
        </div>
    <?php endif; ?>
</body>
</html> 