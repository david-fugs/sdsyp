<?php
session_start();
require_once('../filtros_grupos.php');
include("../../conexion.php");

$programas = "SELECT * FROM programas ";
$result_programas = mysqli_query($mysqli, $programas);
if (!$result_programas) {
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
$result_grupos = mysqli_query($mysqli, $grupos);
if (!$result_grupos) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}
$politicas_publicas = "SELECT * FROM politicas_publicas ";
$result_politicas_publicas = mysqli_query($mysqli, $politicas_publicas);
if (!$result_politicas_publicas) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

$metas = "SELECT * FROM metas ORDER BY descripcion_meta ASC";
$result_metas = mysqli_query($mysqli, $metas);
if (!$result_metas) {
    die("Error en la consulta de metas: " . mysqli_error($mysqli));
}

// Obtener tipo_usuario e id_grupo de la sesión
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// Obtener el prefijo del grupo del usuario si es tipo 2
$grupo_prefix = '';
if ($tipo_usuario == 2 && $id_grupo_session && $id_grupo_session != 0) {
    $query_grupo_prefix = "SELECT descripcion_grupo FROM grupos WHERE id_grupo = ?";
    $stmt_prefix = $mysqli->prepare($query_grupo_prefix);
    $stmt_prefix->bind_param("i", $id_grupo_session);
    $stmt_prefix->execute();
    $result_prefix = $stmt_prefix->get_result();
    if ($row_prefix = $result_prefix->fetch_assoc()) {
        $descripcion = $row_prefix['descripcion_grupo'];
        // Extraer el prefijo (CV, CPSAM, etc.)
        if (stripos($descripcion, 'CV') === 0) {
            $grupo_prefix = 'CV';
        } elseif (stripos($descripcion, 'CPSAM') === 0) {
            $grupo_prefix = 'CPSAM';
        } elseif (stripos($descripcion, 'contratista') === 0) {
            $grupo_prefix = 'contratista';
        } elseif (stripos($descripcion, 'otros') === 0) {
            $grupo_prefix = 'otros';
        } elseif (stripos($descripcion, 'colombia mayor') === 0) {
            $grupo_prefix = 'colombia mayor';
        }
    }
    $stmt_prefix->close();
}

// Filtrar grupos para el select según tipo_usuario
$grupos_filtrados = [];
if ($tipo_usuario == 3) {
    // Para tipo_usuario 3 (CONTRATISTA CPSAM): mostrar todos los CPSAM y su grupo asignado
    $query_grupos_tipo3 = "SELECT * FROM grupos WHERE descripcion_grupo LIKE 'CPSAM%'";
    if ($id_grupo_session) {
        $query_grupos_tipo3 .= " OR id_grupo = '$id_grupo_session'";
    }
    $query_grupos_tipo3 .= " ORDER BY descripcion_grupo ASC";
    $result_grupos_tipo3 = mysqli_query($mysqli, $query_grupos_tipo3);
    if ($result_grupos_tipo3) {
        while ($grupo = mysqli_fetch_assoc($result_grupos_tipo3)) {
            $grupos_filtrados[] = $grupo;
        }
    }
} elseif ($tipo_usuario == 2 && !empty($grupo_prefix)) {
    // Para tipo_usuario 2 con grupo asignado: mostrar solo grupos con el mismo prefijo
    foreach ($result_grupos as $grupo) {
        if (stripos($grupo['descripcion_grupo'], $grupo_prefix) === 0) {
            $grupos_filtrados[] = $grupo;
        }
    }
} else {
    // Para otros usuarios, mostrar todos los grupos filtrados
    foreach ($result_grupos as $grupo) {
        $grupos_filtrados[] = $grupo;
    }
}

if (isset($_GET['delete'])) {
    $cedula_persona = $_GET['delete'];
    deleteMember($cedula_persona);
}

function deleteMember($cedula_persona)
{
    global $mysqli; // Asegurar acceso a la conexión global

    $query = "DELETE FROM personas WHERE cedula_persona  = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $cedula_persona);

    if ($stmt->execute()) {
        echo "<script>alert('Persona borrada corecctamente');
        window.location = 'seePerson.php';</script>";
    } else {
        echo "<script>alert('Error borrando la persona');
        window.location = 'seePerson.php';</script>";
    }

    $stmt->close();
}

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- jQuery (necesario para DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        /* Columnas específicas más anchas para Meta, Actividad, Acción y Política Pública */
        .modern-table .col-meta,
        .modern-table .col-actividad,
        .modern-table .col-accion,
        .modern-table .col-politica {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .modern-table td.col-meta,
        .modern-table td.col-actividad,
        .modern-table td.col-accion,
        .modern-table td.col-politica {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            white-space: normal;
        }

        /* Botón Cancelar del modal edición: negrita y color oscuro siempre */
        .btn-modal-cancelar {
            font-weight: bold !important;
            color: #212529 !important;
            border: 1px solid #ced4da !important;
            background: #fff !important;
        }

        .btn-modal-cancelar:hover,
        .btn-modal-cancelar:focus {
            background: #f8f9fa !important;
            color: #212529 !important;
            border-color: #adb5bd !important;
        }
    </style>
</head>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;"><b><i
                class="bi bi-people-fill"></i> PERSONAS</b></h1>

    <!-- Tabla de Personas -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-people-fill"></i> Personas Registradas</h2>
                <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                    <i class="bi bi-person-plus-fill"></i>
                    Agregar Persona
                </button>
            </div>

            <!-- Filtros modernos -->
            <div class="modern-filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="filter-cedula">Cédula</label>
                        <input type="text" id="filter-cedula" class="modern-input" placeholder="Buscar por cédula...">
                    </div>
                    <div class="filter-group">
                        <label for="filter-nombre">Nombre</label>
                        <input type="text" id="filter-nombre" class="modern-input" placeholder="Buscar por nombre...">
                    </div>
                    <div class="filter-group">
                        <label for="filter-programa">Programa</label>
                        <select id="filter-programa" class="modern-select">
                            <option value="">Todos los programas</option>
                            <?php
                            mysqli_data_seek($result_programas, 0);
                            while ($programa = mysqli_fetch_assoc($result_programas)) { ?>
                                <option value="<?= $programa['id_programa']; ?>"><?= $programa['nombre_programa']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-estado">Estado</label>
                        <select id="filter-estado" class="modern-select">
                            <option value="">Todos los estados</option>
                            <option value="ACTIVO">Activo</option>
                            <option value="EVADIDO">Evadido</option>
                            <option value="FALLECIDO">Fallecido</option>
                            <option value="RETIRADO_VOLUNTARIO">Retirado Voluntario</option>
                            <option value="TRASLADADO">Trasladado</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-creado-por">Creado por</label>
                        <input type="text" id="filter-creado-por" class="modern-input" placeholder="Buscar por creador...">
                    </div>
                    <div class="filter-group">
                        <button type="button" id="btn-filter" class="btn-modern btn-primary">
                            <i class="bi bi-search"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="modern-table-wrapper" style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(65,47,209,0.08); padding: 24px;">
                <table class="modern-table table table-hover align-middle" id="salesTable" style="border-radius: 12px; overflow: hidden;">
                    <thead class="table-dark">
                        <tr style="font-size: 1.1rem;">
                            <th class="col-id">Cédula</th>
                            <th>Nombre Completo</th>
                            <th>Género</th>
                            <th>Edad</th>
                            <th>Programas</th>
                            <th>Centro Vida / CPSAM / Otro</th>
                            <th>Creado por</th>
                            <th class="col-status">Estado</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php include "getPersons.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Add Person -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Hacemos el modal más ancho -->
            <div class="modal-content">
                <form action="addPerson.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar Persona
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila 1: Tipo de identificación y número -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="tipo_identificacion" name="tipo_identificacion">
                                    <option value="" selected disabled>Seleccione tipo...</option>
                                    <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                                    <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                                    <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                    <option value="Sin identificacion">Sin identificacion</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="tipo_identificacion">Tipo de Identificación</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="cedula_persona" name="cedula_persona" placeholder="Número de Identificación" autocomplete="off" autofocus required>
                                <label for="cedula_persona">Número de Identificación</label>
                            </div>
                        </div>
                        <!-- Fila 1.5: Género y Nombres -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="genero_persona" name="genero_persona">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="genero_persona">Género</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="nombres_persona" name="nombres_persona" placeholder="Nombres" required>
                                <label for="nombres_persona">Nombres</label>
                            </div>
                        </div>
                        <!-- Fila 2: Apellidos y Referencia -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="apellidos_persona" name="apellidos_persona" placeholder="Apellidos">
                                <label for="apellidos_persona">Apellidos</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="telefono_persona" name="telefono_persona" placeholder="Teléfono">
                                <label for="telefono_persona">Teléfono</label>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="telefono_referencia_persona" name="telefono_referencia_persona" placeholder="Teléfono">
                                <label for="telefono_referencia_persona">Teléfono Referencia</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="referencia_persona" name="referencia_persona" placeholder="Referencia">
                                <label for="referencia_persona">Referencia</label>
                            </div>
                        </div>
                        <div class="row">
                        </div>
                        <!-- Fila 3: Teléfono y Fecha de Nacimiento -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="correo_persona" name="correo_persona" placeholder="Correo">
                                <label for="correo_persona">Correo</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="Fecha de Nacimiento">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            </div>
                        </div>
                        <!-- Fila nueva: Barrio, Comuna y Zona -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating position-relative">
                                <input type="text" class="form-control" id="barrio_persona" name="barrio_persona" placeholder="Barrio" list="lista-barrios" autocomplete="off">
                                <label for="barrio_persona">Barrio</label>
                                <datalist id="lista-barrios"></datalist>
                                <input type="hidden" id="id_barrio_persona" name="id_barrio_persona">
                                <input type="hidden" id="id_comuna_persona" name="id_comuna_persona">
                            </div>
                            <div class="col-md-3 mb-3 form-floating">
                                <input type="text" class="form-control" id="comuna_persona" name="comuna_persona" placeholder="Comuna" readonly>
                                <label for="comuna_persona">Comuna</label>
                            </div>
                            <div class="col-md-3 mb-3 form-floating">
                                <input type="text" class="form-control" id="zona_persona" name="zona_persona" placeholder="Zona" readonly>
                                <label for="zona_persona">Zona</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <input type="text" class="form-control" id="direccion_persona" name="direccion_persona" placeholder="Direccion">
                                <label for="direccion_persona">Direccion</label>
                            </div>
                        </div>


                        <!-- Fila 4: Edad y Grupo Sisbén -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="edad_calculada" readonly placeholder="Se calculará automáticamente" style="background-color: #f8f9fa;">
                                <label for="edad_calculada">Edad</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="grupo_sisben" name="grupo_sisben">
                                    <option value="" selected>Seleccione grupo...</option>
                                    <?php
                                    $grupos = [
                                        'A' => 5,
                                        'B' => 7,
                                        'C' => 18,
                                        'D' => 21
                                    ];
                                    foreach ($grupos as $letra => $max) {
                                        for ($n = 1; $n <= $max; $n++) {
                                            echo "<option value=\"{$letra}{$n}\">{$letra}{$n}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <label for="grupo_sisben">Grupo Sisbén</label>
                            </div>
                        </div>
                        <!-- Fila: EPS, Peso, Talla -->
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="text" class="form-control" id="eps" name="eps" placeholder="EPS">
                                <label for="eps">EPS</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="number" step="0.01" class="form-control" id="peso" name="peso" placeholder="Peso (kg)">
                                <label for="peso">Peso (kg)</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="number" step="0.01" class="form-control" id="talla" name="talla" placeholder="Talla (cm)">
                                <label for="talla">Talla (cm)</label>
                            </div>
                        </div>
                        <!-- Fila: Patologías, Factores de Riesgo -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="patologias" name="patologias">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Osteomuscular</option>
                                    <option>Respiratoria</option>
                                    <option>Diabetes</option>
                                    <option>EPOC</option>
                                    <option>Trastorno afectivo bipolar</option>
                                    <option>Física</option>
                                    <option>Cataratas senil nuclear</option>
                                    <option>Hipertensión arterial</option>
                                    <option>HTA</option>
                                    <option>Hipertensión senecial</option>
                                    <option>Hipotiroidismo</option>
                                    <option>ICC</option>
                                    <option>Mental</option>
                                    <option>Ninguna</option>
                                    <option>No aplica</option>
                                    <option>Osteoastromuscular</option>
                                    <option>Otras</option>
                                </select>
                                <label for="patologias">Patologías</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="factores_riesgo" name="factores_riesgo">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Alcohol</option>
                                    <option>Tabaco</option>
                                    <option>Tabaco y alcohol</option>
                                    <option>Otros</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="factores_riesgo">Factores de Riesgo</label>
                            </div>
                        </div>
                        <!-- Fila: Factores Preventivos, Ingresos Económicos -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="factores_preventivos" name="factores_preventivos">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Ejercicio</option>
                                    <option>Dieta</option>
                                    <option>Ejercicio y dieta</option>
                                    <option>Otra</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="factores_preventivos">Factores Preventivos</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="ingresos_economicos" name="ingresos_economicos">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Pensión</option>
                                    <option>Estado</option>
                                    <option>Conyuge</option>
                                    <option>Mendicidad</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="ingresos_economicos">Ingresos Económicos</label>
                            </div>
                        </div>
                        <!-- Fila: Convivencia Actual, Resultado Actividad, Remisión -->
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="convivencia_actual" name="convivencia_actual">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Pareja</option>
                                    <option>Familia</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="convivencia_actual">Convivencia Actual</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="resultado_actividad" name="resultado_actividad">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Con deterioro leve</option>
                                    <option>Con deterioro moderado</option>
                                    <option>Con deterioro severo</option>
                                    <option>Sin deterioro</option>
                                    <option>Delgadez</option>
                                    <option>Normal</option>
                                    <option>Obesidad grado 1</option>
                                    <option>Obesidad grado II</option>
                                    <option>Obesidad grado III</option>
                                    <option>Sobrepeso</option>
                                    <option>Depresión</option>
                                    <option>En riesgo</option>
                                    <option>Saludable</option>
                                    <option>Demencia</option>
                                    <option>Demencia leve</option>
                                    <option>Deterioro cognitivo leve</option>
                                    <option>Extrema</option>
                                    <option>Leve</option>
                                    <option>Moderada</option>
                                    <option>Ninguna disfuncionalidad</option>
                                    <option>Severa</option>
                                    <option>Disfuncionalidad extrema</option>
                                    <option>Disfuncionalidad leve</option>
                                    <option>Disfuncionalidad moderada</option>
                                    <option>Disfuncionalidad severa</option>
                                    <option>Ninguna</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="resultado_actividad">Resultado según actividad</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="remision" name="remision">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Actividad física</option>
                                    <option>Enfermería</option>
                                    <option>Gerontología</option>
                                    <option>Neuropsicología</option>
                                    <option>Psicología EPS</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="remision">Remisión</label>
                            </div>
                        </div>
                        <!-- Fila 1.7: Discapacidad y cabeza de hogar -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="persona_discapacidad" name="persona_discapacidad">
                                    <option value="" selected disabled>¿Persona con discapacidad?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="persona_discapacidad">¿Persona con discapacidad?</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating" id="div_cual_discapacidad" style="display:none;">
                                <select class="form-select" id="cual_discapacidad" name="cual_discapacidad">
                                    <option value="" selected disabled>Categoría discapacidad...</option>
                                    <option value="Auditiva">Auditiva</option>
                                    <option value="Física">Física</option>
                                    <option value="Intelectual">Intelectual</option>
                                    <option value="Múltiple">Múltiple</option>
                                    <option value="Psicosocial">Psicosocial</option>
                                    <option value="Sordoceguera">Sordoceguera</option>
                                    <option value="Visual">Visual</option>
                                </select>
                                <label for="cual_discapacidad">Categoría discapacidad</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="cabeza_hogar" name="cabeza_hogar">
                                    <option value="" selected disabled>¿Cabeza de hogar?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="cabeza_hogar">¿Cabeza de hogar?</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="lider_comunidad" name="lider_comunidad">
                                    <option value="" selected disabled>¿Líder/representante comunidad?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="lider_comunidad">¿Líder/representante comunidad?</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="se_reconoce_como" name="se_reconoce_como">
                                    <option value="" selected disabled>Se reconoce como...</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Hombre trans">Hombre trans</option>
                                    <option value="Mujer">Mujer</option>
                                    <option value="Mujer trans">Mujer trans</option>
                                    <option value="No binaria">No binaria</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="se_reconoce_como">Se reconoce como</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="orientacion_sexual" name="orientacion_sexual">
                                    <option value="" selected disabled>Orientación sexual...</option>
                                    <option value="Heterosexual">Heterosexual</option>
                                    <option value="Homosexual">Homosexual</option>
                                    <option value="Asexual">Asexual</option>
                                    <option value="Bisexual">Bisexual</option>
                                    <option value="Pansexual">Pansexual</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="orientacion_sexual">Orientación sexual</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="experiencia_migratoria" name="experiencia_migratoria">
                                    <option value="" selected disabled>¿Experiencia migratoria?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="experiencia_migratoria">¿Experiencia migratoria?</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="grupo_etnico" name="grupo_etnico">
                                    <option value="" selected disabled>Grupo étnico...</option>
                                    <option value="Indígena">Indígena</option>
                                    <option value="Rom">Rom</option>
                                    <option value="Raizal">Raizal</option>
                                    <option value="Palenquero de San Basilio">Palenquero de San Basilio</option>
                                    <option value="Negro/Mulato">Negro/Mulato</option>
                                    <option value="Mestizo">Mestizo</option>
                                </select>
                                <label for="grupo_etnico">Grupo étnico</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="tipo_salud" name="tipo_salud">
                                    <option value="" selected disabled>Tipo de salud...</option>
                                    <option value="Régimen subsidiado">Régimen subsidiado</option>
                                    <option value="Régimen contributivo">Régimen contributivo</option>
                                    <option value="Régimen vinculado">Régimen vinculado</option>
                                </select>
                                <label for="tipo_salud">Tipo de salud</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="nivel_educativo" name="nivel_educativo">
                                    <option value="" selected disabled>Nivel educativo...</option>
                                    <option value="Preescolar">Preescolar</option>
                                    <option value="Básica primaria">Básica primaria</option>
                                    <option value="Básica secundaria">Básica secundaria</option>
                                    <option value="Media académica o clásica">Media académica o clásica</option>
                                    <option value="Media técnica">Media técnica</option>
                                    <option value="Normalista">Normalista</option>
                                    <option value="Técnica profesional">Técnica profesional</option>
                                    <option value="Tecnológica">Tecnológica</option>
                                    <option value="Profesional">Profesional</option>
                                </select>
                                <label for="nivel_educativo">Nivel educativo</label>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="condicion_ocupacion" name="condicion_ocupacion">
                                    <option value="" selected disabled>¿Condicion Ocupacion?</option>
                                    <option value="Ama de Casa">Ama de Casa</option>
                                    <option value="Estudiante">Estudiante</option>
                                    <option value="Empleado">Empleado</option>
                                    <option value="Desempleado">Desempleado</option>
                                    <option value="Independiente">Independiente</option>
                                    <option value="Pensionado">Pensionado</option>
                                    <option value="Buscando Empleo">Buscando Empleo</option>
                                    <option value="Ninguno">Ninguno</option>
                                </select>
                                <label for="condicion_ocupacion">¿Condicion Ocupacion?</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating" id="condicion_componente">
                                <select class="form-select" id="condicion_componente" name="condicion_componente">
                                    <option value="" selected disabled>Condicion Componente...</option>
                                    <option value="C.V Beneficiario Activo">C.V Beneficiario Activo</option>
                                    <option value="C.V Beneficiario Inactivo">C.V Beneficiario Inactivo</option>
                                    <option value="CPSAM Activo">CPSAM Activo</option>
                                    <option value="CPSAM Evadido">CPSAM Evadido</option>
                                    <option value="Trasladado">Trasladado</option>
                                    <option value="Usuario indirecto">Usuario indirecto</option>
                                    <option value="CPSAM Remitido">CPSAM Remitido</option>
                                    <option value="Usuario interesado">Usuario interesado</option>
                                    <option value="CPSAM Fallecido">CPSAM Fallecido</option>
                                    <option value="CPSAM Retiro Voluntario">CPSAM Retiro Voluntario</option>
                                    <option value="CPSAM Trasladado">CPSAM Trasladado</option>
                                    <option value="C.M Retiro Definitivo">C.M Retiro Definitivo</option>
                                    <option value="C.M Activo">C.M Activo</option>
                                    <option value="C.M BDUA">C.M BDUA</option>
                                    <option value="C.M Bloqueo Registraduria">C.M Bloqueo Registraduria</option>
                                    <option value="C.M Duplicidad Documento">C.M Duplicidad Documento</option>
                                    <option value="C.M Ejercicio Mendicidad Comprobada">C.M Ejercicio Mendicidad Comprobada</option>
                                    <option value="C.M En lista de Espera">C.M En lista de Espera</option>
                                    <option value="C.M Fallecido">C.M Fallecido</option>
                                    <option value="C.M Fallecido sin Certificado">C.M Fallecido sin Certificado</option>
                                    <option value="C.M Familias en Accion">C.M Familias en Accion</option>
                                    <option value="C.M Fuera de la Ciudad">C.M Fuera de la Ciudad</option>
                                    <option value="Visita psicosocial fallida">Visita psicosocial fallida</option>
                                </select>
                                <label for="condicion_componente">Condicion Componente</label>
                            </div>
                        </div>

                        <!-- Fila 4 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Programas</label>
                                <?php foreach ($result_programas as $programa) { ?>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="programa[]"
                                            id="programa_<?= $programa['id_programa']; ?>"
                                            value="<?= $programa['id_programa']; ?>">
                                        <label class="form-check-label" for="programa_<?= $programa['id_programa']; ?>">
                                            <?= $programa['nombre_programa']; ?>
                                        </label>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-6 mb-3 form-floating mt-1">
                                <select class="form-select" id="id_grupo" name="id_grupo">
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($grupos_filtrados as $grupo) { ?>
                                        <option value="<?= $grupo['id_grupo']; ?>"><?= $grupo['descripcion_grupo']; ?></option>
                                    <?php } ?>
                                </select>
                                <label class="" for="id_grupo">Centro Vida / CPSAM</label>
                            </div>

                        </div>
                        <!-- fila 5 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="date" class="form-control" id="activo_desde" name="activo_desde" placeholder="Activo Desde">
                                <label for="activo_desde">Activo Desde</label>
                            </div>
                        </div>

                        <!-- Fila: Meta, Actividad, Acción y Política Pública -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="meta" name="id_meta">
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php foreach ($result_metas as $meta) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="meta">Meta</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="actividad" name="id_actividad" disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                                <label for="actividad">Actividad</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="accion" name="id_accion" disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                                <label for="accion">Acción</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="politica-publica" name="id_politica_publica">
                                    <option value="" selected>Seleccione Política Pública...</option>
                                </select>
                                <label for="politica-publica">Política Pública</label>
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


    <!-- modal edicion -->
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 shadow-sm">
                <div class="modal-header bg-dark text-white"> <!-- Negro con texto blanco -->
                    <h5 class="modal-title" id="modalEdicionLabel">Editar Persona</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="editPersona.php" method="POST">
                    <div class="modal-body px-4 py-3">
                        <!-- Fila 1: Tipo de identificación y número -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-tipo-identificacion" class="form-label">Tipo de Identificación</label>
                                <select class="form-select" id="edit-tipo-identificacion" name="tipo_identificacion" required>
                                    <option value="" selected disabled>Seleccione tipo...</option>
                                    <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                                    <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                                    <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                    <option value="Sin identificacion">Sin identificacion</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-cedula" class="form-label">Número de Identificación</label>
                                <input type="text" class="form-control" id="edit-cedula" name="cedula_persona">
                            </div>
                        </div>
                        <!-- Fila 1.2: Nombres y Apellidos -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-nombre" class="form-label">Nombres</label>
                                <input type="text" class="form-control" id="edit-nombre" name="nombres_persona">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-apellido" class="form-label">Apellidos</label>
                                <input type="text" class="form-control" id="edit-apellido" name="apellidos_persona">
                            </div>
                        </div>
                        <!-- Fila 1.5: Género y Grupo Sisbén -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-genero" class="form-label">Género</label>
                                <select class="form-select" id="edit-genero" name="genero_persona" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-grupo-sisben" class="form-label">Grupo Sisbén</label>
                                <select class="form-select" id="edit-grupo-sisben" name="grupo_sisben">
                                    <option value="" selected>Seleccione grupo...</option>
                                    <?php
                                    $grupos = [
                                        'A' => 5,
                                        'B' => 7,
                                        'C' => 18,
                                        'D' => 21
                                    ];
                                    foreach ($grupos as $letra => $max) {
                                        for ($n = 1; $n <= $max; $n++) {
                                            echo "<option value=\"{$letra}{$n}\">{$letra}{$n}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!-- Fila: EPS, Peso, Talla (Edición) -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit-eps" class="form-label">EPS</label>
                                <input type="text" class="form-control" id="edit-eps" name="eps">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-peso" class="form-label">Peso (kg)</label>
                                <input type="number" step="0.01" class="form-control" id="edit-peso" name="peso">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-talla" class="form-label">Talla (cm)</label>
                                <input type="number" step="0.01" class="form-control" id="edit-talla" name="talla">
                                <label for="talla">Talla (cm)</label>
                            </div>
                        </div>
                        <!-- Fila: Patologías, Factores de Riesgo (Edición) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-patologias" class="form-label">Patologías</label>
                                <select class="form-select" id="edit-patologias" name="patologias">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Osteomuscular</option>
                                    <option>Respiratoria</option>
                                    <option>Diabetes</option>
                                    <option>EPOC</option>
                                    <option>Trastorno afectivo bipolar</option>
                                    <option>Física</option>
                                    <option>Cataratas senil nuclear</option>
                                    <option>Hipertensión arterial</option>
                                    <option>HTA</option>
                                    <option>Hipertensión senecial</option>
                                    <option>Hipotiroidismo</option>
                                    <option>ICC</option>
                                    <option>Mental</option>
                                    <option>Ninguna</option>
                                    <option>No aplica</option>
                                    <option>Osteoastromuscular</option>
                                    <option>Otras</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-factores-riesgo" class="form-label">Factores de Riesgo</label>
                                <select class="form-select" id="edit-factores-riesgo" name="factores_riesgo">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Alcohol</option>
                                    <option>Tabaco</option>
                                    <option>Tabaco y alcohol</option>
                                    <option>Otros</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                        </div>
                        <!-- Fila: Factores Preventivos, Ingresos Económicos (Edición) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-factores-preventivos" class="form-label">Factores Preventivos</label>
                                <select class="form-select" id="edit-factores-preventivos" name="factores_preventivos">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Ejercicio</option>
                                    <option>Dieta</option>
                                    <option>Ejercicio y dieta</option>
                                    <option>Otra</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-ingresos-economicos" class="form-label">Ingresos Económicos</label>
                                <select class="form-select" id="edit-ingresos-economicos" name="ingresos_economicos">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Pensión</option>
                                    <option>Estado</option>
                                    <option>Conyuge</option>
                                    <option>Mendicidad</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                        </div>
                        <!-- Fila: Convivencia Actual, Resultado Actividad, Remisión (Edición) -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit-convivencia-actual" class="form-label">Convivencia Actual</label>
                                <select class="form-select" id="edit-convivencia-actual" name="convivencia_actual">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Pareja</option>
                                    <option>Familia</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-resultado-actividad" class="form-label">Resultado según actividad</label>
                                <select class="form-select" id="edit-resultado-actividad" name="resultado_actividad">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Con deterioro leve</option>
                                    <option>Con deterioro moderado</option>
                                    <option>Con deterioro severo</option>
                                    <option>Sin deterioro</option>
                                    <option>Delgadez</option>
                                    <option>Normal</option>
                                    <option>Obesidad grado 1</option>
                                    <option>Obesidad grado II</option>
                                    <option>Obesidad grado III</option>
                                    <option>Sobrepeso</option>
                                    <option>Depresión</option>
                                    <option>En riesgo</option>
                                    <option>Saludable</option>
                                    <option>Demencia</option>
                                    <option>Demencia leve</option>
                                    <option>Deterioro cognitivo leve</option>
                                    <option>Extrema</option>
                                    <option>Leve</option>
                                    <option>Moderada</option>
                                    <option>Ninguna disfuncionalidad</option>
                                    <option>Severa</option>
                                    <option>Disfuncionalidad extrema</option>
                                    <option>Disfuncionalidad leve</option>
                                    <option>Disfuncionalidad moderada</option>
                                    <option>Disfuncionalidad severa</option>
                                    <option>Ninguna</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-remision" class="form-label">Remisión</label>
                                <select class="form-select" id="edit-remision" name="remision">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option>Actividad física</option>
                                    <option>Enfermería</option>
                                    <option>Gerontología</option>
                                    <option>Neuropsicología</option>
                                    <option>Psicología EPS</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                        </div>
                        <!-- Fila 1.7: Discapacidad y cabeza de hogar -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-persona-discapacidad" class="form-label">¿Persona con discapacidad?</label>
                                <select class="form-select" id="edit-persona-discapacidad" name="persona_discapacidad" required>
                                    <option value="" selected disabled>¿Persona con discapacidad?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="edit-div-cual-discapacidad" style="display:none;">
                                <label for="edit-cual-discapacidad" class="form-label">Categoría discapacidad</label>
                                <select class="form-select" id="edit-cual-discapacidad" name="cual_discapacidad">
                                    <option value="" selected disabled>Categoría discapacidad...</option>
                                    <option value="Auditiva">Auditiva</option>
                                    <option value="Física">Física</option>
                                    <option value="Intelectual">Intelectual</option>
                                    <option value="Múltiple">Múltiple</option>
                                    <option value="Psicosocial">Psicosocial</option>
                                    <option value="Sordoceguera">Sordoceguera</option>
                                    <option value="Visual">Visual</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-cabeza-hogar" class="form-label">¿Cabeza de hogar?</label>
                                <select class="form-select" id="edit-cabeza-hogar" name="cabeza_hogar" required>
                                    <option value="" selected disabled>¿Cabeza de hogar?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-lider-comunidad" class="form-label">¿Líder/representante comunidad?</label>
                                <select class="form-select" id="edit-lider-comunidad" name="lider_comunidad" required>
                                    <option value="" selected disabled>¿Líder/representante comunidad?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-se-reconoce-como" class="form-label">Se reconoce como</label>
                                <select class="form-select" id="edit-se-reconoce-como" name="se_reconoce_como">
                                    <option value="" selected disabled>Se reconoce como...</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Hombre trans">Hombre trans</option>
                                    <option value="Mujer">Mujer</option>
                                    <option value="Mujer trans">Mujer trans</option>
                                    <option value="No binaria">No binaria</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-orientacion-sexual" class="form-label">Orientación sexual</label>
                                <select class="form-select" id="edit-orientacion-sexual" name="orientacion_sexual">
                                    <option value="" selected disabled>Orientación sexual...</option>
                                    <option value="Heterosexual">Heterosexual</option>
                                    <option value="Homosexual">Homosexual</option>
                                    <option value="Asexual">Asexual</option>
                                    <option value="Bisexual">Bisexual</option>
                                    <option value="Pansexual">Pansexual</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-experiencia-migratoria" class="form-label">¿Experiencia migratoria?</label>
                                <select class="form-select" id="edit-experiencia-migratoria" name="experiencia_migratoria">
                                    <option value="" selected disabled>¿Experiencia migratoria?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-grupo-etnico" class="form-label">Grupo étnico</label>
                                <select class="form-select" id="edit-grupo-etnico" name="grupo_etnico">
                                    <option value="" selected disabled>Grupo étnico...</option>
                                    <option value="Indígena">Indígena</option>
                                    <option value="Rom">Rom</option>
                                    <option value="Raizal">Raizal</option>
                                    <option value="Palenquero de San Basilio">Palenquero de San Basilio</option>
                                    <option value="Negro/Mulato">Negro/Mulato</option>
                                    <option value="Mestizo">Mestizo</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-tipo-salud" class="form-label">Tipo de salud</label>
                                <select class="form-select" id="edit-tipo-salud" name="tipo_salud">
                                    <option value="" selected disabled>Tipo de salud...</option>
                                    <option value="Régimen subsidiado">Régimen subsidiado</option>
                                    <option value="Régimen contributivo">Régimen contributivo</option>
                                    <option value="Régimen vinculado">Régimen vinculado</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-nivel-educativo" class="form-label">Nivel educativo</label>
                                <select class="form-select" id="edit-nivel-educativo" name="nivel_educativo">
                                    <option value="" selected disabled>Nivel educativo...</option>
                                    <option value="Preescolar">Preescolar</option>
                                    <option value="Básica primaria">Básica primaria</option>
                                    <option value="Básica secundaria">Básica secundaria</option>
                                    <option value="Media académica o clásica">Media académica o clásica</option>
                                    <option value="Media técnica">Media técnica</option>
                                    <option value="Normalista">Normalista</option>
                                    <option value="Técnica profesional">Técnica profesional</option>
                                    <option value="Tecnológica">Tecnológica</option>
                                    <option value="Profesional">Profesional</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="edit-telefono" name="telefono_persona">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-telefono-referencia" class="form-label">Teléfono Referencia</label>
                                <input type="text" class="form-control" id="edit-telefono-referencia" name="telefono_referencia_persona">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-referencia" class="form-label">Referencia</label>
                                <input type="text" class="form-control" id="edit-referencia" name="referencia_persona">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-correo" class="form-label">Correo</label>
                                <input type="text" class="form-control" id="edit-correo" name="correo_persona">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit-direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="edit-direccion" name="direccion_persona">
                            </div>
                        </div>
                        <!-- Fila nueva: Barrio, Comuna y Zona (Edición) -->
                        <div class="row">
                            <div class="col-md-6 mb-3 position-relative">
                                <label for="edit-barrio-persona" class="form-label">Barrio</label>
                                <input type="text" class="form-control" id="edit-barrio-persona" name="barrio_persona" placeholder="Barrio" list="edit-lista-barrios" autocomplete="off">
                                <datalist id="edit-lista-barrios"></datalist>
                                <input type="hidden" id="edit-id-barrio-persona" name="id_barrio_persona">
                                <input type="hidden" id="edit-id-comuna-persona" name="id_comuna_persona">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit-comuna-persona" class="form-label">Comuna</label>
                                <input type="text" class="form-control" id="edit-comuna-persona" name="comuna_persona" placeholder="Comuna" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit-zona-persona" class="form-label">Zona</label>
                                <input type="text" class="form-control" id="edit-zona-persona" name="zona_persona" placeholder="Zona" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-condicion-ocupacion" class="form-label">Condición Ocupación</label>
                                <select class="form-select" id="edit-condicion-ocupacion" name="condicion_ocupacion">
                                    <option value="" selected disabled>¿Condición Ocupación?</option>
                                    <option value="Ama de Casa">Ama de Casa</option>
                                    <option value="Estudiante">Estudiante</option>
                                    <option value="Empleado">Empleado</option>
                                    <option value="Desempleado">Desempleado</option>
                                    <option value="Independiente">Independiente</option>
                                    <option value="Pensionado">Pensionado</option>
                                    <option value="Buscando Empleo">Buscando Empleo</option>
                                    <option value="Ninguno">Ninguno</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-condicion-componente" class="form-label">Condición Componente</label>
                                <select class="form-select" id="edit-condicion-componente" name="condicion_componente">
                                    <option value="" selected disabled>Condición Componente...</option>
                                    <option value="C.V Beneficiario Activo">C.V Beneficiario Activo</option>
                                    <option value="C.V Beneficiario Inactivo">C.V Beneficiario Inactivo</option>
                                    <option value="CPSAM Activo">CPSAM Activo</option>
                                    <option value="CPSAM Evadido">CPSAM Evadido</option>
                                    <option value="CPSAM Fallecido">CPSAM Fallecido</option>
                                    <option value="Trasladado">Trasladado</option>
                                    <option value="Usuario indirecto">Usuario indirecto</option>
                                    <option value="CPSAM Remitido">CPSAM Remitido</option>
                                    <option value="Usuario interesado">Usuario interesado</option>
                                    <option value="CPSAM Retiro Voluntario">CPSAM Retiro Voluntario</option>
                                    <option value="CPSAM Trasladado">CPSAM Trasladado</option>
                                    <option value="C.M Retiro Definitivo">C.M Retiro Definitivo</option>
                                    <option value="C.M Activo">C.M Activo</option>
                                    <option value="C.M BDUA">C.M BDUA</option>
                                    <option value="C.M Bloqueo Registraduria">C.M Bloqueo Registraduria</option>
                                    <option value="C.M Duplicidad Documento">C.M Duplicidad Documento</option>
                                    <option value="C.M Ejercicio Mendicidad Comprobada">C.M Ejercicio Mendicidad Comprobada</option>
                                    <option value="C.M En lista de Espera">C.M En lista de Espera</option>
                                    <option value="C.M Fallecido">C.M Fallecido</option>
                                    <option value="C.M Fallecido sin Certificado">C.M Fallecido sin Certificado</option>
                                    <option value="C.M Familias en Accion">C.M Familias en Accion</option>
                                    <option value="C.M Fuera de la Ciudad">C.M Fuera de la Ciudad</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-fecha-nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="edit-fecha-nacimiento" name="fecha_nacimiento">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-activo-desde" class="form-label">Activo Desde</label>
                                <input type="date" class="form-control" id="edit-activo-desde" name="activo_desde">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-grupo" class="form-label">Centro Vida / CPSAM</label>
                                <select class="form-select" id="edit-grupo" name="id_grupo">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($result_grupos as $grupo) { ?>
                                        <option value="<?= $grupo['id_grupo']; ?>"><?= $grupo['descripcion_grupo']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-programas" class="form-label">Programas</label>
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    <?php foreach ($result_programas as $programa) { ?>
                                        <div class="form-check" style="min-width: 140px;">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="programa[]"
                                                id="programa_<?= $programa['id_programa']; ?>"
                                                value="<?= $programa['id_programa']; ?>">
                                            <label class="form-check-label" for="programa_<?= $programa['id_programa']; ?>">
                                                <?= $programa['nombre_programa']; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <!-- Fila: Meta, Actividad, Acción y Política Pública (Edición) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-meta" class="form-label">Meta</label>
                                <select class="form-select" id="edit-meta" name="id_meta" required>
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php foreach ($result_metas as $meta) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-actividad" class="form-label">Actividad</label>
                                <select class="form-select" id="edit-actividad" name="id_actividad" required disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-accion" class="form-label">Acción</label>
                                <select class="form-select" id="edit-accion" name="id_accion" required disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-politica-publica" class="form-label">Política Pública</label>
                                <select class="form-select" id="edit-politica-publica" name="id_politica_publica" required>
                                    <option value="" selected>Seleccione Política Pública...</option>
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="cedula_original" id="cedula_original" value="">
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn-modern btn-modal-cancelar" data-bs-dismiss="modal">
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Script para filtros dinámicos -->
    <script>
        // Variables PHP pasadas a JavaScript
        const tipoUsuarioSession = <?php echo json_encode($tipo_usuario); ?>;
        const idGrupoSession = <?php echo json_encode($id_grupo_session); ?>;
        const grupoPrefixSession = <?php echo json_encode($grupo_prefix); ?>;
    </script>
    <script>
        // Función para filtrar opciones de Centro Vida/CPSAM según prefijo del grupo
        function filterGrupoOptions(selectId) {
            if (tipoUsuarioSession == 2 && idGrupoSession && idGrupoSession != 0 && grupoPrefixSession) {
                const select = document.getElementById(selectId);
                if (!select) return;
                
                const options = select.querySelectorAll('option');
                options.forEach(function(option) {
                    if (option.value === '') {
                        // Mantener la opción "Seleccione..."
                        option.style.display = '';
                        return;
                    }
                    
                    const text = option.textContent.trim();
                    // Verificar si el texto comienza con el prefijo del grupo del usuario
                    if (text.toUpperCase().indexOf(grupoPrefixSession.toUpperCase()) === 0) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
            }
        }

        // --- Autocompletado de Barrio, Comuna y Zona (Edición Persona) ---
        document.addEventListener('DOMContentLoaded', function() {
            // Aplicar filtro de grupos al cargar la página (para ambos modales)
            filterGrupoOptions('id_grupo');
            filterGrupoOptions('edit-grupo');
            
            // Aplicar filtro cuando se abren los modales
            const modalNewPerson = document.getElementById('modalNewPerson');
            if (modalNewPerson) {
                modalNewPerson.addEventListener('show.bs.modal', function() {
                    filterGrupoOptions('id_grupo');
                });
            }
            
            const modalEdicion = document.getElementById('modalEdicion');
            if (modalEdicion) {
                modalEdicion.addEventListener('show.bs.modal', function() {
                    filterGrupoOptions('edit-grupo');
                });
            }
            
            const inputBarrio = document.getElementById('edit-barrio-persona');
            const datalistBarrios = document.getElementById('edit-lista-barrios');
            const inputComuna = document.getElementById('edit-comuna-persona');
            const inputZona = document.getElementById('edit-zona-persona');
            const inputIdBarrio = document.getElementById('edit-id-barrio-persona');
            const inputIdComuna = document.getElementById('edit-id-comuna-persona');
            let barriosData = [];

            if (inputBarrio && datalistBarrios) {
                inputBarrio.addEventListener('input', function() {
                    const term = this.value.trim();
                    if (term.length < 2) {
                        datalistBarrios.innerHTML = '';
                        return;
                    }
                    fetch('buscar_barrio.php?term=' + encodeURIComponent(term))
                        .then(res => res.json())
                        .then(data => {
                            barriosData = data;
                            datalistBarrios.innerHTML = '';
                            data.forEach(barrio => {
                                const option = document.createElement('option');
                                option.value = barrio.nombre_bar;
                                option.setAttribute('data-id', barrio.id_bar);
                                datalistBarrios.appendChild(option);
                            });
                        });
                });

                inputBarrio.addEventListener('change', function() {
                    const selected = barriosData.find(b => b.nombre_bar.toLowerCase() === inputBarrio.value.trim().toLowerCase());
                    if (selected) {
                        inputComuna.value = selected.nombre_com || '';
                        inputZona.value = selected.zona_bar || '';
                        if (inputIdBarrio) inputIdBarrio.value = selected.id_bar || '';
                        if (inputIdComuna) inputIdComuna.value = selected.id_com || '';
                    } else {
                        inputComuna.value = '';
                        inputZona.value = '';
                        if (inputIdBarrio) inputIdBarrio.value = '';
                        if (inputIdComuna) inputIdComuna.value = '';
                    }
                });
            }
        });
        // --- Autocompletado de Barrio, Comuna y Zona (Alta Persona) ---
        document.addEventListener('DOMContentLoaded', function() {
            const inputBarrio = document.getElementById('barrio_persona');
            const datalistBarrios = document.getElementById('lista-barrios');
            const inputComuna = document.getElementById('comuna_persona');
            const inputZona = document.getElementById('zona_persona');
            const inputIdBarrio = document.getElementById('id_barrio_persona');
            const inputIdComuna = document.getElementById('id_comuna_persona');
            let barriosData = [];

            if (inputBarrio && datalistBarrios) {
                inputBarrio.addEventListener('input', function() {
                    const term = this.value.trim();
                    if (term.length < 2) {
                        datalistBarrios.innerHTML = '';
                        return;
                    }
                    fetch('buscar_barrio.php?term=' + encodeURIComponent(term))
                        .then(res => res.json())
                        .then(data => {
                            barriosData = data;
                            datalistBarrios.innerHTML = '';
                            data.forEach(barrio => {
                                const option = document.createElement('option');
                                option.value = barrio.nombre_bar;
                                option.setAttribute('data-id', barrio.id_bar);
                                datalistBarrios.appendChild(option);
                            });
                        });
                });

                inputBarrio.addEventListener('change', function() {
                    const selected = barriosData.find(b => b.nombre_bar.toLowerCase() === inputBarrio.value.trim().toLowerCase());
                    if (selected) {
                        inputComuna.value = selected.nombre_com || '';
                        inputZona.value = selected.zona_bar || '';
                        if (inputIdBarrio) inputIdBarrio.value = selected.id_bar || '';
                        if (inputIdComuna) inputIdComuna.value = selected.id_com || '';
                    } else {
                        inputComuna.value = '';
                        inputZona.value = '';
                        if (inputIdBarrio) inputIdBarrio.value = '';
                        if (inputIdComuna) inputIdComuna.value = '';
                    }
                });
            }
        });
        // Variable global para DataTable
        let dataTable = null;

        // Inicializar DataTable solo una vez y evitar re-inicialización
        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#salesTable')) {
                return;
            }
            dataTable = $('#salesTable').DataTable({
                "searching": false,
                "lengthChange": false,
                "ordering": true,
                "info": true,
                "paging": true,
                "pageLength": 25,
                "language": {
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "emptyTable": "No hay datos disponibles en la tabla"
                },
                // Ajustar targets para que coincidan con la cantidad de columnas visibles
                "columnDefs": [{
                        "orderable": false,
                        "targets": [8]
                    } // Acciones es la columna 9 (índice 8)
                ],
                "order": [
                    [2, 'asc']
                ]
            });
        }

        // Función para actualizar solo las filas de la tabla y reinicializar correctamente el DataTable
        function updateTableRows(data) {
            try {
                // Verificar si DataTable está inicializado
                if ($.fn.DataTable.isDataTable('#salesTable')) {
                    // Obtener la instancia de DataTable
                    const table = $('#salesTable').DataTable();
                    // Destruir la instancia de forma segura
                    table.clear();
                    table.destroy();
                    // Esperar un momento para que se limpie completamente
                    $('#salesTable').removeClass('dataTable');
                }
                
                // Limpiar completamente el tbody
                $('#salesTable tbody').empty();
                
                // Agregar los nuevos datos
                $('#salesTable tbody').html(data);
                
                // Reinicializar DataTable
                dataTable = null;
                
                // Usar setTimeout para asegurar que el DOM esté completamente actualizado
                setTimeout(function() {
                    initializeDataTable();
                }, 10);
                
            } catch (error) {
                console.error('Error al actualizar tabla:', error);
                // Si hay un error, recargar la página como fallback
                location.reload();
            }
        }

        // Cargar datos filtrados sin recargar la tabla completa
        function loadTableData(params = {}) {
            const tbody = document.getElementById('table-body');
            
            // Destruir DataTable antes de mostrar loading
            if ($.fn.DataTable.isDataTable('#salesTable')) {
                try {
                    const table = $('#salesTable').DataTable();
                    table.clear();
                    table.destroy();
                    $('#salesTable').removeClass('dataTable');
                } catch (e) {
                    console.warn('Error al destruir DataTable:', e);
                }
            }
            
            tbody.innerHTML = '<tr><td colspan="9" class="text-center loading">Cargando datos...</td></tr>';

            // Construir parámetros de consulta
            const queryParams = new URLSearchParams();
            if (params.cedula) queryParams.append('cedula_persona', params.cedula);
            if (params.nombre) queryParams.append('nombre', params.nombre);
            if (params.programa) queryParams.append('programa', params.programa);
            if (params.estado) queryParams.append('estado', params.estado);
            if (params.creado_por) queryParams.append('creado_por', params.creado_por);

            // Realizar petición AJAX
            fetch(`getPersonsAjax.php?${queryParams.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.text();
                })
                .then(text => {
                    // Intentar parsear como JSON
                    try {
                        const jsonData = JSON.parse(text);
                        // Si es JSON y es un error de "sin_acceso"
                        if (jsonData.error && jsonData.tipo === 'sin_acceso') {
                            // Mostrar alert de persona existente pero sin acceso
                            Swal.fire({
                                icon: 'warning',
                                title: 'Persona encontrada - Sin acceso',
                                html: `
                                    <div style="text-align: left; padding: 10px;">
                                        <p><strong>La cédula <span style="color: #856404;">${jsonData.cedula}</span> existe en el sistema.</strong></p>
                                        <hr>
                                        <p><strong>Nombre:</strong> ${jsonData.nombre}</p>
                                        <p><strong>Centro Vida/CPSAM:</strong> ${jsonData.grupo}</p>
                                        <p><strong>Estado:</strong> <span style="color: #0d6efd; font-weight: bold;">${jsonData.estado}</span></p>
                                        <hr>
                                        <p class="text-muted" style="font-size: 0.9em;"><i class="bi bi-info-circle"></i> Esta persona pertenece a un grupo al que no tienes acceso.</p>
                                    </div>
                                `,
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#ffc107',
                                width: '550px'
                            });
                            
                            // Mostrar mensaje en la tabla
                            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-warning"><i class="bi bi-shield-lock-fill"></i><br>Persona encontrada pero no tiene acceso a este grupo.</td></tr>';
                            return;
                        }
                    } catch (e) {
                        // No es JSON, es HTML - continuar normalmente
                    }
                    
                    // Actualizar contenido del tbody con HTML
                    tbody.innerHTML = text;
                    
                    // Reinicializar DataTable después de actualizar el contenido
                    dataTable = null;
                    setTimeout(function() {
                        initializeDataTable();
                    }, 10);
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Error al cargar los datos. Por favor, recarga la página.</td></tr>';
                });
        }

        // Función debounce para evitar demasiadas peticiones
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Configurar eventos de filtros
        document.addEventListener('DOMContentLoaded', function() {
            initializeDataTable();
            // Configurar filtro automático en tiempo real (500ms debounce)
            const filterInputs = ['filter-cedula', 'filter-nombre', 'filter-programa', 'filter-estado', 'filter-creado-por'];
            filterInputs.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('input', debounce(function(e) {
                        const params = {
                            cedula: document.getElementById('filter-cedula').value,
                            nombre: document.getElementById('filter-nombre').value,
                            programa: document.getElementById('filter-programa').value,
                            estado: document.getElementById('filter-estado').value,
                            creado_por: document.getElementById('filter-creado-por').value
                        };
                        loadTableData(params);
                    }, 500));
                    // También filtrar al presionar Enter
                    element.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            const params = {
                                cedula: document.getElementById('filter-cedula').value,
                                nombre: document.getElementById('filter-nombre').value,
                                programa: document.getElementById('filter-programa').value,
                                estado: document.getElementById('filter-estado').value,
                                creado_por: document.getElementById('filter-creado-por').value
                            };
                            loadTableData(params);
                        }
                    });
                }
            });
            // Botón de filtrar manual
            const btnFilter = document.getElementById('btn-filter');
            if (btnFilter) {
                btnFilter.addEventListener('click', function() {
                    const params = {
                        cedula: document.getElementById('filter-cedula').value,
                        nombre: document.getElementById('filter-nombre').value,
                        programa: document.getElementById('filter-programa').value,
                        estado: document.getElementById('filter-estado').value,
                        creado_por: document.getElementById('filter-creado-por').value
                    };
                    loadTableData(params);
                });
            }
        });

        // Función para calcular la edad
        function calcularEdad(fechaNacimiento) {
            if (!fechaNacimiento) return '';
            const hoy = new Date();
            const nacimiento = new Date(fechaNacimiento);
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const diferenciaMeses = hoy.getMonth() - nacimiento.getMonth();
            if (diferenciaMeses < 0 || (diferenciaMeses === 0 && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }
            return edad + ' años';
        }

        // Configurar cálculo de edad en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
            const edadCalculadaInput = document.getElementById('edad_calculada');
            if (fechaNacimientoInput && edadCalculadaInput) {
                fechaNacimientoInput.addEventListener('change', function() {
                    const edad = calcularEdad(this.value);
                    edadCalculadaInput.value = edad;
                });
            }
        });
        // Mostrar/ocultar categoría discapacidad en alta
        document.addEventListener('DOMContentLoaded', function() {
            const selectDiscapacidad = document.getElementById('persona_discapacidad');
            const divCual = document.getElementById('div_cual_discapacidad');
            if (selectDiscapacidad && divCual) {
                selectDiscapacidad.addEventListener('change', function() {
                    if (this.value === 'Si') {
                        divCual.style.display = '';
                    } else {
                        divCual.style.display = 'none';
                        document.getElementById('cual_discapacidad').value = '';
                    }
                });
            }
        });
        // Mostrar/ocultar categoría discapacidad en edición
        document.addEventListener('DOMContentLoaded', function() {
            const selectDiscapacidadEdit = document.getElementById('edit-persona-discapacidad');
            const divCualEdit = document.getElementById('edit-div-cual-discapacidad');
            if (selectDiscapacidadEdit && divCualEdit) {
                selectDiscapacidadEdit.addEventListener('change', function() {
                    if (this.value === 'Si') {
                        divCualEdit.style.display = '';
                    } else {
                        divCualEdit.style.display = 'none';
                        document.getElementById('edit-cual-discapacidad').value = '';
                    }
                });
            }
        });
    </script>

    <!-- Configuración de DataTables -->
    <script>
        $(document).ready(function() {
            // Inicializar DataTable solo una vez al cargar la página
            initializeDataTable();
            // Agregar event listeners para los modales
            setupModalEventListeners();
        });

        function setupModalEventListeners() {
            // Event listener para resetear el modal de nueva persona
            $('#modalNewPerson').on('hidden.bs.modal', function() {
                // Limpiar el formulario
                $('#modalNewPerson form')[0].reset();
                // Remover información de grupo si existe
                $('#grupo-info').remove();
            });

            // Event listener para resetear el modal de edición
            $('#modalEdicion').on('hidden.bs.modal', function() {
                // Remover información de grupo si existe
                $('#edit-grupo-info').remove();
            });
        }
    </script>

    <!-- Script para SweetAlert en formularios -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Alta persona con manejo de duplicados
            const formAdd = document.querySelector('form[action="addPerson.php"]');
            if (formAdd) {
                formAdd.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = this;
                    Swal.fire({
                        title: '¿Guardar persona?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Enviar formulario vía AJAX para capturar respuesta JSON
                            const formData = new FormData(form);
                            
                            fetch('addPerson.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                return response.text().then(text => {
                                    // Intentar parsear como JSON
                                    try {
                                        const data = JSON.parse(text);
                                        return { isJson: true, data: data };
                                    } catch (e) {
                                        // No es JSON, es HTML
                                        return { isJson: false, html: text };
                                    }
                                });
                            })
                            .then(result => {
                                if (result.isJson && result.data) {
                                    const data = result.data;
                                    if (data.error) {
                                        if (data.tipo === 'duplicado') {
                                            // Mostrar error de duplicado con información
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Persona ya existe',
                                                html: `
                                                    <div style="text-align: left; padding: 10px;">
                                                        <p><strong>La cédula <span style="color: #d33;">${data.cedula}</span> ya está registrada.</strong></p>
                                                        <hr>
                                                        <p><strong>Nombre:</strong> ${data.nombre}</p>
                                                        <p><strong>Centro Vida/CPSAM:</strong> ${data.grupo}</p>
                                                        <p><strong>Estado:</strong> <span style="color: #0d6efd; font-weight: bold;">${data.estado}</span></p>
                                                    </div>
                                                `,
                                                confirmButtonText: 'Entendido',
                                                confirmButtonColor: '#3085d6',
                                                width: '500px'
                                            });
                                        } else if (data.tipo === 'general') {
                                            // Mostrar error general
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error al guardar',
                                                text: data.mensaje || 'Ocurrió un error al procesar la solicitud',
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    } else {
                                        // Éxito pero con JSON (caso raro)
                                        window.location.href = 'seePerson.php';
                                    }
                                } else if (!result.isJson) {
                                    // Es HTML, buscar si hay redirección
                                    if (result.html.includes('window.location')) {
                                        // Extraer y ejecutar la redirección
                                        window.location.href = 'seePerson.php';
                                    } else {
                                        // HTML sin redirección, mostrar error
                                        console.error('Respuesta HTML inesperada:', result.html);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error del servidor',
                                            text: 'Hubo un problema al procesar la solicitud. Revisa la consola para más detalles.',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Ocurrió un error al procesar la solicitud',
                                    confirmButtonText: 'OK'
                                });
                            });
                        }
                    });
                });
            }
            // Edición persona
            const formEdit = document.querySelector('form[action="editPersona.php"]');
            if (formEdit) {
                formEdit.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = this;
                    Swal.fire({
                        title: '¿Guardar cambios?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }
        });
    </script>
    <!-- Script original del modal de edición -->
    <script>
        // Variables globales para los modales
        let modalNewPerson = null;
        let modalEdicion = null;

        document.addEventListener("DOMContentLoaded", function() {
            // Inicializar modales de forma segura
            try {
                const modalNewPersonElement = document.getElementById("modalNewPerson");
                const modalEdicionElement = document.getElementById("modalEdicion");

                if (modalNewPersonElement) {
                    modalNewPerson = new bootstrap.Modal(modalNewPersonElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                }

                if (modalEdicionElement) {
                    modalEdicion = new bootstrap.Modal(modalEdicionElement, {
                        backdrop: 'static',
                        keyboard: false
                    });

                    modalEdicionElement.addEventListener("shown.bs.modal", function(event) {
                        const button = event.relatedTarget;

                        // Datos generales
                        document.getElementById("edit-cedula").value = button.getAttribute("data-cedula");
                        if (document.getElementById("edit-nombre")) {
                            document.getElementById("edit-nombre").value = button.getAttribute("data-nombre");
                        }
                        if (document.getElementById("edit-apellido")) {
                            document.getElementById("edit-apellido").value = button.getAttribute("data-apellidos");
                        }
                        document.getElementById("edit-telefono").value = button.getAttribute("data-telefono");
                        if (document.getElementById("edit-telefono-referencia")) {
                            document.getElementById("edit-telefono-referencia").value = button.getAttribute("data-telefono-referencia");
                        }
                        document.getElementById("edit-referencia").value = button.getAttribute("data-referencia");
                        if (document.getElementById("edit-correo")) {
                            document.getElementById("edit-correo").value = button.getAttribute("data-correo");
                        }
                        if (document.getElementById("edit-direccion")) {
                            document.getElementById("edit-direccion").value = button.getAttribute("data-direccion");
                        }
                        document.getElementById("edit-fecha-nacimiento").value = button.getAttribute("data-fecha-nacimiento");
                        document.getElementById("cedula_original").value = button.getAttribute("data-cedula");
                        document.getElementById("edit-genero").value = button.getAttribute("data-genero");
                        // Prellenar tipo de identificación
                        if (document.getElementById("edit-tipo-identificacion")) {
                            // ...ya existente...
                        }

                        // Prellenar barrio, comuna y zona
                        // Precarga barrio y comuna por ID (si existen)
                        const editIdBarrio = button.getAttribute("data-id-barrio-persona") || '';
                        const editIdComuna = button.getAttribute("data-id-comuna-persona") || '';
                        const editZona = button.getAttribute("data-zona-persona") || '';
                        const editBarrioInput = document.getElementById("edit-barrio-persona");
                        const editComunaInput = document.getElementById("edit-comuna-persona");
                        const editZonaInput = document.getElementById("edit-zona-persona");
                        const editIdBarrioInput = document.getElementById("edit-id-barrio-persona");
                        const editIdComunaInput = document.getElementById("edit-id-comuna-persona");

                        if (editIdBarrioInput) editIdBarrioInput.value = editIdBarrio;
                        if (editIdComunaInput) editIdComunaInput.value = editIdComuna;
                        if (editZonaInput) editZonaInput.value = editZona;

                        // Si hay ID de barrio, buscar el nombre y comuna por AJAX
                        if (editIdBarrio) {
                            fetch('buscar_barrio2.php?term=' + encodeURIComponent(editIdBarrio))
                                .then(res => res.json())
                                .then(data => {
                                    console.log(data);
                                    if (data && data.length > 0) {
                                        editBarrioInput.value = data[0].nombre_bar || '';
                                        if (editComunaInput) editComunaInput.value = data[0].nombre_com || '';
                                    } else {
                                        editBarrioInput.value = '';
                                        if (editComunaInput) editComunaInput.value = '';
                                    }
                                })
                                .catch(() => {
                                    editBarrioInput.value = '';
                                    if (editComunaInput) editComunaInput.value = '';
                                });
                        } else {
                            if (editBarrioInput) editBarrioInput.value = '';
                            if (editComunaInput) editComunaInput.value = '';
                        }
                        if (document.getElementById("edit-tipo-identificacion")) {
                            document.getElementById("edit-tipo-identificacion").value = button.getAttribute("data-tipo-identificacion");
                        }
                        // Prellenar campos select adicionales
                        if (document.getElementById("edit-cabeza-hogar")) {
                            document.getElementById("edit-cabeza-hogar").value = button.getAttribute("data-cabeza-hogar");
                        }
                        if (document.getElementById("edit-lider-comunidad")) {
                            document.getElementById("edit-lider-comunidad").value = button.getAttribute("data-lider-comunidad");
                        }
                        if (document.getElementById("edit-se-reconoce-como")) {
                            document.getElementById("edit-se-reconoce-como").value = button.getAttribute("data-se-reconoce-como");
                        }
                        if (document.getElementById("edit-orientacion-sexual")) {
                            document.getElementById("edit-orientacion-sexual").value = button.getAttribute("data-orientacion-sexual");
                        }
                        if (document.getElementById("edit-experiencia-migratoria")) {
                            document.getElementById("edit-experiencia-migratoria").value = button.getAttribute("data-experiencia-migratoria");
                        }
                        if (document.getElementById("edit-grupo-etnico")) {
                            document.getElementById("edit-grupo-etnico").value = button.getAttribute("data-grupo-etnico");
                        }
                        if (document.getElementById("edit-tipo-salud")) {
                            document.getElementById("edit-tipo-salud").value = button.getAttribute("data-tipo-salud");
                        }
                        if (document.getElementById("edit-nivel-educativo")) {
                            document.getElementById("edit-nivel-educativo").value = button.getAttribute("data-nivel-educativo");
                        }
                        if (document.getElementById("edit-condicion-ocupacion")) {
                            document.getElementById("edit-condicion-ocupacion").value = button.getAttribute("data-condicion-ocupacion");
                        }
                        if (document.getElementById("edit-condicion-componente")) {
                            document.getElementById("edit-condicion-componente").value = button.getAttribute("data-condicion-componente");
                        }
                        if (document.getElementById("edit-activo-desde")) {
                            document.getElementById("edit-activo-desde").value = button.getAttribute("data-activo-desde");
                        }
                        // Precargar grupo sisben
                        if (document.getElementById("edit-grupo-sisben")) {
                            document.getElementById("edit-grupo-sisben").value = button.getAttribute("data-grupo-sisben");
                        }

                        // Precargar campos adicionales médicos
                        if (document.getElementById("edit-eps")) {
                            document.getElementById("edit-eps").value = button.getAttribute("data-eps");
                        }
                        if (document.getElementById("edit-peso")) {
                            document.getElementById("edit-peso").value = button.getAttribute("data-peso");
                        }
                        if (document.getElementById("edit-talla")) {
                            document.getElementById("edit-talla").value = button.getAttribute("data-talla");
                        }
                        if (document.getElementById("edit-patologias")) {
                            document.getElementById("edit-patologias").value = button.getAttribute("data-patologias");
                        }
                        if (document.getElementById("edit-factores-riesgo")) {
                            document.getElementById("edit-factores-riesgo").value = button.getAttribute("data-factores-riesgo");
                        }
                        if (document.getElementById("edit-factores-preventivos")) {
                            document.getElementById("edit-factores-preventivos").value = button.getAttribute("data-factores-preventivos");
                        }
                        if (document.getElementById("edit-ingresos-economicos")) {
                            document.getElementById("edit-ingresos-economicos").value = button.getAttribute("data-ingresos-economicos");
                        }
                        if (document.getElementById("edit-convivencia-actual")) {
                            document.getElementById("edit-convivencia-actual").value = button.getAttribute("data-convivencia-actual");
                        }
                        if (document.getElementById("edit-resultado-actividad")) {
                            document.getElementById("edit-resultado-actividad").value = button.getAttribute("data-resultado-actividad");
                        }
                        if (document.getElementById("edit-remision")) {
                            document.getElementById("edit-remision").value = button.getAttribute("data-remision");
                        }
                        // Precargar persona discapacidad
                        if (document.getElementById("edit-persona-discapacidad")) {
                            document.getElementById("edit-persona-discapacidad").value = button.getAttribute("data-persona-discapacidad");
                            // Mostrar/ocultar y precargar categoría discapacidad
                            if (button.getAttribute("data-persona-discapacidad") === "Si") {
                                document.getElementById("edit-div-cual-discapacidad").style.display = '';
                                document.getElementById("edit-cual-discapacidad").value = button.getAttribute("data-cual-discapacidad");
                            } else {
                                document.getElementById("edit-div-cual-discapacidad").style.display = 'none';
                                document.getElementById("edit-cual-discapacidad").value = '';
                            }
                        }

                        // Guardar valor original del grupo y establecer el valor actual
                        const grupoValue = button.getAttribute("data-id-grupo");
                        document.getElementById("edit-grupo").value = grupoValue;
                        $('#edit-grupo').data('original-value', grupoValue);

                        // Precargar Meta, Actividad, Acción y Política Pública
                        const idMeta = button.getAttribute("data-id-meta");
                        const idActividad = button.getAttribute("data-id-actividad");
                        const idAccion = button.getAttribute("data-id-accion");
                        const idPoliticaNueva = button.getAttribute("data-id-politica-publica-nueva");

                        // Establecer Meta
                        if (document.getElementById("edit-meta")) {
                            document.getElementById("edit-meta").value = idMeta || '';
                        }

                        // Cargar actividades si hay meta seleccionada
                        if (idMeta) {
                            $.ajax({
                                url: 'getActividades.php',
                                type: 'POST',
                                data: { id_meta: idMeta },
                                success: function(response) {
                                    $('#edit-actividad').empty().append('<option value="">Seleccione Actividad...</option>');
                                    $('#edit-actividad').append(response).prop('disabled', false);
                                    if (idActividad) {
                                        $('#edit-actividad').val(idActividad);
                                        
                                        // Cargar acciones si hay actividad seleccionada
                                        $.ajax({
                                            url: 'getAcciones.php',
                                            type: 'POST',
                                            data: { id_actividad: idActividad },
                                            success: function(response) {
                                                $('#edit-accion').empty().append('<option value="">Seleccione Acción...</option>');
                                                $('#edit-accion').append(response).prop('disabled', false);
                                                if (idAccion) {
                                                    $('#edit-accion').val(idAccion);
                                                    
                                                    // Cargar políticas públicas si hay acción seleccionada
                                                    $.ajax({
                                                        url: 'getPoliticaPublica.php',
                                                        type: 'POST',
                                                        data: { id_accion: idAccion },
                                                        dataType: 'json',
                                                        success: function(response) {
                                                            $('#edit-politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');
                                                            if (response && response.politicas && response.politicas.length > 0) {
                                                                response.politicas.forEach(function(p) {
                                                                    $('#edit-politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                                                });
                                                                if (idPoliticaNueva) {
                                                                    $('#edit-politica-publica').val(idPoliticaNueva);
                                                                }
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                        }

                        // Seleccionar los checks de programas
                        const idsProgramas = button.getAttribute('data-ids-programas') || '';
                        // Elimina comillas simples si existen y separa por coma
                        const idsArray = idsProgramas.replace(/'/g, '').split(',').map(id => id.trim()).filter(id => id);
                        // Busca los checkboxes dentro del modal de edición
                        const programasChecks = modalEdicionElement.querySelectorAll('input[name="programa[]"]');
                        programasChecks.forEach(cb => {
                            cb.checked = idsArray.includes(cb.value);
                        });

                    });
                }
            } catch (error) {
                console.error('Error inicializando modales:', error);
            }
        });

        $(document).ready(function() {
            $('#id_grupo').on('change', function() {
                let idGrupo = $(this).val();

                if (idGrupo) {
                    $.ajax({
                        url: 'checkGroupLimit.php',
                        type: 'POST',
                        data: {
                            id_grupo: idGrupo
                        },
                        dataType: 'json',
                        success: function(response) {
                            if ($('#grupo-info').length === 0) {
                                $('#id_grupo').parent().append('<small id="grupo-info" class="text-muted mt-1"></small>');
                            }

                            const color = response.limitReached ? 'text-danger' : 'text-success';
                            $('#grupo-info').removeClass('text-muted text-success text-danger').addClass(color);
                            $('#grupo-info').text(`Personas en el grupo: ${response.personasActuales}/${response.limite}`);

                            if (response.limitReached) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Grupo lleno',
                                    text: `El grupo "${response.grupoNombre}" ha alcanzado su límite máximo de ${response.limite} personas.`,
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error checking group limit:', error);
                        }
                    });

                    $.ajax({
                        url: '../obtener_centros_vida.php',
                        type: 'POST',
                        data: {
                            id_grupo: idGrupo
                        },
                        success: function(response) {
                            $('#observacion_persona').html('<option value="" selected>Seleccione...</option>');
                            $('#observacion_persona').append(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading centers:', error);
                        }
                    });
                } else {
                    $('#observacion_persona').html('<option value="" selected>Seleccione...</option>');
                    $('#grupo-info').remove();
                }
            });

            // Nota: La validación del formulario ahora se maneja en el handler de vanilla JavaScript
            // para poder interceptar respuestas JSON de errores duplicados
            // Ver script de SweetAlert más abajo en el código

            // Validar límite del grupo en modal de edición
            $('#edit-grupo').on('change', function() {
                let idGrupo = $(this).val();

                if (idGrupo) {
                    $.ajax({
                        url: 'checkGroupLimit.php',
                        type: 'POST',
                        data: {
                            id_grupo: idGrupo
                        },
                        dataType: 'json',
                        success: function(response) {
                            if ($('#edit-grupo-info').length === 0) {
                                $('#edit-grupo').parent().append('<small id="edit-grupo-info" class="text-muted mt-1"></small>');
                            }

                            const color = response.limitReached ? 'text-danger' : 'text-success';
                            $('#edit-grupo-info').removeClass('text-muted text-success text-danger').addClass(color);
                            $('#edit-grupo-info').text(`Personas en el grupo: ${response.personasActuales}/${response.limite}`);

                            if (response.limitReached) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Grupo lleno',
                                    text: `El grupo "${response.grupoNombre}" ha alcanzado su límite máximo de ${response.limite} personas.`,
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error checking group limit for edit modal:', error);
                        }
                    });
                } else {
                    $('#edit-grupo-info').remove();
                }
            });

            // *** FUNCIONALIDAD PARA META, ACTIVIDAD, ACCIÓN Y POLÍTICA PÚBLICA ***
            
            // Manejar selección de Meta para cargar Actividades (Modal Agregar)
            $('#meta').on('change', function() {
                const idMeta = $(this).val();

                // Limpiar y deshabilitar campos dependientes
                $('#actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

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

            // Manejar selección de Actividad para cargar Acciones (Modal Agregar)
            $('#actividad').on('change', function() {
                const idActividad = $(this).val();
                // Limpiar y deshabilitar campo de acciones
                $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

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

            // Manejar selección de Acción para consultar políticas públicas (Modal Agregar)
            $('#accion').on('change', function() {
                const idAccion = $(this).val();
                // Vaciar y resetear el select de política pública cada vez que se cambia la acción
                $('#politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                $('#politica-publica').prop('selectedIndex', 0);
                if (idAccion) {
                    $.ajax({
                        url: 'getPoliticaPublica.php',
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
                
                if (idAccion) {
                    $.ajax({
                        url: 'getPoliticaPublica.php',
                        type: 'POST',
                        data: { id_accion: idAccion },
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.politicas && response.politicas.length > 0) {
                                response.politicas.forEach(function(p) {
                                    $('#edit-politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                });
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
        });
    </script>
</body>

</html>