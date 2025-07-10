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
    $especialidad_id = $_POST['especialidad_id'] ?? null;
    $servicios_stmt = $pdo->prepare("SELECT nombre FROM servicios WHERE especialidad_id = ?");
    $servicios_stmt->execute([$especialidad_id]);
    $servicios_array = $servicios_stmt->fetchAll(PDO::FETCH_COLUMN);
    $servicios = implode(', ', $servicios_array);
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $sexo = $_POST['sexo'] ?? null;
    $alergias = $_POST['alergias'] ?? null;
    $enfermedades_previas = $_POST['enfermedades_previas'] ?? null;
    $medicamentos = $_POST['medicamentos'] ?? null;
    $antecedentes_familiares = $_POST['antecedentes_familiares'] ?? null;
    $cirugias = $_POST['cirugias'] ?? null;
    $otros_datos = $_POST['otros_datos'] ?? null;

    $sql = "INSERT INTO usuarios (
        tipo, nombres, apellidos, email, password, edad, especialidad, servicios, 
        lat, lng, sexo, alergias, enfermedades_previas, medicamentos, 
        antecedentes_familiares, cirugias, otros_datos
    ) VALUES (
        :tipo, :nombres, :apellidos, :email, :password, :edad, :especialidad, :servicios, 
        :lat, :lng, :sexo, :alergias, :enfermedades_previas, :medicamentos, 
        :antecedentes_familiares, :cirugias, :otros_datos
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tipo' => $tipo,
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':email' => $email,
        ':password' => $password,
        ':edad' => $edad,
        ':especialidad' => $especialidad_id,
        ':servicios' => $servicios,
        ':lat' => $lat,
        ':lng' => $lng,
        ':sexo' => $sexo,
        ':alergias' => $alergias,
        ':enfermedades_previas' => $enfermedades_previas,
        ':medicamentos' => $medicamentos,
        ':antecedentes_familiares' => $antecedentes_familiares,
        ':cirugias' => $cirugias,
        ':otros_datos' => $otros_datos
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
        background: #7eb6f7;
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
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>
</head>

<body>

    <div class="card">
        <h3 class="text-center mb-4 text-primary">Registro</h3>

        <div id="notificacion" class="alert alert-info text-center d-none fade-in" role="alert"></div>

        <form method="POST" action="">

            <div class="mb-3">
                <label class="form-label">Tipo de usuario</label>
                <select name="tipo" id="tipo" class="form-select" onchange="toggleCampos()" required>
                    <option value="paciente">Paciente</option>
                    <option value="doctor">Doctor</option>
                </select>
            </div>

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

            <div id="camposDoctor" style="display: none;">
                <div class="mb-3">
                    <select name="especialidad_id" id="especialidad_id" class="form-select" onchange="cargarServicios()"
                        required>
                        <option value="">Seleccione especialidad</option>
                        <?php
                    $stmt = $pdo->query("SELECT id, nombre FROM especialidades");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                    }
                    ?>
                    </select>
                </div>
                <div class="mb-3">
                    <textarea name="servicios" id="servicios" class="form-control" readonly
                        placeholder="Servicios ofrecidos (autocompletado)"></textarea>
                </div>
            </div>


            <div id="camposPaciente" style="display: block;">

                <div class="mb-3">
                    <select name="sexo" class="form-select">
                        <option value="">Seleccione sexo</option>
                        <option value="femenino">Femenino</option>
                        <option value="masculino">Masculino</option>

                    </select>
                </div>


                <h3 class="text-center mb-4 text-primary">Antecedentes médicos </h3>

                <div class="mb-3">
                    <textarea name="alergias" class="form-control" placeholder="Alergias conocidas"></textarea>
                </div>
                <div class="mb-3">
                    <textarea name="enfermedades_previas" class="form-control"
                        placeholder="Enfermedades previas o crónicas"></textarea>
                </div>
                <div class="mb-3">
                    <textarea name="medicamentos" class="form-control"
                        placeholder="Medicamentos que toma actualmente"></textarea>
                </div>

                <div class="mb-3">
                    <textarea name="antecedentes_familiares" class="form-control"
                        placeholder="Antecedentes familiares (diabetes, hipertensión, etc.)"></textarea>
                </div>
                <div class="mb-3">
                    <textarea name="cirugias" class="form-control"
                        placeholder="Cirugías anteriores (si aplica)"></textarea>
                </div>
                <div class="mb-3">
                    <textarea name="otros_datos" class="form-control"
                        placeholder="Otros datos relevantes (hábitos, etc.)"></textarea>
                </div>
            </div>

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

    function cargarServicios() {
        const id = document.getElementById("especialidad_id").value;
        if (!id) return;

        fetch(`get_servicios.php?especialidad_id=${id}`)
            .then(res => res.json())
            .then(data => {
                const servicios = data.map(s => s.nombre).join(', ');
                document.getElementById("servicios").value = servicios;
            });
    }

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
            map.off();
            map.remove();
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
        marker = L.marker([lat, lng], {
            draggable: false
        }).addTo(map);
        marker.bindPopup(texto).openPopup();
        document.getElementById("lat").value = lat;
        document.getElementById("lng").value = lng;
    }

    function toggleCampos() {
        const tipo = document.getElementById("tipo").value;
        const campos = document.getElementById("camposDoctor");
        const camposPaciente = document.getElementById("camposPaciente");
        campos.style.display = tipo === "doctor" ? "block" : "none";
        camposPaciente.style.display = tipo === "doctor" ? "none" : "block";

        if (tipo === "paciente") {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    initMap(lat, lng);
                    setMarker(lat, lng, "Tu ubicación actual");
                }, function() {
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
                map.on('click', function(e) {
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

    window.onload = function() {
        toggleCampos();
        document.getElementById("tipo").addEventListener("change", toggleCampos);
    };
    </script>

</body>

</html>