<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $idCita = $_POST['id'];
    $doctorId = $_SESSION['usuario']['id'];

    $stmt = $pdo->prepare("UPDATE citas SET finalizada = 1 WHERE id = ? AND id_doctor = ?");
    $stmt->execute([$idCita, $doctorId]);
}

header('Location: pacientes_por_atender.php');
exit;
