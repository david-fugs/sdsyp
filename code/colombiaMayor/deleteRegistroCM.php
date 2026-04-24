<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

$id = intval($_POST['id']);
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id = $_SESSION['id'];

// Validar permisos
if($tipo_usuario == 9) {
    $sql_check = "SELECT usuario_registro FROM registros_individuales_cm WHERE id_registro_individual_cm = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $registro = $result_check->fetch_assoc();
        if($registro['usuario_registro'] != $usuario_id) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar este registro']);
            exit();
        }
    }
    $stmt_check->close();
}

// Obtener fotos asociadas para eliminarlas del servidor
$sql_fotos = "SELECT ruta_foto FROM fotos_registros_cm WHERE id_registro_individual = ?";
$stmt_fotos = $mysqli->prepare($sql_fotos);
$stmt_fotos->bind_param("i", $id);
$stmt_fotos->execute();
$result_fotos = $stmt_fotos->get_result();

$fotos_a_eliminar = [];
while ($foto = $result_fotos->fetch_assoc()) {
    $fotos_a_eliminar[] = $foto['ruta_foto'];
}
$stmt_fotos->close();

// Iniciar transacción
$mysqli->begin_transaction();

try {
    // Eliminar fotos de la base de datos
    $sql_delete_fotos = "DELETE FROM fotos_registros_cm WHERE id_registro_individual = ?";
    $stmt_delete_fotos = $mysqli->prepare($sql_delete_fotos);
    $stmt_delete_fotos->bind_param("i", $id);
    $stmt_delete_fotos->execute();
    $stmt_delete_fotos->close();
    
    // Eliminar registro
    $sql_delete = "DELETE FROM registros_individuales_cm WHERE id_registro_individual_cm = ?";
    $stmt_delete = $mysqli->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();
    $stmt_delete->close();
    
    // Confirmar transacción
    $mysqli->commit();
    
    // Eliminar archivos físicos de fotos
    foreach ($fotos_a_eliminar as $foto) {
        $ruta = 'uploads/fotos_registros/' . $foto;
        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Registro eliminado exitosamente']);
    
} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}

$mysqli->close();
?>
