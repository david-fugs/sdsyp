<?php
session_start();
include("../../conexion.php");

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    header("Location: ../../access.php");
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id'];

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
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="filter-cedula">Cédula</label>
                        <input type="text" id="filter-cedula" class="modern-input" placeholder="Buscar por cédula...">
                    </div>
                    <div class="filter-group">
                        <label for="filter-nombre">Nombre</label>
                        <input type="text" id="filter-nombre" class="modern-input" placeholder="Buscar por nombre...">
                    </div>
                    <div class="filter-group">
                        <label for="filter-estado">Estado</label>
                        <select id="filter-estado" class="modern-select">
                            <option value="">Todos los estados</option>
                            <?php
                            // Recargar el resultado para usarlo en filtros
                            $result_condiciones_cm->data_seek(0);
                            while ($row = $result_condiciones_cm->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($row['descripcion_condicion']); ?>">
                                    <?php echo htmlspecialchars($row['descripcion_condicion']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="button" id="btn-filter" class="btn-modern btn-primary">
                            <i class="bi bi-search"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
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
                    <tbody id="table-body">
                        <!-- Contenido cargado dinámicamente -->
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

                        <!-- Fila 3.7: Correo y Fecha de Nacimiento -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="email" class="form-control" id="correo_persona" name="correo_cm" placeholder="Correo">
                                <label for="correo_persona">Correo</label>
                            </div>
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

                        <!-- Fila 8: EPS, Peso, Talla -->
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="text" class="form-control" id="eps" name="eps" placeholder="EPS">
                                <label for="eps">EPS</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="number" step="0.01" class="form-control" id="peso" name="peso" placeholder="Peso (kg)">
                                <label for="peso">Peso (kg)</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <input type="number" step="0.01" class="form-control" id="talla" name="talla" placeholder="Talla (cm)">
                                <label for="talla">Talla (cm)</label>
                            </div>
                        </div>

                        <!-- Fila 9: Patologías y Factores de Riesgo -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="patologias" name="patologias">
                                    <option value="" selected>Seleccione...</option>
                                    <option>Osteomuscular</option>
                                    <option>Respiratoria</option>
                                    <option>Diabetes</option>
                                    <option>EPOC</option>
                                    <option>Trastorno afectivo bipolar</option>
                                    <option>Física</option>
                                    <option>Cataratas senil nuclear</option>
                                    <option>Hipertensión arterial</option>
                                    <option>HTA</option>
                                    <option>Hipertensión senecial</option>
                                    <option>Hipotiroidismo</option>
                                    <option>ICC</option>
                                    <option>Mental</option>
                                    <option>Ninguna</option>
                                    <option>No aplica</option>
                                    <option>Osteoastromuscular</option>
                                    <option>Otras</option>
                                </select>
                                <label for="patologias">Patologías</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="factores_riesgo" name="factores_riesgo">
                                    <option value="" selected>Seleccione...</option>
                                    <option>Alcohol</option>
                                    <option>Tabaco</option>
                                    <option>Tabaco y alcohol</option>
                                    <option>Otros</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="factores_riesgo">Factores de Riesgo</label>
                            </div>
                        </div>

                        <!-- Fila 10: Factores Preventivos e Ingresos Económicos -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="factores_preventivos" name="factores_preventivos">
                                    <option value="" selected>Seleccione...</option>
                                    <option>Ejercicio</option>
                                    <option>Dieta</option>
                                    <option>Ejercicio y dieta</option>
                                    <option>Otra</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="factores_preventivos">Factores Preventivos</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="ingresos_economicos" name="ingresos_economicos">
                                    <option value="" selected>Seleccione...</option>
                                    <option>Pensión</option>
                                    <option>Estado</option>
                                    <option>Conyuge</option>
                                    <option>Mendicidad</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="ingresos_economicos">Ingresos Económicos</label>
                            </div>
                        </div>

                        <!-- Fila 11: Convivencia, Resultado Actividad, Remisión -->
                        <div class="row">
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="convivencia_actual" name="convivencia_actual">
                                    <option value="" selected>Seleccione...</option>
                                    <option>Pareja</option>
                                    <option>Familia</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="convivencia_actual">Convivencia Actual</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="resultado_actividad" name="resultado_actividad">
                                    <option value="" selected>Seleccione...</option>
                                    <option>Con deterioro leve</option>
                                    <option>Con deterioro moderado</option>
                                    <option>Con deterioro severo</option>
                                    <option>Sin deterioro</option>
                                    <option>Delgadez</option>
                                    <option>Normal</option>
                                    <option>Obesidad grado 1</option>
                                    <option>Obesidad grado II</option>
                                    <option>Obesidad grado III</option>
                                    <option>Sobrepeso</option>
                                    <option>Depresión</option>
                                    <option>En riesgo</option>
                                    <option>Saludable</option>
                                    <option>Demencia</option>
                                    <option>Demencia leve</option>
                                    <option>Deterioro cognitivo leve</option>
                                    <option>Extrema</option>
                                    <option>Leve</option>
                                    <option>Moderada</option>
                                    <option>Ninguna disfuncionalidad</option>
                                    <option>Severa</option>
                                    <option>Disfuncionalidad extrema</option>
                                    <option>Disfuncionalidad leve</option>
                                    <option>Disfuncionalidad moderada</option>
                                    <option>Disfuncionalidad severa</option>
                                    <option>Ninguna</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="resultado_actividad">Resultado según actividad</label>
                            </div>
                            <div class="col-md-4 mb-3 form-floating">
                                <select class="form-select" id="remision" name="remision">
                                    <option value="" selected>Seleccione...</option>
                                    <option>Actividad física</option>
                                    <option>Enfermería</option>
                                    <option>Gerontología</option>
                                    <option>Neuropsicología</option>
                                    <option>Psicología EPS</option>
                                    <option>No aplica</option>
                                </select>
                                <label for="remision">Remisión</label>
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

                        <!-- Fila 13: Cabeza de hogar y Líder comunidad -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="cabeza_hogar" name="cabeza_hogar">
                                    <option value="" selected>¿Cabeza de hogar?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="cabeza_hogar">¿Cabeza de hogar?</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="lider_comunidad" name="lider_comunidad">
                                    <option value="" selected>¿Líder/representante comunidad?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="lider_comunidad">¿Líder/representante comunidad?</label>
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

                        <!-- Fila 15: Experiencia migratoria y Grupo étnico -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="experiencia_migratoria" name="experiencia_migratoria">
                                    <option value="" selected>¿Experiencia migratoria?</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <label for="experiencia_migratoria">¿Experiencia migratoria?</label>
                            </div>
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

                        <!-- Fila 16: Tipo de salud y Nivel educativo -->
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
                            <div class="col-md-6 mb-3 form-floating">
                                <select class="form-select" id="nivel_educativo" name="nivel_educativo">
                                    <option value="" selected>Nivel educativo...</option>
                                    <option value="Preescolar">Preescolar</option>
                                    <option value="Básica primaria">Básica primaria</option>
                                    <option value="Básica secundaria">Básica secundaria</option>
                                    <option value="Media académica o clásica">Media académica o clásica</option>
                                    <option value="Media técnica">Media técnica</option>
                                    <option value="Normalista">Normalista</option>
                                    <option value="Técnica profesional">Técnica profesional</option>
                                    <option value="Tecnológica">Tecnológica</option>
                                    <option value="Profesional">Profesional</option>
                                </select>
                                <label for="nivel_educativo">Nivel educativo</label>
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
                                    <?php
                                    // Recargar el resultado para usarlo en modal agregar
                                    $result_condiciones_cm->data_seek(0);
                                    while ($row = $result_condiciones_cm->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($row['descripcion_condicion']); ?>">
                                            <?php echo htmlspecialchars($row['descripcion_condicion']); ?>
                                        </option>
                                    <?php endwhile; ?>
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

                        <!-- Fila: Correo y Grupo SISBEN -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="edit-correo" name="correo_cm">
                            </div>
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

                        <!-- Fila: EPS, Peso, Talla -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit-eps" class="form-label">EPS</label>
                                <input type="text" class="form-control" id="edit-eps" name="eps">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-peso" class="form-label">Peso (kg)</label>
                                <input type="number" step="0.01" class="form-control" id="edit-peso" name="peso">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-talla" class="form-label">Talla (cm)</label>
                                <input type="number" step="0.01" class="form-control" id="edit-talla" name="talla">
                            </div>
                        </div>

                        <!-- Fila: Patologías y Factores de Riesgo -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-patologias" class="form-label">Patologías</label>
                                <select class="form-select" id="edit-patologias" name="patologias">
                                    <option value="">Seleccione...</option>
                                    <option>Osteomuscular</option>
                                    <option>Respiratoria</option>
                                    <option>Diabetes</option>
                                    <option>EPOC</option>
                                    <option>Trastorno afectivo bipolar</option>
                                    <option>Física</option>
                                    <option>Cataratas senil nuclear</option>
                                    <option>Hipertensión arterial</option>
                                    <option>HTA</option>
                                    <option>Hipertensión senecial</option>
                                    <option>Hipotiroidismo</option>
                                    <option>ICC</option>
                                    <option>Mental</option>
                                    <option>Ninguna</option>
                                    <option>No aplica</option>
                                    <option>Osteoastromuscular</option>
                                    <option>Otras</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-factores-riesgo" class="form-label">Factores de Riesgo</label>
                                <select class="form-select" id="edit-factores-riesgo" name="factores_riesgo">
                                    <option value="">Seleccione...</option>
                                    <option>Alcohol</option>
                                    <option>Tabaco</option>
                                    <option>Tabaco y alcohol</option>
                                    <option>Otros</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Factores Preventivos e Ingresos Económicos -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-factores-preventivos" class="form-label">Factores Preventivos</label>
                                <select class="form-select" id="edit-factores-preventivos" name="factores_preventivos">
                                    <option value="">Seleccione...</option>
                                    <option>Ejercicio</option>
                                    <option>Dieta</option>
                                    <option>Ejercicio y dieta</option>
                                    <option>Otra</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-ingresos-economicos" class="form-label">Ingresos Económicos</label>
                                <select class="form-select" id="edit-ingresos-economicos" name="ingresos_economicos">
                                    <option value="">Seleccione...</option>
                                    <option>Pensión</option>
                                    <option>Estado</option>
                                    <option>Conyuge</option>
                                    <option>Mendicidad</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Convivencia, Resultado Actividad, Remisión -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit-convivencia-actual" class="form-label">Convivencia Actual</label>
                                <select class="form-select" id="edit-convivencia-actual" name="convivencia_actual">
                                    <option value="">Seleccione...</option>
                                    <option>Pareja</option>
                                    <option>Familia</option>
                                    <option>Otro</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-resultado-actividad" class="form-label">Resultado según actividad</label>
                                <select class="form-select" id="edit-resultado-actividad" name="resultado_actividad">
                                    <option value="">Seleccione...</option>
                                    <option>Con deterioro leve</option>
                                    <option>Con deterioro moderado</option>
                                    <option>Con deterioro severo</option>
                                    <option>Sin deterioro</option>
                                    <option>Delgadez</option>
                                    <option>Normal</option>
                                    <option>Obesidad grado 1</option>
                                    <option>Obesidad grado II</option>
                                    <option>Obesidad grado III</option>
                                    <option>Sobrepeso</option>
                                    <option>Depresión</option>
                                    <option>En riesgo</option>
                                    <option>Saludable</option>
                                    <option>Demencia</option>
                                    <option>Demencia leve</option>
                                    <option>Deterioro cognitivo leve</option>
                                    <option>Extrema</option>
                                    <option>Leve</option>
                                    <option>Moderada</option>
                                    <option>Ninguna disfuncionalidad</option>
                                    <option>Severa</option>
                                    <option>Disfuncionalidad extrema</option>
                                    <option>Disfuncionalidad leve</option>
                                    <option>Disfuncionalidad moderada</option>
                                    <option>Disfuncionalidad severa</option>
                                    <option>Ninguna</option>
                                    <option>No aplica</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit-remision" class="form-label">Remisión</label>
                                <select class="form-select" id="edit-remision" name="remision">
                                    <option value="">Seleccione...</option>
                                    <option>Actividad física</option>
                                    <option>Enfermería</option>
                                    <option>Gerontología</option>
                                    <option>Neuropsicología</option>
                                    <option>Psicología EPS</option>
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

                        <!-- Fila: Cabeza de hogar y Líder comunidad -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-cabeza-hogar" class="form-label">¿Cabeza de hogar?</label>
                                <select class="form-select" id="edit-cabeza-hogar" name="cabeza_hogar">
                                    <option value="">Seleccione...</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-lider-comunidad" class="form-label">¿Líder/representante comunidad?</label>
                                <select class="form-select" id="edit-lider-comunidad" name="lider_comunidad">
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

                        <!-- Fila: Experiencia migratoria y Grupo étnico -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-experiencia-migratoria" class="form-label">¿Experiencia migratoria?</label>
                                <select class="form-select" id="edit-experiencia-migratoria" name="experiencia_migratoria">
                                    <option value="">Seleccione...</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
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

                        <!-- Fila: Tipo de salud y Nivel educativo -->
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
                            <div class="col-md-6 mb-3">
                                <label for="edit-nivel-educativo" class="form-label">Nivel educativo</label>
                                <select class="form-select" id="edit-nivel-educativo" name="nivel_educativo">
                                    <option value="">Seleccione...</option>
                                    <option value="Preescolar">Preescolar</option>
                                    <option value="Básica primaria">Básica primaria</option>
                                    <option value="Básica secundaria">Básica secundaria</option>
                                    <option value="Media académica o clásica">Media académica o clásica</option>
                                    <option value="Media técnica">Media técnica</option>
                                    <option value="Normalista">Normalista</option>
                                    <option value="Técnica profesional">Técnica profesional</option>
                                    <option value="Tecnológica">Tecnológica</option>
                                    <option value="Profesional">Profesional</option>
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
                                    <?php
                                    // Recargar el resultado para usarlo en modal editar
                                    $result_condiciones_cm->data_seek(0);
                                    while ($row = $result_condiciones_cm->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($row['descripcion_condicion']); ?>">
                                            <?php echo htmlspecialchars($row['descripcion_condicion']); ?>
                                        </option>
                                    <?php endwhile; ?>
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
            // Función para cargar datos
            function loadTableData() {
                $.ajax({
                    url: 'getPersonasCM.php',
                    type: 'GET',
                    data: {
                        cedula: $('#filter-cedula').val(),
                        nombre: $('#filter-nombre').val(),
                        estado: $('#filter-estado').val()
                    },
                    success: function(data) {
                        $('#table-body').html(data);
                        
                        // Destruir DataTable si ya existe
                        if ($.fn.DataTable.isDataTable('#salesTable')) {
                            $('#salesTable').DataTable().destroy();
                        }

                        // Inicializar DataTable
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
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar datos:', error);
                        $('#table-body').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar datos</td></tr>');
                    }
                });
            }

            // Cargar datos inicialmente
            loadTableData();

            // Botón de filtrar
            $('#btn-filter').on('click', function() {
                loadTableData();
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
                
                modal.find('#edit-cedula-original').val(button.data('cedula'));
                modal.find('#edit-cedula').val(button.data('cedula'));
                modal.find('#edit-tipo-identificacion').val(button.data('tipo-identificacion'));
                modal.find('#edit-nombres').val(button.data('nombres'));
                modal.find('#edit-apellidos').val(button.data('apellidos'));
                modal.find('#edit-genero').val(button.data('genero'));
                modal.find('#edit-telefono').val(button.data('telefono'));
                modal.find('#edit-fecha-nacimiento').val(button.data('fecha-nacimiento'));
                modal.find('#edit-estado').val(button.data('estado'));
                modal.find('#edit-direccion').val(button.data('direccion'));
                modal.find('#edit-barrio').val(button.data('barrio'));
                modal.find('#edit-comuna').val(button.data('comuna'));
                modal.find('#edit-observaciones').val(button.data('observaciones'));
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
                $('#edit-correo').val(btn.data('correo'));
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
                $('#edit-eps').val(btn.data('eps'));
                $('#edit-peso').val(btn.data('peso'));
                $('#edit-talla').val(btn.data('talla'));
                $('#edit-patologias').val(btn.data('patologias'));
                $('#edit-factores-riesgo').val(btn.data('factores-riesgo'));
                $('#edit-factores-preventivos').val(btn.data('factores-preventivos'));
                $('#edit-ingresos-economicos').val(btn.data('ingresos-economicos'));
                $('#edit-convivencia-actual').val(btn.data('convivencia-actual'));
                $('#edit-resultado-actividad').val(btn.data('resultado-actividad'));
                $('#edit-remision').val(btn.data('remision'));
                
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
                $('#edit-lider-comunidad').val(btn.data('lider-comunidad'));
                $('#edit-se-reconoce-como').val(btn.data('se-reconoce-como'));
                $('#edit-orientacion-sexual').val(btn.data('orientacion-sexual'));
                $('#edit-experiencia-migratoria').val(btn.data('experiencia-migratoria'));
                $('#edit-grupo-etnico').val(btn.data('grupo-etnico'));
                $('#edit-tipo-salud').val(btn.data('tipo-salud'));
                $('#edit-nivel-educativo').val(btn.data('nivel-educativo'));
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
