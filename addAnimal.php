<?php
session_start();

if (!isset($_SESSION['auth_token'])) {
    header('Location: login.php');
    exit;
}

// Base URL of the Xano API
define('XANO_BASE_URL', 'https://x8ki-letl-twmt.n7.xano.io/api:d1Dze3Ht');

// Function to send API requests
function sendApiRequest($endpoint, $method = 'GET', $data = null) {
    $curl = curl_init();
    $reqUrl = XANO_BASE_URL . $endpoint;

    curl_setopt($curl, CURLOPT_URL, $reqUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    // Set HTTP method
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    // Set request body if data is provided
    if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    // Handle cURL errors
    if (curl_errno($curl)) {
        echo "cURL Error: " . curl_error($curl);
        curl_close($curl);
        return null;
    }

    curl_close($curl);

    return ['response' => json_decode($response, true), 'http_code' => $httpCode];
}

// Add a new animal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'species' => $_POST['species'],
        'adoption_status' => $_POST['adoption_status'],
        'description' => $_POST['description'],
    ];

    $result = sendApiRequest('/animal', 'POST', $data);

    if ($result && ($result['http_code'] === 200 || $result['http_code'] === 201)) {
        echo "<p style='color: green;'>Animal added successfully!</p>";
        sleep(2);
        header('Location: index.php');

    } else {
        echo "<p style='color: red;'>Failed to add animal: " . htmlspecialchars(json_encode($result['response'] ?? 'Unknown error')) . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <title>Add Animal</title>
</head>
<body>
<div class="container">
    <div class="sessionTab">
        <a href="index.php" class="backBtn"><span class="glyphicon glyphicon-arrow-left"></span> Back to List</a>
        <a href="logout.php" class="logoutBtn"><span class="glyphicon glyphicon-log-out"></span> Log out</a>
    </div>

    <h1>Add a New Animal</h1>

    <form method="POST" action="">
        <label for="name">Name:</label>
        <input type="text" name="name" required>
        <br>
        <label for="species">Species:</label>
        <input type="text" name="species" required>
        <br>
        <label for="adoption_status">Adoption Status:</label>
        <select name="adoption_status" required>
            <option value="available">Available</option>
            <option value="reserved">Reserved</option>
            <option value="adopted">Adopted</option>
        </select>
        <br>
        <label for="description">Description:</label>
        <textarea name="description"></textarea>
        <br>
        <button type="submit" class="btn btn-primary">Add Animal</button>
    </form>
</div>
</body>
</html>
