<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    header("location: ../../index.php");
    exit();
}

include("../../conexion.php");

$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$nombre_usuario = $_SESSION['usuario'];

// Consulta para condiciones (filtrar solo las de Colombia Mayor)
$condiciones_sql = "SELECT id_condicion, descripcion_condicion 
                    FROM condiciones_componente 
                    WHERE descripcion_condicion LIKE 'C.M%' 
                    ORDER BY descripcion_condicion";
$result_condiciones_query = $mysqli->query($condiciones_sql);
$condiciones_array = [];
while($row = $result_condiciones_query->fetch_assoc()) {
    $condiciones_array[] = $row;
}

// Consulta para metas
$metas_sql = "SELECT id_meta, descripcion_meta 
              FROM metas 
              ORDER BY descripcion_meta ASC";
$result_metas_query = $mysqli->query($metas_sql);
$metas_array = [];
while($row = $result_metas_query->fetch_assoc()) {
    $metas_array[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registros Individuales - Colombia Mayor</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-size: 16px !important;
        }

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

        .modern-input,
        .modern-select {
            font-size: 15px !important;
            padding: 10px 12px !important;
        }

        .btn-modern {
            font-size: 15px !important;
            padding: 10px 20px !important;
        }

        /* Estilos para la lista de cédulas */
        .cedulas-list {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 15px;
        }

        .cedula-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }

        .cedula-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .cedula-info {
            flex: 1;
        }

        .cedula-numero {
            font-weight: bold;
            color: #2c3e50;
            font-size: 16px;
        }

        .cedula-nombre {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 2px;
        }

        .btn-remove-cedula {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }

        .btn-remove-cedula:hover {
            background: #c0392b;
        }

        .contador-cedulas {
            background: #3498db;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        .buscar-persona-box {
            background: #e8f4f8;
            border: 2px dashed #3498db;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;">
        <b><i class="bi bi-clipboard-data-fill"></i> REGISTROS INDIVIDUALES - COLOMBIA MAYOR</b>
    </h1>

    <!-- Tabla de Registros -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-clipboard-data"></i> Registros Individuales</h2>
                <div>
                    <button type="button" class="btn-modern btn-success me-2" onclick="window.location.href='exportRegistrosCM.php'">
                        <i class="bi bi-file-excel-fill"></i>
                        Exportar Excel
                    </button>
                    <button type="button" class="btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalNewRegistro">
                        <i class="bi bi-plus-circle-fill"></i>
                        Agregar Registro
                    </button>
                </div>
            </div>

            <!-- Filtros modernos -->
            <div class="modern-filters">
                <form action="formIndividualCM.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="filter_cedula">Cédula</label>
                        <input type="text"
                            id="filter_cedula"
                            name="cedula_persona"
                            class="modern-input"
                            placeholder="Buscar por cédula..."
                            value="<?= isset($_GET['cedula_persona']) ? htmlspecialchars($_GET['cedula_persona']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <label for="filter_nombre">Nombre</label>
                        <input type="text"
                            id="filter_nombre"
                            name="nombre"
                            class="modern-input"
                            placeholder="Buscar por nombre..."
                            value="<?= isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <label for="filter_condicion">Condición</label>
                        <select name="condicion" id="filter_condicion" class="modern-select">
                            <option value="">Todas las condiciones</option>
                            <?php foreach ($condiciones_array as $condicion) {
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
                <table class="modern-table" id="registrosTable">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Condición</th>
                            <th>Meta</th>
                            <th>Actividad</th>
                            <th>Acción</th>
                            <th>Política Pública</th>
                            <th>Fecha Registro</th>
                            <th>Registrado por</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include "getRegistrosCM.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Registro con Múltiples Cédulas -->
    <div class="modal fade" id="modalNewRegistro" tabindex="-1" aria-labelledby="modalNewRegistroLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title" id="modalNewRegistroLabel">
                        <i class="bi bi-plus-circle-fill me-2"></i>Agregar Registro Individual - Colombia Mayor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formNewRegistro" action="addRegistroCM.php" method="POST">
                    <div class="modal-body">
                        <!-- Sección: Buscar y Agregar Cédulas -->
                        <div class="buscar-persona-box">
                            <h6 class="mb-3"><i class="bi bi-person-plus-fill"></i> Buscar y Agregar Personas</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="buscar_cedula_input" 
                                               placeholder="Ingrese número de cédula..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="buscarYAgregarPersona()">
                                        <i class="bi bi-search"></i> Buscar y Agregar
                                    </button>
                                </div>
                            </div>

                            <!-- Lista de Cédulas Agregadas -->
                            <div id="cedulas_container" style="display:none;">
                                <hr class="my-3">
                                <div class="contador-cedulas" id="contador_cedulas">
                                    <i class="bi bi-people-fill"></i> 0 personas agregadas
                                </div>
                                <div class="cedulas-list" id="cedulas_list"></div>
                            </div>
                        </div>

                        <!-- Hidden input para enviar las cédulas -->
                        <input type="hidden" name="cedulas" id="cedulas_hidden">

                        <hr class="my-4">

                        <!-- Datos del Registro -->
                        <h6 class="mb-3"><i class="bi bi-clipboard-data"></i> Información del Registro</h6>
                        
                        <div class="row g-3">
                            <!-- Condición -->
                            <div class="col-md-6">
                                <label for="id_condicion" class="form-label">Condición *</label>
                                <select class="form-select form-select-lg" name="id_condicion" id="id_condicion" required>
                                    <option value="">Seleccione una condición...</option>
                                    <?php foreach ($condiciones_array as $condicion): ?>
                                        <option value="<?= $condicion['id_condicion']; ?>">
                                            <?= $condicion['descripcion_condicion']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Fecha de Registro -->
                            <div class="col-md-6">
                                <label for="fecha_registro" class="form-label">Fecha de Registro *</label>
                                <input type="date" 
                                       class="form-control form-control-lg" 
                                       name="fecha_registro_actividad" 
                                       id="fecha_registro" 
                                       value="<?= date('Y-m-d'); ?>" 
                                       required>
                            </div>

                            <!-- Meta -->
                            <div class="col-md-6">
                                <label for="id_meta" class="form-label">Meta *</label>
                                <select class="form-select form-select-lg" name="id_meta" id="id_meta" required>
                                    <option value="">Seleccione una meta...</option>
                                    <?php foreach ($metas_array as $meta): ?>
                                        <option value="<?= $meta['id_meta']; ?>">
                                            <?= htmlspecialchars($meta['descripcion_meta']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Actividad -->
                            <div class="col-md-6">
                                <label for="id_actividad" class="form-label">Actividad *</label>
                                <select class="form-select form-select-lg" name="id_actividad" id="id_actividad" required disabled>
                                    <option value="">Primero seleccione una meta...</option>
                                </select>
                            </div>

                            <!-- Acción -->
                            <div class="col-md-6">
                                <label for="id_accion" class="form-label">Acción *</label>
                                <select class="form-select form-select-lg" name="id_accion" id="id_accion" required disabled>
                                    <option value="">Primero seleccione una actividad...</option>
                                </select>
                            </div>

                            <!-- Política Pública -->
                            <div class="col-md-6">
                                <label for="id_politica_publica" class="form-label">Política Pública *</label>
                                <select class="form-select form-select-lg" name="id_politica_publica" id="id_politica_publica" required disabled>
                                    <option value="">Primero seleccione una acción...</option>
                                </select>
                            </div>

                            <!-- Observaciones -->
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" 
                                          name="observaciones" 
                                          id="observaciones" 
                                          rows="2" 
                                          placeholder="Observaciones adicionales (opcional)"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success btn-lg" id="btn_guardar_registro">
                            <i class="bi bi-save"></i> Guardar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts: jQuery primero, luego Bootstrap, luego SweetAlert, luego código personalizado -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Array para almacenar las cédulas agregadas
        let cedulasAgregadas = [];

        // Las metas se cargan directamente desde PHP en el HTML
        $(document).ready(function() {
            console.log('Formulario cargado correctamente');
        });

        // Cascada Meta -> Actividad
        $('#id_meta').change(function() {
            const metaId = $(this).val();
            const actividadSelect = $('#id_actividad');
            const accionSelect = $('#id_accion');
            const politicaSelect = $('#id_politica_publica');

            // Resetear selects dependientes
            accionSelect.html('<option value="">Primero seleccione una actividad...</option>').prop('disabled', true);
            politicaSelect.html('<option value="">Primero seleccione una acción...</option>').prop('disabled', true);

            if (metaId) {
                actividadSelect.prop('disabled', false);
                $.ajax({
                    url: 'getActividades.php',
                    type: 'POST',
                    data: { id_meta: metaId },
                    success: function(response) {
                        if (response && response.trim() !== '') {
                            actividadSelect.html('<option value="">Seleccione una actividad...</option>' + response);
                        } else {
                            actividadSelect.html('<option value="">No hay actividades disponibles</option>');
                        }
                    },
                    error: function() {
                        console.error('Error cargando actividades');
                        actividadSelect.html('<option value="">Error al cargar actividades</option>');
                    }
                });
            } else {
                actividadSelect.html('<option value="">Primero seleccione una meta...</option>').prop('disabled', true);
            }
        });

        // Cascada Actividad -> Acción
        $('#id_actividad').change(function() {
            const actividadId = $(this).val();
            const accionSelect = $('#id_accion');
            const politicaSelect = $('#id_politica_publica');

            politicaSelect.html('<option value="">Primero seleccione una acción...</option>').prop('disabled', true);

            if (actividadId) {
                accionSelect.prop('disabled', false);
                $.ajax({
                    url: 'getAcciones.php',
                    type: 'POST',
                    data: { id_actividad: actividadId },
                    success: function(response) {
                        if (response && response.trim() !== '') {
                            accionSelect.html('<option value="">Seleccione una acción...</option>' + response);
                        } else {
                            accionSelect.html('<option value="">No hay acciones disponibles</option>');
                        }
                    },
                    error: function() {
                        console.error('Error cargando acciones');
                        accionSelect.html('<option value="">Error al cargar acciones</option>');
                    }
                });
            } else {
                accionSelect.html('<option value="">Primero seleccione una actividad...</option>').prop('disabled', true);
            }
        });

        // Cascada Acción -> Política Pública
        $('#id_accion').change(function() {
            const accionId = $(this).val();
            const politicaSelect = $('#id_politica_publica');

            if (accionId) {
                politicaSelect.prop('disabled', false);
                $.ajax({
                    url: 'getPoliticaPublica.php',
                    type: 'POST',
                    data: { id_accion: accionId },
                    dataType: 'json',
                    success: function(response) {
                        let options = '<option value="">Seleccione una política pública...</option>';
                        if (response && response.politicas && response.politicas.length > 0) {
                            response.politicas.forEach(function(politica) {
                                options += `<option value="${politica.id_politica}">${politica.descripcion_politica}</option>`;
                            });
                        } else {
                            options = '<option value="">No hay políticas asignadas</option>';
                        }
                        politicaSelect.html(options);
                    },
                    error: function() {
                        console.error('Error cargando políticas públicas');
                        politicaSelect.html('<option value="">Error al cargar políticas</option>');
                    }
                });
            } else {
                politicaSelect.html('<option value="">Primero seleccione una acción...</option>').prop('disabled', true);
            }
        });

        // Función para buscar y agregar persona
        function buscarYAgregarPersona() {
            const cedula = $('#buscar_cedula_input').val().trim();

            if (!cedula) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cédula vacía',
                    text: 'Por favor ingrese un número de cédula'
                });
                return;
            }

            // Verificar si ya está agregada
            if (cedulasAgregadas.some(item => item.cedula === cedula)) {
                Swal.fire({
                    icon: 'info',
                    title: 'Persona ya agregada',
                    text: 'Esta cédula ya está en la lista'
                });
                return;
            }

            // Buscar en la base de datos
            $.ajax({
                url: 'buscarPersonaCM.php',
                type: 'POST',
                data: { cedula: cedula },
                dataType: 'json',
                success: function(response) {
                    if (!response.encontrada) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Persona no encontrada',
                            text: 'La cédula ' + cedula + ' no está registrada en Colombia Mayor'
                        });
                    } else {
                        // Agregar a la lista
                        cedulasAgregadas.push({
                            cedula: cedula,
                            nombre_completo: response.nombre_completo
                        });

                        actualizarListaCedulas();
                        $('#buscar_cedula_input').val('').focus();

                        Swal.fire({
                            icon: 'success',
                            title: 'Persona agregada',
                            text: response.nombre_completo,
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
        }

        // Función para actualizar la lista visual de cédulas
        function actualizarListaCedulas() {
            const container = $('#cedulas_container');
            const lista = $('#cedulas_list');
            const contador = $('#contador_cedulas');

            if (cedulasAgregadas.length === 0) {
                container.hide();
                return;
            }

            container.show();
            contador.html(`<i class="bi bi-people-fill"></i> ${cedulasAgregadas.length} persona(s) agregada(s)`);

            let html = '';
            cedulasAgregadas.forEach((item, index) => {
                html += `
                    <div class="cedula-item">
                        <div class="cedula-info">
                            <div class="cedula-numero">${item.cedula}</div>
                            <div class="cedula-nombre">${item.nombre_completo}</div>
                        </div>
                        <button type="button" class="btn-remove-cedula" onclick="removerCedula(${index})">
                            <i class="bi bi-trash"></i> Quitar
                        </button>
                    </div>
                `;
            });

            lista.html(html);

            // Actualizar hidden input con las cédulas en formato JSON
            $('#cedulas_hidden').val(JSON.stringify(cedulasAgregadas.map(item => item.cedula)));
        }

        // Función para remover una cédula de la lista
        function removerCedula(index) {
            cedulasAgregadas.splice(index, 1);
            actualizarListaCedulas();
        }

        // Permitir agregar con Enter
        $('#buscar_cedula_input').keypress(function(e) {
            if (e.which == 13) {
                e.preventDefault();
                buscarYAgregarPersona();
            }
        });

        // Validar formulario antes de enviar
        $('#formNewRegistro').submit(function(e) {
            if (cedulasAgregadas.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin personas agregadas',
                    text: 'Debe agregar al menos una persona para crear el registro'
                });
                return false;
            }
        });

        // Limpiar el modal al cerrarlo
        $('#modalNewRegistro').on('hidden.bs.modal', function() {
            cedulasAgregadas = [];
            actualizarListaCedulas();
            $('#formNewRegistro')[0].reset();
            $('#buscar_cedula_input').val('');
            $('#id_actividad').prop('disabled', true).html('<option value="">Primero seleccione una meta...</option>');
            $('#id_accion').prop('disabled', true).html('<option value="">Primero seleccione una actividad...</option>');
            $('#id_politica_publica').prop('disabled', true).html('<option value="">Primero seleccione una acción...</option>');
        });
    </script>
</body>
</html>
