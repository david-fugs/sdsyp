<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDSYP - Registros Centro Vida</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- Flatpickr para el calendario -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <style>
        /* Estilos modernos personalizados */
        body {
            font-size: 16px !important;
            background-color: #f8fafc;
        }
        
        .modern-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px auto;
            max-width: 1400px;
        }

        .modern-header {
            background: linear-gradient(135deg, #e91e63 0%, #9c27b0 100%);
            color: white;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .modern-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .btn-modern {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-modern:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            color: white;
        }

        /* Botones específicos para la barra de filtros (más visibles) */
        .modern-filters .btn-modern.btn-primary {
            background: #10b981; /* verde agradable */
            border-color: #10b981;
            color: #ffffff;
            padding: 10px 16px;
            box-shadow: 0 2px 6px rgba(16,185,129,0.12);
        }

        .modern-filters .btn-modern.btn-primary:hover {
            background: #059669;
            border-color: #059669;
            transform: translateY(-2px);
        }

        .modern-filters .btn-modern.btn-secondary {
            background: #6b7280; /* gris */
            border-color: #6b7280;
            color: #fff;
            padding: 10px 14px;
        }

        .filter-row .btn-modern { display: inline-flex; align-items: center; gap: 8px; }

        /* Aumentar ancho mínimo para inputs en filtros */
        .filter-group { min-width: 180px; }

        /* Estilos para el calendario multi-selección */
        .flatpickr-calendar {
            font-size: 14px;
        }
        
        .flatpickr-day.selected {
            background: #e91e63 !important;
            border-color: #e91e63 !important;
        }
        
        .selected-dates-display {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
            min-height: 40px;
        }
        
        .date-tag {
            display: inline-block;
            background: #e91e63;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            margin: 2px;
            font-size: 12px;
        }

        /* Tabla moderna */
        .modern-table-wrapper {
            padding: 0;
            overflow-x: auto;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 14px;
        }

        .modern-table th {
            background: #f8fafc;
            color: #374151;
            font-weight: 600;
            padding: 12px 8px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
            font-size: 13px;
        }

        .modern-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            font-size: 13px;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .modern-table tr:hover {
            background: #f9fafb;
        }

        /* Filtros modernos */
        .modern-filters {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .modern-input, .modern-select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .modern-input:focus, .modern-select:focus {
            outline: none;
            border-color: #e91e63;
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
        }

        /* Botones de acción */
        .col-actions {
            width: 120px !important;
            text-align: center !important;
        }
        
        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
            align-items: center;
        }
        
        .btn-action {
            border: none;
            border-radius: 4px;
            padding: 6px 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 12px;
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

        /* Responsive */
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .modern-header {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Estilos para validación de campos */
        .form-control.is-valid {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
        }

        .form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Toast de SweetAlert */
        .swal2-toast {
            font-size: 14px !important;
        }
    </style>
</head>

<?php
include("../../conexion.php");

// Obtener condiciones
$condiciones = "SELECT * FROM condiciones_componente";
$result_condiciones = mysqli_query($mysqli, $condiciones);
if (!$result_condiciones) {
    die("Error en la consulta condiciones: " . mysqli_error($mysqli));
}

// Obtener metas
$metas = "SELECT * FROM metas ORDER BY descripcion_meta ASC";
$result_metas = mysqli_query($mysqli, $metas);
if (!$result_metas) {
    die("Error en la consulta metas: " . mysqli_error($mysqli));
}

// Obtener actividades centro vida
$actividades_cv = "SELECT id_actividad_centro_vida, descripcion_actividad FROM actividad_centro_vida ORDER BY descripcion_actividad ASC";
$result_actividades_cv_query = $mysqli->query($actividades_cv);
if (!$result_actividades_cv_query) {
    die("Error en consulta actividades centro vida: " . $mysqli->error);
}
$result_actividades_cv = $result_actividades_cv_query->fetch_all(MYSQLI_ASSOC);

// Procesar filtros
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['cedula_persona']) && !empty($_GET['cedula_persona'])) {
    $where_conditions[] = "p.cedula_persona = ?";
    $params[] = $_GET['cedula_persona'];
    $types .= "s";
}

if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
    $where_conditions[] = "(p.nombres_persona LIKE ? OR p.apellidos_persona LIKE ?)";
    $params[] = "%" . $_GET['nombre'] . "%";
    $params[] = "%" . $_GET['nombre'] . "%";
    $types .= "ss";
}

if (isset($_GET['actividad']) && !empty($_GET['actividad'])) {
    $where_conditions[] = "rcv.id_actividad_centro_vida = ?";
    $params[] = $_GET['actividad'];
    $types .= "i";
}

// Función para eliminar registro
if (isset($_GET['delete'])) {
    $id_registro = $_GET['delete'];
    deleteRegistro($id_registro);
}

function deleteRegistro($id_registro)
{
    global $mysqli;

    // Primero eliminar las fechas asociadas
    $query_fechas = "DELETE FROM registro_centro_vida_fechas WHERE id_registro_centro_vida = ?";
    $stmt_fechas = $mysqli->prepare($query_fechas);
    $stmt_fechas->bind_param("i", $id_registro);
    $stmt_fechas->execute();
    $stmt_fechas->close();

    // Luego eliminar el registro principal
    $query = "DELETE FROM registro_centro_vida WHERE id_registro_centro_vida = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_registro);

    if ($stmt->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Eliminado!',
                    text: 'El registro ha sido eliminado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#10b981'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'formCentroVida.php';
                    }
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al eliminar el registro. Inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#ef4444'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'formCentroVida.php';
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
    <h1 style="color: #e91e63; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;">
        <b><i class="bi bi-heart-fill"></i> REGISTROS ACTIVIDADES CENTRO VIDA</b>
    </h1>

    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-heart-fill"></i> Registros Centro Vida</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewRecord">
                        <i class="bi bi-plus-circle-fill"></i>
                        Agregar Registro
                    </button>
                    <a href="exportExcelCentroVida.php<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>" class="btn-modern">
                        <i class="bi bi-file-excel"></i>
                        Exportar Excel
                    </a>
                </div>
            </div>

            <!-- Filtros modernos -->
            <div class="modern-filters">
                <form action="formCentroVida.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="filter_cedula_persona">Cédula</label>
                        <input type="number"
                            id="filter_cedula_persona"
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
                        <label for="actividad">Actividad</label>
                        <select name="actividad" id="actividad" class="modern-select">
                            <option value="">Todas las actividades</option>
                            <?php foreach ($result_actividades_cv as $actividad) {
                                $selected = (isset($_GET['actividad']) && $_GET['actividad'] == $actividad['id_actividad_centro_vida']) ? 'selected' : '';
                            ?>
                                <option value="<?= $actividad['id_actividad_centro_vida']; ?>" <?= $selected ?>>
                                    <?= $actividad['descripcion_actividad']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-group" style="display:flex; gap:8px; align-items:center;">
                        <button type="submit" class="btn-modern btn-primary">
                            <i class="bi bi-search"></i>
                            Buscar
                        </button>
                        <button type="button" id="clearFilters" class="btn-modern btn-secondary">
                            <i class="bi bi-x-circle"></i>
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla moderna -->
            <div class="modern-table-wrapper">
                <table class="modern-table" id="registrosTable">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Actividad Centro Vida</th>
                            <th>Fechas Programadas</th>
                            <th>Política Pública</th>
                            <th>Departamento</th>
                            <th>Observación</th>
                            <th>Funcionario</th>
                            <th>Fecha Registro</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php include "getRegistrosCentroVida.php"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Registro -->
    <div class="modal fade" id="modalNewRecord" tabindex="-1" aria-labelledby="modalNewRecordLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="addRegistroCentroVida.php" method="POST">
                    <input type="hidden" id="id_registro_centro_vida" name="id_registro_centro_vida" value="">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewRecordLabel">
                            <i class="bi bi-heart-plus-fill me-2"></i>Agregar Registro Centro Vida
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila 1: Cédula y Condición -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="number" class="form-control" id="cedula_persona" name="cedula_persona" placeholder="Cédula" required autocomplete="off" autofocus>
                                <label for="cedula_persona">Cédula</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="id_condicion" name="id_condicion" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php while ($condicion = mysqli_fetch_assoc($result_condiciones)) { ?>
                                        <option value="<?= $condicion['id_condicion']; ?>"><?= $condicion['descripcion_condicion']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="id_condicion">Condición</label>
                            </div>
                        </div>

                        <!-- Fila 2: Meta, Actividad, Acción -->
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="id_meta" name="id_meta" required>
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php while ($meta = mysqli_fetch_assoc($result_metas)) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="id_meta">Meta</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="id_actividad" name="id_actividad" required disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                                <label for="id_actividad">Actividad</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="id_accion" name="id_accion" required disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                                <label for="id_accion">Acción</label>
                            </div>
                        </div>

                        <!-- Fila 3: Actividad Centro Vida y Política Pública -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="actividad_centro_vida" name="id_actividad_centro_vida" required>
                                    <option value="" selected>Seleccione Actividad...</option>
                                    <?php foreach ($result_actividades_cv as $actividad) { ?>
                                        <option value="<?= $actividad['id_actividad_centro_vida']; ?>"><?= $actividad['descripcion_actividad']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="actividad_centro_vida">Actividad Centro Vida</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="politica_publica" name="politica_publica">
                                    <option value="" selected>Seleccione Política Pública...</option>
                                </select>
                                <label for="politica_publica">Política Pública</label>
                            </div>
                        </div>

                        <!-- Fila 2: (removida) Actividad Realizada -->

                        <!-- Fila 5: Fechas de Atención (Calendario Multi-selección) -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="fechas_atencion" class="form-label"><strong>Fechas de Atención</strong></label>
                                <input type="text" class="form-control" id="fechas_atencion" name="fechas_atencion" 
                                       placeholder="Haga clic para seleccionar múltiples fechas..." readonly required>
                                <input type="hidden" id="fechas_seleccionadas" name="fechas_seleccionadas">
                                <div class="selected-dates-display" id="selected-dates-display">
                                    <small class="text-muted">Las fechas seleccionadas aparecerán aquí</small>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 4: Departamento de Procedencia -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="departamento_procedencia" name="departamento_procedencia" required>
                                    <option value="" selected>Seleccione Departamento...</option>
                                    <option value="Amazonas">Amazonas</option>
                                    <option value="Antioquia">Antioquia</option>
                                    <option value="Arauca">Arauca</option>
                                    <option value="Atlántico">Atlántico</option>
                                    <option value="Bolívar">Bolívar</option>
                                    <option value="Boyacá">Boyacá</option>
                                    <option value="Caldas">Caldas</option>
                                    <option value="Caquetá">Caquetá</option>
                                    <option value="Casanare">Casanare</option>
                                    <option value="Cauca">Cauca</option>
                                    <option value="Cesar">Cesar</option>
                                    <option value="Chocó">Chocó</option>
                                    <option value="Córdoba">Córdoba</option>
                                    <option value="Cundinamarca">Cundinamarca</option>
                                    <option value="Guainía">Guainía</option>
                                    <option value="Guaviare">Guaviare</option>
                                    <option value="Huila">Huila</option>
                                    <option value="La Guajira">La Guajira</option>
                                    <option value="Magdalena">Magdalena</option>
                                    <option value="Meta">Meta</option>
                                    <option value="Nariño">Nariño</option>
                                    <option value="Norte de Santander">Norte de Santander</option>
                                    <option value="Putumayo">Putumayo</option>
                                    <option value="Quindío">Quindío</option>
                                    <option value="Risaralda">Risaralda</option>
                                    <option value="San Andrés y Providencia">San Andrés y Providencia</option>
                                    <option value="Santander">Santander</option>
                                    <option value="Sucre">Sucre</option>
                                    <option value="Tolima">Tolima</option>
                                    <option value="Valle del Cauca">Valle del Cauca</option>
                                    <option value="Vaupés">Vaupés</option>
                                    <option value="Vichada">Vichada</option>
                                    <option value="Bogotá D.C.">Bogotá D.C.</option>
                                </select>
                                <label for="departamento_procedencia">Departamento de Procedencia</label>
                            </div>
                        </div>

                        <!-- Fila 5: Observación -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="observacion" name="observacion" placeholder="Observación" style="height: 120px; resize: vertical; max-height: 240px;"></textarea>
                                    <label for="observacion">Observación</label>
                                </div>
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

    <br /><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="back" /></a><br>

    <script>
        let selectedDates = [];
        
        // Función para confirmar eliminación
        function confirmarEliminacion(id, actividad) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar el registro de "${actividad}"?`,
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
            console.log('🚀 jQuery ready ejecutado');
            console.log('📦 jQuery version:', $.fn.jquery);
            console.log('🎯 SweetAlert disponible:', typeof Swal !== 'undefined');
            
            // Verificar si la tabla existe y tiene contenido antes de inicializar DataTables
            if ($('#registrosTable tbody tr').length > 0) {
                // Verificar que todas las filas tengan el mismo número de columnas
                var headerCols = $('#registrosTable thead tr th').length;
                var rowsValid = true;
                
                $('#registrosTable tbody tr').each(function() {
                    var cellCount = $(this).find('td').length;
                    if (cellCount !== headerCols) {
                        console.log('Fila con número incorrecto de columnas:', cellCount, 'vs', headerCols);
                        rowsValid = false;
                    }
                });

                if (rowsValid) {
                    // Inicializar DataTables solo si la estructura es válida
                    try {
                        const table = $('#registrosTable').DataTable({
                            pageLength: 15,
                            responsive: true,
                            order: [[10, 'desc']], // Ordenar por fecha de registro desc
                            columnDefs: [
                                { targets: [11], orderable: false, searchable: false }
                            ],
                            language: {
                                "sProcessing": "Procesando...",
                                "sLengthMenu": "Mostrar _MENU_ registros",
                                "sZeroRecords": "No se encontraron resultados",
                                "sEmptyTable": "Ningún dato disponible en esta tabla",
                                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                                "sSearch": "Buscar:",
                                "oPaginate": {
                                    "sFirst": "Primero",
                                    "sLast": "Último",
                                    "sNext": "Siguiente",
                                    "sPrevious": "Anterior"
                                }
                            }
                        });
                        console.log('DataTables inicializado correctamente');
                    } catch (error) {
                        console.error('Error al inicializar DataTables:', error);
                    }
                } else {
                    console.log('Tabla con estructura inconsistente, DataTables no inicializado');
                }
            } else {
                console.log('Tabla vacía, DataTables no inicializado');
            }

            // Inicializar Flatpickr para selección múltiple de fechas
            console.log('Inicializando Flatpickr...');
            flatpickr("#fechas_atencion", {
                mode: "multiple",
                dateFormat: "Y-m-d",
                locale: "es",
                placeholder: "Selecciona las fechas de atención...",
                allowInput: false,
                clickOpens: true,
                onReady: function() {
                    console.log('Flatpickr listo');
                },
                onChange: function(selectedDates, dateStr, instance) {
                    console.log("Fechas seleccionadas:", dateStr);
                    updateSelectedDatesDisplay(selectedDates);
                    document.getElementById('fechas_seleccionadas').value = dateStr;
                }
            });

            // Manejar selección de Meta para cargar Actividades
            $('#id_meta').on('change', function() {
                const idMeta = $(this).val();
                console.log('Meta seleccionada:', idMeta);

                // Limpiar y deshabilitar campos dependientes
                $('#id_actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');

                if (idMeta) {
                    console.log('Cargando actividades para meta:', idMeta);
                    $.ajax({
                        url: '../personMovement/getActividades.php',
                        type: 'POST',
                        data: {
                            id_meta: idMeta
                        },
                        success: function(response) {
                            console.log('Actividades cargadas:', response);
                            $('#id_actividad').append(response).prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar actividades:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al cargar las actividades: ' + error,
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });

            // Manejar selección de Actividad para cargar Acciones
            $('#id_actividad').on('change', function() {
                const idActividad = $(this).val();
                // Limpiar y deshabilitar campo de acciones
                $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                
                if (idActividad) {
                    $.ajax({
                        url: '../personMovement/getAcciones.php',
                        type: 'POST',
                        data: {
                            id_actividad: idActividad
                        },
                        success: function(response) {
                            $('#id_accion').append(response).prop('disabled', false);
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al cargar las acciones',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });

            // Manejar selección de Acción para consultar políticas públicas
            $('#id_accion').on('change', function() {
                const idAccion = $(this).val();
                console.log('🏛️ Cargando políticas públicas para acción:', idAccion);
                
                // Limpiar el select de política pública
                $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                
                if (idAccion) {
                    $.ajax({
                        url: '../personMovement/getPoliticaPublica.php',
                        type: 'POST',
                        data: { id_accion: idAccion },
                        dataType: 'json',
                        success: function(response) {
                            console.log('📋 Políticas públicas recibidas:', response);
                            
                            if (response && response.politicas && response.politicas.length > 0) {
                                // Agregar cada política pública como opción
                                response.politicas.forEach(function(p) {
                                    $('#politica_publica').append('<option value="' + p.descripcion_politica + '">' + p.descripcion_politica + '</option>');
                                });
                                console.log('✅ Se agregaron', response.politicas.length, 'políticas públicas');
                            } else {
                                $('#politica_publica').append('<option value="No asignada">No asignada</option>');
                                console.log('⚠️ No se encontraron políticas públicas');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ Error al cargar políticas públicas:', error);
                            $('#politica_publica').append('<option value="Error al consultar">Error al consultar</option>');
                        }
                    });
                }
            });

            // Validación del formulario con AJAX
            $('#modalNewRecord form').on('submit', function(e) {
                console.log('🚀 Evento submit disparado');
                
                // Siempre prevenir el submit normal para usar AJAX
                e.preventDefault();
                
                // Buscar la cédula dentro del formulario (evita conflicto con filtros)
                const cedula = $(this).find('#cedula_persona').val() ? $(this).find('#cedula_persona').val().trim() : '';
                console.log('👤 Cédula ingresada:', cedula);
                console.log('📅 Fechas seleccionadas:', selectedDates.length);
                
                // Verificar que la cédula no esté vacía
                if (!cedula || cedula.length === 0) {
                    console.log('❌ Formulario bloqueado: Cédula vacía');
                    Swal.fire({
                        title: 'Cédula Requerida',
                        text: 'Debe ingresar una cédula.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    $(this).find('#cedula_persona').focus();
                    return false;
                }

                // Verificar que se hayan seleccionado fechas
                if (selectedDates.length === 0) {
                    console.log('❌ Formulario bloqueado: Sin fechas');
                    Swal.fire({
                        title: 'Fechas Requeridas',
                        text: 'Debe seleccionar al menos una fecha de atención.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    $('#fechas_atencion').focus();
                    return false;
                }
                
                console.log('✅ Validación pasada, enviando formulario por AJAX...');
                
                // Preparar datos del formulario
                let formData = $(this).serialize();
                
                // Agregar las fechas seleccionadas
                formData += '&fechas_atencion=' + encodeURIComponent(JSON.stringify(selectedDates));
                
                console.log('📋 Datos del formulario completos:', formData);
                
                // Enviar por AJAX (elegir endpoint según modo: add o edit)
                const endpointUrl = $('#id_registro_centro_vida').val() ? 'editRegistroCentroVida.php' : 'addRegistroCentroVida.php';
                // Enviar por AJAX
                $.ajax({
                    url: endpointUrl,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('📤 Enviando solicitud AJAX...');
                        // Deshabilitar botón de envío
                        const $submitBtn = $('#modalNewRecord button[type="submit"]');
                        $submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Guardando...');
                    },
                    success: function(response) {
                        console.log('✅ Respuesta AJAX exitosa:', response);
                        
                        if (response.success) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: response.message || 'Registro guardado correctamente',
                                icon: 'success',
                                timer: 2000,
                                position: 'top-end',
                                toast: true,
                                showConfirmButton: false
                            });
                            
                            // Cerrar modal y limpiar formulario
                            $('#modalNewRecord').modal('hide');
                            $('#modalNewRecord form')[0].reset();
                            selectedDates = [];
                            $('#fechas_atencion').val('');
                            
                            // Recargar la página para mostrar el nuevo registro
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                            
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Error al guardar el registro',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('❌ Error AJAX:', {xhr, status, error});
                        console.log('📄 Texto de respuesta:', xhr.responseText);
                        
                        let errorMessage = 'No se pudo conectar con el servidor.';
                        
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            errorMessage = errorResponse.message || errorMessage;
                        } catch (e) {
                            // Si no es JSON válido, usar mensaje genérico
                        }
                        
                        Swal.fire({
                            title: 'Error de Conexión',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    },
                    complete: function() {
                        console.log('🏁 Solicitud AJAX completada');
                        // Rehabilitar botón
                        const $submitBtn = $('#modalNewRecord button[type="submit"]');
                        $submitBtn.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar');
                    }
                });
                
                return false;
            });

            // Evento específico para el botón guardar
            $('#modalNewRecord button[type="submit"]').on('click', function(e) {
                console.log('🖱️ Botón Guardar clickeado');
                console.log('🎯 Formulario padre:', $(this).closest('form').length);
            });

            console.log('✅ Inicialización completada - JavaScript listo');

            // Validación de cédula en tiempo real y precarga de Meta/Actividad/Acción/Política Pública
            let cedulaValida = false;

            // Usar delegación de eventos para asegurar que funcione con elementos dinámicos
            $('#modalNewRecord').on('blur', '#cedula_persona', function() {
                const cedula = $(this).val().trim();
                console.log('🔍 Buscando persona por cédula (centro vida):', cedula);

                if (!cedula) {
                    // Campo vacío
                    $('#cedula_persona').removeClass('is-valid is-invalid is-loading');
                    cedulaValida = false;
                    return;
                }

                // Limpiar clases anteriores
                $(this).removeClass('is-valid is-invalid is-loading');

                $.ajax({
                    url: '../buscar_persona.php',
                    type: 'POST',
                    data: { cedula: cedula },
                    dataType: 'json',
                    success: function(response) {
                        console.log('✅ Respuesta buscar_persona:', response);

                        if (response.encontrado) {
                            if (response.fallecido) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Persona fallecida',
                                    text: 'Esta persona ha fallecido. No es posible registrar.',
                                    confirmButtonText: 'OK'
                                }).then(function() {
                                    $('#cedula_persona').val('').focus();
                                    // Limpiar selects dependientes
                                    $('#id_meta').val('');
                                    $('#id_actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                                    $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                                    $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                                });
                                return;
                            }

                            // Marcar cédula válida
                            $('#cedula_persona').removeClass('is-invalid').addClass('is-valid');
                            cedulaValida = true;

                            // Mensaje corto
                            Swal.fire({
                                icon: 'success',
                                title: 'Persona encontrada',
                                text: 'Nombre: ' + response.nombres + ' ' + response.apellidos,
                                timer: 1500,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });

                            // Precargar Meta, Actividad, Acción y Política Pública si existen en la tabla personas
                            if (response.id_meta) {
                                $('#id_meta').val(response.id_meta);
                                // Cargar actividades para esta meta
                                $.ajax({
                                    url: '../personMovement/getActividades.php',
                                    type: 'POST',
                                    data: { id_meta: response.id_meta },
                                    success: function(actividadesResponse) {
                                        $('#id_actividad').empty().append('<option value="">Seleccione Actividad...</option>');
                                        $('#id_actividad').append(actividadesResponse).prop('disabled', false);

                                        if (response.id_actividad) {
                                            $('#id_actividad').val(response.id_actividad);
                                            // Cargar acciones para esta actividad
                                            $.ajax({
                                                url: '../personMovement/getAcciones.php',
                                                type: 'POST',
                                                data: { id_actividad: response.id_actividad },
                                                success: function(accionesResponse) {
                                                    $('#id_accion').empty().append('<option value="">Seleccione Acción...</option>');
                                                    $('#id_accion').append(accionesResponse).prop('disabled', false);

                                                    if (response.id_accion) {
                                                        $('#id_accion').val(response.id_accion);
                                                        // Cargar políticas públicas para esta acción
                                                        $.ajax({
                                                            url: '../personMovement/getPoliticaPublica.php',
                                                            type: 'POST',
                                                            data: { id_accion: response.id_accion },
                                                            dataType: 'json',
                                                            success: function(politicasResponse) {
                                                                $('#politica_publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                                                                if (politicasResponse && politicasResponse.politicas && politicasResponse.politicas.length > 0) {
                                                                    politicasResponse.politicas.forEach(function(p) {
                                                                        $('#politica_publica').append('<option value="' + p.descripcion_politica + '">' + p.descripcion_politica + '</option>');
                                                                    });
                                                                    // Seleccionar la opción si existe en personas
                                                                    if (response.id_politica_publica) {
                                                                        $('#politica_publica').val(response.id_politica_publica);
                                                                    }
                                                                } else {
                                                                    $('#politica_publica').append('<option value="">No asignada</option>');
                                                                }
                                                            },
                                                            error: function() {
                                                                $('#politica_publica').append('<option value="">Error al consultar</option>');
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        } else {
                            $('#cedula_persona').removeClass('is-valid').addClass('is-invalid');
                            cedulaValida = false;
                            Swal.fire({
                                icon: 'error',
                                title: 'Persona no encontrada',
                                text: 'No se encontró ninguna persona con esa cédula.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Error al buscar persona:', error, xhr.responseText);
                        $('#cedula_persona').removeClass('is-valid').addClass('is-invalid');
                        cedulaValida = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al buscar persona. ' + error,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Resetear validación cuando el usuario empiece a escribir
            $('#modalNewRecord').on('input', '#cedula_persona', function() {
                console.log('✏️ Usuario escribiendo en cédula (modal)');
                $(this).removeClass('is-valid is-invalid');
                cedulaValida = false;
            });

            // Limpiar formulario cuando se cierre el modal
            $('#modalNewRecord').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset();
                $('#cedula_persona').removeClass('is-valid is-invalid');
                $('#id_actividad, #id_accion').prop('disabled', true).html('<option value="">Seleccione...</option>');
                $('#politica_publica').html('<option value="" selected>Seleccione Política Pública...</option>');
                $('#selected-dates-display').html('<small class="text-muted">Las fechas seleccionadas aparecerán aquí</small>');
                selectedDates = [];
                cedulaValida = false;
                // Asegurar que el formulario vuelva a modo 'agregar' limpiando el id de edición
                $('#id_registro_centro_vida').val('');
                
                // Limpiar flatpickr
                if (document.querySelector("#fechas_atencion")._flatpickr) {
                    document.querySelector("#fechas_atencion")._flatpickr.clear();
                }
            });

            // Verificar cuando se abra el modal
            $('#modalNewRecord').on('shown.bs.modal', function() {
                console.log('🎯 Modal abierto - Verificando elemento cédula:', $('#cedula_persona').length);
                if ($('#cedula_persona').length === 0) {
                    console.error('❌ Elemento #cedula_persona no encontrado');
                } else {
                    console.log('✅ Elemento #cedula_persona encontrado');
                    // Solo hacer focus si no está en modo readonly (edición)
                    if (!$('#cedula_persona').prop('readonly')) {
                        $('#cedula_persona').focus();
                    }
                }
            });

            // Evento para limpiar modal al hacer clic en "Agregar Registro"
            $('button[data-bs-target="#modalNewRecord"]').on('click', function() {
                console.log('🆕 Abriendo modal en modo agregar');
                resetModalToAddMode();
            });

            // Evento para resetear el modal cuando se cierre
            $('#modalNewRecord').on('hidden.bs.modal', function () {
                console.log('🔄 Modal cerrado - reseteando a modo agregar');
                resetModalToAddMode();
                $('#modalNewRecord form')[0].reset();
                selectedDates = [];
                updateSelectedDatesDisplay([]);
            });

            // Botón limpiar filtros
            $('#clearFilters').on('click', function() {
                // Limpiar inputs del formulario de filtros
                $('#filter_cedula_persona').val('');
                $('#nombre').val('');
                $('#actividad').val('');
                // Enviar formulario con parámetros vacíos (redirecciona a la misma página sin GET)
                window.location = 'formCentroVida.php';
            });
        });

        function updateSelectedDatesDisplay(dates) {
            selectedDates = dates;
            const display = document.getElementById('selected-dates-display');
            
            if (dates.length === 0) {
                display.innerHTML = '<small class="text-muted">Las fechas seleccionadas aparecerán aquí</small>';
                return;
            }

            let html = '';
            dates.forEach(date => {
                const formattedDate = new Date(date).toLocaleDateString('es-ES', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                html += `<span class="date-tag">${formattedDate}</span>`;
            });
            
            display.innerHTML = html;
        }

        // Función global para abrir el modal en modo edición y precargar datos
        window.editarRegistro = function(id) {
            console.log('✏️ editarRegistro llamado con id:', id);
            if (!id) return;

            // Limpiar estado previo
            $('#modalNewRecord').find('form')[0].reset();
            $('#id_registro_centro_vida').val('');
            $('#cedula_persona').removeClass('is-valid is-invalid');
            selectedDates = [];

            $.ajax({
                url: 'getRegistroByIdCentroVida.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(resp) {
                    if (!resp.success) {
                        Swal.fire('Error', resp.message || 'No se encontró el registro', 'error');
                        return;
                    }

                    const d = resp.data;
                    console.log('Datos para edición:', d, resp.fechas);

                    // Poblar campos básicos
                    $('#id_registro_centro_vida').val(d.id_registro_centro_vida || id);
                    $('#cedula_persona').val(d.cedula_persona).addClass('is-valid').prop('readonly', true);
                    $('#id_condicion').val(d.id_condicion);
                    
                    // Cambiar título del modal para modo edición
                    $('#modalNewRecordLabel').html('<i class="bi bi-pencil-fill me-2"></i>Editar Registro Centro Vida');
                    
                    // Manejar cascada Meta -> Actividad -> Acción
                    if (d.id_meta) {
                        $('#id_meta').val(d.id_meta).trigger('change');
                        
                        // Esperar a que se carguen las actividades y luego seleccionar
                        setTimeout(function() {
                            if (d.id_actividad) {
                                $('#id_actividad').val(d.id_actividad).prop('disabled', false).trigger('change');
                                
                                // Esperar a que se carguen las acciones y luego seleccionar
                                setTimeout(function() {
                                    if (d.id_accion) {
                                        $('#id_accion').val(d.id_accion).prop('disabled', false).trigger('change');
                                        
                                        // Esperar a que se carguen las políticas públicas y luego seleccionar
                                        setTimeout(function() {
                                            if (d.politica_publica) {
                                                $('#politica_publica').val(d.politica_publica);
                                            }
                                        }, 300);
                                    }
                                }, 300);
                            }
                        }, 300);
                    }

                    // Campos específicos de centro vida
                    if (d.id_actividad_centro_vida) {
                        $('#actividad_centro_vida').val(d.id_actividad_centro_vida);
                    }
                    $('#departamento_procedencia').val(d.departamento_procedencia);
                    $('#observacion').val(d.observacion);

                    // Fechas
                    if (Array.isArray(resp.fechas) && resp.fechas.length) {
                        selectedDates = resp.fechas;
                        try {
                            const fp = document.querySelector('#fechas_atencion')._flatpickr;
                            if (fp) {
                                fp.setDate(selectedDates, true);
                            }
                        } catch (e) {
                            console.warn('Flatpickr no disponible:', e);
                        }
                        updateSelectedDatesDisplay(selectedDates);
                        document.getElementById('fechas_seleccionadas').value = selectedDates.join(',');
                    }

                    // Abrir modal
                    $('#modalNewRecord').modal('show');
                },
                error: function(xhr, status, err) {
                    console.error('Error AJAX editarRegistro:', err, xhr.responseText);
                    Swal.fire('Error', 'No se pudo cargar el registro para edición', 'error');
                }
            });
        };

        // Función para restaurar el modal a modo "agregar"
        window.resetModalToAddMode = function() {
            $('#modalNewRecordLabel').html('<i class="bi bi-heart-plus-fill me-2"></i>Agregar Registro Centro Vida');
            $('#id_registro_centro_vida').val('');
            $('#cedula_persona').prop('readonly', false).removeClass('is-valid is-invalid');
            selectedDates = [];
            updateSelectedDatesDisplay([]);
        };
    </script>
</body>
</html>
