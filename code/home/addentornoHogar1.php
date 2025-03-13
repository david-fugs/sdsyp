<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

include("../../conexion.php");
mysqli_set_charset($mysqli, "utf8"); // Asegura la conexión a la base de datos en UTF-8
date_default_timezone_set("America/Bogota");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_dig_hog = mysqli_real_escape_string($mysqli, $_POST['fecha_dig_hog']) ?? "";
    $mun_dig_hog = mysqli_real_escape_string($mysqli, $_POST['mun_dig_hog']) ?? "";
    $nombre_encuestador_hog = mysqli_real_escape_string($mysqli, $_POST['nombre_encuestador_hog']) ?? "";
    $rol_encuestador_hog = mysqli_real_escape_string($mysqli, $_POST['rol_encuestador_hog']) ?? "";
    $num_doc_est = mysqli_real_escape_string($mysqli, $_POST['num_doc_est']) ?? "";
    $nombre_madre_hog = mysqli_real_escape_string($mysqli, $_POST['nombre_madre_hog']) ?? "";
    $vive_madre_hog = ($_POST['vive_madre_hog'] != '') ? $_POST['vive_madre_hog'] : 2;
    $ocupacion_madre_hog = mysqli_real_escape_string($mysqli, $_POST['ocupacion_madre_hog']) ?? "";
    $educacion_madre_hog = mysqli_real_escape_string($mysqli, $_POST['educacion_madre_hog']) ?? "";
    $nombre_padre_hog = mysqli_real_escape_string($mysqli, $_POST['nombre_padre_hog']) ?? "";
    $vive_padre_hog = ($_POST['vive_padre_hog'] != '') ? $_POST['vive_padre_hog'] : 2;
    $ocupacion_padre_hog = mysqli_real_escape_string($mysqli, $_POST['ocupacion_padre_hog']) ?? "";
    $educacion_padre_hog = mysqli_real_escape_string($mysqli, $_POST['educacion_padre_hog']) ?? "";
    $vive_estu_hog = mysqli_real_escape_string($mysqli, $_POST['vive_estu_hog']) ?? "";
    $nom_vive_estu_hog = mysqli_real_escape_string($mysqli, $_POST['nom_vive_estu_hog']) ?? "";
    $cuidador_estu_hog = mysqli_real_escape_string($mysqli, $_POST['cuidador_estu_hog']) ?? "";
    $parentesco_cuid_estu_hog = mysqli_real_escape_string($mysqli, $_POST['parentesco_cuid_estu_hog']) ?? "";
    $nom_parentesco_cuid_estu_hog = mysqli_real_escape_string($mysqli, $_POST['nom_parentesco_cuid_estu_hog']) ?? "";
    $educacion_cuid_estu_hog = mysqli_real_escape_string($mysqli, $_POST['educacion_cuid_estu_hog']) ?? "";
    $ocupacion_cuid_estu_hog = mysqli_real_escape_string($mysqli, $_POST['ocupacion_cuid_estu_hog']) ?? "";
    $nom_ocupacion_cuid_estu_hog = mysqli_real_escape_string($mysqli, $_POST['nom_ocupacion_cuid_estu_hog']) ?? "";
    $tel_cuid_estu_hog = mysqli_real_escape_string($mysqli, $_POST['tel_cuid_estu_hog']) ?? "";
    $email_cuid_estu_hog = mysqli_real_escape_string($mysqli, $_POST['email_cuid_estu_hog']) ?? "";
    $num_herm_estu_hog = mysqli_real_escape_string($mysqli, $_POST['num_herm_estu_hog']) ?? "";
    $lugar_ocupa_estu_hog = mysqli_real_escape_string($mysqli, $_POST['lugar_ocupa_estu_hog']) ?? "";
    $tiene_herm_ie_estu_hog = mysqli_real_escape_string($mysqli, $_POST['tiene_herm_ie_estu_hog']) ?? "";
    $niveles_educativos_herm_ie_estu_hog = isset($_POST['niveles_educativos_herm_ie_estu_hog']) ? mysqli_real_escape_string($mysqli, implode(',', $_POST['niveles_educativos_herm_ie_estu_hog'])) : '';
    $crianza_estu_hog = mysqli_real_escape_string($mysqli, $_POST['crianza_estu_hog']) ?? "";
    $nom_crianza_estu_hog = mysqli_real_escape_string($mysqli, $_POST['nom_crianza_estu_hog']) ?? "";
    $prac_comu_estu_hog = mysqli_real_escape_string($mysqli, $_POST['prac_comu_estu_hog']) ?? "";
    $fam_categ_estu_hog = mysqli_real_escape_string($mysqli, $_POST['fam_categ_estu_hog']) ?? "";
    $fam_subsidio_hog = mysqli_real_escape_string($mysqli, $_POST['fam_subsidio_hog']) ?? "";
    $tipo_subsidio_hog = mysqli_real_escape_string($mysqli, $_POST['tipo_subsidio_hog']) ?? "";
    $mecanismos_conflictos_estu_hog = isset($_POST['mecanismos_conflictos_estu_hog']) ? mysqli_real_escape_string($mysqli, implode(',', $_POST['mecanismos_conflictos_estu_hog'])) : '';
    $nom_mecanismos_conflictos_estu_hog = mysqli_real_escape_string($mysqli, $_POST['nom_mecanismos_conflictos_estu_hog']) ?? "";
    $inconvenientes_quien_hog = isset($_POST['inconvenientes_quien_hog']) ? mysqli_real_escape_string($mysqli, implode(',', $_POST['inconvenientes_quien_hog'])) : '';
    $nom_quien_inconvenientes_hog = mysqli_real_escape_string($mysqli, $_POST['nom_quien_inconvenientes_hog']) ?? "";
    $inconvenientes_como_hog = isset($_POST['inconvenientes_como_hog']) ? mysqli_real_escape_string($mysqli, implode(',', $_POST['inconvenientes_como_hog'])) : '';
    $nom_como_inconvenientes_hog = mysqli_real_escape_string($mysqli, $_POST['nom_como_inconvenientes_hog']);
    $responsabilidades_est_hog = isset($_POST['responsabilidades_est_hog']) ? mysqli_real_escape_string($mysqli, implode(',', $_POST['responsabilidades_est_hog'])) : '';
    $nom_responsabilidades_est_hog = mysqli_real_escape_string($mysqli, $_POST['nom_responsabilidades_est_hog']) ?? "";
    $afecto_est_hog = isset($_POST['afecto_est_hog']) ? mysqli_real_escape_string($mysqli, implode(',', $_POST['afecto_est_hog'])) : '';
    $nom_afecto_est_hog = mysqli_real_escape_string($mysqli, $_POST['nom_afecto_est_hog']) ?? "";
    $tipo_vivienda_hog = mysqli_real_escape_string($mysqli, $_POST['tipo_vivienda_hog']) ?? "";
    $tenencia_vivienda_hog = mysqli_real_escape_string($mysqli, $_POST['tenencia_vivienda_hog']) ?? "";
    $nom_tenencia_vivienda_hog = mysqli_real_escape_string($mysqli, $_POST['nom_tenencia_vivienda_hog']) ?? "";
    $material_vivienda_hog = mysqli_real_escape_string($mysqli, $_POST['material_vivienda_hog']) ?? "";
    $servicios_vivienda_hog = isset($_POST['servicios_vivienda_hog']) ? mysqli_real_escape_string($mysqli, implode(',', $_POST['servicios_vivienda_hog'])) : '';
    $num_personas_vivienda_hog = mysqli_real_escape_string($mysqli, $_POST['num_personas_vivienda_hog']) ?? "";
    $estado_hog = 1;
    $fecha_alta_hog = date('Y-m-d h:i:s');
    $id_usu_alta_hog = $_SESSION['id'];
    $fecha_edit_hog = '0000-00-00 00:00:00';
    $id_usu = $_SESSION['id'];

    $sql_update = "INSERT INTO entornohogar (
                        fecha_dig_hog,
                        mun_dig_hog,
                        nombre_encuestador_hog,
                        rol_encuestador_hog,
                        num_doc_est,
                        nombre_madre_hog,
                        vive_madre_hog,
                        ocupacion_madre_hog,
                        educacion_madre_hog,
                        nombre_padre_hog,
                        vive_padre_hog,
                        ocupacion_padre_hog,
                        educacion_padre_hog,
                        vive_estu_hog,
                        nom_vive_estu_hog,
                        cuidador_estu_hog,
                        parentesco_cuid_estu_hog,
                        nom_parentesco_cuid_estu_hog,
                        educacion_cuid_estu_hog,
                        ocupacion_cuid_estu_hog,
                        nom_ocupacion_cuid_estu_hog,
                        tel_cuid_estu_hog,
                        email_cuid_estu_hog,
                        num_herm_estu_hog,
                        lugar_ocupa_estu_hog,
                        tiene_herm_ie_estu_hog,
                        niveles_educativos_herm_ie_estu_hog,
                        crianza_estu_hog,
                        nom_crianza_estu_hog,
                        prac_comu_estu_hog,
                        fam_categ_estu_hog,
                        fam_subsidio_hog,
                        tipo_subsidio_hog,
                        mecanismos_conflictos_estu_hog,
                        nom_mecanismos_conflictos_estu_hog,
                        inconvenientes_quien_hog,
                        nom_quien_inconvenientes_hog,
                        inconvenientes_como_hog,
                        nom_como_inconvenientes_hog,
                        responsabilidades_est_hog,
                        nom_responsabilidades_est_hog,
                        afecto_est_hog,
                        nom_afecto_est_hog,
                        tipo_vivienda_hog,
                        tenencia_vivienda_hog,
                        nom_tenencia_vivienda_hog,
                        material_vivienda_hog,
                        servicios_vivienda_hog,
                        num_personas_vivienda_hog,
                        estado_hog,
                        fecha_alta_hog,
                        id_usu_alta_hog,
                        fecha_edit_hog,
                        id_usu
                    ) VALUES (
                        '$fecha_dig_hog',
                        '$mun_dig_hog',
                        '$nombre_encuestador_hog',
                        '$rol_encuestador_hog',
                        '$num_doc_est',
                        '$nombre_madre_hog',
                        '$vive_madre_hog',
                        '$ocupacion_madre_hog',
                        '$educacion_madre_hog',
                        '$nombre_padre_hog',
                        '$vive_padre_hog',
                        '$ocupacion_padre_hog',
                        '$educacion_padre_hog',
                        '$vive_estu_hog',
                        '$nom_vive_estu_hog',
                        '$cuidador_estu_hog',
                        '$parentesco_cuid_estu_hog',
                        '$nom_parentesco_cuid_estu_hog',
                        '$educacion_cuid_estu_hog',
                        '$ocupacion_cuid_estu_hog',
                        '$nom_ocupacion_cuid_estu_hog',
                        '$tel_cuid_estu_hog',
                        '$email_cuid_estu_hog',
                        '$num_herm_estu_hog',
                        '$lugar_ocupa_estu_hog',
                        '$tiene_herm_ie_estu_hog',
                        '$niveles_educativos_herm_ie_estu_hog',
                        '$crianza_estu_hog',
                        '$nom_crianza_estu_hog',
                        '$prac_comu_estu_hog',
                        '$fam_categ_estu_hog',
                        '$fam_subsidio_hog',
                        '$tipo_subsidio_hog',
                        '$mecanismos_conflictos_estu_hog',
                        '$nom_mecanismos_conflictos_estu_hog',
                        '$inconvenientes_quien_hog',
                        '$nom_quien_inconvenientes_hog',
                        '$inconvenientes_como_hog',
                        '$nom_como_inconvenientes_hog',
                        '$responsabilidades_est_hog',
                        '$nom_responsabilidades_est_hog',
                        '$afecto_est_hog',
                        '$nom_afecto_est_hog',
                        '$tipo_vivienda_hog',
                        '$tenencia_vivienda_hog',
                        '$nom_tenencia_vivienda_hog',
                        '$material_vivienda_hog',
                        '$servicios_vivienda_hog',
                        '$num_personas_vivienda_hog',
                        '$estado_hog',
                        '$fecha_alta_hog',
                        '$id_usu_alta_hog',
                        '$fecha_edit_hog',
                        '$id_usu'
                    )";

    if (mysqli_query($mysqli, $sql_update)) {
        echo "<script>alert('Información actualizada correctamente.'); window.location.href = 'showentornoHogar.php';</script>";
    } else {
        $error_message = mysqli_error($mysqli);
        echo "<script>alert('Error al actualizar la información: $error_message. Por favor, verifica los datos e inténtalo de nuevo.'); window.location.href = 'showentornoHogar.php';</script>";
    }
}
