<?php
session_start();

// Verificar si hay IDs en la URL
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    // Si no hay IDs, redirigir a la página principal
    header("Location: /3Shape_project/index.php");
    exit();
}

// Almacenar los IDs en la sesión para uso posterior
$_SESSION['selected_ids'] = $_GET['ids'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Captura de Firma Para Acta de Entrada</title>
    <link rel="stylesheet" href="../View/Css/dark-mode.css">
    <link rel="stylesheet" href="../Configuration/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
    <style>
       .signature-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .signature-section {
            margin-bottom: 30px;
        }

        .signature-section h3 {
            margin-bottom: 10px;
            color: #333;
        }

        #signature-pad, #vobo-signature-pad {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 20px 0;
            width: 100%;
            background-color: #f9f9f9;
        }

        .signature-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="signature-container">
        <h1 class="text-center mb-4">Firma para Acta de Entrada</h1>
        
        <!-- Primera sección de firma -->
        <div class="signature-section">
            <h2>Firma del Colaborador</h2>
            <div>
                <canvas id="signature-pad" width="400" height="200"></canvas>
            </div>
            <div class="buttons">
                <button id="clear" class="btn btn-secondary">Limpiar</button>
            </div>
        </div>
        
        <!-- Segunda sección de firma para Vo.Bo. de IT -->
        <div class="signature-section">
            <h2>Vo.Bo. Departamento de IT</h2>
            <div>
                <canvas id="vobo-signature-pad" width="400" height="200"></canvas>
            </div>
            <div class="buttons">
                <button id="clear-vobo" class="btn btn-secondary">Limpiar</button>
            </div>
        </div>
        
        <!-- Botón de guardar común para ambas firmas -->
        <div class="signature-actions">
            <button id="save" class="btn btn-primary">Guardar Firmas y Generar Acta</button>
            <a href="/3Shape_project/index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        // Inicializar ambos pads de firma
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);
        
        const voboCanvas = document.getElementById('vobo-signature-pad');
        const voboSignaturePad = new SignaturePad(voboCanvas);
        
        // Función para ajustar canvas en pantallas de alta resolución
        function resizeCanvas(canvas, signaturePad) {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }
        
        window.addEventListener('resize', () => resizeCanvas(canvas, signaturePad));
        window.addEventListener('resize', () => resizeCanvas(voboCanvas, voboSignaturePad));
        resizeCanvas(canvas, signaturePad);
        resizeCanvas(voboCanvas, voboSignaturePad);

        // Botones para limpiar
        document.getElementById('clear').addEventListener('click', () => {
            signaturePad.clear();
        });
        
        document.getElementById('clear-vobo').addEventListener('click', () => {
            voboSignaturePad.clear();
        });

        // Botón para guardar ambas firmas
        document.getElementById('save').addEventListener('click', () => {
            if (signaturePad.isEmpty()) {
                alert("Por favor, proporcione la firma del colaborador.");
                return;
            }
            
            if (voboSignaturePad.isEmpty()) {
                alert("Por favor, proporcione la firma de Vo.Bo. del Departamento de IT.");
                return;
            }
            
            const dataURL = signaturePad.toDataURL('image/png');
            const voboDataURL = voboSignaturePad.toDataURL('image/png');
            
            // Enviar ambas firmas al servidor
            fetch('../Controller/guardar_firma_entrada.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `signature=${encodeURIComponent(dataURL)}&vobo_signature=${encodeURIComponent(voboDataURL)}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Redirigir al generador de PDF con los IDs originales
                    const urlParams = new URLSearchParams(window.location.search);
                    window.location.href = '/3Shape_project/Controller/prcsr_indv_ent.php?' + urlParams.toString();
                } else {
                    alert("Error al guardar las firmas: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Error al enviar las firmas");
            });
        });
    </script>
</body>
</html>