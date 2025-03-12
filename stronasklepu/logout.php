<?php
session_start();

// Usuwanie wszystkich zmiennych sesyjnych
$_SESSION = array();

// Usuwanie sesji
session_destroy();

// Przekierowanie na stronę główną
header("Location: index.php");
exit();
?> 