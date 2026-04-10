<?php
// Exportar actividades masivas centro vida
session_start();
if (ob_get_length()) { header('Content-Type: text/plain; charset=utf-8'); echo 'Salida previa'; exit; }
require_once '../../conexion.php';
require_once '../filtros_grupo_usuario.php';
require_once '../filtros_grupos.php';
$mysqli->set_charset('utf8mb4');
require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;use PhpOffice\PhpSpreadsheet\Writer\Xlsx;use PhpOffice\PhpSpreadsheet\Style\Fill;use PhpOffice\PhpSpreadsheet\Style\Alignment;use PhpOffice\PhpSpreadsheet\Style\Border;

$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_mes = isset($_GET['filtro_mes']) ? intval($_GET['filtro_mes']) : '';
$filtro_funcionario = isset($_GET['filtro_funcionario']) ? intval($_GET['filtro_funcionario']) : '';
$where='';
if($filtro_anio){ $where.=" AND YEAR(mcv.fecha_atencion)=$filtro_anio"; }
if($filtro_mes){ $where.=" AND MONTH(mcv.fecha_atencion)=$filtro_mes"; }
if($filtro_funcionario){ $where.=" AND mcv.id_usuario=$filtro_funcionario"; }

// Aplicar filtro por grupo de usuario (tipo 11: INGENIERO CENTRO VIDA)
if (debeAplicarFiltroGrupo($_SESSION['tipo_usuario'] ?? null) && isset($_SESSION['id_grupo'])) {
    $id_grupo = intval($_SESSION['id_grupo']);
    $where .= " AND mcv.id_centro_vida = $id_grupo";
}

// Aplicar filtro de grupos para tipo 12 (CONTRATISTA CV ALCALDÍA): solo grupos CV
$tipo_usuario_export = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$grupos_cv_masivo = getGruposPermitidos($mysqli, $tipo_usuario_export);
if (!empty($grupos_cv_masivo)) {
    $ids_cv_masivo = implode(',', array_map('intval', $grupos_cv_masivo));
    $where .= " AND mcv.id_centro_vida IN ($ids_cv_masivo)";
}

// Filtro para tipo 10 y 12: solo sus propios registros
$id_usuario_export_m = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
if (($tipo_usuario_export == 10 || $tipo_usuario_export == 12) && $id_usuario_export_m) {
    $where .= " AND mcv.id_usuario = $id_usuario_export_m";
}

$sql = "SELECT 
 mcv.id_masiva_centro_vida AS id_registro,
 m.descripcion_meta,
 a.descripcion_actividad,
 ac.descripcion_accion,
 acv.descripcion_actividad AS actividad_centro_vida,
 pp.descripcion_politica,
 g.descripcion_grupo AS centro_vida,
 mcv.fecha_atencion,
 mcv.nombre_lider,
 mcv.telefono_contacto,
 c.nombre_com AS nombre_comuna,
 mcv.medio_verificacion,
 mcv.cantidad_masculino,
 mcv.cantidad_femenino,
 mcv.jornada,
 mcv.observacion_actividad,
 mcv.tipo_actividad,
 u1.nombre AS digitado_por,
 u2.nombre AS funcionario_responsable_nombre
FROM masiva_centro_vida mcv
LEFT JOIN metas m ON mcv.id_meta=m.id_meta
LEFT JOIN actividades a ON mcv.id_actividad=a.id_actividad
LEFT JOIN acciones ac ON mcv.id_accion=ac.id_accion
LEFT JOIN actividad_centro_vida acv ON mcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
LEFT JOIN politicas_publicas pp ON mcv.politica_publica = pp.id_politica
LEFT JOIN grupos g ON mcv.id_centro_vida=g.id_grupo
LEFT JOIN comunas c ON mcv.id_comuna=c.id_com
LEFT JOIN usuarios u1 ON mcv.id_usuario=u1.id
LEFT JOIN usuarios u2 ON mcv.funcionario_responsable=u2.id
WHERE 1 $where ORDER BY mcv.fecha_atencion DESC";
$res=$mysqli->query($sql);

$spreadsheet = new Spreadsheet();
$sheet=$spreadsheet->getActiveSheet();$sheet->setTitle('Masivo Centro Vida');
$headers=['ID','Meta','Actividad Plan','Acción','Actividad Centro Vida','Política Pública','Centro Vida','Fecha Atención','Nombre Líder','Teléfono','Comuna','Medio Verificación','Masculino','Femenino','Total','Jornada','Tipo Actividad','Observación','Digitado por','Funcionario Responsable'];
$col='A';foreach($headers as $h){$sheet->setCellValue($col.'1',$h);$col++;}
$lastCol=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
$sheet->getStyle('A1:'.$lastCol.'1')->applyFromArray([
 'font'=>['bold'=>true],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'E0F7FA']],
 'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true],
 'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'B0BEC5']]]]);
$sheet->getRowDimension(1)->setRowHeight(32);
$r=2; if($res){ while($row=$res->fetch_assoc()){ $data=[
 $row['id_registro'], $row['descripcion_meta'],$row['descripcion_actividad'],$row['descripcion_accion'],$row['actividad_centro_vida'],$row['descripcion_politica'],
 $row['centro_vida'],$row['fecha_atencion'],$row['nombre_lider'],$row['telefono_contacto'],$row['nombre_comuna'],$row['medio_verificacion'],
 $row['cantidad_masculino'],$row['cantidad_femenino'],($row['cantidad_masculino']+$row['cantidad_femenino']),$row['jornada'],$row['tipo_actividad'],$row['observacion_actividad'],$row['digitado_por'],$row['funcionario_responsable_nombre']];
 $col='A'; foreach($data as $val){ $sheet->setCellValue($col.$r,$val); $col++; }
 $sheet->getStyle('A'.$r.':'.$lastCol.$r)->applyFromArray(['borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'ECEFF1']]],'alignment'=>['vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true]]);
 $r++; }}
for($i=1;$i<=count($headers);$i++){ $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth(25); }
for($i=2;$i<$r;$i++){ $sheet->getRowDimension($i)->setRowHeight(24); }
if(ob_get_length()) ob_end_clean();
$filename='Actividades_Masivo_CentroVida_'.date('Y-m-d_H-i-s').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$writer=new Xlsx($spreadsheet);$writer->save('php://output');exit;
