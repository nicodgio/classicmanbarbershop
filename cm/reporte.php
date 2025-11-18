<?php
// reporte.php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'dashboard.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Parámetros de filtro
$filtro   = $_GET['filtro']   ?? 'hoy';
$empleado = $_GET['empleado'] ?? 'todos';

// Condición de fechas
switch ($filtro) {
    case 'ayer':
        $date_cond = "DATE(fecha_venta) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        break;
    case 'ultimos7':
        $date_cond = "DATE(fecha_venta) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()";
        break;
    case 'mes_actual':
        $date_cond = "MONTH(fecha_venta)=MONTH(CURDATE()) AND YEAR(fecha_venta)=YEAR(CURDATE())";
        break;
    default:
        $date_cond = "DATE(fecha_venta) = CURDATE()";
}

// Lista de empleados según el filtro
$stmtE = $pdo->query("SELECT DISTINCT empleado FROM Ventas WHERE $date_cond ORDER BY empleado");
$empleados = $stmtE->fetchAll(PDO::FETCH_COLUMN);

// Consulta de ventas
$sql = "SELECT 
            id, concepto, categoria, tipo_pago, precio, precio_final,
            propina, descuento, ticket, notas, fecha_venta, empleado
        FROM Ventas
        WHERE $date_cond";
if ($empleado !== 'todos') {
    $sql .= " AND empleado = :empleado";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':empleado' => $empleado]);
} else {
    $stmt = $pdo->query($sql);
}
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reporte de Ventas</title>
  <style>
    body { background: #f5f6f8; color: #333; }
    .card { border: none; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
    .card-body { padding: 1rem; }
    .card-footer { background: #fff; border-top: 1px solid #e2e2e2; padding: .75rem 1rem; }
    .filter-card { flex: 0 0 280px; display:flex; flex-direction:column; }
    .data-card  { flex: 1; display:flex; flex-direction:column; }
    .data-body  { flex:1; overflow:auto; }
    .table th, .table td { vertical-align: middle; font-size: .9rem; }
    .table thead { background: #eceff1; }
    .form-select, .btn { border-radius: 4px; }
    .btn-outline-secondary { border: 1px solid #ccc; }
    .btn-outline-secondary:hover { background: #e9ecef; }
    .delete-btn { color: #c00; text-decoration: none; font-weight: bold; }
    .delete-btn:hover { color: #900; }
  </style>
</head>
<body>
  <div class="container mt-4 px-3 mb-3">
    <div class="d-flex flex-column flex-md-row" style="height: calc(100vh - 100px);">

      <!-- FILTROS LATERAL -->
      <div class="card me-md-3 mb-3 mb-md-0 filter-card">
        <div class="card-body">
          <h5 class="mb-3">Filtros</h5>
          <form id="filtros" method="GET">
            <div class="mb-3">
              <label class="form-label">Período</label>
              <select name="filtro" class="form-select" onchange="this.form.submit()">
                <option value="hoy"        <?= $filtro==='hoy'        ? 'selected':'' ?>>Hoy</option>
                <option value="ayer"       <?= $filtro==='ayer'       ? 'selected':'' ?>>Ayer</option>
                <option value="ultimos7"   <?= $filtro==='ultimos7'   ? 'selected':'' ?>>Últimos 7 días</option>
                <option value="mes_actual" <?= $filtro==='mes_actual' ? 'selected':'' ?>>Mes actual</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Empleado</label>
              <select name="empleado" class="form-select" onchange="this.form.submit()">
                <option value="todos">Todos</option>
                <?php foreach($empleados as $e): ?>
                  <option value="<?= htmlspecialchars($e) ?>" <?= $empleado===$e?'selected':'' ?>>
                    <?= htmlspecialchars($e) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
          <hr>
          <div class="d-grid gap-2">
            <a href="reporte/reporte_excel.php?filtro=<?= $filtro ?>&empleado=<?= $empleado ?>"
               class="btn btn-outline-secondary">🔽 Excel</a>
            <a href="reporte/ticket.php?time=<?= time() ?>&filtro=<?= $filtro ?>&empleado=<?= $empleado ?>"
               class="btn btn-outline-secondary <?= in_array($filtro,['ultimos7','mes_actual'])?'disabled':'' ?>">
               🖨️ Ticket
            </a>
          </div>
        </div>
      </div>

      <!-- TABLA DE DATOS -->
      <div class="card data-card">
        <div class="card-body data-body">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>ID</th><th>Concepto</th><th>Categoría</th><th>Pago</th>
                <th>Precio</th><th>Desc.</th><th>Final</th><th>Propina</th>
                <th>Ticket</th><th>Notas</th><th>Fecha/Hora</th><th>Empleado</th>
                <th>Eliminar</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($ventas): foreach ($ventas as $v): ?>
                <tr data-id="<?= $v['id'] ?>">
                  <td><?= $v['id'] ?></td>
                  <td><?= htmlspecialchars($v['concepto']) ?></td>
                  <td><?= htmlspecialchars($v['categoria']) ?></td>
                  <td><?= htmlspecialchars($v['tipo_pago']) ?></td>
                  <td><?= number_format($v['precio'],2) ?></td>
                  <td><?= number_format($v['descuento'],2) ?></td>
                  <td><?= number_format($v['precio_final'],2) ?></td>
                  <td><?= number_format($v['propina'],2) ?></td>
                  <td><?= htmlspecialchars($v['ticket']) ?></td>
                  <td><?= htmlspecialchars($v['notas']) ?></td>
                  <td>
                    <?= ($filtro==='ultimos7' || $filtro==='mes_actual')
                        ? date("d/m/Y H:i", strtotime($v['fecha_venta']))
                        : date("H:i", strtotime($v['fecha_venta'])) ?>
                  </td>
                  <td><?= htmlspecialchars($v['empleado']) ?></td>
                  <td class="text-center">
                    <a href="#" class="delete-btn" data-id="<?= $v['id'] ?>">✕</a>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr>
                  <td colspan="13" class="text-center py-4">No hay registros</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card-footer text-muted text-end">
          Total registros: <?= count($ventas) ?>
        </div>
      </div>

    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelector('.data-body').addEventListener('click', async e => {
        if (!e.target.matches('.delete-btn')) return;
        e.preventDefault();
        const id = e.target.dataset.id;
        if (!confirm('¿Eliminar venta #' + id + '?')) return;

        try {
          const form = new FormData();
          form.append('id', id);
          const res = await fetch('reporte/delete_venta.php', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
          });
          const json = await res.json();
          if (json.success) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            row && row.remove();
          } else {
            alert('Error: ' + (json.error || 'desconocido'));
          }
        } catch (err) {
          alert('Error de red');
        }
      });
    });
  </script>
</body>
</html>
