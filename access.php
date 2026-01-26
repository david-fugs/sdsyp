<?php
session_start();

if (!isset($_SESSION['id'])) {
  header("Location: index.php");
}

$usuario      = $_SESSION['usuario'];
$nombre       = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$cod_dane_ie  = $_SESSION['cod_dane_ie'];

// Incluir conexión para obtener datos del dashboard
include("conexion.php");

// Obtener estadísticas del sistema
$stats = array();

// Estadísticas específicas para Colombia Mayor (tipo 8 y 9)
if ($tipo_usuario == 8 || $tipo_usuario == 9) {
  // Total de personas Colombia Mayor
  $where_usuario = ($tipo_usuario == 9) ? " AND usuario_registro = " . $_SESSION['id'] : "";

  // Verificar si las tablas existen
  $tabla_existe = $mysqli->query("SHOW TABLES LIKE 'personas_colombia_mayor'");
  if ($tabla_existe && $tabla_existe->num_rows > 0) {
    $query_personas_cm = "SELECT COUNT(*) as total FROM personas_colombia_mayor WHERE 1=1 $where_usuario";
    $result_personas_cm = $mysqli->query($query_personas_cm);
    $stats['personas'] = $result_personas_cm ? $result_personas_cm->fetch_assoc()['total'] : 0;

    // Personas activas
    $query_activas_cm = "SELECT COUNT(*) as total FROM personas_colombia_mayor WHERE estado_cm = 'ACTIVO' $where_usuario";
    $result_activas_cm = $mysqli->query($query_activas_cm);
    $stats['personas_activas'] = $result_activas_cm ? $result_activas_cm->fetch_assoc()['total'] : 0;

    // Total de movimientos
    $query_movimientos_cm = "SELECT COUNT(*) as total FROM movimientos_colombia_mayor WHERE 1=1 $where_usuario";
    $result_movimientos_cm = $mysqli->query($query_movimientos_cm);
    $stats['movimientos_cm'] = $result_movimientos_cm ? $result_movimientos_cm->fetch_assoc()['total'] : 0;

    // Total de registros individuales
    $query_registros_cm = "SELECT COUNT(*) as total FROM registros_individuales_cm WHERE 1=1 $where_usuario";
    $result_registros_cm = $mysqli->query($query_registros_cm);
    $stats['registros_cm'] = $result_registros_cm ? $result_registros_cm->fetch_assoc()['total'] : 0;

    // Total de pagos
    $query_pagos_cm = "SELECT COUNT(*) as total FROM pagos_colombia_mayor WHERE 1=1 $where_usuario";
    $result_pagos_cm = $mysqli->query($query_pagos_cm);
    $stats['pagos_cm'] = $result_pagos_cm ? $result_pagos_cm->fetch_assoc()['total'] : 0;

    // Movimientos recientes (últimos 30 días)
    $query_movimientos_recientes = "SELECT COUNT(*) as total FROM movimientos_colombia_mayor 
                                         WHERE fecha_movimiento_cm >= DATE_SUB(NOW(), INTERVAL 30 DAY) $where_usuario";
    $result_movimientos_recientes = $mysqli->query($query_movimientos_recientes);
    $stats['movimientos_recientes'] = $result_movimientos_recientes ? $result_movimientos_recientes->fetch_assoc()['total'] : 0;
  } else {
    // Valores por defecto si las tablas no existen aún
    $stats['personas'] = 0;
    $stats['personas_activas'] = 0;
    $stats['movimientos_cm'] = 0;
    $stats['registros_cm'] = 0;
    $stats['pagos_cm'] = 0;
    $stats['movimientos_recientes'] = 0;
  }
} else {
  // Estadísticas normales para otros usuarios
  // Total de personas
  // Contar personas que NO tienen ningún movimiento con id_condicion = 8 (fallecido)
  $query_personas = "
    SELECT COUNT(*) as total FROM personas p
    WHERE NOT EXISTS (
        SELECT 1 FROM movimiento_persona mp
        WHERE mp.cedula_persona = p.cedula_persona
        AND mp.id_condicion = 8
    )
    ";
  $result_personas = $mysqli->query($query_personas);
  $stats['personas'] = $result_personas->fetch_assoc()['total'];
}

// Total de metas
$query_metas = "SELECT COUNT(*) as total FROM metas";
$result_metas = $mysqli->query($query_metas);
$stats['metas'] = $result_metas->fetch_assoc()['total'];

// Total de actividades
$query_actividades = "SELECT COUNT(*) as total FROM actividades";
$result_actividades = $mysqli->query($query_actividades);
$stats['actividades'] = $result_actividades->fetch_assoc()['total'];

// Total de acciones
$query_acciones = "SELECT COUNT(*) as total FROM acciones";
$result_acciones = $mysqli->query($query_acciones);
$stats['acciones'] = $result_acciones->fetch_assoc()['total'];

// Total de políticas públicas
$query_politicas = "SELECT COUNT(*) as total FROM politicas_publicas";
$result_politicas = $mysqli->query($query_politicas);
$stats['politicas'] = $result_politicas->fetch_assoc()['total'];

// Total de grupos
$query_grupos = "SELECT COUNT(*) as total FROM grupos";
$result_grupos = $mysqli->query($query_grupos);
$stats['grupos'] = $result_grupos->fetch_assoc()['total'];

// Total de centros de vida
$query_centros = "SELECT COUNT(*) as total FROM centro_vida";
$result_centros = $mysqli->query($query_centros);
$stats['centros'] = $result_centros->fetch_assoc()['total'];

// Total de movimientos recientes (últimos 30 días)
$query_movimientos = "SELECT COUNT(*) as total FROM movimiento_persona WHERE fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result_movimientos = $mysqli->query($query_movimientos);
$stats['movimientos_recientes'] = $result_movimientos->fetch_assoc()['total'];

// Estadísticas por grupos - contar personas por grupo
$query_grupos_personas = "
SELECT 
    g.id_grupo,
    g.descripcion_grupo,
    g.limite_personas,
    COUNT(p.id_persona) as total_personas
FROM grupos g
LEFT JOIN personas p ON g.id_grupo = p.id_grupo
    AND NOT EXISTS (
        SELECT 1 FROM movimiento_persona mp
        WHERE mp.cedula_persona = p.cedula_persona
        AND mp.id_condicion = 8
    )
GROUP BY g.id_grupo, g.descripcion_grupo, g.limite_personas
ORDER BY g.descripcion_grupo ASC
";
$result_grupos_personas = $mysqli->query($query_grupos_personas);
$grupos_stats = array();
while ($row = $result_grupos_personas->fetch_assoc()) {
  $grupos_stats[] = $row;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - SDSYP</title>

  <!-- Boxicons CSS -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" defer></script>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Menu CSS -->
  <link rel="stylesheet" href="menu/style.css" />

  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
    }

    .main-content {
      margin-left: 80px;
      /* Margen mínimo para sidebar colapsado */
      transition: margin-left 0.3s ease;
      padding: 20px;
      padding-top: 140px;
      /* Compensar navbar fijo + barra superior */
      width: calc(100% - 80px);
      /* Ajustar ancho considerando sidebar colapsado */
      box-sizing: border-box;
      position: relative;
      z-index: 1;
      min-height: calc(100vh - 140px);
    }

    .main-content.sidebar-open {
      margin-left: 350px;
      /* Ancho completo del sidebar cuando está abierto */
      width: calc(100% - 350px);
    }

    /* Asegurar que el sidebar no tape el contenido */
    .sidebar {
      position: fixed !important;
      z-index: 1000;
      left: 0;
      top: 0;
    }

    .navbar {
      position: fixed !important;
      z-index: 1001;
      width: 100%;
      top: 0;
      left: 0;
    }

    /* Barra lateral superior */
    .top-sidebar {
      position: fixed;
      top: 70px;
      /* Debajo del navbar - ajustado */
      left: 80px;
      /* Iniciar después del sidebar colapsado */
      right: 0;
      width: calc(100% - 80px);
      /* Ajustar ancho considerando sidebar colapsado */
      height: 60px;
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(15px);
      border-bottom: 2px solid rgba(0, 123, 255, 0.3);
      z-index: 998;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: center;
    }

    .top-sidebar.sidebar-open {
      left: 350px;
      /* Mover después del sidebar completamente abierto */
      width: calc(100% - 350px);
    }

    .top-sidebar-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 100%;
      padding: 0 20px;
      overflow-x: auto;
    }

    .quick-nav {
      display: flex;
      gap: 15px;
      align-items: center;
    }

    .quick-nav-item {
      display: flex;
      align-items: center;
      padding: 10px 18px;
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      text-decoration: none;
      border-radius: 25px;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      white-space: nowrap;
      box-shadow: 0 3px 10px rgba(0, 123, 255, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .quick-nav-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
      color: white;
      text-decoration: none;
    }

    .quick-nav-item i {
      margin-right: 6px;
      font-size: 16px;
    }

    .quick-nav-item .badge {
      margin-left: 6px;
      background-color: rgba(255, 255, 255, 0.3);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .breadcrumb-info {
      display: flex;
      align-items: center;
      color: #6c757d;
      font-size: 14px;
    }

    .breadcrumb-info i {
      margin-right: 8px;
      color: #007bff;
    }

    .welcome-section {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 2rem;
      margin: 2rem 0;
      color: white;
      text-align: center;
    }

    .stats-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: none;
    }

    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }

    .stats-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .stats-label {
      color: #6c757d;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.9rem;
      letter-spacing: 0.5px;
    }

    .chart-container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
    }

    .page-title {
      color: white;
      text-align: center;
      margin: 2rem 0;
      font-size: 3rem;
      font-weight: 700;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .quick-actions {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .action-btn {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      border: none;
      border-radius: 10px;
      padding: 1rem;
      color: white;
      text-decoration: none;
      display: block;
      margin-bottom: 1rem;
      transition: all 0.3s ease;
      text-align: center;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);
      color: white;
      text-decoration: none;
    }

    /* Estilos específicos para la tabla de grupos */
    .groups-table {
      overflow-x: auto;
    }

    .groups-table table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .groups-table th {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      padding: 15px;
      text-align: center;
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .groups-table td {
      padding: 12px 15px;
      text-align: center;
      border-bottom: 1px solid #eee;
      vertical-align: middle;
    }

    .groups-table tr:hover {
      background-color: #f8f9fa;
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-disponible {
      background-color: #d4edda;
      color: #155724;
    }

    .status-medio {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-lleno {
      background-color: #f8d7da;
      color: #721c24;
    }

    .progress-bar-custom {
      height: 8px;
      border-radius: 10px;
      background-color: #e9ecef;
      overflow: hidden;
      margin: 5px 0;
    }

    .progress-fill {
      height: 100%;
      border-radius: 10px;
      transition: width 0.3s ease;
    }

    .progress-low {
      background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    }

    .progress-medium {
      background: linear-gradient(90deg, #ffc107 0%, #fd7e14 100%);
    }

    .progress-high {
      background: linear-gradient(90deg, #dc3545 0%, #e83e8c 100%);
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 0 !important;
        /* En móvil, no hay margen */
        width: 100% !important;
        padding: 15px;
        padding-top: 130px;
      }

      .main-content.sidebar-open {
        margin-left: 0 !important;
        width: 100% !important;
      }

      .top-sidebar {
        left: 0 !important;
        /* En móvil, barra superior ocupa todo el ancho */
        width: 100% !important;
      }

      .top-sidebar.sidebar-open {
        left: 0 !important;
        width: 100% !important;
      }

      .stats-card {
        margin-bottom: 1rem;
        padding: 1rem;
      }

      .stats-number {
        font-size: 2rem;
      }

      .page-title {
        font-size: 2rem;
        margin: 1rem 0;
      }

      .quick-nav {
        gap: 8px;
      }

      .quick-nav-item {
        padding: 6px 10px;
        font-size: 12px;
      }

      .quick-nav-item .badge {
        font-size: 10px;
      }
    }

    @media (max-width: 576px) {
      .main-content {
        padding: 10px;
        padding-top: 125px;
      }

      .stats-card {
        padding: 0.8rem;
      }

      .page-title {
        font-size: 1.5rem;
      }

      .action-btn {
        padding: 0.8rem;
        margin-bottom: 0.8rem;
      }

      .top-sidebar-content {
        padding: 0 10px;
      }

      .quick-nav {
        gap: 5px;
        flex-wrap: wrap;
      }

      .quick-nav-item {
        padding: 4px 8px;
        font-size: 11px;
      }

      .breadcrumb-info {
        display: none;
      }
    }
  </style>
</head>

<body>
  <!-- navbar -->
  <nav class="navbar">
    <div class="logo_item">
      <i class="bx bx-menu" id="sidebarOpen"></i>
      <img src="img/logo.png" alt="">SDSYP - Dashboard
    </div>
    <div class="navbar_content">
      <i class="bi bi-grid"></i>
      <i class="fa-solid fa-sun" id="darkLight"></i>
      <a href="logout.php"> <i class="fa-solid fa-door-open"></i></a>
      <img src="img/logo.png" alt="" class="profile" />
    </div>
  </nav>

  <!-- Barra lateral superior -->
  <?php if ($tipo_usuario == 1) { ?>
    <div class="top-sidebar" id="topSidebar">
      <div class="top-sidebar-content">
        <div class="quick-nav">
          <a href="code/persons/seePerson.php" class="quick-nav-item">
            <i class="bi bi-people-fill"></i>
            Personas
            <span class="badge"><?php echo $stats['personas']; ?></span>
          </a>
          <a href="code/goals/seeGoals.php" class="quick-nav-item">
            <i class="bi bi-bullseye"></i>
            Metas
            <span class="badge"><?php echo $stats['metas']; ?></span>
          </a>
          <a href="code/activities/seeActivity.php" class="quick-nav-item">
            <i class="bi bi-list-task"></i>
            Actividades
            <span class="badge"><?php echo $stats['actividades']; ?></span>
          </a>
          <a href="code/action/seeActions.php" class="quick-nav-item">
            <i class="bi bi-lightning"></i>
            Acciones
            <span class="badge"><?php echo $stats['acciones']; ?></span>
          </a>
          <a href="code/publicPolicies/seePublicPolicies.php" class="quick-nav-item">
            <i class="bi bi-clipboard-check"></i>
            Políticas
            <span class="badge"><?php echo $stats['politicas']; ?></span>
          </a>
          <a href="code/group/seeGroup.php" class="quick-nav-item">
            <i class="bi bi-collection"></i>
            Grupos
            <span class="badge"><?php echo $stats['grupos']; ?></span>
          </a>
          <a href="code/reports/seeReports.php" class="quick-nav-item">
            <i class="bi bi-file-earmark-bar-graph"></i>
            Informes
          </a>
        </div>

        <div class="breadcrumb-info" style="color: #6c757d; font-weight: 600;">
          <i class="bi bi-house-fill"></i>
          Dashboard Principal
        </div>
      </div>
    </div>

  <?php } ?>

  <!-- Barra superior para Colombia Mayor -->
  <?php if ($tipo_usuario == 8 || $tipo_usuario == 9) { ?>
    <div class="top-sidebar" id="topSidebar">
      <div class="top-sidebar-content">
        <div class="quick-nav">
          <a href="code/colombiaMayor/seePersonaCM.php" class="quick-nav-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="bi bi-people-fill"></i>
            Personas C.M
          </a>
          <a href="code/colombiaMayor/seeMovimientosCM.php" class="quick-nav-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="bi bi-arrow-left-right"></i>
            Movimientos
          </a>
          <a href="code/colombiaMayor/formIndividualCM.php" class="quick-nav-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="bi bi-clipboard-list"></i>
            Registros
          </a>
          <a href="code/colombiaMayor/formPagosCM.php" class="quick-nav-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="bi bi-money-bill-wave"></i>
            Pagos
          </a>
          <a href="code/colombiaMayor/consultaPagosCM.php" class="quick-nav-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="bi bi-search"></i>
            Consultar
          </a>
        </div>

        <div class="breadcrumb-info" style="color: #6c757d; font-weight: 600;">
          <i class="bi bi-award-fill"></i>
          Colombia Mayor - Dashboard
        </div>
      </div>
    </div>

  <?php } ?>
  <!-- sidebar -->
  <?php if ($tipo_usuario != 3 && $tipo_usuario != 10 && $tipo_usuario != 11) { ?>
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>
          <?php if ($tipo_usuario == 2 || $tipo_usuario == 4 || $tipo_usuario == 5) { ?>
            <li class="item">
              <div href="#" class="nav_link submenu_item">
                <span class="navlink_icon">
                  <i class="fa-solid fa-bullseye"></i>
                </span>
                <span class="navlink">Registro art masivas</span>
                <i class="bx bx-chevron-right arrow-left"></i>
              </div>
              <ul class="menu_items submenu">
                <?php if ($tipo_usuario != 4 && $tipo_usuario != 5) : ?>
                  <a href="code/contratista/seeActivities.php" class="nav_link sublink">Agregar Actividades contratista</a>
                  <a href="code/contratistaCentroVida/seeActivitiesCentroVida.php" class="nav_link sublink">Actividades Centro Vida</a>
                <?php endif; ?>
                <?php if ($tipo_usuario != 5) : ?>
                  <!-- <a href="code/movement/seeMovement.php" class="nav_link sublink">Movimientos</a> -->
                  <a href="code/contratista/form.php" class="nav_link sublink">Registro Actividades Masivas</a>
                  <a href="code/contratistaIndividual/form.php" class="nav_link sublink">Registro Actividades Individuales</a>
                <?php endif; ?>
                <?php if ($tipo_usuario != 4) : ?>
                  <a href="code/contratistaCentroVida/formCentroVida.php" class="nav_link sublink">Registros indiv Centro Vida</a>
                  <a href="code/contratistaCentroVida/formMasivoCentroVida.php" class="nav_link sublink">Registros masiva Centro Vida</a>
                <?php endif; ?>
                <?php if ($tipo_usuario != 5) : ?>
                <a href="code/personMovement/seePersonMovement.php" class="nav_link sublink">Movimientos Personas</a>
                <?php endif; ?>
                <?php if ($tipo_usuario == 5) : ?>
                  <a href="code/contratistaCentroVida/comparadorActividades.php" class="nav_link sublink">Comparador Actividades</a>
                <?php endif; ?>

              </ul>
            </li>
          <?php } ?>

          <?php if ($tipo_usuario == 1) { ?>
            <li class="item">
              <div href="#" class="nav_link submenu_item">
                <span class="navlink_icon">
                  <i class="fa-solid fa-user-pen"></i>
                </span>
                <span class="navlink">Usuarios Plataforma</span>
                <i class="bx bx-chevron-right arrow-left"></i>
              </div>
              <ul class="menu_items submenu">
                <a href="code/users/showusers.php" class="nav_link sublink">Permisos</a>
                <a href="code/users/register.php" class="nav_link sublink">Crear Nuevo</a>
              </ul>
            </li>
            <li class="item">
              <div href="#" class="nav_link submenu_item">
                <span class="navlink_icon">
                  <i class="fa-solid fa-bullseye"></i>
                </span>
                <span class="navlink">Registro art masivas </span>
                <i class="bx bx-chevron-right arrow-left"></i>
              </div>
              <ul class="menu_items submenu">
                <a href="code/contratista/seeActivities.php" class="nav_link sublink">Agregar Actividades contratista</a>
                <a href="code/contratistaCentroVida/seeActivitiesCentroVida.php" class="nav_link sublink">Actividades Centro Vida</a>
                <!-- <a href="code/movement/seeMovement.php" class="nav_link sublink">Movimientos</a> -->
                <a href="code/contratista/form.php" class="nav_link sublink">Registro Actividades Masivas</a>
                <a href="code/contratistaIndividual/form.php" class="nav_link sublink">Registro Actividades Individuales</a>
                <a href="code/contratistaCentroVida/formCentroVida.php" class="nav_link sublink">Registros indiv Centro Vida</a>
                <a href="code/contratistaCentroVida/formMasivoCentroVida.php" class="nav_link sublink">Registros masiva Centro Vida</a>
                <a href="code/personMovement/seePersonMovement.php" class="nav_link sublink">Movimientos Personas</a>
                <a href="code/contratistaCentroVida/comparadorActividades.php" class="nav_link sublink">Comparador Actividades</a>
                


              </ul>
            </li>
              <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-bullseye"></i>
              </span>
              <span class="navlink">Registro SPP</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/contratistaCentroVida/formActividadPersonalizada.php" class="nav_link sublink">SPP</a>
              
            </ul>
          </li>
            <li class="item">
              <div href="#" class="nav_link submenu_item">
                <span class="navlink_icon">
                  <i class="fa-solid fa-bullseye"></i>
                </span>
                <span class="navlink">Metas</span>
                <i class="bx bx-chevron-right arrow-left"></i>
              </div>
              <ul class="menu_items submenu">
                <a href="code/goals/seeGoals.php" class="nav_link sublink">Ver Metas</a>
                <a href="code/activities/seeActivity.php" class="nav_link sublink">Ver Actividades</a>
                <a href="code/action/seeActions.php" class="nav_link sublink">Ver Acciones</a>
              </ul>
            </li>
            <li class="item">
              <div href="#" class="nav_link submenu_item">
                <span class="navlink_icon">
                  <i class="fa-solid fa-scale-balanced"></i>
                </span>
                <span class="navlink">Políticas Públicas</span>
                <i class="bx bx-chevron-right arrow-left"></i>
              </div>
              <ul class="menu_items submenu">
                <a href="code/publicPolicies/seePublicPolicies.php" class="nav_link sublink">Ver Políticas Públicas</a>
              </ul>
            </li>
            <li class="item">
              <div href="#" class="nav_link submenu_item">
                <span class="navlink_icon">
                  <i class="fa-solid fa-list-check"></i>
                </span>
                <span class="navlink">Condiciones</span>
                <i class="bx bx-chevron-right arrow-left"></i>
              </div>
              <ul class="menu_items submenu">
                <a href="code/condition/seeCondition.php" class="nav_link sublink">Ver Condiciones</a>
                <a href="code/group/seeGroup.php" class="nav_link sublink">Grupo o CPSAM</a>
                <!-- <a href="code/center/seeCenter.php" class="nav_link sublink">Centro Vida</a> -->
              </ul>
            </li>
          <?php } ?>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-users"></i>
              </span>
              <span class="navlink">Personas</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/persons/seePerson.php" class="nav_link sublink">Ver Personas</a>
              <!-- <a href="code/movement/seeMovement.php" class="nav_link sublink">Movimientos</a> -->
            </ul>
          </li>

          <?php if ($tipo_usuario != 5) { ?>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-chart-bar"></i>
              </span>
              <span class="navlink">Informes</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/reports/seeReports.php" class="nav_link sublink">Informes Anuales</a>
            </ul>
          </li>
          <?php } ?>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-screwdriver-wrench"></i>
              </span>
              <span class="navlink">Mi Cuenta</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="reset-password.php" class="nav_link sublink">Cambiar Contraseña</a>
            </ul>
          </li>
          <div class="bottom_content">
            <div class="bottom expand_sidebar">
              <span> Expand</span>
              <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
              <span> Collapse</span>
              <i class='bx bx-log-out'></i>
            </div>


          </div>
        </ul>

      </div>
    </nav>
  <?php } ?>


  <?php if ($tipo_usuario == 3) { ?>
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-users"></i>
              </span>
              <span class="navlink">Personas</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/persons/seePerson.php" class="nav_link sublink">Ver Personas</a>
              <!-- <a href="code/movement/seeMovement.php" class="nav_link sublink">Movimientos</a> -->
            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-bullseye"></i>
              </span>
              <span class="navlink">Registro art masivas </span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/contratista/seeActivities.php" class="nav_link sublink">Agregar Actividades contratista</a>
              <!-- <a href="code/movement/seeMovement.php" class="nav_link sublink">Movimientos</a> -->
              <a href="code/contratista/form.php" class="nav_link sublink">Registro Actividades</a>
              <a href="code/personMovement/seePersonMovement.php" class="nav_link sublink">Movimientos Personas</a>

            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-chart-bar"></i>
              </span>
              <span class="navlink">Informes</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/reports/seeReports.php" class="nav_link sublink">Informes Anuales</a>
            </ul>
          </li>


          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-screwdriver-wrench"></i>
              </span>
              <span class="navlink">Mi Cuenta</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="reset-password.php" class="nav_link sublink">Cambiar Contraseña</a>
            </ul>
          </li>
          <div class="bottom_content">
            <div class="bottom expand_sidebar">
              <span> Expand</span>
              <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
              <span> Collapse</span>
              <i class='bx bx-log-out'></i>
            </div>


          </div>
        </ul>

      </div>
    </nav>
  <?php } ?>

  <!-- Menu para CONTRATISTA CENTRO VIDA (Tipo 10) -->
  <?php if ($tipo_usuario == 10) { ?>
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-users"></i>
              </span>
              <span class="navlink">Personas</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/persons/seePerson.php" class="nav_link sublink">Ver Personas</a>
            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-bullseye"></i>
              </span>
              <span class="navlink">Registro art masivas </span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <!-- Actividades Centro Vida oculto para tipo 10 y 11 -->
              <a href="code/contratistaCentroVida/formCentroVida.php" class="nav_link sublink">Registros indiv Centro Vida</a>
              <a href="code/contratistaCentroVida/formMasivoCentroVida.php" class="nav_link sublink">Registros masiva Centro Vida</a>
              <!-- Movimientos Personas visible para tipo 10 -->
              
              <a href="code/contratistaCentroVida/comparadorActividades.php" class="nav_link sublink">Comparador Actividades</a>
            </ul>
          </li>
          <!-- Informes oculto para tipo 10 -->

          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-screwdriver-wrench"></i>
              </span>
              <span class="navlink">Mi Cuenta</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="reset-password.php" class="nav_link sublink">Cambiar Contraseña</a>
            </ul>
          </li>
          <div class="bottom_content">
            <div class="bottom expand_sidebar">
              <span> Expand</span>
              <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
              <span> Collapse</span>
              <i class='bx bx-log-out'></i>
            </div>
          </div>
        </ul>

      </div>
    </nav>
  <?php } ?>

  <!-- Menu para INGENIERO CENTRO VIDA (Tipo 11) - Acceso Restringido por Grupo -->
  <?php if ($tipo_usuario == 11) { ?>
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-users"></i>
              </span>
              <span class="navlink">Personas</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/persons/seePerson.php" class="nav_link sublink">Ver Personas</a>
            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-user-pen"></i>
              </span>
              <span class="navlink">Usuarios Plataforma</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/users/showusers.php" class="nav_link sublink">Permisos</a>
              <a href="code/users/register.php" class="nav_link sublink">Crear Nuevo</a>
            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-bullseye"></i>
              </span>
              <span class="navlink">Registro art masivas</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/contratistaCentroVida/formCentroVida.php" class="nav_link sublink">Registros indiv Centro Vida</a>
              <a href="code/contratistaCentroVida/formMasivoCentroVida.php" class="nav_link sublink">Registros masiva Centro Vida</a>
              <a href="code/contratistaCentroVida/comparadorActividades.php" class="nav_link sublink">Comparador Actividades</a>
              <a href="code/personMovement/seePersonMovement.php" class="nav_link sublink">Movimientos Personas</a>
            </ul>
          </li>

          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-screwdriver-wrench"></i>
              </span>
              <span class="navlink">Mi Cuenta</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="reset-password.php" class="nav_link sublink">Cambiar Contraseña</a>
            </ul>
          </li>
          <div class="bottom_content">
            <div class="bottom expand_sidebar">
              <span> Expand</span>
              <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
              <span> Collapse</span>
              <i class='bx bx-log-out'></i>
            </div>
          </div>
        </ul>

      </div>
    </nav>
  <?php } ?>

  <!-- MENU CONTRATISTA CENTRO VIDA ALCALDIA -->
    <!-- Menu para INGENIERO CENTRO VIDA (Tipo 11) - Acceso Restringido por Grupo -->
  <?php if ($tipo_usuario == 12) { ?>
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-users"></i>
              </span>
              <span class="navlink">Personas</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/persons/seePerson.php" class="nav_link sublink">Ver Personas</a>
            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-bullseye"></i>
              </span>
              <span class="navlink">Registro SPP</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/contratistaCentroVida/formActividadPersonalizada.php" class="nav_link sublink">SPP</a>
              
            </ul>
          </li>

          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-screwdriver-wrench"></i>
              </span>
              <span class="navlink">Mi Cuenta</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="reset-password.php" class="nav_link sublink">Cambiar Contraseña</a>
            </ul>
          </li>
          <div class="bottom_content">
            <div class="bottom expand_sidebar">
              <span> Expand</span>
              <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
              <span> Collapse</span>
              <i class='bx bx-log-out'></i>
            </div>
          </div>
        </ul>

      </div>
    </nav>
  <?php } ?>

  <!-- Menu para Colombia Mayor (Tipo 8: Técnico, Tipo 9: Contratista) -->
  <?php if ($tipo_usuario == 8 || $tipo_usuario == 9) { ?>
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>

          <!-- Título especial Colombia Mayor -->
          <li class="item" style="pointer-events: none;">
            <div class="nav_link" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; margin: 10px; padding: 15px; text-align: center;">
              <span style="font-size: 18px; font-weight: 700;">
                <i class="fas fa-award"></i> COLOMBIA MAYOR
              </span>
            </div>
          </li>

          <!-- Personas Colombia Mayor -->
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-users"></i>
              </span>
              <span class="navlink">Personas C.M</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/colombiaMayor/seePersonaCM.php" class="nav_link sublink">Ver Personas</a>
              <a href="code/colombiaMayor/seeMovimientosCM.php" class="nav_link sublink">Movimientos</a>
            </ul>
          </li>

          <!-- Registros de Actividades -->
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-clipboard-list"></i>
              </span>
              <span class="navlink">Registros C.M</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/colombiaMayor/formIndividualCM.php" class="nav_link sublink">Registros Individuales</a>
              <a href="code/colombiaMayor/formRegistroMasivoCM.php" class="nav_link sublink">Registros Masivos</a>
            </ul>
          </li>

          <!-- Pagos Colombia Mayor -->
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-money-bill-wave"></i>
              </span>
              <span class="navlink">Pagos C.M</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/colombiaMayor/formPagosCM.php" class="nav_link sublink">Registrar Pago Masivo</a>
              <a href="code/colombiaMayor/consultaPagosCM.php" class="nav_link sublink">Consultar Pagos</a>
              <a href="code/colombiaMayor/historialPagosCM.php" class="nav_link sublink">Historial de Pagos</a>
            </ul>
          </li>

          <!-- Informes Colombia Mayor -->
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-chart-bar"></i>
              </span>
              <span class="navlink">Informes C.M</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="code/colombiaMayor/exportPersonasCM.php" class="nav_link sublink">Exportar Personas</a>
              <a href="code/colombiaMayor/exportMovimientosCM.php" class="nav_link sublink">Exportar Movimientos</a>
              <a href="code/colombiaMayor/exportRegistrosCM.php" class="nav_link sublink">Exportar Registros</a>
              <a href="code/colombiaMayor/exportPagosCM.php" class="nav_link sublink">Exportar Pagos</a>
            </ul>
          </li>

          <!-- Mi Cuenta -->
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-screwdriver-wrench"></i>
              </span>
              <span class="navlink">Mi Cuenta</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>
            <ul class="menu_items submenu">
              <a href="reset-password.php" class="nav_link sublink">Cambiar Contraseña</a>
            </ul>
          </li>

          <div class="bottom_content">
            <div class="bottom expand_sidebar">
              <span> Expand</span>
              <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
              <span> Collapse</span>
              <i class='bx bx-log-out'></i>
            </div>
          </div>
        </ul>
      </div>
    </nav>
  <?php } ?>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">
    <!-- Page Title -->
    <h1 class="page-title">
      <i class="bi bi-speedometer2 me-3"></i>
      Dashboard Principal
    </h1>

    <!-- Welcome Section -->
    <div class="welcome-section">
      <?php if ($tipo_usuario == 8 || $tipo_usuario == 9) { ?>
        <!-- Bienvenida Colombia Mayor -->
        <h2><i class="fas fa-award me-2"></i>Bienvenido a Colombia Mayor</h2>
        <p class="lead mb-3">Sistema de Gestión Colombia Mayor - Panel de Control</p>
        <small>Acceso como: <?php echo $tipo_usuario == 8 ? 'Técnico Colombia Mayor' : 'Contratista Colombia Mayor'; ?> | Usuario: <?php echo htmlspecialchars($nombre); ?></small>

        <!-- Botón de acceso rápido Colombia Mayor -->
        <div class="mt-4">
          <a href="code/colombiaMayor/seePersonaCM.php" class="btn btn-light btn-lg px-4 py-2" style="border-radius: 25px; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.2); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <i class="bi bi-people-fill me-2"></i>
            Acceder al Módulo de Personas Colombia Mayor
            <span class="badge bg-light text-primary ms-2"><?php echo $stats['personas']; ?></span>
          </a>
        </div>
      <?php } else { ?>
        <!-- Bienvenida normal -->
        <h2><i class="bi bi-house-heart me-2"></i>Bienvenido al Sistema SDSYP</h2>
        <p class="lead mb-3">Sistema de Desarrollo Social y Políticas Públicas - Panel de Control</p>
        <small>Acceso como: <?php echo $tipo_usuario == 1 ? 'Administrador' : 'Usuario'; ?> | Usuario: <?php echo htmlspecialchars($nombre); ?></small>

        <!-- Botón de acceso rápido destacado -->
        <div class="mt-4">
          <a href="code/persons/seePerson.php" class="btn btn-light btn-lg px-4 py-2" style="border-radius: 25px; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <i class="bi bi-people-fill me-2"></i>
            Acceder al Módulo de Personas
            <span class="badge bg-primary ms-2"><?php echo $stats['personas']; ?></span>
          </a>
        </div>
      <?php } ?>
    </div>

    <!-- Statistics Cards -->
    <div class="container-fluid">
      <?php if ($tipo_usuario == 8 || $tipo_usuario == 9) { ?>
        <!-- Estadísticas Colombia Mayor -->
        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-people stats-icon" style="color: #667eea;"></i>
              <div class="stats-number" style="color: #667eea;"><?php echo number_format($stats['personas']); ?></div>
              <div class="stats-label">Personas C.M Total</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-check-circle stats-icon text-success"></i>
              <div class="stats-number text-success"><?php echo number_format($stats['personas_activas']); ?></div>
              <div class="stats-label">Personas Activas</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-arrow-left-right stats-icon text-warning"></i>
              <div class="stats-number text-warning"><?php echo number_format($stats['movimientos_cm']); ?></div>
              <div class="stats-label">Movimientos</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-clipboard-check stats-icon text-info"></i>
              <div class="stats-number text-info"><?php echo number_format($stats['registros_cm']); ?></div>
              <div class="stats-label">Registros</div>
            </div>
          </div>
        </div>

        <!-- Estadísticas secundarias Colombia Mayor -->
        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-money-bill-wave stats-icon" style="color: #764ba2;"></i>
              <div class="stats-number" style="color: #764ba2;"><?php echo number_format($stats['pagos_cm']); ?></div>
              <div class="stats-label">Pagos Registrados</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-calendar-event stats-icon text-danger"></i>
              <div class="stats-number text-danger"><?php echo number_format($stats['movimientos_recientes']); ?></div>
              <div class="stats-label">Movimientos Recientes (30 días)</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-file-earmark-excel stats-icon text-success"></i>
              <div class="stats-number text-success">
                <a href="code/colombiaMayor/exportPersonasCM.php" class="text-success text-decoration-none">
                  <i class="bi bi-download"></i>
                </a>
              </div>
              <div class="stats-label">Exportar Personas</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-graph-up stats-icon text-primary"></i>
              <div class="stats-number text-primary">
                <a href="code/colombiaMayor/consultaPagosCM.php" class="text-primary text-decoration-none">
                  <i class="bi bi-search"></i>
                </a>
              </div>
              <div class="stats-label">Consultar Pagos</div>
            </div>
          </div>
        </div>
      <?php } else { ?>
        <!-- Estadísticas normales -->
        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-people stats-icon text-primary"></i>
              <div class="stats-number text-primary"><?php echo number_format($stats['personas']); ?></div>
              <div class="stats-label">Personas Registradas</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-bullseye stats-icon text-success"></i>
              <div class="stats-number text-success"><?php echo number_format($stats['metas']); ?></div>
              <div class="stats-label">Metas Activas</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-list-task stats-icon text-warning"></i>
              <div class="stats-number text-warning"><?php echo number_format($stats['actividades']); ?></div>
              <div class="stats-label">Actividades</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="stats-card text-center">
              <i class="bi bi-lightning stats-icon text-info"></i>
              <div class="stats-number text-info"><?php echo number_format($stats['acciones']); ?></div>
              <div class="stats-label">Acciones</div>
            </div>
          </div>
        </div>
      <?php } ?>

      <!-- Secondary Statistics -->
      <div class="row">
        <div class="col-md-3 col-sm-6">
          <div class="stats-card text-center">
            <i class="bi bi-clipboard-check stats-icon text-purple"></i>
            <div class="stats-number" style="color: #6f42c1;"><?php echo number_format($stats['politicas']); ?></div>
            <div class="stats-label">Políticas Públicas</div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stats-card text-center">
            <i class="bi bi-collection stats-icon text-dark"></i>
            <div class="stats-number text-dark"><?php echo number_format($stats['grupos']); ?></div>
            <div class="stats-label">Grupos/CPSAM</div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stats-card text-center">
            <i class="bi bi-building stats-icon text-secondary"></i>
            <div class="stats-number text-secondary"><?php echo number_format($stats['centros']); ?></div>
            <div class="stats-label">Centros de Vida</div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stats-card text-center">
            <i class="bi bi-arrow-repeat stats-icon text-danger"></i>
            <div class="stats-number text-danger"><?php echo number_format($stats['movimientos_recientes']); ?></div>
            <div class="stats-label">Movimientos (30 días)</div>
          </div>
        </div>
      </div>

      <!-- Estadísticas por Grupos y Acciones Rápidas -->
      <div class="row">
        <div class="col-lg-8">
          <div class="chart-container">
            <h4 class="mb-4"><i class="bi bi-people-fill me-2"></i>Distribución de Personas por Grupo</h4>
            <div class="table-responsive groups-table">
              <table class="table table-hover">
                <thead class="table-primary">
                  <tr>
                    <th>Grupo</th>
                    <th class="text-center">Personas</th>
                    <th class="text-center">Límite</th>
                    <th class="text-center">Disponibles</th>
                    <th class="text-center">% Ocupación</th>
                    <th class="text-center">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($grupos_stats as $grupo): ?>
                    <?php
                    $disponibles = $grupo['limite_personas'] - $grupo['total_personas'];
                    $porcentaje = $grupo['limite_personas'] > 0 ? round(($grupo['total_personas'] / $grupo['limite_personas']) * 100, 1) : 0;

                    // Determinar el color según la ocupación
                    if ($porcentaje >= 90) {
                      $badge_class = 'bg-danger';
                      $estado = 'Lleno';
                    } elseif ($porcentaje >= 70) {
                      $badge_class = 'bg-warning';
                      $estado = 'Alto';
                    } elseif ($porcentaje >= 40) {
                      $badge_class = 'bg-info';
                      $estado = 'Medio';
                    } else {
                      $badge_class = 'bg-success';
                      $estado = 'Bajo';
                    }
                    ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($grupo['descripcion_grupo']); ?></strong>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-primary fs-6"><?php echo $grupo['total_personas']; ?></span>
                      </td>
                      <td class="text-center">
                        <?php echo $grupo['limite_personas']; ?>
                      </td>
                      <td class="text-center">
                        <span class="<?php echo $disponibles <= 0 ? 'text-danger' : 'text-success'; ?>">
                          <?php echo max(0, $disponibles); ?>
                        </span>
                      </td>
                      <td class="text-center">
                        <?php echo $porcentaje; ?>%
                      </td>
                      <td class="text-center">
                        <span class="badge <?php echo $badge_class; ?>"><?php echo $estado; ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Menu Script -->
  <script src="menu/script.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarOpen = document.getElementById('sidebarOpen');
      const mainContent = document.getElementById('mainContent');
      const topSidebar = document.getElementById('topSidebar');
      const sidebar = document.querySelector('.sidebar');

      // Función para actualizar el estado del contenido principal y barra superior
      function updateMainContentState() {
        // El sidebar siempre está presente, pero cambia su ancho
        // Cuando está cerrado (close), ocupa 80px
        // Cuando está abierto, ocupa 350px
        if (sidebar && sidebar.classList.contains('close')) {
          // Sidebar colapsado (80px)
          mainContent.classList.remove('sidebar-open');
          if (topSidebar) topSidebar.classList.remove('sidebar-open');
        } else {
          // Sidebar expandido (350px)
          mainContent.classList.add('sidebar-open');
          if (topSidebar) topSidebar.classList.add('sidebar-open');
        }
      }

      // Configurar el estado inicial - sidebar cerrado por defecto
      updateMainContentState();

      // Escuchar eventos de hover en el sidebar
      if (sidebar) {
        sidebar.addEventListener('mouseenter', function() {
          if (sidebar.classList.contains('hoverable')) {
            setTimeout(updateMainContentState, 50);
          }
        });

        sidebar.addEventListener('mouseleave', function() {
          if (sidebar.classList.contains('hoverable')) {
            setTimeout(updateMainContentState, 50);
          }
        });
      }

      // Escuchar clicks en el botón del menú
      if (sidebarOpen && mainContent) {
        sidebarOpen.addEventListener('click', function() {
          setTimeout(updateMainContentState, 150);
        });
      }

      // Observer para detectar cambios en las clases del sidebar
      if (sidebar) {
        const observer = new MutationObserver(function(mutations) {
          mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
              setTimeout(updateMainContentState, 50);
            }
          });
        });

        observer.observe(sidebar, {
          attributes: true,
          attributeFilter: ['class']
        });
      }

      // Manejar redimensionamiento de ventana
      window.addEventListener('resize', function() {
        updateMainContentState();
      });
    });
  </script>
</body>

</html>