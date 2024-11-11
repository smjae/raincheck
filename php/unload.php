<?php

//work in progress

header("Access-Control-Allow-Origin: https://raincheck.ch");

// require once config.php!
require_once '../config.php';

try {
    // save data from sensor into database: time
    $sql = "GET * FROM Wettervorhersage ORDER BY datum DESC LIMIT 20";
    $pdo->exec($sql);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()], JSON_THROW_ON_ERROR);
}




