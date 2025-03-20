<?php
$logPath = $_SERVER['DOCUMENT_ROOT'].'/3Shape_project/Model/Logs/session_messages.json';

// Estructura base vacía
$emptyLog = [
    "success" => [],
    "error" => [],
    "error_message" => [],
    "warnings" => []
];

try {
    // Escribir estructura vacía al archivo
    file_put_contents($logPath, json_encode($emptyLog, JSON_PRETTY_PRINT));
    echo "Records deleted successfully";
} catch (Exception $e) {
    http_response_code(500);
    echo "Error deleting records" . $e->getMessage();
}