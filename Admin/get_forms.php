<?php
session_start();
include '../Configuration/Connection.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die('Acceso denegado');
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<div class="row g-4">
    <div class="col-12 col-lg-6 form-container" id="FormAddFormContainer">
        <div class="card shadow-lg border-primary">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0"><i class="bi bi-input-cursor me-2"></i>Agregar Campos Personalizados</h3>
            </div>
            <div class="card-body">
                <form action="./Admin/add_fields.php" id="FormAddForm" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <h4>Data Type <span>*</span></h4>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tipo de Dato <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" name="data_type" required>
                            <option value="">Select Data Type</option>
                            <option value="Date">Date</option>
                            <option value="INTEGER">Integer</option>
                            <option value="VARCHAR(255)">Varchar</option>
                            <option value="BOOLEAN">Boolean</option>
                            <option value="BIGINT">BigInteger</option>
                        </select>
                        <div id="boolean" class="alert alert-warning mt-2" style="display: none;">
                            Warning: BOOLEAN data type selected. this function may not work correctly
                        </div>
                        <script>
                            document.querySelector('select[name="data_type"]').addEventListener('change', function() {
                                if (this.value === 'BOOLEAN') {
                                    document.getElementById('boolean').style.display = 'block';
                                } else {
                                    document.getElementById('boolean').style.display = 'none';
                                }
                            });
                        </script>
                        <h4>Name of Data <span>*</span></h4>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nombre del Campo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="field_name" name="field_name" placeholder="Ej: modelo_equipo" required onfocus="showNetworkAddressInfo()" oninput="validateInput()">
                            <div id="networkAddressInfo" class="alert alert-info mt-2" style="display: none;">
                                The field name should start with a letter and can contain letters, numbers, and underscores. No spaces or special characters allowed.
                            </div>
                            <div id="errorMessage" class="alert alert-danger mt-2" style="display: none;">
                                The field cannot contain only numbers.
                            </div>
                            <script>
                                function showNetworkAddressInfo() {
                                    document.getElementById("networkAddressInfo").style.display = "block";
                                }

                                function validateInput() {
                                    const field = document.getElementById("field_name");
                                    const errorMessage = document.getElementById("errorMessage");
                                    if (/^\d+$/.test(field.value)) {
                                        errorMessage.style.display = "block";
                                    } else {
                                        errorMessage.style.display = "none";
                                    }
                                }

                                function validateForm() {
                                    const field = document.getElementById("field_name").value;
                                    if (/^\d+$/.test(field)) {
                                        alert("The field cannot contain only numbers.");
                                        return false;
                                    }
                                    return true;
                                }
                            </script>
                            <h4>Select Table <span>*</span></h4>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Tabla Destino <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" name="table_name" required>
                                    <option value="">Select Table</option>
                                    <option value="usuarios_equipos">Users</option>
                                    <option value="equipos">Assets</option>
                                </select>
                                <div class="d-grid mt-4">
                                        <button id="Addfield" type="submit" onclick="return validateForm()" class="btn btn-primary btn-lg px-5">
                                            <i class="bi bi-plus-circle me-2"></i>Agregar Campo
                                        </button>
                                    </div>
                </form>

                <script>
                    // Mapeo de formularios en orden
                    const formOrder = ['FormAddFormContainer', 'FormAddTableContainer'];

                    document.addEventListener('DOMContentLoaded', () => {
                        // Ocultar todos los formularios inicialmente
                        document.querySelectorAll('.form-container').forEach(container => {
                            container.classList.remove('active-form');
                        });
                        // Mostrar primer formulario con animación
                        setTimeout(() => {
                            const firstFormContainer = document.querySelector(`#${formOrder[0]}`).closest('.form-container');
                            if (firstFormContainer) {
                                firstFormContainer.classList.add('active-form');
                            }
                        }, 100);
                    });
                </script>
            </div>
            </form>
        </div>
        <div class="card-footer bg-light">
            <small class="text-muted">Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
        </div>
    </div>
</div>
<div class="form-container col-md-6" id="FormAddTableContainer">
    <form action="add_fieldsTable.php" method="post" id="FormAddTable" class="shadow border border-secondary">
        <h1 class="text-center">Add Fields to the Main Table</h1>
        <h4>Name Field <span>*</span></h4>
        <?php
        try {
            $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
            AND COLUMN_NAME NOT IN ('assetname', 'serial_number', 'warranty_enddate', 'expired', 'fk_id', 'fk_assetname',
            'last_user', 'job_title', 'cedula', 'HeadSet', 'fecha_salida','purchase_country','user_status','status_change') 
            ORDER BY TABLE_NAME, ORDINAL_POSITION";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                echo '<div class="row row-cols-1 row-cols-md-3 g-3 mb-4">';
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="col">';
                    echo '<div class="form-check form-check-lg border rounded p-3">';
                    echo '<input class="form-check-input me-3" type="checkbox" name="addmfields[]" value="' . htmlspecialchars($row['Field']) . '" id="field_' . htmlspecialchars($row['Field']) . '">';
                    echo '<label class="form-check-label d-flex align-items-center" for="field_' . htmlspecialchars($row['Field']) . '">';
                    echo '<span class="badge bg-secondary me-2">' . htmlspecialchars($row['Type']) . '</span>';
                    echo htmlspecialchars($row['Field']);
                    echo '</label>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>No data found.</p>';
            }
        } catch (PDOException $e) {
            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
        <br>
        <div id="dynamicInput"></div>
        <input type="hidden" name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="d-grid mt-4">
                <button id="AddfieldTable" type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-save me-2"></i>Guardar Campos
                </button>
            </div>

    </form>
</div>