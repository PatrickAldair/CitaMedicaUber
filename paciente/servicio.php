<?php
require '../db.php';

$idDoctor = $_GET['id'] ?? null;

if (!$idDoctor) {
  echo json_encode([]);
  exit;
}

$stmt = $pdo->prepare("
  SELECT s.id, s.nombre 
  FROM usuarios u 
  JOIN especialidades e ON u.especialidad_id = e.id 
  JOIN servicios s ON s.especialidad_id = e.id 
  WHERE u.id = ?
");
$stmt->execute([$idDoctor]);

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
