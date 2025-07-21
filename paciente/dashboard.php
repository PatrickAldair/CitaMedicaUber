<?php
session_start(); 
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'paciente') 
  header('Location: ../login.php');

require '../db.php';

$stmtCoords = $pdo->prepare("SELECT lat, lng FROM usuarios WHERE id = ?");
$stmtCoords->execute([$_SESSION['usuario']['id']]);
$coords = $stmtCoords->fetch();
$_SESSION['usuario']['lat'] = $coords['lat'];
$_SESSION['usuario']['lng'] = $coords['lng'];

$stmt = $pdo->query("
  SELECT u.id, u.nombres, u.apellidos, u.lat, u.lng, e.nombre AS especialidad
  FROM usuarios u
  LEFT JOIN especialidades e ON u.especialidad_id = e.id
  WHERE u.tipo = 'doctor'
");
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtPrecio = $pdo->prepare("
  SELECT c.id, c.fecha, c.precio_propuesto, s.nombre AS servicio, u.nombres, u.apellidos
  FROM citas c
  JOIN usuarios u ON c.id_doctor = u.id
  JOIN servicios s ON c.id_servicio = s.id
  WHERE c.id_paciente = ? 
    AND c.estado = 'pendiente'
    AND c.precio_propuesto IS NOT NULL 
    AND (c.aceptado_por_paciente IS NULL OR c.aceptado_por_paciente = 0)
");
$stmtPrecio->execute([$_SESSION['usuario']['id']]);
$citasPropuestas = $stmtPrecio->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Paciente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
    body {
        background: #e6f7ff;
        font-family: 'Segoe UI', sans-serif;
    }

    header {
        background-color: #00aaff;
        color: white;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    #menu {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    #map {
        border: 2px solid #cdeffd;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 120, 150, 0.1);
    }
    </style>
</head>

<body>

    <header class="d-flex justify-content-between align-items-center">
        <div style="display: flex; align-items: center;">
            <img src="../img/logo.jpg" alt="logo"
                style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;">
            <h2 class="m-0">Hola, <?= htmlspecialchars($_SESSION['usuario']['nombres']) ?></h2>
        </div>
        <div class="d-flex gap-2">
            <a href="mi_historial.php" class="btn btn-outline-light btn-s">Mi historial</a>
            <a href="notificaciones.php" class="btn btn-outline-light btn-s">Notificaciones</a>
            <a href="calificar_doctores.php" class="btn btn-outline-light btn-s">Calificar Doctores</a>
            <a href="../logout.php" class="btn btn-outline-light btn-s">Cerrar sesión</a>
        </div>
    </header>

    <div class="container">
        <div id="mensajeCita" class="alert alert-success text-center d-none" role="alert"></div>

        <div id="menu" class="mb-3">
            <label for="filtro" class="form-label fw-bold">Especialidad:</label>
            <select id="filtro" class="form-select">
                <option value="">Todas</option>
                <?php foreach (array_unique(array_column($docs, 'especialidad')) as $e): ?>
                <option value="<?= htmlspecialchars($e) ?>"><?= htmlspecialchars($e) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="map" data-lat="<?= $_SESSION['usuario']['lat'] ?>" data-lng="<?= $_SESSION['usuario']['lng'] ?>"
            data-doctores='<?= json_encode($docs) ?>' style="height: 80vh;"></div>
    </div>

    <?php if (!empty($citasPropuestas)): ?>
    <div class="container mt-4">
        <h4 class="text-primary">Citas con Precio Propuesto</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-secondary">
                <tr>
                    <th>Doctor</th>
                    <th>Servicio</th>
                    <th>Fecha</th>
                    <th>Precio</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citasPropuestas as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></td>
                    <td><?= htmlspecialchars($c['servicio']) ?></td>
                    <td><?= htmlspecialchars($c['fecha']) ?></td>
                    <td>S/. <?= number_format($c['precio_propuesto'], 2) ?></td>
                    <td>
                        <form action="respuesta_precio.php" method="POST" class="d-flex gap-1">
                            <input type="hidden" name="cita_id" value="<?= $c['id'] ?>">
                            <button name="respuesta" value="aceptar" class="btn btn-success btn-sm">Aceptar y
                                Pagar</button>
                            <button name="respuesta" value="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="modalCita" tabindex="-1" aria-labelledby="modalCitaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formCita" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCitaLabel">Agendar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label for="fechaHora" class="form-label">Selecciona fecha y hora:</label>
                    <input type="datetime-local" id="fechaHora" class="form-control mb-3" required>

                    <label for="servicio" class="form-label">Selecciona el servicio:</label>
                    <select id="servicio" class="form-select" required>
                        <option value="">Cargando servicios...</option>
                    </select>

                    <input type="hidden" id="doctorId">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js?v=<?= time() ?>"></script>

</body>

</html>