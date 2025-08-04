
<?php
session_start();
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
        .modern-input, .modern-select {
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
        .dataTables_info, .dataTables_paginate {
            font-size: 14px !important;
        }
        
        .dataTables_length select, .dataTables_length label {
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
        
        .form-control, .form-select {
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
        .text-muted, .text-success, .text-danger {
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

$grupos = "SELECT * FROM grupos";
$result_grupos = mysqli_query($mysqli, $grupos);
if (!$result_grupos) {
    die("Error en la consulta: " . mysqli_error($mysqli));
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

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;"><b><i
                class="bi bi-arrow-left-right"></i> MOVIMIENTOS PERSONAS</b></h1>

    <!-- Tabla de Movimientos -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-arrow-left-right"></i> Movimientos de Personas</h2>
                <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                    <i class="bi bi-plus-circle-fill"></i>
                    Agregar Movimiento
                </button>
            </div>

            <!-- Filtros modernos -->
            <div class="modern-filters">
                <form action="seePersonMovement.php" method="get" class="filter-row">
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
                            <th>Centro Vida Traslado</th>
                            <th>Fecha Movimiento</th>
                            <th>Observación</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include "getPersonMovement.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Add Person -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Hacemos el modal más ancho -->
            <div class="modal-content">
                <form action="addPersonMovement.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar Movimiento
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila 1 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="cedula_form" name="cedula_persona" placeholder="Cédula" required autocomplete="off" autofocus>
                                <label class="" for="cedula_persona">Cédula</label>
                            </div>

                            <div class="col-md-6 mb-3 form-floating mt-1">
                                <select class="form-select" id="condicion" name="id_condicion" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_condiciones as $condicion) { ?>
                                        <option value="<?= $condicion['id_condicion']; ?>"><?= $condicion['descripcion_condicion']; ?></option>
                                    <?php } ?>
                                </select>
                                <label class="" for="condicion">Condición</label>
                            </div>
                        </div>
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
                            <div class="col-md-6 mb-3 form-floating mt-2">
                                <input type="date" class="form-control" id="fecha_movimiento" name="fecha_movimiento" placeholder="Fecha Movimiento">
                                <label for="fecha_movimiento">Fecha Movimiento</label>
                            </div>
                        </div>
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
            // Datos generales
            document.getElementById("edit-cedula").value = button.getAttribute("data-cedula");
            document.getElementById("edit-nombre").value = button.getAttribute("data-nombre");
            document.getElementById("edit-apellido").value = button.getAttribute("data-apellidos");
            document.getElementById("edit-fecha_movimiento").value = button.getAttribute("data-fecha_movimiento");
            document.getElementById("cedula_original").value = button.getAttribute("data-cedula");
            document.getElementById("edit-condicion").value = button.getAttribute("data-condicion");
            document.getElementById("edit-observacion").value = button.getAttribute("data-observacion_movimiento");
            document.getElementById("edit-centro-vida").value = button.getAttribute("data-centro_vida_traslado") || "";
            document.getElementById("id_movimiento_persona").value = button.getAttribute("data-id_movimiento_persona");
            
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
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            columnDefs: [
                { orderable: false, targets: [7] }, // Deshabilitar orden en la columna de acciones
                { className: "text-center", targets: [0, 7] } // Centrar columna de ID y acciones
            ],
            order: [[5, 'desc']], // Ordenar por fecha de movimiento (columna 5) descendente
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