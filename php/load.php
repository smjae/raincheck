<?php

//work in progress

header("Access-Control-Allow-Origin: https://raincheck.ch");

// require once config.php!
require_once __DIR__ . '/../config.php';

try {
    // save data from sensor into database: time
    $sql = "INSERT INTO Anfragen (timestamp) VALUES (NOW())";
    $pdo->exec($sql);

    echo "Daten erfolgreich gespeichert.";

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()], JSON_THROW_ON_ERROR);
}