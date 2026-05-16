<?php
session_start();
include("../../conexion.php");

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit;
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$allowed = [1, 4, 5, 8, 11];
if (!in_array($tipo_usuario, $allowed)) {
    header("Location: ../../access.php");
    exit;
}

// ─── ELIMINAR ───────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM grupos_externos WHERE id_grupo_externo = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();
    $msg   = $ok ? 'Grupo externo eliminado correctamente.' : 'Error al eliminar: ' . addslashes($mysqli->error);
    $icon  = $ok ? 'success' : 'error';
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: '$icon', title: '$msg', confirmButtonText: 'OK' })
        .then(function() { window.location.href = 'seeGruposExternos.php'; });
    });
    </script>";
}

// ─── AGREGAR ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $nombre = trim($_POST['nombre_grupo_externo']);
    $desc   = trim($_POST['descripcion']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    $stmt = $mysqli->prepare("INSERT INTO grupos_externos (nombre_grupo_externo, descripcion, activo) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nombre, $desc, $activo);
    $ok = $stmt->execute();
    $stmt->close();
    $msg  = $ok ? 'Grupo externo agregado correctamente.' : 'Error al agregar: ' . addslashes($mysqli->error);
    $icon = $ok ? 'success' : 'error';
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: '$icon', title: '$msg', confirmButtonText: 'OK' })
        .then(function() { window.location.href = 'seeGruposExternos.php'; });
    });
    </script>";
}

// ─── EDITAR ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id     = (int)$_POST['id_grupo_externo'];
    $nombre = trim($_POST['nombre_grupo_externo']);
    $desc   = trim($_POST['descripcion']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    $stmt = $mysqli->prepare("UPDATE grupos_externos SET nombre_grupo_externo=?, descripcion=?, activo=? WHERE id_grupo_externo=?");
    $stmt->bind_param("ssii", $nombre, $desc, $activo, $id);
    $ok = $stmt->execute();
    $stmt->close();
    $msg  = $ok ? 'Grupo externo actualizado correctamente.' : 'Error al actualizar: ' . addslashes($mysqli->error);
    $icon = $ok ? 'success' : 'error';
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: '$icon', title: '$msg', confirmButtonText: 'OK' })
        .then(function() { window.location.href = 'seeGruposExternos.php'; });
    });
    </script>";
}

// ─── LISTAR ──────────────────────────────────────────────────────────────────
$result = $mysqli->query("SELECT * FROM grupos_externos ORDER BY nombre_grupo_externo ASC");
$grupos = [];
while ($row = $result->fetch_assoc()) {
    $grupos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos Externos - SDSYP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { background-color: #f8fafc; font-size: 15px; }

        .page-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: #fff;
            padding: 22px 28px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(13,110,253,.25);
        }

        .page-header h2 { margin: 0; font-size: 22px; font-weight: 700; }

        .card-table {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            padding: 24px;
        }

        .badge-activo   { background: #d1fae5; color: #065f46; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 13px; }
        .badge-inactivo { background: #fee2e2; color: #991b1b; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 13px; }
    </style>
</head>
<body>
<div class="container mt-4 mb-5">

    <div class="text-center mb-3">
        <a href="../../access.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-house-fill"></i> Dashboard
        </a>
    </div>

    <div class="page-header">
        <h2><i class="bi bi-diagram-3-fill me-2"></i>Grupos Externos</h2>
        <button class="btn btn-light fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="bi bi-plus-circle-fill me-1"></i> Agregar Grupo
        </button>
    </div>

    <div class="card-table">
        <table id="tablaGrupos" class="table table-hover align-middle w-100">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grupos as $g): ?>
                <tr>
                    <td><?= $g['id_grupo_externo'] ?></td>
                    <td><?= htmlspecialchars($g['nombre_grupo_externo']) ?></td>
                    <td><?= htmlspecialchars($g['descripcion'] ?? '') ?></td>
                    <td>
                        <?php if ($g['activo']): ?>
                            <span class="badge-activo"><i class="bi bi-check-circle-fill me-1"></i>Activo</span>
                        <?php else: ?>
                            <span class="badge-inactivo"><i class="bi bi-x-circle-fill me-1"></i>Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($g['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning me-1 btn-edit"
                            data-id="<?= $g['id_grupo_externo'] ?>"
                            data-nombre="<?= htmlspecialchars($g['nombre_grupo_externo'], ENT_QUOTES) ?>"
                            data-descripcion="<?= htmlspecialchars($g['descripcion'] ?? '', ENT_QUOTES) ?>"
                            data-activo="<?= $g['activo'] ?>">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete"
                            data-id="<?= $g['id_grupo_externo'] ?>"
                            data-nombre="<?= htmlspecialchars($g['nombre_grupo_externo'], ENT_QUOTES) ?>">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Modal Agregar ──────────────────────────────────────────────────────── -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form method="POST" action="seeGruposExternos.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle-fill me-2"></i>Agregar Grupo Externo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre_grupo_externo" required maxlength="150" placeholder="Nombre del grupo externo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3" maxlength="255" placeholder="Descripción opcional..."></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="activo" id="add_activo" checked>
                        <label class="form-check-label fw-semibold" for="add_activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Editar ───────────────────────────────────────────────────────── -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form method="POST" action="seeGruposExternos.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_grupo_externo" id="edit_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-fill me-2"></i>Editar Grupo Externo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre_grupo_externo" id="edit_nombre" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="3" maxlength="255"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="activo" id="edit_activo">
                        <label class="form-check-label fw-semibold" for="edit_activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-save me-1"></i>Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    $('#tablaGrupos').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: 5 }]
    });

    // Abrir modal editar
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_id').value          = this.dataset.id;
            document.getElementById('edit_nombre').value      = this.dataset.nombre;
            document.getElementById('edit_descripcion').value = this.dataset.descripcion;
            document.getElementById('edit_activo').checked    = this.dataset.activo === '1';
            new bootstrap.Modal(document.getElementById('modalEditar')).show();
        });
    });

    // Confirmar eliminación
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const nombre = this.dataset.nombre;
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar grupo?',
                html: '<strong>' + nombre + '</strong>',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then(function (res) {
                if (res.isConfirmed) {
                    window.location.href = 'seeGruposExternos.php?delete=' + id;
                }
            });
        });
    });
});
</script>
</body>
</html>
