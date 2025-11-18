<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

require_once '../conn/conexionBD.php';
date_default_timezone_set('America/Mexico_City');

// 1. Consulto si existe registro hoy
$stmt = $pdo->prepare(
  "SELECT id 
     FROM dolar_hoy 
     WHERE DATE(fecha_creacion) = CURDATE() 
     LIMIT 1"
);
$stmt->execute();
$registroHoy = (bool) $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="icon" type="image/x-icon" href="../logo.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
  <style>
    #sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      background-color: #000;
      color: #fff;
      overflow: hidden;
      transition: width 0.3s ease;
      z-index: 1100;
    }

    #sidebar.collapsed {
      width: 60px;
    }

    #sidebar.expanded {
      width: 250px;
    }

    @media (max-width: 992px) {
      #sidebar.collapsed {
        width: 15%;
        min-width: 60px;
      }

      #sidebar.expanded {
        width: 40%;
        min-width: 200px;
      }
    }

    #sidebar .nav-link {
      color: #fff;
      white-space: nowrap;
      padding: 10px 15px;
      cursor: pointer;
    }

    #sidebar .nav-link i {
      font-size: 18px;
      margin-right: 10px;
    }

    #sidebar.collapsed .link-text {
      display: none;
    }

    #toggleBtn {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: none;
      border: none;
      color: #fff;
      font-size: 20px;
      cursor: pointer;
    }

    #sidebar.expanded+#main-content {
      margin-left: 250px;
    }

    @media (max-width: 992px) {
      #sidebar.expanded+#main-content {
        margin-left: 40%;
      }
    }

    .form-switch .form-check-input {
      width: 3rem;
      height: 1.5rem;
      cursor: pointer;
    }

    .form-switch .form-check-input:checked {
      background-color: #162B4E;
      border-color: #162B4E;
    }
  </style>
</head>

<body>
  <div id="sidebar" class="collapsed">
    <ul class="nav flex-column mt-3">
      <!--<li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fa fa-home"></i>
          <span class="link-text">Inicio</span>
        </a>
      </li>-->
      <?php if ($_SESSION['user']['categoria'] == 1): ?>
        <li class="nav-item">
          <a href="reporte.php" class="nav-link">
            <i class="fa fa-chart-bar"></i>
            <span class="link-text">Reportes</span>
          </a>
        </li>
      <?php endif; ?>
      <li class="nav-item">
        <a href="#" id="ventasLink" class="nav-link">
          <i class="fa fa-shopping-cart"></i>
          <span class="link-text">Ventas</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="citas.php" class="nav-link">
          <i class="fa fa-calendar"></i>
          <span class="link-text">Citas</span>
        </a>
      </li>
      <?php if ($_SESSION['user']['categoria'] == 1): ?>
        <li class="nav-item">
          <a href="usuarios.php" class="nav-link">
            <i class="fa fa-users"></i>
            <span class="link-text">Usuarios</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="prod_ser.php" class="nav-link">
            <i class="fa fa-box"></i>
            <span class="link-text">Productos y Servicios</span>
          </a>
        </li>
      <?php endif; ?>
      <li class="nav-item">
        <a href="configuracion.php" class="nav-link">
          <i class="fa fa-cogs"></i>
          <span class="link-text">Configuración</span>
        </a>
      </li>
    </ul>
    <button id="toggleBtn"><i class="fa fa-angle-right"></i></button>
  </div>

  <div id="main-content">
    <nav class="navbar navbar-light bg-light">
      <div class="container-fluid justify-content-end">
        <div class="d-flex align-items-center" style="margin-right:20px;">
          <i class="fa fa-user" style="margin-right:8px;"></i>
          <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
        </div>
      </div>
    </nav>
  </div>

  <div class="modal fade" id="dolarModal" tabindex="-1" aria-labelledby="dolarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dolarModalLabel">Falta Configuración</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          Debes asignar el valor del dólar y/o el inicio de caja en Configuración antes de generar una venta.
        </div>
        <div class="modal-footer">
          <a href="configuracion.php" class="btn btn-primary">Ir a Configuración</a>
        </div>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const sidebar = document.getElementById('sidebar');
      const toggleBtn = document.getElementById('toggleBtn');

      toggleBtn.addEventListener('click', function () {
        if (sidebar.classList.contains('collapsed')) {
          sidebar.classList.replace('collapsed', 'expanded');
          toggleBtn.innerHTML = '<i class="fa fa-angle-left"></i>';
        } else {
          sidebar.classList.replace('expanded', 'collapsed');
          toggleBtn.innerHTML = '<i class="fa fa-angle-right"></i>';
        }
      });
      document.getElementById('ventasLink').addEventListener('click', function (e) {
        e.preventDefault();
        fetch('config/check_dolar.php')
          .then(res => res.json())
          .then(data => {
            if (data.valid) {
              // Si ya hay registro hoy, vamos a ventas.php
              window.location.href = 'ventas.php';
            } else {
              // Si no, abrimos el modal
              const modalEl = document.getElementById('dolarModal');
              const modal = new bootstrap.Modal(modalEl);
              modal.show();
            }
          })
          .catch(console.error);
      });
    });
  </script>
</body>

</html>