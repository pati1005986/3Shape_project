<?php
session_start(); //Inicio de la sesion para que la funcion pueda funcionar
include "../Configuration/Connection.php";
// Verificar si hay datos disponibles

if (!isset($_SESSION['asset_data'])) {
    echo "No se han encontrado datos para mostrar.";
    exit;
}

// Obtener los datos desde la sesión
$asset_data = $_SESSION['asset_data'];

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Configuración de idioma
$default_lang = 'en';
$available_langs = ['en', 'es', 'pl'];

// Determinar idioma actual
if (isset($_GET['lang']) && in_array($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $default_lang;
}

// Cargar archivo de idioma
$lang_file = '../lang/' . $_SESSION['lang'] . '.json';
if (file_exists($lang_file)) {
    $lang = json_decode(file_get_contents($lang_file), true);
} else {
    $lang = json_decode(file_get_contents('../lang/en.json'), true);
}

// Función de traducción
function __($key, $lang) {
    return $lang[$key] ?? $key;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
     <!-- Añade Font Awesome -->
     <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="./Css/FormsCRUD.css">
     <link rel="stylesheet" href="./Configuration/JQuery/sweetalert2.min.css">
    <!--Archivo con la funcion para la interfaz de  actualizacion del activo-->
    <script src="./Configuration/JQuery/sweetalert2.all.min.js" defer></script>
    <title><?php echo __('update_register', $lang); ?></title>
 <style>
        /* Estilos consistentes con el formulario de registro */
        .form-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .section-title {
            border-bottom: 2px solid #6c757d;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #495057;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 8px 12px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
        
        label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #565e64;
        }
        
        .help-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 4px;
        }
        
        .animate-form {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Dark mode styles */
        .dark-mode .form-section {
            background-color: #2d3748;
            color: #e2e8f0;
        }
        
        .dark-mode .section-title {
            border-bottom-color: #4a5568;
            color: #e2e8f0;
        }
        
        .dark-mode label {
            color: #e2e8f0;
        }
        
        .dark-mode .form-control, .dark-mode .form-select {
            background-color: #1a202c;
            border-color: #4a5568;
            color: #e2e8f0;
        }
        
        .dark-mode .help-text {
            color: #a0aec0;
        }

        .admin-panel {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .dark-mode .admin-panel {
            background-color: #2d3748;
            border-color: #4a5568;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row d-flex justify-content-center align-items-center">
            <div id="FORMupdateform" class="shadow-lg p-4 rounded bg-light col-md-10 animate-form">
                <!-- Admin Panel -->
                <?php if ($is_admin): ?>
                    <div class="mb-4">
                        <button id="toggleFieldPanel" class="btn btn-primary mb-3">
                            <i class="fa-solid fa-cog me-2"></i> Show/Hide Admin Panel
                        </button>
                        
                        <div class="admin-panel mb-4 p-3 border rounded" style="display: none;">
                            <h4 class="mb-3"><i class="fa-solid fa-cog"></i> Field Management</h4>
                            <form id="dynamicFieldForm" action="../Admin/add_fieldTDO.php" method="POST" class="row g-3">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <?php
                                    $fieldLabels = require '../View/Fragments/field_labels.php';
                                ?>
                                <?php
                                try {
                                    $prohibited_fields = ['assetname', 'serial_number', 'fk_id', 'fk_assetname', 'user_status', 'last_user', 'job_title', 'cedula','fecha_ingreso', 'Carnet', 'LLave', 'Tipo_ID'];

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
                                            $fieldName = $column['COLUMN_NAME'];
                                            $displayName = $fieldLabels[$fieldName] ?? $fieldName;
                                            
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($displayName) . '</td>';
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
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Update Fields</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Main Form -->
                <form id="updateform" action="../Controller/procesar_datos.php" method="post">
                    <div class="text-center mb-4">
                        <h1 id="totalC" class="fw-bold">
                            <i class="fas fa-laptop-medical me-2"></i><?php echo __('update_register', $lang); ?>
                        </h1>
                    </div>
                    
                    <!-- Asset Information Section -->
                    <div class="form-section mb-4">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle me-2"></i><?php echo __('asset_information', $lang); ?>
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assetname"><i class="fas fa-tag me-2"></i><?php echo __('asset_name', $lang); ?>:</label>
                                    <input class="form-control shadow-sm" type="text" id="assetname" name="assetname"
                                        value="<?php echo htmlspecialchars($asset_data['assetname']); ?>" required
                                        oninput="this.value = this.value.toUpperCase()">
                                    <small id="asset-message" class="form-text"></small>
                                    <input type="hidden" id="fk_Assetname" name="fk_Assetname"
                                        value="<?php echo $asset_data['assetname']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="serial"><i class="fas fa-barcode me-2"></i><?php echo __('serial_number', $lang); ?>:</label>
                                    <input class="form-control shadow-sm" type="text" id="serial" name="serial"
                                        value="<?php echo htmlspecialchars($asset_data['serial_number']); ?>" required
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_ingreso"><i class="fas fa-calendar-plus me-2"></i><?php echo __('entry_date', $lang); ?>:</label>
                                    <input class="form-control shadow-sm" type="date" id="fecha_ingreso" name="fecha_ingreso"
                                        value="<?php echo !empty($asset_data['fecha_ingreso']) ? htmlspecialchars($asset_data['fecha_ingreso']) : date('Y-m-d'); ?>"
                                        required>
                                </div>
                            </div>
                            
                            <!-- Dynamic fields for assets -->
                            <?php if (isset($_SESSION['new_upd_fields'])): ?>
                                <?php foreach ($_SESSION['new_upd_fields'] as $field_name): ?>
                                    <?php if (strpos($field_name, 'asset_') === 0 || strpos($field_name, 'equipo_') === 0): ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="<?php echo htmlspecialchars($field_name); ?>">
                                                    <i class="fas fa-keyboard me-2"></i><?php echo htmlspecialchars($field_name); ?>:
                                                </label>
                                                <?php
                                                $field_value = isset($asset_data[$field_name]) ? htmlspecialchars($asset_data[$field_name]) : '';
                                                $field_type = $_SESSION['new_fieldU_types'][$field_name];
                                                ?>
                                                <?php if ($field_type === 'date'): ?>
                                                    <input class="form-control shadow-sm" type="date"
                                                        id="<?php echo htmlspecialchars($field_name); ?>"
                                                        name="<?php echo htmlspecialchars($field_name); ?>"
                                                        value="<?php echo $field_value; ?>" autocomplete="off">
                                                <?php elseif ($field_type === 'tinyint'): ?>
                                                    <select class="form-control shadow-sm" id="<?php echo htmlspecialchars($field_name); ?>"
                                                        name="<?php echo htmlspecialchars($field_name); ?>" autocomplete="off">
                                                        <option value="0" <?php echo ($field_value == 0) ? 'selected' : ''; ?>>No</option>
                                                        <option value="1" <?php echo ($field_value == 1) ? 'selected' : ''; ?>>Yes</option>
                                                    </select>
                                                <?php elseif (in_array($field_type, ['int', 'bigint', 'smallint'])): ?>
                                                    <input class="form-control shadow-sm" type="number"
                                                        id="<?php echo htmlspecialchars($field_name); ?>"
                                                        name="<?php echo htmlspecialchars($field_name); ?>"
                                                        value="<?php echo $field_value ?: 0; ?>" autocomplete="off" min="0"
                                                        oninput="this.value = Math.abs(this.value)">
                                                <?php else: ?>
                                                    <input class="form-control shadow-sm" type="text"
                                                        id="<?php echo htmlspecialchars($field_name); ?>"
                                                        name="<?php echo htmlspecialchars($field_name); ?>"
                                                        value="<?php echo $field_value; ?>" autocomplete="off" data-validate="true">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- User Information Section -->
                    <div class="form-section mb-4">
                        <h4 class="section-title">
                            <i class="fas fa-user me-2"></i><?php echo __('user_information', $lang); ?>
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre_usuario"><i class="fas fa-user me-2"></i><?php echo __('user_name', $lang); ?>:</label>
                                    <input class="form-control shadow-sm" type="text" id="nombre_usuario" name="nombre_usuario"
                                        value="<?php echo htmlspecialchars($asset_data['last_user']); ?>"
                                        autocomplete="off" data-validate="true">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usrStts"><i class="fas fa-user-shield me-2"></i><?php echo __('user_status', $lang); ?>:</label>
                                    <select id="usrStts" class="shadow-sm form-select" name="usrStts">
                                        <option value="0"><?php echo __('select_user_status', $lang); ?></option>
                                        <option value="Stock" <?php echo ($asset_data['user_status'] == 'Stock') ? 'selected' : ''; ?>><?php echo __('stock', $lang); ?></option>
                                        <option value="Active User" <?php echo ($asset_data['user_status'] == 'Active User') ? 'selected' : ''; ?>><?php echo __('active_user', $lang); ?></option>
                                        <option value="Old User" <?php echo ($asset_data['user_status'] == 'Old User') ? 'selected' : ''; ?>><?php echo __('old_user', $lang); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="job_title"><i class="fas fa-briefcase me-2"></i><?php echo __('job_title', $lang); ?>:</label>
                                    <input class="form-control shadow-sm" type="text" id="job_title" name="job_title"
                                        value="<?php echo htmlspecialchars($asset_data['job_title']); ?>" autocomplete="off"
                                        list="job_titles">
                                    <datalist id="job_titles">
                                        <?php
                                        $job_titles = $pdo->query("SELECT DISTINCT job_title FROM usuarios_equipos ORDER BY job_title ASC")->fetchAll(PDO::FETCH_COLUMN);
                                        foreach ($job_titles as $job_title) {
                                            echo "<option value='$job_title'>$job_title</option>";
                                        }
                                        ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id"><i class="fas fa-id-card me-2"></i>Id:</label>
                                    <div class="input-group">
                                        <select class="form-select" name="type_id" id="type_id" style="max-width: 100px;">
                                            <option value="CC" <?php echo ($asset_data['Tipo_ID'] == 'CC') ? 'selected' : ''; ?>>CC</option>
                                            <option value="CE" <?php echo ($asset_data['Tipo_ID'] == 'CE') ? 'selected' : ''; ?>>CE</option>
                                            <option value="PP" <?php echo ($asset_data['Tipo_ID'] == 'PP') ? 'selected' : ''; ?>>PP</option>
                                            <option value="TI" <?php echo ($asset_data['Tipo_ID'] == 'TI') ? 'selected' : ''; ?>>TI</option>
                                            <option value="RC" <?php echo ($asset_data['Tipo_ID'] == 'RC') ? 'selected' : ''; ?>>RC</option>
                                        </select>
                                        <input class="shadow-sm form-control" type="number" id="id" name="cedula" 
                                            value="<?php echo htmlspecialchars($asset_data['cedula']); ?>" required
                                            autocomplete="off" min="0">
                                    </div>
                                    <p id="mensaje" class="text-danger small"></p>
                                </div>
                            </div>
                            
                            <!-- Dynamic fields for users -->
                            <?php if (isset($_SESSION['new_upd_fields'])): ?>
                                <?php foreach ($_SESSION['new_upd_fields'] as $field_name): ?>
                                    <?php if (strpos($field_name, 'user_') === 0 || strpos($field_name, 'usuario_') === 0): ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="<?php echo htmlspecialchars($field_name); ?>">
                                                    <i class="fas fa-keyboard me-2"></i><?php echo htmlspecialchars($field_name); ?>:
                                                </label>
                                                <?php
                                                $field_value = isset($asset_data[$field_name]) ? htmlspecialchars($asset_data[$field_name]) : '';
                                                $field_type = $_SESSION['new_fieldU_types'][$field_name];
                                                ?>
                                                <?php if ($field_type === 'date'): ?>
                                                    <input class="form-control shadow-sm" type="date"
                                                        id="<?php echo htmlspecialchars($field_name); ?>"
                                                        name="<?php echo htmlspecialchars($field_name); ?>"
                                                        value="<?php echo $field_value; ?>" autocomplete="off">
                                                <?php elseif ($field_type === 'tinyint'): ?>
                                                    <select class="form-control shadow-sm" id="<?php echo htmlspecialchars($field_name); ?>"
                                                        name="<?php echo htmlspecialchars($field_name); ?>" autocomplete="off">
                                                        <option value="0" <?php echo ($field_value == 0) ? 'selected' : ''; ?>>No</option>
                                                        <option value="1" <?php echo ($field_value == 1) ? 'selected' : ''; ?>>Yes</option>
                                                    </select>
                                                <?php elseif (in_array($field_type, ['int', 'bigint', 'smallint'])): ?>
                                                    <input class="form-control shadow-sm" type="number"
                                                        id="<?php echo htmlspecialchars($field_name); ?>"
                                                        name="<?php echo htmlspecialchars($field_name); ?>"
                                                        value="<?php echo $field_value ?: 0; ?>" autocomplete="off" min="0"
                                                        oninput="this.value = Math.abs(this.value)">
                                                <?php else: ?>
                                                    <input class="form-control shadow-sm" type="text"
                                                        id="<?php echo htmlspecialchars($field_name); ?>"
                                                        name="<?php echo htmlspecialchars($field_name); ?>"
                                                        value="<?php echo $field_value; ?>" autocomplete="off" data-validate="true">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Additional Information Section -->
                    <div class="form-section mb-4">
                        <h4 class="section-title">
                            <i class="fas fa-clipboard-list me-2"></i><?php echo __('additional_information', $lang); ?>
                        </h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="selectCard"><i class="fas fa-id-card-alt me-2"></i><?php echo __('card', $lang); ?>:</label>
                                    <select class="shadow-sm form-select" name="selectCard" id="selectCard">
                                        <option value="Pendiente" <?php echo ($asset_data['Carnet'] == 'Pendiente') ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="No" <?php echo ($asset_data['Carnet'] == 'No') ? 'selected' : ''; ?>>
                                            No</option>
                                        <option value="Si" <?php echo ($asset_data['Carnet'] == 'Si') ? 'selected' : ''; ?>>
                                            Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="selectKEY"><i class="fas fa-key me-2"></i> <?php echo __('key', $lang); ?>:</label>
                                    <select class="shadow-sm form-select" name="selectKEY" id="selectKEY">
                                        <option value="Pendiente" <?php echo ($asset_data['LLave'] == 'Pendiente') ? 'Selected' : ''; ?>>
                                            Pending</option>
                                        <option value="No" <?php echo ($asset_data['LLave'] == 'No') ? 'selected' : ''; ?>>
                                            No</option>
                                        <option value="Si" <?php echo ($asset_data['LLave'] == 'Si') ? 'selected' : ''; ?>>
                                            Yes</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Other dynamic fields -->
                            <?php if (isset($_SESSION['new_upd_fields'])): ?>
                                <?php 
                                $otherFieldCounter = 0;
                                foreach ($_SESSION['new_upd_fields'] as $field_name): 
                                    if (strpos($field_name, 'asset_') !== 0 && strpos($field_name, 'equipo_') !== 0 && 
                                        strpos($field_name, 'user_') !== 0 && strpos($field_name, 'usuario_') !== 0):
                                        $otherFieldCounter++;
                                ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="<?php echo htmlspecialchars($field_name); ?>">
                                                <i class="fas fa-keyboard me-2"></i><?php echo htmlspecialchars($field_name); ?>:
                                            </label>
                                            <?php
                                            $field_value = isset($asset_data[$field_name]) ? htmlspecialchars($asset_data[$field_name]) : '';
                                            $field_type = $_SESSION['new_fieldU_types'][$field_name];
                                            ?>
                                            <?php if ($field_type === 'date'): ?>
                                                <input class="form-control shadow-sm" type="date"
                                                    id="<?php echo htmlspecialchars($field_name); ?>"
                                                    name="<?php echo htmlspecialchars($field_name); ?>"
                                                    value="<?php echo $field_value; ?>" autocomplete="off">
                                            <?php elseif ($field_type === 'tinyint'): ?>
                                                <select class="form-control shadow-sm" id="<?php echo htmlspecialchars($field_name); ?>"
                                                    name="<?php echo htmlspecialchars($field_name); ?>" autocomplete="off">
                                                    <option value="0" <?php echo ($field_value == 0) ? 'selected' : ''; ?>>No</option>
                                                    <option value="1" <?php echo ($field_value == 1) ? 'selected' : ''; ?>>Yes</option>
                                                </select>
                                            <?php elseif (in_array($field_type, ['int', 'bigint', 'smallint'])): ?>
                                                <input class="form-control shadow-sm" type="number"
                                                    id="<?php echo htmlspecialchars($field_name); ?>"
                                                    name="<?php echo htmlspecialchars($field_name); ?>"
                                                    value="<?php echo $field_value ?: 0; ?>" autocomplete="off" min="0"
                                                    oninput="this.value = Math.abs(this.value)">
                                            <?php else: ?>
                                                <input class="form-control shadow-sm" type="text"
                                                    id="<?php echo htmlspecialchars($field_name); ?>"
                                                    name="<?php echo htmlspecialchars($field_name); ?>"
                                                    value="<?php echo $field_value; ?>" autocomplete="off" data-validate="true">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <?php if ($otherFieldCounter % 3 !== 0): ?>
                                    <div class="col-md-<?php echo 4 * (3 - ($otherFieldCounter % 3)); ?>"></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button id="confirmAllButton" type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?php echo __('save_changes', $lang); ?>
                        </button>
                        <a href="../index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i><?php echo __('return', $lang); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

                 <script src="../View/Js/dark-mode-toggle-new.js"></script>
                <script>
                    // Función general para validar que no se ingresen caracteres especiales
function validarSinCaracteresEspeciales(inputElement, mensajeElement) {
    // Actualizar regex para incluir "@"
    const regex = /^[a-zA-Z0-9\s\/\-\@Ññ]+$/;
    inputElement.addEventListener('input', function() {
        const inputValue = this.value;

        if (!regex.test(inputValue)) {
            mensajeElement.textContent = 'Characters are not allowed except "/", "-", and "@".';
            // Remover caracteres no válidos, incluyendo "@"
            this.value = this.value.replace(/[^a-zA-Z0-9\s\/\-_.@Ññ]/g, '');
        } else {
            mensajeElement.textContent = '';
        }

        // Validar longitud máxima
        const maxLength = inputElement.dataset.maxLength ? parseInt(inputElement.dataset.maxLength) : 100;
        const currentValue = this.value;

        if (currentValue.length > maxLength) {
            mensajeElement.textContent = `Maximum ${maxLength} characters allowed.`;
            this.value = currentValue.slice(0, maxLength);
        }
    });
}
                    function validarAlgunosCaracteresEspeciales(inputElement, mensajeElement) {
                        const regex = /^[a-zA-Z0-9\s\/\-Ññ]+$/;

                        inputElement.addEventListener('input', function() {
                            const originalValue = this.value;
                            // Limpiar caracteres no permitidos (incluyendo Ññ)
                            const cleanedValue = originalValue.replace(/[^a-zA-Z0-9\s\/\-Ññ]/g, '');

                            // Verificar si se eliminaron caracteres no válidos
                            if (cleanedValue !== originalValue) {
                                mensajeElement.textContent = 'Characters are not allowed.';
                                this.value = cleanedValue;
                            } else {
                                mensajeElement.textContent = '';
                            }

                            // Validar longitud máxima (usar data-max-length o 50 por defecto)
                            const maxLength = inputElement.dataset.maxLength ? parseInt(inputElement.dataset.maxLength) : 100;
                            const currentValue = this.value;

                            if (currentValue.length > maxLength) {
                                mensajeElement.textContent = `Maximum ${maxLength} characters allowed.`;
                                this.value = currentValue.slice(0, maxLength);
                            }
                        });
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        const fieldsToValidate = document.querySelectorAll('[data-validate="true"]');
                        fieldsToValidate.forEach(inputElement => {
                            const mensajeElement = document.createElement('p');
                            mensajeElement.classList.add('text-danger');
                            inputElement.parentNode.appendChild(mensajeElement);

                            validarAlgunosCaracteresEspeciales(inputElement, mensajeElement);
                        });
                    });

                    // Aplicar validación al campo `nombre_usuario`
                    const nombreUsuarioInput = document.getElementById('nombre_usuario');
                    const mensajeNombreUsuario = document.createElement('p');
                    mensajeNombreUsuario.classList.add('text-danger');
                    nombreUsuarioInput.parentNode.appendChild(mensajeNombreUsuario);

                    validarSinCaracteresEspeciales(nombreUsuarioInput, mensajeNombreUsuario);

                    // Aplicar validación al campo `newl`
                    const newLaptopInput = document.getElementById('newl');
                    const mensajeNewLaptop = document.createElement('p');
                    mensajeNewLaptop.classList.add('text-danger');
                    newLaptopInput.parentNode.appendChild(mensajeNewLaptop);

                    validarSinCaracteresEspeciales(newLaptopInput, mensajeNewLaptop);
                </script>
            </div>
        </div>
    </div>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <script>
        //Alerta SweetAlert
        // Load SweetAlert2 script
document.addEventListener('DOMContentLoaded', function() {
    // Add SweetAlert2 JS if not already included
    if (typeof Swal === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        document.body.appendChild(script);
    }
    
    // Replace the current form submit handler with SweetAlert confirmation
    document.getElementById('updateform').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission
        
        const select1 = document.getElementById('usrStts');

        const requiredSelects = [select1];
        const errorMessages = [];

        requiredSelects.forEach((select, index) => {
            if (select.value === '0') {
                let message;
                switch (index) {
                    case 0:
                        message = "- Select a User Status.";
                        break;
                }
                errorMessages.push(message);
            }
        });

        if (errorMessages.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: "Please fix the following errors: <br>" + errorMessages.join("<br>"),
                confirmButtonColor: '#3085d6'
            });
        } else {
            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Update',
                text: "Are you sure you want to update this data?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, submit the form
                    const form = document.getElementById('updateform');
                    
                    // Use fetch API to submit the form
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form)
                    })
                    .then(response => response.text())
                    .then(data => {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'The register has been updated successfully.',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            // Redirect to index page after success
                            window.location.href = '../index.php';
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'There was an error updating the data.',
                            confirmButtonColor: '#3085d6'
                        });
                    });
                }
            });
        }
    });
});
    </script>
</body>
</html>