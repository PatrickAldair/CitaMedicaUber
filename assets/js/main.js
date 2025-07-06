document.addEventListener("DOMContentLoaded",() => {
  if (document.getElementById("map")) initMap();
});

let map, userMarker;

function initMap() {
  const lat0 = parseFloat(document.getElementById("map").dataset.lat);
  const lng0 = parseFloat(document.getElementById("map").dataset.lng);
  map = L.map("map").setView([lat0,lng0],13);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:"© OSM"}).addTo(map);
  userMarker = L.marker([lat0,lng0]).addTo(map).bindPopup("Tú").openPopup();

  const doctors = JSON.parse(document.getElementById("map").dataset.doctores);
  let markers = [];
  function renderDoctors(filter){
    markers.forEach(m=>map.removeLayer(m));
    markers = [];
    doctors.filter(d=>!filter||d.especialidad===filter)
      .forEach(d=>{
        const m = L.marker([d.lat,d.lng]).addTo(map);
        m.bindPopup(`<strong>Dr. ${d.nombres} ${d.apellidos}</strong><br>${d.especialidad}<br>
          <button onclick="solicitar(${d.id})">Pedir cita</button>`);
        markers.push(m);
      });
  }

  document.getElementById("filtro").onchange=()=>renderDoctors(document.getElementById("filtro").value);
  renderDoctors();

  window.solicitar = function(id){
    const fecha = prompt("Fecha y hora (YYYY-MM-DD HH:MM):");
    if (!fecha) return;
    fetch('cita.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id_doctor:id,fecha})})
      .then(r=>r.text()).then(alert);
  }
}
