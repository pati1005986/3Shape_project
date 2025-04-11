<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión
}

$tiempo_inactivo = 5000; // Tiempo de expiración de la sesión (en segundos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    // Establecer una cookie con el mensaje de Co por sesión expirada
    setcookie('error_message', 'Session expired, please try again.', time() + 30, '/');
    // Redirigir y cerrar sesión
    include_once '../Controller/Cerrar_sesion.php';
    header("Location: ../index.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    // Si no existe el token, se crea en la sesión de CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$_SESSION['last_activity'] = time(); // Actualizar la última actividad

// Verificar si el usuario es admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirigir y cerrar sesión si no es admin

    include_once '../Controller/Cerrar_sesion.php';
    header("Location: ../index.php");
    exit("Acceso denegado.");
}

include_once "../Controller/validation_message.php";
include '../Configuration/Connection.php';
?>
<?php
// Array de mapeo de nombres 
$fieldLabels = require '../View/Fragments/field_labels.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>

    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="../View/Css/dark-mode.css">
    <link rel="stylesheet" href="../View/Css/Form.css">
    <script src="../Configuration/JQuery/sweetalert2.all.min.js"></script>
    <script src="../Configuration/JQuery/sweetalert2@11.js"></script>
    <link rel="stylesheet" href="../Configuration/JQuery/sweetalert2.min.css">
    <link rel="stylesheet" href="../View/Css/button-styles.css">
    <link rel="stylesheet" href="../View/Css/admin.css">
    <link rel="stylesheet" href="../View/Css/checkbox-styles.css">

<body>
    <script>
        // Funcion para mostrar advertencias 
        function showWarnings() {
            const hiddenWarnings = document.getElementById('hidden-warnings');
            hiddenWarnings.style.display = 'block'; // Muestra las advertencias ocultas
            event.target.style.display = 'none'; // Oculta el botón
        }

        function toggleVisibility(links, forms) {
            function saveAllFormsState() {
                forms.forEach(formId => {
                    const form = document.getElementById(formId);
                    localStorage.setItem(`form-${formId}`, form.style.display);
                });
            }

            links.forEach((linkId, index) => {
                document.getElementById(linkId).addEventListener('click', function(event) {
                    event.preventDefault();

                    // Ocultar todos los formularios
                    forms.forEach(formId => {
                        document.getElementById(formId).style.display = 'none';
                    });

                    // Mostrar y mover el formulario seleccionado
                    const selectedForm = document.getElementById(forms[index]);
                    const firstContainer = document.getElementById('tool');

                    if (firstContainer && selectedForm) {
                        // Mover el formulario después de first_container
                        selectedForm.style.display = 'block';
                        firstContainer.insertAdjacentElement('beforebegin', selectedForm);

                        selectedForm.style.display = 'block';
                    }

                    saveAllFormsState();
                });
            });

            // Cargar estados y posicionar inicialmente los formularios visibles
            forms.forEach(formId => {
                const form = document.getElementById(formId);
                const storedDisplay = localStorage.getItem(`form-${formId}`);

                if (storedDisplay) {
                    form.style.display = storedDisplay;
                    // Si el formulario está visible al cargar, moverlo
                    if (storedDisplay === 'block') {
                        const firstContainer = document.getElementById('tool');
                        if (firstContainer) {
                            firstContainer.insertAdjacentElement('beforebegin', form);
                        }
                    }
                } else {
                    form.style.display = 'none';
                }
            });
        }
        // Relacion entre formulario y nav-links
        document.addEventListener('DOMContentLoaded', function() {
            toggleVisibility(
                ['showFormLink', 'showFormLink1', 'showFormLink2', 'showFormLink3', 'showFormLink5', 'showFormLink6', 'showFormLink7'],
                ['FormAddForm', 'RemoveForm', 'FormAddTable', 'RemoveFieldsTable', 'addBDCSV', 'addFormTodo', 'addLgColumn']
            );
        });
    </script>

    <div class="row d-flex justify-content-center align-items-center">
        <div class="row">
            <br>
            <br>
            <br>
            <!--Navbar-->
            <div class="col-md-12">
                <nav class="navbar d-flex navbar-expand-lg navbar-light bg-light rounded shadow sticky-top">
                    <div class="container-fluid">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="navbar-brand d-flex align-items-center">
                            <img src="../Admin/3shape-logo.png" alt="Logo" width="150" height="31">
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav d-flex w-100">
                                <li class="nav-item">
                                    <a class="nav-link active" id="homeindex" href="#" onclick="confirmHomeRedirect(); return false;"><i class="fa-solid fa-home"></i> Home</a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="fieldsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-database"></i> Admin's Operations
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="fieldsDropdown">
                                        <li><a class="dropdown-item" id="showFormLink" href="#"><i class="fa-solid fa-plus"></i> Add fields</a></li>
                                        <li><a class="dropdown-item" id="showFormLink1" href="#"><i class="fa-solid fa-trash"></i> Remove fields</a></li>
                                        <li><a class="dropdown-item" id="showFormLink2" href="#"><i class="fa-solid fa-table"></i> Add columns to table</a></li>
                                        <li><a class="dropdown-item" id="showFormLink3" href="#"><i class="fa-solid fa-minus"></i> Remove columns from table</a></li>
                                        <li><a class="dropdown-item" id="showFormLink5" href="#"><i class="fa-solid fa-file-export"></i> Export Data Base</a></li>
                                        <li><a class="dropdown-item" id="showFormLink6" href="#"><i class="fa-solid fa-list"></i> Adding fields to all Forms</a></li>
                                        <li><a class="dropdown-item" id="showFormLink7" href="#"><i class="fa-solid fa-history"></i> Adding Logs in columns</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="chartsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-chart-line"></i> Charts
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="chartsDropdown">
                                        <li><button class="dropdown-item chart-switch" data-chart="statusChart"><i class="fa-solid fa-laptop"></i> Equipment Status</button></li>
                                        <li><button class="dropdown-item chart-switch" data-chart="countryChart"><i class="fa-solid fa-globe"></i> Top Countries</button></li>
                                        <li><button class="dropdown-item chart-switch" data-chart="expiredChart"><i class="fa-solid fa-calendar-times"></i> Expired Assets</button></li>
                                    </ul>
                                </li>
                                <li class="nav-item ms-auto">
                                    <div class="btn-group">
                                        <a title="Notifications" type="button" class="btn btn-primary" onclick="showModal()">
                                            <i class="fa-solid fa-bell"></i>
                                        </a>
                                        <a title="Log Files" type="button" class="btn btn-info" href="../View/Int_LogsDelete.php">
                                            <i class="fa-solid fa-info-circle"></i>
                                        </a>
                                        <a title="Settings" type="button" class="btn btn-secondary" href="../View/Int_changePsw.php">
                                            <i class="fa-solid fa-gear"></i>
                                        </a>
                                        <button id="darkModeToggle" class="btn btn-dark" title="Toggle Dark Mode">
                                            <i class="fa-solid fa-moon"></i>
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <br>
            <br>
        </div>
        <div>
            <center>
                <h1 id="totalC" class="text-center my-5" style="font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">Admin Dashboard <i class="fas fa-chart-bar"></i></h1>
            </center>
        </div>
        <div class="row my-5" style="display: flex; gap: 20px;">
            <script src="../View/Js/diagram.js"> </script>
            <div class="col-md-5 ms-4">
                <div id="tool" style="display: none;"></div>
            </div>

            <div class="col-md-6" id="first_container">
                <div id="chartContainer" style="position: relative; min-height: 380px; display: flex;">
                    <?php
                    // Obtener todos los datos en una sola consulta
                    try {
                        // Datos para el primer gráfico
                        $statusData = $pdo->query("SELECT user_status FROM vista_equipos_usuarios")->fetchAll(PDO::FETCH_ASSOC);

                        // Datos para el segundo gráfico
                        $countryData = $pdo->query("SELECT purchase_country, COUNT(*) AS total_ventas FROM equipos GROUP BY purchase_country ORDER BY total_ventas DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

                        // Datos para el tercer gráfico
                        $expiredData = $pdo->query("SELECT expired, COUNT(*) AS total FROM equipos GROUP BY expired ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);

                        echo "<script>
                    var chartData = {
                        status: " . json_encode($statusData) . ",
                        country: " . json_encode($countryData) . ",
                        expired: " . json_encode($expiredData) . "
                    };
                </script>";
                    } catch (PDOException $e) {
                        echo '<p class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>

                    <!-- Los gráficos se crearán dinámicamente aquí -->
                    <canvas id="statusChart" class="chart-canvas"></canvas>
                    <canvas id="countryChart" class="chart-canvas d-none"></canvas>
                    <canvas id="expiredChart" class="chart-canvas d-none"></canvas>
                </div>
            </div>
            <!--Formulario para agregar columnas en la base de datos-->
            <div class="col-md-6">
                <form action="add_fields.php" id="FormAddForm" style="display: block;" method="POST" class="shadow border border-secondary rounded p-4 bg-white">
                    <h1 class="text-center mb-4 fw-bold">Adding fields to the database</h1>
                    <div class="mb-3">
                        <h4 class="form-label">Data Type <span class="text-danger">*</span></h4>
                        <select name="data_type" class="form-select form-select-lg mb-3" required>
                            <option value="">Select Data Type</option>
                            <option value="Date">Date</option>
                            <option value="INTEGER">Integer</option>
                            <option value="VARCHAR(255)">Varchar</option>
                            <option value="BOOLEAN">Boolean</option>
                            <option value="BIGINT">BigInteger</option>
                        </select>
                    </div>
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
                    <div class="mb-3">
                        <h4 class="form-label">Name of Data <span class="text-danger">*</span></h4>
                        <input type="text" id="field_name" name="field_name" class="form-control form-control-lg" required
                            onfocus="showNetworkAddressInfo()" oninput="validateInput()" placeholder="Enter field name" />
                    </div>
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
                    </script>
                    <div class="mb-4">
                        <h4 class="form-label">Select Table <span class="text-danger">*</span></h4>
                        <select name="table_name" class="form-select form-select-lg" required>
                            <option value="">Select Table</option>
                            <option value="usuarios_equipos">Users</option>
                            <option value="equipos">Assets</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-primary nav-arrow btn-lg" onclick="navigateTo('prev')">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>

                        <div class="btn-block">
                            <button id="Addfield" type="submit" onclick="return validateForm()"
                                class="btn btn-primary btn-lg px-4">Add Field</button>
                        </div>

                        <button type="button" class="btn btn-outline-primary nav-arrow btn-lg" onclick="navigateTo('next')">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
                <br>
            </div>
            <!--Formulario pra eliminar columans de la base de datos-->
            <div class="col-md-6">
                <form action="remove_fields.php" id="RemoveForm" style="display: none;" method="post" class="shadow border border-secondary rounded p-4 bg-white">
                    <h1 class="text-center mb-4 fw-bold">Remove Fields</h1>
                    <div class="mb-3">
                        <h4 class="form-label">Name Field <span class="text-danger">*</span></h4>
                        <?php
                        try {
                            $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                    AND COLUMN_NAME NOT IN ('assetname', 'serial_number', 'warranty_enddate', 'expired', 'fk_id', 'fk_assetname',
                    'last_user', 'job_title', 'cedula', 'HeadSet', 'fecha_salida','purchase_country','user_status','status_change') 
                    ORDER BY TABLE_NAME, ORDINAL_POSITION";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">';
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<div class="col">';
                                    echo '<div class="form-check hover-effect p-3 rounded d-flex align-items-center">';
                                    echo '<input class="form-check-input" type="checkbox" name="removefields[]" value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '" id="field_' . htmlspecialchars($row["Field"]) . '">';
                                    echo '<label class="form-check-label text-truncate ms-2" for="field_' . htmlspecialchars($row["Field"]) . '">' . htmlspecialchars($row["Field"]) . '</label>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p>No data found.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }
                        ?>
                        <br>
                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>

                            <div class="btn-block">
                                <button id="Removefield" type="submit" class="btn btn-danger btn-lg px-4">Remove Field</button>
                            </div>

                            <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>

                        <div id="networkInfo" class="alert alert-warning mt-4" style="display: none;">
                            <strong>Warning:</strong> This action cannot be undone. Please be sure before removing the field.
                        </div>
                        <script>
                            function showNetworkInfo() {
                                document.getElementById('networkInfo').style.display = 'block';
                            }
                        </script>
                        <script>
                            document.getElementById('RemoveForm').addEventListener('submit', function(event) {
                                const checkboxes = document.querySelectorAll('input[name="removefields[]"]');
                                let selectedCount = 0;
                                checkboxes.forEach(checkbox => {
                                    if (checkbox.checked) selectedCount++;
                                });

                                if (selectedCount === 0) {
                                    event.preventDefault();
                                    alert('Por favor, seleccione al menos un campo para eliminar.');
                                } else {
                                    if (!confirm('¿Está seguro de que desea eliminar los campos seleccionados? Esta acción no se puede deshacer.')) {
                                        event.preventDefault();
                                    }
                                }
                            });
                        </script>
                    </div>
                    <br>
                </form>
            </div>
            <!--Formulario para agregar campos dinamicos en la tabla principal de index-->
            <div class="col-md-6">
                <form action="add_fieldsTable.php" method="post" id="FormAddTable" style="display: none;"
                    class="shadow border border-secondary">
                    <h1 class="text-center">Add Fields to the Main Table</h1>
                    <h4>Name Field <span>*</span></h4>
                    <?php
                    try {
                        $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
            AND COLUMN_NAME NOT IN ('assetname', 'serial_number', 'warranty_enddate', 'expired', 'fk_id', 'fk_assetname',
            'last_user', 'job_title', 'cedula', 'HeadSet', 'fecha_salida','purchase_country','user_status','status_change') 
            ORDER BY TABLE_NAME, ORDINAL_POSITION";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            echo '<div class="row">';
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="col-md-4 col-sm-6 mb-2">';
                                echo '<div class="form-check">';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="addmfields[]" value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '" id="check_' . htmlspecialchars($row["Field"]) . '">';
                                echo '<label class="form-check-label ms-2" for="check_' . htmlspecialchars($row["Field"]) . '">';
                                echo htmlspecialchars($row["Field"]);
                                echo '</label>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<p>No data found.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>
                    <br>
                    <div id="dynamicInput"></div>
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>

                        <div class="btn-block">
                            <button id="AddfieldTable" type="submit" class="btn btn-secondary">Add Field</button>
                            <br>
                        </div>

                        <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                </form>
            </div>
            <!-- Add this within the existing column in index_admin.php, similar to the "Add Fields to the Main Table" form -->
            <div class="col-md-6">
                <form action="remove-fields-table-script.php" method="post" id="RemoveFieldsTable" style="display: none;"
                    class="shadow border border-secondary">
                    <h1 class="text-center">Remove Fields from the Main Table</h1>
                    <h4>Select Fields to Remove <span>*</span></h4>
                    <?php
                    try {

                        $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                                AND COLUMN_NAME NOT IN ('assetname', 'serial_number', 'purchase_country','warranty_enddate','expired','fk_id','fk_assetname','user_status',
                                'last_user','job_title','status_change','cedula') 
                                ORDER BY TABLE_NAME, ORDINAL_POSITION";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            echo '<div class="row">';
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="col-md-4 col-sm-6 mb-2">';
                                echo '<div class="form-check custom-checkbox mb-2">';
                                echo '<div class="form-check d-flex align-items-center">';
                                echo '<input class="form-check-input me-2" type="checkbox" name="addmfields[]" value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '" id="check_' . htmlspecialchars($row["Field"]) . '">';
                                echo '<label class="form-check-label text-truncate" for="check_' . htmlspecialchars($row["Field"]) . '">' . htmlspecialchars($row["Field"]) . '</label>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<p>No removable fields found.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>
                    <br>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>

                        <div class="btn-block">
                            <button id="RemoveFieldTable" type="submit" class="btn btn-danger">Remove Field</button>
                        </div>

                        <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    <div class="alert alert-warning mt-2">
                        <strong>Warning:</strong> If you want to reinsert a field that was deleted, use the "Add Fields to the Main Table" function. Proceed with caution.
                    </div>
            </div>
            </form>
        </div>
        <!--Exportar datos de ,a base de datos a formato CSV y TXT-->
        <div class="col-md-6 my-2">
            <form action="../Controller/Exportar_BD.php" style="display: none;" method="post" id="addBDCSV"
                class="shadow border border-secondary p-3">
                <h1 class="text-center">Export Data to CSV</h1>
                <h4>Select Columns <span>*</span></h4>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll">Select All</label>
                </div>

                <div class="row">
                    <?php
                    try {
                        $sql = "SELECT COLUMN_NAME 
                            FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                            AND COLUMN_NAME != 'fk_id'
                            ORDER BY TABLE_NAME, ORDINAL_POSITION";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="col-md-4 col-sm-6 mb-2">';
                                echo '<div class="form-check">';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="columns[]" value="' . htmlspecialchars($row["COLUMN_NAME"]) . '" id="col_' . htmlspecialchars($row["COLUMN_NAME"]) . '">';
                                echo '<label class="form-check-label ms-2" for="col_' . htmlspecialchars($row["COLUMN_NAME"]) . '">' . htmlspecialchars($row["COLUMN_NAME"]) . '</label>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No data found.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>
                </div>

                <br>
                <h4>Format <span>*</span></h4>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="format" value="csv" id="format_csv" checked>
                    <label class="form-check-label" for="format_csv">CSV</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="format" value="txt" id="format_txt">
                    <label class="form-check-label" for="format_txt">TXT</label>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>

                    <div class="btn-block text-center">
                        <button type="submit" class="btn btn-secondary">Export</button>
                    </div>


                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <script>
                    document.getElementById('addBDCSV').addEventListener('submit', function(event) {
                        if (!confirm('Are you sure you want to export the data in this format?')) {
                            event.preventDefault(); // Cancelar el envío si el usuario no confirma
                        }
                    });

                    // Función para seleccionar/deseleccionar todos los checkboxes
                    document.getElementById('selectAll').addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('input[name="columns[]"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });

                    // Actualizar el estado del checkbox "Select All" cuando se cambian los checkboxes individuales
                    document.querySelectorAll('input[name="columns[]"]').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const allCheckboxes = document.querySelectorAll('input[name="columns[]"]');
                            const selectAllCheckbox = document.getElementById('selectAll');
                            selectAllCheckbox.checked = Array.from(allCheckboxes).every(cb => cb.checked);
                        });
                    });
                </script>
            </form>
        </div>
        <!--Formulario para agregar registros en la base de datos en los campos de seleccion-->
        <div class="col-md-6 my-2">
            <form action="add_Logs.php" style="display: none" method="POST" id="addLgColumn" class="shadow border border-secondary">
                <h1 class="text-center">Add Logs in the Column</h1>
                <h4>Select Columns <span>*</span></h4>
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <?php
                try {

                    $sql = "SELECT TABLE_NAME, COLUMN_NAME 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                    AND COLUMN_NAME NOT IN ('assetname', 'serial_number', 'warranty_enddate', 'expired', 'new_laptop', 'fk_id', 'fk_assetname',
                    'last_user', 'job_title', 'cedula', 'HeadSet', 'fecha_salida') 
                    ORDER BY TABLE_NAME, ORDINAL_POSITION";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        echo '<div class="row">';
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<div class="col-md-4 col-sm-6 mb-2">';
                            echo '<div class="form-check">';
                            echo '<div class="form-check">';
                            echo '<input class="form-check-input" type="checkbox" name="columns[]" value="' . htmlspecialchars($row["TABLE_NAME"]) . '|' . htmlspecialchars($row["COLUMN_NAME"]) . '" id="log_' . htmlspecialchars($row["COLUMN_NAME"]) . '">';
                            echo '<label class="form-check-label ms-2" for="log_' . htmlspecialchars($row["COLUMN_NAME"]) . '">' .
                                htmlspecialchars($row["COLUMN_NAME"]) . ' (' . htmlspecialchars($row["TABLE_NAME"]) . ')</label>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No data found.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
                <br>
                <h4>Name Log <span>*</span></h4>
                <input class="input-group mb-3" type="text" id="Log_name" name="Log_name" required
                    pattern="[a-zA-Z0-9_ ]+" title="Only letters, numbers, underscores and spaces allowed" />
                <br>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>

                    <button id="AddLogfield" type="submit" class="btn btn-secondary mb-3"
                        onclick="return confirm('Are you sure you want to add this log?')">Add Log</button>

                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
        <!--Formulario para agregar campos dinamicos en todos los formularios-->
        <div class="col-md-6">
            <form action="add_fieldTDO.php" method="post" style="display: none;" id="addFormTodo" class="shadow border border-secondary p-3">
                <h1 class="text-center">Add Fields to All Forms</h1>
                <h4>Select Columns <span>*</span></h4>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="row">
                    <?php
                    try {
                        // Get columns from both tables
                        $prohibited_fields = ['assetname', 'serial_number', 'purchase_country', 'warranty_enddate', 'expired', 'new_laptop', 'fk_id', 'fk_assetname', 'user_status', 'last_user', 'job_title', 'status_change', 'cedula', 'Carnet', 'LLave', 'Tipo_ID'];

                        $sql = "SELECT COLUMN_NAME, DATA_TYPE 
                                   FROM INFORMATION_SCHEMA.COLUMNS 
                                   WHERE TABLE_SCHEMA = 'garantias' 
                                   AND TABLE_NAME IN ('equipos', 'usuarios_equipos')
                                   ORDER BY TABLE_NAME, COLUMN_NAME";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        echo '<div class="col-md-12 mb-3">';
                        echo '<label class="form-label">Available Fields</label>';
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-sm table-bordered">';
                        echo '<thead><tr><th>Field</th><th>Data Type</th><th><input type="checkbox" id="selectAll" class="form-check-input"> Select All</th></tr></thead>';
                        echo '<tbody>';

                        foreach ($columns as $column) {
                            if (!in_array($column['COLUMN_NAME'], $prohibited_fields)) {
                                $fieldName = $column['COLUMN_NAME'];
                                $displayName = isset($fieldLabels[$fieldName]) ? $fieldLabels[$fieldName] : $fieldName;

                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($displayName) . '</td>';
                                echo '<td>' . htmlspecialchars($column['DATA_TYPE']) . '</td>';
                                echo '<td><input class="form-check-input field-checkbox" type="checkbox" name="columns[]" value="' . htmlspecialchars($column['COLUMN_NAME']) . '"></td>';
                                echo '</tr>';
                            }
                        }

                        echo '</tbody></table></div></div>';
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">Error loading fields: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>

                <br>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>

                    <div class="btn-block text-center">
                        <button type="submit" class="btn btn-secondary">Add Field</button>
                    </div>

                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
        <!-- Modal para mostrar las alertas -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <?php
            include_once '../Controller/Notify.php'
            ?>
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h2 class="modal-title fs-4">
                            <i class="bi bi-bell-fill me-2"></i>
                            Notification History
                        </h2>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="notificationFilter" class="form-control mb-3" placeholder="Filter notifications...">
                        <div class="notification-container">
                            <?php if (!empty($alerts)): ?>
                                <div class="notification-list">
                                    <?= implode('', array_map(function ($alert) {
                                        return str_replace(
                                            'class=\'p-3 mb-2 bg-light shadow-sm\'',
                                            'class=\'notification-item p-3 mb-2 bg-white rounded shadow-sm border-start border-4 border-primary\'',
                                            $alert
                                        );
                                    }, $alerts)) ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <i class="bi bi-inbox-fill fs-1 text-muted"></i>
                                    <p class="mt-3 text-muted">No notifications recorded.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="clearLogs()">
                            <i class="bi bi-trash-fill me-1"></i>
                            Clean History
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>
                            Close
                        </button>
                    </div>
                </div>
            </div>
            <script>
                // Función para alternar el modo oscuro
                document.getElementById('darkModeToggle').addEventListener('click', function() {
                    document.body.classList.toggle('dark-mode');

                    // Guardar preferencia en localStorage
                    if (document.body.classList.contains('dark-mode')) {
                        localStorage.setItem('darkMode', 'enabled');
                        this.innerHTML = '<i class="fa-solid fa-sun"></i>';
                    } else {
                        localStorage.setItem('darkMode', 'disabled');
                        this.innerHTML = '<i class="fa-solid fa-moon"></i>';
                    }
                });

                // Cargar preferencia al iniciar
                if (localStorage.getItem('darkMode') === 'enabled') {
                    document.body.classList.add('dark-mode');
                    document.getElementById('darkModeToggle').innerHTML = '<i class="fa-solid fa-sun"></i>';
                }

                // Función para los botones de gráficos
                document.querySelectorAll('.chart-switch').forEach(button => {
                    button.addEventListener('click', function() {
                        // Remover clase active de todos los botones
                        document.querySelectorAll('.chart-switch').forEach(btn => {
                            btn.classList.remove('active');
                        });

                        // Añadir clase active al botón clickeado
                        this.classList.add('active');

                        // Ocultar todos los gráficos
                        document.querySelectorAll('.chart-canvas').forEach(chart => {
                            chart.classList.add('d-none');
                        });

                        // Mostrar el gráfico seleccionado
                        const chartId = this.getAttribute('data-chart');
                        document.getElementById(chartId).classList.remove('d-none');
                    });
                });
            </script>
            <script>
                // Filter notifications
                $('#notificationFilter').on('keyup', function() {
                    const filterValue = $(this).val().toLowerCase();
                    $('.modal-body div').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(filterValue) > -1);
                    });
                });
            </script>
        </div>

        <script src="../View/Js/ModalReport.js"></script>
        <div class="modal fade" id="repeatedSerialsModal" tabindex="-1" aria-labelledby="repeatedSerialsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h2 class="modal-title" id="repeatedSerialsModalLabel"><i
                                class="fas fa-exclamation-triangle"></i>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                                class="fas fa-times"></i>
                            No</button>
                        <button type="button" class="btn btn-danger" id="deleteSerialsButton"><i
                                class="fas fa-trash-alt"></i> Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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
                            const repeatedSerialsModal = new bootstrap.Modal(document.getElementById('repeatedSerialsModal'));
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
        <script>
            // Hover dropdown functionality
            document.addEventListener('DOMContentLoaded', function() {
                const dropdowns = document.querySelectorAll('.dropdown');

                dropdowns.forEach(dropdown => {
                    // Show on hover
                    dropdown.addEventListener('mouseenter', function() {
                        const menu = this.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.display = 'block';
                        }
                    });

                    // Hide when mouse leaves
                    dropdown.addEventListener('mouseleave', function() {
                        const menu = this.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.display = 'none';
                        }
                    });
                });

                // Keep dropdown open when hovering over menu
                const dropdownMenus = document.querySelectorAll('.dropdown-menu');
                dropdownMenus.forEach(menu => {
                    menu.addEventListener('mouseenter', function() {
                        this.style.display = 'block';
                    });

                    menu.addEventListener('mouseleave', function() {
                        this.style.display = 'none';
                    });
                });
            });
        </script>

        <!--Navegabilidad-->
        <script>
            // Mapeo de formularios en orden
            const formOrder = [
                'FormAddForm',
                'RemoveForm',
                'FormAddTable',
                'RemoveFieldsTable',
                'addBDCSV',
                'addFormTodo',
                'addLgColumn'
            ];

            function navigateTo(direction) {
                const currentFormId = document.querySelector('form:not([style*="none"])')?.id;
                if (!currentFormId) return;

                const currentIndex = formOrder.indexOf(currentFormId);
                if (currentIndex === -1) return;

                let nextIndex;
                if (direction === 'next') {
                    nextIndex = (currentIndex + 1) % formOrder.length;
                } else {
                    nextIndex = (currentIndex - 1 + formOrder.length) % formOrder.length;
                }

                // Ocultar todos los formularios
                formOrder.forEach(id => {
                    document.getElementById(id).style.display = 'none';
                });

                // Mostrar el formulario seleccionado
                const nextForm = document.getElementById(formOrder[nextIndex]);
                if (nextForm) {
                    nextForm.style.display = 'block';
                    // Mover al principio del contenedor
                    const firstContainer = document.getElementById('tool');
                    if (firstContainer) {
                        firstContainer.insertAdjacentElement('beforebegin', nextForm);
                    }

                    // Scroll suave al formulario
                    nextForm.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        </script>

    </div>
    </div>
    <!-- Load jQuery first -->
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <!-- Then load Bootstrap JS -->
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <!-- Then load other scripts -->
    <script src="../Configuration/JQuery/Chart.js"></script>
    <!-- Remove the duplicate jQuery loading at the bottom -->
    <script src="../View/Js/confirm_home_redirect.js"></script>
</body>
<?php include '../View/Fragments/footer.php'; ?>

</html>
<script>
    // Función para manejar la selección de todos los campos
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const fieldCheckboxes = document.getElementsByClassName('field-checkbox');

        selectAllCheckbox.addEventListener('change', function() {
            Array.from(fieldCheckboxes).forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Actualizar el estado del checkbox principal cuando se cambian los individuales
        Array.from(fieldCheckboxes).forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(fieldCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            });
        });
    });
</script>