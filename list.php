<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Méthode non autorisée.');
}

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$obj = trim($_POST['obj'] ?? '');

if ($nom === '' || $prenom === '' || $email === '' || $obj === '') {
    exit('Tous les champs sont obligatoires.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Adresse email invalide.');
}

if (!extension_loaded('mysqli')) {
    exit("L'extension PHP mysqli n'est pas activée.");
}

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli('127.0.0.1', 'root', 'root', 'gestalist', 8889);
if ($conn->connect_errno) {
    exit('Connexion MySQL échouée : ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

$createdAtColumn = $conn->query("SHOW COLUMNS FROM identitiication LIKE 'created_at'");
if (!$createdAtColumn) {
    $conn->close();
    exit('Erreur vérification date : ' . $conn->error);
}

if ($createdAtColumn->num_rows === 0 && !$conn->query("ALTER TABLE identitiication ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
    $createdAtColumn->free();
    $conn->close();
    exit('Erreur ajout date : ' . $conn->error);
}
$createdAtColumn->free();

$sql = "INSERT INTO identitiication (nom, prenom, email, obj) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $conn->close();
    exit('Erreur SQL : ' . $conn->error);
}

$stmt->bind_param("ssss", $nom, $prenom, $email, $obj);
if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    exit('Erreur insertion : ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: list.html');
exit;
?>