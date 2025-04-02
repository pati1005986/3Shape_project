<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['signature']) || !isset($_POST['vobo_signature'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan firmas']);
        exit;
    }

    // Guardar firmas directamente en las variables de sesión que espera act_indv_ent.php
    $_SESSION['signature_data'] = $_POST['signature'];
    $_SESSION['vobo_signature_data'] = $_POST['vobo_signature'];

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido']);
?>