<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

include("../../conexion.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "UPDATE usuarios SET estado_usu = 0 WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        header("Location: showusers.php");
        exit();
    } else {
        echo "Error al inactivar el usuario: " . $stmt->error;
    }
} else {
    echo "Usuario no proporcionado.";
}
?>
