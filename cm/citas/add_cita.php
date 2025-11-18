<div class="modal fade" id="addCitaModal" tabindex="-1" aria-labelledby="addCitaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addCitaForm" method="post" action="citas/save_cita.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addCitaModalLabel">Agregar Cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="categoriaSelect" class="form-label">Categoría</label>
            <select id="categoriaSelect" class="form-select" required>
              <option value="" disabled selected>Seleccione categoría</option>
              <?php
              foreach ($pdo->query("SELECT DISTINCT categoria FROM ProdSer WHERE activo = 1")->fetchAll(PDO::FETCH_COLUMN) as $cat) {
                if ($cat === 'PRODUCTOS')
                  continue;
                echo '<option value="' . htmlspecialchars($cat) . '">' . htmlspecialchars($cat) . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="nombreSelect" class="form-label">Nombre de Servicio</label>
            <select id="nombreSelect" name="servicio_id" class="form-select" disabled required>
              <option value="" disabled selected>Seleccione primero categoría</option>
            </select>
          </div>

          <div class="mb-3 d-none" id="durationContainer">
            <label for="durationSelect" class="form-label">Duración (minutos)</label>
            <select id="durationSelect" name="duracion" class="form-select" disabled>
              <option value="" disabled selected>Seleccione duración</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="fechaInput" class="form-label">Fecha</label>
            <input type="text" id="fechaInput" name="fecha" class="form-control" placeholder="Selecciona fecha"
              autocomplete="off" disabled required>
          </div>

          <div class="mb-3 d-none" id="timeSelectContainer">
            <label for="horaSelect" class="form-label">Hora</label>
            <select id="horaSelect" name="hora_inicio" class="form-select" disabled required>
              <option value="" disabled selected>Seleccione hora</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="nombreInput" class="form-label">Nombre</label>
            <input type="text" id="nombreInput" name="nombre" class="form-control" placeholder="Nombre del cliente"
              disabled required>
          </div>

          <div class="mb-3">
            <label for="telefonoInput" class="form-label">Teléfono</label>
            <input type="tel" id="telefonoInput" name="telefono" class="form-control" placeholder="Teléfono de contacto"
              disabled required>
          </div>

          <div class="mb-3">
            <label for="notasInput" class="form-label">Notas</label>
            <textarea id="notasInput" name="notas" class="form-control" rows="3" placeholder="Observaciones (opcional)"
              disabled></textarea>
          </div>

          <input type="hidden" name="estado" value="2">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Cita</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const services = <?php echo json_encode($pdo->query("SELECT id, nombre, categoria FROM ProdSer")->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE); ?>;
    const catSelect = document.getElementById('categoriaSelect');
    const nameSelect = document.getElementById('nombreSelect');
    const durationContainer = document.getElementById('durationContainer');
    const durationSelect = document.getElementById('durationSelect');
    const fechaInput = document.getElementById('fechaInput');
    const timeContainer = document.getElementById('timeSelectContainer');
    const horaSelect = document.getElementById('horaSelect');
    const nombreInput = document.getElementById('nombreInput');
    const telefonoInput = document.getElementById('telefonoInput');
    const notasInput = document.getElementById('notasInput');
    const form = document.getElementById('addCitaForm');
    const empleados = <?= json_encode($empleados, JSON_UNESCAPED_UNICODE) ?>;

    nameSelect.disabled = true;
    durationContainer.classList.add('d-none');
    durationSelect.disabled = true;
    fechaInput.disabled = true;
    timeContainer.classList.add('d-none');
    horaSelect.disabled = true;
    nombreInput.disabled = true;
    telefonoInput.disabled = true;
    notasInput.disabled = true;

    catSelect.addEventListener('change', function () {
      nameSelect.innerHTML = '<option value="" disabled selected>Seleccione nombre</option>';
      services.filter(s => s.categoria === this.value)
        .forEach(s => nameSelect.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.nombre}</option>`));
      nameSelect.disabled = false;

      const cat = this.value.toLowerCase();
      let options = [];
      if (cat.includes('masaje') || cat.includes('facial')) options = [30, 45, 60, 90];
      else if (cat.includes('otros')) options = [30, 45, 60, 90, 120, 150, 180];

      if (options.length) {
        durationSelect.innerHTML = '<option value="" disabled selected>Seleccione duración</option>' +
          options.map(d => `<option value="${d}">${d}</option>`).join('');
        durationContainer.classList.remove('d-none');
        durationSelect.disabled = false;
        durationSelect.required = true;
      } else {
        durationContainer.classList.add('d-none');
        durationSelect.disabled = true;
        durationSelect.required = false;
        durationSelect.innerHTML = '<option value="" disabled selected>Seleccione duración</option>';
      }

      fechaInput.disabled = true;
      timeContainer.classList.add('d-none');
      horaSelect.disabled = true;
      nombreInput.disabled = true;
      telefonoInput.disabled = true;
      notasInput.disabled = true;
    });

    nameSelect.addEventListener('change', function () {
      fechaInput.disabled = false;
      nombreInput.disabled = false;
      telefonoInput.disabled = false;
      notasInput.disabled = false;
    });

    flatpickr(fechaInput, {
      locale: 'es',
      dateFormat: 'Y-m-d',
      minDate: 'today',
      disableMobile: true,
      clickOpens: true,
      appendTo: document.body,
      onChange: function (_, dateStr) {
        if (!dateStr) {
          timeContainer.classList.add('d-none');
          return;
        }
        const today = new Date();
        const selected = new Date(dateStr + 'T00:00:00');
        const isToday = selected.toDateString() === today.toDateString();
        const currentH = today.getHours();
        const currentM = today.getMinutes();

        const pad = n => String(n).padStart(2, '0');
        let html = '<option value="" disabled selected>Seleccione hora</option>';
        
        for (let h = 10; h < 19; h++) {
          for (let m = 0; m < 60; m += 15) {
            if (isToday && (h < currentH || (h === currentH && m <= currentM))) {
              continue;
            }
            const slot = `${pad(h)}:${pad(m)}`;
            const display = `${h % 12 || 12}:${pad(m)} ${h < 12 ? 'am' : 'pm'}`;
            html += `<option value="${slot}">${display}</option>`;
          }
        }
        
        horaSelect.innerHTML = html;
        horaSelect.disabled = false;
        timeContainer.classList.remove('d-none');
      }
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = form.querySelector('button[type="submit"]');
      btn.disabled = true;

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
      })
        .then(response => response.text())
        .then(text => {
          let json;
          try {
            json = JSON.parse(text);
          } catch {
            console.error('Invalid JSON:', text);
            btn.disabled = false;
            return alert('Error de servidor: respuesta no válida');
          }
          if (!json.success) {
            btn.disabled = false;
            return alert('Error: ' + json.message);
          }

          const addModalEl = document.getElementById('addCitaModal');
          if (addModalEl) bootstrap.Modal.getInstance(addModalEl).hide();

          const r = json.data;
          const [y, m, d] = r.fecha.split('-');
          const fechaFmt = `${d}-${m}`;
          const hIni = r.hora_inicio.slice(0, 5);
          const hFin = r.hora_fin.slice(0, 5);
          const svc = services.find(s => s.id == r.servicio_id);
          const servicioNombre = svc ? svc.nombre : r.servicio_id;
          let txt, cls;
          if (r.estado == 2) { txt = 'PENDIENTE'; cls = 'status-pendiente'; }
          else if (r.estado == 1) { txt = 'CONFIRMADA'; cls = 'status-confirmada'; }
          else { txt = 'CANCELADA'; cls = 'status-cancelada'; }

          const tr = document.createElement('tr');
          tr.dataset.id = r.id;
          tr.innerHTML = `
        <td>${servicioNombre}</td>
        <td>${fechaFmt}</td>
        <td>${hIni}</td>
        <td>${hFin}</td>
        <td>${r.nombre}</td>
        <td>${r.telefono}</td>
        <td>${r.notas}</td>
        <td class="status-cell ${cls}" data-status="${r.estado}">${txt}</td>
        <td>
          <select class="form-select employee-select" data-id="${r.id}">
            <option value="">-- Ninguno --</option>
            ${empleados.map(e =>
            `<option value="${e.id}">${e.nombre}</option>`
          ).join('')}
          </select>
        </td>
      `;

          const tbody = document.querySelector('table.table tbody');

          const noData = tbody.querySelector('tr td[colspan="9"]');
          if (noData) noData.parentElement.remove();

          let inserted = false;
          [...tbody.rows].forEach(row => {
            if (inserted) return;
            if (!row.cells[2]) return;
            const cellTime = row.cells[2].innerText.trim();
            if (cellTime > hIni) {
              tbody.insertBefore(tr, row);
              inserted = true;
            }
          });
          if (!inserted) tbody.appendChild(tr);

          const successText = document.getElementById('cita-success-text');
          const successModal = document.getElementById('citaSuccessModal');
          if (successText && successModal) {
            successText.innerText = `Tu cita para las ${hIni} se ha generado con éxito.`;
            new bootstrap.Modal(successModal).show();
          }

          btn.disabled = false;
        })
        .catch(err => {
          console.error(err);
          btn.disabled = false;
          alert('Error de servidor.');
        });
    });

    document.getElementById('addCitaModal').addEventListener('hidden.bs.modal', function () {
      form.reset();
      nameSelect.disabled = true;
      durationContainer.classList.add('d-none');
      durationSelect.disabled = true;
      fechaInput.disabled = true;
      timeContainer.classList.add('d-none');
      horaSelect.disabled = true;
      nombreInput.disabled = true;
      telefonoInput.disabled = true;
      notasInput.disabled = true;
    });
  });
</script>