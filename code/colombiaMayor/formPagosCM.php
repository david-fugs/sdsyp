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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos Masivos - Colombia Mayor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/modern-table-styles.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2><i class="bi bi-cash-coin"></i> Pagos Masivos Colombia Mayor</h2>
                    <a href="../../access.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form id="pagoForm">
                            <div class="row g-3">
                                <!-- Información del Pago -->
                                <div class="col-md-4">
                                    <label class="form-label">Mes de Pago *</label>
                                    <select class="form-select" name="mes_pago" required>
                                        <option value="">Seleccione...</option>
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

                                <div class="col-md-4">
                                    <label class="form-label">Año de Pago *</label>
                                    <select class="form-select" name="anio_pago" required>
                                        <?php
                                        $anio_actual = date('Y');
                                        for($i = $anio_actual; $i >= $anio_actual - 5; $i--) {
                                            echo "<option value='$i'>$i</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Monto por Persona *</label>
                                    <input type="number" class="form-control" name="monto" step="0.01" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Observaciones</label>
                                    <textarea class="form-control" name="observaciones" rows="2"></textarea>
                                </div>

                                <div class="col-12">
                                    <hr>
                                    <h5><i class="bi bi-people"></i> Personas Incluidas en el Pago</h5>
                                    <p class="text-muted">Se incluirán automáticamente todas las personas activas. Puede excluir personas específicas:</p>
                                </div>

                                <!-- Excluir personas -->
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Excluir Personas del Pago</h6>
                                            <div id="exclusion-container">
                                                <div class="row g-2 mb-2 exclusion-row">
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control cedula-excluir" placeholder="Cédula">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control motivo-excluir" placeholder="Motivo de exclusión">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-success btn-sm w-100" onclick="agregarExclusion()">
                                                            <i class="bi bi-plus"></i> Agregar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="exclusiones-lista"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="button" class="btn btn-primary btn-lg" onclick="previewPago()">
                                        <i class="bi bi-eye"></i> Vista Previa del Pago
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preview -->
                <div id="preview-container" style="display:none;" class="mt-3">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="bi bi-eye"></i> Vista Previa del Pago</h5>
                        </div>
                        <div class="card-body">
                            <div id="preview-content"></div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-success btn-lg" onclick="confirmarPago()">
                                    <i class="bi bi-check-circle"></i> Confirmar y Procesar Pago
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="$('#preview-container').hide()">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let exclusiones = [];

        function agregarExclusion() {
            const cedula = $('.cedula-excluir:last').val();
            const motivo = $('.motivo-excluir:last').val();

            if(!cedula || !motivo) {
                Swal.fire('Atención', 'Ingrese cédula y motivo', 'warning');
                return;
            }

            // Verificar que la persona existe
            $.ajax({
                url: 'buscarPersonaCM.php',
                method: 'GET',
                data: { cedula: cedula },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        exclusiones.push({
                            cedula: cedula,
                            nombre: response.data.nombre + ' ' + response.data.apellido,
                            motivo: motivo
                        });
                        actualizarListaExclusiones();
                        $('.cedula-excluir:last, .motivo-excluir:last').val('');
                    } else {
                        Swal.fire('Error', 'Persona no encontrada', 'error');
                    }
                }
            });
        }

        function actualizarListaExclusiones() {
            let html = '<h6 class="mt-3">Personas Excluidas:</h6><ul class="list-group">';
            exclusiones.forEach((exc, index) => {
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><strong>${exc.cedula}</strong> - ${exc.nombre} <small class="text-muted">(${exc.motivo})</small></span>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removerExclusion(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </li>`;
            });
            html += '</ul>';
            $('#exclusiones-lista').html(html);
        }

        function removerExclusion(index) {
            exclusiones.splice(index, 1);
            actualizarListaExclusiones();
        }

        function previewPago() {
            const mes = $('[name="mes_pago"]').val();
            const anio = $('[name="anio_pago"]').val();
            const monto = $('[name="monto"]').val();

            if(!mes || !anio || !monto) {
                Swal.fire('Atención', 'Complete los campos requeridos', 'warning');
                return;
            }

            const data = {
                mes_pago: mes,
                anio_pago: anio,
                monto: monto,
                exclusiones: JSON.stringify(exclusiones),
                preview: 'true'
            };

            $.ajax({
                url: 'procesarPagoCM.php',
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        let html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="alert alert-success">
                                        <h6>Resumen del Pago</h6>
                                        <p><strong>Período:</strong> ${getNombreMes(mes)} ${anio}</p>
                                        <p><strong>Monto por persona:</strong> $${parseFloat(monto).toLocaleString()}</p>
                                        <p><strong>Total personas:</strong> ${response.total_personas}</p>
                                        <p><strong>Personas excluidas:</strong> ${response.total_excluidos}</p>
                                        <p><strong>Total a pagar:</strong> $${response.total_pago.toLocaleString()}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <h6>Criterios de Inclusión</h6>
                                        <ul>
                                            <li>Estado: ACTIVO</li>
                                            <li>No evadidos, no fallecidos, no suspendidos</li>
                                            <li>Excluyendo ${exclusiones.length} persona(s) manualmente</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#preview-content').html(html);
                        $('#preview-container').show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo generar la vista previa', 'error');
                }
            });
        }

        function confirmarPago() {
            Swal.fire({
                title: '¿Confirmar Pago?',
                text: "Se procesará el pago para todas las personas incluidas",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, procesar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = $('#pagoForm').serialize() + '&exclusiones=' + encodeURIComponent(JSON.stringify(exclusiones)) + '&preview=false';
                    
                    $.ajax({
                        url: 'procesarPagoCM.php',
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Pago Procesado',
                                    html: `<p>${response.message}</p><p>Se procesaron <strong>${response.total_personas}</strong> personas</p>`,
                                    confirmButtonText: 'Ver Consulta'
                                }).then(() => {
                                    window.location.href = 'consultaPagosCM.php';
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo procesar el pago', 'error');
                        }
                    });
                }
            });
        }

        function getNombreMes(mes) {
            const meses = {
                '01': 'Enero', '02': 'Febrero', '03': 'Marzo', '04': 'Abril',
                '05': 'Mayo', '06': 'Junio', '07': 'Julio', '08': 'Agosto',
                '09': 'Septiembre', '10': 'Octubre', '11': 'Noviembre', '12': 'Diciembre'
            };
            return meses[mes];
        }
    </script>
</body>
</html>
