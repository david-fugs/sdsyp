<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDSYP</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    <link rel="stylesheet" href="styleSell.css">
    <!-- Bootstrap CSS -->
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Librerías de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    
    <!-- Estilos personalizados para aumentar tamaño de fuente -->
    <style>
        /* Aumentar tamaño de fuente general */
        body {
            font-size: 16px !important;
        }
        
        /* Tabla - aumentar tamaño de fuente */
        .modern-table {
            font-size: 15px !important;
        }
        
        .modern-table th {
            font-size: 16px !important;
            font-weight: 600 !important;
        }
        
        .modern-table td {
            font-size: 15px !important;
            padding: 12px 8px !important;
        }
        
        /* Filtros y inputs - aumentar tamaño */
        .modern-input, .modern-select {
            font-size: 15px !important;
            padding: 10px 12px !important;
        }
        
        .filter-group label {
            font-size: 14px !important;
            font-weight: 600 !important;
        }
        
        /* Botones - aumentar tamaño */
        .btn-modern {
            font-size: 15px !important;
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
        
        .text-muted, .text-success, .text-danger {
            font-size: 13px !important;
        }
    </style>
</head>
<?php
include("../../conexion.php");
$metas = "SELECT * FROM metas ";
$result_metas = mysqli_query($mysqli, $metas);
if (!$result_metas) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

if (isset($_GET['delete'])) {
    $actividad = $_GET['delete'];
    deleteMember($actividad);
}

function deleteMember($actividad)
{
    global $mysqli;

    $query = "DELETE FROM actividades WHERE id_actividad = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $actividad);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                title: '¡Eliminada!',
                text: 'La actividad ha sido eliminada correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#10b981'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'seeActivity.php';
                }
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error',
                text: 'Error al eliminar la actividad. Inténtalo de nuevo.',
                icon: 'error',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'seeActivity.php';
                }
            });
        </script>";
    }

    $stmt->close();
}

?>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;"><b><i
                class="bi bi-calendar-check"></i> ACTIVIDADES</b></h1>

    <!-- Tabla de Actividades -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-calendar-check"></i> Actividades Registradas</h2>
                <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                    <i class="bi bi-plus-circle"></i>
                    Agregar Actividad
                </button>
            </div>

            <!-- Tabla moderna -->
            <div class="modern-table-wrapper">
                <table class="modern-table" id="salesTable">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th>Meta Asociada</th>
                            <th>Descripción de la Actividad</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php include "getActivity.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Agregar Actividad -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addActivity.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Actividad
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Meta Asociada -->
                        <div class="row mb-3">
                            <div class="col-md-12 form-floating">
                                <select name="id_meta" id="id_meta" class="form-select" required>
                                    <option value="">Seleccione Meta Asociada</option>
                                    <?php 
                                    mysqli_data_seek($result_metas, 0);
                                    while ($row = mysqli_fetch_assoc($result_metas)) { ?>
                                        <option value="<?php echo $row['id_meta']; ?>"><?php echo $row['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="id_meta">Meta Asociada</label>
                            </div>
                        </div>
                        <!-- Descripción de la actividad -->
                        <div class="row">
                            <div class="col-md-12 form-floating">
                                <input type="text" class="form-control" id="descripcion_actividad" name="descripcion_actividad" placeholder="Descripción" required autocomplete="off">
                                <label for="descripcion_actividad">Descripción de la Actividad</label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Actividad -->
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 shadow-sm">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalEdicionLabel">Editar Actividad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="editActivity.php" method="POST">
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label for="edit-descripcion" class="form-label">Descripción de la Actividad</label>
                            <input type="text" class="form-control" id="edit-descripcion" name="descripcion_actividad">
                        </div>
                        <div class="mb-3">
                            <label for="edit-id_meta" class="form-label">Meta Asociada</label>
                            <select name="id_meta" id="edit-id_meta" class="form-select">
                                <option value="">Seleccione meta asociada</option>
                                <?php
                                mysqli_data_seek($result_metas, 0);
                                while ($metas = mysqli_fetch_assoc($result_metas)) {
                                    echo "<option value='" . $metas['id_meta'] . "'>" . $metas['descripcion_meta'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="id_actividad" id="edit-id_actividad">
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="guardarCambios">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <br /><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="back" /></a><br>

    <script>
        $(document).ready(function() {
            // Configurar idioma en español para DataTables
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
                }
            });

            // Inicializar DataTables
            const table = $('#salesTable').DataTable({
                pageLength: 25,
                responsive: true,
                order: [[2, 'asc']], // Ordenar por descripción de actividad
                columnDefs: [
                    { targets: [0, 3], orderable: false, searchable: false }
                ]
            });

            // Modal de edición
            $('#modalEdicion').on('shown.bs.modal', function(event) {
                const button = event.relatedTarget;
                
                // Llenar campos del modal
                document.getElementById("edit-descripcion").value = button.getAttribute("data-descripcion_actividad");
                document.getElementById("edit-id_meta").value = button.getAttribute("data-id_meta");
                document.getElementById("edit-id_actividad").value = button.getAttribute("data-id_actividad");
            });
        });

        // Función para confirmar eliminación
        function confirmarEliminacion(id, descripcion) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar la actividad "${descripcion}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?delete=${id}`;
                }
            });
        }
    </script>
</body>
</html>