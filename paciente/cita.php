<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'paciente') {
    http_response_code(403);
    exit;
}
require '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$stmt = $pdo->prepare("INSERT INTO citas (id_paciente, id_doctor, fecha, id_servicio) VALUES (?, ?, ?, ?)");
$stmt->execute([
  $_SESSION['usuario']['id'],
  $data['id_doctor'],
  $data['fecha'],
  $data['id_servicio']
]);
echo "Cita solicitada";
?>