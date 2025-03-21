<?php
	
	$mysqli = new mysqli("localhost", "aprendad_fiee", "~CY]&J9u#wxa", "aprendad_fiee");
	// Verificar conexión
// Verificar conexión
if ($mysqli->connect_error) {
    die("Error en la conexión: " . $mysqli->connect_error);
}
?>
