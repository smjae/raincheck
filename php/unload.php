<?php
// config.php wird eingebunden
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// das heutige Datum und das von vor einer Woche werden ermittelt und in Variablen gespeichert
$today = date("Y-m-d");
$oneWeekAgo = date("Y-m-d H:i:s", strtotime("-1 week"));

// Datenbankverbindung wird hergestellt
try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Erste Abfrage, um den neusten Eintrag der Wettervorhersage in der Datenbank zu erhalten
    $query1 = "SELECT * FROM Wettervorhersage ORDER BY timestamp DESC LIMIT 1";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->execute();
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

    // Überprüfen, ob der neuste Eintrag das heutige Datum hat
    if ($result1['datum'] !== $today) {
        throw new Exception("The newest entry's datum is not today's date.");
    }

    // Zweite Abfrage, um die Anzahl der Anfragen der letzten Woche zu erhalten
    $query2 = "SELECT DATE(detection_time) as date, COUNT(*) as count FROM Anfragen WHERE detection_time >= :oneWeekAgo GROUP BY DATE(detection_time) ORDER BY date DESC";
    $stmt2 = $pdo->prepare($query2);
    $stmt2->bindParam(':oneWeekAgo', $oneWeekAgo);
    $stmt2->execute();
    $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Resultate werden als JSON zurückgegeben
    $result = [
        'wettervorhersage' => $result1,
        'anfragen' => $result2
    ];

    echo json_encode(['data' => $result], JSON_THROW_ON_ERROR);

    // Fehlerbehandlung
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()], JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'JSON encoding error: ' . $e->getMessage()], JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected error: ' . $e->getMessage()], JSON_THROW_ON_ERROR);
}




