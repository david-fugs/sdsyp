<?php
include("../../conexion.php");

// Recoger datos del formulario
$id_meta = isset($_POST['id_meta']) ? intval($_POST['id_meta']) : 0;
$id_actividad = isset($_POST['id_actividad']) ? intval($_POST['id_actividad']) : 0;
$id_accion = isset($_POST['id_accion']) ? intval($_POST['id_accion']) : 0;
$politica_publica = isset($_POST['politica_publica']) ? mysqli_real_escape_string($mysqli, $_POST['politica_publica']) : '';
$id_centro_vida = isset($_POST['id_centro_vida']) ? intval($_POST['id_centro_vida']) : 0;
$fecha_atencion = isset($_POST['fecha_atencion']) ? mysqli_real_escape_string($mysqli, $_POST['fecha_atencion']) : '';
$nombre_lider = isset($_POST['nombre_lider']) ? mysqli_real_escape_string($mysqli, $_POST['nombre_lider']) : '';
$telefono_contacto = isset($_POST['telefono_contacto']) ? mysqli_real_escape_string($mysqli, $_POST['telefono_contacto']) : '';
$id_comuna = isset($_POST['id_comuna']) ? intval($_POST['id_comuna']) : 0;
$medio_verificacion = isset($_POST['medio_verificacion']) ? mysqli_real_escape_string($mysqli, $_POST['medio_verificacion']) : '';
$cantidad_masculino = isset($_POST['cantidad_masculino']) ? intval($_POST['cantidad_masculino']) : 0;
$cantidad_femenino = isset($_POST['cantidad_femenino']) ? intval($_POST['cantidad_femenino']) : 0;
$tipo_actividad = isset($_POST['tipo_actividad']) ? mysqli_real_escape_string($mysqli, $_POST['tipo_actividad']) : '';
$observacion_actividad = isset($_POST['observacion_actividad']) ? mysqli_real_escape_string($mysqli, $_POST['observacion_actividad']) : '';

// Consulta plana para insertar el registro
$query = "INSERT INTO registro_actividades (
    id_meta,
    id_actividad,
    id_accion,
    politica_publica,
    id_centro_vida,
    fecha_atencion,
    nombre_lider,
    telefono_contacto,
    id_comuna,
    medio_verificacion,
    cantidad_masculino,
    cantidad_femenino,
    tipo_actividad,
    observacion_actividad
) VALUES (
    $id_meta,
    $id_actividad,
    $id_accion,
    '$politica_publica',
    $id_centro_vida,
    '$fecha_atencion',
    '$nombre_lider',
    '$telefono_contacto',
    $id_comuna,
    '$medio_verificacion',
    $cantidad_masculino,
    $cantidad_femenino,
    '$tipo_actividad',
    '$observacion_actividad'
)";

$result = mysqli_query($mysqli, $query);

if ($result) {
    echo "<script>alert('Registro guardado correctamente'); window.location = 'form.php';</script>";
} else {
    echo "<script>alert('Error al guardar el registro: " . mysqli_error($mysqli) . "'); window.location = 'form.php';</script>";
}

mysqli_close($mysqli);
?>
