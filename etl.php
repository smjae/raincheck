<?php

header("Access-Control-Allow-Origin: https://raincheck.ch");

// requiere once config.php!
require_once 'config.php';

// Extract and transform data
$url = "https://api.open-meteo.com/v1/forecast?latitude=46.8499&longitude=9.5329&daily=temperature_2m_max,precipitation_sum,snowfall_sum,wind_speed_10m_max&timezone=Europe%2FBerlin&forecast_days=3";

// curl
$ch = curl_init($url);

// curl options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$output = curl_exec($ch);

// Speichere alle Daten in Variablen
$data = json_decode($output, true); // decode the JSON feed

// make new array with needed information
$weather_data = [];

// Process data
// $dailyTime = isset($data['daily']['time']) ? strtotime($data['daily']['time']) : NULL;
$dailyTemperature = isset($data['daily']['temperature_2m_max']) ? $data['daily']['temperature_2m_max'] : NULL;
$daily_precipitation_sum = isset($data['daily']['precipitation_sum'][0]) ? $data['daily']['precipitation_sum'][0] : NULL;
// $daily_precipitation_probability_max = isset($data['daily']['precipitation_probability_max'][0]) ? $data['daily']['precipitation_probability_max'][0] : NULL;
$daily_snowfall_sum = isset($data['daily']['snowfall_sum'][0]) ? $data['daily']['snowfall_sum'][0] : NULL;
$daily_wind_speed_max = isset($data['daily']['wind_speed_10m_max'][0]) ? $data['daily']['wind_speed_10m_max'][0] : NULL;

$weather_data[] = [
    'unixtime' => $dailyTime,
    'temperatur' => $dailyTemperature,
    'tagesniederschlag_sum' => $daily_precipitation_sum,
    'schneefall_sum' => $daily_snowfall_sum,
    'windgeschwindigkeit_max' => $daily_wind_speed_max
];

echo "Extraktion erfolgreich.";
echo "<br>";

// Load data into database
try {
    // Erstellt eine neue PDO-Instanz mit der Konfiguration aus config.php
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    //insert data into table
    // get data from table, check if unixtime is already in the table, if not insert data
    $sql = "SELECT * FROM Wettervorhersage ORDER BY unixtime DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $last_weather_data = $stmt->fetch();

    if (
        !isset($last_weather_data['unixtime']) ||
        $last_weather_data['unixtime'] != $weather_data[0]['unixtime'] ||
        $last_weather_data['temperatur'] != $weather_data[0]['temperatur'] ||
        $last_weather_data['tagesniederschlag_sum'] != $weather_data[0]['tagesniederschlag_sum'] ||
        $last_weather_data['schneefall_sum'] != $weather_data[0]['schneefall_sum'] ||
        $last_weather_data['windgeschwindigkeit_max'] != $weather_data[0]['windgeschwindigkeit_max']
    ) {
        echo "Daten sind noch nicht in der Tabelle.";
        echo "<br>";
    
        // SQL-Query mit Platzhaltern für das Einfügen von Daten
        $sql = "INSERT INTO Wettervorhersage (unixtime, temperatur, tagesniederschlag_sum, schneefall_sum, windgeschwindigkeit_max) VALUES (?, ?, ?, ?)";


        // Bereitet die SQL-Anweisung vor
        $stmt = $pdo->prepare($sql);

        // Fügt jedes Element im Array in die Datenbank ein
        foreach ($weather_data as $item) {
            $stmt->execute([
                $item['unixtime'],
                $item['temperatur'],
                $item['tagesniederschlag_sum'],
                $item['schneefall_sum'],
                $item['windgeschwindigkeit_max']
            ]);
        } 
        echo "Daten erfolgreich eingefügt.";
        
    } else {
        echo "Daten sind bereits in der Tabelle.";
    }
} catch (PDOException $e) {
    die("Verbindung zur Datenbank konnte nicht hergestellt werden: " . $e->getMessage());
}

