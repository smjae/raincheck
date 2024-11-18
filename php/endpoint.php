<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

//create new variable with today's date
$today = date("Y-m-d");

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM Wettervorhersage ORDER BY timestamp DESC LIMIT 1 WHERE datum = $today";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['datum'] === $today) {
        echo json_encode(['data' => $result], JSON_THROW_ON_ERROR);
    } else {
        echo json_encode(['data' => null], JSON_THROW_ON_ERROR);
    }
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
