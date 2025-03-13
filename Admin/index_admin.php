<?php
session_start(); // Iniciar sesión

// Tiempo de expiración de la sesión (en segundos)
$tiempo_inactivo = 4600;

if (empty($_SESSION['csrf_token'])) {
    // si no existe el token se crea en la sesion de csrf
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    // Establecer una cookie con el mensaje de error
    setcookie('error_message', 'Session expired, please try again.', time() + 30, '/');

    // Redirigir y cerrar sesión
    header("Location: ../index.php");
    include_once '../Controller/Cerrar_sesion.php';
    exit();
}

// Actualizar la última actividad
$_SESSION['last_activity'] = time();

// Verificar si el usuario es admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    include_once '../Controller/Cerrar_sesion.php';
    header("Location: ../index.php");
    exit("Acceso denegado.");

}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']); // Limpia el mensaje después de mostrarlo
}
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']); // Limpia el mensaje después de mostrarlo
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index Admin</title>
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="../View/Css/dark-mode.css">
    <link rel="stylesheet" href="../View/Css/Form.css">
    <style>
    .hidden-form {
        visibility: hidden;
        height: 0;
        overflow: hidden;
    }

    .visible-form {
        visibility: visible;
        height: auto;
    }
    </style>
</head>


<body>
    <!--Script para validar la salida de la sesión-->
    <script>
    //Funcion para mostar advertencias 
    function showWarnings() {
        const hiddenWarnings = document.getElementById('hidden-warnings');
        hiddenWarnings.style.display = 'block'; // Muestra las advertencias ocultas
        event.target.style.display = 'none'; // Oculta el botón
    }

    // Función generalizada para alternar la visibilidad de los formularios
    function toggleVisibility(links, forms) {
        links.forEach((linkId, index) => {
            document.getElementById(linkId).addEventListener('click', function(event) {
                event.preventDefault(); // Evitar comportamiento por defecto del enlace
                const form = document.getElementById(forms[index]);
                form.style.display = (form.style.display === 'block' || form.style.display === '') ? 'none' : 'block';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleVisibility(
            ['showFormLink', 'showFormLink1', 'showFormLink2', 'showFormLink5', 'showFormLink6'],
            ['FormAddForm', 'RemoveForm', 'FormAddTable', 'addBDCSV', 'addFormTodo']
        );
    });

    </script>

    <div class="row d-flex justify-content-center align-items-center">
        <div class="row">

            <h1 class="text-center my-5">Dashboard Admin</h1>
            <br>
            <br>
            <br>
            <div class="col-md-12">
                <nav class="navbar d-flex navbar-expand-lg navbar-light bg-light rounded shadow">
                    <div class="container-fluid">
                        <a class="navbar-brand">Admin Dashboard</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav d-flex w-100">
                                <li class="nav-item">
                                    <button class="nav-link active" id="homeindex" href="../index.php">Home</button>
                                    <script>
                                    document.getElementById('homeindex').addEventListener('click', function() {
                                        if (confirm('Are you sure you want to back?')) {
                                            window.location.href = '../index.php';
                                        }
                                    });
                                    </script>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="showFormLink" href="#">Add fields</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="showFormLink1" href="#">Remove fields</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="showFormLink2" href="#">Equipment Status</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="showFormLink5" href="#">Export Data Base From CSV</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="showFormLink6" href="#">Adding fields to all Forms</a>
                                </li>
                                <li class="nav-item ms-auto">
                                    <a type="button" class="btn btn-secondary" href="../View/Int_LogsDelete.php">
                                        <i class="fa-solid fa-info-circle"></i>
                                    </a>
                                    <a type="button" class="btn btn-secondary" href="../View/Int_changePsw.php">
                                        <i class="fa-solid fa-gear"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <br>
            <br>

        </div>
        <!--Formulario de Permisos de Administrador-->
        <div class="row my-5">
            <div class="col-md-6 ">
                <form action="add_fields.php" id="FormAddForm" style="display: block;" method="POST" class="shadow">
                    <h1 class="text-center">Adding fields to the database</h1>
                    <!---Token de autenticacion en caso de  CRSF-->
                    <h4>Data Type <span>*</span></h4>
                    <select name="data_type" required>
                        <option value="">Select Data Type</option>
                        <option value="Date">Date</option>
                        <option value="INTEGER">Integer</option>
                        <option value="VARCHAR(255)">Varchar</option>
                        <option value="BOOLEAN">Boolean</option>
                        <option value="BIGINT">BigInteger</option>
                    </select>
                    <div id="boolean" class="alert alert-warning mt-2" style="display: none;">
                        Warning: BOOLEAN data type selected. this function may not work correctly
                    </div>
                    <script>
                    document.querySelector('select[name="data_type"]').addEventListener('change', function() {
                        if (this.value === 'BOOLEAN') {
                            document.getElementById('boolean').style.display = 'block';
                        } else {
                            document.getElementById('boolean').style.display = 'none';
                        }
                    });
                    </script>
                    </select>

                    <h4>Name Data <span>*</span></h4>
                    <input type="text" id="field_name" name="field_name" required onfocus="showNetworkAddressInfo()"
                        oninput="validateInput()" />

                    <div id="networkAddressInfo" class="alert alert-info mt-2" style="display: none;">
                        The field name should start with a letter and can contain letters, numbers, and underscores. No
                        spaces or special characters allowed.
                    </div>

                    <div id="errorMessage" class="alert alert-danger mt-2" style="display: none;">
                        The field cannot contain only numbers.
                    </div>

                    <script>
                    function showNetworkAddressInfo() {
                        document.getElementById("networkAddressInfo").style.display = "block";
                    }

                    function validateInput() {
                        const field = document.getElementById("field_name");
                        const errorMessage = document.getElementById("errorMessage");

                        if (/^\d+$/.test(field.value)) {
                            errorMessage.style.display = "block";
                        } else {
                            errorMessage.style.display = "none";
                        }
                    }

                    function validateForm() {
                        const field = document.getElementById("field_name").value;

                        if (/^\d+$/.test(field)) {
                            alert("The field cannot contain only numbers.");
                            return false; // Evita el envío del formulario
                        }
                        return true; // Permite el envío si la validación es correcta
                    }

                    function showNetworkAddressInfo() {
                        document.getElementById('networkAddressInfo').style.display = 'block';
                    }
                    </script>
                    <h4>Table <span>*</span></h4>
                    <select name="table_name" required>
                        <option value="">Select Table</option>
                        <option value="usuarios_equipos">Users</option>
                        <option value="equipos">Assets</option>
                    </select>
                    <div class="btn-block">
                        <button id="Addfield" type="submit" onclick="return validateForm()"
                            class="btn btn-secondary">Add Field</button>
                    </div>
                </form>
                <br>
            </div>

            <div class="col-md-6">
               <canvas id="myChart" style="max-width: 100%; height: auto; display: block;"></canvas>
                <br>
                <script>
                // Esperar a que el DOM esté listo
                document.addEventListener("DOMContentLoaded", function() {
                    var ctx = document.getElementById('myChart').getContext('2d');
                    // Agrupar y contar las ocurrencias de 'user_status'
                    var counts = {};
                    data.forEach(item => {
                        var status = item['user_status'];
                        counts[status] = (counts[status] || 0) + 1;
                    });

                    // Convertir el objeto counts a arrays de etiquetas y valores
                    var labels = Object.keys(counts);
                    var values = Object.values(counts);

                    console.log("Labels:", labels); // Verifica los valores únicos
                    console.log("Values:", values); // Verifica las cuentas

                    // Crear el gráfico
                    var myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Equipment Status',
                                data: values,
                                backgroundColor: 'rgba(13, 202, 240, 0.2)',
                                borderColor: 'rgba(13, 202, 240, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
                </script>
                <?php
                // Obtener datos de la base de datos
                $sql = "SELECT * FROM vista_equipos_usuarios";

                include '../Configuration/Connection.php';
                // Ejecutar la consulta y obtener todos los resultados como un array asociativo
                $stmt = $pdo->query($sql);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Pasar los datos a JavaScript
                echo "<script>";
                echo "var data = " . json_encode($data) . ";";
                echo "</script>";
                ?>

            </div>

            <div class="col-md-6">
                <form action="remove_fields.php" id="RemoveForm" style="display: none;" method="post" class="shadow">
                    <h1 class="text-center">Remove fields</h1>

                    <h4>Name Field <span>*</span></h4>
                    <?php
                    try {
                        include '../Configuration/Connection.php';

                        $sql = "SELECT COLUMN_NAME as Field 
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                                ORDER BY TABLE_NAME, ORDINAL_POSITION";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        // Si se encontraron resultados
                        if ($stmt->rowCount() > 0) {
                            echo '<select id="removefields" class="shadow-sm form-select" name="removefields" onfocus="showNetworkInfo()">';
                            echo '<option value="0" selected>Select a field</option>';
                            // Iterar sobre los resultados
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($row["Field"]) . '">' . htmlspecialchars($row["Field"]) . '</option>';
                            }

                            echo '</select>';
                        } else {
                            echo '<p>No data found.</p>';
                        }

                    } catch (PDOException $e) {
                        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>


                    <br>
                    <!--Token de autenticacion en caso de  CRSF-->
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" ">
                    <div class=" btn-block">
                    <button id="Removefield" type="submit" class="btn btn-secondary">Remove Field</button>
                    <div id="networkInfo" class="alert alert-warning mt-2" style="display: none;">
                        Warning: This action cannot be undone. Please be sure before removing the field.
                    </div>
                    <script>
                    function showNetworkInfo() {
                        document.getElementById('networkInfo').style.display = 'block';
                    }
                    </script>
                    <script>
                    document.getElementById('RemoveForm').addEventListener('submit', function(event) {
                        const select1 = document.getElementById('removefields');
                        let errorMessage = "Please fix the following errors: \n";
                        let hasError = false;

                        if (select1.value === '0') {
                            errorMessage += "- Select a Field.\n";
                            hasError = true;
                        }
                        if (hasError) {
                            event.preventDefault(); // Detener el envío del formulario si hay errores
                            alert(errorMessage);
                        } else {
                            if (!confirm('Are you sure you want to remove this data?')) {
                                event.preventDefault(); // Cancelar el envío si el usuario no confirma
                            }
                        }
                    });
                    </script>
            </div>
            <br>


            </form>
        </div>


        <div class="col-md-6">
            <form action="add_fieldsTable.php" method="post" id="FormAddTable" style="display: none;" class="shadow">
                <h1 class="text-center">Add fields in the main table</h1>

                <h4>Name Field <span>*</span></h4>
                <?php
                try {
                    include '../Configuration/Connection.php';

                    $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                    ORDER BY TABLE_NAME, ORDINAL_POSITION";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        echo '<select id="addmfields" class="shadow-sm form-select" name="addmfields[]" multiple required>';
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '">' . htmlspecialchars($row["Field"]) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo '<p>No data found.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
                <br>
                <div id="dynamicInput"></div>
                <!--Token de autenticacion en caso de  CRSF-->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="btn-block">
                    <button id="AddfieldTable" type="submit" class="btn btn-secondary">Add Field</button>
                    <br>
                    <script>
                    // Función para agregar campos dinámicos
                    document.getElementById('FormAddTable').addEventListener('submit', function(event) {
                        const select1 = document.getElementById('addmfields');
                        const nameField = document.querySelector(
                            'input[name="name"]'); // Asume que el campo de nombre tiene el atributo name="name"
                        let errorMessage = "Please fix the following errors: \n";
                        let hasError = false;

                        // Validar si se seleccionó al menos un campo
                        if (select1.selectedOptions.length === 0) {
                            errorMessage += "- Select at least one Field.\n";
                            hasError = true;
                        }

                        // Validar si el campo de nombre contiene solo números
                        if (nameField && /^\d+$/.test(nameField.value)) {
                            errorMessage += "- The name field cannot contain only numbers.\n";
                            hasError = true;
                        }

                        // Validar si el campo ya existe
                        const selectedOptions = Array.from(select1.selectedOptions);
                        const onlyNumbers = selectedOptions.every(option => !isNaN(option.value));
                        if (onlyNumbers) {
                            errorMessage += "- Please select at least one field that is not a number.\n";
                            hasError = true;
                        }

                        if (hasError) {
                            event.preventDefault(); // Detener el envío del formulario si hay errores
                            alert(errorMessage);
                        } else {
                            if (!confirm('Are you sure you want to Add this data?')) {
                                event.preventDefault(); // Cancelar el envío si el usuario no confirma
                            }
                        }
                    });
                    </script>
                </div>
                <br>
            </form>
        </div>

        <!--Formulario de Registro Admin -->

        <div class="col-md-6 my-2">
            <form action="../Controller/Exportar_BD.php" style="display: none;" method="post" id="addBDCSV"
                class="shadow">
                <h1 class="text-center">Export Data to CSV</h1>
                <h4>Select Columns <span>*</span></h4>
                <!--Token de autenticacion en caso de  CRSF-->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <?php
                try {
                    include '../Configuration/Connection.php';
                    $sql = "SELECT COLUMN_NAME 
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                                AND COLUMN_NAME != 'fk_id'
                                ORDER BY TABLE_NAME, ORDINAL_POSITION";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        echo '<select id="columns" class="shadow-sm form-select" name="columns[]" multiple required>';
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . htmlspecialchars($row["COLUMN_NAME"]) . '">' . htmlspecialchars($row["COLUMN_NAME"]) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo '<p>No data found.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
                <br>
                <div class="btn-block"></div>
                <button type="submit" class="btn btn-secondary">Export to CSV</button>
        </div>
        <script>
        document.getElementById('addBDCSV').addEventListener('submit', function(event) {
            if (!confirm('Are you sure you want to export the data to CSV?')) {
                event.preventDefault(); // Cancelar el envío si el usuario no confirma
            }
        });
        </script>
        </form>
    </div>
    <div class="col-md-6">
        <form action="add_fieldTDO.php" method="post" style="display: none;" id="addFormTodo" class="shadow">
            <h1>Add fields to all forms</h1>
            <h4>Select Columns <span>*</span></h4>
            <!--Token de autenticacion en caso de  CRSF-->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <?php
            try {
                include '../Configuration/Connection.php';
                $sql = "SELECT COLUMN_NAME 
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                                AND COLUMN_NAME NOT IN ('assetname', 'serial_number', 'purchase_country','warranty_enddate','expired','new_laptop','fk_id','fk_assetname','user_status',
                                'last_user','job_title','status_change','cedula','Carnet','LLave','Tipo_ID') 
                                ORDER BY TABLE_NAME, ORDINAL_POSITION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    echo '<select id="columns" class="shadow-sm form-select" name="columns[]" multiple required>';
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . htmlspecialchars($row["COLUMN_NAME"]) . '">' . htmlspecialchars($row["COLUMN_NAME"]) . '</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<p>No data found.</p>';
                }
            } catch (PDOException $e) {
                echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
            <br>
            <div class="btn-block"></div>
            <button type="submit" class="btn btn-secondary">Add Field</button>
    </div>
    <script>
    document.getElementById('addBDCSV').addEventListener('submit', function(event) {
        if (!confirm('Are you sure you want to export the data to CSV?')) {
            event.preventDefault(); // Cancelar el envío si el usuario no confirma
        }
    });
    </script>
    </form>
    </div>

    <!--Modal para la validacion de seriales repetidos-->
    <div class="modal fade" id="repeatedSerialsModal" tabindex="-1" aria-labelledby="repeatedSerialsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h2 class="modal-title" id="repeatedSerialsModalLabel"><i class="fas fa-exclamation-triangle"></i>
                        Repeated Serials Found</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <h4><strong>Warning!</strong> The following serials are repeated:</h4>
                        <br>
                        <ul id="repeatedSerialsList"></ul>
                    </div>
                    <div class="alert alert-danger " role="alert">
                        <strong>Action Required:</strong> Do you want to delete them?
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i>
                        No</button>
                    <button type="button" class="btn btn-danger" id="deleteSerialsButton"><i
                            class="fas fa-trash-alt"></i> Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    // // Función para agregar campos dinámicos   
    document.addEventListener('DOMContentLoaded', function() {
        // Check for repeated serials
        // se redirije al archivo para la consulta de seriales repetidos 
        fetch('../Controller/check_repeated_serials.php')
            .then(response => response.json())
            .then(data => {
                if (data.repeatedSerials && data.repeatedSerials.length > 0) {
                    const repeatedSerialsList = document.getElementById('repeatedSerialsList');
                    repeatedSerialsList.innerHTML = ''; // Clear the list
                    data.repeatedSerials.forEach(serial => {
                        const li = document.createElement('li');
                        li.textContent = serial;
                        repeatedSerialsList.appendChild(li);
                    });
                    const repeatedSerialsModal = new bootstrap.Modal(document.getElementById(
                        'repeatedSerialsModal'));
                    repeatedSerialsModal.show();
                }
            })
            .catch(error => console.error('Error fetching repeated serials:', error));

        document.getElementById('deleteSerialsButton').addEventListener('click', function() {
            if (confirm('Are you sure you want to delete the repeated serials?')) {
                fetch('../Controller/delete_serial.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            delete: true
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Serials deleted successfully.');
                            location.reload(); // Reload the page to refresh the data
                        } else {
                            alert('Failed to delete serials: ' + data.error);
                        }
                    })
                    .catch(error => console.error('Error deleting serials:', error));
            }
        });
    });
    </script>

</body>

<footer class="align-items-center text-center mt-5">
    <!--Pie de Pagina-->
    <p>&copy; <?php echo date("Y"); ?> - All rights reserved.</p>
    <p>Page created on: <?php echo date("F j, Y", filectime(__FILE__)); ?></p>
    <p>Page name: <?php echo basename(__FILE__); ?></p>
</footer>

<script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
<script src="../Configuration/JQuery/Chart.js"></script>
<script src="../View/Js/Background.js"></script>


</html>
