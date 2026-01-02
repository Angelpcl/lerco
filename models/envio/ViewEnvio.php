<?php

namespace app\models\Envio;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\db\Expression;
use app\models\envio\EnvioDetalle;
use app\models\envio\EnvioPromocion;
use app\models\esys\EsysListaDesplegable;
use app\models\viaje\Viaje;
use app\models\envio\Envio;
use app\models\cobro\CobroRembolsoEnvio;

/**
 * This is the model class for table "view_envio".
 *
 * @property int $id Envio ID
 * @property int $sucursal_emisor_id Sucursal emisor ID
 * @property string $sucursal_emisor_nombre
 * @property int $sucursal_receptor_id Sucursal receptor ID
 * @property string $sucursal_receptor_nombre
 * @property int $origen Origen
 * @property int $tipo_envio Tipo de envio
 * @property int $cliente_emisor_id Cliente emisor ID
 * @property string $nombre_emisor
 * @property int $cliente_receptor_id Cliente receptor ID
 * @property string $nombre_receptor
 * @property int $promocion_id Promoción ID
 * @property int $promocion_complemento_id Promocion complemento ID
 * @property int $codigo_promocional_id Código promocional ID
 * @property double $descuento_manual Descuento Manual
 * @property int $is_descuento_manual Aplica descuento manual
 * @property double $subtotal SubTotal
 * @property double $impuesto Impuesto
 * @property double $total Total
 * @property double $peso_total Peso total del envio
 * @property int $status Estatus
 * @property string $comentarios Comentarios  / Notas
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewEnvio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_envio';
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sucursal_emisor_id' => 'Sucursal Emisor ID',
            'sucursal_emisor_nombre' => 'Sucursal Emisor Nombre',
            'sucursal_receptor_id' => 'Sucursal Receptor ID',
            'sucursal_receptor_nombre' => 'Sucursal Receptor Nombre',
            'origen' => 'Origen',
            'tipo_envio' => 'Tipo Envio',
            'cliente_emisor_id' => 'Cliente Emisor ID',
            'nombre_emisor' => 'Nombre Emisor',
            'nombre_receptor' => 'Nombre Receptor',
            'promocion_id' => 'Promocion ID',
            'promocion_complemento_id' => 'Promocion Complemento ID',
            'codigo_promocional_id' => 'Codigo Promocional ID',
            'descuento_manual' => 'Descuento Manual',
            'is_descuento_manual' => 'Is Descuento Manual',
            'subtotal' => 'Subtotal',
            'impuesto' => 'Impuesto',
            'total' => 'Total',
            'peso_total' => 'Peso Total',
            'status' => 'Status',
            'comentarios' => 'Comentarios',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'created_by_user' => 'Created By User',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'updated_by_user' => 'Updated By User',
        ];
    }


    //------------------------------------------------------------------------------------------------//
    // JSON Bootstrap Table
    //------------------------------------------------------------------------------------------------//
    public static function getJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;
        //$limit = 20;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "SQL_CALC_FOUND_ROWS `id`",
                'folio',
                'sucursal_emisor_id',
                'sucursal_emisor_nombre',
                'origen',
                'tipo_envio',
                'cliente_emisor_id',
                'nombre_emisor',
                'promocion_id',
                'is_reenvio',
                'is_recoleccion',
                'is_efectivo',
                'costo_reenvio',
                'promocion_complemento_id',
                'codigo_promocional_id',
                'descuento_manual',
                'is_descuento_manual',
                'paquetes_viaje_tierra',
                'paquetes_viaje_lax',
                'subtotal',
                'impuesto',
                'total',
                'n_elementos',
                'n_pz',
                'monto_pagado',
                'cobros_mex',
                'monto_deuda',
                'peso_total',
                'status',
                'comentarios',
                'agente',
                'pre_created_at',
                'created_at',
                'created_by',
                'created_by_user',
                'updated_at',
                'updated_by',
                'updated_by_user',
            ])
            ->from(self::tableName())
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);


        /************************************
        / Filtramos por sucursal
        /***********************************/

        if (isset($filters['date_range']) && $filters['date_range']) {
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $query->andWhere(['between', 'created_at', $date_ini, $date_fin]);
        }

        if (!Yii::$app->user->can('admin')) {
            if (Yii::$app->user->identity->sucursal_id)
                $query->andWhere(['sucursal_emisor_id' =>  Yii::$app->user->identity->sucursal_id]);
        }



        //if (isset($filters['sucursal_emisor']) && $filters['sucursal_emisor'])
        // $query->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_emisor']]);

        if (isset($filters['folio']) && $filters['folio'])
            $query->andWhere(['folio' =>  $filters['folio']]);

        if (isset($filters['status_id']) && $filters['status_id'])
            $query->andWhere(['status' =>  $filters['status_id']]);

        if (isset($filters['sucursal_receptor']) && $filters['sucursal_receptor'])
            $query->andWhere(['sucursal_receptor_id' =>  $filters['sucursal_receptor']]);

        if (isset($filters['off_status']) && $filters['off_status'])
            $query->andWhere(['<>', 'status', $filters['off_status']]);

        /************************************
        / Filtramos la consulta
        /***********************************/
        if (isset($filters['tipo_envio']) && $filters['tipo_envio'])
            $query->andWhere(['tipo_envio' =>  $filters['tipo_envio']]);

        if (isset($filters['historial_cliente_id']) && $filters['historial_cliente_id']) {
            $query->andFilterWhere([
                'or',
                //['cliente_receptor_id'  => $filters['historial_cliente_id']],
                ['cliente_emisor_id'    => $filters['historial_cliente_id']]
            ]);
        }


        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
                ['like', 'sucursal_emisor_nombre', $search],
                ['like', 'nombre_emisor', $search],
                ['like', 'folio', $search],
                ['like', 'agente', $search],
            ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getRecibidosJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "SQL_CALC_FOUND_ROWS `view_envio`.`id`",
                'view_envio.folio',
                'view_envio.sucursal_emisor_id',
                'view_envio.sucursal_emisor_nombre',
                'view_envio.origen',
                'view_envio.tipo_envio',
                'view_envio.cliente_emisor_id',
                'view_envio.nombre_emisor',
                'view_envio.promocion_id',
                'view_envio.is_reenvio',
                'concat_ws(" ",`cliente_receptor`.`nombre`,`cliente_receptor`.`apellidos`) AS `nombre_receptor`',
                '`sucursal_receptor`.`nombre` AS `sucursal_receptor_nombre`',
                'view_envio.costo_reenvio',
                'view_envio.promocion_complemento_id',
                'view_envio.codigo_promocional_id',
                'view_envio.descuento_manual',
                'view_envio.is_descuento_manual',
                'view_envio.subtotal',
                'view_envio.impuesto',
                'view_envio.total',
                'view_envio.monto_pagado',
                'view_envio.monto_deuda',
                'view_envio.peso_total',
                'view_envio.status',
                'view_envio.comentarios',
                'view_envio.agente',
                'view_envio.created_at',
                'view_envio.created_by',
                'view_envio.created_by_user',
                'view_envio.updated_at',
                'view_envio.updated_by',
                'view_envio.updated_by_user',
            ])
            ->from('view_envio')
            ->innerJoin('envio_detalle env_detalle', 'view_envio.id = env_detalle.envio_id')
            ->innerJoin('cliente cliente_receptor', 'env_detalle.cliente_receptor_id = cliente_receptor.id')
            ->innerJoin('sucursal sucursal_receptor', 'env_detalle.sucursal_receptor_id = sucursal_receptor.id')
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);


        /************************************
        / Filtramos por sucursal
        /***********************************/

        if (isset($filters['date_range']) && $filters['date_range']) {
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $query->andWhere(['between', 'created_at', $date_ini, $date_fin]);
        }


        if (isset($filters['sucursal_emisor']) && $filters['sucursal_emisor'])
            $query->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_emisor']]);

        if (isset($filters['folio']) && $filters['folio'])
            $query->andWhere(['folio' =>  $filters['folio']]);

        if (isset($filters['status_id']) && $filters['status_id'])
            $query->andWhere(['status' =>  $filters['status_id']]);

        if (isset($filters['sucursal_receptor']) && $filters['sucursal_receptor'])
            $query->andWhere(['sucursal_receptor_id' =>  $filters['sucursal_receptor']]);

        if (isset($filters['off_status']) && $filters['off_status'])
            $query->andWhere(['<>', 'status', $filters['off_status']]);

        /************************************
        / Filtramos la consulta
        /***********************************/
        if (isset($filters['tipo_envio']) && $filters['tipo_envio'])
            $query->andWhere(['tipo_envio' =>  $filters['tipo_envio']]);

        if (isset($filters['historial_cliente_id']) && $filters['historial_cliente_id']) {
            $query->andFilterWhere([
                'or',
                ['cliente_receptor_id'  => $filters['historial_cliente_id']],
            ]);
        }


        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
                ['like', 'sucursal_emisor_nombre', $search],
                ['like', 'nombre_emisor', $search],
                ['like', 'folio', $search],
            ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getReporteJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);


        $select = [];



        if (isset($filters['agrupar']['sucursal']) || isset($filters['agrupar']['tipo_envio']) || isset($filters['agrupar']['metodo_pago']) || isset($filters['agrupar']['viaje'])) {

            $select = array_merge($select, [
                'SUM(cobro_rembolso_envio.cantidad) AS cantidad',
                'COUNT(`envio`.`id`) AS envio',
                new Expression('"-" AS id'),
            ]);

            if (isset($filters['agrupar']['sucursal']))
                $select = array_merge($select, [
                    '`envio`.`sucursal_emisor_id`',
                    '`sucursal_emisor`.`nombre` AS `sucursal_emisor_nombre`',
                ]);

            if (isset($filters['agrupar']['tipo_envio']))
                $select = array_merge($select, [
                    '`envio`.`tipo_envio` AS `tipo_envio`',
                ]);

            if (isset($filters['agrupar']['metodo_pago']))
                $select = array_merge($select, [
                    'cobro_rembolso_envio.metodo_pago',
                ]);

            if (isset($filters['agrupar']['viaje'])) {
                $select = array_merge($select, [
                    'SUM(cobro_rembolso_envio.cantidad) AS cantidad',
                    "`envio`.`tipo_envio`",
                    "case `envio`.`tipo_envio`
                    when '10' then  ( SELECT `viaje`.id
                                        FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                            inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                    when '20' then  ( SELECT `viaje`.id
                                        FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                            inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                    when '30' then  ( SELECT `viaje`.id
                                        FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 20
                                            inner join caja_mex on viaje_detalle.paquete_id = caja_mex.id
                                            inner join caja_detalle_mex on caja_mex.id = caja_detalle_mex.caja_mex_id
                                            inner join envio_detalle on caja_detalle_mex.envio_detalle_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                END AS `viaje_id`",

                    "case `envio`.`tipo_envio`
                    when '10' then  ( SELECT `viaje`.fecha_salida
                                        FROM viaje
                                        inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                        inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                        where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                    when '20' then  ( SELECT `viaje`.fecha_salida
                                        FROM viaje
                                        inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                        inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                        where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                    when '30' then  ( SELECT `viaje`.fecha_salida
                                        FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 20
                                            inner join caja_mex on viaje_detalle.paquete_id = caja_mex.id
                                            inner join caja_detalle_mex on caja_mex.id = caja_detalle_mex.caja_mex_id
                                            inner join envio_detalle on caja_detalle_mex.envio_detalle_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                END AS `viaje_fecha_salida`",

                    "case `envio`.`tipo_envio`
                    when '10' then  ( SELECT `viaje`.tipo_servicio
                                        FROM viaje
                                        inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                        inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                        where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                    when '20' then  ( SELECT `viaje`.tipo_servicio
                                        FROM viaje
                                        inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                        inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                        where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                    when '30' then  ( SELECT `viaje`.tipo_servicio
                                        FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 20
                                            inner join caja_mex on viaje_detalle.paquete_id = caja_mex.id
                                            inner join caja_detalle_mex on caja_mex.id = caja_detalle_mex.caja_mex_id
                                            inner join envio_detalle on caja_detalle_mex.envio_detalle_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                        limit 1
                                    )
                END AS `viaje_tipo`",
                ]);
            }
        } else {
            $select = array_merge($select, [
                'SQL_CALC_FOUND_ROWS `envio`.`id`',
                '`envio`.`folio`',
                '`envio`.`sucursal_emisor_id`',
                '`sucursal_emisor`.`nombre` AS `sucursal_emisor_nombre`',
                '`envio`.`origen` AS `origen`',
                '`envio`.`tipo_envio` AS `tipo_envio`',
                '`envio`.`cliente_emisor_id` AS `cliente_emisor_id`',
                'concat_ws(" ",cliente_emisor.nombre,  cliente_emisor.apellidos) AS nombre_emisor',
                '`envio`.subtotal',
                '`envio`.impuesto',
                '`envio`.total',
                'cobro_rembolso_envio.cantidad',
                'cobro_rembolso_envio.metodo_pago',
                'cobro_rembolso_envio.tipo',
                '`envio`.status',
                '`cobro_rembolso_envio`.`created_at` AS `created_at`',
                '`cobro_rembolso_envio`.`created_by` AS `created_by`',
                'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`',
            ]);
        }


        if (isset($filters['show']['viaje'])) {

            $select = array_merge(
                $select,
                [
                    "case `envio`.`tipo_envio`
                        when '10' then  ( SELECT `viaje`.id
                                            FROM viaje
                                                inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                                inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                                where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                        when '20' then  ( SELECT `viaje`.id
                                            FROM viaje
                                                inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                                inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                                where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                        when '30' then  ( SELECT `viaje`.id
                                            FROM viaje
                                                inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 20
                                                inner join caja_mex on viaje_detalle.paquete_id = caja_mex.id
                                                inner join caja_detalle_mex on caja_mex.id = caja_detalle_mex.caja_mex_id
                                                inner join envio_detalle on caja_detalle_mex.envio_detalle_id = envio_detalle.id
                                                where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                    END AS `viaje_id`",

                    "case `envio`.`tipo_envio`
                        when '10' then  ( SELECT `viaje`.fecha_salida
                                            FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                            inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                        when '20' then  ( SELECT `viaje`.fecha_salida
                                            FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                            inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                        when '30' then  ( SELECT `viaje`.fecha_salida
                                            FROM viaje
                                                inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 20
                                                inner join caja_mex on viaje_detalle.paquete_id = caja_mex.id
                                                inner join caja_detalle_mex on caja_mex.id = caja_detalle_mex.caja_mex_id
                                                inner join envio_detalle on caja_detalle_mex.envio_detalle_id = envio_detalle.id
                                                where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                    END AS `viaje_fecha_salida`",

                    "case `envio`.`tipo_envio`
                        when '10' then  ( SELECT `viaje`.tipo_servicio
                                            FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                            inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                        when '20' then  ( SELECT `viaje`.tipo_servicio
                                            FROM viaje
                                            inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 10
                                            inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id
                                            where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                        when '30' then  ( SELECT `viaje`.tipo_servicio
                                            FROM viaje
                                                inner join viaje_detalle on viaje.id = viaje_detalle.viaje_id and tipo = 20
                                                inner join caja_mex on viaje_detalle.paquete_id = caja_mex.id
                                                inner join caja_detalle_mex on caja_mex.id = caja_detalle_mex.caja_mex_id
                                                inner join envio_detalle on caja_detalle_mex.envio_detalle_id = envio_detalle.id
                                                where envio_detalle.envio_id = envio.id
                                            limit 1
                                        )
                    END AS `viaje_tipo`",
                ]
            );
        }


        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select($select)
            ->from('cobro_rembolso_envio')
            ->innerJoin('envio', 'cobro_rembolso_envio.envio_id = envio.id')
            ->innerJoin('`sucursal` `sucursal_emisor` ', '`envio`.`sucursal_emisor_id` = `sucursal_emisor`.`id`')
            ->innerJoin('`cliente` `cliente_emisor`', '`envio`.`cliente_emisor_id` = `cliente_emisor`.`id`')
            ->innerJoin('`user` `created`', '`cobro_rembolso_envio`.`created_by` = `created`.`id`')
            ->offset($offset)
            ->limit($limit);


        /************************************
        / Filtramos por sucursal
        /***********************************/

        if (isset($filters['date_range']) && $filters['date_range']) {
            $date_ini = strtotime($filters['from_date']);
            $date_fin = strtotime($filters['to_date']);
            $day = date("N", $date_ini);
            switch ($day) {
                case 5:
                    $date_ini = strtotime('-1 day', strtotime($filters['from_date']));
                    break;
                case 6:
                    $date_ini = strtotime('-2 day', strtotime($filters['from_date']));
                    break;
                case 7:
                    $date_ini = strtotime('-3 day', strtotime($filters['from_date']));
                    break;
            }

            $query->andWhere(['between', 'cobro_rembolso_envio.created_at', $date_ini, $date_fin]);
        }

        if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
            $query->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);

        if (isset($filters['tipo']) && $filters['tipo'])
            $query->andWhere(['cobro_rembolso_envio.tipo' =>  $filters['tipo']]);


        $query->andWhere(['<>', '`envio`.status', Envio::STATUS_CANCELADO]);

        if (isset($filters['is_cobro_mex']) && $filters['is_cobro_mex'])
            $query->andWhere(['IS', '`cobro_rembolso_envio`.is_cobro_mex', new \yii\db\Expression('null')]);
        else
            $query->andWhere(['=', '`cobro_rembolso_envio`.is_cobro_mex', CobroRembolsoEnvio::ISCOBRO_MEX]);


        /************************************
        / Filtramos la consulta
        /***********************************/

        if (isset($filters['tipo_envio']) && $filters['tipo_envio'])
            $query->andWhere(['tipo_envio' =>  $filters['tipo_envio']]);


        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'folio', $search]
            ]);

        /************************************
        / Agrupamos
        /***********************************/

        $groupBy = [];

        if (isset($filters['agrupar']['sucursal']))
            $groupBy[] = 'sucursal_emisor_id';

        if (isset($filters['agrupar']['tipo_envio']))
            $groupBy[] = 'tipo_envio';

        if (isset($filters['agrupar']['metodo_pago']))
            $groupBy[] = 'cobro_rembolso_envio.metodo_pago';

        if (isset($filters['agrupar']['viaje']))
            $groupBy[] = 'viaje_id';

        if (count($groupBy) > 0)
            $query->groupBy($groupBy);
        else
            $query->orderBy($orderBy);


        // Imprime String de la consulta SQL


        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getReporteAgenteJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);


        $select = [];




        $select = array_merge($select, [
            'SQL_CALC_FOUND_ROWS `envio`.`id`',
            '`envio`.`folio`',
            '`envio`.`sucursal_emisor_id`',
            '`sucursal_emisor`.`nombre` AS `sucursal_emisor_nombre`',
            '`envio`.`origen` AS `origen`',
            '`envio`.`tipo_envio` AS `tipo_envio`',
            '`envio`.`cliente_emisor_id` AS `cliente_emisor_id`',
            'concat_ws(" ",cliente_emisor.nombre,  cliente_emisor.apellidos) AS nombre_emisor',
            '`envio`.subtotal',
            '`envio`.impuesto',
            '`envio`.total',
            "(select
                sum(`cobro`.`cantidad`)
              from `cobro_rembolso_envio` `cobro`
              where
                (
                  (`cobro`.`tipo` = 10) and
                  (`cobro`.`envio_id` = `envio`.`id`)
                )) AS `monto_pagado`",
            "(
              `envio`.`total` - if((select
                sum(`cobro`.`cantidad`)
              from `cobro_rembolso_envio` `cobro`
              where
                (
                  (`cobro`.`tipo` = 10) and
                  (`cobro`.`envio_id` = `envio`.`id`)
                )),(select
                sum(`cobro`.`cantidad`)
              from `cobro_rembolso_envio` `cobro`
              where
                (
                  (`cobro`.`tipo` = 10) and
                  (`cobro`.`envio_id` = `envio`.`id`)
                )),0)
            ) AS `monto_deuda`",

            '`envio`.`peso_total` AS `peso_total`',
            'cobro_rembolso_envio.cantidad',
            'cobro_rembolso_envio.metodo_pago',
            'cobro_rembolso_envio.tipo',
            '`envio`.status',
            'concat_ws(" ",`agente`.`nombre`,`agente`.`apellidos`) AS `agente`',
            '`cobro_rembolso_envio`.`created_at` AS `created_at`',
            '`cobro_rembolso_envio`.`created_by` AS `created_by`',
            'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`',
        ]);


        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select($select)
            ->from('cobro_rembolso_envio')
            ->innerJoin('envio', 'cobro_rembolso_envio.envio_id = envio.id')
            ->innerJoin('`sucursal` `sucursal_emisor` ', '`envio`.`sucursal_emisor_id` = `sucursal_emisor`.`id`')
            ->innerJoin('`cliente` `cliente_emisor`', '`envio`.`cliente_emisor_id` = `cliente_emisor`.`id`')
            ->innerJoin('`user` `created`', '`cobro_rembolso_envio`.`created_by` = `created`.`id`')
            ->leftJoin('`user` `agente`', '`agente`.`id` = `cliente_emisor`.`asignado_id`')
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);

        /************************************
        / Filtramos por sucursal
        /***********************************/

        if (isset($filters['date_range']) && $filters['date_range']) {
            /*$date_ini = strtotime($filters['from_date']);
                $date_fin = strtotime($filters['to_date']);*/
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;


            $query->andWhere(['between', 'cobro_rembolso_envio.created_at', $date_ini, $date_fin]);
        }

        if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
            $query->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);

        if (isset($filters['tipo']) && $filters['tipo'])
            $query->andWhere(['cobro_rembolso_envio.tipo' =>  $filters['tipo']]);


        $query->andWhere(['<>', '`envio`.status', Envio::STATUS_CANCELADO]);


        /************************************
        / Filtramos la consulta
        /***********************************/

        if (isset($filters['tipo_envio']) && $filters['tipo_envio'])
            $query->andWhere(['tipo_envio' =>  $filters['tipo_envio']]);


        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'folio', $search]
            ]);


        // Imprime String de la consulta SQL


        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getReporteComisionJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                'SQL_CALC_FOUND_ROWS `envio`.`id`',
                '`envio`.`folio`',
                '`sucursal_receptor`.`nombre` AS `sucursal_receptor_nombre`',
                '`sucursal_emisor`.`nombre` AS `sucursal_emisor_nombre`',
                '`envio`.`origen` AS `origen`',
                '`envio`.`tipo_envio` AS `tipo_envio`',
                '(SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) as total_aseguranza',

                'ROUND((SELECT SUM(paquete.peso) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ),2) AS peso_paquetes',

                '(
                    (10 * ((SELECT SUM(paquete.peso) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) * envio.precio_libra_actual ) ) / 100
                ) AS comision_envio',

                /*'(
                     14.2857 * (SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) / 100
                ) AS comision_aseguranza',*/

                'if(envio.sucursal_emisor_id = 24,(
                     ( ( 2 * 100 ) / 8)  * (SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) / 100
                ),(
                     ( ( 1 * 100 ) / 7)  * (SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) / 100
                )) AS comision_aseguranza',

                /*'(
                    ((10 * ((SELECT SUM(paquete.peso) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) * envio.precio_libra_actual ) ) / 100) + ( 14.2857 * (SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) / 100 )
                ) as total_comision',*/

                'if(envio.sucursal_emisor_id = 24,(
                    ((10 * ((SELECT SUM(paquete.peso) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) * envio.precio_libra_actual ) ) / 100) + ( ( ( 2 * 100 ) / 8 )  * (SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) / 100 )
                ),(
                    ((10 * ((SELECT SUM(paquete.peso) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) * envio.precio_libra_actual ) ) / 100) + ( ( ( 1 * 100 ) / 7)  * (SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete where paquete.envio_id = envio.id  and paquete.sucursal_receptor_id = sucursal_receptor.id and envio_detalle.status = 10 ) / 100 )
                )) as total_comision',

                '`envio`.peso_total',
                '`envio`.subtotal',
                '`envio`.impuesto',
                '`envio`.total',
                "concat_ws(' ',trim(`receptor`.`nombre`),trim(`receptor`.`apellidos`)) AS `nombre_receptor`",
                'IF(promocion_detalle.costo_libra_sin_code,promocion_detalle.costo_libra_sin_code,envio.precio_libra_actual) AS precio_libra_actual',
                '( SELECT SUM(cobro_rembolso_envio.cantidad) FROM cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id ) as cantidad',
                '`envio`.status',

                '`cobro_rembolso_envio`.`created_at` AS `created_at`',
                '`cobro_rembolso_envio`.`created_by` AS `created_by`',
                'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`',
            ])
            ->from('cobro_rembolso_envio')
            ->innerJoin('envio', 'cobro_rembolso_envio.envio_id = envio.id')
            ->leftJoin('promocion_detalle', 'envio.promocion_detalle_id = promocion_detalle.id')
            ->innerJoin('envio_detalle', 'envio.id = envio_detalle.envio_id')
            ->innerJoin('cliente receptor', 'receptor.id = envio_detalle.cliente_receptor_id')

            ->innerJoin('`sucursal` `sucursal_receptor` ', '`envio_detalle`.`sucursal_receptor_id` = `sucursal_receptor`.`id`')
            ->innerJoin('`sucursal` `sucursal_emisor` ', '`envio`.`sucursal_emisor_id` = `sucursal_emisor`.`id`')
            ->innerJoin('`user` `created`', '`cobro_rembolso_envio`.`created_by` = `created`.`id`')
            ->offset($offset)
            ->orderBy($orderBy)
            ->limit($limit);


        /************************************
        / Filtramos por sucursal
        /***********************************/

        if (isset($filters['date_range']) && $filters['date_range']) {
            $date_ini = strtotime($filters['from_date']);
            $date_fin = strtotime($filters['to_date']);

            $query->andWhere(['between', 'cobro_rembolso_envio.created_at', $date_ini, $date_fin]);
        }

        if (isset($filters['sucursal_emisor_id']) && $filters['sucursal_emisor_id'])
            $query->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_emisor_id']]);

        if (isset($filters['sucursal_receptor_id']) && $filters['sucursal_receptor_id'])
            $query->andWhere(['sucursal_receptor_id' =>  $filters['sucursal_receptor_id']]);


        if (isset($filters['status_id']) && $filters['status_id'])
            $query->andWhere(['`envio`.status' =>  $filters['status_id']]);
        else
            $query->andWhere(['<>', '`envio`.status', Envio::STATUS_CANCELADO]);



        $query->andWhere(['<>', '`envio_detalle`.status', EnvioDetalle::STATUS_CANCELADO]);



        /************************************
        / Filtramos la consulta
        /***********************************/


        $query->andWhere(['tipo_envio' => Envio::TIPO_ENVIO_TIERRA]);


        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'folio', $search]
            ]);

        $query->groupBy('envio.id, sucursal_receptor.id');

        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
    public static function getEnvioDetalleAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                'envio_detalle.id',
                'envio_detalle.envio_id',
                'envio_detalle.sucursal_receptor_id',
                'envio_detalle.cliente_receptor_id',
                'envio_detalle.categoria_id',
                'categoria.singular as categoria',
                'esys_unidad_medida.id as unidad_medida_id',
                'esys_unidad_medida.singular as unidad_medida_text',
                'envio_detalle.producto_id',
                "CASE pr.tipo_servicio
                    WHEN '10' THEN CONCAT_WS(' ', pr.nombre, '[TIERRA]')
                    WHEN '20' THEN CONCAT_WS(' ', pr.nombre, '[LAX]')
                    WHEN '30' THEN CONCAT_WS(' ', pr.nombre, '[MEX]')
                END AS producto",
                'lista_precio_mx.required as required_min',
                'pr.is_impuesto',
                'lista_precio_mx.impuesto as costo_extra',
                'lista_precio_mx.intervalo as intervalo',
                '(SELECT esys.id FROM esys_direccion esys WHERE esys.cuenta_id = envio_detalle.id AND esys.cuenta = 5 AND esys.tipo = 1 LIMIT 1) as reenvio_id',
                'envio_detalle.tracked',
                'envio_detalle.valor_declarado',
                'envio_detalle.producto_tipo',
                'envio_detalle.cantidad',
                'envio_detalle.costo_neto_extraordinario',
                'envio_detalle.is_costo_extraordinario',
                'envio_detalle.peso',
                'pr.is_producto as tipo_producto_enviar',
                'envio_detalle.costo_caja_unitario as precio_caja_unitario',
                'envio_detalle.impuesto',
                'envio_detalle.precio_libra_actual',
                'envio_detalle.seguro',
                'envio_detalle.costo_seguro',
                'envio_detalle.status',
                'envio_detalle.observaciones',
                'detail_envio_product.detalle_json AS paquete_detalle'
            ])
            ->from(EnvioDetalle::tableName())
            ->innerJoin("producto as pr", "pr.id = envio_detalle.producto_id")
            ->leftJoin("lista_precio_mx", "envio_detalle.sucursal_receptor_id = lista_precio_mx.sucursal_recibe_id AND pr.categoria_id = lista_precio_mx.categoria_id AND lista_precio_mx.default = 10 AND lista_precio_mx.tipo = 20")
            ->innerJoin("esys_lista_desplegable categoria", "categoria.id = pr.categoria_id")
            ->innerJoin("esys_lista_desplegable as esys_unidad_medida", "esys_unidad_medida.id = pr.unidad_medida_id")
            ->leftJoin("detail_envio_product", "detail_envio_product.detalle_envio_id = envio_detalle.id")
            ->andWhere(['envio_detalle.envio_id' => $arr['envio']]);

        return [
            'rows' => $query->all()
        ];
    }


    public static function getCategoriaInfoMexAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        parse_str($arr['filters'], $filters);


        $query = EsysListaDesplegable::find()
            ->select('id, singular, plural, is_mex, mex_costo_extra, mex_intervalo, mex_required_min')
            ->andWhere([
                'label' => 'categoria_paquete_mex',
            ])
            ->orderBy('orden');

        return $query->all();
    }

    public static function getEnvioPromocionManualAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $EnvioPromocion = EnvioPromocion::find()
            ->select('id, envio_id, tipo, lb_free, lb_pagada, lb_excedente, costo_libra_pagada, costo_libra_excendete')
            ->andWhere(["envio_id" => $arr["envio_id"]]);

        return [
            'libras_free'           => $EnvioPromocion->one(),
            'condonacion_impuesto'  => [],
        ];
    }

    public static function getEsysDireccionAjax($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "esys_direccion.id",
                "esys_direccion.direccion",
                "esys_direccion.num_ext",
                "esys_direccion.num_int",
                "esys_direccion.estado_id",
                "estado.singular as estado",
                "esys_direccion.municipio_id",
                "municipio.singular as municipio",
                "cp.colonia as colonia",
                "cp.codigo_postal  as codigo_postal",
                "cp.id  as colonia_id",
                "esys_direccion.referencia"
            ])
            ->from("envio")
            ->innerJoin('envio_detalle', 'envio.id = envio_detalle.envio_id')
            ->innerJoin('esys_direccion', 'envio_detalle.id = esys_direccion.cuenta_id and esys_direccion.cuenta = 5 and  esys_direccion.tipo =1')
            ->leftJoin('esys_lista_desplegable estado', 'esys_direccion.estado_id = estado.id_2 and estado.label = "crm_estado"')
            ->leftJoin('esys_lista_desplegable municipio', 'esys_direccion.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
            ->leftJoin('esys_direccion_codigo_postal cp', 'esys_direccion.codigo_postal_id = cp.id')
            ->andWhere(['envio.id' =>  $arr['envio_id']]);
        //->groupBy('esys_direccion.estado_id, esys_direccion.municipio_id, cp.id');

        return [
            'rows'  => $query->all()
        ];
    }
    public static function getReporteSeguimientoJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                'SQL_CALC_FOUND_ROWS `envio`.`id`',

                'envio.folio',

                '(SELECT SUM(envio_detalle.cantidad) FROM envio_detalle WHERE  envio_detalle.status = 10 and envio_detalle.envio_id = envio.id) as n_piezas',

                'envio.peso_total as sum_peso',

                '(SELECT COUNT(*) FROM viaje_detalle
             INNER JOIN envio_detalle on  (viaje_detalle.paquete_id = envio_detalle.id AND viaje_detalle.tipo = 10 ) WHERE  envio_detalle.envio_id = envio.id ) as n_transcurso',

                'ROUND((envio.peso_total / (SELECT SUM(envio_detalle.cantidad) FROM envio_detalle WHERE envio_detalle.envio_id = envio.id) ) * (SELECT  count(*) FROM viaje_detalle
             INNER JOIN envio_detalle on  (viaje_detalle.paquete_id = envio_detalle.id AND viaje_detalle.tipo = 10 ) WHERE  envio_detalle.envio_id = envio.id ),2)  as peso_transcurso',

                '(SELECT COUNT(*) FROM movimiento_paquete
            INNER JOIN envio_detalle on  (movimiento_paquete.paquete_id = envio_detalle.id AND movimiento_paquete.tipo = 10 AND movimiento_paquete.tipo_movimiento = 30) WHERE  envio_detalle.envio_id = envio.id ) as n_bodega',

                'ROUND((envio.peso_total / (SELECT SUM(envio_detalle.cantidad) FROM envio_detalle WHERE envio_detalle.envio_id = envio.id) ) * (SELECT count(*) FROM movimiento_paquete
            INNER JOIN envio_detalle on  (movimiento_paquete.paquete_id = envio_detalle.id AND movimiento_paquete.tipo = 10 AND movimiento_paquete.tipo_movimiento = 30) WHERE  envio_detalle.envio_id = envio.id ),2) as peso_bodega',

                '(SELECT COUNT(*) FROM movimiento_paquete
            INNER JOIN envio_detalle on  (movimiento_paquete.paquete_id = envio_detalle.id AND movimiento_paquete.tipo = 10 AND movimiento_paquete.tipo_movimiento = 60) WHERE  envio_detalle.envio_id = envio.id ) as n_entregado',

                'ROUND((SELECT  SUM(viaje_detalle.peso_mx) FROM movimiento_paquete
            INNER JOIN envio_detalle   on  (movimiento_paquete.paquete_id = envio_detalle.id AND movimiento_paquete.tipo = 10 AND movimiento_paquete.tipo_movimiento = 60)
            INNER JOIN viaje_detalle on  (viaje_detalle.tracked = movimiento_paquete.tracked)
            WHERE  envio_detalle.envio_id = envio.id  ),2) as peso_entregado',

                'ROUND((envio.peso_total / (SELECT SUM(envio_detalle.cantidad) FROM envio_detalle WHERE envio_detalle.envio_id = envio.id) ) * (SELECT count(*) FROM movimiento_paquete
            INNER JOIN envio_detalle on  (movimiento_paquete.paquete_id = envio_detalle.id AND movimiento_paquete.tipo = 10 AND movimiento_paquete.tipo_movimiento = 60) WHERE  envio_detalle.envio_id = envio.id ),2) as peso_global_entregado'

            ])
            ->from('envio')
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);

        /************************************
        / Filtramos por sucursal
        /***********************************/

        if (isset($filters['date_range']) && $filters['date_range']) {
            /*$date_ini = strtotime($filters['from_date']);
                $date_fin = strtotime($filters['to_date']);*/
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $query->andWhere(['between', 'envio.created_at', $date_ini, $date_fin]);
        }

        if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
            $query->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);

        if (isset($filters['tipo_envio']) && $filters['tipo_envio'])
            $query->andWhere(['envio.tipo_envio' =>  $filters['tipo_envio']]);


        $query->andWhere(['<>', '`envio`.status', Envio::STATUS_CANCELADO]);


        /************************************
        / Filtramos la consulta
        /***********************************/

        if (isset($filters['tipo_envio']) && $filters['tipo_envio'])
            $query->andWhere(['tipo_envio' =>  $filters['tipo_envio']]);


        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'folio', $search]
            ]);

        // Imprime String de la consulta SQL


        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getPaqueteDocumentado($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);

        $select = [];

        if (isset($filters['agrupar']['sucursal']) || isset($filters['agrupar']['agente']) || isset($filters['agrupar']['dia']) || isset($filters['agrupar']['mes'])) {
            if (isset($filters['agrupar']['sucursal']))
                $select = array_merge($select, [
                    "sucursal_receptor.nombre as nombre_sucursal",
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    new Expression('"-" AS id'),
                ]);
            if (isset($filters['agrupar']['agente']))
                $select = array_merge($select, [
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    new Expression('"-" AS id'),
                    'concat_ws(" ",created.nombre,  created.apellidos) AS created_by_user',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    'movimiento_paquete.created_by AS created_by',
                ]);

            if (isset($filters['agrupar']['dia']))
                $select = array_merge($select, [
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    new Expression('"-" AS id'),
                    'from_unixtime(movimiento_paquete.created_at,"%d") as dia',
                ]);

            if (isset($filters['agrupar']['mes']))
                $select = array_merge($select, [
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    new Expression('"-" AS id'),
                    'from_unixtime(movimiento_paquete.created_at,"%m") as mes',
                ]);
        } else {
            $select = array_merge($select, [
                'SQL_CALC_FOUND_ROWS `movimiento_paquete`.`id`',
                "movimiento_paquete.tracked",
                "movimiento_paquete.paquete_id",
                "sucursal_receptor.nombre as nombre_sucursal",
                'concat_ws(" ",`cliente_receptor`.`nombre`,`cliente_receptor`.`apellidos`) AS `nombre_receptor`',
                'concat_ws(" ",cliente_emisor.nombre,  cliente_emisor.apellidos) AS nombre_emisor',
                "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) as tipo_movimiento",
                "envio_detalle.envio_id",
                "case pr.tipo_servicio
                    when '10' then  CONCAT_WS(' ', `pr`.`nombre`,'[TIERRA]')
                    when '20' then  CONCAT_WS(' ', `pr`.`nombre`,'[LAX]')
                    when '30' then  CONCAT_WS(' ', `pr`.`nombre`,'[MEX]')
                END AS `producto`",
                "envio_detalle.peso",
                "ROUND(envio_detalle.peso / envio_detalle.cantidad, 2) as peso_unitario",
                "envio_detalle.cantidad",
                "envio.folio",
                "envio.total",
                "round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2) as total_unitario",
                "movimiento_paquete.created_at",
                'concat_ws(" ",created.nombre,  created.apellidos) AS created_by_user',
                'movimiento_paquete.created_by AS created_by',
                'from_unixtime(movimiento_paquete.created_at,"%d") as dia',
                'from_unixtime(movimiento_paquete.created_at,"%m") as mes',
            ]);
        }

        $queryPaquetes = (new Query())
            ->select($select)
            ->from('movimiento_paquete')
            ->innerJoin('user created', 'movimiento_paquete.created_by = created.id')
            ->innerJoin('envio_detalle', 'movimiento_paquete.paquete_id = envio_detalle.id')
            ->innerJoin("producto as pr", "pr.id = envio_detalle.producto_id")
            ->innerJoin('cliente cliente_receptor', 'envio_detalle.cliente_receptor_id = cliente_receptor.id')
            ->innerJoin('sucursal sucursal_receptor', 'envio_detalle.sucursal_receptor_id = sucursal_receptor.id')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->innerJoin('`cliente` `cliente_emisor`', '`envio`.`cliente_emisor_id` = `cliente_emisor`.`id`')
            ->andWhere([
                "and",
                ["=", "envio.tipo_envio", Envio::TIPO_ENVIO_TIERRA],
                ["=", "envio.status", Envio::STATUS_HABILITADO],
                ["=", "envio_detalle.status", EnvioDetalle::STATUS_HABILITADO],
                ["<>", "movimiento_paquete.tipo_envio", 30], //Modificación para permitir que muestre LAX en reportes TIE
                ["=", "movimiento_paquete.tipo", 10],
                // ===================================== Filtra paquetes que ya cuenta con pago realizado ==================================================
                ["=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)", 2],
            ])
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);

        if (isset($filters['date_range']) && $filters['date_range']) {
            /*$date_ini = strtotime($filters['from_date']);
                $date_fin = strtotime($filters['to_date']);*/
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;


            $queryPaquetes->andWhere(['between', 'movimiento_paquete.created_at', $date_ini, $date_fin]);
        }

        if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
            $queryPaquetes->andWhere(['envio_detalle.sucursal_receptor_id' =>  $filters['sucursal_id']]);

        //echo ($queryPaquetes->createCommand()->rawSql) . '<br/><br/>';

        if ($search)
            $queryPaquetes->andFilterWhere([
                'or',
                ['like', 'movimiento_paquete.tracked', $search]
            ]);

        /************************************
            / Agrupamos
            /***********************************/

        $groupBy = [];

        if (isset($filters['agrupar']['sucursal']))
            $groupBy[] = 'sucursal_receptor.id';

        if (isset($filters['agrupar']['agente']))
            $groupBy[] = 'created.id';

        if (isset($filters['agrupar']['mes']))
            $groupBy[] = 'mes';

        if (isset($filters['agrupar']['dia']))
            $groupBy[] = 'dia';


        if (count($groupBy) > 0)
            $queryPaquetes->groupBy($groupBy);


        return [
            'rows'  => $queryPaquetes->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getPaqueteBodega($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);

        $select = [];

        if (isset($filters['agrupar']['sucursal']) || isset($filters['agrupar']['agente']) || isset($filters['agrupar']['dia']) || isset($filters['agrupar']['mes'])) {
            if (isset($filters['agrupar']['sucursal']))
                $select = array_merge($select, [
                    "sucursal_receptor.nombre as nombre_sucursal",
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    new Expression('"-" AS id'),
                ]);
            if (isset($filters['agrupar']['agente']))
                $select = array_merge($select, [
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    new Expression('"-" AS id'),
                    'concat_ws(" ",created.nombre,  created.apellidos) AS created_by_user',
                    'movimiento_paquete.created_by AS created_by',
                ]);

            if (isset($filters['agrupar']['dia']))
                $select = array_merge($select, [
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    new Expression('"-" AS id'),
                    'from_unixtime(movimiento_paquete.created_at,"%d") as dia',
                ]);

            if (isset($filters['agrupar']['mes']))
                $select = array_merge($select, [
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    new Expression('"-" AS id'),
                    'from_unixtime(movimiento_paquete.created_at,"%m") as mes',
                ]);
        } else {
            $select = array_merge($select, [
                'SQL_CALC_FOUND_ROWS `movimiento_paquete`.`id`',
                "movimiento_paquete.tracked",
                "movimiento_paquete.paquete_id",
                "sucursal_receptor.nombre as nombre_sucursal",
                'concat_ws(" ",`cliente_receptor`.`nombre`,`cliente_receptor`.`apellidos`) AS `nombre_receptor`',
                'concat_ws(" ",cliente_emisor.nombre,  cliente_emisor.apellidos) AS nombre_emisor',
                "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) as tipo_movimiento",
                "envio_detalle.envio_id",
                "case pr.tipo_servicio
                    when '10' then  CONCAT_WS(' ', `pr`.`nombre`,'[TIERRA]')
                    when '20' then  CONCAT_WS(' ', `pr`.`nombre`,'[LAX]')
                    when '30' then  CONCAT_WS(' ', `pr`.`nombre`,'[MEX]')
                END AS `producto`",
                "envio_detalle.peso",
                "ROUND(envio_detalle.peso / envio_detalle.cantidad, 2) as peso_unitario",
                "envio_detalle.cantidad",
                "envio.folio",
                "envio.total",
                "round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2) as total_unitario",
                "movimiento_paquete.created_at",
                'concat_ws(" ",created.nombre,  created.apellidos) AS created_by_user',
                'movimiento_paquete.created_by AS created_by',
                'from_unixtime(movimiento_paquete.created_at,"%d") as dia',
                'from_unixtime(movimiento_paquete.created_at,"%m") as mes',
            ]);
        }

        $queryPaquetes = (new Query())
            ->select($select)
            ->from('movimiento_paquete')
            ->innerJoin('user created', 'movimiento_paquete.created_by = created.id')
            ->innerJoin('envio_detalle', 'movimiento_paquete.paquete_id = envio_detalle.id')
            ->innerJoin("producto as pr", "pr.id = envio_detalle.producto_id")
            ->innerJoin('cliente cliente_receptor', 'envio_detalle.cliente_receptor_id = cliente_receptor.id')
            ->innerJoin('sucursal sucursal_receptor', 'envio_detalle.sucursal_receptor_id = sucursal_receptor.id')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->innerJoin('`cliente` `cliente_emisor`', '`envio`.`cliente_emisor_id` = `cliente_emisor`.`id`')
            ->andWhere([
                "and",
                ["=", "envio.tipo_envio", Envio::TIPO_ENVIO_TIERRA],
                ["=", "envio.status", Envio::STATUS_HABILITADO],
                ["=", "envio_detalle.status", EnvioDetalle::STATUS_HABILITADO],
                ["<>", "movimiento_paquete.tipo_envio", 30], //Modificación para permitir que muestre LAX en reportes TIE
                ["=", "movimiento_paquete.tipo", 10],
                ["=", "movimiento_paquete.tipo_movimiento", 10],
                // ===================================== Filtra paquetes que ya cuenta con pago realizado ==================================================
                ["=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)", 10],
            ])
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);

        if (isset($filters['date_range']) && $filters['date_range']) {
            /*$date_ini = strtotime($filters['from_date']);
                $date_fin = strtotime($filters['to_date']);*/
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;


            $queryPaquetes->andWhere(['between', 'movimiento_paquete.created_at', $date_ini, $date_fin]);
        }

        if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
            $queryPaquetes->andWhere(['envio_detalle.sucursal_receptor_id' =>  $filters['sucursal_id']]);

        //echo ($queryPaquetes->createCommand()->rawSql) . '<br/><br/>';

        if ($search)
            $queryPaquetes->andFilterWhere([
                'or',
                ['like', 'movimiento_paquete.tracked', $search]
            ]);

        /************************************
            / Agrupamos
            /***********************************/

        $groupBy = [];

        if (isset($filters['agrupar']['sucursal']))
            $groupBy[] = 'sucursal_receptor.id';

        if (isset($filters['agrupar']['agente']))
            $groupBy[] = 'created.id';

        if (isset($filters['agrupar']['mes']))
            $groupBy[] = 'mes';

        if (isset($filters['agrupar']['dia']))
            $groupBy[] = 'dia';


        if (count($groupBy) > 0)
            $queryPaquetes->groupBy($groupBy);



        return [
            'rows'  => $queryPaquetes->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getPaqueteSeguimiento($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);

        $select = [];

        $queryPaquetes = (new Query())
            ->select([
                'SQL_CALC_FOUND_ROWS `movimiento_paquete`.`id`',
                "movimiento_paquete.tracked",
                "movimiento_paquete.paquete_id",
                "sucursal.nombre as sucursal_nombre",
                "sucursal_receptor.nombre as nombre_sucursal",
                'concat_ws(" ",`cliente_receptor`.`nombre`,`cliente_receptor`.`apellidos`) AS `nombre_receptor`',
                "cliente_receptor.telefono_movil AS `telefono_movil`",
                "cliente_receptor.telefono AS `telefono`",
                "sucursal_receptor.is_reenvio as sucursal_recibe_is_reenvio",
                'concat_ws(" ",cliente_emisor.nombre,  cliente_emisor.apellidos) AS nombre_emisor',
                "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) as tipo_movimiento",
                "envio_detalle.envio_id",
                "case pr.tipo_servicio
                    when '10' then  CONCAT_WS(' ', `pr`.`nombre`,'[TIERRA]')
                    when '20' then  CONCAT_WS(' ', `pr`.`nombre`,'[LAX]')
                    when '30' then  CONCAT_WS(' ', `pr`.`nombre`,'[MEX]')
                END AS `producto`",
                "envio_detalle.peso",
                "ROUND(envio_detalle.peso / envio_detalle.cantidad, 2) as peso_unitario",
                "envio_detalle.cantidad",
                "envio.folio",
                "envio.total",
                "envio_detalle.observaciones",
                "round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2) as total_unitario",
                "(select mv.created_at  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) created_at",
                'concat_ws(" ",created.nombre,  created.apellidos) AS created_by_user',
                '(select mv.created_by  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) AS created_by',
            ])
            ->from('movimiento_paquete')
            ->innerJoin('user created', '(select mv.created_by  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) = created.id')
            ->innerJoin('envio_detalle', 'movimiento_paquete.paquete_id = envio_detalle.id')
            ->innerJoin("producto as pr", "pr.id = envio_detalle.producto_id")
            ->innerJoin('cliente cliente_receptor', 'envio_detalle.cliente_receptor_id = cliente_receptor.id')
            ->innerJoin('sucursal sucursal_receptor', 'envio_detalle.sucursal_receptor_id = sucursal_receptor.id')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->leftJoin('sucursal', 'envio.sucursal_emisor_id = sucursal.id')
            ->innerJoin('`cliente` `cliente_emisor`', '`envio`.`cliente_emisor_id` = `cliente_emisor`.`id`')
            ->andWhere([
                "and",
                ["=", "envio.tipo_envio", Envio::TIPO_ENVIO_TIERRA],
                ["=", "envio.status", Envio::STATUS_HABILITADO],
                ["=", "envio_detalle.status", EnvioDetalle::STATUS_HABILITADO],
                //[ "<>", "movimiento_paquete.tipo_envio", 30 ], //Modificación para permitir que muestre LAX en reportes TIE
                ["=", "movimiento_paquete.tipo", 10],
                //[ "=", "movimiento_paquete.tipo_movimiento", 10 ],


            ])
            ->groupBy("movimiento_paquete.tracked")
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);

        if (isset($filters['date_range']) && $filters['date_range']) {
            /*$date_ini = strtotime($filters['from_date']);
                $date_fin = strtotime($filters['to_date']);*/
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;


            $queryPaquetes->andWhere(['between', '(select mv.created_at  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )', $date_ini, $date_fin]);
        }

        if (isset($filters['tipo_movimiento']) && $filters['tipo_movimiento'])
            $queryPaquetes->andWhere(['(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )' =>  $filters['tipo_movimiento']]);

        //echo ($queryPaquetes->createCommand()->rawSql) . '<br/><br/>';

        if ($search)
            $queryPaquetes->andFilterWhere([
                'or',
                ['like', 'movimiento_paquete.tracked', $search]
            ]);




        return [
            'rows'  => $queryPaquetes->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getReportePaqueteSeguimiento($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;

        parse_str($arr['filters'], $filters);

        $select = [];

        $queryPaquetes = (new Query())
            ->select([
                'SQL_CALC_FOUND_ROWS `movimiento_paquete`.`id`',
                "movimiento_paquete.tracked",
                "movimiento_paquete.paquete_id",
                "sucursal.nombre as sucursal_nombre",
                "sucursal_receptor.nombre as nombre_sucursal",
                'concat_ws(" ",`cliente_receptor`.`nombre`,`cliente_receptor`.`apellidos`) AS `nombre_receptor`',
                "cliente_receptor.telefono_movil AS `telefono_movil`",
                "cliente_receptor.telefono AS `telefono`",
                "sucursal_receptor.is_reenvio as sucursal_recibe_is_reenvio",
                'concat_ws(" ",cliente_emisor.nombre,  cliente_emisor.apellidos) AS nombre_emisor',
                "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) as tipo_movimiento",
                "envio_detalle.envio_id",
                "case pr.tipo_servicio
                    when '10' then  CONCAT_WS(' ', `pr`.`nombre`,'[TIERRA]')
                    when '20' then  CONCAT_WS(' ', `pr`.`nombre`,'[LAX]')
                    when '30' then  CONCAT_WS(' ', `pr`.`nombre`,'[MEX]')
                END AS `producto`",
                "envio_detalle.peso",
                "ROUND(envio_detalle.peso / envio_detalle.cantidad, 2) as peso_unitario",
                "envio_detalle.cantidad",
                "envio.folio",
                "envio.total",
                "envio_detalle.observaciones",

                "esys.direccion",
                "estado.singular as estado",
                "municipio.singular as municipio",
                "code_postal.codigo_postal as code_postal",


                "round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2) as total_unitario",
                "(select mv.created_at  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) created_at",
                'concat_ws(" ",created.nombre,  created.apellidos) AS created_by_user',
                '(select mv.created_by  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) AS created_by',
            ])
            ->from('movimiento_paquete')
            ->innerJoin('user created', '(select mv.created_by  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) = created.id')
            ->innerJoin('envio_detalle', 'movimiento_paquete.paquete_id = envio_detalle.id')

            ->leftJoin('esys_direccion esys', 'envio_detalle.id = esys.cuenta_id and esys.cuenta = 5')
            ->leftJoin('esys_lista_desplegable estado', 'esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
            ->leftJoin('esys_lista_desplegable municipio', 'esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
            ->leftJoin('esys_direccion_codigo_postal code_postal', 'esys.codigo_postal_id = code_postal.id')


            ->innerJoin("producto as pr", "pr.id = envio_detalle.producto_id")
            ->innerJoin('cliente cliente_receptor', 'envio_detalle.cliente_receptor_id = cliente_receptor.id')
            ->innerJoin('sucursal sucursal_receptor', 'envio_detalle.sucursal_receptor_id = sucursal_receptor.id')

            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->leftJoin('sucursal', 'envio.sucursal_emisor_id = sucursal.id')
            ->innerJoin('`cliente` `cliente_emisor`', '`envio`.`cliente_emisor_id` = `cliente_emisor`.`id`')
            ->andWhere([
                "and",
                ["=", "envio.tipo_envio", Envio::TIPO_ENVIO_TIERRA],
                ["=", "envio.status", Envio::STATUS_HABILITADO],
                ["=", "envio_detalle.status", EnvioDetalle::STATUS_HABILITADO],
                //[ "<>", "movimiento_paquete.tipo_envio", 30 ], //Modificación para permitir que muestre LAX en reportes TIE
                ["=", "movimiento_paquete.tipo", 10],
                //[ "=", "movimiento_paquete.tipo_movimiento", 10 ],


            ])
            ->groupBy("movimiento_paquete.tracked")
            ->orderBy($orderBy);


        if (isset($filters['date_range']) && $filters['date_range']) {
            /*$date_ini = strtotime($filters['from_date']);
                $date_fin = strtotime($filters['to_date']);*/
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;


            $queryPaquetes->andWhere(['between', '(select mv.created_at  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )', $date_ini, $date_fin]);
        }

        if (isset($filters['tipo_movimiento']) && $filters['tipo_movimiento'])
            $queryPaquetes->andWhere(['(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )' =>  $filters['tipo_movimiento']]);

        //echo ($queryPaquetes->createCommand()->rawSql) . '<br/><br/>';



        return [
            'rows'  => $queryPaquetes->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getDescargaTrailer($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);

        $select = [];

        if (isset($filters['agrupar']['sucursal']) || isset($filters['agrupar']['agente'])) {
            if (isset($filters['agrupar']['sucursal']))
                $select = array_merge($select, [
                    "sucursal.nombre as sucursal_receptor",
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    'COUNT(`viaje_detalle`.`id`) AS paquetes',
                    new Expression('"-" AS id'),
                ]);
            if (isset($filters['agrupar']['agente']))
                $select = array_merge($select, [
                    'SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) AS total_unitario',
                    'COUNT(`movimiento_paquete`.`id`) AS paquetes',
                    "ROUND(SUM(envio_detalle.peso) / SUM(envio_detalle.cantidad), 2) as peso_unitario",
                    new Expression('"-" AS id'),
                    'concat_ws(" ",created.nombre,  created.apellidos) AS created_by_user',
                    'movimiento_paquete.created_by AS created_by',
                ]);
        } else {
            $select = array_merge($select, [
                'SQL_CALC_FOUND_ROWS `viaje_detalle`.`id`',
                '`viaje_detalle`.`viaje_id`',
                '`viaje_detalle`.`paquete_id`',
                '`viaje_detalle`.`tracked`',
                '`viaje_detalle`.`peso_mx`',
                '`viaje_detalle`.`peso_mx`',
                '`envio_detalle`.`envio_id`',
                '`envio_detalle`.`status`',
                '`envio_detalle`.`valor_declarado`',
                '`envio_detalle`.`cantidad_piezas`',
                '`envio_detalle`.`cantidad`',
                "case pr.tipo_servicio
                    when '10' then  CONCAT_WS(' ', `pr`.`nombre`,'[TIERRA]')
                    when '20' then  CONCAT_WS(' ', `pr`.`nombre`,'[LAX]')
                    when '30' then  CONCAT_WS(' ', `pr`.`nombre`,'[MEX]')
                END AS `producto`",
                "ROUND(envio_detalle.peso / envio_detalle.cantidad, 2) as peso_unitario",
                '`envio_detalle`.`impuesto`',
                "round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2) as total_unitario",
                '`envio_detalle`.`costo_seguro`',
                '`envio_detalle`.`observaciones`',
                '`ruta`.`nombre` AS `nombre_ruta`',
                '`sucursal`.`nombre` AS `sucursal_receptor`',
                'concat_ws(" ", cliente.nombre, cliente.apellidos) AS `nombre_receptor`',
                '`cliente`.`telefono` AS `telefono_cliente`',
                '`cliente`.`telefono_movil`',
                'envio.total',
                '(select
                sum(`cobro`.`cantidad`)
                from `cobro_rembolso_envio` `cobro`
                where
                 (
                   (`cobro`.`tipo` = 10) and
                   (`cobro`.`envio_id` = `envio`.`id`)
                 )) AS `monto_pagado`',
                '(
                    `envio`.`total` - if((select
                    sum(`cobro`.`cantidad`)
                from `cobro_rembolso_envio` `cobro`
                where
                (
                  (`cobro`.`tipo` = 10) and
                  (`cobro`.`envio_id` = `envio`.`id`)
                )),(select
                sum(`cobro`.`cantidad`)
                from `cobro_rembolso_envio` `cobro`
                where
                (
                  (`cobro`.`tipo` = 10) and
                  (`cobro`.`envio_id` = `envio`.`id`)
                )),0)
                ) AS `monto_deuda`',
                '(select mv.tipo_movimiento from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked order by id desc limit 1 ) as tipo_movimiento_top',
                '(select mv.created_at from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked and mv.tipo_movimiento = 20 limit 1 ) as created_at'

            ]);
        }

        $queryPaquetes = (new Query())
            ->select($select)
            ->from('envio_detalle')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->innerJoin('viaje_detalle', 'envio_detalle.id = viaje_detalle.paquete_id')
            ->leftJoin('sucursal', 'envio_detalle.sucursal_receptor_id = sucursal.id')
            ->leftJoin('cliente', 'envio_detalle.cliente_receptor_id = cliente.id')
            ->innerJoin("producto as pr", "envio_detalle.producto_id = pr.id")
            ->leftJoin('ruta_sucursal', 'sucursal.id = ruta_sucursal.sucursal_id')
            ->leftJoin('ruta', 'ruta_sucursal.ruta_id = ruta.id')
            ->andWhere(['envio_detalle.status'      => EnvioDetalle::STATUS_HABILITADO])
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);

        if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
            $queryPaquetes->andWhere(['envio_detalle.sucursal_receptor_id' =>  $filters['sucursal_id']]);

        if (isset($filters['viaje_id']) && $filters['viaje_id'])
            $queryPaquetes->andWhere(['viaje_detalle.viaje_id'    => $filters['viaje_id']]);


        if ($search)
            $queryPaquetes->andFilterWhere([
                'or',
                ['like', 'viaje_detalle.tracked', $search]
            ]);

        /************************************
        / Agrupamos
        /***********************************/

        $groupBy = [];

        if (isset($filters['agrupar']['sucursal']))
            $groupBy[] = 'envio_detalle.sucursal_receptor_id';

        if (count($groupBy) > 0)
            $queryPaquetes->groupBy($groupBy);

        return [
            'rows'  => $queryPaquetes->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getViajeCheckList($arr)
    {

        Yii::$app->response->format = Response::FORMAT_JSON;

        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        //$limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);

        if (isset($filters['viaje_id']) && $filters['viaje_id']) {
            $viaje = Viaje::findOne($filters['viaje_id']);
            if (isset($viaje->id) && $viaje->id) {

                $queryPaquetes = (new Query())
                    ->select([
                        "movimiento_paquete.tracked",
                        "movimiento_paquete.paquete_id",
                        "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) as tipo_movimiento",
                        "envio_detalle.envio_id",
                        "case pr.tipo_servicio
                        when '10' then  CONCAT_WS(' ', `pr`.`nombre`,'[TIERRA]')
                        when '20' then  CONCAT_WS(' ', `pr`.`nombre`,'[LAX]')
                        when '30' then  CONCAT_WS(' ', `pr`.`nombre`,'[MEX]')
                    END AS `producto`",
                        "viaje_detalle.viaje_id",
                        "IF(viaje.id, concat_ws(' ', '[', viaje.placas ,']', '[' , viaje.nombre_chofer , ']' ), '') as viaje_nombre ",
                        "envio.folio",
                        "envio.total",
                        "(SELECT count(*) FROM viaje_paquete_denegado v_denagado where v_denagado.viaje_id = " . $viaje->id . " and  v_denagado.tracked = movimiento_paquete.tracked) as is_denegado",
                        "(SELECT cobro_rembolso_envio.created_at FROM cobro_rembolso_envio WHERE cobro_rembolso_envio.envio_id = envio.id order by cobro_rembolso_envio.created_at desc limit 1) AS fecha_pago",
                        "(SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id) AS total_pagado",
                    ])
                    ->from('movimiento_paquete')
                    ->innerJoin('envio_detalle', 'movimiento_paquete.paquete_id = envio_detalle.id')
                    ->innerJoin("producto as pr", "envio_detalle.producto_id = pr.id")
                    ->leftJoin('viaje_detalle', 'envio_detalle.id = viaje_detalle.paquete_id and viaje_detalle.tracked = movimiento_paquete.tracked')
                    ->leftJoin('viaje', 'viaje_detalle.viaje_id = viaje.id')
                    ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
                    ->andWhere([
                        "and",
                        ["between", "envio.id", $viaje->envio_ini_id, $viaje->envio_fin_id],
                        ["=", "envio.tipo_envio", Envio::TIPO_ENVIO_TIERRA],
                        ["=", "envio.status", Envio::STATUS_HABILITADO],
                        ["=", "envio_detalle.status", EnvioDetalle::STATUS_HABILITADO],
                        ["<>", "movimiento_paquete.tipo_envio", 30], //Modificación para permitir que muestre LAX en reportes TIE
                        ["=", "movimiento_paquete.tipo", 10],
                        // ===================================== Filtra paquetes que ya cuenta con pago realizado ==================================================
                        ["<>", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)", 1],

                        [">", "if((SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id),10,0)", 0],
                    ])
                    ->andWhere([
                        'or',
                        ["=", "viaje_detalle.viaje_id", $viaje->id],
                        ['IS', 'viaje_detalle.id', new \yii\db\Expression('null')],
                    ])
                    //->orderBy("movimiento_paquete.tracked asc")
                    ->orderBy("fecha_pago, movimiento_paquete.tracked asc")
                    ->offset($offset)
                    ->limit(Viaje::CARGA_MAXIMA_TIE)
                    ->groupBy("movimiento_paquete.tracked");

                //echo ($queryPaquetes->createCommand()->rawSql) . '<br/><br/>';
                return $queryPaquetes->all();
            }
        }
    }

    public static function getEnvioMexCobros($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort']) ?   $arr['sort'] :   'id';
        $order   = isset($arr['order']) ?  $arr['order'] :  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ?  $arr['limit'] :  50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "SQL_CALC_FOUND_ROWS `id`",
                'folio',
                'sucursal_emisor_id',
                'sucursal_emisor_nombre',
                'origen',
                'tipo_envio',
                'cliente_emisor_id',
                'nombre_emisor',
                'promocion_id',
                'is_reenvio',
                'is_recoleccion',
                'is_efectivo',
                'costo_reenvio',
                'promocion_complemento_id',
                'codigo_promocional_id',
                'descuento_manual',
                'is_descuento_manual',
                'subtotal',
                'impuesto',
                'total',
                'n_elementos',
                'n_pz',
                'monto_pagado',
                'cobros_mex',
                'monto_deuda',
                'peso_total',
                'peso_mex_con_empaque',
                'status',
                'comentarios',
                'agente',
                'pre_created_at',
                'created_at',
                'created_by',
                'created_by_user',
                'updated_at',
                'updated_by',
                'updated_by_user',
            ])
            ->from(self::tableName())
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);


        /************************************
        / Filtramos por sucursal
        /***********************************/

        if (isset($filters['date_range']) && $filters['date_range']) {
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $query->andWhere(['between', 'created_at', $date_ini, $date_fin]);
        }


        if (isset($filters['sucursal_emisor']) && $filters['sucursal_emisor'])
            $query->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_emisor']]);

        if (isset($filters['folio']) && $filters['folio'])
            $query->andWhere(['folio' =>  $filters['folio']]);

        if (isset($filters['status_id']) && $filters['status_id'])
            $query->andWhere(['status' =>  $filters['status_id']]);

        if (isset($filters['sucursal_receptor']) && $filters['sucursal_receptor'])
            $query->andWhere(['sucursal_receptor_id' =>  $filters['sucursal_receptor']]);

        if (isset($filters['pago_parcial']) && $filters['pago_parcial'])
            $query->andWhere(['and', ['>', 'monto_deuda', 0], ['<', 'monto_deuda', 'total']]);



        /************************************
        / Filtramos la consulta
        /***********************************/

        $query->andWhere(['tipo_envio' => Envio::TIPO_ENVIO_MEX]);

        if (isset($filters['historial_cliente_id']) && $filters['historial_cliente_id']) {
            $query->andFilterWhere([
                'or',
                //['cliente_receptor_id'  => $filters['historial_cliente_id']],
                ['cliente_emisor_id'    => $filters['historial_cliente_id']]
            ]);
        }


        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
                ['like', 'sucursal_emisor_nombre', $search],
                ['like', 'nombre_emisor', $search],
                ['like', 'folio', $search],
                ['like', 'agente', $search],
            ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
