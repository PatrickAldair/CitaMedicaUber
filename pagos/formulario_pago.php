<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'paciente') {
    header("Location: ../login.php");
    exit();
}

$precio = $_SESSION['precio_pago'] ?? null;
$cita_id = $_SESSION['cita_pago'] ?? null;

if (!$precio || !$cita_id) {
    die("Acceso inválido.");
}

$stmt = $pdo->prepare("SELECT id_doctor FROM citas WHERE id = ?");
$stmt->execute([$cita_id]);
$id_doctor = $stmt->fetchColumn();

if (!$id_doctor) {
    die("No se pudo obtener el doctor para esta cita.");
}

$stmt = $pdo->prepare("SELECT telefono_yape FROM usuarios WHERE id = ?");
$stmt->execute([$id_doctor]);
$doctor = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagar Cita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #e6f7ff; padding-top: 80px; font-family: 'Segoe UI', sans-serif;">

<div style="position: fixed; top: 0; width: 100%; background-color: #00aaff; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1000;">
    <h4 style="margin: 0;">Pago de Cita</h4>
    <div>
        <a href="../paciente/dashboard.php" class="btn btn-outline-light btn-sm">Volver</a>
    </div>
</div>

<div class="container" style="max-width: 600px; margin-top: 120px;">
    <div class="card p-4 shadow-sm">
        <h5 class="text-center mb-4 text-primary">Total a pagar: <strong>S/. <?= number_format($precio, 2) ?></strong></h5>

        <form action="procesar_pago.php" method="POST">
            <input type="hidden" name="cita_id" value="<?= $cita_id ?>">
            <input type="hidden" name="monto" value="<?= $precio ?>">

            <div class="mb-3">
                <label class="form-label fw-bold">Método de Pago</label>
                <select name="metodo" id="metodo" class="form-select" required onchange="mostrarCampos()">
                    <option value="">-- Selecciona --</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="yape">Yape</option>
                    <option value="efectivo">Efectivo</option>
                </select>
            </div>

            <div id="campos_tarjeta" class="mb-3" style="display: none;">
                <label class="form-label">Número de Tarjeta</label>
                <input type="text" name="tarjeta" class="form-control" placeholder="XXXX-XXXX-XXXX-XXXX">
            </div>

            <div id="campos_yape" style="display: none;">
                <div class="alert alert-warning text-center">
                    Envía el monto al número Yape del doctor:<br>
                    <strong>
                        <?= htmlspecialchars($doctor['telefono_yape'] ?? 'No disponible') ?>
                    </strong>
                </div>
                <div class="mb-3">
                    <label class="form-label">¿Ya realizaste el pago por Yape?</label>
                    <select name="confirmo_yape" class="form-select" required>
                        <option value="">-- Selecciona --</option>
                        <option value="si">Sí, ya pagué</option>
                        <option value="no">Aún no</option>
                    </select>
                </div>
            </div>

            <div id="campos_efectivo" style="display: none;" class="mb-3">
                <div class="alert alert-info text-center">
                    El pago se realizará en persona al momento de la cita.
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>

<script>
function mostrarCampos() {
    const metodo = document.getElementById('metodo').value;
    document.getElementById('campos_tarjeta').style.display = (metodo === 'tarjeta') ? 'block' : 'none';
    document.getElementById('campos_yape').style.display = (metodo === 'yape') ? 'block' : 'none';
    document.getElementById('campos_efectivo').style.display = (metodo === 'efectivo') ? 'block' : 'none';
}
</script>

</body>
</html>
