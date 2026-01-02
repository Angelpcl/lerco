CREATE TABLE `promociones` (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha_inicio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'fecha de inicio',
    fecha_fin TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'fecha de termino',
    status INT(10) NOT NULL COMMENT 'status',

    costo_libra_peso_suc DECIMAL(10,2) NOT NULL COMMENT 'costo por libra de peso para sucursal',
    costo_libra_peso_cli DECIMAL(10,2) NOT NULL COMMENT 'costo por libra de peso para cliente',

    costo_libra_caja_cli DECIMAL(10,2) NOT NULL COMMENT 'costo por libra de peso para cliente',
    costo_libra_caja_suc DECIMAL(10,2) NOT NULL COMMENT 'costo por libra de peso para sucursal',

    costo_caja_limite_cli DECIMAL(10,2) NOT NULL COMMENT 'costo por caja limite para cliente',
    costo_caja_limite_suc DECIMAL(10,2) NOT NULL COMMENT 'costo por caja limite para sucursal',

    sucursal_id INT(10) UNSIGNED NOT NULL COMMENT 'sucursal_id',
    created_at INT(10) DEFAULT NULL COMMENT 'created_at',
    created_by SMALLINT(5) UNSIGNED COMMENT 'created_by',
    updated_at INT(10) DEFAULT NULL COMMENT 'updated_at',
    updated_by SMALLINT(5) UNSIGNED COMMENT 'updated_by',
    FOREIGN KEY (sucursal_id) REFERENCES sucursal(id)
);


SHOW VARIABLES LIKE 'event_scheduler';
SET GLOBAL event_scheduler = ON;


CREATE EVENT IF NOT EXISTS update_status_event
ON SCHEDULE EVERY 1 MINUTE
DO
BEGIN
    UPDATE promociones
    SET status = 20
    WHERE status = 10
      AND fecha_fin < CURDATE();
END;

SHOW EVENTS;
SHOW CREATE EVENT update_status_event;
/**********************************************************************/
DELIMITER //
CREATE EVENT IF NOT EXISTS update_status_event
ON SCHEDULE EVERY 1 MINUTE
DO
BEGIN
    UPDATE promociones
    SET status = 20
    WHERE status = 10
      AND fecha_fin < CURDATE();
END//
DELIMITER ;
/***************************************************************************/
-- Eliminar el evento existente
DROP EVENT IF EXISTS update_status_event;

-- Crear el nuevo evento que se ejecuta cada 12 horas
DELIMITER //

CREATE EVENT IF NOT EXISTS update_status_event
ON SCHEDULE EVERY 12 HOUR
DO
BEGIN
    UPDATE promociones
    SET status = 20
    WHERE status = 10
      AND fecha_fin < CURDATE();
END//

DELIMITER ;
/***************************************************************************/
                                    -- VIEW
/**************************************************************************/

DROP VIEW IF EXISTS view_promos;
CREATE VIEW view_promos AS
SELECT 
    pr.id AS id,
    pr.fecha_inicio,
    pr.fecha_fin,
    pr.status,
    u.nombre AS nombre,
    s.nombre AS sucursal_nombre,
    u.created_at AS created_at,
    u.created_by AS created_by,
    u.updated_at AS updated_at
FROM 
    promociones AS pr
JOIN 
    user AS u ON pr.created_by = u.id
JOIN 
    sucursal AS s ON pr.sucursal_id = s.id;



