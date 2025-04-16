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
    <title>Asset Management System</title>
    <link rel="shortcut icon" href="./Configuration/logo.ico" type="image/x-icon">
    <!-- Carga de jQuery primero para evitar errores de dependencia -->
    <script src="./Configuration/JQuery/jquery-3.7.1.js"></script>
    <!-- Consolidación de CSS -->
    <link href="./Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="./Configuration/DataTables/datatables.min.css">
    <link rel="stylesheet" href="./View/Css/dark-mode.css">
    <link rel="stylesheet" href="./View/Css/Datatable.css">
    <link rel="stylesheet" href="./View/Css/button-styles.css">
    <link rel="stylesheet" href="./Configuration/JQuery/sweetalert2.min.css">
    <!-- Carga diferida de scripts no críticos -->
    <script src="./Configuration/JQuery/sweetalert2.all.min.js" defer></script>
    <link rel="stylesheet" href="./View/Css/navbar.css">
    <link rel="stylesheet" href="./View/Css/search-form.css">
</head>

<body>
    <div class="row align-items-center justify-content-center mb-3">
        <!-- script para la validacion de estado de garantia -->
        <script src="./View/Js/warranty-check.js"></script>

        <div class="col-md-3 mb-2 my-5">
            <!--archivo para traer los datos en caso de error o alerta y confirmacion de subida de datos-->
            <?php
            include_once "./Controller/validation_message.php";
            ?>
            <script src="../Configuration/JQuery/qrcode.min.js"></script>
            <script src="./View/Js/qrcode.js"></script>
            <script src="./View/Js/animations.js"></script>
            <br>
            <form action="./Controller/Importar_BD.php" method="POST" enctype="multipart/form-data" class="form bg-light">
                <span class="form-title">Upload your file
                    <link rel="stylesheet" href="./Configuration/JQuery/fontawesome.min.css">
                    <div id="div1" class="fa"></div>


                </span>
                <p class="form-paragraph"> File should be CSV or TXT</p>
                <label for="file-input" class="drop-container">
                    <span class="drop-title">Drop file here</span>
                    or
                    <input type="file" name="Proyeccion_garan" class="form-control form-control-sm bg-light" accept=".csv, .txt" required id="file-input">
                </label>
                <button type="submit" class="upload-button form-control form-control-sm">Upload File</button>
            </form>
        </div>
        <?php
        include_once './Configuration/Connection.php';

        try {
            // Consulta optimizada para contar el número de registros
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM vista_equipos_usuarios");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            // Total de registros
            $Totalclient = $resultado['total'];
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

                        </div>
                        <div class="col-md-12 text-center mb-4 search-container">
                            <h3 class="search-title"><i class="bi bi-filter"></i> Filter Assets by Specific Criteria</h3>
                            <p class="search-description">Refine your search by selecting one or multiple criteria and quickly find asset information.</p>
                        </div>
                        <!-- Barra Busqueda -->
                        <nav id="Navitem"
                            class="navbar navbar-expand-lg navbar-light bg-light fixed-top rounded shadow-sm">
                            <div class="container-fluid">

                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>"
                                    class="navbar-brand d-flex align-items-center">
                                    <img src="./Configuration/3shape-logo.png" alt="Logo" width="150" height="31">
                                </a>

                                <!-- Botón hamburguesa para móviles -->
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarCollapse" aria-controls="navbarCollapse"
                                    aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>

                                <!-- Contenido colapsable con múltiples funciones -->
                                <div class="collapse navbar-collapse" id="navbarCollapse">
                                    <div class="navbar-nav">
                                        <!-- Reporte PDF que contiene la información de todos los activos registrados -->
                                        <a id="generatePDF" class="nav-link me-2" data-url="./Model/GeneralReport.php" target="_blank" title="Generate General PDF">
                                            <i class="far fa-file-pdf" style="color:red;"></i> Generate General PDF
                                        </a>
                                        <!-- Documento PDF con las actas de salida de la empresa de los empleados y activos -->
                                        <a id="generateActaSalida" class="nav-link me-2" data-url="./Model/Acta_salida.php" title="Generate Departure Certificate">
                                            <i class="fas fa-file-export"></i> Generate Departure Certificate
                                        </a>
                                        <!-- Documento PDF con las actas de ingreso de la empresa de los empleados y activos que se les entregan -->
                                        <a id="generateActaEntrada" class="nav-link me-2" data-url="./Model/Acta_entrada.php" title="Generate Entry Certificate">
                                            <i class="far fa-file-alt"></i> Generate Entry Certificate
                                        </a>
                                        <!-- Formulario para cambiar de aquipo a un usuario  -->
                                        <a href="./View/Int_entrada.php" class="nav-link me-2" title="Change Asset">
                                            <i class="bi bi-arrow-repeat me-1"></i> Change Asset
                                        </a>
                                        <!-- Formulario para asignar una fecha de salida de un empleado y un activo-->
                                        <a href="./View/Int_salida.php" class="nav-link me-2" title="Output Asset">
                                            <i class="bi bi-box-arrow-up me-1"></i> Output Asset
                                        </a>
                                        <div class="notification">
                                            <!--Botón de Campana para mostrar modal de notificaciones -->
                                            <li class="nav-item ms-auto bell-container">
                                                <div class="bell-wrapper">
                                                    <a type="button" class="btn btn-outline-warning bell-button">
                                                        <i class="fa-solid fa-bell" style="color:black"></i>
                                                        <span class="notification-badge">0</span>
                                                    </a>
                                                </div>
                                                <script src="./View/Js/ModalReport.js"></script>
                                            </li>
                                        </div>
                                    </div>
                        </nav>
                        <!-- Formulario de Consulta -->
                        <!-- Contenedor para las 3 categorías alineadas -->
                        <div id="Barra_Busqueda" class="d-flex row align-items-center justify-content-center">
                            <div class="col-md-12">
                                <div class="row g-2">
                                    <!-- Categoría 1: Información del Activo -->
                                    <div class="col-md-4 mb-3">
                                        <div class="card shadow-sm">
                                            <div class="card-header bg-secondary text-white" data-bs-toggle="collapse" href="#collapseAssetInfo" role="button" aria-expanded="false" aria-controls="collapseAssetInfo">
                                                <i class="bi bi-laptop me-2"></i> Asset Information
                                                <i class="bi bi-chevron-down float-end"></i>
                                            </div>
                                            <div class="collapse" id="collapseAssetInfo">
                                                <div class="card-body">
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                                            <input type="text" name="search_assetname" placeholder="Asset Name"
                                                                class="form-control shadow-sm bg-light"
                                                                oninput="this.value = this.value.toUpperCase()" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                                            <input type="text" id="search_serial" name="search_serial"
                                                                placeholder="Serial Number" class="form-control shadow-sm bg-light"
                                                                autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                                            <select id="search_purchase_country" name="search_purchase_country[]"
                                                                class="shadow-sm form-control bg-light">
                                                                <option value="0">Select a country</option>
                                                                <?php
                                                                // Reutilización de la conexión existente
                                                                try {
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
                                                                } catch (PDOException $e) {
                                                                    echo '<option value="">Error loading options</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Categoría 2: Información del Usuario -->
                                    <div class="col-md-4 mb-3">
                                        <div class="card shadow-sm">
                                            <div class="card-header bg-primary text-white" data-bs-toggle="collapse" href="#collapseUserInfo" role="button" aria-expanded="false" aria-controls="collapseUserInfo">
                                                <i class="bi bi-person me-2"></i> User Information
                                                <i class="bi bi-chevron-down float-end"></i>
                                            </div>
                                            <div class="collapse" id="collapseUserInfo">
                                                <div class="card-body">
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="far fa-id-card"></i></span>
                                                            <input type="number" id="search_cedula" name="search_cedula" placeholder="ID"
                                                                class="form-control shadow-sm bg-light" autocomplete="off" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                                            <input type="text" id="search_user" name="search_user" placeholder="Last User"
                                                                class="form-control shadow-sm bg-light" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                                            <input type="text" id="search_job_title" name="search_job_title" placeholder="Job Title"
                                                                class="form-control shadow-sm bg-light" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                                            <select id="search_user_status" name="search_user_status[]"
                                                                class="shadow-sm form-control bg-light">
                                                                <option value="0">Select an option</option>
                                                                <?php
                                                                // Reutilización de la conexión existente
                                                                try {
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
                                                                } catch (PDOException $e) {
                                                                    echo '<option value="">Error loading options</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Categoría 3: Estado y Garantía -->
                                    <div class="col-md-4 mb-3">
                                        <div class="card shadow-sm">
                                            <div class="card-header bg-warning text-dark" data-bs-toggle="collapse" href="#collapseStatusWarranty" role="button" aria-expanded="false" aria-controls="collapseStatusWarranty">
                                                <i class="bi bi-shield-check me-2"></i> Status & Warranty
                                                <i class="bi bi-chevron-down float-end"></i>
                                            </div>
                                            <div class="collapse" id="collapseStatusWarranty">
                                                <div class="card-body">
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                                            <input type="text" name="search_status_change" placeholder="Status Change"
                                                                class="shadow-sm form-control bg-light">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                            <input type="text" id="sgarantia" name="search_fecha_inicio"
                                                                placeholder="Start date (MM/DD/YYYY)"
                                                                class="shadow-sm form-control bg-light"
                                                                pattern="(0[1-9]|1[0-2])/(0[1-9]|[12]\d|3[01])/\d{4}"
                                                                title="Please enter date in MM/DD/YYYY format">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                                            <input type="text" id="garantia" name="search_fecha_fin"
                                                                placeholder="End Date (MM/DD/YYYY)"
                                                                class="shadow-sm form-control bg-light"
                                                                pattern="(0[1-9]|1[0-2])/(0[1-9]|[12]\d|3[01])/\d{4}"
                                                                title="Please enter date in MM/DD/YYYY format">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                                                            <select name="search_expired" class="shadow-sm form-control bg-light">
                                                                <option value="">Select Expired Status</option>
                                                                <option value="1">Expired</option>
                                                                <option value="0">Not Expired</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal para mostrar las alertas -->
                        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
                            <?php
                            // Optimización: Carga de notificaciones más eficiente
                            $jsonFilePath = './Model/Logs/session_messages.json';
                            $alerts = [];
                            $maxMessages = 8;

                            if (file_exists($jsonFilePath)) {
                                // Uso de caché para el archivo JSON
                                $cacheKey = 'notification_' . filemtime($jsonFilePath);
                                $logData = isset($_SESSION[$cacheKey]) ? $_SESSION[$cacheKey] : null;

                                if (!$logData) {
                                    $logData = json_decode(file_get_contents($jsonFilePath), true);
                                    $_SESSION[$cacheKey] = $logData;
                                }

                                // Procesar todos los tipos de mensajes
                                foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
                                    if (!empty($logData[$type])) {
                                        $alertClass = match ($type) {
                                            'success' => 'success',
                                            'error', 'error_message' => 'danger',
                                            'warnings' => 'warning'
                                        };

                                        $count = 0;
                                        foreach ($logData[$type] as $entry) {
                                            $message = is_array($entry) ? $entry['message'] : $entry;
                                            $timestamp = is_array($entry) ? " ({$entry['timestamp']})" : '';
                                            $alerts[] = "<div class='p-3 mb-2 bg-light shadow-sm'>{$message} - {$timestamp}</div>";
                                            $count++;

                                            // Limit to $maxMessages messages per type
                                            if ($count >= $maxMessages) {
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                            <!--Modal de notificciones -->
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
                        </div>

                        <script>
                            function clearLogs() {
                                if (confirm("Are you sure you want to delete all records?")) {
                                    fetch('./Model/clear_logs.php', {
                                            method: 'POST'
                                        })
                                        .then(response => response.text())
                                        .then(data => {
                                            alert(data); // Mostrar respuesta del servidor
                                            location.reload(); // Recargar la página para ver cambios
                                        })
                                        .catch(error => console.error('Error:', error));
                                }
                            }
                        </script>

                        <div class="my-2 mb-2 d-flex justify-content-left">
                            <button type="submit" class="shadow btn btn-light border-success me-1" id="search_btn">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <button type="reset" class="shadow btn btn-light border-danger me-1" id="reset_btn">Clear
                                <i class="bi bi-eraser"></i>
                            </button>
                            <button type="button" class="shadow btn btn-light border-primary" id="refresh_btn">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>

                        <script>
                            // Botón de Clear
                            document.getElementById('reset_btn').addEventListener('click', function() {
                                const textInputs = document.querySelectorAll('input[type="text"]');
                                const numberInputs = document.querySelectorAll('input[type="number"]');
                                const selectElements = document.querySelectorAll('select');

                                textInputs.forEach(input => input.value = '');
                                numberInputs.forEach(input => input.value = '');
                                selectElements.forEach(select => select.selectedIndex = 0);
                            });

                            // Botón de Refresh
                            document.getElementById('refresh_btn').addEventListener('click', function() {
                                window.location.href = window.location.pathname;
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
            <div id="resultados3" class="text-center"></div>
            <div id="resultados2" class="text-center"></div>
            <div id="resultados" class="text-center"></div>
            <script>
                //Script automatico para la busqueda del ultimo usuario
                document.getElementById('search_user').addEventListener('input', function() {
                    const value = this.value;
                    if (value.length > 0) {
                        this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
                    }
                });
            </script>
            <script>
                //Coment
                //Script para visualizar la contraseña
                // Password visibility toggle function
                document.addEventListener('DOMContentLoaded', function() {
                    // Create toggle button for password visibility
                    const passwordField = document.getElementById('passwordadmin');
                    if (passwordField) {
                        const formGroup = passwordField.parentElement;

                        // Create and insert toggle button
                        const toggleButton = document.createElement('button');
                        toggleButton.type = 'button';
                        toggleButton.className = 'btn btn-outline-secondary position-absolute end-0 top-0 h-100 d-flex align-items-center px-2';
                        toggleButton.style.borderTopLeftRadius = '0';
                        toggleButton.style.borderBottomLeftRadius = '0';
                        toggleButton.innerHTML = '<i class="bi bi-eye"></i>';
                        toggleButton.setAttribute('title', 'Show/Hide Password');

                        // Create a wrapper div for the input with position relative
                        const inputWrapper = document.createElement('div');
                        inputWrapper.className = 'position-relative';

                        // Move the password input into the wrapper
                        passwordField.parentNode.insertBefore(inputWrapper, passwordField);
                        inputWrapper.appendChild(passwordField);
                        inputWrapper.appendChild(toggleButton);

                        // Add event listener for the toggle button
                        toggleButton.addEventListener('click', function() {
                            if (passwordField.type === 'password') {
                                passwordField.type = 'text';
                                toggleButton.innerHTML = '<i class="bi bi-eye-slash"></i>';
                            } else {
                                passwordField.type = 'password';
                                toggleButton.innerHTML = '<i class="bi bi-eye"></i>';
                            }
                        });
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
    </form>
    <div class="table-responsive my-5">
        <!--Boton para registrar un nuevo equipo-->

        <!--Tabla Principal donde se muestran los datos en este caso los activos de la empresa-->
        <div class="table-container">
            <?php
            $selectedColumns = isset($_SESSION['selected_fields']) ? $_SESSION['selected_fields'] : [];
            ?>
            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <a href="./View/Int_Registro_equipo.php" class="shadow btn btn-secondary "><i class="fas fa-plus"></i>
                    Register new device</a>
                <button id="deleteAllButton" type="button" class="shadow btn btn-danger ">
                    <i class="fas fa-trash-alt"></i>
                    Delete All Logs</button>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <!-- Modal Admin -->
                    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="adminModalLabel">Admin Tools</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="dynamicForms">
                                        <!-- Los formularios se cargarán aquí dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
                        <button class="shadow btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;" id="adminButton">
                            <i class="fas fa-plus"></i>
                        </button>
                        <script src="./View/Js/AdminModal.js"></script>
                    </div>
                <?php endif; ?>
                <!-- Scripts para el modal de administración -->

                <script>
                    document.getElementById('deleteAllButton').addEventListener('click', function() {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This action will delete all logs permanently!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6d6d6d',
                            confirmButtonText: 'Yes, delete all!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = './Controller/delete_regist.php';
                            }
                        });
                    });
                </script>

            </div>
            <style>
                .sync-icon {
                    transition: transform 0.3s ease;
                }

                .btn:hover .sync-icon {
                    transform: rotate(30deg);
                }
            </style>
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
                        <th title="Update" class="text-center"><i class="fas fa-sync-alt"></i></th>
                        <th title="Status And Observations" class="text-center"><i class="far fa-check-square"></i></th>
                        <th title="Delete" class="text-center"><i class="fa fa-times"></i></th>
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
                        <button id="btnQR" class="btn btn-secondary my-2"> Option 4: Generate QR </button>
                    </div>
                </div>
                <style>
                    /* Animación de giro para el icono */
                    @keyframes spin {
                        0% {
                            transform: rotate(0deg);
                        }

                        100% {
                            transform: rotate(360deg);
                        }
                    }

                    /* Aplica la animación al icono cuando se hace hover en el botón */
                    #procesarSeleccionados:hover .fa-cog {
                        animation: spin 2s linear infinite;
                        display: inline-block;
                        /* Necesario para transformaciones */
                    }

                    /* Opcional: Animación cuando se hace clic (como en tu código JS) */
                    .fa-cog.spin {
                        animation: spin 2s linear infinite;
                    }
                </style>
                <script src="./Configuration/JQuery/qrcode.min.js"></script>
                <script src="./View/Js/Select_proccess.js"></script>
            </table>
            <br>
            <br>
            <!-- Placeholder for Check Warranties button -->
            <div id="warranty-check-placeholder" class="container my-3 d-flex justify-content-center">
                <!-- The button will be inserted here by JS -->
            </div>

            <!-- Admin Login Card -->
            <div class="container my-4">
                <div class="card shadow-sm border-primary mx-auto" style="max-width: 400px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-shield me-2"></i>Administrator Access
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="card-text">Press <kbd>Ctrl</kbd> + <kbd>Q</kbd> to login as</p>
                        <a href="#" id="showAdminModal" class="btn btn-outline-primary">
                            <i class="fas fa-lock me-2"></i>Manager
                        </a>
                    </div>
                    <div class="card-footer bg-light text-muted small">
                        <i class="bi bi-info-circle me-1"></i>Restricted access for authorized personnel only
                    </div>
                </div>
            </div>
            </label></center>
            <br>
        </div>
    </div>

    <!-- Modal for Admin Login -->
    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminLoginModalLabel">Admin Login <i class="fas fa-user-alt"></i></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="./Admin/token.php" method="POST">
                        <?php
                        // Generar un token CSRF si no existe
                        if (empty($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        ?>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group mb-3">
                            <input type="password" id="passwordadmin" name="passwordadmin" placeholder="Enter your password" class="form-control shadow-lg border border-secondary" required autocomplete="off">
                            <div class="invalid-feedback">Please enter a valid password.</div>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100 border border-secondary">Login <i class="fas fa-key"></i></button>
                        <div class="text-center mt-2">
                            <a href="./View/Int_forgot_pasword.php" class="text-decoration-none">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts optimizados con carga diferida -->
    <script src="./View/Js/DatatableIndex.js" defer></script>
    <!-- jQuery ya está cargado en el head -->
    <script src="./Configuration/DataTables/datatables.min.js" defer></script>
    <script src="./Configuration/bootstrap/js/bootstrap.min.js" defer></script>
    <script src="./Configuration/JQuery/sweetalert2@11.js" defer></script>

    <!-- Script para el modal de admin -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showAdminModal = document.getElementById('showAdminModal');
            const adminLoginModal = new bootstrap.Modal(document.getElementById('adminLoginModal'));

            // Configurar animaciones para el modal
            const modalElement = document.getElementById('adminLoginModal');
            modalElement.addEventListener('show.bs.modal', function() {
                setTimeout(() => {
                    document.querySelector('.modal-dialog').classList.add('animate__animated', 'animate__fadeInDown');
                }, 50);
            });

            modalElement.addEventListener('hide.bs.modal', function() {
                document.querySelector('.modal-dialog').classList.remove('animate__animated', 'animate__fadeInDown');
            });

            // Manejador de clic para el enlace
            showAdminModal.addEventListener('click', function(event) {
                event.preventDefault();
                adminLoginModal.show();
            });

            // Atajo de teclado (Ctrl + Q)
            document.addEventListener('keydown', function(event) {
                if (event.ctrlKey && (event.key === 'q' || event.key === 'Q')) {
                    event.preventDefault();
                    adminLoginModal.show();
                }
            });
        });
    </script>
    <!-- Añade el JavaScript para manejar la animación de los iconos de flecha -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Añadir listener para cambiar el icono cuando se expande/colapsa
            const collapsibles = document.querySelectorAll('[data-bs-toggle="collapse"]');

            collapsibles.forEach(function(collapsible) {
                collapsible.addEventListener('click', function() {
                    const chevron = this.querySelector('.bi-chevron-down, .bi-chevron-up');

                    if (chevron.classList.contains('bi-chevron-down')) {
                        chevron.classList.remove('bi-chevron-down');
                        chevron.classList.add('bi-chevron-up');
                    } else {
                        chevron.classList.remove('bi-chevron-up');
                        chevron.classList.add('bi-chevron-down');
                    }
                });
            });

            // Añadir efectos de hover para los encabezados
            document.querySelectorAll('.card-header').forEach(header => {
                header.addEventListener('mouseenter', function() {
                    this.style.opacity = '0.9';
                });
                header.addEventListener('mouseleave', function() {
                    this.style.opacity = '1';
                });
            });
        });
    </script>
</body>
<?php include './View/Fragments/footer.php'; ?>

</html>