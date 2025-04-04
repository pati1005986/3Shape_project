<?php
session_start();
include_once "../Configuration/Connection.php";

header('Content-Type: application/json');

/**
 * Clase Warranty
 * 
 * Maneja la validación y actualización del estado de garantía de los equipos.
 * Incluye funciones para validar formatos de fecha, corregir fechas mal formateadas
 * y actualizar el estado de expiración de las garantías en la base de datos.
 */
class Warranty {
    private $conn;

    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
    }

    /**
     * Valida si una fecha tiene un formato válido
     * 
     * @param string $date La fecha a validar
     * @return bool true si la fecha es válida, false en caso contrario
     */
    private function isValidDate($date) {
        if (empty($date)) return false;
        
        // Primero intentar con el formato mm/dd/yyyy
        $d = DateTime::createFromFormat('m/d/Y', $date);
        if ($d && $d->format('m/d/Y') === $date) {
            return true;
        }
        
        // Intentar con otros formatos comunes
        $formats = ['Y/m/d', 'Y-m-d', 'd/m/Y', 'm-d-Y'];
        foreach ($formats as $format) {
            $d = DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Corrige y estandariza el formato de una fecha
     * 
     * @param string $date La fecha a corregir
     * @return string La fecha en formato mm/dd/yyyy
     * 
     * Maneja varios casos especiales:
     * - Fechas 'Unknown' o vacías: retorna fecha un año en el futuro
     * - Fechas incompletas: completa con valores por defecto
     * - Diferentes separadores: estandariza a /
     * - Años de 2 dígitos: convierte a 4 dígitos (50-99 -> 1900s, 00-49 -> 2000s)
     */
    private function correctDateFormat($date) {
        // Manejar caso 'Unknown' o vacío
        if ($date === 'Unknown' || empty($date)) {
            return date('m/d/Y', strtotime('+1 year')); // Fecha por defecto: 1 año desde hoy
        }

        // Limpiar y estandarizar separadores
        $date = trim($date);
        $date = str_replace(['-', '.'], '/', $date);
        $parts = explode('/', $date);
        
        // Limpiar cada parte y eliminar partes vacías
        $parts = array_filter(array_map('trim', $parts), function($part) {
            return $part !== '';
        });
        $parts = array_values($parts); // Reindexar el array

        // Manejar fechas incompletas (mm/dd/)
        if (count($parts) === 2) {
            // Si tenemos mes y día, agregar el año actual
            $month = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $day = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            if (intval($month) > 0 && intval($month) <= 12 && intval($day) > 0 && intval($day) <= 31) {
                $parts = [$month, $day, date('Y')];
            } else {
                return date('m/d/Y', strtotime('+1 year')); // Fecha inválida, usar fecha por defecto
            }
        } else if (count($parts) === 1) {
            // Si solo hay un número y es un mes válido, usar día 1 y año actual
            $month = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            if (intval($month) > 0 && intval($month) <= 12) {
                $parts = [$month, '01', date('Y')];
            } else {
                return date('m/d/Y', strtotime('+1 year')); // Mes inválido, usar fecha por defecto
            }
        } else if (count($parts) === 3) {
            // Validar y formatear cada parte
            $month = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $day = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            $year = $parts[2];
            
            // Validar el año
            if (strlen($year) === 2) {
                $year = (intval($year) > 50) ? '19'.$year : '20'.$year;
            } else if (strlen($year) !== 4) {
                $year = date('Y');
            }
            
            if (intval($month) > 0 && intval($month) <= 12 && intval($day) > 0 && intval($day) <= 31) {
                $parts = [$month, $day, $year];
            } else {
                return date('m/d/Y', strtotime('+1 year')); // Fecha inválida, usar fecha por defecto
            }
        } else {
            // Si el formato es completamente inválido, usar fecha por defecto
            return date('m/d/Y', strtotime('+1 year'));
        }
        
        // Identificar el año y su posición
        $yearIndex = -1;
        $year = null;
        foreach ($parts as $i => $part) {
            if (strlen($part) === 4 && is_numeric($part)) {
                $yearIndex = $i;
                $year = intval($part);
                break;
            }
        }
        
        // Si no encontramos un año de 4 dígitos, buscar uno de 2 dígitos
        if ($yearIndex === -1) {
            foreach ($parts as $i => $part) {
                if (strlen($part) === 2 && is_numeric($part)) {
                    $yearValue = intval($part);
                    if ($yearValue > 50) {
                        $year = 1900 + $yearValue;
                    } else {
                        $year = 2000 + $yearValue;
                    }
                    $yearIndex = $i;
                    $parts[$i] = $year;
                    break;
                }
            }
        }
        
        // Reorganizar las partes basado en la posición del año
        $month = null;
        $day = null;
        
        // Determinar mes y día basado en rangos válidos
        $remainingParts = array_values(array_diff_key($parts, [$yearIndex => true]));
        foreach ($remainingParts as $part) {
            $value = intval($part);
            if ($value >= 1 && $value <= 12 && $month === null) {
                $month = str_pad($value, 2, '0', STR_PAD_LEFT);
            } else if ($value >= 1 && $value <= 31 && $day === null) {
                $day = str_pad($value, 2, '0', STR_PAD_LEFT);
            }
        }
        
        // Si no se pudo determinar mes o día, usar valores por defecto
        if ($month === null) $month = '01';
        if ($day === null) $day = '01';
        
        // Retornar en formato mm/dd/yyyy
        return $month . '/' . $day . '/' . $year;
    }

    /**
     * Verifica y actualiza el estado de garantía de todos los equipos
     * 
     * @return array Resultado de la operación con la siguiente estructura:
     *               - success: bool - Indica si la operación fue exitosa
     *               - message: string - Mensaje descriptivo del resultado
     *               - updatedAssets: array - Lista de equipos actualizados
     *               - errors: array - Lista de errores encontrados
     */
    public function checkWarrantyStatus() {
        $response = ['success' => false, 'message' => '', 'updatedAssets' => [], 'errors' => []];
        date_default_timezone_set('America/Bogota');
        
        try {
            // Obtener fecha actual en formato Y-m-d
            $currentDate = date('Y-m-d');
            
            // Consultar todos los equipos
            $sql = "SELECT assetname, warranty_enddate, expired FROM equipos";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $updatedAssets = [];
            $errors = [];
            
            foreach ($equipos as $equipo) {
                // Validar y corregir formato de fecha a mm/dd/yyyy
                $equipo['warranty_enddate'] = $this->correctDateFormat($equipo['warranty_enddate']);
                if (!$this->isValidDate($equipo['warranty_enddate'])) {
                    $errors[] = "Invalid warranty date for asset {$equipo['assetname']}: {$equipo['warranty_enddate']}";
                    continue;
                }
                
                // Convertir ambas fechas a DateTime usando el mismo formato
                $warrantyDateTime = DateTime::createFromFormat('m/d/Y', $equipo['warranty_enddate']);
                if (!$warrantyDateTime) {
                    $errors[] = "Error converting warranty date for asset {$equipo['assetname']}: Invalid date format";
                    continue;
                }
                
                // Establecer la hora a medianoche para ambas fechas para comparar solo las fechas
                $currentDateTime = new DateTime();
                $currentDateTime->setTime(0, 0, 0);
                $warrantyDateTime->setTime(0, 0, 0);
                
                // Determinar nuevo estado de expiración comparando solo las fechas
                $newExpiredStatus = $warrantyDateTime < $currentDateTime ? 'yes' : 'no';
                
                // Si el estado ha cambiado, actualizar la base de datos
                if ($equipo['expired'] !== $newExpiredStatus) {
                    try {
                        $updateSql = "UPDATE equipos SET expired = ? WHERE assetname = ?";
                        $updateStmt = $this->conn->prepare($updateSql);
                        $updateStmt->execute([$newExpiredStatus, $equipo['assetname']]);
                        
                        // Registrar el equipo actualizado
                        $updatedAssets[] = $equipo['assetname'];
                    } catch (PDOException $e) {
                        $errors[] = "Error updating asset {$equipo['assetname']}: " . $e->getMessage();
                    }
                }
            }
            
            if (!empty($updatedAssets)) {
                $response['success'] = true;
                $response['message'] = "Assets updated successfully";
                $response['updatedAssets'] = $updatedAssets;
            } else {
                $response['message'] = "No updates required";
                $response['success'] = true;
            }
            
            if (!empty($errors)) {
                $response['errors'] = $errors;
            }
            
            return $response;
        } catch (PDOException $e) {
            $response['message'] = "General error: " . $e->getMessage();
            return $response;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $warranty = new Warranty();
    $result = $warranty->checkWarrantyStatus();
    echo json_encode($result);
}