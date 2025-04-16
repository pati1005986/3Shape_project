<?php
session_start();

// Mostrar mensajes de éxito/error
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}

include "../Configuration/Connection.php";

// Mostrar mensajes de éxito/error
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}
//Si no se proporciona el nombre del equipo
if (!isset($_GET['assetname'])) {
    echo "No se ha proporcionado un assetname.";
    exit;
}

$assetname = $_GET['assetname'];

// Consulta usando JOIN para obtener datos de ambas tablas
$sql = "
    SELECT *
    FROM equipos
    INNER JOIN usuarios_equipos
    ON equipos.assetname = usuarios_equipos.fk_assetname
    WHERE equipos.assetname = :assetname
";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
$stmt->execute();

$asset_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset_data) {
    echo "No se han encontrado datos para el activo especificado.";
    exit;
}

$_SESSION['asset_data'] = $asset_data;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
// Estados predefinidos para cada tipo de activo
$asset_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
$monitor_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
$headset_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
$dongle_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
$celular_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="./Css/FormsCRUD.css">
    <title>Preview PDF Acta de Entrega</title>
    <style>
        .pdf-preview {
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 40px;
            margin: 20px auto;
            max-width: 850px;
            font-family: Arial, sans-serif;
        }
        .header-logo {
            text-align: right;
            margin-bottom: 15px;
        }
        .pdf-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .certificate-text {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .asset-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .asset-table th, .asset-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
            text-align: center;
        }
        .asset-table th {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .admin-section {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .admin-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .admin-table {
            width: 70%;
            border-collapse: collapse;
        }
        .admin-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
        }
        .declaration-text {
            font-size: 14px;
            margin: 20px 0;
            line-height: 1.6;
        }
        .signature-section {
            width: 100%;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            border: 1px solid #000;
            width: 45%;
            height: 100px;
            position: relative;
            padding-top: 30px;
            text-align: center;
        }
        .signature-label {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #000;
            background-color: #f5f5f5;
        }
        /* Controles de edición */
        .editable {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .signature-canvas {
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            width: 100%;
            height: 150px;
        }
    </style>
</head>

<body>
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-4">Vista Previa - Acta de Entrega</h2>
                
                <div class="pdf-preview">
                <form id="acta-form" action="../Controller/procesar_datos.php" method="post">
                        <!-- Encabezado del PDF -->
                        <div class="header-logo">
                            <img src="../Model/logo.png" alt="Logo" height="60">
                        </div>
                        <div class="text-left">
                            <strong>Fecha: </strong><?php echo date('d/m/Y'); ?>
                        </div>
                        <div class="pdf-title">Acta de Entrega</div>
                        
                        <!-- Texto del certificado -->
                        <div class="certificate-text">
                            Por medio de la presente se certifica que el/la señor(a); 
                            <input type="text" class="editable" name="last_user" value="<?php echo htmlspecialchars($asset_data['last_user']); ?>" required>
                            
                            <?php 
                            $tipo_id = isset($asset_data['Tipo_ID']) ? $asset_data['Tipo_ID'] : 'CC';
                            $tipo_text = "";
                            
                            switch ($tipo_id) {
                                case 'CE':
                                    $tipo_text = "identificado(a) con Cédula de Extranjeria No.";
                                    break;
                                case 'PP':
                                    $tipo_text = "identificado(a) con Pasaporte No.";
                                    break;
                                case 'RC':
                                    $tipo_text = "identificado(a) con Cédula de Residencia No.";
                                    break;  
                                case 'CC':
                                    $tipo_text = "identificado(a) con Cédula de Ciudadanía No.";
                                    break;
                                case 'TI':
                                    $tipo_text = "identificado(a) con Tarjeta de Identidad No.";
                                    break;
                                default:
                                    $tipo_text = "identificado(a) con ID:";
                                    break;
                            }
                            ?>
                            
                            <select name="Tipo_ID" class="editable">
                                <option value="CC" <?php echo ($tipo_id == 'CC') ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                                <option value="CE" <?php echo ($tipo_id == 'CE') ? 'selected' : ''; ?>>Cédula de Extranjería</option>
                                <option value="PP" <?php echo ($tipo_id == 'PP') ? 'selected' : ''; ?>>Pasaporte</option>
                                <option value="RC" <?php echo ($tipo_id == 'RC') ? 'selected' : ''; ?>>Cédula de Residencia</option>
                                <option value="TI" <?php echo ($tipo_id == 'TI') ? 'selected' : ''; ?>>Tarjeta de Identidad</option>
                            </select>
                            
                            <input type="text" class="editable" name="cedula" value="<?php echo htmlspecialchars($asset_data['cedula'] ?? ''); ?>" required>, 
                            y quien ocupa el cargo de 
                            <input type="text" class="editable" name="job_title" value="<?php echo htmlspecialchars($asset_data['job_title'] ?? ''); ?>" required>
                            se le hace entrega de los siguientes activos que manejará en 3Shape.
                        </div>
                        
                        <!-- Tabla de activos -->
                        <table class="asset-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Descripción del Activo</th>
                                    <th>Serial</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Computador -->
                                <tr>
                                    <td>1</td>
                                    <td>Computador Personal</td>
                                    <td>
                                        <input type="text" class="editable" name="serial_number" 
                                            value="<?php echo htmlspecialchars($asset_data['serial_number']); ?>" required>
                                    </td>
                                    <td>
                                        <select class="editable" name="asset_status">
                                            <?php foreach ($asset_status_options as $option): ?>
                                                <option value="<?php echo htmlspecialchars($option); ?>"
                                                    <?php echo ($asset_data['asset_status'] == $option) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="editable" name="asset_observations" 
                                            value="<?php echo htmlspecialchars($asset_data['asset_observations'] ?? ''); ?>">
                                    </td>
                                </tr>
                                
                                <!-- Monitor (si existe) -->
                                <?php if (!empty($asset_data['Monitor'])): ?>
                                <tr>
                                    <td>2</td>
                                    <td>Monitor</td>
                                    <td>
                                        <input type="text" class="editable" name="Monitor" 
                                            value="<?php echo htmlspecialchars($asset_data['Monitor']); ?>">
                                    </td>
                                    <td>
                                        <select class="editable" name="monitor_status">
                                            <?php foreach ($monitor_status_options as $option): ?>
                                                <option value="<?php echo htmlspecialchars($option); ?>"
                                                    <?php echo ($asset_data['monitor_status'] == $option) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="editable" name="monitor_observations" 
                                            value="<?php echo htmlspecialchars($asset_data['monitor_observations'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <!-- HeadSet (si existe) -->
                                <?php if (!empty($asset_data['HeadSet'])): ?>
                                <tr>
                                    <td><?php echo (!empty($asset_data['Monitor'])) ? '3' : '2'; ?></td>
                                    <td>Head Set</td>
                                    <td>
                                        <input type="text" class="editable" name="HeadSet" 
                                            value="<?php echo htmlspecialchars($asset_data['HeadSet']); ?>">
                                    </td>
                                    <td>
                                        <select class="editable" name="headset_status">
                                            <?php foreach ($headset_status_options as $option): ?>
                                                <option value="<?php echo htmlspecialchars($option); ?>"
                                                    <?php echo ($asset_data['headset_status'] == $option) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="editable" name="headset_observations" 
                                            value="<?php echo htmlspecialchars($asset_data['headset_observations'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <!-- Dongle (si existe) -->
                                <?php 
                                $row_num = 2;
                                if (!empty($asset_data['Monitor'])) $row_num++;
                                if (!empty($asset_data['HeadSet'])) $row_num++;
                                ?>
                                <?php if (!empty($asset_data['Dongle'])): ?>
                                <tr>
                                    <td><?php echo $row_num; ?></td>
                                    <td>Dongle</td>
                                    <td>
                                        <input type="text" class="editable" name="Dongle" 
                                            value="<?php echo htmlspecialchars($asset_data['Dongle']); ?>">
                                    </td>
                                    <td>
                                        <select class="editable" name="dongle_status">
                                            <?php foreach ($dongle_status_options as $option): ?>
                                                <option value="<?php echo htmlspecialchars($option); ?>"
                                                    <?php echo ($asset_data['dongle_status'] == $option) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="editable" name="dongle_observations" 
                                            value="<?php echo htmlspecialchars($asset_data['dongle_observations'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <!-- Celular (si existe) -->
                                <?php 
                                if (!empty($asset_data['Dongle'])) $row_num++;
                                ?>
                                <?php if (!empty($asset_data['Celular'])): ?>
                                <tr>
                                    <td><?php echo $row_num; ?></td>
                                    <td>Celular</td>
                                    <td>
                                        <input type="text" class="editable" name="Celular" 
                                            value="<?php echo htmlspecialchars($asset_data['Celular']); ?>">
                                    </td>
                                    <td>
                                        <select class="editable" name="celular_status">
                                            <?php foreach ($celular_status_options as $option): ?>
                                                <option value="<?php echo htmlspecialchars($option); ?>"
                                                    <?php echo ($asset_data['celular_status'] == $option) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="editable" name="celular_observations" 
                                            value="<?php echo htmlspecialchars($asset_data['celular_observations'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- Sección administrativa -->
                        <div class="admin-section">
                            <div class="admin-title">Datos exclusivos Administrativos:</div>
                            <table class="admin-table">
                                <tr>
                                    <td style="width: 40%;">Carne:</td>
                                    <td>
                                        <input type="text" class="editable" name="Carnet" 
                                            value="<?php echo htmlspecialchars($asset_data['Carnet'] ?? 'Pendiente'); ?>" style="width: 100%;">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Llave locker:</td>
                                    <td>
                                        <input type="text" class="editable" name="LLave" 
                                            value="<?php echo htmlspecialchars($asset_data['LLave'] ?? 'Pendiente'); ?>" style="width: 100%;">
                                    </td>
                                </tr>
                                <tr>
                                    <td>SIM card:</td>
                                    <td>
                                        <input type="text" class="editable" name="SIMcard" 
                                            value="<?php echo htmlspecialchars($asset_data['SIMcard'] ?? ''); ?>" style="width: 100%;">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Declaración -->
                        <div class="declaration-text">
                            El abajo firmante declara haber recibido con satisfacción los elementos antes mencionados con la condición de que cuidará de ellos.
                        </div>
                        
                        <!-- Sección de firmas -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="signature-pad">Firma del Colaborador:</label>
                                    <canvas id="signature-pad" class="signature-canvas"></canvas>
                                    <input type="hidden" name="signature" id="signature-data">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Limpiar firma</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vobo-signature-pad">Vo.Bo. Departamento de IT:</label>
                                    <canvas id="vobo-signature-pad" class="signature-canvas"></canvas>
                                    <input type="hidden" name="vobo_signature" id="vobo-signature-data">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-secondary" id="clear-vobo-signature">Limpiar firma</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Añadir campo oculto con assetname -->
    <input type="hidden" name="assetname" value="<?php echo htmlspecialchars($asset_data['assetname'] ?? ''); ?>">
    
    <!-- Botones de acción -->
    <div class="row mt-4">
        <div class="col-md-6">
            <button type="submit" name="generate_pdf" value="1" class="btn btn-primary">
                <i class="fas fa-file-pdf me-2"></i>Generar PDF
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-2"></i>Guardar Cambios
            </button>
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Regresar
            </a>
        </div>
    </div>
</form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts necesarios -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuración de SignaturePad para firma del colaborador
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
            
            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
            });

            // Configuración de SignaturePad para firma de Vo.Bo.
            const voboCanvas = document.getElementById('vobo-signature-pad');
            const voboSignaturePad = new SignaturePad(voboCanvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
            
            document.getElementById('clear-vobo-signature').addEventListener('click', function() {
                voboSignaturePad.clear();
            });

            // Capturar las firmas al enviar el formulario
            document.getElementById('acta-form').addEventListener('submit', function(e) {
                if (!signaturePad.isEmpty()) {
                    document.getElementById('signature-data').value = signaturePad.toDataURL();
                }
                
                if (!voboSignaturePad.isEmpty()) {
                    document.getElementById('vobo-signature-data').value = voboSignaturePad.toDataURL();
                }
                
                // Modificar la acción del formulario para generar el PDF
                this.action = '../Controller/generar-acta-pdf.php';
            });
            
            // Ajustar tamaño de los canvas al cargar y al redimensionar
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                
                // Para firma del colaborador
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear(); // Limpiar al redimensionar
                
                // Para firma de Vo.Bo.
                voboCanvas.width = voboCanvas.offsetWidth * ratio;
                voboCanvas.height = voboCanvas.offsetHeight * ratio;
                voboCanvas.getContext("2d").scale(ratio, ratio);
                voboSignaturePad.clear(); // Limpiar al redimensionar
            }
            
            window.onresize = resizeCanvas;
            resizeCanvas();
        });
    </script>
</body>
</html>
