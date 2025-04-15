<?php
session_start();

if (!isset($_SESSION['id'])) {
  header("Location: index.php");
}

$usuario      = $_SESSION['usuario'];
$nombre       = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$cod_dane_ie  = $_SESSION['cod_dane_ie'];
?>

<!DOCTYPE html>
<!-- Coding by CodingNepal || www.codingnepalweb.com -->
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Boxicons CSS -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" defer></script>

  <script src="https://kit.fontawesome.com/fed2435e21.js" crossorigin="anonymous"></script>
  <title>SDSYP</title>
  <link rel="stylesheet" href="menu/style.css" />
</head>

<body>
  <!-- navbar -->
  <nav class="navbar">
    <div class="logo_item">
      <i class="bx bx-menu" id="sidebarOpen"></i>
      <img src="img/logo.png" alt=""></i>PROGRAMAS DE CARACTERIZACIÓN
    </div>


    <div class="navbar_content">
      <i class="bi bi-grid"></i>
      <i class="fa-solid fa-sun" id="darkLight"></i><!--<i class='bx bx-sun' id="darkLight"></i>-->
      <a href="logout.php"> <i class="fa-solid fa-door-open"></i></a>
      <img src="img/logo.png" alt="" class="profile" />
    </div>
  </nav>

  <!--********************************INICIA MENÚ RECTOR********************************-->

  <?php if ($tipo_usuario == 1) { ?>
    <!-- sidebar -->
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>
          <!-- duplicate or remove this li tag if you want to add or remove navlink with submenu -->
          <!-- start -->
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-user-pen"></i>
                <!--<i class="bx bx-home-alt"></i>-->
              </span>
              <span class="navlink">Usuarios Plataforma</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
              <a href="code/users/showusers.php" class="nav_link sublink">Permisos</a>
              <a href="code/users/register.php" class="nav_link sublink">Crear Nuevo</a>
            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
              <i class="fa-solid fa-people-arrows"></i>
                <!--<i class="bx bx-home-alt"></i>-->
              </span>
              <span class="navlink">Personas</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
              <a href="code/persons/seePerson.php" class="nav_link sublink">Ver Personas</a>
            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
              <i class="fa-solid fa-people-arrows"></i>
                <!--<i class="bx bx-home-alt"></i>-->
              </span>
              <span class="navlink">Metas</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
              <a href="code/goals/seeGoals.php" class="nav_link sublink">Ver Metas</a>
              <a href="code/activities/seeActivity.php" class="nav_link sublink">Ver Actividades</a>
              <a href="code/action/seeActions.php" class="nav_link sublink">Ver Acciones</a>


            </ul>
          </li>
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
              <i class="fa-solid fa-people-arrows"></i>
                <!--<i class="bx bx-home-alt"></i>-->
              </span>
              <span class="navlink">Formulario</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
              <a href="code/form/addForm.php" class="nav_link sublink">Ingresar Formulario</a>
            </ul>
          </li>

          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="fa-solid fa-screwdriver-wrench"></i>
              </span>
              <span class="navlink">Mi Cuenta</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
              <a href="reset-password.php" class="nav_link sublink">Cambiar Contraseña</a>
            </ul>
          </li>

          <!-- Sidebar Open / Close -->
          <div class="bottom_content">
            <div class="bottom expand_sidebar">
              <span> Expand</span>
              <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
              <span> Collapse</span>
              <i class='bx bx-log-out'></i>
            </div>
          </div>
      </div>
    </nav>
  <?php } ?>


  <!-- JavaScript -->
  <script src="menu/script.js"></script>
</body>

</html>