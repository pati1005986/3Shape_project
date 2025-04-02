<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que se recibieron todas las firmas
    if (!isset($_POST['signature']) || !isset($_POST['vobo_signature']) || 
        !isset($_POST['it_signature']) || !isset($_POST['admin_signature'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan firmas']);
        exit;
    }

    // Configurar directorio de firmas
    $signatureDir = __DIR__ . '/../signatures/';
    if (!file_exists($signatureDir)) {
        mkdir($signatureDir, 0755, true);
    }

    // Guardar las 4 firmas
    $signatureFilename = 'firma_' . uniqid() . '.png';
    $voboSignatureFilename = 'vobo_' . uniqid() . '.png';
    $itSignatureFilename = 'it_' . uniqid() . '.png';
    $adminSignatureFilename = 'admin_' . uniqid() . '.png';
    
    $signaturePath = $signatureDir . $signatureFilename;
    $voboSignaturePath = $signatureDir . $voboSignatureFilename;
    $itSignaturePath = $signatureDir . $itSignatureFilename;
    $adminSignaturePath = $signatureDir . $adminSignatureFilename;

    // Decodificar y guardar las imágenes
    file_put_contents($signaturePath, base64_decode(preg_replace('#^data:image/png;base64,#i', '', $_POST['signature'])));
    file_put_contents($voboSignaturePath, base64_decode(preg_replace('#^data:image/png;base64,#i', '', $_POST['vobo_signature'])));
    file_put_contents($itSignaturePath, base64_decode(preg_replace('#^data:image/png;base64,#i', '', $_POST['it_signature'])));
    file_put_contents($adminSignaturePath, base64_decode(preg_replace('#^data:image/png;base64,#i', '', $_POST['admin_signature'])));

    // Guardar rutas en sesión
    $_SESSION['signature'] = $signaturePath;
    $_SESSION['vobo_signature'] = $voboSignaturePath;
    $_SESSION['it_signature'] = $itSignaturePath;
    $_SESSION['admin_signature'] = $adminSignaturePath;

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido']);
?>