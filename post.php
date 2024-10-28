<?php
// require once config.php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve and decode the JSON data from the POST request
        $inputData = json_decode(file_get_contents("php://input"), true);
        

        // Ensure we have the required fields
        if (isset($inputData['temperature'], $inputData['precipitation_sum'], $inputData['precipitation_probability'])) {
            // Prepare SQL to insert data into the "Anfragen" table
            $insertQuery = "INSERT INTO Anfragen (unixtime, temperature, tagesniederschlag_sum, tagesniederschlag_max) 
                            VALUES (:unixtime, :temperature, :tagesniederschlag_sum, :tagesniederschlag_max)";
            $insertStmt = $pdo->prepare($insertQuery);

            // Execute the prepared statement with the data from the POST request
            $insertStmt->execute([
                ':unixtime' => time(), // Use the current Unix timestamp
                ':temperature' => $inputData['temperature'],
                ':tagesniederschlag_sum' => $inputData['precipitation_sum'],
                ':tagesniederschlag_max' => $inputData['precipitation_probability']
            ]);

            // Respond with a success message
            echo json_encode(['message' => 'Data successfully inserted into Anfragen.'], JSON_THROW_ON_ERROR);
            exit; // Stop further execution
        } else {
            // Respond with an error if required fields are missing
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input data.'], JSON_THROW_ON_ERROR);
            exit;
        }
    } else {
        // Respond with an error if the request method is not POST
        http_response_code(405);
        echo json_encode(['error' => 'Only POST requests are allowed.'], JSON_THROW_ON_ERROR);
        exit;
    }

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
