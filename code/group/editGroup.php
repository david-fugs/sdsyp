<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario

    $descripcion_grupo = $_POST['descripcion_grupo'];
    $limite_personas = $_POST['limite_personas'];
    $id_grupo = $_POST['id_grupo'];
    // Actualizar la grupo
    $sql_update_grupo = "UPDATE grupos SET descripcion_grupo='$descripcion_grupo', limite_personas='$limite_personas' WHERE id_grupo='$id_grupo'";
    //ejecutar consulta
    if ($mysqli->query($sql_update_grupo)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Grupo actualizado correctamente',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'seeGroup.php';
                });
            });
          </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar grupo: " . addslashes($mysqli->error) . "',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'seeGroup.php';
                });
            });
          </script>";
    }

    

}