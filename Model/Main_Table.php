<?php
// Precalcula el colspan una vez
$colspan = count($selectedColumns) + 12;

if ($resultados) {
    $currentTimestamp = time(); // Tiempo actual fuera del bucle
    $htmlRows = []; // Almacenar todas las filas

    foreach ($resultados as $row) {
        // Optimización 1: Validación de fecha más eficiente
        $warranty_class = 'text-warning';
        $dateStr = $row['warranty_enddate'];

        // Parseo manual más rápido que DateTime
        if (sscanf($dateStr, "%d/%d/%d", $month, $day, $year) === 3 && checkdate($month, $day, $year)) {
            $warrantyTimestamp = mktime(0, 0, 0, $month, $day, $year);
            $warranty_class = $warrantyTimestamp < $currentTimestamp
                ? 'text-danger'
                : 'text-success';
        }

        // Determinar la clase para el estado del usuario
        $userStatusClass = '';
        switch(strtolower(trim($row['user_status']))) {
            case 'active':
                $userStatusClass = 'status-active';
                break;
            case 'inactive':
                $userStatusClass = 'status-inactive';
                break;
            default:
                $userStatusClass = 'status-pending';
        }

        // Optimización 2: Escapar solo cuando se usa y construir HTML como array
        $assetname = htmlspecialchars($row['assetname']);
        $rowHtml = [
            "<tr class='table-row'>",
            "<td><div class='form-check d-flex justify-content-center'><input class='form-check-input' type='checkbox' name='selected_assets[]' value='$assetname'></div></td>",
            "<td><span class='fw-medium'>" . htmlspecialchars($row['assetname']) . "</span></td>",
            "<td><span class='text-secondary'>" . htmlspecialchars($row['serial_number']) . "</span></td>",
            "<td><span class='$userStatusClass'>" . htmlspecialchars($row['user_status']) . "</span></td>",
            "<td>" . htmlspecialchars($row['last_user']) . "</td>",
            "<td><span class='text-muted'>" . htmlspecialchars($row['job_title']) . "</span></td>",
            "<td>" . htmlspecialchars($row['status_change']) . "</td>",
            "<td>" . htmlspecialchars($row['purchase_country']) . "</td>",
            "<td><span class='$warranty_class'>" . htmlspecialchars($row['warranty_enddate']) . "</span></td>",
            "<td>" . htmlspecialchars($row['expired']) . "</td>", // Keeping expired field as it was
            "<td>" . htmlspecialchars($row['cedula']) . "</td>"
        ];

        // Optimización 3: Procesamiento de columnas dinámicas
        foreach ($selectedColumns as $column) {
            $value = $row[$column];
            $processed = (is_numeric($value) && in_array($value, ['0', '1'], true))
                ? ($value == '1' ? 'Yes' : 'No')
                : $value;
            $rowHtml[] = "<td>" . htmlspecialchars($processed) . "</td>";
        }

        // Botones con valor ya escapado y estilos mejorados
        $rowHtml[] = "<td>
            <a href='./Model/act_registro.php?assetname=$assetname' class='action-btn action-btn-edit' title='Update'>
                <i class='fas fa-sync-alt sync-icon'></i>
            </a>
        </td>";
        
        $rowHtml[] = "<td>
            <div class='dropdown'>
                <button class='action-btn action-btn-view' type='button' id='dropdownMenuButton_$assetname' data-bs-toggle='dropdown' aria-expanded='false'>
                    <i class='fas fa-eye'></i>
                </button>
                <ul class='dropdown-menu shadow-sm' aria-labelledby='dropdownMenuButton_$assetname'>
                    <li><a class='dropdown-item' href='./View/preview-salida.php?assetname=$assetname'>
                        <i class='fas fa-file-export me-2'></i>Departure Preview
                    </a></li>
                    <li><a class='dropdown-item' href='./View/preview-entrada.php?assetname=$assetname'>
                        <i class='fas fa-file-import me-2'></i>Entry Preview
                    </a></li>
                </ul>
            </div>
        </td>";
        
        $rowHtml[] = "<td>
            <a href='./Model/act_registro_status.php?assetname=$assetname' class='action-btn action-btn-view' title='Status and Observations'>
                <i class='far fa-check-square'></i>
            </a>
        </td>";
        
        $rowHtml[] = "<td>
            <a href='javascript:void(0);' class='action-btn action-btn-delete delete-item' data-assetname='$assetname' title='Delete'>
                <i class='fa fa-times'></i>
            </a>
        </td>";
        
        $rowHtml[] = "</tr>";

        // Optimización 4: Unir partes de la fila
        $htmlRows[] = implode('', $rowHtml);
    }

    echo implode('', $htmlRows);
} else {
    echo "<tr><td colspan='$colspan' class='text-center py-4'>
            <div class='d-flex flex-column align-items-center'>
                <i class='bi bi-inbox-fill fs-1 text-muted mb-3'></i>
                <p class='text-muted'>No data found</p>
            </div>
          </td></tr>";
}
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-item').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const assetname = this.getAttribute('data-assetname');
                Swal.fire({
                    title: 'Confirm Deletion',
                    text: `Are you sure you want to delete the log for ${assetname}? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash-alt me-2"></i>Yes, delete it!',
                    cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Add loading state
                        Swal.fire({
                            title: 'Deleting...',
                            html: 'Please wait while we delete the record.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Redirect after a short delay to show loading state
                        setTimeout(() => {
                            window.location.href = './Model/elim_registro.php?assetname=' + assetname;
                        }, 500);
                    }
                });
            });
        });
        
        // Add hover effect to table rows
        document.querySelectorAll('#mainTable tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.classList.add('highlight');
            });
            row.addEventListener('mouseleave', function() {
                this.classList.remove('highlight');
            });
        });
    });
</script>
