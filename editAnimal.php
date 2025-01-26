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
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        echo "cURL Error: " . curl_error($curl);
        curl_close($curl);
        return null;
    }

    curl_close($curl);

    return ['response' => json_decode($response, true), 'http_code' => $httpCode];
}

// Get animal ID from query parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Animal ID is required.");
}
$animalId = $_GET['id'];

// Fetch animal details
$animalResponse = sendApiRequest("/animal/{$animalId}", 'GET');
$animal = $animalResponse['response'] ?? null;
$httpCode = $animalResponse['http_code'] ?? 0;

if ($httpCode !== 200 || !$animal) {
    die("Failed to fetch animal details. HTTP Code: {$httpCode}");
}

// Update animal details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'animal_id' => $animalId,
        'name' => $_POST['name'],
        'species' => $_POST['species'],
        'adoption_status' => $_POST['adoption_status'],
        'description' => $_POST['description'],
    ];

    $updateResponse = sendApiRequest("/animal/{$animalId}", 'PATCH', $data);

    if ($updateResponse && ($updateResponse['http_code'] === 200 || $updateResponse['http_code'] === 204)) {
        echo "<p style='color: green;'>Animal updated successfully!</p>";
        header('Location: index.php');
    } else {
        echo "<p style='color: red;'>Failed to update animal: " . htmlspecialchars(json_encode($updateResponse['response'] ?? 'Unknown error')) . "</p>";
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
    <title>Edit Animal</title>
</head>
<body>
<div class="container">
    <div class="sessionTab">
        <a href="index.php" class="backBtn"><span class="glyphicon glyphicon-arrow-left"></span> Back to List</a>
        <a href="logout.php" class="logoutBtn"><span class="glyphicon glyphicon-log-out"></span> Log out</a>
    </div>

    <h1>Edit Animal</h1>

    <form method="POST" action="">
        <label for="name">Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($animal['name']) ?>" required>
        <br>
        <label for="species">Species:</label>
        <input type="text" name="species" value="<?= htmlspecialchars($animal['species']) ?>" required>
        <br>
        <label for="adoption_status">Adoption Status:</label>
        <select name="adoption_status" required>
            <option value="available" <?= $animal['adoption_status'] === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="reserved" <?= $animal['adoption_status'] === 'reserved' ? 'selected' : '' ?>>Reserved</option>
            <option value="adopted" <?= $animal['adoption_status'] === 'adopted' ? 'selected' : '' ?>>Adopted</option>
        </select>
        <br>
        <label for="description">Description:</label>
        <textarea name="description"><?= htmlspecialchars($animal['description']) ?></textarea>
        <br>
        <button type="submit" class="btn btn-primary">Update Animal</button>
    </form>
</div>
</body>
</html>
