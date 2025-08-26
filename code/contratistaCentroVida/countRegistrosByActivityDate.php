<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include("../../conexion.php");

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
  echo json_encode(['error'=>'Método no permitido']);
  exit;
}

$id_actividad_centro_vida = isset($_POST['id_actividad_centro_vida']) ? intval($_POST['id_actividad_centro_vida']) : 0;
$fecha_atencion = isset($_POST['fecha_atencion']) ? trim($_POST['fecha_atencion']) : '';

if($id_actividad_centro_vida <= 0 || $fecha_atencion === ''){
  echo json_encode(['masculino'=>0,'femenino'=>0]);
  exit;
}

// Parse and validate the provided date, then compute month range
$ts = strtotime($fecha_atencion);
if($ts === false){
  echo json_encode(['masculino'=>0,'femenino'=>0]);
  exit;
}
$start_date = date('Y-m-01', $ts);
$end_date = date('Y-m-t', $ts);

// Query: join registro_centro_vida with registro_centro_vida_fechas and personas to count genders for that activity and date
$sql = "SELECT p.genero_persona AS genero, COUNT(*) AS cnt
  FROM registro_centro_vida r
  INNER JOIN registro_centro_vida_fechas f ON r.id_registro_centro_vida = f.id_registro_centro_vida AND f.fecha_atencion BETWEEN ? AND ?
  LEFT JOIN personas p ON r.cedula_persona = p.cedula_persona
  WHERE r.id_actividad_centro_vida = ?
  GROUP BY p.genero_persona";

if($stmt = $mysqli->prepare($sql)){
  // bind start_date, end_date, activity id
  $stmt->bind_param('ssi', $start_date, $end_date, $id_actividad_centro_vida);
  $stmt->execute();
  $res = $stmt->get_result();
  $mas = 0; $fem = 0;
  while($row = $res->fetch_assoc()){
    $gen = strtolower(trim($row['genero'] ?? ''));
    $cnt = intval($row['cnt']);
    if($gen === 'masculino' || $gen === 'm') $mas += $cnt;
    elseif($gen === 'femenino' || $gen === 'f') $fem += $cnt;
    else {
      // unknown gender - ignore or decide based on value; skip
    }
  }
  $stmt->close();
  echo json_encode(['masculino'=>$mas,'femenino'=>$fem]);
  exit;
} else {
  echo json_encode(['error'=>'Error en consulta']);
  exit;
}

?>
