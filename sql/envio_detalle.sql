DROP TABLE IF EXISTS `detail_envio_product`;

CREATE TABLE `detail_envio_product` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `detalle_envio_id` int(11) NOT NULL,
    `detalle_json` text NOT NULL,
    PRIMARY KEY (`id`)
);


ALTER TABLE `envio_detalle` 
ADD COLUMN `detalle_id` INT(11) DEFAULT NULL COMMENT 'Detalle';



ALTER TABLE `envio_detalle`
ADD COLUMN `pais_destino_id` INT(11) DEFAULT NULL COMMENT 'pais de destino';


ALTER TABLE `envio`
ADD COLUMN `pais_destino_id` INT(11) DEFAULT NULL COMMENT 'pais de destino';