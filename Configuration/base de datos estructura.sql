-- Crear base de datos
CREATE DATABASE IF NOT EXISTS garantias;
USE garantias;

-- Tabla de administrador
CREATE TABLE IF NOT EXISTS configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave_admin VARCHAR(255) NOT NULL -- Clave única del administrador
);

-- Tabla principal de equipos (actualizada con todos los campos necesarios)
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
    SIMcard VARCHAR(100)
);

-- Tabla de usuarios (actualizada con todos los campos necesarios)
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

-- Vistas actualizadas
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

-- Índices adicionales para mejorar el rendimiento
CREATE INDEX idx_usuarios_cedula ON usuarios_equipos(cedula);
CREATE INDEX idx_equipos_serial ON equipos(serial_number);
CREATE INDEX idx_usuarios_asset ON usuarios_equipos(fk_assetname);