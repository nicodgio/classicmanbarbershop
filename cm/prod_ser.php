<?php
require_once 'dashboard.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servicios</title>
  <style>
    body {
      background: #f5f6f8;
      color: #333;
    }

    .container {
      padding: 1rem;
    }

    .card {
      background: #fff;
      border: none;
      border-radius: 6px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }

    .card-body {
      padding: 1rem;
    }

    .filter-card {
      flex: 0 0 280px;
      display: flex;
      flex-direction: column;
      margin-right: 1rem;
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

    .form-select,
    .btn {
      border-radius: 4px;
    }

    .btn-primary {
      background-color: #162B4E;
      border: none;
    }

    .btn-primary:hover {
      background-color: #0f2342;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
    }

    .table th,
    .table td {
      padding: .75rem;
      text-align: center;
      vertical-align: middle;
      font-size: .9rem;
      border: 1px solid #e2e2e2;
    }

    .table thead {
      background: #162B4E;
    }

    .table thead th {
      color: #000;
    }

    .text-center {
      text-align: center;
    }

    .mb-3 {
      margin-bottom: 1rem;
    }

    .me-2 {
      margin-right: .5rem;
    }

    .text-primary {
      color: #162B4E;
    }

    .text-primary:hover {
      color: #0f2342;
    }

    .text-danger {
      color: #c00;
    }

    .text-danger:hover {
      color: #900;
    }
  </style>
</head>

<body>
  <div class="container mt-4">
    <div id="content">
      <?php include 'productos/tabla_prod_ser.php'; ?>
    </div>
  </div>
</body>

</html>