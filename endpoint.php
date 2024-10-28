<?php
// require once config.php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the request is a POST request to handle data insertion
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve and decode the JSON data from the POST request
        $inputData = json_decode(file_get_contents("php://input"), true);

        // Make sure we received valid data
        if (isset($inputData['temperature'], $inputData['precipitation_sum'], $inputData['precipitation_probability'])) {
            // Prepare the SQL insert statement for the Abfragen table
            $insertQuery = "INSERT INTO Abfragen (unixtime, temperature, tagesniederschlag_sum, tagesniederschlag_max) 
                            VALUES (:unixtime, :temperature, :tagesniederschlag_sum, :tagesniederschlag_max)";
            $insertStmt = $pdo->prepare($insertQuery);

            // Bind values and execute the insert statement
            $insertStmt->execute([
                ':unixtime' => time(),
                ':temperature' => $inputData['temperature'],
                ':tagesniederschlag_sum' => $inputData['precipitation_sum'],
                ':tagesniederschlag_max' => $inputData['precipitation_probability']
            ]);

            echo json_encode(['message' => 'Data successfully inserted into Abfragen.'], JSON_THROW_ON_ERROR);
            exit; // Exit here since we don't need to return any more data
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input data'], JSON_THROW_ON_ERROR);
            exit;
        }
    }

    // Existing code for fetching the latest record from Wettervorhersage
    $query = "SELECT unixtime, temperature, tagesniederschlag_sum, tagesniederschlag_max FROM Wettervorhersage ORDER BY unixtime DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentTime = [];
    $currentTemperature = [];
    $daily_precipitation_sum = [];
    $daily_precipitation_probability_max = [];

    foreach ($result as $row) {
        // Convert Unix timestamp to DDMMYYYY HH:MM format
        $currentTime[] = date('dmY H:i', (int) $row['unixtime']);
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
