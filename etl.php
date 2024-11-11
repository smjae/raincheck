

<?php
header("Access-Control-Allow-Origin: https://raincheck.ch");
require_once 'config.php';

$url = "https://api.open-meteo.com/v1/forecast?latitude=46.8499&longitude=9.5329&daily=temperature_2m_max,precipitation_sum,snowfall_sum,wind_speed_10m_max&timezone=Europe%2FBerlin&forecast_days=3";
$output = curl_exec(curl_init($url));

// Decode JSON and create a weather data array with essential info
$data = json_decode($output, true);
$weather_data = [
    [
        'datum' => $data['daily']['time'] ?? NULL,
        'temperatur' => $data['daily']['temperature_2m_max'] ?? NULL,
        'tagesniederschlag_sum' => $data['daily']['precipitation_sum'][0] ?? NULL,
        'schneefall_sum' => $data['daily']['snowfall_sum'][0] ?? NULL,
        'windgeschwindigkeit_max' => $data['daily']['wind_speed_10m_max'][0] ?? NULL
    ]
];

// Insert data if it’s not already in the database
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    $stmt = $pdo->query("SELECT * FROM Wettervorhersage ORDER BY datum DESC LIMIT 1");
    $last_data = $stmt->fetch();

    $new_data = $weather_data[0];
    $is_data_new = !$last_data || array_diff_assoc($new_data, $last_data);

    if ($is_data_new) {
        $stmt = $pdo->prepare("INSERT INTO Wettervorhersage (datum, temperatur, tagesniederschlag_sum, schneefall_sum, windgeschwindigkeit_max) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array_values($new_data));
        echo "Daten erfolgreich eingefügt.";
    } else {
        echo "Daten sind bereits in der Tabelle.";
    }
} catch (PDOException $e) {
    die("Verbindung zur Datenbank konnte nicht hergestellt werden: " . $e->getMessage());
}

