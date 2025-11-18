<?php
if (!isset($productos)) {
  if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
  }

  $sql = "SELECT id, nombre, categoria, precio_usd, precio_mxn FROM ProdSer";
  $stmt = $pdo->query($sql);
  $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $categorias = array_unique(array_column($productos, 'categoria'));
  sort($categorias);
}
?>
<div style="display: flex; height: calc(100vh - 140px);">
  <!-- FILTROS -->
  <div class="card filter-card me-3">
    <div class="card-body">
      <h5 class="mb-3">Filtros</h5>
      <div class="mb-3">
        <label for="filterCategoria" class="form-label">Filtrar por Categoría</label>
        <select id="filterCategoria" class="form-select">
          <option value="all">TODAS</option>
          <?php foreach ($categorias as $categoria): ?>
            <option value="<?= htmlspecialchars($categoria) ?>">
              <?= htmlspecialchars($categoria) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="button" class="btn btn-outline-secondary w-100" id="btn-add" data-bs-toggle="modal"
        data-bs-target="#addProdModal">
        + Añadir producto
      </button>
    </div>
  </div>

  <!-- TABLA DE PRODUCTOS -->
  <div class="card data-card flex-fill">
    <div class="card-body data-body">
      <table class="table table-hover mb-0" id="productosTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Precio USD</th>
            <th>Precio MXN</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($productos)):
            $i = 1;
            foreach ($productos as $prod): ?>
              <tr data-categoria="<?= htmlspecialchars($prod['categoria']) ?>">
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td><?= htmlspecialchars($prod['categoria']) ?></td>
                <td><?= number_format($prod['precio_usd'], 2) ?></td>
                <td><?= number_format($prod['precio_mxn'], 2) ?></td>
                <td class="text-center">
                  <a href="#" class="me-2 text-primary btn-edit text-decoration-none" data-id="<?= $prod['id'] ?>"
                    title="Editar">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="#" class="text-danger btn-delete text-decoration-none" data-id="<?= $prod['id'] ?>"
                    title="Eliminar">
                    <i class="fas fa-trash-alt"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="6" class="text-center">No se encontraron registros</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal de “Añadir producto” -->
<div class="modal fade" id="addProdModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addProdForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Añadir Producto / Servicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nombre (EN)</label>
            <input type="text" class="form-control" name="nombre_en">
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción (EN)</label>
            <textarea class="form-control" name="descripcion_en" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select class="form-select" name="categoria" required>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Precio USD</label>
              <input type="number" step="0.01" class="form-control" name="precio_usd">
            </div>
            <div class="col">
              <label class="form-label">Precio MXN</label>
              <input type="number" step="0.01" class="form-control" name="precio_mxn" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Activo</label>
            <div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="activo" value="1" checked>
                <label class="form-check-label">Sí</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="activo" value="0">
                <label class="form-check-label">No</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal de edición -->
<div class="modal fade" id="editProdModal" tabindex="-1" aria-labelledby="editProdModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editProdForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editProdModalLabel">Editar Producto / Servicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">

          <div class="mb-3">
            <label for="edit-nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="edit-nombre" required>
          </div>
          <div class="mb-3">
            <label for="edit-nombre-en" class="form-label">Nombre (EN)</label>
            <input type="text" class="form-control" name="nombre_en" id="edit-nombre-en">
          </div>
          <div class="mb-3">
            <label for="edit-descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" id="edit-descripcion" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="edit-descripcion-en" class="form-label">Descripción (EN)</label>
            <textarea class="form-control" name="descripcion_en" id="edit-descripcion-en" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="edit-categoria" class="form-label">Categoría</label>
            <select class="form-select" name="categoria" id="edit-categoria" required>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3 row">
            <div class="col">
              <label for="edit-precio-usd" class="form-label">Precio USD</label>
              <input type="number" step="0.01" class="form-control" name="precio_usd" id="edit-precio-usd" required>
            </div>
            <div class="col">
              <label for="edit-precio-mxn" class="form-label">Precio MXN</label>
              <input type="number" step="0.01" class="form-control" name="precio_mxn" id="edit-precio-mxn" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Activo</label>
            <div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="activo" id="activo-si" value="1">
                <label class="form-check-label" for="activo-si">Sí</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="activo" id="activo-no" value="0">
                <label class="form-check-label" for="activo-no">No</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1) Definición de loadProdSer en global
    window.loadProdSer = () => {
      fetch('productos/tabla_prod_ser.php')
        .then(r => r.text())
        .then(html => {
          const content = document.getElementById('content');
          content.innerHTML = html;
          // Re-inicializa todo sobre el nuevo HTML
          initProdSerFilter();
          initDeleteButtons();
          initEditButtons();
          initEditFormHandler();
        })
        .catch(() => console.error('Error cargando tabla de productos'));
    };

    // 2) Resto de inicializadores
    function initProdSerFilter() {
      const select = document.getElementById('filterCategoria');
      if (!select) return;
      select.addEventListener('change', () => {
        const val = select.value;
        const rows = document.querySelectorAll('#productosTable tbody tr');
        let c = 1;
        rows.forEach(row => {
          if (val === 'all' || row.dataset.categoria === val) {
            row.style.display = '';
            row.cells[0].textContent = c++;
          } else {
            row.style.display = 'none';
          }
        });
      });
    }

    function initDeleteButtons() {
      document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async e => {
          e.preventDefault();
          if (!confirm('¿Seguro que deseas eliminar este producto?')) return;
          try {
            const res = await fetch('productos/delete_prod_ser.php', {
              method: 'POST',
              credentials: 'include',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: new URLSearchParams({ id: btn.dataset.id })
            });
            const result = await res.json();
            if (result.success) {
              // recarga toda la página para ver el cambio
              window.location.reload();
            } else {
              alert('Error al eliminar: ' + result.error);
            }
          } catch (err) {
            console.error('Error en delete:', err);
            alert('Error de red al intentar eliminar.');
          }
        });
      });
    }

    function initAddFormHandler() {
      const form = document.getElementById('addProdForm');
      form.addEventListener('submit', async e => {
        e.preventDefault();
        const data = new FormData(form);
        try {
          const res = await fetch('productos/add_prod_ser.php', {
            method: 'POST',
            credentials: 'include',
            body: data
          });
          const result = await res.json();
          if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('addProdModal')).hide();
            window.location.reload();
          } else {
            alert('Error al añadir: ' + result.error);
          }
        } catch {
          alert('Error de red al añadir.');
        }
      });
    }
    function initEditButtons() {
      document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', async e => {
          e.preventDefault();
          const id = btn.dataset.id;
          try {
            // 1) Llamada al servidor con credenciales
            const res = await fetch('productos/edit_prod_ser.php?id=' + id, {
              credentials: 'include'
            });

            // Depuración: qué URL y status devuelve
            console.log('FETCH URL:', res.url);
            console.log('FETCH status:', res.status);

            // 2) Leer texto de respuesta
            const text = await res.text();
            console.log('FETCH response text:', text);

            // 3) Intentar parsear JSON
            let prod;
            try {
              prod = JSON.parse(text);
            } catch (parseErr) {
              return alert('Respuesta no es JSON válido. Revisa la consola.');
            }

            // 4) Si no hay datos válidos
            if (!prod.id) {
              return alert('Registro no encontrado o no autorizado.');
            }

            // 5) Rellenar modal
            document.getElementById('edit-id').value = prod.id;
            document.getElementById('edit-nombre').value = prod.nombre;
            document.getElementById('edit-nombre-en').value = prod.nombre_en;
            document.getElementById('edit-descripcion').value = prod.descripcion;
            document.getElementById('edit-descripcion-en').value = prod.descripcion_en;
            document.getElementById('edit-categoria').value = prod.categoria;
            document.getElementById('edit-precio-usd').value = prod.precio_usd;
            document.getElementById('edit-precio-mxn').value = prod.precio_mxn;
            document.getElementById(prod.activo == 1 ? 'activo-si' : 'activo-no').checked = true;

            // 6) Mostrar modal
            new bootstrap.Modal(document.getElementById('editProdModal')).show();

          } catch (err) {
            console.error('Error en initEditButtons:', err);
            alert('Error al solicitar datos. Revisa la consola.');
          }
        });
      });
    }


    function initEditFormHandler() {
      const form = document.getElementById('editProdForm');
      if (!form) return;
      form.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(form);
        const res = await fetch('productos/edit_prod_ser.php', {
          method: 'POST',
          credentials: 'include',
          body: formData
        });
        const result = await res.json();
        if (result.success) {
          bootstrap.Modal.getInstance(
            document.getElementById('editProdModal')
          ).hide();
          // Recarga total de la página
          window.location.reload();
        } else {
          alert('Error al actualizar: ' + result.error);
        }
      });
    }

    // 3) Al cargar la página por primera vez:
    const params = new URLSearchParams(window.location.search);
    if (params.get('section') === 'prod_ser') {
      loadProdSer();
    } else {
      // la tabla ya está en el HTML servido por PHP
      initProdSerFilter();
      initDeleteButtons();
      initEditButtons();
      initEditFormHandler();
      initAddFormHandler();
    }
  });
</script>