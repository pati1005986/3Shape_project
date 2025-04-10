<?php
session_start();

include "../Configuration/Connection.php";
include "../Controller/sanitization.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar una transacción
        $pdo->beginTransaction();
        
        // Obtener el serial number (identificador principal)
        $serial_number = sanitize_input($_POST['serial'] ?? null);
        
        if (!$serial_number) {
            throw new Exception("Serial number is required");
        }
        
        // Obtener el nombre de usuario (último usuario)
        $nombre_usuario = sanitize_input($_POST['nombre_usuario'] ?? null);
        
        // Arrays para almacenar campos y valores
        $equipos_updates = [];
        $equipos_params = [];
        $usuarios_equipos_updates = [];
        $usuarios_equipos_params = [];
        
        // Procesar campos de la tabla equipos
        $equipos_fields = [
            // Campo => [nombre_post, validación_requerida]
            'asset_status' => ['asset_status', false],
            'asset_observations' => ['asset_observations', false],
            'Monitor' => ['Monitor', false],
            'monitor_status' => ['monitor_status', false],
            'monitor_observations' => ['monitor_observations', false],
            'HeadSet' => ['HeadSet', false],
            'headset_status' => ['headset_status', false],
            'headset_observations' => ['headset_observations', false]
        ];
        
        foreach ($equipos_fields as $db_field => $config) {
            $post_field = $config[0];
            $is_required = $config[1];
            
            // Obtener y sanitizar el valor
            $value = sanitize_input($_POST[$post_field] ?? null);
            
            // Verificar si es requerido
            if ($is_required && empty($value)) {
                throw new Exception("El campo $post_field es requerido");
            }
            
            // Si existe el valor, agregarlo a la actualización
            if ($value !== null) {
                $equipos_updates[] = "$db_field = :$db_field";
                $equipos_params[$db_field] = $value;
            }
        }
        
        // Procesar campos de la tabla usuarios_equipos
        $usuarios_equipos_fields = [
            // Campo => [nombre_post, validación_requerida]
            'Dongle' => ['Dongle', false],
            'dongle_status' => ['dongle_status', false],
            'dongle_observations' => ['dongle_observations', false],
            'Celular' => ['Celular', false],
            'celular_status' => ['celular_status', false],
            'celular_observations' => ['celular_observations', false],
            'last_user' => ['nombre_usuario', false]
        ];
        
        foreach ($usuarios_equipos_fields as $db_field => $config) {
            $post_field = $config[0];
            $is_required = $config[1];
            
            // Obtener y sanitizar el valor
            $value = sanitize_input($_POST[$post_field] ?? null);
            
            // Verificar si es requerido
            if ($is_required && empty($value)) {
                throw new Exception("El campo $post_field es requerido");
            }
            
            // Si existe el valor, agregarlo a la actualización
            if ($value !== null) {
                $usuarios_equipos_updates[] = "$db_field = :$db_field";
                $usuarios_equipos_params[$db_field] = $value;
            }
        }
        
        // Actualizar la tabla equipos si hay cambios
        if (!empty($equipos_updates)) {
            $sql_update_equipos = "UPDATE equipos SET " . implode(", ", $equipos_updates) . " WHERE serial_number = :serial_number";
            $stmt_equipos = $pdo->prepare($sql_update_equipos);
            
            // Vincular parámetros
            foreach ($equipos_params as $param => $value) {
                $stmt_equipos->bindValue(":$param", $value);
            }
            $stmt_equipos->bindValue(":serial_number", $serial_number);
            
            // Ejecutar la consulta
            $stmt_equipos->execute();
        }
        
        // Actualizar la tabla usuarios_equipos si hay cambios
        if (!empty($usuarios_equipos_updates)) {
            // Obtener el asset_name correspondiente al serial_number
            $stmt_get_assetname = $pdo->prepare("SELECT assetname FROM equipos WHERE serial_number = :serial_number");
            $stmt_get_assetname->bindValue(":serial_number", $serial_number);
            $stmt_get_assetname->execute();
            $asset_name = $stmt_get_assetname->fetchColumn();
            
            if (!$asset_name) {
                throw new Exception("No se encontró el asset_name para este serial");
            }
            
            $sql_update_usuarios_equipos = "UPDATE usuarios_equipos SET " . implode(", ", $usuarios_equipos_updates) . " WHERE fk_assetname = :asset_name";
            $stmt_usuarios_equipos = $pdo->prepare($sql_update_usuarios_equipos);
            
            // Vincular parámetros
            foreach ($usuarios_equipos_params as $param => $value) {
                $stmt_usuarios_equipos->bindValue(":$param", $value);
            }
            $stmt_usuarios_equipos->bindValue(":asset_name", $asset_name);
            
            // Ejecutar la consulta
            $stmt_usuarios_equipos->execute();
        }
        
        // Confirmar la transacción
        $pdo->commit();
        
        // Mensaje de éxito
        $_SESSION["success"] = "Estados de activos actualizados correctamente.";
        
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        $_SESSION["error"] = "Error al actualizar los estados: " . $e->getMessage();
    }
    
    // Redirigir al usuario
    header("Location: ../index.php");
    exit();
} else {
    $_SESSION["error"] = "Método de acceso no válido";
    header("Location: ../index.php");
    exit();
}