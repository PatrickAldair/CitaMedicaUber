<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'paciente') {
  header('Location: ../login.php');
  exit;
}

require '../db.php';

$stmt = $pdo->prepare("SELECT c.fecha, c.estado, u.nombres, u.apellidos FROM citas c JOIN usuarios u ON c.id_doctor = u.id WHERE c.id_paciente = ?");
$stmt->execute([$_SESSION['usuario']['id']]);
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
    <div
        style="position: fixed; top: 0; width: 100%; background-color: #00aaff; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1000;">
        <h4 style="margin: 0;">Notificaciones de Citas</h4>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-s">Volver</a>
        </div>
    </div>
    <div class="container" style="margin-top: 120px; max-width: 800px;">
        <div class="card p-4 shadow-sm">
            <h5 class="text-primary mb-4 text-center">Estado de tus Citas</h5>

            <?php if (empty($citas)): ?>
            <div class="alert alert-info text-center fw-semibold">No tienes citas registradas.</div>
            <?php else: ?>
            <table class="table table-hover table-bordered">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Doctor</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars("$c[nombres] $c[apellidos]") ?></td>
                        <td><?= htmlspecialchars($c['fecha']) ?></td>
                        <td><?= htmlspecialchars($c['estado']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>