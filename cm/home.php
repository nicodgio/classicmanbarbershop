<?php
require_once 'dashboard.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$hoy = date('Y-m-d');

$citasHoy = $pdo->query("SELECT COUNT(*) FROM Citas WHERE fecha = '$hoy' AND estado IN (1,2)")->fetchColumn();
$ventasHoy = $pdo->query("SELECT COUNT(*) FROM Ventas WHERE DATE(fecha_venta) = '$hoy'")->fetchColumn();
$totalVentasHoy = $pdo->query("SELECT COALESCE(SUM(precio_final), 0) FROM Ventas WHERE DATE(fecha_venta) = '$hoy'")->fetchColumn();
$empleadosActivos = $pdo->query("SELECT COUNT(*) FROM Empleados WHERE activo = 1")->fetchColumn();
$productosActivos = $pdo->query("SELECT COUNT(*) FROM ProdSer WHERE activo = 1")->fetchColumn();
$citasPendientes = $pdo->query("SELECT COUNT(*) FROM Citas WHERE estado = 2 AND fecha >= '$hoy'")->fetchColumn();

$proximasCitas = $pdo->query("
  SELECT c.*, ps.nombre AS servicio 
  FROM Citas c 
  INNER JOIN ProdSer ps ON c.servicio_id = ps.id 
  WHERE c.fecha >= '$hoy' AND c.estado IN (1,2)
  ORDER BY c.fecha, c.hora_inicio 
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$ultimasVentas = $pdo->query("
  SELECT * FROM Ventas 
  WHERE DATE(fecha_venta) = '$hoy'
  ORDER BY fecha_venta DESC 
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard</title>
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
      padding: 1.5rem;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      border-radius: 6px;
      padding: 1.5rem;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }

    .stat-icon.yellow { background: #fff9e6; color: #b8860b; }
    .stat-icon.green { background: #e6f7ed; color: #22c55e; }
    .stat-icon.blue { background: #e6f2ff; color: #3b82f6; }
    .stat-icon.purple { background: #f3e6ff; color: #a855f7; }
    .stat-icon.red { background: #ffe6e6; color: #ef4444; }
    .stat-icon.orange { background: #fff3e6; color: #f97316; }

    .stat-number {
      font-size: 2rem;
      font-weight: bold;
      color: #1f2937;
      margin: 0.5rem 0;
    }

    .stat-label {
      color: #6b7280;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: bold;
      color: #1f2937;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid #eceff1;
    }

    .list-item {
      padding: 0.75rem 0;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .list-item:last-child {
      border-bottom: none;
    }

    .list-item:hover {
      background: #f9fafb;
      margin: 0 -1rem;
      padding-left: 1rem;
      padding-right: 1rem;
    }

    .badge {
      padding: 0.25rem 0.75rem;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-confirmed { background: #d1fae5; color: #065f46; }
    .badge-efectivo { background: #dbeafe; color: #1e40af; }
    .badge-tarjeta { background: #e0e7ff; color: #3730a3; }

    .empty-state {
      text-align: center;
      padding: 2rem 1rem;
      color: #9ca3af;
    }

    .empty-state i {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      opacity: 0.3;
    }

    .stat-link {
      color: inherit;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .stat-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container mt-4 px-3 mb-3">
    <h2 class="mb-4">Dashboard</h2>

    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-card-header">
          <div>
            <div class="stat-label">Citas Hoy</div>
            <div class="stat-number"><?= $citasHoy ?></div>
          </div>
          <div class="stat-icon yellow">
            <i class="fas fa-calendar-check"></i>
          </div>
        </div>
        <a href="citas.php" class="stat-link" style="color: #b8860b;">
          Ver todas <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div>
            <div class="stat-label">Citas Pendientes</div>
            <div class="stat-number"><?= $citasPendientes ?></div>
          </div>
          <div class="stat-icon orange">
            <i class="fas fa-clock"></i>
          </div>
        </div>
        <a href="citas.php?filter=proximos" class="stat-link" style="color: #f97316;">
          Ver pendientes <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div>
            <div class="stat-label">Ventas Hoy</div>
            <div class="stat-number"><?= $ventasHoy ?></div>
          </div>
          <div class="stat-icon green">
            <i class="fas fa-shopping-cart"></i>
          </div>
        </div>
        <a href="#" id="ventasLink2" class="stat-link" style="color: #22c55e;">
          Nueva venta <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div>
            <div class="stat-label">Total Ventas Hoy</div>
            <div class="stat-number">$<?= number_format($totalVentasHoy, 2) ?></div>
          </div>
          <div class="stat-icon blue">
            <i class="fas fa-dollar-sign"></i>
          </div>
        </div>
        <?php if ($_SESSION['user']['categoria'] == 1): ?>
        <a href="reporte.php" class="stat-link" style="color: #3b82f6;">
          Ver reporte <i class="fas fa-arrow-right"></i>
        </a>
        <?php endif; ?>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div>
            <div class="stat-label">Empleados Activos</div>
            <div class="stat-number"><?= $empleadosActivos ?></div>
          </div>
          <div class="stat-icon purple">
            <i class="fas fa-users"></i>
          </div>
        </div>
        <?php if ($_SESSION['user']['categoria'] == 1): ?>
        <a href="usuarios.php" class="stat-link" style="color: #a855f7;">
          Gestionar <i class="fas fa-arrow-right"></i>
        </a>
        <?php endif; ?>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div>
            <div class="stat-label">Productos/Servicios</div>
            <div class="stat-number"><?= $productosActivos ?></div>
          </div>
          <div class="stat-icon red">
            <i class="fas fa-box"></i>
          </div>
        </div>
        <?php if ($_SESSION['user']['categoria'] == 1): ?>
        <a href="prod_ser.php" class="stat-link" style="color: #ef4444;">
          Ver catálogo <i class="fas fa-arrow-right"></i>
        </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h3 class="section-title">
              <i class="fas fa-calendar-alt me-2"></i>Próximas Citas
            </h3>
            <?php if (count($proximasCitas) > 0): ?>
              <?php foreach ($proximasCitas as $cita): ?>
                <div class="list-item">
                  <div>
                    <strong><?= htmlspecialchars($cita['nombre']) ?></strong><br>
                    <small style="color: #6b7280;">
                      <?= htmlspecialchars($cita['servicio']) ?> - 
                      <?= date('d/m', strtotime($cita['fecha'])) ?> a las 
                      <?= date('H:i', strtotime($cita['hora_inicio'])) ?>
                    </small>
                  </div>
                  <span class="badge <?= $cita['estado'] == 2 ? 'badge-pending' : 'badge-confirmed' ?>">
                    <?= $cita['estado'] == 2 ? 'Pendiente' : 'Confirmada' ?>
                  </span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No hay citas próximas</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h3 class="section-title">
              <i class="fas fa-receipt me-2"></i>Últimas Ventas
            </h3>
            <?php if (count($ultimasVentas) > 0): ?>
              <?php foreach ($ultimasVentas as $venta): ?>
                <div class="list-item">
                  <div>
                    <strong><?= htmlspecialchars($venta['concepto']) ?></strong><br>
                    <small style="color: #6b7280;">
                      <?= htmlspecialchars($venta['empleado']) ?> - 
                      <?= date('H:i', strtotime($venta['fecha_venta'])) ?>
                    </small>
                  </div>
                  <div style="text-align: right;">
                    <strong style="color: #22c55e;">$<?= number_format($venta['precio_final'], 2) ?></strong><br>
                    <span class="badge <?= strtolower($venta['tipo_pago']) === 'efectivo' ? 'badge-efectivo' : 'badge-tarjeta' ?>">
                      <?= htmlspecialchars($venta['tipo_pago']) ?>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <p>No hay ventas hoy</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const ventasLink2 = document.getElementById('ventasLink2');
      if (ventasLink2) {
        ventasLink2.addEventListener('click', function(e) {
          e.preventDefault();
          fetch('config/check_dolar.php')
            .then(res => res.json())
            .then(data => {
              if (data.valid) {
                window.location.href = 'ventas.php';
              } else {
                const modalEl = document.getElementById('dolarModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
              }
            })
            .catch(console.error);
        });
      }
    });
  </script>
</body>
</html>