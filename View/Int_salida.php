<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="../Configuration/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="shortcut icon" href="../Controller/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="../Configuration/JQuery/select2.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/sweetalert2.min.css">
    <script src="./Configuration/JQuery/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <title>Record of entry record</title>
    <!--Archivo de vista de proceso de salida de activos -->
    <style>
        body {
            background-color: rgb(239, 232, 233);
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 0 10px;
            font-size: 14px;
            line-height: 22px;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }

        .select2-container--default .select2-selection--single:hover {
            border-color: #80bdff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            transition: border-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: inherit;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center vh-100">
            <div id="FORMoutput" class="col-md-8 shadow p-5 rounded border border-secondary bg-light">
                <form id="updateForm" action="../Controller/act_salida.php" method="post" class="">
                    <div class="p-5 rounded">
                        <h1 id="totalC" class="">Record of Departure Certificate</h1>

                        <h4 class="my-3">Name of the Person:</h4>
                        <!--Consulta de equipos con condicionales basadas en el User Status y last user-->
                        <?php // Consulta de usuario
                        include "../Configuration/Connection.php";
                        $sql = "SELECT * FROM vista_equipos_usuarios
                        WHERE last_user NOT IN ('Stock', 'No user')
                        AND user_status = 'Active User'
                        AND last_user NOT REGEXP '^[0-9]+$';";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        try {
                            if ($stmt->rowCount() > 0) {
                                echo '<select id="equipoSelect" class="shadow form-select text-center shadow select2" name="equipo" aria-label="Default select example" required>';
                                echo '<option value="" selected disabled>Select a Person</option>';
                                echo '<option value="0" selected>Select a Person</option>';

                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . htmlspecialchars($row['assetname']) . '">' . htmlspecialchars($row['last_user']) . '</option>';
                                }
                                echo '</select><br>';
                            } else {
                                echo '<p class="text-center">No data found</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p class="text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }

                        ?>
                        <br>
                        <h4 class="my-3">Departure date:</h4>
                        <div class="form-group">
                            <input class="shadow-sm form-control" type="date" id="fechaSalida" name="fechaSalida"
                                autocomplete="off" required data-validate="true">
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const garantiaInput = document.getElementById('fechaSalida');

                                // Formatear la fecha actual en formato YYYY-MM-DD para el input type="date"
                                const today = new Date().toISOString().split('T')[0];
                                garantiaInput.value = today;

                                garantiaInput.addEventListener('blur', function(e) {
                                    if (!garantiaInput.value) {
                                        garantiaInput.classList.add('is-invalid');
                                    } else {
                                        garantiaInput.classList.remove('is-invalid');
                                    }
                                });

                                document.getElementById('updateForm').addEventListener('submit', function(event) {
                                    if (garantiaInput.classList.contains('is-invalid')) {
                                        event.preventDefault();
                                        alert('Please enter a valid date.');
                                    }
                                });
                            });
                        </script>

                    </div>
                    <br>
                    <div class="text-center">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-upload"></i> Update
                        </button>
                        <a href="../index.php" class="btn btn-danger">
                            <i class="fas fa-arrow-left"></i> Return
                        </a>
                    </div>

                    <br>
                </form>
                <!--Script Reciclado en base a la funcion de mensaje de confirmacion del formulario-->
                <script>
                    $(document).ready(function() {
                        $('#equipoSelect').select2({
                            placeholder: "Select a person",
                            allowClear: true,
                            minimumResultsForSearch: 5,
                            width: '100%'
                        });
                    });

                    // Validacion para campos vacios
                    document.getElementById('updateForm').addEventListener('submit', function(event) {
                        const select = $('#equipoSelect').select2('data')[0];
                        let errorMessage = "please fix the following errors: \n";
                        let haserror = false;

                        if (!select || select.id === '0') {
                            errorMessage += "- Please Select a person before updating: \n";
                            haserror = true;
                        }

                        if (haserror) {
                            event.preventDefault();
                            alert(errorMessage);
                        } else {
                            if (!confirm('Are you sure you want to update this data?')) {
                                event.preventDefault();
                            }
                        }
                    });
                </script>
            </div>
        </div>
    </div>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <script src="../Configuration/JQuery/select2.min.js"></script>
</body>
<!--footer-->
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