<?php
// Verificar si la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// Incluir la conexión a la base de datos
include_once "./Configuration/Connection.php";

try {
   // Inicializar variables
   $resultados = [];
   $params = [];
   $debugInfo = [];
   
   // Verificar si hay una solicitud de búsqueda
   if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
       // Construir la consulta base usando la vista que ya tiene los joins
       $sql = "SELECT * FROM vista_equipos_usuarios WHERE 1=1";
       
       // Función para agregar condición de búsqueda de texto
       function addTextSearchCondition(&$sql, &$params, $field, $paramName, $value) {
           if (!empty($value)) {
               $value = trim($value);
               if ($value !== '') {
                   $sql .= " AND LOWER($field) LIKE LOWER(:$paramName)";
                   $params[":$paramName"] = '%' . $value . '%';
                   return true;
               }
           }
           return false;
       }
       
       // Función para procesar fechas
       function formatDateForSQL($dateString) {
           if (empty($dateString)) return null;
           
           // Verificar si la fecha está en formato MM/DD/YYYY
           if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateString, $matches)) {
               $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
               $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
               $year = $matches[3];
               return "$year-$month-$day"; // Formato YYYY-MM-DD para MySQL
           }
           return null;
       }
       
       // Procesar búsqueda por texto para campos comunes
       $textFields = [
           'search_assetname' => 'assetname',
           'search_serial' => 'serial_number',
           'search_cedula' => 'cedula',
           'search_user' => 'last_user',
           'search_job_title' => 'job_title',
           'search_status_change' => 'status_change'
       ];
       
       foreach ($textFields as $paramName => $fieldName) {
           if (isset($_GET[$paramName]) && trim($_GET[$paramName]) !== '') {
               addTextSearchCondition($sql, $params, $fieldName, $paramName, $_GET[$paramName]);
           }
       }
       
       // Procesar búsqueda por fechas de garantía
       $fechaInicio = isset($_GET['search_fecha_inicio']) ? formatDateForSQL($_GET['search_fecha_inicio']) : null;
       $fechaFin = isset($_GET['search_fecha_fin']) ? formatDateForSQL($_GET['search_fecha_fin']) : null;
       
       if ($fechaInicio || $fechaFin) {
           // Validación base para asegurar que las fechas sean válidas
           $baseValidation = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') IS NOT NULL";
           
           if ($fechaInicio && $fechaFin) {
               $sql .= " AND STR_TO_DATE(warranty_enddate, '%m/%d/%Y') BETWEEN :fecha_inicio AND :fecha_fin AND $baseValidation";
               $params[':fecha_inicio'] = $fechaInicio;
               $params[':fecha_fin'] = $fechaFin;
           } elseif ($fechaInicio) {
               $sql .= " AND STR_TO_DATE(warranty_enddate, '%m/%d/%Y') >= :fecha_inicio AND $baseValidation";
               $params[':fecha_inicio'] = $fechaInicio;
           } elseif ($fechaFin) {
               $sql .= " AND STR_TO_DATE(warranty_enddate, '%m/%d/%Y') <= :fecha_fin AND $baseValidation";
               $params[':fecha_fin'] = $fechaFin;
           }
       }
       
       // Procesar búsqueda por estado expirado
       if (isset($_GET['search_expired']) && $_GET['search_expired'] !== '') {
           $expired = $_GET['search_expired'];
           $today = date('Y-m-d');
           
           if ($expired == '1') {
               // Buscar equipos expirados
               $sql .= " AND STR_TO_DATE(warranty_enddate, '%m/%d/%Y') < :today";
           } else if ($expired == '0') {
               // Buscar equipos no expirados
               $sql .= " AND STR_TO_DATE(warranty_enddate, '%m/%d/%Y') >= :today";
           }
           $params[':today'] = $today;
       }
       
       // Procesar selecciones múltiples (país de compra)
       if (isset($_GET['search_purchase_country']) && is_array($_GET['search_purchase_country'])) {
           $filteredCountries = array_filter($_GET['search_purchase_country'], function($value) {
               return $value !== '0' && $value !== '';
           });
           
           if (!empty($filteredCountries)) {
               $countryConditions = [];
               foreach ($filteredCountries as $index => $country) {
                   $paramName = "country_$index";
                   $countryConditions[] = "purchase_country = :$paramName";
                   $params[":$paramName"] = $country;
               }
               $sql .= " AND (" . implode(" OR ", $countryConditions) . ")";
           }
       }
       
       // Procesar selecciones múltiples (estado de usuario)
       if (isset($_GET['search_user_status']) && is_array($_GET['search_user_status'])) {
           $filteredStatuses = array_filter($_GET['search_user_status'], function($value) {
               return $value !== '0' && $value !== '';
           });
           
           if (!empty($filteredStatuses)) {
               $statusConditions = [];
               foreach ($filteredStatuses as $index => $status) {
                   $paramName = "status_$index";
                   $statusConditions[] = "user_status = :$paramName";
                   $params[":$paramName"] = $status;
               }
               $sql .= " AND (" . implode(" OR ", $statusConditions) . ")";
           }
       }
       
       // Agregar ordenamiento
       $sql .= " ORDER BY assetname ASC";
       
       // Guardar información de depuración
       $debugInfo = [
           'sql' => $sql,
           'params' => $params
       ];
       
       // Preparar y ejecutar la consulta
       $stmt = $pdo->prepare($sql);
       foreach ($params as $param => $value) {
           $stmt->bindValue($param, $value);
       }
       $stmt->execute();
       $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
       // Guardar resultados en la sesión para su uso en Main_Table.php
       $_SESSION['resultados_busqueda'] = $resultados;
       $_SESSION['search_debug_info'] = $debugInfo;
       
       // Mostrar mensaje si no hay resultados
       if (empty($resultados)) {
           echo '<div class="alert alert-info mt-3" role="alert">
                   <i class="bi bi-info-circle me-2"></i>
                   No se encontraron resultados para los criterios de búsqueda especificados.
                 </div>';
       } else {
           $count = count($resultados);
           echo '<div class="alert alert-success mt-3" role="alert">
                   <i class="bi bi-check-circle me-2"></i>
                   Se encontraron ' . $count . ' resultado(s).
                 </div>';
       }
   } else {
       // Si no hay parámetros de búsqueda, obtener todos los registros
       $stmt = $pdo->prepare("SELECT * FROM vista_equipos_usuarios ORDER BY assetname ASC");
       $stmt->execute();
       $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
       $_SESSION['resultados_busqueda'] = $resultados;
   }
} catch (PDOException $e) {
   // Manejar errores de base de datos
   $errorMessage = "Error en la búsqueda: " . $e->getMessage();
   echo '<div class="alert alert-danger mt-3" role="alert">
           <i class="bi bi-exclamation-triangle me-2"></i>
           ' . htmlspecialchars($errorMessage) . '
         </div>';
   
   // Registrar el error para depuración
   error_log("Error en busqueda_Multicriterio.php: " . $e->getMessage());
   
   // Inicializar resultados vacíos para evitar errores
   $resultados = [];
   $_SESSION['resultados_busqueda'] = [];
}

// Función para mostrar información de depuración si está habilitada
function showDebugInfo($debugInfo) {
   if (isset($_GET['debug']) && $_GET['debug'] == 1) {
       echo '<div class="card mt-3 mb-3">
               <div class="card-header bg-dark text-white">
                   <h5 class="mb-0">Información de Depuración</h5>
               </div>
               <div class="card-body">
                   <h6>SQL Query:</h6>
                   <pre>' . htmlspecialchars($debugInfo['sql']) . '</pre>
                   <h6>Parámetros:</h6>
                   <pre>' . print_r($debugInfo['params'], true) . '</pre>
               </div>
             </div>';
   }
}

// Mostrar información de depuración si está habilitada
if (isset($debugInfo) && !empty($debugInfo)) {
   showDebugInfo($debugInfo);
}
?>
