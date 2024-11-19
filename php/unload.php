<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
$today = date("Y-m-d");
$oneWeekAgo = date("Y-m-d h:m:s", strtotime("-1 week"));

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // First query
    $query1 = "SELECT * FROM Wettervorhersage ORDER BY timestamp DESC LIMIT 1";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->execute();
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

    // Check if the newest entry's datum is today's date
    if ($result1['datum'] !== $today) {
        throw new Exception("The newest entry's datum is not today's date.");
    }

    // Second query
    $query2 = "SELECT * FROM Anfragen WHERE detection_time >= '$oneWeekAgo' ORDER BY timestamp DESC";
    $stmt2 = $pdo->prepare($query2);
    $stmt2->execute();
    $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Combine results
    $result = [
        'wettervorhersage' => $result1,
        'anfragen' => $result2
    ];

    echo json_encode(['data' => $result], JSON_THROW_ON_ERROR);
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




