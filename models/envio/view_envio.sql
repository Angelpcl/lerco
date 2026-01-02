SELECT
    `envio`.`id` AS `id`,
    `envio`.`folio` AS `folio`,
    `envio`.`sucursal_emisor_id` AS `sucursal_emisor_id`,
    `sucursal_emisor`.`nombre` AS `sucursal_emisor_nombre`,
    `envio`.`origen` AS `origen`,
    `envio`.`tipo_envio` AS `tipo_envio`,
    `envio`.`is_efectivo` AS `is_efectivo`,
    `envio`.`is_reenvio` AS `is_reenvio`,
    (
        select
            count(0)
        from
            (
                (
                    `viaje_detalle`
                    join `viaje` on((`viaje_detalle`.`viaje_id` = `viaje`.`id`))
                )
                join `envio_detalle` on(
                    (
                        `viaje_detalle`.`paquete_id` = `envio_detalle`.`id`
                    )
                )
            )
        where
            (
                (`viaje`.`status` <> 1)
                and (`viaje`.`tipo_servicio` = 10)
                and (`envio_detalle`.`status` <> 1)
                and (`envio_detalle`.`envio_id` = `envio`.`id`)
                and (
                    `viaje_detalle`.`paquete_id` = `envio_detalle`.`id`
                )
            )
    ) AS `paquetes_viaje_tierra`,
    (
        select
            count(0)
        from
            (
                (
                    `viaje_detalle`
                    join `viaje` on((`viaje_detalle`.`viaje_id` = `viaje`.`id`))
                )
                join `envio_detalle` on(
                    (
                        `viaje_detalle`.`paquete_id` = `envio_detalle`.`id`
                    )
                )
            )
        where
            (
                (`viaje`.`status` <> 1)
                and (`viaje`.`tipo_servicio` = 20)
                and (`envio_detalle`.`status` <> 1)
                and (`envio_detalle`.`envio_id` = `envio`.`id`)
                and (
                    `viaje_detalle`.`paquete_id` = `envio_detalle`.`id`
                )
            )
    ) AS `paquetes_viaje_lax`,
    `envio`.`is_recoleccion` AS `is_recoleccion`,
    `envio`.`costo_reenvio` AS `costo_reenvio`,
    `envio`.`cliente_emisor_id` AS `cliente_emisor_id`,
    concat_ws(
        ' ',
        `cliente_emisor`.`nombre`,
        `cliente_emisor`.`apellidos`
    ) AS `nombre_emisor`,
    `envio`.`promocion_id` AS `promocion_id`,
    `envio`.`promocion_complemento_id` AS `promocion_complemento_id`,
    `envio`.`codigo_promocional_id` AS `codigo_promocional_id`,
    `envio`.`descuento_manual` AS `descuento_manual`,
    `envio`.`is_descuento_manual` AS `is_descuento_manual`,
    `envio`.`subtotal` AS `subtotal`,
    `envio`.`impuesto` AS `impuesto`,
    `envio`.`total` AS `total`,
    (
        select
            sum(`envio_detalle`.`cantidad`)
        from
            `envio_detalle`
        where
            (
                (`envio_detalle`.`status` <> 1)
                and (`envio_detalle`.`envio_id` = `envio`.`id`)
            )
    ) AS `n_pz`,
    (
        select
            sum(`envio_detalle`.`cantidad_piezas`)
        from
            `envio_detalle`
        where
            (
                (`envio_detalle`.`status` <> 1)
                and (`envio_detalle`.`envio_id` = `envio`.`id`)
            )
    ) AS `n_elementos`,
    (
        select
            sum(`cobro`.`cantidad`)
        from
            `cobro_rembolso_envio` `cobro`
        where
            (
                (`cobro`.`tipo` = 10)
                and (`cobro`.`envio_id` = `envio`.`id`)
            )
    ) AS `monto_pagado`,
    (
        select
            count(0)
        from
            `cobro_rembolso_envio` `cobro`
        where
            (
                (`cobro`.`tipo` = 10)
                and (`cobro`.`envio_id` = `envio`.`id`)
                and (`cobro`.`is_cobro_mex` = 10)
            )
    ) AS `cobros_mex`,
    (
        `envio`.`total` - if(
            (
                select
                    sum(`cobro`.`cantidad`)
                from
                    `cobro_rembolso_envio` `cobro`
                where
                    (
                        (`cobro`.`tipo` = 10)
                        and (`cobro`.`envio_id` = `envio`.`id`)
                    )
            ),
            (
                select
                    sum(`cobro`.`cantidad`)
                from
                    `cobro_rembolso_envio` `cobro`
                where
                    (
                        (`cobro`.`tipo` = 10)
                        and (`cobro`.`envio_id` = `envio`.`id`)
                    )
            ),
            0
        )
    ) AS `monto_deuda`,
    `envio`.`peso_total` AS `peso_total`,
    `envio`.`peso_mex_con_empaque` AS `peso_mex_con_empaque`,
    `envio`.`status` AS `status`,
    `envio`.`comentarios` AS `comentarios`,
    concat_ws(' ', `agente`.`nombre`, `agente`.`apellidos`) AS `agente`,
    `envio`.`pre_created_at` AS `pre_created_at`,
    `envio`.`created_at` AS `created_at`,
    `envio`.`created_by` AS `created_by`,
    concat_ws(' ', `created`.`nombre`, `created`.`apellidos`) AS `created_by_user`,
    `envio`.`updated_at` AS `updated_at`,
    `envio`.`updated_by` AS `updated_by`,
    concat_ws(' ', `updated`.`nombre`, `updated`.`apellidos`) AS `updated_by_user`
FROM
    (
        (
            (
                (
                    (
                        `envio`
                        left join `user` `created` on((`envio`.`created_by` = `created`.`id`))
                    )
                    left join `user` `updated` on((`envio`.`updated_by` = `updated`.`id`))
                )
                join `sucursal` `sucursal_emisor` on(
                    (
                        `envio`.`sucursal_emisor_id` = `sucursal_emisor`.`id`
                    )
                )
            )
            left join `cliente` `cliente_emisor` on(
                (
                    `envio`.`cliente_emisor_id` = `cliente_emisor`.`id`
                )
            )
        )
        left join `user` `agente` on((`agente`.`id` = `cliente_emisor`.`asignado_id`))
    )
    /********************************************************************************************************/
    /********************************************************************************************************/
DROP VIEW IF EXISTS `view_envio`;

CREATE VIEW `view_envio` AS

    SELECT
    e.id AS id,
    e.folio AS folio,
    e.sucursal_emisor_id AS sucursal_emisor_id,
    se.nombre AS sucursal_emisor_nombre,
    e.origen AS origen,
    e.tipo_envio AS tipo_envio,
    e.is_efectivo AS is_efectivo,
    e.is_reenvio AS is_reenvio,
    COALESCE(viaje_tierra.paquetes, 0) AS paquetes_viaje_tierra,
    COALESCE(viaje_lax.paquetes, 0) AS paquetes_viaje_lax,
    e.is_recoleccion AS is_recoleccion,
    e.costo_reenvio AS costo_reenvio,
    e.cliente_emisor_id AS cliente_emisor_id,
    CONCAT_WS(' ', ce.nombre, ce.apellidos) AS nombre_emisor,
    e.promocion_id AS promocion_id,
    e.promocion_complemento_id AS promocion_complemento_id,
    e.codigo_promocional_id AS codigo_promocional_id,
    e.descuento_manual AS descuento_manual,
    e.is_descuento_manual AS is_descuento_manual,
    e.subtotal AS subtotal,
    e.impuesto AS impuesto,
    e.total AS total,
    COALESCE(ed_total.cantidad, 0) AS n_pz,
    COALESCE(ed_total.cantidad_piezas, 0) AS n_elementos,
    COALESCE(cobro_total.monto_pagado, 0) AS monto_pagado,
    COALESCE(cobro_mex.count_mex, 0) AS cobros_mex,
    e.total - COALESCE(cobro_total.monto_pagado, 0) AS monto_deuda,
    e.peso_total AS peso_total,
    e.peso_mex_con_empaque AS peso_mex_con_empaque,
    e.status AS status,
    e.comentarios AS comentarios,
    CONCAT_WS(' ', a.nombre, a.apellidos) AS agente,
    e.pre_created_at AS pre_created_at,
    e.created_at AS created_at,
    e.created_by AS created_by,
    CONCAT_WS(' ', created.nombre, created.apellidos) AS created_by_user,
    e.updated_at AS updated_at,
    e.updated_by AS updated_by,
    CONCAT_WS(' ', updated.nombre, updated.apellidos) AS updated_by_user
FROM envio e
LEFT JOIN sucursal se ON e.sucursal_emisor_id = se.id
LEFT JOIN cliente ce ON e.cliente_emisor_id = ce.id
LEFT JOIN user a ON ce.asignado_id = a.id
LEFT JOIN user created ON e.created_by = created.id
LEFT JOIN user updated ON e.updated_by = updated.id
LEFT JOIN (
    SELECT
        ed.envio_id,
        COUNT(*) AS paquetes
    FROM viaje_detalle vd
    JOIN viaje v ON vd.viaje_id = v.id
    JOIN envio_detalle ed ON vd.paquete_id = ed.id
    WHERE v.status <> 1 AND v.tipo_servicio = 10 AND ed.status <> 1
    GROUP BY ed.envio_id
) viaje_tierra ON e.id = viaje_tierra.envio_id
LEFT JOIN (
    SELECT
        ed.envio_id,
        COUNT(*) AS paquetes
    FROM viaje_detalle vd
    JOIN viaje v ON vd.viaje_id = v.id
    JOIN envio_detalle ed ON vd.paquete_id = ed.id
    WHERE v.status <> 1 AND v.tipo_servicio = 20 AND ed.status <> 1
    GROUP BY ed.envio_id
) viaje_lax ON e.id = viaje_lax.envio_id
LEFT JOIN (
    SELECT
        ed.envio_id,
        SUM(ed.cantidad) AS cantidad,
        SUM(ed.cantidad_piezas) AS cantidad_piezas
    FROM envio_detalle ed
    WHERE ed.status <> 1
    GROUP BY ed.envio_id
) ed_total ON e.id = ed_total.envio_id
LEFT JOIN (
    SELECT
        cr.envio_id,
        SUM(cr.cantidad) AS monto_pagado
    FROM cobro_rembolso_envio cr
    WHERE cr.tipo = 10
    GROUP BY cr.envio_id
) cobro_total ON e.id = cobro_total.envio_id
LEFT JOIN (
    SELECT
        cr.envio_id,
        COUNT(*) AS count_mex
    FROM cobro_rembolso_envio cr
    WHERE cr.tipo = 10 AND cr.is_cobro_mex = 10
    GROUP BY cr.envio_id
) cobro_mex ON e.id = cobro_mex.envio_id;
