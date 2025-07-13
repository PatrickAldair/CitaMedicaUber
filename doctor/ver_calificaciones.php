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
    }
  </style>
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2>Mis Calificaciones</h2>

    <div class="alert alert-info">
      ⭐ <strong>Promedio:</strong> <?= $promedio ?> / 5
    </div>

    <?php if (empty($calificaciones)): ?>
      <div class="alert alert-warning">Aún no has recibido calificaciones.</div>
    <?php else: ?>
      <table class="table table-bordered">
        <thead class="table-secondary">
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
              <td>
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
</body>
</html>
