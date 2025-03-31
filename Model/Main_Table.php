<?php
// Precalcula el colspan una vez
$colspan = count($selectedColumns) + 12;

if ($resultados) {
    foreach ($resultados as $row) {
        // Escapar todos los campos
        $escapedRow = array_map('htmlspecialchars', $row);

        // Determinar clase de garantía usando el timestamp de SQL
        $warrantyDate = DateTime::createFromFormat('m/d/Y', $row['warranty_enddate']);
        if ($warrantyDate !== false) {
            $warrantyTimestamp = $warrantyDate->getTimestamp();
            $currentTimestamp = time();
            $warranty_class = ($warrantyTimestamp < $currentTimestamp)
                ? 'text-danger'  // Fecha futura: rojo
                : 'text-success'; // Fecha pasada: verde
        } else {
            // Manejo de fecha inválida (opcional)
            $warranty_class = 'text-warning';
        }

        // Construir fila como string
        $html = "<tr>
            <td><input type='checkbox' name='selected_assets[]' value='{$escapedRow['assetname']}'></td>
            <td>{$escapedRow['assetname']}</td>
            <td>{$escapedRow['serial_number']}</td>
            <td>{$escapedRow['user_status']}</td>
            <td>{$escapedRow['last_user']}</td>
            <td>{$escapedRow['job_title']}</td>
            <td>{$escapedRow['status_change']}</td>
            <td>{$escapedRow['purchase_country']}</td>
            <td><span class='$warranty_class'>{$escapedRow['warranty_enddate']}</span></td>
            <td>{$escapedRow['expired']}</td>
            <td>{$escapedRow['cedula']}</td>";

        // Campos dinámicos (asumiendo que existen)
        foreach ($selectedColumns as $column) {
            $value = is_numeric($escapedRow[$column]) && in_array($escapedRow[$column], ['0', '1'])
                ? ($escapedRow[$column] == '1' ? 'Yes' : 'No')
                : $escapedRow[$column];
            $html .= "<td>$value</td>";
        }

        // Botones
        $html .= "
            <td><a href='./Model/act_registro.php?assetname={$escapedRow['assetname']}' class='btn btn-secondary btn-sm'><i class='fas fa-sync-alt'></i></a></td>
            <td><a href='javascript:void(0);' class='btn btn-danger btn-sm delete-item' data-assetname='{$escapedRow['assetname']}'><i class='fa fa-times'></i></a></td>
        </tr>";

        echo $html;
    }
} else {
    echo "<tr><td colspan='$colspan'>No data found</td></tr>";
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
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6d6d6d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = './Model/elim_registro.php?assetname=' + assetname;
                    }
                });
            });
        });
    });
</script>