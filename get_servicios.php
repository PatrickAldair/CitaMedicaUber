<?php
require_once "db.php";

header('Content-Type: application/json');

$especialidad_id = $_GET['especialidad_id'] ?? null;

if ($especialidad_id) {
    $stmt = $pdo->prepare("SELECT nombre FROM servicios WHERE especialidad_id = ?");
    $stmt->execute([$especialidad_id]);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($servicios);
} else {
    echo json_encode([]);
}
