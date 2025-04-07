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
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change</title>
    <link rel="stylesheet" href="../View/Css/button-styles.css">
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
                                <script src="../View/Js/confirm_home_redirect.js"></script>

                                <li class="nav-item ms-auto">
                                    <a title="Notifications" type="button" class="btn btn-secondary" onclick="showModal()">
                                        <i class="fa-solid fa-bell" style='color:aqua'></i>
                                    </a>
                                    <a title="Log Files" type="button" class="btn btn-secondary" href="../View/Int_LogsDelete.php">
                                        <i class="fa-solid fa-info-circle" style="color:aqua"></i>
                                    </a>
                                    <a title="Settings" type="button" class="btn btn-secondary" href="../View/Int_changePsw.php">
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
                                <script src="../View/Js/ModalReport.js"></script>
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
                        <style>
.Btn {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  width: 45px;
  height: 45px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition-duration: .3s;
  box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.199);
  background-color: rgb(255, 65, 65);
}

/* plus sign */
.sign {
  width: 100%;
  transition-duration: .3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sign svg {
  width: 17px;
}

.sign svg path {
  fill: white;
}
/* text */
.text {
  position: absolute;
  right: 0%;
  width: 0%;
  opacity: 0;
  color: white;
  font-size: 1.2em;
  font-weight: 600;
  transition-duration: .3s;
}
/* hover effect on button width */
.Btn:hover {
  width: 125px;
  border-radius: 40px;
  transition-duration: .3s;
}

.Btn:hover .sign {
  width: 30%;
  transition-duration: .3s;
  padding-left: 20px;
}
/* hover effect button's text */
.Btn:hover .text {
  opacity: 1;
  width: 70%;
  transition-duration: .3s;
  padding-right: 10px;
}
/* button click effect*/
.Btn:active {
  transform: translate(2px ,2px);
}
                        </style>
   <div class="text-center mt-4 d-flex justify-content-center">
    <button class="Btn" id="logoutBtn">
        <div class="sign">
            <svg viewBox="0 0 512 512"><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path></svg>
        </div>
        <div class="text">Logout</div>
    </button>
</div>

<script>
    // SweetAlert2 para confirmar logout
    document.getElementById('logoutBtn').addEventListener('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to log out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Log Out',
            cancelButtonText: 'Cancel',
            backdrop: `
                rgba(0,0,0,0.7)
                url("../Configuration/images/nyan-cat.gif")
                left top
                no-repeat
            `
        }).then((result) => {
            if (result.isConfirmed) {
                // Set cookie and redirect
                document.cookie = "error_message=Successful logout; path=/; max-age=30";
                window.location.href = "../Controller/Cerrar_sesion.php";
            }
        });
    });
</script>
                    </div>
                </div>
            </div>
            <br>
            <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
            <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
</body>
<br>
<?php include './Fragments/footer.php'; ?>

</html>