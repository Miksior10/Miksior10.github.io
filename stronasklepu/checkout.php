<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Klucz szyfrowania (w prawdziwej aplikacji powinien być przechowywany bezpiecznie)
define('ENCRYPTION_KEY', 'twoj_tajny_klucz_szyfrowania_123');

// Funkcja do szyfrowania danych
function encrypt($data) {
    $encryption_key = base64_decode(ENCRYPTION_KEY);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Pobierz zawartość koszyka
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price FROM cart_items c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Oblicz sumę
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Koszty dostawy
$shipping_costs = [
    'courier' => 14.99,
    'parcel_locker' => 9.99,
    'pickup' => 0
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Dodaj koszt dostawy do sumy
        $shipping_cost = $shipping_costs[$_POST['shipping_method']];
        $total_with_shipping = $total + $shipping_cost;

        // Utwórz zamówienie
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_method, shipping_cost, status) 
                              VALUES (?, ?, ?, ?, 'new')");
        $stmt->execute([
            $_SESSION['user_id'],
            $total_with_shipping,
            $_POST['shipping_method'],
            $shipping_cost
        ]);
        $order_id = $pdo->lastInsertId();

        // Zapisz adres dostawy
        $stmt = $pdo->prepare("INSERT INTO shipping_addresses 
                              (order_id, full_name, street, city, postal_code, shipping_point) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $order_id,
            $_POST['full_name'],
            $_POST['street'],
            $_POST['city'],
            $_POST['postal_code'],
            $_POST['shipping_method'] == 'parcel_locker' ? $_POST['parcel_locker_id'] : null
        ]);

        // Zapisz produkty z zamówienia
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
        }

        // Wyczyść koszyk
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        $pdo->commit();
        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Wystąpił błąd podczas przetwarzania zamówienia: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamówienie - Sklep Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Sklep Online</div>
            <ul>
                <li><a href="index.php">Strona główna</a></li>
                <li><a href="products.php">Produkty</a></li>
                <li><a href="cart.php">Koszyk</a></li>
                <li><a href="orders.php">Historia zamówień</a></li>
                <li><a href="logout.php">Wyloguj</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="checkout-container">
            <!-- Podsumowanie koszyka -->
            <div class="order-summary">
                <h2>Podsumowanie zamówienia</h2>
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                            <span class="item-quantity"><?php echo $item['quantity']; ?> x</span>
                            <span class="item-price"><?php echo number_format($item['price'], 2); ?> PLN</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="shipping-cost" class="shipping-cost">
                    Koszt dostawy: <span>0.00 PLN</span>
                </div>
                <div class="order-total">
                    <strong>Suma do zapłaty:</strong>
                    <span id="total-amount"><?php echo number_format($total, 2); ?> PLN</span>
                </div>
            </div>

            <!-- Formularz zamówienia -->
            <div class="checkout-form">
                <form method="POST" id="checkout-form">
                    <!-- Dane dostawy -->
                    <div class="form-section">
                        <h2>Dane dostawy</h2>
                        <div class="form-group">
                            <label for="full_name">Imię i nazwisko</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="street">Ulica i numer</label>
                            <input type="text" id="street" name="street" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="postal_code">Kod pocztowy</label>
                                <input type="text" id="postal_code" name="postal_code" 
                                       pattern="[0-9]{2}-[0-9]{3}" placeholder="00-000" required>
                            </div>
                            <div class="form-group half">
                                <label for="city">Miejscowość</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                        </div>
                    </div>

                    <!-- Metoda dostawy -->
                    <div class="form-section">
                        <h2>Metoda dostawy</h2>
                        <div class="shipping-methods">
                            <div class="shipping-method">
                                <input type="radio" id="courier" name="shipping_method" 
                                       value="courier" required>
                                <label for="courier">
                                    Kurier (14.99 PLN)
                                    <span class="delivery-time">5-7 dni robocze</span>
                                </label>
                            </div>
                            <div class="shipping-method">
                                <input type="radio" id="parcel_locker" name="shipping_method" 
                                       value="parcel_locker">
                                <label for="parcel_locker">
                                    Paczkomat InPost (9.99 PLN)
                                    <span class="delivery-time">1-2 dni robocze</span>
                                </label>
                            </div>
                            <div class="shipping-method">
                                <input type="radio" id="pickup" name="shipping_method" 
                                       value="pickup">
                                <label for="pickup">
                                    Odbiór osobisty (0.00 PLN)
                                    <span class="delivery-time">od ręki</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pole dla paczkomatu -->
                        <div id="parcel-locker-section" class="form-group" style="display: none;">
                            <label for="parcel_locker_id">Wybierz paczkomat</label>
                            <input type="text" id="parcel_locker_id" name="parcel_locker_id" 
                                   placeholder="Wpisz kod paczkomatu">
                        </div>
                    </div>

                    <!-- Dane płatności -->
                    <div class="form-section">
                        <h2>Dane płatności</h2>
                        <div class="form-group">
                            <label for="cardholder_name">Imię i nazwisko na karcie</label>
                            <input type="text" id="cardholder_name" name="cardholder_name" required>
                        </div>
                        <div class="form-group">
                            <label for="card_number">Numer karty</label>
                            <input type="text" id="card_number" name="card_number" 
                                   pattern="[0-9]{16}" maxlength="16" 
                                   placeholder="1234 5678 9012 3456" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="card_expiry">Data ważności</label>
                                <input type="text" id="card_expiry" name="card_expiry" 
                                       pattern="(0[1-9]|1[0-2])\/[0-9]{2}" 
                                       placeholder="MM/RR" required>
                            </div>
                            <div class="form-group half">
                                <label for="card_cvv">CVV</label>
                                <input type="text" id="card_cvv" name="card_cvv" 
                                       pattern="[0-9]{3,4}" maxlength="4" 
                                       placeholder="123" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="checkout-button">
                        Zamów i zapłać <span id="button-total"><?php echo number_format($total, 2); ?> PLN</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Formatowanie kodu pocztowego
    document.getElementById('postal_code').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0,2) + '-' + value.slice(2);
        }
        e.target.value = value;
    });

    // Obsługa metod dostawy
    const shippingMethods = document.getElementsByName('shipping_method');
    const parcelLockerSection = document.getElementById('parcel-locker-section');
    const totalAmount = <?php echo $total; ?>;
    const shippingCosts = <?php echo json_encode($shipping_costs); ?>;

    function updateTotal(shippingMethod) {
        const shippingCost = shippingCosts[shippingMethod] || 0;
        const newTotal = totalAmount + shippingCost;
        
        document.getElementById('shipping-cost').querySelector('span').textContent = 
            shippingCost.toFixed(2) + ' PLN';
        document.getElementById('total-amount').textContent = newTotal.toFixed(2) + ' PLN';
        document.getElementById('button-total').textContent = newTotal.toFixed(2) + ' PLN';
    }

    shippingMethods.forEach(method => {
        method.addEventListener('change', function() {
            parcelLockerSection.style.display = 
                this.value === 'parcel_locker' ? 'block' : 'none';
            
            if (this.value === 'parcel_locker') {
                document.getElementById('parcel_locker_id').required = true;
            } else {
                document.getElementById('parcel_locker_id').required = false;
            }

            updateTotal(this.value);
        });
    });

    // Formatowanie numeru karty
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });

    // Formatowanie daty ważności
    document.getElementById('card_expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0,2) + '/' + value.slice(2);
        }
        e.target.value = value;
    });

    // Formatowanie CVV
    document.getElementById('card_cvv').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });
    </script>
</body>
</html> 