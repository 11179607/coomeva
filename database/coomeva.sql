CREATE DATABASE IF NOT EXISTS coomeva
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE coomeva;

CREATE TABLE IF NOT EXISTS afiliados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(30) NOT NULL UNIQUE,
    nombres VARCHAR(150) NULL,
    apellidos VARCHAR(150) NULL,
    email VARCHAR(190) NULL,
    celular VARCHAR(30) NULL,
    edad INT NULL,
    genero VARCHAR(30) NULL,
    ciudad VARCHAR(100) NULL,
    grupo VARCHAR(100) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_afiliados_cedula_activo ON afiliados (cedula, activo);

CREATE TABLE IF NOT EXISTS asesores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    enlace_whatsapp VARCHAR(255) NULL,
    estado ENUM('disponible','ocupado','inactivo') NOT NULL DEFAULT 'disponible',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_asignado DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_asesores_disponibilidad ON asesores (activo, estado, ultimo_asignado);

CREATE TABLE IF NOT EXISTS sesiones_whatsapp (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    telefono VARCHAR(30) NOT NULL UNIQUE,
    cedula VARCHAR(30) NULL,
    afiliado_id BIGINT UNSIGNED NULL,
    tipo_usuario ENUM('afiliado','no_afiliado') NULL,
    estado VARCHAR(50) NOT NULL DEFAULT 'solicita_cedula',
    ultima_interaccion DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sesion_afiliado
        FOREIGN KEY (afiliado_id) REFERENCES afiliados(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS conversaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    telefono VARCHAR(30) NOT NULL,
    afiliado_id BIGINT UNSIGNED NULL,
    asesor_id BIGINT UNSIGNED NULL,
    estado ENUM('bot','esperando_asesor','asesor','cerrada') NOT NULL DEFAULT 'bot',
    iniciado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cerrado_en DATETIME NULL,
    CONSTRAINT fk_conversacion_afiliado
        FOREIGN KEY (afiliado_id) REFERENCES afiliados(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_conversacion_asesor
        FOREIGN KEY (asesor_id) REFERENCES asesores(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS mensajes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversacion_id BIGINT UNSIGNED NOT NULL,
    direccion ENUM('entrada','salida') NOT NULL,
    mensaje TEXT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensaje_conversacion
        FOREIGN KEY (conversacion_id) REFERENCES conversaciones(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS rate_limits (
    scope VARCHAR(50) NOT NULL,
    identifier_hash CHAR(64) NOT NULL,
    window_started_at DATETIME NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (scope, identifier_hash)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS whatsapp_eventos (
    message_id VARCHAR(120) NOT NULL PRIMARY KEY,
    telefono VARCHAR(30) NOT NULL,
    recibido_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    procesado_en DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consentimientos_web (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_hash CHAR(64) NOT NULL UNIQUE,
    ip_hash CHAR(64) NOT NULL,
    documento_hash CHAR(64) NULL,
    afiliado_id BIGINT UNSIGNED NULL,
    terminos_version VARCHAR(30) NOT NULL,
    datos_version VARCHAR(30) NOT NULL,
    aceptado_en DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_consentimiento_afiliado
        FOREIGN KEY (afiliado_id) REFERENCES afiliados(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Asesores de prueba. Se conservan porque ya forman parte del flujo probado.
INSERT INTO asesores (nombre, telefono, enlace_whatsapp, estado)
SELECT 'Asesor Demo 1', '573000000001', 'https://wa.me/573000000001', 'disponible'
WHERE NOT EXISTS (SELECT 1 FROM asesores WHERE telefono = '573000000001');

INSERT INTO asesores (nombre, telefono, enlace_whatsapp, estado)
SELECT 'Asesor Demo 2', '573000000002', 'https://wa.me/573000000002', 'disponible'
WHERE NOT EXISTS (SELECT 1 FROM asesores WHERE telefono = '573000000002');
