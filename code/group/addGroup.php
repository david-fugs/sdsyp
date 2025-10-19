<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
    $descripcion_grupo = $_POST['descripcion_grupo'];
    $limite_personas = $_POST['limite_personas'];
    $fecha_contratacion = $_POST['fecha_contratacion'];
    
    // Insertar grupo
    $sql_insert_grupo = "INSERT INTO grupos (descripcion_grupo, limite_personas) VALUES ('$descripcion_grupo', '$limite_personas')";
    
    if ($mysqli->query($sql_insert_grupo)) {
        // Obtener el ID del grupo recién insertado
        $id_grupo = $mysqli->insert_id;
        
        // Insertar la fecha de contratación en el historial
        $sql_insert_fecha = "INSERT INTO historial_fechas_contratacion (id_grupo, fecha_contratacion) VALUES ('$id_grupo', '$fecha_contratacion')";
        
        if ($mysqli->query($sql_insert_fecha)) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Grupo agregado correctamente',
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
                        text: 'Grupo creado pero error al registrar fecha: " . addslashes($mysqli->error) . "',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.href = 'seeGroup.php';
                    });
                });
              </script>";
        }
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al agregar grupo: " . addslashes($mysqli->error) . "',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'seeGroup.php';
                });
            });
          </script>";
    }
  
}