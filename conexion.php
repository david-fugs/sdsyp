<?php
	
	$mysqli = new mysqli("localhost", "aprendad_sdsyp", "uH]g(;WPYyC@d", "aprendad_sdsyp");
	// Verificar conexión
// Verificar conexión
if ($mysqli->connect_error) {
    die("Error en la conexión: " . $mysqli->connect_error);
}
?>
