<?php
session_start();

$nombre = $_SESSION['nombre'];
$tipo_usu = $_SESSION['tipo_usu'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SUMALE | SOFT</title>
    <script src="js/64d58efce2.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Orbitron" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../css/estilos.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link href="../../fontawesome/css/all.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/fed2435e21.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body>
    <center>
        <img src='../../img/logo.png' width=200 height=227>

    </center>

    <h1 style="color: #000000; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 40px; text-align: center;"><b><i class="fa-sharp-duotone fa-solid fa-file-excel"></i> SUBIR EXCEL ESTUDIANTES</b>
    </h1>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h4>Subir archivo Excel</h4>
                    </div>
                    <div class="card-body">
                        <form action="uploadStudents.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="fileInput" class="form-label">Selecciona un archivo Excel</label>
                                <input type="file" class="form-control" id="fileInput" name="excelFile" accept=".xlsx, .xls" required>
                                <small class="form-text text-muted">Acepta archivos con extensiones .xlsx o .xls.</small>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Subir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>