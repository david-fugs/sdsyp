<?php
    
    session_start();
    
    if(!isset($_SESSION['id'])){
        header("Location: ../../index.php");
    }
    
    header("Content-Type: text/html;charset=utf-8");
    $nombre         = $_SESSION['nombre'];
    $tipo_usuario   = $_SESSION['tipo_usuario'];
    $cod_dane_ie    = $_SESSION['cod_dane_ie'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FICHA</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <style>
        .responsive {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

   	<?php
        include("../../conexion.php");
        date_default_timezone_set("America/Bogota");
        $mysqli->set_charset('utf8');
	    if(isset($_POST['btn-update']))
        {
            $tip_doc_est            =   $_POST['tip_doc_est'];
            $num_doc_est            =   $_POST['num_doc_est'];
            $fecha_dig_est          =   date('Y-m-d h:i:s');
            $mun_dig_est            =   $_POST['mun_dig_est'];
            $nom_ape_est            =   mb_strtoupper($_POST['nom_ape_est']);
            $fec_nac_est            =   $_POST['fec_nac_est'];
            $ciu_nac_est            =   mb_strtoupper($_POST['ciu_nac_est']);
            $dir_est                =   mb_strtoupper($_POST['dir_est']);
            $mun_res_est            =   mb_strtoupper($_POST['mun_res_est']);
            $estrato_est            =   $_POST['estrato_est'];
            $zona_est               =   $_POST['zona_est'];
            $tel1_est               =   $_POST['tel1_est'];
            $tel2_est               =   $_POST['tel2_est'];
            $email_est              =   mb_strtolower($_POST['email_est']);
            $est_civ_est            =   $_POST['est_civ_est'];
            $gen_est                =   $_POST['gen_est'];
            $eps_est                =   mb_strtoupper($_POST['eps_est']);
            $med_trans_est          =   mb_strtoupper($_POST['med_trans_est']);
            $sisben_est             =   $_POST['sisben_est'];
            $cod_dane_ieSede        =   $_POST['cod_dane_ieSede'];    
            $obs_est                =   mb_strtoupper($_POST['obs_est']);
            $edad_madre_est         =   $_POST['edad_madre_est'];
            $gestacion_semanas_est  =   $_POST['gestacion_semanas_est'];
            $embarazo_mama_est      =   $_POST['embarazo_mama_est'];
            $lactancia_mama_est     =   $_POST['lactancia_mama_est'];
            $gateo_est              =   $_POST['gateo_est'];
            $camino_est             =   $_POST['camino_est'];
            $poblacion_vulnerable_est =   $_POST['poblacion_vulnerable_est'];
            $discapacidad_est       =   $_POST['discapacidad_est'];
            $capacidad_est          =   $_POST['capacidad_est'];
            $etnia_est              =   $_POST['etnia_est'];
            $victima_est            =   $_POST['victima_est']; 
            $estado_est             =   1;
            $nombre_encuestador_est =   mb_strtoupper($_POST['nombre_encuestador_est']);
            $rol_encuestador_est    =   mb_strtoupper($_POST['rol_encuestador_est']);
            $fecha_edit_est         =   date('Y-m-d h:i:s');
            $id_usu                 =   $_SESSION['id'];
           
            $update = "UPDATE estudiantes SET tip_doc_est='".$tip_doc_est."', fecha_dig_est='".$fecha_dig_est."', mun_dig_est='".$mun_dig_est."', nom_ape_est='".$nom_ape_est."', fec_nac_est='".$fec_nac_est."', ciu_nac_est='".$ciu_nac_est."', dir_est='".$dir_est."', mun_res_est='".$mun_res_est."', estrato_est='".$estrato_est."', zona_est='".$zona_est."', tel1_est='".$tel1_est."', tel2_est='".$tel2_est."', email_est='".$email_est."', est_civ_est='".$est_civ_est."', gen_est='".$gen_est."', eps_est='".$eps_est."', med_trans_est='".$med_trans_est."', sisben_est='".$sisben_est."', cod_dane_ieSede='".$cod_dane_ieSede."', obs_est='".$obs_est."', edad_madre_est='".$edad_madre_est."', gestacion_semanas_est='".$gestacion_semanas_est."', embarazo_mama_est='".$embarazo_mama_est."', lactancia_mama_est='".$lactancia_mama_est."', gateo_est='".$gateo_est."', camino_est='".$camino_est."', poblacion_vulnerable_est='".$poblacion_vulnerable_est."', discapacidad_est='".$discapacidad_est."', capacidad_est='".$capacidad_est."', etnia_est='".$etnia_est."', victima_est='".$victima_est."', estado_est='".$estado_est."', nombre_encuestador_est='".$nombre_encuestador_est."', rol_encuestador_est='".$rol_encuestador_est."', fecha_edit_est='".$fecha_edit_est."', id_usu='".$id_usu."' WHERE num_doc_est='".$num_doc_est."'";

            $up = mysqli_query($mysqli, $update);


            echo "
                <!DOCTYPE html>
                    <html lang='es'>
                        <head>
                            <meta charset='utf-8' />
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <meta http-equiv='X-UA-Compatible' content='ie=edge'>
                            <link href='https://fonts.googleapis.com/css?family=Lobster' rel='stylesheet'>
                            <link href='https://fonts.googleapis.com/css?family=Orbitron' rel='stylesheet'>
                            <link rel='stylesheet' href='../../css/bootstrap.min.css'>
                            <link href='../../fontawesome/css/all.css' rel='stylesheet'>
                            <title>FICHA</title>
                            <style>
                                .responsive {
                                    max-width: 100%;
                                    height: auto;
                                }
                            </style>
                        </head>
                        <body>
                            <center>
                               <img src='../../img/logo_educacion.png' width=600 height=121 class='responsive'>
                            <div class='container'>
                                <br />
                                <h3><b><i class='fas fa-users'></i> SE ACTUALIZÓ DE FORMA EXITOSA EL REGISTRO</b></h3><br />
                                <p align='center'><a href='../../access.php'><img src='../../img/atras.png' width=96 height=96></a></p>
                            </div>
                            </center>
                        </body>
                    </html>
        ";
        }
    ?>

</body>
</html>