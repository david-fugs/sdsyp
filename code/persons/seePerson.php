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

$grupos = "SELECT * FROM grupos ";
$result_grupos = mysqli_query($mysqli, $grupos);
if (!$result_grupos) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}
$politicas_publicas = "SELECT * FROM politicas_publicas ";
$result_politicas_publicas = mysqli_query($mysqli, $politicas_publicas);
if (!$result_politicas_publicas) {
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
                        <button type="button" id="btn-filter" class="btn-modern btn-primary">
                            <i class="bi bi-search"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla moderna -->
            <div class="modern-table-wrapper">
                <table class="modern-table" id="salesTable">
                    <thead>
                        <tr>
                            <th class="col-id">Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Género</th>
                            <th>Fecha Nacimiento</th>
                            <th>Edad</th>
                            <th>Teléfono</th>
                            <th>Referencia</th>
                            <th>Programas</th>
                            <th>Centro Vida / CPSAM</th>
                            <th class="col-status">Estado</th>
                            <th>Política Pública</th>
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
                        <!-- Fila 1 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="cedula_persona" name="cedula_persona" placeholder="Cédula" required autocomplete="off" autofocus>
                                <label class="" for="cedula_persona">Cédula</label>
                            </div>

                            <div class="col-md-6 mb-3 form-floating mt-1">
                                <select class="form-select" id="genero_persona" name="genero_persona" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label class="" for="cedula_persona">Genero</label>
                            </div>

                        </div>

                        <!-- Fila 2 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="nombres_persona" name="nombres_persona" placeholder="Nombres" required>
                                <label for="nombres_persona">Nombres</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="apellidos_persona" name="apellidos_persona" placeholder="Apellidos" required>
                                <label for="apellidos_persona">Apellidos</label>
                            </div>
                        </div>

                        <!-- Fila 3 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="referencia_persona" name="referencia_persona" placeholder="Referencia">
                                <label for="referencia_persona">Referencia</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="telefono_persona" name="telefono_persona" placeholder="Teléfono">
                                <label for="telefono_persona">Teléfono</label>
                            </div>
                        </div>

                        <!-- Fila 3.5 - Fecha de Nacimiento -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="Fecha de Nacimiento">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Edad</label>
                                <input type="text" class="form-control" id="edad_calculada" readonly placeholder="Se calculará automáticamente" style="background-color: #f8f9fa;">
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
                                    <?php foreach ($result_grupos as $grupo) { ?>
                                        <option value="<?= $grupo['id_grupo']; ?>"><?= $grupo['descripcion_grupo']; ?></option>
                                    <?php } ?>
                                </select>
                                <label class="" for="id_grupo">Centro Vida / CPSAM</label>
                            </div>

                        </div>
                        <!-- fila 5 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="id_politica_publica" name="id_politica_publica">
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_politicas_publicas as $politica) { ?>
                                        <option value="<?= $politica['id_politica']; ?>"><?= $politica['descripcion_politica']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="id_politica_publica">Política Pública</label>
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
        <div class="modal-dialog">
            <div class="modal-content rounded-4 shadow-sm">
                <div class="modal-header bg-dark text-white"> <!-- Negro con texto blanco -->
                    <h5 class="modal-title" id="modalEdicionLabel">Edit Store Info</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="editPersona.php" method="POST">
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label for="edit-cedula" class="form-label">Cedula </label>
                            <input type="text" class="form-control" id="edit-cedula" name="cedula_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-nombre" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="edit-nombre" name="nombres_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-apellido" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="edit-apellido" name="apellidos_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-genero" class="form-label">Genero</label>
                            <select class="form-select" id="edit-genero" name="genero_persona">
                                <option value="" selected disabled>Seleccione...</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="edit-telefono" name="telefono_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-referencia" class="form-label">Referencia</label>
                            <input type="text" class="form-control" id="edit-referencia" name="referencia_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-fecha-nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="edit-fecha-nacimiento" name="fecha_nacimiento">
                        </div>
                        <div class="mb-3">
                            <label for="edit-programas" class="form-label">Programas</label>

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
                        <div class="mb-3">
                            <label for="edit-grupo" class="form-label">Centro Vida / CPSAM</label>
                            <select class="form-select" id="edit-grupo" name="id_grupo">
                                <option value="">Seleccione...</option>
                                <?php foreach ($result_grupos as $grupo) { ?>
                                    <option value="<?= $grupo['id_grupo']; ?>"><?= $grupo['descripcion_grupo']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-politica-publica" class="form-label">Política Pública</label>
                            <select class="form-select" id="edit-politica-publica" name="id_politica_publica">
                                <option value="">Seleccione...</option>
                                <?php foreach ($result_politicas_publicas as $politica) { ?>
                                    <option value="<?= $politica['id_politica']; ?>"><?= $politica['descripcion_politica']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <input type="hidden" name="cedula_original" id="cedula_original" value="">

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

    <!-- Script para filtros dinámicos -->
    <script>
        // Variable global para DataTable
        let dataTable = null;
        
        // Cargar datos iniciales
        function loadTableData(params = {}) {
            const tbody = document.getElementById('table-body');
            tbody.innerHTML = '<tr><td colspan="13" class="text-center loading">Cargando datos...</td></tr>';
            
            // Construir parámetros de consulta
            const queryParams = new URLSearchParams();
            if (params.cedula) queryParams.append('cedula_persona', params.cedula);
            if (params.nombre) queryParams.append('nombre', params.nombre);
            if (params.programa) queryParams.append('programa', params.programa);
            if (params.estado) queryParams.append('estado', params.estado);
            
            // Realizar petición AJAX
            fetch(`getPersonsAjax.php?${queryParams.toString()}`)
                .then(response => response.text())
                .then(data => {
                    tbody.innerHTML = data;
                    
                    // Solo reinicializar DataTable si ya existe
                    if (dataTable) {
                        dataTable.destroy();
                        dataTable = null;
                    }
                    
                    // Esperar un momento antes de reinicializar
                    setTimeout(() => {
                        initializeDataTable();
                    }, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="13" class="text-center text-danger">Error al cargar los datos</td></tr>';
                });
        }
        
        // Función para inicializar DataTable
        function initializeDataTable() {
            if (!dataTable) {
                dataTable = $('#salesTable').DataTable({
                    "searching": false,
                    "lengthChange": false,
                    "ordering": true,
                    "info": true,
                    "paging": true,
                    "pageLength": 15,
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
                    "columnDefs": [
                        { "orderable": false, "targets": [12] }
                    ],
                    "order": [[2, 'asc']]
                });
            }
        }
        
        // Configurar eventos de filtros
        document.addEventListener('DOMContentLoaded', function() {
            // No cargar datos iniciales, ya están cargados desde PHP
            
            // Configurar filtro automático en tiempo real
            const filterInputs = ['filter-cedula', 'filter-nombre', 'filter-programa', 'filter-estado'];
            
            filterInputs.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('input', debounce(function() {
                        const params = {
                            cedula: document.getElementById('filter-cedula').value,
                            nombre: document.getElementById('filter-nombre').value,
                            programa: document.getElementById('filter-programa').value,
                            estado: document.getElementById('filter-estado').value
                        };
                        loadTableData(params);
                    }, 300));
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
                        estado: document.getElementById('filter-estado').value
                    };
                    loadTableData(params);
                });
            }
        });
        
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
    </script>

    <!-- Configuración de DataTables -->
    <script>
        $(document).ready(function() {
            // Inicializar DataTable solo una vez al cargar la página
            initializeDataTable();
        });
    </script>

    <!-- Script original del modal de edición -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modalEdicion = document.getElementById("modalEdicion");

            modalEdicion.addEventListener("shown.bs.modal", function(event) {
                const button = event.relatedTarget;

                // Datos generales
                document.getElementById("edit-cedula").value = button.getAttribute("data-cedula");
                document.getElementById("edit-nombre").value = button.getAttribute("data-nombre");
                document.getElementById("edit-apellido").value = button.getAttribute("data-apellidos");
                document.getElementById("edit-telefono").value = button.getAttribute("data-telefono");
                document.getElementById("edit-referencia").value = button.getAttribute("data-referencia");
                document.getElementById("edit-fecha-nacimiento").value = button.getAttribute("data-fecha-nacimiento");
                document.getElementById("cedula_original").value = button.getAttribute("data-cedula");
                document.getElementById("edit-genero").value = button.getAttribute("data-genero");
                
                // Guardar valor original del grupo y establecer el valor actual
                const grupoValue = button.getAttribute("data-id-grupo");
                document.getElementById("edit-grupo").value = grupoValue;
                $('#edit-grupo').data('original-value', grupoValue);
                
                document.getElementById("edit-politica-publica").value = button.getAttribute("data-id-politica-publica");
                
                // Programas
                const idsProgramas = button.getAttribute("data-ids-programas");
                const idsArray = idsProgramas.split(",").map(id => id.trim());
                const checkboxes = modalEdicion.querySelectorAll('input[name="programa[]"]');
                checkboxes.forEach(cb => {
                    cb.checked = idsArray.includes(cb.value);
                });
            });
        });

        $(document).ready(function() {
            $('#id_grupo').on('change', function() {
                let idGrupo = $(this).val();

                if (idGrupo) {
                    $.ajax({
                        url: 'checkGroupLimit.php',
                        type: 'POST',
                        data: { id_grupo: idGrupo },
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
                        }
                    });

                    $.ajax({
                        url: '../obtener_centros_vida.php',
                        type: 'POST',
                        data: { id_grupo: idGrupo },
                        success: function(response) {
                            $('#observacion_persona').html('<option value="" selected>Seleccione...</option>');
                            $('#observacion_persona').append(response);
                        }
                    });
                } else {
                    $('#observacion_persona').html('<option value="" selected>Seleccione...</option>');
                    $('#grupo-info').remove();
                }
            });

            // Validar límite del grupo antes de enviar el formulario
            $('form[action="addPerson.php"]').on('submit', function(e) {
                const grupoId = $('#id_grupo').val();
                
                if (grupoId) {
                    e.preventDefault();
                    
                    $.ajax({
                        url: 'checkGroupLimit.php',
                        type: 'POST',
                        data: { id_grupo: grupoId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.limitReached) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Límite alcanzado',
                                    text: `El grupo "${response.grupoNombre}" ha alcanzado su límite máximo de ${response.limite} personas.`,
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                e.target.submit();
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al verificar el límite del grupo',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });

            // Validar límite del grupo en modal de edición
            $('#edit-grupo').on('change', function() {
                let idGrupo = $(this).val();

                if (idGrupo) {
                    $.ajax({
                        url: 'checkGroupLimit.php',
                        type: 'POST',
                        data: { id_grupo: idGrupo },
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
                        }
                    });
                } else {
                    $('#edit-grupo-info').remove();
                }
            });
        });
    </script>
</body>
</html>