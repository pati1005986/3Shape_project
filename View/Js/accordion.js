document.addEventListener('DOMContentLoaded', function() {
    // Obtener todos los elementos colapsables
    const collapseElements = document.querySelectorAll('.collapse');
    const collapseHeaders = document.querySelectorAll('[data-bs-toggle="collapse"]');

    // Verificar si hay elementos para evitar errores
    if (collapseElements.length === 0 || collapseHeaders.length === 0) {
        console.warn('No se encontraron elementos de acordeón en la página');
        return;
    }

    // Crear una instancia de Bootstrap Collapse para cada elemento
    const collapseInstances = Array.from(collapseElements).map(element => {
        try {
            return new bootstrap.Collapse(element, {
                toggle: false
            });
        } catch (error) {
            console.error('Error al inicializar el acordeón:', error);
            return null;
        }
    }).filter(instance => instance !== null);

    // Función para cerrar todos los elementos excepto el actual
    const closeAllExcept = (currentIndex) => {
        collapseElements.forEach((element, elementIndex) => {
            if (elementIndex !== currentIndex && element.classList.contains('show')) {
                collapseInstances[elementIndex].hide();
                
                // Actualizar el estado del aria-expanded en el encabezado
                if (collapseHeaders[elementIndex]) {
                    collapseHeaders[elementIndex].setAttribute('aria-expanded', 'false');
                }
            }
        });
    };

    // Agregar evento click a cada encabezado
    collapseHeaders.forEach((header, index) => {
        header.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Cerrar todos los demás elementos colapsables
            closeAllExcept(index);

            // Alternar el elemento actual
            collapseInstances[index].toggle();
            
            // Actualizar el estado del aria-expanded
            const isExpanded = collapseElements[index].classList.contains('show');
            header.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            
            // Añadir efecto de pulsación al encabezado
            header.classList.add('pulse');
            setTimeout(() => {
                header.classList.remove('pulse');
            }, 300);
        });
    });
    
    // Asegurarse de que solo un elemento esté abierto al cargar la página
    const openElements = Array.from(collapseElements).filter(el => el.classList.contains('show'));
    if (openElements.length > 1) {
        // Mantener solo el primer elemento abierto
        for (let i = 1; i < openElements.length; i++) {
            const index = Array.from(collapseElements).indexOf(openElements[i]);
            if (index !== -1 && collapseInstances[index]) {
                collapseInstances[index].hide();
                if (collapseHeaders[index]) {
                    collapseHeaders[index].setAttribute('aria-expanded', 'false');
                }
            }
        }
    }
});