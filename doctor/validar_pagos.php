<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$doctor_id = $_SESSION['usuario']['id'];

$stmt = $pdo->prepare("
    SELECT p.id, p.id_cita, p.monto, c.fecha, c.id_paciente,
           u.nombres AS paciente_nombre, u.apellidos AS paciente_apellidos
    FROM pagos p
    JOIN citas c ON p.id_cita = c.id
    JOIN usuarios u ON c.id_paciente = u.id
    WHERE p.metodo = 'yape'
      AND p.estado = 'por_confirmar'
      AND c.id_doctor = ?
    ORDER BY c.fecha DESC
");
$stmt->execute([$doctor_id]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validar Pagos Yape</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #e6f7ff; padding-top: 80px; font-family: 'Segoe UI', sans-serif;">

<div style="position: fixed; top: 0; width: 100%; background-color: #00aaff; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1000;">
    <h4 style="margin: 0;">Validar Pagos por Yape</h4>
    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-s">Volver</a>
    </div>
</div>

<div class="container" style="margin-top: 120px; max-width: 900px;">
    <div class="card p-4 shadow-sm">
        <h5 class="text-primary mb-4 text-center">Pagos Pendientes de Confirmación</h5>

        <?php if (isset($_SESSION['exito'])): ?>
            <div class="alert alert-success text-center"><?= $_SESSION['exito'] ?></div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>

        <?php if (empty($pagos)): ?>
            <div class="alert alert-info text-center fw-semibold">No hay pagos por confirmar.</div>
        <?php else: ?>
            <table class="table table-hover table-bordered bg-white">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Paciente</th>
                        <th>Fecha Cita</th>
                        <th>Monto</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['paciente_nombre'] . ' ' . $p['paciente_apellidos']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></td>
                        <td>S/. <?= number_format($p['monto'], 2) ?></td>
                        <td class="text-center">
                            <a href="valida_yape.php?id=<?= $p['id'] ?>&accion=aceptar" class="btn btn-sm btn-success me-2">Confirmar</a>
                            <a href="valida_yape.php?id=<?= $p['id'] ?>&accion=rechazar" class="btn btn-sm btn-danger">Rechazar</a>
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
