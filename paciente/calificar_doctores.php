<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'paciente') {
    header('Location: ../login.php');
    exit;
}
require '../db.php';

$stmt = $pdo->prepare("
  SELECT c.id AS cita_id, c.fecha, c.finalizada, u.nombres, u.apellidos, u.id AS id_doctor
  FROM citas c
  JOIN usuarios u ON c.id_doctor = u.id
  WHERE c.id_paciente = ?
    AND c.estado = 'aceptada'
    AND c.finalizada = 1
    AND NOT EXISTS (
      SELECT 1 FROM calificaciones cal
      WHERE cal.id_cita = c.id
    )
");
$stmt->execute([$_SESSION['usuario']['id']]);

$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Calificar Doctores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2>Calificar Doctores</h2>
        <?php if (empty($citas)): ?>
        <div class="alert alert-info">No hay doctores que calificar en este momento</div>
        <?php else: ?>
        <table class="table table-bordered mt-3">
            <thead class="table-primary">
                <tr>
                    <th>Doctor</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $c): ?>
                <tr>
                    <td>Dr. <?= htmlspecialchars($c['nombres'] . " " . $c['apellidos']) ?></td>
                    <td><?= $c['fecha'] ?></td>
                    <td>
                        <?php if ($c['finalizada']): ?>
                        <form action="guardar_calificacion.php" method="POST">
                            <input type="hidden" name="id_cita" value="<?= $c['cita_id'] ?>">
                            <input type="hidden" name="id_doctor" value="<?= $c['id_doctor'] ?>">
                            <select name="estrellas" class="form-select d-inline w-auto">
                                <?php for ($i=1; $i<=5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                                <?php endfor; ?>
                            </select>
                            <input type="text" name="comentario" placeholder="Comentario"
                                class="form-control d-inline w-50">
                            <button class="btn btn-primary btn-sm">Enviar</button>
                        </form>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-sm" disabled>En espera de finalización</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</body>

</html>