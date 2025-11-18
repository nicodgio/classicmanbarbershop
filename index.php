<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'conn/conexionBD.php';
$query = "SELECT `id`, `nombre_en`, `descripcion_en`, `categoria`, `precio_usd`
          FROM `ProdSer`
          WHERE activo = 1";
$result = $pdo->query($query);
$categoryMapping = array(
  "SERVICIOS" => "SERVICES",
  "PAQUETES" => "PACKAGES",
  "OTROS SERVICIOS" => "COLORING",
  "SERVICIOS PARA MUJERES" => "WOMEN'S SERVICES",
  "MASAJE" => "Spa & Massage",
  "FACIAL" => "Spa & Massage"
);
$services = array();
if ($result) {
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    if ($row['categoria'] === 'PRODUCTOS') {
      continue;
    }
    $dbCategory = $row['categoria'];
    $mappedCategory = $categoryMapping[$dbCategory] ?? $dbCategory;
    if ($mappedCategory === 'PRODUCTOS') {
      continue;
    }
    $services[$mappedCategory][] = $row;
  }
}
if (isset($services["Spa & Massage"])) {
  usort($services["Spa & Massage"], function ($a, $b) {
    return strcmp($a['categoria'], $b['categoria']);
  });
}

function generateId($string)
{
  return preg_replace('/[^a-z0-9]+/', '-', strtolower($string));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Classicman Barbershop</title>
  <link rel="icon" type="image/x-icon" href="logo.ico">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @font-face {
      font-family: 'AnthonyItalic';
      src: url('font/Anthony_Italic.ttf') format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    .script-font {
      font-family: 'AnthonyItalic', cursive;
    }

    .hero-bg {
      background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('./imgs/banner.webp') center center;
      background-size: cover;
    }

    .fade-in {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 0.8s ease-out, transform 0.8s ease-out;
    }

    .fade-in.appear {
      opacity: 1;
      transform: translateY(0);
    }

    .service-card {
      transition: all 0.3s ease;
    }

    .service-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(184, 134, 11, 0.3);
    }

    .category-btn {
      position: relative;
      overflow: hidden;
    }

    .category-btn::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: #b8860b;
      transition: width 0.3s ease;
    }

    .category-btn.active::before,
    .category-btn:hover::before {
      width: 100%;
    }

    .navbar-glass {
      background: rgba(0, 0, 0, 0.95);
      backdrop-filter: blur(10px);
    }

    @media (max-width: 768px) {
      .hero-bg {
        background-attachment: scroll;
      }
    }
  </style>
</head>

<body class="bg-black text-gray-100">

  <!-- Navbar -->
  <nav class="navbar-glass fixed w-full top-0 z-50 shadow-lg">
    <div class="container mx-auto px-4 py-4">
      <div class="flex items-center justify-between">
        <a href="#" class="flex items-center space-x-3">
          <img src="./imgs/classic.webp" alt="Logo" class="h-16 md:h-20">
          <div class="flex flex-col">
            <span class="script-font text-3xl md:text-4xl text-white">Classic Man</span>
            <span class="text-xs md:text-sm tracking-widest text-gray-300 font-bold">BARBERSHOP</span>
          </div>
        </a>

        <div class="hidden lg:flex items-center space-x-8">
          <a href="#hero" class="text-gray-300 hover:text-yellow-600 transition">Home</a>
          <a href="#about-us" class="text-gray-300 hover:text-yellow-600 transition">About us</a>
          <a href="#services" class="text-gray-300 hover:text-yellow-600 transition">Services</a>
          <a href="cm/login.php" class="text-gray-300 hover:text-yellow-600 transition"><i class="fas fa-user"></i></a>
          <a href="https://www.classicmanbarbershop.com.mx/index-es.php" target="_self">
            <img src="./imgs/mex.png" alt="Spanish" class="h-8">
          </a>
        </div>

        <button id="mobile-menu-btn" class="lg:hidden text-white">
          <i class="fas fa-bars text-2xl"></i>
        </button>
      </div>

      <div id="mobile-menu" class="hidden lg:hidden mt-4 pb-4 space-y-4">
        <a href="#hero" class="block text-gray-300 hover:text-yellow-600 transition">Home</a>
        <a href="#about-us" class="block text-gray-300 hover:text-yellow-600 transition">About us</a>
        <a href="#services" class="block text-gray-300 hover:text-yellow-600 transition">Services</a>
        <a href="cm/login.php" class="block text-gray-300 hover:text-yellow-600 transition">Login</a>
        <a href="https://www.classicmanbarbershop.com.mx/index-es.php" target="_self"
          class="inline-block text-gray-300 hover:text-yellow-600 transition">
          <img src="./imgs/mex.png" alt="Spanish" class="h-8">
        </a>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section id="hero" class="hero-bg min-h-screen flex items-center pt-24">
    <div class="container mx-auto px-4">
      <div class="max-w-2xl fade-in">
        <h1 class="text-5xl md:text-7xl font-bold mb-6 text-white leading-tight">
          BARBERS & HAIR CUTTING
        </h1>
        <p class="text-xl md:text-2xl mb-8 text-gray-200">
          Welcome to Classicman Barbershop, where timeless craftsmanship meets modern style.
        </p>
        <a href="#services"
          class="inline-block bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-4 px-8 rounded-full transition transform hover:scale-105">
          EXPLORE SERVICES
        </a>
      </div>
    </div>
  </section>

  <!-- What We Do -->
  <section class="container mx-auto px-4 py-20 -mt-20 relative z-10">
    <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl shadow-2xl p-8 md:p-12 fade-in">
      <div class="grid md:grid-cols-2 gap-8 items-center">
        <div>
          <img src="./imgs/wwd.png" class="rounded-2xl w-full shadow-lg" alt="What We Do">
        </div>
        <div>
          <h2 class="text-4xl md:text-5xl font-bold mb-6 text-white border-l-4 border-yellow-600 pl-4">
            What we do?
          </h2>
          <p class="text-gray-300 text-lg mb-8">
            We blend classic barber craft with modern style to deliver impeccable grooming services.
          </p>
          <div class="grid md:grid-cols-2 gap-6">
            <div class="flex items-start space-x-4">
              <i class="fas fa-cut text-yellow-600 text-3xl"></i>
              <div>
                <h6 class="text-white font-semibold mb-2">Classic Cuts</h6>
                <p class="text-gray-400 text-sm">Classic cuts are characterized by their traditional nature. They are
                  not excessively short and have an elegant and unique style</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <i class="fas fa-layer-group text-yellow-600 text-3xl"></i>
              <div>
                <h6 class="text-white font-semibold mb-2">Faded Cuts</h6>
                <p class="text-gray-400 text-sm">Faded cuts stand out for being a current trend and have achieved great
                  success. Due to its modern style, suitable for all hair types and ages.</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <i class="fa-solid fa-face-meh-blank text-yellow-600 text-3xl"></i>
              <div>
                <h6 class="text-white font-semibold mb-2">Beard Arrangement</h6>
                <p class="text-gray-400 text-sm">Beards are common in many gentlemen, varying in abundance and
                  thickness. there are multiple styles, and choosing the right one depends on the shape of your beard
                  and face.</p>
              </div>
            </div>
            <div class="flex items-start space-x-4">
              <i class="fas fa-female text-yellow-600 text-3xl"></i>
              <div>
                <h6 class="text-white font-semibold mb-2">Women's Cuts</h6>
                <p class="text-gray-400 text-sm">Women's hairstyles define a lot of each one's personality. There are
                  too many cutting styles for every face and your taste. Change your look as you like.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Us -->
  <section id="about-us" class="py-20 bg-gradient-to-b from-black to-gray-900">
    <div class="container mx-auto px-4">
      <div class="grid lg:grid-cols-2 gap-12 items-center fade-in">
        <div>
          <h2 class="text-4xl md:text-5xl font-bold mb-6 border-l-4 border-yellow-600 pl-4">
            About Us
          </h2>
          <p class="text-gray-300 text-lg mb-4 leading-relaxed">
            At Classic Man, we merge <strong class="text-yellow-600">classic barbering</strong> with modern techniques
            to craft personalized grooming experiences.
          </p>
          <p class="text-gray-300 text-lg mb-6 leading-relaxed">
            Our skilled team ensures <em class="text-yellow-600">precision</em> and <em
              class="text-yellow-600">attention to detail</em> with every cut and shave.
          </p>

          <div class="mb-8">
            <p class="text-white font-semibold mb-3">Follow us on social media:</p>
            <div class="flex space-x-4">
              <a href="https://www.instagram.com/classicmanbarbershop/" target="_blank"
                class="bg-yellow-600 hover:bg-yellow-700 text-white w-12 h-12 rounded-full flex items-center justify-center transition transform hover:scale-110">
                <i class="fab fa-instagram text-xl"></i>
              </a>
              <a href="https://www.facebook.com/classicmanbarbershoppv" target="_blank"
                class="bg-yellow-600 hover:bg-yellow-700 text-white w-12 h-12 rounded-full flex items-center justify-center transition transform hover:scale-110">
                <i class="fab fa-facebook-f text-xl"></i>
              </a>
            </div>
          </div>

          <a href="#services"
            class="inline-block border-2 border-yellow-600 text-yellow-600 hover:bg-yellow-600 hover:text-white font-bold py-3 px-8 rounded-full transition">
            Discover Our Services
          </a>
        </div>
        <div class="fade-in">
          <img src="./imgs/about.webp" alt="Barbershop team at work" class="rounded-3xl shadow-2xl w-full">
        </div>
      </div>
    </div>
  </section>

  <!-- Services -->
  <section id="services" class="py-20 bg-black">
    <div class="container mx-auto px-4">
      <h2 class="text-4xl md:text-5xl font-bold text-center mb-12 text-white">OUR SERVICES</h2>

      <div class="flex flex-wrap justify-center gap-4 mb-12">
        <?php $firstBtn = true;
        foreach ($services as $category => $items):
          $catId = 'cat-' . generateId($category); ?>
          <button
            class="category-btn px-6 py-3 text-white font-semibold transition <?php echo $firstBtn ? 'active' : ''; ?>"
            data-target="<?php echo $catId; ?>">
            <?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
          </button>
          <?php $firstBtn = false; endforeach; ?>
      </div>

      <?php $firstSection = true;
      foreach ($services as $category => $items):
        $catId = 'cat-' . generateId($category);
        $displayStyle = $firstSection ? 'block' : 'none'; ?>
        <div class="service-content fade-in" id="<?php echo $catId; ?>" style="display: <?php echo $displayStyle; ?>;">

          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $item): ?>
              <div
                class="service-card bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-6 border border-gray-700 hover:border-yellow-600">
                <div class="flex justify-between items-start mb-4">
                  <h5 class="text-xl font-bold text-white">
                    <?= htmlspecialchars($item['nombre_en'], ENT_QUOTES, 'UTF-8') ?>
                  </h5>
                  <span class="bg-yellow-600 text-black font-bold px-4 py-1 rounded-full text-sm">
                    <?php if ($category === 'COLORING'): ?>
                      FROM: $<?= htmlspecialchars($item['precio_usd'], ENT_QUOTES, 'UTF-8') ?>
                    <?php else: ?>
                      $<?= htmlspecialchars($item['precio_usd'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                  </span>
                </div>
                <p class="text-gray-400">
                  <?= htmlspecialchars($item['descripcion_en'], ENT_QUOTES, 'UTF-8') ?>
                </p>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if ($category === 'COLORING'): ?>
            <div class="mt-8 bg-gray-800 rounded-xl p-6 border-l-4 border-yellow-600">
              <p class="text-gray-300">
                Prices vary depending on the specific product required. For more information or to schedule an
                appointment, please send us a message on WhatsApp.
              </p>
            </div>
          <?php endif; ?>

        </div>
        <?php $firstSection = false; endforeach; ?>
    </div>
  </section>

  <!-- Find Us -->
  <section id="find-us" class="py-20 bg-gradient-to-b from-black to-gray-900">
    <div class="container mx-auto px-4">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div class="text-center lg:text-left">
          <h2 class="text-4xl font-bold mb-8 text-white">Find Us</h2>
          <div class="space-y-4 text-gray-300 text-lg">
            <p class="flex items-start justify-center lg:justify-start">
              <i class="fas fa-map-marker-alt text-yellow-600 mr-3 mt-1"></i>
              <span>C. Popa, Marina Vallarta,<br>48335 Puerto Vallarta, Jal.</span>
            </p>
            <p class="flex items-start justify-center lg:justify-start">
              <i class="fas fa-clock text-yellow-600 mr-3 mt-1"></i>
              <span>Mon – Sat: 10 am – 8 pm<br>Sun: 10 am – 5 pm</span>
            </p>
          </div>
        </div>
        <div class="rounded-2xl overflow-hidden shadow-2xl">
          <a href="https://www.google.com/maps/search/?api=1&query=20.6674429,-105.2517405" target="_blank"
            class="block">
            <div class="aspect-video">
              <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3733.0525022855463!2d-105.25174052416541!3d20.66744290008934!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x842145e07aa25129%3A0x501e5863800c4687!2sClassic%20Man%20Barbershop!5e0!3m2!1ses-419!2smx!4v1747631880003!5m2!1ses-419!2smx"
                class="w-full h-full" style="border:0;" allowfullscreen loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
              </iframe>
            </div>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- WhatsApp Float -->
  <a href="https://api.whatsapp.com/send/?phone=523221628060&text&type=phone_number&app_absent=0"
    class="fixed bottom-6 right-6 z-50 bg-green-500 hover:bg-green-600 rounded-full p-4 shadow-2xl transition transform hover:scale-110"
    target="_blank" rel="noopener">
    <img src="./imgs/whats.webp" alt="WhatsApp" class="w-12 h-12">
  </a>

  <!-- Footer -->
  <footer class="bg-black border-t border-gray-800 py-6">
    <div class="container mx-auto px-4">
      <p class="text-center text-gray-400">&copy; <span id="year"></span> Classic Man Barbershop.</p>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.getElementById('year').innerText = new Date().getFullYear();

      const mobileMenuBtn = document.getElementById('mobile-menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
      });

      const faders = document.querySelectorAll('.fade-in');
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            e.target.classList.add('appear');
          }
        });
      }, { threshold: 0.1 });
      faders.forEach(f => observer.observe(f));

      const catButtons = document.querySelectorAll('.category-btn');
      const serviceContents = document.querySelectorAll('.service-content');
      catButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          catButtons.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          const tgt = btn.dataset.target;
          serviceContents.forEach(sec => sec.style.display = 'none');
          const el = document.getElementById(tgt);
          el.style.display = 'block';
        });
      });
    });
  </script>
</body>

</html>