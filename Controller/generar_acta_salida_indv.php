<?php
session_start();

// Este archivo sirve como puente para redirigir al usuario al generador de PDF
// después de guardar las firmas

// Redirigir al generador de PDF
header("Location: /3Shape_project/Model/prcsr_indv_sal.php");
exit();
?>
