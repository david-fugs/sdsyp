<?php
session_start();
include("../../conexion.php");

// Validar método
if($_SERVER['REQUEST_METHOD']!=='POST'){
    echo "<script>alert('Método no permitido');window.location='formMasivoCentroVida.php';</script>";exit;
}

$id_meta = intval($_POST['id_meta'] ?? 0);
$id_actividad = intval($_POST['id_actividad'] ?? 0);
$id_accion = intval($_POST['id_accion'] ?? 0);
$id_actividad_centro_vida = intval($_POST['id_actividad_centro_vida'] ?? 0);
$politica_publica = $mysqli->real_escape_string($_POST['politica_publica'] ?? '');
$id_centro_vida = intval($_POST['id_centro_vida'] ?? 0);
$fecha_atencion = $mysqli->real_escape_string($_POST['fecha_atencion'] ?? '');
$nombre_lider = $mysqli->real_escape_string($_POST['nombre_lider'] ?? '');
$telefono_contacto = $mysqli->real_escape_string($_POST['telefono_contacto'] ?? '');
$id_comuna = intval($_POST['id_comuna'] ?? 0);
$medio_verificacion = $mysqli->real_escape_string($_POST['medio_verificacion'] ?? '');
$cantidad_masculino = intval($_POST['cantidad_masculino'] ?? 0);
$cantidad_femenino = intval($_POST['cantidad_femenino'] ?? 0);
$observacion_actividad = $mysqli->real_escape_string($_POST['observacion_actividad'] ?? '');
$id_usuario = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
$funcionario_responsable = intval($_POST['funcionario_responsable'] ?? 0);
$tipo_registro = $mysqli->real_escape_string($_POST['tipo_registro'] ?? '');

// Insertar (sin tipo_actividad: siempre 'Masiva')
$tipo_actividad = 'Masiva';

// Construir consulta SQL plana (escapando valores de texto y casteando enteros)
$cols = "id_meta,id_actividad,id_accion,politica_publica,id_centro_vida,fecha_atencion,nombre_lider,telefono_contacto,id_comuna,medio_verificacion,cantidad_masculino,cantidad_femenino,tipo_actividad,observacion_actividad,id_usuario,funcionario_responsable,id_actividad_centro_vida,tipo_registro";

// Escapar y formatear valores
$vals = [
    intval($id_meta),
    intval($id_actividad),
    intval($id_accion),
    "'" . $mysqli->real_escape_string($politica_publica) . "'",
    intval($id_centro_vida),
    "'" . $mysqli->real_escape_string($fecha_atencion) . "'",
    "'" . $mysqli->real_escape_string($nombre_lider) . "'",
    "'" . $mysqli->real_escape_string($telefono_contacto) . "'",
    intval($id_comuna),
    "'" . $mysqli->real_escape_string($medio_verificacion) . "'",
    intval($cantidad_masculino),
    intval($cantidad_femenino),
    "'" . $mysqli->real_escape_string($tipo_actividad) . "'",
    "'" . $mysqli->real_escape_string($observacion_actividad) . "'",
    intval($id_usuario),
    intval($funcionario_responsable),
    intval($id_actividad_centro_vida),
    "'" . $mysqli->real_escape_string($tipo_registro) . "'"
];

$sql = "INSERT INTO masiva_centro_vida (" . $cols . ") VALUES (" . implode(',', $vals) . ")";

if ($mysqli->query($sql)) {
    echo "<script>alert('Registro guardado correctamente');window.location='formMasivoCentroVida.php';</script>";
} else {
    $error = $mysqli->error;
    echo "<script>alert('Error al guardar: " . addslashes($error) . "');window.location='formMasivoCentroVida.php';</script>";
}

$mysqli->close();
