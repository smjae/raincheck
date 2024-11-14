<?php

header("Access-Control-Allow-Origin: https://raincheck.ch");

# require once config.php!
require_once __DIR__ . '/../config.php';

# connect to db
try{
    $pdo = new PDO($dsn, $db_user, $db_pass, $options); 
    echo "DB Verbindung ist erfolgreich";
}
catch(PDOException $e){
    error_log("DB Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
}

# Empfangen der JSON-Daten
$inputJSON = file_get_contents('php://input'); // JSON-Daten aus dem Body der Anfrage
$input = json_decode($inputJSON, true); // Dekodieren der JSON-Daten in ein Array

# Prüfen, ob die JSON-Daten erfolgreich dekodiert wurden
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON format']);
    exit; // Beenden, wenn das JSON ungültig ist
}

$movement = $input["movement"] ?? null; // Extrahiere den Wert "movement" aus dem JSON-Input, Standardwert null
$detectionTime = $input["detectionTime"] ?? null; // Extrahiere den Wert "detectionTime" aus dem JSON-Input, Standardwert null

# Überprüfen, ob die Werte gültig sind
if ($movement !== true || $detectionTime === null) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid input: movement must be true and detectionTime must be provided']);
    exit; // Beenden, wenn die Bedingungen nicht erfüllt sind
}

# write data to db
try {
    # insert detected movement and detection time into db
    $sql = "INSERT INTO Anfragen (movement, detection_time) VALUES (?, ?)"; // Füge das Feld "detection_time" zur SQL-Anweisung hinzu
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$movement, $detectionTime]); // Füge den Wert "detectionTime" zur Ausführung hinzu

    echo "Daten erfolgreich gespeichert.";
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()], JSON_THROW_ON_ERROR);
}

?>