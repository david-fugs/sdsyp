<?php
session_start();

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    header("Location: ../../access.php");
    exit();
}

include("../../conexion.php");

$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id'];

// Consultas para selectores
$metas = "SELECT * FROM metas ORDER BY descripcion_meta ASC";
$result_metas = $mysqli->query($metas);
if (!$result_metas) {
    die("Error en la consulta de metas: " . $mysqli->error);
}

// Contar personas activas en Colombia Mayor (excluyendo fallecidos y retirados)
$sql_count = "SELECT COUNT(*) as total 
              FROM personas_colombia_mayor 
              WHERE estado_cm = 'ACTIVO' 
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
            <!-- Información de personas activas -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="info-box text-center">
                        <h3><?= $total_personas ?></h3>
                        <p>Personas Activas en Colombia Mayor</p>
                    </div>
                </div>
            </div>

            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-clipboard-data-fill"></i> Registrar Actividad Masiva</h2>
                <div>
                    <button type="button" class="btn-modern btn-success me-2" onclick="window.location.href='exportRegistrosMasivosCM.php'">
                        <i class="bi bi-file-excel-fill"></i>
                        Exportar Excel
                    </button>
                    <button type="button" class="btn-modern btn-secondary" onclick="window.location.href='../../access.php'">
                        <i class="bi bi-arrow-left-circle-fill"></i>
                        Volver
                    </button>
                </div>
            </div>

            <!-- Formulario de Registro Masivo -->
            <div class="modern-filters" style="background: #fff; padding: 30px; border-radius: 18px; margin-bottom: 30px;">
                <form action="addRegistroMasivoCM.php" method="POST" id="formRegistroMasivo">
                    <div class="row g-3">
                        <!-- Fecha del Registro -->
                        <div class="col-md-4">
                            <label for="fecha_registro" class="form-label fw-bold">Fecha del Registro *</label>
                            <input type="date" class="form-control modern-input" id="fecha_registro" name="fecha_registro" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Meta -->
                        <div class="col-md-4">
                            <label for="meta" class="form-label fw-bold">Meta *</label>
                            <select class="form-select modern-select" id="meta" name="id_meta" required>
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
                            <select class="form-select modern-select" id="actividad" name="id_actividad" required disabled>
                                <option value="">Primero seleccione una meta</option>
                            </select>
                        </div>

                        <!-- Acción -->
                        <div class="col-md-6">
                            <label for="accion" class="form-label fw-bold">Acción *</label>
                            <select class="form-select modern-select" id="accion" name="id_accion" required disabled>
                                <option value="">Primero seleccione una actividad</option>
                            </select>
                        </div>

                        <!-- Política Pública -->
                        <div class="col-md-6">
                            <label for="politica-publica" class="form-label fw-bold">Política Pública *</label>
                            <select class="form-select modern-select" id="politica-publica" name="id_politica_publica" required>
                                <option value="">Seleccione Política Pública...</option>
                            </select>
                        </div>

                        <!-- Cantidad Masculino -->
                        <div class="col-md-4">
                            <label for="cantidad_masculino" class="form-label fw-bold">Cantidad Masculino *</label>
                            <input type="number" class="form-control modern-input" id="cantidad_masculino" name="cantidad_masculino" min="0" value="0" required>
                        </div>

                        <!-- Cantidad Femenino -->
                        <div class="col-md-4">
                            <label for="cantidad_femenino" class="form-label fw-bold">Cantidad Femenino *</label>
                            <input type="number" class="form-control modern-input" id="cantidad_femenino" name="cantidad_femenino" min="0" value="0" required>
                        </div>

                        <!-- Total de Personas (calculado automáticamente) -->
                        <div class="col-md-4">
                            <label for="total_registro" class="form-label fw-bold">Total Registrados</label>
                            <input type="number" class="form-control modern-input" id="total_registro" name="total_personas" readonly style="background-color: #e9ecef; font-weight: bold; font-size: 18px; color: #0d6efd;" value="0">
                        </div>

                        <!-- Info: Total de Personas Activas -->
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-info-circle-fill me-2" style="font-size: 24px;"></i>
                                <div>
                                    <strong>Información:</strong> Hay <strong><?= $total_personas ?></strong> personas activas en Colombia Mayor. 
                                    Los campos de cantidad son independientes y debes ingresarlos manualmente.
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-12">
                            <label for="observaciones" class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control modern-input" id="observaciones" name="observaciones" rows="3" placeholder="Ingrese observaciones adicionales..."></textarea>
                        </div>

                        <!-- Botón de envío -->
                        <div class="col-md-12 text-center mt-4">
                            <button type="submit" class="btn-modern btn-success btn-lg">
                                <i class="bi bi-save-fill"></i> Registrar Actividad Masiva
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabla de Registros -->
            <div class="modern-filters">
                <h3 class="mb-3"><i class="bi bi-table"></i> Historial de Registros Masivos</h3>
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
            });
        });
    </script>
</body>
</html>
