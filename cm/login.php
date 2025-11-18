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
        header("Location: home.php");
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
  <title>Login - Classic Man Barbershop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../logo.ico">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @font-face {
      font-family: 'AnthonyItalic';
      src: url('../font/Anthony_Italic.ttf') format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    .script-font {
      font-family: 'AnthonyItalic', cursive;
    }

    .login-bg {
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    }

    .login-card {
      backdrop-filter: blur(10px);
      animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .input-field:focus {
      border-color: #b8860b;
      box-shadow: 0 0 0 3px rgba(184, 134, 11, 0.1);
    }
  </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">
  
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <a href="../index-es.php" class="inline-flex items-center space-x-3 mb-4">
        <img src="../imgs/classic.webp" alt="Logo" class="h-16">
        <div class="flex flex-col items-start">
          <span class="script-font text-4xl text-white">Classic Man</span>
          <span class="text-xs tracking-widest text-gray-300 font-bold">BARBERSHOP</span>
        </div>
      </a>
    </div>

    <div class="login-card bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl shadow-2xl border border-gray-700 overflow-hidden">
      <div class="h-1 bg-gradient-to-r from-yellow-600 to-yellow-500"></div>
      
      <div class="p-8">
        <h2 class="text-3xl font-bold text-white mb-2 text-center">Bienvenido</h2>
        <p class="text-gray-400 text-center mb-8">Ingresa tus credenciales para continuar</p>

        <?php if(isset($error)): ?>
          <div class="bg-red-500/10 border border-red-500 text-red-500 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
          </div>
        <?php endif; ?>

        <form action="" method="POST" novalidate>
          <div class="mb-6">
            <label for="username" class="block text-gray-300 font-semibold mb-2">
              <i class="fas fa-user mr-2 text-yellow-600"></i>Usuario
            </label>
            <input 
              type="text" 
              name="username" 
              id="username" 
              class="input-field w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none transition duration-200"
              placeholder="Ingresa tu usuario"
              required
              autocomplete="username"
            >
          </div>

          <div class="mb-6">
            <label for="password" class="block text-gray-300 font-semibold mb-2">
              <i class="fas fa-lock mr-2 text-yellow-600"></i>Contraseña
            </label>
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="input-field w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none transition duration-200"
              placeholder="Ingresa tu contraseña"
              required
              autocomplete="current-password"
            >
          </div>

          <button 
            type="submit" 
            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 flex items-center justify-center"
          >
            <span>Ingresar</span>
            <i class="fas fa-arrow-right ml-2"></i>
          </button>
        </form>

        <div class="mt-6 text-center">
          <a href="../index-es.php" class="text-gray-400 hover:text-yellow-600 transition duration-200 text-sm">
            <i class="fas fa-arrow-left mr-2"></i>Volver al inicio
          </a>
        </div>
      </div>
    </div>

    <p class="text-center text-gray-500 text-sm mt-6">
      &copy; <span id="year"></span> Classic Man Barbershop
    </p>
  </div>

  <script>
    document.getElementById('year').innerText = new Date().getFullYear();
  </script>
</body>
</html>