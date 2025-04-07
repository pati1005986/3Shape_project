<?php
ob_start();
//Archivo que consulta la mayoria de los datos de los activos osea registros y los trae a el pdf principal como pdf
require './fpdf.php';
include '../Configuration/Connection.php';

// Función de conversión para texto
function convertirTexto($texto) {
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}
//Crear Clase en base a la Api de FPDF
class PDF extends FPDF
{
    
   // Cabecera de página
   function Header()
   {
    // Configuración inicial
    $this->SetMargins(10, 10, 10);
    
    // Fondo del encabezado
    $this->SetFillColor(245, 245, 245);
    $this->Rect(0, 0, $this->GetPageWidth(), 50, 'F');
    
    // Logo
    $this->Image('logo.png', 15, 10, 35);
    
    // Línea decorativa
    $this->SetDrawColor(164, 0, 125);
    $this->SetLineWidth(0.5);
    $this->Line(55, 15, $this->GetPageWidth() - 15, 15);
    
    // Título principal
    $this->SetFont('Arial', 'B', 24);
    $this->SetTextColor(50, 50, 50);
    $this->SetXY(55, 20);
    $this->Cell(0, 10, convertirTexto("General Report"), 0, 1, 'L');
    
    // Fecha de generación
    $this->SetFont('Arial', '', 11);
    $this->SetTextColor(100, 100, 100);
    $this->SetXY(55, 32);
    $this->Cell(0, 10, convertirTexto("Generated on: " . date('d/m/Y')), 0, 1, 'L');
    
    // Espacio antes de la tabla
    $this->Ln(20);
    
    // Estilo de la tabla
    $this->SetFillColor(164, 0, 125);
    $this->SetTextColor(255, 255, 255);
    $this->SetDrawColor(190, 190, 190);
    $this->SetLineWidth(0.2);
    $this->SetFont('Arial', 'B', 8); // Aumentado ligeramente el tamaño para mejor legibilidad

      // Encabezados de tabla con mejor espaciado y diseño
      $cellHeight = 12;
      $this->Cell(22, $cellHeight, convertirTexto('Asset Name'), 1, 0, 'C', 1);
      $this->Cell(18, $cellHeight, convertirTexto('Serial'), 1, 0, 'C', 1);
      $this->Cell(19, $cellHeight, convertirTexto('User Status'), 1, 0, 'C', 1);
      $this->Cell(30, $cellHeight, convertirTexto('Last User'), 1, 0, 'C', 1);
      $this->Cell(23, $cellHeight, convertirTexto('Status Change'), 1, 0, 'C', 1);
      $this->Cell(20, $cellHeight, convertirTexto('Purchase'), 1, 0, 'C', 1);
      $this->Cell(25, $cellHeight, convertirTexto('Warranty End'), 1, 0, 'C', 1);
      $this->Cell(15, $cellHeight, convertirTexto('Expired'), 1, 0, 'C', 1);
      $this->Cell(20, $cellHeight, convertirTexto('ID'), 1, 1, 'C', 1);
   }

   // Pie de página
   function Footer()
   {
      // Línea separadora
      $this->SetY(-20);
      $this->SetDrawColor(164, 0, 125);
      $this->SetLineWidth(0.5);
      $this->Line(15, $this->GetY(), $this->GetPageWidth() - 15, $this->GetY());
      
      // Información del pie de página
      $this->SetY(-15);
      $this->SetFont('Arial', '', 8);
      $this->SetTextColor(100, 100, 100);
      $this->Cell(0, 10, convertirTexto('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
   }
}

$pdf = new PDF();//CREAR PDF
$pdf->SetLeftMargin(0); // Ajusta este valor si necesitas un pequeño margen
$pdf->AddPage();
$pdf->AliasNbPages();
$pdf->SetFont('Arial', '', 5);
$pdf->SetDrawColor(163, 163, 163);

// Construir la consulta usando los filtros de búsqueda
$sql = "SELECT * FROM vista_equipos_usuarios";
$where = [];
$params = [];

function buildDateSearchQuery($searchParams) {
    $where = [];
    $params = [];
    
    $fecha_inicio = !empty($searchParams['search_fecha_inicio']) 
        ? date('Y-m-d', strtotime($searchParams['search_fecha_inicio'])) 
        : null;
    
    $fecha_fin = !empty($searchParams['search_fecha_fin']) 
        ? date('Y-m-d', strtotime($searchParams['search_fecha_fin'])) 
        : null;

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
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') <= :fecha_inicio 
                    AND " . $baseValidation;
        $params[':fecha_inicio'] = $fecha_inicio;
    } elseif ($fecha_fin) {
        // Caso 3: Solo fecha final
        $where[] = "STR_TO_DATE(warranty_enddate, '%m/%d/%Y') >= :fecha_fin 
                    AND " . $baseValidation;
        $params[':fecha_fin'] = $fecha_fin;
    }

    return [
        'where' => $where,
        'params' => $params
    ];
}

// Ejemplo de uso:
$searchResult = buildDateSearchQuery($_GET);

// Otros filtros
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

// Agregar condiciones dinámicas
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Pasar todos los parámetros de una vez
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($resultados) {
        $fillColor = false; // Inicializar la variable antes del bucle
        foreach ($resultados as $row) {
            if ($row['cedula'] == 0 || $row['user_status'] == 'Stock') {
                continue; // Saltar registros según los criterios
            }
            // Generar contenido del PDF con filas alternadas
            $fillColor = !$fillColor;
            $pdf->SetFillColor($fillColor ? 245 : 255, $fillColor ? 245 : 255, $fillColor ? 245 : 255);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetFont('Arial', '', 8);
            
            $pdf->Cell(22, 10, convertirTexto($row["assetname"]), 1, 0, 'C', $fillColor);
            $pdf->Cell(18, 10, convertirTexto($row["serial_number"]), 1, 0, 'C', $fillColor);
            $pdf->Cell(19, 10, convertirTexto($row["user_status"]), 1, 0, 'C', $fillColor);
            $pdf->Cell(30, 10, convertirTexto($row["last_user"]), 1, 0, 'C', $fillColor);
            $pdf->Cell(23, 10, convertirTexto($row["status_change"]), 1, 0, 'C', $fillColor);
            $pdf->Cell(20, 10, convertirTexto($row["purchase_country"]), 1, 0, 'C', $fillColor);
            $pdf->Cell(25, 10, convertirTexto($row["warranty_enddate"]), 1, 0, 'C', $fillColor);
            $pdf->Cell(15, 10, convertirTexto($row["expired"]), 1, 0, 'C', $fillColor);
            $pdf->Cell(20, 10, convertirTexto($row["cedula"]), 1, 1, 'C', $fillColor);
        }
    } else {
        $pdf->Cell(0, 10, 'No data found', 1, 1, 'C', 0);
    }
} catch (PDOException $e) {
    ob_end_clean(); // Limpiar el buffer en caso de error
    die("Error: " . $e->getMessage());
}

// Limpiar cualquier salida en el buffer antes de enviar el PDF
ob_end_clean();
$pdf->Output('Reporte_Equipos.pdf', 'I');