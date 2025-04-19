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
$programas = "SELECT * FROM programas ";
$result_programas = mysqli_query($mysqli, $programas);
if (!$result_programas) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

$movimientos = "SELECT * FROM movimientos";
$result_movimientos = mysqli_query($mysqli, $movimientos);
if (!$result_movimientos) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

if (isset($_GET['delete'])) {
    $cedula_persona = $_GET['delete'];
    deleteMember($cedula_persona);
}

function deleteMember($cedula_persona)
{
    global $mysqli; // Asegurar acceso a la conexión global

    $query = "DELETE FROM personas WHERE cedula_persona  = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $cedula_persona);

    if ($stmt->execute()) {
        echo "<script>alert('Persona borrada corecctamente');
        window.location = 'seePerson.php';</script>";
    } else {
        echo "<script>alert('Error borrando la persona');
        window.location = 'seePerson.php';</script>";
    }

    $stmt->close();
}

?>

<body>
    <center style="margin-top: 20px;">
        <img src='../../img/logo.png' width="150" height="120" class="responsive">
    </center>
    <h1 style="color: #412fd1; text-shadow: #FFFFFF 0.1em 0.1em 0.2em; font-size: 40px; text-align: center;"><b><i
                class="fa-solid fa-file-signature"></i>MOVIMIENTOS PERSONAS</b></h1>

    <div class="flex">
        <div class="box">
            <form action="seePersonMovement.php" method="get" class="form">
                <input name="cedula_persona" type="number" placeholder="Cédula"
                    value="<?= isset($_GET['cedula_persona']) ? htmlspecialchars($_GET['cedula_persona']) : '' ?>">

                <input name="nombre" type="text" placeholder="Nombre"
                    value="<?= isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '' ?>">

                <select name="movimiento">
                    <option value="">Selecciona movimiento</option>
                    <?php foreach ($result_movimientos as $movimiento) {
                        $selected = (isset($_GET['movimiento']) && $_GET['movimiento'] == $movimiento['id_movimiento']) ? 'selected' : '';
                    ?>
                        <option value="<?= $movimiento['id_movimiento']; ?>" <?= $selected ?>>
                            <?= $movimiento['descripcion_movimiento']; ?>
                        </option>
                    <?php } ?>
                </select>

                <input value="Buscar" type="submit">
            </form>
        </div>
    </div>

    <!-- Tabla de Ventas -->
    <div class="container mt-5">
        <div class="position-relative mb-3">
            <h2 class="text-center">Movimientos Registradas</h2>
            <button type="button" class="btn btn-success position-absolute top-0 end-0" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                Agregar Movimiento
            </button>

        </div>
        <table class="table table-striped" id="salesTable">
            <thead>
                <tr>
                    <th>Cedula</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Movimiento</th>
                    <th>Fecha Movimiento</th>
                    <th>Observacion</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php include "getPersonMovement.php"; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal Add Person -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Hacemos el modal más ancho -->
            <div class="modal-content">
                <form action="addPersonMovement.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar Movimiento
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila 1 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="cedula_form" name="cedula_persona" placeholder="Cédula" required autocomplete="off" autofocus>
                                <label class="" for="cedula_persona">Cédula</label>
                            </div>

                            <div class="col-md-6 mb-3 form-floating mt-1">
                                <select class="form-select" id="movimiento" name="movimiento" required>
                                    <option value="" selected>Seleccione...</option>
                                    <?php foreach ($result_movimientos as $movimiento) { ?>
                                        <option value="<?= $movimiento['id_movimiento']; ?>"><?= $movimiento['descripcion_movimiento']; ?></option>
                                    <?php } ?>
                                </select>
                                <label class="" for="cedula_persona">Movimiento</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating mt-2">
                                <input type="date" class="form-control" id="fecha_movimiento" name="fecha_movimiento" placeholder="Fecha Movimiento">
                                <label for="fecha_movimiento">Fecha Movimiento</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="observacion_movimiento" name="observacion_movimiento" placeholder="Observacion Movimiento">
                                <label for="observacion_movimiento">Observacion</label>
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
        <div class="modal-dialog">
            <div class="modal-content rounded-4 shadow-sm">
                <div class="modal-header bg-dark text-white"> <!-- Negro con texto blanco -->
                    <h5 class="modal-title" id="modalEdicionLabel">Edit Store Info</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="editPersonMovement.php" method="POST">
                    <div class="modal-body px-4 py-3">

                        <div class="mb-3">
                            <label for="edit-cedula" class="form-label">Cedula </label>
                            <input type="text" class="form-control" id="edit-cedula" name="cedula_persona" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-nombre" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="edit-nombre" name="nombres_persona" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-apellido" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="edit-apellido" name="apellidos_persona" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-movimiento" class="form-label">Movimiento</label>
                            <select class="form-select" id="edit-movimiento" name="id_movimiento">
                                <option value="" selected>Seleccione...</option>
                                <?php foreach ($result_movimientos as $movimiento) { ?>
                                    <option value="<?= $movimiento['id_movimiento']; ?>"><?= $movimiento['descripcion_movimiento']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-fecha_movimiento" class="form-label">fecha_movimiento</label>
                            <input type="date" class="form-control" id="edit-fecha_movimiento" name="fecha_movimiento">
                        </div>
                        <div class="mb-3">
                            <label for="edit-observacion" class="form-label">Observacion</label>
                            <input type="text" class="form-control" id="edit-observacion" name="observacion_movimiento">
                        </div>
                        <input type="hidden" name="cedula_original" id="cedula_original" value="">
                        <input type="hidden" name="id_movimiento_persona" id="id_movimiento_persona" value="">
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
            document.getElementById("edit-cedula").value = button.getAttribute("data-cedula");
            document.getElementById("edit-nombre").value = button.getAttribute("data-nombre");
            document.getElementById("edit-apellido").value = button.getAttribute("data-apellidos");
            document.getElementById("edit-fecha_movimiento").value = button.getAttribute("data-fecha_movimiento");
            document.getElementById("cedula_original").value = button.getAttribute("data-cedula");
            document.getElementById("edit-movimiento").value = button.getAttribute("data-movimiento");
            document.getElementById("edit-observacion").value = button.getAttribute("data-observacion_movimiento");
            document.getElementById("id_movimiento_persona").value = button.getAttribute("data-id_movimiento_persona");
        });
    });
    $(document).ready(function() {
        function buscarPersona() {
            const cedula = $('#cedula_form').val().trim();
            if (cedula === '') return;

            $.ajax({
                url: '../buscar_persona.php',
                method: 'POST',
                data: {
                    cedula: cedula
                },
                dataType: 'json',
                success: function(response) {
                    if (response.encontrado) {
                        alert('Nombre de la persona: ' + response.nombres + ' ' + response.apellidos);
                    } else {
                        alert('Persona no encontrada.');
                    }
                },
                error: function() {
                    alert('Error al buscar persona.');
                }
            });
        }

        // Buscar cuando presiona Enter
        $('#cedula_form').on('keypress', function(e) {
            if (e.which === 13) { // Enter
                buscarPersona();
            }
        });

        // Buscar cuando hace clic fuera
        $('#cedula_form').on('blur', function() {
            buscarPersona();
        });
    });
</script>

</html>