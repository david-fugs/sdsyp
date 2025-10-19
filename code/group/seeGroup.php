<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos - SDSYP</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <link href="../../css/modern-table-styles.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .header-icon {
            font-size: 3rem;
        }
        
        .btn-add-main {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .btn-add-main:hover {
            background: white;
            color: #007bff;
            transform: translateY(-2px);
        }
        
        .main-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-container {
            padding: 2rem;
        }
        
        .table th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .btn-close {
            filter: invert(1);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
<?php
include("../../conexion.php");

if (isset($_GET['delete'])) {
    $grupo = $_GET['delete'];
    deleteMember($grupo);
}

function deleteMember($grupo)
{
    global $mysqli;

    $query = "DELETE FROM grupos WHERE id_grupo = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $grupo);

    if ($stmt->execute()) {
        echo "<script>
        Swal.fire({
            title: '¡Éxito!',
            text: 'Grupo eliminado correctamente',
            icon: 'success',
            confirmButtonColor: '#007bff'
        }).then((result) => {
            window.location = 'seeGroup.php';
        });
        </script>";
    } else {
        echo "<script>
        Swal.fire({
            title: 'Error',
            text: 'Error eliminando el grupo',
            icon: 'error',
            confirmButtonColor: '#007bff'
        }).then((result) => {
            window.location = 'seeGroup.php';
        });
        </script>";
    }

    $stmt->close();
}
?>

    <!-- Logo Section -->
    <div class="text-center mt-4 mb-4">
        <img src="../../img/logo.png" width="150" height="120" alt="Logo SDSYP" class="img-fluid">
    </div>

    <!-- Header Section -->
    <div class="header-section">
        <div class="container">
            <div class="header-content">
                <div class="header-title">
                    <i class="bi bi-people header-icon"></i>
                    <h1>Grupos</h1>
                </div>
                <button type="button" class="btn btn-add-main" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="bi bi-plus-circle me-2"></i>
                    Agregar Grupo
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <div class="table-container">
                <table class="table table-hover" id="gruposTable">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th>Descripción del Grupo</th>
                            <th>Límite de Personas</th>
                            <th>Fecha Contratación</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include "getGroup.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Grupo -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addGroup.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarLabel">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Grupo
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="descripcion_grupo" class="form-label">Descripción del Grupo</label>
                            <input type="text" class="form-control" id="descripcion_grupo" name="descripcion_grupo" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="limite_personas" class="form-label">Límite de Personas</label>
                            <input type="number" class="form-control" id="limite_personas" name="limite_personas" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_contratacion" class="form-label">Fecha de Contratación</label>
                            <input type="date" class="form-control" id="fecha_contratacion" name="fecha_contratacion" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Grupo -->
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="editGroup.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEdicionLabel">
                            <i class="bi bi-pencil-square me-2"></i>Editar Grupo
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit-descripcion" class="form-label">Descripción del Grupo</label>
                            <input type="text" class="form-control" id="edit-descripcion" name="descripcion_grupo" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-limite" class="form-label">Límite de Personas</label>
                            <input type="number" class="form-control" id="edit-limite" name="limite_personas" min="1" required>
                        </div>
                        
                        <!-- Sección de Historial de Fechas de Contratación -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Historial de Fechas de Contratación</label>
                            <div id="historial-fechas-container" class="border rounded p-3 mb-3" style="max-height: 300px; overflow-y: auto;">
                                <!-- Se llenará dinámicamente con AJAX -->
                                <div class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    Cargando historial...
                                </div>
                            </div>
                            
                            <!-- Formulario para agregar nueva fecha -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="bi bi-plus-circle"></i> Agregar Nueva Fecha</h6>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <input type="date" class="form-control" id="nueva-fecha-contratacion">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-success w-100" id="btn-agregar-fecha">
                                                <i class="bi bi-plus"></i> Agregar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="id_grupo" id="edit-id_grupo">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="container mt-4 mb-5">
        <a href="../../access.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Menú Principal
        </a>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#gruposTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'asc']],
                pageLength: 15,
                responsive: true,
                columnDefs: [
                    {
                        targets: [0],
                        width: '80px',
                        className: 'col-id'
                    },
                    {
                        targets: [3],
                        width: '150px',
                        className: 'text-center'
                    },
                    {
                        targets: [4],
                        width: '120px',
                        className: 'col-actions',
                        orderable: false
                    }
                ]
            });

            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Modal edición grupo
            $('#modalEdicion').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#edit-descripcion').val(button.data('descripcion_grupo'));
                modal.find('#edit-limite').val(button.data('limite_personas'));
                modal.find('#edit-id_grupo').val(button.data('id_grupo'));
                
                // Cargar historial de fechas
                cargarHistorialFechas(button.data('id_grupo'));
            });
            
            // Función para cargar historial de fechas
            function cargarHistorialFechas(idGrupo) {
                $.ajax({
                    url: 'getHistorialFechas.php',
                    type: 'GET',
                    data: { id_grupo: idGrupo },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarHistorialFechas(response.data, idGrupo);
                        } else {
                            $('#historial-fechas-container').html('<p class="text-danger">Error al cargar el historial</p>');
                        }
                    },
                    error: function() {
                        $('#historial-fechas-container').html('<p class="text-danger">Error al cargar el historial</p>');
                    }
                });
            }
            
            // Función para mostrar historial de fechas
            function mostrarHistorialFechas(fechas, idGrupo) {
                var html = '';
                
                if (fechas.length === 0) {
                    html = '<p class="text-muted text-center">No hay fechas registradas</p>';
                } else {
                    html = '<div class="list-group">';
                    fechas.forEach(function(fecha) {
                        var fechaFormato = new Date(fecha.fecha_contratacion + 'T00:00:00').toLocaleDateString('es-CO', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        
                        html += '<div class="list-group-item d-flex justify-content-between align-items-center">';
                        html += '<div>';
                        html += '<i class="bi bi-calendar-check text-primary me-2"></i>';
                        html += '<strong>' + fechaFormato + '</strong>';
                        html += '<small class="text-muted ms-2">(' + fecha.created_at + ')</small>';
                        html += '</div>';
                        html += '<div>';
                        html += '<button type="button" class="btn btn-sm btn-outline-primary me-1 btn-editar-fecha" data-id="' + fecha.id_fecha_contratacion + '" data-fecha="' + fecha.fecha_contratacion + '">';
                        html += '<i class="bi bi-pencil"></i>';
                        html += '</button>';
                        html += '<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fecha" data-id="' + fecha.id_fecha_contratacion + '">';
                        html += '<i class="bi bi-trash"></i>';
                        html += '</button>';
                        html += '</div>';
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                $('#historial-fechas-container').html(html);
            }
            
            // Agregar nueva fecha
            $('#btn-agregar-fecha').on('click', function() {
                var idGrupo = $('#edit-id_grupo').val();
                var nuevaFecha = $('#nueva-fecha-contratacion').val();
                
                if (!nuevaFecha) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debe seleccionar una fecha',
                        confirmButtonColor: '#007bff'
                    });
                    return;
                }
                
                $.ajax({
                    url: 'addFechaContratacion.php',
                    type: 'POST',
                    data: {
                        id_grupo: idGrupo,
                        fecha_contratacion: nuevaFecha
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Fecha agregada correctamente',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            $('#nueva-fecha-contratacion').val('');
                            cargarHistorialFechas(idGrupo);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error al agregar la fecha',
                                confirmButtonColor: '#007bff'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al procesar la solicitud',
                            confirmButtonColor: '#007bff'
                        });
                    }
                });
            });
            
            // Editar fecha (delegación de eventos)
            $(document).on('click', '.btn-editar-fecha', function() {
                var idFecha = $(this).data('id');
                var fechaActual = $(this).data('fecha');
                var idGrupo = $('#edit-id_grupo').val();
                
                Swal.fire({
                    title: 'Editar Fecha',
                    html: '<input type="date" id="swal-fecha-edit" class="swal2-input" value="' + fechaActual + '">',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#007bff',
                    preConfirm: () => {
                        return document.getElementById('swal-fecha-edit').value;
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        $.ajax({
                            url: 'editFechaContratacion.php',
                            type: 'POST',
                            data: {
                                id_fecha: idFecha,
                                fecha_contratacion: result.value
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Éxito',
                                        text: 'Fecha actualizada correctamente',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    cargarHistorialFechas(idGrupo);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Error al actualizar la fecha',
                                        confirmButtonColor: '#007bff'
                                    });
                                }
                            }
                        });
                    }
                });
            });
            
            // Eliminar fecha (delegación de eventos)
            $(document).on('click', '.btn-eliminar-fecha', function() {
                var idFecha = $(this).data('id');
                var idGrupo = $('#edit-id_grupo').val();
                
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción eliminará la fecha de contratación",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'deleteFechaContratacion.php',
                            type: 'POST',
                            data: { id_fecha: idFecha },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Eliminado',
                                        text: 'Fecha eliminada correctamente',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    cargarHistorialFechas(idGrupo);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Error al eliminar la fecha',
                                        confirmButtonColor: '#007bff'
                                    });
                                }
                            }
                        });
                    }
                });
            });
        });

        // Función para eliminar grupo
        function eliminarGrupo(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esta acción",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete=' + id;
                }
            });
        }
    </script>
</body>
</html>