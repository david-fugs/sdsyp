<?php
// Frontend temporal para probar con endpoints simplificados
include("../../access.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes Anuales - Prueba</title>
    <link rel="icon" type="image/x-icon" href="../../img/logo_peq.png">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estilos2024.css">
    <link rel="stylesheet" href="../../menu/style.css">
    
    <style>
        .main-content {
            margin-left: 280px;
            margin-top: 70px;
            padding: 20px;
            min-height: calc(100vh - 70px);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<!-- Navegación superior -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); height: 70px;">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">
            <img src="../../img/logo_peq.png" alt="Logo" height="40" class="d-inline-block align-text-top me-2">
            SDSYP - Informes Anuales (Prueba)
        </span>
        
        <div class="d-flex align-items-center">
            <span class="me-3 text-light">
                <i class="fas fa-user-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?>
            </span>
            <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Salir
            </a>
        </div>
    </div>
</nav>

<!-- Menú lateral -->
<?php include("../../menu/menu.php"); ?>

<!-- Contenido principal -->
<div class="main-content">
    <div class="container-fluid">
        
        <!-- Header del módulo -->
        <div class="row">
            <div class="col-12">
                <div class="page-header mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="page-title">
                                <i class="fas fa-chart-bar text-primary me-2"></i>
                                Informes Anuales (Prueba)
                            </h2>
                            <p class="text-muted">Consulta y exporta información detallada por año</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de año -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            <h5 class="card-title mb-0">Seleccionar Año</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <select id="yearSelect" class="form-select">
                                    <option value="">Seleccione un año...</option>
                                    <?php
                                    $currentYear = date('Y');
                                    for ($year = $currentYear; $year >= 2020; $year--) {
                                        echo "<option value='$year'>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button id="btnConsultar" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Consultar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div id="statsContainer" class="row mb-4" style="display: none;">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3 id="statsPersonasActivas">0</h3>
                    <p>Personas Activas</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                    <h3 id="statsPersonasNuevas">0</h3>
                    <p>Con Movimientos</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-exchange-alt fa-2x mb-2"></i>
                    <h3 id="statsTotalMovimientos">0</h3>
                    <p>Total Movimientos</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h3 id="statsTotalRegistros">0</h3>
                    <p>Total Registros</p>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div id="actionsContainer" class="row mb-3" style="display: none;">
            <div class="col-12">
                <div class="d-flex gap-2">
                    <button id="btnExportExcel" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i>Exportar Excel
                    </button>
                    <button id="btnExportPDF" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-1"></i>Exportar PDF
                    </button>
                    <button id="btnPrint" class="btn btn-info">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div id="tableContainer" class="row" style="display: none;">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2"></i>
                            Datos Detallados - Año <span id="selectedYearDisplay"></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reportsTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
                                        <th>Género</th>
                                        <th>Fecha Nacimiento</th>
                                        <th>Centro de Vida</th>
                                        <th>Política Pública</th>
                                        <th>Fecha Registro</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../menu/script.js"></script>

<script>
let dataTable;
let currentData = [];
let currentStats = {};

$(document).ready(function() {
    // Inicializar DataTable
    dataTable = $('#reportsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 15,
        responsive: true,
        order: [[2, 'asc']]
    });

    // Event handlers
    $('#btnConsultar').click(function() {
        const year = $('#yearSelect').val();
        if (!year) {
            Swal.fire('Atención', 'Por favor seleccione un año', 'warning');
            return;
        }
        loadReportData(year);
    });

    $('#yearSelect').change(function() {
        const year = $(this).val();
        if (year) {
            $('#selectedYearDisplay').text(year);
        }
    });
});

function loadReportData(year) {
    // Mostrar loading
    Swal.fire({
        title: 'Cargando datos...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Cargar estadísticas
    $.get('getReportStats_simple.php', { year: year })
        .done(function(response) {
            console.log('Stats response:', response);
            if (response.success) {
                currentStats = response.stats;
                updateStatsDisplay();
            } else {
                console.error('Error en estadísticas:', response.error);
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error al cargar estadísticas:', status, error);
        });

    // Cargar datos detallados
    $.get('getReportData_simple.php', { year: year })
        .done(function(response) {
            console.log('Data response:', response);
            if (response.success) {
                currentData = response.data;
                updateTable();
                updateStatsDisplay();
                Swal.close();
                
                // Mostrar contenedores
                $('#statsContainer').show();
                $('#actionsContainer').show();
                $('#tableContainer').show();
                
                // Actualizar contador de registros
                $('#statsTotalRegistros').text(response.total_registros);
            } else {
                Swal.fire('Error', response.error || 'Error al cargar datos', 'error');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error al cargar datos:', xhr.responseText);
            Swal.fire('Error', 'Error de conexión al cargar datos: ' + error, 'error');
        });
}

function updateStatsDisplay() {
    if (currentStats) {
        $('#statsPersonasActivas').text(currentStats.personas_activas || 0);
        $('#statsPersonasNuevas').text(currentStats.personas_nuevas || 0);
        $('#statsTotalMovimientos').text(currentStats.total_movimientos || 0);
    }
}

function updateTable() {
    // Limpiar tabla
    dataTable.clear();

    // Agregar nuevos datos
    currentData.forEach(function(persona) {
        dataTable.row.add([
            persona.cedula_persona,
            persona.nombres_persona,
            persona.apellidos_persona,
            persona.genero_persona,
            persona.fecha_nacimiento,
            persona.centro_vida,
            persona.descripcion_politica,
            persona.fecha_registro
        ]);
    });

    dataTable.draw();
}
</script>

</body>
</html>
