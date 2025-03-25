<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'CSRF token validation failed';
        header('Location: index_admin.php');
        exit();
    }

    // 2. Validar campos seleccionados
    if (empty($_POST['removemfields'])) {
        $_SESSION['error'] = 'No fields selected for hiding';
        header('Location: index_admin.php');
        exit();
    }

    $fieldsToHide = $_POST['removemfields'];
    
    // 3. Validar que hay campos visibles para ocultar
    if (!isset($_SESSION['selected_fields']) || empty($_SESSION['selected_fields'])) {
        $_SESSION['error'] = 'There are no visible fields to hide';
        header('Location: index_admin.php');
        exit();
    }

    // 4. Filtrar solo los campos que están actualmente visibles
    $validFieldsToHide = array_intersect($fieldsToHide, $_SESSION['selected_fields']);
    
    if (empty($validFieldsToHide)) {
        $_SESSION['error'] = 'None of the selected fields are currently visible in the table';
        header('Location: index_admin.php');
        exit();
    }

    // 5. Actualizar las sesiones
    // Campos ocultos (añadir los nuevos)
    $_SESSION['hidden_fields'] = array_unique(array_merge(
        $_SESSION['hidden_fields'] ?? [],
        $validFieldsToHide
    ));
    
    // Campos visibles (remover los ocultados)
    $_SESSION['selected_fields'] = array_diff($_SESSION['selected_fields'], $validFieldsToHide);
    
    // 6. Registrar éxito
    $logMessage = 'Fields hidden from table: ' . implode(', ', $validFieldsToHide);
    $logFile = '../Model/Logs/session_messages.json';
    $logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
    $logs['success'][] = [
        'message' => $logMessage,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    file_put_contents($logFile, json_encode($logs));
    
    $_SESSION['success'] = 'Fields hidden successfully: ' . implode(', ', $validFieldsToHide);
    header('Location: index_admin.php');
    exit();
}
?>