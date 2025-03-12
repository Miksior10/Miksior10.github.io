<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    if (isset($_POST['item_id'])) {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['item_id'], $_SESSION['user_id']]);
    }
    
    header('Location: cart.php');
    exit();
    
} catch (PDOException $e) {
    header('Location: cart.php?error=remove_failed');
    exit();
}
?> 