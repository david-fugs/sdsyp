<?php
// No iniciar sesión aquí - ya está iniciada en formIndividualCM.php
// session_start();

// Verificar que venimos de una sesión válida
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    exit();
}

// La conexión ya debería estar disponible desde el archivo padre
if (!isset($mysqli)) {
    include("../../conexion.php");
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id = $_SESSION['id'];

// Filtros - los nombres deben coincidir con los campos del formulario en formIndividualCM.php
$filtro_cedula = isset($_GET['cedula_persona']) ? $mysqli->real_escape_string(trim($_GET['cedula_persona'])) : '';
$filtro_nombre = isset($_GET['nombre']) ? $mysqli->real_escape_string(trim($_GET['nombre'])) : '';
$filtro_condicion = isset($_GET['condicion']) && is_numeric($_GET['condicion']) ? intval($_GET['condicion']) : 0;
$filtro_tipo_usuario = isset($_GET['filtro_tipo_usuario']) && is_numeric($_GET['filtro_tipo_usuario']) ? intval($_GET['filtro_tipo_usuario']) : 0;

// Construir WHERE
$where = "1=1";

if ($tipo_usuario == 9) {
    $where .= " AND r.usuario_registro = " . intval($usuario_id);
}

if ($filtro_cedula !== '') {
    $where .= " AND p.cedula_persona_cm LIKE '%" . $filtro_cedula . "%'";
}

if ($filtro_nombre !== '') {
    $where .= " AND CONCAT(p.nombres_persona_cm, ' ', p.apellidos_persona_cm) LIKE '%" . $filtro_nombre . "%'";
}

if ($filtro_condicion > 0) {
    $where .= " AND r.id_condicion = " . $filtro_condicion;
}

if ($filtro_tipo_usuario > 0) {
    $where .= " AND u.tipo_usuario = " . $filtro_tipo_usuario;
}

// Consulta principal
$sql = "SELECT r.id_registro_individual_cm,
        r.fecha_registro_actividad,
        p.cedula_persona_cm,
        CONCAT(p.nombres_persona_cm, ' ', p.apellidos_persona_cm) as persona_nombre,
        c.descripcion_condicion as condicion,
        m.descripcion_meta as meta,
        act.descripcion_actividad as actividad,
        acc.descripcion_accion as accion,
        pp.descripcion_politica as politica_publica,
        u.nombre as usuario
        FROM registros_individuales_cm r
        INNER JOIN personas_colombia_mayor p ON r.cedula_persona_cm = p.cedula_persona_cm
        LEFT JOIN condiciones_componente c ON r.id_condicion = c.id_condicion
        LEFT JOIN metas m ON r.id_meta = m.id_meta
        LEFT JOIN actividades act ON r.id_actividad = act.id_actividad
        LEFT JOIN acciones acc ON r.id_accion = acc.id_accion
        LEFT JOIN politicas_publicas pp ON r.id_politica_publica = pp.id_politica
        LEFT JOIN usuarios u ON r.usuario_registro = u.id
        WHERE $where
        ORDER BY r.fecha_registro_actividad DESC, r.id_registro_individual_cm DESC";

$result = $mysqli->query($sql);

// Función para truncar texto con botón "ver más"
function truncateCM($text, $max = 40) {
    $text = $text ?? '';
    if (mb_strlen($text) <= $max) {
        return '<span>' . htmlspecialchars($text) . '</span>';
    }
    $short = htmlspecialchars(mb_substr($text, 0, $max));
    $full  = htmlspecialchars($text, ENT_QUOTES);
    return '<span class="cm-texto-corto">' . $short
         . '... <button type="button" class="btn-ver-mas-cm" data-full="' . $full
         . '" onclick="verMasCM(this)" title="' . $full . '"><i class="bi bi-eye-fill"></i> ver</button></span>';
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fecha = !empty($row['fecha_registro_actividad'])
            ? date('d/m/Y', strtotime($row['fecha_registro_actividad']))
            : 'N/A';
?>
        <tr>
            <td><?= htmlspecialchars($row['cedula_persona_cm'] ?? '') ?></td>
            <td><?= truncateCM($row['persona_nombre'] ?? '', 30) ?></td>
            <td><?= truncateCM($row['condicion'] ?? 'N/A', 35) ?></td>
            <td><?= truncateCM($row['meta'] ?? 'N/A', 38) ?></td>
            <td><?= truncateCM($row['actividad'] ?? 'N/A', 38) ?></td>
            <td><?= truncateCM($row['accion'] ?? 'N/A', 35) ?></td>
            <td><?= truncateCM($row['politica_publica'] ?? 'N/A', 38) ?></td>
            <td><?= $fecha ?></td>
            <td><?= htmlspecialchars($row['usuario'] ?? 'N/A') ?></td>
            <td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit"
                            title="Editar registro"
                            onclick="editarRegistro(<?= $row['id_registro_individual_cm'] ?>)">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button type="button" class="btn-action btn-delete"
                            title="Eliminar registro"
                            onclick="eliminarRegistro(<?= $row['id_registro_individual_cm'] ?>)">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </td>
        </tr>
<?php
    }
} else {
    echo '<tr><td colspan="10" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No se encontraron registros</td></tr>';
}
?>
