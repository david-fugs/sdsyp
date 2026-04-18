<?php
session_start();
include("../../conexion.php");
require_once('../filtros_grupo_usuario.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_grupo = isset($_POST['id_grupo']) ? intval($_POST['id_grupo']) : 0;
if ($id_grupo <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de grupo inválido']);
    exit;
}

// Filtro de grupo del usuario (tipo 11: solo su CV)
$where_filtro = obtenerCondicionFiltroGrupo('p');

$query = "SELECT
            p.cedula_persona,
            p.nombres_persona,
            p.apellidos_persona,
            p.genero_persona,
            p.jornada
          FROM personas p
          WHERE p.id_grupo = $id_grupo
          AND (p.estado_persona = 1 OR p.estado_persona IS NULL)
          AND (p.sin_convenio IS NULL OR p.sin_convenio = 0)
          $where_filtro
          ORDER BY p.nombres_persona, p.apellidos_persona ASC";

$result = $mysqli->query($query);
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $mysqli->error]);
    exit;
}

$personas = [];
while ($row = $result->fetch_assoc()) {
    $personas[] = $row;
}

echo json_encode($personas);
$mysqli->close();
