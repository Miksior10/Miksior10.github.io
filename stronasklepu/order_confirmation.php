<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

try {
    // Pobierz dane zamówienia wraz z adresem dostawy
    $stmt = $pdo->prepare("
        SELECT o.*, 
               sa.full_name, sa.street, sa.city, sa.postal_code, sa.shipping_point,
               SUM(oi.quantity * oi.price) as items_total
        FROM orders o
        LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ? AND o.user_id = ?
        GROUP BY o.id
    ");
    $stmt->execute([$_GET['order_id'], $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Zamówienie nie zostało znalezione');
    }

    // Pobierz produkty z zamówienia
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$_GET['order_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Błąd: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potwierdzenie zamówienia - Sklep Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-box">
            <div class="success-icon">✓</div>
            <h1>Dziękujemy za zakupy!</h1>
            <p class="order-number">Numer zamówienia: #<?php echo htmlspecialchars($order['id']); ?></p>
            
            <div class="confirmation-details">
                <h2>Szczegóły zamówienia</h2>
                
                <div class="products-list">
                    <?php foreach ($items as $item): ?>
                    <div class="product-item">
                        <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        <span class="product-quantity"><?php echo $item['quantity']; ?> szt.</span>
                        <span class="product-price"><?php echo number_format($item['price'] * $item['quantity'], 2); ?> PLN</span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="shipping-info">
                    <h3>Dane dostawy</h3>
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
                            default:
                                echo 'Nieznana metoda dostawy';
                        }
                        ?>
                    </p>
                </div>

                <div class="order-summary">
                    <div class="summary-row">
                        <span>Wartość produktów:</span>
                        <span><?php echo number_format($order['items_total'], 2); ?> PLN</span>
                    </div>
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

            <div class="confirmation-actions">
                <a href="orders.php" class="btn btn-secondary">Historia zamówień</a>
                <a href="index.php" class="btn btn-primary">Wróć do sklepu</a>
            </div>
        </div>
    </div>
</body>
</html> 