<?php
session_start();
require_once('../filtros_grupos.php');
require_once('../filtros_grupo_usuario.php');
include("../../conexion.php");

// Aplicar filtro de grupos según tipo de usuario
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;

// Cargar listas necesarias
$usuarios = $mysqli->query("SELECT id, nombre FROM usuarios WHERE tipo_usuario = 3 ORDER BY nombre ASC");

// Filtros
$filtro_dia = isset($_GET['filtro_dia']) ? intval($_GET['filtro_dia']) : '';
$filtro_fecha = isset($_GET['filtro_fecha']) ? $mysqli->real_escape_string($_GET['filtro_fecha']) : '';
$filtro_mes = isset($_GET['filtro_mes']) ? intval($_GET['filtro_mes']) : '';
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_funcionario = isset($_GET['filtro_funcionario']) ? intval($_GET['filtro_funcionario']) : '';

// Variables para mostrar estadísticas
$total_masivas_masculino = 0;
$total_masivas_femenino = 0;
$total_masivas = 0;
$total_individuales = 0;
$total_general = 0;

// Construir WHERE para masivas
$where_masivas = '';
if ($filtro_dia) {
    $where_masivas .= " AND DAY(mcv.fecha_atencion) = $filtro_dia";
}
if ($filtro_fecha) {
    $where_masivas .= " AND mcv.fecha_atencion = '$filtro_fecha'";
}
if ($filtro_mes) {
    $where_masivas .= " AND MONTH(mcv.fecha_atencion) = $filtro_mes";
}
if ($filtro_anio) {
    $where_masivas .= " AND YEAR(mcv.fecha_atencion) = $filtro_anio";
}
if ($filtro_funcionario) {
    $where_masivas .= " AND mcv.id_usuario = $filtro_funcionario";
}

// Construir WHERE para individuales
$where_individuales = '';
if ($filtro_fecha) {
    // Fecha específica tiene prioridad
    $where_individuales .= " AND rcvf.fecha_atencion = '$filtro_fecha'";
} else {
    // Aplicar filtros de día, mes, año individualmente
    if ($filtro_dia) {
        $where_individuales .= " AND DAY(rcvf.fecha_atencion) = $filtro_dia";
    }
    if ($filtro_mes) {
        $where_individuales .= " AND MONTH(rcvf.fecha_atencion) = $filtro_mes";
    }
    if ($filtro_anio) {
        $where_individuales .= " AND YEAR(rcvf.fecha_atencion) = $filtro_anio";
    }
}

// Aplicar filtro por grupo de usuario (tipo 11: INGENIERO CENTRO VIDA)
// Para masivas, el filtro se aplica sobre la tabla grupos (g) que está relacionada con id_centro_vida
$where_grupo_usuario_masivas = '';
if (debeAplicarFiltroGrupo($tipo_usuario) && isset($_SESSION['id_grupo'])) {
    $id_grupo = intval($_SESSION['id_grupo']);
    $where_grupo_usuario_masivas = " AND mcv.id_centro_vida = $id_grupo";
}

// Consulta para actividades masivas
$query_masivas = "SELECT 
    SUM(mcv.cantidad_masculino) as total_masculino,
    SUM(mcv.cantidad_femenino) as total_femenino,
    COUNT(*) as num_registros
FROM masiva_centro_vida mcv
WHERE 1 $where_masivas $where_grupo_usuario_masivas";

$result_masivas = $mysqli->query($query_masivas);
if ($result_masivas && $row = $result_masivas->fetch_assoc()) {
    $total_masivas_masculino = intval($row['total_masculino']);
    $total_masivas_femenino = intval($row['total_femenino']);
    $total_masivas = $total_masivas_masculino + $total_masivas_femenino;
}

// Aplicar filtro por grupo de usuario para individuales
// Para individuales, el filtro se aplica sobre la tabla personas (p) que está relacionada con cedula_persona
$where_grupo_usuario_individuales = obtenerCondicionFiltroGrupo('p');

// Consulta para actividades individuales - contar cada fecha individual
$query_individuales = "SELECT 
    COUNT(*) as total_individuales
FROM registro_centro_vida rcv
INNER JOIN personas p ON rcv.cedula_persona = p.cedula_persona
INNER JOIN registro_centro_vida_fechas rcvf ON rcv.id_registro_centro_vida = rcvf.id_registro_centro_vida
WHERE 1 $where_individuales $where_grupo_usuario_individuales";

$result_individuales = $mysqli->query($query_individuales);
if ($result_individuales && $row = $result_individuales->fetch_assoc()) {
    $total_individuales = intval($row['total_individuales']);
}

$total_general = $total_masivas + $total_individuales;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>SDSYP - Comparador Actividades</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-size: 16px;
            background: #f8fafc
        }

        .modern-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
            overflow: hidden;
            margin: 20px auto;
            max-width: 1400px
        }

        .modern-header {
            background: linear-gradient(135deg, #e91e63, #9c27b0);
            color: #fff;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px
        }

        .modern-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600
        }

        .btn-modern {
            background: rgba(255, 255, 255, .2);
            border: 2px solid rgba(255, 255, 255, .3);
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: .3s
        }

        .btn-modern:hover {
            background: rgba(255, 255, 255, .3);
            border-color: rgba(255, 255, 255, .5);
            transform: translateY(-2px);
            color: #fff
        }

        .modern-filters {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px
        }

        .modern-input,
        .modern-select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px
        }

        .stats-container {
            padding: 30px 20px;
            background: #fff;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            padding: 20px;
            color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .1);
        }

        .stat-card.masivas {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card.individuales {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card.total {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-card h3 {
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 10px 0;
            opacity: 0.9;
        }

        .stat-card .value {
            font-size: 36px;
            font-weight: 700;
            margin: 0;
        }

        .stat-card .detail {
            font-size: 12px;
            margin-top: 8px;
            opacity: 0.9;
        }

        .comparison-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .comparison-section h4 {
            color: #374151;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .comparison-bar {
            display: flex;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        .comparison-bar .masivas-bar {
            background: #f5576c;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            transition: all 0.3s;
        }

        .comparison-bar .individuales-bar {
            background: #00f2fe;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            transition: all 0.3s;
        }

        .filter-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            color: #856404;
        }

        .filter-info i {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <center style="margin-top:20px"><img src='../../img/logo.png' width="150" height="120" /></center>
    <h1 style="color:#e91e63;text-shadow:#fff 0.1em 0.1em 0.2em;font-size:44px;text-align:center;font-weight:bold">
        <i class="bi bi-bar-chart-fill"></i> COMPARADOR DE ACTIVIDADES
    </h1>

    <div class="container mt-5">
        <div class="modern-container">
            <div class="modern-header">
                <h2><i class="bi bi-bar-chart-fill"></i> Comparación de Actividades</h2>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <form id="exportForm" action="exportComparadorActividades.php" method="get" style="display:inline;">
                        <input type="hidden" name="filtro_dia" id="export_filtro_dia">
                        <input type="hidden" name="filtro_fecha" id="export_filtro_fecha">
                        <input type="hidden" name="filtro_mes" id="export_filtro_mes">
                        <input type="hidden" name="filtro_anio" id="export_filtro_anio">
                        <input type="hidden" name="filtro_funcionario" id="export_filtro_funcionario">
                        <button type="submit" class="btn-modern" style="background:rgba(255,255,255,.25)">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </button>
                    </form>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const sync = () => {
                        export_filtro_dia.value = filtro_dia.value;
                        export_filtro_fecha.value = filtro_fecha.value;
                        export_filtro_mes.value = filtro_mes.value;
                        export_filtro_anio.value = filtro_anio.value;
                        export_filtro_funcionario.value = filtro_funcionario.value;
                    };
                    ['change'].forEach(ev => ['filtro_dia', 'filtro_fecha', 'filtro_mes', 'filtro_anio', 'filtro_funcionario'].forEach(id => {
                        const elem = document.getElementById(id);
                        if (elem) elem.addEventListener(ev, sync);
                    }));
                    sync();
                });
            </script>

            <!-- Mensaje informativo de filtro por grupo -->
            <?php echo generarMensajeFiltroGrupo($mysqli); ?>

            <div class="modern-filters">
                <form action="comparadorActividades.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="filtro_dia">Día (Opcional)</label>
                        <input type="number" id="filtro_dia" name="filtro_dia" class="modern-input" min="1" max="31" 
                               placeholder="1-31" value="<?= htmlspecialchars($filtro_dia) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="filtro_fecha">Fecha Específica</label>
                        <input type="date" id="filtro_fecha" name="filtro_fecha" class="modern-input" 
                               value="<?= htmlspecialchars($filtro_fecha) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="filtro_mes">Mes</label>
                        <select id="filtro_mes" name="filtro_mes" class="modern-select">
                            <option value="">Todos</option>
                            <?php 
                            $meses = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                            ];
                            foreach ($meses as $num => $nom) {
                                $sel = $filtro_mes == $num ? 'selected' : '';
                                echo "<option value='$num' $sel>$nom</option>";
                            } 
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_anio">Año</label>
                        <select id="filtro_anio" name="filtro_anio" class="modern-select">
                            <option value="">Todos</option>
                            <?php 
                            $currentYear = date('Y');
                            for ($y = 2023; $y <= $currentYear; $y++) {
                                $sel = $filtro_anio == $y ? 'selected' : '';
                                echo "<option value='$y' $sel>$y</option>";
                            } 
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_funcionario">Funcionario</label>
                        <select id="filtro_funcionario" name="filtro_funcionario" class="modern-select">
                            <option value="">Todos</option>
                            <?php 
                            if ($usuarios) {
                                while ($u = $usuarios->fetch_assoc()) {
                                    $sel = $filtro_funcionario == $u['id'] ? 'selected' : '';
                                    echo "<option value='{$u['id']}' $sel>" . htmlspecialchars($u['nombre']) . "</option>";
                                }
                            } 
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-modern" style="background:#10b981;border-color:#10b981">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>

            <div class="stats-container">
                <?php if ($filtro_dia || $filtro_fecha || $filtro_mes || $filtro_anio): ?>
                <div class="filter-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>Filtros aplicados:</strong>
                    <?php 
                    $filtros_activos = [];
                    if ($filtro_dia) $filtros_activos[] = "Día: $filtro_dia";
                    if ($filtro_fecha) $filtros_activos[] = "Fecha: $filtro_fecha";
                    if ($filtro_mes) $filtros_activos[] = "Mes: " . $meses[$filtro_mes];
                    if ($filtro_anio) $filtros_activos[] = "Año: $filtro_anio";
                    if ($filtro_funcionario) {
                        mysqli_data_seek($usuarios, 0);
                        while ($u = $usuarios->fetch_assoc()) {
                            if ($u['id'] == $filtro_funcionario) {
                                $filtros_activos[] = "Funcionario: " . htmlspecialchars($u['nombre']);
                                break;
                            }
                        }
                    }
                    echo implode(' | ', $filtros_activos);
                    ?>
                </div>
                <?php endif; ?>

                <div class="stats-row">
                    <div class="stat-card masivas">
                        <h3>ACTIVIDADES MASIVAS</h3>
                        <div class="value"><?= number_format($total_masivas) ?></div>
                        <div class="detail">
                            Masculino: <?= number_format($total_masivas_masculino) ?> | 
                            Femenino: <?= number_format($total_masivas_femenino) ?>
                        </div>
                    </div>

                    <div class="stat-card individuales">
                        <h3>ACTIVIDADES INDIVIDUALES</h3>
                        <div class="value"><?= number_format($total_individuales) ?></div>
                        <div class="detail">Total de fechas de atención</div>
                    </div>

                    <div class="stat-card total">
                        <h3>TOTAL GENERAL</h3>
                        <div class="value"><?= number_format($total_general) ?></div>
                        <div class="detail">Suma de masivas e individuales</div>
                    </div>
                </div>

                <?php if ($total_general > 0): ?>
                <div class="comparison-section">
                    <h4><i class="bi bi-pie-chart-fill"></i> Comparación Visual</h4>
                    <div class="comparison-bar">
                        <?php 
                        $porcentaje_masivas = ($total_masivas / $total_general) * 100;
                        $porcentaje_individuales = ($total_individuales / $total_general) * 100;
                        ?>
                        <div class="masivas-bar" style="width: <?= $porcentaje_masivas ?>%">
                            Masivas: <?= number_format($porcentaje_masivas, 1) ?>%
                        </div>
                        <div class="individuales-bar" style="width: <?= $porcentaje_individuales ?>%">
                            Individuales: <?= number_format($porcentaje_individuales, 1) ?>%
                        </div>
                    </div>
                    <div style="margin-top: 15px; text-align: center; color: #6b7280;">
                        <p style="margin: 0;">
                            <strong>Proporción:</strong> 
                            Por cada actividad masiva hay <?= $total_individuales > 0 && $total_masivas > 0 ? number_format($total_individuales / $total_masivas, 2) : 0 ?> actividades individuales
                        </p>
                    </div>
                </div>
                <?php else: ?>
                <div class="comparison-section">
                    <p style="text-align: center; color: #6b7280; font-style: italic;">
                        No hay datos para mostrar con los filtros seleccionados.
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <br>
    <center><a href='../../access.php'><img src='../../img/atras.png' width='72' height='72'></a></center><br>

</body>

</html>
