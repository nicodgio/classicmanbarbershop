<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function actualizarPreciosUSD(PDO $pdo, float $tipoCambio): void
{
  $stmt = $pdo->prepare("SELECT id, precio_mxn FROM ProdSer WHERE activo = 1");
  $stmt->execute();
  $upd = $pdo->prepare("UPDATE ProdSer SET precio_usd = ? WHERE id = ?");
  while ($p = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $usd = ceil(($p['precio_mxn'] / $tipoCambio) * 2) / 2;
    $upd->execute([$usd, $p['id']]);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
  ini_set('display_errors', 0);
  error_reporting(0);
  ob_start();
  header('Content-Type: application/json');
  date_default_timezone_set('America/Mexico_City');
  require __DIR__ . '/../conn/conexionBD.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  try {
    $action = $_POST['action'] ?? '';
    $valorDolar = isset($_POST['dolar']) ? floatval($_POST['dolar']) : null;
    $valorCaja = isset($_POST['inicio_caja']) ? floatval($_POST['inicio_caja']) : null;
    $stmt = $pdo->prepare("SELECT id FROM dolar_hoy WHERE DATE(fecha_creacion)=CURDATE() LIMIT 1");
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_COLUMN);
    if ($action === 'dolar') {
      if ($existing) {
        $upd = $pdo->prepare("UPDATE dolar_hoy SET dolar = ?, fecha_creacion = NOW() WHERE id = ?");
        $upd->execute([$valorDolar, $existing]);
        $id = $existing;
      } else {
        $ins = $pdo->prepare("INSERT INTO dolar_hoy (dolar,inicio_caja,fecha_creacion) VALUES (?,NULL,NOW())");
        $ins->execute([$valorDolar]);
        $id = $pdo->lastInsertId();
      }
      actualizarPreciosUSD($pdo, $valorDolar);
    } elseif ($action === 'caja') {
      if ($existing) {
        $upd = $pdo->prepare("UPDATE dolar_hoy SET inicio_caja = ?, fecha_creacion = NOW() WHERE id = ?");
        $upd->execute([$valorCaja, $existing]);
        $id = $existing;
      } else {
        $ins = $pdo->prepare("INSERT INTO dolar_hoy (dolar,inicio_caja,fecha_creacion) VALUES (NULL,?,NOW())");
        $ins->execute([$valorCaja]);
        $id = $pdo->lastInsertId();
      }
    } else {
      ob_end_clean();
      echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
      exit;
    }
    $stmt = $pdo->prepare("SELECT id,dolar,inicio_caja,fecha_creacion FROM dolar_hoy WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $resp = [
      'status' => 'success',
      'id' => $row['id'],
      'dolar' => $row['dolar'] !== null ? number_format($row['dolar'], 2, '.', '') : null,
      'dolar_fmt' => $row['dolar'] !== null ? number_format($row['dolar'], 2) : null,
      'inicio_caja' => $row['inicio_caja'] !== null ? number_format($row['inicio_caja'], 2, '.', '') : null,
      'inicio_caja_fmt' => $row['inicio_caja'] !== null ? number_format($row['inicio_caja'], 2) : null,
      'fecha' => date('d/m/Y H:i:s', strtotime($row['fecha_creacion']))
    ];
    ob_end_clean();
    echo json_encode($resp);
    exit;
  } catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
  }
}

require 'dashboard.php';
date_default_timezone_set('America/Mexico_City');
$stmt = $pdo->prepare("
    SELECT id,dolar,inicio_caja,fecha_creacion
      FROM dolar_hoy
     WHERE DATE(fecha_creacion)=CURDATE()
     ORDER BY fecha_creacion DESC
     LIMIT 1
");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
  $row = ['id' => null, 'dolar' => null, 'inicio_caja' => null, 'fecha_creacion' => null];
}
?>
<div class="container mt-4">
  <div class="row gy-3">
    <!-- Card Dólar -->
    <div class="col-md-6">
      <div style="
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 1.5rem;
      ">
        <h5 style="
          font-weight: 600;
          color: #333333;
          margin-bottom: 1rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
        ">
          <span>Dólar hoy</span>
          <span id="cardDolarHeader" style="
            background: #e9ecef;
            color: #333333;
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
          ">
            <?= $row['dolar'] !== null ? number_format($row['dolar'], 2) . ' MXN' : 'No disponible' ?>
          </span>
        </h5>
        <div class="mb-3">
          <input type="number" step="0.01" id="dolarInput" value="<?= $row['dolar'] ?>" <?= $row['dolar'] !== null ? 'disabled' : '' ?> style="
              width: 100%;
              padding: 0.75rem;
              border: 1px solid #dddddd;
              border-radius: 4px;
              font-size: 1rem;
            ">
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
          <?php if ($row['dolar'] !== null): ?>
            <button id="editDolarBtn" style="
                background: #ffc107;
                color: #333333;
                border: none;
                border-radius: 4px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
              ">
              Editar
            </button>
            <button id="saveDolarBtn" class="d-none" style="
                background: #28a745;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
              ">
              Guardar
            </button>
          <?php else: ?>
            <button id="insertDolarBtn" style="
                width: 100%;
                background: #007bff;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
              ">
              Insertar
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Card Inicio de Caja -->
    <div class="col-md-6">
      <div style="
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 1.5rem;
      ">
        <h5 style="
          font-weight: 600;
          color: #333333;
          margin-bottom: 1rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
        ">
          <span>Inicio de caja</span>
          <span id="cardCajaHeader" style="
            background: #e9ecef;
            color: #333333;
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
          ">
            <?= $row['inicio_caja'] !== null ? number_format($row['inicio_caja'], 2) . ' MXN' : 'No disponible' ?>
          </span>
        </h5>
        <div class="mb-3">
          <input type="number" step="0.01" id="cajaInput" value="<?= $row['inicio_caja'] ?>"
            <?= $row['inicio_caja'] !== null ? 'disabled' : '' ?> style="
              width: 100%;
              padding: 0.75rem;
              border: 1px solid #dddddd;
              border-radius: 4px;
              font-size: 1rem;
            ">
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
          <?php if ($row['inicio_caja'] !== null): ?>
            <button id="editCajaBtn" style="
                background: #17a2b8;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
              ">
              Editar
            </button>
            <button id="saveCajaBtn" class="d-none" style="
                background: #28a745;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
              ">
              Guardar
            </button>
          <?php else: ?>
            <button id="insertCajaBtn" style="
                width: 100%;
                background: #6c757d;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
              ">
              Insertar
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const recordId = document.getElementById('recordId');
    const dolarInput = document.getElementById('dolarInput');
    const editDolarBtn = document.getElementById('editDolarBtn');
    const saveDolarBtn = document.getElementById('saveDolarBtn');
    const insertDolarBtn = document.getElementById('insertDolarBtn');
    const cardDolarHdr = document.getElementById('cardDolarHeader');
    const cajaInput = document.getElementById('cajaInput');
    const editCajaBtn = document.getElementById('editCajaBtn');
    const saveCajaBtn = document.getElementById('saveCajaBtn');
    const insertCajaBtn = document.getElementById('insertCajaBtn');
    const cardCajaHdr = document.getElementById('cardCajaHeader');
    function sendRequest(type) {
      const data = new FormData();
      data.append('ajax', '1');
      data.append('action', type);
      if (type === 'dolar') data.append('dolar', dolarInput.value);
      else data.append('inicio_caja', cajaInput.value);
      if (recordId) data.append('id', recordId.value);
      fetch('configuracion.php', { method: 'POST', body: data, credentials: 'same-origin' })
        .then(r => { if (!r.ok) throw r; return r.json() })
        .then(json => {
          if (json.status === 'success') {
            if (type === 'dolar') {
              dolarInput.value = json.dolar;
              cardDolarHdr.textContent = `Dólar hoy: ${json.dolar_fmt} MXN`;
              dolarInput.disabled = true;
              insertDolarBtn?.classList.add('d-none');
              saveDolarBtn?.classList.add('d-none');
              editDolarBtn?.classList.remove('d-none');
            } else {
              cajaInput.value = json.inicio_caja;
              cardCajaHdr.textContent = `Inicio de caja: ${json.inicio_caja_fmt} MXN`;
              cajaInput.disabled = true;
              insertCajaBtn?.classList.add('d-none');
              saveCajaBtn?.classList.add('d-none');
              editCajaBtn?.classList.remove('d-none');
            }
            if (recordId) recordId.value = json.id;
          } else alert(json.message);
        })
        .catch(e => { console.error(e); alert('Error de red'); });
    }
    editDolarBtn?.addEventListener('click', () => {
      dolarInput.disabled = false;
      editDolarBtn.classList.add('d-none');
      saveDolarBtn.classList.remove('d-none');
    });
    saveDolarBtn?.addEventListener('click', () => sendRequest('dolar'));
    insertDolarBtn?.addEventListener('click', () => sendRequest('dolar'));
    editCajaBtn?.addEventListener('click', () => {
      cajaInput.disabled = false;
      editCajaBtn.classList.add('d-none');
      saveCajaBtn.classList.remove('d-none');
    });
    saveCajaBtn?.addEventListener('click', () => sendRequest('caja'));
    insertCajaBtn?.addEventListener('click', () => sendRequest('caja'));
  });
</script>