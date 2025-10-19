<?php
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_registro = $_POST['id_registro'];
    $id_meta = $_POST['id_meta'];
    $id_actividad = $_POST['id_actividad'];
    $id_accion = $_POST['id_accion'];
    $politica_publica = $_POST['politica_publica'];
    $id_centro_vida = $_POST['id_centro_vida'];
    $fecha_atencion = $_POST['fecha_atencion'];
    $nombre_lider = $_POST['nombre_lider'];
    $telefono_contacto = $_POST['telefono_contacto'];
    $id_comuna = $_POST['id_comuna'];
    $medio_verificacion = $_POST['medio_verificacion'];
    $cantidad_masculino = $_POST['cantidad_masculino'];
    $cantidad_femenino = $_POST['cantidad_femenino'];
    $tipo_actividad = $_POST['tipo_actividad'];
    $observacion_actividad = $_POST['observacion_actividad'];
    $id_entregas = $_POST['id_entregas'];
    $funcionario_responsable = $_POST['funcionario_responsable'];
    $otro_lugar = isset($_POST['otro_lugar']) ? $_POST['otro_lugar'] : '';

    $query = "UPDATE registro_actividades SET 
        id_meta = '$id_meta',
        id_actividad = '$id_actividad',
        id_accion = '$id_accion',
        politica_publica = '$politica_publica',
        id_centro_vida = '$id_centro_vida',
        fecha_atencion = '$fecha_atencion',
        nombre_lider = '$nombre_lider',
        telefono_contacto = '$telefono_contacto',
        id_comuna = '$id_comuna',
        medio_verificacion = '$medio_verificacion',
        cantidad_masculino = '$cantidad_masculino',
        cantidad_femenino = '$cantidad_femenino',
        tipo_actividad = '$tipo_actividad',
        observacion_actividad = '$observacion_actividad',
        id_entregas = '$id_entregas',
        funcionario_responsable = '$funcionario_responsable',
        otro_lugar = '$otro_lugar'
        WHERE id_registro = '$id_registro'";

    if (mysqli_query($mysqli, $query)) {
        echo "<script>alert('Registro actualizado correctamente'); window.location = 'form.php';</script>";
    } else {
        echo "<script>alert('Error actualizando el registro: " . mysqli_error($mysqli) . "'); window.location = 'form.php';</script>";
    }
} else {
    echo "<script>alert('Acceso inválido'); window.location = 'form.php';</script>";
}
?>
