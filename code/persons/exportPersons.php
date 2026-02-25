<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    die('Acceso denegado');
}

// Limpiar cualquier salida previa
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once '../../conexion.php';
require_once '../filtros_grupos.php';

try {
    require_once '../../vendor/autoload.php';
} catch (Exception $e) {
    die('Error cargando autoload: ' . $e->getMessage());
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Verificar conexión a base de datos
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// Aplicar filtro de grupos según tipo de usuario
$where_grupos_filtro = '';
try {
    $where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');
} catch (Exception $e) {
    ob_end_clean();
    die('Error en filtros de grupos: ' . $e->getMessage());
}

$where = "WHERE p.estado_persona = 1";

// Filtro por cédula
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona = '$cedula'";
}

// Filtro por nombre
if (!empty($_GET['nombre'])) {
    $nombre = $mysqli->real_escape_string($_GET['nombre']);
    $where .= " AND (p.nombres_persona LIKE '%$nombre%' OR p.apellidos_persona LIKE '%$nombre%')";
}

// Filtro por programa
if (!empty($_GET['programa'])) {
    $programa = $mysqli->real_escape_string($_GET['programa']);
    $where .= " AND pp.id_programa = '$programa'";
}

// Filtro por creado por
if (!empty($_GET['creado_por'])) {
    $creado_por = $mysqli->real_escape_string($_GET['creado_por']);
    $where .= " AND u.nombre LIKE '%$creado_por%'";
}

// Filtrar por id_grupo si el tipo_usuario en la sesión es diferente de 1, 3, 4 y 5
if ($tipo_usuario != 1 && $id_grupo_session && !in_array($tipo_usuario, [3, 4, 5])) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}

// Aplicar filtro adicional para usuarios con restricciones de grupos
$where .= $where_grupos_filtro;

// Preparar filtro por estado (se aplicará después de la consulta principal)
$filtro_estado = '';
if (!empty($_GET['estado'])) {
    $filtro_estado = $_GET['estado'];
}

// Consulta SQL para obtener los datos
$query = "
SELECT p.*, 
       GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC SEPARATOR ', ') AS programas,
       g.descripcion_grupo,
       pol.descripcion_politica,
       m.descripcion_meta,
       a.descripcion_actividad,
       acc.descripcion_accion,
       u.nombre AS creado_por,
       b.nombre_bar AS nombre_barrio,
       c.nombre_com AS nombre_comuna,
       (SELECT cc.descripcion_condicion 
        FROM movimiento_persona mp 
        JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
        WHERE mp.cedula_persona = p.cedula_persona 
        AND cc.descripcion_condicion IN ('CPSAM EVADIDO', 'CPSAM FALLECIDO', 'CPSAM RETIRADO VOLUNTARIO', 'CPSAM TRASLADADO')
        ORDER BY mp.fecha_movimiento DESC 
        LIMIT 1) AS estado_movimiento
FROM personas p
LEFT JOIN persona_programa pp ON p.cedula_persona = pp.cedula_persona
LEFT JOIN programas pr ON pp.id_programa = pr.id_programa
LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
LEFT JOIN metas m ON p.id_meta = m.id_meta
LEFT JOIN actividades a ON p.id_actividad = a.id_actividad
LEFT JOIN acciones acc ON p.id_accion = acc.id_accion
LEFT JOIN usuarios u ON p.id_usuario = u.id
LEFT JOIN barrios b ON p.id_barrio_persona = b.id_bar
LEFT JOIN comunas c ON p.id_comuna_persona = c.id_com
$where
GROUP BY p.cedula_persona
ORDER BY p.apellidos_persona ASC
";

$result = $mysqli->query($query);

if (!$result) {
    ob_end_clean();
    die('Error en consulta SQL: ' . $mysqli->error . '<br><br>Query: ' . htmlspecialchars($query));
}

// Crear Excel
try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Personas');
} catch (Exception $e) {
    ob_end_clean();
    die('Error creando Excel: ' . $e->getMessage());
}

// Encabezados
$headers = [
    'Tipo Identificación',
    'Cédula',
    'Nombres',
    'Apellidos',
    'Género',
    'Fecha Nacimiento',
    'Edad',
    'Teléfono',
    'Teléfono Referencia',
    'Referencia',
    'Correo',
    'Dirección',
    'Barrio',
    'Comuna',
    'Zona',
    'Grupo Sisbén',
    'EPS',
    'Peso (kg)',
    'Talla (cm)',
    'Patologías',
    'Factores de Riesgo',
    'Factores Preventivos',
    'Ingresos Económicos',
    'Convivencia Actual',
    'Resultado Actividad',
    'Remisión',
    'Persona con Discapacidad',
    'Categoría Discapacidad',
    'Cabeza de Hogar',
    'Líder Comunidad',
    'Se Reconoce Como',
    'Orientación Sexual',
    'Experiencia Migratoria',
    'Grupo Étnico',
    'Tipo de Salud',
    'Nivel Educativo',
    'Condición Ocupación',
    'Condición Componente',
    'Activo Desde',
    'Programas',
    'Centro Vida / CPSAM / Otro',
    'Política Pública',
    'Meta',
    'Actividad',
    'Acción',
    'Estado',
    'Creado por'
];

$col = 'A';
foreach($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}

// Estilo encabezados
$lastCol = 'AU'; // Columna 47
$sheet->getStyle('A1:'.$lastCol.'1')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:'.$lastCol.'1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('412fd1');
$sheet->getStyle('A1:'.$lastCol.'1')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A1:'.$lastCol.'1')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(25);

// Freeze pane (congelar encabezado)
$sheet->freezePane('A2');

// Datos
$fila = 2;
$data_exported = 0;

while($row = $result->fetch_assoc()) {
    // Determinar el estado de la persona
    $estado_persona = $row['estado_movimiento'] ? $row['estado_movimiento'] : 'CPSAM ACTIVO';
    
    // Permitir también el estado 'Usuario Interesado'
    if (isset($row['condicion_componente']) && trim(mb_strtolower($row['condicion_componente'])) === 'usuario interesado') {
        $estado_persona = 'USUARIO INTERESADO';
    }
    
    // Permitir también el estado 'Usuario Indirecto'
    if (isset($row['condicion_componente']) && trim(mb_strtolower($row['condicion_componente'])) === 'usuario indirecto') {
        $estado_persona = 'USUARIO INDIRECTO';
    }
    
    // Verificar si es "Visita fallida"
    $es_visita_fallida = false;
    if (isset($row['condicion_componente']) && mb_strtolower(trim($row['condicion_componente'])) === 'visita psicosocial fallida') {
        $es_visita_fallida = true;
    }
    
    // EXCLUIR personas con estado RETIRADO VOLUNTARIO, FALLECIDO, EVADIDO, Usuario Interesado o Visita fallida
    if ($estado_persona === 'CPSAM RETIRADO VOLUNTARIO' || 
        $estado_persona === 'CPSAM FALLECIDO' || 
        $estado_persona === 'CPSAM EVADIDO' ||
        $estado_persona === 'USUARIO INTERESADO' ||
        $es_visita_fallida) {
        continue; // Saltar esta persona
    }
    
    // Aplicar filtro por estado si está seleccionado
    if (!empty($filtro_estado)) {
        $estado_filtro_map = [
            'ACTIVO' => 'CPSAM ACTIVO',
            'EVADIDO' => 'CPSAM EVADIDO',
            'FALLECIDO' => 'CPSAM FALLECIDO',
            'RETIRADO_VOLUNTARIO' => 'CPSAM RETIRADO VOLUNTARIO',
            'TRASLADADO' => 'CPSAM TRASLADADO',
            'USUARIO_INTERESADO' => 'USUARIO INTERESADO',
            'USUARIO_INDIRECTO' => 'USUARIO INDIRECTO'
        ];

        if (isset($estado_filtro_map[$filtro_estado]) && $estado_persona !== $estado_filtro_map[$filtro_estado]) {
            continue; // Saltar esta fila si no coincide con el filtro
        }
    }
    
    $col = 'A';
    
    // Tipo Identificación
    $sheet->setCellValue($col++.$fila, $row['tipo_identificacion'] ?? '');
    // Cédula
    $sheet->setCellValue($col++.$fila, $row['cedula_persona'] ?? '');
    // Nombres
    $sheet->setCellValue($col++.$fila, $row['nombres_persona'] ?? '');
    // Apellidos
    $sheet->setCellValue($col++.$fila, $row['apellidos_persona'] ?? '');
    // Género
    $sheet->setCellValue($col++.$fila, $row['genero_persona'] ?? '');
    // Fecha Nacimiento
    $sheet->setCellValue($col++.$fila, !empty($row['fecha_nacimiento']) && $row['fecha_nacimiento'] != '0000-00-00' ? date('d/m/Y', strtotime($row['fecha_nacimiento'])) : '');
    // Edad
    if (!empty($row['fecha_nacimiento']) && $row['fecha_nacimiento'] != '0000-00-00') {
        $hoy = new DateTime();
        $nacimiento = new DateTime($row['fecha_nacimiento']);
        $edad = $hoy->diff($nacimiento)->y;
        $sheet->setCellValue($col++.$fila, $edad);
    } else {
        $sheet->setCellValue($col++.$fila, '');
    }
    // Teléfono
    $sheet->setCellValue($col++.$fila, $row['telefono_persona'] ?? '');
    // Teléfono Referencia
    $sheet->setCellValue($col++.$fila, $row['telefono_referencia_persona'] ?? '');
    // Referencia
    $sheet->setCellValue($col++.$fila, $row['referencia_persona'] ?? '');
    // Correo
    $sheet->setCellValue($col++.$fila, $row['correo_persona'] ?? '');
    // Dirección
    $sheet->setCellValue($col++.$fila, $row['direccion_persona'] ?? '');
    // Barrio
    $sheet->setCellValue($col++.$fila, $row['nombre_barrio'] ?? '');
    // Comuna
    $sheet->setCellValue($col++.$fila, $row['nombre_comuna'] ?? '');
    // Zona
    $sheet->setCellValue($col++.$fila, $row['zona_persona'] ?? '');
    // Grupo Sisbén
    $sheet->setCellValue($col++.$fila, $row['grupo_sisben'] ?? '');
    // EPS
    $sheet->setCellValue($col++.$fila, $row['eps'] ?? '');
    // Peso
    $sheet->setCellValue($col++.$fila, $row['peso'] ?? '');
    // Talla
    $sheet->setCellValue($col++.$fila, $row['talla'] ?? '');
    // Patologías
    $sheet->setCellValue($col++.$fila, $row['patologias'] ?? '');
    // Factores de Riesgo
    $sheet->setCellValue($col++.$fila, $row['factores_riesgo'] ?? '');
    // Factores Preventivos
    $sheet->setCellValue($col++.$fila, $row['factores_preventivos'] ?? '');
    // Ingresos Económicos
    $sheet->setCellValue($col++.$fila, $row['ingresos_economicos'] ?? '');
    // Convivencia Actual
    $sheet->setCellValue($col++.$fila, $row['convivencia_actual'] ?? '');
    // Resultado Actividad
    $sheet->setCellValue($col++.$fila, $row['resultado_actividad'] ?? '');
    // Remisión
    $sheet->setCellValue($col++.$fila, $row['remision'] ?? '');
    // Persona con Discapacidad
    $sheet->setCellValue($col++.$fila, $row['persona_discapacidad'] ?? '');
    // Categoría Discapacidad
    $sheet->setCellValue($col++.$fila, $row['cual_discapacidad'] ?? '');
    // Cabeza de Hogar
    $sheet->setCellValue($col++.$fila, $row['cabeza_hogar'] ?? '');
    // Líder Comunidad
    $sheet->setCellValue($col++.$fila, $row['lider_comunidad'] ?? '');
    // Se Reconoce Como
    $sheet->setCellValue($col++.$fila, $row['se_reconoce_como'] ?? '');
    // Orientación Sexual
    $sheet->setCellValue($col++.$fila, $row['orientacion_sexual'] ?? '');
    // Experiencia Migratoria
    $sheet->setCellValue($col++.$fila, $row['experiencia_migratoria'] ?? '');
    // Grupo Étnico
    $sheet->setCellValue($col++.$fila, $row['grupo_etnico'] ?? '');
    // Tipo de Salud
    $sheet->setCellValue($col++.$fila, $row['tipo_salud'] ?? '');
    // Nivel Educativo
    $sheet->setCellValue($col++.$fila, $row['nivel_educativo'] ?? '');
    // Condición Ocupación
    $sheet->setCellValue($col++.$fila, $row['condicion_ocupacion'] ?? '');
    // Condición Componente
    $sheet->setCellValue($col++.$fila, $row['condicion_componente'] ?? '');
    // Activo Desde
    $sheet->setCellValue($col++.$fila, !empty($row['activo_desde']) && $row['activo_desde'] != '0000-00-00' ? date('d/m/Y', strtotime($row['activo_desde'])) : '');
    // Programas
    $sheet->setCellValue($col++.$fila, $row['programas'] ?? '');
    // Centro Vida / CPSAM / Otro
    $sheet->setCellValue($col++.$fila, $row['descripcion_grupo'] ?? '');
    // Política Pública
    $sheet->setCellValue($col++.$fila, $row['descripcion_politica'] ?? '');
    // Meta
    $sheet->setCellValue($col++.$fila, $row['descripcion_meta'] ?? '');
    // Actividad
    $sheet->setCellValue($col++.$fila, $row['descripcion_actividad'] ?? '');
    // Acción
    $sheet->setCellValue($col++.$fila, $row['descripcion_accion'] ?? '');
    
    // Estado formateado
    $estado_sin_cpsam = str_ireplace('CPSAM ', '', $estado_persona);
    if (strtoupper($estado_sin_cpsam) === 'TRASLADADO') {
        $estado_mostrar = 'ACTIVO (TRASLADADO)';
    } elseif ($estado_persona == 'USUARIO INTERESADO') {
        $estado_mostrar = 'Usuario Interesado';
    } elseif ($estado_persona == 'USUARIO INDIRECTO') {
        $estado_mostrar = 'Usuario Indirecto';
    } else {
        $estado_mostrar = $estado_sin_cpsam;
    }
    $sheet->setCellValue($col++.$fila, $estado_mostrar);
    
    // Creado por
    $sheet->setCellValue($col++.$fila, $row['creado_por'] ?? 'N/A');
    
    // Filas alternadas con color
    if ($fila % 2 == 0) {
        $sheet->getStyle('A'.$fila.':'.$lastCol.$fila)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7F0FF');
    }
    
    // Altura de fila
    $sheet->getRowDimension($fila)->setRowHeight(18);
    $fila++;
    $data_exported++;
}

// Si no hay datos para exportar
if ($data_exported == 0) {
    ob_end_clean();
    echo "<script>alert('No hay datos para exportar con los filtros seleccionados.'); window.history.back();</script>";
    exit;
}

// Ajustar columnas
$columnWidths = [
    'A' => 20,  // Tipo Identificación
    'B' => 15,  // Cédula
    'C' => 25,  // Nombres
    'D' => 25,  // Apellidos
    'E' => 12,  // Género
    'F' => 18,  // Fecha Nacimiento
    'G' => 8,   // Edad
    'H' => 15,  // Teléfono
    'I' => 18,  // Teléfono Referencia
    'J' => 20,  // Referencia
    'K' => 30,  // Correo
    'L' => 35,  // Dirección
    'M' => 25,  // Barrio
    'N' => 20,  // Comuna
    'O' => 10,  // Zona
    'P' => 12,  // Grupo Sisbén
    'Q' => 20,  // EPS
    'R' => 10,  // Peso
    'S' => 10,  // Talla
    'T' => 25,  // Patologías
    'U' => 20,  // Factores de Riesgo
    'V' => 20,  // Factores Preventivos
    'W' => 20,  // Ingresos Económicos
    'X' => 18,  // Convivencia Actual
    'Y' => 30,  // Resultado Actividad
    'Z' => 20,  // Remisión
    'AA' => 15, // Persona con Discapacidad
    'AB' => 20, // Categoría Discapacidad
    'AC' => 15, // Cabeza de Hogar
    'AD' => 15, // Líder Comunidad
    'AE' => 18, // Se Reconoce Como
    'AF' => 18, // Orientación Sexual
    'AG' => 20, // Experiencia Migratoria
    'AH' => 18, // Grupo Étnico
    'AI' => 20, // Tipo de Salud
    'AJ' => 20, // Nivel Educativo
    'AK' => 20, // Condición Ocupación
    'AL' => 30, // Condición Componente
    'AM' => 18, // Activo Desde
    'AN' => 25, // Programas
    'AO' => 30, // Centro Vida / CPSAM / Otro
    'AP' => 25, // Política Pública
    'AQ' => 30, // Meta
    'AR' => 30, // Actividad
    'AS' => 30, // Acción
    'AT' => 25, // Estado
    'AU' => 20  // Creado por
];

foreach($columnWidths as $col => $width) {
    $sheet->getColumnDimension($col)->setWidth($width);
}

// Bordes
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ],
        'outline' => [
            'borderStyle' => Border::BORDER_MEDIUM,
            'color' => ['rgb' => '000000']
        ]
    ],
];
$sheet->getStyle('A1:'.$lastCol.($fila-1))->applyFromArray($styleArray);

// Alineación vertical centrada para todos los datos
$sheet->getStyle('A2:'.$lastCol.($fila-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

$mysqli->close();

ob_end_clean();

// Descargar
try {
    $filename = 'Personas_'.date('Y-m-d_His').'.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error generando archivo Excel: ' . $e->getMessage());
}

exit;
?>
