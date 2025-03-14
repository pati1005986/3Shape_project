<?php

if (isset($_COOKIE['error_message'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_COOKIE['error_message']) . '</div>';
    setcookie('error_message', '', time() - 4600, '/'); // Eliminar la cookie
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']); // Limpia el mensaje después de mostrarlo
}

if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['warnings']) && count($_SESSION['warnings']) > 0) {
    echo '<div class="alert alert-warning">';
    echo '<ul>';
    $maxDisplayWarnings = 10; // Límite máximo de advertencias a mostrar
    $displayedWarnings = array_slice($_SESSION['warnings'], 0, $maxDisplayWarnings); // Limita las advertencias a mostrar

    foreach ($displayedWarnings as $warning) {
        echo '<li>' . htmlspecialchars($warning) . '</li>';
    }

    if (count($_SESSION['warnings']) > $maxDisplayWarnings) {
        echo '<li>And ' . (count($_SESSION['warnings']) - $maxDisplayWarnings) . ' more warnings...</li>';
    }
    echo '</ul>';
    echo '</div>';
    unset($_SESSION['warnings']); // Limpia las advertencias después de mostrarlas
}

?>