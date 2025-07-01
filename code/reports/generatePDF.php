<?php
require('../../fpdf/fpdf.php');
include("../../conexion.php");

// Verificar que se haya proporcionado el año
if (!isset($_GET['year']) || empty($_GET['year'])) {
    die('Año no proporcionado');
}

$year = intval($_GET['year']);

class PDF extends FPDF
{
    private $year;
    
    function __construct($year) {
        parent::__construct();
        $this->year = $year;
    }
    
    // Cabecera de página
    function Header()
    {
        // Logo
        $this->Image('../../img/logo.png', 10, 6, 30);
        
        // Arial bold 15
        $this->SetFont('Arial','B',16);
        
        // Movernos a la derecha
        $this->Cell(80);
        
        // Título
        $this->Cell(80,10,'SISTEMA DE SEGUIMIENTO PARA PERSONAS','C');
        $this->Ln(8);
        $this->Cell(80);
        $this->Cell(80,10,'INFORME ANUAL ' . $this->year,'C');
        
        // Salto de línea
        $this->Ln(20);
    }
    
    // Pie de página
    function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        
        // Número de página
        $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    }
    
    // Función para crear tabla de estadísticas
    function CreateStatsTable($stats)
    {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'ESTADÍSTICAS GENERALES',0,1,'C');
        $this->Ln(5);
        
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(200,220,255);
        
        // Cabeceras
        $this->Cell(90,7,'Indicador',1,0,'C',true);
        $this->Cell(30,7,'Cantidad',1,1,'C',true);
        
        $this->SetFont('Arial','',9);
        $this->SetFillColor(245,245,245);
        
        // Datos
        $fill = false;
        
        $this->Cell(90,6,'Personas nuevas registradas en ' . $this->year,1,0,'L',$fill);
        $this->Cell(30,6,$stats['personas_nuevas'],1,1,'C',$fill);
        $fill = !$fill;
        
        $this->Cell(90,6,'Total personas activas al final del año',1,0,'L',$fill);
        $this->Cell(30,6,$stats['personas_activas'],1,1,'C',$fill);
        $fill = !$fill;
        
        $this->Cell(90,6,'Total movimientos registrados en ' . $this->year,1,0,'L',$fill);
        $this->Cell(30,6,$stats['total_movimientos'],1,1,'C',$fill);
        
        $this->Ln(10);
    }
    
    // Función para crear tabla de personas por estado
    function CreateStatusTable($estados)
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'PERSONAS POR ESTADO',0,1,'C');
        $this->Ln(3);
        
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(200,220,255);
        
        // Cabeceras
        $this->Cell(90,7,'Estado',1,0,'C',true);
        $this->Cell(30,7,'Cantidad',1,1,'C',true);
        
        $this->SetFont('Arial','',9);
        $this->SetFillColor(245,245,245);
        
        $fill = false;
        foreach ($estados as $estado => $cantidad) {
            $this->Cell(90,6,$estado,1,0,'L',$fill);
            $this->Cell(30,6,$cantidad,1,1,'C',$fill);
            $fill = !$fill;
        }
        
        $this->Ln(10);
    }
    
    // Función para crear tabla de grupos
    function CreateGroupsTable($grupos)
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'PERSONAS POR CENTRO DE VIDA',0,1,'C');
        $this->Ln(3);
        
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(200,220,255);
        
        // Cabeceras
        $this->Cell(120,7,'Centro de Vida',1,0,'C',true);
        $this->Cell(30,7,'Cantidad',1,1,'C',true);
        
        $this->SetFont('Arial','',9);
        $this->SetFillColor(245,245,245);
        
        $fill = false;
        foreach ($grupos as $grupo) {
            $this->Cell(120,6,$grupo['descripcion_grupo'],1,0,'L',$fill);
            $this->Cell(30,6,$grupo['cantidad'],1,1,'C',$fill);
            $fill = !$fill;
        }
        
        $this->Ln(10);
    }
}

try {
    // Obtener estadísticas
    $stats_query = file_get_contents("http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/getReportStats.php?year=" . $year);
    $stats_data = json_decode($stats_query, true);
    
    if (!$stats_data || !$stats_data['success']) {
        die('Error al obtener estadísticas');
    }
    
    $stats = $stats_data['stats'];
    
    // Crear PDF
    $pdf = new PDF($year);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    // Crear tablas
    $pdf->CreateStatsTable($stats);
    
    if (!empty($stats['personas_por_estado'])) {
        $pdf->CreateStatusTable($stats['personas_por_estado']);
    }
    
    if (!empty($stats['personas_por_grupo'])) {
        $pdf->CreateGroupsTable($stats['personas_por_grupo']);
    }
    
    // Nueva página para movimientos por tipo
    $pdf->AddPage();
    
    if (!empty($stats['movimientos_por_tipo'])) {
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,'MOVIMIENTOS POR TIPO EN ' . $year,0,1,'C');
        $pdf->Ln(3);
        
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(200,220,255);
        
        // Cabeceras
        $pdf->Cell(120,7,'Tipo de Movimiento',1,0,'C',true);
        $pdf->Cell(30,7,'Cantidad',1,1,'C',true);
        
        $pdf->SetFont('Arial','',9);
        $pdf->SetFillColor(245,245,245);
        
        $fill = false;
        foreach ($stats['movimientos_por_tipo'] as $movimiento) {
            $pdf->Cell(120,6,$movimiento['descripcion_condicion'],1,0,'L',$fill);
            $pdf->Cell(30,6,$movimiento['cantidad'],1,1,'C',$fill);
            $fill = !$fill;
        }
    }
    
    // Fecha de generación
    $pdf->Ln(20);
    $pdf->SetFont('Arial','I',8);
    $pdf->Cell(0,10,'Informe generado el ' . date('d/m/Y H:i:s'),0,1,'C');
    
    // Salida del PDF
    $pdf->Output('D', 'Informe_Anual_' . $year . '.pdf');
    
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
?>
