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

        .btn-action {
            padding: 8px 12px !important;
            font-size: 14px !important;
        }

        .modern-header h2 {
            font-size: 26px !important;
        }

        .dataTables_info,
        .dataTables_paginate {
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

        /* Estilos adicionales para mejorar la experiencia */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .status-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        .status-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .status-info {
            background: linear-gradient(135deg, #17a2b8, #007bff);
            color: white;
        }

        /* Animaciones para botones */
        .export-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .export-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .export-btn:hover::before {
            left: 100%;
        }

        .export-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        /* Mejoras para DataTables */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(135deg, #007bff, #0056b3) !important;
            color: white !important;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .export-buttons {
                flex-direction: column;
                gap: 5px;
            }

            .stats-summary .row .col-md-3 {
                margin-bottom: 15px;
            }

            .year-selector {
                text-align: center;
            }
        }

        /* Loading spinner personalizado */
        .custom-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* ========== NAVEGACIÓN MODERNA ========== */
        .modern-navigation {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            padding: 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 15px 15px;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            flex-wrap: wrap;
        }

        .main-nav {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-weight: 500;
            font-size: 14px;
            min-width: 120px;
            justify-content: center;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .nav-link.active {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .nav-link.active:hover {
            background: linear-gradient(135deg, #2980b9, #1f4e79);
            transform: translateY(-2px);
        }

        .nav-icon {
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-text {
            font-size: 13px;
            font-weight: 600;
        }

        /* Breadcrumb moderno */
        .breadcrumb-section {
            margin-left: auto;
        }

        .breadcrumb-modern {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }

        .breadcrumb-modern li {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            gap: 5px;
        }

        .breadcrumb-modern li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .breadcrumb-modern li a:hover {
            color: white;
        }

        .breadcrumb-modern li.active {
            color: white;
            font-weight: 600;
        }

        /* ========== BOTONES DE VUELTA AL DASHBOARD ========== */
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 15px 30px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
            position: relative;
            overflow: hidden;
            min-width: 250px;
            justify-content: center;
        }

        .btn-back-home::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-back-home:hover::before {
            left: 100%;
        }

        .btn-back-home:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(231, 76, 60, 0.4);
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
        }

        .btn-back-home:active {
            transform: translateY(-1px);
        }

        .btn-back-home i {
            font-size: 18px;
        }

        /* ========== RESPONSIVE DESIGN ========== */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }

            .main-nav {
                justify-content: center;
                width: 100%;
            }

            .nav-link {
                flex-direction: column;
                gap: 5px;
                padding: 10px 15px;
                min-width: 90px;
                font-size: 12px;
            }

            .nav-text {
                font-size: 11px;
            }

            .breadcrumb-section {
                margin-left: 0;
                width: 100%;
                text-align: center;
            }

            .breadcrumb-modern {
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn-back-home {
                padding: 12px 25px;
                font-size: 15px;
                min-width: 200px;
            }
        }

        @media (max-width: 480px) {
            .main-nav {
                gap: 3px;
            }

            .nav-link {
                min-width: 70px;
                padding: 8px 10px;
            }

            .nav-icon {
                font-size: 14px;
            }

            .nav-text {
                font-size: 10px;
            }

            .btn-back-home {
                padding: 10px 20px;
                font-size: 14px;
                min-width: 180px;
                gap: 8px;
            }

            .btn-back-home i {
                font-size: 16px;
            }
        }

        /* Ocultar navegación en impresión */
        @media print {

            .modern-navigation,
            .btn-back-home {
                display: none !important;
            }
        }

        /* Estilos para modal de debugging */
        .swal-wide {
            max-width: 90% !important;
        }

        .swal2-html-container {
            max-height: 400px !important;
            overflow-y: auto !important;
        }

        /* Estilos mejorados para la tabla de reportes */
        .modern-table thead th {
            background: linear-gradient(135deg, #fff3a0, #ffeaa7) !important;
            color: #2d3436 !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            padding: 15px 8px !important;
            height: 55px !important;
            vertical-align: middle !important;
            border-bottom: 2px solid #fdcb6e !important;
            font-size: 11px !important;
            text-align: center !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .modern-table tbody tr {
            transition: all 0.3s ease;
        }

        .modern-table tbody tr:hover {
            background-color: #f8f9fa !important;
            transform: translateX(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .modern-table tbody td {
            padding: 18px 12px !important;
            vertical-align: middle !important;
            font-size: 13px;
            height: 60px !important;
        }

        /* Hacer todas las filas más altas */
        .modern-table tr {
            height: 60px !important;
        }

        /* Aplicar altura específica a DataTables después de que se inicialice */
        .dataTables_wrapper table tr {
            height: 60px !important;
        }

        .dataTables_wrapper table td {
            padding: 18px 12px !important;
            height: 60px !important;
            line-height: 1.4 !important;
        }

        .dataTables_wrapper table th {
            padding: 18px 12px !important;
            height: 65px !important;
            line-height: 1.2 !important;
        }

        /* Estilos específicos para DataTables */
        #reportsTable_wrapper .dataTables_scrollHead .table thead th {
            background: linear-gradient(135deg, #fff3a0, #ffeaa7) !important;
            color: #2d3436 !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            height: 65px !important;
            padding: 18px 12px !important;
            border-bottom: 2px solid #fdcb6e !important;
        }

        /* Forzar altura en DataTables */
        table.dataTable tbody tr {
            height: 60px !important;
        }

        table.dataTable tbody td {
            padding: 18px 12px !important;
            height: 60px !important;
        }
    </style>
</head>

<?php
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

    <!-- Navegación moderna mejorada -->
    <div class="modern-navigation">
        <div class="container-fluid">
            <div class="nav-container">
                <nav class="main-nav">
                    <a href="../../access.php" class="nav-link" title="Dashboard Principal">
                        <div class="nav-icon">
                            <i class="bi bi-house"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>

                    <a href="../persons/seePerson.php" class="nav-link" title="Gestión de Personas">
                        <div class="nav-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <span class="nav-text">Personas</span>
                    </a>

                    <a href="../personMovement/seePersonMovement.php" class="nav-link" title="Movimientos">
                        <div class="nav-icon">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <span class="nav-text">Movimientos</span>
                    </a>

                    <a href="seeReports.php" class="nav-link active" title="Informes y Reportes">
                        <div class="nav-icon">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                        </div>
                        <span class="nav-text">Informes</span>
                    </a>

                    <a href="../users/showusers.php" class="nav-link" title="Gestión de Usuarios">
                        <div class="nav-icon">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <span class="nav-text">Usuarios</span>
                    </a>
                </nav>

                <div class="breadcrumb-section">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb-modern">
                            <li><a href="../../access.php"><i class="bi bi-house"></i> Inicio</a></li>
                            <li><i class="bi bi-chevron-right"></i></li>
                            <li class="active"><i class="bi bi-file-earmark-bar-graph"></i> Informes Anuales</li>
                        </ol>
                    </nav>
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
                            <th>Centro de Vida</th>
                            <th>Programas</th>
                            <th>Estado Actual</th>
                            <th>Política Pública</th>
                            <th>Movimientos</th>
                            <th>Traslados</th>
                            <th>Activo Desde</th>
                            <th>Activo Hasta</th>
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
                order: [
                    [2, 'asc']
                ], // Ordenar por apellidos
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                columnDefs: [{
                        targets: [0],
                        width: '100px'
                    }, // Cédula
                    {
                        targets: [4, 8],
                        width: '100px'
                    }, // Fechas
                    {
                        targets: [5],
                        width: '60px'
                    }, // Edad
                    {
                        targets: [11],
                        width: '120px'
                    }, // Estado
                    {
                        targets: [13, 14],
                        width: '80px'
                    } // Movimientos y traslados
                ],
                drawCallback: function() {
                    // Forzar altura de filas después de cada redibujado
                    $('#reportsTable tbody tr').css({
                        'height': '60px',
                        'min-height': '60px'
                    });
                    $('#reportsTable tbody td').css({
                        'padding': '18px 12px',
                        'height': '60px',
                        'vertical-align': 'middle'
                    });
                    $('#reportsTable thead th').css({
                        'padding': '18px 12px',
                        'height': '65px',
                        'vertical-align': 'middle'
                    });
                }
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
            $.get('getReportStats.php', {
                    year: year
                })
                .done(function(response) {
                    console.log('Response getReportStats:', response);
                    if (response && response.success) {
                        currentStats = response.stats;
                        updateStatsDisplay();
                    } else {
                        console.error('Error getReportStats.php:', response ? response.error : 'Respuesta inválida');
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error getReportStats:');
                    console.error('Status:', jqXHR.status);
                    console.error('Response:', jqXHR.responseText);
                    console.error('Error:', errorThrown);
                });

            // Cargar datos detallados
            $.get('getReportData.php', {
                    year: year
                })
                .done(function(response, textStatus, jqXHR) {
                    console.log('Response getReportData:', response);
                    console.log('TextStatus:', textStatus);
                    console.log('Response Type:', typeof response);
                    
                    if (response && response.success) {
                        currentData = response.data;
                        updateTable();
                        updateStatsDisplay();
                        Swal.close();

                        // Mostrar contenedor de estadísticas
                        $('#statsContainer').show();

                        // Actualizar contador de registros
                        $('#statsTotalRegistros').text(response.total_registros);
                    } else {
                        // Mostrar error detallado en el modal y en la consola
                        let errorMsg = response && response.error ? response.error : 'Error al cargar datos - Respuesta inválida';
                        console.error('Error getReportData.php:', errorMsg);
                        console.error('Full response:', response);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al cargar datos',
                            html: `
                                <div style="text-align: left;">
                                    <strong>Error:</strong> ${errorMsg}<br><br>
                                    <strong>Para debugging:</strong><br>
                                    • Revisa la consola del navegador (F12)<br>
                                    • Verifica el archivo debug_report_error.php<br>
                                    • Tipo de respuesta: ${typeof response}<br>
                                    • Estado: ${textStatus}
                                </div>
                            `,
                            width: 600
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error Details:');
                    console.error('Status:', jqXHR.status);
                    console.error('Status Text:', jqXHR.statusText);
                    console.error('Response Text:', jqXHR.responseText);
                    console.error('Text Status:', textStatus);
                    console.error('Error Thrown:', errorThrown);
                    
                    let errorDetails = `
                        <div style="text-align: left; font-family: monospace; font-size: 12px;">
                            <strong>Detalles del error AJAX:</strong><br>
                            • Status Code: ${jqXHR.status}<br>
                            • Status Text: ${jqXHR.statusText}<br>
                            • Text Status: ${textStatus}<br>
                            • Error: ${errorThrown}<br><br>
                            
                            <strong>Respuesta del servidor:</strong><br>
                            <div style="max-height: 200px; overflow-y: auto; background: #f5f5f5; padding: 10px; border: 1px solid #ddd;">
                                ${jqXHR.responseText ? jqXHR.responseText.substring(0, 1000) + (jqXHR.responseText.length > 1000 ? '...' : '') : 'Sin respuesta'}
                            </div>
                        </div>
                    `;
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión al cargar datos',
                        html: errorDetails,
                        width: 700,
                        customClass: {
                            popup: 'swal-wide'
                        }
                    });
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

            // Agregar nuevos datos con el nuevo orden (ACTIVO DESDE y ACTIVO HASTA al final)
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
                    persona.centro_vida,
                    persona.programas,
                    estadoBadge,
                    persona.descripcion_politica,
                    '<span class="badge bg-primary">' + persona.movimientos_en_year + '</span>',
                    '<span class="badge bg-info">' + persona.traslados_en_year + '</span>',
                    persona.fecha_registro, // "ACTIVO DESDE"
                    persona.activo_hasta || 'N/A' // "ACTIVO HASTA"
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
                'TRASLADADO': '<span class="status-badge status-info"><i class="bi bi-arrow-right-circle-fill"></i> TRASLADADO</span>',
                'SUSPENDIDO': '<span class="status-badge status-warning"><i class="bi bi-pause-circle-fill"></i> SUSPENDIDO</span>'
            };

            return badgeMap[estado] || '<span class="status-badge status-active"><i class="bi bi-question-circle-fill"></i> ' + estado + '</span>';
        }

        function exportToExcel() {
            // Abrir el generador de Excel directamente para descargar
            window.open('generateExcel.php?year=' + currentYear, '_blank');
        }

        function exportToPDF() {
            window.open('generatePDF.php?year=' + currentYear, '_blank');
        }

        function printReport() {
            window.print();
        }
    </script>

    <!-- Botón de vuelta al inicio - Parte inferior -->
    <div class="container-fluid mt-5 mb-4">
        <div class="row">
            <div class="col-12 text-center">
                <a href="../../access.php" class="btn-back-home">
                    <i class="bi bi-house-door"></i>
                    <span>Volver al Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Estilos para impresión -->
    <style media="print">
        .modern-header,
        .modern-navigation,
        .controls-section,
        .export-buttons,
        .btn-back-home {
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