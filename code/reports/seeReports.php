<?php
session_start();
require_once('../filtros_grupos.php');
include("../../conexion.php");

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

// Para usuarios tipo 2, obtener el prefijo del grupo (CV o CPSAM)
$prefijo_grupo = '';
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
if ($tipo_usuario == 2) {
    $id_grupo_usuario = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : 0;
    if ($id_grupo_usuario != 0) {
        $query_grupo = "SELECT descripcion_grupo FROM grupos WHERE id_grupo = ?";
        $stmt = $mysqli->prepare($query_grupo);
        $stmt->bind_param('i', $id_grupo_usuario);
        $stmt->execute();
        $result_grupo = $stmt->get_result();
        if ($row_grupo = $result_grupo->fetch_assoc()) {
            $descripcion = $row_grupo['descripcion_grupo'];
            // Determinar si empieza con CV o CPSAM
            if (stripos($descripcion, 'CV') === 0) {
                $prefijo_grupo = 'CV';
            } elseif (stripos($descripcion, 'CPSAM') === 0) {
                $prefijo_grupo = 'CPSAM';
            }
        }
        $stmt->close();
    }
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
            width: 200px;
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

// Obtener variables de sesión
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

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
    <?php if ($_SESSION['tipo_usuario'] != 10) { ?>
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
    <?php } ?>

    <!-- Contenido principal -->
    <div class="container mt-4">
        <!-- Selector de año y controles -->
        <div class="controls-section">
            <div class="row align-items-center mb-3">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <div class="year-selector">
                        <label class="form-label fw-bold">
                            <i class="bi bi-building"></i> Filtrar por Grupo:
                        </label>
                        <select id="filtroGrupo" class="modern-select">
                            <option value="">Todos los grupos</option>
                            <?php
                            // Obtener grupos filtrados según tipo de usuario
                            $tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
                            $where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');
                            $query_grupos = "SELECT g.* FROM grupos g WHERE 1=1 $where_grupos ORDER BY g.descripcion_grupo ASC";
                            $result_grupos = mysqli_query($mysqli, $query_grupos);
                            if ($result_grupos) {
                                while ($grupo = mysqli_fetch_assoc($result_grupos)) {
                                    echo '<option value="' . $grupo['id_grupo'] . '">' . htmlspecialchars($grupo['descripcion_grupo']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="year-selector">
                        <label class="form-label fw-bold">
                            <i class="bi bi-calendar-month"></i> Filtrar por Mes:
                        </label>
                        <select id="filtroMes" class="modern-select">
                            <option value="">Todos los meses</option>
                            <option value="01">Enero</option>
                            <option value="02">Febrero</option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-md-4">
                    <div class="year-selector">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person-badge"></i> Filtrar por Usuario:
                        </label>
                        <select id="filtroUsuario" class="modern-select">
                            <option value="">Todos los usuarios</option>
                            <?php
                            // Construir filtro WHERE para usuarios según tipo de usuario de sesión
                            $where_usuarios = "WHERE 1=1";
                            
                            // Si es tipo 3 (CONTRATISTA CPSAM), solo mostrar su propio usuario
                            if ($tipo_usuario == 3 && isset($_SESSION['id'])) {
                                $id_usuario_session = intval($_SESSION['id']);
                                $where_usuarios .= " AND u.id = $id_usuario_session";
                            }
                            // Si es tipo 10 (CONTRATISTA CENTRO VIDA), mostrar usuarios del mismo grupo
                            elseif ($tipo_usuario == 10 && $id_grupo_session) {
                                $where_usuarios .= " AND u.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
                            }
                            // Si es tipo 4 o 5 (Técnico/Supervisor), mostrar usuarios del mismo grupo
                            elseif (($tipo_usuario == 4 || $tipo_usuario == 5) && $id_grupo_session) {
                                $where_usuarios .= " AND u.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
                            }
                            
                            $query_usuarios = "SELECT u.id, u.nombre FROM usuarios u $where_usuarios ORDER BY u.nombre ASC";
                            $result_usuarios = mysqli_query($mysqli, $query_usuarios);
                            if ($result_usuarios) {
                                while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
                                    echo '<option value="' . $usuario['id'] . '">' . htmlspecialchars($usuario['nombre']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 text-end offset-md-4">
                    <label class="form-label fw-bold" style="visibility: hidden;">Acciones</label>
                    <div class="export-buttons d-flex flex-column gap-2">
                        <!-- quitar este boton para los usuarios cpsam o centro vida que es el 2 -->
                         <?php if ($tipo_usuario != 2) : ?>
                        <button type="button" id="btnExportExcel" class="export-btn">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Datos Personas
                        </button>
                        <?php endif; ?>
                        <!-- que no le aparezca el boton a el usuario cotnratista cpsam que es el 3 -->
                        <?php if ($tipo_usuario != 3) : ?>
                        <button type="button" id="btnExportMovimientos" class="export-btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-arrow-left-right"></i> Exportar Movimientos
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Nuevos botones de exportación -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 12px;">
                        <div class="card-body p-3">
                            <h5 class="text-white mb-3"><i class="bi bi-download"></i> Exportar Actividades</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php 
                                // Mostrar formulario Contratista solo si NO es tipo 10 (CONTRATISTA CENTRO VIDA)
                                // y si es tipo 2, solo si su grupo NO empieza con CV
                                if ($tipo_usuario != 10 && !($tipo_usuario == 2 && $prefijo_grupo == 'CV')) : 
                                ?>
                                <form id="exportContratistaForm" action="exportContratistaFromReports.php" method="get" style="display:inline;">
                                    <input type="hidden" name="filtro_anio" id="export_contratista_anio">
                                    <input type="hidden" name="filtro_grupo" id="export_contratista_grupo">
                                    <input type="hidden" name="filtro_mes" id="export_contratista_mes">
                                    <input type="hidden" name="filtro_usuario" id="export_contratista_usuario">
                                    <button type="submit" class="btn btn-light btn-sm">
                                        <i class="bi bi-file-earmark-excel-fill"></i> Actividades Masivas CONTRATISTA
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Mostrar formulario Actividades Individuales solo si NO es tipo 10 (CONTRATISTA CENTRO VIDA) -->
                                <?php if ($tipo_usuario != 10 && !($tipo_usuario == 2 && $prefijo_grupo == 'CV')) : ?>
                                <form id="exportIndividualesForm" action="exportIndividualesFromReports.php" method="get" style="display:inline;">
                                    <input type="hidden" name="filtro_anio" id="export_individuales_anio">
                                    <input type="hidden" name="filtro_grupo" id="export_individuales_grupo">
                                    <input type="hidden" name="filtro_mes" id="export_individuales_mes">
                                    <input type="hidden" name="filtro_usuario" id="export_individuales_usuario">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="bi bi-file-earmark-person-fill"></i> Actividades INDIVIDUALES
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Mostrar formulario Combinado (Masivas e Individuales) solo si NO es tipo 10 (CONTRATISTA CENTRO VIDA) -->
                                <?php if ($tipo_usuario != 10 && !($tipo_usuario == 2 && $prefijo_grupo == 'CV')) : ?>
                                <form id="exportContratistaCombinadoForm" action="exportContratistaCombinadoFromReports.php" method="get" style="display:inline;">
                                    <input type="hidden" name="filtro_anio" id="export_combinado_anio">
                                    <input type="hidden" name="filtro_grupo" id="export_combinado_grupo">
                                    <input type="hidden" name="filtro_mes" id="export_combinado_mes">
                                    <input type="hidden" name="filtro_usuario" id="export_combinado_usuario">
                                    <button type="submit" class="btn btn-info btn-sm">
                                        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Masivas e Individuales CONTRATISTA
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Mostrar formulario Centro Vida solo si NO es tipo 3 (CONTRATISTA CPSAM) ni tipo 4 (TÉCNICO)
                                     y si es tipo 2, solo si su grupo NO empieza con CPSAM -->
                                <?php if ($tipo_usuario != 3 && $tipo_usuario != 4 && !($tipo_usuario == 2 && $prefijo_grupo == 'CPSAM')) : ?>
                                <form id="exportCentroVidaForm" action="exportCentroVidaFromReports.php" method="get" style="display:inline;">
                                    <input type="hidden" name="filtro_anio" id="export_cv_anio">
                                    <input type="hidden" name="filtro_grupo" id="export_cv_grupo">
                                    <input type="hidden" name="filtro_mes" id="export_cv_mes">
                                    <input type="hidden" name="filtro_usuario" id="export_cv_usuario">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-file-earmark-excel-fill"></i> Actividades CENTRO VIDA MASIVO
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentYear = <?php echo $selectedYear; ?>;

        $(document).ready(function() {

            // Sincronizar filtros con formularios de exportación
            function updateExportForms() {
                const anio = $('#yearSelect').val();
                const grupo = $('#filtroGrupo').val();
                const mes = $('#filtroMes').val();
                const usuario = $('#filtroUsuario').val();
                
                // Actualizar formulario Contratista
                $('#export_contratista_anio').val(anio);
                $('#export_contratista_grupo').val(grupo);
                $('#export_contratista_mes').val(mes);
                $('#export_contratista_usuario').val(usuario);
                
                // Actualizar formulario Individuales
                $('#export_individuales_anio').val(anio);
                $('#export_individuales_grupo').val(grupo);
                $('#export_individuales_mes').val(mes);
                $('#export_individuales_usuario').val(usuario);
                
                // Actualizar formulario Combinado (Masivas e Individuales)
                $('#export_combinado_anio').val(anio);
                $('#export_combinado_grupo').val(grupo);
                $('#export_combinado_mes').val(mes);
                $('#export_combinado_usuario').val(usuario);
                
                // Actualizar formulario Centro Vida
                $('#export_cv_anio').val(anio);
                $('#export_cv_grupo').val(grupo);
                $('#export_cv_mes').val(mes);
                $('#export_cv_usuario').val(usuario);
            }

            // Inicializar valores de formularios
            updateExportForms();

            // Event listeners
            $('#yearSelect').on('change', function() {
                currentYear = $(this).val();
                updateExportForms();
            });

            $('#filtroGrupo').on('change', function() {
                updateExportForms();
            });

            $('#filtroMes').on('change', function() {
                updateExportForms();
            });

            $('#filtroUsuario').on('change', function() {
                updateExportForms();
            });

            $('#btnExportExcel').on('click', exportToExcel);
            $('#btnExportMovimientos').on('click', exportMovimientos);
        });

        function exportToExcel() {
            // Obtener filtros
            const filtroGrupo = $('#filtroGrupo').val();
            const filtroMes = $('#filtroMes').val();
            const filtroUsuario = $('#filtroUsuario').val();
            
            // Construir URL con parámetros
            let url = 'generateExcel.php?year=' + currentYear;
            if (filtroGrupo) {
                url += '&filtro_grupo=' + filtroGrupo;
            }
            if (filtroMes) {
                url += '&filtro_mes=' + filtroMes;
            }
            if (filtroUsuario) {
                url += '&filtro_usuario=' + filtroUsuario;
            }
            
            // Abrir el generador de Excel directamente para descargar
            window.open(url, '_blank');
        }

        function exportMovimientos() {
            // Obtener filtros
            const filtroGrupo = $('#filtroGrupo').val();
            const filtroMes = $('#filtroMes').val();
            const filtroUsuario = $('#filtroUsuario').val();
            
            // Construir URL con parámetros
            let url = 'exportMovimientos.php?year=' + currentYear;
            if (filtroGrupo) {
                url += '&filtro_grupo=' + filtroGrupo;
            }
            if (filtroMes) {
                url += '&filtro_mes=' + filtroMes;
            }
            if (filtroUsuario) {
                url += '&filtro_usuario=' + filtroUsuario;
            }
            
            // Abrir el generador de Excel directamente para descargar
            window.open(url, '_blank');
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