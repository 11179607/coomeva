USE coomeva;

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
