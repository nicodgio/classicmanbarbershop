<?php
// citas.php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once 'dashboard.php';
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

// Parámetro de filtro
$filter = $_GET['filter'] ?? 'hoy';
$today = date('Y-m-d');
$where = $filter === 'proximos'
  ? 'cit.fecha > :today'
  : 'cit.fecha = :today';

// Obtengo citas
$sql = "
  SELECT cit.*, ps.nombre AS servicio_nombre, cit.empleado_asignado
  FROM Citas AS cit
  INNER JOIN ProdSer AS ps ON cit.servicio_id = ps.id
  WHERE {$where}
  ORDER BY cit.fecha, cit.hora_inicio
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['today' => $today]);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtengo lista de empleados activos
$empsStmt = $pdo->query("SELECT id, nombre FROM Empleados WHERE activo = 1");
$empleados = $empsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestión de Citas</title>
  <style>
    body {
      background: #f5f6f8;
      color: #333;
    }

    .card {
      border: none;
      border-radius: 6px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }

    .card-body {
      padding: 1rem;
    }

    .card-footer {
      background: #fff;
      border-top: 1px solid #e2e2e2;
      padding: .75rem 1rem;
    }

    .filter-card {
      flex: 0 0 280px;
      display: flex;
      flex-direction: column;
    }

    .data-card {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .data-body {
      flex: 1;
      overflow: auto;
    }

    .table th,
    .table td {
      vertical-align: middle;
      font-size: .9rem;
    }

    .table thead {
      background: #eceff1;
    }

    .form-select,
    .btn {
      border-radius: 4px;
    }

    .status-cell {
      cursor: pointer;
    }

    .status-pendiente {
      background-color: #fff9c4 !important;
    }

    .status-confirmada {
      background-color: #c8e6c9 !important;
    }

    .status-cancelada {
      background-color: #ffcdd2 !important;
    }

    .flatpickr-calendar {
      z-index: 3000 !important;
    }
  </style>
</head>

<body>
  <div class="container mt-4 px-3 mb-3">
    <div class="d-flex flex-column flex-md-row" style="height: calc(100vh - 100px);">

      <!-- FILTROS LATERAL -->
      <div class="card me-md-3 mb-3 mb-md-0 filter-card">
        <div class="card-body">
          <h5 class="mb-3">Filtros</h5>
          <form id="filterForm" method="GET">
            <div class="mb-3">
              <label class="form-label">Mostrar</label>
              <select name="filter" class="form-select" onchange="this.form.submit()">
                <option value="hoy" <?= $filter === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                <option value="proximos" <?= $filter === 'proximos' ? 'selected' : '' ?>>Próximos días</option>
              </select>
            </div>
          </form>
          <hr>
          <div class="d-grid gap-2">
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCitaModal">
              + Agregar Cita
            </button>
          </div>
        </div>
      </div>

      <!-- TABLA DE CITAS -->
      <div class="card data-card">
        <div class="card-body data-body">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Servicio</th>
                <th>Fecha</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Notas</th>
                <th>Estado</th>
                <th>Empleado</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($citas):
                foreach ($citas as $c): ?>
                  <?php
                  $fechaFmt = date('d-m', strtotime($c['fecha']));
                  $hIni = date('H:i', strtotime($c['hora_inicio']));
                  $hFin = date('H:i', strtotime($c['hora_fin']));
                  switch ((int) $c['estado']) {
                    case 2:
                      $txt = 'PENDIENTE';
                      $cls = 'status-pendiente';
                      break;
                    case 1:
                      $txt = 'CONFIRMADA';
                      $cls = 'status-confirmada';
                      break;
                    default:
                      $txt = 'CANCELADA';
                      $cls = 'status-cancelada';
                      break;
                  }
                  ?>
                  <tr data-id="<?= $c['id'] ?>">
                    <td><?= htmlspecialchars($c['servicio_nombre']) ?></td>
                    <td><?= $fechaFmt ?></td>
                    <td><?= $hIni ?></td>
                    <td><?= $hFin ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['telefono']) ?></td>
                    <td><?= nl2br(htmlspecialchars($c['notas'])) ?></td>
                    <td class="status-cell <?= $cls ?>" data-status="<?= $c['estado'] ?>"><?= $txt ?></td>
                    <td>
                      <select class="form-select employee-select" data-id="<?= $c['id'] ?>">
                        <option value="">-- Ninguno --</option>
                        <?php foreach ($empleados as $e): ?>
                          <option value="<?= $e['id'] ?>" <?= $e['id'] == $c['empleado_asignado'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nombre']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                  </tr>
                <?php endforeach; else: ?>
                <tr>
                  <td colspan="9" class="text-center py-4">No hay citas</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card-footer text-muted text-end">
          Total citas: <?= count($citas) ?>
        </div>
      </div>

    </div>
  </div>

  <?php include 'citas/add_cita.php'; ?>

  <!-- Modal editar estado -->
  <div class="modal fade" id="editStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="editStatusForm" method="post" action="citas/update_status.php">
        <div class="modal-content card">
          <div class="modal-header card-body">
            <h5 class="modal-title">Modificar Estatus</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body card-body">
            <input type="hidden" id="editId" name="id">
            <div class="mb-3">
              <label for="editStatus" class="form-label">Estatus</label>
              <select id="editStatus" name="estado" class="form-select" required>
                <option value="" disabled selected>Selecciona un estado</option>
                <option value="1">CONFIRMADA</option>
                <option value="0">CANCELADA</option>
              </select>
            </div>
          </div>
          <div class="modal-footer card-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Cambiar estado (igual que antes)…
      const editModal = new bootstrap.Modal(document.getElementById('editStatusModal'));
      const editForm = document.getElementById('editStatusForm');
      const editId = document.getElementById('editId');
      const editSel = document.getElementById('editStatus');
      const tbody = document.querySelector('table.table tbody');

      tbody.addEventListener('click', e => {
        const cell = e.target.closest('td.status-cell');
        if (!cell) return;
        const tr = cell.closest('tr');
        editId.value = tr.dataset.id;
        editSel.selectedIndex = 0;
        editModal.show();
      });

      editForm.addEventListener('submit', e => {
        e.preventDefault();
        const btn = editForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        fetch(editForm.action, {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: new FormData(editForm)
        })
          .then(r => r.json())
          .then(json => {
            btn.disabled = false;
            if (!json.success) return alert(json.message);
            const { id, estado } = json.data;
            const tr = document.querySelector(`tr[data-id="${id}"]`);
            if (estado == 0) tr.remove();
            else {
              const c = tr.querySelector('td.status-cell');
              c.className = 'status-cell status-confirmada';
              c.innerText = 'CONFIRMADA';
            }
            editModal.hide();
          })
          .catch(() => { btn.disabled = false; alert('Error en el servidor.'); });
      });

      tbody.addEventListener('change', e => {
        // solo reaccionamos si el target fue un <select class="employee-select">
        if (!e.target.matches('.employee-select')) return;

        const select = e.target;
        const id = select.dataset.id;
        const empleado_asignado = select.value || null;

        fetch('citas/update_employee.php', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id, empleado_asignado })
        })
          .then(r => r.json())
          .then(json => {
            if (!json.success) alert(json.message);
          })
          .catch(() => alert('Error al asignar empleado'));
      });
    });
  </script>
</body>

</html>