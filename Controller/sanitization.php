<?php
// General function to sanitize input data
function sanitize_input($data) {
    // Trim whitespace
    $data = trim($data);
    // Remove HTML tags
    $data = strip_tags($data);
    // Escape special characters
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>
