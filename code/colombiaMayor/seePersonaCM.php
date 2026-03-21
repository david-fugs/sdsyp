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

// Construir query base para personas
$where = "WHERE 1=1";

// Si es contratista (tipo 9), solo ver sus propios registros
if ($tipo_usuario == 9) {
    $where .= " AND usuario_registro = '$id_usuario'";
}

// Filtro por cédula (búsqueda parcial)
if (!empty($_GET['cedula'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula']);
    $where .= " AND cedula_persona_cm LIKE '%$cedula%'";
}

// Filtro por nombre
if (!empty($_GET['nombre'])) {
    $nombre = $mysqli->real_escape_string($_GET['nombre']);
    $where .= " AND (nombres_persona_cm LIKE '%$nombre%' OR apellidos_persona_cm LIKE '%$nombre%')";
}

// Filtro por estado
if (!empty($_GET['estado'])) {
    $estado = $mysqli->real_escape_string($_GET['estado']);
    $where .= " AND estado_cm LIKE '%$estado%'";
}

// Consulta de personas con filtros
$query_personas = "
    SELECT 
        p.*,
        u.nombre AS nombre_contratista
    FROM personas_colombia_mayor p
    LEFT JOIN usuarios u ON p.usuario_registro = u.id
    $where
    ORDER BY p.apellidos_persona_cm ASC, p.nombres_persona_cm ASC
";

$result_personas = $mysqli->query($query_personas);
if (!$result_personas) {
    die("Error en la consulta de personas: " . $mysqli->error);
}

// Consultas para selectores
$metas = "SELECT * FROM metas ORDER BY descripcion_meta ASC";
$result_metas = $mysqli->query($metas);
if (!$result_metas) {
    die("Error en la consulta de metas: " . $mysqli->error);
}

$politicas_publicas = "SELECT * FROM politicas_publicas ORDER BY descripcion_politica ASC";
$result_politicas_publicas = $mysqli->query($politicas_publicas);
if (!$result_politicas_publicas) {
    die("Error en la consulta de políticas públicas: " . $mysqli->error);
}

// Consulta para condiciones Colombia Mayor
$condiciones_cm = "SELECT id_condicion, descripcion_condicion FROM condiciones_componente WHERE descripcion_condicion LIKE 'C.M%' ORDER BY descripcion_condicion ASC";
$result_condiciones_cm = $mysqli->query($condiciones_cm);
if (!$result_condiciones_cm) {
    die("Error en la consulta de condiciones: " . $mysqli->error);
}

// Guardar opciones en array para reutilizar
$opciones_estado_cm = [];
while ($row = $result_condiciones_cm->fetch_assoc()) {
    $opciones_estado_cm[] = $row['descripcion_condicion'];
}

if (isset($_GET['delete'])) {
    $cedula_persona = $_GET['delete'];
    deleteMember($cedula_persona);
}

function deleteMember($cedula_persona_cm)
{
    global $mysqli;
    $query = "DELETE FROM personas_colombia_mayor WHERE cedula_persona_cm = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $cedula_persona_cm);

    if ($stmt->execute()) {
        echo "<script>alert('Persona borrada correctamente');
        window.location = 'seePersonaCM.php';</script>";
    } else {
        echo "<script>alert('Error borrando la persona');
        window.location = 'seePersonaCM.php';</script>";
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
    <title>Colombia Mayor - Personas</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <!-- SweetAlert2 -->
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

        .modern-input,
        .modern-select {
            font-size: 15px !important;
            padding: 10px 12px !important;
        }

        .filter-group label {
            font-size: 14px !important;
            font-weight: 600 !important;
        }

        .btn-modern {
            font-size: 15px !important;
            padding: 10px 20px !important;
        }

        .btn-action {
            padding: 8px 10px !important;
            font-size: 14px !important;
        }

        .modern-header h2 {
            font-size: 26px !important;
        }
        
        .cm-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #667eea; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 48px; text-align: center; font-weight: bold;">
        <b><i class="bi bi-people-fill"></i> COLOMBIA MAYOR - PERSONAS</b>
    </h1>

    <!-- Tabla de Personas Colombia Mayor -->
    <div class="container mt-5">
        <div class="modern-container">
            <!-- Header moderno -->
            <div class="modern-header">
                <h2><i class="bi bi-people-fill"></i> Personas Registradas Colombia Mayor</h2>
                <div>
                    <button type="button" class="btn-modern btn-success me-2" onclick="window.location.href='exportPersonasCM.php'">
                        <i class="bi bi-file-excel-fill"></i>
                        Exportar Excel
                    </button>
                    <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                        <i class="bi bi-person-plus-fill"></i>
                        Agregar Persona
                    </button>
                </div>
            </div>

            <!-- Filtros modernos -->
            <div class="modern-filters">
                <form method="GET" action="" id="form-filtros">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="cedula">Cédula</label>
                            <input type="text" id="cedula" name="cedula" class="modern-input" 
                                   placeholder="Buscar por cédula..." 
                                   value="<?php echo htmlspecialchars($_GET['cedula'] ?? ''); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="modern-input" 
                                   placeholder="Buscar por nombre..." 
                                   value="<?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado" class="modern-select">
                                <option value="">Todos los estados</option>
                                <option value="ACTIVO" <?php echo (($_GET['estado'] ?? '') == 'ACTIVO') ? 'selected' : ''; ?>>ACTIVO</option>
                                <option value="SUSPENDIDO" <?php echo (($_GET['estado'] ?? '') == 'SUSPENDIDO') ? 'selected' : ''; ?>>SUSPENDIDO</option>
                                <option value="FALLECIDO" <?php echo (($_GET['estado'] ?? '') == 'FALLECIDO') ? 'selected' : ''; ?>>FALLECIDO</option>
                                <option value="RETIRO_VOLUNTARIO" <?php echo (($_GET['estado'] ?? '') == 'RETIRO_VOLUNTARIO') ? 'selected' : ''; ?>>RETIRO VOLUNTARIO</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn-modern btn-primary">
                                <i class="bi bi-search"></i>
                                Filtrar
                            </button>
                            <a href="seePersonaCM.php" class="btn-modern btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabla moderna -->
            <div class="modern-table-wrapper" style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(102,126,234,0.08); padding: 24px;">
                <table class="modern-table table table-hover align-middle" id="salesTable" style="border-radius: 12px; overflow: hidden;">
                    <thead class="table-dark">
                        <tr style="font-size: 1.1rem;">
                            <th>Cédula</th>
                            <th>Nombre Completo</th>
                            <th>Género</th>
                            <th>Edad</th>
                            <th>Teléfono</th>
                            <th>Fecha Ingreso</th>
                            <th class="col-status">Estado</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_personas && $result_personas->num_rows > 0) {
                            while ($row = $result_personas->fetch_assoc()) {
                                // Calcular edad si tiene fecha de nacimiento
                                $edad_texto = 'N/A';
                                if ($row['fecha_nacimiento_cm'] && $row['fecha_nacimiento_cm'] != '0000-00-00') {
                                    $hoy = new DateTime();
                                    $nacimiento = new DateTime($row['fecha_nacimiento_cm']);
                                    $edad = $hoy->diff($nacimiento)->y;
                                    $edad_texto = $edad . ' años';
                                } elseif ($row['edad_cm']) {
                                    $edad_texto = $row['edad_cm'] . ' años';
                                }

                                // Determinar badge de estado
                                $badge_class = 'status-badge status-secondary';
                                $estado_icon = '<i class="bi bi-circle-fill"></i>';
                                $estado_value = strtoupper($row['estado_cm'] ?? '');
                                
                                // Detectar estado por palabras clave
                                if ($estado_value === 'ACTIVO' || stripos($estado_value, 'ACTIVO') !== false) {
                                    $badge_class = 'status-badge status-active';
                                    $estado_icon = '<i class="bi bi-check-circle-fill"></i>';
                                } elseif (stripos($estado_value, 'FALLECIDO') !== false || stripos($estado_value, 'FALLECIDA') !== false) {
                                    $badge_class = 'status-badge status-secondary';
                                    $estado_icon = '<i class="bi bi-x-circle-fill"></i>';
                                } elseif (stripos($estado_value, 'RETIRO') !== false || stripos($estado_value, 'RETIRADO') !== false) {
                                    $badge_class = 'status-badge status-info';
                                    $estado_icon = '<i class="bi bi-arrow-left-circle-fill"></i>';
                                } elseif (stripos($estado_value, 'SUSPENDIDO') !== false || stripos($estado_value, 'SUSPENDIDA') !== false) {
                                    $badge_class = 'status-badge status-warning';
                                    $estado_icon = '<i class="bi bi-pause-circle-fill"></i>';
                                } elseif (stripos($estado_value, 'ESPERA') !== false || stripos($estado_value, 'LISTA') !== false) {
                                    $badge_class = 'status-badge status-warning';
                                    $estado_icon = '<i class="bi bi-clock-fill"></i>';
                                } elseif (stripos($estado_value, 'BDUA') !== false || stripos($estado_value, 'BLOQUEO') !== false || stripos($estado_value, 'DUPLICIDAD') !== false) {
                                    $badge_class = 'status-badge status-danger';
                                    $estado_icon = '<i class="bi bi-exclamation-triangle-fill"></i>';
                                }

                                // Formatear fecha de ingreso
                                $fecha_ingreso = 'N/A';
                                if ($row['fecha_ingreso_cm'] && $row['fecha_ingreso_cm'] != '0000-00-00') {
                                    $fecha_ingreso = date('d/m/Y', strtotime($row['fecha_ingreso_cm']));
                                }

                                echo "<tr class='fade-in'>";
                                echo "<td><strong>" . htmlspecialchars($row['cedula_persona_cm']) . "</strong></td>";
                                echo "<td>";
                                echo "<b>" . htmlspecialchars($row['nombres_persona_cm'] . ' ' . $row['apellidos_persona_cm']) . "</b><br>";
                                echo "<span class='cm-badge'><i class='bi bi-award-fill'></i> Colombia Mayor</span>";
                                echo "</td>";
                                echo "<td>" . htmlspecialchars($row['genero_persona_cm'] ?? 'N/A') . "</td>";
                                echo "<td><span class='badge bg-primary'>" . $edad_texto . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['telefono_persona_cm'] ?? 'N/A') . "</td>";
                                echo "<td>" . $fecha_ingreso . "</td>";
                                echo "<td class='col-status'><span class='$badge_class'>$estado_icon " . str_replace('_', ' ', $row['estado_cm']) . "</span></td>";

                                // Botones de acción
                                echo '<td class="col-actions">
                                        <div class="action-buttons">
                                            <button type="button" class="btn-action btn-edit" 
                                                title="Editar persona"
                                                data-bs-toggle="modal" data-bs-target="#modalEdicion"
                                                data-cedula="' . htmlspecialchars($row['cedula_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-tipo-identificacion="' . htmlspecialchars($row['tipo_identificacion_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-nombres="' . htmlspecialchars($row['nombres_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-apellidos="' . htmlspecialchars($row['apellidos_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-genero="' . htmlspecialchars($row['genero_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-telefono="' . htmlspecialchars($row['telefono_persona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-telefono-referencia="' . htmlspecialchars($row['telefono_referencia_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-referencia="' . htmlspecialchars($row['referencia_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-fecha-nacimiento="' . htmlspecialchars($row['fecha_nacimiento_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-edad="' . htmlspecialchars($row['edad_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-grupo-sisben="' . htmlspecialchars($row['grupo_sisben'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-direccion="' . htmlspecialchars($row['direccion_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-barrio="' . htmlspecialchars($row['barrio_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-comuna="' . htmlspecialchars($row['comuna_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-zona="' . htmlspecialchars($row['zona_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-departamento="' . htmlspecialchars($row['departamento_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-municipio="' . htmlspecialchars($row['municipio_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-convivencia-actual="' . htmlspecialchars($row['convivencia_actual'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-persona-discapacidad="' . htmlspecialchars($row['persona_discapacidad'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-cual-discapacidad="' . htmlspecialchars($row['cual_discapacidad'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-cabeza-hogar="' . htmlspecialchars($row['cabeza_hogar'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-se-reconoce-como="' . htmlspecialchars($row['se_reconoce_como'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-orientacion-sexual="' . htmlspecialchars($row['orientacion_sexual'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-grupo-etnico="' . htmlspecialchars($row['grupo_etnico'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-tipo-salud="' . htmlspecialchars($row['tipo_salud'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-condicion-ocupacion="' . htmlspecialchars($row['condicion_ocupacion'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-condicion-componente="' . htmlspecialchars($row['condicion_componente'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-fecha-ingreso="' . htmlspecialchars($row['fecha_ingreso_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-estado="' . htmlspecialchars($row['estado_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-id-meta="' . htmlspecialchars($row['id_meta'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-id-actividad="' . htmlspecialchars($row['id_actividad'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-id-accion="' . htmlspecialchars($row['id_accion'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-id-politica-publica="' . htmlspecialchars($row['id_politica_publica'] ?? '', ENT_QUOTES, 'UTF-8') . '"
                                                data-observaciones="' . htmlspecialchars($row['observaciones_cm'] ?? '', ENT_QUOTES, 'UTF-8') . '">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn-action btn-delete" 
                                                title="Eliminar persona"
                                                data-cedula="' . htmlspecialchars($row['cedula_persona_cm']) . '">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                            <a href="exportPersonaCM.php?cedula=' . htmlspecialchars($row['cedula_persona_cm']) . '" 
                                               class="btn-action btn-info" 
                                               title="Exportar a Excel">
                                                <i class="bi bi-file-earmark-excel"></i>
                                            </a>
                                        </div>
                                      </td>';
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No se encontraron registros</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add Person -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addPersonaCM.php" method="POST">
                    <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar Persona - Colombia Mayor
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila 1: Tipo de identificación y número -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="tipo_identificacion" name="tipo_identificacion_cm" required>
                                    <option value="" selected disabled>Seleccione tipo...</option>
                                    <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                                    <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                                    <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                    <option value="Sin identificacion">Sin identificacion</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="tipo_identificacion">Tipo de Identificación</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="cedula_persona" name="cedula_persona_cm" placeholder="Número de Identificación" autocomplete="off" required>
                                <label for="cedula_persona">Número de Identificación</label>
                            </div>
                        </div>

                        <!-- Fila 2: Género y Nombres -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="genero_persona" name="genero_persona_cm" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="genero_persona">Género</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="nombres_persona" name="nombres_persona_cm" placeholder="Nombres" required>
                                <label for="nombres_persona">Nombres</label>
                            </div>
                        </div>

                        <!-- Fila 3: Apellidos y Teléfono -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="apellidos_persona" name="apellidos_persona_cm" placeholder="Apellidos">
                                <label for="apellidos_persona">Apellidos</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="telefono_persona" name="telefono_persona_cm" placeholder="Teléfono">
                                <label for="telefono_persona">Teléfono</label>
                            </div>
                        </div>

                        <!-- Fila 3.5: Teléfono Referencia y Referencia -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="telefono_referencia_persona" name="telefono_referencia_cm" placeholder="Teléfono Referencia">
                                <label for="telefono_referencia_persona">Teléfono Referencia</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="referencia_persona" name="referencia_cm" placeholder="Referencia">
                                <label for="referencia_persona">Referencia</label>
                            </div>
                        </div>

                        <!-- Fila 3.7: Fecha de Nacimiento -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento_cm" placeholder="Fecha de Nacimiento">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            </div>
                        </div>

                        <!-- Fila 4: Edad y Grupo Sisbén -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="number" class="form-control" id="edad" name="edad_cm" placeholder="Edad" readonly style="background-color: #f8f9fa;">
                                <label for="edad">Edad (calculada)</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="grupo_sisben" name="grupo_sisben">
                                    <option value="" selected>Seleccione grupo...</option>
                                    <?php
                                    $grupos = [
                                        'A' => 5,
                                        'B' => 7,
                                        'C' => 18,
                                        'D' => 21
                                    ];
                                    foreach ($grupos as $letra => $max) {
                                        for ($n = 1; $n <= $max; $n++) {
                                            echo "<option value=\"{$letra}{$n}\">{$letra}{$n}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <label for="grupo_sisben">Grupo Sisbén</label>
                            </div>
                        </div>

                        <!-- Fila 5: Dirección -->
                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <input type="text" class="form-control" id="direccion" name="direccion_cm" placeholder="Dirección">
                                <label for="direccion">Dirección</label>
                            </div>
                        </div>

                        <!-- Fila 6: Barrio, Comuna y Zona -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating position-relative">
                                <input type="text" class="form-control" id="barrio" name="barrio_cm" placeholder="Barrio" list="lista-barrios" autocomplete="off">
                                <label for="barrio">Barrio</label>
                                <datalist id="lista-barrios"></datalist>
                                <input type="hidden" id="id_barrio_cm" name="id_barrio_cm">
                                <input type="hidden" id="id_comuna_cm" name="id_comuna_cm">
                            </div>
                            <div class="col-md-3 mb-3 form-floating">
                                <input type="text" class="form-control" id="comuna" name="comuna_cm" placeholder="Comuna" readonly>
                                <label for="comuna">Comuna</label>
                            </div>
                            <div class="col-md-3 mb-3 form-floating">
                                <input type="text" class="form-control" id="zona" name="zona_cm" placeholder="Zona" readonly>
                                <label for="zona">Zona</label>
                            </div>
                        </div>

                        <!-- Fila 7: Departamento y Municipio -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="departamento" name="departamento_cm">
                                    <option value="Risaralda" selected>Risaralda</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="departamento">Departamento</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="municipio" name="municipio_cm">
                                    <option value="Pereira" selected>Pereira</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="municipio">Municipio</label>
                            </div>
                        </div>

                        <!-- Fila 11: Convivencia Actual -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="convivencia_actual" name="convivencia_actual">
                                    <option value="" selected>Seleccione...</option>
                                    <option>Pareja</option>
                                    <option>Familia</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="convivencia_actual">Convivencia Actual</label>
                            </div>
                        </div>

                        <!-- Fila 12: Discapacidad -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="persona_discapacidad" name="persona_discapacidad">
                                    <option value="" selected>¿Persona con discapacidad?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="persona_discapacidad">¿Persona con discapacidad?</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating" id="div_cual_discapacidad" style="display:none;">
                                <select class="form-select" id="cual_discapacidad" name="cual_discapacidad">
                                    <option value="" selected>Categoría discapacidad...</option>
                                    <option value="Auditiva">Auditiva</option>
                                    <option value="Física">Física</option>
                                    <option value="Intelectual">Intelectual</option>
                                    <option value="Múltiple">Múltiple</option>
                                    <option value="Psicosocial">Psicosocial</option>
                                    <option value="Sordoceguera">Sordoceguera</option>
                                    <option value="Visual">Visual</option>
                                </select>
                                <label for="cual_discapacidad">Categoría discapacidad</label>
                            </div>
                        </div>

                        <!-- Fila 13: Cabeza de hogar -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="cabeza_hogar" name="cabeza_hogar">
                                    <option value="" selected>¿Cabeza de hogar?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="cabeza_hogar">¿Cabeza de hogar?</label>
                            </div>
                        </div>

                        <!-- Fila 14: Se reconoce como y Orientación sexual -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="se_reconoce_como" name="se_reconoce_como">
                                    <option value="" selected>Se reconoce como...</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Hombre trans">Hombre trans</option>
                                    <option value="Mujer">Mujer</option>
                                    <option value="Mujer trans">Mujer trans</option>
                                    <option value="No binaria">No binaria</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="se_reconoce_como">Se reconoce como</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="orientacion_sexual" name="orientacion_sexual">
                                    <option value="" selected>Orientación sexual...</option>
                                    <option value="Heterosexual">Heterosexual</option>
                                    <option value="Homosexual">Homosexual</option>
                                    <option value="Asexual">Asexual</option>
                                    <option value="Bisexual">Bisexual</option>
                                    <option value="Pansexual">Pansexual</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="orientacion_sexual">Orientación sexual</label>
                            </div>
                        </div>

                        <!-- Fila 15: Grupo étnico -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="grupo_etnico" name="grupo_etnico">
                                    <option value="" selected>Grupo étnico...</option>
                                    <option value="Indígena">Indígena</option>
                                    <option value="Rom">Rom</option>
                                    <option value="Raizal">Raizal</option>
                                    <option value="Palenquero de San Basilio">Palenquero de San Basilio</option>
                                    <option value="Negro/Mulato">Negro/Mulato</option>
                                    <option value="Mestizo">Mestizo</option>
                                </select>
                                <label for="grupo_etnico">Grupo étnico</label>
                            </div>
                        </div>

                        <!-- Fila 16: Tipo de salud -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="tipo_salud" name="tipo_salud">
                                    <option value="" selected>Tipo de salud...</option>
                                    <option value="Régimen subsidiado">Régimen subsidiado</option>
                                    <option value="Régimen contributivo">Régimen contributivo</option>
                                    <option value="Régimen vinculado">Régimen vinculado</option>
                                </select>
                                <label for="tipo_salud">Tipo de salud</label>
                            </div>
                        </div>

                        <!-- Fila 17: Condición Ocupación y Condición Componente -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="condicion_ocupacion" name="condicion_ocupacion">
                                    <option value="" selected>Condición Ocupación...</option>
                                    <option value="Ama de Casa">Ama de Casa</option>
                                    <option value="Estudiante">Estudiante</option>
                                    <option value="Empleado">Empleado</option>
                                    <option value="Desempleado">Desempleado</option>
                                    <option value="Independiente">Independiente</option>
                                    <option value="Pensionado">Pensionado</option>
                                    <option value="Buscando Empleo">Buscando Empleo</option>
                                    <option value="Ninguno">Ninguno</option>
                                </select>
                                <label for="condicion_ocupacion">Condición Ocupación</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="condicion_componente" name="condicion_componente">
                                    <option value="" selected>Condición Componente...</option>
                                    <option value="C.M Retiro Definitivo">C.M Retiro Definitivo</option>
                                    <option value="C.M Activo">C.M Activo</option>
                                    <option value="C.M BDUA">C.M BDUA</option>
                                    <option value="C.M Bloqueo Registraduria">C.M Bloqueo Registraduria</option>
                                    <option value="C.M Duplicidad Documento">C.M Duplicidad Documento</option>
                                    <option value="C.M Ejercicio Mendicidad Comprobada">C.M Ejercicio Mendicidad Comprobada</option>
                                    <option value="C.M En lista de Espera">C.M En lista de Espera</option>
                                    <option value="C.M Fallecido">C.M Fallecido</option>
                                    <option value="C.M Fallecido sin Certificado">C.M Fallecido sin Certificado</option>
                                    <option value="C.M Familias en Accion">C.M Familias en Accion</option>
                                    <option value="C.M Fuera de la Ciudad">C.M Fuera de la Ciudad</option>
                                    <option value="Visita psicosocial fallida">Visita psicosocial fallida</option>
                                </select>
                                <label for="condicion_componente">Condición Componente</label>
                            </div>
                        </div>

                        <!-- Fila 18: Fecha de Ingreso y Estado -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso_cm" required>
                                <label for="fecha_ingreso">Fecha Atencion</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="estado" name="estado_cm" required>
                                    <option value="">Seleccione un estado...</option>
                                    <option value="ACTIVO">ACTIVO</option>
                                    <?php foreach ($opciones_estado_cm as $opcion): ?>
                                        <option value="<?php echo htmlspecialchars($opcion); ?>">
                                            <?php echo htmlspecialchars($opcion); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="estado">Estado</label>
                            </div>
                        </div>

                        <!-- Fila 19: Meta, Actividad, Acción y Política Pública -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="meta" name="id_meta">
                                    <option value="" selected>Seleccione Meta...</option>
                                    <?php while ($meta = $result_metas->fetch_assoc()) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="meta">Meta</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="actividad" name="id_actividad" disabled>
                                    <option value="" selected>Seleccione Actividad...</option>
                                </select>
                                <label for="actividad">Actividad</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="accion" name="id_accion" disabled>
                                    <option value="" selected>Seleccione Acción...</option>
                                </select>
                                <label for="accion">Acción</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="politica-publica" name="id_politica_publica">
                                    <option value="" selected>Seleccione Política Pública...</option>
                                </select>
                                <label for="politica-publica">Política Pública</label>
                            </div>
                        </div>

                        <!-- Fila 20: Observaciones -->
                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <textarea class="form-control" id="observaciones" name="observaciones_cm" placeholder="Observaciones" style="height: 100px"></textarea>
                                <label for="observaciones">Observaciones</label>
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

    <!-- Modal Edit Person -->
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="editPersonaCM.php" method="POST">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" id="modalEdicionLabel">Editar Persona - Colombia Mayor</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" id="edit-cedula-original" name="cedula_original">
                        
                        <!-- Similar structure to add modal -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-tipo-identificacion" class="form-label">Tipo de Identificación</label>
                                <select class="form-select" id="edit-tipo-identificacion" name="tipo_identificacion_cm" required>
                                    <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                                    <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                                    <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                    <option value="Sin identificacion">Sin identificacion</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-cedula" class="form-label">Número de Identificación</label>
                                <input type="text" class="form-control" id="edit-cedula" name="cedula_persona_cm" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-nombres" class="form-label">Nombres</label>
                                <input type="text" class="form-control" id="edit-nombres" name="nombres_persona_cm" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-apellidos" class="form-label">Apellidos</label>
                                <input type="text" class="form-control" id="edit-apellidos" name="apellidos_persona_cm">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-genero" class="form-label">Género</label>
                                <select class="form-select" id="edit-genero" name="genero_persona_cm" required>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="edit-telefono" name="telefono_persona_cm">
                            </div>
                        </div>

                        <!-- Fila: Teléfono de Referencia y Referencia -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-telefono-referencia" class="form-label">Teléfono de Referencia</label>
                                <input type="text" class="form-control" id="edit-telefono-referencia" name="telefono_referencia_cm">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-referencia" class="form-label">Referencia</label>
                                <input type="text" class="form-control" id="edit-referencia" name="referencia_cm">
                            </div>
                        </div>

                        <!-- Fila: Grupo SISBEN -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-grupo-sisben" class="form-label">Grupo SISBEN</label>
                                <select class="form-select" id="edit-grupo-sisben" name="grupo_sisben">
                                    <option value="">Seleccione...</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-fecha-nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="edit-fecha-nacimiento" name="fecha_nacimiento_cm">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-edad" class="form-label">Edad</label>
                                <input type="number" class="form-control" id="edit-edad" name="edad_cm" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit-direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="edit-direccion" name="direccion_cm">
                            </div>
                        </div>

                        <!-- Fila: Barrio, Comuna y Zona (Edición) -->
                        <div class="row">
                            <div class="col-md-6 mb-3 position-relative">
                                <label for="edit-barrio" class="form-label">Barrio</label>
                                <input type="text" class="form-control" id="edit-barrio" name="barrio_cm" placeholder="Barrio" list="edit-lista-barrios" autocomplete="off">
                                <datalist id="edit-lista-barrios"></datalist>
                                <input type="hidden" id="edit-id-barrio-cm" name="id_barrio_cm">
                                <input type="hidden" id="edit-id-comuna-cm" name="id_comuna_cm">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit-comuna" class="form-label">Comuna</label>
                                <input type="text" class="form-control" id="edit-comuna" name="comuna_cm" placeholder="Comuna" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit-zona" class="form-label">Zona</label>
                                <input type="text" class="form-control" id="edit-zona" name="zona_cm" placeholder="Zona" readonly>
                            </div>
                        </div>

                        <!-- Fila: Departamento y Municipio -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-departamento" class="form-label">Departamento</label>
                                <select class="form-select" id="edit-departamento" name="departamento_cm">
                                    <option value="Risaralda">Risaralda</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-municipio" class="form-label">Municipio</label>
                                <select class="form-select" id="edit-municipio" name="municipio_cm">
                                    <option value="Pereira">Pereira</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Convivencia Actual -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-convivencia-actual" class="form-label">Convivencia Actual</label>
                                <select class="form-select" id="edit-convivencia-actual" name="convivencia_actual">
                                    <option value="">Seleccione...</option>
                                    <option>Pareja</option>
                                    <option>Familia</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Discapacidad -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-persona-discapacidad" class="form-label">¿Persona con discapacidad?</label>
                                <select class="form-select" id="edit-persona-discapacidad" name="persona_discapacidad">
                                    <option value="">Seleccione...</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="edit-div-cual-discapacidad" style="display:none;">
                                <label for="edit-cual-discapacidad" class="form-label">Categoría discapacidad</label>
                                <select class="form-select" id="edit-cual-discapacidad" name="cual_discapacidad">
                                    <option value="">Categoría discapacidad...</option>
                                    <option value="Auditiva">Auditiva</option>
                                    <option value="Física">Física</option>
                                    <option value="Intelectual">Intelectual</option>
                                    <option value="Múltiple">Múltiple</option>
                                    <option value="Psicosocial">Psicosocial</option>
                                    <option value="Sordoceguera">Sordoceguera</option>
                                    <option value="Visual">Visual</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Cabeza de hogar -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-cabeza-hogar" class="form-label">¿Cabeza de hogar?</label>
                                <select class="form-select" id="edit-cabeza-hogar" name="cabeza_hogar">
                                    <option value="">Seleccione...</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Se reconoce como y Orientación sexual -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-se-reconoce-como" class="form-label">Se reconoce como</label>
                                <select class="form-select" id="edit-se-reconoce-como" name="se_reconoce_como">
                                    <option value="">Seleccione...</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Hombre trans">Hombre trans</option>
                                    <option value="Mujer">Mujer</option>
                                    <option value="Mujer trans">Mujer trans</option>
                                    <option value="No binaria">No binaria</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-orientacion-sexual" class="form-label">Orientación sexual</label>
                                <select class="form-select" id="edit-orientacion-sexual" name="orientacion_sexual">
                                    <option value="">Seleccione...</option>
                                    <option value="Heterosexual">Heterosexual</option>
                                    <option value="Homosexual">Homosexual</option>
                                    <option value="Asexual">Asexual</option>
                                    <option value="Bisexual">Bisexual</option>
                                    <option value="Pansexual">Pansexual</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Grupo étnico -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-grupo-etnico" class="form-label">Grupo étnico</label>
                                <select class="form-select" id="edit-grupo-etnico" name="grupo_etnico">
                                    <option value="">Seleccione...</option>
                                    <option value="Indígena">Indígena</option>
                                    <option value="Rom">Rom</option>
                                    <option value="Raizal">Raizal</option>
                                    <option value="Palenquero de San Basilio">Palenquero de San Basilio</option>
                                    <option value="Negro/Mulato">Negro/Mulato</option>
                                    <option value="Mestizo">Mestizo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Tipo de salud -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-tipo-salud" class="form-label">Tipo de salud</label>
                                <select class="form-select" id="edit-tipo-salud" name="tipo_salud">
                                    <option value="">Seleccione...</option>
                                    <option value="Régimen subsidiado">Régimen subsidiado</option>
                                    <option value="Régimen contributivo">Régimen contributivo</option>
                                    <option value="Régimen vinculado">Régimen vinculado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Condición Ocupación y Condición Componente -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-condicion-ocupacion" class="form-label">Condición Ocupación</label>
                                <select class="form-select" id="edit-condicion-ocupacion" name="condicion_ocupacion">
                                    <option value="">Seleccione...</option>
                                    <option value="Ama de Casa">Ama de Casa</option>
                                    <option value="Estudiante">Estudiante</option>
                                    <option value="Empleado">Empleado</option>
                                    <option value="Desempleado">Desempleado</option>
                                    <option value="Independiente">Independiente</option>
                                    <option value="Pensionado">Pensionado</option>
                                    <option value="Buscando Empleo">Buscando Empleo</option>
                                    <option value="Ninguno">Ninguno</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-condicion-componente" class="form-label">Condición Componente</label>
                                <select class="form-select" id="edit-condicion-componente" name="condicion_componente">
                                    <option value="">Seleccione...</option>
                                    <option value="C.M Retiro Definitivo">C.M Retiro Definitivo</option>
                                    <option value="C.M Activo">C.M Activo</option>
                                    <option value="C.M BDUA">C.M BDUA</option>
                                    <option value="C.M Bloqueo Registraduria">C.M Bloqueo Registraduria</option>
                                    <option value="C.M Duplicidad Documento">C.M Duplicidad Documento</option>
                                    <option value="C.M Ejercicio Mendicidad Comprobada">C.M Ejercicio Mendicidad Comprobada</option>
                                    <option value="C.M En lista de Espera">C.M En lista de Espera</option>
                                    <option value="C.M Fallecido">C.M Fallecido</option>
                                    <option value="C.M Fallecido sin Certificado">C.M Fallecido sin Certificado</option>
                                    <option value="C.M Familias en Accion">C.M Familias en Accion</option>
                                    <option value="C.M Fuera de la Ciudad">C.M Fuera de la Ciudad</option>
                                    <option value="Visita psicosocial fallida">Visita psicosocial fallida</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Fecha de Ingreso y Estado -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-fecha-ingreso" class="form-label">Fecha de Ingreso al Programa</label>
                                <input type="date" class="form-control" id="edit-fecha-ingreso" name="fecha_ingreso_cm">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-estado" class="form-label">Estado</label>
                                <select class="form-select" id="edit-estado" name="estado_cm" required>
                                    <option value="ACTIVO">ACTIVO</option>
                                    <?php foreach ($opciones_estado_cm as $opcion): ?>
                                        <option value="<?php echo htmlspecialchars($opcion); ?>">
                                            <?php echo htmlspecialchars($opcion); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Meta, Actividad, Acción y Política Pública -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-meta" class="form-label">Meta</label>
                                <select class="form-select" id="edit-meta" name="id_meta">
                                    <option value="">Seleccione Meta...</option>
                                    <?php 
                                    $result_metas_edit = $mysqli->query("SELECT id_meta, descripcion_meta FROM metas ORDER BY descripcion_meta ASC");
                                    while ($meta = $result_metas_edit->fetch_assoc()) { ?>
                                        <option value="<?= $meta['id_meta']; ?>"><?= $meta['descripcion_meta']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-actividad" class="form-label">Actividad</label>
                                <select class="form-select" id="edit-actividad" name="id_actividad" disabled>
                                    <option value="">Seleccione Actividad...</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-accion" class="form-label">Acción</label>
                                <select class="form-select" id="edit-accion" name="id_accion" disabled>
                                    <option value="">Seleccione Acción...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-politica-publica" class="form-label">Política Pública</label>
                                <select class="form-select" id="edit-politica-publica" name="id_politica_publica">
                                    <option value="">Seleccione Política Pública...</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit-observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="edit-observaciones" name="observaciones_cm" rows="3"></textarea>
                            </div>
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
            // Inicializar DataTable con datos ya cargados
            $('#salesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "pageLength": 25,
                "order": [[1, "asc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 7 } // Columna de acciones no ordenable
                ]
            });

            // Calcular edad automáticamente
            $('#fecha_nacimiento').on('change', function() {
                var fecha_nacimiento = new Date($(this).val());
                var hoy = new Date();
                var edad = hoy.getFullYear() - fecha_nacimiento.getFullYear();
                var mes = hoy.getMonth() - fecha_nacimiento.getMonth();
                if (mes < 0 || (mes === 0 && hoy.getDate() < fecha_nacimiento.getDate())) {
                    edad--;
                }
                $('#edad').val(edad);
            });

            // Cargar datos en modal de edición
            $('#modalEdicion').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                console.log('=== ABRIENDO MODAL EDICIÓN ===');
                console.log('Cédula recibida:', button.data('cedula'));
                console.log('Estado recibido:', button.data('estado'));
                
                // Datos básicos
                modal.find('#edit-cedula-original').val(button.data('cedula'));
                modal.find('#edit-cedula').val(button.data('cedula'));
                
                console.log('Cedula original asignada:', modal.find('#edit-cedula-original').val());
                console.log('Cedula asignada:', modal.find('#edit-cedula').val());
                modal.find('#edit-tipo-identificacion').val(button.data('tipo-identificacion'));
                modal.find('#edit-nombres').val(button.data('nombres'));
                modal.find('#edit-apellidos').val(button.data('apellidos'));
                modal.find('#edit-genero').val(button.data('genero'));
                modal.find('#edit-telefono').val(button.data('telefono'));
                modal.find('#edit-telefono-referencia').val(button.data('telefono-referencia'));
                modal.find('#edit-referencia').val(button.data('referencia'));
                modal.find('#edit-fecha-nacimiento').val(button.data('fecha-nacimiento'));
                modal.find('#edit-edad').val(button.data('edad'));
                modal.find('#edit-grupo-sisben').val(button.data('grupo-sisben'));
                
                // Ubicación
                modal.find('#edit-direccion').val(button.data('direccion'));
                modal.find('#edit-barrio').val(button.data('barrio'));
                modal.find('#edit-comuna').val(button.data('comuna'));
                modal.find('#edit-zona').val(button.data('zona'));
                modal.find('#edit-departamento').val(button.data('departamento'));
                modal.find('#edit-municipio').val(button.data('municipio'));
                
                // Salud
                modal.find('#edit-convivencia-actual').val(button.data('convivencia-actual'));
                
                // Caracterización
                modal.find('#edit-persona-discapacidad').val(button.data('persona-discapacidad'));
                modal.find('#edit-cual-discapacidad').val(button.data('cual-discapacidad'));
                modal.find('#edit-cabeza-hogar').val(button.data('cabeza-hogar'));
                modal.find('#edit-se-reconoce-como').val(button.data('se-reconoce-como'));
                modal.find('#edit-orientacion-sexual').val(button.data('orientacion-sexual'));
                modal.find('#edit-grupo-etnico').val(button.data('grupo-etnico'));
                modal.find('#edit-tipo-salud').val(button.data('tipo-salud'));
                modal.find('#edit-condicion-ocupacion').val(button.data('condicion-ocupacion'));
                modal.find('#edit-condicion-componente').val(button.data('condicion-componente'));
                
                // Estado y fecha de ingreso
                modal.find('#edit-fecha-ingreso').val(button.data('fecha-ingreso'));
                var estadoValue = button.data('estado');
                console.log('Cargando estado:', estadoValue);
                modal.find('#edit-estado').val(estadoValue);
                
                // Verificar si se seleccionó correctamente
                if (modal.find('#edit-estado').val() !== estadoValue) {
                    console.warn('El estado "' + estadoValue + '" no coincide con ninguna opción del select');
                    console.log('Opciones disponibles:', $('#edit-estado option').map(function() { return $(this).val(); }).get());
                }
                
                // Meta, actividad, acción y política pública
                modal.find('#edit-id-meta').val(button.data('id-meta'));
                modal.find('#edit-id-actividad').val(button.data('id-actividad'));
                modal.find('#edit-id-accion').val(button.data('id-accion'));
                modal.find('#edit-id-politica-publica').val(button.data('id-politica-publica'));
                
                // Observaciones
                modal.find('#edit-observaciones').val(button.data('observaciones'));
                
                // Manejar el campo de discapacidad
                if (button.data('persona-discapacidad') === 'Si') {
                    modal.find('#edit-div-cual-discapacidad').show();
                } else {
                    modal.find('#edit-div-cual-discapacidad').hide();
                }
                
                // Trigger para disparar cambios en metas/actividades si es necesario
                modal.find('#edit-id-meta').trigger('change');
            });
            
            // Debug: Mostrar valor del select de estado cuando cambie
            $('#edit-estado').on('change', function() {
                console.log('Estado seleccionado:', $(this).val());
            });
            
            // Debug: Interceptar submit del formulario de edición
            $('#modalEdicion form').on('submit', function(e) {
                var cedulaOriginal = $('#edit-cedula-original').val();
                var estadoSeleccionado = $('#edit-estado').val();
                
                console.log('=== ENVIANDO FORMULARIO DE EDICIÓN ===');
                console.log('Cédula original:', cedulaOriginal);
                console.log('Estado seleccionado:', estadoSeleccionado);
                console.log('Nombres:', $('#edit-nombres').val());
                
                if (!cedulaOriginal) {
                    e.preventDefault();
                    alert('ERROR: No se ha cargado la cédula original. No se puede actualizar.');
                    return false;
                }
                
                if (!estadoSeleccionado) {
                    e.preventDefault();
                    alert('ERROR: Debe seleccionar un estado.');
                    return false;
                }
                
                console.log('Formulario válido, enviando...');
                // Permitir que el formulario se envíe normalmente
            });

            // Confirmar eliminación (usando delegación de eventos)
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var cedula = $(this).data('cedula');
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
                        window.location.href = 'seePersonaCM.php?delete=' + cedula;
                    }
                });
            });

            // --- Autocompletado de Barrio y Comuna (Modal Agregar) ---
            const inputBarrio = document.getElementById('barrio');
            const datalistBarrios = document.getElementById('lista-barrios');
            const inputComuna = document.getElementById('comuna');
            const inputIdBarrio = document.getElementById('id_barrio_cm');
            const inputIdComuna = document.getElementById('id_comuna_cm');
            let barriosData = [];

            if (inputBarrio && datalistBarrios) {
                console.log('Autocompletado de barrios (agregar) inicializado');
                
                inputBarrio.addEventListener('input', function() {
                    const term = this.value.trim();
                    console.log('Buscando barrios para:', term);
                    
                    if (term.length < 2) {
                        datalistBarrios.innerHTML = '';
                        return;
                    }
                    
                    fetch('../persons/buscar_barrio.php?term=' + encodeURIComponent(term))
                        .then(res => {
                            console.log('Respuesta recibida:', res);
                            return res.json();
                        })
                        .then(data => {
                            console.log('Datos de barrios:', data);
                            barriosData = data;
                            datalistBarrios.innerHTML = '';
                            
                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(barrio => {
                                    const option = document.createElement('option');
                                    option.value = barrio.nombre_bar;
                                    option.setAttribute('data-id', barrio.id_bar);
                                    datalistBarrios.appendChild(option);
                                });
                            } else {
                                console.log('No se encontraron barrios');
                            }
                        })
                        .catch(err => {
                            console.error('Error al buscar barrios:', err);
                        });
                });

                inputBarrio.addEventListener('change', function() {
                    const selected = barriosData.find(b => b.nombre_bar.toLowerCase() === inputBarrio.value.trim().toLowerCase());
                    if (selected) {
                        inputComuna.value = selected.nombre_com || '';
                        if (inputIdBarrio) inputIdBarrio.value = selected.id_bar || '';
                        if (inputIdComuna) inputIdComuna.value = selected.id_com || '';
                    } else {
                        inputComuna.value = '';
                        if (inputIdBarrio) inputIdBarrio.value = '';
                        if (inputIdComuna) inputIdComuna.value = '';
                    }
                });
            }

            // --- Autocompletado de Barrio y Comuna (Modal Editar) ---
            const inputBarrioEdit = document.getElementById('edit-barrio');
            const datalistBarriosEdit = document.getElementById('edit-lista-barrios');
            const inputComunaEdit = document.getElementById('edit-comuna');
            const inputZonaEdit = document.getElementById('edit-zona');
            const inputIdBarrioEdit = document.getElementById('edit-id-barrio-cm');
            const inputIdComunaEdit = document.getElementById('edit-id-comuna-cm');
            let barriosDataEdit = [];

            if (inputBarrioEdit && datalistBarriosEdit) {
                console.log('Autocompletado de barrios (editar) inicializado');
                
                inputBarrioEdit.addEventListener('input', function() {
                    const term = this.value.trim();
                    console.log('Buscando barrios (editar) para:', term);
                    
                    if (term.length < 2) {
                        datalistBarriosEdit.innerHTML = '';
                        return;
                    }
                    
                    fetch('../persons/buscar_barrio.php?term=' + encodeURIComponent(term))
                        .then(res => {
                            console.log('Respuesta recibida (editar):', res);
                            return res.json();
                        })
                        .then(data => {
                            console.log('Datos de barrios (editar):', data);
                            barriosDataEdit = data;
                            datalistBarriosEdit.innerHTML = '';
                            
                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(barrio => {
                                    const option = document.createElement('option');
                                    option.value = barrio.nombre_bar;
                                    option.setAttribute('data-id', barrio.id_bar);
                                    datalistBarriosEdit.appendChild(option);
                                });
                            } else {
                                console.log('No se encontraron barrios (editar)');
                            }
                        })
                        .catch(err => {
                            console.error('Error al buscar barrios (editar):', err);
                        });
                });

                inputBarrioEdit.addEventListener('change', function() {
                    const selected = barriosDataEdit.find(b => b.nombre_bar.toLowerCase() === inputBarrioEdit.value.trim().toLowerCase());
                    if (selected) {
                        inputComunaEdit.value = selected.nombre_com || '';
                        inputZonaEdit.value = selected.zona_bar || '';
                        if (inputIdBarrioEdit) inputIdBarrioEdit.value = selected.id_bar || '';
                        if (inputIdComunaEdit) inputIdComunaEdit.value = selected.id_com || '';
                    } else {
                        inputComunaEdit.value = '';
                        inputZonaEdit.value = '';
                        if (inputIdBarrioEdit) inputIdBarrioEdit.value = '';
                        if (inputIdComunaEdit) inputIdComunaEdit.value = '';
                    }
                });
            }

            // --- Actualizar Zona al seleccionar barrio (Modal Agregar) ---
            inputBarrio.addEventListener('change', function() {
                const selected = barriosData.find(b => b.nombre_bar.toLowerCase() === inputBarrio.value.trim().toLowerCase());
                if (selected) {
                    inputComuna.value = selected.nombre_com || '';
                    const inputZona = document.getElementById('zona');
                    if (inputZona) inputZona.value = selected.zona_bar || '';
                    if (inputIdBarrio) inputIdBarrio.value = selected.id_bar || '';
                    if (inputIdComuna) inputIdComuna.value = selected.id_com || '';
                } else {
                    inputComuna.value = '';
                    const inputZona = document.getElementById('zona');
                    if (inputZona) inputZona.value = '';
                    if (inputIdBarrio) inputIdBarrio.value = '';
                    if (inputIdComuna) inputIdComuna.value = '';
                }
            });

            // --- Mostrar/Ocultar campo de discapacidad ---
            $('#persona_discapacidad').on('change', function() {
                if ($(this).val() === 'Si') {
                    $('#div_cual_discapacidad').show();
                } else {
                    $('#div_cual_discapacidad').hide();
                    $('#cual_discapacidad').val('');
                }
            });

            // *** FUNCIONALIDAD PARA META, ACTIVIDAD, ACCIÓN Y POLÍTICA PÚBLICA (MODAL AGREGAR) ***
            
            // Manejar selección de Meta para cargar Actividades
            $('#meta').on('change', function() {
                const idMeta = $(this).val();
                console.log('Meta seleccionada:', idMeta);

                // Limpiar y deshabilitar campos dependientes
                $('#actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

                if (idMeta) {
                    $.ajax({
                        url: 'getActividades.php',
                        type: 'POST',
                        data: {
                            id_meta: idMeta
                        },
                        success: function(response) {
                            console.log('Actividades recibidas:', response);
                            $('#actividad').append(response).prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar actividades:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al cargar las actividades',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });

            // Manejar selección de Actividad para cargar Acciones
            $('#actividad').on('change', function() {
                const idActividad = $(this).val();
                console.log('Actividad seleccionada:', idActividad);
                
                // Limpiar y deshabilitar campo de acciones
                $('#accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

                if (idActividad) {
                    $.ajax({
                        url: 'getAcciones.php',
                        type: 'POST',
                        data: {
                            id_actividad: idActividad
                        },
                        success: function(response) {
                            console.log('Acciones recibidas:', response);
                            $('#accion').append(response).prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar acciones:', error);
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
            $('#accion').on('change', function() {
                const idAccion = $(this).val();
                console.log('Acción seleccionada:', idAccion);
                
                // Vaciar y resetear el select de política pública
                $('#politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                $('#politica-publica').prop('selectedIndex', 0);
                
                if (idAccion) {
                    $.ajax({
                        url: 'getPoliticaPublica.php',
                        type: 'POST',
                        data: { id_accion: idAccion },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Políticas públicas recibidas:', response);
                            if (response && response.politicas && response.politicas.length > 0) {
                                response.politicas.forEach(function(p) {
                                    $('#politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                });
                            } else {
                                $('#politica-publica').append('<option value="">No asignada</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar políticas públicas:', error);
                            $('#politica-publica').append('<option value="">Error al consultar</option>');
                        }
                    });
                }
            });

            // *** FUNCIONALIDAD PARA META, ACTIVIDAD, ACCIÓN Y POLÍTICA PÚBLICA (MODAL EDITAR) ***
            
            // Manejar selección de Meta para cargar Actividades (Modal Edición)
            $('#edit-meta').on('change', function() {
                const idMeta = $(this).val();
                console.log('Meta seleccionada (editar):', idMeta);

                // Limpiar y deshabilitar campos dependientes
                $('#edit-actividad').empty().append('<option value="">Seleccione Actividad...</option>').prop('disabled', true);
                $('#edit-accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#edit-politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

                if (idMeta) {
                    $.ajax({
                        url: 'getActividades.php',
                        type: 'POST',
                        data: {
                            id_meta: idMeta
                        },
                        success: function(response) {
                            console.log('Actividades recibidas (editar):', response);
                            $('#edit-actividad').append(response).prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar actividades (editar):', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al cargar las actividades',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });

            // Manejar selección de Actividad para cargar Acciones (Modal Edición)
            $('#edit-actividad').on('change', function() {
                const idActividad = $(this).val();
                console.log('Actividad seleccionada (editar):', idActividad);
                
                // Limpiar y deshabilitar campo de acciones
                $('#edit-accion').empty().append('<option value="">Seleccione Acción...</option>').prop('disabled', true);
                $('#edit-politica-publica').empty().append('<option value="">Seleccione Política Pública...</option>');

                if (idActividad) {
                    $.ajax({
                        url: 'getAcciones.php',
                        type: 'POST',
                        data: {
                            id_actividad: idActividad
                        },
                        success: function(response) {
                            console.log('Acciones recibidas (editar):', response);
                            $('#edit-accion').append(response).prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar acciones (editar):', error);
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

            // Manejar selección de Acción para consultar políticas públicas (Modal Edición)
            $('#edit-accion').on('change', function() {
                const idAccion = $(this).val();
                console.log('Acción seleccionada (editar):', idAccion);
                
                // Vaciar y resetear el select de política pública
                $('#edit-politica-publica').empty().append('<option value="" selected>Seleccione Política Pública...</option>');
                $('#edit-politica-publica').prop('selectedIndex', 0);
                
                if (idAccion) {
                    $.ajax({
                        url: 'getPoliticaPublica.php',
                        type: 'POST',
                        data: { id_accion: idAccion },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Políticas públicas recibidas (editar):', response);
                            if (response && response.politicas && response.politicas.length > 0) {
                                response.politicas.forEach(function(p) {
                                    $('#edit-politica-publica').append('<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>');
                                });
                            } else {
                                $('#edit-politica-publica').append('<option value="">No asignada</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar políticas públicas (editar):', error);
                            $('#edit-politica-publica').append('<option value="">Error al consultar</option>');
                        }
                    });
                }
            });

            // --- Mostrar/Ocultar campo de discapacidad (Modal Editar) ---
            $('#edit-persona-discapacidad').on('change', function() {
                if ($(this).val() === 'Si') {
                    $('#edit-div-cual-discapacidad').show();
                } else {
                    $('#edit-div-cual-discapacidad').hide();
                    $('#edit-cual-discapacidad').val('');
                }
            });

            // Calcular edad automáticamente en modal editar
            $('#edit-fecha-nacimiento').on('change', function() {
                const birthDate = new Date($(this).val());
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                $('#edit-edad').val(age >= 0 ? age : '');
            });

            // Poblar modal de edición cuando se hace clic en el botón editar
            $(document).on('click', '.btn-edit', function() {
                const btn = $(this);
                
                // Datos básicos
                $('#edit-cedula-original').val(btn.data('cedula'));
                $('#edit-tipo-identificacion').val(btn.data('tipo-identificacion'));
                $('#edit-cedula').val(btn.data('cedula'));
                $('#edit-nombres').val(btn.data('nombres'));
                $('#edit-apellidos').val(btn.data('apellidos'));
                $('#edit-genero').val(btn.data('genero'));
                $('#edit-telefono').val(btn.data('telefono'));
                $('#edit-telefono-referencia').val(btn.data('telefono-referencia'));
                $('#edit-referencia').val(btn.data('referencia'));
                $('#edit-fecha-nacimiento').val(btn.data('fecha-nacimiento'));
                $('#edit-edad').val(btn.data('edad'));
                $('#edit-grupo-sisben').val(btn.data('grupo-sisben'));
                
                // Dirección
                $('#edit-direccion').val(btn.data('direccion'));
                $('#edit-barrio').val(btn.data('barrio'));
                $('#edit-comuna').val(btn.data('comuna'));
                $('#edit-zona').val(btn.data('zona'));
                $('#edit-departamento').val(btn.data('departamento'));
                $('#edit-municipio').val(btn.data('municipio'));
                
                // Salud
                $('#edit-convivencia-actual').val(btn.data('convivencia-actual'));
                
                // Caracterización
                const personaDiscapacidad = btn.data('persona-discapacidad');
                $('#edit-persona-discapacidad').val(personaDiscapacidad);
                if (personaDiscapacidad === 'Si') {
                    $('#edit-div-cual-discapacidad').show();
                    $('#edit-cual-discapacidad').val(btn.data('cual-discapacidad'));
                } else {
                    $('#edit-div-cual-discapacidad').hide();
                    $('#edit-cual-discapacidad').val('');
                }
                
                $('#edit-cabeza-hogar').val(btn.data('cabeza-hogar'));
                $('#edit-se-reconoce-como').val(btn.data('se-reconoce-como'));
                $('#edit-orientacion-sexual').val(btn.data('orientacion-sexual'));
                $('#edit-grupo-etnico').val(btn.data('grupo-etnico'));
                $('#edit-tipo-salud').val(btn.data('tipo-salud'));
                $('#edit-condicion-ocupacion').val(btn.data('condicion-ocupacion'));
                $('#edit-condicion-componente').val(btn.data('condicion-componente'));
                
                // Fecha ingreso y estado
                $('#edit-fecha-ingreso').val(btn.data('fecha-ingreso'));
                $('#edit-estado').val(btn.data('estado'));
                
                // Meta, Actividad, Acción, Política Pública
                const idMeta = btn.data('id-meta');
                const idActividad = btn.data('id-actividad');
                const idAccion = btn.data('id-accion');
                const idPoliticaPublica = btn.data('id-politica-publica');
                
                $('#edit-meta').val(idMeta || '');
                
                // Si hay meta, cargar las actividades
                if (idMeta) {
                    $.ajax({
                        url: 'getActividades.php',
                        type: 'POST',
                        data: { id_meta: idMeta },
                        success: function(response) {
                            $('#edit-actividad').html('<option value="">Seleccione Actividad...</option>' + response).prop('disabled', false);
                            $('#edit-actividad').val(idActividad || '');
                            
                            // Si hay actividad, cargar las acciones
                            if (idActividad) {
                                $.ajax({
                                    url: 'getAcciones.php',
                                    type: 'POST',
                                    data: { id_actividad: idActividad },
                                    success: function(response) {
                                        $('#edit-accion').html('<option value="">Seleccione Acción...</option>' + response).prop('disabled', false);
                                        $('#edit-accion').val(idAccion || '');
                                        
                                        // Si hay acción, cargar políticas públicas
                                        if (idAccion) {
                                            $.ajax({
                                                url: 'getPoliticaPublica.php',
                                                type: 'POST',
                                                data: { id_accion: idAccion },
                                                dataType: 'json',
                                                success: function(response) {
                                                    let options = '<option value="">Seleccione Política Pública...</option>';
                                                    if (response && response.politicas && response.politicas.length > 0) {
                                                        response.politicas.forEach(function(p) {
                                                            options += '<option value="' + p.id_politica + '">' + p.descripcion_politica + '</option>';
                                                        });
                                                    }
                                                    $('#edit-politica-publica').html(options);
                                                    $('#edit-politica-publica').val(idPoliticaPublica || '');
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                }
                
                // Observaciones
                $('#edit-observaciones').val(btn.data('observaciones'));
            });
        });
    </script>
</body>
</html>
