<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión
}

// Tiempo de expiración de la sesión (en segundos)
$tiempo_inactivo = 4600;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    setcookie('error_message', 'Sesión expirada. Vuelve a iniciar sesión.', time() + 30, '/');
    header("Location: ../index.php");
    include_once '../Controller/Cerrar_sesion.php';
    exit();
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    include_once '../Controller/Cerrar_sesion.php';
    header("Location: ../index.php");
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
    <style>
        #mainTable {
  width: 100%;
  border-collapse: collapse;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

#mainTable th {
  background-color: #007bff;
  color: white;
  padding: 12px;
  text-align: left;
}

#mainTable td {
  padding: 12px;
  border-bottom: 1px solid #ddd;
}

#mainTable tr:nth-child(even) {
  background-color: #f9f9f9;
}

#mainTable tr:hover {
  background-color: #e9ecef;
  transition: background 0.3s ease-in-out;
}

/* Botones de paginación más modernos */
.dataTables_wrapper .dataTables_paginate .paginate_button {
  background-color: #007bff;
  color: white !important;
  border-radius: 4px;
  margin: 2px;
  padding: 5px 10px;
  border: none;
  cursor: pointer;
  transition: background 0.3s;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background-color: #0056b3;

  @media (max-width: 768px) {
  #mainTable {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}

}

    </style>
</head>

<body>
    <div class="container mt-5">
        <br>
        <center> <h2 id="totalC" class="mb-4 my-2 text-center">Log Files</h2></center>
        
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
                            <br>
                        </th>
                        <th>Last Modified</th>
                        <th>Download</th>
                        <th>View</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
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
            <center> <a href="../Admin/index_admin.php" class="btn btn-secondary" ><i class="fas fa-home"></i> Back to Menu</a> </center>

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
                    echo '<a href="Int_LogsDelete.php" class="btn btn-danger mt-2 ml-2">Cancel </a>';
                    echo '</form>';
                } else {
                    echo '<div class="alert alert-danger mt-3">File not found.</div>';
                }
            }

            ?>
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
                    scrollY: '40vh',
                    scrollCollapse: true,
                    stateSave: true,
                    fixedHeader: true,
                    responsive: true,
                    lengthMenu: [
                        [5, 10, 25, 50, -1],
                        [5, 10, 25, 50, 'Todos']
                    ],
                    pageLength: 25,
                    autoWidth: false,
                    dom: '<"top"lB>rt<"bottom"ip><"clear">',
                    initComplete: function() {
                        this.api().columns().every(function() {
                            var column = this;
                            var input = $('<input type="text" placeholder="Search" />')
                                .appendTo($(column.footer()).empty())
                                .on('keyup change clear', function() {
                                    if (column.search() !== this.value) {
                                        column.search(this.value).draw();
                                    }
                                });
                        });
                    }
                }).buttons().container().appendTo('#mainTable_wrapper .col-md-6:eq(0)');
            });
        </script>
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