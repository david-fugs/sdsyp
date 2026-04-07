<?php
session_start();

// Verificar que el usuario tenga acceso (tipo 1, 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    header("Location: ../../access.php");
    exit();
}

include("../../conexion.php");

$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id'];

// Obtener lista de usuarios según permisos
$usuarios_filtro = [];
if ($tipo_usuario == 1) {
    // Tipo 1 (Admin) puede ver todos
    $query_usuarios = "SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (8, 9) ORDER BY nombre ASC";
} elseif ($tipo_usuario == 8) {
    // Tipo 8 puede filtrar por usuarios tipo 8 y 9
    $query_usuarios = "SELECT id, nombre FROM usuarios WHERE tipo_usuario IN (8, 9) ORDER BY nombre ASC";
} elseif ($tipo_usuario == 9) {
    // Tipo 9 solo ve sus propios registros (no necesita filtro de usuario)
    $query_usuarios = "SELECT id, nombre FROM usuarios WHERE id = $id_usuario";
} else {
    $query_usuarios = "SELECT id, nombre FROM usuarios WHERE id = $id_usuario";
}
$result_usuarios_filtro = $mysqli->query($query_usuarios);
if ($result_usuarios_filtro) {
    while($u = $result_usuarios_filtro->fetch_assoc()) {
        $usuarios_filtro[] = $u;
    }
}

// Consultas para selectores
$metas = "SELECT * FROM metas ORDER BY descripcion_meta ASC";
$result_metas = $mysqli->query($metas);
if (!$result_metas) {
    die("Error en la consulta de metas: " . $mysqli->error);
}

// Contar personas disponibles en Colombia Mayor (excluyendo fallecidos y retirados)
$sql_count = "SELECT COUNT(*) as total 
              FROM personas_colombia_mayor 
              WHERE estado_cm IN ('ACTIVO', 'POTENCIAL_BENEFICIARIO', 'INSCRITO') 
              AND (condicion_componente IS NULL 
                   OR condicion_componente NOT IN ('C.M Fallecido', 'C.M Fallecido sin Certificado', 'C.M Retiro Definitivo'))";
$result_count = $mysqli->query($sql_count);
$total_personas = 0;
if ($result_count) {
    $row_count = $result_count->fetch_assoc();
    $total_personas = $row_count['total'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registros Masivos - Colombia Mayor</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        .filter-group label {
            font-size: 14px !important;
            font-weight: 600 !important;
        }

        .btn-modern {
            font-size: 15px !important;
            padding: 10px 20px !important;
        }

        .modern-header h2 {
            font-size: 26px !important;
        }
        
        .info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .info-box h3 {
            font-size: 48px;
            font-weight: bold;
            margin: 0;
        }
        
        .info-box p {
            margin: 5px 0 0 0;
            font-size: 18px;
        }

        /* Estilos para lista de cédulas seleccionadas */
        .selected-persons-list {
            max-height: 300px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
        }

        .person-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .person-item strong {
            color: #667eea;
        }

        .btn-remove-person {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-remove-person:hover {
            background: #c82333;
        }
    </style>
</head>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #667eea; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;">
        <b><i class="bi bi-clipboard-data-fill"></i> REGISTROS MASIVOS - COLOMBIA MAYOR</b>
    </h1>

    <div class="container mt-5">
        <div class="modern-container">

            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-clipboard-data-fill"></i> Historial de Registros Masivos</h2>
                <div>
                    <button type="button" class="btn-modern btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalRegistroMasivo">
                        <i class="bi bi-plus-circle-fill"></i>
                        Registrar Actividad Masiva
                    </button>
                    <button type="button" class="btn-modern btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalExportarExcel">
                        <i class="bi bi-file-excel-fill"></i>
                        Exportar Excel
                    </button>
                    <button type="button" class="btn-modern btn-secondary" onclick="window.location.href='../../access.php'">
                        <i class="bi bi-arrow-left-circle-fill"></i>
                        Volver
                    </button>
                </div>
            </div>

            <!-- Filtros de Tabla -->
            <div class="modern-filters">
                <h3 class="mb-3"><i class="bi bi-filter"></i> Filtrar Registros</h3>
                <div class="filter-row mb-3">
                    <div class="filter-group">
                        <label for="filter-fecha-desde">Desde</label>
                        <input type="date" id="filter-fecha-desde" class="modern-input">
                    </div>
                    <div class="filter-group">
                        <label for="filter-fecha-hasta">Hasta</label>
                        <input type="date" id="filter-fecha-hasta" class="modern-input">
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
            <div class="modern-table-wrapper" style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(102,126,234,0.08); padding: 24px; overflow-x: auto;">
                <table class="table table-striped table-hover align-middle" id="registrosTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Meta</th>
                            <th>Actividad</th>
                            <th>Acción</th>
                            <th>Política Pública</th>
                            <th class="text-center">Masculino</th>
                            <th class="text-center">Femenino</th>
                            <th class="text-center">Total</th>
                            <th>Registrado Por</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Los datos se cargarán aquí -->
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="5" class="text-end"><strong>TOTALES:</strong></th>
                            <th class="text-center"><span id="total-masculino" class="badge bg-info fs-6">0</span></th>
                            <th class="text-center"><span id="total-femenino" class="badge bg-warning fs-6">0</span></th>
                            <th class="text-center"><span id="total-general" class="badge bg-primary fs-6">0</span></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Registro Masivo -->
    <div class="modal fade" id="modalRegistroMasivo" tabindex="-1" aria-labelledby="modalRegistroMasivoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title" id="modalRegistroMasivoLabel">
                        <i class="bi bi-clipboard-data-fill me-2"></i>Registrar Actividad Masiva - Colombia Mayor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="addRegistroMasivoCM.php" method="POST" id="formRegistroMasivo" enctype="multipart/form-data">
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row g-3">
                            <!-- Fecha del Registro -->
                            <div class="col-md-4">
                                <label for="fecha_registro" class="form-label fw-bold">Fecha del Registro *</label>
                                <input type="date" class="form-control" id="fecha_registro" name="fecha_registro" required value="<?= date('Y-m-d') ?>">
                            </div>

                            <!-- Tipo Actividad -->
                            <div class="col-md-4">
                                <label for="tipo_actividad" class="form-label fw-bold">Tipo Actividad *</label>
                                <select class="form-select" id="tipo_actividad" name="tipo_actividad" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Articulacion">Articulación</option>
                                    <option value="Masiva">Masiva</option>
                                    <option value="Registro de Actividad">Registro de Actividad</option>
                                </select>
                            </div>

                            <!-- Espacio -->
                            <div class="col-md-4"></div>

                            <!-- Meta -->
                            <div class="col-md-4">
                                <label for="meta" class="form-label fw-bold">Meta *</label>
                                <select class="form-select" id="meta" name="id_meta" required>
                                    <option value="">Seleccione Meta...</option>
                                    <?php 
                                    $result_metas->data_seek(0);
                                    while ($meta = $result_metas->fetch_assoc()) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Actividad -->
                            <div class="col-md-4">
                                <label for="actividad" class="form-label fw-bold">Actividad *</label>
                                <select class="form-select" id="actividad" name="id_actividad" required disabled>
                                    <option value="">Primero seleccione una meta</option>
                                </select>
                            </div>

                            <!-- Acción -->
                            <div class="col-md-4">
                                <label for="accion" class="form-label fw-bold">Acción *</label>
                                <select class="form-select" id="accion" name="id_accion" required disabled>
                                    <option value="">Primero seleccione una actividad</option>
                                </select>
                            </div>

                            <!-- Política Pública -->
                            <div class="col-md-12">
                                <label for="politica-publica" class="form-label fw-bold">Política Pública *</label>
                                <select class="form-select" id="politica-publica" name="id_politica_publica" required>
                                    <option value="">Seleccione Política Pública...</option>
                                </select>
                            </div>

                            <!-- Cantidad Masculino -->
                            <div class="col-md-4">
                                <label for="cantidad_masculino" class="form-label fw-bold">Cantidad Masculino *</label>
                                <input type="number" class="form-control" id="cantidad_masculino" name="cantidad_masculino" min="0" value="0" required>
                            </div>

                            <!-- Cantidad Femenino -->
                            <div class="col-md-4">
                                <label for="cantidad_femenino" class="form-label fw-bold">Cantidad Femenino *</label>
                                <input type="number" class="form-control" id="cantidad_femenino" name="cantidad_femenino" min="0" value="0" required>
                            </div>

                            <!-- Total de Personas -->
                            <div class="col-md-4">
                                <label for="total_registro" class="form-label fw-bold">Total Registrados</label>
                                <input type="number" class="form-control" id="total_registro" name="total_personas" readonly style="background-color: #e9ecef; font-weight: bold; color: #0d6efd;" value="0">
                            </div>

                            <!-- Sección Personas (visible solo si Tipo Actividad = Registro de Actividad) -->
                            <div id="seccion-personas" class="col-md-12 d-none">
                                <hr class="my-4">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    <strong>Importante:</strong> Debe seleccionar exactamente la misma cantidad de personas que indicó en "Cantidad Masculino" + "Cantidad Femenino". 
                                    <span id="contador-seleccionadas" class="badge bg-primary ms-2">0 seleccionadas</span>
                                </div>
                                
                                <h6 class="mb-3 text-primary">
                                    <i class="bi bi-people-fill"></i> Seleccionar Personas de Colombia Mayor
                                    <small class="text-muted">(Requerido para Registro de Actividad)</small>
                                </h6>

                                <!-- Filtro de búsqueda rápida -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <input type="text" 
                                               class="form-control" 
                                               id="filtro_cedula_personas" 
                                               placeholder="Filtrar por cédula...">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" 
                                               class="form-control" 
                                               id="filtro_nombre_personas" 
                                               placeholder="Filtrar por nombre...">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-secondary w-100" id="btn-limpiar-seleccion">
                                            <i class="bi bi-x-circle"></i> Limpiar Selección
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabla de personas de Colombia Mayor -->
                                <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px;">
                                    <table class="table table-sm table-hover mb-0" id="tabla-personas-cm">
                                        <thead class="table-dark sticky-top">
                                            <tr>
                                                <th style="width: 50px;">
                                                    <input type="checkbox" id="seleccionar-todas" class="form-check-input">
                                                </th>
                                                <th>Cédula</th>
                                                <th>Nombre Completo</th>
                                                <th>Género</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-personas-cm">
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                                    Cargando personas...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Campo hidden para enviar las cédulas -->
                                <input type="hidden" name="cedulas_json" id="cedulas_json" value="[]">
                            </div>

                            <!-- Observaciones -->
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label fw-bold">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Ingrese observaciones adicionales..."></textarea>
                            </div>

                            <!-- Fotografías -->
                            <div class="col-md-12">
                                <label for="fotografias" class="form-label fw-bold">Fotografías (Máximo 3)</label>
                                <input type="file" 
                                       class="form-control" 
                                       name="fotografias[]" 
                                       id="fotografias" 
                                       accept="image/*" 
                                       capture="environment"
                                       multiple 
                                       onchange="validarFotos(this)">
                                <small class="text-muted">Tamaño máximo por foto: 2MB. Formatos: JPG, PNG, JPEG</small>
                                <div id="preview_fotos" class="mt-3" style="display: flex; gap: 10px; flex-wrap: wrap;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save-fill"></i> Registrar Actividad Masiva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Función para cargar los registros
            function cargarRegistros() {
                $.ajax({
                    url: 'getRegistrosMasivosCM.php',
                    type: 'GET',
                    success: function(data) {
                        $('#table-body').html(data);
                        calcularTotales();
                    },
                    error: function() {
                        $('#table-body').html('<tr><td colspan="10" class="text-center text-danger">Error al cargar registros</td></tr>');
                    }
                });
            }

            // Función para calcular totales de la tabla
            function calcularTotales() {
                let totalMasculino = 0;
                let totalFemenino = 0;
                let totalGeneral = 0;

                // Recorrer todas las filas de la tabla (excepto la fila de "no hay registros")
                $('#table-body tr').each(function() {
                    // Saltar si es la fila de mensaje vacío
                    if ($(this).find('td').length === 1) return;
                    
                    // Obtener valores de los badges
                    const masculino = parseInt($(this).find('td:eq(5) .badge').text()) || 0;
                    const femenino = parseInt($(this).find('td:eq(6) .badge').text()) || 0;
                    const total = parseInt($(this).find('td:eq(7) .badge').text()) || 0;
                    
                    console.log('Fila:', { masculino, femenino, total });
                    
                    totalMasculino += masculino;
                    totalFemenino += femenino;
                    totalGeneral += total;
                });

                console.log('Totales calculados:', { totalMasculino, totalFemenino, totalGeneral });

                // Actualizar los badges de totales
                $('#total-masculino').text(totalMasculino);
                $('#total-femenino').text(totalFemenino);
                $('#total-general').text(totalGeneral);
            }

            // Cargar registros al iniciar
            cargarRegistros();

            // Calcular total del formulario automáticamente
            function calcularTotal() {
                const masculino = parseInt($('#cantidad_masculino').val()) || 0;
                const femenino = parseInt($('#cantidad_femenino').val()) || 0;
                const total = masculino + femenino;
                $('#total_registro').val(total);
            }

            // Calcular cuando cambien los valores
            $('#cantidad_masculino, #cantidad_femenino').on('input', function() {
                calcularTotal();
            });

            // Cascada Meta → Actividad → Acción → Política Pública
            $('#meta').on('change', function() {
                const idMeta = $(this).val();
                console.log('Meta seleccionada:', idMeta);

                $('#actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

                if (idMeta) {
                    console.log('Haciendo petición AJAX para actividades...');
                    $.ajax({
                        url: 'getActividades.php',
                        type: 'POST',
                        data: { id_meta: idMeta },
                        success: function(response) {
                            console.log('Respuesta de actividades:', response);
                            if (response && response.trim() !== '') {
                                $('#actividad').html('<option value="">Seleccione Actividad...</option>' + response).prop('disabled', false);
                            } else {
                                $('#actividad').html('<option value="">No hay actividades disponibles</option>');
                                Swal.fire('Aviso', 'No hay actividades disponibles para esta meta', 'info');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar actividades:', error);
                            console.error('Status:', status);
                            console.error('Response:', xhr.responseText);
                            Swal.fire('Error', 'Error al cargar las actividades: ' + error, 'error');
                        }
                    });
                }
            });

            $('#actividad').on('change', function() {
                const idActividad = $(this).val();
                console.log('Actividad seleccionada:', idActividad);
                
                $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

                if (idActividad) {
                    console.log('Haciendo petición AJAX para acciones...');
                    $.ajax({
                        url: 'getAcciones.php',
                        type: 'POST',
                        data: { id_actividad: idActividad },
                        success: function(response) {
                            console.log('Respuesta de acciones:', response);
                            if (response && response.trim() !== '') {
                                $('#accion').html('<option value="">Seleccione Acción...</option>' + response).prop('disabled', false);
                            } else {
                                $('#accion').html('<option value="">No hay acciones disponibles</option>');
                                Swal.fire('Aviso', 'No hay acciones disponibles para esta actividad', 'info');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar acciones:', error);
                            console.error('Response:', xhr.responseText);
                            Swal.fire('Error', 'Error al cargar las acciones: ' + error, 'error');
                        }
                    });
                }
            });

            $('#accion').on('change', function() {
                const idAccion = $(this).val();
                console.log('Acción seleccionada:', idAccion);
                
                $('#politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

                if (idAccion) {
                    console.log('Haciendo petición AJAX para políticas públicas...');
                    $.ajax({
                        url: 'getPoliticaPublica.php',
                        type: 'POST',
                        data: { id_accion: idAccion },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Respuesta de políticas públicas:', response);
                            if (response && response.politicas && response.politicas.length > 0) {
                                response.politicas.forEach(function(p) {
                                    $('#politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                });
                            } else {
                                $('#politica-publica').append('<option value="">No hay políticas asignadas</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar políticas públicas:', error);
                            console.error('Response:', xhr.responseText);
                            $('#politica-publica').append('<option value="">Error al consultar</option>');
                        }
                    });
                }
            });

            // Validación del formulario
            $('#formRegistroMasivo').on('submit', function(e) {
                const totalRegistro = parseInt($('#total_registro').val()) || 0;
                if (totalRegistro === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cantidad requerida',
                        text: 'Debe ingresar al menos una persona (masculino o femenino).',
                        confirmButtonText: 'Entendido'
                    });
                    return false;
                }

                // Validar si es "Registro de Actividad"
                const tipoActividad = $('#tipo_actividad').val();
                if (tipoActividad === 'Registro de Actividad') {
                    const totalEsperado = totalRegistro;
                    
                    if (personasSeleccionadas.length !== totalEsperado) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Cantidad incorrecta',
                            text: `Debe seleccionar exactamente ${totalEsperado} personas. Actualmente tiene ${personasSeleccionadas.length} seleccionadas.`,
                            confirmButtonText: 'Entendido'
                        });
                        return false;
                    }
                }
            });

            // Mostrar/ocultar sección de personas según el tipo de actividad
            $('#tipo_actividad').on('change', function() {
                const tipoSeleccionado = $(this).val();
                if (tipoSeleccionado === 'Registro de Actividad') {
                    $('#seccion-personas').removeClass('d-none');
                    cargarPersonasColombiaMayor();
                } else {
                    $('#seccion-personas').addClass('d-none');
                    // Limpiar personas seleccionadas
                    personasSeleccionadas = [];
                    $('#cedulas_json').val('[]');
                    actualizarContadorSeleccionadas();
                }
            });

            // Array para almacenar personas seleccionadas
            let personasSeleccionadas = [];
            let todasLasPersonas = [];

            // Función para cargar todas las personas de Colombia Mayor
            function cargarPersonasColombiaMayor() {
                $.ajax({
                    url: 'getPersonasCMParaSeleccion.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.personas) {
                            todasLasPersonas = response.personas;
                            renderizarTablaPersonas(todasLasPersonas);
                        } else {
                            $('#tbody-personas-cm').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar personas</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar personas:', error);
                        $('#tbody-personas-cm').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar personas</td></tr>');
                    }
                });
            }

            // Función para obtener el badge de estado
            function getBadgeEstado(estado) {
                switch (estado) {
                    case 'ACTIVO':
                        return '<span class="badge bg-success">ACTIVO</span>';
                    case 'SUSPENDIDO':
                        return '<span class="badge bg-warning text-dark">SUSPENDIDO</span>';
                    case 'FALLECIDO':
                        return '<span class="badge bg-secondary">FALLECIDO</span>';
                    case 'RETIRO_VOLUNTARIO':
                        return '<span class="badge bg-info text-dark">RETIRO VOLUNTARIO</span>';
                    case 'POTENCIAL_BENEFICIARIO':
                        return '<span class="badge bg-primary">POTENCIAL BENEFICIARIO</span>';
                    case 'INSCRITO':
                        return '<span class="badge bg-primary">INSCRITO</span>';
                    default:
                        return '<span class="badge bg-secondary">' + (estado ? estado.replace(/_/g, ' ') : 'N/A') + '</span>';
                }
            }

            // Función para renderizar la tabla de personas
            function renderizarTablaPersonas(personas) {
                const tbody = $('#tbody-personas-cm');
                tbody.empty();

                if (personas.length === 0) {
                    tbody.html('<tr><td colspan="5" class="text-center text-muted">No hay personas disponibles</td></tr>');
                    return;
                }

                personas.forEach(function(persona) {
                    const isSelected = personasSeleccionadas.includes(persona.cedula);
                    const row = `
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       class="form-check-input checkbox-persona" 
                                       data-cedula="${persona.cedula}"
                                       data-nombre="${persona.nombre_completo}"
                                       ${isSelected ? 'checked' : ''}>
                            </td>
                            <td>${persona.cedula}</td>
                            <td>${persona.nombre_completo}</td>
                            <td>${persona.genero || 'N/A'}</td>
                            <td>${getBadgeEstado(persona.estado)}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Actualizar checkbox "seleccionar todas"
                actualizarCheckboxSelectAll();
            }

            // Manejar selección individual de personas
            $(document).on('change', '.checkbox-persona', function() {
                const cedula = $(this).data('cedula');
                
                if ($(this).is(':checked')) {
                    if (!personasSeleccionadas.includes(cedula)) {
                        personasSeleccionadas.push(cedula);
                    }
                } else {
                    personasSeleccionadas = personasSeleccionadas.filter(c => c !== cedula);
                }

                actualizarContadorSeleccionadas();
                actualizarCheckboxSelectAll();
                $('#cedulas_json').val(JSON.stringify(personasSeleccionadas));
            });

            // Seleccionar/deseleccionar todas
            $('#seleccionar-todas').on('change', function() {
                const isChecked = $(this).is(':checked');
                
                $('.checkbox-persona:visible').each(function() {
                    const cedula = $(this).data('cedula');
                    $(this).prop('checked', isChecked);
                    
                    if (isChecked) {
                        if (!personasSeleccionadas.includes(cedula)) {
                            personasSeleccionadas.push(cedula);
                        }
                    } else {
                        personasSeleccionadas = personasSeleccionadas.filter(c => c !== cedula);
                    }
                });

                actualizarContadorSeleccionadas();
                $('#cedulas_json').val(JSON.stringify(personasSeleccionadas));
            });

            // Actualizar estado del checkbox "seleccionar todas"
            function actualizarCheckboxSelectAll() {
                const totalVisible = $('.checkbox-persona:visible').length;
                const totalChecked = $('.checkbox-persona:visible:checked').length;
                $('#seleccionar-todas').prop('checked', totalVisible > 0 && totalVisible === totalChecked);
            }

            // Actualizar contador de personas seleccionadas
            function actualizarContadorSeleccionadas() {
                $('#contador-seleccionadas').text(personasSeleccionadas.length + ' seleccionadas');
            }

            // Limpiar selección
            $('#btn-limpiar-seleccion').on('click', function() {
                personasSeleccionadas = [];
                $('.checkbox-persona').prop('checked', false);
                $('#seleccionar-todas').prop('checked', false);
                actualizarContadorSeleccionadas();
                $('#cedulas_json').val('[]');
            });

            // Filtros de búsqueda en la tabla
            $('#filtro_cedula_personas, #filtro_nombre_personas').on('input', function() {
                const filtroCedula = $('#filtro_cedula_personas').val().toLowerCase();
                const filtroNombre = $('#filtro_nombre_personas').val().toLowerCase();

                const personasFiltradas = todasLasPersonas.filter(function(persona) {
                    const coincideCedula = !filtroCedula || persona.cedula.toLowerCase().includes(filtroCedula);
                    const coincideNombre = !filtroNombre || persona.nombre_completo.toLowerCase().includes(filtroNombre);
                    return coincideCedula && coincideNombre;
                });

                renderizarTablaPersonas(personasFiltradas);
            });

            // Resetear modal al cerrar
            $('#modalRegistroMasivo').on('hidden.bs.modal', function () {
                $('#formRegistroMasivo')[0].reset();
                personasSeleccionadas = [];
                todasLasPersonas = [];
                $('#cedulas_json').val('[]');
                $('#seccion-personas').addClass('d-none');
                $('#tbody-personas-cm').html('<tr><td colspan="5" class="text-center text-muted">No hay personas cargadas</td></tr>');
                $('#filtro_cedula_personas').val('');
                $('#filtro_nombre_personas').val('');
                $('#seleccionar-todas').prop('checked', false);
                actualizarContadorSeleccionadas();
                $('#actividad').prop('disabled', true).html('<option value="">Primero seleccione una meta</option>');
                $('#accion').prop('disabled', true).html('<option value="">Primero seleccione una actividad</option>');
                $('#politica-publica').html('<option value="">Seleccione Política Pública...</option>');
                $('#preview_fotos').html('');
            });

            // Función para validar fotografías
            window.validarFotos = function(input) {
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
            };
        });
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
                <form id="formFiltrosExportar" method="GET" action="exportRegistrosMasivosCM.php">
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
