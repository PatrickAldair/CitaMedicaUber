<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cita_id = $_POST['cita_id'];
    $precio = floatval($_POST['precio']);
    $doctor_id = $_SESSION['usuario']['id'];

    $stmt = $pdo->prepare("UPDATE citas SET precio_propuesto = ?, estado = 'pendiente' WHERE id = ? AND id_doctor = ?");
    $stmt->execute([$precio, $cita_id, $doctor_id]);

    $_SESSION['mensaje'] = "Precio propuesto correctamente.";
    header("Location: dashboard.php");
    exit();
}
