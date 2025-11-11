<?php
// Este archivo puede ser incluido o llamado vía AJAX
session_start();
include("../../conexion.php");

// Verificar acceso
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    echo "<tr><td colspan='8' class='text-center text-danger'>Acceso denegado</td></tr>";
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id'];

// Construir query base
$where = "WHERE 1=1";

// Si es contratista (tipo 9), solo ver sus propios registros
if ($tipo_usuario == 9) {
    $where .= " AND usuario_registro = '$id_usuario'";
}

// Filtro por cédula
if (!empty($_GET['cedula'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula']);
    $where .= " AND cedula_persona_cm = '$cedula'";
}

// Filtro por nombre
if (!empty($_GET['nombre'])) {
    $nombre = $mysqli->real_escape_string($_GET['nombre']);
    $where .= " AND (nombres_persona_cm LIKE '%$nombre%' OR apellidos_persona_cm LIKE '%$nombre%')";
}

// Filtro por estado
if (!empty($_GET['estado'])) {
    $estado = $mysqli->real_escape_string($_GET['estado']);
    $where .= " AND estado_cm = '$estado'";
}

// Consulta SQL
$query = "
    SELECT 
        p.*,
        u.nombre AS nombre_contratista
    FROM personas_colombia_mayor p
    LEFT JOIN usuarios u ON p.usuario_registro = u.id
    $where
    ORDER BY p.apellidos_persona_cm ASC, p.nombres_persona_cm ASC
";

$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Calcular edad si tiene fecha de nacimiento
        $edad_texto = 'N/A';
        if ($row['fecha_nacimiento_cm'] && $row['fecha_nacimiento_cm'] != '0000-00-00') {
            $hoy = new DateTime();
            $nacimiento = new DateTime($row['fecha_nacimiento_cm']);
            $edad = $hoy->diff($nacimiento)->y;
            $edad_texto = $edad . ' años';
        } elseif ($row['edad_cm']) {
            $edad_texto = $row['edad_cm'] . ' años';
        }

        // Determinar badge de estado
        $badge_class = '';
        $estado_icon = '';
        switch ($row['estado_cm']) {
            case 'ACTIVO':
                $badge_class = 'status-badge status-active';
                $estado_icon = '<i class="bi bi-check-circle-fill"></i>';
                break;
            case 'SUSPENDIDO':
                $badge_class = 'status-badge status-warning';
                $estado_icon = '<i class="bi bi-pause-circle-fill"></i>';
                break;
            case 'FALLECIDO':
                $badge_class = 'status-badge status-secondary';
                $estado_icon = '<i class="bi bi-x-circle-fill"></i>';
                break;
            case 'RETIRO_VOLUNTARIO':
                $badge_class = 'status-badge status-info';
                $estado_icon = '<i class="bi bi-arrow-left-circle-fill"></i>';
                break;
        }

        // Formatear fecha de ingreso
        $fecha_ingreso = 'N/A';
        if ($row['fecha_ingreso_cm'] && $row['fecha_ingreso_cm'] != '0000-00-00') {
            $fecha_ingreso = date('d/m/Y', strtotime($row['fecha_ingreso_cm']));
        }

        echo "<tr class='fade-in'>";
        echo "<td><strong>" . htmlspecialchars($row['cedula_persona_cm']) . "</strong></td>";
        echo "<td>";
        echo "<b>" . htmlspecialchars($row['nombres_persona_cm'] . ' ' . $row['apellidos_persona_cm']) . "</b><br>";
        echo "<span class='cm-badge'><i class='bi bi-award-fill'></i> Colombia Mayor</span>";
        echo "</td>";
        echo "<td>" . htmlspecialchars($row['genero_persona_cm'] ?? 'N/A') . "</td>";
        echo "<td><span class='badge bg-primary'>" . $edad_texto . "</span></td>";
        echo "<td>" . htmlspecialchars($row['telefono_persona_cm'] ?? 'N/A') . "</td>";
        echo "<td>" . $fecha_ingreso . "</td>";
        echo "<td class='col-status'><span class='$badge_class'>$estado_icon " . str_replace('_', ' ', $row['estado_cm']) . "</span></td>";

        // Botones de acción
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar persona"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-cedula="' . htmlspecialchars($row['cedula_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-tipo-identificacion="' . htmlspecialchars($row['tipo_identificacion_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-nombres="' . htmlspecialchars($row['nombres_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-apellidos="' . htmlspecialchars($row['apellidos_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-genero="' . htmlspecialchars($row['genero_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-telefono="' . htmlspecialchars($row['telefono_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-telefono-referencia="' . htmlspecialchars($row['telefono_referencia_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-referencia="' . htmlspecialchars($row['referencia_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-correo="' . htmlspecialchars($row['correo_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-fecha-nacimiento="' . htmlspecialchars($row['fecha_nacimiento_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-edad="' . htmlspecialchars($row['edad_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-grupo-sisben="' . htmlspecialchars($row['grupo_sisben'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-direccion="' . htmlspecialchars($row['direccion_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-barrio="' . htmlspecialchars($row['barrio_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-comuna="' . htmlspecialchars($row['comuna_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-zona="' . htmlspecialchars($row['zona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-departamento="' . htmlspecialchars($row['departamento_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-municipio="' . htmlspecialchars($row['municipio_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-eps="' . htmlspecialchars($row['eps'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-peso="' . htmlspecialchars($row['peso'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-talla="' . htmlspecialchars($row['talla'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-patologias="' . htmlspecialchars($row['patologias'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-factores-riesgo="' . htmlspecialchars($row['factores_riesgo'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-factores-preventivos="' . htmlspecialchars($row['factores_preventivos'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-ingresos-economicos="' . htmlspecialchars($row['ingresos_economicos'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-convivencia-actual="' . htmlspecialchars($row['convivencia_actual'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-resultado-actividad="' . htmlspecialchars($row['resultado_actividad'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-remision="' . htmlspecialchars($row['remision'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-persona-discapacidad="' . htmlspecialchars($row['persona_discapacidad'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-cual-discapacidad="' . htmlspecialchars($row['cual_discapacidad'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-cabeza-hogar="' . htmlspecialchars($row['cabeza_hogar'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-lider-comunidad="' . htmlspecialchars($row['lider_comunidad'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-se-reconoce-como="' . htmlspecialchars($row['se_reconoce_como'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-orientacion-sexual="' . htmlspecialchars($row['orientacion_sexual'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-experiencia-migratoria="' . htmlspecialchars($row['experiencia_migratoria'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-grupo-etnico="' . htmlspecialchars($row['grupo_etnico'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-tipo-salud="' . htmlspecialchars($row['tipo_salud'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-nivel-educativo="' . htmlspecialchars($row['nivel_educativo'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-condicion-ocupacion="' . htmlspecialchars($row['condicion_ocupacion'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-condicion-componente="' . htmlspecialchars($row['condicion_componente'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-fecha-ingreso="' . htmlspecialchars($row['fecha_ingreso_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-estado="' . htmlspecialchars($row['estado_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-id-meta="' . htmlspecialchars($row['id_meta'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-id-actividad="' . htmlspecialchars($row['id_actividad'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-id-accion="' . htmlspecialchars($row['id_accion'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-id-politica-publica="' . htmlspecialchars($row['id_politica_publica'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                        data-observaciones="' . htmlspecialchars($row['observaciones_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button type="button" class="btn-action btn-delete" 
                        title="Eliminar persona"
                        data-cedula="' . htmlspecialchars($row['cedula_persona_cm']) . '">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <a href="exportPersonaCM.php?cedula=' . htmlspecialchars($row['cedula_persona_cm']) . '" 
                       class="btn-action btn-info" 
                       title="Exportar a Excel">
                        <i class="bi bi-file-earmark-excel"></i>
                    </a>
                </div>
              </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' class='text-center'>No se encontraron registros</td></tr>";
}
?>
