<?php
session_start();
include("../../conexion.php");

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el año seleccionado (por defecto el año actual)
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;

// Obtener rango de años disponibles (desde 2020 hasta año actual + 1)
$startYear = 2020;
$endYear = $currentYear + 1;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDSYP - Informes Anuales</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    
    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 y Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SheetJS para exportar Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <style>
        body {
            font-size: 16px !important;
        }
        
        .modern-table {
            font-size: 14px !important;
        }
        
        .modern-table th {
            font-size: 15px !important;
            font-weight: 600 !important;
        }
        
        .modern-table td {
            font-size: 14px !important;
            padding: 10px 8px !important;
        }
        
        .modern-input, .modern-select {
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
        
        .btn-action {
            padding: 8px 12px !important;
            font-size: 14px !important;
        }
        
        .modern-header h2 {
            font-size: 26px !important;
        }
        
        .dataTables_info, .dataTables_paginate {
            font-size: 14px !important;
        }
        
        .report-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .year-selector {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 1rem;
        }
        
        .export-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .stats-summary {
            background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stats-item {
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
</head>

<?php
session_start();
include("../../conexion.php");

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el año seleccionado (por defecto el año actual)
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;

// Obtener rango de años disponibles (desde 2020 hasta año actual + 1)
$startYear = 2020;
$endYear = $currentYear + 1;
?>

<body>
    <!-- Header modernizado -->
    <div class="modern-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <img src='../../img/logo.png' width="60" height="48" class="me-3">
                        <div>
                            <h1 class="header-title mb-0">
                                <i class="bi bi-file-earmark-bar-graph"></i> INFORMES ANUALES
                            </h1>
                            <p class="header-subtitle mb-0">Sistema de Seguimiento y Datos para Personas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="user-info">
                        <i class="bi bi-person-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra superior de navegación -->
    <div class="top-sidebar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav class="top-nav">
                        <a href="../../access.php" class="nav-item" title="Dashboard">
                            <i class="bi bi-house"></i> Inicio
                        </a>
                        <a href="../persons/seePerson.php" class="nav-item" title="Gestión de Personas">
                            <i class="bi bi-people"></i> Personas
                        </a>
                        <a href="../personMovement/seePersonMovement.php" class="nav-item" title="Movimientos">
                            <i class="bi bi-arrow-left-right"></i> Movimientos
                        </a>
                        <a href="seeReports.php" class="nav-item active" title="Informes">
                            <i class="bi bi-file-earmark-bar-graph"></i> Informes
                        </a>
                        <a href="../users/showusers.php" class="nav-item" title="Usuarios">
                            <i class="bi bi-person-gear"></i> Usuarios
                        </a>
                    </nav>
                </div>
                <div class="col-md-4 text-end">
                    <div class="breadcrumb-container">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../../access.php">Inicio</a></li>
                                <li class="breadcrumb-item active">Informes Anuales</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="container mt-4">
        <!-- Selector de año y controles -->
        <div class="controls-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="year-selector">
                        <label class="form-label fw-bold">
                            <i class="bi bi-calendar3"></i> Seleccionar Año:
                        </label>
                        <select id="yearSelect" class="modern-select">
                            <?php for ($year = $endYear; $year >= $startYear; $year--): ?>
                                <option value="<?php echo $year; ?>" <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="export-buttons">
                        <button type="button" id="btnExportExcel" class="export-btn">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </button>
                        <button type="button" id="btnExportPDF" class="export-btn">
                            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                        </button>
                        <button type="button" id="btnPrint" class="export-btn">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas resumidas -->
        <div class="stats-summary" id="statsContainer" style="display: none;">
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-item">
                        <div class="stats-number" id="statsPersonasNuevas">0</div>
                        <div class="stats-label">Personas con Movimientos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-item">
                        <div class="stats-number" id="statsPersonasActivas">0</div>
                        <div class="stats-label">Personas Activas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-item">
                        <div class="stats-number" id="statsTotalMovimientos">0</div>
                        <div class="stats-label">Total Movimientos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-item">
                        <div class="stats-number" id="statsTotalRegistros">0</div>
                        <div class="stats-label">Registros en Informe</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de informe -->
        <div class="table-container">
            <div class="table-responsive">
                <table id="reportsTable" class="modern-table table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Género</th>
                            <th>Fecha Nac.</th>
                            <th>Edad</th>
                            <th>Teléfono</th>
                            <th>Referencia</th>
                            <th>Fecha Registro</th>
                            <th>Centro de Vida</th>
                            <th>Programas</th>
                            <th>Estado Actual</th>
                            <th>Política Pública</th>
                            <th>Movimientos</th>
                            <th>Traslados</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                        <!-- Los datos se cargan dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let currentYear = <?php echo $selectedYear; ?>;
        let currentData = [];
        let currentStats = {};
        let dataTable;

        $(document).ready(function() {
            // Inicializar DataTable
            initializeDataTable();
            
            // Cargar datos iniciales
            loadReportData(currentYear);
            
            // Event listeners
            $('#yearSelect').on('change', function() {
                currentYear = $(this).val();
                loadReportData(currentYear);
            });
            
            $('#btnExportExcel').on('click', exportToExcel);
            $('#btnExportPDF').on('click', exportToPDF);
            $('#btnPrint').on('click', printReport);
        });

        function initializeDataTable() {
            dataTable = $('#reportsTable').DataTable({
                pageLength: 15,
                responsive: true,
                order: [[2, 'asc']], // Ordenar por apellidos
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                columnDefs: [
                    { targets: [0], width: '100px' }, // Cédula
                    { targets: [4, 8], width: '100px' }, // Fechas
                    { targets: [5], width: '60px' }, // Edad
                    { targets: [11], width: '120px' }, // Estado
                    { targets: [13, 14], width: '80px' } // Movimientos y traslados
                ]
            });
        }

        function loadReportData(year) {
            // Mostrar loading
            Swal.fire({
                title: 'Cargando datos...',
                text: 'Generando informe para el año ' + year,
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Cargar estadísticas
            $.get('getReportStats.php', { year: year })
                .done(function(response) {
                    if (response.success) {
                        currentStats = response.stats;
                        updateStatsDisplay();
                    }
                })
                .fail(function() {
                    console.error('Error al cargar estadísticas');
                });

            // Cargar datos detallados
            $.get('getReportData.php', { year: year })
                .done(function(response) {
                    if (response.success) {
                        currentData = response.data;
                        updateTable();
                        updateStatsDisplay();
                        Swal.close();
                        
                        // Mostrar contenedor de estadísticas
                        $('#statsContainer').show();
                        
                        // Actualizar contador de registros
                        $('#statsTotalRegistros').text(response.total_registros);
                    } else {
                        Swal.fire('Error', response.error || 'Error al cargar datos', 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Error de conexión al cargar datos', 'error');
                });
        }

        function updateStatsDisplay() {
            if (currentStats) {
                $('#statsPersonasNuevas').text(currentStats.personas_nuevas || 0);
                $('#statsPersonasActivas').text(currentStats.personas_activas || 0);
                $('#statsTotalMovimientos').text(currentStats.total_movimientos || 0);
            }
        }

        function updateTable() {
            // Limpiar tabla
            dataTable.clear();

            // Agregar nuevos datos
            currentData.forEach(function(persona) {
                const estadoBadge = getEstadoBadge(persona.estado_actual);
                
                dataTable.row.add([
                    persona.cedula_persona,
                    persona.nombres_persona,
                    persona.apellidos_persona,
                    persona.genero_persona,
                    persona.fecha_nacimiento || 'No registrada',
                    persona.edad_actual ? persona.edad_actual + ' años' : 'N/A',
                    persona.telefono_persona || '',
                    persona.referencia_persona || '',
                    persona.fecha_registro,
                    persona.centro_vida,
                    persona.programas,
                    estadoBadge,
                    persona.descripcion_politica,
                    '<span class="badge bg-primary">' + persona.movimientos_en_year + '</span>',
                    '<span class="badge bg-info">' + persona.traslados_en_year + '</span>'
                ]);
            });

            // Redibujar tabla
            dataTable.draw();
        }

        function getEstadoBadge(estado) {
            const badgeMap = {
                'ACTIVO': '<span class="status-badge status-active"><i class="bi bi-check-circle-fill"></i> ACTIVO</span>',
                'EVADIDO': '<span class="status-badge status-warning"><i class="bi bi-exclamation-triangle-fill"></i> EVADIDO</span>',
                'FALLECIDO': '<span class="status-badge status-secondary"><i class="bi bi-x-circle-fill"></i> FALLECIDO</span>',
                'RETIRADO VOLUNTARIO': '<span class="status-badge status-info"><i class="bi bi-arrow-left-circle-fill"></i> RETIRADO</span>',
                'TRASLADADO': '<span class="status-badge status-info"><i class="bi bi-arrow-right-circle-fill"></i> TRASLADADO</span>'
            };
            
            return badgeMap[estado] || '<span class="status-badge status-active">' + estado + '</span>';
        }

        function exportToExcel() {
            if (currentData.length === 0) {
                Swal.fire('Advertencia', 'No hay datos para exportar', 'warning');
                return;
            }

            // Crear workbook
            const wb = XLSX.utils.book_new();

            // Hoja de datos detallados
            const wsData = [];
            
            // Headers
            wsData.push([
                'Cédula', 'Nombres', 'Apellidos', 'Género', 'Fecha Nacimiento', 'Edad',
                'Teléfono', 'Referencia', 'Fecha Registro', 'Centro de Vida', 'Programas',
                'Estado Actual', 'Fecha Último Estado', 'Política Pública', 
                'Movimientos en ' + currentYear, 'Traslados en ' + currentYear, 'Último Centro Traslado'
            ]);

            // Datos
            currentData.forEach(function(persona) {
                wsData.push([
                    persona.cedula_persona,
                    persona.nombres_persona,
                    persona.apellidos_persona,
                    persona.genero_persona,
                    persona.fecha_nacimiento || 'No registrada',
                    persona.edad_actual || 'N/A',
                    persona.telefono_persona || '',
                    persona.referencia_persona || '',
                    persona.fecha_registro,
                    persona.centro_vida,
                    persona.programas,
                    persona.estado_actual,
                    persona.fecha_ultimo_estado || '',
                    persona.descripcion_politica,
                    persona.movimientos_en_year,
                    persona.traslados_en_year,
                    persona.ultimo_centro_traslado
                ]);
            });

            const ws1 = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws1, "Datos Detallados");

            // Hoja de estadísticas
            if (currentStats) {
                const wsStats = [];
                wsStats.push(['ESTADÍSTICAS GENERALES - AÑO ' + currentYear]);
                wsStats.push([]);
                wsStats.push(['Indicador', 'Valor']);
                wsStats.push(['Personas nuevas registradas', currentStats.personas_nuevas || 0]);
                wsStats.push(['Personas activas al final del año', currentStats.personas_activas || 0]);
                wsStats.push(['Total movimientos en el año', currentStats.total_movimientos || 0]);
                wsStats.push([]);
                
                // Estados
                wsStats.push(['PERSONAS POR ESTADO']);
                wsStats.push(['Estado', 'Cantidad']);
                if (currentStats.personas_por_estado) {
                    Object.entries(currentStats.personas_por_estado).forEach(([estado, cantidad]) => {
                        wsStats.push([estado, cantidad]);
                    });
                }
                
                wsStats.push([]);
                
                // Grupos
                wsStats.push(['PERSONAS POR CENTRO DE VIDA']);
                wsStats.push(['Centro de Vida', 'Cantidad']);
                if (currentStats.personas_por_grupo) {
                    currentStats.personas_por_grupo.forEach(grupo => {
                        wsStats.push([grupo.descripcion_grupo, grupo.cantidad]);
                    });
                }

                const ws2 = XLSX.utils.aoa_to_sheet(wsStats);
                XLSX.utils.book_append_sheet(wb, ws2, "Estadísticas");
            }

            // Descargar archivo
            XLSX.writeFile(wb, 'Informe_Anual_' + currentYear + '.xlsx');
            
            Swal.fire('Éxito', 'Archivo Excel exportado correctamente', 'success');
        }

        function exportToPDF() {
            window.open('generatePDF.php?year=' + currentYear, '_blank');
        }

        function printReport() {
            window.print();
        }
    </script>

    <!-- Estilos para impresión -->
    <style media="print">
        .modern-header, .top-sidebar, .controls-section, .export-buttons {
            display: none !important;
        }
        
        .container {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .table {
            font-size: 12px !important;
        }
        
        .stats-summary {
            break-inside: avoid;
            margin-bottom: 20px !important;
        }
        
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
    </style>
</body>
</html>
