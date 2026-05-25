<?php
session_start();
include("../../conexion.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$numero_grupo = isset($_GET['numero_grupo']) ? intval($_GET['numero_grupo']) : 0;
if ($numero_grupo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Número de grupo inválido.']);
    exit;
}

// Obtener todos los registros de ese grupo con datos de persona
$sql = "SELECT rcv.id_registro_centro_vida, rcv.cedula_persona, rcv.id_condicion, rcv.condicion_otra,
               rcv.id_meta, rcv.id_actividad, rcv.id_accion, rcv.id_actividad_centro_vida,
               rcv.politica_publica, rcv.departamento_procedencia, rcv.observacion,
               rcv.profesion, rcv.jornada, rcv.numero_grupo,
               CONCAT(p.nombres_persona, ' ', p.apellidos_persona) AS nombre_completo
        FROM registro_centro_vida rcv
        LEFT JOIN personas p ON rcv.cedula_persona = p.cedula_persona
        WHERE rcv.numero_grupo = ?
        ORDER BY rcv.id_registro_centro_vida ASC";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar consulta: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param('i', $numero_grupo);
$stmt->execute();
$result = $stmt->get_result();
$registros = [];
while ($row = $result->fetch_assoc()) {
    $registros[] = $row;
}
$stmt->close();

if (empty($registros)) {
    echo json_encode(['success' => false, 'message' => 'No se encontraron registros para el grupo ' . $numero_grupo . '.']);
    exit;
}

// Obtener las fechas del primer registro para precargar el formulario
$id_primer_reg = $registros[0]['id_registro_centro_vida'];
$fechas_sql = "SELECT fecha_atencion FROM registro_centro_vida_fechas WHERE id_registro_centro_vida = ?";
$stmt_f = $mysqli->prepare($fechas_sql);
$fechas = [];
if ($stmt_f) {
    $stmt_f->bind_param('i', $id_primer_reg);
    $stmt_f->execute();
    $res_f = $stmt_f->get_result();
    while ($rf = $res_f->fetch_assoc()) {
        $fechas[] = $rf['fecha_atencion'];
    }
    $stmt_f->close();
}

// Usar datos del primer registro como plantilla del formulario
$plantilla = $registros[0];

echo json_encode([
    'success'      => true,
    'numero_grupo' => $numero_grupo,
    'registros'    => $registros,
    'plantilla'    => $plantilla,
    'fechas'       => $fechas
]);
