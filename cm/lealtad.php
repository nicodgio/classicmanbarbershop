<?php
require_once 'dashboard.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM clientes_lealtad ORDER BY fecha_registro DESC";
$stmt = $pdo->query($sql);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Programa de Lealtad</title>
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

        .tabs-container {
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .nav-tabs {
            border-bottom: 2px solid #eceff1;
            padding: 0 1.5rem;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6b7280;
            padding: 1rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #b8860b;
        }

        .nav-tabs .nav-link.active {
            color: #b8860b;
            border-bottom: 2px solid #b8860b;
            margin-bottom: -2px;
        }

        .table th,
        .table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .table thead {
            background: #eceff1;
        }

        .progress {
            height: 8px;
            background: #e9ecef;
        }

        .progress-bar {
            background: linear-gradient(90deg, #b8860b 0%, #d4a227 100%);
        }

        .badge-visits {
            background: #fff3cd;
            color: #856404;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .badge-free {
            background: #d1fae5;
            color: #065f46;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .btn-action {
            padding: 0.25rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 4px;
        }

        .client-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .client-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .client-name {
            font-size: 1.1rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .client-info {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .stats-row {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }

        .stat-item {
            flex: 1;
        }

        .stat-label {
            color: #9ca3af;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #b8860b;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .input-group-text {
            background: white;
            border-right: none;
            color: #6b7280;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group .form-control:focus {
            border-color: #dee2e6;
            box-shadow: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: #b8860b;
        }

        .input-group:focus-within .form-control {
            border-color: #b8860b;
        }
    </style>
</head>

<body>
    <div class="container mt-4 px-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Programa de Lealtad</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                <i class="fas fa-user-plus me-2"></i>Registrar Cliente
            </button>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Buscar por nombre o teléfono...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select id="filterSelect" class="form-select">
                            <option value="todos">Todos los clientes</option>
                            <option value="proximos">Próximos a corte gratis (8-9 visitas)</option>
                            <option value="disponibles">Con corte gratis disponible</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="tabs-container">
            <ul class="nav nav-tabs" id="lealtadTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="clientes-tab" data-bs-toggle="tab" data-bs-target="#clientes"
                        type="button">
                        <i class="fas fa-users me-2"></i>Clientes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="visitas-tab" data-bs-toggle="tab" data-bs-target="#visitas"
                        type="button">
                        <i class="fas fa-history me-2"></i>Historial de Visitas
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4">
                <div class="tab-pane fade show active" id="clientes" role="tabpanel">
                    <?php if (count($clientes) > 0): ?>
                        <div class="row">
                            <?php foreach ($clientes as $cliente):
                                $visitas = (int) $cliente['visitas'];
                                $visitasGratis = (int) $cliente['visitas_gratis'];
                                $visitasRequeridas = 10;
                                $progreso = min(($visitas % $visitasRequeridas) / $visitasRequeridas * 100, 100);
                                ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="client-card">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <div class="client-name"><?= htmlspecialchars($cliente['nombre']) ?></div>
                                                <div class="client-info">
                                                    <i
                                                        class="fas fa-phone me-2"></i><?= htmlspecialchars($cliente['telefono']) ?>
                                                </div>
                                                <div class="client-info">
                                                    <i class="fas fa-calendar me-2"></i>Miembro desde:
                                                    <?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="#"
                                                            onclick="registrarVisita(<?= $cliente['id'] ?>)">
                                                            <i class="fas fa-plus me-2"></i>Registrar Visita
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#"
                                                            onclick="canjearGratuito(<?= $cliente['id'] ?>)">
                                                            <i class="fas fa-gift me-2"></i>Canjear Gratuito
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#"
                                                            onclick="eliminarCliente(<?= $cliente['id'] ?>)">
                                                            <i class="fas fa-trash me-2"></i>Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <small class="text-muted">Progreso hacia corte gratis</small>
                                                <small
                                                    class="text-muted"><?= $visitas % $visitasRequeridas ?>/<?= $visitasRequeridas ?></small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?= $progreso ?>%">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="stats-row">
                                            <div class="stat-item">
                                                <div class="stat-label">Visitas Totales</div>
                                                <div class="stat-value"><?= $visitas ?></div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-label">Cortes Gratis</div>
                                                <div class="stat-value"><?= $visitasGratis ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No hay clientes registrados en el programa de lealtad</p>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addClientModal">
                                <i class="fas fa-user-plus me-2"></i>Registrar Primer Cliente
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="visitas" role="tabpanel">
                    <?php
                    $sqlVisitas = "SELECT v.*, c.nombre, c.telefono 
                         FROM visitas_lealtad v 
                         INNER JOIN clientes_lealtad c ON v.cliente_id = c.id 
                         ORDER BY v.fecha_visita DESC 
                         LIMIT 50";
                    $stmtVisitas = $pdo->query($sqlVisitas);
                    $visitas = $stmtVisitas->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (count($visitas) > 0): ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($visitas as $visita): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($visita['nombre']) ?></td>
                                        <td><?= htmlspecialchars($visita['telefono']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($visita['fecha_visita'])) ?></td>
                                        <td>
                                            <?php if ($visita['es_gratuito']): ?>
                                                <span class="badge-free">
                                                    <i class="fas fa-gift me-1"></i>Gratuito
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-visits">
                                                    <i class="fas fa-check me-1"></i>Regular
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($visita['notas'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <p>No hay visitas registradas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Cliente -->
    <div class="modal fade" id="addClientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addClientForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            El cliente recibirá un corte gratis cada 10 visitas.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('addClientForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('lealtad/add_cliente.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => alert('Error en el servidor'));
        });

        const searchInput = document.getElementById('searchInput');
        const filterSelect = document.getElementById('filterSelect');
        const clientCards = document.querySelectorAll('.client-card');

        function filterClients() {
            const searchTerm = searchInput.value.toLowerCase();
            const filterValue = filterSelect.value;
            let visibleCount = 0;

            clientCards.forEach(card => {
                const nombre = card.querySelector('.client-name').textContent.toLowerCase();
                const telefono = card.querySelector('.client-info').textContent.toLowerCase();
                const visitas = parseInt(card.querySelector('.stat-value').textContent);
                const visitasMod = visitas % 10;

                let matchSearch = nombre.includes(searchTerm) || telefono.includes(searchTerm);
                let matchFilter = true;

                if (filterValue === 'proximos') {
                    matchFilter = visitasMod >= 8 && visitasMod < 10;
                } else if (filterValue === 'disponibles') {
                    matchFilter = visitasMod === 0 && visitas >= 10;
                }

                if (matchSearch && matchFilter) {
                    card.parentElement.style.display = '';
                    visibleCount++;
                } else {
                    card.parentElement.style.display = 'none';
                }
            });

            let emptyState = document.getElementById('searchEmptyState');

            if (visibleCount === 0) {
                if (!emptyState) {
                    emptyState = document.createElement('div');
                    emptyState.id = 'searchEmptyState';
                    emptyState.className = 'col-12';
                    emptyState.innerHTML = '<div class="empty-state"><i class="fas fa-search"></i><p>No se encontraron clientes con esos criterios</p></div>';
                    document.querySelector('#clientes .row').appendChild(emptyState);
                }
                emptyState.style.display = '';
            } else {
                if (emptyState) {
                    emptyState.style.display = 'none';
                }
            }
        }
        if (searchInput) {
            searchInput.addEventListener('input', filterClients);
            filterSelect.addEventListener('change', filterClients);
        }

        function registrarVisita(clienteId) {
            if (confirm('¿Registrar una nueva visita para este cliente?')) {
                fetch('lealtad/registrar_visita.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cliente_id: clienteId, es_gratuito: false })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (data.corte_gratis_disponible) {
                                alert('¡El cliente ha alcanzado 10 visitas! Próximo corte es GRATIS.');
                            }
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(() => alert('Error en el servidor'));
            }
        }

        function canjearGratuito(clienteId) {
            if (confirm('¿Canjear un corte gratuito para este cliente?')) {
                fetch('lealtad/registrar_visita.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cliente_id: clienteId, es_gratuito: true })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            alert('Corte gratuito canjeado exitosamente');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(() => alert('Error en el servidor'));
            }
        }

        function eliminarCliente(clienteId) {
            if (confirm('¿Está seguro de eliminar este cliente? Se perderá todo su historial.')) {
                fetch('lealtad/delete_cliente.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: clienteId })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(() => alert('Error en el servidor'));
            }
        }
    </script>
</body>

</html>