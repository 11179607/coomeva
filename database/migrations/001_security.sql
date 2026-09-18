USE coomeva;

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
