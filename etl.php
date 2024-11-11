<?php
header("Access-Control-Allow-Origin: https://raincheck.ch");
require_once 'config.php';

$url = "https://api.open-meteo.com/v1/forecast?latitude=46.8499&longitude=9.5329&daily=temperature_2m_max,precipitation_sum,snowfall_sum,wind_speed_10m_max&timezone=Europe%2FBerlin&forecast_days=1";
$output = curl_exec(curl_init($url));

// Decode JSON and create a weather data array with essential info
$data = json_decode($output, true);
echo json_encode($data, JSON_THROW_ON_ERROR);

// make new array with needed information
$weather_data = [];

$datum = strtotime($data['daily']['time'][0]);

// save data into string variable
$weather_data[] = [
    'datum' => $datum,
    'currentTemperature' => $data['daily']['temperature_2m_max'],
    'daily_precipitation_sum' => $data['daily']['precipitation_sum'],
    'daily_snowfall_sum' => $data['daily']['snowfall_sum'],
    'daily_wind_speed_max' => $data['daily']['wind_speed_10m_max']
];

// Insert weather data into database
try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM Wettervorhersage ORDER BY datum DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $last_weather_data = $stmt->fetch();
    if ($last_weather_data['datum'] != $weather_data[0]['datum']) {
        echo "Daten sind noch nicht in der Tabelle";
        $sql = "INSERT INTO Wettervorhersage (datum, temperatur, tagesniederschlag_sum, schneefall_sum, windgeschwindigkeit_max) VALUES (:datum, :currentTemperature, :daily_precipitation_sum, :daily_snowfall_sum, :daily_wind_speed_max)";
        // Bereitet die SQL-Anweisung vor
        $stmt = $pdo->prepare($sql);
        // Fügt jedes Element im Array in die Datenbank ein
        foreach ($weather_data as $row) {
            $stmt->execute($row);
        }   
    } else {
        echo "Daten sind schon in der Tabelle";
    }
} catch (PDOException $e) {
    echo "Fehler beim Einfügen der Daten";
}