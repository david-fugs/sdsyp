<?php
    session_start();
    
    if(!isset($_SESSION['id'])){
        header("Location: ../../index.php");
        exit();
    }
    
    $usuario      = $_SESSION['usuario'];
    $nombre       = $_SESSION['nombre'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
    $cod_dane_ie  = $_SESSION['cod_dane_ie'];

    date_default_timezone_set("America/Bogota");
    header("Content-Type: text/html;charset=utf-8");
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>SDSYP</title>
        <link rel="stylesheet" href="../../css/bootstrap.min.css">
        <script type="text/javascript" src="../../js/jquery.min.js"></script>
        <script type="text/javascript" src="../../js/popper.min.js"></script>
        <script type="text/javascript" src="../../js/bootstrap.min.js"></script>
        <link href="../../fontawesome/css/all.css" rel="stylesheet"> <!--load all styles -->
        <style>
            .responsive {
                max-width: 100%;
                height: auto;
            }
        </style>
        <script>
            function ordenarSelect(id_componente)
            {
                var selectToSort = jQuery('#' + id_componente);
                var optionActual = selectToSort.val();
                selectToSort.html(selectToSort.children('option').sort(function (a, b) {
                    return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
                })).val(optionActual);
            }
            $(document).ready(function () {
                ordenarSelect('selectIE');
            });

            function evitarEspacios(e) {
                if (e.which === 32) {
                    return false;
                }
            }

            function togglePasswordVisibility() {
                var passwordField = document.getElementById("password");
                var passwordToggle = document.getElementById("password-toggle");
                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    passwordToggle.classList.remove("fa-eye");
                    passwordToggle.classList.add("fa-eye-slash");
                } else {
                    passwordField.type = "password";
                    passwordToggle.classList.remove("fa-eye-slash");
                    passwordToggle.classList.add("fa-eye");
                }
            }
        </script>
    </head>
    <body>
  
        <center>
            <img src="../../img/logo_educacion.png" class="responsive">
        </center>
        <br />

<?php
    require('../../conexion.php');
    $mysqli->set_charset('utf8');
    if (isset($_REQUEST['usuario'])){
        $usuario = stripslashes($_REQUEST['usuario']); // removes backslashes
        $usuario = mysqli_real_escape_string($mysqli, $usuario); //escapes special characters in a string
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($mysqli, $password);
        $nombre = stripslashes($_REQUEST['nombre']);
        $cedula = stripslashes($_REQUEST['cedula']);
        $tipo_usuario = 7;
        
        $query = "INSERT INTO `usuarios` (usuario, password, tipo_usuario, nombre,cedula_usuario) VALUES ('$usuario', '".sha1($password)."', '$tipo_usuario', '$nombre','$cedula')";
        $result = mysqli_query($mysqli, $query);
        if ($result) {
            echo "<center><p style='border-radius: 20px;box-shadow: 10px 10px 5px #c68615; font-size: 23px; font-weight: bold;' >REGISTRO CREADO SATISFACTORIAMENTE<br><br></p></center>
                <div class='form' align='center'><h3>Regresar para iniciar la sesión... <br/><br/><center><a href='showusers.php'>Regresar</a></center></h3></div>";
        }
    } else {
?>
        
        <div class="container">
            <h1><b><i class="fa-solid fa-user-pen"></i> REGISTRO DE UN NUEVO USUARIO</b></h1>
            <p><i><b><font size=3 color=#c68615>*Datos obligatorios</i></b></font></p>
            <form action='' method="POST">
                
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <label for="nombre">* NOMBRES COMPLETOS (persona que se registra):</label>
                            <input type='text' name='nombre' class='form-control' id="nombre" required autofocus style="text-transform:uppercase;" />
                        </div>
                        <div class="col-12 col-sm-3">
                            <label for="cedula">* CEDULA :</label>
                            <input type='number' name='cedula' class='form-control' id="cedula" required autofocus style="text-transform:uppercase;" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-12 col-sm-3">
                            <label for="usuario">* USUARIO:</label>
                            <input type='text' name='usuario' id="usuario" class='form-control' required onkeypress="return evitarEspacios(event)" />
                            <label for="usuario">(minúsculas, sin espacios, ni caracteres especiales)</label>
                        </div>
                        <div class="col-12 col-sm-3">
                            <label for="password">* PASSWORD:</label>
                            <div class="input-group">
                                <input type='password' name='password' id="password" class='form-control' required style="text-transform:uppercase;" />
                                <div class="input-group-append">
                                    <span class="input-group-text" onclick="togglePasswordVisibility();">
                                        <i class="fas fa-eye" id="password-toggle"></i>
                                    </span>
                                </div>
                            </div>
                            <label for="password"> (no tiene restricción)</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm"></span>
                    REGISTRAR USUARIO
                </button>
                <button type="reset" class="btn btn-outline-dark" role='link' onclick="history.back();" type='reset'><img src='../../img/atras.png' width=27 height=27> REGRESAR
                </button>
            </form>
        </div>

        <script src="../student/js/app.js"></script>
        <script src="https://www.jose-aguilar.com/scripts/fontawesome/js/all.min.js" data-auto-replace-svg="nest"></script>

    </body>
</html>

<?php } ?>
