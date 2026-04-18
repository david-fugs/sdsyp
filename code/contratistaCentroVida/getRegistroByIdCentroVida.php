<?php
include("../../conexion.php");
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT rcv.*, GROUP_CONCAT(rcvf.fecha_atencion ORDER BY rcvf.fecha_atencion ASC SEPARATOR ',') AS fechas,
               GROUP_CONCAT(DISTINCT rcvge.id_grupo_externo ORDER BY rcvge.id_grupo_externo ASC SEPARATOR ',') AS ids_grupos_externos
        FROM registro_centro_vida rcv
        LEFT JOIN registro_centro_vida_fechas rcvf ON rcv.id_registro_centro_vida = rcvf.id_registro_centro_vida
        LEFT JOIN registro_centro_vida_grupo_externo rcvge ON rcv.id_registro_centro_vida = rcvge.id_registro_centro_vida
        WHERE rcv.id_registro_centro_vida = ?
        GROUP BY rcv.id_registro_centro_vida LIMIT 1";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Convertir fechas a array YYYY-MM-DD
    $fechas = [];
    if (!empty($row['fechas'])) {
        $raw = explode(',', $row['fechas']);
        foreach ($raw as $f) {
            $fechas[] = trim($f);
        }
    }

    echo json_encode(['success' => true, 'data' => $row, 'fechas' => $fechas]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
}

$stmt->close();
$mysqli->close();
?>
