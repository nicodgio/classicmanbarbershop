<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'conn/conexionBD.php';
$query = "SELECT `id`, `nombre`, `descripcion`, `categoria`, `precio_mxn`
          FROM `ProdSer`
          WHERE activo = 1";
$result = $pdo->query($query);
$categoryMapping = array(
    "SERVICIOS" => "SERVICIOS",
    "PAQUETES" => "PAQUETES",
    "OTROS SERVICIOS" => "OTROS SERVICIOS",
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        @font-face {
            font-family: 'AnthonyItalic';
            src: url('font/Anthony_Italic.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        .brand-text .script {
            font-family: 'AnthonyItalic', cursive;
            font-size: 2rem;
        }

        .brand-text .sans {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #e0e0e0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            margin: 0;
            padding: 0;
            color: #e0e0e0;
        }

        .navbar {
            background-color: #000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .navbar-brand,
        .nav-link {
            color: #e0e0e0 !important;
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.1);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255,1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        .hero {
            background: url('./imgs/banner.png') center center no-repeat;
            background-size: cover;
            height: 90vh;
            position: relative;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.65);
        }

        .hero-text {
            position: absolute;
            top: 50%;
            left: 100px;
            transform: translateY(-50%);
            max-width: 600px;
            color: #ffffff;
            text-align: left;
        }

        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .hero-text p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .hero-text .btn {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            border: none;
            transition: background-color 0.3s, transform 0.3s;
            background-color: #b8860b;
            color: #e0e0e0;
        }

        .hero-text .btn:hover {
            transform: scale(1.05);
            background-color: #9a7d0d;
        }

        @media (max-width: 768px) {
            .hero {
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
            }

            .hero-text {
                left: 20px !important;
                max-width: 600px;
                margin-right: 20px;
                top: 35% !important;
            }
        }

        #whatwedo {
            margin-top: 0;
            padding-bottom: 50px;
        }

        .overlap-card {
            margin-top: -100px;
            position: relative;
            z-index: 2;
        }

        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .fade-in.appear {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 992px) {
            .overlap-card {
                margin-top: -50px;
            }
        }

        @media (max-width: 576px) {
            .overlap-card {
                margin-top: 0;
            }
        }

        @keyframes fadeInAnimation {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInAnimation 0.6s ease-out;
        }

        .whatsapp-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .whatsapp-float img {
            width: 50px;
            height: 50px;
            cursor: pointer;
        }

        @media (max-width: 768px) {

            .overlap-card.fade-in,
            .overlap-card.fade-in-up {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="./imgs/classic.webp" alt="Logo" height="75" class="me-2">
                <div class="brand-text">
                    <span class="script">Classic Man</span>
                    <span class="sans">Barbershop</span>
                </div>
            </a>

            <div class="d-flex align-items-center">
                <a href="index.php" target="_self" class="me-2 d-lg-none">
                    <img src="./imgs/eu.png" alt="EU Icon" height="30">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Alternar navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#hero">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about-us">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link" href="cm/login.php"><i class="fas fa-user"></i></a></li>
                    <li class="nav-item d-none d-lg-block">
                        <a class="nav-link p-0 ms-2" href="https://www.classicmanbarbershop.com.mx" target="_self">
                            <img src="./imgs/eu.png" alt="EU Icon" height="30">
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="hero" class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-text fade-in">
            <h1 class="display-3">Barbería y Corte de Cabello</h1>
            <p>Bienvenido a Classicman Barbershop, donde la artesanía atemporal se une al estilo moderno.</p>
            <a href="#book-appointment" class="btn">RESERVAR AHORA</a>
        </div>
    </header>

    <section id="whatwedo" class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card overlap-card shadow-lg p-5 mx-auto fade-in"
                    style="max-width: 1100px; background-color: #1f1f1f; border: none;">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-5">
                            <img src="./imgs/wwd.png" class="img-fluid rounded" alt="What We Do">
                        </div>
                        <div class="col-md-7">
                            <div class="card-body">
                                <h2 class="display-6 fw-semibold mb-3 text-white ps-3">
                                    ¿Qué hacemos?
                                </h2>
                                <p class="card-text text-secondary" style="font-size: 1.2rem;">
                                    Combinamos la artesanía clásica de barbería con estilo moderno para ofrecer
                                    servicios de aseo impecables.
                                </p>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-cut text-warning me-3" style="font-size: 2.2rem;"></i>
                                            <div>
                                                <h6 class="text-white mb-1">Cortes clásicos</h6>
                                                <small class="text-white-50">
                                                    Estilos tradicionales, no muy cortos, con un acabado elegante y
                                                    distintivo.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-layer-group text-warning me-3"
                                                style="font-size: 2.2rem;"></i>
                                            <div>
                                                <h6 class="text-white mb-1">Degradados</h6>
                                                <small class="text-white-50">
                                                    Tendencia moderna adecuada para todo tipo de cabello y edades.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="d-flex align-items-start">
                                            <i class="fa-solid fa-face-meh-blank text-warning me-3"
                                                style="font-size: 2.2rem;"></i>
                                            <div>
                                                <h6 class="text-white mb-1">Arreglo de barba</h6>
                                                <small class="text-white-50">
                                                    Estilos variados según la forma del rostro y el tipo de barba.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-female text-warning me-3" style="font-size: 2.2rem;"></i>
                                            <div>
                                                <h6 class="text-white mb-1">Cortes para dama</h6>
                                                <small class="text-white-50">
                                                    Variedad de estilos para reflejar tu personalidad.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.card-body -->
                        </div><!-- /.col-md-7 -->
                    </div><!-- /.row g-0 -->
                </div><!-- /.card -->
            </div><!-- /.col-12 -->
        </div><!-- /.row -->
    </section>

    <section id="about-us" class="py-5">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6">
                    <h2 class="display-6 fw-semibold mb-3 border-start border-3 border-warning ps-3">
                        Sobre nosotros
                    </h2>
                    <p class="mb-3 lh-lg">
                        En Classic Man fusionamos <strong>barbería clásica</strong> con técnicas modernas para crear
                        experiencias
                        de aseo personalizadas.
                    </p>
                    <p class="mb-4 lh-lg">
                        Nuestro equipo garantiza <em>precisión</em> y <em>detalles cuidados</em> en cada corte y
                        afeitado.
                    </p>

                    <p class="mb-4">
                        <strong>Síguenos en redes sociales:</strong>
                        <a href="https://www.instagram.com/classicmanbarbershop/" target="_blank"
                            class="text-warning text-decoration-none">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="https://www.facebook.com/classicmanbarbershoppv" target="_blank"
                            class="text-warning text-decoration-none">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                    </p>

                    <a href="#our-services" class="btn btn-outline-warning px-4">Descubre nuestros servicios</a>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="./imgs/about.webp" alt="Barbershop team at work" class="img-fluid rounded-3 shadow-sm">
                </div>
            </div>
        </div>
    </section>

    <section id="our-services" class="container my-5">
        <h2 class="text-center text-white mb-4">NUESTROS SERVICIOS</h2>
        <div class="d-flex justify-content-center mb-4">
            <?php $firstBtn = true;
            foreach ($services as $category => $items):
                $catId = generateId($category); ?>
                <button class="btn btn-outline-light mx-2 service-btn <?= $firstBtn ? 'active' : '' ?>"
                    data-target="<?= $catId ?>">
                    <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
                </button>
                <?php $firstBtn = false; endforeach; ?>
        </div>

        <?php $firstSection = true;
        foreach ($services as $category => $items):
            $catId = generateId($category);
            $displayStyle = $firstSection ? 'block' : 'none'; ?>
            <div class="service-content fade-in-up" id="<?= $catId ?>" style="display: <?= $displayStyle ?>;">

                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($items as $item): ?>
                        <div class="col">
                            <div class="card bg-dark text-light h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </h5>
                                        <span class="badge bg-warning text-dark">
                                            <?php if ($category === 'OTROS SERVICIOS'): ?>
                                                DESDE: $<?= htmlspecialchars($item['precio_mxn'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php else: ?>
                                                $<?= htmlspecialchars($item['precio_mxn'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <p class="card-text mt-2">
                                        <?= htmlspecialchars($item['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($category === 'OTROS SERVICIOS'): ?>
                    <!-- Nota específica para OTROS SERVICIOS -->
                    <div class="mt-4 p-3 bg-secondary text-light rounded">
                        <p class="mb-0">
                            Los precios varían según el producto requerido. Para más información o para agendar una cita,
                            envíanos un mensaje por WhatsApp.
                        </p>
                    </div>
                <?php endif; ?>

            </div>
            <?php $firstSection = false; endforeach; ?>
    </section>



    <section id="book-appointment" class="container my-5">
        <h2 class="text-center text-white mb-4">Reservar cita</h2>

        <div class="text-center mb-4">
            <button id="show-book-btn" class="btn btn-warning btn-lg">Reservar ahora</button>
        </div>

        <div id="book-card" style="display: none;">
            <form id="bookForm" class="row g-3">
                <div class="col-md-4">
                    <label for="category-select" class="form-label text-white">Categoría</label>
                    <select id="category-select" class="form-select" required>
                        <option value="" disabled selected>Selecciona categoría</option>
                        <?php foreach (array_keys($services) as $cat): ?>
                            <?php if ($cat === 'OTROS SERVICIOS' || $cat === 'PRODUCTOS')
                                continue; ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>">
                                <?= htmlspecialchars($cat, ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="servicio_id" class="form-label text-white">Servicio</label>
                    <select id="servicio_id" name="servicio_id" class="form-select" disabled required>
                        <option value="" disabled selected>Selecciona servicio</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="fecha" class="form-label text-white">Fecha</label>
                    <input type="text" id="fecha" name="fecha" class="form-control" placeholder="AAAA-MM-DD"
                        autocomplete="off" disabled required>
                </div>
                <div class="col-md-4">
                    <label for="hora_inicio" class="form-label text-white">Hora</label>
                    <select id="hora_inicio" name="hora_inicio" class="form-select" disabled required>
                        <option value="" disabled selected>Selecciona hora</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="nombre_cliente" class="form-label text-white">Nombre</label>
                    <input type="text" id="nombre_cliente" name="nombre" class="form-control" placeholder="Tu nombre"
                        disabled required>
                </div>
                <div class="col-md-4">
                    <label for="telefono" class="form-label text-white">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" placeholder="Tu teléfono"
                        disabled required>
                </div>
                <div class="col-12">
                    <label for="notas" class="form-label text-white">Notas (opcional)</label>
                    <textarea id="notas" name="notas" class="form-control" rows="2" placeholder="Alguna nota..."
                        disabled></textarea>
                </div>
                <input type="hidden" name="estado" value="2">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-warning btn-lg" disabled>Reservar cita</button>
                </div>
            </form>

            <div class="modal fade" id="bookingSuccessModal" tabindex="-1" aria-labelledby="bookingSuccessModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark text-light">
                        <div class="modal-header border-0">
                            <h5 class="modal-title" id="bookingSuccessModalLabel">¡Cita confirmada!</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body text-center">
                            <p>Tu cita a las <span id="successTime"></span> ha sido confirmada con éxito.</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center">
                            <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="booking-message" class="mt-3 text-center"></div>
        </div>
    </section>

    <section id="find-us" class="bg-dark text-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                    <h2 class="h3 mb-4">Encuéntranos</h2>
                    <p class="mb-2">
                        <i class="fas fa-map-marker-alt text-warning me-2"></i>
                        C. Popa, Marina Vallarta,
                    </p>
                    <p class="mb-2 ps-4">48335 Puerto Vallarta, Jal.</p>
                    <p class="mb-2">
                        <i class="fas fa-clock text-warning me-2"></i>
                        Lun – Sáb: 10 am – 8 pm
                    </p>
                    <p class="mb-0 ps-4">Dom: 10 am – 5 pm</p>
                </div>
                <div class="col-md-6">
                    <a href="https://www.google.com/maps/search/?api=1&query=20.6674429,-105.2517405" target="_blank"
                        class="d-block">
                        <div class="ratio ratio-16x9">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3733.0525022855463!2d-105.25174052416541!3d20.66744290008934!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x842145e07aa25129%3A0x501e5863800c4687!2sClassic%20Man%20Barbershop!5e0!3m2!1ses-419!2smx!4v1747631880003!5m2!1ses-419!2smx"
                                style="border:0;" allowfullscreen loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <a href="https://api.whatsapp.com/send/?phone=523221628060&text&type=phone_number&app_absent=0"
        class="whatsapp-float" target="_blank" rel="noopener">
        <img src="./imgs/whats.webp" alt="WhatsApp">
    </a>

    <footer class="bg-dark text-light">
        <div class="container">
            <p class="text-center">&copy; <span id="year"></span> Classic Man Barbershop.</p>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const showBtn = document.getElementById('show-book-btn');
            const bookCard = document.getElementById('book-card');
            bookCard.style.display = 'none';

            showBtn.addEventListener('click', () => {
                bookCard.style.display = 'block';
                showBtn.style.display = 'none';
            });
            // 1. Actualiza año del footer
            document.getElementById('year').innerText = new Date().getFullYear();

            // 2. Datos y selectores comunes
            const servicesData = <?= json_encode($services, JSON_UNESCAPED_UNICODE) ?>;
            delete servicesData['PRODUCTOS'];
            const catButtons = document.querySelectorAll('.service-btn');
            const serviceContents = document.querySelectorAll('.service-content');
            const faders = document.querySelectorAll('.fade-in');

            // 3. Intersect Observer para fade-in
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('appear');
                        obs.unobserve(e.target);
                    }
                });
            }, { threshold: 0.5, rootMargin: '0px 0px -20px 0px' });
            faders.forEach(f => observer.observe(f));

            // 4. Cambio de pestañas de servicios
            catButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    catButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const tgt = btn.dataset.target;
                    serviceContents.forEach(sec => sec.style.display = 'none');
                    const el = document.getElementById(tgt);
                    el.style.display = 'block';
                    // reactivar animación
                    el.classList.remove('fade-in-up');
                    void el.offsetWidth;
                    el.classList.add('fade-in-up');
                });
            });

            // 5. Variables del formulario de reserva
            const catSel = document.getElementById('category-select');
            const svcSel = document.getElementById('servicio_id');
            const fechaInput = document.getElementById('fecha');
            const horaSel = document.getElementById('hora_inicio');
            const nombreInput = document.getElementById('nombre_cliente');
            const telefonoInput = document.getElementById('telefono');
            const notasInput = document.getElementById('notas');
            const submitBtn = document.querySelector('#bookForm button[type="submit"]');
            const msgContainer = document.getElementById('booking-message');

            // 6. Al cambiar categoría, puebla servicios y habilita campos
            catSel.addEventListener('change', function () {
                svcSel.innerHTML = '<option disabled selected>Seleccione servicio</option>';
                (servicesData[this.value] || []).forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.text = item.nombre;
                    svcSel.appendChild(opt);
                });
                svcSel.disabled = false;
                [fechaInput, nombreInput, telefonoInput, notasInput, submitBtn]
                    .forEach(el => el.disabled = false);
            });

            flatpickr(fechaInput, {
                locale: 'es',
                minDate: 'today',
                dateFormat: 'Y-m-d',
                disableMobile: true,
                onChange: (_, dateStr) => {
                    if (!dateStr) return;
                    const today = new Date();
                    const selected = new Date(dateStr + 'T00:00:00');
                    const isToday = selected.toDateString() === today.toDateString();
                    const currentH = today.getHours();
                    const currentM = today.getMinutes();

                    fetch(`cm/citas/list_citas.php?fecha=${encodeURIComponent(dateStr)}`)
                        .then(r => r.json())
                        .then(citasList => {
                            // encabezado del select
                            horaSel.innerHTML = '<option disabled selected>Seleccione hora</option>';

                            const pad = n => String(n).padStart(2, '0');
                            const maxBarberos = 2;
                            const duration = 60;

                            for (let h = 10; h < 19; h++) {
                                for (let m = 0; m < 60; m += 15) {
                                    if (isToday && (h < currentH || (h === currentH && m <= currentM))) {
                                        continue;
                                    }
                                    const slot = `${pad(h)}:${pad(m)}`;

                                    const dtEnd = new Date();
                                    dtEnd.setHours(h, m + duration);
                                    const endSlot = `${pad(dtEnd.getHours())}:${pad(dtEnd.getMinutes())}`;

                                    const overlaps = citasList.filter(c =>
                                        c.inicio < endSlot && c.fin > slot
                                    ).length;

                                    if (overlaps >= maxBarberos) continue;

                                    const display = `${h % 12 || 12}:${pad(m)} ${h < 12 ? 'am' : 'pm'}`;
                                    horaSel.insertAdjacentHTML(
                                        'beforeend',
                                        `<option value="${slot}">${display}</option>`
                                    );
                                }
                            }

                            horaSel.disabled = false;
                        });
                }
            });


            document.getElementById('bookForm').addEventListener('submit', function (e) {
                e.preventDefault();
                submitBtn.disabled = true;
                msgContainer.innerHTML = '';

                fetch('cm/citas/save_cita.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                    .then(r => r.json())
                    .then(json => {
                        if (!json.success) {
                            msgContainer.innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
                            submitBtn.disabled = false;
                        } else {
                            document.getElementById('successTime')
                                .innerText = json.data.hora_inicio.slice(0, 5);
                            new bootstrap.Modal(document.getElementById('bookingSuccessModal')).show();
                            this.reset();
                            // re-bloquea campos
                            [svcSel, fechaInput, horaSel, nombreInput, telefonoInput, notasInput, submitBtn]
                                .forEach(el => el.disabled = true);
                        }
                    })
                    .catch(() => {
                        msgContainer.innerHTML = `<div class="alert alert-danger">Error en el servidor.</div>`;
                        submitBtn.disabled = false;
                    });
            });
        });
    </script>
</body>

</html>