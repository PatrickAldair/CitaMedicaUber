<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$doctor_id = $_SESSION['usuario']['id'];
$pago_id = $_GET['id'] ?? null;
$accion = $_GET['accion'] ?? null;

if (!$pago_id || !in_array($accion, ['aceptar', 'rechazar'])) {
    die("Parámetros inválidos.");
}

$stmt = $pdo->prepare("
    SELECT p.*, c.id_doctor
    FROM pagos p
    JOIN citas c ON p.id_cita = c.id
    WHERE p.id = ?
");
$stmt->execute([$pago_id]);
$pago = $stmt->fetch();

if (!$pago || $pago['id_doctor'] != $doctor_id) {
    die("Pago no válido.");
}

if ($accion === 'aceptar') {
    $pdo->prepare("UPDATE pagos SET estado = 'pagado' WHERE id = ?")->execute([$pago_id]);
    $pdo->prepare("UPDATE citas SET estado = 'aceptada', estado_pago = 'pagado' WHERE id = ?")->execute([$pago['id_cita']]);
    $_SESSION['exito'] = "Pago confirmado y cita aceptada.";
} elseif ($accion === 'rechazar') {
    $pdo->prepare("UPDATE pagos SET estado = 'rechazado' WHERE id = ?")->execute([$pago_id]);
    $pdo->prepare("UPDATE citas SET estado = 'rechazada', estado_pago = 'rechazado' WHERE id = ?")->execute([$pago['id_cita']]);
    $_SESSION['exito'] = "Pago rechazado y cita cancelada.";
}

header("Location: validar_pagos.php");
exit();
?>
