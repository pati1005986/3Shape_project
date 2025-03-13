<?php
if ($resultados) {
    foreach ($resultados as $row) {
        // Escape all fields in one go
        $escapedRow = array_map('htmlspecialchars', $row);

        // Check warranty end date
        $warranty_class = (strtotime($escapedRow["warranty_enddate"]) < time()) ? 'text-danger' : 'text-success';
        echo "<tr>";
        // Check box
        echo '<td><input type="checkbox" id="selected_assets[]" name="selected_assets[]" value="' . $escapedRow["assetname"] . '"></td>';
        echo "<td>{$escapedRow['assetname']}</td>";
        echo "<td>{$escapedRow['serial_number']}</td>";
        echo "<td>{$escapedRow['user_status']}</td>";
        echo "<td>{$escapedRow['last_user']}</td>";
        echo "<td>{$escapedRow['job_title']}</td>";
        echo "<td>{$escapedRow['status_change']}</td>";
        echo "<td>{$escapedRow['purchase_country']}</td>";
        echo "<td><span class='$warranty_class'>{$escapedRow['warranty_enddate']}</span></td>";
        echo "<td>{$escapedRow['expired']}</td>";
        echo "<td>{$escapedRow['cedula']}</td>";

        // Dynamic fields
        foreach ($selectedColumns as $column) {
            if (isset($escapedRow[$column])) {
                // Check if the column is a state
                $value = is_numeric($escapedRow[$column]) && in_array($escapedRow[$column], [0, 1]) ? ($escapedRow[$column] == 1 ? "Yes" : "No") : htmlspecialchars($escapedRow[$column]);
                echo "<td>$value</td>";
            } else {
                echo "<td></td>";
            }
        }

        // Update button
        echo '<td><a href="./Model/act_registro.php?assetname=' . $escapedRow["assetname"] . '" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i></a></td>';
        // Delete button
        echo '<td><a href="./Model/elim_registro.php?assetname=' . $escapedRow["assetname"] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this item?\')"><i class="fa fa-times"></i></a></td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='" . (count($selectedColumns) + 12) . "'>No data found</td></tr>";
}
