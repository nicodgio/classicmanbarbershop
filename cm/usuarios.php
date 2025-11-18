<?php
require_once 'dashboard.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$sql = "SELECT id, nombre, telefono, fecha_inicio, activo FROM Empleados";
$stmt = $pdo->query($sql);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestión de Empleados</title>
  <style>
    .icon-navy {
      color: #162B4E !important;
    }
    .table a {
      text-decoration: none;
    }

    body {
      background: #f5f6f8;
      color: #333;
    }

    .card {
      border: none;
      border-radius: 6px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
    }

    .card-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      padding: 1.5rem;
    }

    .card-footer {
      background: #fff;
      border-top: 1px solid #e2e2e2;
      padding: .75rem 1.5rem;
      text-align: right;
      font-size: .9rem;
      color: #666;
    }

    .data-body {
      overflow: auto;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
    }

    .table-hover tbody tr:hover {
      background: rgba(22, 43, 78, .05);
    }

    .table th,
    .table td {
      vertical-align: middle;
      font-size: .9rem;
      padding: .75rem;
    }

    .table thead {
      background: #eceff1;
    }

    .btn {
      border-radius: 4px;
      padding: .5rem 1rem;
      font-size: .9rem;
      cursor: pointer;
      border: none;
    }

    .btn-outline-secondary {
      background: #fff;
      border: 1px solid #ccc;
    }

    .btn-outline-secondary:hover {
      background: #e9ecef;
    }

    .form-check-input {
      width: 1.2em;
      height: 1.2em;
    }
  </style>
  <!-- Asegúrate de incluir Bootstrap 5 y FontAwesome en tu proyecto -->
</head>

<body>
  <div class="container mt-4 px-3 mb-3">
    <div class="card data-card">
      <div class="card-body data-body">
        <div class="d-flex justify-content-end mb-3">
          <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus"></i> Añadir Empleado
          </button>
        </div>
        <?php include 'usuarios/tabla_usuarios.php'; ?>
      </div>
      <div class="card-footer">
        Total de empleados: <?= count($empleados) ?>
      </div>
    </div>
  </div>

  <!-- Modal Añadir -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="usuarios/add_user.php" method="post" class="ajax-form">
          <div class="modal-header">
            <h5 class="modal-title">Añadir Empleado</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="nombre" class="form-label">Nombre</label>
              <input type="text" class="form-control" name="nombre" id="nombre" required>
            </div>
            <div class="mb-3">
              <label for="telefono" class="form-label">Teléfono</label>
              <input type="text" class="form-control" name="telefono" id="telefono" required>
            </div>
            <div class="mb-3">
              <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
              <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php foreach ($empleados as $empleado): ?>
    <!-- Modal para editar usuario -->
    <div class="modal fade" id="editModal<?= $empleado['id'] ?>" tabindex="-1"
      aria-labelledby="editModalLabel<?= $empleado['id'] ?>" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="usuarios/edit_user.php" method="post" class="ajax-form">
            <div class="modal-header">
              <h5 class="modal-title" id="editModalLabel<?= $empleado['id'] ?>">Editar Usuario</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id" value="<?= $empleado['id'] ?>">
              <div class="mb-3">
                <label for="nombre_<?= $empleado['id'] ?>" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre" id="nombre_<?= $empleado['id'] ?>"
                  value="<?= htmlspecialchars($empleado['nombre']) ?>" required>
              </div>
              <div class="mb-3">
                <label for="telefono_<?= $empleado['id'] ?>" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" id="telefono_<?= $empleado['id'] ?>"
                  value="<?= htmlspecialchars($empleado['telefono']) ?>" required>
              </div>
              <div class="mb-3">
                <label for="fecha_inicio_<?= $empleado['id'] ?>" class="form-label">Fecha de Inicio</label>
                <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio_<?= $empleado['id'] ?>"
                  value="<?= htmlspecialchars(date('Y-m-d', strtotime($empleado['fecha_inicio']))) ?>" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="deleteModal<?= $empleado['id'] ?>" tabindex="-1"
      aria-labelledby="deleteModalLabel<?= $empleado['id'] ?>" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="usuarios/delete_user.php" method="post" class="ajax-form">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteModalLabel<?= $empleado['id'] ?>">Eliminar Usuario</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id" value="<?= $empleado['id'] ?>">
              ¿Estás seguro de que deseas eliminar al usuario
              <strong><?= htmlspecialchars($empleado['nombre']) ?></strong>?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {

      function refreshEmpleadosTable() {
        fetch('usuarios/tabla_usuarios.php')
          .then(response => response.text())
          .then(html => {
            document.getElementById('content').innerHTML = html;
          })
          .catch(error => console.error('Error refrescando la tabla:', error));
      }

      document.getElementById('content').addEventListener('submit', function (e) {
        if (e.target && e.target.matches('.ajax-form')) {
          e.preventDefault();
          const form = e.target;
          const formData = new FormData(form);

          fetch(form.action, {
            method: form.method,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Oculta el modal que corresponda
                const modalEl = form.closest('.modal');
                if (modalEl) {
                  const modalInstance = bootstrap.Modal.getInstance(modalEl);
                  if (modalInstance) {
                    modalInstance.hide();
                  }
                }
                // Refresca la tabla si la acción es de empleados
                if (form.action.indexOf('usuarios') !== -1) {
                  refreshEmpleadosTable();
                }
              } else {
                alert('Error: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Error en la conexión');
            });
        }
      });

      // Manejo del cambio de estado (toggle) del empleado
      document.getElementById('content').addEventListener('change', function (e) {
        if (e.target && e.target.matches('.toggle-active')) {
          const checkbox = e.target;
          const userId = checkbox.getAttribute('data-id');
          const newStatus = checkbox.checked ? 1 : 0;

          fetch('usuarios/toggle_active.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: userId, activo: newStatus })
          })
            .then(response => response.json())
            .then(data => {
              if (!data.success) {
                alert('Error al actualizar el estado.');
                checkbox.checked = !checkbox.checked;
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Error en la conexión.');
              checkbox.checked = !checkbox.checked;
            });
        }
      });
    });
  </script>
</body>

</html>