<?php
ob_start();

session_start(); // Iniciar sesión

// Tiempo de expiración de la sesión (en segundos)
$tiempo_inactivo = 4600;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    setcookie('error_message', 'Sesión expirada. Vuelve a iniciar sesión.', time() + 30, '/');
    include_once '../Controller/Cerrar_sesion.php';
    exit();
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    include_once '../Controller/Cerrar_sesion.php';
    exit("Acceso denegado.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backups</title>
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Configuration/DataTables/datatables.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/fontawesome.min.css">
    <script src="../Configuration/JQuery/sweetalert2.all.min.js"></script>
    <script src="../Configuration/JQuery/sweetalert2@11.js"></script>
    <link rel="stylesheet" href="../Configuration/JQuery/sweetalert2.min.css">
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
                                    <a class="nav-link active" id="homeindex" href="#" onclick="confirmHomeRedirect(); return false;">Home</a>
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
            <div class="container mt-5">
                <br>
                <center>
                    <h2 id="totalC" class="mb-4 my-2 text-center">Log Files <i class="fas fa-file-alt"></i></h2>
                </center>
                <div id="FORMeditingLog" class="table-responsive">
                    <table id="mainTable" class="shadow-lg table table-bordered table-striped fade-in">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Total Size (Formatted):
                                    <?php
                                    // Formato de tamaño de archivo
                                    $logDir = __DIR__ . '/../Model/Logs';
                                    $folderSize = 0;
                                    $files = scandir($logDir);
                                    foreach ($files as $file) {
                                        if ($file !== '.' && $file !== '..') {
                                            $filePath = $logDir . '/' . $file;
                                            $folderSize += filesize($filePath);
                                        }
                                    }
                                    // Convert bytes to human-readable format 
                                    function formatFileSize($size)
                                    {
                                        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                        $index = 0;
                                        while ($size >= 1024 && $index < count($units) - 1) {
                                            $size /= 1024;
                                            $index++;
                                        }
                                        return round($size, 2) . ' ' . $units[$index];
                                    }
                                    $formattedSize = formatFileSize($folderSize);
                                    echo '(' . $formattedSize . ')';
                                    ?>
                                </th>
                                <th>Last Modified</th>
                                <th>Download</th>
                                <th>View</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            date_default_timezone_set('America/Bogota');
                            // Listar archivos
                            $logDir = __DIR__ . '/../Model/Logs';
                            if (is_dir($logDir)) {
                                $files = scandir($logDir);
                                foreach ($files as $file) {
                                    if ($file !== '.' && $file !== '..') {
                                        $filePath = $logDir . '/' . $file;
                                        echo '<tr>';
                                        // mostrar tamaño del archivo por registro
                                        echo '<td>' . htmlspecialchars($file) . '</td>';
                                        echo '<td>' . round(filesize($filePath) / 1024, 2) . ' KB' . '</td>';
                                        echo '<td>' . date("F d Y H:i:s.", filemtime($filePath)) . '</td>';
                                        // Funcion para descargar archivos
                                        echo '<td title="Download"><a href="../Model/Logs/' . htmlspecialchars($file) . '" download class="btn btn-secondary btn-sm"><i class="fas fa-download"></i></a></td>';
                                        // Funcion para visualizar archivos
                                        echo '<td title="View"><a href="Int_LogsDelete.php?view=' . urlencode($file) . '" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a></td>';
                                        // Funcion para eliminar archivos
                                        echo '<td title="Delete"><a href="Int_LogsDelete.php?delete=' . urlencode($file) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this file?\')"><i class="fas fa-trash-alt"></i></a></td>';
                                        echo '</tr>';
                                    }
                                }
                            } else {
                                echo '<tr><td colspan="6">Log directory not found.</td></tr>';
                            }
                            // Handle file deletion
                            if (isset($_GET['delete'])) {
                                $fileToDelete = $logDir . '/' . $_GET['delete'];
                                if (file_exists($fileToDelete)) {
                                    unlink($fileToDelete);
                                    header("Location: Int_LogsDelete.php");
                                    exit();
                                } else {
                                    echo '<tr><td colspan="6">File not found.</td></tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <br>
                    <center><a href="../Admin/index_admin.php" class="btn btn-secondary">Back to Menu</a></center>
                    <?php
                    // Handle file viewing // Manejo de vista del arhivo
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['view'])) {
                        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
                            die('Unauthorized access');
                        }
                        $fileToEdit = $logDir . '/' . $_GET['view'];
                        if (file_exists($fileToEdit)) {
                            $logContent = $_POST['log_content'] ?? '';
                            $logContent = trim($logContent);
                            if (!empty($logContent)) {
                                file_put_contents($fileToEdit, $logContent);
                            }
                            header("Location: Int_LogsDelete.php");
                            exit();
                        } else {
                            setcookie('error_message', 'File not found.', time() + 30, '/');
                            header("Location: Int_LogsDelete.php");
                            exit();
                        }
                    }
                    // logica relacionada a la vista e edicion de los logs mostrados en la tabla
                    if (isset($_GET['view'])) {
                        $fileToView = $logDir . '/' . $_GET['view'];
                        if (file_exists($fileToView)) {
                            echo '<h3 class="mt-5">Editing: ' . htmlspecialchars($_GET['view']) . '</h3>';
                            echo '<br>';
                            echo '<form method="POST" class="shadow-lg"  action="Int_LogsDelete.php?view=' . urlencode($_GET['view']) . '">';
                            echo '<textarea name="log_content" class="form-control" rows="10">' . htmlspecialchars(file_get_contents($fileToView)) . '</textarea>';
                            echo '<br></br>';
                            echo '<button type="submit" class="btn btn-secondary mt-2 me-2">Save Changes</button>';
                            echo '<a href="Int_LogsDelete.php" class="btn btn-danger mt-2 ml-2">Cancel</a>';
                            echo '</form>';
                        } else {
                            echo '<div class="alert alert-danger mt-3">File not found.</div>';
                        }
                    }
                    ?>
                </div>

                <!-- Modal para mostrar las alertas -->
                <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
                    <?php
                    include_once '../Controller/Notify.php'
                    ?>

                    <div class="modal-dialog modal-dialog-scrollable modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h2 class="modal-title">Notification History</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="text" id="notificationFilter" class="form-control mb-3" placeholder="Filter notifications...">
                                <?= !empty($alerts) ? implode('', $alerts) : '<div class="p-3 mb-2 bg-light shadow-sm">No notifications recorded. </div>' ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" onclick="clearLogs()">Clean History </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                            <script src="../View/Js/ModalReport.js"></script>
                        </div>
                    </div>
                </div>

                <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
                <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
                <script src="../Configuration/DataTables/datatables.min.js"></script>
                <script>
                    $(document).ready(function() {
                        $('#mainTable').DataTable({
                            paging: true,
                            ordering: true,
                            scrollX: true,
                            scrollY: '60vh', // Aumentado para mejor visualización
                            scrollCollapse: true,
                            stateSave: true,
                            fixedHeader: {
                                header: true,
                                footer: true
                            },
                            responsive: true,
                            lengthMenu: [
                                [5, 10, 25, 50, -1],
                                [5, 10, 25, 50, 'Todos']
                            ],
                            pageLength: 25,
                            autoWidth: false,
                            initComplete: function() {
                                // Búsqueda por columnas en el footer
                                this.api().columns().every(function() {
                                    let column = this;
                                    let title = $(column.header()).text();

                                    $('<div class="form-group"/>').append(
                                        $('<input type="text" class="form-control form-control-sm" placeholder="Filtrar ' + title + '" />')
                                        .on('keyup change clear', function() {
                                            if (column.search() !== this.value) {
                                                column.search(this.value).draw();
                                            }
                                        })
                                    ).appendTo($(column.footer()).empty());
                                });

                                // Añadir clase al search principal
                                $('.dataTables_filter input').addClass('form-control form-control-sm');
                            }
                        })
                        // Mejorar estilo del length menu
                        $('.dataTables_length select').addClass('form-control form-control-sm');

                        // Filter notifications
                        $('#notificationFilter').on('keyup', function() {
                            const filterValue = $(this).val().toLowerCase();
                            $('.modal-body div').filter(function() {
                                $(this).toggle($(this).text().toLowerCase().indexOf(filterValue) > -1);
                            });
                        });
                    });
                </script>
</body>
<?php include './Fragments/footer.php'; ?>
</html>