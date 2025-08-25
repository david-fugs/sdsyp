<?php
session_start();
include("../../conexion.php");

// Cargar listas necesarias
$metas = $mysqli->query("SELECT * FROM metas ORDER BY descripcion_meta ASC");
$actividades_cv = $mysqli->query("SELECT id_actividad_centro_vida, descripcion_actividad FROM actividad_centro_vida ORDER BY descripcion_actividad ASC");
$usuarios = $mysqli->query("SELECT id, nombre FROM usuarios WHERE tipo_usuario = 3 ORDER BY nombre ASC");
$grupos = $mysqli->query("SELECT * FROM grupos ORDER BY descripcion_grupo ASC");
$comunas = $mysqli->query("SELECT * FROM comunas ORDER BY nombre_com ASC");

// Filtros
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_mes = isset($_GET['filtro_mes']) ? intval($_GET['filtro_mes']) : '';
$filtro_funcionario = isset($_GET['filtro_funcionario']) ? intval($_GET['filtro_funcionario']) : '';
$filtro_tipo_registro = isset($_GET['filtro_tipo_registro']) ? $mysqli->real_escape_string($_GET['filtro_tipo_registro']) : '';
$where = '';
// Eliminación (GET ?delete=ID)
if (isset($_GET['delete'])) {
    $del = intval($_GET['delete']);
    if ($del > 0) {
        $stmtDel = $mysqli->prepare("DELETE FROM masiva_centro_vida WHERE id_masiva_centro_vida=?");
        if ($stmtDel) {
            $stmtDel->bind_param('i', $del);
            $stmtDel->execute();
            $stmtDel->close();
        }
        echo "<script>window.location='formMasivoCentroVida.php';</script>";
        exit;
    }
}
if ($filtro_anio) {
    $where .= " AND YEAR(ra.fecha_atencion) = $filtro_anio";
}
if ($filtro_mes) {
    $where .= " AND MONTH(ra.fecha_atencion) = $filtro_mes";
}
if ($filtro_funcionario) {
    $where .= " AND ra.id_usuario = $filtro_funcionario";
}
if ($filtro_tipo_registro) {
    // filtrar por tipo_registro en la tabla masiva
    $where .= " AND mcv.tipo_registro = '" . $mysqli->real_escape_string($filtro_tipo_registro) . "'";
}

// Consulta principal (nueva tabla masiva_centro_vida). Alias de id para compatibilidad visual
$query = "SELECT 
    mcv.id_masiva_centro_vida AS id_registro,
    mcv.id_meta,
    mcv.id_actividad,
    mcv.id_accion,
    mcv.politica_publica,
    mcv.id_centro_vida,
    mcv.id_comuna,
    mcv.id_actividad_centro_vida,
    mcv.funcionario_responsable,
    mcv.tipo_registro,
    m.descripcion_meta,
    a.descripcion_actividad,
    ac.descripcion_accion,
    pp.descripcion_politica,
    g.descripcion_grupo AS centro_vida,
    mcv.fecha_atencion,
    mcv.nombre_lider,
    mcv.telefono_contacto,
    c.nombre_com AS nombre_comuna,
    mcv.medio_verificacion,
    mcv.cantidad_masculino,
    mcv.cantidad_femenino,
    mcv.observacion_actividad,
    u1.nombre AS digitado_por,
    u2.nombre AS funcionario_responsable_nombre,
    acv.descripcion_actividad AS actividad_centro_vida
FROM masiva_centro_vida mcv
LEFT JOIN metas m ON mcv.id_meta = m.id_meta
LEFT JOIN actividades a ON mcv.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON mcv.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON mcv.politica_publica = pp.id_politica
LEFT JOIN grupos g ON mcv.id_centro_vida = g.id_grupo
LEFT JOIN comunas c ON mcv.id_comuna = c.id_com
LEFT JOIN usuarios u1 ON mcv.id_usuario = u1.id
LEFT JOIN usuarios u2 ON mcv.funcionario_responsable = u2.id
LEFT JOIN actividad_centro_vida acv ON mcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
WHERE 1 $where
ORDER BY mcv.fecha_atencion DESC";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>SDSYP - Actividades Centro Vida (Masivas)</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-size: 16px;
            background: #f8fafc
        }

        .modern-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
            overflow: hidden;
            margin: 20px auto;
            max-width: 1400px
        }

        .modern-header {
            background: linear-gradient(135deg, #e91e63, #9c27b0);
            color: #fff;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px
        }

        .modern-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600
        }

        .btn-modern {
            background: rgba(255, 255, 255, .2);
            border: 2px solid rgba(255, 255, 255, .3);
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: .3s
        }

        .btn-modern:hover {
            background: rgba(255, 255, 255, .3);
            border-color: rgba(255, 255, 255, .5);
            transform: translateY(-2px);
            color: #fff
        }

        .modern-filters {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px
        }

        .modern-input,
        .modern-select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px
        }

        .modern-table-wrapper {
            overflow-x: auto
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px
        }

        .modern-table th {
            background: #f8fafc;
            color: #374151;
            font-weight: 600;
            padding: 10px 8px;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap
        }

        .modern-table td {
            padding: 8px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            white-space: nowrap;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center
        }

        .btn-action {
            border: none;
            border-radius: 4px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: .2s
        }

        .btn-edit {
            background: #3b82f6;
            color: #fff
        }

        .btn-edit:hover {
            background: #2563eb
        }

        .btn-delete {
            background: #ef4444;
            color: #fff
        }

        .btn-delete:hover {
            background: #dc2626
        }
    </style>
</head>

<body>
    <center style="margin-top:20px"><img src='../../img/logo.png' width="150" height="120" /></center>
    <h1 style="color:#e91e63;text-shadow:#fff 0.1em 0.1em 0.2em;font-size:44px;text-align:center;font-weight:bold"><i class="bi bi-heart-fill"></i> ACTIVIDADES MASIVAS CENTRO VIDA</h1>
    <div class="container mt-5">
        <div class="modern-container">
            <div class="modern-header">
                <h2><i class="bi bi-heart-fill"></i> Actividades Realizadas</h2>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button type="button" class="btn-modern" data-bs-toggle="modal" data-bs-target="#modalAdd"><i class="bi bi-plus-circle-fill"></i> Agregar</button>
                    <form id="exportForm" action="exportActividadesExcelCentroVidaMasivo.php" method="get" style="display:inline;">
                        <input type="hidden" name="filtro_anio" id="export_filtro_anio">
                        <input type="hidden" name="filtro_mes" id="export_filtro_mes">
                        <input type="hidden" name="filtro_funcionario" id="export_filtro_funcionario">
                        <input type="hidden" name="filtro_tipo_registro" id="export_filtro_tipo_registro">
                        <button type="submit" class="btn-modern" style="background:rgba(255,255,255,.25)"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</button>
                    </form>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const sync = () => {
                        export_filtro_anio.value = filtro_anio.value;
                        export_filtro_mes.value = filtro_mes.value;
                        export_filtro_funcionario.value = filtro_funcionario.value;
                        export_filtro_tipo_registro.value = filtro_tipo_registro.value;
                    };
                    ['change'].forEach(ev => ['filtro_anio', 'filtro_mes', 'filtro_funcionario', 'filtro_tipo_registro'].forEach(id => document.getElementById(id).addEventListener(ev, sync)));
                    sync();
                });
            </script>
            <div class="modern-filters">
                <form action="formMasivoCentroVida.php" method="get" class="filter-row">
                    <div class="filter-group">
                        <label for="filtro_anio">Año</label>
                        <select id="filtro_anio" name="filtro_anio" class="modern-select">
                            <option value="">Todos</option>
                            <?php $currentYear = date('Y');
                            for ($y = 2023; $y <= $currentYear; $y++) {
                                $sel = $filtro_anio == $y ? 'selected' : '';
                                echo "<option value='$y' $sel>$y</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_mes">Mes</label>
                        <select id="filtro_mes" name="filtro_mes" class="modern-select">
                            <option value="">Todos</option>
                            <?php $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
                            foreach ($meses as $num => $nom) {
                                $sel = $filtro_mes == $num ? 'selected' : '';
                                echo "<option value='$num' $sel>$nom</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_funcionario">Funcionario</label>
                        <select id="filtro_funcionario" name="filtro_funcionario" class="modern-select">
                            <option value="">Todos</option>
                            <?php if ($usuarios) {
                                while ($u = $usuarios->fetch_assoc()) {
                                    $sel = $filtro_funcionario == $u['id'] ? 'selected' : '';
                                    echo "<option value='{$u['id']}' $sel>" . htmlspecialchars($u['nombre']) . "</option>";
                                }
                            } ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro_tipo_registro">Tipo Registro</label>
                        <select id="filtro_tipo_registro" name="filtro_tipo_registro" class="modern-select">
                            <option value="">Todos</option>
                            <option value="Registro Actividad" <?= $filtro_tipo_registro === 'Registro Actividad' ? 'selected' : '' ?>>Registro Actividad</option>
                            <option value="Masivas" <?= $filtro_tipo_registro === 'Masivas' ? 'selected' : '' ?>>Masivas</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-modern" style="background:#10b981;border-color:#10b981"><i class="bi bi-search"></i> Buscar</button>
                    </div>
                </form>
            </div>
            <div class="modern-table-wrapper">
                <table class="modern-table" id="tabla">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Meta</th>
                            <th>Actividad (Plan)</th>
                            <th>Acción</th>
                            <th>Actividad Centro Vida</th>
                            <th>Política Pública</th>
                            <th>Centro Vida</th>
                            <th>Fecha Atención</th>
                            <th>Nombre Líder</th>
                            <th>Teléfono</th>
                            <th>Comuna</th>
                            <th>Medio Verif.</th>
                            <th>Masculino</th>
                            <th>Femenino</th>
                            <th>Observación</th>
                            <th>Digitado por</th>
                            <th>Funcionario Resp.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows) {
                            while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= $row['id_registro']; ?></td>
                                    <td title='<?= htmlspecialchars($row['descripcion_meta'] ?? '') ?>'><?= $row['descripcion_meta'] ?? '' ?></td>
                                    <td title='<?= htmlspecialchars($row['descripcion_actividad'] ?? '') ?>'><?= $row['descripcion_actividad'] ?? '' ?></td>
                                    <td title='<?= htmlspecialchars($row['descripcion_accion'] ?? '') ?>'><?= $row['descripcion_accion'] ?? '' ?></td>
                                    <td title='<?= htmlspecialchars($row['actividad_centro_vida'] ?? '') ?>'><?= $row['actividad_centro_vida'] ?? '' ?></td>
                                    <td><?= $row['descripcion_politica'] ?? '' ?></td>
                                    <td><?= $row['centro_vida'] ?? '' ?></td>
                                    <td><?= $row['fecha_atencion'] ?? '' ?></td>
                                    <td><?= $row['nombre_lider'] ?? '' ?></td>
                                    <td><?= $row['telefono_contacto'] ?? '' ?></td>
                                    <td><?= $row['nombre_comuna'] ?? '' ?></td>
                                    <td><?= $row['medio_verificacion'] ?? '' ?></td>
                                    <td><?= $row['cantidad_masculino'] ?? 0 ?></td>
                                    <td><?= $row['cantidad_femenino'] ?? 0 ?></td>
                                    <td><?= $row['observacion_actividad'] ?? '' ?></td>
                                    <td><?= $row['digitado_por'] ?? '' ?></td>
                                    <td><?= $row['funcionario_responsable_nombre'] ?? '' ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#modalAdd"
                                                data-json='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Editar"><i class="bi bi-pencil-fill"></i></button>
                                            <button class="btn-action btn-delete" data-id="<?= $row['id_registro']; ?>" title="Eliminar"><i class="bi bi-trash-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } else {
                            // Fila vacía compatible con DataTables (sin colspan)
                            echo "<tr class='no-data'>";
                            for ($i = 0; $i < 18; $i++) {
                                echo "<td class='text-muted'></td>";
                            }
                            echo "</tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <br>
    <center><a href='../../access.php'><img src='../../img/atras.png' width='72' height='72'></a></center><br>

    <!-- Modal Agregar -->
    <div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addRegistroMasivoCentroVida.php" method="POST" id="formAdd">
                    <input type="hidden" name="id_masiva_centro_vida" id="id_masiva_centro_vida" value="">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalAddTitle"><i class="bi bi-plus-circle-fill me-2"></i><span class="mode-text">Agregar Actividad Masiva Centro Vida</span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="meta" name="id_meta" required>
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php if ($metas) {
                                        mysqli_data_seek($metas, 0);
                                        while ($m = $metas->fetch_assoc()) {
                                            echo "<option value='{$m['id_meta']}'>" . htmlspecialchars($m['descripcion_meta']) . "</option>";
                                        }
                                    } ?>
                                </select>
                                <label for="meta">Meta</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="actividad" name="id_actividad" required disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                                <label for="actividad">Actividad (Plan)</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="accion" name="id_accion" required disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                                <label for="accion">Acción</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="actividad_centro_vida" name="id_actividad_centro_vida" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php if ($actividades_cv) {
                                        mysqli_data_seek($actividades_cv, 0);
                                        while ($acv = $actividades_cv->fetch_assoc()) {
                                            echo "<option value='{$acv['id_actividad_centro_vida']}'>" . htmlspecialchars($acv['descripcion_actividad']) . "</option>";
                                        }
                                    } ?>
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
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="centro_vida" name="id_centro_vida">
                                    <option value="" selected>Seleccione...</option>
                                    <?php if ($grupos) {
                                        while ($g = $grupos->fetch_assoc()) {
                                            echo "<option value='{$g['id_grupo']}'>" . htmlspecialchars($g['descripcion_grupo']) . "</option>";
                                        }
                                    } ?>
                                </select>
                                <label for="centro_vida">Lugar del evento</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="date" class="form-control" id="fecha_atencion" name="fecha_atencion" required>
                                <label for="fecha_atencion">Fecha Atención</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select name="funcionario_responsable" id="funcionario_responsable" class="form-select">
                                    <option value="" selected>Seleccione funcionario...</option>
                                    <?php mysqli_data_seek($usuarios, 0);
                                    if ($usuarios) {
                                        while ($u = $usuarios->fetch_assoc()) {
                                            echo "<option value='{$u['id']}'>" . htmlspecialchars($u['nombre']) . "</option>";
                                        }
                                    } ?>
                                </select>
                                <label for="funcionario_responsable">Funcionario Responsable</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="text" class="form-control" id="nombre_lider" name="nombre_lider" placeholder="Nombre líder">
                                <label for="nombre_lider">Nombre líder</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" placeholder="Teléfono">
                                <label for="telefono_contacto">Teléfono contacto</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select name="id_comuna" id="id_comuna" class="form-select">
                                    <option value="" selected>Seleccione...</option>
                                    <?php if ($comunas) {
                                        while ($c = $comunas->fetch_assoc()) {
                                            echo "<option value='{$c['id_com']}'>" . htmlspecialchars($c['nombre_com']) . "</option>";
                                        }
                                    } ?>
                                </select>
                                <label for="id_comuna">Comuna/Corregimiento</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select name="medio_verificacion" id="medio_verificacion" class="form-select">
                                    <option value="" selected>Seleccione...</option>
                                    <option value="Acta">Acta</option>
                                    <option value="Acta y registro fotografico">Acta y registro fotografico</option>
                                    <option value="Registro campo">Registro Campo</option>
                                    <option value="Historio/ expediente">Historio/ expediente</option>
                                    <option value="Captura pantalla digital">Captura pantalla digital</option>
                                    <option value="SPP">SPP</option>
                                    <option value="SPP - Registro fotografico">SPP - Registro fotografico</option>
                                </select>
                                <label for="medio_verificacion">Medio de Verificación</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="number" name="cantidad_masculino" id="cantidad_masculino" class="form-control" placeholder="Masculino" min="0">
                                <label for="cantidad_masculino">Cantidad Masculino</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="number" name="cantidad_femenino" id="cantidad_femenino" class="form-control" placeholder="Femenino" min="0">
                                <label for="cantidad_femenino">Cantidad Femenino</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="tipo_registro" name="tipo_registro">
                                    <option value=""></option>
                                    <option value="Registro Actividad">Registro Actividad</option>
                                    <option value="Masivas">Masivas</option>
                                </select>
                                <label for="tipo_registro">Tipo de Registro</label>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <input type="text" class="form-control" id="observacion_actividad" name="observacion_actividad" placeholder="Observación">
                                <label for="observacion_actividad">Observación Actividad</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnSubmit"><i class="bi bi-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            // Inicialización DataTables con verificación de consistencia columnas
            if ($.fn.DataTable) {
                const headerCols = $('#tabla thead tr th').length;
                let valid = true;
                $('#tabla tbody tr').each(function() {
                    if ($(this).find('td').length !== headerCols) {
                        valid = false;
                    }
                });
                if (valid) {
                    $('#tabla').DataTable({
                        pageLength: 15,
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                        },
                        order: [
                            [7, 'desc']
                        ]
                    });
                }
            }

            const $form = $('#formAdd');
            const $meta = $('#meta');
            const $actividad = $('#actividad');
            const $accion = $('#accion');
            const $politica = $('#politica_publica');
            const $idHidden = $('#id_masiva_centro_vida');
            const $title = $('#modalAddTitle .mode-text');
            const originalAction = $form.attr('action');

            function resetActividad() {
                $actividad.html('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                resetAccion();
            }

            function resetAccion() {
                $accion.html('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                resetPolitica();
            }

            function resetPolitica() {
                $politica.html('<option value="" selected>Seleccione Política Pública...</option>');
            }

            // Meta -> Actividad
            $meta.on('change', function() {
                const idMeta = $(this).val();
                resetActividad();
                if (!idMeta) return;
                $actividad.prop('disabled', true).html('<option value="">Cargando...</option>');
                $.ajax({
                    url: '../contratista/getActividades.php',
                    type: 'POST',
                    data: {
                        id_meta: idMeta
                    },
                    success: function(r) {
                        $actividad.html('<option value="">Seleccione Actividad...</option>' + r).prop('disabled', false);
                    },
                    error: function() {
                        $actividad.html('<option value="">Error cargando actividades</option>');
                    }
                });
            });

            // Actividad -> Acción
            $actividad.on('change', function() {
                const idAct = $(this).val();
                resetAccion();
                if (!idAct) return;
                $accion.prop('disabled', true).html('<option value="">Cargando...</option>');
                $.ajax({
                    url: '../contratista/getAcciones.php',
                    type: 'POST',
                    data: {
                        id_actividad: idAct
                    },
                    success: function(r) {
                        $accion.html('<option value="">Seleccione Acción...</option>' + r).prop('disabled', false);
                    },
                    error: function() {
                        $accion.html('<option value="">Error cargando acciones</option>');
                    }
                });
            });

            // Acción -> Políticas
            $accion.on('change', function() {
                const idAcc = $(this).val();
                resetPolitica();
                if (!idAcc) return;
                $politica.prop('disabled', true).html('<option value="">Cargando...</option>');
                $.ajax({
                    url: '../personMovement/getPoliticaPublica.php',
                    type: 'POST',
                    data: {
                        id_accion: idAcc
                    },
                    dataType: 'json',
                    success: function(resp) {
                        resetPolitica();
                        if (resp && resp.politicas && resp.politicas.length) {
                            resp.politicas.forEach(p => $politica.append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>'));
                        } else {
                            $politica.append('<option value="">No asignada</option>');
                        }
                        $politica.prop('disabled', false);
                    },
                    error: function() {
                        resetPolitica();
                        $politica.append('<option value="">Error al consultar</option>');
                        $politica.prop('disabled', false);
                    }
                });
            });

            // Inicial: deshabilitar dependientes
            resetActividad();

            // Modo agregar al abrir
            $('#modalAdd').on('show.bs.modal', function(e) {
                const btn = $(e.relatedTarget);
                if (!btn || !btn.hasClass('btn-edit')) {
                    // modo agregar
                    $form.attr('action', 'addRegistroMasivoCentroVida.php');
                    $title.text('Agregar Actividad Masiva Centro Vida');
                    $('#btnSubmit').html('<i class="bi bi-save"></i> Guardar');
                    $idHidden.val('');
                    $form[0].reset();
                    resetActividad();
                    return;
                }
                // modo edición
                const data = btn.data('json');
                $form.attr('action', 'editRegistroMasivoCentroVida.php');
                $title.text('Editar Actividad Masiva Centro Vida');
                $('#btnSubmit').html('<i class="bi bi-save"></i> Actualizar');
                $idHidden.val(data.id_registro);
                $('#fecha_atencion').val(data.fecha_atencion || '');
                $('#nombre_lider').val(data.nombre_lider || '');
                $('#telefono_contacto').val(data.telefono_contacto || '');
                $('#centro_vida').val(data.id_centro_vida || '');
                $('#id_comuna').val(data.id_comuna || '');
                $('#medio_verificacion').val(data.medio_verificacion || '');
                $('#cantidad_masculino').val(data.cantidad_masculino || 0);
                $('#cantidad_femenino').val(data.cantidad_femenino || 0);
                $('#observacion_actividad').val(data.observacion_actividad || '');
                $('#funcionario_responsable').val(data.funcionario_responsable || '');
                $('#actividad_centro_vida').val(data.id_actividad_centro_vida || '');
                // tipo_registro preload
                $('#tipo_registro').val(data.tipo_registro || '');
                // Carga en cascada: meta -> actividad -> acción -> política
                const metaId = data.id_meta || '';
                const actividadId = data.id_actividad || '';
                const accionId = data.id_accion || '';
                const politicaId = data.politica_publica || '';
                $meta.val(metaId);
                if (metaId) {
                    $actividad.prop('disabled', true).html('<option value="">Cargando...</option>');
                    $.post('../contratista/getActividades.php', {
                        id_meta: metaId
                    }, function(r) {
                        $actividad.html('<option value="">Seleccione Actividad...</option>' + r).prop('disabled', false);
                        if (actividadId) {
                            $actividad.val(actividadId);
                            $accion.prop('disabled', true).html('<option value="">Cargando...</option>');
                            $.post('../contratista/getAcciones.php', {
                                id_actividad: actividadId
                            }, function(r2) {
                                $accion.html('<option value="">Seleccione Acción...</option>' + r2).prop('disabled', false);
                                if (accionId) {
                                    $accion.val(accionId);
                                    // Políticas
                                    $politica.prop('disabled', true).html('<option value="">Cargando...</option>');
                                    $.ajax({
                                        url: '../personMovement/getPoliticaPublica.php',
                                        type: 'POST',
                                        data: {
                                            id_accion: accionId
                                        },
                                        dataType: 'json',
                                        success: function(resp) {
                                            $politica.html('<option value="">Seleccione Política Pública...</option>');
                                            if (resp && resp.politicas) {
                                                resp.politicas.forEach(p => $politica.append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>'));
                                            }
                                            if (politicaId) {
                                                $politica.val(politicaId);
                                            }
                                            $politica.prop('disabled', false);
                                        },
                                        error: function() {
                                            $politica.html('<option value="">Error al cargar</option>').prop('disabled', false);
                                        }
                                    });
                                }
                            });
                        }
                    });
                } else {
                    resetActividad();
                }
            });

            // Restaurar a modo agregar al cerrar
            $('#modalAdd').on('hidden.bs.modal', function() {
                $form.attr('action', originalAction);
                $title.text('Agregar Actividad Masiva Centro Vida');
                $('#btnSubmit').html('<i class=\"bi bi-save\"></i> Guardar');
                $idHidden.val('');
                $form[0].reset();
                resetActividad();
            });

            // Eliminar
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (!id) return;
                Swal.fire({
                    title: '¿Eliminar registro?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(res => {
                    if (res.isConfirmed) {
                        window.location = 'formMasivoCentroVida.php?delete=' + id;
                    }
                });
            });
        });
    </script>
</body>

</html>