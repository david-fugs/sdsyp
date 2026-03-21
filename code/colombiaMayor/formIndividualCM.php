<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    header("location: ../../index.php");
    exit();
}

include("../../conexion.php");

$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$nombre_usuario = $_SESSION['usuario'];

// Obtener lista de usuarios según permisos
$usuarios_filtro = [];
if ($tipo_usuario == 1) {
    // Tipo 1 (Admin) puede ver todos
    $query_usuarios = "SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (1, 8, 9) ORDER BY nombre ASC";
} elseif ($tipo_usuario == 8) {
    // Tipo 8 puede filtrar por usuarios tipo 8 y 9
    $query_usuarios = "SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (8, 9) ORDER BY nombre ASC";
} elseif ($tipo_usuario == 9) {
    // Tipo 9 solo ve sus propios registros (no necesita filtro de usuario)
    $query_usuarios = "SELECT id, nombre FROM usuarios WHERE id = $usuario_id";
} else {
    $query_usuarios = "SELECT id, nombre FROM usuarios WHERE id = $usuario_id";
}
$result_usuarios_filtro = $mysqli->query($query_usuarios);
if ($result_usuarios_filtro) {
    while($u = $result_usuarios_filtro->fetch_assoc()) {
        $usuarios_filtro[] = $u;
    }
}

// Consulta para condiciones (filtrar solo las de Colombia Mayor)
$condiciones_sql = "SELECT id_condicion, descripcion_condicion 
                    FROM condiciones_componente 
                    WHERE descripcion_condicion LIKE 'C.M%' 
                    AND id_condicion > 35
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
                    <button type="button" class="btn-modern btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalExportarExcel">
                        <i class="bi bi-file-excel-fill"></i>
                        Exportar Excel
                    </button>
                    <button type="button" class="btn-modern btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalNewRegistro">
                        <i class="bi bi-plus-circle-fill"></i>
                        Agregar Registro
                    </button>
                    <button type="button" class="btn-modern btn-secondary" onclick="window.location.href='../../access.php'">
                        <i class="bi bi-arrow-left-circle-fill"></i>
                        Volver
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

                <form id="formNewRegistro" action="addRegistroCM.php" method="POST" enctype="multipart/form-data">
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

                            <!-- Fotografías -->
                            <div class="col-md-12">
                                <label for="fotografias" class="form-label">Fotografías (Máximo 3)</label>
                                <input type="file" 
                                       class="form-control" 
                                       name="fotografias[]" 
                                       id="fotografias" 
                                       accept="image/*" 
                                       capture="environment"
                                       multiple 
                                       onchange="validarFotos(this)">
                                <small class="text-muted">Tamaño máximo por foto: 2MB. Formatos: JPG, PNG, JPEG</small>
                                <div id="preview_fotos" class="mt-2" style="display: flex; gap: 10px; flex-wrap: wrap;"></div>
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

    <!-- Modal Editar Registro -->
    <div class="modal fade" id="modalEditRegistro" tabindex="-1" aria-labelledby="modalEditRegistroLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="modal-title" id="modalEditRegistroLabel">
                        <i class="bi bi-pencil-square me-2"></i>Editar Registro Individual - Colombia Mayor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formEditRegistro">
                    <input type="hidden" name="id_registro" id="edit_id_registro">
                    <input type="hidden" name="cedula_persona_cm" id="edit_cedula_persona">
                    
                    <div class="modal-body">
                        <h6 class="mb-3"><i class="bi bi-person-badge"></i> Información de la Persona</h6>
                        <div class="alert alert-info">
                            <strong>Cédula:</strong> <span id="edit_cedula_display"></span><br>
                            <strong>Nombre:</strong> <span id="edit_nombre_display"></span>
                        </div>

                        <hr class="my-4">

                        <!-- Datos del Registro -->
                        <h6 class="mb-3"><i class="bi bi-clipboard-data"></i> Información del Registro</h6>
                        
                        <div class="row g-3">
                            <!-- Condición -->
                            <div class="col-md-6">
                                <label for="edit_id_condicion" class="form-label">Condición *</label>
                                <select class="form-select form-select-lg" name="id_condicion" id="edit_id_condicion" required>
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
                                <label for="edit_fecha_registro" class="form-label">Fecha de Registro *</label>
                                <input type="date" 
                                       class="form-control form-control-lg" 
                                       name="fecha_registro_actividad" 
                                       id="edit_fecha_registro" 
                                       required>
                            </div>

                            <!-- Meta -->
                            <div class="col-md-6">
                                <label for="edit_id_meta" class="form-label">Meta *</label>
                                <select class="form-select form-select-lg" name="id_meta" id="edit_id_meta" required>
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
                                <label for="edit_id_actividad" class="form-label">Actividad *</label>
                                <select class="form-select form-select-lg" name="id_actividad" id="edit_id_actividad" required>
                                    <option value="">Primero seleccione una meta...</option>
                                </select>
                            </div>

                            <!-- Acción -->
                            <div class="col-md-6">
                                <label for="edit_id_accion" class="form-label">Acción *</label>
                                <select class="form-select form-select-lg" name="id_accion" id="edit_id_accion" required>
                                    <option value="">Primero seleccione una actividad...</option>
                                </select>
                            </div>

                            <!-- Política Pública -->
                            <div class="col-md-6">
                                <label for="edit_id_politica_publica" class="form-label">Política Pública *</label>
                                <select class="form-select form-select-lg" name="id_politica_publica" id="edit_id_politica_publica" required>
                                    <option value="">Primero seleccione una acción...</option>
                                </select>
                            </div>

                            <!-- Observaciones -->
                            <div class="col-md-12">
                                <label for="edit_observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" 
                                          name="observaciones" 
                                          id="edit_observaciones" 
                                          rows="2" 
                                          placeholder="Observaciones adicionales (opcional)"></textarea>
                            </div>

                            <!-- Fotografías Existentes -->
                            <div class="col-md-12">
                                <label class="form-label">Fotografías Actuales</label>
                                <div id="fotos_existentes" class="d-flex gap-2 flex-wrap mb-3"></div>
                            </div>

                            <!-- Agregar Nuevas Fotografías -->
                            <div class="col-md-12">
                                <label for="edit_fotografias" class="form-label">Agregar Fotografías</label>
                                <input type="file" 
                                       class="form-control" 
                                       id="edit_fotografias" 
                                       accept="image/*" 
                                       capture="environment"
                                       multiple 
                                       onchange="validarFotosEdicion(this)">
                                <small class="text-muted">Tamaño máximo por foto: 2MB. Formatos: JPG, PNG, JPEG. Máximo 3 fotos en total.</small>
                                <div id="edit_preview_fotos" class="mt-2" style="display: flex; gap: 10px; flex-wrap: wrap;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-save"></i> Actualizar Registro
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
            $('#preview_fotos').html('');
        });

        // Función para validar fotografías
        function validarFotos(input) {
            const maxFiles = 3;
            const maxSize = 2 * 1024 * 1024; // 2MB en bytes
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            const files = input.files;
            const previewContainer = document.getElementById('preview_fotos');
            
            // Limpiar preview
            previewContainer.innerHTML = '';
            
            // Validar cantidad de archivos
            if (files.length > maxFiles) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Demasiadas fotos',
                    text: `Solo puedes subir máximo ${maxFiles} fotografías`
                });
                input.value = '';
                return false;
            }
            
            // Validar cada archivo
            let valid = true;
            Array.from(files).forEach((file, index) => {
                // Validar tipo
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Formato no permitido',
                        text: `El archivo ${file.name} no es una imagen válida (JPG, PNG, JPEG)`
                    });
                    valid = false;
                    return;
                }
                
                // Validar tamaño
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo muy grande',
                        text: `El archivo ${file.name} supera los 2MB`
                    });
                    valid = false;
                    return;
                }
                
                // Crear preview
                if (valid) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.style.position = 'relative';
                        div.innerHTML = `
                            <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;">
                            <small style="display: block; text-align: center; margin-top: 5px;">${file.name}</small>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            if (!valid) {
                input.value = '';
                previewContainer.innerHTML = '';
                return false;
            }
            
            return true;
        }

        // ===== FUNCIONES DE EDICIÓN =====
        
        let registroEditando = null;
        
        // Función para abrir modal de edición
        function editarRegistro(id) {
            $.ajax({
                url: 'obtenerRegistroCM.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        registroEditando = id;
                        
                        // Llenar campos del modal
                        $('#edit_id_registro').val(id);
                        $('#edit_cedula_persona').val(data.cedula_persona_cm);
                        $('#edit_cedula_display').text(data.cedula_persona_cm);
                        $('#edit_nombre_display').text(data.nombre_completo);
                        $('#edit_id_condicion').val(data.id_condicion);
                        $('#edit_fecha_registro').val(data.fecha_registro_actividad);
                        $('#edit_observaciones').val(data.observaciones);
                        
                        // Cargar Meta y cascada
                        $('#edit_id_meta').val(data.id_meta);
                        cargarActividadesEdicion(data.id_meta, data.id_actividad, data.id_accion, data.id_politica_publica);
                        
                        // Cargar fotos existentes
                        cargarFotosExistentes(id);
                        
                        // Abrir modal
                        $('#modalEditRegistro').modal('show');
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo cargar el registro', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al cargar el registro', 'error');
                }
            });
        }
        
        // Cargar actividades en modo edición
        function cargarActividadesEdicion(idMeta, idActividad, idAccion, idPolitica) {
            $.ajax({
                url: 'getActividades.php',
                type: 'POST',
                data: { id_meta: idMeta },
                success: function(response) {
                    $('#edit_id_actividad').prop('disabled', false)
                        .html('<option value="">Seleccione una actividad...</option>' + response);
                    $('#edit_id_actividad').val(idActividad);
                    
                    cargarAccionesEdicion(idActividad, idAccion, idPolitica);
                }
            });
        }
        
        // Cargar acciones en modo edición
        function cargarAccionesEdicion(idActividad, idAccion, idPolitica) {
            $.ajax({
                url: 'getAcciones.php',
                type: 'POST',
                data: { id_actividad: idActividad },
                success: function(response) {
                    $('#edit_id_accion').prop('disabled', false)
                        .html('<option value="">Seleccione una acción...</option>' + response);
                    $('#edit_id_accion').val(idAccion);
                    
                    cargarPoliticasEdicion(idAccion, idPolitica);
                }
            });
        }
        
        // Cargar políticas en modo edición
        function cargarPoliticasEdicion(idAccion, idPolitica) {
            $.ajax({
                url: 'getPoliticaPublica.php',
                type: 'POST',
                data: { id_accion: idAccion },
                dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Seleccione una política pública...</option>';
                    if (response && response.politicas && response.politicas.length > 0) {
                        response.politicas.forEach(function(politica) {
                            options += `<option value="${politica.id_politica}">${politica.descripcion_politica}</option>`;
                        });
                    }
                    $('#edit_id_politica_publica').prop('disabled', false).html(options);
                    $('#edit_id_politica_publica').val(idPolitica);
                }
            });
        }
        
        // Cascadas para modo edición
        $('#edit_id_meta').change(function() {
            const metaId = $(this).val();
            $('#edit_id_actividad').html('<option value="">Primero seleccione una meta...</option>').prop('disabled', true);
            $('#edit_id_accion').html('<option value="">Primero seleccione una actividad...</option>').prop('disabled', true);
            $('#edit_id_politica_publica').html('<option value="">Primero seleccione una acción...</option>').prop('disabled', true);
            
            if (metaId) {
                $.ajax({
                    url: 'getActividades.php',
                    type: 'POST',
                    data: { id_meta: metaId },
                    success: function(response) {
                        $('#edit_id_actividad').prop('disabled', false)
                            .html('<option value="">Seleccione una actividad...</option>' + response);
                    }
                });
            }
        });
        
        $('#edit_id_actividad').change(function() {
            const actividadId = $(this).val();
            $('#edit_id_accion').html('<option value="">Primero seleccione una actividad...</option>').prop('disabled', true);
            $('#edit_id_politica_publica').html('<option value="">Primero seleccione una acción...</option>').prop('disabled', true);
            
            if (actividadId) {
                $.ajax({
                    url: 'getAcciones.php',
                    type: 'POST',
                    data: { id_actividad: actividadId },
                    success: function(response) {
                        $('#edit_id_accion').prop('disabled', false)
                            .html('<option value="">Seleccione una acción...</option>' + response);
                    }
                });
            }
        });
        
        $('#edit_id_accion').change(function() {
            const accionId = $(this).val();
            $('#edit_id_politica_publica').html('<option value="">Primero seleccione una acción...</option>').prop('disabled', true);
            
            if (accionId) {
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
                        }
                        $('#edit_id_politica_publica').prop('disabled', false).html(options);
                    }
                });
            }
        });
        
        // Cargar fotos existentes
        function cargarFotosExistentes(idRegistro) {
            $.ajax({
                url: 'obtenerFotos.php',
                type: 'GET',
                data: { id_registro: idRegistro, tipo: 'individual' },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.fotos) {
                        const container = $('#fotos_existentes');
                        container.html('');
                        
                        if (response.fotos.length === 0) {
                            container.html('<p class="text-muted">No hay fotografías</p>');
                        } else {
                            response.fotos.forEach(function(foto) {
                                const fotoHtml = `
                                    <div style="position: relative; display: inline-block;">
                                        <img src="../../${foto.ruta}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;">
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                style="position: absolute; top: -10px; right: -10px; border-radius: 50%; width: 30px; height: 30px; padding: 0;"
                                                onclick="eliminarFoto(${foto.id_foto}, this)">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                `;
                                container.append(fotoHtml);
                            });
                        }
                    }
                }
            });
        }
        
        // Eliminar foto
        function eliminarFoto(idFoto, btn) {
            Swal.fire({
                title: '¿Eliminar fotografía?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'eliminarFoto.php',
                        type: 'POST',
                        data: { id_foto: idFoto },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                $(btn).parent().remove();
                                Swal.fire('Eliminada', 'Fotografía eliminada correctamente', 'success');
                                
                                // Actualizar vista si no quedan fotos
                                if ($('#fotos_existentes').children().length === 0) {
                                    $('#fotos_existentes').html('<p class="text-muted">No hay fotografías</p>');
                                }
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al eliminar la fotografía', 'error');
                        }
                    });
                }
            });
        }
        
        // Validar fotos en edición
        function validarFotosEdicion(input) {
            const maxFiles = 3;
            const maxSize = 2 * 1024 * 1024;
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            const files = input.files;
            const previewContainer = document.getElementById('edit_preview_fotos');
            
            previewContainer.innerHTML = '';
            
            // Contar fotos existentes
            const fotosExistentes = $('#fotos_existentes').children('div').length;
            
            if ((fotosExistentes + files.length) > maxFiles) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite excedido',
                    text: `Ya tienes ${fotosExistentes} foto(s). Solo puedes agregar ${maxFiles - fotosExistentes} más.`
                });
                input.value = '';
                return false;
            }
            
            let valid = true;
            Array.from(files).forEach((file) => {
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire('Error', `${file.name} no es una imagen válida`, 'error');
                    valid = false;
                    return;
                }
                
                if (file.size > maxSize) {
                    Swal.fire('Error', `${file.name} supera los 2MB`, 'error');
                    valid = false;
                    return;
                }
                
                if (valid) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.style.position = 'relative';
                        div.innerHTML = `
                            <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;">
                            <small style="display: block; text-align: center; margin-top: 5px;">${file.name}</small>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            if (!valid) {
                input.value = '';
                previewContainer.innerHTML = '';
                return false;
            }
            
            return true;
        }
        
        // Enviar formulario de edición
        $('#formEditRegistro').submit(function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fotosInput = $('#edit_fotografias')[0];
            
            if (fotosInput.files.length > 0) {
                // Primero actualizar datos del registro
                $.ajax({
                    url: 'actualizarRegistroCM.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Luego subir nuevas fotos si las hay
                            const formDataFotos = new FormData();
                            formDataFotos.append('id_registro', registroEditando);
                            formDataFotos.append('tipo', 'individual');
                            
                            for (let i = 0; i < fotosInput.files.length; i++) {
                                formDataFotos.append('fotografias[]', fotosInput.files[i]);
                            }
                            
                            $.ajax({
                                url: 'agregarFotos.php',
                                type: 'POST',
                                data: formDataFotos,
                                processData: false,
                                contentType: false,
                                dataType: 'json',
                                success: function(fotoResponse) {
                                    $('#modalEditRegistro').modal('hide');
                                    Swal.fire('Actualizado', 'Registro actualizado correctamente', 'success')
                                        .then(() => location.reload());
                                },
                                error: function() {
                                    $('#modalEditRegistro').modal('hide');
                                    Swal.fire('Parcial', 'Registro actualizado pero error al subir fotos', 'warning')
                                        .then(() => location.reload());
                                }
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al actualizar el registro', 'error');
                    }
                });
            } else {
                // Solo actualizar datos del registro
                $.ajax({
                    url: 'actualizarRegistroCM.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modalEditRegistro').modal('hide');
                            Swal.fire('Actualizado', 'Registro actualizado correctamente', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al actualizar el registro', 'error');
                    }
                });
            }
        });
        
        // Función para eliminar registro
        function eliminarRegistro(id) {
            Swal.fire({
                title: '¿Eliminar registro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'deleteRegistroCM.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Eliminado', 'Registro eliminado correctamente', 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al eliminar el registro', 'error');
                        }
                    });
                }
            });
        }
    </script>

    <!-- Modal de Filtros de Exportación -->
    <div class="modal fade" id="modalExportarExcel" tabindex="-1" aria-labelledby="modalExportarExcelLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalExportarExcelLabel">
                        <i class="bi bi-file-excel-fill"></i> Filtros de Exportación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formFiltrosExportar" method="GET" action="exportRegistrosCM.php">
                    <div class="modal-body">
                        <!-- Fecha de inicio -->
                        <div class="mb-3">
                            <label for="filtro_fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="filtro_fecha_inicio" name="filtro_fecha_inicio">
                        </div>
                        
                        <!-- Fecha de fin -->
                        <div class="mb-3">
                            <label for="filtro_fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="filtro_fecha_fin" name="filtro_fecha_fin">
                        </div>
                        
                        <?php if ($tipo_usuario == 1 || $tipo_usuario == 8): ?>
                        <!-- Filtro de usuario (solo para tipo 1 y 8) -->
                        <div class="mb-3">
                            <label for="filtro_usuario" class="form-label">Usuario</label>
                            <select class="form-select" id="filtro_usuario" name="filtro_usuario">
                                <option value="">-- Todos --</option>
                                <?php foreach ($usuarios_filtro as $usuario): ?>
                                    <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-file-excel-fill"></i> Exportar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
