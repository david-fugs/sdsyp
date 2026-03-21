<?php
session_start();
include("../../conexion.php");

// Verificar que el usuario tenga acceso (tipo 1, 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    header("Location: ../../access.php");
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id'];

// Obtener condiciones Colombia Mayor desde condiciones_componente (solo C.M)
$condiciones = "SELECT id_condicion as id_condicion_cm, descripcion_condicion as descripcion_condicion_cm FROM condiciones_componente WHERE descripcion_condicion LIKE 'C.M%' ORDER BY descripcion_condicion ASC";
$result_condiciones = $mysqli->query($condiciones);
if (!$result_condiciones) {
    die("Error en la consulta de condiciones: " . $mysqli->error);
}

if (isset($_GET['delete'])) {
    $id_movimiento = $_GET['delete'];
    deleteMember($id_movimiento);
}

function deleteMember($id_movimiento_cm)
{
    global $mysqli;
    $query = "DELETE FROM movimientos_colombia_mayor WHERE id_movimiento_cm = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_movimiento_cm);

    if ($stmt->execute()) {
        echo "<script>alert('Movimiento borrado correctamente');
        window.location = 'seeMovimientosCM.php';</script>";
    } else {
        echo "<script>alert('Error borrando el movimiento');
        window.location = 'seeMovimientosCM.php';</script>";
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Colombia Mayor - Movimientos</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-size: 16px !important;
        }

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

        .cm-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .badge-suspendido {
            background: #ffc107;
            color: #000;
        }
        
        .badge-fallecido {
            background: #6c757d;
            color: #fff;
        }
        
        .badge-retiro {
            background: #17a2b8;
            color: #fff;
        }
    </style>
</head>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #667eea; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;">
        <b><i class="bi bi-arrow-left-right"></i> COLOMBIA MAYOR - MOVIMIENTOS</b>
    </h1>

    <div class="container mt-5">
        <div class="modern-container">
            <div class="modern-header">
                <h2><i class="bi bi-arrow-left-right"></i> Movimientos de Personas</h2>
                <div>
                    <button type="button" class="btn-modern btn-success me-2" onclick="window.location.href='exportMovimientosCM.php'">
                        <i class="bi bi-file-excel-fill"></i>
                        Exportar Excel
                    </button>
                    <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewMovimiento">
                        <i class="bi bi-plus-circle-fill"></i>
                        Agregar Movimiento
                    </button>
                </div>
            </div>

            <div class="modern-filters">
                <form action="seeMovimientosCM.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="cedula_persona">Cédula</label>
                        <input type="text"
                            id="cedula_persona"
                            name="cedula_persona"
                            class="modern-input"
                            placeholder="Buscar por cédula..."
                            value="<?= isset($_GET['cedula_persona']) ? htmlspecialchars($_GET['cedula_persona']) : '' ?>">
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
                        <label for="condicion">Condición</label>
                        <select name="condicion" id="condicion" class="modern-select">
                            <option value="">Todas las condiciones</option>
                            <?php
                            $result_condiciones->data_seek(0);
                            while ($condicion = $result_condiciones->fetch_assoc()) {
                                $selected = (isset($_GET['condicion']) && $_GET['condicion'] == $condicion['id_condicion_cm']) ? 'selected' : '';
                            ?>
                                <option value="<?= $condicion['id_condicion_cm']; ?>" <?= $selected ?>>
                                    <?= $condicion['descripcion_condicion_cm']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-modern btn-primary">
                            <i class="bi bi-search"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <div class="modern-table-wrapper">
                <table class="modern-table" id="salesTable">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Condición</th>
                            <th>Fecha Movimiento</th>
                            <th>Observación</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include "getMovimientosCM.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add Movimiento -->
    <div class="modal fade" id="modalNewMovimiento" tabindex="-1" aria-labelledby="modalNewMovimientoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addMovimientoCM.php" method="POST">
                    <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title" id="modalNewMovimientoLabel">
                            <i class="bi bi-plus-circle-fill me-2"></i>Agregar Movimiento - Colombia Mayor
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="cedula_form" name="cedula_persona_cm" placeholder="Cédula" required autocomplete="off" autofocus>
                                <label for="cedula_form">Cédula</label>
                            </div>

                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="condicion1" name="id_condicion_cm" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php
                                    $result_condiciones->data_seek(0);
                                    while ($condicion = $result_condiciones->fetch_assoc()) { ?>
                                        <option value="<?= $condicion['id_condicion_cm']; ?>"><?= $condicion['descripcion_condicion_cm']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="condicion1">Condición C.M</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="departamento_procedencia" name="departamento_procedencia_cm">
                                    <option value="">Seleccione Departamento...</option>
                                    <option value="Risaralda" selected>Risaralda</option>
                                    <option value="Antioquia">Antioquia</option>
                                    <option value="Caldas">Caldas</option>
                                    <option value="Valle del Cauca">Valle del Cauca</option>
                                    <option value="Quindío">Quindío</option>
                                </select>
                                <label for="departamento_procedencia">Departamento de Procedencia</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="municipio_procedencia" name="municipio_procedencia_cm" placeholder="Municipio">
                                <label for="municipio_procedencia">Municipio de Procedencia</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="date" class="form-control" id="fecha_movimiento" name="fecha_movimiento_cm" placeholder="Fecha Movimiento" required>
                                <label for="fecha_movimiento">Fecha Movimiento</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <textarea class="form-control" id="observacion_movimiento" name="observaciones_cm" placeholder="Observación" style="height: 100px"></textarea>
                                <label for="observacion_movimiento">Observación</label>
                            </div>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong>Nota:</strong> Las observaciones con C.M + "Suspendido", "Fallecido" o "Retiro Voluntario" marcarán a la persona como NO activa en el programa.
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

    <!-- Modal Edit Movimiento -->
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="editMovimientoCM.php" method="POST">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" id="modalEdicionLabel">Editar Movimiento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        <input type="hidden" id="edit-id-movimiento" name="id_movimiento_cm">
                        
                        <div class="mb-3">
                            <label for="edit-cedula" class="form-label">Cédula</label>
                            <input type="text" class="form-control" id="edit-cedula" name="cedula_persona_cm" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-condicion" class="form-label">Condición</label>
                            <select class="form-select" id="edit-condicion" name="id_condicion_cm" required>
                                <option value="">Seleccione...</option>
                                <?php
                                $result_condiciones->data_seek(0);
                                while ($condicion = $result_condiciones->fetch_assoc()) { ?>
                                    <option value="<?= $condicion['id_condicion_cm']; ?>"><?= $condicion['descripcion_condicion_cm']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit-fecha-movimiento" class="form-label">Fecha Movimiento</label>
                            <input type="date" class="form-control" id="edit-fecha-movimiento" name="fecha_movimiento_cm" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit-observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="edit-observacion" name="observaciones_cm" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // DataTables removido - tabla simple para evitar errores
            console.log('Vista de movimientos CM cargada');

            // Buscar persona por cédula
            $('#cedula_form').on('blur', function() {
                var cedula = $(this).val();
                console.log('Blur en cédula, valor:', cedula);
                
                if (cedula && cedula.trim() !== '') {
                    console.log('Buscando persona con cédula:', cedula);
                    $.ajax({
                        url: 'buscarPersonaCM.php',
                        type: 'POST',
                        data: { cedula: cedula },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Respuesta del servidor:', response);
                            if (!response.encontrada) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Persona no encontrada',
                                    text: 'La cédula no está registrada en Colombia Mayor'
                                });
                            } else {
                                // Mostrar alerta cuando se encuentra la persona
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Persona encontrada',
                                    text: response.nombre_completo,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error en AJAX:', error);
                            console.error('Status:', status);
                            console.error('Response:', xhr.responseText);
                        }
                    });
                }
            });

            // Modal edición
            $('#modalEdicion').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#edit-id-movimiento').val(button.data('id'));
                modal.find('#edit-cedula').val(button.data('cedula'));
                modal.find('#edit-condicion').val(button.data('condicion'));
                modal.find('#edit-fecha-movimiento').val(button.data('fecha'));
                modal.find('#edit-observacion').val(button.data('observacion'));
            });

            // Confirmar eliminación
            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'seeMovimientosCM.php?delete=' + id;
                    }
                });
            });
        });
    </script>
</body>
</html>
