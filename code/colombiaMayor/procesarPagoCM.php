<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

$mes_pago = $mysqli->real_escape_string($_POST['mes_pago']);
$anio_pago = $mysqli->real_escape_string($_POST['anio_pago']);
$monto = floatval($_POST['monto']);
$observaciones = $mysqli->real_escape_string($_POST['observaciones'] ?? '');
$exclusiones_json = $_POST['exclusiones'] ?? '[]';
$exclusiones = json_decode($exclusiones_json, true);
$preview = $_POST['preview'] === 'true';

$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

// Extraer cédulas excluidas
$cedulas_excluidas = array_map(function($e) { return $e['cedula']; }, $exclusiones);
$where_exclusion = '';
if(count($cedulas_excluidas) > 0) {
    $cedulas_str = "'" . implode("','", array_map(function($c) use ($mysqli) { 
        return $mysqli->real_escape_string($c); 
    }, $cedulas_excluidas)) . "'";
    $where_exclusion = " AND p.cedula NOT IN ($cedulas_str)";
}

// Filtro por usuario si es contratista
$where_usuario = '';
if($tipo_usuario == 9) {
    $where_usuario = " AND p.usuario_registro = '$usuario_id'";
}

// Consultar personas elegibles
$sql = "SELECT p.* 
        FROM personas_colombia_mayor p
        WHERE p.estado_cm = 'ACTIVO'
        $where_usuario
        $where_exclusion
        ORDER BY p.apellido, p.nombre";

$result = $mysqli->query($sql);
$total_personas = $result->num_rows;
$total_pago = $total_personas * $monto;

// Si es preview, solo devolver estadísticas
if($preview) {
    echo json_encode([
        'success' => true,
        'total_personas' => $total_personas,
        'total_excluidos' => count($exclusiones),
        'total_pago' => $total_pago
    ]);
    exit();
}

// Verificar que no exista ya un pago para este período
$sql_check = "SELECT id FROM pagos_colombia_mayor 
              WHERE mes_pago = '$mes_pago' 
              AND anio_pago = '$anio_pago'
              AND usuario_registro = '$usuario_id'";
$result_check = $mysqli->query($sql_check);

if($result_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Ya existe un pago registrado para este período']);
    exit();
}

// Iniciar transacción
$mysqli->begin_transaction();

try {
    // Insertar el pago principal
    $sql_pago = "INSERT INTO pagos_colombia_mayor (
        mes_pago, anio_pago, monto_por_persona, total_personas, 
        total_pago, observaciones, usuario_registro
    ) VALUES (
        '$mes_pago', '$anio_pago', '$monto', '$total_personas',
        '$total_pago', '$observaciones', '$usuario_id'
    )";
    
    if(!$mysqli->query($sql_pago)) {
        throw new Exception('Error al crear el pago: ' . $mysqli->error);
    }
    
    $id_pago = $mysqli->insert_id;
    
    // Insertar detalles de pago para cada persona
    $result->data_seek(0);
    while($persona = $result->fetch_assoc()) {
        $id_persona = $persona['id'];
        
        $sql_detalle = "INSERT INTO detalle_pagos_cm (
            id_pago, id_persona, monto, estado_cobro
        ) VALUES (
            '$id_pago', '$id_persona', '$monto', 'PENDIENTE'
        )";
        
        if(!$mysqli->query($sql_detalle)) {
            throw new Exception('Error al insertar detalle: ' . $mysqli->error);
        }
    }
    
    // Insertar exclusiones
    foreach($exclusiones as $exc) {
        $cedula_exc = $mysqli->real_escape_string($exc['cedula']);
        $motivo_exc = $mysqli->real_escape_string($exc['motivo']);
        
        // Buscar ID de persona
        $sql_persona = "SELECT id FROM personas_colombia_mayor WHERE cedula = '$cedula_exc'";
        $result_persona = $mysqli->query($sql_persona);
        if($persona_exc = $result_persona->fetch_assoc()) {
            $sql_exclusion = "INSERT INTO exclusiones_pago_cm (
                id_pago, id_persona, motivo
            ) VALUES (
                '$id_pago', '{$persona_exc['id']}', '$motivo_exc'
            )";
            $mysqli->query($sql_exclusion);
        }
    }
    
    // Confirmar transacción
    $mysqli->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pago procesado exitosamente',
        'total_personas' => $total_personas,
        'id_pago' => $id_pago
    ]);
    
} catch(Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$mysqli->close();
?>
