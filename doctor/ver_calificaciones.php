<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

require '../db.php';

$idDoctor = $_SESSION['usuario']['id'];

$stmt = $pdo->prepare("
  SELECT c.estrellas, c.comentario, c.fecha, u.nombres, u.apellidos
  FROM calificaciones c
  JOIN usuarios u ON c.id_paciente = u.id
  WHERE c.id_doctor = ?
  ORDER BY c.fecha DESC
");
$stmt->execute([$idDoctor]);
$calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular promedio
$promedio = count($calificaciones)
    ? round(array_sum(array_column($calificaciones, 'estrellas')) / count($calificaciones), 2)
    : 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mis Calificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .estrella {
        color: gold;
        font-size: 1.2rem;
    }
    </style>
</head>

<body style="background-color: #e6f7ff; padding-top: 80px; font-family: 'Segoe UI', sans-serif;">
    <div
        style="position: fixed; top: 0; width: 100%; background-color: #00aaff; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1000;">
        <h4 style="margin: 0;">Mis Calificaciones</h4>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-s">Volver</a>
        </div>
    </div>
    <div class="container" style="margin-top: 120px; max-width: 800px;">
        <div class="card p-4 shadow-sm">
            <h5 class="text-primary mb-3">Calificaciones Recibidas</h5>

            <div class="alert alert-info fw-semibold">
                ⭐ <strong>Promedio:</strong> <?= $promedio ?> / 5
            </div>

            <?php if (empty($calificaciones)): ?>
            <div class="alert alert-warning text-center fw-semibold">Aún no has recibido calificaciones.</div>
            <?php else: ?>
            <table class="table table-hover table-bordered">
                <thead class="table-secondary text-center">
                    <tr>
                        <th>Paciente</th>
                        <th>Estrellas</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calificaciones as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></td>
                        <td class="text-center">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="estrella"><?= $i <= $c['estrellas'] ? '★' : '☆' ?></span>
                            <?php endfor; ?>
                        </td>
                        <td><?= htmlspecialchars($c['comentario']) ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($c['fecha'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>