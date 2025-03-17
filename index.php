<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <link rel="shortcut icon" href="./Configuration/logo.ico" type="image/x-icon">
    <script src="./Configuration/JQuery/jquery-3.7.1.js"></script>
    <link href="./Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="./Configuration/DataTables/datatables.min.css">
    <link rel="stylesheet" href="./View/Css/dark-mode.css">
    <link rel="stylesheet" href="./View/Css/Datatable.css">
</head>

<body>
    <div class="row align-items-center justify-content-center mb-3">

        <div class="col-md-3 mb-2 my-5">
            <!--Condiciones para traer los datos en caso de error o alerta y confirmacion de subida de datos-->
            <?php
            include_once "./Controller/validation_message.php";
            ?>
            <script>
                //Funcion para mostar advertencias 
                function showWarnings() {
                    const hiddenWarnings = document.getElementById('hidden-warnings');
                    hiddenWarnings.classList.add('show'); // Muestra las advertencias ocultas
                    event.target.classList.add('hide'); // Oculta el botón
                }
            </script>
            <!--Formulario de la entrada de archivo CSV-->
            <form action="./Controller/Importar_BD.php" method="POST" enctype="multipart/form-data"
                class="mb-3 my-5 shadow-sm">
                <input type="file" name="Proyeccion_garan" class="form-control form-control-sm">
                <button type="submit" class="form-control form-control-sm">Upload File</button>
            </form>
        </div>
        <?php
        include './Configuration/Connection.php';

        try {
            //Consulta para contar el numero de registros
            $stmt = $pdo->prepare("SELECT serial_number FROM vista_equipos_usuarios");
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Lista de CLientes (Numero de Registros)
            $Totalclient = count($resultados);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
        <br>
        <div id="Consulta" class="mb-3">
            <div class="row mb-3">
                <form method="GET" action="">
                    <div class="container-fluid">
                        <div class="col-md-12 text-center mb-5">
                            <h1 id="totalC" class="counter">Asset List
                                <strong id="total-client">
                                    <?php echo number_format($Totalclient, 0, ',', '.'); ?>
                                </strong>
                            </h1>
                            <script>
                                const counterElement = document.getElementById('total-client');
                                const counter = parseInt(counterElement.textContent);
                                let count = 0;
                                const intervalId = setInterval(() => {
                                    if (count < counter) {
                                        count++;
                                        counterElement.textContent = count.toString().padStart(counter.toString().length, '0');
                                    } else {
                                        clearInterval(intervalId);
                                    }
                                }, 10);
                            </script>
                        </div>
                        <!-- Barra Busqueda -->
                        <nav id="Navitem"
                            class="navbar navbar-expand-lg navbar-light bg-light fixed-top rounded shadow-sm">
                            <div class="container-fluid">

                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>"
                                    class="navbar-brand d-flex align-items-center">
                                    <img src="./Configuration/logo.ico" alt="Logo" width="30" height="30">
                                    <span class="ms-2">3 Shape</span>
                                </a>

                                <!-- Botón hamburguesa para móviles -->
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarCollapse" aria-controls="navbarCollapse"
                                    aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>

                                <!-- Contenido colapsable -->
                                <div class="collapse navbar-collapse" id="navbarCollapse">
                                    <div class="navbar-nav">
                                        <a id="generatePDF" class="nav-link me-2" data-url="./Model/PruebaV.php" target="_blank" title="Generate General PDF">Generate General PDF</a>
                                        <a id="generateActaSalida" class="nav-link me-2" data-url="./Model/Acta_salida.php" title="Generate Departure Certificate">Generate Departure Certificate</a>
                                        <a id="generateActaEntrada" class="nav-link me-2" data-url="./Model/Acta_entrada.php" title="Generate Entry Certificate">Generate Entry Certificate</a>
                                        <a href="./View/Int_entrada.php" class="nav-link me-2" title="Change Asset">Change Asset</a>
                                        <a href="./View/Int_salida.php" class="nav-link me-2" title="Output Asset">Output Asset</a>
                                    </div>
                                </div>
                            </div>
                        </nav>

                        <!-- Formulario de Consulta -->
<div id="Barra_Busqueda" class="d-flex row align-items-center justify-content-center">
    <div class="col-md-12">
        <div class="row g-2">
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                    <input type="text" name="search_assetname" placeholder="Asset Name"
                        class="form-control shadow-sm bg-light"
                        oninput="this.value = this.value.toUpperCase()" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" id="search_serial" name="search_serial"
                        placeholder="Serial Number" class="form-control shadow-sm bg-light"
                        autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <select id="search_user_status" name="search_user_status[]"
                        class="shadow-sm form-control bg-light">
                        <option value="0">Select a option</option>
                        <?php
                        include './Configuration/Connection.php';
                        $stmt = $pdo->prepare("SELECT DISTINCT user_status FROM vista_equipos_usuarios");
                        $stmt->execute();
                        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (empty($resultados)) {
                            echo '<option value=""> No results found </option>';
                        } else {
                            foreach ($resultados as $resultado) {
                                echo '<option value="' . $resultado['user_status'] . '">' . $resultado['user_status'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                    <input type="text" name="search_status_change" placeholder="Status Change"
                        class="shadow-sm form-control bg-light">
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                    <input type="text" id="garantia" name="search_fecha_fin" placeholder="End date"
                        class="shadow-sm form-control bg-light">
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    <input type="text" id="sgarantia" name="search_fecha_inicio"
                        placeholder="Start date" class="shadow-sm form-control bg-light">
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                    <input type="number" id="search_cedula" name="search_cedula" placeholder="ID"
                        class="form-control shadow-sm bg-light" autocomplete="off" min="0">
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                    <input type="text" id="search_user" name="search_user" placeholder="Last User" 
                        class="form-control shadow-sm bg-light" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-globe"></i></span>
                    <select id="search_purchase_country" name="search_purchase_country[]"
                        class="shadow-sm form-control bg-light">
                        <option value="0">Select a country</option>
                        <?php
                        include './Configuration/Connection.php';
                        $stmt = $pdo->prepare("SELECT DISTINCT purchase_country FROM vista_equipos_usuarios");
                        $stmt->execute();
                        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (empty($resultados)) {
                            echo '<option value=""> No results found </option>';
                        } else {
                            foreach ($resultados as $resultado) {
                                echo '<option value="' . $resultado['purchase_country'] . '">' . $resultado['purchase_country'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
                                    <div class="my-2 mb-2 d-flex justify-content-left">
    <button type="submit" class="shadow btn btn-light border-success me-1" id="search_btn">
        <i class="bi bi-search"></i> Search
    </button>
    <button type="reset" class="shadow btn btn-light border-danger" id="reset_btn">Reset
    <i class="bi bi-arrow-clockwise"></i>
    </button>
</div>

                                    <script>
                                        // Boton de Restart
                                        document.getElementById('reset_btn').addEventListener('click', function() {
                                            const textInputs = document.querySelectorAll('input[type="text"]');
                                            const numberInputs = document.querySelectorAll('input[type="number"]');
                                            const selectElements = document.querySelectorAll('select');

                                            textInputs.forEach(input => input.value = '');
                                            numberInputs.forEach(input => input.value = '');
                                            selectElements.forEach(select => select.selectedIndex = 0);
                                        });
                                    </script>
                                </div>
                                <script>
                                    //Funcion para la validacion de entrada de fecha
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const garantiaInput = document.getElementById('garantia');
                                        const sgarantiaInput = document.getElementById('sgarantia');

                                        function formatInputDate(input) {
                                            input.addEventListener('input', function(e) {
                                                let value = e.target.value.replace(/\D/g, '');
                                                let formattedDate = '';

                                                if (value.length > 8) {
                                                    value = value.substr(0, 8);
                                                }

                                                if (value.length > 0) {
                                                    formattedDate = value.substr(0, 2);
                                                    if (value.length > 2) {
                                                        formattedDate += '/' + value.substr(2, 2);
                                                    }
                                                    if (value.length > 4) {
                                                        formattedDate += '/' + value.substr(4);
                                                    }
                                                }

                                                let month = parseInt(value.substr(0, 2));
                                                let day = parseInt(value.substr(2, 2));

                                                if (month > 12) {
                                                    month = 12;
                                                    formattedDate = '12' + formattedDate.substr(2);
                                                }

                                                if (day > 31) {
                                                    day = 31;
                                                    formattedDate = formattedDate.substr(0, 3) + '31' +
                                                        formattedDate.substr(5);
                                                }

                                                e.target.value = formattedDate;
                                            });
                                            input.addEventListener('blur', function(e) {
                                                const datePattern =
                                                    /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/\d{4}$/;
                                                if (!datePattern.test(e.target.value)) {
                                                    e.target.classList.add('is-invalid');
                                                } else {
                                                    e.target.classList.remove('is-invalid');
                                                }
                                            });
                                        }
                                        formatInputDate(garantiaInput);
                                        formatInputDate(sgarantiaInput);
                                    });
                                </script>
                            </div>
                            <div id="resultados3" class="text-center my-2"></div>
                            <div id="resultados2" class="text-center my-2"></div>
                            <div id="resultados" class="text-center my-2"></div>
                            <script>
                                //Script automatico para la busqueda del ultimo usuario
                                document.getElementById('search_user').addEventListener('input', function() {
                                    const value = this.value;
                                    if (value.length > 0) {
                                        this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
                                    }
                                });
                            </script>
                            <script src="./View/Js/Automatic_search.js"></script>
                        </div>
                        <script src="./View/Js/Search_Selectors.js"></script>
                        <div class="col-md-4">
                            <?php
                            include_once './Controller/busqueda_Multicriterio.php';
                            ?>
                        </div>
                    </div>
                    <!--Inputs de Busqueda-->
            </div>
        </div>
        <br>
        </form>
        <div class="table-responsive my-5">
            <!--Boton para registrar un nuevo equipo-->

            <!--Tabla Principal donde se muestran los datos en este caso los activos de la empresa-->
            <div class="table-container">
                <?php
                $selectedColumns = isset($_SESSION['selected_fields']) ? $_SESSION['selected_fields'] : [];
                ?>
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    <a href="./View/Int_Registro_equipo.php" class="shadow btn btn-secondary "><i
                            class="fas fa-plus"></i>
                        Register
                        new device</a>
                    <button id="deleteAllButton" type="button" class="shadow btn btn-danger ">
                        Delete All Logs</button>
                    <script>
                        //Script para la eliminacion de todos los registros
                        document.getElementById('deleteAllButton').addEventListener('click', function() {
                            if (confirm('Are you sure you want to delete all logs?')) {
                                if (window.confirm('Are you sure you want to delete all logs?')) {
                                    window.location.href = './Controller/delete_regist.php';
                                }
                            }
                        });
                    </script>
                </div>
                <table id="mainTable" class="shadow-lg table table-bordered table-striped fade-in">
                    <thead class="text-center">
                        <tr class="align-items-center text-center">
                            <!--Columnas de la tabla-->
                            <th>@</th>
                            <th>Asset Name</th>
                            <th>Serial Number</th>
                            <th>User Status</th>
                            <th>Last User</th>
                            <th>Job Title</th>
                            <th>Status Change</th>
                            <th>Purchase Country</th>
                            <th>Warranty End Date</th>
                            <th>Expired</th>
                            <th>ID</th>
                            <?php
                            $selectedColumns = $_SESSION['selected_fields'] ?? [];
                            $headerTemplate = "<th class='text-center'>%s</th>";
                            $headers = implode('', array_map(function ($column) use ($headerTemplate) {
                                return sprintf($headerTemplate, $column);
                            }, $selectedColumns));
                            echo $headers;
                            ?>
                            <th class="text-center"><i class="fas fa-sync-alt"></i></th>
                            <th class="text-center"><i class="fa fa-times"></i></th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php
                        include './Model/Main_Table.php';
                        ?>
                    </tbody>
                    <!--Ventana emergente donde se selecciona las opcion de select process-->
                    <div id="modalConfirmacion" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; display: none;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);">
                            <button id="btnCerrarModal" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
                            <p id="modalMensaje" style="color: black;"></p>
                            <button id="btnAceptar" class="btn btn-secondary"> Option 1: Generate Departure </button>
                            <button id="btnCancelar" class="btn btn-secondary my-2"> Option 2: Create Entry </button>
                            <button id="btnOpcion3" class="btn btn-secondary my-2"> Option 3: Asset Information </button>
                        </div>
                    </div>
                    <script src="./View/Js/Select_proccess.js"></script>
                </table>
                <br>
                <br>
                <label>Ctrl + Q to login as <a href="#" id="ShowmiFormulario" title="Login as Manager">Manager</a></label>
                <br>
            </div>
        </div>
        <div id="FormAdmin" class="container">
            <div class="row d-flex justify-content-center align-items-center mt-5">
                <div class="col-md-4 text-center">
                    <?php
                    // Generar un token CSRF si no existe
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('miFormulario');
                            const link = document.getElementById('ShowmiFormulario');

                            // Función única para manejar el clic
                            function toggleForm(event) {
                                event.preventDefault();
                                form.style.display = form.style.display === 'none' ? 'block' : 'none';
                            }
                            // Eliminar cualquier listener previo y asignar uno nuevo
                            link.removeEventListener('click', toggleForm);
                            link.addEventListener('click', toggleForm);

                            // Atajo de teclado
                            document.addEventListener('keydown', (event) => {
                                if (event.ctrlKey && (event.key === 'q' || event.key === 'Q')) {
                                    event.preventDefault();
                                    form.style.display = form.style.display === 'none' ? 'block' : 'none';
                                }
                            });
                        });
                    </script>
                    <form id="miFormulario" style="display: none;" action="./Admin/token.php" method="POST" class="bg-light p-4 rounded shadow-lg border border-secondary">
                        <h2 id="totalC" class="text-center mb-4">Admin Login</h2>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group mb-3">
                            <input type="password" id="passwordadmin" name="passwordadmin" placeholder="Enter your password" class="form-control shadow-lg border border-secondary" required autocomplete="off">
                            <div class="invalid-feedback">Please enter a valid password.</div>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100 border border-secondary">Send</button>
                        <div class="text-center mt-2">
                            <a href="./View/Int_forgot_pasword.php" id="" class="text-decoration-none">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>


        </div>
        <!-- Scripts -->
        <script src="./View/Js/DatatableIndex.js"></script>
        <script src="./Configuration/JQuery/jquery-3.7.1.js"></script>
        <script src="./Configuration/DataTables/datatables.min.js"></script>
        <script src="./Configuration/bootstrap/js/bootstrap.min.js"></script>
        <script src="./View/Js/Change_query.js"></script>
</body>
<footer class="container align-items-center text-center mt-5">
    <div class="row">
        <div class="col-md-4">
            <p>&copy; <?php echo date("Y"); ?> - All rights reserved.</p>
            <p>Page created on: <?php echo date("F j, Y", filectime(__FILE__)); ?></p>
        </div>
        <div class="col-md-4">
            <p>Developed by: Daniel Patiño</p>
            <p>Contact: <a href="mailto:Daniel.Cortes@3Shape.com">Daniel.Cortes@3Shape.com</a></p>
        </div>
        <div class="col-md-4">
            <p class="text-muted">Version: 3.0.0</p>
            <p class="text-muted">Last updated: <?php echo date("F j, Y", filemtime(__FILE__)); ?></p>
        </div>
    </div>
</footer>

</html>