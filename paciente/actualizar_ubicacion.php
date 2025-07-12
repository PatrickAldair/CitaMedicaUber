<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario'])) {
    $lat = $_POST['latitud'] ?? null;
    $lng = $_POST['longitud'] ?? null;
    $id = $_SESSION['usuario']['id'];

    if ($lat && $lng) {
        $stmt = $pdo->prepare("UPDATE usuarios SET lat = ?, lng = ? WHERE id = ?");
        $stmt->execute([$lat, $lng, $id]);

        // 🧠 Muy importante: también actualizamos la sesión para que el mapa lo refleje al recargar
        $_SESSION['usuario']['lat'] = $lat;
        $_SESSION['usuario']['lng'] = $lng;
    }
}
?>
