<?php
// File: Model/get_notification_count.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');

// Get the last check timestamp from the request
$data = json_decode(file_get_contents('php://input'), true);
$lastCheck = isset($data['lastCheck']) ? (int)$data['lastCheck'] : 0;

// Path to the notifications JSON file
$jsonFilePath = '../Model/Logs/session_messages.json';
$notificationCount = 0;

// Check if file exists
if (file_exists($jsonFilePath)) {
    // Read the JSON file
    $logData = json_decode(file_get_contents($jsonFilePath), true);
    
    // Count all types of messages
    foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
        if (!empty($logData[$type])) {
            foreach ($logData[$type] as $entry) {
                // If the entry has a timestamp, check if it's newer than lastCheck
                if (is_array($entry) && isset($entry['timestamp'])) {
                    $entryTime = strtotime($entry['timestamp']);
                    if ($entryTime * 1000 > $lastCheck) { // Convert to milliseconds
                        $notificationCount++;
                    }
                } else {
                    // If no timestamp, count it if this is the first check
                    if ($lastCheck == 0) {
                        $notificationCount++;
                    }
                }
            }
        }
    }
}

// Return the count as JSON
echo json_encode([
    'count' => $notificationCount,
    'timestamp' => time() * 1000 // Current time in milliseconds
]);
?>