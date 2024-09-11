<!DOCTYPE html>
<html lang="es">
<head>
    <title>Importar Excel</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>Importar archivo de Excel</h2>
    <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" name="archivo" accept=".xlsx, .xls">
        <input type="submit" value="Importar">
    </form>

    <!-- Mostrar el mensaje de carga -->
    <div id="loading" style="display:none;">
        <h3>Procesando, por favor espera...</h3>
        <img src="loading.gif" alt="Cargando...">
    </div>

    <div id="resultado"></div>

    <script>
$('#uploadForm').on('submit', function(e) {
    e.preventDefault();
    
    let partes = ['primera', 'segunda', 'tercera'];
    let parteActual = 2;
    
    function procesarParte(parte) {
        let formData = new FormData($('#uploadForm')[0]);
        formData.append('parte', parte);
        $.ajax({
            url: 'procesarSimat.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log("Respuesta recibida:", response);  // Ver respuesta completa

                try {
                    let resultado = JSON.parse(response);
                    $('#resultado').append('<p>Parte ' + resultado.parte + ' procesada.</p>');

                    // Mostrar loteDatos
                    if (resultado.loteDatos && Array.isArray(resultado.loteDatos)) {
                        $('#resultado').append('<h3>Datos del lote:</h3>');
                        resultado.loteDatos.forEach(function(item, index) {
                            $('#resultado').append('<p>Item ' + index + ': ' + item + '</p>');
                        });
                    } else {
                        $('#resultado').append('<p>No se encontraron datos en el lote.</p>');
                    }

                } catch (error) {
                    console.error("Error al parsear el JSON:", error);
                    $('#resultado').append('<p>Error al parsear el JSON. Respuesta recibida: ' + response + '</p>');
                }

                parteActual++;

                if (parteActual < partes.length) {
                    procesarParte(partes[parteActual]);
                } else {
                    $('#loading').hide();
                    alert('Archivo procesado completamente.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#loading').hide();
                console.error('Error al procesar el archivo:', textStatus, errorThrown);
                $('#resultado').append('<p>Error al procesar el archivo.</p>');
            }
        });
    }
    
    $('#loading').show();
    procesarParte(partes[parteActual]);
});
    </script>
</body>
</html>
