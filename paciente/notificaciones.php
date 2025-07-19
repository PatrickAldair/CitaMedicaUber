<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'paciente') {
    header("Location: ../login.php");
    exit();
}

$paciente_id = $_SESSION['usuario']['id'];

$stmt = $pdo->prepare("
    SELECT c.id AS cita_id, c.fecha, c.estado, c.estado_pago,
           s.nombre AS servicio,
           u.nombres AS doctor_nombre, u.apellidos AS doctor_apellidos,
           c.precio_propuesto, c.aceptado_por_paciente
    FROM citas c
    JOIN usuarios u ON c.id_doctor = u.id
    JOIN servicios s ON c.id_servicio = s.id
    WHERE c.id_paciente = ?
      AND c.precio_propuesto IS NOT NULL
      AND (
           c.estado = 'pendiente'
        OR c.estado_pago = 'por_confirmar'
      )
    ORDER BY c.fecha DESC
");
$stmt->execute([$paciente_id]);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #e6f7ff; padding-top: 80px; font-family: 'Segoe UI', sans-serif;">

<div style="position: fixed; top: 0; width: 100%; background-color: #00aaff; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1000;">
    <h4 class="m-0">Notificaciones de Citas</h4>
    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-s">Volver</a>
    </div>
</div>

<div class="container" style="margin-top: 120px; max-width: 1000px;">
    <div class="card p-4 shadow-sm">
        <h5 class="text-primary mb-4 text-center">Citas con Precio Propuesto</h5>

        <?php if (empty($citas)): ?>
            <div class="alert alert-info text-center fw-semibold">No tienes notificaciones pendientes.</div>
        <?php else: ?>
            <table class="table table-hover table-bordered bg-white">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Servicio</th>
                        <th>Doctor</th>
                        <th>Fecha</th>
                        <th>Precio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['servicio']) ?></td>
                            <td><?= htmlspecialchars($c['doctor_nombre'] . ' ' . $c['doctor_apellidos']) ?></td>
                            <td><?= htmlspecialchars($c['fecha']) ?></td>
                            <td>S/. <?= number_format($c['precio_propuesto'], 2) ?></td>
                            <td class="text-center">
                                <?php
                                if ($c['aceptado_por_paciente'] == 0 || $c['aceptado_por_paciente'] === null) {
                                    echo '<a href="responder_precio.php?id=' . $c['cita_id'] . '&res=aceptar" class="btn btn-success btn-sm">Aceptar y Pagar</a> ';
                                    echo '<a href="responder_precio.php?id=' . $c['cita_id'] . '&res=rechazar" class="btn btn-danger btn-sm">Rechazar</a>';
                                } elseif ($c['estado_pago'] === 'pendiente') {
                                    $_SESSION['cita_pago'] = $c['cita_id'];
                                    $_SESSION['precio_pago'] = $c['precio_propuesto'];
                                    echo '<a href="../pagos/formulario_pago.php" class="btn btn-warning btn-sm">Completar Pago</a>';
                                } elseif ($c['estado_pago'] === 'por_confirmar') {
                                    echo '<span class="badge bg-warning text-dark">Esperando validación del doctor</span>';
                                } elseif ($c['estado_pago'] === 'pagado') {
                                    echo '<span class="badge bg-success">Pagado</span>';
                                } else {
                                    echo '<span class="text-muted">En proceso</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
