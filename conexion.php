<?php
	
	$mysqli = new mysqli("localhost", "root", "", "profesional7");
	// Verificar conexión
// Verificar conexión
if ($mysqli->connect_error) {
    die("Error en la conexión: " . $mysqli->connect_error);
}
?>
