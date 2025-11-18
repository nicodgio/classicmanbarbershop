<?php
require_once 'dashboard.php';
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

// Obtener tasa de cambio de hoy
$stmt_rate = $pdo->prepare(
  "SELECT dolar, fecha_creacion
     FROM dolar_hoy
    WHERE DATE(fecha_creacion) = CURDATE()
    LIMIT 1"
);
$stmt_rate->execute();
$rate_data = $stmt_rate->fetch(PDO::FETCH_ASSOC);

$dolarHoy = $rate_data ? (float) $rate_data['dolar'] : 0;

$sql = "SELECT id, nombre, categoria, precio_usd, precio_mxn
        FROM ProdSer WHERE activo = 1 ORDER BY id, categoria, nombre";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categorias_array = [];
foreach ($productos as $p) {
  $categorias_array[$p['categoria']][] = $p;
}
$sql_emp = "SELECT id, nombre FROM Empleados WHERE activo = 1";
$stmt_emp = $pdo->query($sql_emp);
$empleados = $stmt_emp->fetchAll(PDO::FETCH_ASSOC);
$currentUser = htmlspecialchars($_SESSION['user']['username']);
?>
<div class="container">
  <div class="mt-4 px-3 mb-3">
    <div class="d-flex flex-column flex-md-row" style="height: calc(100vh - 116px);">
      <div class="card me-md-3 mb-3 mb-md-0" style="flex:0 0 30%; display:flex; flex-direction:column;">
        <div class="card-body overflow-auto">
          <h5 class="card-title">Carrito</h5>
          <ul id="cart-items" class="list-group"></ul>
        </div>
        <div class="card-footer">
          <div class="d-flex align-items-center mb-3 flex-nowrap">
            <label for="discountInput" class="small mb-0" style="white-space:nowrap; flex:0 0 auto;">Aplicar descuento
              %</label>
            <input type="number" id="discountInput" class="form-control flex-grow-1 ms-2" style="min-width:0;" min="0"
              max="100" value="0">
          </div>
          <hr class="my-2">
          <div class="d-flex justify-content-between mb-2">
            <strong>Total:</strong><span>$<span id="cart-total">0.00</span></span>
          </div>
          <div class="d-flex">
            <button id="btn-clear" class="btn btn-outline-danger flex-fill me-2">Limpiar</button>
            <button id="btn-checkout" class="btn btn-success flex-fill">Cobrar</button>
          </div>
        </div>
      </div>
      <div class="d-flex flex-column" style="flex:1;">
        <div class="card mb-3">
          <div class="card-body d-flex flex-wrap align-content-center justify-content-center">
            <?php foreach (array_keys($categorias_array) as $cat): ?>
              <div class="card m-2 p-3 text-center category-card" data-category="<?= htmlspecialchars($cat) ?>"
                style="flex:1 0 auto; min-width:100px; cursor:pointer;">
                <?= htmlspecialchars($cat) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card flex-grow-1 d-flex flex-column" style="overflow:hidden;">
          <div class="card-body flex-grow-1 overflow-auto">
            <h5 id="products-title" class="card-title">Seleccione categoría</h5>
            <div id="products-list" class="d-flex flex-wrap"></div>
          </div>
          <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="form-check form-switch d-flex align-items-center mb-0">
              <span>🇲🇽</span>
              <input class="form-check-input mx-2" type="checkbox" id="currencyToggle">
              <span>🇺🇸</span>
            </div>
            <select id="employeeSelect" class="form-select w-auto mb-0">
              <?php foreach ($empleados as $emp): ?>
                <option value="<?= htmlspecialchars($emp['nombre']) ?>" <?= $emp['nombre'] === $currentUser ? ' selected' : '' ?>>
                  <?= htmlspecialchars($emp['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para precio en OTROS SERVICIOS -->
<div class="modal fade" id="modalPrecio" tabindex="-1" aria-labelledby="modalPrecioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPrecioLabel">Precio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="inputPrecioPersonalizado" class="form-label">Ingrese el precio</label>
          <input type="number" step="0.01" class="form-control" id="inputPrecioPersonalizado" placeholder="0.00">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarPrecio">Guardar precio</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal de pago -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Resumen de pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="modalCurrencyInfo" class="mb-3">
          <strong>Moneda:</strong> <span id="modalCurrencyLabel"></span>
        </div>
        <div class="mb-3" id="paymentMethodGroup">
          <label for="paymentMethodSelect" class="form-label">Método de pago</label>
          <select id="paymentMethodSelect" class="form-select">
            <option value="EFECTIVO">EFECTIVO</option>
            <option value="TARJETA">TARJETA</option>
          </select>
        </div>
        <div class="mb-3" id="paidAmountGroup">
          <label for="paidAmountInput" class="form-label">Pago con</label>
          <input type="number" step="0.01" id="paidAmountInput" class="form-control">
          <div class="form-text">Ingresa la cantidad con la que paga el cliente.</div>
        </div>
        <div class="mb-3" id="changeInfo" style="display:none;">
          <strong>Cambio:</strong> <span id="changeLabel">0.00</span>
        </div>
        <hr>
        <div class="mb-3">
          <label for="tipAmountInput" class="form-label">Propina (opcional)</label>
          <input type="number" step="0.01" id="tipAmountInput" class="form-control">
        </div>
        <div class="mb-3">
          <label for="tipMethodSelect" class="form-label">Método de propina</label>
          <select id="tipMethodSelect" class="form-select">
            <option value="">— Ninguno —</option>
            <option value="DOLARES">DÓLARES</option>
            <option value="EFECTIVO">EFECTIVO</option>
            <option value="TARJETA">TARJETA</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="modalConfirmBtn" class="btn btn-primary">Confirmar pago</button>
      </div>
    </div>
  </div>
</div>
<script>
  const dolarRate = <?= json_encode($dolarHoy) ?>;
  const prodData = <?= json_encode($categorias_array, JSON_HEX_TAG) ?>;
  const modalPrecio = new bootstrap.Modal(document.getElementById('modalPrecio'));
  let cart = [], selectedCurrency = 'mxn', currentCat = '';

  // 1 sola vez: inicializa el modal
  const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));

  function roundToHalf(num) {
    return Math.round(num * 2) / 2;
  }

  function formatPrice(n) {
    return parseFloat(n).toFixed(2);
  }

  function renderProducts(cat) {
    currentCat = cat;
    const cont = document.getElementById('products-list'),
      title = document.getElementById('products-title');
    cont.innerHTML = '';
    title.textContent = cat;

    (prodData[cat] || []).forEach(p => {
      const price = selectedCurrency === 'usd' ? p.precio_usd : p.precio_mxn;
      const card = document.createElement('div');
      card.className = 'card m-2 product-card';
      card.style.flex = '0 0 calc(33.33% - 1rem)';
      card.style.cursor = 'pointer';
      card.innerHTML = `
      <div class="card-body text-center">
        <h5 class="card-title">${p.nombre}</h5>
        <p class="card-text mb-2">$${formatPrice(price)}</p>
      </div>`;

      card.onclick = () => {
        if (cat === 'OTROS SERVICIOS') {
          modalPrecio.show();
          document.getElementById('btnGuardarPrecio').onclick = () => {
            const custom = parseFloat(
              document.getElementById('inputPrecioPersonalizado').value
            );
            if (isNaN(custom) || custom <= 0) {
              alert('Ingresa un precio válido.');
              return;
            }
            modalPrecio.hide();
            const customMxn = custom;
            const customUsd = customMxn / dolarRate;
            addToCart(p.nombre, customMxn, customUsd, cat);
          };
        } else {
          addToCart(p.nombre, p.precio_mxn, p.precio_usd, cat);
        }
      };

      cont.appendChild(card);
    });

    updateCartUI();
  }

  function addToCart(name, mxn, usd, cat) {
    cart.push({ name, precio_mxn: mxn, precio_usd: usd, categoria: cat });
    updateCartUI();
  }

  function updateCartUI() {
    const list = document.getElementById('cart-items'),
      totalEl = document.getElementById('cart-total'),
      discount = parseFloat(document.getElementById('discountInput').value) || 0;
    list.innerHTML = '';
    let sum = 0;
    cart.forEach((item, i) => {
      const price = selectedCurrency === 'usd' ? item.precio_usd : item.precio_mxn;
      sum += parseFloat(price);
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center';
      li.innerHTML = `
        <div>
          <strong>${item.name}</strong><br/><small>${item.categoria}</small>
        </div>
        <div>
          $${formatPrice(price)}
          <i class="fa fa-times text-danger ms-2" style="cursor:pointer" onclick="removeItem(${i})"></i>
        </div>`;
      list.appendChild(li);
    });
    totalEl.textContent = formatPrice(sum * (1 - discount / 100));
  }

  function removeItem(i) {
    cart.splice(i, 1);
    updateCartUI();
  }

  document.getElementById('currencyToggle').addEventListener('change', e => {
    selectedCurrency = e.target.checked ? 'usd' : 'mxn';
    if (currentCat) renderProducts(currentCat);
  });

  document.getElementById('discountInput').addEventListener('input', updateCartUI);

  document.getElementById('btn-clear').onclick = () => {
    if (confirm('¿Limpiar toda la cuenta?')) {
      cart = [];
      updateCartUI();
    }
  };

  // Al hacer click en Cobrar abrimos el modal
  document.getElementById('btn-checkout').onclick = () => {
    if (!cart.length) { alert('El carrito está vacío.'); return; }
    document.getElementById('modalCurrencyLabel').textContent = selectedCurrency === 'usd' ? 'USD' : 'MXN';
    document.getElementById('paymentMethodGroup').style.display = selectedCurrency === 'usd' ? 'none' : 'block';
    document.getElementById('paymentMethodSelect').value = 'EFECTIVO';
    document.getElementById('paidAmountInput').value = '';
    document.getElementById('tipAmountInput').value = '';
    document.getElementById('tipMethodSelect').value = '';
    document.getElementById('paymentMethodSelect').dispatchEvent(new Event('change'));
    checkoutModal.show();
  };

  // Ajustar visibilidad de pago y cambio (con conversión y redondeo a .0/.5)
  document.getElementById('paymentMethodSelect').onchange =
    document.getElementById('paidAmountInput').oninput = () => {
      const metodo = document.getElementById('paymentMethodSelect').value;
      const paid = parseFloat(document.getElementById('paidAmountInput').value) || 0;
      const total = parseFloat(document.getElementById('cart-total').textContent);

      if (metodo === 'TARJETA') {
        document.getElementById('paidAmountGroup').style.display = 'none';
        document.getElementById('changeInfo').style.display = 'none';
      } else {
        document.getElementById('paidAmountGroup').style.display = 'block';
        document.getElementById('changeInfo').style.display = 'block';

        const delta = paid - total;
        // Si estamos en USD, convertir a pesos
        const rawChange = selectedCurrency === 'usd'
          ? delta * dolarRate
          : delta;

        // Redondear a .0 o .5
        const rounded = roundToHalf(rawChange);

        document.getElementById('changeLabel').textContent = rounded.toFixed(2);
      }
    };



  document.getElementById('modalConfirmBtn').onclick = async () => {
    const isUsd = selectedCurrency === 'usd';
    const discountPct = parseInt(document.getElementById('discountInput').value) || 0;
    const methodPay = isUsd ? 'DOLARES' : document.getElementById('paymentMethodSelect').value;
    const tipAmt = parseFloat(document.getElementById('tipAmountInput').value) || 0;
    const tipMethod = document.getElementById('tipMethodSelect').value || '';
    const changeLabel = parseFloat(document.getElementById('changeLabel').textContent) || 0;

    checkoutModal.hide();

    const ticketResp = await fetch('ventas/get_ticket.php');
    const { ticket } = await ticketResp.json();

    const respuestas = await Promise.all(cart.map((item, idx) => {
      const price = isUsd ? item.precio_usd : item.precio_mxn;
      const precio_final = parseFloat((price * (1 - discountPct / 100)).toFixed(2));
      const propinaToSend = idx === 0 ? tipAmt : 0;
      const tipoPropinaToSend = idx === 0 ? tipMethod : '';

      const params = new URLSearchParams({
        categoria: item.categoria,
        producto: item.name,
        tipo_pago: methodPay,
        precio: price,
        descuento: discountPct,
        precio_final: precio_final,
        ticket: ticket,
        propina: propinaToSend,
        tipo_propina: tipoPropinaToSend,
        empleado: document.getElementById('employeeSelect').value
      });

      if (isUsd && idx === 0) {
        const changeLabel = parseFloat(document.getElementById('changeLabel').textContent) || 0;
        params.append('cambio', changeLabel);
      }

      return fetch('ventas/add_venta.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: params
      }).then(r => r.json());
    }));


    if (respuestas.every(r => r.success)) {
      alert(`Ticket #${ticket} registrado correctamente.`);
      cart = [];
      updateCartUI();
      document.getElementById('discountInput').value = '0';
    } else {
      alert('Ocurrió un error al registrar algunas ventas.');
    }
  };


  document.querySelectorAll('.category-card').forEach(c => {
    c.onclick = () => renderProducts(c.dataset.category);
  });

  document.addEventListener('DOMContentLoaded', () => {
    const firstCat = Object.keys(prodData)[0];
    if (firstCat) renderProducts(firstCat);
  });
</script>