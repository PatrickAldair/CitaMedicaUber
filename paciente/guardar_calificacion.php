<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCita = $_POST['id_cita'];
    $idDoctor = $_POST['id_doctor'];
    $idPaciente = $_SESSION['usuario']['id'];
    $estrellas = intval($_POST['estrellas']);
    $comentario = trim($_POST['comentario']);

    $stmt = $pdo->prepare("INSERT INTO calificaciones (id_cita, id_paciente, id_doctor, estrellas, comentario) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$idCita, $idPaciente, $idDoctor, $estrellas, $comentario]);
}

header('Location: calificar_doctores.php');
exit;
