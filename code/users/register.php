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
        <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
        <link rel="stylesheet" href="../../css/estilos2024.css">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link href="../../fontawesome/css/all.css" rel="stylesheet">
        
        <!-- Estilos personalizados para aumentar tamaño de fuente -->
        <style>
            /* Aumentar tamaño de fuente general */
            body {
                font-size: 16px !important;
                background-color: #f8fafc;
            }
            
            .responsive {
                max-width: 100%;
                height: auto;
            }

            /* Formulario moderno */
            .modern-form-container {
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                padding: 2rem;
                margin: 2rem auto;
                max-width: 800px;
            }

            .form-title {
                color: #412fd1;
                font-size: 32px !important;
                font-weight: bold;
                text-align: center;
                margin-bottom: 1.5rem;
                text-shadow: #FFFFFF 0.1em 0.1em 0.2em;
            }

            .form-subtitle {
                font-size: 16px !important;
                color: #c68615;
                font-weight: bold;
                text-align: center;
                margin-bottom: 2rem;
            }

            /* Labels y inputs más grandes */
            .form-label {
                font-size: 15px !important;
                font-weight: 600 !important;
                color: #374151;
                margin-bottom: 0.5rem;
            }

            .form-control, .form-select {
                font-size: 16px !important;
                padding: 12px 16px !important;
                border-radius: 8px;
                border: 2px solid #e5e7eb;
                transition: all 0.2s ease;
            }

            .form-control:focus, .form-select:focus {
                border-color: #412fd1;
                box-shadow: 0 0 0 3px rgba(65, 47, 209, 0.1);
            }

            /* Botones modernos */
            .btn-modern {
                font-size: 16px !important;
                padding: 12px 24px !important;
                border-radius: 8px;
                font-weight: 600;
                transition: all 0.2s ease;
                margin: 0.5rem;
            }

            .btn-modern.btn-primary {
                background: linear-gradient(135deg, #412fd1 0%, #3b82f6 100%);
                border: none;
                color: white;
            }

            .btn-modern.btn-primary:hover {
                background: linear-gradient(135deg, #3730a3 0%, #2563eb 100%);
                transform: translateY(-1px);
            }

            .btn-modern.btn-secondary {
                background: #6b7280;
                border: none;
                color: white;
            }

            .btn-modern.btn-secondary:hover {
                background: #4b5563;
                transform: translateY(-1px);
            }

            /* Input group styling */
            .input-group-text {
                background: #f3f4f6;
                border: 2px solid #e5e7eb;
                border-left: none;
                cursor: pointer;
                font-size: 16px;
            }

            .input-group .form-control {
                border-right: none;
            }

            .input-group .form-control:focus + .input-group-append .input-group-text {
                border-color: #412fd1;
            }

            /* Mensajes */
            .success-message {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 20px;
                border-radius: 12px;
                text-align: center;
                font-size: 18px;
                font-weight: 600;
                margin: 2rem auto;
                max-width: 600px;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            }

            .form-help {
                font-size: 13px !important;
                color: #6b7280;
                margin-top: 4px;
            }

            /* Responsive improvements */
            @media (max-width: 768px) {
                .modern-form-container {
                    margin: 1rem;
                    padding: 1.5rem;
                }
                
                .form-title {
                    font-size: 28px !important;
                }
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

            // Validación del formulario
            function validarFormulario() {
                const usuario = document.getElementById('usuario').value;
                const password = document.getElementById('password').value;
                const nombre = document.getElementById('nombre').value;
                const cedula = document.getElementById('cedula').value;

                if (!nombre || !cedula || !usuario || !password) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos requeridos',
                        text: 'Por favor complete todos los campos obligatorios.',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                if (password.length < 4) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Contraseña muy corta',
                        text: 'La contraseña debe tener al menos 4 caracteres.',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                return true;
            }
        </script>
    </head>
    <body>
  
        <center style="margin-top: 20px;">
            <img src="../../img/logo.png" width="150" height="120" class="responsive">
        </center>

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
            echo "<div class='success-message'>
                    <i class='bi bi-check-circle-fill' style='font-size: 24px; margin-right: 10px;'></i>
                    <strong>¡REGISTRO CREADO SATISFACTORIAMENTE!</strong>
                    <br><br>
                    <a href='showusers.php' class='btn btn-light btn-modern' style='margin-top: 15px;'>
                        <i class='bi bi-arrow-left'></i> Regresar a Usuarios
                    </a>
                  </div>";
        }
    } else {
?>
        
        <div class="container">
            <div class="modern-form-container">
                <h1 class="form-title">
                    <i class="bi bi-person-plus-fill"></i> 
                    REGISTRO DE NUEVO USUARIO
                </h1>
                <p class="form-subtitle">
                    <i class="bi bi-asterisk"></i>
                    Los campos marcados con * son obligatorios
                </p>
                
                <form action="" method="POST" onsubmit="return validarFormulario()">
                    
                    <div class="row mb-4">
                        <div class="col-12 col-md-8">
                            <label for="nombre" class="form-label">
                                <i class="bi bi-person"></i> * NOMBRES COMPLETOS:
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   class="form-control" 
                                   id="nombre" 
                                   required 
                                   autofocus 
                                   style="text-transform:uppercase;"
                                   placeholder="Ingrese nombres y apellidos completos" />
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="cedula" class="form-label">
                                <i class="bi bi-card-text"></i> * CÉDULA:
                            </label>
                            <input type="number" 
                                   name="cedula" 
                                   class="form-control" 
                                   id="cedula" 
                                   required
                                   placeholder="Número de cédula" />
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12 col-md-6">
                            <label for="usuario" class="form-label">
                                <i class="bi bi-at"></i> * USUARIO:
                            </label>
                            <input type="text" 
                                   name="usuario" 
                                   id="usuario" 
                                   class="form-control" 
                                   required 
                                   onkeypress="return evitarEspacios(event)"
                                   placeholder="nombre.usuario" />
                            <div class="form-help">
                                <i class="bi bi-info-circle"></i>
                                Solo minúsculas, sin espacios ni caracteres especiales
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> * CONTRASEÑA:
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control" 
                                       required 
                                       placeholder="Contraseña segura" />
                                <div class="input-group-append">
                                    <span class="input-group-text" onclick="togglePasswordVisibility();">
                                        <i class="fas fa-eye" id="password-toggle"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-help">
                                <i class="bi bi-shield-check"></i>
                                Mínimo 4 caracteres
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="submit" class="btn btn-modern btn-primary">
                            <i class="bi bi-person-plus"></i>
                            REGISTRAR USUARIO
                        </button>
                        <button type="button" 
                                class="btn btn-modern btn-secondary" 
                                onclick="window.location.href='showusers.php'">
                            <i class="bi bi-arrow-left"></i>
                            REGRESAR
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://www.jose-aguilar.com/scripts/fontawesome/js/all.min.js" data-auto-replace-svg="nest"></script>

    </body>
</html>

<?php } ?>
