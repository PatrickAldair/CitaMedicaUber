<?php
session_start();
if (!isset($_SESSION['id'])) {
    exit("Sin sesión");
}

require_once '../db.php';

$lat = $_POST['latitud'];
$lon = $_POST['longitud'];
$id = $_SESSION['id'];

$stmt = $conn->prepare("UPDATE usuarios SET latitud = ?, longitud = ? WHERE id = ?");
$stmt->bind_param("ddi", $lat, $lon, $id);
$stmt->execute();
?>
