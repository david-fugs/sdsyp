<?php
    session_start();

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Verifica que el usuario está autenticado
    if (!isset($_SESSION['id_usu'])) {
        header("Location: ../../index.php");
        exit();
    }

    // Variables de sesión
    $id_usu = $_SESSION['id_usu'];
    $tipo_usu = $_SESSION['tipo_usu'];

    include("../../conexion.php");

    // Inicializar variable para almacenar datos
    $datos_usuario = [];

    // Validar si es tipo_usu == 3
    if ($tipo_usu == 3) {
        // Consulta para combinar tablas usuarios y asociados
        $query = "
            SELECT u.*, 
                   a.cedula_aso, 
                   a.nombre_aso, 
                   a.direccion_aso, 
                   a.tel_aso, 
                   a.email_aso, 
                   a.fecha_nacimiento_aso, 
                   a.estrato_aso, 
                   a.cel_aso, 
                   a.fecha_exp_cedula_aso, 
                   a.fecha_ingreso_aso
            FROM usuarios u
            JOIN asociados a ON u.usuario = a.cedula_aso
            WHERE u.id_usu = ?
        ";

        $stmt = $mysqli->prepare($query); // Preparar consulta segura
        $stmt->bind_param("i", $id_usu);  // Asignar parámetro
        $stmt->execute();                 // Ejecutar consulta
        $result = $stmt->get_result();    // Obtener resultados

        if ($result->num_rows > 0) {
            $datos_usuario = $result->fetch_assoc(); // Obtener datos del usuario
        } else {
            echo "<p class='text-danger'>No se encontraron datos para este usuario.</p>";
        }

        $stmt->close(); // Cerrar declaración
    }
    $mysqli->close(); // Cerrar conexión
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SOLICITUD</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../fontawesome/css/all.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/fed2435e21.js" crossorigin="anonymous"></script>
    <script type="text/javascript" src="../../js/jquery.min.js"></script>
    <!-- Using Select2 from a CDN-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .responsive {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <center>
            <img src='../../img/img3.png' class="responsive">
        </center>
        <br />
        <h1><b><i class="fa-regular fa-credit-card"></i> SOLICITUD DE CREDITO</b></h1>
        <p><i><b><font size=3 color=#c68615>*Datos obligatorios</i></b></font></p>
    
        <form action='agregarSolicitud.php' method="POST">
            <!-- Información del asociado -->
            <div class="form-group">
                <h3>DATOS PERSONALES</h3>
                <div class="row">
                <div class="col-12 col-sm-2">
                        <label for="tipo_doc_aso">* TIPO DOCUMENTO</label>
                        <select name="tipo_doc_aso" class="form-control" id="tipo_doc_aso" required>
                            <option value="C.C.">C.C.</option>
                            <option value="C.E.">C.E.</option>
                            <option value="PASAPORTE">PASAPORTE</option>
                            <option value="OTRO">OTRO</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="cedula_aso">* CÉDULA No.</label>
                        <input type='text' name='cedula_aso' class='form-control' id="cedula_aso" 
                               value='<?php echo $datos_usuario['cedula_aso'] ?? ''; ?>'  />
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="nombre_aso">NOMBRE ASOCIADO</label>
                        <input type='text' name='nombre_aso' id="nombre_aso" class='form-control' 
                               value='<?php echo $datos_usuario['nombre_aso'] ?? ''; ?>'  />
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="direccion_aso">DIRECCIÓN</label>
                        <input type='text' name='direccion_aso' class='form-control' 
                               value='<?php echo $datos_usuario['direccion_aso'] ?? ''; ?>' />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="fecha_exp_doc_aso">* FECHA EXPEDICION</label>
                        <input type="date" name="fecha_exp_doc_aso" class="form-control" id="fecha_exp_doc_aso" required
                            value='<?php echo $datos_usuario['fecha_exp_doc_aso'] ?? ''; ?>' />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="pais_exp_cedula_aso">* PAIS EXPEDICION</label>
                        <input type="text" name="pais_exp_cedula_aso" class="form-control" id="pais_exp_cedula_aso" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="dpto_exp_cedula_aso">* DEPARTAMENTO EXPEDICION</label>
                        <input type="text" name="dpto_exp_cedula_aso" class="form-control" id="dpto_exp_cedula_aso" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="ciudad_exp_cedula_aso">* CIUDAD EXPEDICION</label>
                        <input type="text" name="ciudad_exp_cedula_aso" class="form-control" id="ciudad_exp_cedula_aso" required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="fecha_nacimiento_aso">* FECHA NACIMIENTO</label>
                        <input type="date" name="fecha_nacimiento_aso" class="form-control" id="fecha_nacimiento_aso" required 
                            value='<?php echo $datos_usuario['fecha_nacimiento_aso'] ?? ''; ?>' />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="pais_naci_aso">* PAIS NACIMIENTO</label>
                        <input type="text" name="pais_naci_aso" class="form-control" id="pais_naci_aso" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="dpto_naci_aso">* DEPARTAMENTO NACIMIENTO</label>
                        <input type="text" name="dpto_naci_aso" class="form-control" id="dpto_naci_aso" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="ciudad_naci_aso">* CIUDAD NACIMIENTO</label>
                        <input type="text" name="ciudad_naci_aso" class="form-control" id="ciudad_naci_aso" required/>
                    </div>    
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="edad_aso">* EDAD</label>
                        <input type='number' name='edad_aso' class='form-control' id="edad_aso" required/>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="sexo_aso">* SEXO</label>
                        <select name="sexo_aso" class="form-control" id="sexo_aso" required>
                            <option value="Femenino">FEMENINO</option>
                            <option value="Masculino">MASCULINO</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="nacionalidad_aso">* NACIONALIDAD</label>
                        <input type='text' name='nacionalidad_aso' class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="estado_civil_aso">* ESTADO CIVIL</label>
                        <select name="estado_civil_aso" class="form-control" id="estado_civil_aso" required>
                            <option value="soltero">SOLTERO (A)</option>
                            <option value="casado">CASADO (A)</option>
                            <option value="union libre">UNION LIBRE</option>
                            <option value="divorciado">DIVORCIADO (A)</option>
                            <option value="viudo">VIUDO (A)</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="per_cargo_aso">* PERSONAS A CARGO</label>
                        <input type='number' name='per_cargo_aso' id="per_cargo_aso" class='form-control' required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="tip_vivienda_aso">* TIPO DE VIVIENDA</label>
                        <select name="tip_vivienda_aso" class="form-control" id="tip_vivienda_aso" required>
                            <option value="Arriendo">ARRIENDO</option>
                            <option value="Propia">PROPIA</option>
                            <option value="Familiar">FAMILIAR</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="barrio_aso">* BARRIO</label>
                        <input type='text' name='barrio_aso' id="barrio_aso" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="ciudad_aso">* CIUDAD</label>
                        <input type='text' name='ciudad_aso' class='form-control' required />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="departamente_aso">* DEPARTAMENTO</label>
                        <input type='text' name='departamente_aso' class='form-control' required>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="estrato_aso">* ESTRATO</label>
                        <input type='number' name='estrato_aso' id="estrato_aso" class='form-control' required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <label for="email_aso">* EMAIL</label>
                        <input type='email' name='email_aso' id="email_aso" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="tel_aso">* TELEFONO FIJO</label>
                        <input type='number' name='tel_aso' id="tel_aso" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="cel_aso">* CELULAR</label>
                        <input type='number' name='cel_aso' class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="nivel_educa_aso">* NIVEL EDUCATIVO</label>
                        <select name="nivel_educa_aso" class="form-control" id="nivel_educa_aso" required>
                            <option value="primaria">PRIMARIA</option>
                            <option value="bachiller">BACHILLER</option>
                            <option value="tecnico">TECNICO (A)</option>
                            <option value="tecnologo">TECNOLOGO (A)</option>
                            <option value="universitario">UNIVERSITARIO (A)</option>
                            <option value="especializacion">ESPECIALIZACION</option>
                            <option value="maestria">MAESTRIA</option>
                            <option value="doctorado">DOCTORADO</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                <div class="col-12 col-sm-6">
                        <label for="titulo_obte_aso">TITULO OBTENIDO</label>
                        <input type='text' name='titulo_obte_aso' class='form-control' />
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="titulo_pos_aso">TITULO EN POSGRADO</label>
                        <input type='text' name='titulo_pos_aso' class='form-control' />
                    </div>
                </div>
            </div>

            <!-- Solicitud de Crédito -->
            <div class="form-group">
                <h3>DATOS DEL CREDITO</h3>
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="fecha_sol">* FECHA</label>
                        <input type="text" name="fecha_sol" class="form-control" id="fecha_sol" 
                               value="<?php echo date('Y-m-d H:i:s'); ?>"  />
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="tipo_deudor_aso">* TIPO DEUDOR</label>
                        <select name="tipo_deudor_aso" class="form-control" id="tipo_deudor_aso" required>
                            <option value="DEUDOR PRINCIPAL">DEUDOR PRINCIPAL</option>
                            <option value="DEUDOR SOLIDARIO">DEUDOR SOLIDARIO</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="monto_sol">* MONTO SOLICITADO</label>
                        <input type="number" name="monto_sol" class="form-control" id="monto_sol" required />
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="plazo_sol">* PLAZO</label>
                        <select name="plazo_sol" class="form-control" id="plazo_sol" required>
                            <option value="12 MESES">12 MESES</option>
                            <option value="24 MESES">24 MESES</option>
                            <option value="36 MESES">36 MESES</option>
                            <option value="48 MESES">48 MESES</option>
                            <option value="60 MESES">60 MESES</option>
                            <option value="72 MESES">72 MESES</option>
                            <option value="84 MESES">84 MESES</option>
                            <option value="96 MESES">96 MESES</option>
                            <option value="108 MESES">108 MESES</option>
                            <option value="120 MESES">120 MESES</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="linea_cred_aso">* LÍNEA DE CRÉDITO</label>
                        <select name="linea_cred_aso" class="form-control" id="linea_cred_aso" required>
                            <option value="LIBRE INVERSION">LIBRE INVERSION</option>
                            <option value="CREDIAPORTES">CREDIAPORTES</option>
                            <option value="CREDITO EDUCATIVO">CREDITO EDUCATIVO</option>
                            <option value="CREDITO ROTATIVO">CREDITO ROTATIVO</option>
                            <option value="CREDITO PRIMA">CREDITO PRIMA</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Datos financieros -->
            <div class="form-group">
                <h3>DATOS LABORALES</h3>
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="ocupacion_sol">* OCUPACION</label>
                        <select name="ocupacion_sol" class="form-control" id="ocupacion_sol" required>
                            <option value="EMPLEADO">EMPLEADO (A)</option>
                            <option value="INDEPENDIENTE">INDEPENDIENTE</option>
                            <option value="COMERCIANTE">COMERCIANTE</option>
                            <option value="PENSIONADO">PENSIONADO (A)</option>
                            <option value="RENTISTA CAPITAL">RENTISTA CAPITAL</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="func_estad_sol">* FUNCIONARIO DEL ESTADO</label>
                        <select name="func_estad_sol" id="func_estad_sol" class="form-control" required>
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="emp_labo_sol">* EMPRESA DONDE LABORA</label>
                        <input type='text' name='emp_labo_sol' id="emp_labo_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="nit_emp_labo_sol">* NIT EMPRESA</label>
                        <input type='text' name='nit_emp_labo_sol' id="nit_emp_labo_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="act_emp_labo_sol">* ACTIVIDAD EMPRESA</label>
                        <input type='text' name='act_emp_labo_sol' id="act_emp_labo_sol" class='form-control' required/> 
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="dir_emp_sol">* DIRECCION EMPRESA</label>
                        <input name="dir_emp_sol" class="form-control" id="dir_emp_sol" required />
                    </div>    
                    <div class="col-12 col-sm-3">
                        <label for="ciudad_emp_sol">* CIUDAD</label>
                        <input name="ciudad_emp_sol" id="ciudad_emp_sol" class="form-control" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="depar_emp_sol">* DEPARTAMENTO</label>
                        <input type='text' name='depar_emp_sol' id="depar_emp_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="tel_emp_sol">* TELEFONO EMPRESA</label>
                        <input type='number' name='tel_emp_sol' id="tel_emp_sol" class='form-control' required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="fecha_ing_emp_sol">* FECHA DE INGRESO</label>
                        <input type="date" name="fecha_ing_emp_sol" class="form-control" id="fecha_ing_emp_sol" required />
                    </div>    
                    <div class="col-12 col-sm-3">
                        <label for="anti_emp_sol">* ANTIGÜEDAD EN AÑOS</label>
                        <input type="number" name="anti_emp_sol" id="anti_emp_sol" class="form-control" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="cargo_actual_emp_sol">* CARGO ACTUAL</label>
                        <input type='text' name='cargo_actual_emp_sol' id="cargo_actual_emp_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="area_trabajo_sol">* ÁREA DE TRABAJO</label>
                        <input type='text' name='area_trabajo_sol' id="area_trabajo_sol" class='form-control' required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <label for="acti_inde_sol">* ACTIVIDAD COMO INDEPENDIENTE</label>
                        <input type="text" name="acti_inde_sol" class="form-control" id="acti_inde_sol" required/>
                    </div>    
                    <div class="col-12 col-sm-6">
                        <label for="num_emple_emp_sol">* NÚMERO DE EMPLEADOS DE SU EMPRESA</label>
                        <input type="number" name="num_emple_emp_sol" id="num_emple_emp_sol" class="form-control" required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <h3>DATOS FINANCIEROS</h3>
                <h5>INGRESOS</h5>
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="salario_sol">* SALARIO</label>
                        <input type="number" name="salario_sol" class="form-control" id="salario_sol" required />
                    </div>    
                    <div class="col-12 col-sm-3">
                        <label for="ing_arri_sol">* INGRESO POR ARRIENDO</label>
                        <input type="number" name="ing_arri_sol" id="ing_arri_sol" class="form-control" required />
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="honorarios_sol">* HONORARIOS</label>
                        <input type='number' name='honorarios_sol' id="honorarios_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="pension_sol">* PENSIÓN</label>
                        <input type='number' name='pension_sol' id="pension_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="otros_ing_sol">* OTROS INGRESOS</label>
                        <input type='number' name='otros_ing_sol' id="otros_ing_sol" class='form-control' required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <h5>EGRESOS</h5>
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="cuota_pres_sol">* CUOTA PRESTAMOS</label>
                        <input type="number" name="cuota_pres_sol" class="form-control" id="cuota_pres_sol" required />
                    </div>    
                    <div class="col-12 col-sm-3">
                        <label for="cuota_tar_cred_sol">* CUOTA TARJETA DE CREDITO</label>
                        <input type="number" name="cuota_tar_cred_sol" id="cuota_tar_cred_sol" class="form-control" required/>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="arrendo_sol">* ARRENDAMIENTO</label>
                        <input type='number' name='arrendo_sol' id="arrendo_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="gastos_fam_sol">* GASTOS FAMILIARES</label>
                        <input type='number' name='gastos_fam_sol' id="gastos_fam_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="otros_gastos_sol">* OTROS GASTOS</label>
                        <input type='number' name='otros_gastos_sol' id="otros_gastos_sol" class='form-control' required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <label for="ahorro_banco_sol">* BANCOS (AHORROS, INVERSIONES, CDTS)</label>
                        <input type="number" name="ahorro_banco_sol" class="form-control" id="ahorro_banco_sol" required />
                    </div>    
                    <div class="col-12 col-sm-4">
                        <label for="vehiculo_sol">* VEHICULOS (VALOR COMERCIAL)</label>
                        <input type="number" name="vehiculo_sol" id="vehiculo_sol" class="form-control" required/>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="bienes_raices_sol">* BIENES RAICES (VALOR COMERCIAL)</label>
                        <input type='number' name='bienes_raices_sol' id="bienes_raices_sol" class='form-control' required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <label for="otros_activos_sol">* OTROS ACTIVOS</label>
                        <input type='number' name='otros_activos_sol' id="otros_activos_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="presta_total_sol">* PRESTAMOS (DEUDA TOTAL)</label>
                        <input type='number' name='presta_total_sol' id="presta_total_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="hipotecas_sol">* HIPOTECAS</label>
                        <input type='number' name='hipotecas_sol' id="hipotecas_sol" class='form-control' required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <label for="tar_cred_total_sol">* TARJETA DE CREDITO (DEUDA TOTAL)</label>
                        <input type='number' name='tar_cred_total_sol' id="tar_cred_total_sol" class='form-control' required/>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="otros_pasivos_sol">* OTROS PASIVOS</label>
                        <input type='number' name='otros_pasivos_sol' id="otros_pasivos_sol" class='form-control' required/>
                    </div>
                </div>
            </div>

            <!--Relacion de inmuebles-->
            <div class="form-group">
                <h3>RELACION INMUEBLES</h3>
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <label for="tipo_inmu_1_sol">TIPO DE INMUEBLE 1</label>
                        <select name="tipo_inmu_1_sol" class="form-control" id="tipo_inmu_1_sol" >
                            <option value="LOTE">LOTE</option>
                            <option value="CASA">CASA</option>
                            <option value="FINCA">FINCA</option>
                            <option value="APARTAMENTO">APARTAMENTO</option>
                            <option value="LOCAL">LOCAL</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="direccion_1_sol">DIRECCION 1</label>
                        <input type='text' name='direccion_1_sol' id="direccion_1_sol" class='form-control'/>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="valor_comer_1_sol">VALOR COMERCIAL 1</label>
                        <input type='number' name='valor_comer_1_sol' id="valor_comer_1_sol" class='form-control'/>
                    </div>           
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <label for="tipo_inmu_2_sol">TIPO DE INMUEBLE 2</label>
                        <select name="tipo_inmu_2_sol" class="form-control" id="tipo_inmu_2_sol" >
                            <option value="LOTE">LOTE</option>
                            <option value="CASA">CASA</option>
                            <option value="FINCA">FINCA</option>
                            <option value="APARTAMENTO">APARTAMENTO</option>
                            <option value="LOCAL">LOCAL</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="direccion_2_sol">DIRECCION 2</label>
                        <input type='text' name='direccion_2_sol' id="direccion_2_sol" class='form-control' />
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="valor_comer_2_sol">VALOR COMERCIAL 2</label>
                        <input type='number' name='valor_comer_2_sol' id="valor_comer_2_sol" class='form-control' />
                    </div>           
                </div>
            </div>

            <div class="form-group">
                <h3>RELACION VEHICULOS</h3>
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="tipo_vehi_1_sol">TIPO DE VEHICULO 1</label>
                        <select name="tipo_vehi_1_sol" class="form-control" id="tipo_vehi_1_sol" >
                            <option value="MOTO">MOTO</option>
                            <option value="CARRO">CARRO</option>
                            <option value="CAMIONETA">CAMIONETA</option>
                            <option value="BUS">BUS</option>
                            <option value="BUSETA">BUSETA</option>
                            <option value="MICROBUS">MICROBUS</option>
                            <option value="TAXI">TAXI</option>
                            <option value="CAMION">CAMION</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="modelo_1_sol">MODELO 1</label>
                        <input type='number' name='modelo_1_sol' id="modelo_1_sol" class='form-control' />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="marca_1_sol">MARCA 1</label>
                        <input type='text' name='marca_1_sol' id="marca_1_sol" class='form-control' />
                    </div>      
                    <div class="col-12 col-sm-2">
                        <label for="placa_1_sol">PLACA 1</label>
                        <input type='text' name='placa_1_sol' id="placa_1_sol" class='form-control'/>
                    </div>   
                    <div class="col-12 col-sm-3">
                        <label for="valor_1_sol">VALOR COMERCIAL 1</label>
                        <input type='number' name='valor_1_sol' id="valor_1_sol" class='form-control' />
                    </div>        
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="tipo_vehi_2_sol">TIPO DE VEHICULO 2</label>
                        <select name="tipo_vehi_2_sol" class="form-control" id="tipo_vehi_2_sol" >
                            <option value="MOTO">MOTO</option>
                            <option value="CARRO">CARRO</option>
                            <option value="CAMIONETA">CAMIONETA</option>
                            <option value="BUS">BUS</option>
                            <option value="BUSETA">BUSETA</option>
                            <option value="MICROBUS">MICROBUS</option>
                            <option value="TAXI">TAXI</option>
                            <option value="CAMION">CAMION</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="modelo_2_sol">MODELO 2</label>
                        <input type='number' name='modelo_2_sol' id="modelo_2_sol" class='form-control' />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="marca_2_sol">MARCA 2</label>
                        <input type='text' name='marca_2_sol' id="marca_2_sol" class='form-control' />
                    </div>      
                    <div class="col-12 col-sm-2">
                        <label for="placa_2_sol">PLACA 2</label>
                        <input type='text' name='placa_2_sol' id="placa_2_sol" class='form-control'/>
                    </div>   
                    <div class="col-12 col-sm-3">
                        <label for="valor_2_sol">VALOR COMERCIAL 2</label>
                        <input type='number' name='valor_2_sol' id="valor_2_sol" class='form-control' />
                    </div>        
                </div>
            </div>

            <div class="form-group">
                <h3>OTROS ACTIVOS</h3>
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <label for="ahorros_sol">* (CDT, CARTERA, INVERSIONES, CUENTAS, APORTES, OTROS)</label>
                        <select name="ahorros_sol" class="form-control" id="ahorros_sol" required>
                            <option value="AHORROS">AHORROS</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="valor_ahor_sol">* VALOR GENERAL</label>
                        <input type='number' name='valor_ahor_sol' id="valor_ahor_sol" class='form-control' required/>
                    </div> 
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <label for="enseres_sol">* (MUEBLES, ENSERES, EQUIPOS)</label>
                        <select name="enseres_sol" class="form-control" id="enseres_sol" required>
                            <option value="MUEBLES Y OTROS">MUEBLES Y OTROS</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="valor_enser_sol">* VALOR GENERAL</label>
                        <input type='number' name='valor_enser_sol' id="valor_enser_sol" class='form-control' required/>
                    </div> 
                </div>
            </div>

            <!-- Datos del conyugue -->
            <div class="form-group">
                <h3>DATOS DEL CONYUGUE</h3>
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="conyu_nombre_sol">NOMBRE COMPLETO</label>
                        <input type="text" name="conyu_nombre_sol" class="form-control" id="conyu_nombre_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_cedula_sol">CEDULA No.</label>
                        <input type="text" name="conyu_cedula_sol" class="form-control" id="conyu_cedula_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_naci_sol">FECHA NACIMIENTO</label>
                        <input type="date" name="conyu_naci_sol" class="form-control" id="conyu_naci_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_exp_sol">CIUDAD DE EXPEDICION</label>
                        <input type="text" name="conyu_exp_sol" class="form-control" id="conyu_exp_sol" />
                    </div>
                </div>
            </div> 

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="conyu_ciudadn_sol">CIUDAD NACIMIENTO</label>
                        <input type="text" name="conyu_ciudadn_sol" class="form-control" id="conyu_ciudadn_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_dpton_sol">DEPARTAMENTO NACIMIENTO</label>
                        <input type="text" name="conyu_dpton_sol" class="form-control" id="conyu_dpton_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_paism_sol">PAIS NACIMIENTO</label>
                        <input type="text" name="conyu_paism_sol" class="form-control" id="conyu_paism_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_correo_sol">EMAIL</label>
                        <input type="email" name="conyu_correo_sol" class="form-control" id="conyu_correo_sol" />
                    </div>
                </div>
            </div> 

            <div class="form-group">
                <h3>DATOS LABORALES DEL CONYUGUE</h3>
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="conyu_ocupacion_sol">OCUPACION</label>
                        <select name="conyu_ocupacion_sol" class="form-control" id="conyu_ocupacion_sol" >
                            <option value="EMPLEADO">EMPLEADO (A)</option>
                            <option value="INDEPENDIENTE">INDEPENDIENTE</option>
                            <option value="COMERCIANTE">COMERCIANTE</option>
                            <option value="PENSIONADO">PENSIONADO (A)</option>
                            <option value="RENTISTA CAPITAL">RENTISTA CAPITAL</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_func_sol">FUNCIONARIO DEL ESTADO</label>
                        <select name="conyu_func_sol" class="form-control" id="conyu_func_sol" >
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_emp_lab_sol">NOMBRE EMPRESA</label>
                        <input type="text" name="conyu_emp_lab_sol" class="form-control" id="conyu_emp_lab_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_cargo_sol">CARGO</label>
                        <input type="text" name="conyu_cargo_sol" class="form-control" id="conyu_cargo_sol" />
                    </div>
                </div>
            </div> 

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="conyu_salario_sol">SALARIO</label>
                        <input type="number" name="conyu_salario_sol" class="form-control" id="conyu_salario_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_dir_lab_sol">DIRECCION</label>
                        <input type="text" name="conyu_dir_lab_sol" class="form-control" id="conyu_dir_lab_sol" />
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="conyu_tel_lab_sol">TELEFONO</label>
                        <input type="number" name="conyu_tel_lab_sol" class="form-control" id="conyu_tel_lab_sol" />
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="conyu_ciudad_lab_sol">CIUDAD</label>
                        <input type="text" name="conyu_ciudad_lab_sol" class="form-control" id="conyu_ciudad_lab_sol" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="conyu_dpto_lab_sol">DEPARTAMENTO</label>
                        <input type="text" name="conyu_dpto_lab_sol" class="form-control" id="conyu_dpto_lab_sol" />
                    </div>
                </div>
            </div> 

            <div class="form-group">
                <h3>REFERENCIAS FAMILIARES</h3>
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="fami_nombre_1_sol">* NOMBRE COMPLETO 1</label>
                        <input type="text" name="fami_nombre_1_sol" class="form-control" id="fami_nombre_1_sol" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="fami_cel_1_sol">* CELULAR 1</label>
                        <input type="number" name="fami_cel_1_sol" class="form-control" id="fami_cel_1_sol" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="fami_tel_1_sol">* TELEFONO FIJO 1</label>
                        <input type="number" name="fami_tel_1_sol" class="form-control" id="fami_tel_1_sol" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="fami_parent_1_sol">* PARENTESCO 1</label>
                        <select name="fami_parent_1_sol" class="form-control" id="fami_parent_1_sol" required>
                            <option value="MADRE">MADRE</option>
                            <option value="PADRE">PADRE</option>
                            <option value="HERMANO">HERMANO (A)</option>
                            <option value="HIJO">HIJO (A)</option>
                            <option value="ESPOSO">ESPOSO (A)</option>
                            <option value="ABUELO">ABUELO (A)</option>
                            <option value="TIO">TIO (A)</option>
                            <option value="SOBRINO">SOBRINO (A)</option>
                            <option value="PRIMO">PRIMO (A)</option>
                            <option value="SUEGRO">SUEGRO (A)</option>
                            <option value="CUÑADO">CUÑADO (A)</option>
                            <option value="YERNO">YERNO</option>
                            <option value="NUERA">NUERA</option>
                            <option value="NIETO">NIETO (A)</option>
                        </select>
                    </div>
                </div>
            </div> 

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="fami_nombre_2_sol">* NOMBRE COMPLETO 2</label>
                        <input type="text" name="fami_nombre_2_sol" class="form-control" id="fami_nombre_2_sol" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="fami_cel_2_sol">* CELULAR 2</label>
                        <input type="number" name="fami_cel_2_sol" class="form-control" id="fami_cel_2_sol" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="fami_tel_2_sol">* TELEFONO FIJO 2</label>
                        <input type="number" name="fami_tel_2_sol" class="form-control" id="fami_tel_2_sol" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="fami_parent_2_sol">* PARENTESCO 2</label>
                        <select name="fami_parent_2_sol" class="form-control" id="fami_parent_2_sol" required>
                            <option value="MADRE">MADRE</option>
                            <option value="PADRE">PADRE</option>
                            <option value="HERMANO">HERMANO (A)</option>
                            <option value="HIJO">HIJO (A)</option>
                            <option value="ESPOSO">ESPOSO (A)</option>
                            <option value="ABUELO">ABUELO (A)</option>
                            <option value="TIO">TIO (A)</option>
                            <option value="SOBRINO">SOBRINO (A)</option>
                            <option value="PRIMO">PRIMO (A)</option>
                            <option value="SUEGRO">SUEGRO (A)</option>
                            <option value="CUÑADO">CUÑADO (A)</option>
                            <option value="YERNO">YERNO</option>
                            <option value="NUERA">NUERA</option>
                            <option value="NIETO">NIETO (A)</option>
                        </select>
                    </div>
                </div>
            </div> 

            <div class="form-group">
                <h3>REFERENCIAS PERSONALES</h3>
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <label for="refer_nombre_1_sol">* NOMBRE COMPLETO 1</label>
                        <input type="text" name="refer_nombre_1_sol" class="form-control" id="refer_nombre_1_sol" required/>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="refer_cel_1_sol">* CELULAR 1</label>
                        <input type="number" name="refer_cel_1_sol" class="form-control" id="refer_cel_1_sol" required/>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="refer_tel_1_sol">* TELEFONO FIJO 1</label>
                        <input type="number" name="refer_tel_1_sol" class="form-control" id="refer_tel_1_sol" required/>
                    </div>
                </div>
            </div> 

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <label for="refer_nombre_2_sol">* NOMBRE COMPLETO 2</label>
                        <input type="text" name="refer_nombre_2_sol" class="form-control" id="refer_nombre_2_sol" required/>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="refer_cel_2_sol">* CELULAR 2</label>
                        <input type="number" name="refer_cel_2_sol" class="form-control" id="refer_cel_2_sol" required/>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="refer_tel_2_sol">* TELEFONO FIJO 2</label>
                        <input type="number" name="refer_tel_2_sol" class="form-control" id="refer_tel_2_sol" required/>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
        </form>
    </div>
</body>
</html>
