<?php
// Exportar actividades personalizadas a Excel
session_start();
if (ob_get_length()) { 
    header('Content-Type: text/plain; charset=utf-8'); 
    echo 'Salida previa'; 
    exit; 
}

require_once '../../conexion.php';
$mysqli->set_charset('utf8mb4');
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Actividades Personalizadas');

    // Encabezados en el orden especificado
    $headers = [
        'ID',
        'HORA DE INICIO',
        'HORA DE FINALIZACIÓN',
        'FECHA DE LA ACTIVIDAD',
        'NOMBRES Y APELLIDOS USUARIO/LÍDER',
        'GÉNERO',
        'FECHA DE NACIMIENTO',
        'TIPO DE DOCUMENTO',
        'NÚMERO DE DOCUMENTO',
        // CONDICIÓN
        'DESPLAZADO',
        'MUJER CABEZA DE HOGAR',
        'HOMBRE CABEZA DE HOGAR',
        'ORIENTACIÓN SEXUAL POBLACIÓN',
        'TIPO DE DISCAPACIDAD',
        'MIGRANTE',
        'HABITANTE DE CALLE Y/O RIESGO',
        // ETNIA
        'MESTIZO',
        'AFRODESCENDIENTE',
        'INDÍGENA',
        // INFORMACIÓN ADICIONAL
        'TIPO DE SEGURIDAD EN SALUD',
        'CONDICIÓN OCUPACIONAL',
        'NIVEL DE ESTUDIO',
        'TELÉFONO - CELULAR',
        // ACTIVIDAD
        'NOMBRE ACTIVIDAD',
        'EVENTO/TEMA O ASUNTO (QUE MUEVE LA META)',
        'NÚMERO DE ACTIVIDADES REALIZADAS',
        // BENEFICIADOS
        'TOTAL MASCULINO',
        'TOTAL FEMENINO',
        'TOTAL GENERAL',
        // SISTEMA
        'FECHA DE REGISTRO',
        'TIENE FIRMA'
    ];

    // Escribir cabeceras
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $col++;
    }

    // Estilo cabeceras
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $headerRange = 'A1:' . $lastCol . '1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]]
    ]);
    $sheet->getRowDimension(1)->setRowHeight(35);

    // Consulta de datos
    $query = "SELECT * FROM actividad_personalizada ORDER BY fecha_actividad DESC, id_actividad_personalizada DESC";
    $result = $mysqli->query($query);

    $rowNum = 2;
    if ($result && $result->num_rows > 0) {
        while ($r = $result->fetch_assoc()) {
            // Formatear fechas
            $fecha_actividad = '';
            if (!empty($r['fecha_actividad']) && $r['fecha_actividad'] != '0000-00-00') {
                $fecha_actividad = date('d/m/Y', strtotime($r['fecha_actividad']));
            }

            $fecha_nacimiento = '';
            if (!empty($r['fecha_nacimiento']) && $r['fecha_nacimiento'] != '0000-00-00') {
                $fecha_nacimiento = date('d/m/Y', strtotime($r['fecha_nacimiento']));
            }

            $fecha_registro = '';
            if (!empty($r['fecha_registro'])) {
                $fecha_registro = date('d/m/Y H:i', strtotime($r['fecha_registro']));
            }

            // Calcular total general
            $total_general = intval($r['total_masculino']) + intval($r['total_femenino']);

            // Convertir booleanos a texto
            $desplazado = $r['desplazado'] == 1 ? 'Sí' : 'No';
            $cabeza_hogar_mujer = $r['cabeza_hogar_mujer'] == 1 ? 'Sí' : 'No';
            $cabeza_hogar_hombre = $r['cabeza_hogar_hombre'] == 1 ? 'Sí' : 'No';
            $habitante_calle = $r['habitante_calle'] == 1 ? 'Sí' : 'No';
            $tiene_firma = !empty($r['firma_data']) ? 'Sí' : 'No';

            // Construir fila de datos en el orden especificado
            $data = [
                $r['id_actividad_personalizada'],
                $r['hora_inicio'],
                $r['hora_finalizacion'],
                $fecha_actividad,
                $r['nombres_apellidos'],
                $r['genero'],
                $fecha_nacimiento,
                $r['tipo_documento'],
                $r['numero_documento'],
                // CONDICIÓN
                $desplazado,
                $cabeza_hogar_mujer,
                $cabeza_hogar_hombre,
                $r['orientacion_sexual'] ?? '',
                $r['tipo_discapacidad'] ?? '',
                $r['migrante'] ?? '',
                $habitante_calle,
                // ETNIA
                $r['mestizo'] ?? '',
                $r['afrodescendiente'] ?? '',
                $r['indigena'] ?? '',
                // INFORMACIÓN ADICIONAL
                $r['tipo_seguridad_salud'] ?? '',
                $r['condicion_ocupacional'] ?? '',
                $r['nivel_estudio'] ?? '',
                $r['telefono_celular'] ?? '',
                // ACTIVIDAD
                $r['nombre_actividad'],
                $r['evento_tema'] ?? '',
                $r['numero_actividades'],
                // BENEFICIADOS
                $r['total_masculino'],
                $r['total_femenino'],
                $total_general,
                // SISTEMA
                $fecha_registro,
                $tiene_firma
            ];

            $col = 'A';
            foreach ($data as $val) {
                $sheet->setCellValue($col . $rowNum, $val);
                $col++;
            }

            // Estilo de fila
            $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
            ]);

            // Alternar color de fondo
            if ($rowNum % 2 == 0) {
                $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']]
                ]);
            }

            $rowNum++;
        }
    }

    // Fila de totales
    if ($rowNum > 2) {
        $sheet->setCellValue('A' . $rowNum, 'TOTALES');
        
        // Calcular totales
        $query_totales = "SELECT 
            COUNT(*) as total_registros,
            SUM(total_masculino) as suma_masculino,
            SUM(total_femenino) as suma_femenino,
            SUM(total_masculino + total_femenino) as suma_total
        FROM actividad_personalizada";
        
        $result_totales = $mysqli->query($query_totales);
        if ($result_totales && $row_totales = $result_totales->fetch_assoc()) {
            $sheet->setCellValue('B' . $rowNum, $row_totales['total_registros'] . ' registros');
            
            // Totales en las columnas correspondientes (columnas AA, AB, AC)
            $col_masculino = 'AA'; // Columna 27
            $col_femenino = 'AB';  // Columna 28
            $col_total = 'AC';     // Columna 29
            
            $sheet->setCellValue($col_masculino . $rowNum, $row_totales['suma_masculino']);
            $sheet->setCellValue($col_femenino . $rowNum, $row_totales['suma_femenino']);
            $sheet->setCellValue($col_total . $rowNum, $row_totales['suma_total']);
        }
        
        $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '43E97B']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '333333']]]
        ]);
    }

    // Ajustar anchos de columnas
    for ($i = 1; $i <= count($headers); $i++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        
        // Columnas específicas con anchos personalizados
        if ($i === 5 || $i === 24 || $i === 25) { // Nombres, Actividad, Evento
            $sheet->getColumnDimension($colLetter)->setWidth(35);
        } elseif ($i === 14) { // Tipo discapacidad
            $sheet->getColumnDimension($colLetter)->setWidth(30);
        } else {
            $sheet->getColumnDimension($colLetter)->setWidth(20);
        }
    }

    // Exportar
    if (ob_get_length()) ob_end_clean();
    $filename = 'Actividades_Personalizadas_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error al generar el archivo Excel: " . $e->getMessage();
    exit;
}
