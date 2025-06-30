<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
}

$usuario      = $_SESSION['usuario'];
$nombre       = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$cod_dane_ie  = $_SESSION['cod_dane_ie'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SDSYP</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Librerías de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/fed2435e21.js" crossorigin="anonymous"></script>

    <!-- Estilos personalizados para aumentar tamaño de fuente -->
    <style>
        /* Aumentar tamaño de fuente general */
        body {
            font-size: 16px !important;
        }
        
        /* Tabla - aumentar tamaño de fuente */
        .modern-table {
            font-size: 18px !important;
        }
        
        .modern-table th {
            font-size: 20px !important;
            font-weight: 600 !important;
        }
        
        .modern-table td {
            font-size: 18px !important;
            padding: 12px 8px !important;
        }
        
        /* Filtros y inputs - aumentar tamaño */
        .modern-input, .modern-select {
            font-size: 18px !important;
            padding: 10px 12px !important;
        }
        
        .filter-group label {
            font-size: 18px !important;
            font-weight: 600 !important;
        }
        
        /* Botones - aumentar tamaño */
        .btn-modern {
            font-size: 18px !important;
            padding: 10px 20px !important;
        }
        
        .btn-action {
            padding: 8px 10px !important;
            font-size: 14px !important;
        }
        
        /* Header moderno */
        .modern-header h2 {
            font-size: 26px !important;
        }
        
        /* DataTables controles */
        .dataTables_info, .dataTables_paginate {
            font-size: 14px !important;
        }
        
        .dataTables_length select, .dataTables_length label {
            font-size: 14px !important;
        }
        
        .paginate_button {
            font-size: 14px !important;
        }
        
        /* Modales - aumentar tamaño de fuente */
        .modal-title {
            font-size: 20px !important;
        }
        
        .modal-body {
            font-size: 15px !important;
        }
        
        .form-label {
            font-size: 14px !important;
            font-weight: 600 !important;
        }
        
        .form-control, .form-select {
            font-size: 15px !important;
        }
        
        /* SweetAlert - aumentar tamaño */
        .swal2-title {
            font-size: 20px !important;
        }
        
        .swal2-content {
            font-size: 16px !important;
        }
        
        /* Enlaces y navegación */
        a {
            font-size: 15px !important;
        }
        
        /* Mensajes de estado */
        .text-muted, .text-success, .text-danger {
            font-size: 13px !important;
        }

        .responsive {
            max-width: 100%;
            height: auto;
        }

        .selector-for-some-widget {
            box-sizing: content-box;
        }
    </style>
    <script>
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas ELIMINAR este usuario de la lista? Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deleteusers.php?id=' + id;
                }
            });
        }
    </script>
</head>

<body>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"></script>

    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class='responsive'>
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;"><b><i
                class="bi bi-people-fill"></i> GESTIÓN DE USUARIOS</b></h1>

    <!-- Tabla de Usuarios -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-person-gear"></i> Administración de Usuarios</h2>
                <a href="addusers.php" class="btn-modern btn-success">
                    <i class="bi bi-person-plus-fill"></i>
                    Agregar Usuario
                </a>
            </div>

            <!-- Filtros modernos -->
            <div class="modern-filters">
                <form action="showusers.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="usuario">Usuario</label>
                        <input type="text" 
                               id="usuario" 
                               name="usuario" 
                               class="modern-input" 
                               placeholder="Buscar por usuario..."
                               value="<?= isset($_GET['usuario']) ? htmlspecialchars($_GET['usuario']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               class="modern-input" 
                               placeholder="Buscar por nombre..."
                               value="<?= isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '' ?>">
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-modern btn-primary">
                            <i class="bi bi-search"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla moderna -->
            <div class="modern-table-wrapper">

        <?php

        date_default_timezone_set("America/Bogota");
        include("../../conexion.php");
        require_once("../../zebra.php");
        @$usuario = ($_GET['usuario']);
        @$nombre = ($_GET['nombre']);

        $query = "SELECT * FROM `usuarios` WHERE (usuario LIKE '%" . $usuario . "%') AND (nombre LIKE '%" . $nombre . "%') AND usuarios.estado_usu = 1 ORDER BY usuarios.id ASC";
        $res = $mysqli->query($query);
        $num_registros = mysqli_num_rows($res);
        $resul_x_pagina = 50;

        if ($res) {

            $paginacion = new Zebra_Pagination();
            $paginacion->records($num_registros);
            $paginacion->records_per_page($resul_x_pagina);

            $consulta = "SELECT * FROM `usuarios` WHERE (usuario LIKE '%" . $usuario . "%') AND (nombre LIKE '%" . $nombre . "%') AND usuarios.estado_usu = 1 ORDER BY usuarios.id ASC LIMIT " . (($paginacion->get_page() - 1) * $resul_x_pagina) . "," . $resul_x_pagina;
            $result = $mysqli->query($consulta);
            if ($result) {
                echo '<table class="modern-table" id="usersTable">
                        <thead>
                            <tr>
                                <th class="col-id">No.</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Tipo Usuario</th>
                                <th class="col-actions">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>';

                $i = 1;
                while ($row = mysqli_fetch_array($result)) {
                    // Determinar el tipo de usuario para mostrar
                    $tipo_usuario_texto = '';
                    $badge_class = '';
                    switch($row['tipo_usuario']) {
                        case 1:
                            $tipo_usuario_texto = 'ADMIN';
                            $badge_class = 'badge bg-success';
                            break;
                        case 2:
                            $tipo_usuario_texto = 'EMPLEADO';
                            $badge_class = 'badge bg-primary';
                            break;
                        case 7:
                            $tipo_usuario_texto = 'SIN ACCESO';
                            $badge_class = 'badge bg-secondary';
                            break;
                        default:
                            $tipo_usuario_texto = 'DESCONOCIDO';
                            $badge_class = 'badge bg-warning';
                    }

                    echo '<tr class="fade-in">
                        <td class="col-id">' . ($i + (($paginacion->get_page() - 1) * $resul_x_pagina)) . '</td>
                        <td>' . htmlspecialchars($row['usuario']) . '</td>
                        <td>' . htmlspecialchars(utf8_encode($row['nombre'])) . '</td>
                        <td><span class="' . $badge_class . '">' . $tipo_usuario_texto . '</span></td>
                        <td class="col-actions">
                            <div class="action-buttons">
                                <a href="editusers.php?id=' . $row['id'] . '" 
                                   class="btn-action btn-edit" 
                                   title="Editar usuario">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="#" 
                                   onclick="confirmarEliminacion(' . $row['id'] . ')" 
                                   class="btn-action btn-delete" 
                                   title="Eliminar usuario">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>';
                    $i++;
                }

                echo '</tbody>
                    </table>
                </div>
            </div>
        </div>';
                
                echo '<br>';
                $paginacion->render();
            } else {
                echo "Error en la consulta: " . $mysqli->error;
            }
        } else {
            echo "Error en la consulta: " . $mysqli->error;
        }
        ?>

        <center>
            <br /><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="Regresar" /></a>
        </center>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"></script>
    <script src="https://www.jose-aguilar.com/scripts/fontawesome/js/all.min.js" data-auto-replace-svg="nest"></script>

    <script>
        // Inicializar DataTables para la tabla de usuarios
        let usersTable;
        
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#usersTable')) {
                $('#usersTable').DataTable().destroy();
            }
            
            usersTable = $('#usersTable').DataTable({
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                columnDefs: [
                    { orderable: false, targets: [4] }, // Deshabilitar orden en la columna de acciones
                    { className: "text-center", targets: [0, 4] } // Centrar columna de ID y acciones
                ],
                order: [[0, 'asc']], // Ordenar por número ascendente
                dom: 'frtip', // Solo mostrar filtro, tabla, información y paginación
                searching: false, // Deshabilitar búsqueda de DataTables (usamos filtros propios)
                info: true,
                paging: true,
                responsive: true
            });
        }

        // Inicializar cuando el documento esté listo
        $(document).ready(function() {
            initDataTable();
        });
    </script>

</body>

</html>