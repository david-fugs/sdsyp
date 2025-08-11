<?php
// Eliminar cualquier salida previa
if (ob_get_length()) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: Hay salida previa al header. El archivo Excel se corromperá.\n";
    echo "Verifica que no haya espacios, saltos de línea o echo/print antes de los headers.\n";
    exit;
}

// Forzar codificación UTF-8 en la conexión MySQL
require_once '../../conexion.php';
if (isset($mysqli)) {
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES 'utf8mb4'");
    $mysqli->query("SET CHARACTER SET 'utf8mb4'");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Verificar que se proporcione el año
if (!isset($_GET['year']) || empty($_GET['year'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error: Año no proporcionado';
    exit;
}

$year = intval($_GET['year']);

try {

    // Crear nuevo objeto Spreadsheet
    $spreadsheet = new Spreadsheet();
    // Forzar codificación UTF-8 en Spreadsheet
    \PhpOffice\PhpSpreadsheet\Settings::setLocale('es');
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Informe SDSYP ' . $year);

    // Configurar metadatos
    $spreadsheet->getProperties()
        ->setCreator('Sistema SDSYP')
        ->setTitle('Informe Completo SDSYP ' . $year)
        ->setSubject('Datos completos de personas y movimientos')
        ->setDescription('Informe generado el ' . date('Y-m-d H:i:s'));

    // Definir las cabeceras reales según la estructura de la tabla personas y los datos solicitados
    $headers = [
        'CÉDULA',
        'TIPO IDENTIFICACIÓN',
        'NOMBRES',
        'APELLIDOS',
        'FECHA NACIMIENTO',
        'TELÉFONO',
        'TELÉFONO REFERENCIA',
        'REFERENCIA',
        'GÉNERO',
        'GRUPO SISBEN',
        'PERSONA DISCAPACIDAD',
        'CUÁL DISCAPACIDAD',
        'CABEZA HOGAR',
        'LÍDER COMUNIDAD',
        'SE RECONOCE COMO',
        'ORIENTACIÓN SEXUAL',
        'EXPERIENCIA MIGRATORIA',
        'GRUPO ÉTNICO',
        'TIPO SALUD',
        'NIVEL EDUCATIVO',
        'GRUPO',
        'POLÍTICA PÚBLICA',
        'RESPONSABLE',
        'ZONA PERSONA',
        'DIRECCIÓN',
        'CORREO',
        'BARRIO',
        'COMUNA',
        'EPS',
        'PESO',
        'TALLA',
        'PATOLOGÍAS',
        'FACTORES RIESGO',
        'FACTORES PREVENTIVOS',
        'INGRESOS ECONÓMICOS',
        'CONVIVENCIA ACTUAL',
        'RESULTADO ACTIVIDAD',
        'REMISIÓN',
        'TELÉFONO REFERENCIA PERSONA',
        'CONDICIÓN OCUPACIÓN',
        'CONDICIÓN COMPONENTE',
        // Datos de movimiento y cálculos
        'CENTRO DE VIDA', 'POLÍTICA PÚBLICA',
        'ESTADO ACTUAL', 'FECHA ÚLTIMO ESTADO',
        'ÚLTIMA META', 'ÚLTIMA ACTIVIDAD', 'ÚLTIMA ACCIÓN', 'ÚLTIMO DPTO PROCEDENCIA',
        'MOVIMIENTOS AÑO', 'TRASLADOS AÑO', 'ÚLTIMO CENTRO TRASLADO',
        'ACTIVO DESDE', 'ACTIVO HASTA', 'DÍAS ACTIVOS'
        , 'DÍAS EN CENTRO ANTERIOR / ACTUAL (TRASLADADO)'
    ];

    // Escribir cabeceras en la fila 1
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }

    // Aplicar estilos a las cabeceras
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $headerRange = 'A1:' . $lastCol . '1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 11,
            'color' => ['rgb' => '2D3436']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFF3A0'] // Amarillo muy suave
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ]);

    // Configurar altura de la fila de cabeceras
    $sheet->getRowDimension(1)->setRowHeight(40);

    // Consulta completa para obtener todos los datos
    $query = "
        SELECT 
            p.*,
            g.descripcion_grupo as centro_vida,
            b.nombre_bar as barrio_nombre,
            c.nombre_com as comuna_nombre,
            u.nombre as nombre_usuario,

            -- Descripción de la política pública del último movimiento
            (SELECT pol.descripcion_politica
             FROM movimiento_persona mp
             LEFT JOIN politicas_publicas pol ON mp.id_politica_publica = pol.id_politica
             WHERE mp.cedula_persona = p.cedula_persona
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS descripcion_politica,
            -- Último estado/condición del movimiento
            (SELECT cc.descripcion_condicion 
             FROM movimiento_persona mp 
             JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_estado_movimiento,
             
            (SELECT mp.fecha_movimiento 
             FROM movimiento_persona mp 
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS fecha_ultimo_movimiento,
             
            -- Última meta, actividad y acción
            (SELECT m.descripcion_meta 
             FROM movimiento_persona mp 
             LEFT JOIN metas m ON mp.id_meta = m.id_meta
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_meta,
             
            (SELECT a.descripcion_actividad 
             FROM movimiento_persona mp 
             LEFT JOIN actividades a ON mp.id_actividad = a.id_actividad
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_actividad,
             
            (SELECT ac.descripcion_accion 
             FROM movimiento_persona mp 
             LEFT JOIN acciones ac ON mp.id_accion = ac.id_accion
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultima_accion,
             
            (SELECT mp.departamento_procedencia 
             FROM movimiento_persona mp 
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_departamento_procedencia,

            -- Centro de vida anterior y actual del último movimiento
            (SELECT g_ant.descripcion_grupo
             FROM movimiento_persona mp
             LEFT JOIN grupos g_ant ON mp.id_centro_vida_traslado_anterior = g_ant.id_grupo
             WHERE mp.cedula_persona = p.cedula_persona
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_centro_traslado_anterior,
            (SELECT g_act.descripcion_grupo
             FROM movimiento_persona mp
             LEFT JOIN grupos g_act ON mp.id_centro_vida_traslado = g_act.id_grupo
             WHERE mp.cedula_persona = p.cedula_persona
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_centro_traslado_actual,

            -- Estadísticas de movimientos en el año
            (SELECT COUNT(*)
             FROM movimiento_persona mp2
             WHERE mp2.cedula_persona = p.cedula_persona
             AND YEAR(mp2.fecha_movimiento) = ?) AS movimientos_en_year,
             
            -- Traslados en el año
            (SELECT COUNT(*)
             FROM movimiento_persona mp3
             JOIN condiciones_componente cc3 ON mp3.id_condicion = cc3.id_condicion
             WHERE mp3.cedula_persona = p.cedula_persona
             AND cc3.descripcion_condicion LIKE '%TRASLADADO%'
             AND YEAR(mp3.fecha_movimiento) = ?) AS traslados_en_year,

            -- Último centro de traslado (para compatibilidad)
            (SELECT g2.descripcion_grupo
             FROM movimiento_persona mp4
             JOIN condiciones_componente cc4 ON mp4.id_condicion = cc4.id_condicion
             LEFT JOIN grupos g2 ON mp4.id_centro_vida_traslado = g2.id_grupo
             WHERE mp4.cedula_persona = p.cedula_persona
             AND cc4.descripcion_condicion LIKE '%TRASLADADO%'
             ORDER BY mp4.fecha_movimiento DESC
             LIMIT 1) AS ultimo_centro_traslado,
             
            -- Cálculo de edad actual
            CASE 
                WHEN p.fecha_nacimiento IS NOT NULL AND p.fecha_nacimiento != '0000-00-00' 
                THEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE())
                ELSE NULL 
            END AS edad_actual,
            
            -- Cálculo de días activos
            CASE 
                WHEN p.activo_desde IS NOT NULL AND p.activo_desde != '0000-00-00' THEN
                    CASE
                        WHEN (SELECT cc.descripcion_condicion 
                              FROM movimiento_persona mp 
                              JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                              WHERE mp.cedula_persona = p.cedula_persona 
                              ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                              LIMIT 1) IS NOT NULL 
                             AND (UPPER((SELECT cc.descripcion_condicion 
                                        FROM movimiento_persona mp 
                                        JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                                        WHERE mp.cedula_persona = p.cedula_persona 
                                        ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                                        LIMIT 1)) LIKE '%FALLECIDO%' 
                                  OR UPPER((SELECT cc.descripcion_condicion 
                                           FROM movimiento_persona mp 
                                           JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                                           WHERE mp.cedula_persona = p.cedula_persona 
                                           ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                                           LIMIT 1)) LIKE '%EVADIDO%'
                                  OR UPPER((SELECT cc.descripcion_condicion 
                                           FROM movimiento_persona mp 
                                           JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                                           WHERE mp.cedula_persona = p.cedula_persona 
                                           ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                                           LIMIT 1)) LIKE '%RETIRADO%') THEN
                            DATEDIFF((SELECT mp.fecha_movimiento 
                                     FROM movimiento_persona mp 
                                     WHERE mp.cedula_persona = p.cedula_persona 
                                     ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
                                     LIMIT 1), p.activo_desde)
                        ELSE DATEDIFF(CURDATE(), p.activo_desde)
                    END
                ELSE NULL
            END AS dias_activos
            
        FROM personas p
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        LEFT JOIN barrios b ON p.id_barrio_persona = b.id_bar
        LEFT JOIN comunas c ON p.id_comuna_persona = c.id_com
        LEFT JOIN usuarios u ON p.id_usuario = u.id
        WHERE p.estado_persona = 1 
        ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $year, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $row_num = 2; // Empezar en la fila 2 (después de las cabeceras)
    $total_registros = 0;

    while ($row = $result->fetch_assoc()) {
        $total_registros++;

        // Determinar el estado actual basado en el último movimiento o condicion_componente
        $estado_actual = 'ACTIVO';
        if (isset($row['condicion_componente']) && trim(mb_strtolower($row['condicion_componente'])) === 'usuario interesado') {
            $estado_actual = 'Usuario Interesado';
        } elseif ($row['ultimo_estado_movimiento']) {
            $ultimo_estado = strtoupper($row['ultimo_estado_movimiento']);
            if (strpos($ultimo_estado, 'EVADIDO') !== false || strpos($ultimo_estado, 'EVASION') !== false) {
                $estado_actual = 'EVADIDO';
            } elseif (strpos($ultimo_estado, 'FALLECIDO') !== false || strpos($ultimo_estado, 'MUERTE') !== false) {
                $estado_actual = 'FALLECIDO';
            } elseif (strpos($ultimo_estado, 'RETIRADO') !== false || strpos($ultimo_estado, 'RETIRO') !== false) {
                $estado_actual = 'RETIRADO VOLUNTARIO';
            } elseif (strpos($ultimo_estado, 'TRASLADADO') !== false || strpos($ultimo_estado, 'TRASLADO') !== false) {
                $estado_actual = 'ACTIVO (TRASLADADO)';
            } elseif (strpos($ultimo_estado, 'SUSPENDIDO') !== false || strpos($ultimo_estado, 'SUSPENSION') !== false) {
                $estado_actual = 'SUSPENDIDO';
            } elseif (strpos($ultimo_estado, 'ACTIVO') !== false || strpos($ultimo_estado, 'INGRESO') !== false) {
                $estado_actual = 'ACTIVO';
            } else {
                $estado_actual = $ultimo_estado;
            }
        }

        // Formatear fechas
        $fecha_nacimiento = '';
        if ($row['fecha_nacimiento'] && $row['fecha_nacimiento'] != '0000-00-00') {
            $fecha_nacimiento = date('d/m/Y', strtotime($row['fecha_nacimiento']));
        }

        $activo_desde = 'No registrada';
        if ($row['activo_desde'] && $row['activo_desde'] != '0000-00-00') {
            $activo_desde = date('d/m/Y', strtotime($row['activo_desde']));
        }

        $fecha_ultimo_estado = '';
        if ($row['fecha_ultimo_movimiento']) {
            $fecha_ultimo_estado = date('d/m/Y', strtotime($row['fecha_ultimo_movimiento']));
        }

        // Determinar "ACTIVO HASTA"
        $activo_hasta = '';
        if ($estado_actual == 'FALLECIDO' || $estado_actual == 'EVADIDO') {
        $activo_hasta = $fecha_ultimo_estado ?: 'No registrada';
    } elseif ($estado_actual == 'TRASLADADO') {
        // Si hay centro anterior y centro traslado, mostrar ambos
        if (!empty($row['ultimo_centro_traslado_anterior']) && !empty($row['ultimo_centro_traslado_actual'])) {
            $activo_hasta = 'Trasladado de: ' . $row['ultimo_centro_traslado_anterior'] . ' a: ' . $row['ultimo_centro_traslado_actual'];
        } elseif (!empty($row['ultimo_centro_traslado_actual'])) {
            $activo_hasta = 'Trasladado a: ' . $row['ultimo_centro_traslado_actual'];
        } else {
            $activo_hasta = 'Traslado sin destino';
        }
        } elseif ($estado_actual == 'RETIRADO VOLUNTARIO') {
            $activo_hasta = $fecha_ultimo_estado ?: 'No registrada';
        } elseif($estado_actual == 'CPSAM EVADADIDO') {
            $activo_hasta = $fecha_ultimo_estado ?: 'No registrada';
        }
        else {
            $activo_hasta = 'N/A';
        }

        // Obtener programas
        $programas = 'Sin programa';
        $table_check = $mysqli->query("SHOW TABLES LIKE 'persona_programa'");
        if ($table_check && $table_check->num_rows > 0) {
            $programas_query = "SELECT GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC) AS programas
                               FROM persona_programa pp
                               JOIN programas pr ON pp.id_programa = pr.id_programa
                               WHERE pp.cedula_persona = ?";
            $stmt_prog = $mysqli->prepare($programas_query);
            $stmt_prog->bind_param("s", $row['cedula_persona']);
            $stmt_prog->execute();
            $result_prog = $stmt_prog->get_result();
            $programas_row = $result_prog->fetch_assoc();
            $programas = $programas_row['programas'] ?: 'Sin programa';
        } else {
            $programas = $row['centro_vida'] ?: 'Sin programa';
        }

        // Calcular días en cada centro por cada traslado
        $traslados_info = '';
        if (strpos($estado_actual, 'TRASLADADO') !== false) {
            // Obtener todos los movimientos de traslado ordenados por fecha ASC
            $traslados_query = "SELECT mp.fecha_movimiento, mp.id_centro_vida_traslado_anterior, mp.id_centro_vida_traslado, g_ant.descripcion_grupo as centro_anterior, g_act.descripcion_grupo as centro_actual FROM movimiento_persona mp LEFT JOIN grupos g_ant ON mp.id_centro_vida_traslado_anterior = g_ant.id_grupo LEFT JOIN grupos g_act ON mp.id_centro_vida_traslado = g_act.id_grupo WHERE mp.cedula_persona = ? AND mp.id_centro_vida_traslado IS NOT NULL ORDER BY mp.fecha_movimiento ASC, mp.id_movimiento_persona ASC";
            $stmt_tras = $mysqli->prepare($traslados_query);
            $stmt_tras->bind_param("s", $row['cedula_persona']);
            $stmt_tras->execute();
            $res_tras = $stmt_tras->get_result();
            $fechas_traslados = [];
            while ($row_tras = $res_tras->fetch_assoc()) {
                $fechas_traslados[] = $row_tras;
            }
            // Si hay traslados, calcular los días en cada centro
            if (count($fechas_traslados) > 0) {
                $fechas = [];
                // Fecha de inicio (activo_desde)
                $fecha_inicio = ($row['activo_desde'] && $row['activo_desde'] != '0000-00-00') ? $row['activo_desde'] : null;
                for ($i = 0; $i < count($fechas_traslados); $i++) {
                    $tras = $fechas_traslados[$i];
                    // Días en centro anterior
                    if ($fecha_inicio) {
                        $dt1 = new DateTime($fecha_inicio);
                        $dt2 = new DateTime($tras['fecha_movimiento']);
                        $dias = $dt1->diff($dt2)->days;
                        $centro = $tras['centro_anterior'] ?? 'anterior';
                        $traslados_info .= $dias . ' días en ' . $centro . "\n";
                    }
                    // Preparar para el siguiente ciclo: la fecha de inicio para el siguiente traslado es la fecha de este traslado
                    $fecha_inicio = $tras['fecha_movimiento'];
                }
                // Días en el último centro actual: desde el último traslado hasta hoy o fecha último movimiento si no está activo
                $ultimo_tras = end($fechas_traslados);
                $dt3 = new DateTime($ultimo_tras['fecha_movimiento']);
                // Buscar el siguiente movimiento después del último traslado
                $sig_mov_query = "SELECT mp.fecha_movimiento FROM movimiento_persona mp WHERE mp.cedula_persona = ? AND mp.fecha_movimiento > ? ORDER BY mp.fecha_movimiento ASC, mp.id_movimiento_persona ASC LIMIT 1";
                $stmt_sig = $mysqli->prepare($sig_mov_query);
                $stmt_sig->bind_param("ss", $row['cedula_persona'], $ultimo_tras['fecha_movimiento']);
                $stmt_sig->execute();
                $res_sig = $stmt_sig->get_result();
                $row_sig = $res_sig->fetch_assoc();
                if ($row_sig && $row_sig['fecha_movimiento']) {
                    $dt4 = new DateTime($row_sig['fecha_movimiento']);
                } else {
                    $dt4 = new DateTime(); // hoy
                }
                $dias = $dt3->diff($dt4)->days;
                $centro = $ultimo_tras['centro_actual'] ?? 'actual';
                $traslados_info .= $dias . ' días en ' . $centro;
            }
        }

        // Escribir datos en las celdas
        // Si es Usuario Interesado, días activos debe ser N/A
        $dias_activos_val = (mb_strtolower($estado_actual) === 'usuario interesado') ? 'N/A' : ($row['dias_activos'] ?? 'N/A');
        $data = [

            $row['cedula_persona'] ?? '',
            $row['tipo_identificacion'] ?? '',
            $row['nombres_persona'] ?? '',
            $row['apellidos_persona'] ?? '',
            $fecha_nacimiento,
            $row['telefono_persona'] ?? '',
            $row['telefono_referencia_persona'] ?? '',
            $row['referencia_persona'] ?? '',
            $row['genero_persona'] ?? '',
            $row['grupo_sisben'] ?? '',
            $row['persona_discapacidad'] ?? '',
            $row['cual_discapacidad'] ?? '',
            $row['cabeza_hogar'] ?? '',
            $row['lider_comunidad'] ?? '',
            $row['se_reconoce_como'] ?? '',
            $row['orientacion_sexual'] ?? '',
            $row['experiencia_migratoria'] ?? '',
            $row['grupo_etnico'] ?? '',
            $row['tipo_salud'] ?? '',
            $row['nivel_educativo'] ?? '',
            $row['centro_vida'] ?? '',
            $row['descripcion_politica'] ?? '',
            $row['nombre_usuario'] ?? '',
            $row['zona_persona'] ?? '',
            $row['direccion_persona'] ?? '',
            $row['correo_persona'] ?? '',
            $row['barrio_nombre'] ?? '',
            $row['comuna_nombre'] ?? '',
            $row['eps'] ?? '',
            $row['peso'] ?? '',
            $row['talla'] ?? '',
            $row['patologias'] ?? '',
            $row['factores_riesgo'] ?? '',
            $row['factores_preventivos'] ?? '',
            $row['ingresos_economicos'] ?? '',
            $row['convivencia_actual'] ?? '',
            $row['resultado_actividad'] ?? '',
            $row['remision'] ?? '',
            $row['telefono_referencia_persona'] ?? '',
            $row['condicion_ocupacion'] ?? '',
            $row['condicion_componente'] ?? '',
            // Datos de movimiento y cálculos
            $row['centro_vida'] ?? 'No asignado',
            $row['descripcion_politica'] ?? 'No asignada',
            $estado_actual,
            $fecha_ultimo_estado,
            $row['ultima_meta'] ?? 'No registrada',
            $row['ultima_actividad'] ?? 'No registrada',
            $row['ultima_accion'] ?? 'No registrada',
            $row['ultimo_departamento_procedencia'] ?? 'No registrado',
            $row['movimientos_en_year'] ?? 0,
            $row['traslados_en_year'] ?? 0,
            $row['ultimo_centro_traslado'] ?? 'N/A',
            $activo_desde,
            $activo_hasta,
            $dias_activos_val,
            $traslados_info
        ];

        // Escribir datos en la fila
        $col = 'A';
        foreach ($data as $value) {
            $sheet->setCellValue($col . $row_num, $value);
            $col++;
        }

        // Aplicar estilos a las filas de datos
        $dataRange = 'A' . $row_num . ':' . $lastCol . $row_num;
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E0E0E0']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ]);

        $row_num++;
    }

    // Configurar anchos de columna automáticamente
    // Configurar ancho fijo de todas las columnas a 30
    for ($colIdx = 1; $colIdx <= count($headers); $colIdx++) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
        $sheet->getColumnDimension($col)->setWidth(30);
    }

    // Configurar altura mínima de filas
    for ($i = 2; $i < $row_num; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(25);
    }



    // Descargar el archivo directamente sin guardarlo
    $fileName = 'SDSYP_Informe_Completo_' . $year . '_' . date('Y-m-d_H-i-s') . '.xlsx';
    // Limpiar el buffer de salida antes de enviar headers
    if (ob_get_length()) {
        ob_end_clean();
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al generar Excel: ' . $e->getMessage();
}

$mysqli->close();
// No cierre PHP para evitar salida accidental
