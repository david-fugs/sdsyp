<?php
    
    session_start();
    
    if(!isset($_SESSION['id']))
    {
        header("Location: index.php");
        exit();  // Asegúrate de salir del script después de redirigir
    }
    
    $id     	= $_SESSION['id'];
    $usuario    	= $_SESSION['usuario'];
    $nombre     	= $_SESSION['nombre'];
    $tipo_usuario   = $_SESSION['tipo_usuario'];
    
    header("Content-Type: text/html;charset=utf-8");
	include("conexion.php");

	if($_SERVER['REQUEST_METHOD']== 'POST')
	{
		if($_POST['nuevopassword'] === $_POST['confirmapassword'])
		{
			$password = mysqli_real_escape_string($mysqli,$_POST['nuevopassword']);
			$password_encrypt = sha1($password);

			$sql = "UPDATE usuarios SET password='$password_encrypt' WHERE id='$id'";
			$result = $mysqli->query($sql);

			if($result > 0)
			{
				echo "<script>
						alert('LA CONTRASEÑA FUE ACTUALIZADA DE FORMA CORRECTA');
						window.location = 'access.php';
					</script>";
				exit();
			}
		}else{
			echo "<script>
						alert('LAS CONTRASEÑAS NO COINCIDEN, POR FAVOR VERIFIQUE');
						window.location = 'access.php';
					</script>";
			exit();
	
		}
	}
?>