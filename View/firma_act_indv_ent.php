<?php
session_start();

// Check if there is asset data in the session
if (!isset($_SESSION['asset_data'])) {
    // If no data, redirect to the main page
    header("Location: /3Shape_project/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Capture - Delivery Certificate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .signature-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 30px;
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .signature-section {
            margin-bottom: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .signature-section h2 {
            margin-top: 0;
            color: #3498db;
            font-size: 1.4rem;
        }

        .signature-pad-container {
            position: relative;
            margin: 20px 0;
        }

        canvas {
            display: block;
            width: 100%;
            height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
        }

        .signature-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .signature-instructions {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .signature-container {
                margin: 1rem;
                padding: 15px;
            }

            .signature-actions {
                flex-direction: column;
                gap: 10px;
            }

            .buttons {
                justify-content: space-between;
            }
        }
    </style>
</head>

<body>
    <div class="signature-container">
        <h1>Delivery Certificate - Signature Capture</h1>

        <?php
        $max_colaboradores = min(count($_SESSION['asset_data']), 10);
        for ($i = 0; $i < $max_colaboradores; $i++): ?>
            <div class="signature-section">
                <h2>Employee Signature - <?php echo $_SESSION['asset_data'][$i]['last_user']; ?></h2>
                <p class="signature-instructions">Please sign in the area below using your mouse or finger on touch devices</p>
                <div class="signature-pad-container">
                    <canvas id="signature-pad-<?php echo $i; ?>"></canvas>
                </div>
                <div class="buttons">
                    <button id="clear-<?php echo $i; ?>" class="btn btn-secondary">Clear Signature</button>
                    <button id="undo-<?php echo $i; ?>" class="btn btn-danger">Undo</button>
                </div>
            </div>
        <?php endfor; ?>

        <div class="signature-section">
            <h2>IT Department Approval</h2>
            <p class="signature-instructions">Please sign in the area below as approval from the IT department</p>
            <div class="signature-pad-container">
                <canvas id="vobo-signature-pad"></canvas>
            </div>
            <div class="buttons">
                <button id="clear-vobo" class="btn btn-secondary">Clear Signature</button>
                <script>
                    document.getElementById('clear-vobo').addEventListener('click', () => {
                        voboSignaturePad.clear();
                    });
                </script>

                <button id="undo-vobo" class="btn btn-danger">Undo</button>
                <script>
                    document.getElementById('undo-vobo').addEventListener('click', () => {
                        const data = voboSignaturePad.toData();
                        if (data.length > 0) {
                            data.pop(); // remove the last dot or line
                            voboSignaturePad.fromData(data);
                        }
                    });
                </script>
            </div>
        </div>

        <div class="signature-actions">
            <button id="cancel" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
            <button id="save" class="btn btn-primary">Save Signatures and Generate Certificate</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        // Initialize both signature pads with improved configuration
        const signaturePads = [];
        for (let i = 0; i < <?php echo $max_colaboradores; ?>; i++) {
            const canvas = document.getElementById(`signature-pad-${i}`);
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(0, 0, 0)',
                velocityFilterWeight: 0.7,
                minWidth: 0.5,
                maxWidth: 2.5,
                throttle: 16
            });
            signaturePads.push(signaturePad);

            // Buttons for clearing
            document.getElementById(`clear-${i}`).addEventListener('click', () => {
                signaturePad.clear();
            });

            // Buttons for undoing
            document.getElementById(`undo-${i}`).addEventListener('click', () => {
                const data = signaturePad.toData();
                if (data.length > 0) {
                    data.pop(); // remove the last dot or line
                    signaturePad.fromData(data);
                }
            });
        }

        const voboCanvas = document.getElementById('vobo-signature-pad');
        const voboSignaturePad = new SignaturePad(voboCanvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: 'rgb(0, 0, 0)',
            velocityFilterWeight: 0.7,
            minWidth: 0.5,
            maxWidth: 2.5,
            throttle: 16
        });

        // Function to adjust canvas on high-resolution screens
        function resizeCanvas(canvas, signaturePad) {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            // Redraw existing signature if there is one
            if (!signaturePad.isEmpty()) {
                const data = signaturePad.toData();
                signaturePad.clear();
                signaturePad.fromData(data);
            } else {
                signaturePad.clear();
            }
        }

        // Adjust canvas on load and resize
        window.addEventListener('load', () => {
            for (let i = 0; i < signaturePads.length; i++) {
                resizeCanvas(document.getElementById(`signature-pad-${i}`), signaturePads[i]);
            }
            resizeCanvas(voboCanvas, voboSignaturePad);
        });

        window.addEventListener('resize', () => {
            for (let i = 0; i < signaturePads.length; i++) {
                resizeCanvas(document.getElementById(`signature-pad-${i}`), signaturePads[i]);
            }
            resizeCanvas(voboCanvas, voboSignaturePad);
        });

        // Validate before sending
        function validateSignatures() {
            let allFilled = true;
            for (let i = 0; i < signaturePads.length; i++) {
                if (signaturePads[i].isEmpty()) {
                    allFilled = false;
                    break;
                }
            }

            return true;
        }

        // Button to save both signatures
        document.getElementById('save').addEventListener('click', () => {
            if (!validateSignatures()) return;

            // Show processing message
            const saveBtn = document.getElementById('save');
            const originalText = saveBtn.textContent;
            saveBtn.textContent = 'Processing...';
            saveBtn.disabled = true;

            const signatures = signaturePads.map(pad => pad.toDataURL('image/png'));
            const voboDataURL = voboSignaturePad.toDataURL('image/png');

            // Send all signatures to the server
            fetch('../Controller/guardar_firma_entrada.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        signatures: signatures,
                        vobo_signature: voboDataURL
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error in server response');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Redirect to PDF generator
                        window.location.href = '/3Shape_project/Controller/act_indv_ent.php';
                    } else {
                        throw new Error(data.message || 'Error saving signatures');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Error sending signatures: " + error.message);
                    saveBtn.textContent = originalText;
                    saveBtn.disabled = false;
                });
        });

        // Prevent accidental navigation
        window.addEventListener('beforeunload', (e) => {
            if (!signaturePads[0].isEmpty() || !voboSignaturePad.isEmpty()) {
                e.preventDefault();
                e.returnValue = 'You have unsaved signatures. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    </script>
</body>

</html>