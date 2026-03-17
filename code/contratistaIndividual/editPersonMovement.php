<?php
session_start();
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_movimiento = isset($_POST['id_registro_individual']) ? intval($_POST['id_registro_individual']) : 0;
    $cedula_persona = isset($_POST['cedula_persona']) ? $mysqli->real_escape_string($_POST['cedula_persona']) : '';
    $id_condicion = isset($_POST['id_condicion']) ? intval($_POST['id_condicion']) : 0;
    $id_meta = isset($_POST['id_meta']) ? intval($_POST['id_meta']) : 0;
    $id_actividad = isset($_POST['id_actividad']) ? intval($_POST['id_actividad']) : 0;
    $id_accion = isset($_POST['id_accion']) ? intval($_POST['id_accion']) : 0;
    $id_politica_publica = isset($_POST['id_politica_publica']) ? intval($_POST['id_politica_publica']) : 0;
    $departamento_procedencia = isset($_POST['departamento_procedencia']) ? $mysqli->real_escape_string($_POST['departamento_procedencia']) : '';
    $id_centro_vida_traslado = isset($_POST['id_centro_vida_traslado']) && $_POST['id_centro_vida_traslado'] !== '' ? intval($_POST['id_centro_vida_traslado']) : 0;
    $fecha_movimiento = isset($_POST['fecha_movimiento']) ? $mysqli->real_escape_string($_POST['fecha_movimiento']) : '';
    $observacion_movimiento = isset($_POST['observacion_movimiento']) ? $mysqli->real_escape_string($_POST['observacion_movimiento']) : '';
    $id_barrio = isset($_POST['id_barrio']) ? intval($_POST['id_barrio']) : 0;
    $id_comuna = isset($_POST['id_comuna']) ? intval($_POST['id_comuna']) : 0;

    if ($id_movimiento <= 0) {
        echo "<script>alert('ID de registro inválido.'); window.location='form.php';</script>";
        exit;
    }

    $query = "UPDATE registro_individual SET
        id_condicion = $id_condicion,
        id_meta = $id_meta,
        id_actividad = $id_actividad,
        id_accion = $id_accion,
        id_politica_publica = $id_politica_publica,
        departamento_procedencia = '$departamento_procedencia',
        id_centro_vida_traslado = $id_centro_vida_traslado,
        fecha_registro = '$fecha_movimiento',
        observacion_registro = '$observacion_movimiento',
        id_barrio = $id_barrio,
        id_comuna = $id_comuna
        WHERE id_registro_individual = $id_movimiento";

    if ($mysqli->query($query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro actualizado',
                    text: 'El registro individual fue actualizado correctamente.',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'form.php';
                });
            });
        </script>";
    } else {
        echo "<script>alert('Error actualizando el registro: " . $mysqli->error . "'); window.location='form.php';</script>";
    }
} else {
    echo "<script>alert('Acceso inválido'); window.location='form.php';</script>";
}
?>
