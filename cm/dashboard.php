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
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
  <style>
    @font-face {
      font-family: 'AnthonyItalic';
      src: url('../font/Anthony_Italic.ttf') format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    body {
      font-family: 'Roboto', sans-serif;
    }

    #sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      background: linear-gradient(180deg, #1a1a1a 0%, #000000 100%);
      color: #fff;
      overflow: hidden;
      transition: width 0.3s ease;
      z-index: 1100;
      box-shadow: 4px 0 12px rgba(0, 0, 0, 0.3);
    }

    #sidebar.collapsed {
      width: 70px;
    }

    #sidebar.expanded {
      width: 260px;
    }

    @media (max-width: 992px) {
      #sidebar.collapsed {
        width: 60px;
      }

      #sidebar.expanded {
        width: 220px;
      }
    }

    .sidebar-header {
      padding: 1.5rem 1rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .sidebar-logo {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .brand-text {
      overflow: hidden;
      white-space: nowrap;
    }

    .brand-name {
      font-family: 'AnthonyItalic', cursive;
      font-size: 1.5rem;
      color: #b8860b;
      margin: 0;
      line-height: 1;
    }

    .brand-subtitle {
      font-size: 0.65rem;
      color: rgba(255, 255, 255, 0.6);
      letter-spacing: 0.1em;
      text-transform: uppercase;
      font-weight: bold;
      margin: 0;
    }

    #sidebar.collapsed .brand-text {
      display: none;
    }

    #sidebar .nav {
      padding: 1rem 0;
    }

    #sidebar .nav-link {
      color: rgba(255, 255, 255, 0.7);
      padding: 0.875rem 1.25rem;
      border-left: 3px solid transparent;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 1rem;
      margin: 0.25rem 0;
      white-space: nowrap;
      cursor: pointer;
    }

    #sidebar .nav-link:hover {
      background: rgba(184, 134, 11, 0.1);
      border-left-color: #b8860b;
      color: #ffffff;
    }

    #sidebar .nav-link.active {
      background: rgba(184, 134, 11, 0.15);
      border-left-color: #b8860b;
      color: #ffffff;
    }

    #sidebar .nav-link i {
      font-size: 1.25rem;
      width: 24px;
      text-align: center;
      flex-shrink: 0;
    }

    #sidebar.collapsed .link-text {
      display: none;
    }

    #toggleBtn {
      position: absolute;
      bottom: 1.5rem;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(184, 134, 11, 0.2);
      border: 1px solid rgba(184, 134, 11, 0.3);
      color: #b8860b;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 1.25rem;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #toggleBtn:hover {
      background: rgba(184, 134, 11, 0.3);
      border-color: #b8860b;
      color: #ffffff;
    }

    #main-content {
      margin-left: 70px;
      transition: margin-left 0.3s ease;
      min-height: 100vh;
      background: #f5f6f8;
    }

    #sidebar.expanded+#main-content {
      margin-left: 260px;
    }

    @media (max-width: 992px) {
      #sidebar.expanded+#main-content {
        margin-left: 220px;
      }
    }

    .top-navbar {
      background: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      position: sticky;
      top: 0;
      z-index: 1000;
      padding: 1rem 0;
    }

    .user-menu-btn {
      background: #f8f9fa;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .user-menu-btn:hover {
      background: #e9ecef;
      border-color: #dee2e6;
    }

    .user-menu-btn i.fa-user-circle {
      font-size: 1.5rem;
      color: #6c757d;
    }

    .user-menu-btn .user-name {
      font-weight: 600;
      color: #495057;
    }

    .user-menu-btn i.fa-chevron-down {
      font-size: 0.75rem;
      color: #adb5bd;
    }

    .dropdown-menu {
      border: 1px solid #e9ecef;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      margin-top: 0.5rem;
    }

    .dropdown-header {
      background: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
      padding: 0.75rem 1rem;
    }

    .dropdown-item {
      padding: 0.75rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .dropdown-item:hover {
      background: #f8f9fa;
    }

    .dropdown-item.text-danger:hover {
      background: #fff5f5;
    }

    .form-switch .form-check-input {
      width: 3rem;
      height: 1.5rem;
      cursor: pointer;
    }

    .form-switch .form-check-input:checked {
      background-color: #b8860b;
      border-color: #b8860b;
    }

    .modal-content {
      border: none;
      border-radius: 12px;
      overflow: hidden;
    }

    .modal-header {
      background: linear-gradient(135deg, #b8860b 0%, #9a7d0d 100%);
      color: white;
      border: none;
      padding: 1.5rem;
    }

    .modal-body {
      padding: 2rem;
    }

    .modal-footer {
      background: #f8f9fa;
      border: none;
      padding: 1rem 1.5rem;
    }

    .alert-icon {
      width: 60px;
      height: 60px;
      background: #fff3cd;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
    }

    .alert-icon i {
      font-size: 1.75rem;
      color: #b8860b;
    }
  </style>
</head>

<body>
  <div id="sidebar" class="collapsed">
    <div class="sidebar-header">
      <img src="../imgs/classic.webp" alt="Logo" class="sidebar-logo">
      <div class="brand-text">
        <p class="brand-name">Classic Man</p>
        <p class="brand-subtitle">Barbershop</p>
      </div>
    </div>

    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="home.php" class="nav-link">
          <i class="fas fa-home"></i>
          <span class="link-text">Inicio</span>
        </a>
      </li>
      <?php if ($_SESSION['user']['categoria'] == 1): ?>
        <li class="nav-item">
          <a href="reporte.php" class="nav-link">
            <i class="fas fa-chart-bar"></i>
            <span class="link-text">Reportes</span>
          </a>
        </li>
      <?php endif; ?>
      <li class="nav-item">
        <a href="#" id="ventasLink" class="nav-link">
          <i class="fas fa-shopping-cart"></i>
          <span class="link-text">Ventas</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="citas.php" class="nav-link">
          <i class="fas fa-calendar"></i>
          <span class="link-text">Citas</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="lealtad.php" class="nav-link">
          <i class="fas fa-award"></i>
          <span class="link-text">Recompensas</span>
        </a>
      </li>
      <?php if ($_SESSION['user']['categoria'] == 1): ?>
        <li class="nav-item">
          <a href="usuarios.php" class="nav-link">
            <i class="fas fa-users"></i>
            <span class="link-text">Usuarios</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="prod_ser.php" class="nav-link">
            <i class="fas fa-box"></i>
            <span class="link-text">Productos y Servicios</span>
          </a>
        </li>
      <?php endif; ?>
      <li class="nav-item">
        <a href="configuracion.php" class="nav-link">
          <i class="fas fa-cogs"></i>
          <span class="link-text">Configuración</span>
        </a>
      </li>
    </ul>

    <button id="toggleBtn">
      <i class="fas fa-angle-right"></i>
    </button>
  </div>

  <div id="main-content">
    <nav class="top-navbar">
      <div class="container-fluid px-4">
        <div class="d-flex justify-content-end">
          <div class="dropdown">
            <button class="user-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-user-circle"></i>
              <span class="user-name"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
              <i class="fas fa-chevron-down"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li class="dropdown-header">
                <div class="small text-muted">Sesión iniciada como:</div>
                <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item text-danger" href="logout.php">
                  <i class="fas fa-sign-out-alt"></i>
                  <span>Cerrar Sesión</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

  <div class="modal fade" id="dolarModal" tabindex="-1" aria-labelledby="dolarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dolarModalLabel">
            <i class="fas fa-exclamation-triangle me-2"></i>Configuración Requerida
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body text-center">
          <div class="alert-icon">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <p class="lead">
            Debes asignar el valor del dólar y/o el inicio de caja en Configuración antes de generar una venta.
          </p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <a href="configuracion.php" class="btn btn-warning">
            <i class="fas fa-cog me-2"></i>Ir a Configuración
          </a>
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
          toggleBtn.querySelector('i').className = 'fas fa-angle-left';
        } else {
          sidebar.classList.replace('expanded', 'collapsed');
          toggleBtn.querySelector('i').className = 'fas fa-angle-right';
        }
      });

      const ventasLink = document.getElementById('ventasLink');
      if (ventasLink) {
        ventasLink.addEventListener('click', function (e) {
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