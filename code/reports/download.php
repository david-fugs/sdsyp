<?php
// Archivo para descargar los archivos Excel generados
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = __DIR__ . '/temp/' . $filename;
    
    // Verificar que el archivo existe y es seguro
    if (file_exists($filepath) && strpos($filename, '..') === false) {
        // Configurar headers para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Leer y enviar el archivo
        readfile($filepath);
        
        // Opcional: eliminar el archivo después de la descarga
        // unlink($filepath);
        
        exit;
    } else {
        header('HTTP/1.0 404 Not Found');
        echo 'Archivo no encontrado';
        exit;
    }
} else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Parámetro de archivo requerido';
    exit;
}
?>
