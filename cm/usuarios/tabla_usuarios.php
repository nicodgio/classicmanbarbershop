<?php
// $empleados proviene de usuarios.php
?>
<table class="table table-hover mb-0 text-center">
  <thead>
    <tr>
      <th>N°</th>
      <th>Nombre</th>
      <th>Teléfono</th>
      <th>Inicio</th>
      <th>Activo</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($empleados)):
      $i = 1; ?>
      <?php foreach ($empleados as $e): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($e['nombre']) ?></td>
          <td><?= htmlspecialchars($e['telefono']) ?></td>
          <td><?= date('d-m-Y', strtotime($e['fecha_inicio'])) ?></td>
          <td>
            <div class="form-check form-switch d-inline-block">
              <input class="form-check-input toggle-active" data-id="<?= $e['id'] ?>" type="checkbox" <?= $e['activo'] ? 'checked' : '' ?>>
            </div>
          </td>
          <td>
            <!-- Editar -->
            <a href="#" data-bs-toggle="modal" data-bs-target="#editModal<?= $e['id'] ?>" title="Editar" class="me-2">
              <i class="fas fa-edit icon-navy"></i>
            </a>

            <!-- Eliminar -->
            <a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $e['id'] ?>" title="Eliminar">
              <i class="fas fa-trash-alt icon-navy"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="6" class="py-4">No se encontraron registros</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>