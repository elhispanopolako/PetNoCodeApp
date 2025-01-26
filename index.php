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


//Delete animals
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_animal') {
    $animalId = $_POST['animal_id'];
    $result = sendApiRequest("/animal/$animalId", 'DELETE');

    if ($result['http_code'] === 200) {
        echo "Animal deleted successfully!";
    } else {
        echo "Failed to delete animal: " . json_encode($result['response']);
    }
}


// Fetch all animals
$animalResponse = sendApiRequest('/animals', 'GET');
$animals = $animalResponse['response'] ?? [];
$httpCode = $animalResponse['http_code'] ?? 0;

// Check for errors in fetching animals
if ($httpCode !== 200) {
    echo "<p style='color: red;'>Failed to fetch animals. HTTP Code: {$httpCode}</p>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <title>Shelter Animal Management</title>
</head>
<body>
<div class="container">
    <div class="sessionTab">
        <span></span>
        <a href="logout.php" class="logoutBtn"><span class="glyphicon glyphicon-log-out"></span> Log out</a>
    </div>

    <h1>Shelter Animal Management</h1>
    <h2>Animals</h2>
    <a href="addAnimal.php" class="btn btn-success" style="margin-bottom: 20px;">
        <span class="glyphicon glyphicon-plus"></span> Add New Animal
    </a>
    <ul>
        <?php if (!empty($animals) && is_array($animals)): ?>
            <?php foreach ($animals as $animal): ?>
                <li id="<?= $animal['id']?>">
                    <div class="petDetails">
                        <h3></span> <?= htmlspecialchars($animal['name'] ?? 'Unnamed') ?></h3>
                        <p class="species"><span class="glyphicon glyphicon-heart"></span> <?= htmlspecialchars($animal['species'] ?? 'Unknown') ?></p>
                        <p class="status"><span class="glyphicon glyphicon-tag"></span> <?= htmlspecialchars($animal['adoption_status'] ?? 'No status') ?></p>
                        <p class="description"><span class="glyphicon glyphicon-comment"></span> <?= htmlspecialchars($animal['description'] ?? 'No description') ?>
                        </p>
                    </div>
                    <div class="petActions">
                    <a href="editAnimal.php?id=<?= $animal['id'] ?>" class="editBtn btn btn-success"><span class="glyphicon glyphicon-pencil" style="color:#fff;"></span> Edit </a>

                        <form method="POST" action="" class="action">
                                <input type="hidden" name="action" value="delete_animal">
                                <input type="hidden" name="animal_id" value="<?= htmlspecialchars($animal['id']) ?>">
                                <button type="submit" class="btn btn-danger"> <span class="glyphicon glyphicon-remove" style="color:#fff;"></span> Delete</button>
                        </form>
                    </div>
                  
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No animals found or an error occurred.</li>
        <?php endif; ?>
    </ul>
        </div>
</body>
</html>
