<?php
    
    session_start();
    
    if(!isset($_SESSION['id'])){
        header("Location: ../../index.php");
    }
    
    header("Content-Type: text/html;charset=utf-8");
    $nombre         = $_SESSION['nombre'];
    $tipo_usuario   = $_SESSION['tipo_usuario'];
    $cod_dane_ie    = $_SESSION['cod_dane_ie'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FICHA</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../fontawesome/css/all.css" rel="stylesheet">
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
    <script>
        function ordenarSelect(id_componente)
          {
            var selectToSort = jQuery('#' + id_componente);
            var optionActual = selectToSort.val();
            selectToSort.html(selectToSort.children('option').sort(function (a, b) {
              return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
            })).val(optionActual);
          }
          $(document).ready(function () {
            ordenarSelect('selectDocument');
          });
    </script>
    <script>
        function ordenarSelect(id_componente)
          {
            var selectToSort = jQuery('#' + id_componente);
            var optionActual = selectToSort.val();
            selectToSort.html(selectToSort.children('option').sort(function (a, b) {
              return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
            })).val(optionActual);
          }
          $(document).ready(function () {
            ordenarSelect('selectMunicipio');
          });
    </script>
    <script>
        function ordenarSelect(id_componente)
          {
            var selectToSort = jQuery('#' + id_componente);
            var optionActual = selectToSort.val();
            selectToSort.html(selectToSort.children('option').sort(function (a, b) {
              return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
            })).val(optionActual);
          }
          $(document).ready(function () {
            ordenarSelect('selectDiscap');
          });
    </script>
    <script>
        function ordenarSelect(id_componente)
          {
            var selectToSort = jQuery('#' + id_componente);
            var optionActual = selectToSort.val();
            selectToSort.html(selectToSort.children('option').sort(function (a, b) {
              return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
            })).val(optionActual);
          }
          $(document).ready(function () {
            ordenarSelect('selectCapac');
          });
    </script>
    <script>
        function ordenarSelect(id_componente)
          {
            var selectToSort = jQuery('#' + id_componente);
            var optionActual = selectToSort.val();
            selectToSort.html(selectToSort.children('option').sort(function (a, b) {
              return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
            })).val(optionActual);
          }
          $(document).ready(function () {
            ordenarSelect('selectEtnia');
          });
    </script>
    <script>
        function ordenarSelect(id_componente)
          {
            var selectToSort = jQuery('#' + id_componente);
            var optionActual = selectToSort.val();
            selectToSort.html(selectToSort.children('option').sort(function (a, b) {
              return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
            })).val(optionActual);
          }
          $(document).ready(function () {
            ordenarSelect('selectIE');
          });
    </script>
    <script>
        function ordenarSelect(id_componente)
          {
            var selectToSort = jQuery('#' + id_componente);
            var optionActual = selectToSort.val();
            selectToSort.html(selectToSort.children('option').sort(function (a, b) {
              return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
            })).val(optionActual);
          }
          $(document).ready(function () {
            ordenarSelect('selectTransp');
          });
    </script>
    <script>
        function ordenarSelect(id_componente)
          {
            var selectToSort = jQuery('#' + id_componente);
            var optionActual = selectToSort.val();
            selectToSort.html(selectToSort.children('option').sort(function (a, b) {
              return a.text === b.text ? 0 : a.text < b.text ? -1 : 1;
            })).val(optionActual);
          }
          $(document).ready(function () {
            ordenarSelect('selectPoblacion');
          });
    </script>
</head>
<body >
   	<?php
        include("../../conexion.php");
        date_default_timezone_set("America/Bogota");
        $time = time();
	    $num_doc_est  = $_GET['num_doc_est'];
	    if(isset($_GET['num_doc_est']))
	    { 
            $sql = mysqli_query($mysqli, "SELECT * FROM estudiantes WHERE num_doc_est = '$num_doc_est'");
	        $row = mysqli_fetch_array($sql);

            //$row = $result->fetch_assoc();
            $fec_nac_est = $row['fec_nac_est'];

            // Calcula la edad
            $fecha_actual = new DateTime();
            $fec_nac_est = new DateTime($fec_nac_est);
            $edad = $fecha_actual->diff($fec_nac_est)->y;
        }

    ?>

   	<div class="container">
        <center>
            <img src='../../img/logo_educacion.png' width=600 height=121 class='responsive'>
        </center>

        <h1><b><i class="fas fa-users"></i> ACTUALIZAR INFORMACIÓN DEL ESTUDIANTE</b></h1>
        <p><i><b><font size=3 color=#c68615>* Datos obligatorios</i></b></font></p>
        
        <form action='addstudentedit1.php' enctype="multipart/form-data" method="POST">
            
            <hr style="border: 2px solid #16087B; border-radius: 2px;">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="fecha_dig_est">FECHA DILIGENC.</label>
                        <input type='text' name='fecha_dig_est' id="fecha_dig_est" class='form-control' value='<?php echo date("d-m-Y h:i", $time); ?>' readonly />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="mun_dig_est">* MUNICIPIO DILIGENCIAMIENTO:</label>
                        <select name='mun_dig_est' class='form-control' id='selectMunicipio' required />
                            <option value=''></option>
                            <?php
                                header('Content-Type: text/html;charset=utf-8');
                                $consulta='SELECT * FROM municipios';
                                $res = mysqli_query($mysqli,$consulta);
                                $num_reg = mysqli_num_rows($res);
                                while($row1 = $res->fetch_array())
                                {
                                ?>
                                    <option value='<?php echo $row1['nombre_mun']; ?>'<?php if($row['mun_dig_est']==$row1['nombre_mun']){echo 'selected';} ?>>
                                        <?php echo $row1['nombre_mun']; ?>
                                    </option>
                                <?php
                                }
                            ?>    
                        </select>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="nombre_encuestador_est">* NOMBRE DEL ENCUESTADOR:</label>
                        <input type='text' name='nombre_encuestador_est' class='form-control'  value='<?php echo $row['nombre_encuestador_est']; ?>' style="text-transform:uppercase;" required/>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="rol_encuestador_est">* ROL DEL ENCUESTADOR:</label>
                        <select class="form-control" name="rol_encuestador_est" required >
                            <option value="">SELECCIONE:</option>   
                            <option value="DOCENTE" <?php if($row['rol_encuestador_est']=='DOCENTE'){echo 'selected';} ?>>DOCENTE</option>
                            <option value="DOCENTE DIRECTIVO" <?php if($row['rol_encuestador_est']=='DOCENTE DIRECTIVO'){echo 'selected';} ?>>DOCENTE DIRECTIVO</option>
                            <option value="DOCENTE ORIENTADOR" <?php if($row['rol_encuestador_est']=='DOCENTE ORIENTADOR'){echo 'selected';} ?>>DOCENTE ORIENTADOR</option>
                            <option value="ADMINISTRATIVO" <?php if($row['rol_encuestador_est']=='ADMINISTRATIVO'){echo 'selected';} ?>>ADMINISTRATIVO</option>
                        </select>
                    </div>
                </div>
            </div>
            <hr style="border: 2px solid #16087B; border-radius: 2px;">

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <label for="num_doc_est">* TIPO DE DOCUMENTO:</label>
                        <select class="form-control" name="tip_doc_est" required autofocus id="selectDocument">
                            <option value=""></option>   
                            <option value="TI" <?php if($row['tip_doc_est']=='TI'){echo 'selected';} ?>>TARJETA DE IDENTIDAD</option>
                            <option value="CC" <?php if($row['tip_doc_est']=='CC'){echo 'selected';} ?>>CEDULA DE CIUDADANIA</option>
                            <option value="CE" <?php if($row['tip_doc_est']=='CE'){echo 'selected';} ?>>CEDULA DE EXTRANJERIA O IDENTIFICACION DE EXTRANJERIA</option>
                            <option value="NIESE" <?php if($row['tip_doc_est']=='NIESE'){echo 'selected';} ?>>NUMERO DE IDENTIFICACION ESTABLECIDO POR LA SECRETARIA DE EDUCACION</option>
                            <option value="PEP" <?php if($row['tip_doc_est']=='PEP'){echo 'selected';} ?>>PEP: PERMISO ESPECIAL DE PERMANENCIA</option>
                            <option value="PPT" <?php if($row['tip_doc_est']=='PPT'){echo 'selected';} ?>>PPT: PERMISO DE PROTECCION TEMPORAL</option>
                            <option value="RC" <?php if($row['tip_doc_est']=='RC'){echo 'selected';} ?>>REGISTRO CIVIL DE NACIMIENTO</option>
                            <option value="VISA" <?php if($row['tip_doc_est']=='VISA'){echo 'selected';} ?>>VISA</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="num_doc_est">No. DOCUMENTO:</label>
                        <input type='number' name='num_doc_est' class='form-control' id="num_doc_est" value='<?php echo $row['num_doc_est']; ?>' readonly />
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="nom_ape_est">* NOMBRES Y APELLIDOS COMPLETOS:</label>
                        <input type='text' name='nom_ape_est' id="nom_ape_est" class='form-control' value='<?php echo utf8_encode($row['nom_ape_est']); ?>' required style="text-transform:uppercase;" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-2">
                        <label for="fec_nac_est">* F. NAC. (dd-mm-aa)</label>
                        <input type='date' name='fec_nac_est' class='form-control' value='<?php echo $row['fec_nac_est']; ?>' required />
                    </div>
                    <div class="col-12 col-sm-1">
                        <label for="edad_est">EDAD:</label>
                        <input type='number' name='edad_est' class='form-control' id="edad_est" value='<?php echo $edad; ?>' readonly />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="ciu_nac_est">* CIUDAD DE NACIMIENTO:</label>
                        <input type='text' name='ciu_nac_est' class='form-control' value='<?php echo $row['ciu_nac_est']; ?>' required style="text-transform:uppercase;" />
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="dir_est">* DIRECCIÓN RESIDENCIA:</label>
                        <input type='text' name='dir_est' class='form-control'  value='<?php echo $row['dir_est']; ?>' style="text-transform:uppercase;" required/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    
                    <div class="col-12 col-sm-4">
                        <label for="mun_res_est">* CIUDAD DE RESIDENCIA:</label>
                        <select class="form-control" name="mun_res_est" required id='selectMunicipio'/>
                            <option value="">SELECCIONE:</option>   
                            <option value="APÍA" <?php if($row['mun_res_est']=='APÍA'){echo 'selected';} ?>>APÍA</option>
                            <option value="BALBOA" <?php if($row['mun_res_est']=='BALBOA'){echo 'selected';} ?>>BALBOA</option>
                            <option value="BELÉN DE UMBRÍA" <?php if($row['mun_res_est']=='BELÉN DE UMBRÍA'){echo 'selected';} ?>>BELÉN DE UMBRÍA</option>
                            <option value="GUÁTICA" <?php if($row['mun_res_est']=='GUÁTICA'){echo 'selected';} ?>>GUÁTICA</option>
                            <option value="LA CELIA" <?php if($row['mun_res_est']=='LA CELIA'){echo 'selected';} ?>>LA CELIA</option>
                            <option value="LA VIRGINIA" <?php if($row['mun_res_est']=='LA VIRGINIA'){echo 'selected';} ?>>LA VIRGINIA</option>
                            <option value="MARSELLA" <?php if($row['mun_res_est']=='MARSELLA'){echo 'selected';} ?>>MARSELLA</option>
                            <option value="MISTRATÓ" <?php if($row['mun_res_est']=='MISTRATÓ'){echo 'selected';} ?>>MISTRATÓ</option>
                            <option value="PUEBLO RICO" <?php if($row['mun_res_est']=='PUEBLO RICO'){echo 'selected';} ?>>PUEBLO RICO</option>
                            <option value="QUINCHÍA" <?php if($row['mun_res_est']=='QUINCHÍA'){echo 'selected';} ?>>QUINCHÍA</option>
                            <option value="SANTA ROSA DE CABAL" <?php if($row['mun_res_est']=='SANTA ROSA DE CABAL'){echo 'selected';} ?>>SANTA ROSA DE CABAL</option>
                            <option value="SANTUARIO" <?php if($row['mun_res_est']=='SANTUARIO'){echo 'selected';} ?>>SANTUARIO</option>
                            <option value="DOSQUEBRADAS" <?php if($row['mun_res_est']=='DOSQUEBRADAS'){echo 'selected';} ?>>DOSQUEBRADAS</option>
                            <option value="PEREIRA" <?php if($row['mun_res_est']=='PEREIRA'){echo 'selected';} ?>>PEREIRA</option>
                        </select> 
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="estrato_est">* ESTRATO:</label>
                        <select class="form-control" name="estrato_est" required/>
                            <option value="">SELECCIONE:</option>
                            <option value=0 <?php if($row['estrato_est']==0){echo 'selected';} ?>>0</option>   
                            <option value=1 <?php if($row['estrato_est']==1){echo 'selected';} ?>>1</option>
                            <option value=2 <?php if($row['estrato_est']==2){echo 'selected';} ?>>2</option>
                            <option value=3 <?php if($row['estrato_est']==3){echo 'selected';} ?>>3</option>
                            <option value=4 <?php if($row['estrato_est']==4){echo 'selected';} ?>>4</option>
                            <option value=5 <?php if($row['estrato_est']==5){echo 'selected';} ?>>5</option>
                            <option value=6 <?php if($row['estrato_est']==6){echo 'selected';} ?>>6</option>
                            <option value=7 <?php if($row['estrato_est']==7){echo 'selected';} ?>>7</option>
                            <option value=8 <?php if($row['estrato_est']==8){echo 'selected';} ?>>8</option>
                            <option value=9 <?php if($row['estrato_est']==9){echo 'selected';} ?>>9</option>
                            <option value=10 <?php if($row['estrato_est']==10){echo 'selected';} ?>>10</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="zona_est">* ZONA:</label>
                        <select class="form-control" name="zona_est" required/>
                            <option value=""></option>   
                            <option value="URBANA" <?php if($row['zona_est']=='URBANA'){echo 'selected';} ?>>URBANA</option>
                            <option value="RURAL" <?php if($row['zona_est']=='RURAL'){echo 'selected';} ?>>RURAL</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="tel1_est">TELÉFONO:</label>
                        <input type='text' name='tel1_est' class='form-control' value='<?php echo $row['tel1_est']; ?>' />
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="tel2_est">TELÉFONO:</label>
                        <input type='text' name='tel2_est' class='form-control' value='<?php echo $row['tel2_est']; ?>' />
                    </div>                   
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <label for="email_est">EMAIL (CORREO ELECTRÓNICO):</label>
                        <input type='email' name='email_est' class='form-control' value='<?php echo $row['email_est']; ?>' style="text-transform:lowercase;" />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="est_civ_est">ESTADO CIVIL:</label>
                        <select class="form-control" name="est_civ_est" />
                            <option value="">SELECCIONE:</option>   
                            <option value="SOLTERO(A)" <?php if($row['est_civ_est']=='SOLTERO(A)'){echo 'selected';} ?>>SOLTERO(A)</option>
                            <option value="CASADO(A)" <?php if($row['est_civ_est']=='CASADO(A)'){echo 'selected';} ?>>CASADO(A)</option>
                            <option value="VIUDO(A)" <?php if($row['est_civ_est']=='VIUDO(A)'){echo 'selected';} ?>>VIUDO(A)</option>
                            <option value="UNION LIBRE" <?php if($row['est_civ_est']=='UNION LIBRE'){echo 'selected';} ?>>UNION LIBRE</option>
                            <option value="SEPARADO(A)" <?php if($row['est_civ_est']=='SEPARADO(A)'){echo 'selected';} ?>>SEPARADO(A)</option>
                            <option value="DIVORCIADO(A)" <?php if($row['est_civ_est']=='DIVORCIADO(A)'){echo 'selected';} ?>>DIVORCIADO(A)</option>
                          <option value="COMPROMETIDO(A)" <?php if($row['est_civ_est']=='COMPROMETIDO(A)'){echo 'selected';} ?>>COMPROMETIDO(A)</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="gen_est">* GÉNERO:</label>
                        <select class="form-control" name="gen_est" required >
                            <option value="">SELECCIONE:</option>   
                            <option value="F" <?php if($row['gen_est']=='F'){echo 'selected';} ?>>FEMENINO</option>
                            <option value="M" <?php if($row['gen_est']=='M'){echo 'selected';} ?>>MASCULINO</option>
                            <option value="O" <?php if($row['gen_est']=='O'){echo 'selected';} ?>>OTRO</option>
                            <option value="Ns/Nr" <?php if($row['gen_est']=='Ns/Nr'){echo 'selected';} ?>>Ns/Nr</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="eps_est">EPS:</label>
                        <input type='text' name='eps_est' class='form-control' value='<?php echo $row['eps_est']; ?>' />
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="med_trans_est">* TRANSPORTE:</label>
                        <select class="form-control" name="med_trans_est" required id="selectTransp" onchange="agregarTransporte(this)">
                            <option value="">SELECCIONE:</option>
                            <option value=1>AGREGAR NUEVA:</option>   
                            <option value="A PIE" <?php if($row['med_trans_est']=='A PIE'){echo 'selected';} ?>>A PIE</option>
                            <option value="BUS" <?php if($row['med_trans_est']=='BUS'){echo 'selected';} ?>>BUS</option>
                            <option value="MOTO" <?php if($row['med_trans_est']=='MOTO'){echo 'selected';} ?>>MOTO</option>
                            <option value="CARRO PARTICULAR" <?php if($row['med_trans_est']=='CARRO PARTICULAR'){echo 'selected';} ?>>CARRO PARTICULAR</option>
                            <option value="OTRO" <?php if($row['med_trans_est']=='OTRO'){echo 'selected';} ?>>OTRO</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="sisben_est">* SISBEN:</label>
                        <select class="form-control" name="sisben_est" required/>
                            <option value=""></option>
                            <option value=0 <?php if($row['sisben_est']==0){echo 'selected';} ?>>0</option>
                            <option value="N/A" <?php if($row['sisben_est']=='N/A'){echo 'selected';} ?>>N/A</option>
                            <option value="A1" <?php if($row['sisben_est']=='A1'){echo 'selected';} ?>>A1</option>
                            <option value="A2" <?php if($row['sisben_est']=='A2'){echo 'selected';} ?>>A2</option> 
                            <option value="A3" <?php if($row['sisben_est']=='A3'){echo 'selected';} ?>>A3</option>
                            <option value="A4" <?php if($row['sisben_est']=='A4'){echo 'selected';} ?>>A4</option> 
                            <option value="A5" <?php if($row['sisben_est']=='A5'){echo 'selected';} ?>>A5</option>
                            <option value="B1" <?php if($row['sisben_est']=='B1'){echo 'selected';} ?>>B1</option> 
                            <option value="B2" <?php if($row['sisben_est']=='B2'){echo 'selected';} ?>>B2</option>
                            <option value="B3" <?php if($row['sisben_est']=='B3'){echo 'selected';} ?>>B3</option> 
                            <option value="B4" <?php if($row['sisben_est']=='B4'){echo 'selected';} ?>>B4</option>
                            <option value="B5" <?php if($row['sisben_est']=='B5'){echo 'selected';} ?>>B5</option> 
                            <option value="B6" <?php if($row['sisben_est']=='B6'){echo 'selected';} ?>>B6</option>
                            <option value="B7" <?php if($row['sisben_est']=='B7'){echo 'selected';} ?>>B7</option> 
                            <option value="C1" <?php if($row['sisben_est']=='C1'){echo 'selected';} ?>>C1</option>
                            <option value="C2" <?php if($row['sisben_est']=='C2'){echo 'selected';} ?>>C2</option>
                            <option value="C3" <?php if($row['sisben_est']=='C3'){echo 'selected';} ?>>C3</option>
                            <option value="C4" <?php if($row['sisben_est']=='C4'){echo 'selected';} ?>>C4</option>
                            <option value="C5" <?php if($row['sisben_est']=='C5'){echo 'selected';} ?>>C5</option>
                            <option value="C6" <?php if($row['sisben_est']=='C6'){echo 'selected';} ?>>C6</option>
                            <option value="C7" <?php if($row['sisben_est']=='C7'){echo 'selected';} ?>>C7</option>
                            <option value="C8" <?php if($row['sisben_est']=='C8'){echo 'selected';} ?>>C8</option>
                            <option value="C9" <?php if($row['sisben_est']=='C9'){echo 'selected';} ?>>C9</option>
                            <option value="C10" <?php if($row['sisben_est']=='C10'){echo 'selected';} ?>>C10</option>
                            <option value="C11" <?php if($row['sisben_est']=='C11'){echo 'selected';} ?>>C11</option>
                            <option value="C12" <?php if($row['sisben_est']=='C12'){echo 'selected';} ?>>C12</option>
                            <option value="C13" <?php if($row['sisben_est']=='C13'){echo 'selected';} ?>>C13</option>
                            <option value="C14" <?php if($row['sisben_est']=='C14'){echo 'selected';} ?>>C14</option>
                            <option value="C15" <?php if($row['sisben_est']=='C15'){echo 'selected';} ?>>C15</option>
                            <option value="C16" <?php if($row['sisben_est']=='C16'){echo 'selected';} ?>>C16</option>
                            <option value="C17" <?php if($row['sisben_est']=='C17'){echo 'selected';} ?>>C17</option>
                            <option value="C18" <?php if($row['sisben_est']=='C18'){echo 'selected';} ?>>C18</option>
                            <option value="D1" <?php if($row['sisben_est']=='D1'){echo 'selected';} ?>>D1</option>
                            <option value="D2" <?php if($row['sisben_est']=='D2'){echo 'selected';} ?>>D2</option>
                            <option value="D3" <?php if($row['sisben_est']=='D3'){echo 'selected';} ?>>D3</option>
                            <option value="D4" <?php if($row['sisben_est']=='D4'){echo 'selected';} ?>>D4</option>
                            <option value="D5" <?php if($row['sisben_est']=='D5'){echo 'selected';} ?>>D5</option>
                            <option value="D6" <?php if($row['sisben_est']=='D6'){echo 'selected';} ?>>D6</option>
                            <option value="D7" <?php if($row['sisben_est']=='D7'){echo 'selected';} ?>>D7</option>
                            <option value="D8" <?php if($row['sisben_est']=='D8'){echo 'selected';} ?>>D8</option>
                            <option value="D9" <?php if($row['sisben_est']=='D9'){echo 'selected';} ?>>D9</option>
                            <option value="D10" <?php if($row['sisben_est']=='D10'){echo 'selected';} ?>>D10</option>
                            <option value="D11" <?php if($row['sisben_est']=='D11'){echo 'selected';} ?>>D11</option>
                            <option value="D12" <?php if($row['sisben_est']=='D12'){echo 'selected';} ?>>D12</option>
                            <option value="D13" <?php if($row['sisben_est']=='D13'){echo 'selected';} ?>>D13</option>
                            <option value="D14" <?php if($row['sisben_est']=='D14'){echo 'selected';} ?>>D14</option>
                            <option value="D15" <?php if($row['sisben_est']=='D15'){echo 'selected';} ?>>D15</option>
                            <option value="D16" <?php if($row['sisben_est']=='D16'){echo 'selected';} ?>>D16</option>
                            <option value="D17" <?php if($row['sisben_est']=='D17'){echo 'selected';} ?>>D17</option>
                            <option value="D18" <?php if($row['sisben_est']=='D18'){echo 'selected';} ?>>D18</option>
                            <option value="D19" <?php if($row['sisben_est']=='D19'){echo 'selected';} ?>>D19</option>
                            <option value="D20" <?php if($row['sisben_est']=='D20'){echo 'selected';} ?>>D20</option>
                            <option value="D21" <?php if($row['sisben_est']=='D21'){echo 'selected';} ?>>D21</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="cod_dane_ieSede">* INSTITUCIÓN EDUCATIVA:</label>
                        <select name='cod_dane_ieSede' class='form-control' id="selectIE" required />
                            <option value=''></option>
                                <?php
                                    header('Content-Type: text/html;charset=utf-8');
                                    $consulta='SELECT * FROM ieSede';
                                    $res = mysqli_query($mysqli,$consulta);
                                    $num_reg = mysqli_num_rows($res);
                                    while($row2 = $res->fetch_array())
                                    {
                                ?>
                            <option value='<?php echo $row2['cod_dane_ieSede']; ?>'<?php if($row['cod_dane_ieSede']==$row2['cod_dane_ieSede']){echo 'selected';} ?>>
                                <?php echo utf8_encode($row2['nombre_ieSede']); ?>
                            </option>
   
                                    <?php
                                    }
                                    ?>    
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    
                    <div class="col-12 col-sm-12">
                        <label for="obs_est">OBSERVACIONES y/o COMENTARIOS ADICIONALES:</label>
                        <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="obs_est" style="text-transform:uppercase;" /><?php echo $row['obs_est']; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    
                </div>
            </div>

            <hr style="border: 4px solid #4EFE2B; border-radius: 5px;">
            <h4><b><CENTER><img src="../../img/baby.png" width=25 height=25>DATOS PRE Y POSTNATALES<img src="../../img/baby.png" width=25 height=25></h4></CENTER></b>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="edad_madre_est">* EDAD DE LA MADRE:</label>
                        <select class="form-control" name="edad_madre_est" required >
                            <option value=""></option>   
                            <option value=10 <?php if($row['edad_madre_est']=='10'){echo 'selected';} ?>>10</option>
                            <option value=11 <?php if($row['edad_madre_est']=='11'){echo 'selected';} ?>>11</option>
                            <option value=12 <?php if($row['edad_madre_est']=='12'){echo 'selected';} ?>>12</option>
                            <option value=13 <?php if($row['edad_madre_est']=='13'){echo 'selected';} ?>>13</option>
                            <option value=14 <?php if($row['edad_madre_est']=='14'){echo 'selected';} ?>>14</option>
                            <option value=15 <?php if($row['edad_madre_est']=='15'){echo 'selected';} ?>>15</option>
                            <option value=16 <?php if($row['edad_madre_est']=='16'){echo 'selected';} ?>>16</option>
                            <option value=17 <?php if($row['edad_madre_est']=='17'){echo 'selected';} ?>>17</option>
                            <option value=18 <?php if($row['edad_madre_est']=='18'){echo 'selected';} ?>>18</option>
                            <option value=19 <?php if($row['edad_madre_est']=='19'){echo 'selected';} ?>>19</option>
                            <option value=20 <?php if($row['edad_madre_est']=='20'){echo 'selected';} ?>>20</option>
                            <option value=21 <?php if($row['edad_madre_est']=='21'){echo 'selected';} ?>>21</option>
                            <option value=22 <?php if($row['edad_madre_est']=='22'){echo 'selected';} ?>>22</option>
                            <option value=23 <?php if($row['edad_madre_est']=='23'){echo 'selected';} ?>>23</option>
                            <option value=24 <?php if($row['edad_madre_est']=='24'){echo 'selected';} ?>>24</option>
                            <option value=25 <?php if($row['edad_madre_est']=='25'){echo 'selected';} ?>>25</option>
                            <option value=26 <?php if($row['edad_madre_est']=='26'){echo 'selected';} ?>>26</option>
                            <option value=27 <?php if($row['edad_madre_est']=='27'){echo 'selected';} ?>>27</option>
                            <option value=28 <?php if($row['edad_madre_est']=='28'){echo 'selected';} ?>>28</option>
                            <option value=29 <?php if($row['edad_madre_est']=='29'){echo 'selected';} ?>>29</option>
                            <option value=30 <?php if($row['edad_madre_est']=='30'){echo 'selected';} ?>>30</option>
                            <option value=31 <?php if($row['edad_madre_est']=='31'){echo 'selected';} ?>>31</option>
                            <option value=32 <?php if($row['edad_madre_est']=='32'){echo 'selected';} ?>>32</option>
                            <option value=33 <?php if($row['edad_madre_est']=='33'){echo 'selected';} ?>>33</option>
                            <option value=34 <?php if($row['edad_madre_est']=='34'){echo 'selected';} ?>>34</option>
                            <option value=35 <?php if($row['edad_madre_est']=='35'){echo 'selected';} ?>>35</option>
                            <option value=36 <?php if($row['edad_madre_est']=='36'){echo 'selected';} ?>>36</option>
                            <option value=37 <?php if($row['edad_madre_est']=='37'){echo 'selected';} ?>>37</option>
                            <option value=38 <?php if($row['edad_madre_est']=='38'){echo 'selected';} ?>>38</option>
                            <option value=39 <?php if($row['edad_madre_est']=='39'){echo 'selected';} ?>>39</option>
                            <option value=40 <?php if($row['edad_madre_est']=='40'){echo 'selected';} ?>>40</option>
                            <option value=41 <?php if($row['edad_madre_est']=='41'){echo 'selected';} ?>>MÁS DE 40</option>
                        </select>
                        <label for="edad_madre_est">(en el momento del embarazo)</label>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="gestacion_semanas_est">* GESTACIÓN EN MESES:</label>
                        <select class="form-control" name="gestacion_semanas_est" required >
                            <option value=""></option>   
                            <option value=5 <?php if($row['gestacion_semanas_est']=='5'){echo 'selected';} ?>>5 MESES</option>
                            <option value=6 <?php if($row['gestacion_semanas_est']=='6'){echo 'selected';} ?>>6 MESES</option>
                            <option value=7 <?php if($row['gestacion_semanas_est']=='7'){echo 'selected';} ?>>7 MESES</option>
                            <option value=8 <?php if($row['gestacion_semanas_est']=='8'){echo 'selected';} ?>>8 MESES</option>
                            <option value=9 <?php if($row['gestacion_semanas_est']=='9'){echo 'selected';} ?>>9 MESES</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="embarazo_mama_est">* ¿EL EMBARAZO PRESENTÓ?</label>
                        <select class="form-control" name="embarazo_mama_est" required >
                            <option value=""></option>   
                            <option value="PARTO A TIEMPO (NATURAL)" <?php if($row['embarazo_mama_est']=='PARTO A TIEMPO (NATURAL)'){echo 'selected';} ?>>PARTO A TIEMPO (NATURAL)</option>
                            <option value="PARTO ASISTIDO (CESÁREA)" <?php if($row['embarazo_mama_est']=='PARTO ASISTIDO (CESÁREA)'){echo 'selected';} ?>>PARTO ASISTIDO (CESÁREA)</option>
                            <option value="SIN ANTECEDENTE" <?php if($row['embarazo_mama_est']=='SIN ANTECEDENTE'){echo 'selected';} ?>>SIN ANTECEDENTE</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="lactancia_mama_est">* LACTANCIA:</label>
                        <select class="form-control" name="lactancia_mama_est" required >
                            <option value=""></option>   
                            <option value=0 <?php if($row['lactancia_mama_est']=='0'){echo 'selected';} ?>>0</option>
                            <option value=1 <?php if($row['lactancia_mama_est']=='1'){echo 'selected';} ?>>1</option>
                            <option value=2 <?php if($row['lactancia_mama_est']=='2'){echo 'selected';} ?>>2</option>
                            <option value=3 <?php if($row['lactancia_mama_est']=='3'){echo 'selected';} ?>>3</option>
                            <option value=4 <?php if($row['lactancia_mama_est']=='4'){echo 'selected';} ?>>4</option>
                            <option value=5 <?php if($row['lactancia_mama_est']=='5'){echo 'selected';} ?>>5</option>
                            <option value=6 <?php if($row['lactancia_mama_est']=='6'){echo 'selected';} ?>>6</option>
                            <option value=7 <?php if($row['lactancia_mama_est']=='7'){echo 'selected';} ?>>7</option>
                            <option value=8 <?php if($row['lactancia_mama_est']=='8'){echo 'selected';} ?>>8</option>
                            <option value=9 <?php if($row['lactancia_mama_est']=='9'){echo 'selected';} ?>>9</option>
                            <option value=10 <?php if($row['lactancia_mama_est']=='10'){echo 'selected';} ?>>10</option>
                            <option value=11 <?php if($row['lactancia_mama_est']=='11'){echo 'selected';} ?>>11</option>
                            <option value=12 <?php if($row['lactancia_mama_est']=='12'){echo 'selected';} ?>>12</option>
                            <option value=13 <?php if($row['lactancia_mama_est']=='13'){echo 'selected';} ?>>13</option>
                            <option value=14 <?php if($row['lactancia_mama_est']=='14'){echo 'selected';} ?>>14</option>
                            <option value=15 <?php if($row['lactancia_mama_est']=='15'){echo 'selected';} ?>>15</option>
                            <option value=16 <?php if($row['lactancia_mama_est']=='16'){echo 'selected';} ?>>16</option>
                            <option value=17 <?php if($row['lactancia_mama_est']=='17'){echo 'selected';} ?>>17</option>
                            <option value=18 <?php if($row['lactancia_mama_est']=='18'){echo 'selected';} ?>>18</option>
                            <option value=19 <?php if($row['lactancia_mama_est']=='19'){echo 'selected';} ?>>19</option>
                            <option value=20 <?php if($row['lactancia_mama_est']=='20'){echo 'selected';} ?>>20</option>
                            <option value=21 <?php if($row['lactancia_mama_est']=='21'){echo 'selected';} ?>>21</option>
                            <option value=22 <?php if($row['lactancia_mama_est']=='22'){echo 'selected';} ?>>22</option>
                            <option value=23 <?php if($row['lactancia_mama_est']=='23'){echo 'selected';} ?>>23</option>
                            <option value=24 <?php if($row['lactancia_mama_est']=='24'){echo 'selected';} ?>>24</option>
                            <option value=25 <?php if($row['lactancia_mama_est']=='25'){echo 'selected';} ?>>MÁS DE 24</option>
                        </select>
                        <label for="lactancia_mama_est">(tiempo en meses)</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="gateo_est">* ¿EL ESTUDIANTE GATEÓ?</label>
                        <select class="form-control" name="gateo_est" required >
                            <option value=""></option>   
                            <option value="SI" <?php if($row['gateo_est']=='SI'){echo 'selected';} ?>>SI</option>
                            <option value="NO" <?php if($row['gateo_est']=='NO'){echo 'selected';} ?>>NO</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="camino_est">* ¿EL ESTUDIANTE CAMINÓ (tiempo meses)?</label>
                        <select class="form-control" name="camino_est" required >
                            <option value=""></option>   
                            <option value=8 <?php if($row['camino_est']=='8'){echo 'selected';} ?>>8</option>
                            <option value=9 <?php if($row['camino_est']=='9'){echo 'selected';} ?>>9</option>
                            <option value=10 <?php if($row['camino_est']=='10'){echo 'selected';} ?>>10</option>
                            <option value=11 <?php if($row['camino_est']=='11'){echo 'selected';} ?>>11</option>
                            <option value=12 <?php if($row['camino_est']=='12'){echo 'selected';} ?>>12</option>
                            <option value=13 <?php if($row['camino_est']=='13'){echo 'selected';} ?>>13</option>
                            <option value=14 <?php if($row['camino_est']=='14'){echo 'selected';} ?>>14</option>
                            <option value=15 <?php if($row['camino_est']=='15'){echo 'selected';} ?>>15</option>
                            <option value=16 <?php if($row['camino_est']=='16'){echo 'selected';} ?>>16</option>
                            <option value=17 <?php if($row['camino_est']=='17'){echo 'selected';} ?>>17</option>
                            <option value=18 <?php if($row['camino_est']=='18'){echo 'selected';} ?>>18</option>
                            <option value=19 <?php if($row['camino_est']=='19'){echo 'selected';} ?>>19</option>
                            <option value=20 <?php if($row['camino_est']=='20'){echo 'selected';} ?>>20</option>
                            <option value=21 <?php if($row['camino_est']=='21'){echo 'selected';} ?>>21</option>
                            <option value=22 <?php if($row['camino_est']=='22'){echo 'selected';} ?>>22</option>
                            <option value=23 <?php if($row['camino_est']=='23'){echo 'selected';} ?>>23</option>
                            <option value=24 <?php if($row['camino_est']=='24'){echo 'selected';} ?>>24</option>
                            <option value=25 <?php if($row['camino_est']=='25'){echo 'selected';} ?>>MÁS DE 24</option>
                            <option value=0 <?php if($row['camino_est']=='0'){echo 'selected';} ?>>NO APLICA</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label for="poblacion_vulnerable_est">* TIPO DE POBLACIÓN:</label>
                        <select class="form-control" name="poblacion_vulnerable_est" required id="selectPoblacion" onchange="agregarPoblacion(this)">
                            <option value="">SELECCIONE:</option>
                            <option value=1>AGREGAR NUEVA:</option>  
                            <option value="DISCAPACITADO" <?php if($row['poblacion_vulnerable_est']=='DISCAPACITADO'){echo 'selected';} ?>>DISCAPACITADO</option>
                            <option value="VICTIMA" <?php if($row['poblacion_vulnerable_est']=='VICTIMA'){echo 'selected';} ?>>VICTIMA</option>
                            <option value="INDIGENA" <?php if($row['poblacion_vulnerable_est']=='INDIGENA'){echo 'selected';} ?>>INDIGENA</option>
                            <option value="AFROCOLOMBIANO" <?php if($row['poblacion_vulnerable_est']=='AFROCOLOMBIANO'){echo 'selected';} ?>>AFROCOLOMBIANO</option>
                            <option value="RAIZALES" <?php if($row['poblacion_vulnerable_est']=='RAIZALES'){echo 'selected';} ?>>RAIZALES</option>
                            <option value="ROM" <?php if($row['poblacion_vulnerable_est']=='ROM'){echo 'selected';} ?>>ROM</option>
                            <option value="MUJER CABEZA DE HOGAR" <?php if($row['poblacion_vulnerable_est']=='MUJER CABEZA DE HOGAR'){echo 'selected';} ?>>MUJER CABEZA DE HOGAR</option>
                            <option value="LGBTQI+" <?php if($row['poblacion_vulnerable_est']=='LGBTQI+'){echo 'selected';} ?>>LGBTQI+</option>
                            <option value="REINSERTADO" <?php if($row['poblacion_vulnerable_est']=='REINSERTADO'){echo 'selected';} ?>>REINSERTADO</option>
                            <option value="MIGRANTES" <?php if($row['poblacion_vulnerable_est']=='MIGRANTES'){echo 'selected';} ?>>MIGRANTES</option>
                            <option value="NINGUNO" <?php if($row['poblacion_vulnerable_est']=='NINGUNO'){echo 'selected';} ?>>NINGUNO</option>
                            <option value="EMBARAZO ADOLESCENTE" <?php if($row['poblacion_vulnerable_est']=='EMBARAZO ADOLESCENTE'){echo 'selected';} ?>>EMBARAZO ADOLESCENTE</option>
                            <option value="PATERNIDAD ADOLESCENTE" <?php if($row['poblacion_vulnerable_est']=='PATERNIDAD ADOLESCENTE'){echo 'selected';} ?>>PATERNIDAD ADOLESCENTE</option>
                            <option value="SRPA" <?php if($row['poblacion_vulnerable_est']=='SRPA'){echo 'selected';} ?>>SRPA (Sistema de Responsabilidad Penal para Adolescentes)</option>
                            <option value="OTROS" <?php if($row['poblacion_vulnerable_est']=='OTROS'){echo 'selected';} ?>>OTROS</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="discapacidad_est">* DISCAPACIDAD:</label>
                        <select class="form-control" name="discapacidad_est" required id='selectDiscap'/>
                            <option value=""></option>   
                            <option value="INTELECTUAL" <?php if($row['discapacidad_est']=='INTELECTUAL'){echo 'selected';} ?>>INTELECTUAL</option>
                            <option value="LIMITACION FISICA" <?php if($row['discapacidad_est']=='LIMITACION FISICA'){echo 'selected';} ?>>LIMITACION FISICA</option>
                            <option value="MULTIPLE" <?php if($row['discapacidad_est']=='MULTIPLE'){echo 'selected';} ?>>MULTIPLE</option>
                            <option value="OTRA DISCAPACIDAD" <?php if($row['discapacidad_est']=='OTRA DISCAPACIDAD'){echo 'selected';} ?>>OTRA DISCAPACIDAD</option>
                            <option value="NO APLICA" <?php if($row['discapacidad_est']=='NO APLICA'){echo 'selected';} ?>>NO APLICA</option>
                            <option value="PSICOSOCIAL" <?php if($row['discapacidad_est']=='PSICOSOCIAL'){echo 'selected';} ?>>PSICOSOCIAL</option>
                            <option value="SA - USUARIO DE CASTELLANO" <?php if($row['discapacidad_est']=='SA - USUARIO DE CASTELLANO'){echo 'selected';} ?>>SA - USUARIO DE CASTELLANO</option>
                            <option value="SA - USUARIO DE LSC" <?php if($row['discapacidad_est']=='SA - USUARIO DE LSC'){echo 'selected';} ?>>SA - USUARIO DE LSC</option>
                            <option value="SISTEMICA" <?php if($row['discapacidad_est']=='SISTEMICA'){echo 'selected';} ?>>SISTEMICA</option>
                            <option value="TRANSTORNO DEL ESPECTRO AUTISTA" <?php if($row['discapacidad_est']=='TRANSTORNO DEL ESPECTRO AUTISTA'){echo 'selected';} ?>>TRANSTORNO DEL ESPECTRO AUTISTA</option>
                            <option value="VISUAL - BAJA VISION IRREVERSIBLE" <?php if($row['discapacidad_est']=='VISUAL - BAJA VISION IRREVERSIBLE'){echo 'selected';} ?>>VISUAL - BAJA VISION IRREVERSIBLE</option>
                            <option value="VISUAL - CEGUERA" <?php if($row['discapacidad_est']=='VISUAL - CEGUERA'){echo 'selected';} ?>>VISUAL - CEGUERA</option>
                            <option value="VOZ Y HABLA" <?php if($row['discapacidad_est']=='VOZ Y HABLA'){echo 'selected';} ?>>VOZ Y HABLA</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-3">
                        <label for="capacidad_est">* CAPACIDAD:</label>
                        <select class="form-control" name="capacidad_est" required id='selectCapac'/>
                            <option value=""></option>   
                            <option value="CAPACIDADES EXCEPCIONALES" <?php if($row['capacidad_est']=='CAPACIDADES EXCEPCIONALES'){echo 'selected';} ?>>CAPACIDADES EXCEPCIONALES</option>
                            <option value="NO APLICA" <?php if($row['capacidad_est']=='NO APLICA'){echo 'selected';} ?>>NO APLICA</option>
                            <option value="TALENTO ATLETICO" <?php if($row['capacidad_est']=='TALENTO ATLETICO'){echo 'selected';} ?>>TALENTO ATLETICO</option>
                            <option value="TALENTO EXCEPCIONAL EN LIDERAZGO SOCIAL Y EMPRENDIMIENTO" <?php if($row['capacidad_est']=='TALENTO EXCEPCIONAL EN LIDERAZGO SOCIAL Y EMPRENDIMIENTO'){echo 'selected';} ?>>TALENTO EXCEPCIONAL EN LIDERAZGO SOCIAL Y EMPRENDIMIENTO</option>
                            <option value="TALENTO EXCEPCIONAL EN CIENCIAS SOCIALES O HUMANAS" <?php if($row['capacidad_est']=='TALENTO EXCEPCIONAL EN CIENCIAS SOCIALES O HUMANAS'){echo 'selected';} ?>>TALENTO EXCEPCIONAL EN CIENCIAS SOCIALES O HUMANAS</option>
                            <option value="TALENTO EXCEPCIONAL EN TECNOLOGÍA" <?php if($row['capacidad_est']=='TALENTO EXCEPCIONAL EN TECNOLOGÍA'){echo 'selected';} ?>>TALENTO EXCEPCIONAL EN TECNOLOGÍA</option>
                            <option value="TALENTO SUBJETIVO" <?php if($row['capacidad_est']=='TALENTO SUBJETIVO'){echo 'selected';} ?>>TALENTO SUBJETIVO</option>
                            <option value="TALENTO TECNOLOGICO" <?php if($row['capacidad_est']=='TALENTO TECNOLOGICO'){echo 'selected';} ?>>TALENTO TECNOLOGICO</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-2">
                        <label for="etnia_est">* ETNIA:</label>
                        <select class="form-control" name="etnia_est" required id='selectEtnia'/>
                            <option value=""></option>   
                            <option value="ACHAGUA" <?php if($row['etnia_est']=='ACHAGUA'){echo 'selected';} ?>>ACHAGUA</option>
                            <option value="AFRODESCENDIENTE" <?php if($row['etnia_est']=='AFRODESCENDIENTE'){echo 'selected';} ?>>AFRODESCENDIENTE</option>
                            <option value="AMBALO" <?php if($row['etnia_est']=='AMBALO'){echo 'selected';} ?>>AMBALO</option>
                            <option value="AWA (CUAIKER)" <?php if($row['etnia_est']=='AWA (CUAIKER)'){echo 'selected';} ?>>AWA (CUAIKER)</option>
                            <option value="BETOYE" <?php if($row['etnia_est']=='BETOYE'){echo 'selected';} ?>>BETOYE</option>
                            <option value="BORA" <?php if($row['etnia_est']=='BORA'){echo 'selected';} ?>>BORA</option>
                            <option value="COCAMA" <?php if($row['etnia_est']=='COCAMA'){echo 'selected';} ?>>COCAMA</option>
                            <option value="EMBERA CATIO (EMBERA KATIO)" <?php if($row['etnia_est']=='EMBERA CATIO (EMBERA KATIO)'){echo 'selected';} ?>>EMBERA CATIO (EMBERA KATIO)</option>
                            <option value="EMBERA" <?php if($row['etnia_est']=='EMBERA'){echo 'selected';} ?>>EMBERA</option>
                            <option value="DESANO" <?php if($row['etnia_est']=='DESANO'){echo 'selected';} ?>>DESANO</option>
                            <option value="EMBERA CHAMI" <?php if($row['etnia_est']=='EMBERA CHAMI'){echo 'selected';} ?>>EMBERA CHAMI</option>
                            <option value="EMBERA SIAPIDARA" <?php if($row['etnia_est']=='EMBERA SIAPIDARA'){echo 'selected';} ?>>EMBERA SIAPIDARA</option>
                            <option value="INGA" <?php if($row['etnia_est']=='INGA'){echo 'selected';} ?>>INGA</option>
                            <option value="KICHWA" <?php if($row['etnia_est']=='KICHWA'){echo 'selected';} ?>>KICHWA</option>
                            <option value="MACAGUAJE (MAKAGUAJE)" <?php if($row['etnia_est']=='MACAGUAJE (MAKAGUAJE)'){echo 'selected';} ?>>MACAGUAJE (MAKAGUAJE)</option>
                            <option value="MOKANA" <?php if($row['etnia_est']=='MOKANA'){echo 'selected';} ?>>MOKANA</option>
                            <option value="MUISCA" <?php if($row['etnia_est']=='MUISCA'){echo 'selected';} ?>>MUISCA</option>
                            <option value="MURUI (MURUI - WITO)" <?php if($row['etnia_est']=='MURUI (MURUI - WITO)'){echo 'selected';} ?>>MURUI (MURUI - WITO)</option>
                            <option value="MACÚ (NUKAK MAKU)" <?php if($row['etnia_est']=='MACÚ (NUKAK MAKU)'){echo 'selected';} ?>>MACÚ (NUKAK MAKU)</option>
                            <option value="NO APLICA" <?php if($row['etnia_est']=='NO APLICA'){echo 'selected';} ?>>NO APLICA</option>
                            <option value="NEGRITUDES" <?php if($row['etnia_est']=='NEGRITUDES'){echo 'selected';} ?>>NEGRITUDES</option>
                            <option value="NONUYA" <?php if($row['etnia_est']=='NONUYA'){echo 'selected';} ?>>NONUYA</option>
                            <option value="PIJAOS" <?php if($row['etnia_est']=='PIJAOS'){echo 'selected';} ?>>PIJAOS</option>
                            <option value="PAÉZ (NASA)" <?php if($row['etnia_est']=='PAÉZ (NASA)'){echo 'selected';} ?>>PAÉZ (NASA)</option>
                            <option value="PASTOS" <?php if($row['etnia_est']=='PASTOS'){echo 'selected';} ?>>PASTOS</option>
                            <option value="ZENÚ" <?php if($row['etnia_est']=='ZENÚ'){echo 'selected';} ?>>ZENÚ</option>
                            <option value="WOUNAAN (WAUNAN [WUANANA])" <?php if($row['etnia_est']=='WOUNAAN (WAUNAN [WUANANA])'){echo 'selected';} ?>>WOUNAAN (WAUNAN [WUANANA])</option>
                            <option value="WAYUU" <?php if($row['etnia_est']=='WAYUU'){echo 'selected';} ?>>WAYUU</option>
                            <option value="SIKUANI" <?php if($row['etnia_est']=='SIKUANI'){echo 'selected';} ?>>SIKUANI</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="victima_est">* ¿ESTUDIANTE VÍCTIMA DEL CONFLICTO?</label>
                        <select class="form-control" name="victima_est" required >
                            <option value=""></option>   
                            <option value="SI" <?php if($row['victima_est']=='SI'){echo 'selected';} ?>>SI</option>
                            <option value="NO" <?php if($row['victima_est']=='NO'){echo 'selected';} ?>>NO</option>
                        </select>
                    </div>
                    
                </div>
            </div>


            <hr style="border: 4px solid #4EFE2B; border-radius: 5px;">

            <button type="submit" class="btn btn-primary" name="btn-update">
                <span class="spinner-border spinner-border-sm"></span>
                ACTUALIZAR INFORMACIÓN 
            </button>
            <button type="reset" class="btn btn-outline-dark" role='link' onclick="history.back();" type='reset'><img src='../../img/atras.png' width=27 height=27> REGRESAR
            </button>
        </form>
    </div>
</body>
    <script>
        function agregarTransporte(selectElement) {
            var selectedOption = selectElement.value;
            if (selectedOption === "1") {
                var nuevaOpcion = prompt("INGRESE EL MEDIO DE TRANSPORTE UTILIZADO:");
                if (nuevaOpcion !== null) {
                    // Convertir la entrada del usuario a mayúsculas
                    nuevaOpcion = nuevaOpcion.toUpperCase();
  
                    // Crear un nuevo elemento de opción
                    var nuevaOpcionElement = document.createElement("option");
                    nuevaOpcionElement.value = nuevaOpcion;
                    nuevaOpcionElement.text = nuevaOpcion;
  
                    // Agregar la nueva opción al select
                    selectElement.appendChild(nuevaOpcionElement);

                    // Seleccionar la nueva opción
                    selectElement.value = nuevaOpcion;
                } else {
                    // Si el usuario cancela el prompt, seleccionar una opción predeterminada
                    selectElement.value = "A PIE"; // Puedes cambiar esto según tus necesidades
                }
            }
        }
    </script>
    <script>
        function agregarPoblacion(selectElement) {
            var selectedOption = selectElement.value;
            if (selectedOption === "1") {
                var nuevaOpcion = prompt("INGRESE EL TIPO DE POBLACIÓN:");
                if (nuevaOpcion !== null) {
                    // Convertir la entrada del usuario a mayúsculas
                    nuevaOpcion = nuevaOpcion.toUpperCase();
  
                    // Crear un nuevo elemento de opción
                    var nuevaOpcionElement = document.createElement("option");
                    nuevaOpcionElement.value = nuevaOpcion;
                    nuevaOpcionElement.text = nuevaOpcion;
  
                    // Agregar la nueva opción al select
                    selectElement.appendChild(nuevaOpcionElement);

                    // Seleccionar la nueva opción
                    selectElement.value = nuevaOpcion;
                } else {
                    // Si el usuario cancela el prompt, seleccionar una opción predeterminada
                    selectElement.value = "NINGUNO"; // Puedes cambiar esto según tus necesidades
                }
            }
        }
    </script>
</html>