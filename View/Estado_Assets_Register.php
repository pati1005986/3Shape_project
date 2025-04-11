<?php
session_start();
include "../Configuration/Connection.php";

if (!isset($_SESSION['asset_data'])) {
    echo "No se han encontrado datos para mostrar.";
    exit;
}

// Obtener los datos desde la sesión
$asset_data = $_SESSION['asset_data'];

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// 1. Verificación de datos de la fase 1
if (!isset($_SESSION['phase1_data'])) {
    // Redirigir con mensaje de error si no hay datos de la fase 1
    $_SESSION['error_message'] = "Por favor complete primero el formulario de registro básico.";
    header("Location: Int_Registro_equipo.php");
    exit;
}

// 2. Procesamiento del formulario de fase 2
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phase']) && $_POST['phase'] === '2') {
    try {
        // Iniciar transacción para asegurar integridad de datos
        $pdo->beginTransaction();

        // 3. Preparar datos combinados
        $complete_data = array_merge($_SESSION['phase1_data'], $_POST);
        
        // 4. Insertar en tabla equipos
        $sql_equipos = "INSERT INTO equipos (
            assetname, serial_number, purchase_country, warranty_enddate, 
            expired, new_laptop, asset_status, asset_observations,
            monitor_status, monitor_observations, headset_status, 
            headset_observations, Monitor, HeadSet
        ) VALUES (
            :assetname, :serial, :compra, :garantia,
            :expired, :newl, :asset_status, :asset_observations,
            :monitor_status, :monitor_observations, :headset_status,
            :headset_observations, :Monitor, :HeadSet
        )";
        
        $stmt_equipos = $pdo->prepare($sql_equipos);
        $stmt_equipos->execute([
            ':assetname' => $complete_data['assetname'],
            ':serial' => $complete_data['serial'],
            ':compra' => $complete_data['compra'],
            ':garantia' => $complete_data['garantia'],
            ':expired' => $complete_data['expired'] ?? 0,
            ':newl' => $complete_data['newl'] ?? 0,
            ':asset_status' => $complete_data['asset_status'] ?? 'Nuevo',
            ':asset_observations' => $complete_data['asset_observations'] ?? '',
            ':monitor_status' => $complete_data['monitor_status'] ?? 'N/A',
            ':monitor_observations' => $complete_data['monitor_observations'] ?? '',
            ':headset_status' => $complete_data['headset_status'] ?? 'N/A',
            ':headset_observations' => $complete_data['headset_observations'] ?? '',
            ':Monitor' => $complete_data['Monitor'] ?? '',
            ':HeadSet' => $complete_data['HeadSet'] ?? ''
        ]);
        
        $equipo_id = $pdo->lastInsertId();

        // 5. Insertar en tabla usuarios_equipos
        $sql_usuarios = "INSERT INTO usuarios_equipos (
            id, fk_assetname, last_user, job_title, user_status, 
            status_change, cedula, Carnet, LLave, Tipo_ID,
            dongle_status, dongle_observations, celular_status,
            celular_observations, Dongle, Celular
        ) VALUES (
            :id, :fk_assetname, :nombre_usuario, :job_title, :usrStts,
            :status_change, :id, :selectCard, :selectKEY, :type_id,
            :dongle_status, :dongle_observations, :celular_status,
            :celular_observations, :Dongle, :Celular
        )";
        
        $stmt_usuarios = $pdo->prepare($sql_usuarios);
        $stmt_usuarios->execute([
            ':id' => $equipo_id,
            ':fk_assetname' => $complete_data['assetname'],
            ':nombre_usuario' => $complete_data['nombre_usuario'],
            ':job_title' => $complete_data['job_title'] ?? 'Unknown',
            ':usrStts' => $complete_data['usrStts'] ?? 'Active User',
            ':status_change' => $complete_data['status_change'] ?? 'Active Warranty',
            ':id' => $complete_data['id'] ?? 0,
            ':selectCard' => $complete_data['selectCard'] ?? 'Pendiente',
            ':selectKEY' => $complete_data['selectKEY'] ?? 'Pendiente',
            ':type_id' => $complete_data['type_id'] ?? 'CC',
            ':dongle_status' => $complete_data['dongle_status'] ?? 'N/A',
            ':dongle_observations' => $complete_data['dongle_observations'] ?? '',
            ':celular_status' => $complete_data['celular_status'] ?? 'N/A',
            ':celular_observations' => $complete_data['celular_observations'] ?? '',
            ':Dongle' => $complete_data['Dongle'] ?? '',
            ':Celular' => $complete_data['Celular'] ?? ''
        ]);

        // 6. Confirmar transacción
        $pdo->commit();

        // 7. Limpiar sesión y redirigir
        unset($_SESSION['phase1_data']);
        $_SESSION['success_message'] = "Activo registrado exitosamente!";
        header("Location: ../index.php");
        exit;

    } catch (PDOException $e) {
        // Revertir transacción en caso de error
        $pdo->rollBack();
        $error_message = "Error al registrar el activo: " . $e->getMessage();
    }
}

// 8. Consultar estados disponibles para los dropdowns
try {
    // [Consultas para los estados de cada asset permanecen iguales]
    $sql_asset_status = "SELECT DISTINCT asset_status FROM equipos WHERE asset_status IS NOT NULL AND asset_status != ''";
    // ... resto de consultas ...
} catch (PDOException $e) {
    $error_message = "Error loading status options: " . $e->getMessage();
}
?>
<?php
// Consultar los estados únicos de cada tipo de activo desde la base de datos
try {
    // Estados de computador
    $sql_asset_status = "SELECT DISTINCT asset_status FROM equipos WHERE asset_status IS NOT NULL AND asset_status != ''";
    $stmt_asset = $pdo->prepare($sql_asset_status);
    $stmt_asset->execute();
    $asset_status_options = $stmt_asset->fetchAll(PDO::FETCH_COLUMN);

    // Estados de monitor
    $sql_monitor_status = "SELECT DISTINCT monitor_status FROM equipos WHERE monitor_status IS NOT NULL AND monitor_status != ''";
    $stmt_monitor = $pdo->prepare($sql_monitor_status);
    $stmt_monitor->execute();
    $monitor_status_options = $stmt_monitor->fetchAll(PDO::FETCH_COLUMN);

    // Estados de headset
    $sql_headset_status = "SELECT DISTINCT headset_status FROM equipos WHERE headset_status IS NOT NULL AND headset_status != ''";
    $stmt_headset = $pdo->prepare($sql_headset_status);
    $stmt_headset->execute();
    $headset_status_options = $stmt_headset->fetchAll(PDO::FETCH_COLUMN);

    // Estados de dongle
    $sql_dongle_status = "SELECT DISTINCT dongle_status FROM usuarios_equipos WHERE dongle_status IS NOT NULL AND dongle_status != ''";
    $stmt_dongle = $pdo->prepare($sql_dongle_status);
    $stmt_dongle->execute();
    $dongle_status_options = $stmt_dongle->fetchAll(PDO::FETCH_COLUMN);

    // Estados de celular
    $sql_celular_status = "SELECT DISTINCT celular_status FROM usuarios_equipos WHERE celular_status IS NOT NULL AND celular_status != ''";
    $stmt_celular = $pdo->prepare($sql_celular_status);
    $stmt_celular->execute();
    $celular_status_options = $stmt_celular->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading status options: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Assets - Paso 2 de 2</title>
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="./Css/FormsCRUD.css">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <script src="../Configuration/JQuery/sweetalert2.all.min.js"></script>
    <style>
        .progress-bar {
            height: 10px;
            margin-bottom: 20px;
        }
        .phase-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .asset-section {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row d-flex justify-content-center align-items-center vh-100">
        <div id="FORMupdateform" class="shadow-lg p-5 rounded bg-light">
            <!-- Barra de progreso -->
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">Paso 2 de 2</div>
            </div>

            <!-- Mostrar errores si existen -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- Resumen de información de la fase 1 -->
            <div class="phase-info">
                <h4><i class="fas fa-info-circle"></i> Información Básica del Activo</h4>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['phase1_data']['nombre_usuario']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Activo:</strong> <?php echo htmlspecialchars($_SESSION['phase1_data']['assetname']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Serial:</strong> <?php echo htmlspecialchars($_SESSION['phase1_data']['serial']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Formulario de estados -->
            <form id="updateform" method="post" action="">
                <input type="hidden" name="phase" value="2">
                
                <!-- Sección de Computador -->
                <div class="asset-section">
                    <h5><i class="fas fa-laptop"></i> Computador</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                            <label for="asset_status">Status</label>
                <select class="form-control" id="asset_status" name="asset_status">
                    <option value="">Select status</option>
                    <?php foreach ($asset_status_options as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>"
                            <?php echo ($asset_data['asset_status'] == $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (!in_array($asset_data['asset_status'], $asset_status_options) && !empty($asset_data['asset_status'])): ?>
                        <option value="<?php echo htmlspecialchars($asset_data['asset_status']); ?>" selected>
                            <?php echo htmlspecialchars($asset_data['asset_status']); ?> (Current)
                        </option>
                    <?php endif; ?>
                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="asset_observations">Observaciones</label>
                                <textarea class="form-control" id="asset_observations" name="asset_observations" rows="2" maxlength="255"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de HeadSet -->
                <div class="asset-section">
                    <h5><i class="fas fa-desktop"></i> HeadSet</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="HeadSet">Serial</label>
                                <input class="form-control" type="text" id="HeadSet" name="HeadSet">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                            <label for="headset_status">Status</label>
                <select class="form-control" id="headset_status" name="headset_status">
                    <option value="">Select status</option>
                    <?php foreach ($headset_status_options as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>"
                            <?php echo ($asset_data['headset_status'] == $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (!in_array($asset_data['headset_status'], $headset_status_options) && !empty($asset_data['headset_status'])): ?>
                        <option value="<?php echo htmlspecialchars($asset_data['headset_status']); ?>" selected>
                            <?php echo htmlspecialchars($asset_data['headset_status']); ?> (Current)
                        </option>
                    <?php endif; ?>
                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="monitor_observations">Observaciones</label>
                                <textarea class="form-control" id="monitor_observations" name="monitor_observations" rows="2" maxlength="255"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [Resto de secciones (Headset, Dongle, Celular) con estructura similar] -->

                <!-- Botones de acción -->
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-between">
                        <a href="Int_Registro_equipo.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Completar Registro
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
<script>
// Validación mejorada del formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('updateform');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessage = "Por favor corrija los siguientes errores:\n";
        
        // Validar estado del computador (requerido)
        const assetStatus = document.getElementById('asset_status');
        if (assetStatus.value === '') {
            isValid = false;
            errorMessage += "- Seleccione un estado para el computador.\n";
            assetStatus.classList.add('is-invalid');
        } else {
            assetStatus.classList.remove('is-invalid');
        }
        
        // Validar seriales (si se ingresan)
        const serialInputs = document.querySelectorAll('input[type="text"][name*="Serial"]');
        serialInputs.forEach(input => {
            if (input.value.trim() !== '' && !/^[a-zA-Z0-9\-]+$/.test(input.value)) {
                isValid = false;
                errorMessage += `- El serial ${input.name} contiene caracteres inválidos.\n`;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error de Validación',
                text: errorMessage
            });
        } else {
            // Confirmación antes de enviar
            e.preventDefault();
            Swal.fire({
                title: '¿Confirmar registro?',
                text: "¿Está seguro que desea completar el registro con los datos ingresados?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
    
    // Validación en tiempo real
    const validateInput = (input) => {
        if (input.value.trim() !== '' && !/^[a-zA-Z0-9\s\/\-Ññ]+$/.test(input.value)) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    };
    
    document.querySelectorAll('input[type="text"], textarea').forEach(input => {
        input.addEventListener('input', () => validateInput(input));
    });
});
</script>
</body>
</html>