<?php
/**
 * Script de reparación: convierte nombres en funcionario_registro al ID del usuario correspondiente.
 * Ejecutar UNA sola vez desde el navegador o CLI.
 * Acceso protegido por sesión de administrador.
 */
session_start();
include("../../conexion.php");

// Solo admins (ajusta los tipos según tu sistema)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? (int)$_SESSION['tipo_usuario'] : 0;
if (!in_array($tipo_usuario, [1, 2, 3])) {
    http_response_code(403);
    die("Acceso denegado. Solo administradores pueden ejecutar este script.");
}

header('Content-Type: text/html; charset=utf-8');

$dry_run = !isset($_GET['ejecutar']); // Sin ?ejecutar=1 solo muestra qué haría

echo "<h2>Reparación de funcionario_registro — registro_centro_vida</h2>";
echo $dry_run ? "<p><strong style='color:orange'>MODO SIMULACIÓN</strong> (sin cambios reales). Añade <code>?ejecutar=1</code> a la URL para aplicar.</p>" : "<p><strong style='color:green'>MODO EJECUCIÓN</strong></p>";

// Traer todos los registros donde funcionario_registro NO es numérico
$sql = "SELECT id_registro_centro_vida, funcionario_registro
        FROM registro_centro_vida
        WHERE funcionario_registro IS NOT NULL
          AND funcionario_registro != ''
          AND funcionario_registro REGEXP '^[^0-9]'";

$result = $mysqli->query($sql);
if (!$result) {
    die("Error en consulta: " . $mysqli->error);
}

$total       = 0;
$actualizados = 0;
$sin_match   = [];
$log         = [];

while ($row = $result->fetch_assoc()) {
    $total++;
    $id_reg  = (int)$row['id_registro_centro_vida'];
    $nombre  = trim($row['funcionario_registro']);

    // Buscar usuario que coincida con LIKE
    $nombre_esc = $mysqli->real_escape_string($nombre);
    $sql_user = "SELECT id, nombre FROM usuarios WHERE nombre LIKE '%{$nombre_esc}%' LIMIT 1";
    $res_user = $mysqli->query($sql_user);

    if ($res_user && $user = $res_user->fetch_assoc()) {
        $id_usuario = (int)$user['id'];
        $log[] = [
            'id_registro' => $id_reg,
            'nombre_actual' => $nombre,
            'usuario_encontrado' => $user['nombre'],
            'id_usuario' => $id_usuario,
            'accion' => 'ACTUALIZAR'
        ];

        if (!$dry_run) {
            $sql_upd = "UPDATE registro_centro_vida
                        SET funcionario_registro = ?
                        WHERE id_registro_centro_vida = ?";
            $stmt = $mysqli->prepare($sql_upd);
            $stmt->bind_param('ii', $id_usuario, $id_reg);
            $stmt->execute();
            $stmt->close();
        }
        $actualizados++;
    } else {
        $sin_match[] = ['id_registro' => $id_reg, 'nombre' => $nombre];
        $log[] = [
            'id_registro' => $id_reg,
            'nombre_actual' => $nombre,
            'usuario_encontrado' => '—',
            'id_usuario' => '—',
            'accion' => 'SIN COINCIDENCIA'
        ];
    }
}

// Mostrar resultados
echo "<p>Registros con nombre en funcionario_registro: <strong>{$total}</strong></p>";
echo "<p>" . ($dry_run ? "Se actualizarían" : "Actualizados") . ": <strong style='color:green'>{$actualizados}</strong></p>";
echo "<p>Sin coincidencia de usuario: <strong style='color:red'>" . count($sin_match) . "</strong></p>";

echo "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;font-size:13px'>";
echo "<tr style='background:#ddd'>
        <th>id_registro</th>
        <th>Nombre almacenado</th>
        <th>Usuario encontrado</th>
        <th>ID a asignar</th>
        <th>Acción</th>
      </tr>";
foreach ($log as $entry) {
    $color = $entry['accion'] === 'ACTUALIZAR' ? '#e8f5e9' : '#fff3e0';
    echo "<tr style='background:{$color}'>
            <td>{$entry['id_registro']}</td>
            <td>" . htmlspecialchars($entry['nombre_actual']) . "</td>
            <td>" . htmlspecialchars($entry['usuario_encontrado']) . "</td>
            <td>{$entry['id_usuario']}</td>
            <td>{$entry['accion']}</td>
          </tr>";
}
echo "</table>";

if ($dry_run && $total > 0) {
    echo "<br><a href='?ejecutar=1' style='padding:10px 20px;background:#e91e63;color:white;text-decoration:none;border-radius:6px'>Aplicar cambios</a>";
}

$mysqli->close();
?>
