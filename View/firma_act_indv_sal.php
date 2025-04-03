<?php
session_start();

// Check if there is asset data in the session
if (!isset($_SESSION['asset_data'])) {
    // If no data, redirect to main page
    header("Location: /3Shape_project/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Capture - Exit Form</title>
    <style>
    body {
        font-family: 'Roboto', Arial, sans-serif;
        background-color: #f0f2f5;
        margin: 0;
        padding: 0;
        color: #333;
        line-height: 1.6;
    }

    .signature-container {
        max-width: 850px;
        margin: 2rem auto;
        padding: 35px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .signature-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }

    h1 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 30px;
        font-weight: 700;
        position: relative;
        padding-bottom: 15px;
    }

    h1:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: #3498db;
        border-radius: 2px;
    }

    .signature-section {
        margin-bottom: 40px;
        padding: 25px;
        background-color: #f9f9f9;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .signature-section:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
        border-color: #d0d0d0;
    }

    .signature-section h2 {
        margin-top: 0;
        color: #3498db;
        font-size: 1.4rem;
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .signature-section h2:before {
        content: '✓';
        margin-right: 10px;
        background: #3498db;
        color: white;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .signature-pad-container {
        position: relative;
        margin: 20px 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    canvas {
        display: block;
        width: 100%;
        height: 200px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        transition: border 0.3s ease;
    }

    canvas:hover {
        border-color: #3498db;
    }

    .signature-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .buttons {
        display: flex;
        gap: 12px;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s ease;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .btn-primary {
        background-color: #3498db;
        color: white;
        box-shadow: 0 4px 6px rgba(52, 152, 219, 0.2);
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(52, 152, 219, 0.25);
    }

    .btn-secondary {
        background-color: #95a5a6;
        color: white;
        box-shadow: 0 4px 6px rgba(149, 165, 166, 0.2);
    }

    .btn-secondary:hover {
        background-color: #7f8c8d;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(149, 165, 166, 0.25);
    }

    .btn-danger {
        background-color: #e74c3c;
        color: white;
        box-shadow: 0 4px 6px rgba(231, 76, 60, 0.2);
    }

    .btn-danger:hover {
        background-color: #c0392b;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(231, 76, 60, 0.25);
    }

    .signature-instructions {
        font-size: 0.9rem;
        color: #7f8c8d;
        margin-top: 10px;
        margin-bottom: 15px;
        padding: 10px;
        background: rgba(52, 152, 219, 0.05);
        border-left: 3px solid #3498db;
        border-radius: 0 4px 4px 0;
    }

    @media (max-width: 768px) {
        .signature-container {
            margin: 1rem;
            padding: 20px;
        }

        .signature-section {
            padding: 18px;
        }

        .signature-actions {
            flex-direction: column;
            gap: 15px;
        }

        .buttons {
            justify-content: space-between;
        }
        
        .btn {
            padding: 10px 16px;
            font-size: 14px;
        }
    }
    
    /* Animation for saving button */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
        100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
    }
    
    #save:hover {
        animation: pulse 1.5s infinite;
    }
</style>
</head>

<body>
    <div class="signature-container">
        <h1>Exit Form - Signature Capture</h1>


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
            <h2>Immediate Supervisor Signature</h2>
            <p class="signature-instructions">Please sign in the area below as approval from the employee's immediate supervisor</p>
            <div class="signature-pad-container">
                <canvas id="vobo-signature-pad"></canvas>
            </div>
            <div class="buttons">
                <button id="clear-vobo" class="btn btn-secondary">Clear Signature</button>
                <button id="undo-vobo" class="btn btn-danger">Undo</button>
                <script>
                    document.getElementById('clear-vobo').addEventListener('click', () => {
                        voboSignaturePad.clear();
                    });
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

        <div class="signature-section">
            <h2>IT Department Approval</h2>
            <p class="signature-instructions">Please sign in the area below as approval from the IT department</p>
            <div class="signature-pad-container">
                <canvas id="it-signature-pad"></canvas>
            </div>
            <div class="buttons">
                <button id="clear-it" class="btn btn-secondary">Clear Signature</button>
                <button id="undo-it" class="btn btn-danger">Undo</button>

                <script>
                    document.getElementById('clear-it').addEventListener('click', () => {
                        itSignaturePad.clear();
                    });
                    document.getElementById('undo-it').addEventListener('click', () => {
                        const data = itSignaturePad.toData();
                        if (data.length > 0) {
                            data.pop(); // remove the last dot or line
                            itSignaturePad.fromData(data);
                        }
                    });
                </script>
            </div>
        </div>

        <div class="signature-section">
            <h2>Administrative Approval</h2>
            <p class="signature-instructions">Please sign in the area below as approval from the administrative department</p>
            <div class="signature-pad-container">
                <canvas id="admin-signature-pad"></canvas>
            </div>
            <div class="buttons">
                <button id="clear-admin" class="btn btn-secondary">Clear Signature</button>
                <button id="undo-admin" class="btn btn-danger">Undo</button>
                <script>
                    document.getElementById('clear-admin').addEventListener('click', () => {
                        adminSignaturePad.clear();
                    });
                    document.getElementById('undo-admin').addEventListener('click', () => {
                        const data = adminSignaturePad.toData();
                        if (data.length > 0) {
                            data.pop(); // remove the last dot or line
                            adminSignaturePad.fromData(data);
                        }
                    });
                </script>
            </div>
        </div>

        <div class="signature-actions">
            <button id="cancel" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
            <button id="save" class="btn btn-primary">Save Signatures and Generate Form</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    // Dynamically initialize signature pads for employees
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

        // Configure events for each pad
        document.getElementById(`clear-${i}`).addEventListener('click', () => {
            signaturePad.clear();
        });

        document.getElementById(`undo-${i}`).addEventListener('click', () => {
            const data = signaturePad.toData();
            if (data.length > 0) {
                data.pop();
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

    // Initialize signature pads for IT and Admin
    const itCanvas = document.getElementById('it-signature-pad');
    const itSignaturePad = new SignaturePad(itCanvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)',
        velocityFilterWeight: 0.7,
        minWidth: 0.5,
        maxWidth: 2.5,
        throttle: 16
    });

    const adminCanvas = document.getElementById('admin-signature-pad');
    const adminSignaturePad = new SignaturePad(adminCanvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)',
        velocityFilterWeight: 0.7,
        minWidth: 0.5,
        maxWidth: 2.5,
        throttle: 16
    });

    // Function to resize the canvas
    function resizeCanvas(canvas, signaturePad) {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);

        // Redraw the existing signature if there is one
        if (!signaturePad.isEmpty()) {
            const data = signaturePad.toData();
            signaturePad.clear();
            signaturePad.fromData(data);
        } else {
            signaturePad.clear();
        }
    }

    // Adjust canvas size on load
    window.addEventListener('load', () => {
        for (let i = 0; i < signaturePads.length; i++) {
            resizeCanvas(document.getElementById(`signature-pad-${i}`), signaturePads[i]);
        }
        resizeCanvas(voboCanvas, voboSignaturePad);
        resizeCanvas(itCanvas, itSignaturePad);
        resizeCanvas(adminCanvas, adminSignaturePad);
    });

    // Adjust canvas size on resize
    window.addEventListener('resize', () => {
        for (let i = 0; i < signaturePads.length; i++) {
            resizeCanvas(document.getElementById(`signature-pad-${i}`), signaturePads[i]);
        }
        resizeCanvas(voboCanvas, voboSignaturePad);
        resizeCanvas(itCanvas, itSignaturePad);
        resizeCanvas(adminCanvas, adminSignaturePad);
    });

    // Events to clear signatures are already defined in the HTML inline scripts, but keeping them here for completeness
    document.getElementById('clear-vobo').addEventListener('click', () => {
        voboSignaturePad.clear();
    });

    document.getElementById('clear-it').addEventListener('click', () => {
        itSignaturePad.clear();
    });

    document.getElementById('clear-admin').addEventListener('click', () => {
        adminSignaturePad.clear();
    });

    // Events to undo strokes are already defined in the HTML inline scripts, but keeping them here for completeness
    document.getElementById('undo-vobo').addEventListener('click', () => {
        const data = voboSignaturePad.toData();
        if (data.length > 0) {
            data.pop();
            voboSignaturePad.fromData(data);
        }
    });

    document.getElementById('undo-it').addEventListener('click', () => {
        const data = itSignaturePad.toData();
        if (data.length > 0) {
            data.pop();
            itSignaturePad.fromData(data);
        }
    });

    document.getElementById('undo-admin').addEventListener('click', () => {
        const data = adminSignaturePad.toData();
        if (data.length > 0) {
            data.pop();
            adminSignaturePad.fromData(data);
        }
    });

    // Function to validate signatures
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

    // Event to save signatures
    document.getElementById('save').addEventListener('click', () => {
        if (!validateSignatures()) return;

        const saveBtn = document.getElementById('save');
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Processing...';
        saveBtn.disabled = true;

        const colaboradoresDataURL = signaturePads.map(pad => pad.toDataURL('image/png'));
        const voboDataURL = voboSignaturePad.toDataURL('image/png');
        const itDataURL = itSignaturePad.toDataURL('image/png');
        const adminDataURL = adminSignaturePad.toDataURL('image/png');

        // Send the 4 signatures to the server
        fetch('../Controller/guardar_firma_salida.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                signatures: colaboradoresDataURL,
                vobo_signature: voboDataURL,
                it_signature: itDataURL,
                admin_signature: adminDataURL
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
                    window.location.href = '/3Shape_project/Controller/act_indv_sal.php';
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
        // Check if any signature is not empty
        let hasSignatures = false;
        
        // Check employee signatures
        for (let i = 0; i < signaturePads.length; i++) {
            if (!signaturePads[i].isEmpty()) {
                hasSignatures = true;
                break;
            }
        }
        
        // Check other signatures
        if (!hasSignatures && (!voboSignaturePad.isEmpty() || !itSignaturePad.isEmpty() || !adminSignaturePad.isEmpty())) {
            hasSignatures = true;
        }
        
        if (hasSignatures) {
            e.preventDefault();
            e.returnValue = 'You have unsaved signatures. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
</script>
</body>

</html>