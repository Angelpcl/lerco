DROP TABLE IF EXISTS zonas_rojas;

CREATE TABLE zonas_rojas (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL COMMENT 'nombre',
    estado VARCHAR(200) DEFAULT NULL COMMENT 'estado',
    pais_id INT(10) UNSIGNED NOT NULL COMMENT 'pais_id',
    created_at INT DEFAULT NULL COMMENT 'created_at',
    created_by SMALLINT(5) UNSIGNED COMMENT 'created_by',
    updated_at INT DEFAULT NULL COMMENT 'updated_at',
    updated_by SMALLINT(5) UNSIGNED COMMENT 'updated_by',
    FOREIGN KEY (pais_id) REFERENCES paises_latam(id) ON DELETE CASCADE ON UPDATE CASCADE
);


ALTER TABLE zonas_rojas
ADD UNIQUE KEY unique_code_pais (code, pais_id);

DROP VIEW IF EXISTS view_zonas_rojas;
CREATE VIEW view_zonas_rojas AS
SELECT 
    zonas_rojas.id AS id,
    zonas_rojas.code,
    zonas_rojas.estado,
    zonas_rojas.pais_id,
    paises_latam.nombre AS pais_nombre,
    paises_latam.codigo_iso,
    paises_latam.imagen AS pais_imagen
FROM 
    zonas_rojas
JOIN 
    paises_latam 
ON 
    zonas_rojas.pais_id = paises_latam.id;
