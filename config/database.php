<?php
$host = "localhost";
$dbname = "millebytesdb"; // Nome della tabella esistente
$username = "root"; // Utente predefinito di XAMPP
$password = ""; // Vuoto di default su XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connessione fallita: " . $e->getMessage());
}
?>
