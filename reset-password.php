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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>FICHA | SOFT</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/fed2435e21.js" crossorigin="anonymous"></script>
    <style>
        .responsive {
            max-width: 100%;
            height: auto;
        }
        .input-group-text {
            cursor: pointer;
        }
        .form-control {
            font-size: 0.9rem;
            padding: 0.375rem 0.75rem;
        }
        .btn-outline-warning, .btn-outline-dark {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        .modal-content {
            border-radius: 0.5rem;
        }
        .modal-header {
            background-color: #f8d7da;
            border-bottom: none;
        }
        .modal-title {
            color: #721c24;
        }
        .modal-footer {
            border-top: none;
        }
        .modal-body {
            color: #721c24;
        }
    </style>
</head>
<body>
    <center>
        <img src='img/logo_educacion.png' width=600 height=121 class="responsive">
    </center>

    <br />
    <div class="container">
        <h1 class="text-center text-primary mb-4"><b><i class="fas fa-key"></i> ACTUALIZACIÓN DE LA CONTRASEÑA</b></h1>
        <p class="text-center text-warning"><i><b><font size=3>*Datos obligatorios</i></b></font></p>
        <form id="passwordForm" action='reset-password1.php' method="POST">
            <input type='number' name='id' class='form-control' id="id" value='<?php echo $id; ?>' hidden />
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label for="nuevopassword">* NUEVA CONTRASEÑA:</label>
                        <div class="input-group">
                            <input type='password' name='nuevopassword' class='form-control' id="nuevopassword" required autofocus />
                            <div class="input-group-append">
                                <span class="input-group-text" onclick="togglePassword('nuevopassword')">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label for="confirmapassword">* CONFIRMAR CONTRASEÑA:</label>
                        <div class="input-group">
                            <input type='password' name='confirmapassword' id="confirmapassword" class='form-control' required />
                            <div class="input-group-append">
                                <span class="input-group-text" onclick="togglePassword('confirmapassword')">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-outline-warning">
                    <span class="spinner-border spinner-border-sm"></span>
                    ACTUALIZAR CONTRASEÑA
                </button>
                <button type="reset" class="btn btn-outline-dark" role='link' onclick="history.back();" type='reset'>
                    <img src='img/atras.png' width=27 height=27> REGRESAR
                </button>
            </div>
        </form>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Error</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Las contraseñas no coinciden. Por favor, inténtalo de nuevo.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function togglePassword(id) {
            var input = document.getElementById(id);
            var icon = input.nextElementSibling.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        document.getElementById('passwordForm').addEventListener('submit', function(event) {
            var nuevopassword = document.getElementById('nuevopassword').value;
            var confirmapassword = document.getElementById('confirmapassword').value;
            if (nuevopassword !== confirmapassword) {
                event.preventDefault();
                $('#passwordModal').modal('show');
            }
        });
    </script>
</body>
</html>
