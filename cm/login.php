<?php
require_once '../conn/conexionBD.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $hashed_password = hash('sha256', $password);

    $sql = "SELECT * FROM Sesion WHERE username = ? AND password = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hashed_password]);

    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* Body centrado */
    body {
      margin: 0;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f5f7fa;
      font-family: 'Montserrat', sans-serif;
    }
    /* Tarjeta minimalista */
    .login-card {
      width: 100%;
      max-width: 360px;
      border: none;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      background: #ffffff;
      overflow: hidden;
    }
    /* Accent bar superior */
    .login-accent {
      height: 4px;
      background: #4e73df;
    }
    .login-body {
      padding: 2rem;
    }
    /* Títulos y botones */
    .login-body h3 {
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: #333333;
    }
    .form-control {
      border-radius: 4px;
      padding: .75rem;
    }
    .btn-primary {
      background: #4e73df;
      border: none;
      border-radius: 4px;
      padding: .75rem;
      font-weight: 600;
    }
    .btn-primary:hover {
      background: #2e59d9;
    }
    .alert {
      font-size: .9rem;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-accent"></div>
    <div class="login-body">
      <h3 class="text-center">Iniciar Sesión</h3>
      <?php if(isset($error)): ?>
        <div class="alert alert-danger text-center">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>
      <form action="" method="POST" novalidate>
        <div class="mb-3">
          <label for="username" class="form-label">Usuario</label>
          <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Ingresar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
