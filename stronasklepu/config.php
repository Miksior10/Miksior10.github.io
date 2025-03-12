<?php
try {
    $host = 'localhost';
    $dbname = 'sklep_online';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Błąd połączenia z bazą danych: " . $e->getMessage();
    die();
}
?> 