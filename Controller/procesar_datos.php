<?php
session_start();

include "../Configuration/Connection.php";
include "../Controller/sanitization.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperar datos del formulario
    $fields = [
        [
            'post_key' => 'nombre_usuario',
            'var_name' => 'nombre_usuario',
            'label' => 'Assetname',
            'max_length' => 100
        ],
        [
            'post_key' => 'serial',
            'var_name' => 'serial',
            'label' => 'serial number',
            'max_length' => 100
        ],
        [
            'post_key' => 'compra',
            'var_name' => 'compra',
            'label' => 'Purchase country',
            'max_length' => 100
        ],
        [
            'post_key' => 'garantia',
            'var_name' => 'garantia',
            'label' => 'Warranty end date',
            'max_length' => 100
        ],
        [
            'post_key' => 'newl',
            'var_name' => 'newl'
        ],
        [
            'post_key' => 'usrStts',
            'var_name' => 'usrStts',
            'label' => 'User status',
            'max_length' => 100
        ],
        [
            'post_key' => 'job_title',
            'var_name' => 'job_title'
        ],
        [
            'post_key' => 'status_change',
            'var_name' => 'stats_change',
            'label' => 'Status Change',
            'max_length' => 100
        ],
        [
            'post_key' => 'id',
            'var_name' => 'cedula',
            'label' => 'ID',
            'max_length' => 100
        ],
        [
            'post_key' => 'selectCard',
            'var_name' => 'carnet',
            'label' => '',
            'max_length' => 100
        ],
        [
            'post_key' => 'selectKEY',
            'var_name' => 'llave'
        ],
        [
            'post_key' => 'selectID',
            'var_name' => 'tipoID'
        ],
        [
            'post_key' => 'assetname',
            'var_name' => 'assetname',
            'label' => 'Asset name',
            'max_length' => 100
        ],
        [
            'post_key' => 'fk_assetname',
            'var_name' => 'fk_assetname',
            'label' => 'foreign key asset name',
            'max_length' => 100
        ],
        // Campos adicionales para los PDFs
        [
            'post_key' => 'asset_status',
            'var_name' => 'asset_status'
        ],
        [
            'post_key' => 'asset_observations',
            'var_name' => 'asset_observations'
        ],
        [
            'post_key' => 'monitor_status',
            'var_name' => 'monitor_status'
        ],
        [
            'post_key' => 'monitor_observations',
            'var_name' => 'monitor_observations'
        ],
        [
            'post_key' => 'headset_status',
            'var_name' => 'headset_status'
        ],
        [
            'post_key' => 'headset_observations',
            'var_name' => 'headset_observations'
        ],
        [
            'post_key' => 'dongle_status',
            'var_name' => 'dongle_status'
        ],
        [
            'post_key' => 'dongle_observations',
            'var_name' => 'dongle_observations'
        ],
        [
            'post_key' => 'celular_status',
            'var_name' => 'celular_status'
        ],
        [
            'post_key' => 'celular_observations',
            'var_name' => 'celular_observations'
        ],
        [
            'post_key' => 'SIMcard',
            'var_name' => 'SIMcard'
        ]
    ];
    
    // Procesar campos del formulario
    foreach ($fields as $field) {
        ${$field['var_name']} = isset($_POST[$field['post_key']]) 
            ? sanitize_input($_POST[$field['post_key']]) 
            : null;
        
        if (isset($field['max_length']) && ${$field['var_name']} !== null) {
            validate_input_length(
                ${$field['var_name']},
                $field['label'],
                $field['max_length']
            );
        }
    }

    // Verificar campos requeridos
    if (empty($nombre_usuario))     {
        $_SESSION["error"] = "El nombre de usuario es requerido";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Calcular fecha de expiración
    $fecha_actual = date('Y-m-d');
    $expira = (strtotime($garantia) < strtotime($fecha_actual)) ? 'yes' : 'no';

    // Obtener estructura de las tablas
    $tables_structure = [
        'equipos' => [],
        'usuarios_equipos' => []
    ];

    try {
        // Obtener columnas de la tabla equipos
        $stmt = $pdo->query("SHOW COLUMNS FROM equipos");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tables_structure['equipos'][] = $row['Field'];
        }

        // Obtener columnas de la tabla usuarios_equipos
        $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_equipos");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tables_structure['usuarios_equipos'][] = $row['Field'];
        }
    } catch (PDOException $e) {
        $_SESSION["error"] = "Error al obtener estructura de tablas: " . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Procesar campos dinámicos
    $new_fields = $_SESSION['new_upd_fields'] ?? [];
    $equipos_fields = [];
    $usuarios_equipos_fields = [];
    $bind_params = [];

    foreach ($new_fields as $field_name) {
        $field_value = isset($_POST[$field_name]) ? sanitize_input($_POST[$field_name]) : null;
        
        if ($field_value !== null) {
            if (in_array($field_name, $tables_structure['equipos'])) {
                $equipos_fields[] = "$field_name = :$field_name";
                $bind_params[$field_name] = $field_value;
            } elseif (in_array($field_name, $tables_structure['usuarios_equipos'])) {
                $usuarios_equipos_fields[] = "$field_name = :$field_name";
                $bind_params[$field_name] = $field_value;
            }
        }
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

        // Obtener assetname actual desde la sesión
        $old_assetname = $_SESSION['asset_data']['assetname'] ?? $assetname;

        // Actualizar tabla equipos
        $sql_update_equipos = "UPDATE equipos SET
                              assetname = :new_assetname,
                              serial_number = :serial_number,
                              purchase_country = :purchase_country,
                              warranty_enddate = :warranty_enddate,
                              expired = :expired,
                              new_laptop = :new_laptop";

        if (!empty($equipos_fields)) {
            $sql_update_equipos .= ", " . implode(", ", $equipos_fields);
        }

        $sql_update_equipos .= " WHERE assetname = :old_assetname";

        $stmt_update_equipos = $pdo->prepare($sql_update_equipos);
        $stmt_update_equipos->bindValue(':new_assetname', $assetname, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':old_assetname', $old_assetname, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':serial_number', $serial, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':purchase_country', $compra, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':warranty_enddate', $garantia, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':expired', $expira, PDO::PARAM_STR);
        $stmt_update_equipos->bindValue(':new_laptop', $newl, PDO::PARAM_STR);

        foreach ($bind_params as $param => $value) {
            if (in_array($param, $tables_structure['equipos'])) {
                $stmt_update_equipos->bindValue(":$param", $value, PDO::PARAM_STR);
            }
        }

        if (!$stmt_update_equipos->execute()) {
            throw new PDOException("Error al actualizar equipos");
        }

        // Actualizar tabla usuarios_equipos
        $sql_update_usuarios_equipos = "UPDATE usuarios_equipos SET
                                      fk_assetname = :new_assetname,
                                      user_status = :user_status,
                                      last_user = :last_user,
                                      job_title = :job_title,
                                      status_change = :status_change,
                                      cedula = :cedula,
                                      Carnet = :carnet,
                                      LLave = :llave,
                                      Tipo_ID = :tipoID,
                                      asset_status = :asset_status,
                                      asset_observations = :asset_observations,
                                      monitor_status = :monitor_status,
                                      monitor_observations = :monitor_observations,
                                      headset_status = :headset_status,
                                      headset_observations = :headset_observations,
                                      dongle_status = :dongle_status,
                                      dongle_observations = :dongle_observations,
                                      celular_status = :celular_status,
                                      celular_observations = :celular_observations,
                                      SIMcard = :SIMcard";

        if (!empty($usuarios_equipos_fields)) {
            $sql_update_usuarios_equipos .= ", " . implode(", ", $usuarios_equipos_fields);
        }

        $sql_update_usuarios_equipos .= " WHERE fk_assetname = :old_assetname";

        $stmt_update_usuarios_equipos = $pdo->prepare($sql_update_usuarios_equipos);
        $stmt_update_usuarios_equipos->bindValue(':new_assetname', $assetname, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':old_assetname', $old_assetname, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':user_status', $usrStts, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':last_user', $nombre_usuario, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':job_title', $job_title, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':status_change', $stats_change, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':cedula', $cedula, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':carnet', $carnet, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':llave', $llave, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':tipoID', $tipoID, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':asset_status', $asset_status, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':asset_observations', $asset_observations, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':monitor_status', $monitor_status, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':monitor_observations', $monitor_observations, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':headset_status', $headset_status, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':headset_observations', $headset_observations, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':dongle_status', $dongle_status, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':dongle_observations', $dongle_observations, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':celular_status', $celular_status, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':celular_observations', $celular_observations, PDO::PARAM_STR);
        $stmt_update_usuarios_equipos->bindValue(':SIMcard', $SIMcard, PDO::PARAM_STR);

        foreach ($bind_params as $param => $value) {
            if (in_array($param, $tables_structure['usuarios_equipos'])) {
                $stmt_update_usuarios_equipos->bindValue(":$param", $value, PDO::PARAM_STR);
            }
        }

        if (!$stmt_update_usuarios_equipos->execute()) {
            throw new PDOException("Error al actualizar usuarios_equipos");
        }

        // Confirmar transacción
        $pdo->commit();
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Actualizar datos en sesión para PDFs
        $_SESSION['asset_data'] = [
            'assetname' => $assetname,
            'serial_number' => $serial,
            'purchase_country' => $compra,
            'warranty_enddate' => $garantia,
            'expired' => $expira,
            'new_laptop' => $newl,
            'user_status' => $usrStts,
            'last_user' => $nombre_usuario,
            'job_title' => $job_title,
            'status_change' => $stats_change,
            'cedula' => $cedula,
            'Carnet' => $carnet,
            'LLave' => $llave,
            'Tipo_ID' => $tipoID,
            'asset_status' => $asset_status,
            'asset_observations' => $asset_observations,
            'Monitor' => $_POST['Monitor'] ?? null,
            'monitor_status' => $monitor_status,
            'monitor_observations' => $monitor_observations,
            'HeadSet' => $_POST['HeadSet'] ?? null,
            'headset_status' => $headset_status,
            'headset_observations' => $headset_observations,
            'Dongle' => $_POST['Dongle'] ?? null,
            'dongle_status' => $dongle_status,
            'dongle_observations' => $dongle_observations,
            'Celular' => $_POST['Celular'] ?? null,
            'celular_status' => $celular_status,
            'celular_observations' => $celular_observations,
            'SIMcard' => $SIMcard
        ];

        $_SESSION["success"] = "Datos actualizados correctamente";

        // Manejar generación de PDF si fue solicitado
        if (isset($_POST['generate_pdf'])) {
            // Guardar firmas en sesión
            if (isset($_POST['signature'])) {
                $_SESSION['signature_data'] = $_POST['signature'];
            }
            if (isset($_POST['vobo_signature'])) {
                $_SESSION['vobo_signature_data'] = $_POST['vobo_signature'];
            }
            
            // Determinar qué PDF generar
            $redirect = strpos($_SERVER['HTTP_REFERER'], 'salida') !== false 
                ? '../Model/act_indv_sal.php' 
                : '../Model/act_indv_ent.php';
            header("Location: $redirect");
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        }
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $_SESSION["error"] = "Error en la base de datos: " . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    $_SESSION["error"] = "Acceso no válido";
    header("Location: ../index.php");
    exit();
}