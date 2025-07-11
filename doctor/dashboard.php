<?php
session_start(); 
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'doctor') 
  header('Location: ../login.php');

require '../db.php';
$stmt = $pdo->prepare("
  SELECT c.id, c.fecha, c.id_paciente, u.nombres, u.apellidos 
  FROM citas c 
  JOIN usuarios u ON c.id_paciente = u.id 
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

    <div
        style="position: fixed; top: 0; width: 100%; background-color: #00aaff; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1000;">
        <h2 style="margin: 0;">Dr. <?= htmlspecialchars($_SESSION['usuario']['nombres']) ?></h2>
        <a href="../logout.php" class="btn btn-light btn-sm">Cerrar sesión</a>
    </div>

    <div class="container" style="max-width: 900px; margin-top: 30px;">
        <h3 style="margin-bottom: 20px; color: #007bff;">Citas Pendientes</h3>

        <?php if (empty($citas)): ?>
        <div class="alert alert-info text-center" style="font-weight: 500;">No tienes citas pendientes.</div>
        <?php else: ?>
        <table class="table table-bordered table-striped" style="background: white; border-radius: 8px;">
            <thead class="table-primary text-center">
                <tr>
                    <th>Paciente</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= htmlspecialchars("$c[nombres] $c[apellidos]") ?></td>
                    <td><?= htmlspecialchars($c['fecha']) ?></td>
                    <td class="text-center">
                        <a href="confirmar.php?id=<?= $c['id'] ?>&res=aceptada" class="btn btn-success btn-sm"
                            style="margin-right: 6px;">Aceptar</a>
                        <a href="confirmar.php?id=<?= $c['id'] ?>&res=rechazada"
                            class="btn btn-danger btn-sm">Rechazar</a>
                        <a href="historial_paciente.php?id=<?= $c['id_paciente'] ?>"
                            class="btn btn-outline-info btn-sm">
                            <i class="bi bi-folder2-open"></i> Historial
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