<?php
// config.php wird eingebunden
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// das heutige Datum wird ermittelt und in eine Variable gespeichert
$today = date("Y-m-d");

// Datenbankverbindung wird hergestellt
try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Abfrage, um die neuste Wettervorhersage für das heutige Datum zu erhalten
    $query = "SELECT * FROM Wettervorhersage WHERE datum = :today ORDER BY timestamp DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':today', $today);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // Wenn ein Ergebnis mit dem selben Datum wie "$today" gefunden wurde, wird dieses als JSON zurückgegeben
    if ($result && $result['datum'] === $today) {
        echo json_encode(['data' => $result], JSON_THROW_ON_ERROR);
    } else { // Wenn kein Ergebnis gefunden wurde, wird ein leeres JSON-Objekt zurückgegeben
        echo json_encode(['data' => null], JSON_THROW_ON_ERROR);
    }

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
