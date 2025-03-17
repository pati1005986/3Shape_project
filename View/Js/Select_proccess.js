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
        'Option 3 - New option.';
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
        // Redirigir a la URL de la opción 1
        const urlOpcion1 =
            `${window.location.origin}/3Shape_project/Model/prcsr_indv_sal.php?${parametros}`;
        console.log(`Redirigiendo a: ${urlOpcion1}`);
        window.location.href = urlOpcion1;
    };

    document.getElementById('btnCancelar').onclick = function(event) {
        event.preventDefault(); // Prevenir comportamiento por defecto
        if (!parametros) {
            console.error("Error: Parameters not defined.")
            return;
        }
        // Redirigir a la URL de la opción 2
        const urlOpcion2 =
            `${window.location.origin}/3Shape_project/Model/prcsr_indv_ent.php?${parametros}`;
        console.log(`Redirigiendo a: ${urlOpcion2}`);
        window.location.href = urlOpcion2;
    };

    document.getElementById('btnOpcion3').onclick = function(event) {
        event.preventDefault(); // Prevenir comportamiento por defecto
        if (!parametros) {
            console.error("Error: Parameters not defined.");
            return;
        }
        // Redirigir a la URL de la opción 3
        const urlOpcion3 =
            `${window.location.origin}/3Shape_project/Model/prcr_nuevo_info.php?${parametros}`;
        console.log(`Redirigiendo a: ${urlOpcion3}`);
        window.location.href = urlOpcion3;
    };

    // Manejador del botón de cerrar modal
    document.getElementById('btnCerrarModal').onclick = function() {
        const modal = document.getElementById('modalConfirmacion');
        modal.style.display = 'none';
    };
};