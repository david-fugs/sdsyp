<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDSYP</title>
    <link rel="stylesheet" type="text/css" href="../../css/styles.css">
    <link rel="stylesheet" type="text/css" href="../../css/estilos2024.css">
    <link rel="stylesheet" href="styleSell.css">
    <!-- Bootstrap CSS -->
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Librerías de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</head>
<?php
include("../../conexion.php");
$grupos = "SELECT * FROM grupos";
$result_grupos = mysqli_query($mysqli, $grupos);
if (!$result_grupos) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

if (isset($_GET['delete'])) {
    $centro_vida = $_GET['delete'];
    deleteMember($centro_vida);
}

function deleteMember($centro_vida)
{
    global $mysqli; // Asegurar acceso a la conexión global

    $query = "DELETE FROM centro_vida WHERE id_centro_vida  = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $centro_vida);

    if ($stmt->execute()) {
        echo "<script>alert('centro_vida borrada corecctamente');
        window.location = 'seeCenter.php';</script>";
    } else {
        echo "<script>alert('Error borrando la centro_vida');
        window.location = 'seeCenter.php';</script>";
    }

    $stmt->close();
}

?>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 40px; text-align: center;"><b><i
                class="fa-solid fa-file-signature"></i> Centro Vida o CPSAM</b></h1>
    <!-- Tabla de Ventas -->
    <div class="container mt-5">
        <div class="position-relative mb-3">
            <h2 class="text-center">Centro Vida o CPSAM Registradas</h2>
            <button type="button" class="btn btn-success position-absolute top-0 end-0" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                Agregar Centro Vida o CPSAM
            </button>

        </div>
        <table class="table table-striped" id="salesTable">
            <thead>
                <tr>
                    <th>No.Centro Vida o CPSAM</th>
                    <th>Descripcion Grupo</th>
                    <th>Descripcion Centro Vida o CPSAM</th>
                </tr>
            </thead>
            <tbody>
                <?php include "getCenter.php"; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal Add Person -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addCenter.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar Meta
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila para el select (Meta Asociada) -->
                        <div class="row mb-3 mt-3">
                            <div class="col-md-12 form-floating">
                                <select name="id_grupo" id="id_grupo" class="form-select">
                                    <option value="">Seleccione grupo Asociada</option>
                                    <?php while ($row = mysqli_fetch_assoc($result_grupos)) { ?>
                                        <option value="<?php echo $row['id_grupo']; ?>"><?php echo $row['descripcion_grupo']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="id_grupo">grupo Asociada</label>
                            </div>
                        </div>
                        <!-- Fila para la descripción de la actividad -->
                        <div class="row">
                            <div class="col-md-12 form-floating mt-3">
                                <input type="text" class="form-control" id="descripcion_actividad" name="descripcion_actividad" placeholder="Descripcion">
                                <label for="descripcion_actividad">Descripción Actividad</label>
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



    <!-- modal edicion -->
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 shadow-sm">
                <div class="modal-header bg-dark text-white"> <!-- Negro con texto blanco -->
                    <h5 class="modal-title" id="modalEdicionLabel">Editar grupo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="editCenter.php" method="POST">
                    <div class="modal-body px-4 py-3">

                        <div class="mb-3">
                            <label for="edit-descripcion" class="form-label">Descripcion </label>
                            <input type="text" class="form-control" id="edit-descripcion" name="descripcion_actividad">
                        </div>
                        <div class="mb-3">
                            <label for="edit-grupo" class="form-label">grupo </label>
                            <select name="id_grupo" id="edit-id_grupo" class="form-select text-truncate" style="width: 100%;">                                <option value="">Seleccione grupo asociada</option>
                                <?php
                                foreach ($result_grupos as $grupos) {
                                    echo "<option value='" . $grupos['id_grupo'] . "'>" . $grupos['descripcion_grupo'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="id_actividad" id="edit-id_actividad">
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="guardarCambios">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <br /><a href="../../access.php"><img src='../../img/atras.png' width="72" height="72" title="back" /></a><br>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modalEdicion = document.getElementById("modalEdicion");

        modalEdicion.addEventListener("shown.bs.modal", function(event) {
            const button = event.relatedTarget;

            // Datos generales
            document.getElementById("edit-descripcion").value = button.getAttribute("data-descripcion_actividad");
            document.getElementById("edit-id_grupo").value = button.getAttribute("data-id_grupo");
            document.getElementById("edit-id_actividad").value = button.getAttribute("data-id_actividad");

        });
    });
</script>


</html>