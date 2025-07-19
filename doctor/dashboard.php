<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') {
  header('Location: ../login.php');
  exit;
}
require '../db.php';

$stmt = $pdo->prepare("
  SELECT c.id, c.fecha, c.id_paciente, u.nombres, u.apellidos,
         c.precio_propuesto, c.aceptado_por_paciente, c.estado_pago,
         s.nombre AS servicio
  FROM citas c
  JOIN usuarios u ON c.id_paciente = u.id
  JOIN servicios s ON c.id_servicio = s.id
  WHERE c.id_doctor = ? AND c.estado = 'pendiente'
");
$stmt->execute([$_SESSION['usuario']['id']]);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Doctor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #e6f7ff; padding-top: 80px; font-family: 'Segoe UI', sans-serif;">

<div style="position: fixed; top:0; width:100%; background:#00aaff; color:white; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; z-index:1000;">
  <h2 style="margin:0;">Dr. <?=htmlspecialchars($_SESSION['usuario']['nombres'])?></h2>
  <div class="d-flex gap-2">
    <a href="pacientes_por_atender.php" class="btn btn-outline-light btn-s">📋 Pacientes por Atender</a>
    <a href="ver_calificaciones.php" class="btn btn-outline-light btn-s">⭐ Ver Calificaciones</a>
    <a href="validar_pagos.php" class="btn btn-outline-light btn-s">💰 Validar Pagos Yape</a>
    <a href="../logout.php" class="btn btn-outline-light btn-s">Cerrar sesión</a>
  </div>
</div>

<div class="container" style="max-width:1000px; margin-top:30px;">
  <h3 class="text-primary mb-4">Citas Pendientes</h3>

  <?php if (empty($citas)): ?>
    <div class="alert alert-info text-center">No tienes citas pendientes.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped bg-white rounded shadow-sm">
      <thead class="table-primary text-center">
        <tr>
          <th>Paciente</th>
          <th>Servicio</th>
          <th>Fecha</th>
          <th>Estado de Precio</th>
          <th>Historial</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($citas as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></td>
          <td><?= htmlspecialchars($c['servicio']) ?></td>
          <td><?= htmlspecialchars($c['fecha']) ?></td>
          <td class="text-center">
            <?php if ($c['precio_propuesto'] === null): ?>
              <form action="proponer_precio.php" method="POST" class="d-flex justify-content-center gap-2">
                <input type="hidden" name="cita_id" value="<?= $c['id'] ?>">
                <input type="number" name="precio" class="form-control form-control-sm" style="width:100px;" min="1" step="0.10" placeholder="S/." required>
                <button type="submit" class="btn btn-sm btn-success">Enviar</button>
              </form>
            <?php else: ?>
              <?php
              if ($c['aceptado_por_paciente'] == 1) {
                  if ($c['estado_pago'] === 'pagado') {
                      echo '<span class="badge bg-success">Pagado</span>';
                  } elseif ($c['estado_pago'] === 'por_confirmar') {
                      echo '<span class="badge bg-warning text-dark">Pago en validación</span>';
                  } elseif ($c['estado_pago'] === 'pendiente') {
                      echo '<span class="badge bg-info text-dark">Aceptado – pago pendiente</span>';
                  } else {
                      echo '<span class="badge bg-secondary">Aceptado</span>';
                  }
              } elseif ($c['aceptado_por_paciente'] == 2) {
                  echo '<span class="badge bg-danger">Rechazado</span>';
              } else {
                  echo '<span class="badge bg-warning text-dark">Esperando respuesta paciente</span>';
              }
              ?>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <a href="historial_paciente.php?id=<?= $c['id_paciente'] ?>" class="btn btn-outline-info btn-sm">
              📁 Historial
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>
