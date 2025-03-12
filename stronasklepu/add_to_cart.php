<?php
session_start();
require_once 'config.php';

// Sprawdź czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Sprawdź czy otrzymano ID produktu
if (!isset($_POST['product_id'])) {
    header('Location: products.php');
    exit();
}

try {
    // Sprawdź czy produkt istnieje
    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id = ?");
    $stmt->execute([$_POST['product_id']]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php?error=product_not_found');
        exit();
    }

    // Sprawdź czy produkt już jest w koszyku
    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $_POST['product_id']]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        // Aktualizuj ilość
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $_POST['product_id']]);
    } else {
        // Dodaj nowy produkt do koszyka
        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$_SESSION['user_id'], $_POST['product_id']]);
    }

    // Przekieruj do koszyka
    header('Location: cart.php');
    exit();

} catch (PDOException $e) {
    // W przypadku błędu przekieruj z komunikatem błędu
    header('Location: products.php?error=add_to_cart_failed');
    exit();
}
?> 