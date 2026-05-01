<?php
session_start();
require_once('../filtros_grupos.php');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDSYP - Registros Centro Vida</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    <link rel="stylesheet" type="text/css" href="styles.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
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
        /* Estilos modernos personalizados */
        body {
            font-size: 16px !important;
            background-color: #f8fafc;
        }

        .modern-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px auto;
            max-width: 1400px;
        }

        .modern-header {
            background: linear-gradient(135deg, #e91e63 0%, #9c27b0 100%);
            color: white;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .modern-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .btn-modern {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-modern:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            color: white;
        }

        /* Botones específicos para la barra de filtros (más visibles) */
        .modern-filters .btn-modern.btn-primary {
            background: #10b981;
            /* verde agradable */
            border-color: #10b981;
            color: #ffffff;
            padding: 10px 16px;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.12);
        }

        .modern-filters .btn-modern.btn-primary:hover {
            background: #059669;
            border-color: #059669;
            transform: translateY(-2px);
        }

        .modern-filters .btn-modern.btn-secondary {
            background: #6b7280;
            /* gris */
            border-color: #6b7280;
            color: #fff;
            padding: 10px 14px;
        }

        .filter-row .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Aumentar ancho mínimo para inputs en filtros */
        .filter-group {
            min-width: 180px;
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

        /* Tabla moderna */
        .modern-table-wrapper {
            padding: 0;
            overflow-x: auto;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 14px;
        }

        .modern-table th {
            background: #f8fafc;
            color: #374151;
            font-weight: 600;
            padding: 12px 8px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
            font-size: 13px;
        }

        .modern-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            font-size: 13px;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .modern-table tr:hover {
            background: #f9fafb;
        }

        /* Filtros modernos */
        .modern-filters {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        :root { --filter-input-height: 44px; }

        .modern-input,
        .modern-select,
        .filter-group input[type="month"] {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            transition: border-color 0.2s;
            height: var(--filter-input-height);
            box-sizing: border-box;
            line-height: normal;
            display: inline-block;
            vertical-align: middle;
        }

        .modern-input:focus,
        .modern-select:focus {
            outline: none;
            border-color: #e91e63;
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
        }

        /* Botones de acción */
        .col-actions {
            width: 120px !important;
            text-align: center !important;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
            align-items: center;
        }

        .btn-action {
            border: none;
            border-radius: 4px;
            padding: 6px 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-edit {
            background-color: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }

            .filter-group {
                min-width: 100%;
            }

            .modern-header {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Estilos para validación de campos */
        .form-control.is-valid {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
        }

        .form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Toast de SweetAlert */
        .swal2-toast {
            font-size: 14px !important;
        }
    </style>
</head>

<?php
include("../../conexion.php");
require_once('../filtros_grupo_usuario.php');

// Obtener condiciones
$condiciones = "SELECT * FROM condiciones_componente";
$result_condiciones_query = mysqli_query($mysqli, $condiciones);
if (!$result_condiciones_query) {
    die("Error en la consulta condiciones: " . mysqli_error($mysqli));
}
$result_condiciones = mysqli_fetch_all($result_condiciones_query, MYSQLI_ASSOC);

// Obtener metas
$metas = "SELECT * FROM metas ORDER BY descripcion_meta ASC";
$result_metas_query = mysqli_query($mysqli, $metas);
if (!$result_metas_query) {
    die("Error en la consulta metas: " . mysqli_error($mysqli));
}
$result_metas = mysqli_fetch_all($result_metas_query, MYSQLI_ASSOC);

// Obtener actividades centro vida
$actividades_cv = "SELECT id_actividad_centro_vida, descripcion_actividad FROM actividad_centro_vida ORDER BY descripcion_actividad ASC";
$result_actividades_cv_query = $mysqli->query($actividades_cv);
if (!$result_actividades_cv_query) {
    die("Error en consulta actividades centro vida: " . $mysqli->error);
}
$result_actividades_cv = $result_actividades_cv_query->fetch_all(MYSQLI_ASSOC);

// Obtener lista de personas para el modal masivo (ordenadas alfabéticamente)
// Solo personas relacionadas con grupos que inician con "CV"
// Excluir personas cuya condicion_componente contenga "Inactivo" (ej: "C.V Beneficiario Inactivo")
$personas_sql = "SELECT p.cedula_persona, CONCAT(p.nombres_persona, ' ', p.apellidos_persona) AS nombre_completo, p.jornada
                 FROM personas p
                 INNER JOIN grupos g ON p.id_grupo = g.id_grupo
                 WHERE g.descripcion_grupo LIKE 'CV%'
                 AND (p.condicion_componente IS NULL OR p.condicion_componente NOT LIKE '%Inactivo%')";

// Aplicar filtro por grupo de usuario si corresponde (tipo 11: INGENIERO CENTRO VIDA)
$personas_sql .= obtenerCondicionFiltroGrupo('p');
// Para tipo 10 (CONTRATISTA CV): solo personas de su centro asignado
$_tipo_u_temp = isset($_SESSION['tipo_usuario']) ? (int)$_SESSION['tipo_usuario'] : 0;
if ($_tipo_u_temp === 10) {
    $_id_grupo_temp = isset($_SESSION['id_grupo']) ? (int)$_SESSION['id_grupo'] : 0;
    if ($_id_grupo_temp > 0) {
        $personas_sql .= " AND p.id_grupo = $_id_grupo_temp";
    }
}
$personas_sql .= " ORDER BY p.nombres_persona ASC, p.apellidos_persona ASC";

$result_personas_query = $mysqli->query($personas_sql);
if (!$result_personas_query) {
    die("Error en consulta personas: " . $mysqli->error);
}
$result_personas = $result_personas_query->fetch_all(MYSQLI_ASSOC);

// Variables de sesión para control de permisos
$tipo_usuario_cv = isset($_SESSION['tipo_usuario']) ? (int)$_SESSION['tipo_usuario'] : 0;
$id_usuario_cv   = isset($_SESSION['id'])           ? (int)$_SESSION['id']           : 0;

// Grupos CV para el modal de exportación
$cvs_export = [];
$cvs_export_query = "SELECT id_grupo, descripcion_grupo FROM grupos WHERE descripcion_grupo LIKE 'CV%' ORDER BY descripcion_grupo ASC";
$cvs_export_res = $mysqli->query($cvs_export_query);
if ($cvs_export_res) { $cvs_export = $cvs_export_res->fetch_all(MYSQLI_ASSOC); }

// Usuarios funcionarios para el modal de exportación
$funcionarios_export = [];
if (in_array($tipo_usuario_cv, [5, 11])) {
    $func_query = "SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (5, 10, 11, 12) ORDER BY nombre ASC";
    if ($tipo_usuario_cv === 11) {
        // Solo funcionarios de su grupo
        $id_grupo_func = isset($_SESSION['id_grupo']) ? (int)$_SESSION['id_grupo'] : 0;
        if ($id_grupo_func > 0) {
            $func_query = "SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (10, 11, 12) AND id_grupo = $id_grupo_func ORDER BY nombre ASC";
        }
    }
    $func_res = $mysqli->query($func_query);
    if ($func_res) { $funcionarios_export = $func_res->fetch_all(MYSQLI_ASSOC); }
}

// Obtener grupos externos activos para los modales
$ge_query = "SELECT id_grupo_externo, nombre_grupo_externo FROM grupos_externos WHERE activo=1 ORDER BY nombre_grupo_externo ASC";
$ge_result = $mysqli->query($ge_query);
$grupos_externos_list = $ge_result ? $ge_result->fetch_all(MYSQLI_ASSOC) : [];
// Generar HTML de opciones para JS
$grupoExternoOptionsHtml = '<option value="">Seleccione...</option>';
foreach ($grupos_externos_list as $ge) {
    $grupoExternoOptionsHtml .= '<option value="' . htmlspecialchars($ge['id_grupo_externo']) . '">' . htmlspecialchars($ge['nombre_grupo_externo']) . '</option>';
}

// Procesar filtros
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['cedula_persona']) && !empty($_GET['cedula_persona'])) {
    $where_conditions[] = "p.cedula_persona = ?";
    $params[] = $_GET['cedula_persona'];
    $types .= "s";
}

if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
    $where_conditions[] = "(p.nombres_persona LIKE ? OR p.apellidos_persona LIKE ?)";
    $params[] = "%" . $_GET['nombre'] . "%";
    $params[] = "%" . $_GET['nombre'] . "%";
    $types .= "ss";
}

if (isset($_GET['actividad']) && !empty($_GET['actividad'])) {
    $where_conditions[] = "rcv.id_actividad_centro_vida = ?";
    $params[] = $_GET['actividad'];
    $types .= "i";
}

// Función para eliminar registro
if (isset($_GET['delete'])) {
    $id_registro = $_GET['delete'];
    deleteRegistro($id_registro);
}

function deleteRegistro($id_registro)
{
    global $mysqli;

    // Primero eliminar las fechas asociadas
    $query_fechas = "DELETE FROM registro_centro_vida_fechas WHERE id_registro_centro_vida = ?";
    $stmt_fechas = $mysqli->prepare($query_fechas);
    $stmt_fechas->bind_param("i", $id_registro);
    $stmt_fechas->execute();
    $stmt_fechas->close();

    // Luego eliminar el registro principal
    $query = "DELETE FROM registro_centro_vida WHERE id_registro_centro_vida = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_registro);

    if ($stmt->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Eliminado!',
                    text: 'El registro ha sido eliminado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#10b981'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'formCentroVida.php';
                    }
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al eliminar el registro. Inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#ef4444'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'formCentroVida.php';
                    }
                });
            });
        </script>";
    }

    $stmt->close();
}

?>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #e91e63; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;">
        <b><i class="bi bi-heart-fill"></i> REGISTROS ACTIVIDADES CENTRO VIDA</b>
    </h1>

    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-heart-fill"></i> Registros Centro Vida</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewRecord">
                        <i class="bi bi-plus-circle-fill"></i>
                        Agregar Registro
                    </button>
                    <button type="button" id="btnAbrirMasivo" class="btn-modern btn-warning">
                        <i class="bi bi-people-fill"></i>
                        Agregar Grupal 
                    </button>
                    <button type="button" class="btn-modern" style="background:rgba(0,188,212,0.35);border-color:rgba(0,188,212,0.6);" data-bs-toggle="modal" data-bs-target="#modalControlAsistencia">
                        <i class="bi bi-calendar-check-fill"></i>
                        Control Asistencia
                    </button>
                    <button type="button" class="btn-modern" data-bs-toggle="modal" data-bs-target="#modalExportCentroVida">
                        <i class="bi bi-file-excel"></i>
                        Exportar Excel
                    </button>
                    <?php if ($tipo_usuario_cv === 11): ?>
                    <button type="button" class="btn-modern" style="background:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.4);" data-bs-toggle="modal" data-bs-target="#modalConsolidado">
                        <i class="bi bi-calendar3"></i>
                        Consolidado por Mes
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mensaje informativo de filtro por grupo -->
            <?php echo generarMensajeFiltroGrupo($mysqli); ?>
            <?php echo generarMensajeFiltroPropio(); ?>

            <!-- Filtros modernos -->
            <div class="modern-filters">
                <form action="formCentroVida.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="filter_cedula_persona">Cédula</label>
                        <input type="number"
                            id="filter_cedula_persona"
                            name="cedula_persona"
                            class="modern-input"
                            placeholder="Buscar por cédula..."
                            value="<?= isset($_GET['cedula_persona']) ? htmlspecialchars($_GET['cedula_persona']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <label for="nombre">Nombre</label>
                        <input type="text"
                            id="nombre"
                            name="nombre"
                            class="modern-input"
                            placeholder="Buscar por nombre..."
                            value="<?= isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <label for="mes">Mes</label>
                        <input type="month"
                            id="mes"
                            name="mes"
                            class="modern-input"
                            value="<?= isset($_GET['mes']) ? htmlspecialchars($_GET['mes']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <label for="actividad">Actividad</label>
                        <select name="actividad" id="actividad" class="modern-select">
                            <option value="">Todas las actividades</option>
                            <?php foreach ($result_actividades_cv as $actividad) {
                                $selected = (isset($_GET['actividad']) && $_GET['actividad'] == $actividad['id_actividad_centro_vida']) ? 'selected' : '';
                            ?>
                                <option value="<?= $actividad['id_actividad_centro_vida']; ?>" <?= $selected ?>>
                                    <?= $actividad['descripcion_actividad']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-group" style="display:flex; gap:8px; align-items:center;">
                        <button type="submit" class="btn-modern btn-primary">
                            <i class="bi bi-search"></i>
                            Buscar
                        </button>
                        <button type="button" id="clearFilters" class="btn-modern btn-secondary">
                            <i class="bi bi-x-circle"></i>
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla moderna -->
            <div class="modern-table-wrapper">
                <table class="modern-table" id="registrosTable">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Actividad Centro Vida</th>
                            <th>Fechas Programadas</th>
                            <th>Política Pública</th>
                            <th>Departamento</th>
                            <th>Nombre actividad,evento o asunto</th>
                            <th>Funcionario</th>
                            <th>Fecha Registro</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php include "getRegistrosCentroVida.php"; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación servidor -->
            <?php
            $pag_total     = isset($total_pages)      ? $total_pages      : 1;
            $pag_current   = isset($current_page)     ? $current_page     : 1;
            $pag_total_reg = isset($total_registros)  ? $total_registros  : 0;
            $pag_per_page  = isset($per_page)         ? $per_page         : 25;
            if ($pag_total > 1 || $pag_total_reg > 0) {
                $params_base = $_GET;
                unset($params_base['page']);
                $base_url = 'formCentroVida.php?' . http_build_query($params_base);
                $start_reg = ($pag_current - 1) * $pag_per_page + 1;
                $end_reg   = min($pag_current * $pag_per_page, $pag_total_reg);
                echo '<div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #e5e7eb;flex-wrap:wrap;gap:8px;">';
                echo '<span style="font-size:14px;color:#6b7280;">Mostrando ' . $start_reg . ' – ' . $end_reg . ' de <strong>' . $pag_total_reg . '</strong> registros</span>';
                echo '<nav><ul class="pagination pagination-sm mb-0">';
                // Anterior
                if ($pag_current > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($pag_current - 1) . '">&#8249; Ant</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">&#8249; Ant</span></li>';
                }
                // Páginas
                $window = 2;
                for ($p = 1; $p <= $pag_total; $p++) {
                    if ($p == 1 || $p == $pag_total || abs($p - $pag_current) <= $window) {
                        $active = ($p == $pag_current) ? ' active' : '';
                        echo '<li class="page-item' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $p . '">' . $p . '</a></li>';
                    } elseif (abs($p - $pag_current) == $window + 1) {
                        echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    }
                }
                // Siguiente
                if ($pag_current < $pag_total) {
                    echo '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($pag_current + 1) . '">Sig &#8250;</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">Sig &#8250;</span></li>';
                }
                echo '</ul></nav></div>';
            }
            ?>
        </div>
    </div>
    <!-- Modal Exportar Excel Centro Vida -->
    <div class="modal fade" id="modalExportCentroVida" tabindex="-1" aria-labelledby="modalExportCentroVidaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formExportCentroVida" action="exportExcelCentroVida.php" method="get" target="_blank">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalExportCentroVidaLabel">
                            <i class="bi bi-file-earmark-excel-fill me-2"></i>Exportar Registros Centro Vida
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Aplique filtros antes de exportar. Los permisos de visualización se aplican automáticamente según su perfil.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="number" class="form-control" id="exp_cedula" name="cedula_persona" placeholder="Cédula">
                                <label for="exp_cedula">Cédula</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="exp_nombre" name="nombre" placeholder="Nombre">
                                <label for="exp_nombre">Nombre</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="number" class="form-control" id="exp_anio" name="anio" placeholder="Año" min="2020" max="2099">
                                <label for="exp_anio">Año</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="exp_mes" name="mes">
                                    <option value="">Todos los meses</option>
                                    <?php
                                    $meses_exp = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                                                  7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                                    foreach ($meses_exp as $num => $nom) {
                                        echo "<option value=\"$num\">$nom</option>";
                                    } ?>
                                </select>
                                <label for="exp_mes">Mes</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="exp_actividad" name="actividad">
                                    <option value="">Todas las actividades</option>
                                    <?php foreach ($result_actividades_cv as $act_exp) { ?>
                                        <option value="<?= $act_exp['id_actividad_centro_vida'] ?>"><?= htmlspecialchars($act_exp['descripcion_actividad']) ?></option>
                                    <?php } ?>
                                </select>
                                <label for="exp_actividad">Actividad Centro Vida</label>
                            </div>
                            <?php if (in_array($tipo_usuario_cv, [5, 11])): ?>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="exp_funcionario" name="funcionario">
                                    <option value="">Todos los funcionarios</option>
                                    <?php foreach ($funcionarios_export as $func_e) { ?>
                                        <option value="<?= $func_e['id'] ?>"><?= htmlspecialchars($func_e['nombre']) ?></option>
                                    <?php } ?>
                                </select>
                                <label for="exp_funcionario">Funcionario</label>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($tipo_usuario_cv === 5): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="exp_centro_vida" name="id_grupo_cv">
                                    <option value="">Todos los centros de vida</option>
                                    <?php foreach ($cvs_export as $cv_e) { ?>
                                        <option value="<?= $cv_e['id_grupo'] ?>"><?= htmlspecialchars($cv_e['descripcion_grupo']) ?></option>
                                    <?php } ?>
                                </select>
                                <label for="exp_centro_vida">Centro de Vida</label>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($tipo_usuario_cv === 11): ?>
    <!-- Modal Consolidado por Mes (solo tipo_usuario 11) -->
    <div class="modal fade" id="modalConsolidado" tabindex="-1" aria-labelledby="modalConsolidadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formConsolidado" action="exportConsolidadoCentroVida.php" method="get" target="_blank">
                    <div class="modal-header text-white" style="background: linear-gradient(135deg,#1976d2,#42a5f5);">
                        <h5 class="modal-title" id="modalConsolidadoLabel">
                            <i class="bi bi-calendar3 me-2"></i>Consolidado por Mes — Centro Vida
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Genera un Excel con el listado de personas del centro de vida y marca los días de asistencia en el mes seleccionado.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="cons_mes" name="mes" required>
                                    <option value="">-- Seleccione --</option>
                                    <?php
                                    $meses_cons = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                                                   7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                                    foreach ($meses_cons as $num => $nom) {
                                        echo "<option value=\"$num\">$nom</option>";
                                    } ?>
                                </select>
                                <label for="cons_mes">Mes <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="number" class="form-control" id="cons_anio" name="anio" placeholder="Año"
                                    min="2020" max="2099" value="<?= date('Y') ?>" required>
                                <label for="cons_anio">Año <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="cons_actividad" name="id_actividad_cv">
                                    <option value="">Todas las actividades</option>
                                    <?php foreach ($result_actividades_cv as $act_c) { ?>
                                        <option value="<?= $act_c['id_actividad_centro_vida'] ?>"><?= htmlspecialchars($act_c['descripcion_actividad']) ?></option>
                                    <?php } ?>
                                </select>
                                <label for="cons_actividad">Actividad Centro Vida</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="cons_funcionario" name="funcionario">
                                    <option value="">Todos los funcionarios</option>
                                    <?php foreach ($funcionarios_export as $func_c) { ?>
                                        <option value="<?= $func_c['id'] ?>"><?= htmlspecialchars($func_c['nombre']) ?></option>
                                    <?php } ?>
                                </select>
                                <label for="cons_funcionario">Funcionario</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label class="form-label fw-bold">Jornada</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="jornada" id="cons_jornada_ambas" value="" checked>
                                    <label class="btn btn-outline-secondary" for="cons_jornada_ambas">Ambas</label>
                                    <input type="radio" class="btn-check" name="jornada" id="cons_jornada_manana" value="Mañana">
                                    <label class="btn btn-outline-primary" for="cons_jornada_manana">Mañana</label>
                                    <input type="radio" class="btn-check" name="jornada" id="cons_jornada_tarde" value="Tarde">
                                    <label class="btn btn-outline-warning" for="cons_jornada_tarde">Tarde</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-excel"></i> Generar Consolidado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Agregar Masivo -->
    <div class="modal fade" id="modalMasivo" tabindex="-1" aria-labelledby="modalMasivoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="formMasivo" action="addRegistroCentroVidaMasivo.php" method="POST">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="modalMasivoLabel">
                            <i class="bi bi-people-fill me-2"></i>Agregar Registros Masivos
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted">Seleccione múltiples personas de la lista o escriba una cédula para agregarla manualmente. Los registros se agregarán con la misma información que en el formulario individual.</p>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <input type="text" id="searchPersona" class="form-control" placeholder="Buscar por nombre o cédula...">
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group w-100" role="group" aria-label="Filtro jornada">
                                    <input type="radio" class="btn-check" name="filtroJornada" id="filtroTodos" value="todos" checked>
                                    <label class="btn btn-outline-secondary btn-sm" for="filtroTodos">Todos</label>
                                    <input type="radio" class="btn-check" name="filtroJornada" id="filtroManana" value="Mañana">
                                    <label class="btn btn-outline-primary btn-sm" for="filtroManana">Mañana</label>
                                    <input type="radio" class="btn-check" name="filtroJornada" id="filtroTarde" value="Tarde">
                                    <label class="btn btn-outline-warning btn-sm" for="filtroTarde">Tarde</label>
                                </div>
                            </div>
                        </div>

                        <div style="max-height: 300px; overflow:auto; border:1px solid #e5e7eb; padding:8px; border-radius:6px;">
                            <div class="form-check mb-2 border-bottom pb-2">
                                <input class="form-check-input" type="checkbox" id="selectAllPersonas">
                                <label class="form-check-label fw-bold" for="selectAllPersonas">
                                    <i class="bi bi-check-square me-1"></i>Seleccionar Todos
                                </label>
                            </div>
                            <div id="listaPersonasMasivo">
                                <?php foreach ($result_personas as $p) { ?>
                                    <div class="form-check persona-item" data-jornada="<?= htmlspecialchars($p['jornada'] ?? '') ?>">
                                        <input class="form-check-input persona-checkbox" type="checkbox" value="<?= htmlspecialchars($p['cedula_persona']) ?>" id="persona_<?= htmlspecialchars($p['cedula_persona']) ?>">
                                        <label class="form-check-label" for="persona_<?= htmlspecialchars($p['cedula_persona']) ?>"><?= htmlspecialchars($p['nombre_completo']) ?> — <small><?= htmlspecialchars($p['cedula_persona']) ?></small><?= !empty($p['jornada']) ? ' <span class="badge bg-secondary">' . htmlspecialchars($p['jornada']) . '</span>' : '' ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <hr>

                        <!-- Incluir aquí los mismos campos que el formulario individual, pero sin la cédula -->
                        <div id="camposMasivo">
                            <!-- Reusar partes del formulario individual -->
                            <div class="row">
                                <div class="col-md-6 mb-3 form-floating">
                                    <?php
                                    $id_cv_activo_masivo = '';
                                    $desc_cv_activo_masivo = '';
                                    foreach ($result_condiciones as $c_find) {
                                        $d_find = $c_find['descripcion_condicion'];
                                        if (stripos($d_find, 'C.V') !== false && stripos($d_find, 'Activo') !== false && stripos($d_find, 'Inactivo') === false) {
                                            $id_cv_activo_masivo = $c_find['id_condicion'];
                                            $desc_cv_activo_masivo = $d_find;
                                            break;
                                        }
                                    }
                                    ?>
                                    <select class="form-select" id="id_condicion_masivo" style="pointer-events: none; background-color: #e9ecef;" tabindex="-1">
                                        <option value="<?= htmlspecialchars($id_cv_activo_masivo) ?>" selected><?= htmlspecialchars($desc_cv_activo_masivo) ?></option>
                                    </select>
                                    <input type="hidden" name="id_condicion" value="<?= htmlspecialchars($id_cv_activo_masivo) ?>">
                                    <label for="id_condicion_masivo">Condición</label>
                                </div>
                                <div class="col-md-6 mb-3 form-floating" id="condicion_otra_masivo_wrap" style="display:none;">
                                    <input type="text" class="form-control" id="condicion_otra_masivo" name="condicion_otra" placeholder="Especifique condición">
                                    <label for="condicion_otra_masivo">Especifique condición</label>
                                </div>
                                <div class="col-md-6 mb-3 form-floating">
                                    <select class="form-select" id="id_meta_masivo" name="id_meta" required>
                                        <option value="" selected>Seleccione Meta...</option>
                                        <?php foreach ($result_metas as $meta) { ?>
                                            <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="id_meta_masivo">Meta</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3 form-floating">
                                    <select class="form-select" id="id_actividad_masivo" name="id_actividad" required disabled>
                                        <option value="" selected>Seleccione Actividad...</option>
                                    </select>
                                    <label for="id_actividad_masivo">Actividad</label>
                                </div>
                                <div class="col-md-4 mb-3 form-floating">
                                    <select class="form-select" id="id_accion_masivo" name="id_accion" required disabled>
                                        <option value="" selected>Seleccione Acción...</option>
                                    </select>
                                    <label for="id_accion_masivo">Acción</label>
                                </div>
                                <div class="col-md-4 mb-3 form-floating">
                                    <select class="form-select" id="actividad_centro_vida_masivo" name="id_actividad_centro_vida" required>
                                        <option value="" selected>Seleccione Actividad...</option>
                                        <?php foreach ($result_actividades_cv as $actividad) { ?>
                                            <option value="<?= $actividad['id_actividad_centro_vida']; ?>"><?= $actividad['descripcion_actividad']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="actividad_centro_vida_masivo">Actividad Centro Vida</label>
                                </div>
                            </div>

                            <div class="row">
                                <input type="hidden" name="departamento_procedencia" value="Risaralda">
                                <div class="col-md-6 mb-3 form-floating">
                                    <select class="form-select" id="politica_publica_masivo" name="politica_publica">
                                        <option value="" selected>Seleccione Política Pública...</option>
                                    </select>
                                    <label for="politica_publica_masivo">Política Pública</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3 form-floating">
                                    <select class="form-select" id="profesion_masivo" name="profesion">
                                        <option value="" selected>Seleccione Profesión...</option>
                                        <option value="Trabajo social">Trabajo social</option>
                                        <option value="Psicología">Psicología</option>
                                        <option value="Psicosocial">Psicosocial</option>
                                        <option value="Gerontología">Gerontología</option>
                                    </select>
                                    <label for="profesion_masivo">Profesión</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="fechas_atencion_masivo" class="form-label"><strong>Fechas de Atención</strong></label>
                                    <input type="text" class="form-control" id="fechas_atencion_masivo" name="fechas_atencion" placeholder="Haga clic para seleccionar múltiples fechas..." readonly required>
                                    <input type="hidden" id="fechas_seleccionadas_masivo" name="fechas_seleccionadas">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="observacion_masivo" name="observacion" placeholder="Observación" style="height: 100px;"></textarea>
                                        <label for="observacion_masivo">Nombre actividad,evento o asunto</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Agregar Masivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Control de Asistencia -->
    <div class="modal fade" id="modalControlAsistencia" tabindex="-1" aria-labelledby="modalCALabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#00bcd4,#0097a7);color:#fff;">
                    <h5 class="modal-title" id="modalCALabel">
                        <i class="bi bi-calendar-check-fill me-2"></i>Control de Asistencia
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Marque las personas presentes y seleccione la fecha de asistencia. Al guardar, podrá cargar esta lista al abrir el modal de Agregar Masivo.</p>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold"><i class="bi bi-calendar3"></i> Fecha de Asistencia</label>
                            <input type="date" class="form-control" id="ca_fecha" max="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <input type="text" id="ca_searchPersona" class="form-control" placeholder="Buscar por nombre o cédula...">
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="ca_filtroJornada" id="ca_filtroTodos" value="todos" checked>
                                <label class="btn btn-outline-secondary btn-sm" for="ca_filtroTodos">Todos</label>
                                <input type="radio" class="btn-check" name="ca_filtroJornada" id="ca_filtroManana" value="Mañana">
                                <label class="btn btn-outline-primary btn-sm" for="ca_filtroManana">Mañana</label>
                                <input type="radio" class="btn-check" name="ca_filtroJornada" id="ca_filtroTarde" value="Tarde">
                                <label class="btn btn-outline-warning btn-sm" for="ca_filtroTarde">Tarde</label>
                            </div>
                        </div>
                    </div>
                    <div style="max-height: 350px; overflow:auto; border:1px solid #e5e7eb; padding:8px; border-radius:6px;">
                        <div class="form-check mb-2 border-bottom pb-2">
                            <input class="form-check-input" type="checkbox" id="ca_selectAll">
                            <label class="form-check-label fw-bold" for="ca_selectAll">
                                <i class="bi bi-check-square me-1"></i>Seleccionar Todos
                            </label>
                        </div>
                        <div id="ca_listaPersonas">
                            <?php foreach ($result_personas as $p) { ?>
                                <div class="form-check persona-ca-item" data-jornada="<?= htmlspecialchars($p['jornada'] ?? '') ?>">
                                    <input class="form-check-input persona-ca-checkbox" type="checkbox"
                                           value="<?= htmlspecialchars($p['cedula_persona']) ?>"
                                           id="ca_p_<?= htmlspecialchars($p['cedula_persona']) ?>">
                                    <label class="form-check-label" for="ca_p_<?= htmlspecialchars($p['cedula_persona']) ?>">
                                        <?= htmlspecialchars($p['nombre_completo']) ?>
                                        — <small><?= htmlspecialchars($p['cedula_persona']) ?></small>
                                        <?= !empty($p['jornada']) ? ' <span class="badge bg-secondary">' . htmlspecialchars($p['jornada']) . '</span>' : '' ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="bi bi-info-circle"></i> Personas seleccionadas: <span id="ca_contador" class="fw-bold">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info text-white" id="ca_btnGuardar">
                        <i class="bi bi-save"></i> Guardar Asistencia
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Registro -->
    <div class="modal fade" id="modalNewRecord" tabindex="-1" aria-labelledby="modalNewRecordLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="addRegistroCentroVida.php" method="POST">
                    <input type="hidden" id="id_registro_centro_vida" name="id_registro_centro_vida" value="">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewRecordLabel">
                            <i class="bi bi-heart-plus-fill me-2"></i>Agregar Registro Centro Vida
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila 1: Cédula y Condición -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="number" class="form-control" id="cedula_persona" name="cedula_persona" placeholder="Cédula" required autocomplete="off" autofocus>
                                <label for="cedula_persona">Cédula</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="id_condicion" name="id_condicion" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_condiciones as $condicion) { 
                                        // Filtrar opciones que empiecen con CPSAM o C.M.
                                        $descripcion = $condicion['descripcion_condicion'];
                                        if (substr($descripcion, 0, 5) === 'CPSAM' || substr($descripcion, 0, 4) === 'C.M.' || substr($descripcion, 0, 4) === 'C.M ') {
                                            continue;
                                        }
                                    ?>
                                        <option value="<?= $condicion['id_condicion']; ?>"><?= $condicion['descripcion_condicion']; ?></option>
                                    <?php } ?>
                                    <option value="otra">Otra</option>
                                </select>
                                <label for="id_condicion">Condición</label>
                            </div>
                        </div>
                        <div class="row" id="condicion_otra_individual_row" style="display:none;">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="condicion_otra_individual" name="condicion_otra" placeholder="Especifique condición">
                                <label for="condicion_otra_individual">Especifique condición</label>
                            </div>
                        </div>

                        <!-- Fila 2: Meta, Actividad, Acción -->
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="id_meta" name="id_meta" required>
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php foreach ($result_metas as $meta) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="id_meta">Meta</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="id_actividad" name="id_actividad" required disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                                <label for="id_actividad">Actividad</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="id_accion" name="id_accion" required disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                                <label for="id_accion">Acción</label>
                            </div>
                        </div>

                        <!-- Fila 3: Actividad Centro Vida y Política Pública -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="actividad_centro_vida" name="id_actividad_centro_vida" required>
                                    <option value="" selected>Seleccione Actividad...</option>
                                    <?php foreach ($result_actividades_cv as $actividad) { ?>
                                        <option value="<?= $actividad['id_actividad_centro_vida']; ?>"><?= $actividad['descripcion_actividad']; ?></option>
                                    <?php } ?>
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

                        <!-- Fila 2: (removida) Actividad Realizada -->

                        <!-- Fila 5: Fechas de Atención (Calendario Multi-selección) -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="fechas_atencion" class="form-label"><strong>Fechas de Atención</strong></label>
                                <input type="text" class="form-control" id="fechas_atencion" name="fechas_atencion"
                                    placeholder="Haga clic para seleccionar múltiples fechas..." readonly required>
                                <input type="hidden" id="fechas_seleccionadas" name="fechas_seleccionadas">
                                <div class="selected-dates-display" id="selected-dates-display">
                                    <small class="text-muted">Las fechas seleccionadas aparecerán aquí</small>
                                </div>
                            </div>
                        </div>



                        <!-- Fila 4: Departamento de Procedencia -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="departamento_procedencia" name="departamento_procedencia" required>
                                    <option value="" selected>Seleccione Departamento...</option>
                                    <option value="Amazonas">Amazonas</option>
                                    <option value="Antioquia">Antioquia</option>
                                    <option value="Arauca">Arauca</option>
                                    <option value="Atlántico">Atlántico</option>
                                    <option value="Bolívar">Bolívar</option>
                                    <option value="Boyacá">Boyacá</option>
                                    <option value="Caldas">Caldas</option>
                                    <option value="Caquetá">Caquetá</option>
                                    <option value="Casanare">Casanare</option>
                                    <option value="Cauca">Cauca</option>
                                    <option value="Cesar">Cesar</option>
                                    <option value="Chocó">Chocó</option>
                                    <option value="Córdoba">Córdoba</option>
                                    <option value="Cundinamarca">Cundinamarca</option>
                                    <option value="Guainía">Guainía</option>
                                    <option value="Guaviare">Guaviare</option>
                                    <option value="Huila">Huila</option>
                                    <option value="La Guajira">La Guajira</option>
                                    <option value="Magdalena">Magdalena</option>
                                    <option value="Meta">Meta</option>
                                    <option value="Nariño">Nariño</option>
                                    <option value="Norte de Santander">Norte de Santander</option>
                                    <option value="Putumayo">Putumayo</option>
                                    <option value="Quindío">Quindío</option>
                                    <option value="Risaralda">Risaralda</option>
                                    <option value="San Andrés y Providencia">San Andrés y Providencia</option>
                                    <option value="Santander">Santander</option>
                                    <option value="Sucre">Sucre</option>
                                    <option value="Tolima">Tolima</option>
                                    <option value="Valle del Cauca">Valle del Cauca</option>
                                    <option value="Vaupés">Vaupés</option>
                                    <option value="Vichada">Vichada</option>
                                    <option value="Bogotá D.C.">Bogotá D.C.</option>
                                </select>
                                <label for="departamento_procedencia">Departamento de Procedencia</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="profesion_individual" name="profesion">
                                    <option value="" selected>Seleccione Profesión...</option>
                                    <option value="Trabajo social">Trabajo social</option>
                                    <option value="Psicología">Psicología</option>
                                    <option value="Psicosocial">Psicosocial</option>
                                    <option value="Gerontología">Gerontología</option>
                                </select>
                                <label for="profesion_individual">Profesión</label>
                            </div>
                        </div>

                        <!-- Jornada -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jornada</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jornada" id="jornada_manana_ind" value="Mañana">
                                        <label class="form-check-label" for="jornada_manana_ind">Mañana</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jornada" id="jornada_tarde_ind" value="Tarde">
                                        <label class="form-check-label" for="jornada_tarde_ind">Tarde</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grupos Externos -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Grupos Externos</label>
                                <div id="grupos_externos_ind_container">
                                    <div class="input-group mb-2 grupo-externo-row-ind">
                                        <select class="form-select" name="grupos_externos[]">
                                            <?= $grupoExternoOptionsHtml ?>
                                        </select>
                                        <button type="button" class="btn btn-danger btn-remove-ge-ind" tabindex="-1">
                                            <i class="bi bi-dash-circle"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn_add_ge_ind">
                                    <i class="bi bi-plus-circle"></i> Agregar grupo externo
                                </button>
                            </div>
                        </div>

                        <!-- Fila 5: Observación -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="observacion" name="observacion" placeholder="Observación" style="height: 120px; resize: vertical; max-height: 240px;"></textarea>
                                    <label for="observacion">Nombre actividad,evento o asunto</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <br /><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="back" /></a><br>

    <script>
        let selectedDates = [];

        // Función para confirmar eliminación
        function confirmarEliminacion(id, actividad) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar el registro de "${actividad}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?delete=${id}`;
                }
            });
        }

        $(document).ready(function() {
            console.log('🚀 jQuery ready ejecutado');
            console.log('📦 jQuery version:', $.fn.jquery);
            console.log('🎯 SweetAlert disponible:', typeof Swal !== 'undefined');

            // Verificar si la tabla existe y tiene contenido antes de inicializar DataTables
            if ($('#registrosTable tbody tr').length > 0) {
                // Verificar que todas las filas tengan el mismo número de columnas
                var headerCols = $('#registrosTable thead tr th').length;
                var rowsValid = true;

                $('#registrosTable tbody tr').each(function() {
                    var cellCount = $(this).find('td').length;
                    if (cellCount !== headerCols) {
                        console.log('Fila con número incorrecto de columnas:', cellCount, 'vs', headerCols);
                        rowsValid = false;
                    }
                });

                if (rowsValid) {
                    // Inicializar DataTables solo si la estructura es válida
                    try {
                        const table = $('#registrosTable').DataTable({
                            paging: false,
                            info: false,
                            responsive: true,
                            order: [
                                [10, 'desc']
                            ], // Ordenar por fecha de registro desc
                            columnDefs: [{
                                targets: [11],
                                orderable: false,
                                searchable: false
                            }],
                            language: {
                                "sProcessing": "Procesando...",
                                "sLengthMenu": "Mostrar _MENU_ registros",
                                "sZeroRecords": "No se encontraron resultados",
                                "sEmptyTable": "Ningún dato disponible en esta tabla",
                                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                                "sSearch": "Buscar:",
                                "oPaginate": {
                                    "sFirst": "Primero",
                                    "sLast": "Último",
                                    "sNext": "Siguiente",
                                    "sPrevious": "Anterior"
                                }
                            }
                        });
                        console.log('DataTables inicializado correctamente');
                    } catch (error) {
                        console.error('Error al inicializar DataTables:', error);
                    }
                } else {
                    console.log('Tabla con estructura inconsistente, DataTables no inicializado');
                }
            } else {
                console.log('Tabla vacía, DataTables no inicializado');
            }

            // Inicializar Flatpickr para selección múltiple de fechas
            console.log('Inicializando Flatpickr...');
            flatpickr("#fechas_atencion", {
                mode: "multiple",
                dateFormat: "Y-m-d",
                locale: "es",
                placeholder: "Selecciona las fechas de atención...",
                allowInput: false,
                clickOpens: true,
                onReady: function() {
                    console.log('Flatpickr listo');
                },
                onChange: function(selectedDates, dateStr, instance) {
                    console.log("Fechas seleccionadas:", dateStr);
                    updateSelectedDatesDisplay(selectedDates);
                    document.getElementById('fechas_seleccionadas').value = dateStr;
                }
            });

            // Manejar selección de Meta para cargar Actividades
            $('#id_meta').on('change', function() {
                const idMeta = $(this).val();
                console.log('Meta seleccionada:', idMeta);

                // Limpiar y deshabilitar campos dependientes
                $('#id_actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');

                if (idMeta) {
                    console.log('Cargando actividades para meta:', idMeta);
                    $.ajax({
                        url: '../personMovement/getActividades.php',
                        type: 'POST',
                        data: {
                            id_meta: idMeta
                        },
                        success: function(response) {
                            console.log('Actividades cargadas:', response);
                            $('#id_actividad').append(response).prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar actividades:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al cargar las actividades: ' + error,
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });

            // Manejar selección de Actividad para cargar Acciones
            $('#id_actividad').on('change', function() {
                const idActividad = $(this).val();
                // Limpiar y deshabilitar campo de acciones
                $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');

                if (idActividad) {
                    $.ajax({
                        url: '../personMovement/getAcciones.php',
                        type: 'POST',
                        data: {
                            id_actividad: idActividad
                        },
                        success: function(response) {
                            $('#id_accion').append(response).prop('disabled', false);
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al cargar las acciones',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });

            // Manejar selección de Acción para consultar políticas públicas
            $('#id_accion').on('change', function() {
                const idAccion = $(this).val();
                console.log('🏛️ Cargando políticas públicas para acción:', idAccion);

                // Limpiar el select de política pública
                $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');

                if (idAccion) {
                    $.ajax({
                        url: '../personMovement/getPoliticaPublica.php',
                        type: 'POST',
                        data: {
                            id_accion: idAccion
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log('📋 Políticas públicas recibidas:', response);

                            if (response && response.politicas && response.politicas.length > 0) {
                                // Agregar cada política pública como opción
                                response.politicas.forEach(function(p) {
                                    $('#politica_publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                });
                                console.log('✅ Se agregaron', response.politicas.length, 'políticas públicas');
                            } else {
                                $('#politica_publica').append('<option value="No asignada">No asignada</option>');
                                console.log('⚠️ No se encontraron políticas públicas');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ Error al cargar políticas públicas:', error);
                            $('#politica_publica').append('<option value="Error al consultar">Error al consultar</option>');
                        }
                    });
                }
            });

            // Validación del formulario con AJAX
            $('#modalNewRecord form').on('submit', function(e) {
                console.log('🚀 Evento submit disparado');

                // Siempre prevenir el submit normal para usar AJAX
                e.preventDefault();

                // Buscar la cédula dentro del formulario (evita conflicto con filtros)
                const cedula = $(this).find('#cedula_persona').val() ? $(this).find('#cedula_persona').val().trim() : '';
                console.log('👤 Cédula ingresada:', cedula);
                console.log('📅 Fechas seleccionadas:', selectedDates.length);

                // Verificar que la cédula no esté vacía
                if (!cedula || cedula.length === 0) {
                    console.log('❌ Formulario bloqueado: Cédula vacía');
                    Swal.fire({
                        title: 'Cédula Requerida',
                        text: 'Debe ingresar una cédula.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    $(this).find('#cedula_persona').focus();
                    return false;
                }

                // Verificar que se hayan seleccionado fechas
                if (selectedDates.length === 0) {
                    console.log('❌ Formulario bloqueado: Sin fechas');
                    Swal.fire({
                        title: 'Fechas Requeridas',
                        text: 'Debe seleccionar al menos una fecha de atención.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    $('#fechas_atencion').focus();
                    return false;
                }

                console.log('✅ Validación pasada, enviando formulario por AJAX...');

                // Preparar datos del formulario
                let formData = $(this).serialize();

                // Agregar las fechas seleccionadas
                formData += '&fechas_atencion=' + encodeURIComponent(JSON.stringify(selectedDates));

                console.log('📋 Datos del formulario completos:', formData);

                // Enviar por AJAX (elegir endpoint según modo: add o edit)
                const endpointUrl = $('#id_registro_centro_vida').val() ? 'editRegistroCentroVida.php' : 'addRegistroCentroVida.php';
                // Enviar por AJAX
                $.ajax({
                    url: endpointUrl,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('📤 Enviando solicitud AJAX...');
                        // Deshabilitar botón de envío
                        const $submitBtn = $('#modalNewRecord button[type="submit"]');
                        $submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Guardando...');
                    },
                    success: function(response) {
                        console.log('✅ Respuesta AJAX exitosa:', response);

                        if (response.success) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: response.message || 'Registro guardado correctamente',
                                icon: 'success',
                                timer: 2000,
                                position: 'top-end',
                                toast: true,
                                showConfirmButton: false
                            });

                            // Cerrar modal y limpiar formulario
                            $('#modalNewRecord').modal('hide');
                            $('#modalNewRecord form')[0].reset();
                            selectedDates = [];
                            $('#fechas_atencion').val('');

                            // Recargar la página para mostrar el nuevo registro
                            setTimeout(() => {
                                location.reload();
                            }, 1000);

                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Error al guardar el registro',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('❌ Error AJAX:', {
                            xhr,
                            status,
                            error
                        });
                        console.log('📄 Texto de respuesta:', xhr.responseText);

                        let errorMessage = 'No se pudo conectar con el servidor.';

                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            errorMessage = errorResponse.message || errorMessage;
                        } catch (e) {
                            // Si no es JSON válido, usar mensaje genérico
                        }

                        Swal.fire({
                            title: 'Error de Conexión',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    },
                    complete: function() {
                        console.log('🏁 Solicitud AJAX completada');
                        // Rehabilitar botón
                        const $submitBtn = $('#modalNewRecord button[type="submit"]');
                        $submitBtn.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar');
                    }
                });

                return false;
            });

            // Evento específico para el botón guardar
            $('#modalNewRecord button[type="submit"]').on('click', function(e) {
                console.log('🖱️ Botón Guardar clickeado');
                console.log('🎯 Formulario padre:', $(this).closest('form').length);
            });

            console.log('✅ Inicialización completada - JavaScript listo');

            // Validación de cédula en tiempo real y precarga de Meta/Actividad/Acción/Política Pública
            let cedulaValida = false;

            // Usar delegación de eventos para asegurar que funcione con elementos dinámicos
            $('#modalNewRecord').on('blur', '#cedula_persona', function() {
                const cedula = $(this).val().trim();
                console.log('🔍 Buscando persona por cédula (centro vida):', cedula);

                if (!cedula) {
                    // Campo vacío
                    $('#cedula_persona').removeClass('is-valid is-invalid is-loading');
                    cedulaValida = false;
                    return;
                }

                // Limpiar clases anteriores
                $(this).removeClass('is-valid is-invalid is-loading');

                $.ajax({
                    url: '../buscar_persona.php',
                    type: 'POST',
                    data: {
                        cedula: cedula
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('✅ Respuesta buscar_persona:', response);

                        if (response.encontrado) {
                            if (response.fallecido) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Persona fallecida',
                                    text: 'Esta persona ha fallecido. No es posible registrar.',
                                    confirmButtonText: 'OK'
                                }).then(function() {
                                    $('#cedula_persona').val('').focus();
                                    // Limpiar selects dependientes
                                    $('#id_meta').val('');
                                    $('#id_actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                                    $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                                    $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                                });
                                return;
                            }

                            // Marcar cédula válida
                            $('#cedula_persona').removeClass('is-invalid').addClass('is-valid');
                            cedulaValida = true;

                            // Mensaje corto
                            Swal.fire({
                                icon: 'success',
                                title: 'Persona encontrada',
                                text: 'Nombre: ' + response.nombres + ' ' + response.apellidos,
                                timer: 1500,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });

                            // Precargar condición: condicion_componente es texto, hay que buscar la opción cuyo texto coincida
                            if (response.condicion_componente) {
                                let foundCond = false;
                                const normalize = s => s.trim().toLowerCase().replace(/\./g, '').replace(/\s+/g, ' ');
                                const condText = normalize(response.condicion_componente);
                                $('#id_condicion option').each(function() {
                                    if (normalize($(this).text()) === condText) {
                                        $('#id_condicion').val($(this).val());
                                        foundCond = true;
                                        return false;
                                    }
                                });
                                if (!foundCond && response.id_condicion) {
                                    $('#id_condicion').val(response.id_condicion);
                                }
                            } else if (response.id_condicion) {
                                $('#id_condicion').val(response.id_condicion);
                            }
                            if ($('#id_condicion').val() === 'otra') {
                                $('#condicion_otra_individual_row').show();
                                $('#condicion_otra_individual').prop('required', true);
                            }

                            // Precargar Jornada
                            if (response.jornada) {
                                $('input[name="jornada"][value="' + response.jornada + '"]').prop('checked', true);
                            }

                            // Precargar Grupos Externos
                            if (response.ids_grupos_externos) {
                                const geIds = response.ids_grupos_externos.toString().split(',').map(s => s.trim()).filter(s => s);
                                resetGruposExternosInd();
                                if (geIds.length > 0) {
                                    const $container = $('#grupos_externos_ind_container');
                                    $container.empty();
                                    geIds.forEach(function(geId) {
                                        const $row = $('<div class="input-group mb-2 grupo-externo-row-ind"></div>');
                                        const $sel = $('<select class="form-select" name="grupos_externos[]"><?= $grupoExternoOptionsHtml ?></select>');
                                        $sel.val(geId);
                                        const $btn = $('<button type="button" class="btn btn-danger btn-remove-ge-ind" tabindex="-1"><i class="bi bi-dash-circle"></i></button>');
                                        $row.append($sel).append($btn);
                                        $container.append($row);
                                    });
                                } else {
                                    resetGruposExternosInd();
                                }
                            }

                            // Precargar Meta, Actividad, Acción y Política Pública si existen en la tabla personas
                            if (response.id_meta) {
                                $('#id_meta').val(response.id_meta);
                                // Cargar actividades para esta meta
                                $.ajax({
                                    url: '../personMovement/getActividades.php',
                                    type: 'POST',
                                    data: {
                                        id_meta: response.id_meta
                                    },
                                    success: function(actividadesResponse) {
                                        $('#id_actividad').empty().append('<option value="">Seleccione Actividad...</option>');
                                        $('#id_actividad').append(actividadesResponse).prop('disabled', false);

                                        if (response.id_actividad) {
                                            $('#id_actividad').val(response.id_actividad);
                                            // Cargar acciones para esta actividad
                                            $.ajax({
                                                url: '../personMovement/getAcciones.php',
                                                type: 'POST',
                                                data: {
                                                    id_actividad: response.id_actividad
                                                },
                                                success: function(accionesResponse) {
                                                    $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>');
                                                    $('#id_accion').append(accionesResponse).prop('disabled', false);

                                                    if (response.id_accion) {
                                                        $('#id_accion').val(response.id_accion);
                                                        // Cargar políticas públicas para esta acción
                                                        $.ajax({
                                                            url: '../personMovement/getPoliticaPublica.php',
                                                            type: 'POST',
                                                            data: {
                                                                id_accion: response.id_accion
                                                            },
                                                            dataType: 'json',
                                                            success: function(politicasResponse) {
                                                                $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                                                                if (politicasResponse && politicasResponse.politicas && politicasResponse.politicas.length > 0) {
                                                                    politicasResponse.politicas.forEach(function(p) {
                                                                        $('#politica_publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                                                    });
                                                                    // Seleccionar la opción si existe en personas
                                                                    if (response.id_politica_publica) {
                                                                        $('#politica_publica').val(response.id_politica_publica);
                                                                    }
                                                                } else {
                                                                    $('#politica_publica').append('<option value="">No asignada</option>');
                                                                }
                                                            },
                                                            error: function() {
                                                                $('#politica_publica').append('<option value="">Error al consultar</option>');
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        } else {
                            $('#cedula_persona').removeClass('is-valid').addClass('is-invalid');
                            cedulaValida = false;
                            Swal.fire({
                                icon: 'error',
                                title: 'Persona no encontrada',
                                text: 'No se encontró ninguna persona con esa cédula.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Error al buscar persona:', error, xhr.responseText);
                        $('#cedula_persona').removeClass('is-valid').addClass('is-invalid');
                        cedulaValida = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al buscar persona. ' + error,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Resetear validación cuando el usuario empiece a escribir
            $('#modalNewRecord').on('input', '#cedula_persona', function() {
                console.log('✏️ Usuario escribiendo en cédula (modal)');
                $(this).removeClass('is-valid is-invalid');
                cedulaValida = false;
            });

            // Limpiar formulario cuando se cierre el modal
            $('#modalNewRecord').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset();
                $('#cedula_persona').removeClass('is-valid is-invalid');
                $('#id_actividad, #id_accion').prop('disabled', true).html('<option value="">Seleccione...</option>');
                $('#politica_publica').html('<option value="" selected>Seleccione Política Pública...</option>');
                $('#selected-dates-display').html('<small class="text-muted">Las fechas seleccionadas aparecerán aquí</small>');
                selectedDates = [];
                cedulaValida = false;
                // Asegurar que el formulario vuelva a modo 'agregar' limpiando el id de edición
                $('#id_registro_centro_vida').val('');
                // Ocultar campo condicion_otra
                $('#condicion_otra_individual_row').hide();
                $('#condicion_otra_individual').val('').prop('required', false);
                // Resetear grupos externos
                resetGruposExternosInd();

                // Limpiar flatpickr
                if (document.querySelector("#fechas_atencion")._flatpickr) {
                    document.querySelector("#fechas_atencion")._flatpickr.clear();
                }
            });

            // Verificar cuando se abra el modal
            $('#modalNewRecord').on('shown.bs.modal', function() {
                console.log('🎯 Modal abierto - Verificando elemento cédula:', $('#cedula_persona').length);
                if ($('#cedula_persona').length === 0) {
                    console.error('❌ Elemento #cedula_persona no encontrado');
                } else {
                    console.log('✅ Elemento #cedula_persona encontrado');
                    // Solo hacer focus si no está en modo readonly (edición)
                    if (!$('#cedula_persona').prop('readonly')) {
                        $('#cedula_persona').focus();
                    }
                }
                // Precargar meta "2." y actividad "2.1" si no hay datos ya cargados
                if (!$('#id_registro_centro_vida').val()) {
                    autoprecargarMetaActividadInd();
                }
            });

            // Evento para limpiar modal al hacer clic en "Agregar Registro"
            $('button[data-bs-target="#modalNewRecord"]').on('click', function() {
                console.log('🆕 Abriendo modal en modo agregar');
                resetModalToAddMode();
            });

            // Evento para resetear el modal cuando se cierre
            $('#modalNewRecord').on('hidden.bs.modal', function() {
                console.log('🔄 Modal cerrado - reseteando a modo agregar');
                resetModalToAddMode();
                $('#modalNewRecord form')[0].reset();
                selectedDates = [];
                updateSelectedDatesDisplay([]);
                $('#condicion_otra_individual_row').hide();
                $('#condicion_otra_individual').val('').prop('required', false);
                resetGruposExternosInd();
            });

            // Botón limpiar filtros
            $('#clearFilters').on('click', function() {
                // Limpiar inputs del formulario de filtros
                $('#filter_cedula_persona').val('');
                $('#nombre').val('');
                $('#actividad').val('');
                // Enviar formulario con parámetros vacíos (redirecciona a la misma página sin GET)
                window.location = 'formCentroVida.php';
            });

            // Mostrar/ocultar campo condicion_otra en modal individual
            $('#id_condicion').on('change', function() {
                if ($(this).val() === 'otra') {
                    $('#condicion_otra_individual_row').show();
                    $('#condicion_otra_individual').prop('required', true);
                } else {
                    $('#condicion_otra_individual_row').hide();
                    $('#condicion_otra_individual').val('').prop('required', false);
                }
            });

            // Grupos externos en modal individual
            function createGruposExternosIndRow(selectedId) {
                const $row = $('<div class="input-group mb-2 grupo-externo-row-ind"></div>');
                const $sel = $('<select class="form-select" name="grupos_externos[]"><?= $grupoExternoOptionsHtml ?></select>');
                if (selectedId) $sel.val(selectedId);
                const $btn = $('<button type="button" class="btn btn-danger btn-remove-ge-ind" tabindex="-1"><i class="bi bi-dash-circle"></i></button>');
                $btn.on('click', function() {
                    if ($('#grupos_externos_ind_container .grupo-externo-row-ind').length > 1) {
                        $row.remove();
                    } else {
                        $sel.val('');
                    }
                });
                $row.append($sel).append($btn);
                return $row;
            }

            $('#btn_add_ge_ind').on('click', function() {
                $('#grupos_externos_ind_container').append(createGruposExternosIndRow(''));
            });

            $(document).on('click', '.btn-remove-ge-ind', function() {
                const $row = $(this).closest('.grupo-externo-row-ind');
                if ($('#grupos_externos_ind_container .grupo-externo-row-ind').length > 1) {
                    $row.remove();
                } else {
                    $row.find('select').val('');
                }
            });

            function resetGruposExternosInd() {
                $('#grupos_externos_ind_container').html(
                    '<div class="input-group mb-2 grupo-externo-row-ind">' +
                    '<select class="form-select" name="grupos_externos[]"><?= $grupoExternoOptionsHtml ?></select>' +
                    '<button type="button" class="btn btn-danger btn-remove-ge-ind" tabindex="-1"><i class="bi bi-dash-circle"></i></button>' +
                    '</div>'
                );
            }

            // Precarga Meta "2." y Actividad "2.1" para modal individual
            function autoprecargarMetaActividadInd() {
                if ($('#id_meta').val()) return;
                let idMeta2 = null;
                $('#id_meta option').each(function() {
                    if (/^2\./.test($(this).text().trim())) {
                        idMeta2 = $(this).val();
                        return false;
                    }
                });
                if (!idMeta2) return;
                $('#id_meta').val(idMeta2);
                $.ajax({
                    url: '../personMovement/getActividades.php',
                    type: 'POST',
                    data: { id_meta: idMeta2 },
                    success: function(resp) {
                        $('#id_actividad').empty().append('<option value="">Seleccione Actividad...</option>').append(resp).prop('disabled', false);
                        let idAct21 = null;
                        $('#id_actividad option').each(function() {
                            if (/^2\.1/.test($(this).text().trim())) {
                                idAct21 = $(this).val();
                                return false;
                            }
                        });
                        if (idAct21) {
                            $('#id_actividad').val(idAct21);
                            $.ajax({
                                url: '../personMovement/getAcciones.php',
                                type: 'POST',
                                data: { id_actividad: idAct21 },
                                success: function(resp2) {
                                    $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>').append(resp2).prop('disabled', false);
                                }
                            });
                        }
                    }
                });
            }
        });

        // -------------------- JS para Modal Masivo --------------------
        $(document).ready(function() {
            // Función helper para actualizar visibilidad de personas según texto y jornada
            function aplicarFiltrosMasivo() {
                const q = $('#searchPersona').val().toLowerCase().trim();
                const jornada = $('input[name="filtroJornada"]:checked').val();
                $('#listaPersonasMasivo .persona-item').each(function() {
                    const txt = $(this).text().toLowerCase();
                    const pJornada = $(this).data('jornada') || '';
                    const matchText = !q || txt.indexOf(q) !== -1;
                    const matchJornada = jornada === 'todos' || pJornada === jornada;
                    $(this).toggle(matchText && matchJornada);
                });
                const totalVisible = $('#listaPersonasMasivo .persona-checkbox:visible').length;
                const totalChecked = $('#listaPersonasMasivo .persona-checkbox:visible:checked').length;
                $('#selectAllPersonas').prop('checked', totalVisible > 0 && totalVisible === totalChecked);
            }

            // Seleccionar/deseleccionar todos
            $('#selectAllPersonas').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('#listaPersonasMasivo .persona-checkbox:visible').prop('checked', isChecked);
            });

            // Actualizar estado del checkbox "Seleccionar todos" cuando se cambian checkboxes individuales
            $(document).on('change', '#listaPersonasMasivo .persona-checkbox', function() {
                const totalVisible = $('#listaPersonasMasivo .persona-checkbox:visible').length;
                const totalChecked = $('#listaPersonasMasivo .persona-checkbox:visible:checked').length;
                $('#selectAllPersonas').prop('checked', totalVisible > 0 && totalVisible === totalChecked);
            });

            // Filtrar la lista de personas por texto
            $('#searchPersona').on('input', function() {
                aplicarFiltrosMasivo();
            });

            // Filtrar por jornada
            $('input[name="filtroJornada"]').on('change', function() {
                aplicarFiltrosMasivo();
            });

            // Mostrar/ocultar campo condicion_otra en masivo
            $('#id_condicion_masivo').on('change', function() {
                if ($(this).val() === 'otra') {
                    $('#condicion_otra_masivo_wrap').show();
                    $('#condicion_otra_masivo').prop('required', true);
                } else {
                    $('#condicion_otra_masivo_wrap').hide();
                    $('#condicion_otra_masivo').val('').prop('required', false);
                }
            });

            // Inicializar flatpickr para masivo
            flatpickr("#fechas_atencion_masivo", {
                mode: "multiple",
                dateFormat: "Y-m-d",
                locale: "es",
                onChange: function(selectedDates, dateStr, instance) {
                    // Guardar como JSON simple de strings (ISO yyyy-mm-dd)
                    const arr = selectedDates.map(d => {
                        try { return d.toISOString().slice(0,10); } catch(e) { return null; }
                    }).filter(x => x);
                    $('#fechas_seleccionadas_masivo').val(JSON.stringify(arr));
                }
            });

            // Precarga Meta "2." y Actividad "2.1" en masivo cuando se abre el modal
            $('#modalMasivo').on('shown.bs.modal', function() {
                autoprecargarMetaActividad('masivo');
            });

            // Función para precargar la meta que empiece con "2." y actividad "2.1"
            function autoprecargarMetaActividad(modo) {
                const metaSel = modo === 'masivo' ? '#id_meta_masivo' : '#id_meta';
                const actSel  = modo === 'masivo' ? '#id_actividad_masivo' : '#id_actividad';
                const acnSel  = modo === 'masivo' ? '#id_accion_masivo' : '#id_accion';

                // Si ya hay una meta seleccionada, no sobreescribir
                if ($(metaSel).val()) return;

                // Buscar option cuyo texto empiece con "2."
                let idMeta2 = null;
                $(metaSel + ' option').each(function() {
                    if (/^2\./.test($(this).text().trim())) {
                        idMeta2 = $(this).val();
                        return false;
                    }
                });
                if (!idMeta2) return;

                $(metaSel).val(idMeta2);
                // Cargar actividades y luego seleccionar "2.1"
                $.ajax({
                    url: '../personMovement/getActividades.php',
                    type: 'POST',
                    data: { id_meta: idMeta2 },
                    success: function(resp) {
                        $(actSel).empty().append('<option value="">Seleccione Actividad...</option>').append(resp).prop('disabled', false);
                        // Buscar actividad que empiece con "2.1"
                        let idAct21 = null;
                        $(actSel + ' option').each(function() {
                            if (/^2\.1/.test($(this).text().trim())) {
                                idAct21 = $(this).val();
                                return false;
                            }
                        });
                        if (idAct21) {
                            $(actSel).val(idAct21);
                            // Cargar acciones para esa actividad
                            $.ajax({
                                url: '../personMovement/getAcciones.php',
                                type: 'POST',
                                data: { id_actividad: idAct21 },
                                success: function(resp2) {
                                    $(acnSel).empty().append('<option value="">Seleccione Acción...</option>').append(resp2).prop('disabled', false);
                                }
                            });
                        }
                    }
                });
            }

            // Cascada Meta -> Actividad -> Acción para masivo
            $('#id_meta_masivo').on('change', function() {
                const idMeta = $(this).val();
                $('#id_actividad_masivo').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                $('#id_accion_masivo').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                if (idMeta) {
                    $.ajax({
                        url: '../personMovement/getActividades.php',
                        type: 'POST',
                        data: {
                            id_meta: idMeta
                        },
                        success: function(resp) {
                            $('#id_actividad_masivo').append(resp).prop('disabled', false);
                        }
                    });
                }
            });

            $('#id_actividad_masivo').on('change', function() {
                const idAct = $(this).val();
                $('#id_accion_masivo').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                if (idAct) {
                    $.ajax({
                        url: '../personMovement/getAcciones.php',
                        type: 'POST',
                        data: {
                            id_actividad: idAct
                        },
                        success: function(resp) {
                            $('#id_accion_masivo').append(resp).prop('disabled', false);
                        }
                    });
                }
            });

            // Cargar políticas públicas para modal masivo cuando cambie la acción
            $('#id_accion_masivo').on('change', function() {
                const idAccion = $(this).val();
                $('#politica_publica_masivo').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                if (idAccion) {
                    $.ajax({
                        url: '../personMovement/getPoliticaPublica.php',
                        type: 'POST',
                        data: { id_accion: idAccion },
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.politicas && response.politicas.length > 0) {
                                response.politicas.forEach(function(p) {
                                    $('#politica_publica_masivo').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                });
                            } else {
                                $('#politica_publica_masivo').append('<option value="No asignada">No asignada</option>');
                            }
                        },
                        error: function() {
                            $('#politica_publica_masivo').append('<option value="Error al consultar">Error al consultar</option>');
                        }
                    });
                }
            });

            // Enviar formulario masivo por AJAX
            $('#formMasivo').on('submit', function(e) {
                e.preventDefault();

                // Recopilar cédulas seleccionadas
                const cedulas = [];
                $('#listaPersonasMasivo input.persona-checkbox:checked').each(function() {
                    cedulas.push($(this).val());
                });

                if (cedulas.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin cédulas',
                        text: 'Debe seleccionar al menos una cédula.'
                    });
                    return;
                }

                // Validar fechas (parsear JSON guardado por flatpickr)
                let fechasArr = [];
                try {
                    const raw = $('#fechas_seleccionadas_masivo').val();
                    fechasArr = raw ? JSON.parse(raw) : [];
                } catch (err) {
                    fechasArr = [];
                }
                if (!Array.isArray(fechasArr) || fechasArr.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fechas requeridas',
                        text: 'Seleccione al menos una fecha.'
                    });
                    return;
                }

                // Preparar payload
                const payload = $(this).serializeArray();
                payload.push({
                    name: 'cedulas',
                    value: JSON.stringify(cedulas)
                });

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $.param(payload),
                    dataType: 'json',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Procesando',
                            text: 'Se están agregando los registros...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(resp) {
                        Swal.close();
                        if (resp.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Hecho',
                                text: resp.message
                            });
                            $('#modalMasivo').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: resp.message || 'Error al agregar registros.'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        let msg = 'No se pudo procesar la solicitud.';
                        try {
                            const j = JSON.parse(xhr.responseText);
                            msg = j.message || msg;
                        } catch (e) {}
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            });
        });

        // ---------------------------------------------------------------

        function updateSelectedDatesDisplay(dates) {
            selectedDates = dates;
            const display = document.getElementById('selected-dates-display');

            if (dates.length === 0) {
                display.innerHTML = '<small class="text-muted">Las fechas seleccionadas aparecerán aquí</small>';
                return;
            }

            let html = '';
            dates.forEach(date => {
                const formattedDate = new Date(date).toLocaleDateString('es-ES', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                html += `<span class="date-tag">${formattedDate}</span>`;
            });

            display.innerHTML = html;
        }

        // Función global para abrir el modal en modo edición y precargar datos
        window.editarRegistro = function(id) {
            console.log('✏️ editarRegistro llamado con id:', id);
            if (!id) return;

            // Limpiar estado previo
            $('#modalNewRecord').find('form')[0].reset();
            $('#id_registro_centro_vida').val('');
            $('#cedula_persona').removeClass('is-valid is-invalid');
            selectedDates = [];

            $.ajax({
                url: 'getRegistroByIdCentroVida.php',
                type: 'GET',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(resp) {
                    if (!resp.success) {
                        Swal.fire('Error', resp.message || 'No se encontró el registro', 'error');
                        return;
                    }

                    const d = resp.data;
                    console.log('Datos para edición:', d, resp.fechas);

                    // Poblar campos básicos
                    $('#id_registro_centro_vida').val(d.id_registro_centro_vida || id);
                    $('#cedula_persona').val(d.cedula_persona).addClass('is-valid').prop('readonly', true);
                    $('#id_condicion').val(d.id_condicion);

                    // Cambiar título del modal para modo edición
                    $('#modalNewRecordLabel').html('<i class="bi bi-pencil-fill me-2"></i>Editar Registro Centro Vida');

                    // Manejar cascada Meta -> Actividad -> Acción
                    if (d.id_meta) {
                        $('#id_meta').val(d.id_meta).trigger('change');

                        // Esperar a que se carguen las actividades y luego seleccionar
                        setTimeout(function() {
                            if (d.id_actividad) {
                                $('#id_actividad').val(d.id_actividad).prop('disabled', false).trigger('change');

                                // Esperar a que se carguen las acciones y luego seleccionar
                                setTimeout(function() {
                                    if (d.id_accion) {
                                        $('#id_accion').val(d.id_accion).prop('disabled', false).trigger('change');

                                        // Esperar a que se carguen las políticas públicas y luego seleccionar
                                        setTimeout(function() {
                                            if (d.politica_publica) {
                                                $('#politica_publica').val(d.politica_publica);
                                            }
                                        }, 300);
                                    }
                                }, 300);
                            }
                        }, 300);
                    }

                    // Campos específicos de centro vida
                    if (d.id_actividad_centro_vida) {
                        $('#actividad_centro_vida').val(d.id_actividad_centro_vida);
                    }
                    $('#departamento_procedencia').val(d.departamento_procedencia);
                    $('#observacion').val(d.observacion);

                    // Precargar campos nuevos
                    if (d.condicion_otra) {
                        $('#id_condicion').val('otra');
                        $('#condicion_otra_individual_row').show();
                        $('#condicion_otra_individual').val(d.condicion_otra).prop('required', true);
                    }
                    if (d.profesion) {
                        $('#profesion_individual').val(d.profesion);
                    }
                    if (d.jornada) {
                        $('input[name="jornada"][value="' + d.jornada + '"]').prop('checked', true);
                    }
                    if (d.ids_grupos_externos) {
                        const geIds = d.ids_grupos_externos.toString().split(',').map(s => s.trim()).filter(s => s);
                        const $container = $('#grupos_externos_ind_container');
                        $container.empty();
                        if (geIds.length > 0) {
                            geIds.forEach(function(geId) {
                                const $row = $('<div class="input-group mb-2 grupo-externo-row-ind"></div>');
                                const $sel = $('<select class="form-select" name="grupos_externos[]"><?= $grupoExternoOptionsHtml ?></select>');
                                $sel.val(geId);
                                const $btn = $('<button type="button" class="btn btn-danger btn-remove-ge-ind" tabindex="-1"><i class="bi bi-dash-circle"></i></button>');
                                $row.append($sel).append($btn);
                                $container.append($row);
                            });
                        } else {
                            resetGruposExternosInd();
                        }
                    }

                    // Fechas
                    if (Array.isArray(resp.fechas) && resp.fechas.length) {
                        selectedDates = resp.fechas;
                        try {
                            const fp = document.querySelector('#fechas_atencion')._flatpickr;
                            if (fp) {
                                fp.setDate(selectedDates, true);
                            }
                        } catch (e) {
                            console.warn('Flatpickr no disponible:', e);
                        }
                        updateSelectedDatesDisplay(selectedDates);
                        document.getElementById('fechas_seleccionadas').value = selectedDates.join(',');
                    }

                    // Abrir modal
                    $('#modalNewRecord').modal('show');
                },
                error: function(xhr, status, err) {
                    console.error('Error AJAX editarRegistro:', err, xhr.responseText);
                    Swal.fire('Error', 'No se pudo cargar el registro para edición', 'error');
                }
            });
        };

        // Función para restaurar el modal a modo "agregar"
        window.resetModalToAddMode = function() {
            $('#modalNewRecordLabel').html('<i class="bi bi-heart-plus-fill me-2"></i>Agregar Registro Centro Vida');
            $('#id_registro_centro_vida').val('');
            $('#cedula_persona').prop('readonly', false).removeClass('is-valid is-invalid');
            selectedDates = [];
            updateSelectedDatesDisplay([]);
            $('#condicion_otra_individual_row').hide();
            $('#condicion_otra_individual').val('').prop('required', false);
            $('input[name="jornada"]').prop('checked', false);
            if (typeof resetGruposExternosInd === 'function') resetGruposExternosInd();
        };
    </script>

    <!-- ====== Control de Asistencia JS ====== -->
    <script>
    $(function() {

        // ---- Filtros del modal Control Asistencia ----
        function filtrarCA() {
            const q       = $('#ca_searchPersona').val().toLowerCase().trim();
            const jornada = $('input[name="ca_filtroJornada"]:checked').val();
            $('#ca_listaPersonas .persona-ca-item').each(function() {
                const txt        = $(this).text().toLowerCase();
                const pJornada   = $(this).data('jornada') || '';
                const matchText  = !q || txt.indexOf(q) !== -1;
                const matchJorn  = jornada === 'todos' || pJornada === jornada;
                $(this).toggle(matchText && matchJorn);
            });
            updateCACounter();
        }

        function updateCACounter() {
            const total   = $('#ca_listaPersonas .persona-ca-checkbox:checked').length;
            const visible = $('#ca_listaPersonas .persona-ca-checkbox:visible').length;
            const chkVis  = $('#ca_listaPersonas .persona-ca-checkbox:visible:checked').length;
            $('#ca_contador').text(total);
            $('#ca_selectAll').prop('checked', visible > 0 && visible === chkVis);
        }

        // ---- Precargar asistencia guardada al cambiar fecha ----
        function cargarAsistenciaGuardadaCA(fecha) {
            if (!fecha) return;
            $.ajax({
                url: 'getControlAsistencia.php',
                type: 'GET',
                data: { fecha: fecha },
                dataType: 'json',
                success: function(resp) {
                    // Desmarcar todos primero
                    $('#ca_listaPersonas .persona-ca-checkbox').prop('checked', false);
                    if (resp.success && resp.cedulas.length > 0) {
                        let marcadas = 0;
                        $('#ca_listaPersonas .persona-ca-checkbox').each(function() {
                            if (resp.cedulas.indexOf($(this).val()) !== -1) {
                                $(this).prop('checked', true);
                                marcadas++;
                            }
                        });
                        if (marcadas > 0) {
                            Swal.fire({ icon: 'info', title: 'Asistencia cargada', text: marcadas + ' persona(s) precargadas del registro guardado para esta fecha.', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                        }
                    }
                    updateCACounter();
                }
            });
        }

        $('#ca_fecha').on('change', function() {
            cargarAsistenciaGuardadaCA($(this).val());
        });

        $('#ca_searchPersona').on('input', filtrarCA);
        $('input[name="ca_filtroJornada"]').on('change', filtrarCA);

        $('#ca_selectAll').on('change', function() {
            $('#ca_listaPersonas .persona-ca-checkbox:visible').prop('checked', $(this).is(':checked'));
            updateCACounter();
        });

        $(document).on('change', '#ca_listaPersonas .persona-ca-checkbox', updateCACounter);

        // Limpiar al cerrar modal Control Asistencia
        $('#modalControlAsistencia').on('hidden.bs.modal', function() {
            $('#ca_listaPersonas .persona-ca-checkbox').prop('checked', false);
            $('#ca_selectAll').prop('checked', false);
            $('#ca_searchPersona').val('');
            $('#ca_fecha').val('');
            $('input[name="ca_filtroJornada"][value="todos"]').prop('checked', true);
            filtrarCA();
        });

        // ---- Guardar Control de Asistencia ----
        $('#ca_btnGuardar').on('click', function() {
            const fecha = $('#ca_fecha').val();
            if (!fecha) {
                Swal.fire({ icon: 'warning', title: 'Fecha requerida', text: 'Seleccione una fecha de asistencia.', toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
                return;
            }
            const cedulas = [];
            $('#ca_listaPersonas .persona-ca-checkbox:checked').each(function() {
                cedulas.push($(this).val());
            });
            if (cedulas.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Sin personas', text: 'Seleccione al menos una persona.', toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
                return;
            }
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
            $.ajax({
                url: 'saveControlAsistencia.php',
                type: 'POST',
                data: { cedulas: JSON.stringify(cedulas), fecha_asistencia: fecha },
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        Swal.fire({ icon: 'success', title: 'Asistencia guardada', text: resp.count + ' persona(s) registradas para el ' + fecha + '.', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                        $('#modalControlAsistencia').modal('hide');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo guardar.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar con el servidor.' });
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar Asistencia');
                }
            });
        });

        // ---- Intercept "Agregar Masivo" button: preguntar por Control de Asistencia ----
        let _skipCAMasivo = false;

        $('#btnAbrirMasivo').on('click', function() {
            if (_skipCAMasivo) {
                _skipCAMasivo = false;
                $('#modalMasivo').modal('show');
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
                        html: '<input type="date" id="swal_ca_fecha_masivo" class="swal2-input" max="' + (function(){var d=new Date();return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');})() + '">',
                        didOpen: function() { document.getElementById('swal_ca_fecha_masivo').value = (function(){var d=new Date();return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');})(); },
                        confirmButtonText: 'Cargar',
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar',
                        preConfirm: function() {
                            const f = document.getElementById('swal_ca_fecha_masivo').value;
                            if (!f) { Swal.showValidationMessage('Seleccione una fecha'); }
                            return f;
                        }
                    }).then(function(dateResult) {
                        if (dateResult.isConfirmed && dateResult.value) {
                            $.ajax({
                                url: 'getControlAsistencia.php',
                                type: 'GET',
                                data: { fecha: dateResult.value },
                                dataType: 'json',
                                success: function(resp) {
                                    $('#modalMasivo').modal('show');
                                    if (resp.success && resp.cedulas.length > 0) {
                                        // Pre-check matching people
                                        $('#listaPersonasMasivo .persona-checkbox').each(function() {
                                            $(this).prop('checked', resp.cedulas.indexOf($(this).val()) !== -1);
                                        });
                                        const found = $('#listaPersonasMasivo .persona-checkbox:checked').length;
                                        Swal.fire({ icon: 'success', title: 'Listo', text: found + ' persona(s) precargadas del control de asistencia del ' + dateResult.value + '.', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                                    } else {
                                        Swal.fire({ icon: 'info', title: 'Sin registros', text: 'No hay control de asistencia guardado para esa fecha.', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                                    }
                                },
                                error: function() {
                                    $('#modalMasivo').modal('show');
                                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo obtener el control de asistencia.' });
                                }
                            });
                        } else {
                            $('#modalMasivo').modal('show');
                        }
                    });
                } else {
                    $('#modalMasivo').modal('show');
                }
            });
        });

    });
    </script>
</body>

</html>