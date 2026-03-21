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

// Filtros
$filtro_cedula = isset($_GET['cedula']) ? $mysqli->real_escape_string($_GET['cedula']) : '';
$filtro_nombre = isset($_GET['nombre']) ? $mysqli->real_escape_string($_GET['nombre']) : '';
$filtro_condicion = isset($_GET['condicion']) ? $mysqli->real_escape_string($_GET['condicion']) : '';

// Construir WHERE
$where = "1=1";

if($tipo_usuario == 9) {
    $where .= " AND r.usuario_registro = '$usuario_id'";
}

if($filtro_cedula != '') {
    $where .= " AND p.cedula LIKE '%$filtro_cedula%'";
}

if($filtro_nombre != '') {
    $where .= " AND CONCAT(p.nombre, ' ', p.apellido) LIKE '%$filtro_nombre%'";
}

if($filtro_condicion != '') {
    $where .= " AND r.id_condicion = '$filtro_condicion'";
}

// Consulta principal (usando tablas generales)
$sql = "SELECT r.*, 
        CONCAT(p.nombres_persona_cm, ' ', p.apellidos_persona_cm) as persona_nombre,
        p.cedula_persona_cm as cedula,
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
        ORDER BY r.fecha_registro DESC, r.id_registro_individual_cm DESC";

$result = $mysqli->query($sql);

if($result->num_rows > 0) {
?>
    <table id="registrosTable" class="table modern-table table-hover">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Persona</th>
                <th>Condición</th>
                <th>Meta</th>
                <th>Actividad</th>
                <th>Acción</th>
                <th>Política Pública</th>
                <th>Fecha Registro</th>
                <th>Registrado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                <td><?php echo htmlspecialchars($row['persona_nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['condicion'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['meta'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['actividad'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['accion'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['politica_publica'] ?? 'N/A'); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                <td><?php echo htmlspecialchars($row['usuario'] ?? 'N/A'); ?></td>
                <td class="col-actions">
                    <div class="action-buttons">
                        <button type="button" class="btn-action btn-edit" 
                                title="Editar registro"
                                onclick="editarRegistro(<?php echo $row['id_registro_individual_cm']; ?>)">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button type="button" class="btn-action btn-delete" 
                                title="Eliminar registro"
                                onclick="eliminarRegistro(<?php echo $row['id_registro_individual_cm']; ?>)">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php
} else {
    echo '<div class="alert alert-info">No se encontraron registros</div>';
}
?>
