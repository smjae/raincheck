<?php
//Header wird gesetzt, um CORS zu ermöglichen
header("Access-Control-Allow-Origin: https://raincheck.ch");

//config.php wird eingebunden
require_once __DIR__ . '/../config.php';

// API-URL
$url = "https://api.open-meteo.com/v1/forecast?latitude=46.8499&longitude=9.5329&daily=temperature_2m_max,precipitation_sum,snowfall_sum,wind_speed_10m_max&timezone=Europe%2FBerlin&forecast_days=1";

// cURL-Verbindung wird aufgebaut
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$output = curl_exec($ch);

// Speichere alle Daten in Variablen
$data = json_decode($output, true);

// Neuer Array für die Wetterdaten
$weather_data = [];

// Umformatierung der Daten
$dailyTime = isset($data['daily']['time'][0]) ? (new DateTime($data['daily']['time'][0]))->format('Y-m-d') : NULL;
$dailyTemperature = isset($data['daily']['temperature_2m_max'][0]) ? $data['daily']['temperature_2m_max'][0] : NULL;
$daily_precipitation_sum = isset($data['daily']['precipitation_sum'][0]) ? $data['daily']['precipitation_sum'][0] : NULL;
$daily_snowfall_sum = isset($data['daily']['snowfall_sum'][0]) ? $data['daily']['snowfall_sum'][0] : NULL;
$daily_wind_speed_max = isset($data['daily']['wind_speed_10m_max'][0]) ? $data['daily']['wind_speed_10m_max'][0] : NULL;

$weather_data[] = [
    'datum' => $dailyTime,
    'temperatur' => $dailyTemperature,
    'tagesniederschlag_sum' => $daily_precipitation_sum,
    'schneefall_sum' => $daily_snowfall_sum,
    'windgeschwindigkeit_max' => $daily_wind_speed_max
];

echo "Extraktion erfolgreich.";
echo "<br>";

// das heutige Datum wird ermittelt und in eine Variable gespeichert
$today = date("Y-m-d");

// Speichern der Daten in die Datenbank via PDO-Verbindung
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    // Abfrage des neusten Eintrags der Wettervorhersage in der Datenbank
    $sql = "SELECT * FROM Wettervorhersage ORDER BY timestamp DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $last_weather_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // // Debugging information
    // echo "Last weather data: ";
    // print_r($last_weather_data);
    // echo "<br>";

    // Abgleich der Daten
    $is_data_new = !$last_weather_data ||
        $last_weather_data['datum'] != $weather_data[0]['datum'] ||
        $last_weather_data['temperatur'] != $weather_data[0]['temperatur'] ||
        $last_weather_data['tagesniederschlag_sum'] != $weather_data[0]['tagesniederschlag_sum'] ||
        $last_weather_data['schneefall_sum'] != $weather_data[0]['schneefall_sum'] ||
        $last_weather_data['windgeschwindigkeit_max'] != $weather_data[0]['windgeschwindigkeit_max'];

        //falls die Daten neu sind und das Datum von heute ist, werden die Daten in die Datenbank eingefügt
    if ($is_data_new && $weather_data[0]['datum'] == $today) {
        // echo "Daten sind noch nicht in der Tabelle.";
        // echo "<br>";
        // echo "Neue Wetter Daten: ";
        // print_r($weather_data[0]);
        // echo "<br>";

        $sql = "INSERT INTO Wettervorhersage (datum, temperatur, tagesniederschlag_sum, schneefall_sum, windgeschwindigkeit_max) VALUES (?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $weather_data[0]['datum'],
            $weather_data[0]['temperatur'],
            $weather_data[0]['tagesniederschlag_sum'],
            $weather_data[0]['schneefall_sum'],
            $weather_data[0]['windgeschwindigkeit_max']
        ]);
        echo "Daten erfolgreich eingefügt.";
        
    } else if ($weather_data[0]['datum'] != $today) { //falls das Datum nicht von heute ist, wird eine Fehlermeldung ausgegeben
        echo "Daten sind nicht von heute.";
    } else { //falls die Daten bereits in der Tabelle sind, wird eine Fehlermeldung ausgegeben
        echo "Daten sind bereits in der Tabelle.";
    }
    
    // Fehlerbehandlung
} catch (PDOException $e) {
    die("Verbindung zur Datenbank konnte nicht hergestellt werden: " . $e->getMessage());
}

