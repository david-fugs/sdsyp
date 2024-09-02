<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

$num_doc_est = $_GET['num_doc_est'];

include("../../conexion.php");

$query = "SELECT * FROM desempeno WHERE num_doc_est = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $num_doc_est);
$stmt->execute();
$result = $stmt->get_result();

function Si1No2($value)
{
    if ($value == 1) {
        return "Si";
    } else {
        return "No";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Detalles de Encuestas</title>
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <style>
        .table th,
        .table td {
            white-space: nowrap;
        }

        .table-responsive {
            overflow-x: auto;
        }
    </style>
    <script>
        function confirmarEliminacion(id_prePostnatales) {
            if (confirm('¿Está seguro que desea eliminar esta encuesta? Esta acción no se puede deshacer.')) {
                window.location.href = 'deleteSurvey.php?id_prePostnatales=' + id_prePostnatales;
            }
        }
    </script>
</head>

<body>
    <div class="container mt-5">
        <h1>Detalles de Encuestas Aplicadas</h1>
        <div class="table-responsive mt-5">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>No.</th>
                        <th>ID</th>
                        <th>Fecha de Digitación</th>
                        <th>Municipio de Digitación</th>
                        <th>Nombre del Encuestador</th>
                        <th>Rol del Encuestador</th>

                        <th>Desempeño Ciencias naturales</th>
                        <th>Desempeño Ciencias sociales</th>
                        <th>Desempeño Educacion Fisica</th>
                        <th> Etica y valores</th>
                        <th>Religion</th>
                        <th>Artes</th>
                        <th>Humanidades</th>
                        <th>Matematicas</th>
                        <th>Fisica</th>
                        <th>Algebra</th>
                        <th>Calculo</th>
                        <th>Ingles</th>
                        <th>Tecnologia Informatica</th>
                        <th>Emprendimiento</th>
                        <th>Areas Tecnicas</th>
                        <th>Filosofia</th>
                        <th>Ciencias Economicas</th>
                        <th>Vinculado doble titulacion</th>
                        <th>Cual programa</th>

                      


                        <th>Fecha de Aplicación</th>
                        <th>Fecha de Modificación</th>
                        <th>ELIMINAR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$i}</td>
                            <td>{$row['id_desempeno']}</td>
                            <td>{$row['fecha_dig_desempeno']}</td>
                            <td>{$row['mun_dig_desempeno']}</td>
                            <td>{$row['nombre_encuestador_desempeno']}</td>
                            <td>{$row['rol_encuestador_desempeno']}</td>
                            <td>{$row['comprension_ciencia_desempeno']}</td>
                            <td>{$row['comprension_sociales_desempeno']}</td>
                            <td>{$row['comprension_edufisica_desempeno']}</td>
                             <td>{$row['comprension_etica_desempeno']}</td>
                            <td>{$row['comprension_religion_desempeno']}</td>
                            <td>{$row['comprension_artistica_desempeno']}</td>
                            <td>{$row['comprension_humanidades_desempeno']}</td>
                            <td>{$row['comprension_matematicas_desempeno']}</td>
                            <td>{$row['comprension_fisica_desempeno']}</td>
                            <td>{$row['comprension_algebra_desempeno']}</td>
                            <td>{$row['comprension_calculo_desempeno']}</td>
                            <td>{$row['comprension_ingles_desempeno']}</td>
                            <td>{$row['comprension_tecno_desempeno']}</td>
                            <td>{$row['comprension_emprendimiento_desempeno']}</td>
                            <td>{$row['comprension_areastec_desempeno']}</td>
                            <td>{$row['comprension_filosofia_desempeno']}</td>
                            <td>{$row['comprension_cienciaseco_desempeno']}</td>
                            ";
                        echo '<td>' . Si1No2($row['doble_titu_desempeno']) . '</td>';
                        echo "<td>{$row['nom_dobletitu_desempeno']}</td>";
                        echo '<td>' . $row['fechacreacion_desempeno'] . '</td>';
                        echo '<td>' . $row['fechaedicion_desempeno'] . '</td>';
                        echo "<td><button class='btn btn-danger' onclick='confirmarEliminacion({$row['id_desempeno']})'>Eliminar</button></td>";
       echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <a href="javascript:history.back()" class="btn btn-primary">Volver</a>
    </div>
</body>

</html>
<script>
    function confirmarEliminacion(id_prePostnatales) {
        if (confirm('¿Está seguro que desea eliminar esta encuesta? Esta acción no se puede deshacer.')) {
            window.location.href = 'deleteSurvey.php?id_prePostnatales=' + id_prePostnatales;
        }
    }
</script>
<?php
$stmt->close();
$mysqli->close();
?>