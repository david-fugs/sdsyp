<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    header("location: ../../index.php");
    exit();
}

include("../../conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Pagos - Colombia Mayor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/modern-table-styles.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2><i class="bi bi-search"></i> Consulta de Pagos Colombia Mayor</h2>
                    <div>
                        <button class="btn btn-success me-2" onclick="window.location.href='exportPagosCM.php'">
                            <i class="bi bi-file-excel"></i> Exportar Excel
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
                            <div class="col-md-2">
                                <label class="form-label">Mes</label>
                                <select class="form-select" id="filtro_mes">
                                    <option value="">Todos</option>
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Año</label>
                                <select class="form-select" id="filtro_anio">
                                    <option value="">Todos</option>
                                    <?php
                                    $anio_actual = date('Y');
                                    for($i = $anio_actual; $i >= $anio_actual - 5; $i--) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="buscarHistorial()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resultados -->
                <div class="card">
                    <div class="card-body">
                        <div id="resultados-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function buscarHistorial() {
            const cedula = $('#filtro_cedula').val();
            const nombre = $('#filtro_nombre').val();
            const mes = $('#filtro_mes').val();
            const anio = $('#filtro_anio').val();

            $.ajax({
                url: 'historialPagosCM.php',
                method: 'GET',
                data: {
                    cedula: cedula,
                    nombre: nombre,
                    mes: mes,
                    anio: anio
                },
                success: function(response) {
                    $('#resultados-content').html(response);
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron cargar los resultados', 'error');
                }
            });
        }

        function marcarCobrado(idDetalle) {
            Swal.fire({
                title: '¿Marcar como cobrado?',
                text: "Se registrará la fecha actual",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, marcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'actualizarEstadoPago.php',
                        method: 'POST',
                        data: { id_detalle: idDetalle, estado: 'COBRADO' },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Éxito', 'Estado actualizado', 'success');
                                buscarHistorial();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        function marcarNoCobrado(idDetalle) {
            Swal.fire({
                title: 'Motivo de no cobro',
                input: 'textarea',
                inputPlaceholder: 'Ingrese el motivo...',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debe ingresar un motivo'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'actualizarEstadoPago.php',
                        method: 'POST',
                        data: { 
                            id_detalle: idDetalle, 
                            estado: 'NO_COBRADO',
                            motivo: result.value
                        },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Éxito', 'Estado actualizado', 'success');
                                buscarHistorial();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        $(document).ready(function() {
            // Cargar todos los pagos al iniciar
            buscarHistorial();
        });
    </script>
</body>
</html>
