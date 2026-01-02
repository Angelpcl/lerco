<?php

namespace app\models\viaje;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use app\models\Esys;
use app\models\user\User;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;
use app\models\movimiento\MovimientoPaquete;


/**
 * This is the model class for table "trailer".
 *
 * @property int $id ID
 * @property int $fecha_salida Fecha de salida
 * @property string $nombre_chofer Nombre de chofer
 * @property string $placas Placas
 * @property int $status Estatus
 * @property int $created_at Creado por
 * @property int $created_by Creado by
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Viaje extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE     = 10;
    const STATUS_CERRADO    = 20;
    const STATUS_TERMINADO  = 30;
    const STATUS_CANCEL     = 2;
    const STATUS_INACTIVE   = 1;

    const ETAPA_ENABLE          = 10;
    const CARGA_MAXIMA_TIE      = 750;


    public static $statusList = [
        self::STATUS_ACTIVE     => 'Habilitado',
        self::STATUS_CERRADO    => 'Cerrado / Enviado',
        self::STATUS_TERMINADO  => 'Terminado / Concluido',
        self::STATUS_CANCEL     => 'Cancelado',
        self::STATUS_INACTIVE   => 'Inhabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'viaje';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_salida'], 'required'],
            [['fecha_salida'], 'safe'],
            [['status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'tipo_servicio', 'envio_ini_id', 'envio_fin_id', 'num_viaje', 'etapa_1', 'etapa_2', 'etapa_3', 'etapa_4'], 'integer'],
            [['nombre_chofer'], 'string', 'max' => 100],
            [['transportista'], 'string', 'max' => 150],
            [['placas'], 'string', 'max' => 50],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha_salida' => 'Fecha Salida',
            'nombre_chofer' => 'Nombre Chofer',
            'placas' => 'Placas',
            'num_viaje' => 'N° de viaje',
            'transportista' => 'Transportista',
            'envio_fin_id' => 'Envio fin',
            'envio_ini_id' => 'Envio inicio',
            'tipo_servicio' => 'Tipo de servicio',
            'etapa_1' => 'Etapa Uno',
            'etapa_2' => 'Etapa Dos',
            'etapa_3' => 'Etapa Tres',
            'etapa_4' => 'Etapa Cuatro',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvioIni()
    {
        return $this->hasOne(Envio::className(), ['id' => 'envio_ini_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvioFin()
    {
        return $this->hasOne(Envio::className(), ['id' => 'envio_fin_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovimientoPaquetes()
    {
        return $this->hasMany(MovimientoPaquete::className(), ['viaje_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */

    public static function getLoadEnvioInicial()
    {
        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "movimiento_paquete.tracked",
                "movimiento_paquete.paquete_id",
                "envio_detalle.envio_id",
                "envio.folio",
            ])
            ->from('movimiento_paquete')
            ->innerJoin('envio_detalle', 'movimiento_paquete.paquete_id = envio_detalle.id')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->andWhere([
                "and",
                ["=", "envio.tipo_envio", Envio::TIPO_ENVIO_TIERRA],
                ["=", "envio.status", Envio::STATUS_HABILITADO],
                ["=", "envio_detalle.status", EnvioDetalle::STATUS_HABILITADO],
                ["<>", "movimiento_paquete.tipo_envio", 30],
                ["=", "movimiento_paquete.tipo", 10],
                //[ "=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )", 10 ]
                //[ "<>", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)", 1 ],

                [">", "if((SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id),10,0)", 0],

                ["=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )", 10],
            ])
            ->groupBy("movimiento_paquete.tracked")
            ->limit(Viaje::CARGA_MAXIMA_TIE)
            ->all();

        $folio_inicial = isset($query[0]["folio"]) ? $query[0]["folio"] : 0;
        $folio_final   =  count($query) > 0 ?  $query[(count($query) - 1)]["folio"] : 0;

        return [
            "folio_inicial" => $folio_inicial,
            "folio_final" => $folio_final,
        ];
    }

    public function getViajeDetalles()
    {
        return $this->hasMany(ViajeDetalle::className(), ['viaje_id' => 'id']);
    }

    public static function getEtapa_($viaje_id)
    {
        $queryPaquetes = (new Query())
            ->select([
                "movimiento_paquete.tracked",
                "movimiento_paquete.paquete_id",
                "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) as tipo_movimiento",
                "envio_detalle.envio_id",
                "envio_detalle.bodega_descarga",
                "viaje_detalle.viaje_id",
                "envio.folio",
                "envio.total",
                "(SELECT count(*) FROM viaje_paquete_denegado v_denagado where v_denagado.viaje_id = " . $viaje_id . " and  v_denagado.tracked = movimiento_paquete.tracked) as is_denegado",
                "(SELECT cobro_rembolso_envio.created_at FROM cobro_rembolso_envio WHERE cobro_rembolso_envio.envio_id = envio.id order by cobro_rembolso_envio.created_at desc limit 1) AS fecha_pago",
                "(SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id) AS total_pagado",
            ])
            ->from('movimiento_paquete')
            ->innerJoin('envio_detalle', 'movimiento_paquete.paquete_id = envio_detalle.id')
            ->leftJoin('viaje_detalle', 'envio_detalle.id = viaje_detalle.paquete_id and viaje_detalle.tracked = movimiento_paquete.tracked')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')

            ->andWhere([
                "and",
                ["=", "envio.tipo_envio", Envio::TIPO_ENVIO_TIERRA],
                ["<>", "envio.status", Envio::STATUS_CANCELADO],
                ["<>", "envio_detalle.status", EnvioDetalle::STATUS_CANCELADO],
                ["=", "movimiento_paquete.tipo", 10],
                ["<>", "movimiento_paquete.tipo_envio", 30],
                // ===================================== Filtra paquetes que ya cuenta con pago realizado ==================================================
                [">", "if((SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id),10,0)", 0],
            ])
            ->andWhere(
                [
                    "or",
                    ["=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)", 10],
                    ["=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)", 2],
                ]
            )
            //->orderBy("movimiento_paquete.tracked asc")
            ->orderBy("fecha_pago, movimiento_paquete.tracked asc")
            ->groupBy("movimiento_paquete.tracked");

        return count($queryPaquetes->all()) > 0 ? $queryPaquetes->all() : [];
    }
    public static function getEtapa($viaje_id)
    {
        $subqueryUltimoMovimiento = (new Query())
            ->select('mv.tipo_movimiento')
            ->from('movimiento_paquete mv')
            ->where('mv.tracked = mp.tracked')
            ->orderBy('mv.id DESC')
            ->limit(1);

        $subqueryIsDenegado = (new Query())
            ->select('COUNT(*)')
            ->from('viaje_paquete_denegado v_denegado')
            ->where('v_denegado.viaje_id = :viaje_id AND v_denegado.tracked = mp.tracked', [':viaje_id' => $viaje_id]);

        $subqueryFechaPago = (new Query())
            ->select('cre.created_at')
            ->from('cobro_rembolso_envio cre')
            ->where('cre.envio_id = e.id')
            ->orderBy('cre.created_at DESC')
            ->limit(1);

        $subqueryTotalPagado = (new Query())
            ->select('SUM(cre.cantidad)')
            ->from('cobro_rembolso_envio cre')
            ->where('cre.envio_id = e.id');

        $queryPaquetes = (new Query())
            ->select([
                "mp.tracked",
                "mp.paquete_id",
                "({$subqueryUltimoMovimiento->createCommand()->getRawSql()}) as tipo_movimiento",
                "ed.envio_id",
                "ed.bodega_descarga",
                "vd.viaje_id",
                "e.folio",
                "e.total",
                "({$subqueryIsDenegado->createCommand()->getRawSql()}) as is_denegado",
                "({$subqueryFechaPago->createCommand()->getRawSql()}) as fecha_pago",
                "({$subqueryTotalPagado->createCommand()->getRawSql()}) as total_pagado",
            ])
            ->from('movimiento_paquete mp')
            ->innerJoin('envio_detalle ed', 'mp.paquete_id = ed.id')
            ->leftJoin('viaje_detalle vd', 'ed.id = vd.paquete_id AND vd.tracked = mp.tracked')
            ->innerJoin('envio e', 'ed.envio_id = e.id')
            ->where([
                'and',
                ['e.tipo_envio' => Envio::TIPO_ENVIO_TIERRA],
                ['<>', 'e.status', Envio::STATUS_CANCELADO],
                ['<>', 'ed.status', EnvioDetalle::STATUS_CANCELADO],
                ['mp.tipo' => 10],
                ['<>', 'mp.tipo_envio', 30],
                ['>', "({$subqueryTotalPagado->createCommand()->getRawSql()})", 10],
            ])
            ->andWhere([
                'or',
                ["({$subqueryUltimoMovimiento->createCommand()->getRawSql()})" => 10],
                ["({$subqueryUltimoMovimiento->createCommand()->getRawSql()})" => 2],
            ])
            ->orderBy(['fecha_pago' => SORT_ASC, 'mp.tracked' => SORT_ASC])
            ->groupBy('mp.tracked');

        return $queryPaquetes->all();
    }

    


    public function getPesoTotalViaje()
    {
        $pesoTotalViaje = 0;
        $ViajeDetalle = ViajeDetalle::find()->andWhere(['viaje_id' => $this->id])->all();
        foreach ($ViajeDetalle as $key => $item) {
            $pesoTotalViaje =  $pesoTotalViaje + round($item->envioDetalleLaxTierra->peso / intval($item->envioDetalleLaxTierra->cantidad), 2);
        }
        return $pesoTotalViaje;
    }

    public static function getPQEntregados($viaje_id)
    {
        $queryPaquetes = (new Query())
            ->select([
                'viaje_detalle.id',
                '(select mv.tipo_movimiento from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked order by id desc limit 1 ) as tipo_movimiento_top',
            ])
            ->from('viaje_detalle')
            ->andWhere([
                "and",
                ['viaje_detalle.viaje_id' => $viaje_id],
                ["(select mv.tipo_movimiento from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked order by id desc limit 1 )" => MovimientoPaquete::LAX_TIER_ENTREGADO]
            ])
            ->all();

        return count($queryPaquetes);
    }

    public static function getSucursalPaquete($viaje_id, $sucursal_id)
    {

        $queryPaquetes = (new Query())
            ->select([
                "count(viaje_detalle.id) as paquete_count",
                "SUM(ROUND(envio_detalle.peso / envio_detalle.cantidad, 2)) as peso_unitario",

                "SUM(round(envio.total / (select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id ),2)) as total_unitario",

                'SUM(`envio_detalle`.`costo_seguro`)  as total_costo_seguro',

                '`sucursal`.`nombre` AS `sucursal_receptor`',
                'SUM(envio.total) AS total',
                'SUM((select
                sum(`cobro`.`cantidad`)
                from `cobro_rembolso_envio` `cobro`
                where
                 (
                   (`cobro`.`tipo` = 10) and
                   (`cobro`.`envio_id` = `envio`.`id`)
                 ))) AS `monto_pagado`',

                'SUM(
                    (
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
                    )
                ) AS `monto_deuda`',
            ])
            ->from('envio_detalle')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->innerJoin('viaje_detalle', 'envio_detalle.id = viaje_detalle.paquete_id')
            ->leftJoin('sucursal', 'envio_detalle.sucursal_receptor_id = sucursal.id')
            ->andWhere([
                "and",
                ['=', 'viaje_detalle.viaje_id', $viaje_id],
                ['=', 'sucursal.id', $sucursal_id]
            ])
            ->groupBy('sucursal.id');

        return $queryPaquetes->all();
    }


    public static function getSucursalEnviaPaquete($viaje_id, $sucursal_id)
    {

        $queryPaquetes = (new Query())
            ->select([
                "count(viaje_detalle.id) as paquete_count",
                "SUM(ROUND(envio_detalle.peso / envio_detalle.cantidad, 2)) as peso_unitario",

                '`sucursal`.`nombre` AS `sucursal_emisor`',

                'SUM((
                    (10 * (
                      ((SELECT paquete.peso FROM envio_detalle paquete inner  join envio env on paquete.envio_id = env.id  where paquete.id = envio_detalle.id  and env.sucursal_emisor_id = sucursal.id and envio_detalle.status = 10 ) / envio_detalle.cantidad ) * envio.precio_libra_actual ) ) / 100
                )) AS comision_envio',

                'SUM((
                    14.28 * ((SELECT paquete.costo_seguro FROM envio_detalle paquete inner  join envio env on paquete.envio_id = env.id  where paquete.id = envio_detalle.id   and env.sucursal_emisor_id = sucursal.id and envio_detalle.status = 10 ) / envio_detalle.cantidad ) / 100
                )) AS comision_aseguranza',

                'SUM((
                    ((10 * ( ((SELECT paquete.peso FROM envio_detalle paquete inner  join envio env on paquete.envio_id = env.id where paquete.id = envio_detalle.id   and env.sucursal_emisor_id = sucursal.id and envio_detalle.status = 10 )  / envio_detalle.cantidad )  * envio.precio_libra_actual ) ) / 100) + ( 14.28 * ((SELECT  paquete.costo_seguro FROM envio_detalle paquete inner  join envio env on paquete.envio_id = env.id  where paquete.id = envio_detalle.id  and env.sucursal_emisor_id = sucursal.id and envio_detalle.status = 10 )  / envio_detalle.cantidad ) / 100 )
                )) as total_comision',



                'SUM((
                    (90 * (((SELECT paquete.peso FROM envio_detalle paquete inner  join envio env on paquete.envio_id = env.id  where paquete.id = envio_detalle.id  and env.sucursal_emisor_id = sucursal.id and envio_detalle.status = 10 ) / envio_detalle.cantidad ) * envio.precio_libra_actual ) ) / 100
                )) AS comision_envio_dimas',

                'SUM((
                    85.68 * ((SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete inner  join envio env on paquete.envio_id = env.id  where paquete.id = envio_detalle.id  and env.sucursal_emisor_id = sucursal.id and envio_detalle.status = 10 ) / envio_detalle.cantidad ) / 100
                )) AS comision_aseguranza_dimas',

                'SUM((
                    ((90 * (((SELECT SUM(paquete.peso) FROM envio_detalle paquete inner  join envio env on paquete.envio_id = env.id where paquete.id = envio_detalle.id  and env.sucursal_emisor_id = sucursal.id and envio_detalle.status = 10 ) / envio_detalle.cantidad ) * envio.precio_libra_actual ) ) / 100) + ( 85.68 * ((SELECT SUM(paquete.costo_seguro) FROM envio_detalle paquete inner  join envio env on paquete.envio_id = env.id  where paquete.id = envio_detalle.id  and env.sucursal_emisor_id = sucursal.id and envio_detalle.status = 10 ) / envio_detalle.cantidad ) / 100 )
                )) as total_comision_dimas',
            ])
            ->from('envio_detalle')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->innerJoin('sucursal', 'envio.sucursal_emisor_id = sucursal.id')
            ->innerJoin('viaje_detalle', 'envio_detalle.id = viaje_detalle.paquete_id')
            ->andWhere([
                "and",
                ['=', 'viaje_detalle.viaje_id', $viaje_id],
                ['=', 'sucursal.id', $sucursal_id]
            ])
            ->groupBy('sucursal.id');

        return $queryPaquetes->all();
    }


    public static function getSucursalEnviaPaqueteAll($viaje_id, $sucursal_id)
    {

        $queryPaquetes = (new Query())
            ->select([
                '`viaje_detalle`.`id`',
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
                '`sucursal`.`nombre` AS `sucursal_emisor`',
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
            ])
            ->from('envio_detalle')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->leftJoin('sucursal', 'envio.sucursal_emisor_id = sucursal.id')
            ->innerJoin('viaje_detalle', 'envio_detalle.id = viaje_detalle.paquete_id')
            ->leftJoin('cliente', 'envio_detalle.cliente_receptor_id = cliente.id')
            ->innerJoin("producto as pr", "envio_detalle.producto_id = pr.id")
            ->andWhere([
                "and",
                ['=', 'viaje_detalle.viaje_id', $viaje_id],
                ['=', 'sucursal.id', $sucursal_id]
            ]);

        return $queryPaquetes->all();
    }

    public static function getSucursalPaqueteAll($viaje_id, $sucursal_id)
    {

        $queryPaquetes = (new Query())
            ->select([
                '`viaje_detalle`.`id`',
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
            ])
            ->from('envio_detalle')
            ->innerJoin('envio', 'envio_detalle.envio_id = envio.id')
            ->innerJoin('viaje_detalle', 'envio_detalle.id = viaje_detalle.paquete_id')
            ->leftJoin('sucursal', 'envio_detalle.sucursal_receptor_id = sucursal.id')
            ->leftJoin('cliente', 'envio_detalle.cliente_receptor_id = cliente.id')
            ->innerJoin("producto as pr", "envio_detalle.producto_id = pr.id")
            ->andWhere([
                "and",
                ['=', 'viaje_detalle.viaje_id', $viaje_id],
                ['=', 'sucursal.id', $sucursal_id]
            ]);

        return $queryPaquetes->all();
    }



    public static function getPQReparto($viaje_id)
    {
        $queryPaquetes = (new Query())
            ->select([
                'viaje_detalle.id',
                '(select mv.tipo_movimiento from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked order by id desc limit 1 ) as tipo_movimiento_top',
            ])
            ->from('viaje_detalle')
            ->andWhere([
                "and",
                ['=', 'viaje_detalle.viaje_id', $viaje_id],
                ['=', "(select mv.tipo_movimiento from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked order by id desc limit 1 )", MovimientoPaquete::LAX_TIER_TRANSCURSO]
            ])
            ->all();

        return count($queryPaquetes);
    }


    public static function getPQBodega($viaje_id)
    {
        $queryPaquetes = (new Query())
            ->select([
                'viaje_detalle.id',
                '(select mv.tipo_movimiento from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked order by id desc limit 1 ) as tipo_movimiento_top',
            ])
            ->from('viaje_detalle')
            ->andWhere([
                "and",
                ['=', 'viaje_detalle.viaje_id', $viaje_id],
                ['=', "(select mv.tipo_movimiento from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked order by id desc limit 1 )", MovimientoPaquete::LAX_TIER_BODEGA]
            ])
            ->all();

        return count($queryPaquetes);
    }

    public static function getTranscursoMex()
    {
        $Viaje = self::find()->andWhere(["status" => self::STATUS_ACTIVE])->andWhere(['tipo_servicio' => Envio::TIPO_ENVIO_MEX]);
        return ArrayHelper::map($Viaje->all(), 'id', function ($value) {

            return '[' . $value->placas . '] ' . $value->nombre_chofer . '/ Fecha de salida : [' . date('Y-m-d', $value->fecha_salida) . ']';
        });
    }
    /*public static function getTranscursoLax()
    {
        $Viaje = self::find()->andWhere([ "status" => self::STATUS_ACTIVE ])->andWhere([ 'tipo_servicio' => Envio::TIPO_ENVIO_LAX ]);
        return ArrayHelper::map($Viaje->all(), 'id', function($value){

            return '['.$value->placas.'] '.$value->nombre_chofer . '/ Fecha de salida : ['. date('Y-m-d', $value->fecha_salida) .']';
        });
    }*/
    public static function getTranscursoTierra($is_mapeo = false)
    {
        if ($is_mapeo) {
            $Viaje = self::find()
                ->leftJoin("mapeo_detalle", "viaje.id = mapeo_detalle.viaje_id")
                ->andWhere([
                    'or',
                    ["=", "viaje.status", self::STATUS_CERRADO],
                    ["=", "viaje.status", self::STATUS_TERMINADO],
                ])
                ->andWhere([
                    'or',
                    ['=', 'tipo_servicio', Envio::TIPO_ENVIO_TIERRA],
                    //['=' ,'tipo_servicio' , Envio::TIPO_ENVIO_LAX],
                ]);

            /*$Viaje->andWhere(['or',
                ['IS', 'mapeo_detalle.viaje_id', new \yii\db\Expression('null')],
            ]);*/
        } else
            $Viaje = self::find()->andWhere(["status" => self::STATUS_ACTIVE])->andWhere(['tipo_servicio' => Envio::TIPO_ENVIO_TIERRA]);


        return ArrayHelper::map($Viaje->all(), 'id', function ($value) {

            return '[' . Envio::$tipoList[$value->tipo_servicio] . '] ' . '[' . $value->placas . '] ' . $value->nombre_chofer . '/ Fecha de salida : [' . date('Y-m-d', $value->fecha_salida) . ']';
        });
    }

    public static function getViajeTranscursoTierra()
    {
        $Viaje = self::find()
            ->leftJoin("reparto", "viaje.id = reparto.viaje_id")
            ->andWhere([
                'or',
                ["=", "viaje.status", self::STATUS_CERRADO],
                ["=", "viaje.status", self::STATUS_TERMINADO],
            ])
            ->andWhere(['tipo_servicio' => Envio::TIPO_ENVIO_TIERRA])->orderBy('viaje.id desc');


        return ArrayHelper::map($Viaje->all(), 'id', function ($value) {

            return '[' . Envio::$tipoList[$value->tipo_servicio] . '] ' . '[' . $value->placas . '] ' . $value->nombre_chofer . '/ Fecha de salida : [' . date('Y-m-d', $value->fecha_salida) . ']';
        });
    }

    public static function getItems()
    {
        $Viaje = self::find()
            ->andWhere([
                'and',
                ["<>", "viaje.status", self::STATUS_CANCEL],
            ])
            ->andWhere(['tipo_servicio' => Envio::TIPO_ENVIO_TIERRA])->orderBy('viaje.id desc');


        return ArrayHelper::map($Viaje->all(), 'id', function ($value) {

            return '[' . Envio::$tipoList[$value->tipo_servicio] . '] ' . '[' . $value->placas . '] ' . $value->nombre_chofer . '/ Fecha de salida : [' . date('Y-m-d', $value->fecha_salida) . ']';
        });
    }


    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->fecha_salida = Esys::stringToTimeUnix($this->fecha_salida);
            if ($insert) {
                $this->status = self::STATUS_ACTIVE;
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            } else {

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            }

            return true;
        } else
            return false;
    }
}
