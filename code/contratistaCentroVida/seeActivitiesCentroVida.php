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
    <link rel="stylesheet" type="text/css" href="styles.css">
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
        
        /* Paginación - aumentar tamaño */
        .dataTables_length select, .dataTables_filter input {
            font-size: 15px !important;
            padding: 8px 12px !important;
        }
        
        .dataTables_info, .dataTables_paginate {
            font-size: 14px !important;
        }
        
        /* Botones - aumentar tamaño */
        .btn-modern {
            font-size: 15px !important;
            padding: 12px 20px !important;
        }
        
        .btn-action {
            font-size: 14px !important;
            padding: 8px 12px !important;
        }
        
        /* Modales - aumentar tamaño */
        .modal-title {
            font-size: 18px !important;
        }
        
        .modal-body {
            font-size: 15px !important;
        }
        
        .form-control, .form-select {
            font-size: 15px !important;
        }
        
        .text-muted, .text-success, .text-danger {
            font-size: 13px !important;
        }

        /* Estilos para botones de acción */
        .col-actions {
            width: 120px !important;
            text-align: center !important;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }
        
        .btn-action {
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-edit {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }
        
        .col-id {
            width: 80px !important;
            text-align: center !important;
        }
    </style>
</head>
<?php
include("../../conexion.php");


if (isset($_GET['delete'])) {
    $id_actividad = $_GET['delete'];
    deleteMember($id_actividad);
}

function deleteMember($id_actividad)
{
    global $mysqli;

    $query = "DELETE FROM actividad_centro_vida WHERE id_actividad_centro_vida = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_actividad);

    if ($stmt->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Eliminada!',
                    text: 'La actividad ha sido eliminada correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#10b981'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'seeActivitiesCentroVida.php';
                    }
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al eliminar la actividad. Inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#ef4444'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'seeActivitiesCentroVida.php';
                    }
                });
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
                class="bi bi-heart-fill"></i> ACTIVIDADES CENTRO VIDA</b></h1>

    <!-- Tabla de Actividades -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-heart-fill"></i> Actividades Centro Vida Registradas</h2>
                <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                    <i class="bi bi-plus-circle"></i>
                    Agregar Actividad Centro Vida
                </button>
            </div>

            <!-- Tabla moderna -->
            <div class="modern-table-wrapper">
                <table class="modern-table" id="salesTable">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th>Descripción de la Actividad Centro Vida</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php include "getActivitiesCentroVida.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Agregar Actividad -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addActivityCentroVida.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Actividad Centro Vida
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <input type="text" class="form-control" id="descripcion_actividad" name="descripcion_actividad" placeholder="Descripción" required autocomplete="off" autofocus>
                                <label for="descripcion_actividad">Descripción de la Actividad Centro Vida</label>
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
                    <h5 class="modal-title" id="modalEdicionLabel">Editar Actividad Centro Vida</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="editActivityCentroVida.php" method="POST">
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label for="edit-descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="edit-descripcion" name="descripcion_actividad" >
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
        // Función para confirmar eliminación - Definir antes de DOM ready
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

        $(document).ready(function() {
            // Inicializar DataTables con configuración en español local
            const table = $('#salesTable').DataTable({
                pageLength: 15,
                responsive: true,
                order: [[1, 'asc']],
                columnDefs: [
                    { targets: [0, 2], orderable: false, searchable: false }
                ],
                language: {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });

            // Modal de edición
            $('#modalEdicion').on('shown.bs.modal', function(event) {
                const button = event.relatedTarget;
                
                // Llenar campos del modal
                document.getElementById("edit-descripcion").value = button.getAttribute("data-descripcion_actividad");
                document.getElementById("edit-id_actividad").value = button.getAttribute("data-id_actividad");
            });
        });
    </script>
</body>
</html>
