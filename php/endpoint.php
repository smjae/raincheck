<?php
require_once 'php/config.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT datum, temperatur, tagesniederschlag_sum, schneefall_sum, windgeschwindigkeit_max FROM Wettervorhersage ORDER BY datum DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
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
