<?php
session_start();
require_once('../filtros_grupos.php');
require_once('../filtros_grupo_usuario.php');
include("../../conexion.php");

// Aplicar filtro de grupos según tipo de usuario
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_usuario = isset($_SESSION['id']) ? $_SESSION['id'] : null;
$where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

// Cargar listas necesarias
$metas = $mysqli->query("SELECT * FROM metas ORDER BY descripcion_meta ASC");
$actividades_cv = $mysqli->query("SELECT id_actividad_centro_vida, descripcion_actividad FROM actividad_centro_vida ORDER BY descripcion_actividad ASC");
$usuarios = $mysqli->query("SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (5, 10, 11, 12, 13) ORDER BY nombre ASC");
$grupos = $mysqli->query("SELECT g.* FROM grupos g WHERE 1=1 $where_grupos ORDER BY g.descripcion_grupo ASC");

// Queries filtradas para modal
// Tipo 11: ve todos los funcionarios (lista completa), solo su grupo
// Tipos 10, 12 y 13: solo se ven a sí mismos
if (($tipo_usuario == 10 || $tipo_usuario == 12 || $tipo_usuario == 13) && $id_usuario) {
    $usuarios_modal = $mysqli->query("SELECT id, nombre FROM usuarios WHERE id = " . intval($id_usuario));
    if (isset($_SESSION['id_grupo']) && $_SESSION['id_grupo']) {
        $id_grupo_modal = intval($_SESSION['id_grupo']);
        $grupos_modal = $mysqli->query("SELECT g.* FROM grupos g WHERE g.id_grupo = $id_grupo_modal ORDER BY g.descripcion_grupo ASC");
    } else {
        $grupos_modal = null;
    }
} elseif ($tipo_usuario == 11 && isset($_SESSION['id_grupo']) && $_SESSION['id_grupo']) {
    $id_grupo_modal = intval($_SESSION['id_grupo']);
    $usuarios_modal = null; // tipo 11 ve todos los funcionarios (usará $usuarios)
    $grupos_modal   = $mysqli->query("SELECT g.* FROM grupos g WHERE g.id_grupo = $id_grupo_modal ORDER BY g.descripcion_grupo ASC");
} else {
    $usuarios_modal = null; // usará $usuarios con seek
    $grupos_modal   = null; // usará $grupos con seek
}
$comunas = $mysqli->query("SELECT * FROM comunas ORDER BY nombre_com ASC");

// Filtros
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_mes = isset($_GET['filtro_mes']) ? intval($_GET['filtro_mes']) : '';
$filtro_funcionario = isset($_GET['filtro_funcionario']) ? intval($_GET['filtro_funcionario']) : '';
$filtro_tipo_registro = isset($_GET['filtro_tipo_registro']) ? $mysqli->real_escape_string($_GET['filtro_tipo_registro']) : '';
$where = '';
// Eliminación (GET ?delete=ID)
if (isset($_GET['delete'])) {
    $del = intval($_GET['delete']);
    if ($del > 0) {
        $stmtDel = $mysqli->prepare("DELETE FROM masiva_centro_vida WHERE id_masiva_centro_vida=?");
        if ($stmtDel) {
            $stmtDel->bind_param('i', $del);
            $stmtDel->execute();
            $stmtDel->close();
        }
        echo "<script>window.location='formMasivoCentroVida.php';</script>";
        exit;
    }
}
if ($filtro_anio) {
    $where .= " AND YEAR(mcv.fecha_atencion) = $filtro_anio";
}
if ($filtro_mes) {
    $where .= " AND MONTH(mcv.fecha_atencion) = $filtro_mes";
}
if ($filtro_funcionario) {
    $where .= " AND mcv.id_usuario = $filtro_funcionario";
}
if ($filtro_tipo_registro) {
    // filtrar por tipo_registro en la tabla masiva
    $where .= " AND mcv.tipo_registro = '" . $mysqli->real_escape_string($filtro_tipo_registro) . "'";
}

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');
$where .= $where_grupos_filtro;

// Aplicar filtro por grupo de usuario (tipo 11: INGENIERO CENTRO VIDA)
// Tipo 11 solo ve actividades de su centro asociado
if ($tipo_usuario == 11 && isset($_SESSION['id_grupo']) && $_SESSION['id_grupo']) {
    $id_grupo_session = intval($_SESSION['id_grupo']);
    $where .= " AND mcv.id_centro_vida = $id_grupo_session";
}

// Filtro para tipo usuario 10, 12 y 13: solo ver sus propios registros
if (($tipo_usuario == 10 || $tipo_usuario == 12 || $tipo_usuario == 13) && $id_usuario) {
    $where .= " AND mcv.id_usuario = " . intval($id_usuario);
}
// Tipo usuario 5 puede ver todo (no se agrega filtro adicional)

// Paginación servidor
$per_page_m = 25;
$current_page_m = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset_m = ($current_page_m - 1) * $per_page_m;

// Consulta principal (nueva tabla masiva_centro_vida). Alias de id para compatibilidad visual
$query = "SELECT 
    mcv.id_masiva_centro_vida AS id_registro,
    mcv.id_meta,
    mcv.id_actividad,
    mcv.id_accion,
    mcv.politica_publica,
    mcv.id_centro_vida,
    mcv.id_comuna,
    mcv.id_actividad_centro_vida,
    mcv.funcionario_responsable,
    mcv.tipo_registro,
    m.descripcion_meta,
    a.descripcion_actividad,
    ac.descripcion_accion,
    pp.descripcion_politica,
    g.descripcion_grupo AS centro_vida,
    mcv.fecha_atencion,
    mcv.nombre_lider,
    mcv.telefono_contacto,
    c.nombre_com AS nombre_comuna,
    mcv.medio_verificacion,
    mcv.cantidad_masculino,
    mcv.cantidad_femenino,
    mcv.observacion_actividad,
    mcv.jornada,
    mcv.profesion,
    u1.nombre AS digitado_por,
    u2.nombre AS funcionario_responsable_nombre,
    acv.descripcion_actividad AS actividad_centro_vida
FROM masiva_centro_vida mcv
LEFT JOIN metas m ON mcv.id_meta = m.id_meta
LEFT JOIN actividades a ON mcv.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON mcv.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON mcv.politica_publica = pp.id_politica
LEFT JOIN grupos g ON mcv.id_centro_vida = g.id_grupo
LEFT JOIN comunas c ON mcv.id_comuna = c.id_com
LEFT JOIN usuarios u1 ON mcv.id_usuario = u1.id
LEFT JOIN usuarios u2 ON mcv.funcionario_responsable = u2.id
LEFT JOIN actividad_centro_vida acv ON mcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
WHERE 1 $where
ORDER BY mcv.fecha_atencion DESC";
// Contar total sin LIMIT (subquery para garantizar mismos JOINs/WHERE)
$query_count_m = "SELECT COUNT(*) as total FROM ($query) as cnt_subq";
$result_count_m = $mysqli->query($query_count_m);
$total_registros_m = ($result_count_m) ? (int)$result_count_m->fetch_assoc()['total'] : 0;
$total_pages_m = max(1, (int)ceil($total_registros_m / $per_page_m));
// Añadir LIMIT para paginación
$query .= " LIMIT $per_page_m OFFSET $offset_m";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>SDSYP - Actividades Centro Vida (Masivas)</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- Flatpickr para el calendario -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <style>
        body {
            font-size: 16px;
            background: #f8fafc
        }

        .modern-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
            overflow: hidden;
            margin: 20px auto;
            max-width: 1400px
        }

        .modern-header {
            background: linear-gradient(135deg, #e91e63, #9c27b0);
            color: #fff;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px
        }

        .modern-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600
        }

        .btn-modern {
            background: rgba(255, 255, 255, .2);
            border: 2px solid rgba(255, 255, 255, .3);
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: .3s
        }

        .btn-modern:hover {
            background: rgba(255, 255, 255, .3);
            border-color: rgba(255, 255, 255, .5);
            transform: translateY(-2px);
            color: #fff
        }

        .modern-filters {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px
        }

        .modern-input,
        .modern-select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px
        }

        .modern-table-wrapper {
            overflow-x: auto
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px
        }

        .modern-table th {
            background: #f8fafc;
            color: #374151;
            font-weight: 600;
            padding: 10px 8px;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap
        }

        .modern-table td {
            padding: 8px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            white-space: nowrap;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center
        }

        .btn-action {
            border: none;
            border-radius: 4px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: .2s
        }

        .btn-edit {
            background: #3b82f6;
            color: #fff
        }

        .btn-edit:hover {
            background: #2563eb
        }

        .btn-delete {
            background: #ef4444;
            color: #fff
        }

        .btn-delete:hover {
            background: #dc2626
        }

        /* Estilos para el calendario multi-selección */
        .flatpickr-calendar {
            font-size: 14px;
        }

        .flatpickr-day.selected {
            background: #e91e63 !important;
            border-color: #e91e63 !important;
        }

        .selected-dates-display {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
            min-height: 40px;
        }

        .date-tag {
            display: inline-block;
            background: #e91e63;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            margin: 2px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <center style="margin-top:20px"><img src='../../img/logo.png' width="150" height="120" /></center>
    <h1 style="color:#e91e63;text-shadow:#fff 0.1em 0.1em 0.2em;font-size:44px;text-align:center;font-weight:bold"><i class="bi bi-heart-fill"></i> ACTIVIDADES MASIVAS CENTRO VIDA</h1>
    <div class="container mt-5">
        <div class="modern-container">
            <div class="modern-header">
                <h2><i class="bi bi-heart-fill"></i> Actividades Realizadas</h2>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button type="button" id="btnAbrirModalAdd" class="btn-modern"><i class="bi bi-plus-circle-fill"></i> Agregar</button>
                    <button type="button" class="btn-modern" style="background:rgba(0,188,212,0.35);border-color:rgba(0,188,212,0.6);" data-bs-toggle="modal" data-bs-target="#modalControlAsistenciaM">
                        <i class="bi bi-calendar-check-fill"></i> Control Asistencia
                    </button>
                    <button type="button" class="btn-modern" data-bs-toggle="modal" data-bs-target="#modalExportMasivo">
                        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                    </button>
                </div>
            </div>
            <!-- Mensaje informativo de filtro por grupo -->
            <?php echo generarMensajeFiltroGrupo($mysqli); ?>
            <?php echo generarMensajeFiltroPropio(); ?>

            <div class="modern-filters">
                <form action="formMasivoCentroVida.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="filtro_anio">Año</label>
                        <select id="filtro_anio" name="filtro_anio" class="modern-select">
                            <option value="">Todos</option>
                            <?php $currentYear = date('Y');
                            for ($y = 2023; $y <= $currentYear; $y++) {
                                $sel = $filtro_anio == $y ? 'selected' : '';
                                echo "<option value='$y' $sel>$y</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_mes">Mes</label>
                        <select id="filtro_mes" name="filtro_mes" class="modern-select">
                            <option value="">Todos</option>
                            <?php $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
                            foreach ($meses as $num => $nom) {
                                $sel = $filtro_mes == $num ? 'selected' : '';
                                echo "<option value='$num' $sel>$nom</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_funcionario">Funcionario</label>
                        <select id="filtro_funcionario" name="filtro_funcionario" class="modern-select">
                            <option value="">Todos</option>
                            <?php if ($usuarios) {
                                while ($u = $usuarios->fetch_assoc()) {
                                    $sel = $filtro_funcionario == $u['id'] ? 'selected' : '';
                                    echo "<option value='{$u['id']}' $sel>" . htmlspecialchars($u['nombre']) . "</option>";
                                }
                            } ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_tipo_registro">Tipo Registro</label>
                        <select id="filtro_tipo_registro" name="filtro_tipo_registro" class="modern-select">
                            <option value="">Todos</option>
                            <option value="Registro Actividad" <?= $filtro_tipo_registro === 'Registro Actividad' ? 'selected' : '' ?>>Registro Actividad</option>
                            <option value="Masivas" <?= $filtro_tipo_registro === 'Masivas' ? 'selected' : '' ?>>Masivas</option>
                            <option value="Articulacion" <?= $filtro_tipo_registro === 'Articulacion' ? 'selected' : '' ?>>Articulación</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-modern" style="background:#10b981;border-color:#10b981"><i class="bi bi-search"></i> Buscar</button>
                    </div>
                </form>
            </div>
            <div class="modern-table-wrapper">
                <table class="modern-table" id="tabla">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Meta</th>
                            <th>Actividad (Plan)</th>
                            <th>Acción</th>
                            <th>Actividad Centro Vida</th>
                            <th>Política Pública</th>
                            <th>Centro Vida</th>
                            <th>Fecha Atención</th>
                            <th>Nombre Líder</th>
                            <th>Teléfono</th>
                            <th>Comuna</th>
                            <th>Medio Verif.</th>
                            <th>Masculino</th>
                            <th>Femenino</th>
                            <th>Nombre actividad,evento o asunto</th>
                            <th>Digitado por</th>
                            <th>Funcionario Resp.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows) {
                            while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= $row['id_registro']; ?></td>
                                    <td title='<?= htmlspecialchars($row['descripcion_meta'] ?? '') ?>'><?= $row['descripcion_meta'] ?? '' ?></td>
                                    <td title='<?= htmlspecialchars($row['descripcion_actividad'] ?? '') ?>'><?= $row['descripcion_actividad'] ?? '' ?></td>
                                    <td title='<?= htmlspecialchars($row['descripcion_accion'] ?? '') ?>'><?= $row['descripcion_accion'] ?? '' ?></td>
                                    <td title='<?= htmlspecialchars($row['actividad_centro_vida'] ?? '') ?>'><?= $row['actividad_centro_vida'] ?? '' ?></td>
                                    <td><?= $row['descripcion_politica'] ?? '' ?></td>
                                    <td><?= $row['centro_vida'] ?? '' ?></td>
                                    <td><?= $row['fecha_atencion'] ?? '' ?></td>
                                    <td><?= $row['nombre_lider'] ?? '' ?></td>
                                    <td><?= $row['telefono_contacto'] ?? '' ?></td>
                                    <td><?= $row['nombre_comuna'] ?? '' ?></td>
                                    <td><?= $row['medio_verificacion'] ?? '' ?></td>
                                    <td><?= $row['cantidad_masculino'] ?? 0 ?></td>
                                    <td><?= $row['cantidad_femenino'] ?? 0 ?></td>
                                    <td><?= $row['observacion_actividad'] ?? '' ?></td>
                                    <td><?= $row['digitado_por'] ?? '' ?></td>
                                    <td><?= $row['funcionario_responsable_nombre'] ?? '' ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#modalAdd"
                                                data-json='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Editar"><i class="bi bi-pencil-fill"></i></button>
                                            <button class="btn-action btn-delete" data-id="<?= $row['id_registro']; ?>" title="Eliminar"><i class="bi bi-trash-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } else {
                            // Fila vacía compatible con DataTables (sin colspan)
                            echo "<tr class='no-data'>";
                            for ($i = 0; $i < 18; $i++) {
                                echo "<td class='text-muted'></td>";
                            }
                            echo "</tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación servidor -->
            <?php
            if ($total_pages_m > 1 || $total_registros_m > 0) {
                $params_base_m = $_GET;
                unset($params_base_m['page']);
                $base_url_m = 'formMasivoCentroVida.php?' . http_build_query($params_base_m);
                $start_m = ($current_page_m - 1) * $per_page_m + 1;
                $end_m   = min($current_page_m * $per_page_m, $total_registros_m);
                echo '<div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #e5e7eb;flex-wrap:wrap;gap:8px;">';
                echo '<span style="font-size:14px;color:#6b7280;">Mostrando ' . $start_m . ' – ' . $end_m . ' de <strong>' . $total_registros_m . '</strong> registros</span>';
                echo '<nav><ul class="pagination pagination-sm mb-0">';
                if ($current_page_m > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . $base_url_m . '&page=' . ($current_page_m - 1) . '">&#8249; Ant</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">&#8249; Ant</span></li>';
                }
                $window_m = 2;
                for ($p = 1; $p <= $total_pages_m; $p++) {
                    if ($p == 1 || $p == $total_pages_m || abs($p - $current_page_m) <= $window_m) {
                        $active_m = ($p == $current_page_m) ? ' active' : '';
                        echo '<li class="page-item' . $active_m . '"><a class="page-link" href="' . $base_url_m . '&page=' . $p . '">' . $p . '</a></li>';
                    } elseif (abs($p - $current_page_m) == $window_m + 1) {
                        echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    }
                }
                if ($current_page_m < $total_pages_m) {
                    echo '<li class="page-item"><a class="page-link" href="' . $base_url_m . '&page=' . ($current_page_m + 1) . '">Sig &#8250;</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">Sig &#8250;</span></li>';
                }
                echo '</ul></nav></div>';
            }
            ?>
        </div>
    </div> <a href="../../access.php"> <img src='../../img/atras.png' width='72' height='72'></a></center><br>

    <!-- Modal Agregar -->
    <div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addRegistroMasivoCentroVida.php" method="POST" id="formAdd">
                    <input type="hidden" name="id_masiva_centro_vida" id="id_masiva_centro_vida" value="">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalAddTitle"><i class="bi bi-plus-circle-fill me-2"></i><span class="mode-text">Agregar Actividad Masiva Centro Vida</span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="meta" name="id_meta" required>
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php if ($metas) {
                                        mysqli_data_seek($metas, 0);
                                        while ($m = $metas->fetch_assoc()) {
                                            echo "<option value='{$m['id_meta']}'>" . htmlspecialchars($m['descripcion_meta']) . "</option>";
                                        }
                                    } ?>
                                </select>
                                <label for="meta">Meta</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="actividad" name="id_actividad" required disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                                <label for="actividad">Actividad (Plan)</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="accion" name="id_accion" required disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                                <label for="accion">Acción</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="actividad_centro_vida" name="id_actividad_centro_vida" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php if ($actividades_cv) {
                                        mysqli_data_seek($actividades_cv, 0);
                                        while ($acv = $actividades_cv->fetch_assoc()) {
                                            echo "<option value='{$acv['id_actividad_centro_vida']}'>" . htmlspecialchars($acv['descripcion_actividad']) . "</option>";
                                        }
                                    } ?>
                                </select>
                                <label for="actividad_centro_vida">Actividad Centro Vida</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="politica_publica" name="politica_publica">
                                    <option value="" selected>Seleccione Política Pública...</option>
                                </select>
                                <label for="politica_publica">Política Pública</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="tipo_registro" name="tipo_registro">
                                    <option value=""></option>
                                    <option value="Registro Actividad">Registro Actividad</option>
                                    <option value="Masivas">Masivas</option>
                                    <option value="Articulacion">Articulación</option>
                                </select>
                                <label for="tipo_registro">Tipo de Registro</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select name="funcionario_responsable" id="funcionario_responsable" class="form-select" <?= ($tipo_usuario == 10 || $tipo_usuario == 12 || $tipo_usuario == 13) ? 'disabled' : '' ?>>
                                    <?php if ($tipo_usuario == 10 || $tipo_usuario == 12 || $tipo_usuario == 13): ?>
                                        <?php
                                        $self_func = $usuarios_modal ? $usuarios_modal->fetch_assoc() : null;
                                        if ($self_func): ?>
                                            <option value="<?= $self_func['id'] ?>" selected><?= htmlspecialchars($self_func['nombre']) ?></option>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <option value="" selected>Seleccione funcionario...</option>
                                        <?php
                                        mysqli_data_seek($usuarios, 0);
                                        while ($u = $usuarios->fetch_assoc()) {
                                            echo "<option value='{$u['id']}'>" . htmlspecialchars($u['nombre']) . "</option>";
                                        } ?>
                                    <?php endif; ?>
                                </select>
                                <?php if ($tipo_usuario == 10 || $tipo_usuario == 12 || $tipo_usuario == 13): ?>
                                    <input type="hidden" name="funcionario_responsable" value="<?= intval($id_usuario) ?>">
                                <?php endif; ?>
                                <label for="funcionario_responsable">Funcionario Responsable</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="fecha_atencion" class="form-label"><strong>Fechas de Atención</strong></label>
                                <input type="text" class="form-control" id="fecha_atencion" name="fecha_atencion" placeholder="Haga clic para seleccionar múltiples fechas..." readonly required>
                                <input type="hidden" id="fechas_seleccionadas" name="fechas_seleccionadas">
                                <div class="selected-dates-display" id="selected-dates-display">
                                    <small class="text-muted">Las fechas seleccionadas aparecerán aquí</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="text" class="form-control" id="nombre_lider" name="nombre_lider" placeholder="Nombre líder">
                                <label for="nombre_lider">Nombre líder</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" placeholder="Teléfono">
                                <label for="telefono_contacto">Teléfono contacto</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select name="id_comuna" id="id_comuna" class="form-select">
                                    <option value="" selected>Seleccione...</option>
                                    <?php if ($comunas) {
                                        while ($c = $comunas->fetch_assoc()) {
                                            echo "<option value='{$c['id_com']}'>" . htmlspecialchars($c['nombre_com']) . "</option>";
                                        }
                                    } ?>
                                </select>
                                <label for="id_comuna">Comuna/Corregimiento</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select name="medio_verificacion" id="medio_verificacion" class="form-select">
                                    <option value="" selected>Seleccione...</option>
                                    <option value="Acta">Acta</option>
                                    <option value="Acta y registro fotografico">Acta y registro fotografico</option>
                                    <option value="Registro fotografico">Registro fotográfico</option>
                                    <option value="Registro campo">Registro Campo</option>
                                    <option value="Historio/ expediente">Historico/ expediente</option>
                                    <option value="Captura pantalla digital">Captura pantalla digital</option>
                                    <option value="SPP">SPP</option>
                                    <option value="SPP - Registro fotografico">SPP - Registro fotografico</option>
                                </select>
                                <label for="medio_verificacion">Medio de Verificación</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="number" name="cantidad_masculino" id="cantidad_masculino" class="form-control" placeholder="Masculino" min="0" required>
                                <label for="cantidad_masculino">Cantidad Masculino</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="number" name="cantidad_femenino" id="cantidad_femenino" class="form-control" placeholder="Femenino" min="0" required>
                                <label for="cantidad_femenino">Cantidad Femenino</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="centro_vida" name="id_centro_vida">
                                    <option value="" selected>Seleccione...</option>
                                    <?php
                                    $grupos_modal_use = $grupos_modal ?? $grupos;
                                    if ($grupos_modal === null) {
                                        mysqli_data_seek($grupos, 0);
                                    }
                                    if ($grupos_modal_use) {
                                        while ($g = $grupos_modal_use->fetch_assoc()) {
                                            $descripcion = $g['descripcion_grupo'];
                                            if (substr($descripcion, 0, 5) === 'CPSAM') {
                                                continue;
                                            }
                                            echo "<option value='{$g['id_grupo']}'>" . htmlspecialchars($descripcion) . "</option>";
                                        }
                                    } ?>
                                </select>
                                <label for="centro_vida">Lugar del evento</label>
                            </div>
                        </div>

                        <!-- Sección Personas: visible solo cuando tipo_registro = Registro Actividad -->
                        <div id="seccion_personas_cv" class="d-none">
                            <hr>
                            <div class="bg-light p-3 rounded border mb-3">
                                <h6 class="text-primary mb-3"><i class="bi bi-people-fill"></i> Buscar y Agregar Personas C.V.
                                    <small class="text-muted">(Requerido para Registro Actividad)</small>
                                </h6>
                                <div class="alert alert-warning mb-3">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <strong>Importante:</strong> Debe agregar exactamente la misma cantidad de cédulas que indicó en Cantidad Masculino + Cantidad Femenino.
                                    <span id="contador_req_cv" class="badge bg-danger ms-2">0 requeridas</span>
                                    <span id="contador_sel_cv" class="badge bg-success ms-1">0 agregadas</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="buscar_persona_cv_input" placeholder="Buscar por nombre o cédula...">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="filtroJornadaCV" id="filtroJornadaCV_todos" value="todos" checked>
                                            <label class="btn btn-outline-secondary btn-sm" for="filtroJornadaCV_todos">Todos</label>
                                            <input type="radio" class="btn-check" name="filtroJornadaCV" id="filtroJornadaCV_manana" value="Mañana">
                                            <label class="btn btn-outline-primary btn-sm" for="filtroJornadaCV_manana">Mañana</label>
                                            <input type="radio" class="btn-check" name="filtroJornadaCV" id="filtroJornadaCV_tarde" value="Tarde">
                                            <label class="btn btn-outline-warning btn-sm" for="filtroJornadaCV_tarde">Tarde</label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Selector de CV para cargar personas -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-8">
                                        <select class="form-select form-select-sm" id="filtro_grupo_cv_personas">
                                            <option value="">Seleccione un Centro de Vida...</option>
                                            <?php
                                            if (($tipo_usuario == 10 || $tipo_usuario == 11 || $tipo_usuario == 13) && isset($_SESSION['id_grupo']) && $_SESSION['id_grupo']) {
                                                $cvs_modal = $mysqli->query("SELECT id_grupo, descripcion_grupo FROM grupos WHERE id_grupo = " . intval($_SESSION['id_grupo']));
                                            } else {
                                                $cvs_modal = $mysqli->query("SELECT id_grupo, descripcion_grupo FROM grupos WHERE descripcion_grupo LIKE 'CV%' ORDER BY descripcion_grupo ASC");
                                            }
                                            if ($cvs_modal) {
                                                while ($cv_row = $cvs_modal->fetch_assoc()) {
                                                    echo '<option value="' . $cv_row['id_grupo'] . '">' . htmlspecialchars($cv_row['descripcion_grupo']) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-info btn-sm w-100" id="btn_cargar_personas_cv">
                                            <i class="bi bi-arrow-clockwise"></i> Cargar Personas
                                        </button>
                                    </div>
                                </div>
                                <!-- Lista de personas del CV -->
                                <div id="area_personas_cv" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Personas: <span id="total_personas_cv" class="badge bg-info">0</span></h6>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-success btn-sm" id="btn_sel_todas_cv"><i class="bi bi-check-all"></i> Todas</button>
                                            <button type="button" class="btn btn-warning btn-sm" id="btn_desel_todas_cv"><i class="bi bi-x"></i> Ninguna</button>
                                            <button type="button" class="btn btn-primary btn-sm" id="btn_agregar_sel_cv"><i class="bi bi-plus-circle"></i> Agregar</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive" style="max-height:300px;overflow-y:auto;border:1px solid #dee2e6;border-radius:4px;">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width:50px"><input type="checkbox" class="form-check-input" id="chk_all_personas_cv"></th>
                                                    <th>Cédula</th>
                                                    <th>Nombres</th>
                                                    <th>Apellidos</th>
                                                    <th>Género</th>
                                                    <th>Jornada</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_personas_cv"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- Lista personas agregadas -->
                                <div id="cedulas_cv_container" style="display:none;">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="bi bi-people-fill"></i> Personas Agregadas: <span id="contador_cedulas_cv" class="badge bg-secondary">0</span></h6>
                                    </div>
                                    <div id="lista_cedulas_cv" class="list-group mb-3"></div>
                                </div>
                            </div>
                            <input type="hidden" id="cedulas_json_cv" name="cedulas_json" value="">
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold"><i class="bi bi-clock-fill"></i> Jornada</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="jornada_manana" name="jornada" value="Mañana" required aria-required="true">
                                        <label class="form-check-label" for="jornada_manana">
                                            <i class="bi bi-sunrise"></i> Mañana
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="jornada_tarde" name="jornada" value="Tarde">
                                        <label class="form-check-label" for="jornada_tarde">
                                            <i class="bi bi-sun"></i> Tarde
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="profesion_masivo" name="profesion">
                                    <option value="" selected>Seleccione Profesión...</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Auxiliar administrativo">Auxiliar administrativo</option>
                                    <option value="Deportes">Deportes</option>
                                    <option value="Enfermera">Enfermera</option>
                                    <option value="Fisioterapeuta">Fisioterapeuta</option>
                                    <option value="Gerontología">Gerontología</option>
                                    <option value="Nutricionista">Nutricionista</option>
                                    <option value="Psicología">Psicología</option>
                                    <option value="Psicosocial">Psicosocial</option>
                                    <option value="Tallerista">Tallerista</option>
                                    <option value="Trabajo social">Trabajo social</option>
                                </select>
                                <label for="profesion_masivo">Profesión</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <input type="text" class="form-control" id="observacion_actividad" name="observacion_actividad" placeholder="Observación">
                                <label for="observacion_actividad">Nombre actividad,evento o asunto</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnSubmit"><i class="bi bi-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Control de Asistencia (Masivo) -->
    <div class="modal fade" id="modalControlAsistenciaM" tabindex="-1" aria-labelledby="modalCAMLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#00bcd4,#0097a7);color:#fff;">
                    <h5 class="modal-title" id="modalCAMLabel">
                        <i class="bi bi-calendar-check-fill me-2"></i>Control de Asistencia
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Marque las personas presentes y seleccione la fecha de asistencia. Al guardar, podrá cargar esta lista al abrir el modal de Agregar.</p>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold"><i class="bi bi-calendar3"></i> Fecha de Asistencia</label>
                            <input type="date" class="form-control" id="cam_fecha" max="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <!-- Selector de CV para cargar personas -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <select class="form-select" id="cam_filtro_grupo">
                                <option value="">Seleccione un Centro de Vida para cargar personas...</option>
                                <?php
                                if (($tipo_usuario == 10 || $tipo_usuario == 11 || $tipo_usuario == 13) && isset($_SESSION['id_grupo']) && $_SESSION['id_grupo']) {
                                    $cvs_cam = $mysqli->query("SELECT id_grupo, descripcion_grupo FROM grupos WHERE id_grupo = " . intval($_SESSION['id_grupo']));
                                } else {
                                    $cvs_cam = $mysqli->query("SELECT id_grupo, descripcion_grupo FROM grupos WHERE descripcion_grupo LIKE 'CV%' ORDER BY descripcion_grupo ASC");
                                }
                                if ($cvs_cam) {
                                    while ($cv_row = $cvs_cam->fetch_assoc()) {
                                        echo '<option value="' . $cv_row['id_grupo'] . '">' . htmlspecialchars($cv_row['descripcion_grupo']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-info btn-sm w-100 text-white" id="cam_btnCargar">
                                <i class="bi bi-arrow-clockwise"></i> Cargar Personas
                            </button>
                        </div>
                    </div>
                    <!-- Lista de personas -->
                    <div id="cam_areaPersonas" style="display:none;">
                        <div class="row mb-2">
                            <div class="col-md-8">
                                <input type="text" id="cam_searchPersona" class="form-control" placeholder="Buscar por nombre o cédula...">
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="cam_filtroJornada" id="cam_filtroTodos" value="todos" checked>
                                    <label class="btn btn-outline-secondary btn-sm" for="cam_filtroTodos">Todos</label>
                                    <input type="radio" class="btn-check" name="cam_filtroJornada" id="cam_filtroManana" value="Mañana">
                                    <label class="btn btn-outline-primary btn-sm" for="cam_filtroManana">Mañana</label>
                                    <input type="radio" class="btn-check" name="cam_filtroJornada" id="cam_filtroTarde" value="Tarde">
                                    <label class="btn btn-outline-warning btn-sm" for="cam_filtroTarde">Tarde</label>
                                </div>
                            </div>
                        </div>
                        <div style="max-height:350px;overflow:auto;border:1px solid #e5e7eb;padding:8px;border-radius:6px;">
                            <div class="form-check mb-2 border-bottom pb-2">
                                <input class="form-check-input" type="checkbox" id="cam_selectAll">
                                <label class="form-check-label fw-bold" for="cam_selectAll">
                                    <i class="bi bi-check-square me-1"></i>Seleccionar Todos
                                </label>
                            </div>
                            <div id="cam_listaPersonas"></div>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-info-circle"></i> Personas seleccionadas: <span id="cam_contador" class="fw-bold">0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info text-white" id="cam_btnGuardar">
                        <i class="bi bi-save"></i> Guardar Asistencia
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Exportar Excel Masivo -->
    <div class="modal fade" id="modalExportMasivo" tabindex="-1" aria-labelledby="modalExportMasivoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="exportActividadesExcelCentroVidaMasivo.php" method="get" target="_blank">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalExportMasivoLabel">
                            <i class="bi bi-file-earmark-excel-fill me-2"></i>Exportar Actividades Masivas
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="exp_m_anio" name="filtro_anio">
                                    <option value="">Todos los años</option>
                                    <?php $yr = date('Y');
                                    for ($y = 2023; $y <= $yr; $y++) echo "<option value=\"$y\">$y</option>"; ?>
                                </select>
                                <label for="exp_m_anio">Año</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="exp_m_mes" name="filtro_mes">
                                    <option value="">Todos los meses</option>
                                    <?php
                                    $meses_m = [
                                        1 => 'Enero',
                                        2 => 'Febrero',
                                        3 => 'Marzo',
                                        4 => 'Abril',
                                        5 => 'Mayo',
                                        6 => 'Junio',
                                        7 => 'Julio',
                                        8 => 'Agosto',
                                        9 => 'Septiembre',
                                        10 => 'Octubre',
                                        11 => 'Noviembre',
                                        12 => 'Diciembre'
                                    ];
                                    foreach ($meses_m as $nm => $nom_m) echo "<option value=\"$nm\">$nom_m</option>"; ?>
                                </select>
                                <label for="exp_m_mes">Mes</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="exp_m_tipo" name="filtro_tipo_registro">
                                    <option value="">Todos</option>
                                    <option value="Registro Actividad">Registro Actividad</option>
                                    <option value="Masivas">Masivas</option>
                                    <option value="Articulacion">Articulación</option>
                                </select>
                                <label for="exp_m_tipo">Tipo Registro</label>
                            </div>
                            <?php if (in_array($tipo_usuario, [5, 11])): ?>
                                <div class="col-md-6 mb-3 form-floating">
                                    <select class="form-select" id="exp_m_func" name="filtro_funcionario">
                                        <option value="">Todos los funcionarios</option>
                                        <?php
                                        if ($tipo_usuario == 11 && isset($_SESSION['id_grupo']) && $_SESSION['id_grupo']) {
                                            $id_grupo_exp_m = intval($_SESSION['id_grupo']);
                                            $func_m = $mysqli->query("SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (10,11,12) AND id_grupo = $id_grupo_exp_m ORDER BY nombre ASC");
                                        } else {
                                            $func_m = $mysqli->query("SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (5,10,11,12) ORDER BY nombre ASC");
                                        }
                                        if ($func_m) {
                                            while ($fu = $func_m->fetch_assoc()) echo "<option value=\"{$fu['id']}\">" . htmlspecialchars($fu['nombre']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <label for="exp_m_func">Funcionario</label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Descargar Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables globales para fechas seleccionadas
        let selectedDates = [];

        // Función para actualizar el display de fechas seleccionadas
        function updateSelectedDatesDisplay(dates) {
            selectedDates = dates;
            const displayDiv = document.getElementById('selected-dates-display');
            if (dates.length === 0) {
                displayDiv.innerHTML = '<small class="text-muted">Las fechas seleccionadas aparecerán aquí</small>';
            } else {
                let html = '';
                dates.forEach(date => {
                    const dateStr = date.toLocaleDateString('es-CO', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                    html += '<span class="date-tag">' + dateStr + '</span>';
                });
                displayDiv.innerHTML = html;
            }
        }

        // Inicializar Flatpickr para selección múltiple de fechas
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#fecha_atencion", {
                mode: "multiple",
                dateFormat: "Y-m-d",
                locale: "es",
                placeholder: "Selecciona las fechas de atención...",
                allowInput: false,
                clickOpens: true,
                onReady: function() {
                    console.log('Flatpickr listo');
                },
                onChange: function(selectedDatesArray, dateStr, instance) {
                    console.log("Fechas seleccionadas:", dateStr);
                    updateSelectedDatesDisplay(selectedDatesArray);
                    document.getElementById('fechas_seleccionadas').value = dateStr;
                }
            });
        });

        $(function() {
            // Inicialización DataTables con verificación de consistencia columnas
            if ($.fn.DataTable) {
                const headerCols = $('#tabla thead tr th').length;
                let valid = true;
                $('#tabla tbody tr').each(function() {
                    if ($(this).find('td').length !== headerCols) {
                        valid = false;
                    }
                });
                if (valid) {
                    $('#tabla').DataTable({
                        paging: false,
                        info: false,
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                        },
                        order: [
                            [7, 'desc']
                        ]
                    });
                }
            }

            const $form = $('#formAdd');
            const $meta = $('#meta');
            const $actividad = $('#actividad');
            const $accion = $('#accion');
            const $politica = $('#politica_publica');
            const $idHidden = $('#id_masiva_centro_vida');
            const $title = $('#modalAddTitle .mode-text');
            const originalAction = $form.attr('action');

            function resetActividad() {
                $actividad.html('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                resetAccion();
            }

            function resetAccion() {
                $accion.html('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                resetPolitica();
            }

            function resetPolitica() {
                $politica.html('<option value="" selected>Seleccione Política Pública...</option>');
            }

            // Meta -> Actividad
            $meta.on('change', function() {
                const idMeta = $(this).val();
                resetActividad();
                if (!idMeta) return;
                $actividad.prop('disabled', true).html('<option value="">Cargando...</option>');
                $.ajax({
                    url: '../contratista/getActividades.php',
                    type: 'POST',
                    data: {
                        id_meta: idMeta
                    },
                    success: function(r) {
                        $actividad.html('<option value="">Seleccione Actividad...</option>' + r).prop('disabled', false);
                    },
                    error: function() {
                        $actividad.html('<option value="">Error cargando actividades</option>');
                    }
                });
            });

            // Actividad -> Acción
            $actividad.on('change', function() {
                const idAct = $(this).val();
                resetAccion();
                if (!idAct) return;
                $accion.prop('disabled', true).html('<option value="">Cargando...</option>');
                $.ajax({
                    url: '../contratista/getAcciones.php',
                    type: 'POST',
                    data: {
                        id_actividad: idAct
                    },
                    success: function(r) {
                        $accion.html('<option value="">Seleccione Acción...</option>' + r).prop('disabled', false);
                    },
                    error: function() {
                        $accion.html('<option value="">Error cargando acciones</option>');
                    }
                });
            });

            // Acción -> Políticas
            $accion.on('change', function() {
                const idAcc = $(this).val();
                resetPolitica();
                if (!idAcc) return;
                $politica.prop('disabled', true).html('<option value="">Cargando...</option>');
                $.ajax({
                    url: '../personMovement/getPoliticaPublica.php',
                    type: 'POST',
                    data: {
                        id_accion: idAcc
                    },
                    dataType: 'json',
                    success: function(resp) {
                        resetPolitica();
                        if (resp && resp.politicas && resp.politicas.length) {
                            resp.politicas.forEach(p => $politica.append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>'));
                        } else {
                            $politica.append('<option value="">No asignada</option>');
                        }
                        $politica.prop('disabled', false);
                    },
                    error: function() {
                        resetPolitica();
                        $politica.append('<option value="">Error al consultar</option>');
                        $politica.prop('disabled', false);
                    }
                });
            });

            // Inicial: deshabilitar dependientes
            resetActividad();

            // Modo agregar al abrir
            // ---- Control Asistencia: flag y cedulas precargadas ----
            let _skipCAQuery = false;
            window._caCedulasToLoad = null;

            // Intercept header "Agregar" button (not edit buttons)
            $('#btnAbrirModalAdd').on('click', function() {
                if (_skipCAQuery) {
                    _skipCAQuery = false;
                    $('#modalAdd').modal('show');
                    return;
                }
                Swal.fire({
                    title: '¿Control de Asistencia?',
                    text: '¿Desea precargar las personas desde un control de asistencia guardado?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-calendar-check-fill"></i> Sí, cargar',
                    cancelButtonText: 'No, continuar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Seleccione la fecha',
                            html: '<input type="date" id="swal_cam_fecha" class="swal2-input" max="' + (function() {
                                var d = new Date();
                                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                            })() + '">',
                            didOpen: function() {
                                document.getElementById('swal_cam_fecha').value = (function() {
                                    var d = new Date();
                                    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                                })();
                            },
                            confirmButtonText: 'Cargar',
                            showCancelButton: true,
                            cancelButtonText: 'Cancelar',
                            preConfirm: function() {
                                const f = document.getElementById('swal_cam_fecha').value;
                                if (!f) {
                                    Swal.showValidationMessage('Seleccione una fecha');
                                }
                                return f;
                            }
                        }).then(function(dateResult) {
                            if (dateResult.isConfirmed && dateResult.value) {
                                $.ajax({
                                    url: 'getControlAsistencia.php',
                                    type: 'GET',
                                    data: {
                                        fecha: dateResult.value
                                    },
                                    dataType: 'json',
                                    success: function(resp) {
                                        if (resp.success && resp.cedulas.length > 0) {
                                            window._caCedulasToLoad = resp.cedulas;
                                        } else {
                                            window._caCedulasToLoad = null;
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Sin registros',
                                                text: 'No hay control de asistencia para esa fecha.',
                                                toast: true,
                                                position: 'top-end',
                                                timer: 3000,
                                                showConfirmButton: false
                                            });
                                        }
                                    },
                                    error: function() {
                                        window._caCedulasToLoad = null;
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'No se pudo obtener el control de asistencia.',
                                            toast: true,
                                            position: 'top-end',
                                            timer: 3000,
                                            showConfirmButton: false
                                        });
                                    },
                                    complete: function() {
                                        _skipCAQuery = true;
                                        $('#modalAdd').modal('show');
                                    }
                                });
                            } else {
                                _skipCAQuery = true;
                                window._caCedulasToLoad = null;
                                $('#modalAdd').modal('show');
                            }
                        });
                    } else {
                        _skipCAQuery = true;
                        window._caCedulasToLoad = null;
                        $('#modalAdd').modal('show');
                    }
                });
            });

            // Modo agregar al abrir
            $('#modalAdd').on('show.bs.modal', function(e) {
                const btn = $(e.relatedTarget);
                if (!btn || !btn.hasClass('btn-edit')) {
                    // modo agregar
                    $form.attr('action', 'addRegistroMasivoCentroVida.php');
                    $title.text('Agregar Actividad Masiva Centro Vida');
                    $('#btnSubmit').html('<i class="bi bi-save"></i> Guardar');
                    $idHidden.val('');
                    $form[0].reset();
                    $('input[name="jornada"]').prop('checked', false);
                    resetPersonasCVSection();
                    resetActividad();

                    // Precargar meta que empieza por "2." y luego actividad que empieza por "2.1."
                    const metaOption = $meta.find('option').filter(function() {
                        const text = $(this).text().trim();
                        return text.startsWith('2.');
                    }).first();

                    // Limpiar Flatpickr
                    const fpInstance = document.getElementById('fecha_atencion')._flatpickr;
                    if (fpInstance) {
                        fpInstance.clear();
                    }
                    selectedDates = [];
                    updateSelectedDatesDisplay([]);

                    if (metaOption.length) {
                        const metaId = metaOption.val();
                        $meta.val(metaId);

                        // Cargar actividades para esta meta
                        $actividad.prop('disabled', true).html('<option value="">Cargando...</option>');
                        $.post('../contratista/getActividades.php', {
                            id_meta: metaId
                        }, function(r) {
                            $actividad.html('<option value="">Seleccione Actividad...</option>' + r).prop('disabled', false);

                            // Buscar actividad que empieza por "2.1."
                            const actividadOption = $actividad.find('option').filter(function() {
                                const text = $(this).text().trim();
                                return text.startsWith('2.1.');
                            }).first();

                            if (actividadOption.length) {
                                $actividad.val(actividadOption.val());
                                $actividad.trigger('change');
                            }
                        });
                    }

                    return;
                }
                // modo edición
                const data = btn.data('json');
                $form.attr('action', 'editRegistroMasivoCentroVida.php');
                $title.text('Editar Actividad Masiva Centro Vida');
                $('#btnSubmit').html('<i class="bi bi-save"></i> Actualizar');
                $idHidden.val(data.id_registro);
                // Cargar fecha en Flatpickr si existe
                if (data.fecha_atencion) {
                    const fpInstance = document.getElementById('fecha_atencion')._flatpickr;
                    if (fpInstance) {
                        fpInstance.setDate(data.fecha_atencion);
                    }
                } else {
                    // Limpiar Flatpickr
                    const fpInstance = document.getElementById('fecha_atencion')._flatpickr;
                    if (fpInstance) {
                        fpInstance.clear();
                    }
                }
                $('#nombre_lider').val(data.nombre_lider || '');
                $('#telefono_contacto').val(data.telefono_contacto || '');
                $('#centro_vida').val(data.id_centro_vida || '');
                $('#id_comuna').val(data.id_comuna || '');
                $('#medio_verificacion').val(data.medio_verificacion || '');
                $('#cantidad_masculino').val(data.cantidad_masculino || 0);
                $('#cantidad_femenino').val(data.cantidad_femenino || 0);
                $('#observacion_actividad').val(data.observacion_actividad || '');
                $('#funcionario_responsable').val(data.funcionario_responsable || '');
                $('#actividad_centro_vida').val(data.id_actividad_centro_vida || '');
                $('#profesion_masivo').val(data.profesion || '');
                // tipo_registro preload
                $('#tipo_registro').val(data.tipo_registro || '');
                toggleSeccionPersonasCV(data.tipo_registro || '');
                // jornada preload (radio)
                $('input[name="jornada"]').prop('checked', false);
                if (data.jornada) {
                    $('input[name="jornada"][value="' + data.jornada.trim() + '"]').prop('checked', true);
                }
                // Carga en cascada: meta -> actividad -> acción -> política
                const metaId = data.id_meta || '';
                const actividadId = data.id_actividad || '';
                const accionId = data.id_accion || '';
                const politicaId = data.politica_publica || '';
                $meta.val(metaId);
                if (metaId) {
                    $actividad.prop('disabled', true).html('<option value="">Cargando...</option>');
                    $.post('../contratista/getActividades.php', {
                        id_meta: metaId
                    }, function(r) {
                        $actividad.html('<option value="">Seleccione Actividad...</option>' + r).prop('disabled', false);
                        if (actividadId) {
                            $actividad.val(actividadId);
                            $accion.prop('disabled', true).html('<option value="">Cargando...</option>');
                            $.post('../contratista/getAcciones.php', {
                                id_actividad: actividadId
                            }, function(r2) {
                                $accion.html('<option value="">Seleccione Acción...</option>' + r2).prop('disabled', false);
                                if (accionId) {
                                    $accion.val(accionId);
                                    // Políticas
                                    $politica.prop('disabled', true).html('<option value="">Cargando...</option>');
                                    $.ajax({
                                        url: '../personMovement/getPoliticaPublica.php',
                                        type: 'POST',
                                        data: {
                                            id_accion: accionId
                                        },
                                        dataType: 'json',
                                        success: function(resp) {
                                            $politica.html('<option value="">Seleccione Política Pública...</option>');
                                            if (resp && resp.politicas) {
                                                resp.politicas.forEach(p => $politica.append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>'));
                                            }
                                            if (politicaId) {
                                                $politica.val(politicaId);
                                            }
                                            $politica.prop('disabled', false);
                                        },
                                        error: function() {
                                            $politica.html('<option value="">Error al cargar</option>').prop('disabled', false);
                                        }
                                    });
                                }
                            });
                        }
                    });
                } else {
                    resetActividad();
                }
            });

            // Restaurar a modo agregar al cerrar
            $('#modalAdd').on('hidden.bs.modal', function() {
                $form.attr('action', originalAction);
                $title.text('Agregar Actividad Masiva Centro Vida');
                $('#btnSubmit').html('<i class="bi bi-save"></i> Guardar');
                $idHidden.val('');
                $form[0].reset();
                $('input[name="jornada"]').prop('checked', false);
                resetPersonasCVSection();
                resetActividad();
                // Limpiar Flatpickr
                const fpInstance = document.getElementById('fecha_atencion')._flatpickr;
                if (fpInstance) {
                    fpInstance.clear();
                }
                selectedDates = [];
                updateSelectedDatesDisplay([]);
            });

            // Eliminar
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (!id) return;
                Swal.fire({
                    title: '¿Eliminar registro?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(res => {
                    if (res.isConfirmed) {
                        window.location = 'formMasivoCentroVida.php?delete=' + id;
                    }
                });
            });

            // Debounced fetch for counts by activity+date
            let _countTimer = null;

            function fetchCountsForSelection() {
                clearTimeout(_countTimer);
                _countTimer = setTimeout(function() {
                    const actividad = $('#actividad_centro_vida').val();
                    const fecha = $('#fecha_atencion').val();
                    const tipo = $('#tipo_registro').val();
                    if (tipo !== 'Registro Actividad') {
                        // clear and skip
                        $('#cantidad_masculino').val(0);
                        $('#cantidad_femenino').val(0);
                        return;
                    }
                    if (!actividad || !fecha) return;
                    $.post('countRegistrosByActivityDate.php', {
                        id_actividad_centro_vida: actividad,
                        fecha_atencion: fecha
                    }, function(resp) {
                        if (resp && !resp.error) {
                            $('#cantidad_masculino').val(resp.masculino || 0);
                            $('#cantidad_femenino').val(resp.femenino || 0);
                        }
                    }, 'json');
                }, 300);
            }

            // Trigger when the user changes the date or the activity inside the modal
            $(document).on('change', '#fecha_atencion', function() {
                fetchCountsForSelection();
            });
            $(document).on('change', '#actividad_centro_vida', function() {
                fetchCountsForSelection();
            });
            // cuando el tipo cambia, actualizar o limpiar según corresponda
            $(document).on('change', '#tipo_registro', function() {
                fetchCountsForSelection();
                toggleSeccionPersonasCV($(this).val());
            });

            // ================== SECCIÓN PERSONAS CV ==================
            let personasAgregadasCV = []; // {cedula, nombre, genero}

            function toggleSeccionPersonasCV(tipoReg) {
                if (tipoReg === 'Registro Actividad') {
                    $('#seccion_personas_cv').removeClass('d-none');
                    // Si hay cédulas del control de asistencia pendientes, cargarlas directamente
                    if (window._caCedulasToLoad && window._caCedulasToLoad.length > 0) {
                        const cedulasPendientes = window._caCedulasToLoad.slice();
                        window._caCedulasToLoad = null;
                        $.ajax({
                            url: 'getPersonasByCedulas.php',
                            type: 'POST',
                            data: {
                                cedulas: JSON.stringify(cedulasPendientes)
                            },
                            dataType: 'json',
                            success: function(resp) {
                                if (!resp.success || !resp.personas.length) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Control de Asistencia',
                                        text: 'No se encontraron personas para las cédulas guardadas.',
                                        toast: true,
                                        position: 'top-end',
                                        timer: 3000,
                                        showConfirmButton: false
                                    });
                                    return;
                                }
                                let agregadas = 0;
                                resp.personas.forEach(function(p) {
                                    const cedula = p.cedula_persona;
                                    const nombre = p.nombres_persona + ' ' + p.apellidos_persona;
                                    const genero = p.genero_persona || '';
                                    if (!personasAgregadasCV.find(function(x) {
                                            return x.cedula === cedula;
                                        })) {
                                        personasAgregadasCV.push({
                                            cedula: cedula,
                                            nombre: nombre,
                                            genero: genero
                                        });
                                        const item = $('<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"></div>');
                                        item.append('<span><i class="bi bi-person-fill me-2"></i>' + nombre + ' — <small>' + cedula + '</small></span>');
                                        const btnRem = $('<button type="button" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>');
                                        (function(c, itm) {
                                            btnRem.on('click', function() {
                                                personasAgregadasCV = personasAgregadasCV.filter(function(x) {
                                                    return x.cedula !== c;
                                                });
                                                itm.remove();
                                                if (personasAgregadasCV.length === 0) $('#cedulas_cv_container').hide();
                                                actualizarContadoresCV();
                                            });
                                        })(cedula, item);
                                        item.append(btnRem);
                                        $('#lista_cedulas_cv').append(item);
                                        agregadas++;
                                    }
                                });
                                if (agregadas > 0) {
                                    $('#cedulas_cv_container').show();
                                    // Contar géneros y actualizar inputs
                                    let cntMasc = 0,
                                        cntFem = 0;
                                    personasAgregadasCV.forEach(function(px) {
                                        const g = (px.genero || '').toLowerCase();
                                        if (g === 'masculino') cntMasc++;
                                        else if (g === 'femenino') cntFem++;
                                    });
                                    $('#cantidad_masculino').val(cntMasc);
                                    $('#cantidad_femenino').val(cntFem);
                                    actualizarContadoresCV();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Control de Asistencia',
                                        text: agregadas + ' persona(s) precargadas: ' + cntMasc + ' masculino(s), ' + cntFem + ' femenino(s).',
                                        toast: true,
                                        position: 'top-end',
                                        timer: 4000,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudieron cargar las personas del control de asistencia.',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                } else {
                    $('#seccion_personas_cv').addClass('d-none');
                }
                actualizarContadoresCV();
            }

            function actualizarContadoresCV() {
                const sel = personasAgregadasCV.length;
                
                // Calcular cantidad de hombres y mujeres de las personas agregadas
                let contMasc = 0, contFem = 0;
                personasAgregadasCV.forEach(p => {
                    if (p.genero && p.genero.toLowerCase().includes('masculino')) {
                        contMasc++;
                    } else if (p.genero && p.genero.toLowerCase().includes('femenino')) {
                        contFem++;
                    }
                });
                
                // Actualizar automáticamente los campos de cantidad si hay personas agregadas
                if (sel > 0) {
                    $('#cantidad_masculino').val(contMasc);
                    $('#cantidad_femenino').val(contFem);
                }
                
                // Actualizar contadores informativos
                const req = contMasc + contFem; // Basado en personas agregadas
                $('#contador_req_cv').text(req + ' requeridas');
                $('#contador_sel_cv').text(sel + ' agregadas');
                $('#contador_cedulas_cv').text(sel);
                
                if (sel > 0 && sel === req) {
                    $('#contador_sel_cv').removeClass('bg-danger bg-warning').addClass('bg-success');
                } else if (sel > 0) {
                    $('#contador_sel_cv').removeClass('bg-success bg-danger').addClass('bg-warning');
                } else {
                    $('#contador_sel_cv').removeClass('bg-success bg-warning').addClass('bg-danger');
                }
                $('#cedulas_json_cv').val(JSON.stringify(personasAgregadasCV.map(p => p.cedula)));
            }

            function actualizarContadoresCV_SoloContadores() {
                const req = (parseInt($('#cantidad_masculino').val()) || 0) + (parseInt($('#cantidad_femenino').val()) || 0);
                const sel = personasAgregadasCV.length;
                $('#contador_req_cv').text(req + ' requeridas');
                $('#contador_sel_cv').text(sel + ' agregadas');
                $('#contador_cedulas_cv').text(sel);
                if (sel > 0 && sel === req) {
                    $('#contador_sel_cv').removeClass('bg-danger bg-warning').addClass('bg-success');
                } else if (sel > 0) {
                    $('#contador_sel_cv').removeClass('bg-success bg-danger').addClass('bg-warning');
                } else {
                    $('#contador_sel_cv').removeClass('bg-success bg-warning').addClass('bg-danger');
                }
                $('#cedulas_json_cv').val(JSON.stringify(personasAgregadasCV.map(p => p.cedula)));
            }

            function resetPersonasCVSection() {
                personasAgregadasCV = [];
                $('#area_personas_cv').hide();
                $('#cedulas_cv_container').hide();
                $('#lista_cedulas_cv').empty();
                $('#tbody_personas_cv').empty();
                $('#seccion_personas_cv').addClass('d-none');
                $('#cedulas_json_cv').val('');
            }

            // Filtro de texto y jornada sobre la tabla de personas
            function filtrarPersonasCV() {
                const texto = $('#buscar_persona_cv_input').val().toLowerCase();
                const jornada = $('input[name="filtroJornadaCV"]:checked').val();
                $('#tbody_personas_cv tr').each(function() {
                    const nombre = $(this).data('nombre') || '';
                    const cedula = $(this).data('cedula') || '';
                    const jorn = $(this).data('jornada') || '';
                    const textoMatch = !texto || nombre.includes(texto) || cedula.includes(texto);
                    const jornadaMatch = jornada === 'todos' || !jornada || jorn === jornada;
                    $(this).toggle(textoMatch && jornadaMatch);
                });
            }

            $('#buscar_persona_cv_input').on('input', filtrarPersonasCV);
            $('input[name="filtroJornadaCV"]').on('change', filtrarPersonasCV);

            // Cargar personas del CV seleccionado
            $('#btn_cargar_personas_cv').on('click', function() {
                const idGrupo = $('#filtro_grupo_cv_personas').val();
                if (!idGrupo) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Seleccione un Centro de Vida',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                $.ajax({
                    url: 'obtenerPersonasCentroVida.php',
                    type: 'POST',
                    data: {
                        id_grupo: idGrupo
                    },
                    dataType: 'json',
                    success: function(personas) {
                        $('#tbody_personas_cv').empty();
                        if (!personas || personas.length === 0) {
                            $('#tbody_personas_cv').append('<tr><td colspan="6" class="text-center text-muted">No hay personas en este Centro de Vida</td></tr>');
                        } else {
                            personas.forEach(function(p) {
                                const jornBadge = p.jornada ? '<span class="badge bg-secondary">' + p.jornada + '</span>' : '';
                                const tr = $('<tr></tr>')
                                    .data('cedula', p.cedula_persona)
                                    .data('nombre', (p.nombres_persona + ' ' + p.apellidos_persona).toLowerCase())
                                    .data('jornada', p.jornada || '')
                                    .data('genero', p.genero_persona || '');
                                tr.append('<td><input type="checkbox" class="form-check-input chk_persona_cv" value="' + p.cedula_persona + '" data-nombre="' + p.nombres_persona + ' ' + p.apellidos_persona + '" data-genero="' + (p.genero_persona || '') + '"></td>');
                                tr.append('<td>' + p.cedula_persona + '</td>');
                                tr.append('<td>' + p.nombres_persona + '</td>');
                                tr.append('<td>' + p.apellidos_persona + '</td>');
                                tr.append('<td>' + (p.genero_persona || '') + '</td>');
                                tr.append('<td>' + jornBadge + '</td>');
                                $('#tbody_personas_cv').append(tr);
                            });
                        }
                        $('#total_personas_cv').text(personas.length);
                        $('#area_personas_cv').show();
                        filtrarPersonasCV();
                        // Si hay personas del Control de Asistencia precargadas, marcarlas
                        if (window._caCedulasToLoad && window._caCedulasToLoad.length > 0) {
                            let marcadas = 0;
                            $('.chk_persona_cv').each(function() {
                                if (window._caCedulasToLoad.indexOf($(this).val()) !== -1) {
                                    $(this).prop('checked', true);
                                    marcadas++;
                                }
                            });
                            window._caCedulasToLoad = null;
                            if (marcadas > 0) {
                                // Agregar automáticamente las personas marcadas a "Personas Agregadas"
                                $('#btn_agregar_sel_cv').trigger('click');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Control de Asistencia',
                                    text: marcadas + ' persona(s) del control de asistencia fueron agregadas automáticamente.',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 3500,
                                    showConfirmButton: false
                                });
                            }
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al cargar personas',
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    complete: function() {
                        $('#btn_cargar_personas_cv').prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Cargar Personas');
                    }
                });
            });

            $('#chk_all_personas_cv').on('change', function() {
                $('.chk_persona_cv:visible').prop('checked', $(this).is(':checked'));
            });
            $('#btn_sel_todas_cv').on('click', function() {
                $('.chk_persona_cv:visible').prop('checked', true);
            });
            $('#btn_desel_todas_cv').on('click', function() {
                $('.chk_persona_cv:visible').prop('checked', false);
            });

            $('#btn_agregar_sel_cv').on('click', function() {
                $('.chk_persona_cv:checked').each(function() {
                    const cedula = $(this).val();
                    const nombre = $(this).data('nombre');
                    const genero = $(this).data('genero');
                    if (!personasAgregadasCV.find(p => p.cedula === cedula)) {
                        personasAgregadasCV.push({
                            cedula,
                            nombre,
                            genero
                        });
                        const item = $('<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"></div>');
                        item.append('<span><i class="bi bi-person-fill me-2"></i>' + nombre + ' — <small>' + cedula + '</small></span>');
                        const btnRem = $('<button type="button" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>');
                        btnRem.on('click', function() {
                            personasAgregadasCV = personasAgregadasCV.filter(p => p.cedula !== cedula);
                            item.remove();
                            if (personasAgregadasCV.length === 0) $('#cedulas_cv_container').hide();
                            actualizarContadoresCV();
                        });
                        item.append(btnRem);
                        $('#lista_cedulas_cv').append(item);
                    }
                });
                $('.chk_persona_cv').prop('checked', false);
                $('#chk_all_personas_cv').prop('checked', false);
                if (personasAgregadasCV.length > 0) $('#cedulas_cv_container').show();
                actualizarContadoresCV();
            });

            // Actualizar contadores cuando cambian cantidades (solo contadores, NO recalcular desde géneros)
            $(document).on('input', '#cantidad_masculino, #cantidad_femenino', function() {
                actualizarContadoresCV_SoloContadores();
            });

            // Validación del formulario: verificar fechas seleccionadas
            $('#formAdd').on('submit', function(e) {
                // Siempre prevenir el submit normal para usar AJAX
                e.preventDefault();

                console.log('Fechas seleccionadas:', selectedDates.length);

                // Verificar que se hayan seleccionado fechas
                if (selectedDates.length === 0) {
                    console.log('❌ Formulario bloqueado: Sin fechas');
                    Swal.fire({
                        title: 'Fechas Requeridas',
                        text: 'Debe seleccionar al menos una fecha de atención.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    $('#fecha_atencion').focus();
                    return false;
                }

                console.log('✅ Validación pasada, enviando formulario por AJAX...');

                // Preparar datos del formulario
                let formData = $(this).serialize();

                // Agregar las fechas seleccionadas como JSON
                formData += '&fechas_atencion=' + encodeURIComponent(JSON.stringify(selectedDates.map(d => d.toISOString().split('T')[0])));

                console.log('📋 Datos del formulario completos');

                // Enviar por AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('📤 Enviando solicitud AJAX...');
                        // Deshabilitar botón de envío
                        const $submitBtn = $('#formAdd button[type="submit"]');
                        $submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Guardando...');
                    },
                    success: function(response) {
                        console.log('✅ Respuesta AJAX exitosa:', response);

                        if (response.success) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: response.message || 'Registros guardados correctamente',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Recargar página
                                window.location.href = 'formMasivoCentroVida.php';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Error al guardar',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            const $submitBtn = $('#formAdd button[type="submit"]');
                            $submitBtn.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Error AJAX:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al conectar con el servidor: ' + error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        const $submitBtn = $('#formAdd button[type="submit"]');
                        $submitBtn.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar');
                    }
                });

                return false;
            });
        });
    </script>

    <!-- ====== Control de Asistencia (Masivo) JS ====== -->
    <script>
        $(function() {

            // ---- Cargar personas en modal CAM ----
            $('#cam_btnCargar').on('click', function() {
                const idGrupo = $('#cam_filtro_grupo').val();
                if (!idGrupo) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Seleccione un Centro de Vida',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }
                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                $.ajax({
                    url: 'obtenerPersonasCentroVida.php',
                    type: 'POST',
                    data: {
                        id_grupo: idGrupo
                    },
                    dataType: 'json',
                    success: function(personas) {
                        $('#cam_listaPersonas').empty();
                        $('#cam_selectAll').prop('checked', false);
                        if (!personas || personas.length === 0) {
                            $('#cam_listaPersonas').html('<p class="text-muted text-center">No hay personas en este Centro de Vida.</p>');
                        } else {
                            personas.forEach(function(p) {
                                const jornBadge = p.jornada ? ' <span class="badge bg-secondary">' + p.jornada + '</span>' : '';
                                const item = $('<div class="form-check persona-cam-item"></div>')
                                    .attr('data-jornada', p.jornada || '');
                                const chk = $('<input class="form-check-input persona-cam-checkbox" type="checkbox">')
                                    .val(p.cedula_persona);
                                const lbl = $('<label class="form-check-label"></label>')
                                    .html(p.nombres_persona + ' ' + p.apellidos_persona + ' — <small>' + p.cedula_persona + '</small>' + jornBadge);
                                item.append(chk).append(lbl);
                                $('#cam_listaPersonas').append(item);
                            });
                        }
                        $('#cam_areaPersonas').show();
                        filtrarCAM();
                        // Si ya hay fecha seleccionada, precargar asistencia guardada
                        const fechaActual = $('#cam_fecha').val();
                        if (fechaActual) cargarAsistenciaGuardadaCAM(fechaActual);
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al cargar personas',
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Cargar Personas');
                    }
                });
            });

            // ---- Precargar asistencia guardada al cambiar fecha (CAM) ----
            function cargarAsistenciaGuardadaCAM(fecha) {
                if (!fecha) return;
                // Solo si ya hay personas cargadas en la lista
                if ($('#cam_listaPersonas .persona-cam-checkbox').length === 0) return;
                $.ajax({
                    url: 'getControlAsistencia.php',
                    type: 'GET',
                    data: {
                        fecha: fecha
                    },
                    dataType: 'json',
                    success: function(resp) {
                        $('#cam_listaPersonas .persona-cam-checkbox').prop('checked', false);
                        if (resp.success && resp.cedulas.length > 0) {
                            let marcadas = 0;
                            $('#cam_listaPersonas .persona-cam-checkbox').each(function() {
                                if (resp.cedulas.indexOf($(this).val()) !== -1) {
                                    $(this).prop('checked', true);
                                    marcadas++;
                                }
                            });
                            if (marcadas > 0) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Asistencia cargada',
                                    text: marcadas + ' persona(s) precargadas del registro guardado para esta fecha.',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }
                        }
                        updateCAMCounter();
                    }
                });
            }

            $('#cam_fecha').on('change', function() {
                cargarAsistenciaGuardadaCAM($(this).val());
            });

            // ---- Filtros modal CAM ----
            function filtrarCAM() {
                const q = $('#cam_searchPersona').val().toLowerCase().trim();
                const jornada = $('input[name="cam_filtroJornada"]:checked').val();
                $('#cam_listaPersonas .persona-cam-item').each(function() {
                    const txt = $(this).text().toLowerCase();
                    const pJornada = $(this).data('jornada') || '';
                    const mText = !q || txt.indexOf(q) !== -1;
                    const mJorn = jornada === 'todos' || pJornada === jornada;
                    $(this).toggle(mText && mJorn);
                });
                updateCAMCounter();
            }

            function updateCAMCounter() {
                const total = $('#cam_listaPersonas .persona-cam-checkbox:checked').length;
                const visible = $('#cam_listaPersonas .persona-cam-checkbox:visible').length;
                const chkVis = $('#cam_listaPersonas .persona-cam-checkbox:visible:checked').length;
                $('#cam_contador').text(total);
                $('#cam_selectAll').prop('checked', visible > 0 && visible === chkVis);
            }

            $('#cam_searchPersona').on('input', filtrarCAM);
            $('input[name="cam_filtroJornada"]').on('change', filtrarCAM);

            $('#cam_selectAll').on('change', function() {
                $('#cam_listaPersonas .persona-cam-checkbox:visible').prop('checked', $(this).is(':checked'));
                updateCAMCounter();
            });

            $(document).on('change', '#cam_listaPersonas .persona-cam-checkbox', updateCAMCounter);

            // Limpiar al cerrar
            $('#modalControlAsistenciaM').on('hidden.bs.modal', function() {
                $('#cam_listaPersonas').empty();
                $('#cam_areaPersonas').hide();
                $('#cam_selectAll').prop('checked', false);
                $('#cam_searchPersona').val('');
                $('#cam_fecha').val('');
                $('input[name="cam_filtroJornada"][value="todos"]').prop('checked', true);
            });

            // ---- Guardar Control de Asistencia (Masivo) ----
            $('#cam_btnGuardar').on('click', function() {
                const fecha = $('#cam_fecha').val();
                if (!fecha) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha requerida',
                        text: 'Seleccione una fecha de asistencia.',
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false
                    });
                    return;
                }
                const cedulas = [];
                $('#cam_listaPersonas .persona-cam-checkbox:checked').each(function() {
                    cedulas.push($(this).val());
                });
                if (cedulas.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin personas',
                        text: 'Seleccione al menos una persona.',
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false
                    });
                    return;
                }
                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
                $.ajax({
                    url: 'saveControlAsistencia.php',
                    type: 'POST',
                    data: {
                        cedulas: JSON.stringify(cedulas),
                        fecha_asistencia: fecha
                    },
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Asistencia guardada',
                                text: resp.count + ' persona(s) registradas para el ' + fecha + '.',
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                showConfirmButton: false
                            });
                            $('#modalControlAsistenciaM').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: resp.message || 'No se pudo guardar.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo conectar con el servidor.'
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar Asistencia');
                    }
                });
            });

        });
    </script>
</body>

</html>