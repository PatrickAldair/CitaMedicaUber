<?php
require_once("../db.php");
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$id_paciente = $_GET['id'] ?? null;
if (!$id_paciente) die("ID de paciente no válido.");

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND tipo = 'paciente'");
$stmt->execute([$id_paciente]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) die("Paciente no encontrado.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de <?= htmlspecialchars($paciente['nombres']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #e6f7ff;
      padding-top: 80px;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      padding: 2rem;
    }
  </style>
</head>
<body>

<div style="position: fixed; top: 0; width: 100%; background-color: #00aaff; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1000;">
  <h4 style="margin: 0;">Historial de <?= htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos']) ?></h4>
  <a href="dashboard.php" class="btn btn-light btn-sm">Volver</a>
</div>

<div class="container mt-5">
  <div class="card">
    <h5 class="text-primary mb-3">Datos Personales</h5>
    <p><strong>Edad:</strong> <?= $paciente['edad'] ?></p>
    <p><strong>Sexo:</strong> <?= $paciente['sexo'] ?></p>

    <hr>
    <h5 class="text-primary mb-3">Historial Médico</h5>
    <p><strong>Alergias:</strong> <?= nl2br($paciente['alergias']) ?></p>
    <p><strong>Enfermedades Previas:</strong> <?= nl2br($paciente['enfermedades_previas']) ?></p>
    <p><strong>Medicamentos:</strong> <?= nl2br($paciente['medicamentos']) ?></p>
    <p><strong>Antecedentes Familiares:</strong> <?= nl2br($paciente['antecedentes_familiares']) ?></p>
    <p><strong>Cirugías:</strong> <?= nl2br($paciente['cirugias']) ?></p>
    <p><strong>Otros Datos:</strong> <?= nl2br($paciente['otros_datos']) ?></p>

    <hr>
    <h5 class="text-primary mb-3">Servicios Recibidos</h5>
    <ul class="list-group">
    <?php
      $sql_historial = "SELECT c.fecha, s.nombre AS servicio
                        FROM citas c
                        JOIN usuarios d ON d.id = c.id_doctor
                        JOIN especialidades e ON d.especialidad_id = e.id
                        JOIN servicios s ON s.especialidad_id = e.id
                        WHERE c.id_paciente = ? AND c.estado = 'aceptada'
                        ORDER BY c.fecha DESC";
      $stmt2 = $pdo->prepare($sql_historial);
      $stmt2->execute([$id_paciente]);
      $historial = $stmt2->fetchAll(PDO::FETCH_ASSOC);

      if (count($historial)) {
          foreach ($historial as $item) {
              echo "<li class='list-group-item'><strong>{$item['fecha']}</strong>: {$item['servicio']}</li>";
          }
      } else {
          echo "<li class='list-group-item text-muted'>Sin registros de atención aún.</li>";
      }
    ?>
    </ul>
  </div>
</div>

</body>
</html>
