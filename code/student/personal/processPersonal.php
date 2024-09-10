<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../../index.php");
}

header("Content-Type: text/html;charset=utf-8");
$usuario      = $_SESSION['usuario'];
$nombre       = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$cod_dane_ie  = $_SESSION['cod_dane_ie'];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FICHA</title>
    <link href="../../../css/bootstrap.min.css" rel="stylesheet">
    <style>
        .responsive {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <?php
    include("../../../conexion.php");
    date_default_timezone_set("America/Bogota");
    $mysqli->set_charset('utf8');
    if (isset($_POST)) {
        $fecha_dig_personal                  = $_POST['fecha_dig_personal'];
        $mun_dig_personal                   = $_POST['mun_dig_personal'];
        $nombre_encuestador_personal         = $_POST['nombre_encuestador_personal'];
        $rol_encuestador_personal           = $_POST['rol_encuestador_personal'];
        $num_doc_est                         = $_POST['num_doc_est'];
        $nom_ape_est                        = $_POST['nom_ape_est'];
        $normas_personal                    = $_POST['normas_personal'];
        $acata_personal                      = $_POST['acata_personal'];
        $interactua_personal                = $_POST['interactua_personal'];
        $cuidado_personal                   = $_POST['cuidado_personal'];
        $observacion_personal               = $_POST['observacion_personal'];
        
    }
