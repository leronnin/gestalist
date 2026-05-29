<?php
header('Content-Type: application/json; charset=utf-8');

if (!extension_loaded('mysqli')) {
    http_response_code(500);
    echo json_encode(['error' => "L'extension PHP mysqli n'est pas activée."]);
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli('127.0.0.1', 'root', 'root', 'gestalist', 8889);
if ($conn->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Connexion MySQL échouée : ' . $conn->connect_error]);
    exit;
}

$conn->set_charset('utf8mb4');

$createdAtColumn = $conn->query("SHOW COLUMNS FROM identitiication LIKE 'created_at'");
if (!$createdAtColumn) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur vérification date : ' . $conn->error]);
    $conn->close();
    exit;
}

if ($createdAtColumn->num_rows === 0 && !$conn->query("ALTER TABLE identitiication ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur ajout date : ' . $conn->error]);
    $createdAtColumn->free();
    $conn->close();
    exit;
}
$createdAtColumn->free();

$sql = "SELECT nom, prenom, email, obj, DATE_FORMAT(created_at, '%d/%m/%Y à %H:%i') AS created_at FROM identitiication ORDER BY nom ASC, prenom ASC";
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur SQL : ' . $conn->error]);
    $conn->close();
    exit;
}

$clients = [];
while ($row = $result->fetch_assoc()) {
    $clients[] = $row;
}

$result->free();
$conn->close();

echo json_encode($clients);
?>
