<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'CSRF token validation failed';
        header('Location: index_admin.php');
        exit();
    }

    // Validar campos seleccionados
    if (empty($_POST['removemfields'])) {
        $_SESSION['error'] = 'No fields selected for hiding';
        header('Location: index_admin.php');
        exit();
    }

    $fieldsToHide = $_POST['removemfields'];
    
    // Obtener los campos actualmente ocultos (si existen)
    $hiddenFields = isset($_SESSION['hidden_fields']) ? $_SESSION['hidden_fields'] : [];
    
    // Combinar los campos existentes con los nuevos
    $allHiddenFields = array_unique(array_merge($hiddenFields, $fieldsToHide));
    
    // Guardar en sesión
    $_SESSION['hidden_fields'] = $allHiddenFields;
    
    // Actualizar los campos seleccionados para mostrar
    if (isset($_SESSION['selected_fields'])) {
        $_SESSION['selected_fields'] = array_diff($_SESSION['selected_fields'], $fieldsToHide);
    }
    
    // Registrar éxito
    $logMessage = 'Fields hidden from table: ' . implode(', ', $fieldsToHide);
    $logFile = '../Model/Logs/session_messages.json';
    $logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
    $logs['success'][] = [
        'message' => $logMessage,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    file_put_contents($logFile, json_encode($logs));
    
    $_SESSION['success'] = 'Fields hidden from table successfully';
    header('Location: index_admin.php');
    exit();
}
?>