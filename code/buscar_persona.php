<?php
include("../conexion.php"); 
session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cedula = trim($_POST['cedula'] ?? '');

    if ($cedula === '') {
        echo json_encode(['encontrado' => false, 'mensaje' => 'Cédula vacía']);
        exit;
    }

    // Preparar consulta para evitar inyecciones SQL - incluir los nuevos campos
    $stmt = $mysqli->prepare("
        SELECT p.nombres_persona, p.apellidos_persona, p.id_meta, p.id_actividad, p.id_accion, p.id_politica_publica,
               m.descripcion_meta, a.descripcion_actividad, acc.descripcion_accion, pol.descripcion_politica
        FROM personas p
        LEFT JOIN metas m ON p.id_meta = m.id_meta
        LEFT JOIN actividades a ON p.id_actividad = a.id_actividad
        LEFT JOIN acciones acc ON p.id_accion = acc.id_accion
        LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
        WHERE p.cedula_persona = ?
    ");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($row = $resultado->fetch_assoc()) {
        echo json_encode([
            'encontrado' => true,
            'nombres' => $row['nombres_persona'],
            'apellidos' => $row['apellidos_persona'],
            'id_meta' => $row['id_meta'],
            'id_actividad' => $row['id_actividad'],
            'id_accion' => $row['id_accion'],
            'id_politica_publica' => $row['id_politica_publica'],
            'descripcion_meta' => $row['descripcion_meta'],
            'descripcion_actividad' => $row['descripcion_actividad'],
            'descripcion_accion' => $row['descripcion_accion'],
            'descripcion_politica' => $row['descripcion_politica']
        ]);
    } else {
        echo json_encode(['encontrado' => false, 'mensaje' => 'Persona no encontrada']);
    }

    $stmt->close();
} else {
    echo json_encode(['encontrado' => false, 'mensaje' => 'Método no permitido']);
}
?>
