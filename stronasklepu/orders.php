<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pobierz liczbę produktów w koszyku
$cart_count = $pdo->query("SELECT SUM(quantity) FROM cart_items WHERE user_id = " . $_SESSION['user_id'])->fetchColumn() ?: 0;

try {
    // Pobierz wszystkie zamówienia użytkownika wraz z produktami
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_date,
            o.total_amount,
            o.shipping_method,
            o.shipping_cost,
            o.status,
            sa.full_name,
            sa.street,
            sa.city,
            sa.postal_code,
            sa.shipping_point,
            GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ' szt.)') SEPARATOR ', ') as products_list
        FROM orders o
        LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Błąd podczas pobierania zamówień: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historia zamówień - Sklep Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Sklep Online</div>
            <ul>
                <li><a href="index.php">Strona główna</a></li>
                <li><a href="products.php">Produkty</a></li>
                <li>
                    <a href="cart.php" class="cart-link">
                        Koszyk
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="orders.php" class="active">Historia zamówień</a></li>
                <li><a href="logout.php">Wyloguj</a></li>
                <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li><a href="admin_panel.php" class="admin-link">Panel Admina</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Historia zamówień</h1>

        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <p>Nie masz jeszcze żadnych zamówień.</p>
                <a href="products.php" class="btn btn-primary">Przejdź do sklepu</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Zamówienie #<?php echo $order['id']; ?></h3>
                                <span class="order-date">
                                    Data: <?php echo date('d.m.Y H:i', strtotime($order['order_date'])); ?>
                                </span>
                            </div>
                            <div class="order-status <?php echo strtolower($order['status']); ?>">
                                <?php 
                                switch($order['status']) {
                                    case 'new': echo 'Nowe'; break;
                                    case 'processing': echo 'W realizacji'; break;
                                    case 'shipped': echo 'Wysłane'; break;
                                    case 'delivered': echo 'Dostarczone'; break;
                                    default: echo ucfirst($order['status']);
                                }
                                ?>
                            </div>
                        </div>

                        <div class="order-content">
                            <div class="products-section">
                                <h4>Zamówione produkty:</h4>
                                <p><?php echo htmlspecialchars($order['products_list']); ?></p>
                            </div>

                            <div class="shipping-section">
                                <h4>Dane dostawy:</h4>
                                <?php if ($order['shipping_method'] != 'pickup'): ?>
                                    <p><?php echo htmlspecialchars($order['full_name'] ?? ''); ?></p>
                                    <p><?php echo htmlspecialchars($order['street'] ?? ''); ?></p>
                                    <p><?php echo htmlspecialchars(($order['postal_code'] ?? '') . ' ' . ($order['city'] ?? '')); ?></p>
                                <?php endif; ?>
                                
                                <p class="shipping-method">
                                    Metoda dostawy: 
                                    <?php
                                    switch($order['shipping_method']) {
                                        case 'courier':
                                            echo 'Kurier';
                                            break;
                                        case 'parcel_locker':
                                            $point = $order['shipping_point'] ?? '';
                                            echo 'Paczkomat ' . htmlspecialchars($point);
                                            break;
                                        case 'pickup':
                                            echo 'Odbiór osobisty';
                                            break;
                                    }
                                    ?>
                                </p>
                            </div>

                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Koszt dostawy:</span>
                                    <span><?php echo number_format($order['shipping_cost'], 2); ?> PLN</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Razem:</span>
                                    <span><?php echo number_format($order['total_amount'], 2); ?> PLN</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 Sklep Online</p>
    </footer>
</body>
</html> 