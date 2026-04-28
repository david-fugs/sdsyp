<?php
// Exportar actividades individuales de contratista desde página de reportes
// Iniciar sesión para obtener tipo_usuario
session_start();

// Eliminar cualquier salida previa
if (ob_get_length()) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: Hay salida previa al header. El archivo Excel se corromperá.\n";
    exit;
}

require_once '../filtros_grupos.php';
require_once '../../conexion.php';
if (isset($mysqli)) {
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES 'utf8mb4'");
    $mysqli->query("SET CHARACTER SET 'utf8mb4'");
}

require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Obtener filtros
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_grupo = isset($_GET['filtro_grupo']) ? $_GET['filtro_grupo'] : '';
$filtro_mes = isset($_GET['filtro_mes']) && !empty($_GET['filtro_mes']) ? $_GET['filtro_mes'] : '';
$filtro_usuario = isset($_GET['filtro_usuario']) ? $_GET['filtro_usuario'] : '';
$filtro_todos_tipo3 = ($filtro_usuario === 'TODOS_TIPO3');
$filtro_fecha_inicio = isset($_GET['filtro_fecha_inicio']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['filtro_fecha_inicio']) ? $_GET['filtro_fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['filtro_fecha_fin']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['filtro_fecha_fin']) ? $_GET['filtro_fecha_fin'] : '';

// Detectar si se seleccionó "Todos CPSAM" o "Todos Centros Vida"
$filtro_todos_grupos = false;
$prefijo_grupo = '';
$grupos_a_exportar = [];

if ($filtro_grupo === 'TODOS_CPSAM') {
    $filtro_todos_grupos = true;
    $prefijo_grupo = 'CPSAM';
    // Obtener todos los grupos que empiezan con CPSAM
    $query_grupos = "SELECT id_grupo, descripcion_grupo FROM grupos WHERE descripcion_grupo LIKE 'CPSAM%' ORDER BY descripcion_grupo ASC";
    $result_grupos_query = $mysqli->query($query_grupos);
    if ($result_grupos_query) {
        while ($grupo_row = $result_grupos_query->fetch_assoc()) {
            $grupos_a_exportar[] = $grupo_row;
        }
    }
} elseif ($filtro_grupo === 'TODOS_CV') {
    $filtro_todos_grupos = true;
    $prefijo_grupo = 'CV';
    // Obtener todos los grupos que empiezan con CV
    $query_grupos = "SELECT id_grupo, descripcion_grupo FROM grupos WHERE descripcion_grupo LIKE 'CV%' ORDER BY descripcion_grupo ASC";
    $result_grupos_query = $mysqli->query($query_grupos);
    if ($result_grupos_query) {
        while ($grupo_row = $result_grupos_query->fetch_assoc()) {
            $grupos_a_exportar[] = $grupo_row;
        }
    }
}

// Aplicar filtro de grupos según tipo de usuario
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

$where = 'WHERE p.estado_persona = 1';

// Filtro por rango de fechas (anula año y mes si ambos presentes)
if ($filtro_fecha_inicio && $filtro_fecha_fin) {
    $where .= " AND ri.fecha_registro BETWEEN '$filtro_fecha_inicio' AND '$filtro_fecha_fin' ";
} else {
    if ($filtro_anio) {
        $where .= " AND YEAR(ri.fecha_registro) = $filtro_anio ";
    }
    // Aplicar filtro de mes si se seleccionó
    if ($filtro_mes) {
        $where .= " AND MONTH(ri.fecha_registro) = " . intval($filtro_mes) . " ";
    }
}

// Si se seleccionó un grupo específico, agregar ese filtro
if ($filtro_grupo && !$filtro_todos_grupos) {
    $filtro_grupo_int = intval($filtro_grupo);
    $where .= " AND p.id_grupo = $filtro_grupo_int ";
} elseif ($filtro_todos_grupos && !empty($grupos_a_exportar)) {
    // Si es "Todos CPSAM" o "Todos CV", filtrar por todos esos grupos
    $ids_grupos = array_map(function($g) { return intval($g['id_grupo']); }, $grupos_a_exportar);
    $where .= " AND p.id_grupo IN (" . implode(',', $ids_grupos) . ") ";
}

// Aplicar filtro de usuario si se seleccionó
if ($filtro_todos_tipo3) {
    $where .= " AND ri.id_usuario IN (SELECT id FROM usuarios WHERE tipo_usuario = 3) ";
} elseif (is_numeric($filtro_usuario) && intval($filtro_usuario) > 0) {
    $filtro_usuario_int = intval($filtro_usuario);
    $where .= " AND ri.id_usuario = $filtro_usuario_int ";
}

// Si es usuario tipo 2 (INGENIERO), filtrar solo actividades de su grupo y usuarios de su grupo
if ($tipo_usuario == 2 && isset($_SESSION['id_grupo'])) {
    $id_grupo_session = intval($_SESSION['id_grupo']);
    $where .= " AND p.id_grupo = $id_grupo_session ";
    if (is_numeric($filtro_usuario) && intval($filtro_usuario) > 0) {
        $query_check = "SELECT id FROM usuarios WHERE id = " . intval($filtro_usuario) . " AND id_grupo = $id_grupo_session";
        $result_check = $mysqli->query($query_check);
        if (!$result_check || $result_check->num_rows == 0) {
            die("Acceso denegado: No puede exportar datos de usuarios de otros grupos.");
        }
    }
}
// Si es usuario tipo 3 (CONTRATISTA CPSAM), filtrar solo sus actividades
elseif ($tipo_usuario == 3 && isset($_SESSION['id'])) {
    $id_usuario = intval($_SESSION['id']);
    $where .= " AND ri.id_usuario = $id_usuario ";
}

// Aplicar filtro adicional para usuarios técnicos (tipos 4 y 5)
$where .= $where_grupos_filtro;

$query = "SELECT 
    ri.id_registro_individual,
    p.cedula_persona,
    p.tipo_identificacion,
    p.nombres_persona,
    p.apellidos_persona,
    p.genero_persona,
    p.fecha_nacimiento,
    p.telefono_persona,
    p.telefono_referencia_persona,
    p.referencia_persona,
    p.correo_persona,
    p.direccion_persona,
    p.zona_persona,
    b_per.nombre_bar AS barrio_residencia,
    p.grupo_sisben,
    p.eps,
    p.peso,
    p.talla,
    p.patologias,
    p.factores_riesgo,
    p.factores_preventivos,
    p.ingresos_economicos,
    p.convivencia_actual,
    p.resultado_actividad,
    p.remision,
    p.persona_discapacidad,
    p.cual_discapacidad,
    p.victima,
    p.cabeza_hogar,
    p.lider_comunidad,
    p.se_reconoce_como,
    p.orientacion_sexual,
    p.experiencia_migratoria,
    p.grupo_etnico,
    p.tipo_salud,
    p.nivel_educativo,
    p.condicion_ocupacion,
    p.condicion_componente AS condicion_componente_persona,
    c.descripcion_condicion,
    ri.fecha_registro,
    ri.observacion_registro,
    g.descripcion_grupo as centro_vida_traslado,
    m.descripcion_meta,
    a.descripcion_actividad,
    ac.descripcion_accion,
    pp.descripcion_politica,
    ri.departamento_procedencia,
    b.nombre_bar AS nombre_barrio,
    com.nombre_com AS nombre_com,
    u.nombre as nombre_usuario,
    p_grupo.descripcion_grupo as grupo_persona,
    p.sin_convenio
FROM registro_individual as ri
JOIN personas as p ON ri.cedula_persona = p.cedula_persona
JOIN condiciones_componente as c ON ri.id_condicion = c.id_condicion
LEFT JOIN grupos g ON ri.id_centro_vida_traslado = g.id_grupo
LEFT JOIN grupos p_grupo ON p.id_grupo = p_grupo.id_grupo
LEFT JOIN metas m ON ri.id_meta = m.id_meta
LEFT JOIN actividades a ON ri.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON ri.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON ri.id_politica_publica = pp.id_politica
LEFT JOIN usuarios u ON ri.id_usuario = u.id
LEFT JOIN barrios b ON ri.id_barrio = b.id_bar
LEFT JOIN comunas com ON ri.id_comuna = com.id_com
LEFT JOIN barrios b_per ON p.id_barrio_persona = b_per.id_bar
$where
ORDER BY " . ($filtro_todos_tipo3 ? "u.nombre ASC, ri.fecha_registro DESC" : ($filtro_todos_grupos ? "p_grupo.descripcion_grupo ASC, ri.fecha_registro DESC" : "ri.fecha_registro DESC")) . "
";

$result = $mysqli->query($query);

$spreadsheet = new Spreadsheet();
\PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Actividades Individuales');

// Cabeceras
$headers = [
    'Fecha Registro',
    'Cédula',
    'Tipo Identificación',
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
    'Barrio Residencia',
    'Zona',
    'Grupo/Centro Vida',
    'Condición',
    'Meta',
    'Actividad',
    'Acción',
    'Política Pública',
    'Centro Traslado',
    'Dpto. Procedencia',
    'Barrio (Actividad)',
    'Comuna/Corregimiento',
    'Observaciones',
    'Con Convenio',
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
    '¿Discapacidad?',
    'Categoría Discapacidad',
    'Víctima',
    '¿Cabeza Hogar?',
    '¿Líder Comunidad?',
    'Se Reconoce Como',
    'Orientación Sexual',
    '¿Exp. Migratoria?',
    'Grupo Étnico',
    'Tipo Salud',
    'Nivel Educativo',
    'Condición Ocupación',
    'Condición Componente',
    'Usuario Registro',
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Aplicar estilos a encabezados
$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
$sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'FFA726']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

$sheet->getRowDimension(1)->setRowHeight(32);

// Llenar datos
$row = 2;
$grupo_anterior = '';
$usuario_anterior = '';
if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        // Separador por usuario cuando se filtra por TODOS_TIPO3
        if ($filtro_todos_tipo3 && $data['nombre_usuario'] != $usuario_anterior && $usuario_anterior != '') {
            $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
            $sheet->setCellValue('A' . $row, '--- ' . strtoupper($data['nombre_usuario']) . ' ---');
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a237e']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension($row)->setRowHeight(30);
            $row++;
        }
        $usuario_anterior = $data['nombre_usuario'];
        // Si es "Todos CPSAM" o "Todos CV", agregar fila separadora entre grupos
        if ($filtro_todos_grupos && $data['grupo_persona'] != $grupo_anterior && $grupo_anterior != '') {
            // Agregar fila de separación
            $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
            $sheet->setCellValue('A' . $row, '--- ' . strtoupper($data['grupo_persona']) . ' ---');
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE0B2']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            $sheet->getRowDimension($row)->setRowHeight(30);
            $row++;
        }
        $grupo_anterior = $data['grupo_persona'];

        $con_convenio = (isset($data['sin_convenio']) && $data['sin_convenio'] == 1) ? 'NO' : 'SÍ';

        $edad = '';
        if (!empty($data['fecha_nacimiento'])) {
            $nacimiento = new DateTime($data['fecha_nacimiento']);
            $hoy = new DateTime();
            $edad = $nacimiento->diff($hoy)->y;
        }

        $rowData = [
            $data['fecha_registro'],
            $data['cedula_persona'],
            $data['tipo_identificacion'] ?? '',
            $data['nombres_persona'],
            $data['apellidos_persona'],
            $data['genero_persona'] ?? '',
            $data['fecha_nacimiento'] ?? '',
            $edad,
            $data['telefono_persona'] ?? '',
            $data['telefono_referencia_persona'] ?? '',
            $data['referencia_persona'] ?? '',
            $data['correo_persona'] ?? '',
            $data['direccion_persona'] ?? '',
            $data['barrio_residencia'] ?? '',
            $data['zona_persona'] ?? '',
            $data['grupo_persona'] ?? 'N/A',
            $data['descripcion_condicion'],
            $data['descripcion_meta'] ?? '',
            $data['descripcion_actividad'] ?? '',
            $data['descripcion_accion'] ?? '',
            $data['descripcion_politica'] ?? '',
            $data['centro_vida_traslado'] ?? '',
            $data['departamento_procedencia'] ?? '',
            $data['nombre_barrio'] ?? '',
            $data['nombre_com'] ?? '',
            $data['observacion_registro'] ?? '',
            $con_convenio,
            $data['grupo_sisben'] ?? '',
            $data['eps'] ?? '',
            $data['peso'] ?? '',
            $data['talla'] ?? '',
            $data['patologias'] ?? '',
            $data['factores_riesgo'] ?? '',
            $data['factores_preventivos'] ?? '',
            $data['ingresos_economicos'] ?? '',
            $data['convivencia_actual'] ?? '',
            $data['resultado_actividad'] ?? '',
            $data['remision'] ?? '',
            $data['persona_discapacidad'] ?? '',
            $data['cual_discapacidad'] ?? '',
            $data['victima'] ?? '',
            $data['cabeza_hogar'] ?? '',
            $data['lider_comunidad'] ?? '',
            $data['se_reconoce_como'] ?? '',
            $data['orientacion_sexual'] ?? '',
            $data['experiencia_migratoria'] ?? '',
            $data['grupo_etnico'] ?? '',
            $data['tipo_salud'] ?? '',
            $data['nivel_educativo'] ?? '',
            $data['condicion_ocupacion'] ?? '',
            $data['condicion_componente_persona'] ?? '',
            $data['nombre_usuario'] ?? '',
        ];

        $col = 'A';
        foreach ($rowData as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }

        // Aplicar estilos a la fila
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'ECEFF1']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ]);

        $sheet->getRowDimension($row)->setRowHeight(24);
        $row++;
    }
}

// Ajustar anchos de columna
for ($i = 1; $i <= count($headers); $i++) {
    $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth(22);
}

// Limpiar buffer y generar archivo
if (ob_get_length()) {
    ob_end_clean();
}

$anio_texto = ($filtro_fecha_inicio && $filtro_fecha_fin) ? $filtro_fecha_inicio . '_' . $filtro_fecha_fin : ($filtro_anio ? $filtro_anio : 'Todos');
$mes_texto = '';
if (!$filtro_fecha_inicio && $filtro_mes) {
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $mes_texto = '_' . $meses[intval($filtro_mes)];
}
$filename = 'Actividades_Individuales_' . $anio_texto . $mes_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
