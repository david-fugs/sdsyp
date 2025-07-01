<?php
require_once('../../fpdf/fpdf.php');
include("../../conexion.php");

// Configurar codificación UTF-8 para caracteres especiales
mysqli_set_charset($mysqli, "utf8");

class InformePDF extends FPDF
{
    private $year;
    private $currentStats;
    
    public function __construct($year = null, $stats = null) {
        parent::__construct('L', 'mm', 'A3'); // Cambiar a A3 horizontal para más espacio
        $this->year = $year ?: date('Y');
        $this->currentStats = $stats;
    }
    
    // Método para manejar caracteres UTF-8
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        // Convertir caracteres especiales para PDF
        $txt = $this->convertUtf8($txt);
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }
    
    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        // Convertir caracteres especiales para PDF
        $txt = $this->convertUtf8($txt);
        parent::MultiCell($w, $h, $txt, $border, $align, $fill);
    }
    
    // Convertir caracteres UTF-8 a ISO-8859-1 para FPDF
    function convertUtf8($txt)
    {
        // Si iconv está disponible, usarlo para la conversión
        if (function_exists('iconv')) {
            return iconv('UTF-8', 'ISO-8859-1//IGNORE', $txt);
        }
        
        // Mapeo manual de caracteres especiales como fallback
        $utf8_chars = ['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ','ü','Ü','¿','¡'];
        $iso_chars = ['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ','ü','Ü','¿','¡'];
        
        return str_replace($utf8_chars, $iso_chars, $txt);
    }
    
    // Encabezado de página
    function Header()
    {
        // Logo
        if (file_exists('../../img/logo.png')) {
            $this->Image('../../img/logo.png', 10, 6, 25);
        }
        
        // Título principal
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(41, 128, 185);
        $this->Cell(0, 12, 'SISTEMA DE SEGUIMIENTO Y DATOS PARA PERSONAS', 0, 1, 'C');
        
        // Subtítulo
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(52, 73, 94);
        $this->Cell(0, 8, 'INFORME ANUAL DETALLADO ' . $this->year, 0, 1, 'C');
        
        // Línea decorativa
        $this->SetDrawColor(41, 128, 185);
        $this->SetLineWidth(0.8);
        $this->Line(15, 32, 405, 32);
        
        // Información adicional
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(127, 140, 141);
        $this->SetY(34);
        $this->Cell(0, 4, 'Generado: ' . date('d/m/Y H:i:s') . ' | Página ' . $this->PageNo(), 0, 1, 'C');
        
        $this->Ln(8);
    }
    
    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        
        // Línea decorativa
        $this->SetDrawColor(41, 128, 185);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY() - 2, 405, $this->GetY() - 2);
        
        // Texto del pie
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(127, 140, 141);
        $this->Cell(195, 8, '© 2025 SDSYP - Sistema de Seguimiento y Datos para Personas', 0, 0, 'L');
        $this->Cell(195, 8, 'Página ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
    
    // Portada del informe
    function Portada()
    {
        $this->AddPage();
        $this->SetY(80);
        
        // Título principal de portada
        $this->SetFont('Arial', 'B', 32);
        $this->SetTextColor(41, 128, 185);
        $this->Cell(0, 25, 'INFORME ANUAL COMPLETO', 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 42);
        $this->SetTextColor(231, 76, 60);
        $this->Cell(0, 30, $this->year, 0, 1, 'C');
        
        // Cuadro de resumen expandido
        $this->SetY(160);
        $this->SetFillColor(236, 240, 241);
        $this->SetDrawColor(189, 195, 199);
        $this->Rect(80, 160, 260, 80, 'FD');
        
        // Estadísticas principales
        $this->SetY(175);
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(52, 73, 94);
        $this->Cell(0, 10, 'RESUMEN EJECUTIVO', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 13);
        $this->SetTextColor(44, 62, 80);
        
        if ($this->currentStats) {
            $this->Cell(0, 8, '📋 Registros en informe: ' . ($this->currentStats['total_registros'] ?? 0), 0, 1, 'C');
            $this->Cell(0, 8, '👥 Personas con movimientos: ' . ($this->currentStats['personas_con_movimientos'] ?? 0), 0, 1, 'C');
            $this->Cell(0, 8, '✅ Personas activas: ' . ($this->currentStats['personas_activas'] ?? 0), 0, 1, 'C');
            $this->Cell(0, 8, '🔄 Total de movimientos: ' . ($this->currentStats['total_movimientos'] ?? 0), 0, 1, 'C');
        }
        
        // Descripción del contenido
        $this->SetY(260);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(41, 128, 185);
        $this->Cell(0, 8, 'CONTENIDO DEL INFORME', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(0, 6, '• 16 campos detallados por persona', 0, 1, 'C');
        $this->Cell(0, 6, '• Incluye fechas ACTIVO DESDE y ACTIVO HASTA', 0, 1, 'C');
        $this->Cell(0, 6, '• Datos de centros de vida, programas y políticas públicas', 0, 1, 'C');
        $this->Cell(0, 6, '• Conteo de movimientos y traslados por año', 0, 1, 'C');
    }
    
    // Crear tabla con todas las 16 columnas
    function CreateDetailedTable($data)
    {
        // Solo proceder si hay datos
        if (empty($data)) {
            $this->SetFont('Arial', 'B', 14);
            $this->SetTextColor(231, 76, 60);
            $this->Cell(0, 20, 'NO HAY DATOS DISPONIBLES PARA ESTE AÑO', 0, 1, 'C');
            return;
        }
        
        // Headers completos (16 columnas como en Excel)
        $headers = [
            'Cédula', 'Nombres', 'Apellidos', 'Género', 'F.Nac', 'Edad',
            'Teléfono', 'Referencia', 'Centro Vida', 'Programas',
            'Estado', 'Política Púb.', 'Mov.', 'Tras.', 'Activo Desde', 'Activo Hasta'
        ];
        
        // Anchos optimizados para A3 horizontal (390mm disponibles)
        $widths = [20, 25, 25, 15, 18, 12, 20, 25, 30, 30, 18, 30, 12, 12, 22, 25];
        
        // Verificar que hay espacio suficiente para la tabla
        if ($this->GetY() > 250) {
            $this->AddPage();
        }
        
        // Encabezados con estilo mejorado (amarillo como en Excel)
        $this->SetFillColor(255, 243, 160); // Amarillo suave
        $this->SetTextColor(45, 52, 54); // Texto oscuro
        $this->SetDrawColor(253, 203, 110); // Borde amarillo
        $this->SetLineWidth(0.3);
        $this->SetFont('Arial', 'B', 7);
        
        // Primera fila de headers
        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Datos con filas más altas y alternadas
        $this->SetFont('Arial', '', 6);
        $this->SetTextColor(44, 62, 80);
        
        foreach ($data as $index => $row) {
            // Verificar si necesitamos nueva página
            if ($this->GetY() > 260) {
                $this->AddPage();
                
                // Repetir headers en nueva página
                $this->SetFillColor(255, 243, 160);
                $this->SetTextColor(45, 52, 54);
                $this->SetFont('Arial', 'B', 7);
                
                for ($i = 0; $i < count($headers); $i++) {
                    $this->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
                }
                $this->Ln();
                
                $this->SetFont('Arial', '', 6);
                $this->SetTextColor(44, 62, 80);
            }
            
            // Color alternado para filas
            if ($index % 2 == 0) {
                $this->SetFillColor(248, 249, 250);
            } else {
                $this->SetFillColor(255, 255, 255);
            }
            
            // Fila de datos más alta (8mm en lugar de 6mm)
            for ($i = 0; $i < count($row) && $i < count($widths); $i++) {
                $text = $row[$i] ?? '';
                
                // Truncar texto según el ancho de la columna
                $maxLength = ($widths[$i] > 25) ? 30 : ($widths[$i] > 20 ? 20 : 15);
                if (strlen($text) > $maxLength) {
                    $text = substr($text, 0, $maxLength - 3) . '...';
                }
                
                $this->Cell($widths[$i], 8, $text, 1, 0, 'C', true);
            }
            $this->Ln();
        }
    }
    
    // Página de estadísticas mejorada
    function EstadisticasPage()
    {
        $this->AddPage();
        
        // Título
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(41, 128, 185);
        $this->Cell(0, 12, 'ESTADÍSTICAS DETALLADAS - ' . $this->year, 0, 1, 'C');
        $this->Ln(10);
        
        if (!$this->currentStats) {
            $this->SetFont('Arial', '', 12);
            $this->SetTextColor(231, 76, 60);
            $this->Cell(0, 10, 'No hay estadísticas disponibles', 0, 1, 'C');
            return;
        }
        
        // Estadísticas principales en cuadros mejorados
        $stats = [
            ['Total Registros', $this->currentStats['total_registros'] ?? 0, [46, 204, 113]],
            ['Con Movimientos', $this->currentStats['personas_con_movimientos'] ?? 0, [52, 152, 219]],
            ['Personas Activas', $this->currentStats['personas_activas'] ?? 0, [155, 89, 182]],
            ['Tot. Movimientos', $this->currentStats['total_movimientos'] ?? 0, [241, 196, 15]]
        ];
        
        $boxWidth = 85;
        $spacing = 15;
        $totalWidth = (count($stats) * $boxWidth) + ((count($stats) - 1) * $spacing);
        $startX = (420 - $totalWidth) / 2;
        
        foreach ($stats as $i => $stat) {
            $x = $startX + ($i * ($boxWidth + $spacing));
            $y = 70;
            
            // Cuadro con gradiente simulado
            $this->SetFillColor($stat[2][0], $stat[2][1], $stat[2][2]);
            $this->Rect($x, $y, $boxWidth, 35, 'F');
            
            // Número grande
            $this->SetXY($x, $y + 8);
            $this->SetFont('Arial', 'B', 20);
            $this->SetTextColor(255, 255, 255);
            $this->Cell($boxWidth, 12, number_format($stat[1]), 0, 1, 'C');
            
            // Etiqueta
            $this->SetXY($x, $y + 20);
            $this->SetFont('Arial', 'B', 8);
            $this->Cell($boxWidth, 10, $stat[0], 0, 1, 'C');
        }
        
        // Información adicional
        $this->SetY(130);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(41, 128, 185);
        $this->Cell(0, 8, 'INFORMACIÓN DEL INFORME', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(44, 62, 80);
        $this->Ln(5);
        
        $info_items = [
            '📊 Formato: 16 columnas de datos detallados',
            '🎨 Incluye: Cédula, nombres, apellidos, género, fecha nacimiento, edad',
            '📞 Contacto: Teléfono y referencia personal',
            '🏢 Programas: Centro de vida y programas asignados',
            '📋 Estado: Estado actual y política pública',
            '🔄 Actividad: Conteo de movimientos y traslados del año',
            '📅 Fechas: ACTIVO DESDE y ACTIVO HASTA'
        ];
        
        foreach ($info_items as $item) {
            $this->Cell(0, 6, $item, 0, 1, 'L');
        }
        
        // Tabla de distribución por estado si hay datos
        if (isset($this->currentStats['personas_por_estado']) && !empty($this->currentStats['personas_por_estado'])) {
            $this->SetY(200);
            $this->SetFont('Arial', 'B', 12);
            $this->SetTextColor(41, 128, 185);
            $this->Cell(0, 8, 'DISTRIBUCIÓN POR ESTADO', 0, 1, 'C');
            $this->Ln(3);
            
            // Crear tabla de estados
            $this->SetFont('Arial', 'B', 9);
            $this->SetFillColor(41, 128, 185);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(90, 7, 'Estado', 1, 0, 'C', true);
            $this->Cell(30, 7, 'Cantidad', 1, 1, 'C', true);
            
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(44, 62, 80);
            $fill = false;
            foreach ($this->currentStats['personas_por_estado'] as $estado => $cantidad) {
                if ($cantidad > 0) { // Solo mostrar estados que tienen personas
                    $this->SetFillColor($fill ? 248 : 255, $fill ? 249 : 255, $fill ? 250 : 255);
                    $this->Cell(90, 6, $estado, 1, 0, 'L', true);
                    $this->Cell(30, 6, $cantidad, 1, 1, 'C', true);
                    $fill = !$fill;
                }
            }
        }
    }
}

// Verificar parámetros
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

try {
    // Consulta principal con la misma lógica que el Excel (incluyendo estados basados en movimientos)
    $dataQuery = "
        SELECT DISTINCT
            p.cedula_persona,
            p.nombres_persona,
            p.apellidos_persona,
            CASE p.genero_persona 
                WHEN 'M' THEN 'MASCULINO'
                WHEN 'F' THEN 'FEMENINO'
                ELSE p.genero_persona
            END as genero_persona,
            CASE 
                WHEN p.fecha_nacimiento IS NOT NULL AND p.fecha_nacimiento != '0000-00-00' 
                THEN DATE_FORMAT(p.fecha_nacimiento, '%d/%m/%Y')
                ELSE NULL 
            END AS fecha_nacimiento,
            CASE 
                WHEN p.fecha_nacimiento IS NOT NULL AND p.fecha_nacimiento != '0000-00-00' 
                THEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE())
                ELSE NULL 
            END AS edad_actual,
            p.telefono_persona,
            p.referencia_persona,
            COALESCE(g.descripcion_grupo, 'Sin asignar') as centro_vida,
            COALESCE(g.descripcion_grupo, 'Sin programas') as programas,
            COALESCE(pol.descripcion_politica, 'Sin asignar') as descripcion_politica,
            
            -- Estado actual basado en el último movimiento (igual que en Excel)
            (SELECT cc.descripcion_condicion 
             FROM movimiento_persona mp 
             JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS ultimo_estado_movimiento,
             
            -- Fecha del último movimiento
            (SELECT mp.fecha_movimiento 
             FROM movimiento_persona mp 
             WHERE mp.cedula_persona = p.cedula_persona 
             ORDER BY mp.fecha_movimiento DESC, mp.id_movimiento_persona DESC
             LIMIT 1) AS fecha_ultimo_movimiento,
            
            COUNT(DISTINCT CASE WHEN YEAR(mp.fecha_movimiento) = ? THEN mp.id_movimiento_persona END) as movimientos_en_year,
            COUNT(DISTINCT CASE WHEN YEAR(mp.fecha_movimiento) = ? AND cc.descripcion_condicion LIKE '%TRASLAD%' THEN mp.id_movimiento_persona END) as traslados_en_year,
            DATE_FORMAT(p.fecha_alta_persona, '%d/%m/%Y') as fecha_registro
        FROM personas p
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
        LEFT JOIN movimiento_persona mp ON p.cedula_persona = mp.cedula_persona
        LEFT JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
        WHERE p.estado_persona = 1 
        GROUP BY p.cedula_persona, p.nombres_persona, p.apellidos_persona, p.genero_persona, 
                p.fecha_nacimiento, p.telefono_persona, p.referencia_persona, 
                g.descripcion_grupo, pol.descripcion_politica, p.estado_persona, 
                p.fecha_alta_persona
        ORDER BY p.apellidos_persona ASC, p.nombres_persona ASC
    ";
    
    $stmt = $mysqli->prepare($dataQuery);
    $stmt->bind_param("ii", $year, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $personData = [];
    while ($row = $result->fetch_assoc()) {
        // Determinar el estado actual basado en el último movimiento (igual que en Excel)
        $estado_actual = 'ACTIVO'; // Estado por defecto
        
        if ($row['ultimo_estado_movimiento']) {
            $ultimo_estado = strtoupper($row['ultimo_estado_movimiento']);
            
            // Mapear estados según condiciones - igual que en getReportData.php
            if (strpos($ultimo_estado, 'EVADIDO') !== false || 
                strpos($ultimo_estado, 'EVASION') !== false ||
                strpos($ultimo_estado, 'FUGA') !== false) {
                $estado_actual = 'EVADIDO';
            } elseif (strpos($ultimo_estado, 'FALLECIDO') !== false || 
                      strpos($ultimo_estado, 'MUERTE') !== false ||
                      strpos($ultimo_estado, 'DEFUNCION') !== false) {
                $estado_actual = 'FALLECIDO';
            } elseif (strpos($ultimo_estado, 'RETIRADO') !== false || 
                      strpos($ultimo_estado, 'RETIRO') !== false ||
                      strpos($ultimo_estado, 'SALIDA') !== false) {
                $estado_actual = 'RETIRADO VOLUNTARIO';
            } elseif (strpos($ultimo_estado, 'TRASLADADO') !== false || 
                      strpos($ultimo_estado, 'TRASLADO') !== false) {
                $estado_actual = 'TRASLADADO';
            } elseif (strpos($ultimo_estado, 'SUSPENDIDO') !== false || 
                      strpos($ultimo_estado, 'SUSPENSION') !== false) {
                $estado_actual = 'SUSPENDIDO';
            } elseif (strpos($ultimo_estado, 'ACTIVO') !== false || 
                      strpos($ultimo_estado, 'INGRESO') !== false ||
                      strpos($ultimo_estado, 'ACTIVACION') !== false) {
                $estado_actual = 'ACTIVO';
            } else {
                // Si no coincide con ningún patrón conocido, usar el estado original
                $estado_actual = $ultimo_estado;
            }
        }
        
        // Determinar "Activo Hasta" basado en el estado
        $activo_hasta = 'Actualmente activo';
        if ($estado_actual == 'FALLECIDO' || $estado_actual == 'EVADIDO') {
            // Para fallecidos y evadidos, mostrar la fecha del último movimiento
            if ($row['fecha_ultimo_movimiento']) {
                $activo_hasta = date('d/m/Y', strtotime($row['fecha_ultimo_movimiento']));
            } else {
                $activo_hasta = 'Fecha no disponible';
            }
        } elseif ($estado_actual == 'RETIRADO VOLUNTARIO' || $estado_actual == 'TRASLADADO') {
            // Para retirados y trasladados, también mostrar fecha del último movimiento
            if ($row['fecha_ultimo_movimiento']) {
                $activo_hasta = date('d/m/Y', strtotime($row['fecha_ultimo_movimiento']));
            } else {
                $activo_hasta = 'Fecha no disponible';
            }
        }
        
        $personData[] = [
            $row['cedula_persona'],
            $row['nombres_persona'],
            $row['apellidos_persona'],
            $row['genero_persona'],
            $row['fecha_nacimiento'] ?: 'No registrada',
            $row['edad_actual'] ? $row['edad_actual'] . ' años' : 'N/A',
            $row['telefono_persona'] ?: '',
            $row['referencia_persona'] ?: '',
            $row['centro_vida'],
            $row['programas'] ?: 'Sin programas',
            $estado_actual, // Estado calculado basado en último movimiento
            $row['descripcion_politica'],
            $row['movimientos_en_year'],
            $row['traslados_en_year'],
            $row['fecha_registro'],
            $activo_hasta // Fecha calculada basada en el estado
        ];
    }
    
    // Obtener estadísticas
    $totalRegistros = count($personData);
    $personasConMovimientos = 0;
    $personasActivas = 0;
    $totalMovimientos = 0;
    
    // Estadísticas adicionales por estado
    $estadosCounts = [
        'ACTIVO' => 0,
        'EVADIDO' => 0,
        'FALLECIDO' => 0,
        'RETIRADO VOLUNTARIO' => 0,
        'TRASLADADO' => 0,
        'SUSPENDIDO' => 0
    ];
    
    foreach ($personData as $persona) {
        if ($persona[12] > 0) $personasConMovimientos++; // movimientos_en_year
        if ($persona[10] === 'ACTIVO') $personasActivas++; // estado_actual
        $totalMovimientos += $persona[12]; // sumar movimientos
        
        // Contar por estado
        $estado = $persona[10];
        if (isset($estadosCounts[$estado])) {
            $estadosCounts[$estado]++;
        }
    }
    
    $stats = [
        'total_registros' => $totalRegistros,
        'personas_con_movimientos' => $personasConMovimientos,
        'personas_activas' => $personasActivas,
        'total_movimientos' => $totalMovimientos,
        'personas_por_estado' => $estadosCounts
    ];
    
    // Crear PDF
    $pdf = new InformePDF($year, $stats);
    $pdf->AliasNbPages();
    
    // Generar contenido solo si hay datos
    if (!empty($personData)) {
        // Portada
        $pdf->Portada();
        
        // Página de estadísticas
        $pdf->EstadisticasPage();
        
        // Datos detallados
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(41, 128, 185);
        $pdf->Cell(0, 12, 'LISTADO DETALLADO - ' . count($personData) . ' REGISTROS', 0, 1, 'C');
        $pdf->Ln(5);
        
        $pdf->CreateDetailedTable($personData);
    } else {
        // Solo portada si no hay datos
        $pdf->Portada();
    }
    
    // Configurar para descarga
    $filename = 'SDSYP_Informe_Completo_' . $year . '_' . date('Ymd_His') . '.pdf';
    
    header('Content-Type: application/pdf; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    $pdf->Output('D', $filename);

} catch (Exception $e) {
    // PDF de error más informativo
    $pdf = new InformePDF($year);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(231, 76, 60);
    $pdf->Cell(0, 20, 'ERROR AL GENERAR INFORME', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->MultiCell(0, 8, 'Error técnico: ' . $e->getMessage(), 0, 'C');
    $pdf->Ln(10);
    $pdf->Cell(0, 8, 'Año solicitado: ' . $year, 0, 1, 'C');
    $pdf->Cell(0, 8, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    
    $pdf->Output('D', 'Error_Informe_' . $year . '.pdf');
}

$mysqli->close();
?>
