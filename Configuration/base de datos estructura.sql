-- Crear base de datos
CREATE DATABASE garantias;

-- Usar base de datos
USE garantias;

-- Crear tablas
CREATE TABLE `configuracion_sistema` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `clave_admin` varchar(255) NOT NULL
);

CREATE TABLE `equipos` (
  `assetname` varchar(50) PRIMARY KEY,
  `serial_number` varchar(50) UNIQUE,
  `purchase_country` varchar(50),
  `warranty_enddate` varchar(50),
  `expired` varchar(50),
  `new_laptop` varchar(50),
  `fk_id` int,
  `HeadSet` varchar(255),
  `asset_status` varchar(25),
  `asset_observations` text,
  `Monitor` varchar(50),
  `monitor_status` varchar(25),
  `monitor_observations` text,
  `headset_status` varchar(25),
  `headset_observations` text
);

CREATE TABLE `usuarios_equipos` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `fk_assetname` varchar(50),
  `user_status` varchar(50) NOT NULL,
  `last_user` varchar(50) NOT NULL,
  `job_title` varchar(50),
  `status_change` varchar(50),
  `cedula` int,
  `Dongle` bigint,
  `Carnet` varchar(255),
  `LLave` varchar(255),
  `Tipo_ID` varchar(40),
  `fecha_salida` date,
  `dongle_status` varchar(25),
  `dongle_observations` text,
  `Celular` varchar(50),
  `celular_status` varchar(25),
  `celular_observations` text
);

ALTER TABLE `equipos` ADD FOREIGN KEY (`fk_id`) REFERENCES `configuracion_sistema` (`id`);
ALTER TABLE `usuarios_equipos` ADD FOREIGN KEY (`fk_assetname`) REFERENCES `equipos` (`assetname`);

-- Insertar datos
/* Crear registro único para el administrador */
INSERT INTO configuracion_sistema (clave_admin) VALUES ('Sena@1234');

-- Crear vistas
/* Vista de consulta general */
CREATE VIEW vista_equipos_usuarios AS
SELECT 
    e.assetname,
    e.serial_number,
    e.purchase_country,
    e.warranty_enddate,
    e.expired,
    e.new_laptop,
    e.HeadSet,
    ue.user_status,
    ue.last_user,
    ue.job_title,
    ue.status_change,
    ue.cedula,
    ue.Dongle,
    ue.Carnet,
    ue.LLave,
    ue.Tipo_ID,
    ue.fecha_salida
FROM equipos e
INNER JOIN usuarios_equipos ue
ON e.assetname = ue.fk_assetname;
