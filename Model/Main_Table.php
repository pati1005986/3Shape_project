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

        // Optimización 2: Escapar solo cuando se usa y construir HTML como array
        $assetname = htmlspecialchars($row['assetname']);
        $rowHtml = [
            "<tr>",
            "<td><input type='checkbox' name='selected_assets[]' value='$assetname'></td>",
            "<td>" . htmlspecialchars($row['assetname']) . "</td>",
            "<td>" . htmlspecialchars($row['serial_number']) . "</td>",
            "<td>" . htmlspecialchars($row['user_status']) . "</td>",
            "<td>" . htmlspecialchars($row['last_user']) . "</td>",
            "<td>" . htmlspecialchars($row['job_title']) . "</td>",
            "<td>" . htmlspecialchars($row['status_change']) . "</td>",
            "<td>" . htmlspecialchars($row['purchase_country']) . "</td>",
            "<td><span class='$warranty_class'>" . htmlspecialchars($row['warranty_enddate']) . "</span></td>",
            "<td>" . htmlspecialchars($row['expired']) . "</td>",
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

        // Botones con valor ya escapado
        $rowHtml[] = "<td><a href='./Model/act_registro.php?assetname=$assetname' class='btn btn-secondary btn-sm'>" .
        "<i class='fas fa-sync-alt sync-icon'></i></a></td>" .
        "<td><a href='./Model/act_registro_status.php?assetname=$assetname' class='btn btn-secondary btn-sm'>" .
        "<i class='far fa-check-square'></i></a></td>" .
         "<td><a href='javascript:void(0);' class='btn btn-danger btn-sm delete-item' " .
        "data-assetname='$assetname'><i class='fa fa-times'></i></a></td>".
        "</tr>";

        // Optimización 4: Unir partes de la fila
        $htmlRows[] = implode('', $rowHtml);
    }

    echo implode('', $htmlRows);
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