document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("map")) initMap();

  const form = document.getElementById("formCita");
  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      const fecha = document.getElementById("fechaHora").value;
      if (!fecha || !doctorSeleccionadoId) return;

      const res = await fetch('cita.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_doctor: doctorSeleccionadoId, fecha })
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
        m.bindPopup(`<strong>Dr. ${d.nombres} ${d.apellidos}</strong><br>${d.especialidad}<br>
          <button class="btn btn-sm btn-primary mt-1" onclick="abrirModalCita(${d.id})">Pedir cita</button>`);
        markers.push(m);
      });
  }

  document.getElementById("filtro").onchange = () =>
    renderDoctors(document.getElementById("filtro").value);
  renderDoctors();

  if (navigator.geolocation) {
    navigator.geolocation.watchPosition(function(position) {
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

window.abrirModalCita = function (doctorId) {
  doctorSeleccionadoId = doctorId;
  document.getElementById("doctorId").value = doctorId;
  document.getElementById("fechaHora").value = "";
  const modal = new bootstrap.Modal(document.getElementById("modalCita"));
  modal.show();
};
