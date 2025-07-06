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
  <style>
    body {
      background: #e6f2ff;
      min-height: 100vh;
      padding-top: 3rem;
    }
    .container {
      background: white;
      padding: 2rem;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="text-center text-primary mb-4">Estado de tus citas</h2>
    <div class="text-end mb-3">
      <a href="dashboard.php" class="btn btn-outline-secondary">Volver</a>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-primary">
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
  </div>
</body>
</html>
