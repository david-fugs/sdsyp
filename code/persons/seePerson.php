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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Librerías de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</head>
<?php
include("../../conexion.php");
$programas = "SELECT * FROM programas ";
$result_programas = mysqli_query($mysqli, $programas);
if (!$result_programas) {
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
                class="fa-solid fa-file-signature"></i> PERSONAS</b></h1>

    <div class="flex">
        <div class="box">
            <form action="showitems.php" method="get" class="form">
                <input name="upc_item" type="text" placeholder="Upc ">
                <input name="item" type="text" placeholder="Item">
                <input name="ref" type="text" placeholder="Reference">
                <input value="Search" type="submit">
            </form>
        </div>
    </div>

    <!-- Tabla de Ventas -->
    <div class="container mt-5">
        <div class="position-relative mb-3">
            <h2 class="text-center">Personas Registradas</h2>
            <button type="button" class="btn btn-success position-absolute top-0 end-0" data-bs-toggle="modal" data-bs-target="#modalNewPerson">
                Agregar Persona
            </button>

        </div>
        <table class="table table-striped" id="salesTable">
            <thead>
                <tr>
                    <th>Cedula</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Telefono</th>
                    <th>Referencia</th>
                    <th>Programas</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php include "getPersons.php"; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal Add Person -->
    <div class="modal fade" id="modalNewPerson" tabindex="-1" aria-labelledby="modalNewPersonLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Hacemos el modal más ancho -->
            <div class="modal-content">
                <form action="addPerson.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalNewPersonLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Agregar Persona
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Fila 1 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="cedula_persona" name="cedula_persona" placeholder="Cédula" required autocomplete="off" autofocus>
                                <label class="" for="cedula_persona">Cédula</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="telefono_persona" name="telefono_persona" placeholder="Teléfono" required>
                                <label for="telefono_persona">Teléfono</label>
                            </div>
                        </div>

                        <!-- Fila 2 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="nombres_persona" name="nombres_persona" placeholder="Nombres" required>
                                <label for="nombres_persona">Nombres</label>
                            </div>
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="apellidos_persona" name="apellidos_persona" placeholder="Apellidos" required>
                                <label for="apellidos_persona">Apellidos</label>
                            </div>
                        </div>

                        <!-- Fila 3 -->
                        <div class="row">
                            <div class="col-md-6 mb-3 form-floating">
                                <input type="text" class="form-control" id="referencia_persona" name="referencia_persona" placeholder="Referencia" required>
                                <label for="referencia_persona">Referencia</label>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Programas</label>
                                <?php foreach ($result_programas as $programa) { ?>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="programa[]"
                                            id="programa_<?= $programa['id_programa']; ?>"
                                            value="<?= $programa['id_programa']; ?>">
                                        <label class="form-check-label" for="programa_<?= $programa['id_programa']; ?>">
                                            <?= $programa['nombre_programa']; ?>
                                        </label>
                                    </div>
                                <?php } ?>
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

                <form action="editSucursal.php" method="POST">
                    <div class="modal-body px-4 py-3">

                        <div class="mb-3">
                            <label for="edit-cedula" class="form-label">Cedula </label>
                            <input type="text" class="form-control" id="edit-cedula" name="cedula_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-nombre" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="edit-nombre" name="nombres_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-apellido" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="edit-apellido" name="apellidos_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="edit-telefono" name="telefono_persona">
                        </div>
                        <div class="mb-3">
                            <label for="edit-referencia" class="form-label">Referencia</label>
                            <input type="text" class="form-control" id="edit-referencia" name="referencia_persona">
                        </div>


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
        let modalEdicion = document.getElementById("modalEdicion");

        modalEdicion.addEventListener("show.bs.modal", function(event) {
            let button = event.relatedTarget; // Botón que abrió el modal

            document.getElementById("edit-cedula").value = button.getAttribute("data-cedula");
            document.getElementById("edit-nombre").value = button.getAttribute("data-nombre");
            document.getElementById("edit-apellido").value = button.getAttribute("data-apellidos");
            document.getElementById("edit-telefono").value = button.getAttribute("data-telefono");
            document.getElementById("edit-referencia").value = button.getAttribute("data-referencia");



        });

        // Enviar datos al servidor con AJAX al hacer clic en "Guardar cambios"
        document.getElementById("guardarCambios").addEventListener("click", function() {
            let formData = new FormData(document.getElementById("formEditar"));

            fetch("editItems.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Registro actualizado correctamente");
                        window.location.reload(); // Recargar la página para ver los cambios
                    } else {
                        alert("Error al actualizar");
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
</script>

</html>