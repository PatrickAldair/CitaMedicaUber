<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'paciente') {
    header("Location: ../login.php");
    exit();
}

$cita_id = $_GET['id'] ?? null;
$respuesta = $_GET['res'] ?? null;

if (!$cita_id || !in_array($respuesta, ['aceptar', 'rechazar'])) {
    die("Parámetros inválidos.");
}

$stmt = $pdo->prepare("SELECT * FROM citas WHERE id = ? AND id_paciente = ?");
$stmt->execute([$cita_id, $_SESSION['usuario']['id']]);
$cita = $stmt->fetch();

if (!$cita) {
    die("Cita no encontrada.");
}

if ($respuesta === 'aceptar' && $cita['precio_propuesto'] === null) {
    die("No se puede aceptar una cita sin precio propuesto.");
}

if ($respuesta === 'rechazar') {
    $pdo->prepare("UPDATE citas SET estado = 'rechazada', aceptado_por_paciente = 2 WHERE id = ?")
        ->execute([$cita_id]);

    $_SESSION['exito'] = "Has rechazado la cita.";
    header("Location: notificaciones.php");
    exit();
}

if ($respuesta === 'aceptar') {
    $pdo->prepare("UPDATE citas SET aceptado_por_paciente = 1 WHERE id = ?")
        ->execute([$cita_id]);
    $_SESSION['precio_pago'] = $cita['precio_propuesto'];
    $_SESSION['cita_pago'] = $cita_id;
    header("Location: ../pagos/formulario_pago.php");
    exit();
}
?>
