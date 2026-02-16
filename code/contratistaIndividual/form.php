
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
    <link rel="stylesheet" href="../personMovement/styleSell.css">
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
        /* Aumentar tamaño de fuente general */
        body {
            font-size: 16px !important;
        }

        /* Tabla - aumentar tamaño de fuente */
        .modern-table {
            font-size: 45px !important;
        }

        .modern-table th {
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        .modern-table td {
            font-size: 15px !important;
            padding: 12px 8px !important;
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

// Obtener tipo de usuario para filtrar condiciones
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;

// Filtrar condiciones según tipo de usuario
if ($tipo_usuario == 3) {
    // Para tipo usuario 3 (CONTRATISTA CPSAM): Excluir condiciones que contengan "C.V." o "C.M."
    $condiciones = "SELECT * FROM condiciones_componente WHERE descripcion_condicion NOT LIKE '%C.V.%' AND descripcion_condicion NOT LIKE '%C.M.%'";
} else {
    $condiciones = "SELECT * FROM condiciones_componente";
}
$result_condiciones = mysqli_query($mysqli, $condiciones);
if (!$result_condiciones) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

// Aplicar filtro de grupos según tipo de usuario
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

if (isset($_GET['delete'])) {
    $cedula_persona = $_GET['delete'];
    deleteMember($cedula_persona);
}

function deleteMember($id_movimiento)
{
    global $mysqli; // Asegurar acceso a la conexión global

    $query = "DELETE FROM movimiento_persona WHERE id_movimiento  = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_movimiento);

    if ($stmt->execute()) {
        echo "<script>alert('Movimiento borrado correctamente');
        window.location = 'seePersonMovement.php';</script>";
    } else {
        echo "<script>alert('Error borrando el movimiento');
        window.location = 'seePersonMovement.php';</script>";
    }

    $stmt->close();
}

?>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;"><b><i
                class="bi bi-arrow-left-right"></i> REGISTROS INDIVIDUALES</b></h1>

    <!-- Tabla de Movimientos -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-arrow-left-right"></i> Registros Individuales</h2>
                <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                    <i class="bi bi-plus-circle-fill"></i>
                    Agregar Registro
                </button>
            </div>

            <!-- Filtros modernos -->
            <div class="modern-filters">
                <form action="form.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="cedula_persona">Cédula</label>
                        <input type="number"
                            id="cedula_persona"
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
                        <label for="condicion">Condición</label>
                        <select name="condicion" id="condicion" class="modern-select">
                            <option value="">Todas las condiciones</option>
                            <?php foreach ($result_condiciones as $condicion) {
                                $selected = (isset($_GET['condicion']) && $_GET['condicion'] == $condicion['id_condicion']) ? 'selected' : '';
                            ?>
                                <option value="<?= $condicion['id_condicion']; ?>" <?= $selected ?>>
                                    <?= $condicion['descripcion_condicion']; ?>
                                </option>
                            <?php } ?>
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
                <table class="modern-table" id="salesTable">
                    <thead>
                        <tr>
                            <th class="col-id">Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Condición</th>
                            <th class="col-meta">Meta</th>
                            <th class="col-actividad">Actividad</th>
                            <th class="col-accion">Acción</th>
                            <th>Politica Publica</th>
                            <th>Departamento</th>
                            <th>Centro Vida Traslado</th>
                            <th>Fecha Registro</th>
                            <th>Observación</th>
                            <th>Funcionario</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include "getRegistros.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Add movimiento-->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Hacemos el modal más ancho -->
            <div class="modal-content">
                <form action="addRegistro.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar Movimiento
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Sección: Buscar y Agregar Cédulas -->
                        <div class="buscar-persona-box mb-4" style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 0.375rem;">
                            <h6 class="mb-3 text-primary"><i class="bi bi-person-plus-fill"></i> Buscar y Agregar Personas</h6>
                            
                            <!-- Tabs para alternar entre búsqueda manual y por grupo -->
                            <ul class="nav nav-tabs mb-3" id="busquedaTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-manual-individual" data-bs-toggle="tab" data-bs-target="#busqueda-manual-individual" type="button" role="tab">
                                        <i class="bi bi-search"></i> Búsqueda Manual
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-grupo-individual" data-bs-toggle="tab" data-bs-target="#busqueda-grupo-individual" type="button" role="tab">
                                        <i class="bi bi-people"></i> Selección por Grupo
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="busquedaTabsContent">
                                <!-- Tab 1: Búsqueda Manual por Cédula -->
                                <div class="tab-pane fade show active" id="busqueda-manual-individual" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="buscar_cedula_input_individual" 
                                                       placeholder="Ingrese número de cédula..."
                                                       autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-primary w-100" onclick="buscarYAgregarPersonaIndividual()">
                                                <i class="bi bi-search"></i> Buscar y Agregar
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2: Selección por Grupo -->
                                <div class="tab-pane fade" id="busqueda-grupo-individual" role="tabpanel">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-8">
                                            <select class="form-select" id="filtro_grupo_personas_individual">
                                                <option value="">Seleccione un grupo...</option>
                                                <?php foreach ($result_grupos as $grupo) { 
                                                    $desc = $grupo['descripcion_grupo'];
                                                    // Mostrar solo grupos CPSAM y Contratista
                                                    if (stripos($desc, 'CPSAM') === 0 || stripos($desc, 'Contratista') === 0) {
                                                ?>
                                                    <option value="<?= $grupo['id_grupo']; ?>"><?= $desc; ?></option>
                                                <?php 
                                                    }
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-info w-100" onclick="cargarPersonasGrupoIndividual()">
                                                <i class="bi bi-arrow-clockwise"></i> Cargar Personas
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Área de resultados con checkboxes -->
                                    <div id="area_personas_grupo_individual" style="display:none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">Personas encontradas: <span id="total_personas_grupo_individual" class="badge bg-info">0</span></h6>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-success" onclick="seleccionarTodasPersonasIndividual()">
                                                    <i class="bi bi-check-all"></i> Seleccionar Todas
                                                </button>
                                                <button type="button" class="btn btn-warning" onclick="deseleccionarTodasPersonasIndividual()">
                                                    <i class="bi bi-x"></i> Deseleccionar Todas
                                                </button>
                                                <button type="button" class="btn btn-primary" onclick="agregarPersonasSeleccionadasIndividual()">
                                                    <i class="bi bi-plus-circle"></i> Agregar Seleccionadas
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px;">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th style="width: 50px;">
                                                            <input type="checkbox" class="form-check-input" id="check_all_personas_individual" onclick="toggleTodosCheckboxesIndividual(this)">
                                                        </th>
                                                        <th>Cédula</th>
                                                        <th>Nombres</th>
                                                        <th>Apellidos</th>
                                                        <th>Género</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody_personas_grupo_individual">
                                                    <!-- Se llena dinámicamente -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de Cédulas Agregadas con Validación -->
                            <div id="cedulas_container_individual" style="display:none;">
                                <hr class="my-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">
                                        <i class="bi bi-people-fill"></i> 
                                        Personas Agregadas: 
                                        <span id="contador_cedulas_individual" class="badge bg-secondary">0</span>
                                    </h6>
                                    <div id="validacion_cantidad_individual" style="display:none;"></div>
                                </div>
                                <div id="lista_cedulas_individual" class="list-group mb-3"></div>
                            </div>
                        </div>

                        <!-- Hidden input para enviar las cédulas -->
                        <input type="hidden" name="cedulas_json" id="cedulas_hidden_individual">

                        <hr class="my-4">
                        <h6 class="mb-3"><i class="bi bi-clipboard-data"></i> Información del Registro</h6>

                        <!-- Condición -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating mt-1">
                                <select class="form-select" id="condicion_modal" name="id_condicion" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_condiciones as $condicion) { ?>
                                        <option value="<?= $condicion['id_condicion']; ?>"><?= $condicion['descripcion_condicion']; ?></option>
                                    <?php } ?>
                                </select>
                                <label class="" for="condicion_modal">Condición</label>
                            </div>
                        </div>
                        <!-- Fila 2: Meta, Actividad, Acción -->
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
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="politica-publica" name="politica_publica" required>
                                    <option value="" selected>Seleccione Política Pública...</option>
                                </select>
                                <label for="politica-publica">Política Pública</label>
                            </div>
                        </div>
                        <!-- Fila 3: Departamento de Procedencia y Fecha -->
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
                                <input type="date" class="form-control" id="fecha_movimiento" name="fecha_movimiento" placeholder="Fecha Movimiento">
                                <label for="fecha_movimiento">Fecha Atencion</label>
                            </div>
                        </div>
                        <!-- Fila 4: Centro Vida Traslado (oculto por defecto) -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating mt-1 d-none">
                                <select class="form-select" id="grupo" name="id_centro_vida_traslado" disabled>
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_grupos as $grupo) { ?>
                                        <option value="<?= $grupo['id_grupo']; ?>" data-limite="<?= $grupo['limite_personas']; ?>"><?= $grupo['descripcion_grupo']; ?></option>
                                    <?php } ?>
                                </select>
                                <label class="" for="grupo">Centro Vida Traslado</label>
                            </div>
                        </div>
                        <!-- Fila 5: Observación -->
                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <input type="text" class="form-control" id="observacion_movimiento" name="observacion_movimiento" placeholder="Observacion Movimiento">
                                <label for="observacion_movimiento">Observacion</label>
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
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 shadow-sm">
                <div class="modal-header bg-dark text-white"> <!-- Negro con texto blanco -->
                    <h5 class="modal-title" id="modalEdicionLabel">Edit Store Info</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="editPersonMovement.php" method="POST">
                    <div class="modal-body px-4 py-3">

                        <div class="mb-3">
                            <label for="edit-cedula" class="form-label">Cedula </label>
                            <input type="text" class="form-control" id="edit-cedula" name="cedula_persona" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-nombre" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="edit-nombre" name="nombres_persona" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-apellido" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="edit-apellido" name="apellidos_persona" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-condicion" class="form-label">Condición</label>
                            <select class="form-select" id="edit-condicion" name="id_condicion">
                                <option value="" selected>Seleccione...</option>
                                <?php foreach ($result_condiciones as $condicion) { ?>
                                    <option value="<?= $condicion['id_condicion']; ?>"><?= $condicion['descripcion_condicion']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-meta" class="form-label">Meta</label>
                            <select class="form-select" id="edit-meta" name="id_meta">
                                <option value="" selected>Seleccione Meta...</option>
                                <?php foreach ($result_metas as $meta) { ?>
                                    <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-actividad" class="form-label">Actividad</label>
                            <select class="form-select" id="edit-actividad" name="id_actividad" disabled>
                                <option value="" selected>Seleccione Actividad...</option>
                            </select>
                            <label for="edit-accion" class="form-label">Acción</label>
                            <select class="form-select" id="edit-accion" name="id_accion" disabled>
                                <option value="" selected>Seleccione Acción...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-politica-publica" class="form-label">Política Pública</label>
                            <select class="form-select" id="edit-politica-publica" name="id_politica_publica" required>
                                <option value="" selected>Seleccione Política Pública...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-departamento-procedencia" class="form-label">Departamento de Procedencia</label>
                            <select class="form-select" id="edit-departamento-procedencia" name="departamento_procedencia">
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
                        </div>
                        <div class="mb-3 d-none" id="edit-centro-vida-container">
                            <label for="edit-centro-vida" class="form-label">Centro Vida Traslado</label>
                            <select class="form-select" id="edit-centro-vida" name="id_centro_vida_traslado" disabled>
                                <option value="" selected>Seleccione...</option>
                                <?php foreach ($result_grupos as $grupo) { ?>
                                    <option value="<?= $grupo['id_grupo']; ?>"><?= $grupo['descripcion_grupo']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-fecha_movimiento" class="form-label">fecha_movimiento</label>
                            <input type="date" class="form-control" id="edit-fecha_movimiento" name="fecha_movimiento">
                        </div>
                        <div class="mb-3">
                            <label for="edit-observacion" class="form-label">Observacion</label>
                            <input type="text" class="form-control" id="edit-observacion" name="observacion_movimiento">
                        </div>
                        <input type="hidden" name="cedula_original" id="cedula_original" value="">
                        <input type="hidden" name="id_movimiento_persona" id="id_movimiento_persona" value="">
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

    <br /><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="back" /></a><br>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modalEdicion = document.getElementById("modalEdicion");

        modalEdicion.addEventListener("shown.bs.modal", function(event) {
            const button = event.relatedTarget;
        window.lastEditButton = button; // Guardar referencia global para el JS de política pública
            document.getElementById("edit-cedula").value = button.getAttribute("data-cedula");
            document.getElementById("edit-nombre").value = button.getAttribute("data-nombre");
            document.getElementById("edit-apellido").value = button.getAttribute("data-apellidos");
            document.getElementById("edit-fecha_movimiento").value = button.getAttribute("data-fecha_movimiento");
            document.getElementById("cedula_original").value = button.getAttribute("data-cedula");
            document.getElementById("edit-condicion").value = button.getAttribute("data-condicion");
            document.getElementById("edit-observacion").value = button.getAttribute("data-observacion_movimiento");
            document.getElementById("edit-centro-vida").value = button.getAttribute("data-centro_vida_traslado") || "";
            document.getElementById("id_movimiento_persona").value = button.getAttribute("data-id_movimiento_persona");

            // Prellenar nuevos campos
            document.getElementById("edit-meta").value = button.getAttribute("data-meta") || "";
            document.getElementById("edit-departamento-procedencia").value = button.getAttribute("data-departamento_procedencia") || "";

            // Cargar actividad, acción y política pública si existen
            const idMeta = button.getAttribute("data-meta");
            const idActividad = button.getAttribute("data-actividad");
            const idAccion = button.getAttribute("data-accion");
            const idPolitica = button.getAttribute("data-id_politica_publica");

            if (idMeta) {
                $.ajax({
                    url: '../personMovement/getActividades.php',
                    type: 'POST',
                    data: { id_meta: idMeta },
                    success: function(response) {
                        $('#edit-actividad').empty().append('<option value="">Seleccione Actividad...</option>');
                        $('#edit-actividad').append(response).prop('disabled', false);
                        if (idActividad) {
                            $('#edit-actividad').val(idActividad);
                            $.ajax({
                                url: '../personMovement/getAcciones.php',
                                type: 'POST',
                                data: { id_actividad: idActividad },
                                success: function(response) {
                                    $('#edit-accion').empty().append('<option value="">Seleccione Acción...</option>');
                                    $('#edit-accion').append(response).prop('disabled', false);
                                    if (idAccion) {
                                        $('#edit-accion').val(idAccion);
                                        // Cargar políticas públicas para esta acción
                                        $('#edit-politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                                        $.ajax({
                                            url: '../personMovement/getPoliticaPublica.php',
                                            type: 'POST',
                                            data: { id_accion: idAccion },
                                            dataType: 'json',
                                            success: function(response) {
                                                if (response && response.politicas && response.politicas.length > 0) {
                                                    response.politicas.forEach(function(p) {
                                                        $('#edit-politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                                    });
                                                    // Seleccionar la opción después de agregar todas
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

            // Trigger change en condición para mostrar/ocultar centro de vida
            $('#edit-condicion').trigger('change');
        });
    });
    $(document).ready(function() {
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
                    if (!response) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Respuesta inválida del servidor.' });
                        return;
                    }

                    if (response.encontrado) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Persona encontrada',
                            text: 'Nombre: ' + response.nombres + ' ' + response.apellidos,
                            confirmButtonText: 'OK'
                        });

                        // Si el backend devuelve id_meta, id_actividad, id_accion, id_politica_publica
                        const idMeta = response.id_meta || '';
                        const idActividad = response.id_actividad || '';
                        const idAccion = response.id_accion || '';
                        const idPolitica = response.id_politica_publica || '';
                        const idCondicion = response.id_condicion || '';
                        const departamento = response.departamento_procedencia || '';

                        // Aplicar condicion y departamento inmediatamente si vienen
                        if (idCondicion) {
                            // Setear la condición en el modal (no en el filtro)
                            $('#condicion_modal').val(idCondicion).trigger('change');
                        }
                        if (departamento) {
                            $('#departamento_procedencia').val(departamento);
                        }

                        // Rellenar selects encadenando llamadas
                        if (idMeta) {
                            // Cargar actividades para la meta
                            $.ajax({
                                url: '../personMovement/getActividades.php',
                                type: 'POST',
                                data: { id_meta: idMeta },
                                success: function(htmlActividades) {
                                    $('#meta').val(idMeta);
                                    $('#actividad').empty().append('<option value="">Seleccione Actividad...</option>').append(htmlActividades).prop('disabled', false);

                                    if (idActividad) {
                                        $('#actividad').val(idActividad);

                                        // Cargar acciones para la actividad
                                        $.ajax({
                                            url: '../personMovement/getAcciones.php',
                                            type: 'POST',
                                            data: { id_actividad: idActividad },
                                            success: function(htmlAcciones) {
                                                $('#accion').empty().append('<option value="">Seleccione Acción...</option>').append(htmlAcciones).prop('disabled', false);

                                                if (idAccion) {
                                                    $('#accion').val(idAccion);

                                                    // Cargar políticas públicas para la acción (devuelve JSON)
                                                    $.ajax({
                                                        url: '../personMovement/getPoliticaPublica.php',
                                                        type: 'POST',
                                                        data: { id_accion: idAccion },
                                                        dataType: 'json',
                                                        success: function(respPoliticas) {
                                                            $('#politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                                                            if (respPoliticas && respPoliticas.politicas && respPoliticas.politicas.length > 0) {
                                                                respPoliticas.politicas.forEach(function(p) {
                                                                    $('#politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                                                });
                                                                if (idPolitica) $('#politica-publica').val(idPolitica);
                                                            } else {
                                                                $('#politica-publica').append('<option value="">No asignada</option>');
                                                            }
                                                        },
                                                        error: function() {
                                                            $('#politica-publica').empty().append('<option value="">Error al consultar</option>');
                                                        }
                                                    });
                                                }
                                            },
                                            error: function() {
                                                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar las acciones' });
                                            }
                                        });
                                    } else {
                                        // Si no hay actividad sugerida, limpiar acciones y politica
                                        $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                                        $('#politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                                    }
                                },
                                error: function() {
                                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar las actividades' });
                                }
                            });
                        } else {
                            // No vino meta sugerida: limpiar dependientes
                            $('#meta').val('');
                            $('#actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                            $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                            $('#politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                        }

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Persona no encontrada',
                            text: 'No se encontró ninguna persona con esa cédula.',
                            confirmButtonText: 'OK'
                        });
                        // Limpiar campos dependientes
                        $('#meta').val('');
                        $('#actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                        $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                        $('#politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
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

        // Controlar habilitación del campo grupo según la condición (modal de agregar)
        $('#condicion_modal').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const condicionTexto = selectedOption.text().toUpperCase();

            if (condicionTexto.includes('CPSAM TRASLADADO')) {
                $('#grupo').prop('disabled', false).prop('required', true);
                $('#grupo').parent().removeClass('d-none');
                $('#grupo').parent().find('label').text('Centro Vida Traslado');
            } else {
                $('#grupo').prop('disabled', true).prop('required',false);
                $('#grupo').val(''); // Limpiar selección
                $('#grupo').parent().addClass('d-none');
                $('#limite-info').remove(); // Remover info del límite
            }
        });

        // Inicializar estado del campo grupo (modal)
        $('#condicion_modal').trigger('change');

        // Manejar selección de Meta para cargar Actividades
        $('#meta').on('change', function() {
            const idMeta = $(this).val();

            // Limpiar y deshabilitar campos dependientes
            $('#actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
            $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);

            if (idMeta) {
                $.ajax({
                    url: '../personMovement/getActividades.php',
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
                    url: '../personMovement/getAcciones.php',
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
                    url: '../personMovement/getPoliticaPublica.php',
                    type: 'POST',
                    data: { id_accion: idAccion },
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
                    url: '../personMovement/getActividades.php',
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
                    url: '../personMovement/getAcciones.php',
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
                    url: '../personMovement/getPoliticaPublica.php',
                    type: 'POST',
                    data: { id_accion: idAccion },
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

    // ==================== GESTIÓN DE MÚLTIPLES CÉDULAS ====================
    $(document).ready(function() {
        let cedulasAgregadasIndividual = [];

        // Función para buscar y agregar persona al registro individual
        window.buscarYAgregarPersonaIndividual = function() {
            const cedula = $('#buscar_cedula_input_individual').val().trim();

            if (!cedula) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cédula vacía',
                    text: 'Por favor ingrese un número de cédula'
                });
                return;
            }

            // Verificar si ya está agregada
            if (cedulasAgregadasIndividual.some(item => item.cedula === cedula)) {
                Swal.fire({
                    icon: 'info',
                    title: 'Persona ya agregada',
                    text: 'Esta cédula ya está en la lista'
                });
                return;
            }

            // Buscar en la base de datos
            $.ajax({
                url: '../buscar_persona.php',
                type: 'POST',
                data: { cedula: cedula },
                dataType: 'json',
                success: function(response) {
                    if (!response || !response.encontrado) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Persona no encontrada',
                            text: 'La cédula no está registrada o no tiene acceso a grupos CPSAM/Contratista'
                        });
                    } else {
                        // Agregar a la lista
                        cedulasAgregadasIndividual.push({
                            cedula: cedula,
                            nombre_completo: response.nombres + ' ' + response.apellidos,
                            genero: response.genero || 'N/A'
                        });

                        actualizarListaCedulasIndividual();
                        $('#buscar_cedula_input_individual').val('').focus();

                        Swal.fire({
                            icon: 'success',
                            title: 'Persona agregada',
                            text: response.nombres + ' ' + response.apellidos,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al buscar la persona'
                    });
                }
            });
        };

        // Actualizar lista visual de cédulas con validación
        function actualizarListaCedulasIndividual() {
            const lista = $('#lista_cedulas_individual');
            const container = $('#cedulas_container_individual');
            const totalAgregado = cedulasAgregadasIndividual.length;
            
            if (totalAgregado === 0) {
                container.hide();
                lista.html('');
                $('#contador_cedulas_individual').text('0').removeClass('bg-success bg-warning bg-danger bg-info').addClass('bg-secondary');
                $('#cedulas_hidden_individual').val('');
                $('#validacion_cantidad_individual').hide();
                return;
            }

            container.show();
            
            // Actualizar contador con colores
            const contadorBadge = $('#contador_cedulas_individual');
            contadorBadge.text(totalAgregado);
            contadorBadge.removeClass('bg-success bg-warning bg-danger bg-secondary').addClass('bg-info');
            
            // Mostrar simple confirmación de que hay personas
            const validacionDiv = $('#validacion_cantidad_individual');
            validacionDiv.show().html(`<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> ${totalAgregado} persona(s)</span>`);

            let html = '';
            cedulasAgregadasIndividual.forEach((item, index) => {
                const iconoGenero = item.genero === 'Masculino' ? 'bi-gender-male text-primary' : 
                                    item.genero === 'Femenino' ? 'bi-gender-female text-danger' : 
                                    'bi-person text-secondary';
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi ${iconoGenero}"></i>
                            <strong>${item.cedula}</strong> - ${item.nombre_completo}
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removerCedulaIndividual(${index})">
                            <i class="bi bi-trash"></i> Quitar
                        </button>
                    </div>
                `;
            });

            lista.html(html);

            // Actualizar hidden input con las cédulas en formato JSON
            $('#cedulas_hidden_individual').val(JSON.stringify(cedulasAgregadasIndividual.map(item => item.cedula)));
        }

        // Función para remover una cédula de la lista
        window.removerCedulaIndividual = function(index) {
            cedulasAgregadasIndividual.splice(index, 1);
            actualizarListaCedulasIndividual();
        };

        // ==================== FUNCIONES PARA SELECCIÓN POR GRUPO ====================

        // Cargar personas de un grupo seleccionado
        window.cargarPersonasGrupoIndividual = function() {
            const idGrupo = $('#filtro_grupo_personas_individual').val();
            
            if (!idGrupo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Grupo no seleccionado',
                    text: 'Por favor seleccione un grupo primero'
                });
                return;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Cargando personas...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'obtenerPersonasPorGrupo.php',
                type: 'POST',
                data: { id_grupo: idGrupo },
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    
                    if (response.success && response.personas) {
                        mostrarPersonasEnTablaIndividual(response.personas);
                        $('#total_personas_grupo_individual').text(response.total);
                        $('#area_personas_grupo_individual').show();
                        
                        if (response.total === 0) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Sin resultados',
                                text: 'No se encontraron personas activas en este grupo'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudieron cargar las personas'
                        });
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                }
            });
        };

        // Mostrar personas en la tabla con checkboxes
        function mostrarPersonasEnTablaIndividual(personas) {
            const tbody = $('#tbody_personas_grupo_individual');
            tbody.empty();
            $('#check_all_personas_individual').prop('checked', false);

            if (personas.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center text-muted">No hay personas disponibles</td></tr>');
                return;
            }

            personas.forEach((persona, index) => {
                const iconoGenero = persona.genero === 'Masculino' ? 'bi-gender-male text-primary' : 
                                   persona.genero === 'Femenino' ? 'bi-gender-female text-danger' : 
                                   'bi-person text-secondary';
                
                const row = `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" 
                                   class="form-check-input persona-checkbox-individual" 
                                   data-cedula="${persona.cedula}"
                                   data-nombres="${persona.nombres}"
                                   data-apellidos="${persona.apellidos}"
                                   data-genero="${persona.genero}">
                        </td>
                        <td><strong>${persona.cedula}</strong></td>
                        <td>${persona.nombres}</td>
                        <td>${persona.apellidos}</td>
                        <td><i class="bi ${iconoGenero}"></i> ${persona.genero}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Toggle todos los checkboxes con el checkbox principal
        window.toggleTodosCheckboxesIndividual = function(checkbox) {
            $('.persona-checkbox-individual').prop('checked', checkbox.checked);
        };

        // Botón para seleccionar todas
        window.seleccionarTodasPersonasIndividual = function() {
            $('.persona-checkbox-individual').prop('checked', true);
            $('#check_all_personas_individual').prop('checked', true);
        };

        // Botón para deseleccionar todas
        window.deseleccionarTodasPersonasIndividual = function() {
            $('.persona-checkbox-individual').prop('checked', false);
            $('#check_all_personas_individual').prop('checked', false);
        };

        // Agregar personas seleccionadas a la lista final
        window.agregarPersonasSeleccionadasIndividual = function() {
            const checkboxesSeleccionados = $('.persona-checkbox-individual:checked');
            
            if (checkboxesSeleccionados.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin selección',
                    text: 'Por favor seleccione al menos una persona'
                });
                return;
            }

            let personasAgregadas = 0;
            let personasDuplicadas = 0;

            checkboxesSeleccionados.each(function() {
                const cedula = $(this).data('cedula');
                const nombres = $(this).data('nombres');
                const apellidos = $(this).data('apellidos');
                const genero = $(this).data('genero');

                // Verificar si ya está agregada
                if (cedulasAgregadasIndividual.some(item => item.cedula === cedula)) {
                    personasDuplicadas++;
                    return; // continue al siguiente
                }

                // Agregar a la lista
                cedulasAgregadasIndividual.push({
                    cedula: cedula,
                    nombre_completo: nombres + ' ' + apellidos,
                    genero: genero
                });
                personasAgregadas++;
            });

            actualizarListaCedulasIndividual();

            // Deseleccionar checkboxes después de agregar
            deseleccionarTodasPersonasIndividual();

            // Mostrar resultado
            let mensaje = `${personasAgregadas} persona(s) agregada(s) correctamente`;
            if (personasDuplicadas > 0) {
                mensaje += `<br><small>${personasDuplicadas} persona(s) ya estaban en la lista</small>`;
            }

            // Debug: Mostrar estado del array
            console.log('Personas agregadas al array (Individual):', cedulasAgregadasIndividual);
            console.log('Total en array:', cedulasAgregadasIndividual.length);
            console.log('Hidden input value:', $('#cedulas_hidden_individual').val());

            Swal.fire({
                icon: 'success',
                title: 'Personas agregadas',
                html: mensaje,
                timer: 2000,
                showConfirmButton: false
            });
        };

        // ==================== FIN FUNCIONES SELECCIÓN POR GRUPO ====================

        // Permitir agregar con Enter
        $('#buscar_cedula_input_individual').keypress(function(e) {
            if (e.which == 13) {
                e.preventDefault();
                buscarYAgregarPersonaIndividual();
            }
        });

        // Validar formulario antes de enviar - debe haber al menos una cédula
        $('#modalNewPerson form').submit(function(e) {
            // Debug: Mostrar estado al enviar
            console.log('=== VALIDACIÓN DE ENVÍO (Individual) ===');
            console.log('Array cedulasAgregadasIndividual:', cedulasAgregadasIndividual);
            console.log('Cantidad en array:', cedulasAgregadasIndividual.length);
            console.log('Hidden input value:', $('#cedulas_hidden_individual').val());
            
            if (cedulasAgregadasIndividual.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin personas agregadas',
                    text: 'Debe agregar al menos una persona para crear el registro',
                    confirmButtonText: 'Entendido'
                });
                return false;
            }
            
            console.log('✓ Validación pasada, formulario se enviará');
        });

        // Limpiar lista al cerrar el modal
        $('#modalNewPerson').on('hidden.bs.modal', function () {
            cedulasAgregadasIndividual = [];
            actualizarListaCedulasIndividual();
            $('#buscar_cedula_input_individual').val('');
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
</script>

</html>