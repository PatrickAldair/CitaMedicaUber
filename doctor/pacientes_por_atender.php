<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

require '../db.php';

$stmt = $pdo->prepare("
  SELECT c.*, u.nombres, u.apellidos
  FROM citas c
  JOIN usuarios u ON c.id_paciente = u.id
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
<body class="bg-light">
<div class="container mt-5">
  <h2>Pacientes por Atender</h2>
  <?php if (empty($citas)): ?>
    <div class="alert alert-info">No tienes pacientes pendientes.</div>
  <?php else: ?>
    <table class="table table-bordered mt-3">
      <thead class="table-dark">
        <tr>
          <th>Paciente</th>
          <th>Fecha y Hora</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($citas as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['nombres'] . " " . $c['apellidos']) ?></td>
            <td><?= $c['fecha'] ?></td>
            <td>
              <form action="finalizar_cita.php" method="POST">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button class="btn btn-success btn-sm" 
                        <?= esPasada($c['fecha']) ? '' : 'disabled' ?>>Finalizar Cita</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>
