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

$acciones = "SELECT * FROM acciones ";
$result_acciones = mysqli_query($mysqli, $acciones);
if (!$result_acciones) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

if (isset($_GET['delete'])) {
    $id_politica = $_GET['delete'];
    deleteMember($id_politica);
}

function deleteMember($id_politica)
{
    global $mysqli; // Asegurar acceso a la conexión global

    $query = "DELETE FROM politicas_publicas WHERE id_politica = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_politica);

    if ($stmt->execute()) {
        echo "<script>alert('Política pública borrada correctamente');
        window.location = 'seePublicPolicies.php';</script>";
    } else {
        echo "<script>alert('Error borrando la política pública');
        window.location = 'seePublicPolicies.php';</script>";
    }

    $stmt->close();
}

?>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 40px; text-align: center;"><b><i
                class="fa-solid fa-scale-balanced"></i> POLÍTICAS PÚBLICAS</b></h1>
    
    <!-- Tabla de Políticas Públicas -->
    <div class="container mt-5">
        <div class="position-relative mb-3">
            <h2 class="text-center">Gestión de Políticas Públicas</h2>
            <div class="position-absolute top-0 end-0">
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalNewPolicy">
                    <i class="bi bi-plus-circle"></i> Agregar Política Pública
                </button>
            </div>
        </div>
        <table class="table table-striped" id="policiesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripción</th>
                    <th>Acción</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php include "getPublicPolicies.php"; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Add Policy -->
    <div class="modal fade" id="modalNewPolicy" tabindex="-1" aria-labelledby="modalNewPolicyLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="addPublicPolicy.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPolicyLabel">
                            <i class="bi bi-plus-circle-fill me-2"></i>Agregar Política Pública
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <input type="text" class="form-control" id="descripcion_politica" name="descripcion_politica" placeholder="Descripción" required autofocus>
                                <label for="descripcion_politica">Descripción Política Pública</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3 form-floating">
                                <select class="form-select" id="id_accion" name="id_accion">
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_acciones as $accion) { ?>
                                        <option value="<?= $accion['id_accion']; ?>"><?= $accion['descripcion_accion']; ?></option>
                                    <?php } ?>
                                </select>
                                <label for="id_accion">Acción</label>
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

    <!-- Modal Edición Política -->
    <div class="modal fade" id="modalEdicion" tabindex="-1" aria-labelledby="modalEdicionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 shadow-sm">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalEdicionLabel">Editar Política Pública</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="editPublicPolicy.php" method="POST">
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label for="edit-descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="edit-descripcion" name="descripcion_politica" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-accion" class="form-label">Acción</label>
                            <select class="form-select" id="edit-accion" name="id_accion">
                                <option value="">Seleccione...</option>
                                <?php foreach ($result_acciones as $accion) { ?>
                                    <option value="<?= $accion['id_accion']; ?>"><?= $accion['descripcion_accion']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <input type="hidden" name="id_politica" id="edit-id_politica">
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
        // Modal edición política
        const modalEdicion = document.getElementById("modalEdicion");
        modalEdicion.addEventListener("shown.bs.modal", function(event) {
            const button = event.relatedTarget;
            document.getElementById("edit-descripcion").value = button.getAttribute("data-descripcion_politica");
            document.getElementById("edit-id_politica").value = button.getAttribute("data-id_politica");
            document.getElementById("edit-accion").value = button.getAttribute("data-id_accion");
        });

        // Inicializar DataTable
        $('#policiesTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
            },
            "order": [[ 0, "asc" ]]
        });
    });
</script>

</html>
