<?php

// Configurar codificación UTF-8 más específica
ini_set('default_charset', 'UTF-8');
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require "conexion.php";

// Configurar charset para MySQL con verificación
if ($mysqli->connect_error) {
  die("Error de conexión: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

session_start();

if ($_POST) {
  $usuario = $_POST['usuario'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM usuarios WHERE usuario='$usuario'";
  $resultado = $mysqli->query($sql);
  $num = $resultado->num_rows;

  if ($num > 0) {
    $row = $resultado->fetch_assoc();
    $password_bd = $row['password'];

    $pass_c = sha1($password);

    if ($password_bd == $pass_c) {
      $_SESSION['id'] = $row['id'];
      $_SESSION['nombre'] = $row['nombre'];
      $_SESSION['tipo_usuario'] = $row['tipo_usuario'];
      $_SESSION['usuario'] = $row['usuario'];
      $_SESSION['id_grupo'] = $row['id_grupo'];

      if ($row['tipo_usuario'] == 1 || $row['tipo_usuario'] == 2 || $row['tipo_usuario'] == 3) {
        header("Location: access.php");
      } elseif ($row['tipo_usuario'] == 7) {
        echo '<script>
        alert("Usuario aun sin acceso, contacto con administrador");
        window.location.href = "index.php";
    </script>';
      } else {
        header("Location: index.php");
      }
    } else {
      $error_message = "La contrase&ntilde;a no coincide";
    }
  } else {
    $error_message = "El usuario no existe";
  }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="Content-Language" content="es">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Sistema de Gestión - Iniciar Sesión</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <style>
    /* Reset y configuración base - Más específico que Bootstrap */
    * {
      box-sizing: border-box !important;
    }

    body.login-page {
      font-family: 'Inter', sans-serif !important;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
      min-height: 100vh !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    /* Contenedor principal del login */
    .login-container {
      display: grid !important;
      grid-template-columns: 1fr 1fr !important;
      width: 100% !important;
      max-width: 1200px !important;
      min-height: 700px !important;
      background: white !important;
      border-radius: 20px !important;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1) !important;
      overflow: hidden !important;
      margin: 20px !important;
    }

    /* Lado izquierdo - Branding */
    .login-left {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
      color: white !important;
      padding: 60px 40px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      position: relative !important;
      overflow: hidden !important;
    }

    .login-left::before {
      content: '' !important;
      position: absolute !important;
      top: -50% !important;
      right: -50% !important;
      width: 200% !important;
      height: 200% !important;
      opacity: 0.1 !important;
      animation: float 20s ease-in-out infinite !important;
    }

    @keyframes float {

      0%,
      100% {
        transform: translate(-20px, -20px) rotate(0deg);
      }

      50% {
        transform: translate(20px, 20px) rotate(180deg);
      }
    }

    .brand-content {
      text-align: center !important;
      position: relative !important;
      z-index: 2 !important;
    }

    .logo-container {
      margin-bottom: 30px !important;
    }

    .logo {
      width: 183px !important;
      height: 80px !important;
      border-radius: 50% !important;
      object-fit: cover !important;
    }

    .brand-title {
      font-size: 2.5rem !important;
      font-weight: 700 !important;
      margin-bottom: 15px !important;
      color: white !important;
    }

    .brand-subtitle {
      font-size: 1.1rem !important;
      opacity: 0.9 !important;
      margin-bottom: 40px !important;
      line-height: 1.6 !important;
      color: white !important;
    }

    .features {
      display: flex !important;
      flex-direction: column !important;
      gap: 20px !important;
    }

    .feature-item {
      display: flex !important;
      align-items: center !important;
      gap: 15px !important;
      font-size: 1rem !important;
      opacity: 0.8 !important;
      color: white !important;
    }

    .feature-item i {
      font-size: 1.2rem !important;
      color: #3498db !important;
    }

    /* Lado derecho - Formulario */
    .login-right {
      padding: 60px 40px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      background: white !important;
    }

    .login-form-container {
      width: 100% !important;
      max-width: 400px !important;
    }

    .login-header {
      text-align: center !important;
      margin-bottom: 40px !important;
    }

    .login-header h2 {
      font-size: 2rem !important;
      font-weight: 600 !important;
      color: #2c3e50 !important;
      margin-bottom: 10px !important;
    }

    .login-header p {
      color: #7f8c8d !important;
      font-size: 1rem !important;
      margin: 0 !important;
    }

    /* Mensaje de error */
    .error-message {
      background: #fee !important;
      border: 1px solid #fcc !important;
      color: #c66 !important;
      padding: 12px 16px !important;
      border-radius: 8px !important;
      margin-bottom: 20px !important;
      display: flex !important;
      align-items: center !important;
      gap: 10px !important;
      font-size: 0.9rem !important;
    }

    .error-message i {
      color: #e74c3c !important;
    }

    /* Formulario */
    .login-form {
      width: 100% !important;
    }

    .form-group {
      margin-bottom: 25px !important;
    }

    .form-group label {
      display: block !important;
      margin-bottom: 8px !important;
      font-weight: 500 !important;
      color: #2c3e50 !important;
      font-size: 0.9rem !important;
    }

    .input-wrapper {
      position: relative !important;
      display: flex !important;
      align-items: center !important;
    }

    .input-wrapper i {
      position: absolute !important;
      left: 15px !important;
      color: #bdc3c7 !important;
      font-size: 1rem !important;
      transition: color 0.3s ease !important;
      z-index: 2 !important;
    }

    .input-wrapper input {
      width: 100% !important;
      padding: 15px 15px 15px 45px !important;
      border: 2px solid #ecf0f1 !important;
      border-radius: 10px !important;
      font-size: 1rem !important;
      transition: all 0.3s ease !important;
      background: #fafafa !important;
    }

    .input-wrapper input:focus {
      outline: none !important;
      border-color: #3498db !important;
      background: white !important;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1) !important;
    }

    .input-wrapper.focused i {
      color: #3498db !important;
    }

    .toggle-password {
      position: absolute !important;
      right: 15px !important;
      background: none !important;
      border: none !important;
      color: #bdc3c7 !important;
      cursor: pointer !important;
      padding: 5px !important;
      transition: color 0.3s ease !important;
    }

    .toggle-password:hover {
      color: #3498db !important;
    }

    /* Opciones del formulario */
    .form-options {
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      margin-bottom: 30px !important;
    }

    .checkbox-container {
      display: flex !important;
      align-items: center !important;
      cursor: pointer !important;
      font-size: 0.9rem !important;
      color: #7f8c8d !important;
    }

    .checkbox-container input {
      display: none !important;
    }

    .checkmark {
      width: 18px !important;
      height: 18px !important;
      border: 2px solid #bdc3c7 !important;
      border-radius: 4px !important;
      margin-right: 8px !important;
      position: relative !important;
      transition: all 0.3s ease !important;
    }

    .checkbox-container input:checked+.checkmark {
      background: #3498db !important;
      border-color: #3498db !important;
    }

    .checkbox-container input:checked+.checkmark::after {
      content: '' !important;
      position: absolute !important;
      left: 5px !important;
      top: 1px !important;
      width: 6px !important;
      height: 10px !important;
      border: solid white !important;
      border-width: 0 2px 2px 0 !important;
      transform: rotate(45deg) !important;
    }

    .forgot-password {
      color: #3498db !important;
      text-decoration: none !important;
      font-size: 0.9rem !important;
      transition: color 0.3s ease !important;
    }

    .forgot-password:hover {
      color: #2980b9 !important;
      text-decoration: none !important;
    }

    /* Botón de login */
    .login-btn {
      width: 100% !important;
      padding: 15px !important;
      background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
      color: white !important;
      border: none !important;
      border-radius: 10px !important;
      font-size: 1rem !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 10px !important;
      margin-bottom: 20px !important;
    }

    .login-btn:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 10px 30px rgba(52, 152, 219, 0.3) !important;
    }

    .login-btn:active {
      transform: translateY(0) !important;
    }

    /* Link de registro */
    .signup-link {
      text-align: center !important;
      color: #7f8c8d !important;
      font-size: 0.9rem !important;
    }

    .signup-link a {
      color: #3498db !important;
      text-decoration: none !important;
      font-weight: 500 !important;
    }

    .signup-link a:hover {
      color: #2980b9 !important;
      text-decoration: none !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .login-container {
        grid-template-columns: 1fr !important;
        margin: 10px !important;
        min-height: auto !important;
      }

      .login-left {
        padding: 40px 20px !important;
        min-height: 300px !important;
      }

      .brand-title {
        font-size: 2rem !important;
      }

      .brand-subtitle {
        font-size: 1rem !important;
      }

      .features {
        flex-direction: row !important;
        justify-content: space-around !important;
        flex-wrap: wrap !important;
      }

      .feature-item {
        flex-direction: column !important;
        text-align: center !important;
        gap: 5px !important;
        font-size: 0.9rem !important;
      }

      .login-right {
        padding: 40px 20px !important;
      }

      .login-header h2 {
        font-size: 1.75rem !important;
      }
    }

    @media (max-width: 480px) {
      .login-container {
        margin: 5px !important;
      }

      .login-left {
        padding: 30px 15px !important;
      }

      .login-right {
        padding: 30px 15px !important;
      }

      .brand-title {
        font-size: 1.75rem !important;
      }

      .features {
        display: none !important;
      }
    }
  </style>
</head>

<body class="login-page">
  <div class="login-container">
    <!-- Parte izquierda con imagen/branding -->
    <div class="login-left">
      <div class="brand-content">
        <div class="logo-container">
          <img src="img/logo.png" alt="Logo" class="logo">
        </div>
        <h1 class="brand-title">Sistema de Gesti&oacute;n</h1>
        <p class="brand-subtitle">Plataforma integral para la administraci&oacute;n y seguimiento de actividades</p>
        <div class="features">
          <div class="feature-item">
            <i class="fas fa-shield-alt"></i>
            <span>Seguro y confiable</span>
          </div>
          <div class="feature-item">
            <i class="fas fa-users"></i>
            <span>Gesti&oacute;n de usuarios</span>
          </div>
          <div class="feature-item">
            <i class="fas fa-chart-line"></i>
            <span>Reportes detallados</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Parte derecha con formulario -->
    <div class="login-right">
      <div class="login-form-container">
        <div class="login-header">
          <h2>Iniciar Sesi&oacute;n</h2>
          <p>Ingresa tus credenciales para acceder</p>
        </div>

        <?php if (isset($error_message)): ?>
          <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_message; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="login-form">
          <div class="form-group">
            <label for="usuario">Usuario</label>
            <div class="input-wrapper">
              <i class="fas fa-user"></i>
              <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
            </div>
          </div>

          <div class="form-group">
            <label for="password">Contrase&ntilde;a</label>
            <div class="input-wrapper">
              <i class="fas fa-lock"></i>
              <input type="password" id="password" name="password" placeholder="Ingresa tu contrase&ntilde;a" required>
              <button type="button" class="toggle-password" onclick="togglePassword()">
                <i class="fas fa-eye" id="toggleIcon"></i>
              </button>
            </div>
          </div>

          <div class="form-options">
            <label class="checkbox-container">
              <input type="checkbox" name="remember">
              <span class="checkmark"></span>
              Recordarme
            </label>
            <a href="reset-password.php" class="forgot-password">&iquest;Olvidaste tu contrase&ntilde;a?</a>
          </div>

          <button type="submit" class="login-btn">
            <span>Iniciar Sesi&oacute;n</span>
            <i class="fas fa-arrow-right"></i>
          </button>

          <div class="signup-link">
            &iquest;No tienes cuenta? <a href="code/users/register.php">Crear cuenta</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }

    // Animación para los inputs
    document.querySelectorAll('.input-wrapper input').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
      });

      input.addEventListener('blur', function() {
        if (this.value === '') {
          this.parentElement.classList.remove('focused');
        }
      });
    });
  </script>
</body>

</html>