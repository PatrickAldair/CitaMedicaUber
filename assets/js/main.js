document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("map")) initMap();

  const form = document.getElementById("formCita");
  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const fecha = document.getElementById("fechaHora").value;
      const servicioId = document.getElementById("servicio").value;
      if (!fecha || !doctorSeleccionadoId || !servicioId) return;

      const res = await fetch('cita.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id_doctor: doctorSeleccionadoId,
          fecha,
          id_servicio: servicioId
        })
      });

      const text = await res.text();

      const mensaje = document.getElementById("mensajeCita");
      mensaje.textContent = text;
      mensaje.classList.remove("d-none");

      bootstrap.Modal.getInstance(document.getElementById("modalCita")).hide();
      form.reset();

      setTimeout(() => {
        mensaje.classList.add("d-none");
      }, 4000);
    });
  }

  // Escucha clics en botones "Pedir cita" (incluso los generados dinámicamente)
  document.addEventListener("click", function (e) {
    if (e.target && e.target.classList.contains("pedir-cita-btn")) {
      const id = e.target.getAttribute("data-id");
      if (id) abrirModalCita(parseInt(id));
    }
  });
});

let map, userMarker;
let doctorSeleccionadoId = null;

function initMap() {
  const lat0 = parseFloat(document.getElementById("map").dataset.lat);
  const lng0 = parseFloat(document.getElementById("map").dataset.lng);
  map = L.map("map").setView([lat0, lng0], 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OSM"
  }).addTo(map);

  userMarker = L.marker([lat0, lng0]).addTo(map).bindPopup("Tú").openPopup();

  const doctors = JSON.parse(document.getElementById("map").dataset.doctores);
  let markers = [];

  function renderDoctors(filter) {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    doctors.filter(d => !filter || d.especialidad === filter)
      .forEach(d => {
        const m = L.marker([d.lat, d.lng]).addTo(map);
        m.bindPopup(`
          <strong>Dr. ${d.nombres} ${d.apellidos}</strong><br>${d.especialidad}<br>
          <button class="btn btn-sm btn-primary mt-1 pedir-cita-btn" data-id="${d.id}">Pedir cita</button>
        `);
        markers.push(m);
      });
  }

  document.getElementById("filtro").onchange = () =>
    renderDoctors(document.getElementById("filtro").value);
  renderDoctors();

  if (navigator.geolocation) {
    navigator.geolocation.watchPosition(function (position) {
      const newLat = position.coords.latitude;
      const newLng = position.coords.longitude;

      if (userMarker) {
        userMarker.setLatLng([newLat, newLng]).bindPopup("Tú").openPopup();
        map.setView([newLat, newLng]);
      }

      fetch("actualizar_ubicacion.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `latitud=${newLat}&longitud=${newLng}`
      });
    });
  }
}

async function abrirModalCita(doctorId) {
  console.log("abrirModalCita llamada con id:", doctorId);

  doctorSeleccionadoId = doctorId;
  document.getElementById("doctorId").value = doctorId;
  document.getElementById("fechaHora").value = "";

  const servicioSelect = document.getElementById("servicio");
  servicioSelect.innerHTML = '<option>Cargando servicios...</option>';

  try {
    const res = await fetch(`servicio.php?id=${doctorId}`);
    const servicios = await res.json();
    console.log("Servicios recibidos:", servicios);

    if (!servicios.length) {
      servicioSelect.innerHTML = '<option disabled>No hay servicios disponibles</option>';
    } else {
      servicioSelect.innerHTML = '';
      servicios.forEach(serv => {
        const opt = document.createElement('option');
        opt.value = serv.id;
        opt.textContent = serv.nombre;
        servicioSelect.appendChild(opt);
      });
    }
  } catch (err) {
    console.error("Error al cargar servicios", err);
    servicioSelect.innerHTML = '<option disabled>Error al cargar servicios</option>';
  }

  const modal = new bootstrap.Modal(document.getElementById("modalCita"));
  modal.show();
}
