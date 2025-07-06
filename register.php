<?php
require_once "db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $edad = $_POST['edad'];
    $especialidad = $_POST['especialidad'] ?? null;
    $servicios = $_POST['servicios'] ?? null;
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $sql = "INSERT INTO usuarios (tipo, nombres, apellidos, email, password, edad, especialidad, servicios, lat, lng)
            VALUES (:tipo, :nombres, :apellidos, :email, :password, :edad, :especialidad, :servicios, :lat, :lng)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tipo' => $tipo,
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':email' => $email,
        ':password' => $password,
        ':edad' => $edad,
        ':especialidad' => $especialidad,
        ':servicios' => $servicios,
        ':lat' => $lat,
        ':lng' => $lng
    ]);

    $_SESSION['exito'] = "Registro exitoso. Ahora puedes iniciar sesión.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background:#7eb6f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            width: 100%;
            max-width: 600px;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            background: white;
        }
        #map {
            height: 250px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="card">
    <h3 class="text-center mb-4 text-primary">Registro</h3>

    <!-- Notificación visual -->
    <div id="notificacion" class="alert alert-info text-center d-none fade-in" role="alert"></div>

    <form method="POST" action="">
        <div class="mb-3">
            <input type="text" name="nombres" class="form-control" placeholder="Nombres" required>
        </div>
        <div class="mb-3">
            <input type="text" name="apellidos" class="form-control" placeholder="Apellidos" required>
        </div>
        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
        </div>
        <div class="mb-3">
            <input type="number" name="edad" class="form-control" placeholder="Edad" required>
        </div>

        <!-- Campos del doctor ocultos inicialmente -->
        <div id="camposDoctor" style="display: none;">
            <div class="mb-3">
                <input type="text" name="especialidad" class="form-control" placeholder="Especialidad">
            </div>
            <div class="mb-3">
                <textarea name="servicios" class="form-control" placeholder="Servicios ofrecidos"></textarea>
            </div>
        </div>

        <!-- Selección de tipo al final -->
        <div class="mb-3">
            <label class="form-label">Tipo de usuario</label>
            <select name="tipo" id="tipo" class="form-select" onchange="toggleCampos()" required>
                <option value="paciente">Paciente</option>
                <option value="doctor">Doctor</option>
            </select>
        </div>

        <!-- Mapa -->
        <div id="map"></div>
        <input type="hidden" name="lat" id="lat">
        <input type="hidden" name="lng" id="lng">

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </div>
    </form>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let marker;
let yaMostroAlerta = false;

function mostrarNotificacion(texto) {
    const div = document.getElementById("notificacion");
    div.textContent = texto;
    div.classList.remove("d-none");
    div.classList.add("show", "fade-in");

    setTimeout(() => {
        div.classList.add("d-none");
        div.classList.remove("fade-in");
    }, 4000);
}

function initMap(lat = -12.0464, lng = -77.0428, zoom = 15) {
    if (map) {
        map.off(); map.remove();
    }
    map = L.map('map').setView([lat, lng], zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
}

function setMarker(lat, lng, texto = "Ubicación seleccionada") {
    if (marker) {
        map.removeLayer(marker);
    }
    marker = L.marker([lat, lng], { draggable: false }).addTo(map);
    marker.bindPopup(texto).openPopup();
    document.getElementById("lat").value = lat;
    document.getElementById("lng").value = lng;
}

function toggleCampos() {
    const tipo = document.getElementById("tipo").value;
    const campos = document.getElementById("camposDoctor");
    campos.style.display = tipo === "doctor" ? "block" : "none";

    if (tipo === "paciente") {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                initMap(lat, lng);
                setMarker(lat, lng, "Tu ubicación actual");
            }, function () {
                mostrarNotificacion("No se pudo obtener la ubicación.");
                initMap();
            });
        } else {
            mostrarNotificacion("Tu navegador no soporta geolocalización.");
            initMap();
        }
    }

    if (tipo === "doctor") {
        initMap();
        map.whenReady(() => {
            map.on('click', function (e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                setMarker(lat, lng, "Ubicación del consultorio");
            });

            if (!yaMostroAlerta) {
                mostrarNotificacion("Haz clic en el mapa para marcar la ubicación de tu consultorio.");
                yaMostroAlerta = true;
            }
        });
    }
}

window.onload = function () {
    toggleCampos();
    document.getElementById("tipo").addEventListener("change", toggleCampos);
};
</script>

</body>
</html>

