<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión
}

$tiempo_inactivo = 4600;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    // Si la sesión ha expirado
    setcookie('error_message', 'Sesión expirada. Vuelve a iniciar sesión.', time() + 30, '/');
    header("Location: ../index.php");
    include_once '../Controller/Cerrar_sesion.php';
    exit();
}

// Verificar si el usuario es admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../index.php");
    include_once '../Controller/Cerrar_sesion.php';
    exit("Acceso denegado.");
} 
// Mensajes de Usuario
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']); // Limpia el mensaje después de mostrarlo
}
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']); // Limpia el mensaje después de mostrarlo
}

$_SESSION['last_activity'] = time();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change</title>
    <link rel="shortcut icon" href="../Configuracion/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="../Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/sweetalert2.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/animate.min.css">
    <script src="../Configuration/JQuery/sweetalert2.all.min.js"></script>
    <style>
        body {
            background-color: rgb(239, 232, 233);
        }

        .form-container {
            margin-top: 50px;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="row d-flex justify-content-center align-items-center">
        <div class="row">
            <br>
            <br>
            <br>
            <div class="col-md-12">
                <nav class="navbar d-flex navbar-expand-lg navbar-light bg-light rounded shadow">
                    <div class="container-fluid">
                        <a href="../Admin/index_admin.php" class="navbar-brand d-flex align-items-center">
                            <img src="../Configuration/logo.ico" alt="Logo" width="30" height="30">
                            <small class="ms-2">3 Shape</small>
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav d-flex w-100">
                                <li class="nav-item">
                                    <a class="nav-link active" id="homeindex" href="../index.php">Home</a>
                                    <script>
                                        document.getElementById('homeindex').addEventListener('click', function() {
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
                                                    window.location.href = '../index.php';
                                                }
                                            });
                                        });
                                    </script>
                                </li>

                                <li class="nav-item ms-auto">
                                    <a type="button" class="btn btn-secondary" onclick="showModal()">
                                        <i class="fa-solid fa-bell" style='color:yellow'></i>
                                    </a>
                                    <a type="button" class="btn btn-secondary" href="../View/Int_LogsDelete.php">
                                        <i class="fa-solid fa-info-circle" style="color:aqua"></i>
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
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div id="FORMchange" class="form-container shadow">
                            <!-- Detalles de la pagina -->
                            <div class="text-center mb-4">
                                <h1 id="totalC">Settings</h1> <i class="fas fa-tools" style='font-size:48px;'></i>
                            </div>

                            <div class=" mb-4">
                                <h2 id="totalC">Password Change</h2>
                            </div>

                            <?php
                            if (isset($_SESSION['error'])) {
                                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                                unset($_SESSION['error']);
                            }
                            if (isset($_SESSION['success'])) {
                                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                                unset($_SESSION['success']);
                            }
                            ?>
                            <?php if (!empty($_SESSION['debug_messages'])): ?>
                                <div class="debug-panel">
                                    <?php foreach ($_SESSION['debug_messages'] as $debug): ?>
                                        <div class="debug-item">
                                            <div class="debug-message"><?= htmlspecialchars($debug['message']) ?></div>
                                            <?php if ($debug['data'] !== null): ?>
                                                <div class="debug-data"><?= htmlspecialchars($debug['data']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php unset($_SESSION['debug_messages']); ?>
                                </div>
                            <?php endif; ?>
                            <!-- Formulario para cambiar la contraseña -->
                            <form action="../Controller/proccess_change_psw.php" method="POST" id="changePasswordForm"
                                onsubmit="return validateForm()">
                                <div class="form-group">
                                    <label for="currentPassword">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                        required>
                                </div>
                                <br>
                                <button type="submit" class="btn btn-secondary "><i class="fas fa-key"></i> Change Password</button>
                            </form>
                            <br>
                            <br>
                            <!-- Formulario para cambiar la dirección de red -->
                            <h2 class="mb-4" id="totalC">Network Address Change</h2>
                            <form action="../Controller/ChangeAddrNetwork.php" method="post" id="networkformconifg"
                                onsubmit="return confirmNetworkChange()">
                                <div class="form-group">
                                    <label for="NetworkAddress">New Network Address: </label>
                                    <input type="text" class="form-control" id="NetworkAddress" name="NetworkAddress" required
                                        autocomplete="off" onfocus="showNetworkAddressInfo()">
                                    <div id="networkAddressInfo" class="alert alert-warning mt-2" style="display: none;">
                                        This function does not work at this moment
                                        <br>
                                    </div>
                                    <script>
                                        function showNetworkAddressInfo() {
                                            document.getElementById('networkAddressInfo').style.display = 'block';
                                        }
                                    </script>
                                </div>
                                <br>
                                <div class="my-2">
                                    <button type="submit" class="btn btn-secondary" disabled>Change Network Address</button>
                                    <a href="../Admin/index_admin.php" class="btn btn-danger me-2"><i class="fas fa-arrow-left"></i> Return to Admin Menu</a>


                                    <script>
                                        document.getElementById('NetworkAddress').addEventListener('input', function(e) {
                                            const input = e.target;
                                            const value = input.value;

                                            // Remover caracteres no permitidos (solo permitir números, puntos y ":")
                                            const sanitizedValue = value.replace(/[^0-9a-fA-F:.]/g, '');
                                            input.value = sanitizedValue;

                                            // Limitar la longitud máxima a 39 caracteres (longitud máxima de una dirección IPv6)
                                            if (sanitizedValue.length > 39) {
                                                input.value = sanitizedValue.slice(0, 39);
                                            } else {
                                                input.value = sanitizedValue;
                                            }

                                            // Validar la dirección de red
                                            if (isValidNetworkAddress(sanitizedValue)) {
                                                input.setCustomValidity('');
                                            } else {
                                                input.setCustomValidity('Invalid network address format.');
                                            }
                                        });

                                        function isValidNetworkAddress(address) {
                                            const ipv4Pattern =
                                                /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
                                            const ipv6Pattern = /^([0-9a-fA-F]{1,4}:){7}([0-9a-fA-F]{1,4}|:)$/;

                                            return ipv4Pattern.test(address) || ipv6Pattern.test(address);
                                        }
                                    </script>

                                </div>
                            </form>
                        </div>
                        <!-- Modal para mostrar las alertas -->
                        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
                            <?php
                            $jsonFilePath = '../Model/Logs/session_messages.json';
                            $alerts = [];
                            $maxMessages = 8;

                            if (file_exists($jsonFilePath)) {
                                $logData = json_decode(file_get_contents($jsonFilePath), true);

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
                                            $alerts[] = "<div class='p-3 mb-2 bg-light'>{$message} - {$timestamp}</div>";
                                            $count++;

                                            // Limitar a $maxMessages mensajes por tipo
                                            if ($count >= $maxMessages) {
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            ?>

                            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-secondary text-white">
                                        <h2 class="modal-title">Notification History</h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?= !empty($alerts) ? implode('', $alerts) : '<p class="text-muted">No notifications recorded. </p>' ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" onclick="clearLogs()">Clean History </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                    <script src="../View/Js/ModalReport.js"></script>
                                </div>
                            </div>
                        </div>
                        <script>
                            function validateForm() {
                                var newPassword = document.getElementById("newPassword").value;
                                var confirmPassword = document.getElementById("confirmPassword").value;
                                var passwordPattern = /^(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/;

                                if (newPassword !== confirmPassword) {
                                    alert("Passwords do not match")
                                    return false;
                                }

                                if (!passwordPattern.test(newPassword)) {
                                    alert(
                                        "The password must contain a minimum of 8 characters, at least 1 special character and numbers"
                                    );
                                    return false;
                                }
                                return true;
                            }
                        </script>
                        <!-- Botón de Cerrar Sesión -->
                        <div class="text-center mt-4">
                            <button class="btn btn-danger" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i>Log Out </button>
                        </div>

                        <script>
                            function confirmLogout() {
                                if (window.confirm("Are you sure you want to log out?")) {
                                    // Set a cookie for the error message
                                    document.cookie = "error_message=Successful logout; path=/; max-age=30";
                                    window.location.href = "../Controller/Cerrar_sesion.php";

                                    return false;
                                }
                            }
                        </script>
                    </div>
                </div>
            </div>
            <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
            <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
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