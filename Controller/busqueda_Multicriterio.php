<?php
include "./Configuration/Connection.php";
// Iniciar consulta base y array de condiciones
$sql = "SELECT *
        FROM equipos
        INNER JOIN usuarios_equipos
        ON equipos.assetname = usuarios_equipos.fk_assetname";
$where = [];
$params = [];

// Function to validate date
function isValidDate($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Busqueda de fechas multicirterio
function buildDateSearchQuery($searchParams)
{
    $where = [];
    $params = [];

    $fecha_inicio = !empty($searchParams['search_fecha_inicio']) ? date('Y-m-d', strtotime($searchParams['search_fecha_inicio'])) : null;
    $fecha_fin = !empty($searchParams['search_fecha_fin']) ? date('Y-m-d', strtotime($searchParams['search_fecha_fin'])) : null;

    // Validate dates
    if ($fecha_inicio && !isValidDate($fecha_inicio)) {
        $fecha_inicio = null;
        error_log("Fecha de inicio inválida: " . $fecha_inicio);
    }
    if ($fecha_fin && !isValidDate($fecha_fin)) {
        $fecha_fin = null;
        error_log("Fecha de fin inválida: " . $fecha_fin);
    }

    // Debugging information
    error_log("Fecha Inicio: " . $fecha_inicio);
    error_log("Fecha Fin: " . $fecha_fin);

    // Validación base para asegurar que las fechas sean válidas
    $baseValidation = "MONTH(STR_TO_DATE(warranty_enddate, '%m/%d/%Y')) > 0 
                                               AND DAY(STR_TO_DATE(warranty_enddate, '%m/%d/%Y')) > 0";

    if ($fecha_inicio && $fecha_fin) {
        // Caso 1: Ambas fechas proporcionadas
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') BETWEEN :fecha_inicio AND :fecha_fin 
                                            AND " . $baseValidation;
        $params[':fecha_inicio'] = $fecha_inicio;
        $params[':fecha_fin'] = $fecha_fin;
    } elseif ($fecha_inicio) {
        // Caso 2: Solo fecha de inicio
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') >= :fecha_inicio 
                                            AND " . $baseValidation;
        $params[':fecha_inicio'] = $fecha_inicio;
    } elseif ($fecha_fin) {
        // Caso 3: Solo fecha final
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') <= :fecha_fin 
                                            AND " . $baseValidation;
        $params[':fecha_fin'] = $fecha_fin;
    }

    // Debugging SQL query
    error_log("SQL Query: " . implode(" AND ", $where));

    return [
        'where' => $where,
        'params' => $params
    ];
}

// Ejemplo de uso:
$searchResult = buildDateSearchQuery($_GET);
$where = $searchResult['where'];
$params = $searchResult['params'];

// Filtro de nombre de activo
if (!empty($_GET['search_assetname'])) {
    $assetname = trim($_GET['search_assetname']); // Elimina espacios en blanco al inicio y al final
}

if (!empty($assetname)) {
    $where[] = "LOWER(assetname) LIKE LOWER(:assetname)";
    $params[':assetname'] = '%' . $assetname . '%';
}

// Filtro de número de serie
if (!empty($_GET['search_serial'])) {
    $serial = trim($_GET['search_serial']); // Elimina espacios en blanco al inicio y al final
    if (!empty($serial)) {
        $where[] = "LOWER(serial_number) LIKE LOWER(:serial)";
        $params[':serial'] = '%' . $serial . '%';
    }
}
// Filtro de cédula
if (!empty($_GET['search_cedula'])) {
    $cedula = trim($_GET['search_cedula']); // Elimina espacios en blanco al inicio y al final
    if (!empty($cedula)) {
        $where[] = "LOWER(cedula) LIKE LOWER(:cedula)";
        $params[':cedula'] = '%' . $cedula . '%';
    }
}

// Filtro de usuario
if (!empty($_GET['search_user'])) {
    $user = trim($_GET['search_user']); // Elimina espacios en blanco al inicio y al final
    if (!empty($user)) {
        $where[] = "LOWER(last_user) LIKE LOWER(:user)";
        $params[':user'] = '%' . $user . '%';
    }
}

// Filtro de cambio de estado
if (!empty($_GET['search_status_change'])) {
    $status_change = trim($_GET['search_status_change']); // Elimina espacios en blanco al inicio y al final
    if (!empty($status_change)) {
        $where[] = "LOWER(status_change) LIKE LOWER(:status_change)";
        $params[':status_change'] = '%' . $status_change . '%';
    }
}

// Filtro de estado de usuario (selección múltiple)
if (!empty($_GET['search_user_status'])) {
    $filtered_statuses = array_filter($_GET['search_user_status'], function ($status) {
        return $status != 0;
    });
    //Condicion en caso de que el estado del usuario no este vacio
    if (!empty($filtered_statuses)) {
        $user_status_conditions = [];
        foreach ($filtered_statuses as $index => $status) {
            $param_name = ":status_$index";
            $user_status_conditions[] = "user_status = $param_name";
            $params[$param_name] = $status;
        }
        $where[] = '(' . implode(' OR ', $user_status_conditions) . ')';
    }
}

// Filtro de pais de compra (selección múltiple)
if (!empty($_GET['search_purchase_country'])) {
    $filtered_statuses = array_filter($_GET['search_purchase_country'], function ($status) {
        return $status != 0;
    });
    //Condicion en caso de que el pais de compra no este vacio
    if (!empty($filtered_statuses)) {
        $user_status_conditions = [];
        foreach ($filtered_statuses as $index => $status) {
            $param_name = ":status_$index";
            $user_status_conditions[] = "purchase_country = $param_name";
            $params[$param_name] = $status;
        }
        $where[] = '(' . implode(' OR ', $user_status_conditions) . ')';
    }
}

// Filtro para Warranty End Date
if (!empty($_GET['search_fecha_fin'])) {
    $warranty_end = trim($_GET['search_fecha_fin']);
    if (!empty($warranty_end)) {
        // Convertir el formato de fecha MM/DD/YYYY a YYYY-MM-DD para la comparación
        $date_parts = explode('/', $warranty_end);
        if (count($date_parts) === 3) {
            $formatted_date = $date_parts[2] . '-' . $date_parts[0] . '-' . $date_parts[1];
            $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') = STR_TO_DATE(:warranty_end, '%Y-%m-%d')";
            $params[':warranty_end'] = $formatted_date;
        }
    }
}

// Filtro para Expired
if (isset($_GET['search_expired']) && $_GET['search_expired'] !== '') {
    $expired = $_GET['search_expired'];
    $today = date('Y-m-d');
    if ($expired == '1') {
        // Buscar equipos expirados
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') < :today";
    } else if ($expired == '0') {
        // Buscar equipos no expirados
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') >= :today";
    }
    $params[':today'] = $today;
}

// Filtro de job title
if (!empty($_GET['search_job_title'])) {
    $job_title = trim($_GET['search_job_title']); // Elimina espacios en blanco al inicio y al final
    if (!empty($job_title)) {
        $where[] = "LOWER(job_title) LIKE LOWER(:job_title)";
        $params[':job_title'] = '%' . $job_title . '%';
    }
}

// Agregar condiciones al SQL
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($sql);
// Asociar parámetros con valores
if (!empty($_GET['search_assetname'])) {
    $stmt->bindValue(':assetname', '%' . $_GET['search_assetname'] . '%');
}
if (!empty($_GET['search_serial'])) {
    $stmt->bindValue(':serial', '%' . $_GET['search_serial'] . '%');
}
if (!empty($_GET['search_cedula'])) {
    $stmt->bindValue(':cedula', '%' . $_GET['search_cedula'] . '%');
}
if (!empty($_GET['search_user'])) {
    $stmt->bindValue(':user', '%' . $_GET['search_user'] . '%');
}
if (!empty($_GET['search_job_title'])) {
    $stmt->bindValue(':job_title', '%' . $_GET['search_job_title'] . '%');
}
if (!empty($_GET['search_status_change'])) {
    $stmt->bindValue(':status_change', '%' . $_GET['search_status_change'] . '%');
}
if (!empty($_GET['search_user_status'])) {
    foreach ($params as $param_name => $value) {
        $stmt->bindValue($param_name, $value);
    }
}

if (!empty($_GET['search_purchase_country'])) {
    foreach ($params as $param_name => $value) {
        $stmt->bindValue($param_name, $value);
    }
}

// Ejecutar y obtener resultados
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
