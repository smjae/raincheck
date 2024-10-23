<?php
// require once config.php
require_once 'config.php';

try {
    $currentTime = []; // current time in iso8601 format (YYYY-MM-DDTHH:MM) Neue Werte alle 15 Minuten
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
        $currentTime[] = $row['unixtime'];
        $currentTemperature[] = $row['temperature'];
        $daily_precipitation_sum[] = $row['tagesniederschlag_sum'];
        $daily_precipitation_probability_max[] = $row['tagesniederschlag_max'];
    }

    $data = [
        'currentTime' => array_map('floatval', $currentTime),
        'currentTemperature' => array_map('floatval', $currentTemperature),
        'daily_precipitation_sum' => array_map('floatval', $daily_precipitation_sum),
        'daily_precipitation_probability_max' => array_map('floatval', $daily_precipitation_probability_max),
    ];

    $allData = json_encode(['data' => $data]);

    echo $allData;

    // Code zum Testen der letzten Daten aus dem JSON-Objekt
    $decodedData = json_decode($allData, true);
    
    // Auskommentierte Debug-Ausgaben bleiben erhalten
    // echo "<br><br>";
    // echo "Latest currentTime: " . end($decodedData['data']['currentTime']) . "<br>";
    // echo "Latest currentTemperature: " . end($decodedData['data']['currentTemperature']) . "<br>";
    // echo "Latest daily_precipitation_sum: " . end($decodedData['data']['daily_precipitation_sum']) . "<br>";
    // echo "Latest daily_precipitation_probability_max: " . end($decodedData['data']['daily_precipitation_probability_max']) . "<br>";

} catch (PDOException $e) {
    die("ERROR: Could not able to execute $query. " . $e->getMessage());
}
