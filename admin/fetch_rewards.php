<?php
session_start();
require_once '../config/database.php';

try {
    // Query per contare tutte le richieste non pagate
    $stmt = $pdo->query("SELECT COUNT(user_id) as total_requests FROM t_user_history WHERE event_type='withdraw' AND pagato=0");
    $totalRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total_requests'];

    // Query per contare le richieste di "Buono Amazon"
    $stmt = $pdo->query("SELECT COUNT(user_id) as amazon_requests FROM t_user_history WHERE event_type='withdraw' AND pagato=0 AND event_info LIKE '%Buono Amazon%'");
    $amazonRequests = $stmt->fetch(PDO::FETCH_ASSOC)['amazon_requests'];

    // Query per contare le richieste di "Ricarica Paypal"
    $stmt = $pdo->query("SELECT COUNT(user_id) as paypal_requests FROM t_user_history WHERE event_type='withdraw' AND pagato=0 AND event_info LIKE '%Ricarica Paypal%'");
    $paypalRequests = $stmt->fetch(PDO::FETCH_ASSOC)['paypal_requests'];

    // Restituisce i dati in JSON
    echo json_encode([
        'total' => $totalRequests,
        'amazon' => $amazonRequests,
        'paypal' => $paypalRequests
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Errore nel recupero dati']);
}
?>
