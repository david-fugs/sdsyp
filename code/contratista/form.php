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
    <title>SDSYP</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    <link rel="stylesheet" href="styleSell.css">
    <!-- Bootstrap CSS -->
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Librerías de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

    <!-- Estilos personalizados para aumentar tamaño de fuente -->
    <style>
        /* Aumentar tamaño de fuente general */
        body {
            font-size: 16px !important;
        }

        /* Tabla - aumentar tamaño de fuente */
        .modern-table {
            font-size: 15px !important;
        }

        .modern-table th {
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        .modern-table td {
            font-size: 15px !important;
            padding: 12px 8px !important;
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Columnas específicas más anchas */
        .modern-table td.col-meta,
        .modern-table td.col-actividad,
        .modern-table td.col-accion {
            max-width: 250px;
        }

        /* Filtros y inputs - aumentar tamaño */
        .modern-input,
        .modern-select {
            font-size: 15px !important;
            padding: 10px 12px !important;
        }

        .filter-group label {
            font-size: 14px !important;
            font-weight: 600 !important;
        }

        /* Botones - aumentar tamaño */
        .btn-modern {
            font-size: 15px !important;
            padding: 10px 20px !important;
        }

        .btn-action {
            padding: 8px 10px !important;
            font-size: 14px !important;
        }

        /* Header moderno */
        .modern-header h2 {
            font-size: 26px !important;
        }

        /* DataTables controles */
        .dataTables_info,
        .dataTables_paginate {
            font-size: 14px !important;
        }

        .dataTables_length select,
        .dataTables_length label {
            font-size: 14px !important;
        }

        .paginate_button {
            font-size: 14px !important;
        }

        /* Modales - aumentar tamaño de fuente */
        .modal-title {
            font-size: 20px !important;
        }

        .modal-body {
            font-size: 15px !important;
        }

        .form-label {
            font-size: 14px !important;
            font-weight: 600 !important;
        }

        .form-control,
        .form-select {
            font-size: 15px !important;
        }

        /* SweetAlert - aumentar tamaño */
        .swal2-title {
            font-size: 20px !important;
        }

        .swal2-content {
            font-size: 16px !important;
        }

        /* Enlaces y navegación */
        a {
            font-size: 15px !important;
        }

        /* Scroll sincronizado para tablas grandes: barra superior */
        .table-scroll-top {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            height: 16px; /* altura del scrollbar */
            box-sizing: border-box;
            display: none; /* se mostrará cuando la tabla necesite scroll */
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        /* Hacer que la tabla sea de ancho completo para permitir overflow */
        .table-scroll .modern-table {
            width: 100%;
            min-width: 1200px; /* ajustar según columnas para forzar scroll horizontal cuando sea necesario */
        }

        /* Mensajes de estado */
        .text-muted,
        .text-success,
        .text-danger {
            font-size: 13px !important;
        }
    </style>
</head>
<?php
include("../../conexion.php");

$programas = "SELECT * FROM programas ";
$result_programas = mysqli_query($mysqli, $programas);
if (!$result_programas) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

$condiciones = "SELECT * FROM condiciones_componente";
$result_condiciones = mysqli_query($mysqli, $condiciones);
if (!$result_condiciones) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

$usuarios = "SELECT * FROM usuarios ORDER BY nombre ASC";
$result_usuarios = mysqli_query($mysqli, $usuarios);
if (!$result_usuarios) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

// Aplicar filtro de grupos según tipo de usuario
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');
$grupos = "SELECT g.* FROM grupos g WHERE 1=1 $where_grupos ORDER BY g.descripcion_grupo ASC";
$result_grupos_query = mysqli_query($mysqli, $grupos);
if (!$result_grupos_query) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}
// Convertir resultado a array para poder reutilizarlo en múltiples loops
$result_grupos = mysqli_fetch_all($result_grupos_query, MYSQLI_ASSOC);

$metas = "SELECT * FROM metas ORDER BY descripcion_meta ASC";
$result_metas = mysqli_query($mysqli, $metas);
if (!$result_metas) {
    die("Error en la consulta de metas: " . mysqli_error($mysqli));
}
$actividades = "SELECT * FROM actividad_contratista ORDER BY descripcion_actividad ASC";
$result_actividades = mysqli_query($mysqli, $actividades);
if (!$result_actividades) {
    die("Error en la consulta de actividades: " . mysqli_error($mysqli));
}
$comunas = "SELECT * FROM comunas ORDER BY nombre_com ASC";
$result_comunas = mysqli_query($mysqli, $comunas);
if (!$result_comunas) {
    die("Error en la consulta de comunas: " . mysqli_error($mysqli));
}

?>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;"><b><i
                class="bi bi-arrow-left-right"></i> REGISTRO ACTIVIDADES MASIVAS</b></h1>

    <!-- Tabla de Movimientos -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-arrow-left-right"></i> Actividades Realizadas</h2>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                        <i class="bi bi-plus-circle-fill"></i>
                        Agregar actividad
                    </button>
                    <form id="exportExcelForm" action="exportActividadesExcel.php" method="get" style="display:inline;">
                        <input type="hidden" name="filtro_anio" id="export_filtro_anio">
                        <input type="hidden" name="filtro_mes" id="export_filtro_mes">
                        <input type="hidden" name="filtro_funcionario" id="export_filtro_funcionario">
                        <button type="submit" class="btn-modern btn-warning">
                            <i class="bi bi-file-earmark-excel"></i>
                            Exportar Excel
                        </button>
                    </form>
                </div>
            </div>

            <!-- Filtros modernos -->
            <script>
                // Sincronizar los filtros con el formulario de exportación
                document.addEventListener('DOMContentLoaded', function() {
                    function syncExportFilters() {
                        document.getElementById('export_filtro_anio').value = document.getElementById('filtro_anio').value;
                        document.getElementById('export_filtro_mes').value = document.getElementById('filtro_mes').value;
                        document.getElementById('export_filtro_funcionario').value = document.getElementById('filtro_funcionario').value;
                    }
                    // Actualizar al cargar y cuando cambian los filtros
                    syncExportFilters();
                    document.getElementById('filtro_anio').addEventListener('change', syncExportFilters);
                    document.getElementById('filtro_mes').addEventListener('change', syncExportFilters);
                    document.getElementById('filtro_funcionario').addEventListener('change', syncExportFilters);
                });
            </script>
            <div class="modern-filters">
                <form action="form.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="filtro_anio">Año</label>
                        <select id="filtro_anio" name="filtro_anio" class="modern-select">
                            <option value="">Todos los años</option>
                            <?php
                            $currentYear = date('Y');
                            $startYear = 2022;
                            for ($y = $startYear; $y <= $currentYear; $y++) {
                                $selected = (isset($_GET['filtro_anio']) && $_GET['filtro_anio'] == $y) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_mes">Mes</label>
                        <select id="filtro_mes" name="filtro_mes" class="modern-select">
                            <option value="">Todos los meses</option>
                            <?php
                            $meses = [
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
                            foreach ($meses as $num => $nombre) {
                                $selected = (isset($_GET['filtro_mes']) && $_GET['filtro_mes'] == $num) ? 'selected' : '';
                                echo "<option value='$num' $selected>$nombre</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_funcionario">Funcionario Responsable</label>
                        <select id="filtro_funcionario" name="filtro_funcionario" class="modern-select">
                            <option value="">Todos</option>
                            <?php
                            // Si es tipo usuario 3, solo mostrar su propio usuario
                            $tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
                            $id_usuario_session = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;
                            
                            if ($tipo_usuario == 3 && $id_usuario_session) {
                                $query_funcionarios = "SELECT id, nombre FROM usuarios WHERE id = $id_usuario_session ORDER BY nombre ASC";
                            } else {
                                $query_funcionarios = "SELECT id, nombre FROM usuarios WHERE tipo_usuario = 3 ORDER BY nombre ASC";
                            }
                            
                            $result_funcionarios = mysqli_query($mysqli, $query_funcionarios);
                            if ($result_funcionarios) {
                                while ($funcionario = mysqli_fetch_assoc($result_funcionarios)) {
                                    $selected = (isset($_GET['filtro_funcionario']) && $_GET['filtro_funcionario'] == $funcionario['id']) ? 'selected' : '';
                                    echo "<option value='" . $funcionario['id'] . "' $selected>" . htmlspecialchars($funcionario['nombre']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-modern btn-primary">
                            <i class="bi bi-search"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla moderna -->
            <div class="modern-table-wrapper">
                <!-- Contenedor para scroll superior sincronizado -->
                <div class="table-scroll-top" id="tableScrollTop" aria-hidden="true"></div>
                <div class="table-scroll" id="tableScroll">
                    <table class="modern-table" id="salesTable">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-meta">Meta</th>
                            <th class="col-actividad">Actividad</th>
                            <th class="col-accion">Acción</th>
                            <th>Política Pública</th>
                            <th>Centro Vida</th>
                            <th>Fecha Atención</th>
                            <th>Nombre Líder</th>
                            <th>Teléfono Contacto</th>
                            <th>Comuna/Corregimiento</th>
                            <th>Medio de Verificación</th>
                            <th>Cant. Masculino</th>
                            <th>Cant. Femenino</th>
                            <th>Tipo Actividad</th>
                            <th>Observación Actividad</th>
                            <th>Funcionario Responsable</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include "getActivitiesForm.php"; ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <br /><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="back" /></a><br>
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 shadow-sm">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalEdicionLabel">Editar registro actividad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="editRegistro.php" method="POST">
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="id_registro" id="edit-id_registro" value="">
                        <!-- Fila 2: Meta, Actividad, Acción, Política Pública, Lugar del evento -->
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="edit-meta" name="id_meta" required>
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php foreach ($result_metas as $meta) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="edit-meta">Meta</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="edit-actividad" name="id_actividad" required disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                                <label for="edit-actividad">Actividad</label>
                            </div>

                            <div class="col-md-3 mb-3 form-floating">
                                <select class="form-select" id="edit-accion" name="id_accion" required disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                                <label for="edit-accion">Acción</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="edit-politica-publica" name="politica_publica" required>
                                    <option value="" selected>Seleccione Política Pública...</option>
                                </select>
                                <label for="edit-politica-publica">Política Pública</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="edit-centro-vida" name="id_centro_vida">
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_grupos as $grupo) { ?>
                                        <option value="<?= $grupo['id_grupo']; ?>" data-limite="<?= $grupo['limite_personas']; ?>"><?= $grupo['descripcion_grupo']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="edit-centro-vida">Lugar del evento</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating d-none" id="edit-otro-lugar-container">
                                <input type="text" class="form-control" id="edit-otro_lugar" name="otro_lugar" placeholder="Especifique el lugar">
                                <label for="edit-otro_lugar">Otro lugar (especificar)</label>
                            </div>
                            <div class="col-md-3 mb-3 form-floating">
                                <select class="form-select" id="edit-id_entregas" name="id_entregas" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_actividades as $actividad) { ?>
                                        <option value="<?= $actividad['id_actividad_contratista']; ?>"><?= $actividad['descripcion_actividad']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="edit-id_entregas">Entregas/Actividades</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="date" class="form-control" id="edit-fecha_atencion" name="fecha_atencion">
                                <label for="edit-fecha_atencion">Fecha Atención</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="edit-nombre_lider" name="nombre_lider" placeholder="Funcionario responsable">
                                <label for="edit-nombre_lider">Nombre del líder</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="edit-telefono_contacto" name="telefono_contacto" placeholder="Teléfono de contacto">
                                <label for="edit-telefono_contacto">Teléfono de contacto</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select name="id_comuna" id="edit-id_comuna" class="form-select">
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_comunas as $comuna) { ?>
                                        <option value="<?= $comuna['id_com']; ?>"><?= $comuna['nombre_com']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="edit-id_comuna">Comuna/Corregimiento</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select name="medio_verificacion" id="edit-medio_verificacion" class="form-select">
                                    <option value="" selected>Seleccione...</option>
                                    <option value="Acta">Acta</option>
                                    <option value="Acta y registro fotografico">Acta y registro fotografico</option>
                                    <option value="Registro campo">Registro Campo</option>
                                    <option value="Historio/ expediente">Historio/ expediente</option>
                                    <option value="Captura pantalla digital">Captura pantalla digital</option>
                                    <option value="SPP">SPP</option>
                                    <option value="SPP - Registro fotografico">SPP - Registro fotografico</option>
                                </select>
                                <label for="edit-medio_verificacion">Medio de Verificación</label>
                            </div>
                            <div class="col-md-3 mb-3 form-floating">
                                <input type="number" name="cantidad_masculino" id="edit-cantidad_masculino" class="form-control" placeholder="Cantidad Masculino">
                                <label for="edit-cantidad_masculino">Cantidad Masculino</label>
                            </div>
                            <div class="col-md-3 mb-3 form-floating">
                                <input type="number" name="cantidad_femenino" id="edit-cantidad_femenino" class="form-control" placeholder="Cantidad Femenino">
                                <label for="edit-cantidad_femenino">Cantidad Femenino</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="text" class="form-control" id="edit-funcionario_responsable" name="funcionario_responsable" placeholder="Funcionario responsable">
                                <label for="edit-funcionario_responsable">Funcionario Responsable</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select name="tipo_actividad" id="edit-tipo_actividad" class="form-select">
                                    <option value="" selected>Seleccione...</option>
                                    <option value="Articulacion">Articulacion</option>
                                    <option value="Masiva">Masiva</option>
                                    <option value="Registro de Actividad">Registro de Actividad</option>
                                </select>
                                <label for="edit-tipo_actividad">Tipo Actividad</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="text" class="form-control" id="edit-observacion_actividad" name="observacion_actividad" placeholder="Observación Actividad">
                                <label for="edit-observacion_actividad">Observación Actividad</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn-modern btn-outline btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn-modern btn-primary" id="guardarCambios">
                            <i class="bi bi-check-lg"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Add registro-->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Hacemos el modal más ancho -->
            <div class="modal-content">
                <form action="addRegistro.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar registro actividad
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila 2: Meta, Actividad, Acción, Política Pública -->
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="meta" name="id_meta" required>
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php foreach ($result_metas as $meta) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="meta">Meta</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="actividad" name="id_actividad" required disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                                <label for="actividad">Actividad</label>
                            </div>
                            <div class="col-md-3 mb-3 form-floating">
                                <select class="form-select" id="accion" name="id_accion" required disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                                <label for="accion">Acción</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="politica-publica" name="politica_publica" required>
                                    <option value="" selected>Seleccione Política Pública...</option>
                                </select>
                                <label for="politica-publica">Política Pública</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="centro-vida" name="id_centro_vida">
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_grupos as $grupo) { ?>
                                        <option value="<?= $grupo['id_grupo']; ?>" data-limite="<?= $grupo['limite_personas']; ?>"><?= $grupo['descripcion_grupo']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="centro-vida">Lugar del evento</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating d-none" id="otro-lugar-container">
                                <input type="text" class="form-control" id="otro_lugar" name="otro_lugar" placeholder="Especifique el lugar">
                                <label for="otro_lugar">Otro lugar (especificar)</label>
                            </div>

                            <!-- Fila 5: Observación -->
                            <div class="row">
                                <div class="col-md-6 mb-3 form-floating">
                                    <!-- <input type="text" class="form-control" id="id_actividad" name="id_actividad" placeholder="Entregas/Actividades" > -->
                                    <select class="form-select" id="id_entregas" name="id_entregas" required>
                                        <option value="" selected>Seleccione...</option>
                                        <?php foreach ($result_actividades as $actividad) { ?>
                                            <option value="<?= $actividad['id_actividad_contratista']; ?>"><?= $actividad['descripcion_actividad']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="id_actividad">Entregas/Actividades</label>
                                </div>
                                <div class="col-md-6 mb-3 form-floating">
                                    <input type="date" class="form-control" id="fecha_atencion" name="fecha_atencion">
                                    <label for="fecha_atencion">Fecha Atencion</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3 form-floating">
                                    <input type="text" class="form-control" id="nombre_lider" name="nombre_lider" placeholder="Funcionario responsable">
                                    <label for="nombre_lider">Nombre del lider</label>
                                </div>
                                <div class="col-md-6 mb-3 form-floating">
                                    <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" placeholder="Telefono de contacto">
                                    <label for="telefono_contacto">Telefono de contacto</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3 form-floating">
                                    <input type="text" class="form-control" id="funcionario_responsable" name="funcionario_responsable" placeholder="Funcionario responsable">
                                    <label for="funcionario_responsable">Funcionario Responsable</label>
                                </div>
                                <div class="col-md-6 mb-3 form-floating">
                                    <select name="tipo_actividad" id="tipo_actividad" class="form-select">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="Articulacion">Articulacion</option>
                                        <option value="Masiva">Masiva</option>
                                        <option value="Registro de Actividad">Registro de Actividad</option>

                                    </select>
                                    <label for="tipo_actividad">Tipo Actividad</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3 form-floating">
                                    <select name="id_comuna" id="id_comuna" class="form-select">
                                        <option value="" selected>Seleccione...</option>
                                        <?php foreach ($result_comunas as $comuna) { ?>
                                            <option value="<?= $comuna['id_com']; ?>"><?= $comuna['nombre_com']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="id_comuna">Comuna/Corregimiento</label>
                                </div>
                                <div class="col-md-6 mb-3 form-floating">
                                    <select name="medio_verificacion" id="medio_verificacion" class="form-select">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="Acta">Acta</option>
                                        <option value="Acta y registro fotografico">Acta y registro fotografico</option>
                                        <option value="Registro campo">Registro Campo</option>
                                        <option value="Historio/ expediente">Historio/ expediente</option>
                                        <option value="Captura pantalla digital">Captura pantalla digital</option>
                                        <option value="SPP">SPP</option>
                                        <option value="SPP - Registro fotografico">SPP - Registro fotografico</option>
                                    </select>
                                    <label for="medio_verificacion">Medio de Verificación</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3 form-floating">
                                        <input type="number" name="cantidad_masculino" id="cantidad_masculino" class="form-control" placeholder="Cantidad Masculino">
                                        <label for="cantidad_masculino">Cantidad Masculino</label>
                                    </div>
                                    <div class="col-md-4 mb-3 form-floating">
                                        <input type="number" name="cantidad_femenino" id="cantidad_femenino" class="form-control" placeholder="Cantidad Femenino">
                                        <label for="cantidad_femenino">Cantidad Femenino</label>
                                    </div>

                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3 form-floating">
                                    <input type="text" class="form-control" id="observacion_actividad" name="observacion_actividad" placeholder="Observacion Actividad">
                                    <label for="observacion_actividad">Observacion Actividad</label>
                                </div>
                            </div>
                        </div>


                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn-modern btn-outline btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                            <button type="submit" class="btn-modern btn-success">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>



    <!-- modal edicion -->

</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modalEdicion = document.getElementById("modalEdicion");
        if (!modalEdicion) {
            Swal.fire({
                icon: 'error',
                title: 'Error de modal',
                text: 'No se encontró el modal de edición en el HTML.'
            });
            return;
        }
        modalEdicion.addEventListener("shown.bs.modal", function(event) {
            // event.relatedTarget puede ser undefined si el modal se abre por JS
            // (por ejemplo después de una búsqueda o redraw). Usar fallback a window.lastEditButton
            const button = event.relatedTarget || window.lastEditButton;
            if (!button) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de edición',
                    text: 'No se detectó el botón que abrió el modal. Verifica la estructura del HTML.'
                });
                return;
            }
            // Precargar todos los campos del modal de edición
            $("#edit-id_registro").val(button.getAttribute("data-id_registro") || "");
            $("#edit-meta").val(button.getAttribute("data-meta") || "");
            // Precargar entregas/actividad desde nueva data attribute (id_entregas en la BD)
            const idEntregas = button.getAttribute("data-entregas_actividad") || button.getAttribute("data-id_entregas") || "";
            $("#edit-id_entregas").val(idEntregas);
            // Actividad y acción se cargan por AJAX
            const idMeta = button.getAttribute("data-meta");
            const idActividad = button.getAttribute("data-actividad");
            const idAccion = button.getAttribute("data-accion");
            const idPolitica = button.getAttribute("data-politica_publica");
            $("#edit-centro-vida").val(button.getAttribute("data-centro_vida") || "");
            $("#edit-fecha_atencion").val(button.getAttribute("data-fecha_atencion") || "");
            $("#edit-nombre_lider").val(button.getAttribute("data-nombre_lider") || "");
            $("#edit-telefono_contacto").val(button.getAttribute("data-telefono_contacto") || "");
            $("#edit-id_comuna").val(button.getAttribute("data-comuna") || "");
            $("#edit-medio_verificacion").val(button.getAttribute("data-medio_verificacion") || "");
            $("#edit-cantidad_masculino").val(button.getAttribute("data-cantidad_masculino") || "");
            $("#edit-cantidad_femenino").val(button.getAttribute("data-cantidad_femenino") || "");
            $("#edit-tipo_actividad").val(button.getAttribute("data-tipo_actividad") || "");
            $("#edit-observacion_actividad").val(button.getAttribute("data-observacion_actividad") || "");
            $("#edit-funcionario_responsable").val(button.getAttribute("data-funcionario_responsable") || "");
            
            // Manejar el campo otro_lugar
            const centroVida = button.getAttribute("data-centro_vida") || "";
            const otroLugar = button.getAttribute("data-otro_lugar") || "";
            
            if (otroLugar) {
                $("#edit-centro-vida").val(""); // Otro seleccionado
                $("#edit-otro_lugar").val(otroLugar);
                $("#edit-otro-lugar-container").removeClass("d-none");
            } else {
                $("#edit-centro-vida").val(centroVida);
                $("#edit-otro-lugar-container").addClass("d-none");
            }

            // Cargar actividades y acciones por AJAX
            if (idMeta) {
                $.ajax({
                    url: 'getActividades.php',
                    type: 'POST',
                    data: {
                        id_meta: idMeta
                    },
                    success: function(response) {
                        $('#edit-actividad').empty().append('<option value="">Seleccione Actividad...</option>');
                        $('#edit-actividad').append(response).prop('disabled', false);
                        if (idActividad) {
                            $('#edit-actividad').val(idActividad);
                            $.ajax({
                                url: 'getAcciones.php',
                                type: 'POST',
                                data: {
                                    id_actividad: idActividad
                                },
                                success: function(response) {
                                    $('#edit-accion').empty().append('<option value="">Seleccione Acción...</option>');
                                    $('#edit-accion').append(response).prop('disabled', false);
                                    // Precargar el valor de acción igual que actividad
                                    if (idAccion) {
                                        $('#edit-accion').val(idAccion);
                                        // Si no lo selecciona, forzar con trigger
                                        if ($('#edit-accion').val() !== idAccion) {
                                            setTimeout(function() {
                                                $('#edit-accion').val(idAccion).trigger('change');
                                            }, 100);
                                        }
                                    }
                                    // Cargar políticas públicas para esta acción
                                    if (idAccion) {
                                        $('#edit-politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                                        $.ajax({
                                            url: 'getPoliticaPublica.php',
                                            type: 'POST',
                                            data: {
                                                id_accion: idAccion
                                            },
                                            dataType: 'json',
                                            success: function(response) {
                                                if (response && response.politicas && response.politicas.length > 0) {
                                                    response.politicas.forEach(function(p) {
                                                        $('#edit-politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                                    });
                                                    $('#edit-politica-publica').val(idPolitica);
                                                } else {
                                                    $('#edit-politica-publica').append('<option value="">No asignada</option>');
                                                }
                                            },
                                            error: function() {
                                                $('#edit-politica-publica').append('<option value="">Error al consultar</option>');
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            }
        });
        // Registrar el botón editar pulsado (delegado) para usarlo como fallback
        $(document).on('click', '.btn-edit', function(e) {
            window.lastEditButton = this;
        });
    });
    $(document).ready(function() {
        // Manejar cambio en lugar del evento (modal agregar)
        $('#centro-vida').on('change', function() {
            const selectedText = $(this).find('option:selected').text().toUpperCase();
            if (selectedText.includes('OTRO')) {
                $('#otro-lugar-container').removeClass('d-none');
                $('#otro_lugar').prop('required', true);
            } else {
                $('#otro-lugar-container').addClass('d-none');
                $('#otro_lugar').prop('required', false).val('');
            }
        });

        // Manejar cambio en lugar del evento (modal edición)
        $('#edit-centro-vida').on('change', function() {
            const selectedText = $(this).find('option:selected').text().toUpperCase();
            if (selectedText.includes('OTRO')) {
                $('#edit-otro-lugar-container').removeClass('d-none');
                $('#edit-otro_lugar').prop('required', true);
            } else {
                $('#edit-otro-lugar-container').addClass('d-none');
                $('#edit-otro_lugar').prop('required', false).val('');
            }
        });

        function buscarPersona() {
            const cedula = $('#cedula_form').val().trim();
            if (cedula === '') return;

            $.ajax({
                url: '../buscar_persona.php',
                method: 'POST',
                data: {
                    cedula: cedula
                },
                dataType: 'json',
                success: function(response) {
                    if (response.encontrado) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Persona encontrada',
                            text: 'Nombre: ' + response.nombres + ' ' + response.apellidos,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Persona no encontrada',
                            text: 'No se encontró ninguna persona con esa cédula.',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al buscar persona.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Buscar cuando presiona Enter
        $('#cedula_form').on('keypress', function(e) {
            if (e.which === 13) { // Enter
                buscarPersona();
            }
        });

        // Buscar cuando hace clic fuera
        $('#cedula_form').on('blur', function() {
            buscarPersona();
        });

        // Controlar habilitación del campo grupo según la condición
        $('#condicion').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const condicionTexto = selectedOption.text().toUpperCase();

            if (condicionTexto.includes('CPSAM TRASLADADO')) {
                $('#grupo').prop('disabled', false).prop('required', true);
                $('#grupo').parent().removeClass('d-none');
                $('#grupo').parent().find('label').text('Centro Vida Traslado');
            } else {
                $('#grupo').prop('disabled', true).prop('required', false);
                $('#grupo').val(''); // Limpiar selección
                $('#grupo').parent().addClass('d-none');
                $('#limite-info').remove(); // Remover info del límite
            }
        });

        // Inicializar estado del campo grupo
        $('#condicion').trigger('change');

        // Manejar selección de Meta para cargar Actividades
        $('#meta').on('change', function() {
            const idMeta = $(this).val();

            // Limpiar y deshabilitar campos dependientes
            $('#actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
            $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);

            if (idMeta) {
                $.ajax({
                    url: 'getActividades.php',
                    type: 'POST',
                    data: {
                        id_meta: idMeta
                    },
                    success: function(response) {
                        $('#actividad').append(response).prop('disabled', false);
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al cargar las actividades',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });

        // Manejar selección de Actividad para cargar Acciones
        $('#actividad').on('change', function() {
            const idActividad = $(this).val();
            // Limpiar y deshabilitar campo de acciones
            $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
            $('#politica-publica').val(''); // Limpiar política pública
            if (idActividad) {
                $.ajax({
                    url: 'getAcciones.php',
                    type: 'POST',
                    data: {
                        id_actividad: idActividad
                    },
                    success: function(response) {
                        $('#accion').append(response).prop('disabled', false);
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

        // Manejar selección de Acción para consultar políticas públicas (formulario agregar movimiento)
        $('#accion').on('change', function() {
            const idAccion = $(this).val();
            // Vaciar y resetear el select de política pública cada vez que se cambia la acción
            $('#politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
            $('#politica-publica').prop('selectedIndex', 0);
            if (idAccion) {
                $.ajax({
                    url: 'getPoliticaPublica.php',
                    type: 'POST',
                    data: {
                        id_accion: idAccion
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.politicas && response.politicas.length > 0) {
                            response.politicas.forEach(function(p) {
                                $('#politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                            });
                        } else {
                            $('#politica-publica').append('<option value="">No asignada</option>');
                        }
                    },
                    error: function() {
                        $('#politica-publica').append('<option value="">Error al consultar</option>');
                    }
                });
            }
        });

        // Manejar selección de Meta para cargar Actividades (Modal de Edición)
        $('#edit-meta').on('change', function() {
            const idMeta = $(this).val();

            // Limpiar y deshabilitar campos dependientes
            $('#edit-actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
            $('#edit-accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
            $('#edit-politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

            if (idMeta) {
                $.ajax({
                    url: 'getActividades.php',
                    type: 'POST',
                    data: {
                        id_meta: idMeta
                    },
                    success: function(response) {
                        $('#edit-actividad').append(response).prop('disabled', false);
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al cargar las actividades',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });

        // Manejar selección de Actividad para cargar Acciones (Modal de Edición)
        $('#edit-actividad').on('change', function() {
            const idActividad = $(this).val();

            // Limpiar y deshabilitar campo de acciones
            $('#edit-accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
            $('#edit-politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

            if (idActividad) {
                $.ajax({
                    url: 'getAcciones.php',
                    type: 'POST',
                    data: {
                        id_actividad: idActividad
                    },
                    success: function(response) {
                        $('#edit-accion').append(response).prop('disabled', false);
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

        // Manejar selección de Acción para consultar política pública (Modal de Edición)
        $('#edit-accion').on('change', function() {
            const idAccion = $(this).val();
            // Vaciar y resetear el select de política pública cada vez que se cambia la acción
            $('#edit-politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
            $('#edit-politica-publica').prop('selectedIndex', 0);

            // Obtener el valor actual de id_politica_publica del botón que abrió el modal
            let idPolitica = null;
            if (window.lastEditButton) {
                idPolitica = window.lastEditButton.getAttribute('data-id_politica_publica');
            }

            if (idAccion) {
                $.ajax({
                    url: 'getPoliticaPublica.php',
                    type: 'POST',
                    data: {
                        id_accion: idAccion
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.politicas && response.politicas.length > 0) {
                            response.politicas.forEach(function(p) {
                                $('#edit-politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                            });
                            // Seleccionar la opción después de agregar todas
                            if (idPolitica) {
                                $('#edit-politica-publica').val(idPolitica);
                            }
                        } else {
                            $('#edit-politica-publica').append('<option value="">No asignada</option>');
                        }
                    },
                    error: function() {
                        $('#edit-politica-publica').append('<option value="">Error al consultar</option>');
                    }
                });
            }
        });

        // Controlar habilitación del campo grupo según la condición en modal de edición
        $('#edit-condicion').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const condicionTexto = selectedOption.text().toUpperCase();

            if (condicionTexto.includes('CPSAM TRASLADADO')) {
                $('#edit-centro-vida').prop('disabled', false);
                $('#edit-centro-vida-container').removeClass('d-none');
            } else {
                $('#edit-centro-vida').prop('disabled', true);
                $('#edit-centro-vida').val(''); // Limpiar selección
                $('#edit-centro-vida-container').addClass('d-none');
                $('#edit-limite-info').remove(); // Remover info del límite
            }
        });

        // Validar límite del centro de vida en modal de edición
        $('#edit-centro-vida').on('change', function() {
            const idGrupo = $(this).val();

            if (idGrupo) {
                // Verificar el límite actual del grupo
                $.ajax({
                    url: '../persons/checkGroupLimit.php',
                    type: 'POST',
                    data: {
                        id_grupo: idGrupo
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Crear elemento de información si no existe
                        if ($('#edit-limite-info').length === 0) {
                            $('#edit-centro-vida').parent().append('<small id="edit-limite-info" class="text-muted mt-1"></small>');
                        }

                        const color = response.limitReached ? 'text-danger' : 'text-success';
                        $('#edit-limite-info').removeClass('text-muted text-success text-danger').addClass(color);
                        $('#edit-limite-info').text(`Personas en el centro: ${response.personasActuales}/${response.limite}`);

                        if (response.limitReached) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Centro lleno',
                                text: `El centro "${response.grupoNombre}" ha alcanzado su límite máximo de ${response.limite} personas.`,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    }
                });
            } else {
                $('#edit-limite-info').remove();
            }
        });

        // Validar límite antes de enviar el formulario de edición
        $('form[action="editPersonMovement.php"]').on('submit', function(e) {
            const grupoId = $('#edit-centro-vida').val();
            const grupoOriginal = $('#edit-centro-vida').data('original-value') || '';

            // Solo validar si se cambió el centro o se agregó uno nuevo
            if (grupoId && grupoId !== grupoOriginal) {
                e.preventDefault(); // Detener el envío del formulario

                // Verificar el límite del grupo
                $.ajax({
                    url: '../persons/checkGroupLimit.php',
                    type: 'POST',
                    data: {
                        id_grupo: grupoId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.limitReached) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Límite alcanzado',
                                text: `El centro "${response.grupoNombre}" ha alcanzado su límite máximo de ${response.limite} personas. Actualmente tiene ${response.personasActuales} personas.`,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            // Si no se alcanzó el límite, enviar el formulario
                            e.target.submit();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al verificar el límite del centro',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            // En caso de error, permitir el envío (la validación del backend se encargará)
                            e.target.submit();
                        });
                    }
                });
            }
        });

        // Mostrar límite del grupo seleccionado y validar capacidad
        $('#grupo').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const limite = selectedOption.data('limite');
            const idGrupo = $(this).val();

            if (limite && idGrupo) {
                // Verificar el límite actual del grupo
                $.ajax({
                    url: '../persons/checkGroupLimit.php',
                    type: 'POST',
                    data: {
                        id_grupo: idGrupo
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Crear elemento de información si no existe
                        if ($('#limite-info').length === 0) {
                            $('#grupo').parent().append('<small id="limite-info" class="text-muted mt-1"></small>');
                        }

                        const color = response.limitReached ? 'text-danger' : 'text-success';
                        $('#limite-info').removeClass('text-muted text-success text-danger').addClass(color);
                        $('#limite-info').text(`Personas en el centro: ${response.personasActuales}/${response.limite}`);

                        if (response.limitReached) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Centro lleno',
                                text: `El centro "${response.grupoNombre}" ha alcanzado su límite máximo de ${response.limite} personas.`,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function() {
                        if ($('#limite-info').length === 0) {
                            $('#grupo').parent().append('<small id="limite-info" class="text-muted"></small>');
                        }
                        $('#limite-info').text('Límite máximo: ' + limite + ' personas');
                    }
                });
            } else {
                $('#limite-info').remove();
            }
        });

        // Validar límite antes de enviar el formulario
        $('form[action="addPersonMovement.php"]').on('submit', function(e) {
            const grupoId = $('#grupo').val();

            if (grupoId) {
                e.preventDefault(); // Detener el envío del formulario

                // Verificar el límite del grupo
                $.ajax({
                    url: '../persons/checkGroupLimit.php',
                    type: 'POST',
                    data: {
                        id_grupo: grupoId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.limitReached) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Límite alcanzado',
                                text: `El centro "${response.grupoNombre}" ha alcanzado su límite máximo de ${response.limite} personas. Actualmente tiene ${response.personasActuales} personas.`,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            // Si no se alcanzó el límite, enviar el formulario
                            e.target.submit();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al verificar el límite del centro',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            // En caso de error, permitir el envío (la validación del backend se encargará)
                            e.target.submit();
                        });
                    }
                });
            }
        });
    });

    // Inicializar DataTables para la tabla de movimientos
    let movementTable;

    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#salesTable')) {
            $('#salesTable').DataTable().destroy();
        }

        movementTable = $('#salesTable').DataTable({
            pageLength: 15,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "Todos"]
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            columnDefs: [{
                    orderable: false,
                    targets: [9]
                }, // Deshabilitar orden en la columna de acciones (ahora es la columna 9)
                {
                    className: "text-center",
                    targets: [0, 9]
                } // Centrar columna de ID y acciones
            ],
            order: [
                [7, 'desc']
            ], // Ordenar por fecha de movimiento (ahora es la columna 7) descendente
            dom: 'frtip', // Solo mostrar filtro, tabla, información y paginación
            searching: false, // Deshabilitar búsqueda de DataTables (usamos filtros propios)
            info: true,
            paging: true,
            responsive: true
        });
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        initDataTable();
    });

    // Manejar eliminación de registros con SweetAlert
    $(document).on('click', '.btn-delete-registro', function() {
        const idRegistro = $(this).data('id');
        const urlParams = new URLSearchParams(window.location.search);
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esta acción",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'deleteRegistro.php',
                    type: 'POST',
                    data: { id_registro: idRegistro },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: response.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Recargar con los mismos filtros
                                window.location.href = 'form.php?' + urlParams.toString();
                            });
                        } else {
                            Swal.fire(
                                'Error',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error',
                            'Hubo un problema al eliminar el registro',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Inicializar sincronización de scroll superior para la tabla
    (function() {
        function setupTopScroller() {
            const topScroller = document.getElementById('tableScrollTop');
            const tableScroller = document.getElementById('tableScroll');
            if (!topScroller || !tableScroller) return;

            // crear inner si no existe
            let inner = topScroller.querySelector('.inner');
            const table = tableScroller.querySelector('.modern-table');
            if (!inner) {
                inner = document.createElement('div');
                inner.className = 'inner';
                topScroller.appendChild(inner);
            }
            function sync() {
                if (!table) return;
                inner.style.width = table.scrollWidth + 'px';
                // Mostrar top scroller solo si hay overflow
                topScroller.style.display = (table.scrollWidth > tableScroller.clientWidth) ? 'block' : 'none';
            }
            topScroller.addEventListener('scroll', function() { tableScroller.scrollLeft = topScroller.scrollLeft; });
            tableScroller.addEventListener('scroll', function() { topScroller.scrollLeft = tableScroller.scrollLeft; });
            window.addEventListener('resize', sync);
            // ejecutar después de un breve timeout para que datatables haya renderizado
            setTimeout(sync, 100);
            // re-ejecutar cuando DataTables dibuje
            $(document).on('draw.dt init.dt column-visibility.dt column-reorder.dt', function() { setTimeout(sync, 50); });
        }
        // ejecutar al cargar
        document.addEventListener('DOMContentLoaded', setupTopScroller);
        // también al finalizar inicialización de jQuery
        $(window).on('load', setupTopScroller);
    })();
</script>

</html>