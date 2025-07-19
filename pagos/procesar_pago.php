<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'paciente') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cita_id = $_POST['cita_id'] ?? null;
    $metodo = $_POST['metodo'] ?? '';
    $monto = floatval($_POST['monto'] ?? 0);

    if (!$cita_id || !$metodo || $monto <= 0) {
        die("Datos incompletos para procesar el pago.");
    }

    $stmt = $pdo->prepare("SELECT * FROM citas WHERE id = ? AND id_paciente = ?");
    $stmt->execute([$cita_id, $_SESSION['usuario']['id']]);
    $cita = $stmt->fetch();

    if (!$cita) {
        die("Cita inválida o no autorizada.");
    }

    $estado_pago = "pendiente";
    $mensaje = "";

    if ($metodo === "tarjeta") {
        $estado_pago = "pagado";
        $mensaje = "Pago exitoso. La cita ha sido confirmada.";
    } elseif ($metodo === "yape") {
        $estado_pago = "por_confirmar";
        $mensaje = "Pago Yape enviado. Esperando validación del doctor.";
    } elseif ($metodo === "efectivo") {
        $estado_pago = "pagado";
        $mensaje = "El pago se realizará en persona. La cita ha sido confirmada.";
    }

    $comision = round($monto * 0.03, 2);
    $ganancia_doctor = round($monto - $comision, 2);

    $stmtPago = $pdo->prepare("
        INSERT INTO pagos (id_cita, metodo, estado, monto, ganancia_doctor)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtPago->execute([$cita_id, $metodo, $estado_pago, $monto, $ganancia_doctor]);

    $pdo->prepare("
        UPDATE citas SET estado = 'aceptada', estado_pago = ? WHERE id = ?
    ")->execute([$estado_pago, $cita_id]);

    unset($_SESSION['precio_pago'], $_SESSION['cita_pago']);

    $_SESSION['exito'] = $mensaje;
    header("Location: ../paciente/notificaciones.php");
    exit();
}
?>
