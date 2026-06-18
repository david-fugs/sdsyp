<?php
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(300);
session_start();

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    exit('Acceso denegado');
}

require_once '../../conexion.php';

$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id   = $_SESSION['id'];

$where = "1=1";
if ($tipo_usuario == 9) {
    $where .= " AND p.usuario_registro = " . intval($usuario_id);
}

$sql = "SELECT
        p.tipo_identificacion_cm,
        p.cedula_persona_cm,
        p.genero_persona_cm,
        p.nombres_persona_cm,
        p.apellidos_persona_cm,
        p.telefono_persona_cm,
        p.telefono_referencia_cm,
        p.referencia_cm,
        p.fecha_nacimiento_cm,
        TIMESTAMPDIFF(YEAR, p.fecha_nacimiento_cm, CURDATE()) AS edad_calculada,
        p.edad_cm,
        p.grupo_sisben,
        p.direccion_cm,
        p.barrio_cm,
        p.comuna_cm,
        p.zona_cm,
        p.departamento_cm,
        p.municipio_cm,
        p.convivencia_actual,
        p.persona_discapacidad,
        p.cual_discapacidad,
        p.cabeza_hogar,
        p.se_reconoce_como,
        p.orientacion_sexual,
        p.grupo_etnico,
        p.tipo_salud,
        p.condicion_ocupacion,
        p.condicion_componente,
        p.fecha_ingreso_cm,
        p.estado_cm,
        p.fecha_registro,
        u.nombre AS registrado_por
        FROM personas_colombia_mayor p
        LEFT JOIN usuarios u ON p.usuario_registro = u.id
        WHERE $where
        ORDER BY p.apellidos_persona_cm, p.nombres_persona_cm";

$result = $mysqli->query($sql);
if (!$result) {
    die('Error en consulta: ' . $mysqli->error);
}

// ── Helpers ──────────────────────────────────────────────────────────────────

function xe($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

// Column letters A–AE for 31 columns
function colLetter($idx) {
    if ($idx < 26) return chr(65 + $idx);
    return chr(64 + intval($idx / 26)) . chr(65 + ($idx % 26));
}

// ── Build worksheet XML to a temp file (streaming, O(1) memory) ──────────────

$sheetTmp = tempnam(sys_get_temp_dir(), 'sheet_');
$fp = fopen($sheetTmp, 'w');

$colWidths  = [20,15,12,25,25,15,18,20,18,8,12,35,25,10,10,15,15,18,20,20,18,18,18,18,20,20,30,18,30,18,20];
$numCols    = count($colWidths);

fwrite($fp, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
fwrite($fp, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');

// Freeze header row
fwrite($fp, '<sheetViews><sheetView workbookViewId="0">');
fwrite($fp, '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>');
fwrite($fp, '</sheetView></sheetViews>');

// Column widths
fwrite($fp, '<cols>');
for ($i = 0; $i < $numCols; $i++) {
    fwrite($fp, '<col min="'.($i+1).'" max="'.($i+1).'" width="'.$colWidths[$i].'" customWidth="1"/>');
}
fwrite($fp, '</cols>');

fwrite($fp, '<sheetData>');

// ── Header row (style 1 = bold blue) ─────────────────────────────────────────
$headers = [
    'Tipo Identificación','Cédula','Género','Nombres','Apellidos',
    'Teléfono','Teléfono Referencia','Referencia','Fecha Nacimiento','Edad',
    'Grupo Sisbén','Dirección','Barrio','Comuna','Zona',
    'Departamento','Municipio','Convivencia Actual','Persona con Discapacidad','Categoría Discapacidad',
    'Cabeza de Hogar','Se Reconoce Como','Orientación Sexual','Grupo Étnico','Tipo de Salud',
    'Condición Ocupación','Condición Componente','Fecha Atención','Estado','Fecha Registro',
    'Registrado por',
];

fwrite($fp, '<row r="1" ht="25" customHeight="1">');
foreach ($headers as $i => $h) {
    $ref = colLetter($i) . '1';
    fwrite($fp, '<c r="'.$ref.'" t="inlineStr" s="1"><is><t>'.xe($h).'</t></is></c>');
}
fwrite($fp, '</row>');

// ── Data rows ─────────────────────────────────────────────────────────────────
$fila = 2;
while ($row = $result->fetch_assoc()) {
    $vals = [
        $row['tipo_identificacion_cm']  ?? '',
        $row['cedula_persona_cm']       ?? '',
        $row['genero_persona_cm']       ?? '',
        $row['nombres_persona_cm']      ?? '',
        $row['apellidos_persona_cm']    ?? '',
        $row['telefono_persona_cm']     ?? '',
        $row['telefono_referencia_cm']  ?? '',
        $row['referencia_cm']           ?? '',
        !empty($row['fecha_nacimiento_cm']) ? date('d/m/Y', strtotime($row['fecha_nacimiento_cm'])) : '',
        $row['edad_calculada'] ?? $row['edad_cm'] ?? '',
        $row['grupo_sisben']            ?? '',
        $row['direccion_cm']            ?? '',
        $row['barrio_cm']               ?? '',
        $row['comuna_cm']               ?? '',
        $row['zona_cm']                 ?? '',
        $row['departamento_cm']         ?? '',
        $row['municipio_cm']            ?? '',
        $row['convivencia_actual']      ?? '',
        $row['persona_discapacidad']    ?? '',
        $row['cual_discapacidad']       ?? '',
        $row['cabeza_hogar']            ?? '',
        $row['se_reconoce_como']        ?? '',
        $row['orientacion_sexual']      ?? '',
        $row['grupo_etnico']            ?? '',
        $row['tipo_salud']              ?? '',
        $row['condicion_ocupacion']     ?? '',
        $row['condicion_componente']    ?? '',
        !empty($row['fecha_ingreso_cm'])  ? date('d/m/Y', strtotime($row['fecha_ingreso_cm']))  : '',
        $row['estado_cm']               ?? '',
        !empty($row['fecha_registro'])   ? date('d/m/Y H:i', strtotime($row['fecha_registro'])) : '',
        $row['registrado_por']          ?? 'N/A',
    ];

    fwrite($fp, '<row r="'.$fila.'">');
    foreach ($vals as $ci => $val) {
        $ref = colLetter($ci) . $fila;
        if (is_numeric($val) && $val !== '') {
            fwrite($fp, '<c r="'.$ref.'"><v>'.xe($val).'</v></c>');
        } else {
            fwrite($fp, '<c r="'.$ref.'" t="inlineStr"><is><t>'.xe($val).'</t></is></c>');
        }
    }
    fwrite($fp, '</row>');
    $fila++;
}

fwrite($fp, '</sheetData></worksheet>');
fclose($fp);

$mysqli->close();

// ── Package into XLSX (zip) ───────────────────────────────────────────────────

$xlsxTmp = tempnam(sys_get_temp_dir(), 'xlsx_');
$zip = new ZipArchive();
$zip->open($xlsxTmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);

$zip->addFromString('[Content_Types].xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
    '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
      '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
      '<Default Extension="xml" ContentType="application/xml"/>' .
      '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
      '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
      '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
    '</Types>');

$zip->addFromString('_rels/.rels',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
    '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
      '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
    '</Relationships>');

$zip->addFromString('xl/workbook.xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
    '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"' .
    ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
      '<sheets><sheet name="Personas CM" sheetId="1" r:id="rId1"/></sheets>' .
    '</workbook>');

$zip->addFromString('xl/_rels/workbook.xml.rels',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
    '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
      '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
      '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
    '</Relationships>');

// Styles: index 0 = default, index 1 = header (bold, blue bg #2E75B6, white text, centered, bordered)
$zip->addFromString('xl/styles.xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
    '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
      '<fonts count="2">' .
        '<font><sz val="11"/><name val="Calibri"/></font>' .
        '<font><b/><sz val="12"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font>' .
      '</fonts>' .
      '<fills count="3">' .
        '<fill><patternFill patternType="none"/></fill>' .
        '<fill><patternFill patternType="gray125"/></fill>' .
        '<fill><patternFill patternType="solid"><fgColor rgb="FF2E75B6"/></patternFill></fill>' .
      '</fills>' .
      '<borders count="2">' .
        '<border><left/><right/><top/><bottom/><diagonal/></border>' .
        '<border>' .
          '<left style="thin"><color rgb="FF000000"/></left>' .
          '<right style="thin"><color rgb="FF000000"/></right>' .
          '<top style="thin"><color rgb="FF000000"/></top>' .
          '<bottom style="thin"><color rgb="FF000000"/></bottom>' .
          '<diagonal/>' .
        '</border>' .
      '</borders>' .
      '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
      '<cellXfs count="2">' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
        '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' .
          '<alignment horizontal="center" vertical="center"/>' .
        '</xf>' .
      '</cellXfs>' .
    '</styleSheet>');

$zip->addFile($sheetTmp, 'xl/worksheets/sheet1.xml');
$zip->close();

// ── Send to browser ───────────────────────────────────────────────────────────
while (ob_get_level()) ob_end_clean();

$filename = 'PersonasCM_' . date('Y-m-d_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Content-Length: ' . filesize($xlsxTmp));

readfile($xlsxTmp);

unlink($xlsxTmp);
unlink($sheetTmp);
exit;
?>
