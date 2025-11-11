<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    exit();
}

include("../../conexion.php");

$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id = $_SESSION['id'];

// Filtros
$filtro_cedula = isset($_GET['cedula']) ? $mysqli->real_escape_string($_GET['cedula']) : '';
$filtro_nombre = isset($_GET['nombre']) ? $mysqli->real_escape_string($_GET['nombre']) : '';
$filtro_mes = isset($_GET['mes']) ? $mysqli->real_escape_string($_GET['mes']) : '';
$filtro_anio = isset($_GET['anio']) ? $mysqli->real_escape_string($_GET['anio']) : '';

// Construir WHERE
$where = "1=1";

if($tipo_usuario == 9) {
    $where .= " AND pag.usuario_registro = '$usuario_id'";
}

if($filtro_cedula != '') {
    $where .= " AND p.cedula LIKE '%$filtro_cedula%'";
}

if($filtro_nombre != '') {
    $where .= " AND CONCAT(p.nombre, ' ', p.apellido) LIKE '%$filtro_nombre%'";
}

if($filtro_mes != '') {
    $where .= " AND pag.mes_pago = '$filtro_mes'";
}

if($filtro_anio != '') {
    $where .= " AND pag.anio_pago = '$filtro_anio'";
}

// Consulta
$sql = "SELECT det.id_detalle_pago_cm, det.id_pago_cm, det.estado_cobro, det.fecha_cobro, det.monto,
        p.cedula_persona_cm, CONCAT(p.nombres_persona_cm, ' ', p.apellidos_persona_cm) as persona_nombre,
        pag.mes_pago, pag.anio_pago, pag.fecha_registro as fecha_pago,
        u.nombre as registrado_por
        FROM detalle_pagos_cm det
        INNER JOIN personas_colombia_mayor p ON det.cedula_persona_cm = p.cedula_persona_cm
        INNER JOIN pagos_colombia_mayor pag ON det.id_pago_cm = pag.id_pago_cm
        LEFT JOIN usuarios u ON pag.usuario_registro = u.id
        WHERE $where
        ORDER BY pag.anio_pago DESC, pag.mes_pago DESC, p.apellidos_persona_cm, p.nombres_persona_cm";

$result = $mysqli->query($sql);

if($result->num_rows > 0) {
    // Estadísticas
    $total_registros = $result->num_rows;
    $total_cobrados = 0;
    $total_no_cobrados = 0;
    $total_pendientes = 0;
    $monto_total = 0;
    
    $result->data_seek(0);
    while($row = $result->fetch_assoc()) {
        if($row['estado_cobro'] == 'COBRADO') $total_cobrados++;
        elseif($row['estado_cobro'] == 'NO_COBRADO') $total_no_cobrados++;
        else $total_pendientes++;
        $monto_total += floatval($row['monto']);
    }
?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h5><?php echo $total_registros; ?></h5>
                    <p class="mb-0">Total Registros</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h5><?php echo $total_cobrados; ?></h5>
                    <p class="mb-0">Cobrados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-danger text-white">
                <div class="card-body">
                    <h5><?php echo $total_no_cobrados; ?></h5>
                    <p class="mb-0">No Cobrados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-warning text-dark">
                <div class="card-body">
                    <h5><?php echo $total_pendientes; ?></h5>
                    <p class="mb-0">Pendientes</p>
                </div>
            </div>
        </div>
    </div>

    <table id="historialTable" class="table modern-table table-hover">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Persona</th>
                <th>Período</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Fecha Cobro</th>
                <th>Registrado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $result->data_seek(0);
            while($row = $result->fetch_assoc()): 
                $meses = ['01'=>'Ene', '02'=>'Feb', '03'=>'Mar', '04'=>'Abr', '05'=>'May', '06'=>'Jun',
                          '07'=>'Jul', '08'=>'Ago', '09'=>'Sep', '10'=>'Oct', '11'=>'Nov', '12'=>'Dic'];
                $periodo = $meses[$row['mes_pago']] . ' ' . $row['anio_pago'];
                
                $badge_color = '';
                if($row['estado_cobro'] == 'COBRADO') $badge_color = 'bg-success';
                elseif($row['estado_cobro'] == 'NO_COBRADO') $badge_color = 'bg-danger';
                else $badge_color = 'bg-warning';
            ?>
            <tr>
                <td><?php echo $row['cedula']; ?></td>
                <td><?php echo $row['persona_nombre']; ?></td>
                <td><?php echo $periodo; ?></td>
                <td>$<?php echo number_format($row['monto'], 0); ?></td>
                <td><span class="badge <?php echo $badge_color; ?>"><?php echo $row['estado_cobro']; ?></span></td>
                <td><?php echo $row['fecha_cobro'] ? date('d/m/Y', strtotime($row['fecha_cobro'])) : '-'; ?></td>
                <td><?php echo $row['registrado_por'] ?? 'N/A'; ?></td>
                <td>
                    <?php if($row['estado_cobro'] == 'PENDIENTE'): ?>
                    <button class="btn btn-sm btn-success" onclick="marcarCobrado(<?php echo $row['id']; ?>)">
                        <i class="bi bi-check-circle"></i> Cobrado
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="marcarNoCobrado(<?php echo $row['id']; ?>)">
                        <i class="bi bi-x-circle"></i> No Cobrado
                    </button>
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <script>
        $('#historialTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[2, 'desc']]
        });
    </script>
<?php
} else {
    echo '<div class="alert alert-info">No se encontraron registros de pagos</div>';
}

$mysqli->close();
?>
