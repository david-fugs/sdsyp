<?php
    session_start();
    
    if(!isset($_SESSION['id'])){
        header("Location: index.php");
    }
    
    $nombre = $_SESSION['nombre'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>BD SISBEN</title>
        <script src="js/64d58efce2.js" ></script>
        <link rel="stylesheet" href="css/menu.css">
        <style>
            .responsive {
                max-width: 100%;
                height: auto;
            }
        </style>
    <head>
    <body>
        
        <header>
            <div class="container">
                <input type="checkbox" name="" id="check">

                <div class="logo-container">
                    <h3 class="logo"><?php echo $nombre; ?><span></span></a></h3>
                </div>

                <?php if($tipo_usuario == 1) { ?>

                    <div class="nav-btn">
                        <div class="nav-links">
                            <ul>
                                <li class="nav-link" style="--i: .85s">
                                    <a href="#">Master<i class="fas fa-caret-down"></i></a>
                                    <div class="dropdown">
                                        <ul>
                                            <li class="dropdown-link">
                                                <a href="#">Contratistas<i class="fas fa-caret-down"></i></a>
                                                <div class="dropdown second">
                                                    <ul>
                                                        <li class="dropdown-link">
                                                            <a href="code/tmaster/addencuestadores1.php">Ingresar</a>
                                                        </li>
                                                        <li class="dropdown-link">
                                                            <a href="code/tmaster/addencuestadores.php">Consultar</a>
                                                        </li>
                                                        <div class="arrow"></div>
                                                    </ul>
                                                </div>
                                            </li>
                                            <div class="arrow"></div>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                
                               
                    <div class="log-sign" style="--i: 1.8s">
                        <!--<a href="#" class="btn transparent">Log in</a>-->
                        <a href="logout.php" class="btn solid">Salir</a>
                   
                        <div class="log-sign" style="--i: 1.8s">
                            <!--<a href="#" class="btn transparent">Log in</a>-->
                            <a href="reset-password.php" class="btn solid">Cambiar Contraseña</a>
                        </div>
                    </div>

                    <div class="nav-btn">
                        <div class="nav-links">
                            <ul>
                                <li class="nav-link" style="--i: .6s">
                                    <a href="code/usuarios/adduser.php">Usuarios</a>
                                </li>
                                
                            </ul>
                        </div>
                    </div>
                <?php } ?>

                <?php if($tipo_usuario == 2) { ?>
                    
                <div class="nav-btn">
                    <div class="nav-links">
                        <ul>
                            <li class="nav-link" style="--i: .85s">
                                <a href="#">Master<i class="fas fa-caret-down"></i></a>
                                <div class="dropdown">
                                    <ul>
                                        <li class="dropdown-link">
                                            <a href="#">Contratistas<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                   <li class="dropdown-link">
                                                        <a href="code/tmaster/addencuestadores1.php">Ingresar</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/tmaster/addencuestadores.php">Consultar</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="dropdown-link">
                                            <a href="#">Comunas<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                    <li class="dropdown-link">
                                                        <a href="code/acudientes/addparent1.php">Ingresar</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/acudientes/addparent.php">Consultar</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="dropdown-link">
                                            <a href="#">Barrios<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                    <li class="dropdown-link">
                                                        <a href="code/colegios/addschool1.php">Ingresar</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/colegios/addschool.php">Consultar</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="dropdown-link">
                                            <a href="#">Operadores<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                    <li class="dropdown-link">
                                                        <a href="code/municipios/addcity1.php">Ingresar</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/municipios/addcity.php">Consultar</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="dropdown-link">
                                            <a href="#">Trámites<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                    <li class="dropdown-link">
                                                        <a href="code/municipios/addcity1.php">Ingresar</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/municipios/addcity.php">Consultar</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <div class="arrow"></div>
                                    </ul>
                                </div>
                            </li>
                
                            <li class="nav-link" style="--i: 1.1s">
                                <a href="#">Registro<i class="fas fa-caret-down"></i></a>
                                <div class="dropdown">
                                    <ul>
                                        <li class="dropdown-link">
                                            <a href="#">Académico<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                    <li class="dropdown-link">
                                                        <a href="code/matriculas/vincular_matricula.php">Matrícula</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/matriculas/vincular.php">I Semestre</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/matriculas/vincular_semestre.php">II, III, IV, V, VI, VII... Semestre</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/matriculas/addenrollment.php">Consultar</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="dropdown-link">
                                            <a href="#">Universidad<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                    <li class="dropdown-link">
                                                        <a href="code/universidades/adduniversity1.php">Ingresar</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/universidades/adduniversity.php">Consultar</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="dropdown-link">
                                            <a href="#">Programa<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                    <li class="dropdown-link">
                                                        <a href="code/programas/addprograma1.php">Ingresar</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/programas/addprograma.php">Consultar</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <div class="arrow"></div>
                                    </ul>
                                </div>
                            </li>

                             <li class="nav-link" style="--i: 1.35s">
                                <a href="#">Informes<i class="fas fa-caret-down"></i></a>
                                <div class="dropdown">
                                    <ul>
                                        <li class="dropdown-link">
                                            <a href="#">Estudiantes<i class="fas fa-caret-down"></i></a>
                                            <div class="dropdown second">
                                                <ul>
                                                    <li class="dropdown-link">
                                                        <a href="code/report/student/est_inf_1.php">Total Estudiantes</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/report/student/est_inf_6.php">Estudiantes Digitados</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/report/student/est_inf_2.php">I.E. - Población</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/report/student/est_inf_3.php">Municipio - Población</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/report/student/est_inf_4.php">I.E. - Univ. - Prog.</a>
                                                    </li>
                                                    <li class="dropdown-link">
                                                        <a href="code/report/student/est_inf_5.php">Estudiantes por Período Acad.</a>
                                                    </li>
                                                    <div class="arrow"></div>
                                                </ul>
                                            </div>
                                        </li>
                                        <div class="arrow"></div>
                                    </ul>
                                </div>
                            </li>

                        </ul>
                    </div>

                        <div class="log-sign" style="--i: 1.8s">
                            <!--<a href="#" class="btn transparent">Log in</a>-->
                            <a href="logout.php" class="btn solid">Salir</a>
                       
                            <div class="log-sign" style="--i: 1.8s">
                                <!--<a href="#" class="btn transparent">Log in</a>-->
                                <a href="reset-password.php" class="btn solid">Cambiar Contraseña</a>
                            </div>
                        </div>
                </div>
                <?php } ?>

                <div class="hamburger-menu-container">
                    <div class="hamburger-menu">
                        <div></div>
                    </div>
                </div>
            </div>
        </header>
            <main>
                <section>
                    <div class="overlay"></div>
                </section>
            </main>
    </body>
</html>