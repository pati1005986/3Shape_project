window.onload = function() {
    // Crear botón dinámicamente
    const procesarSeleccionadosBtn = `
    <button id="procesarSeleccionados" class="shadow btn btn-secondary my-2" title="Process Selected Items">
    <i class="fas fa-cog"></i> Process Selected
    </button>`
    const table = document.querySelector('table');
    if (table) {
        table.insertAdjacentHTML('beforebegin', procesarSeleccionadosBtn);
    } else {
        console.error('dont found a table to the DOM.');
        return;
    }
    // Variable global para almacenar los parámetros seleccionados
    let parametros = "";

    // Manejador de clic del botón
    document.getElementById('procesarSeleccionados').addEventListener('click', function() {
        // Añadir efecto de spinning al icono
        let icon = this.querySelector('i');
        icon.classList.add('spin');

        // Detener la animación después de 2 segundos
        setTimeout(() => {
            icon.classList.remove('spin');
        }, 2000);
        
        const checkboxesSeleccionados = document.querySelectorAll(
            'input[name="selected_assets[]"]:checked');
        // Validar que haya al menos un checkbox seleccionado
        if (checkboxesSeleccionados.length === 0) {
            alert('Please, select at least one item to process.');
            return;
        }

        // Construir lista de parámetros seleccionados
        parametros = Array.from(checkboxesSeleccionados)
            .map(checkbox => 'ids[]=' + encodeURIComponent(checkbox.value))
            .join('&');

        // Mostrar el modal
        const modal = document.getElementById('modalConfirmacion');
        const modalMensaje = document.getElementById('modalMensaje');
        modalMensaje.innerHTML =
        'Do you want to process:<br>' +
        'Option 1 - Generate an output record.<br>' +
        'Option 2 - Create an input record.<br>' +
        'Option 3 - Asset Information.<br>' +
        'Option 4 - Generate QR Codes.';
        modal.style.display = 'block';

        // Depuración en consola
        console.log('show modal');
        console.log('Parameters Select:', parametros);
    });

    // Manejadores de botones del modal
    document.getElementById('btnAceptar').onclick = function(event) {
        event.preventDefault(); // Prevenir comportamiento por defecto
        if (!parametros) {
            console.error("Error: Parameters not defined.");
            return;
        }
        // Redirigir a la URL de la opción 1, Firma de acta de salida
        const urlFirmaSalida = `${window.location.origin}/3Shape_project/Model/prcsr_indv_sal.php?${parametros}`;
        console.log(`Redirigiendo a: ${urlFirmaSalida}`);
        window.location.href = urlFirmaSalida;
    };

document.getElementById('btnCancelar').onclick = function(event) {
    event.preventDefault(); // Prevenir comportamiento por defecto
    if (!parametros) {
        console.error("Error: Parameters not defined.")
        return;
    }
    // Redirigir a la sección de firma de acta de entrada
    const urlFirma =
        `${window.location.origin}/3Shape_project/Model/prcsr_indv_ent.php?${parametros}`;
    console.log(`Redirigiendo a: ${urlFirma}`);
    window.location.href = urlFirma;

        // After signature is completed, redirect to the PDF document
        // This part will need to be implemented in the firma_act_indv_ent.php file
    };

    document.getElementById('btnOpcion3').onclick = function(event) {
        event.preventDefault(); // Prevenir comportamiento por defecto
        if (!parametros) {
            console.error("Error: Parameters not defined.");
            return;
        }
        // Redirigir a la URL de la opción 3 que es un Reporte con la información de los activos seleccionador
        const urlOpcion3 =
            `${window.location.origin}/3Shape_project/Model/prcr_nuevo_info.php?${parametros}`;
        console.log(`Redirigiendo a: ${urlOpcion3}`);
        window.location.href = urlOpcion3;
    };

    // Manejador del botón de cerrar modal
    // Manejador para generar códigos QR
    document.getElementById('btnQR').onclick = function(event) {
        event.preventDefault();
        if (!parametros) {
            console.error("Error: Parameters not defined.");
            return;
        }

        // Crear modal para QR
        const qrModal = document.createElement('div');
        qrModal.id = 'qrModal';
        qrModal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: flex; justify-content: center; align-items: center; z-index: 1000;';

        const qrContainer = document.createElement('div');
        qrContainer.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 80%; max-height: 80%; overflow-y: auto;';

        // Obtener los checkboxes seleccionados y generar QR para cada uno
        const checkboxes = document.querySelectorAll('input[name="selected_assets[]"]:checked');
        checkboxes.forEach((checkbox, index) => {
            const itemContainer = document.createElement('div');
            itemContainer.style.marginBottom = '20px';
            itemContainer.style.textAlign = 'center';

            const canvas = document.createElement('canvas');
            canvas.id = `qr-${index}`;
            const qr = new QRCode(canvas, {
                size: 200,
                padding: 10
            });
            qr.generate(checkbox.value);

            const downloadBtn = document.createElement('button');
            downloadBtn.textContent = 'Download QR';
            downloadBtn.className = 'btn btn-primary mt-2';
            downloadBtn.onclick = () => {
                const link = document.createElement('a');
                link.download = `qr-code-${checkbox.value}.png`;
                link.href = canvas.toDataURL();
                link.click();
            };

            itemContainer.appendChild(canvas);
            itemContainer.appendChild(downloadBtn);
            qrContainer.appendChild(itemContainer);
        });

        const closeBtn = document.createElement('button');
        closeBtn.textContent = 'Close';
        closeBtn.className = 'btn btn-secondary mt-3';
        closeBtn.onclick = () => qrModal.remove();
        qrContainer.appendChild(closeBtn);

        qrModal.appendChild(qrContainer);
        document.body.appendChild(qrModal);
    };

    document.getElementById('btnCerrarModal').onclick = function() {
        const modal = document.getElementById('modalConfirmacion');
        modal.style.display = 'none';
    };
};
document.getElementById('procesarSeleccionados').addEventListener('click', function() {
    let icon = this.querySelector('i');
    icon.classList.add('spin');

    // Detener la animación después de 2 segundos
    setTimeout(() => {
        icon.classList.remove('spin');
    }, 2000);
});
