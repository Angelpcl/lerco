ALTER TABLE
    `cliente`
ADD
    COLUMN `country_id` INT(11) default NULL COMMENT 'PAIS DE ORIGEN';

ALTER TABLE
    `cliente`
ADD
    COLUMN `is_zona_riesgo` INT(11) default 10 COMMENT 'ZONA DE RIEZGO';

ALTER TABLE
    `envio`
ADD
    COLUMN `is_zona_riesgo` INT(11) default 0 COMMENT 'ZONA DE RIEZGO';

SELECT
    `cliente`.`id` AS `id`,
    `cliente`.`country_id` AS `id`,
    `cliente`.`titulo_personal_id` AS `titulo_personal_id`,
    `titulo_personal`.`singular` AS `titulo_personal`,
    `tipo_cliente`.`singular` AS `tipo_cliente`,
    `tipo_cliente`.`id` AS `tipo_cliente_id`,
    concat_ws(
        ' ',
        trim(`cliente`.`nombre`),
        trim(`cliente`.`apellidos`)
    ) AS `nombre_completo`,
    `cliente`.`nombre` AS `nombre`,
    `cliente`.`apellidos` AS `apellidos`,
    `cliente`.`email` AS `email`,
    `cliente`.`sexo` AS `sexo`,
    `cliente`.`country_id` AS `country_id`,
    (
        select
            `ch`.`tipo_respuesta_id`
        from
            `cliente_historico_call` `ch`
        where
            (`ch`.`cliente_id` = `cliente`.`id`)
        order by
            `ch`.`id` desc
        limit
            1
    ) AS `status_call_id`,
    (
        select
            `status_llamada`.`singular`
        from
            (
                `cliente_historico_call` `ch`
                join `esys_lista_desplegable` `status_llamada` on(
                    (`ch`.`tipo_respuesta_id` = `status_llamada`.`id`)
                )
            )
        where
            (`ch`.`cliente_id` = `cliente`.`id`)
        order by
            `ch`.`id` desc
        limit
            1
    ) AS `status_call`,
    `cliente`.`origen` AS `origen`,
    `cliente`.`asignado_id` AS `asignado_id`,
    `cliente`.`telefono` AS `telefono`,
    `cliente`.`telefono_movil` AS `telefono_movil`,
    `cliente`.`status` AS `status`,
    `cliente`.`notas` AS `notas`,
    `cliente`.`created_at` AS `created_at`,
    `cliente`.`created_by` AS `created_by`,
    concat_ws(' ', `created`.`nombre`, `created`.`apellidos`) AS `created_by_user`,
    `cliente`.`updated_at` AS `updated_at`,
    `cliente`.`updated_by` AS `updated_by`,
    concat_ws(' ', `updated`.`nombre`, `updated`.`apellidos`) AS `updated_by_user`
FROM
    (
        (
            (
                (
                    `cliente`
                    left join `esys_lista_desplegable` `titulo_personal` on(
                        (
                            `cliente`.`titulo_personal_id` = `titulo_personal`.`id`
                        )
                    )
                )
                left join `esys_lista_desplegable` `tipo_cliente` on(
                    (
                        `cliente`.`tipo_cliente_id` = `tipo_cliente`.`id`
                    )
                )
            )
            left join `user` `created` on((`cliente`.`created_by` = `created`.`id`))
        )
        left join `user` `updated` on((`cliente`.`updated_by` = `updated`.`id`))
    )