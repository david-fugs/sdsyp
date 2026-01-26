<?php
session_start();
require_once('../filtros_grupos.php');
include("../../conexion.php");

// Manejo de eliminación
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_delete = intval($_GET['delete']);
    $query_delete = "DELETE FROM actividad_personalizada WHERE id_actividad_personalizada = ?";
    $stmt = $mysqli->prepare($query_delete);
    $stmt->bind_param("i", $id_delete);
    
    if ($stmt->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: 'El registro ha sido eliminado correctamente.',
                    confirmButtonColor: '#667eea'
                }).then(() => {
                    window.location.href = 'formActividadPersonalizada.php';
                });
            });
        </script>";
    }
    $stmt->close();
}

// Cargar registros existentes
$query_registros = "SELECT * FROM actividad_personalizada ORDER BY fecha_registro DESC";
$result_registros = $mysqli->query($query_registros);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDSYP - Formulario Actividad Personalizada</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- Signature Pad -->
    <style>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .signature-pad {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: white;
            cursor: crosshair;
        }

        .signature-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .clear-signature {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .form-section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .form-section h5 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .table-container {
            padding: 24px;
            background: white;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px 10px;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Signature Pad Library -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>

<body>
    <?php if (isset($_GET['delete'])) { ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // El mensaje de eliminación ya se muestra en el PHP
        });
    </script>
    <?php } ?>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #667eea; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;">
        <b><i class="bi bi-clipboard-check-fill"></i> FORMULARIO ACTIVIDAD PERSONALIZADA</b>
    </h1>

    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-clipboard-check-fill"></i> Registros de Actividades</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalFormulario">
                        <i class="bi bi-plus-circle-fill"></i>
                        Agregar Registro
                    </button>
                    <a href="exportActividadPersonalizada.php" class="btn-modern">
                        <i class="bi bi-file-excel"></i>
                        Exportar Excel
                    </a>
                </div>
            </div>

            <!-- Tabla de registros -->
            <div class="table-container">
                <table id="tablaActividades" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Actividad</th>
                            <th>Nombres y Apellidos</th>
                            <th>Documento</th>
                            <th>Género</th>
                            <th>Actividad</th>
                            <th>Total Beneficiados</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_registros && $result_registros->num_rows > 0) {
                            while ($row = $result_registros->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= $row['id_actividad_personalizada'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['fecha_actividad'])) ?></td>
                                    <td><?= htmlspecialchars($row['nombres_apellidos']) ?></td>
                                    <td><?= htmlspecialchars($row['numero_documento']) ?></td>
                                    <td><?= htmlspecialchars($row['genero']) ?></td>
                                    <td><?= htmlspecialchars($row['nombre_actividad']) ?></td>
                                    <td><?= intval($row['total_masculino']) + intval($row['total_femenino']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editarRegistro(<?= $row['id_actividad_personalizada'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="eliminarRegistro(<?= $row['id_actividad_personalizada'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Formulario -->
    <div class="modal fade" id="modalFormulario" tabindex="-1" aria-labelledby="modalFormularioLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="formActividad" action="addActividadPersonalizada.php" method="POST">
                    <input type="hidden" id="id_actividad_personalizada" name="id_actividad_personalizada" value="">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="modal-title" id="modalFormularioLabel">
                            <i class="bi bi-clipboard-plus me-2"></i>Nuevo Registro de Actividad
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        
                        <!-- SECCIÓN 1: INFORMACIÓN DE HORARIO Y FECHA -->
                        <div class="form-section">
                            <h5><i class="bi bi-clock"></i> Información de Horario y Fecha</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="hora_finalizacion" class="form-label">Hora de Finalización</label>
                                    <input type="time" class="form-control" id="hora_finalizacion" name="hora_finalizacion" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="fecha_actividad" class="form-label">Fecha de la Actividad</label>
                                    <input type="date" class="form-control" id="fecha_actividad" name="fecha_actividad" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: INFORMACIÓN PERSONAL -->
                        <div class="form-section">
                            <h5><i class="bi bi-person"></i> Información Personal</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombres_apellidos" class="form-label">Nombres y Apellidos Usuario/Líder</label>
                                    <input type="text" class="form-control" id="nombres_apellidos" name="nombres_apellidos" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="genero" class="form-label">Género</label>
                                    <select class="form-select" id="genero" name="genero" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Hombre">Hombre</option>
                                        <option value="Mujer">Mujer</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                    <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                                        <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                                        <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="numero_documento" class="form-label">Número de Documento</label>
                                    <input type="text" class="form-control" id="numero_documento" name="numero_documento" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 3: CONDICIÓN -->
                        <div class="form-section">
                            <h5><i class="bi bi-person-badge"></i> Condición</h5>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="desplazado" name="desplazado" value="1">
                                        <label class="form-check-label" for="desplazado">Desplazado</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cabeza_hogar_mujer" name="cabeza_hogar_mujer" value="1">
                                        <label class="form-check-label" for="cabeza_hogar_mujer">Mujer Cabeza de Hogar</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cabeza_hogar_hombre" name="cabeza_hogar_hombre" value="1">
                                        <label class="form-check-label" for="cabeza_hogar_hombre">Hombre Cabeza de Hogar</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="habitante_calle" name="habitante_calle" value="1">
                                        <label class="form-check-label" for="habitante_calle">Habitante de Calle y/o Riesgo</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="orientacion_sexual" class="form-label">Orientación Sexual Población</label>
                                    <select class="form-select" id="orientacion_sexual" name="orientacion_sexual">
                                        <option value="">Seleccione...</option>
                                        <option value="LGBTI">LGBTI</option>
                                        <option value="Hetero">Hetero</option>
                                        <option value="Gay">Gay</option>
                                        <option value="Lesbiana">Lesbiana</option>
                                        <option value="Bi">Bi</option>
                                        <option value="No binario">No binario</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="tipo_discapacidad" class="form-label">Tipo de Discapacidad</label>
                                    <input type="text" class="form-control" id="tipo_discapacidad" name="tipo_discapacidad" placeholder="Ninguna o especificar">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="migrante" class="form-label">Migrante</label>
                                    <select class="form-select" id="migrante" name="migrante">
                                        <option value="">Seleccione...</option>
                                        <option value="Sí">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 4: ETNIA -->
                        <div class="form-section">
                            <h5><i class="bi bi-people"></i> Etnia</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="mestizo" class="form-label">Mestizo</label>
                                    <select class="form-select" id="mestizo" name="mestizo">
                                        <option value="">Seleccione...</option>
                                        <option value="Sí">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="afrodescendiente" class="form-label">Afrodescendiente</label>
                                    <select class="form-select" id="afrodescendiente" name="afrodescendiente">
                                        <option value="">Seleccione...</option>
                                        <option value="Sí">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="indigena" class="form-label">Indígena</label>
                                    <select class="form-select" id="indigena" name="indigena">
                                        <option value="">Seleccione...</option>
                                        <option value="Sí">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 5: INFORMACIÓN ADICIONAL -->
                        <div class="form-section">
                            <h5><i class="bi bi-hospital"></i> Información Adicional</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="tipo_seguridad_salud" class="form-label">Tipo de Seguridad en Salud</label>
                                    <select class="form-select" id="tipo_seguridad_salud" name="tipo_seguridad_salud">
                                        <option value="">Seleccione...</option>
                                        <option value="Contributivo">Contributivo</option>
                                        <option value="Subsidiado">Subsidiado</option>
                                        <option value="Vinculado">Vinculado</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="condicion_ocupacional" class="form-label">Condición Ocupacional</label>
                                    <select class="form-select" id="condicion_ocupacional" name="condicion_ocupacional">
                                        <option value="">Seleccione...</option>
                                        <option value="Ama de casa">Ama de casa</option>
                                        <option value="Empleado">Empleado</option>
                                        <option value="Desempleado">Desempleado</option>
                                        <option value="Independiente">Independiente</option>
                                        <option value="Buscando trabajo">Buscando trabajo</option>
                                        <option value="Estudiante">Estudiante</option>
                                        <option value="Ninguno">Ninguno</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="nivel_estudio" class="form-label">Nivel de Estudio</label>
                                    <select class="form-select" id="nivel_estudio" name="nivel_estudio">
                                        <option value="">Seleccione...</option>
                                        <?php for ($i = 0; $i <= 12; $i++) { ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php } ?>
                                        <option value="Técnico">Técnico</option>
                                        <option value="Tecnología">Tecnología</option>
                                        <option value="Profesional">Profesional</option>
                                        <option value="Posgrado">Posgrado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="telefono_celular" class="form-label">Teléfono - Celular</label>
                                    <input type="text" class="form-control" id="telefono_celular" name="telefono_celular">
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 6: ACTIVIDAD -->
                        <div class="form-section">
                            <h5><i class="bi bi-calendar-event"></i> Información de la Actividad</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_actividad" class="form-label">Nombre Actividad</label>
                                    <input type="text" class="form-control" id="nombre_actividad" name="nombre_actividad" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="evento_tema" class="form-label">Evento/Tema o Asunto (Que mueve la meta)</label>
                                    <input type="text" class="form-control" id="evento_tema" name="evento_tema">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="numero_actividades" class="form-label">Número de Actividades Realizadas</label>
                                    <input type="number" class="form-control" id="numero_actividades" name="numero_actividades" min="1" value="1">
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 7: BENEFICIADOS -->
                        <div class="form-section">
                            <h5><i class="bi bi-people-fill"></i> Número de Personas Beneficiadas</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="total_masculino" class="form-label">Total Masculino</label>
                                    <input type="number" class="form-control" id="total_masculino" name="total_masculino" min="0" value="0" onchange="calcularTotal()">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="total_femenino" class="form-label">Total Femenino</label>
                                    <input type="number" class="form-control" id="total_femenino" name="total_femenino" min="0" value="0" onchange="calcularTotal()">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="total_general" class="form-label">Total General</label>
                                    <input type="number" class="form-control" id="total_general" name="total_general" min="0" value="0" readonly style="background-color: #e9ecef; font-weight: bold;">
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 8: FIRMA -->
                        <div class="form-section">
                            <h5><i class="bi bi-pen"></i> Firma</h5>
                            
                            <!-- Mostrar firma existente al editar -->
                            <div id="firma-existente-container" style="display: none; margin-bottom: 20px;">
                                <div style="background: #f8f9fa; border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; text-align: center;">
                                    <h6 class="mb-3" style="color: #667eea;"><i class="bi bi-check-circle"></i> Firma Actual</h6>
                                    <img id="firma-existente-img" src="" alt="Firma" style="max-width: 100%; max-height: 200px; border: 1px solid #ccc; border-radius: 5px; background: white;">
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-sm btn-info" onclick="descargarFirma()">
                                            <i class="bi bi-download"></i> Descargar Firma
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="modificarFirma()">
                                            <i class="bi bi-pencil"></i> Modificar Firma
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Canvas para nueva firma -->
                            <div class="signature-container" id="signature-container-new">
                                <button type="button" class="btn btn-sm btn-secondary clear-signature" onclick="clearSignature()">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </button>
                                <canvas id="signature-pad" class="signature-pad" width="500" height="200"></canvas>
                                <input type="hidden" id="firma_data" name="firma_data">
                            </div>
                            <small class="text-muted d-block text-center mt-2" id="firma-help-text">Firme en el recuadro usando el mouse o dedo táctil</small>
                        </div>

                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <br>
    <center><a href='../../access.php'><img src='../../img/atras.png' width='72' height='72'></a></center><br>

    <script>
        // Inicializar DataTable
        $(document).ready(function() {
            $('#tablaActividades').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                order: [[0, 'desc']]
            });
        });

        // Inicializar Signature Pad
        let signaturePad;
        const canvas = document.getElementById('signature-pad');

        function initSignaturePad() {
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
        }

        function clearSignature() {
            signaturePad.clear();
        }

        // Calcular total de beneficiados
        function calcularTotal() {
            const masculino = parseInt(document.getElementById('total_masculino').value) || 0;
            const femenino = parseInt(document.getElementById('total_femenino').value) || 0;
            document.getElementById('total_general').value = masculino + femenino;
        }

        // Inicializar signature pad cuando se abre el modal
        $('#modalFormulario').on('shown.bs.modal', function() {
            if (!signaturePad) {
                initSignaturePad();
            }
        });

        // Validar y guardar firma antes de enviar
        document.getElementById('formActividad').addEventListener('submit', function(e) {
            e.preventDefault();

            const isEditing = document.getElementById('id_actividad_personalizada').value !== '';

            // Solo requerir firma si es un nuevo registro
            if (!isEditing && signaturePad.isEmpty()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Firma requerida',
                    text: 'Por favor, firme en el recuadro antes de guardar.',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            // Guardar la firma como base64 solo si hay firma
            if (!signaturePad.isEmpty()) {
                document.getElementById('firma_data').value = signaturePad.toDataURL();
            }

            // Enviar el formulario
            this.submit();
        });

        // Variable global para almacenar la firma actual
        let firmaActual = null;

        // Función para editar registro
        function editarRegistro(id) {
            // Hacer petición AJAX para obtener los datos
            fetch('getActividadPersonalizada.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Llenar el formulario con los datos
                        document.getElementById('id_actividad_personalizada').value = data.registro.id_actividad_personalizada;
                        document.getElementById('hora_inicio').value = data.registro.hora_inicio;
                        document.getElementById('hora_finalizacion').value = data.registro.hora_finalizacion;
                        document.getElementById('fecha_actividad').value = data.registro.fecha_actividad;
                        document.getElementById('nombres_apellidos').value = data.registro.nombres_apellidos;
                        document.getElementById('genero').value = data.registro.genero;
                        document.getElementById('fecha_nacimiento').value = data.registro.fecha_nacimiento;
                        document.getElementById('tipo_documento').value = data.registro.tipo_documento;
                        document.getElementById('numero_documento').value = data.registro.numero_documento;
                        
                        // Checkboxes de condición
                        document.getElementById('desplazado').checked = data.registro.desplazado == 1;
                        document.getElementById('cabeza_hogar_mujer').checked = data.registro.cabeza_hogar_mujer == 1;
                        document.getElementById('cabeza_hogar_hombre').checked = data.registro.cabeza_hogar_hombre == 1;
                        document.getElementById('habitante_calle').checked = data.registro.habitante_calle == 1;
                        
                        // Otros campos de condición
                        document.getElementById('orientacion_sexual').value = data.registro.orientacion_sexual || '';
                        document.getElementById('tipo_discapacidad').value = data.registro.tipo_discapacidad || '';
                        document.getElementById('migrante').value = data.registro.migrante || '';
                        
                        // Etnia
                        document.getElementById('mestizo').value = data.registro.mestizo || '';
                        document.getElementById('afrodescendiente').value = data.registro.afrodescendiente || '';
                        document.getElementById('indigena').value = data.registro.indigena || '';
                        
                        // Información adicional
                        document.getElementById('tipo_seguridad_salud').value = data.registro.tipo_seguridad_salud || '';
                        document.getElementById('condicion_ocupacional').value = data.registro.condicion_ocupacional || '';
                        document.getElementById('nivel_estudio').value = data.registro.nivel_estudio || '';
                        document.getElementById('telefono_celular').value = data.registro.telefono_celular || '';
                        
                        // Actividad
                        document.getElementById('nombre_actividad').value = data.registro.nombre_actividad;
                        document.getElementById('evento_tema').value = data.registro.evento_tema || '';
                        document.getElementById('numero_actividades').value = data.registro.numero_actividades;
                        
                        // Beneficiados
                        document.getElementById('total_masculino').value = data.registro.total_masculino;
                        document.getElementById('total_femenino').value = data.registro.total_femenino;
                        calcularTotal();
                        
                        // Manejar firma existente
                        if (data.registro.firma_data) {
                            firmaActual = data.registro.firma_data;
                            document.getElementById('firma-existente-img').src = data.registro.firma_data;
                            document.getElementById('firma-existente-container').style.display = 'block';
                            document.getElementById('signature-container-new').style.display = 'none';
                            document.getElementById('firma-help-text').style.display = 'none';
                        } else {
                            firmaActual = null;
                            document.getElementById('firma-existente-container').style.display = 'none';
                            document.getElementById('signature-container-new').style.display = 'block';
                            document.getElementById('firma-help-text').style.display = 'block';
                        }
                        
                        // Cambiar título del modal
                        document.getElementById('modalFormularioLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar Registro de Actividad';
                        
                        // Abrir el modal
                        $('#modalFormulario').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar el registro',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar el registro',
                        confirmButtonColor: '#d33'
                    });
                });
        }

        // Función para descargar la firma
        function descargarFirma() {
            if (firmaActual) {
                const link = document.createElement('a');
                link.href = firmaActual;
                link.download = 'firma_actividad_' + document.getElementById('id_actividad_personalizada').value + '.png';
                link.click();
            }
        }

        // Función para modificar la firma
        function modificarFirma() {
            document.getElementById('firma-existente-container').style.display = 'none';
            document.getElementById('signature-container-new').style.display = 'block';
            document.getElementById('firma-help-text').style.display = 'block';
            if (signaturePad) {
                signaturePad.clear();
            }
        }

        // Limpiar formulario al cerrar modal
        $('#modalFormulario').on('hidden.bs.modal', function() {
            document.getElementById('formActividad').reset();
            document.getElementById('id_actividad_personalizada').value = '';
            document.getElementById('modalFormularioLabel').innerHTML = '<i class="bi bi-clipboard-plus me-2"></i>Nuevo Registro de Actividad';
            
            // Resetear firma
            firmaActual = null;
            document.getElementById('firma-existente-container').style.display = 'none';
            document.getElementById('signature-container-new').style.display = 'block';
            document.getElementById('firma-help-text').style.display = 'block';
            
            if (signaturePad) {
                signaturePad.clear();
            }
        });

        // Función para eliminar registro
        function eliminarRegistro(id) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'formActividadPersonalizada.php?delete=' + id;
                }
            });
        }
    </script>
</body>

</html>
