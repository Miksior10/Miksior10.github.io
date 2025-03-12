<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    if (isset($_POST['item_id']) && isset($_POST['action'])) {
        // Pobierz aktualną ilość produktu
        $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['item_id'], $_SESSION['user_id']]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            if ($_POST['action'] === 'increase') {
                // Zwiększ ilość
                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ? AND user_id = ?");
                $stmt->execute([$_POST['item_id'], $_SESSION['user_id']]);
            } else if ($_POST['action'] === 'decrease') {
                if ($item['quantity'] > 1) {
                    // Zmniejsz ilość
                    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE id = ? AND user_id = ?");
                    $stmt->execute([$_POST['item_id'], $_SESSION['user_id']]);
                } else {
                    // Usuń produkt jeśli ilość będzie 0
                    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
                    $stmt->execute([$_POST['item_id'], $_SESSION['user_id']]);
                }
            }
        }
    }

    // Przekieruj z powrotem do koszyka
    header('Location: cart.php');
    exit();

} catch (PDOException $e) {
    header('Location: cart.php?error=update_failed');
    exit();
}
?> 