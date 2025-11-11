<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    header("location: ../../index.php");
    exit();
}

include("../../conexion.php");

$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$nombre_usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros Individuales - Colombia Mayor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/modern-table-styles.css">
    <style>
        .form-floating > .form-control, .form-floating > .form-select {
            height: calc(3.5rem + 2px);
            line-height: 1.25;
        }
        .form-floating > label {
            padding: 1rem 0.75rem;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .search-persona {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2><i class="bi bi-clipboard-data"></i> Registros Individuales Colombia Mayor</h2>
                    <div>
                        <button class="btn btn-success me-2" onclick="window.location.href='exportRegistrosCM.php'">
                            <i class="bi bi-file-excel"></i> Exportar Excel
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle"></i> Nuevo Registro
                        </button>
                        <a href="../../access.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Buscar por Cédula</label>
                                <input type="text" class="form-control" id="filtro_cedula" placeholder="Ingrese cédula...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar por Nombre</label>
                                <input type="text" class="form-control" id="filtro_nombre" placeholder="Ingrese nombre...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Condición</label>
                                    <select class="form-select" name="id_condicion" id="filtro_condicion">
                                    <option value="">Todas</option>
                                    <?php
                                    $sql_condiciones = "SELECT id_condicion as id_condicion_cm, descripcion_condicion as descripcion_condicion_cm FROM condiciones_componente WHERE descripcion_condicion LIKE 'C.M%' ORDER BY descripcion_condicion";
                                    $result_condiciones = $mysqli->query($sql_condiciones);
                                    while($cond = $result_condiciones->fetch_assoc()) {
                                        echo '<option value="'.$cond['id_condicion_cm'].'">'.$cond['descripcion_condicion_cm'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="loadRegistros()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de registros -->
                <div class="card">
                    <div class="card-body">
                        <div id="registros-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nuevo Registro Individual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addForm">
                    <div class="modal-body">
                        <!-- Buscar Persona -->
                        <div class="search-persona">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="buscar_cedula" placeholder="Cédula">
                                        <label>Cédula de la Persona</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary w-100 h-100" onclick="buscarPersona()">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                            <div id="persona_info" class="mt-2"></div>
                        </div>

                        <input type="hidden" name="id_persona" id="add_id_persona" required>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <select class="form-select" name="id_condicion" id="add_id_condicion" required>
                                        <option value="">Seleccione...</option>
                                        <?php
                                        $sql_cond = "SELECT id_condicion as id_condicion_cm, descripcion_condicion as descripcion_condicion_cm FROM condiciones_componente WHERE descripcion_condicion LIKE 'C.M%' ORDER BY descripcion_condicion";
                                        $result_cond = $mysqli->query($sql_cond);
                                        while($c = $result_cond->fetch_assoc()) {
                                            echo '<option value="'.$c['id_condicion_cm'].'">'.$c['descripcion_condicion_cm'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <label>Condición *</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="id_meta" id="add_id_meta" required>
                                        <option value="">Seleccione...</option>
                                        <?php
                                        $sql_metas = "SELECT id_meta, descripcion_meta FROM metas ORDER BY descripcion_meta";
                                        $result_metas = $mysqli->query($sql_metas);
                                        while($meta = $result_metas->fetch_assoc()) {
                                            echo '<option value="'.$meta['id_meta'].'">'.$meta['descripcion_meta'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <label>Meta *</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="id_actividad" id="add_id_actividad" required>
                                        <option value="">Primero seleccione una meta</option>
                                    </select>
                                    <label>Actividad *</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <select class="form-select" name="id_accion" id="add_id_accion" required>
                                        <option value="">Primero seleccione una actividad</option>
                                    </select>
                                    <label>Acción *</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" name="fecha_registro" id="add_fecha_registro" required>
                                    <label>Fecha de Registro *</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea class="form-control" name="observaciones" id="add_observaciones" style="height: 100px"></textarea>
                                    <label>Observaciones</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Registro</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="id_persona" id="edit_id_persona">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Persona:</strong> <span id="edit_persona_nombre"></span><br>
                            <strong>Cédula:</strong> <span id="edit_persona_cedula"></span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <select class="form-select" name="id_condicion" id="edit_id_condicion" required>
                                        <option value="">Seleccione...</option>
                                        <?php
                                        $result_cond->data_seek(0);
                                        while($c = $result_cond->fetch_assoc()) {
                                            echo '<option value="'.$c['id'].'">'.$c['descripcion'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <label>Condición *</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="id_meta" id="edit_id_meta" required>
                                        <option value="">Seleccione...</option>
                                        <?php
                                        $result_metas->data_seek(0);
                                        while($meta = $result_metas->fetch_assoc()) {
                                            echo '<option value="'.$meta['id'].'">'.$meta['descripcion'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <label>Meta *</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="id_actividad" id="edit_id_actividad" required>
                                        <option value="">Primero seleccione una meta</option>
                                    </select>
                                    <label>Actividad *</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <select class="form-select" name="id_accion" id="edit_id_accion" required>
                                        <option value="">Primero seleccione una actividad</option>
                                    </select>
                                    <label>Acción *</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" name="fecha_registro" id="edit_fecha_registro" required>
                                    <label>Fecha de Registro *</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea class="form-control" name="observaciones" id="edit_observaciones" style="height: 100px"></textarea>
                                    <label>Observaciones</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Cargar registros
        function loadRegistros() {
            const cedula = $('#filtro_cedula').val();
            const nombre = $('#filtro_nombre').val();
            const condicion = $('#filtro_condicion').val();

            $.ajax({
                url: 'getRegistrosCM.php',
                method: 'GET',
                data: {
                    cedula: cedula,
                    nombre: nombre,
                    condicion: condicion
                },
                success: function(response) {
                    $('#registros-content').html(response);
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron cargar los registros', 'error');
                }
            });
        }

        // Buscar persona por cédula
        function buscarPersona() {
            const cedula = $('#buscar_cedula').val();
            
            if(!cedula) {
                Swal.fire('Atención', 'Ingrese una cédula', 'warning');
                return;
            }

            $.ajax({
                url: 'buscarPersonaCM.php',
                method: 'GET',
                data: { cedula: cedula },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#add_id_persona').val(response.data.id);
                        $('#persona_info').html(`
                            <div class="alert alert-success">
                                <strong>Persona encontrada:</strong><br>
                                <strong>Nombre:</strong> ${response.data.nombre} ${response.data.apellido}<br>
                                <strong>Edad:</strong> ${response.data.edad} años<br>
                                <strong>Estado:</strong> ${response.data.estado_cm}
                            </div>
                        `);
                    } else {
                        $('#persona_info').html(`
                            <div class="alert alert-danger">
                                ${response.message}
                            </div>
                        `);
                        $('#add_id_persona').val('');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo buscar la persona', 'error');
                }
            });
        }

        // Agregar registro
        $('#addForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'addRegistroCM.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#addModal').modal('hide');
                        $('#addForm')[0].reset();
                        $('#persona_info').html('');
                        loadRegistros();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo guardar el registro', 'error');
                }
            });
        });

        // Editar registro (llamado desde getRegistrosCM.php)
        function editarRegistro(id) {
            $.ajax({
                url: 'getRegistroCM.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        const data = response.data;
                        $('#edit_id').val(data.id);
                        $('#edit_id_persona').val(data.id_persona);
                        $('#edit_persona_nombre').text(data.persona_nombre);
                        $('#edit_persona_cedula').text(data.persona_cedula);
                        $('#edit_id_condicion').val(data.id_condicion);
                        $('#edit_id_meta').val(data.id_meta);
                        
                        // Cargar actividades
                        cargarActividades('edit', data.id_meta, data.id_actividad);
                        
                        // Cargar acciones
                        setTimeout(function() {
                            cargarAcciones('edit', data.id_actividad, data.id_accion);
                        }, 500);
                        
                        $('#edit_fecha_registro').val(data.fecha_registro);
                        $('#edit_observaciones').val(data.observaciones);
                        
                        $('#editModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo cargar el registro', 'error');
                }
            });
        }

        // Actualizar registro
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'editRegistroCM.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#editModal').modal('hide');
                        loadRegistros();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo actualizar el registro', 'error');
                }
            });
        });

        // Eliminar registro
        function eliminarRegistro(id) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'deleteRegistroCM.php',
                        method: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Eliminado', response.message, 'success');
                                loadRegistros();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo eliminar el registro', 'error');
                        }
                    });
                }
            });
        }

        // Cargar actividades por meta
        function cargarActividades(prefix, idMeta, selectedId = null) {
            if(!idMeta) {
                $(`#${prefix}_id_actividad`).html('<option value="">Primero seleccione una meta</option>');
                return;
            }

            $.ajax({
                url: '../activities/getActivity.php',
                method: 'GET',
                data: { id_meta: idMeta },
                dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Seleccione...</option>';
                    response.forEach(function(act) {
                        const selected = selectedId && act.id == selectedId ? 'selected' : '';
                        options += `<option value="${act.id}" ${selected}>${act.descripcion}</option>`;
                    });
                    $(`#${prefix}_id_actividad`).html(options);
                }
            });
        }

        // Cargar acciones por actividad
        function cargarAcciones(prefix, idActividad, selectedId = null) {
            if(!idActividad) {
                $(`#${prefix}_id_accion`).html('<option value="">Primero seleccione una actividad</option>');
                return;
            }

            $.ajax({
                url: '../action/getActions.php',
                method: 'GET',
                data: { id_actividad: idActividad },
                dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Seleccione...</option>';
                    response.forEach(function(acc) {
                        const selected = selectedId && acc.id == selectedId ? 'selected' : '';
                        options += `<option value="${acc.id}" ${selected}>${acc.descripcion}</option>`;
                    });
                    $(`#${prefix}_id_accion`).html(options);
                }
            });
        }

        // Event listeners para cambios en selects
        $('#add_id_meta').on('change', function() {
            cargarActividades('add', $(this).val());
            $('#add_id_accion').html('<option value="">Primero seleccione una actividad</option>');
        });

        $('#add_id_actividad').on('change', function() {
            cargarAcciones('add', $(this).val());
        });

        $('#edit_id_meta').on('change', function() {
            cargarActividades('edit', $(this).val());
            $('#edit_id_accion').html('<option value="">Primero seleccione una actividad</option>');
        });

        $('#edit_id_actividad').on('change', function() {
            cargarAcciones('edit', $(this).val());
        });

        // Limpiar modal al cerrar
        $('#addModal').on('hidden.bs.modal', function() {
            $('#addForm')[0].reset();
            $('#persona_info').html('');
        });

        // Cargar registros al iniciar
        $(document).ready(function() {
            loadRegistros();
            
            // Establecer fecha actual en campos de fecha
            const today = new Date().toISOString().split('T')[0];
            $('#add_fecha_registro').val(today);
        });
    </script>
</body>
</html>
