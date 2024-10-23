<?php
// require once config.php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $currentTime = []; // current time in ISO8601 format (YYYY-MM-DDTHH:MM:SS) New values every 15 minutes
    $currentTemperature = []; // current temperature in °C
    $daily_precipitation_sum = []; // daily precipitation sum in mm
    $daily_precipitation_probability_max = []; // daily precipitation probability max in %

    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $query = "SELECT unixtime, temperature, tagesniederschlag_sum, tagesniederschlag_max FROM Wettervorhersage";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($result as $row) {
        $currentTime[] = date('c', $row['unixtime']); // Convert Unix timestamp to ISO8601
        $currentTemperature[] = $row['temperature'];
        $daily_precipitation_sum[] = $row['tagesniederschlag_sum'];
        $daily_precipitation_probability_max[] = $row['tagesniederschlag_max'];
    }

    $data = [
        'currentTime' => $currentTime,
        'currentTemperature' => array_map('floatval', $currentTemperature),
        'daily_precipitation_sum' => array_map('floatval', $daily_precipitation_sum),
        'daily_precipitation_probability_max' => array_map('floatval', $daily_precipitation_probability_max),
    ];

    echo json_encode(['data' => $data], JSON_THROW_ON_ERROR);

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
