<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Políticas Públicas - SDSYP</title>
    
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

$acciones = "SELECT * FROM acciones ";
$result_acciones = mysqli_query($mysqli, $acciones);
if (!$result_acciones) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

if (isset($_GET['delete'])) {
    $id_politica = $_GET['delete'];
    deleteMember($id_politica);
}

function deleteMember($id_politica)
{
    global $mysqli;

    $query = "DELETE FROM politicas_publicas WHERE id_politica = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_politica);

    if ($stmt->execute()) {
        echo "<script>
        Swal.fire({
            title: '¡Éxito!',
            text: 'Política pública eliminada correctamente',
            icon: 'success',
            confirmButtonColor: '#007bff'
        }).then((result) => {
            window.location = 'seePublicPolicies.php';
        });
        </script>";
    } else {
        echo "<script>
        Swal.fire({
            title: 'Error',
            text: 'Error eliminando la política pública',
            icon: 'error',
            confirmButtonColor: '#007bff'
        }).then((result) => {
            window.location = 'seePublicPolicies.php';
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
                    <i class="bi bi-clipboard-check header-icon"></i>
                    <h1>Políticas Públicas</h1>
                </div>
                <button type="button" class="btn btn-add-main" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="bi bi-plus-circle me-2"></i>
                    Agregar Política Pública
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <div class="table-container">
                <table class="table table-hover" id="politicasTable">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include "getPublicPolicies.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Política -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addPublicPolicy.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarLabel">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Política Pública
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="descripcion_politica" class="form-label">Descripción de la Política Pública</label>
                            <input type="text" class="form-control" id="descripcion_politica" name="descripcion_politica" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="id_accion" class="form-label">Acción</label>
                            <select class="form-select" id="id_accion" name="id_accion">
                                <option value="">Seleccione una acción...</option>
                                <?php foreach ($result_acciones as $accion) { ?>
                                    <option value="<?= $accion['id_accion']; ?>"><?= $accion['descripcion_accion']; ?></option>
                                <?php } ?>
                            </select>
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

    <!-- Modal Editar Política -->
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="editPublicPolicy.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEdicionLabel">
                            <i class="bi bi-pencil-square me-2"></i>Editar Política Pública
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit-descripcion" class="form-label">Descripción de la Política Pública</label>
                            <input type="text" class="form-control" id="edit-descripcion" name="descripcion_politica" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-accion" class="form-label">Acción</label>
                            <select class="form-select" id="edit-accion" name="id_accion">
                                <option value="">Seleccione una acción...</option>
                                <?php foreach ($result_acciones as $accion) { ?>
                                    <option value="<?= $accion['id_accion']; ?>"><?= $accion['descripcion_accion']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <input type="hidden" name="id_politica" id="edit-id_politica">
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
            $('#politicasTable').DataTable({
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

            // Modal edición política
            $('#modalEdicion').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#edit-descripcion').val(button.data('descripcion_politica'));
                modal.find('#edit-id_politica').val(button.data('id_politica'));
                modal.find('#edit-accion').val(button.data('id_accion'));
            });
        });

        // Función para eliminar política
        function eliminarPolitica(id) {
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
