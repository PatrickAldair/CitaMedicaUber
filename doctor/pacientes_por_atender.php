<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

require '../db.php';

$stmt = $pdo->prepare("
  SELECT c.*, u.nombres, u.apellidos, s.nombre AS servicio
FROM citas c
JOIN usuarios u ON c.id_paciente = u.id
LEFT JOIN servicios s ON c.id_servicio = s.id
WHERE c.id_doctor = ? AND c.estado = 'aceptada' AND c.finalizada = 0
");
$stmt->execute([$_SESSION['usuario']['id']]);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esPasada($fecha) {
    return strtotime($fecha) < time();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pacientes por Atender</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #e6f7ff; padding-top: 80px; font-family: 'Segoe UI', sans-serif;">
    <div
        style="position: fixed; top: 0; width: 100%; background-color: #00aaff; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1000;">
        <div style="display: flex; align-items: center;">
            <img src="../img/logo.jpg" alt="logo"
                style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;">
            <h4 style="margin: 0;">Pacientes por Atender</h4>
        </div>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-s">Volver</a>
        </div>
    </div>
    <div class="container" style="margin-top: 120px; max-width: 800px;">
        <div class="card p-4 shadow-sm">
            <h5 class="text-primary mb-4">Lista de Pacientes Pendientes</h5>

            <?php if (empty($citas)): ?>
            <div class="alert alert-info text-center fw-semibold">No tienes pacientes pendientes.</div>
            <?php else: ?>
            <table class="table table-hover table-bordered">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Paciente</th>
                        <th>Fecha y Hora</th>
                        <th>Servicio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombres'] . " " . $c['apellidos']) ?></td>
                        <td><?= $c['fecha'] ?></td>
                        <td><?= htmlspecialchars($c['servicio'] ?? 'Sin especificar') ?></td>
                        <td class="text-center">
                            <form action="finalizar_cita.php" method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button class="btn btn-success btn-sm" <?= esPasada($c['fecha']) ? '' : 'disabled' ?>>
                                    Finalizar
                                </button>
                            </form>
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