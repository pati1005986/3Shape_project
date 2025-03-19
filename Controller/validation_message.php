<?php
// Función optimizada para mostrar alertas

function displayAlert($type, $content, $isList = false) {
    ob_start();
    ?>
    <div class="alert alert-<?= $type ?> animate-slide-in">
        <div class="alert-progress"></div>
        <?php if($isList && is_array($content)): ?>
            <div class="alert-scroll-container">
                <ul>
                    <?php foreach($content as $warning): ?>
                        <li><?= htmlspecialchars($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <?= htmlspecialchars($content) ?>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

$alerts = [];
$tempMessages = []; // Array to temporarily store session messages


// Manejar todos los tipos de mensajes
if(isset($_COOKIE['error_message'])) {
    $alerts[] = displayAlert('danger', $_COOKIE['error_message']);
    setcookie('error_message', '', time() - 3600, '/');
    $tempMessages['error_message'] = $_COOKIE['error_message']; // Store message before unsetting
    unset($_COOKIE['error_message']);
}

if(isset($_SESSION['error'])) {
    $alerts[] = displayAlert('danger', $_SESSION['error']);
    $tempMessages['error'] = $_SESSION['error']; // Store message before unsetting
    unset($_SESSION['error']);

}

if(isset($_SESSION['success'])) {
    $alerts[] = displayAlert('success', $_SESSION['success']);
    $tempMessages['success'] = $_SESSION['success']; // Store message before unsetting
    unset($_SESSION['success']);

}

if(!empty($_SESSION['warnings'])) {
    $alerts[] = displayAlert('warning', $_SESSION['warnings'], true);
    $tempMessages['warnings'] = $_SESSION['warnings']; // Store message before unsetting
    unset($_SESSION['warnings']);

}

$logPath = $_SERVER['DOCUMENT_ROOT'].'/3Shape_project/Model/Logs/session_messages.json';

// Leer registro existente
$existingLog = [];
if (file_exists($logPath)) {
    $existingLog = json_decode(file_get_contents($logPath), true) ?: [];
}

// Fusionar nuevos mensajes con el registro
foreach ($tempMessages as $type => $content) {
    if ($type === 'warnings') {
        // Fusionar arrays de advertencias
        $existingLog[$type] = array_merge($existingLog[$type] ?? [], $content);
    } else {
        // Agregar mensajes simples con timestamp
        $existingLog[$type][] = [
            'message' => $content,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Guardar registro actualizado
file_put_contents($logPath, json_encode($existingLog, JSON_PRETTY_PRINT));
// Mostrar todas las alertas acumuladas

echo implode('', $alerts);
?>

<style>
/* Animaciones CSS optimizadas */
.alert {
    position: relative;
    margin: 1rem 0;
    padding: 1rem;
    border-radius: 4px;
    overflow: hidden;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.animate-slide-in {
    animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1), fadeIn 0.5s ease;
}

.alert.fade-out {
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
}

.alert-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: rgba(0,0,0,0.1);
    width: 100%;
    transform-origin: left;
    animation: progress 5s linear forwards;
}

@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes progress {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}

/* Mejorar rendimiento de animaciones */
@media (prefers-reduced-motion: no-preference) {
    .alert {
        will-change: transform, opacity;
    }
}

.alert-scroll-container {
    max-height: 200px; /* Altura máxima equivalente a ~10 elementos */
    overflow-y: auto;
    margin: 8px 0;
    padding-right: 5px;
}

/* Estilizar scrollbar para mejor apariencia */
.alert-scroll-container::-webkit-scrollbar {
    width: 6px;
}

.alert-scroll-container::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.1);
    border-radius: 3px;
}

.alert-scroll-container::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 3px;
}

.alert-scroll-container::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.3);
}

.alert-scroll-container ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.alert-scroll-container li {
    padding: 4px 0;
    line-height: 1.4;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.alert-scroll-container li:last-child {
    border-bottom: none;
}
</style>


<script>
// Función asíncrona para desvanecer alertas
async function autoDismissAlert(alertElement, duration = 5000) {
    // Esperar el tiempo de duración
    await new Promise(resolve => setTimeout(resolve, duration));
    
    // Añadir clase para animación de salida
    alertElement.classList.add('fade-out');
    
    // Esperar a que termine la transición
    await new Promise(resolve => {
        alertElement.addEventListener('transitionend', resolve, {once: true});
    });
    
    // Eliminar el elemento del DOM
    alertElement.remove();
}

// Inicializar para todas las alertas al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert').forEach(alert => {
        autoDismissAlert(alert);
    });
});
</script>
