<?php
// require once config.php
require_once 'config.php';

header('Content-Type: application/json');


try {
    $currentTime = []; // time in DDMMYYYY HH:MM format
    $currentTemperature = []; // current temperature in °C
    $daily_precipitation_sum = []; // daily precipitation sum in mm
    $daily_precipitation_probability_max = []; // daily precipitation probability max in %

    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT unixtime, temperatur, tagesniederschlag_sum, schneefall_sum, windgeschwindigkeit_max FROM Wettervorhersage ORDER BY unixtime DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        // Convert Unix timestamp to DDMMYYYY HH:MM format
        $currentTime[] = date('dmY H:i', (int) $row['unixtime']);
        $currentTemperature[] = $row['temperatur'];
        $daily_precipitation_sum[] = $row['tagesniederschlag_sum'];
        $daily_snowfall_sum[] = $row['schneefall_sum'];
        $daily_wind_speed_max[] = $row['windgeschwindigkeit_max'];
    }

    $data = [
        'currentTime' => $currentTime,
        'currentTemperature' => array_map('floatval', $currentTemperature),
        'daily_precipitation_sum' => array_map('floatval', $daily_precipitation_sum),
        'daily_snowfall_sum' => array_map('floatval', $daily_snowfall_sum),
        'daily_wind_speed_max' => array_map('floatval', $daily_wind_speed_max)
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
