<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    $cedula_persona = $_POST['cedula_persona'];
    $nombres_persona = $_POST['nombres_persona'];
    $apellidos_persona = $_POST['apellidos_persona'];
    $telefono_persona = $_POST['telefono_persona'];
    $referencia_persona = $_POST['referencia_persona'];
    $programa = $_POST['programa'];
    $genero_persona = $_POST['genero_persona'];

    $sql_insert_persona = "INSERT INTO personas (cedula_persona, nombres_persona, apellidos_persona, telefono_persona, referencia_persona, genero_persona)
    VALUES ('$cedula_persona', '$nombres_persona', '$apellidos_persona', '$telefono_persona', '$referencia_persona', '$genero_persona')";

    // Ejecutar consulta
    if ($mysqli->query($sql_insert_persona)) {
        //insert en persona_programa
        foreach ($programa as $id_programa) {
            $sql_insert_persona_programa = "INSERT INTO persona_programa (cedula_persona, id_programa) VALUES ('$cedula_persona', '$id_programa')";

            if ($mysqli->query($sql_insert_persona_programa)) {
                echo "✅ Programa ID $id_programa insertado correctamente.<br>";
            } else {
                echo "❌ Error al insertar programa ID $id_programa: " . $mysqli->error . "<br>";
            }
        }
        echo "<script>
            alert('Insert successful');
            window.location.href = 'seePerson.php';
          </script>";
    } else {
        echo "<script>
            alert('Error  " . $mysqli->error . "');
            window.location.href = 'seePerson.php';
          </script>";
    }
} else {
    echo "<script>
            alert('Method not valid');
            window.location.href = 'seePerson.php';
          </script>";
}
