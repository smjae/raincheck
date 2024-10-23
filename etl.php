<?php

// requiere once config.php!
require_once 'config.php';

// Extract and transform data
$url = "https://api.open-meteo.com/v1/forecast?latitude=46.85327613490756&longitude=9.528191447119516&current=temperature_2m&daily=precipitation_sum,precipitation_probability_max&timezone=Europe%2FBerlin&forecast_days=1";

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
$currentTime = isset($data['current']['time']) ? strtotime($data['current']['time']) : NULL;
$currentTemperature = isset($data['current']['temperature_2m']) ? $data['current']['temperature_2m'] : NULL;
$daily_precipitation_sum = isset($data['daily']['precipitation_sum'][0]) ? $data['daily']['precipitation_sum'][0] : NULL;
$daily_precipitation_probability_max = isset($data['daily']['precipitation_probability_max'][0]) ? $data['daily']['precipitation_probability_max'][0] : NULL;

$weather_data[] = [
    'unixtime' => $currentTime,
    'temperature' => $currentTemperature,
    'tagesniederschlag_sum' => $daily_precipitation_sum,
    'tagesniederschlag_max' => $daily_precipitation_probability_max
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

    if (!isset($last_weather_data['unixtime']) || $last_weather_data['unixtime'] != $weather_data[0]['unixtime']) {
        echo "Daten sind noch nicht in der Tabelle.";
        echo "<br>";
    
        // SQL-Query mit Platzhaltern für das Einfügen von Daten
        $sql = "INSERT INTO Wettervorhersage (unixtime, temperature, tagesniederschlag_sum, tagesniederschlag_max) VALUES (?, ?, ?, ?)";

        // Bereitet die SQL-Anweisung vor
        $stmt = $pdo->prepare($sql);

        // Fügt jedes Element im Array in die Datenbank ein
        foreach ($weather_data as $item) {
            $stmt->execute([
                $item['unixtime'],
                $item['temperature'],
                $item['tagesniederschlag_sum'],
                $item['tagesniederschlag_max']
            ]);
        } 
        echo "Daten erfolgreich eingefügt.";
    } else {
        echo "Daten sind bereits in der Tabelle.";
    }
} catch (PDOException $e) {
    die("Verbindung zur Datenbank konnte nicht hergestellt werden: " . $e->getMessage());
}
