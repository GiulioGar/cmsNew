<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT id, name, roles FROM t_users WHERE name = ? AND password = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['roles'] = $user['roles'];
            header("Location: index.php");
            exit();
        } else {
            header("Location: pages-sign-in.php?error=1"); // Credenziali errate
            exit();
        }
    } catch (Exception $e) {
        header("Location: pages-sign-in.php?error=2"); // Errore generico
        exit();
    }
} else {
    header("Location: pages-sign-in.php");
    exit();
}
?>
