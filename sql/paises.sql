DROP TABLE IF EXISTS paises_latam;

CREATE TABLE paises_latam (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL COMMENT 'nombre',
    codigo_iso VARCHAR(10) DEFAULT NULL COMMENT 'CODIGO ISO',
    imagen VARCHAR(255) DEFAULT NULL COMMENT 'imagen',
    created_at INT DEFAULT NULL COMMENT 'created_at',
    created_by smallint(5) UNSIGNED COMMENT 'created_by',
    updated_at INT DEFAULT NULL COMMENT 'updated_at',
    updated_by smallint(5) UNSIGNED COMMENT 'updated_by'
);