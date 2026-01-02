ALTER TABLE `producto` 
ADD COLUMN `costo_suc` DECIMAL(12,2) DEFAULT 0 COMMENT 'costo_suc';

ALTER TABLE `producto` 
ADD COLUMN `is_caja_sin_limite_id` INT(10) DEFAULT NULL COMMENT 'CAJA SIN LIMITE';

ALTER TABLE `producto` 
ADD COLUMN `pais_id` INT(10) DEFAULT NULL COMMENT 'País';

ALTER TABLE `producto` 
ADD COLUMN `costo_libra` DECIMAL(12,2) DEFAULT 0 COMMENT 'Costo por libra';

DROP TABLE IF EXISTS `caja_sin_limite`;
CREATE TABLE `caja_sin_limite` (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    largo INT(10) DEFAULT NULL COMMENT 'Largo',
    ancho INT(10) DEFAULT NULL COMMENT 'Ancho',
    alto INT(10) DEFAULT NULL COMMENT 'Alto',
    created_at INT DEFAULT NULL COMMENT 'created_at',
    created_by smallint(5) UNSIGNED COMMENT 'created_by',
    updated_at INT DEFAULT NULL COMMENT 'updated_at',
    updated_by smallint(5) UNSIGNED COMMENT 'updated_by',
    costo_suc DECIMAL(12,2) DEFAULT 0 COMMENT 'costo_suc',
    costo_cli DECIMAL(12,2) DEFAULT 0 COMMENT 'costo_cli'
);

ALTER TABLE `caja_sin_limite` 
ADD COLUMN `pais_id` INT(10) DEFAULT NULL COMMENT 'País';

