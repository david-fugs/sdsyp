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

if (isset($_GET['delete'])) {
    $grupo = $_GET['delete'];
    deleteMember($grupo);
}

function deleteMember($grupo)
{
    global $mysqli; // Asegurar acceso a la conexión global

    $query = "DELETE FROM grupos WHERE id_grupo  = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $grupo);

    if ($stmt->execute()) {
        echo "<script>alert('grupo borrada corectamente');
        window.location = 'seeGroup.php';</script>";
    } else {
        echo "<script>alert('Error borrando el grupo');
        window.location = 'seeGroup.php';</script>";
    }

    $stmt->close();
}

?>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 40px; text-align: center;"><b><i
                class="fa-solid fa-file-signature"></i> Grupos</b></h1>
    <!-- Tabla de Ventas -->
    <div class="container mt-5">
        <div class="position-relative mb-3">
            <h2 class="text-center">Grupos Registradas</h2>
            <button type="button" class="btn btn-success position-absolute top-0 end-0" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                Agregar Grupo
            </button>

        </div>
        <table class="table table-striped" id="salesTable">
            <thead>
                <tr>
                    <th>No.Grupo</th>
                    <th>Descripcion Grupo</th>
                    <th>Límite Personas</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php include "getGroup.php"; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal Add Person -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addGroup.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar Grupo
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila para la descripción de la condicion -->
                        <div class="row">
                            <div class="col-md-12 form-floating mt-3">
                                <input type="text" class="form-control" id="descripcion_grupo" name="descripcion_grupo" placeholder="Descripcion" required>
                                <label for="descripcion_grupo">Descripción grupo</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 form-floating mt-3">
                                <input type="number" class="form-control" id="limite_personas" name="limite_personas" placeholder="Límite de personas" min="1" required>
                                <label for="limite_personas">Límite de personas</label>
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
                    <h5 class="modal-title" id="modalEdicionLabel">Editar Grupo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="editGroup.php" method="POST">
                    <div class="modal-body px-4 py-3">

                        <div class="mb-3">
                            <label for="edit-descripcion" class="form-label">Descripcion </label>
                            <input type="text" class="form-control" id="edit-descripcion" name="descripcion_grupo" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-limite" class="form-label">Límite de personas</label>
                            <input type="number" class="form-control" id="edit-limite" name="limite_personas" min="1" required>
                        </div>
                        <input type="hidden" name="id_grupo" id="edit-id_grupo">
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
            document.getElementById("edit-descripcion").value = button.getAttribute("data-descripcion_grupo");
            document.getElementById("edit-limite").value = button.getAttribute("data-limite_personas");
            document.getElementById("edit-id_grupo").value = button.getAttribute("data-id_grupo");

        });
    });
</script>


</html>