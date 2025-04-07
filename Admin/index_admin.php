<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión
}

$tiempo_inactivo = 5000; // Tiempo de expiración de la sesión (en segundos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    // Establecer una cookie con el mensaje de error por sesión expirada
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
    <style>
       /* Estilos base */
:root {
  --primary-color: #0056b3;
  --secondary-color: #6c757d;
  --accent-color: #0ea021;
  --light-bg: #f8f9fa;
  --dark-bg: #343a40;
  --border-radius: 8px;
  --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  --transition: all 0.3s ease;
}

body {
  background-color: #f5f5f5;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Estilizar el dashboard */
.dashboard-container {
  margin-top: 2rem;
}

.card {
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  transition: var(--transition);
  border: none;
  margin-bottom: 1.5rem;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.card-header {
  background-color: var(--primary-color);
  color: white;
  border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
  padding: 1rem;
}

/* Estilos para formularios */
form {
  border-radius: var(--border-radius) !important;
  padding: 1.5rem !important;
  background-color: white;
  transition: var(--transition);
}

form h1 {
  color: var(--primary-color);
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

form h4 {
  font-size: 1.1rem;
  color: var(--dark-bg);
  margin-top: 1rem;
}

form h4 span {
  color: #dc3545;
}

/* Mejorar los controles del formulario */
input[type="text"], select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
  transition: var(--transition);
}

input[type="text"]:focus, select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Estilo personalizado para checkboxes */
.checkbox-btn {
  display: block;
  position: relative;
  padding-left: 35px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 14px;
  user-select: none;
}

.checkbox-btn input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkbox-btn label {
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #555;
}

.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 25px;
  width: 25px;
  border: 2.5px solid #adb5bd;
  border-radius: 4px;
  transition: .2s linear;
  background-color: white;
}

.checkbox-btn input:checked ~ .checkmark {
  background-color: transparent;
}

.checkmark:after {
  content: "";
  position: absolute;
  visibility: hidden;
  opacity: 0;
  left: 50%;
  top: 40%;
  width: 10px;
  height: 14px;
  border: 2px solid var(--accent-color);
  filter: drop-shadow(0px 0px 10px var(--accent-color));
  border-width: 0 2.5px 2.5px 0;
  transition: .2s linear;
  transform: translate(-50%, -50%) rotate(-90deg) scale(0.2);
}

.checkbox-btn input:checked ~ .checkmark:after {
  visibility: visible;
  opacity: 1;
  transform: translate(-50%, -50%) rotate(0deg) scale(1);
  animation: pulse 1s ease-in;
}

.checkbox-btn input:checked ~ .checkmark {
  transform: rotate(45deg);
  border: none;
}

@keyframes pulse {
  0%, 100% {
    transform: translate(-50%, -50%) rotate(0deg) scale(1);
  }
  50% {
    transform: translate(-50%, -50%) rotate(0deg) scale(1.6);
  }
}

/* Estilos para botones */
.btn {
  border-radius: 5px;
  padding: 0.5rem 1.2rem;
  font-weight: 500;
  transition: var(--transition);
}

.btn-secondary {
  background-color: var(--secondary-color);
  border-color: var(--secondary-color);
}

.btn-secondary:hover {
  background-color: #5a6268;
  border-color: #545b62;
  transform: translateY(-2px);
}

.btn-primary:hover {
  background-color:rgb(58, 15, 211);
  border-color: #545b62;
  transform: translateY(-2px);
}

.btn-info:hover {
    background-color:rgb(136, 181, 223);
    border-color: #545b62;
    transform: translateY(-2px);
}

.btn-dark:hover {
    background-color: #333;
    border-color: #545b62;
    transform: translateY(-2px);
}

.btn-danger:hover {
  transform: translateY(-2px);
}

/* Navbar mejorado */
.navbar {
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 0.8rem 1rem;
  margin-bottom: 1.5rem !important;
}

.navbar-brand {
  font-weight: 600;
}

.nav-link {
  font-weight: 500;
  color: #495057 !important;
  padding: 0.5rem 1rem !important;
  transition: var(--transition);
}

.nav-link:hover, .nav-link.active {
  color: var(--primary-color) !important;
}

.chart-switch {
  color: #6c757d;
  background: none;
  border: none;
  margin-right: 5px;
  padding: 5px 10px;
  border-radius: 20px;
  transition: all 0.3s;
}

.chart-switch:hover, .chart-switch.active {
  background-color: var(--primary-color);
  color: white;
}

/* Estilo para gráficos */
#chartContainer {
  background-color: white;
  padding: 20px;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  margin-bottom: 2rem;
}

.chart-canvas {
  min-height: 400px;
}

/* Modal personalizado */
.modal-header {
  border-bottom: none;
  border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.modal-content {
  border-radius: var(--border-radius);
  border: none;
}

.notification-item {
  transition: var(--transition);
}

.notification-item:hover {
  transform: translateX(5px);
}

/* Alertas personalizadas */
.alert {
  border-radius: var(--border-radius);
}

/* Mejorar responsividad */
@media (max-width: 768px) {
  .dashboard-item {
    flex: 1 1 100%;
  }
  
  .navbar .container-fluid {
    justify-content: space-between;
  }
  
  #chartContainer {
    min-height: 300px;
  }
}

/* Modo oscuro */
.dark-mode {
  background-color: #222;
  color: #f5f5f5;
}

.dark-mode .card, 
.dark-mode form {
  background-color: #333;
  color: #f5f5f5;
}

.dark-mode .form-check-label {
  color: #f5f5f5;
}

.dark-mode .navbar {
  background-color: #333 !important;
}

.dark-mode .nav-link {
  color: #f5f5f5 !important;
}

.dark-mode input, .dark-mode select {
  background-color: #444;
  color: #f5f5f5;
  border-color: #555;
}

.dark-mode .checkmark {
  border-color: #888;
}

/* Hover dropdown styles */
.navbar .dropdown:hover .dropdown-menu {
    display: block;
    margin-top: 0; /* remove the gap so it doesn't close */
}

.dropdown-menu {
    margin-top: 0; /* remove the gap so it doesn't close */
    transition: all 0.3s ease;
    display: none; /* hidden by default, shown on hover */
}

/* Keep dropdown open when hovering over menu */
.dropdown-menu:hover {
    display: block;
}
        
/* Añade esto al bloque de estilos */
.nav-arrow {
    min-width: 120px;
    margin: 0 10px;
    transition: all 0.3s ease;
}

.nav-arrow:hover {
    transform: translateY(-2px);
}

/* Mejor espaciado para los formularios */
form {
    margin-bottom: 2rem;
    padding: 1.5rem !important;
}

/* Ajustar el contenedor principal */
.dashboard-container {
    max-width: 95%;
    margin: 0 auto;
}
    </style>

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
            <script src="../View/Js/diagrams.js"> </script>
            <div class="col-md-5 ms-2">
                <div id="tool" style="display: none;"></div>
            </div>

            <div class="col-md-6" id="first_container">
                <div id="chartContainer" style="position: relative; min-height: 380px; display: flex;">
                    <?php
                    // Obtener todos los datos en una sola consulta
                    try {
                        include '../Configuration/Connection.php';

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
                <form action="add_fields.php" id="FormAddForm" style="display: block;" method="POST" class="shadow border border-secondary">
                    <h1 class="text-center">Adding fields to the database</h1>
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
                    <h4>Name of Data <span>*</span></h4>
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
                    </script>
                    <h4>Select Table <span>*</span></h4>
                    <select name="table_name" required>
                        <option value="">Select Table</option>
                        <option value="usuarios_equipos">Users</option>
                        <option value="equipos">Assets</option>
                    </select>
                    <div class="d-flex justify-content-between align-items-center mt-3">
    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
        <i class="fas fa-arrow-left"></i> Previous
    </button>
    
    <div class="btn-block">
                        <button id="Addfield" type="submit" onclick="return validateForm()"
                            class="btn btn-secondary">Add Field</button>
                    </div>
    
    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
        Next <i class="fas fa-arrow-right"></i>
    </button>
</div>
                </form>
                <br>
            </div>
            <!--Formulario pra eliminar columans de la base de datos-->
            <div class="col-md-6">
                <form action="remove_fields.php" id="RemoveForm" style="display: none;" method="post" class="shadow border border-secondary">
                    <h1 class="text-center">Remove Fields</h1>
                    <h4>Name Field <span>*</span></h4>
                    <?php
try {
    include '../Configuration/Connection.php';
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
            echo '<label class="checkbox-btn">';
            echo '<input type="checkbox" name="addmfields[]" value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '">';
            echo '<span class="checkmark"></span>';
            echo '<label>' . htmlspecialchars($row["Field"]) . '</label>';
            echo '</label>';
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
                        <button id="Removefield" type="submit" class="btn btn-secondary">Remove Field</button>
                    </div>
    
    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
        Next <i class="fas fa-arrow-right"></i>
    </button>
</div>
                  
                        <div id="networkInfo" class="alert alert-warning mt-2" style="display: none;">
                            <strong>Warning:</strong> This action cannot be undone. Please be sure before removing the field.
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
            <!--Formulario para agregar campos dinamicos en la tabla principal de index-->
            <div class="col-md-6">
                <form action="add_fieldsTable.php" method="post" id="FormAddTable" style="display: none;"
                    class="shadow border border-secondary">
                    <h1 class="text-center">Add Fields to the Main Table</h1>
                    <h4>Name Field <span>*</span></h4>
                    <?php
try {
    include '../Configuration/Connection.php';
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
            echo '<label class="checkbox-btn">';
            echo '<input type="checkbox" name="addmfields[]" value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '">';
            echo '<span class="checkmark"></span>';
            echo '<label>' . htmlspecialchars($row["Field"]) . '</label>';
            echo '</label>';
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
                        include '../Configuration/Connection.php';
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
                                echo '<label class="checkbox-btn">';
                                echo '<input type="checkbox" name="addmfields[]" value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '">';
                                echo '<span class="checkmark"></span>';
                                echo '<label>' . htmlspecialchars($row["Field"]) . '</label>';
                                echo '</label>';
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

                    <div class="row">
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
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<div class="col-md-4 col-sm-6 mb-2">';
                                    echo '<div class="form-check">';
                                    echo '<input class="form-check-input" type="checkbox" name="columns[]" value="' . htmlspecialchars($row["COLUMN_NAME"]) . '" id="col_' . htmlspecialchars($row["COLUMN_NAME"]) . '">';
                                    echo '<label class="form-check-label" for="col_' . htmlspecialchars($row["COLUMN_NAME"]) . '">' . htmlspecialchars($row["COLUMN_NAME"]) . '</label>';
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
                        include '../Configuration/Connection.php';
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
                                echo '<input class="form-check-input" type="checkbox" name="columns[]" value="' . htmlspecialchars($row["TABLE_NAME"]) . '|' . htmlspecialchars($row["COLUMN_NAME"]) . '" id="log_' . htmlspecialchars($row["COLUMN_NAME"]) . '">';
                                echo '<label class="form-check-label" for="log_' . htmlspecialchars($row["COLUMN_NAME"]) . '">' .
                                    htmlspecialchars($row["COLUMN_NAME"]) . ' (' . htmlspecialchars($row["TABLE_NAME"]) . ')</label>';
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
                            echo '<thead><tr><th>Field</th><th>Data Type</th><th>Select</th></tr></thead>';
                            echo '<tbody>';
                            
                            foreach ($columns as $column) {
                                if (!in_array($column['COLUMN_NAME'], $prohibited_fields)) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($column['COLUMN_NAME']) . '</td>';
                                    echo '<td>' . htmlspecialchars($column['DATA_TYPE']) . '</td>';
                                    echo '<td><input type="checkbox" name="columns[]" value="' . htmlspecialchars($column['COLUMN_NAME']) . '"></td>';
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
                        <div class="modal-header bg-secondary text-white">
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
                                                'class=\'notification-item p-3 mb-2 bg-white rounded shadow-sm border-start border-4 border-secondary\'',
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
        if(document.body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
            this.innerHTML = '<i class="fa-solid fa-sun"></i>';
        } else {
            localStorage.setItem('darkMode', 'disabled');
            this.innerHTML = '<i class="fa-solid fa-moon"></i>';
        }
    });
    
    // Cargar preferencia al iniciar
    if(localStorage.getItem('darkMode') === 'enabled') {
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
            nextForm.scrollIntoView({ behavior: 'smooth' });
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