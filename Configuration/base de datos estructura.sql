-- Crear base de datos
CREATE DATABASE IF NOT EXISTS garantias CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE garantias;

-- Tabla de usuarios para autenticación (NUEVA)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'manager', 'user') NOT NULL DEFAULT 'user',
    remember_token VARCHAR(64) NULL,
    reset_token VARCHAR(64) NULL,
    reset_token_expires DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Tabla de administrador (ORIGINAL)
CREATE TABLE IF NOT EXISTS configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave_admin VARCHAR(255) NOT NULL -- Clave única del administrador
);

-- Tabla principal de equipos (ORIGINAL)
CREATE TABLE IF NOT EXISTS equipos (
    assetname VARCHAR(100) PRIMARY KEY,
    serial_number VARCHAR(100),
    purchase_country VARCHAR(100),
    warranty_enddate VARCHAR(100),
    expired VARCHAR(10),
    new_laptop VARCHAR(10),
    asset_status VARCHAR(25),
    asset_observations TEXT,
    Monitor VARCHAR(100),
    monitor_status VARCHAR(25),
    monitor_observations TEXT,
    HeadSet VARCHAR(100),
    headset_status VARCHAR(25),
    headset_observations TEXT,
    Dongle VARCHAR(100),
    dongle_status VARCHAR(25),
    dongle_observations TEXT,
    Celular VARCHAR(100),
    celular_status VARCHAR(25),
    celular_observations TEXT,
    SIMcard VARCHAR(100),
    asset_photo VARCHAR(255)
);

-- Tabla de usuarios (ORIGINAL)
CREATE TABLE IF NOT EXISTS usuarios_equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fk_assetname VARCHAR(100) NOT NULL,
    user_status VARCHAR(100) NOT NULL,
    last_user VARCHAR(100) NOT NULL,
    job_title VARCHAR(100),
    status_change VARCHAR(100),
    cedula VARCHAR(100),
    Carnet VARCHAR(100),
    LLave VARCHAR(100),
    Tipo_ID VARCHAR(100),
    fecha_salida DATE,
    FOREIGN KEY (fk_assetname) REFERENCES equipos(assetname) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Insertar datos iniciales
INSERT INTO configuracion_sistema (clave_admin) VALUES ('Sena@1234');

-- Insertar usuario administrador predeterminado
INSERT INTO users (username, password, email, full_name, role) 
VALUES ('admin', 'admin123', 'admin@example.com', 'System Administrator', 'admin');

-- Vistas (ORIGINALES)
CREATE OR REPLACE VIEW vista_equipos_usuarios AS
SELECT 
    e.assetname,
    e.serial_number,
    e.purchase_country,
    e.warranty_enddate,
    e.expired,
    e.new_laptop,
    e.asset_status,
    e.asset_observations,
    e.Monitor,
    e.monitor_status,
    e.monitor_observations,
    e.HeadSet,
    e.headset_status,
    e.headset_observations,
    e.Dongle,
    e.dongle_status,
    e.dongle_observations,
    e.Celular,
    e.celular_status,
    e.celular_observations,
    e.SIMcard,
    e.asset_photo,
    ue.user_status,
    ue.last_user,
    ue.job_title,
    ue.status_change,
    ue.cedula,
    ue.Carnet,
    ue.LLave,
    ue.Tipo_ID,
    ue.fecha_salida
FROM equipos e
LEFT JOIN usuarios_equipos ue ON e.assetname = ue.fk_assetname;

CREATE OR REPLACE VIEW vista_claves_admin AS
SELECT id, clave_admin
FROM configuracion_sistema
WITH CHECK OPTION;

-- Índices adicionales para mejorar el rendimiento (ORIGINALES)
CREATE INDEX idx_usuarios_cedula ON usuarios_equipos(cedula);
CREATE INDEX idx_equipos_serial ON equipos(serial_number);
CREATE INDEX idx_usuarios_asset ON usuarios_equipos(fk_assetname);

-- Índices adicionales para la tabla de usuarios (NUEVOS)
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_remember_token ON users(remember_token);

-- Script para actualizar la tabla de usuarios y asegurar que el usuario admin existe
-- con la contraseña correcta

-- Verificar si el usuario admin existe
SELECT COUNT(*) FROM users WHERE username = 'admin';

-- Si el usuario no existe, crearlo
INSERT INTO users (username, password, email, full_name, role)
SELECT 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'System Administrator', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

-- Si el usuario existe, actualizar su contraseña
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';

-- Verificar que el usuario existe con la contraseña correcta
SELECT id, username, email, role FROM users WHERE username = 'admin';

