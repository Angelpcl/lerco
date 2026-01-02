<?php

namespace app\models\viaje;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;

/**
 * This is the model class for table "view_trailer".
 *
 * @property int $id ID
 * @property int $fecha_salida Fecha de salida
 * @property string $nombre_chofer Nombre de chofer
 * @property string $placas Placas
 * @property int $status Estatus
 * @property string $nota Nota
 * @property int $created_at Creado por
 * @property int $created_by Creado by
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewViaje extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_viaje';
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
            'status' => 'Status',
            'nota' => 'Nota',
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
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    "SQL_CALC_FOUND_ROWS `id`",
                        'fecha_salida',
                        'nombre_chofer',
                        'placas',
                        'status',
                        'tipo_servicio',
                        'nota',
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
        / Filtramos la consulta
        /***********************************/
            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);

            if (isset($filters['tipo_servicio']) && $filters['tipo_servicio'])
                $query->andWhere(['tipo_servicio' =>  $filters['tipo_servicio']]);

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getReporteViajeAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
            ->select([
                "envio.id",
                "envio.folio",
                "concat_ws(' ',ce.nombre,ce.apellidos) as cliente_emisor",
                "se.nombre as sucursal_emisor",
                "ce.telefono as telefono_emisor",
                "ce.telefono_movil",
                "concat_ws(' ',user.nombre,user.apellidos) as vendedor",
                "esys.direccion",
                "estado.singular as estado",
                "municipio.singular as municipio",
                "esys.referencia",
                "envio.is_reenvio",
                "(SELECT SUM(envio_detalle.peso) FROM envio_detalle where envio_detalle.envio_id = envio.id) AS peso_total_paquete",
                "envio.costo_reenvio",
                "envio.precio_libra_actual",
                "envio.peso_total",
                "envio.subtotal",
                "envio.seguro_total",
                "envio.impuesto",
                "envio.total",
                "envio.created_at",
            ])
            ->from('envio')
            ->leftJoin('cliente ce','envio.cliente_emisor_id = ce.id')
            ->leftJoin('sucursal se','envio.sucursal_emisor_id = se.id')

            ->leftJoin('esys_direccion esys','envio.id = esys.cuenta_id and esys.cuenta = 4')
            ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
            ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id and municipio.label = "crm_municipio" ')
            ->innerJoin('user' ,'envio.created_by = user.id')
            ->andWhere(['envio.id' => $arr['envio_id']]);
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->one();
    }

    public static function getReporteViajePaqueteDetalleDireccion($arr){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                    ->select([
                        "esys.id",
                        "esys.direccion",
                        "estado.singular as estado",
                        "municipio.singular as municipio",
                        "esys.referencia",
                    ])
                    ->from('esys_direccion esys')
                    ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
                    ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id and municipio.label = "crm_municipio" ')
                    ->andWhere(['esys.cuenta' => 5])
                    ->andWhere(['esys.tipo'   => 1])
                    ->andWhere(['esys.cuenta_id' => $arr['paquete_id']]);
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->one();
    }

    public static function getReporteViajePaqueteDetalle($arr,$params)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "viaje_detalle.viaje_id",
                    "viaje_detalle.paquete_id",
                    "viaje_detalle.tracked",
                    "viaje_detalle.peso_mx",
                    "envio_detalle.envio_id",
                    "envio_detalle.status",
                    "envio_detalle.valor_declarado",
                    "envio_detalle.cantidad_piezas",
                    "envio_detalle.cantidad",
                    "envio_detalle.peso",
                    "envio_detalle.impuesto",
                    "envio_detalle.costo_seguro",
                    "envio_detalle.observaciones",
                    "ruta.nombre as nombre_ruta",
                    "sucursal.nombre as sucursal_receptor",
                    "concat_ws(' ', cliente.nombre, cliente.apellidos) as cliente_receptor",
                    "producto.nombre",
                    "cliente.telefono as telefono_cliente",
                    "cliente.telefono_movil",
                    "(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    ) as tipo_movimiento_top",
                ])
                ->from('envio_detalle')
                ->innerJoin('viaje_detalle','envio_detalle.id = viaje_detalle.paquete_id')
                ->leftJoin('sucursal','envio_detalle.sucursal_receptor_id = sucursal.id')
                ->leftJoin('cliente','envio_detalle.cliente_receptor_id = cliente.id')

                ->leftJoin('ruta_sucursal','sucursal.id = ruta_sucursal.sucursal_id')
                ->leftJoin('ruta','ruta_sucursal.ruta_id = ruta.id')

                ->innerJoin('producto' ,'envio_detalle.producto_id = producto.id')
                ->andWhere(['envio_detalle.envio_id'    => $arr['envio_id']])
                ->andWhere(['envio_detalle.status'      => EnvioDetalle::STATUS_HABILITADO ])
                ->andWhere(['viaje_detalle.viaje_id'    => $params['viaje_id']]);

        return $query->all();
    }

    public static function getReportePaqueteDetalleAll($arr,$params)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "envio_detalle.id",
                    "viaje_detalle.viaje_id",
                    "viaje_detalle.paquete_id",
                    "viaje_detalle.tracked",
                    "viaje_detalle.peso_mx",
                    "envio_detalle.envio_id",
                    "envio_detalle.status",
                    "envio_detalle.valor_declarado",
                    "envio_detalle.cantidad_piezas",
                    "envio_detalle.cantidad",
                    "envio_detalle.peso",
                    "envio_detalle.impuesto",
                    "envio_detalle.costo_seguro",
                    "envio_detalle.observaciones",
                    "ruta.nombre as nombre_ruta",
                    "sucursal.nombre as sucursal_receptor",
                    "concat_ws(' ', cliente.nombre, cliente.apellidos) as cliente_receptor",
                    "producto.nombre",
                    "producto.is_producto",
                    "cliente.telefono as telefono_cliente",
                    "cliente.telefono_movil",
                    "(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    ) as tipo_movimiento_top",
                ])
                ->from('envio_detalle')
                ->innerJoin('viaje_detalle','envio_detalle.id = viaje_detalle.paquete_id')
                ->leftJoin('sucursal','envio_detalle.sucursal_receptor_id = sucursal.id')
                ->leftJoin('cliente','envio_detalle.cliente_receptor_id = cliente.id')

                ->leftJoin('ruta_sucursal','sucursal.id = ruta_sucursal.sucursal_id')
                ->leftJoin('ruta','ruta_sucursal.ruta_id = ruta.id')

                ->innerJoin('producto' ,'envio_detalle.producto_id = producto.id')
                ->andWhere(['envio_detalle.envio_id'    => $arr['envio_id']])
                ->andWhere(['envio_detalle.status'      => EnvioDetalle::STATUS_HABILITADO ]);
                //->andWhere(['viaje_detalle.viaje_id'    => $params['viaje_id']]);

        return $query->all();
    }

    public static function getReporteViajeEnvio($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "envio_detalle.envio_id",
                ])
                ->from('envio_detalle')
                ->innerJoin('viaje_detalle','envio_detalle.id = viaje_detalle.paquete_id')
                ->andWhere(['viaje_detalle.viaje_id' => $arr['viaje_id']])
                ->andWhere(['envio_detalle.status' => EnvioDetalle::STATUS_HABILITADO ])
                ->groupBy(['envio_detalle.envio_id']);
        return $query->all();
    }

    public static function getReporteViajeMexEnvio($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "viaje_detalle.caja_id",
                    "viaje_detalle.paquete_id",
                    "viaje_detalle.tracked",
                    "viaje_detalle.tipo",
                ])
                ->from('viaje_detalle')
                ->andWhere(['viaje_detalle.viaje_id' => $arr['viaje_id']])
                ->groupBy(['viaje_detalle.id']);
        return $query->all();
    }

    public static function getReporteViajeConcilacion($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "viaje.id",
                    "viaje_detalle.paquete_id",
                    "viaje_detalle.tracked",
                    "sucursal_emisor.nombre as sucursal_emisor_nombre",
                    "sucursal_receptor.nombre as sucursal_receptor_nombre",
                    "viaje_detalle.tipo",
                    "envio_detalle.cantidad",
                    "envio_detalle.peso",
                    "esys.estado_id as estado_id",
                    "esys.municipio_id as municipio_id",
                    "estado.singular as estado",
                    "municipio.singular as municipio",
                    "viaje_detalle.peso_mx  as peso_reparto",
                    "envio_detalle.valor_declarado",
                    "envio_detalle.observaciones",
                    "pro.nombre",
                    "(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    ) as tipo_movimiento_top"
                ])
                ->from('viaje')
                ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                ->leftJoin('envio_detalle','viaje_detalle.paquete_id = envio_detalle.id')
                ->leftJoin('esys_direccion esys','envio_detalle.id = esys.cuenta_id and esys.cuenta = 5')
                ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
                ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
                ->leftJoin('sucursal sucursal_receptor', 'envio_detalle.sucursal_receptor_id = sucursal_receptor.id')
                ->leftJoin('producto pro','envio_detalle.producto_id = pro.id')
                ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                ->leftJoin('sucursal sucursal_emisor', 'envio.sucursal_emisor_id = sucursal_emisor.id')
                ->andWhere(['viaje.id' => $arr['viaje_id']]);

        return $query->all();
    }

      public static function getPaquetesSinViaje($arr)
    {
        $Viaje = Viaje::findOne($arr['viaje_id']);

        if ($Viaje) {

            $viajeDetalleAsc = ViajeDetalle::find()->andWhere([ "viaje_id" => $Viaje->id ])->orderBy("paquete_id asc")->all();
            $viajeDetalleDesc = ViajeDetalle::find()->andWhere([ "viaje_id" => $Viaje->id ])->orderBy("paquete_id desc")->all();



            Yii::$app->response->format = Response::FORMAT_JSON;
            $query = (new Query())
                    ->select([
                        "movimiento_paquete.id",
                        "movimiento_paquete.paquete_id",
                        "sucursal.nombre as sucursal_receptor",
                        "movimiento_paquete.tracked",
                        "movimiento_paquete.tipo",
                        "envio_detalle.cantidad",
                        "envio_detalle.peso",
                        "movimiento_paquete.created_at",
                        "envio_detalle.valor_declarado",
                        "envio_detalle.observaciones",
                        "pro.nombre",
                        "estado.singular as estado",
                        "municipio.singular as municipio",
                        "esys.direccion",
                        "(select mv.peso_mx  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked and  mv.tipo_movimiento = 30 order by mv.id asc limit 1) as mov_peso_mx",
                        "(select mv.tipo_movimiento
                           from movimiento_paquete mv
                            where mv.tracked = movimiento_paquete.tracked order by id desc limit 1
                        ) as tipo_movimiento_top"

                    ])
                    ->from('movimiento_paquete')
                    ->leftJoin('envio_detalle','movimiento_paquete.paquete_id = envio_detalle.id')
                    ->leftJoin('esys_direccion esys','envio_detalle.id = esys.cuenta_id and esys.cuenta = 5')
                    ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
                    ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id and municipio.label = "crm_municipio" ')
                    ->leftJoin('sucursal','envio_detalle.sucursal_receptor_id = sucursal.id')
                    ->leftJoin('producto pro','envio_detalle.producto_id = pro.id')
                    ->andWhere(["and",
                        ['=','(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)',  30],

                        ['=','(select count(*) from movimiento_paquete  mov where mov.tracked = movimiento_paquete.tracked and mov.tipo_movimiento = 20 )' ,  0 ],
                    ])->groupBy('movimiento_paquete.tracked');

            $query->andWhere([ "and",
                [">=", "envio_detalle.envio_id",$viajeDetalleAsc[0]->envioDetalleLaxTierra->envio_id],
                ["<=", "envio_detalle.envio_id",$viajeDetalleDesc[0]->envioDetalleLaxTierra->envio_id]
            ]);

            //echo ($query->createCommand()->rawSql) . '<br/><br/>';
            //die();

            return $query->all();

        }

        return [];
    }

    public static function getReporteReimpresion($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "viaje.id",
                    "viaje_detalle.paquete_id",
                    "s_receptor.id as sucursal_id",
                    "s_receptor.nombre as sucursal_receptor",
                    "viaje_detalle.tracked",
                    "esys.referencia",
                    "estado.singular as estado",
                    "municipio.singular as municipio",
                    "envio_detalle.is_reenvio",
                    "esys.direccion",
                    "ruta.nombre as ruta_nombre",
                    "CONCAT_WS(' ',c_emisor.nombre,c_emisor.apellidos)  as emisor_nombre",
                    "CONCAT_WS(' ',c_receptor.nombre,c_receptor.apellidos)  as receptor_nombre",
                    "c_emisor.telefono as emisor_telefono",
                    "c_receptor.telefono as receptor_telefono",
                    "c_emisor.telefono_movil as emisor_telefono_movil",
                    "c_receptor.telefono_movil as receptor_telefono_movil",
                    "envio_detalle.cantidad_piezas",
                    "envio_detalle.peso",
                    "envio.id as envio_id",
                    "(select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id) as cantidad",

                    "(SELECT count(*) FROM viaje_detalle as env_viaje inner join envio_detalle env_detalle on env_viaje.paquete_id = env_detalle.id where env_detalle.envio_id = envio.id  and env_viaje.viaje_id = viaje.id ) as paquetes_envio_mapeo",


                    "envio_detalle.valor_declarado",
                    "envio_detalle.observaciones",
                    "pro.nombre as producto_nombre",
                ])
                ->from('viaje')
                ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                ->leftJoin('envio_detalle','viaje_detalle.paquete_id = envio_detalle.id')
                ->innerJoin('envio','envio_detalle.envio_id = envio.id')

                ->leftJoin('esys_direccion esys','envio_detalle.id = esys.cuenta_id and esys.cuenta = 5')
                ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
                ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id and municipio.label = "crm_municipio" ')

                ->innerJoin('cliente c_emisor','envio.cliente_emisor_id = c_emisor.id')
                ->innerJoin('cliente c_receptor','envio_detalle.cliente_receptor_id = c_receptor.id')
                ->leftJoin('producto pro','envio_detalle.producto_id = pro.id')
                ->leftJoin('ruta_sucursal' ,'envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
                ->leftJoin('sucursal s_receptor', 'ruta_sucursal.sucursal_id = s_receptor.id')
                ->leftJoin('ruta' ,'ruta_sucursal.ruta_id = ruta.id')
                ->andWhere(['viaje.id' => $arr['viaje_id']])
                ->orderBy('ruta.orden,s_receptor.id desc, viaje_detalle.tracked asc');

        return $query->all();
    }

    public static function getValidaEnvios($envio_ini, $envio_fin, $tipo)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "envio.id",
                ])
                ->from('movimiento_paquete')
                ->innerJoin('envio_detalle','movimiento_paquete.paquete_id = envio_detalle.id')
                ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                ->andWhere(['and',
                        ["between","envio.id",$envio_ini,$envio_fin],
                        ["=","envio.tipo_envio" , $tipo ],
                        ["=","envio.status" , Envio::STATUS_HABILITADO ],
                        ["=","envio_detalle.status" , EnvioDetalle::STATUS_HABILITADO ],
                        [ "<>", "movimiento_paquete.tipo_envio", 30 ], //Modificación para permitir que muestre LAX en reportes TIE
                        [ "=", "movimiento_paquete.tipo", 10 ],

                        [ ">", "if((SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id),10,0)", 0 ],

                        [ "=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )", 10 ]
                    ])
                ->groupBy("envio.id")
                ->all();


        $queryPaquete = (new Query())
            ->select([
                "*",
            ])
            ->from('movimiento_paquete')
            ->innerJoin('envio_detalle','movimiento_paquete.paquete_id = envio_detalle.id')
            ->innerJoin('envio','envio_detalle.envio_id = envio.id')
            ->andWhere(["and",
                ["between","envio.id",$envio_ini,$envio_fin],
                ["=","envio.tipo_envio" , $tipo ],
                ["=","envio.status" , Envio::STATUS_HABILITADO ],
                ["=","envio_detalle.status" , EnvioDetalle::STATUS_HABILITADO ],
                [ "<>", "movimiento_paquete.tipo_envio", 30 ], //Modificación para permitir que muestre LAX en reportes TIE
                [ "=", "movimiento_paquete.tipo", 10 ],
                 // ===================================== Filtra paquetes que ya cuenta con pago realizado ==================================================
                [ ">", "if((SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id),10,0)", 0 ],

                [ "=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )", 10 ]
            ])
            ->groupBy("movimiento_paquete.tracked") //REVISAR CONSULTA ALERTAAAAAAA
            ->all();

        return [
            "code" => 202,
            "message" => "Numero de envios ". (count($query) ? count($query)  : 0) ." Numero de paquetes: " . (count($queryPaquete) ? count($queryPaquete) : 0) ." ",
            "num_paquete" => count($queryPaquete) ? count($queryPaquete) : 0,
        ];
    }

    public static function getValidaEnviosInfo($envio_ini, $envio_fin, $tipo)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;


        $queryPaquete = (new Query())
            ->select([
                "movimiento_paquete.tracked",
                "movimiento_paquete.paquete_id",
                "envio_detalle.envio_id",
                "envio.folio",
                "(SELECT cobro_rembolso_envio.created_at FROM cobro_rembolso_envio WHERE cobro_rembolso_envio.envio_id = envio.id order by cobro_rembolso_envio.created_at desc limit 1) AS fecha_pago",
            ])
            ->from('movimiento_paquete')
            ->innerJoin('envio_detalle','movimiento_paquete.paquete_id = envio_detalle.id')
            ->innerJoin('envio','envio_detalle.envio_id = envio.id')
            ->andWhere(["and",
                ["between","envio.id",$envio_ini,$envio_fin],
                [ "=","envio.tipo_envio" , $tipo ],
                [ "=","envio.status" , Envio::STATUS_HABILITADO ],
                [ "=","envio_detalle.status" , EnvioDetalle::STATUS_HABILITADO ],
                [ "<>", "movimiento_paquete.tipo_envio", 30 ], //Modificación para permitir que muestre LAX en reportes TIE
                [ "=", "movimiento_paquete.tipo", 10 ],
                // ===================================== Filtra paquetes que ya cuenta con pago realizado ==================================================
                [ ">", "if((SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id),10,0)", 0 ],
                [ "=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 )", 10 ],
            ])
            ->orderBy("fecha_pago, movimiento_paquete.tracked asc")
            ->groupBy("movimiento_paquete.tracked") //REVISAR CONSULTA ALERTAAAAAAA
            ->all();
            //echo ($queryPaquetes->createCommand()->rawSql) . '<br/><br/>';

        return [
            "code" => 202,
            "message" => $queryPaquete,
        ];
    }

     public static function getReporteCarga($viaje_id){
        $queryPaquetes = (new Query())
            ->select([
                "movimiento_paquete.tracked",
                "movimiento_paquete.paquete_id",
                "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) as tipo_movimiento",
                "envio_detalle.envio_id",
                "viaje_detalle.viaje_id",
                "envio.folio",
                "envio.total",
                "(SELECT count(*) FROM viaje_paquete_denegado v_denagado where v_denagado.viaje_id = " . $viaje_id ." and  v_denagado.tracked = movimiento_paquete.tracked) as is_denegado",
                "(SELECT cobro_rembolso_envio.created_at FROM cobro_rembolso_envio WHERE cobro_rembolso_envio.envio_id = envio.id order by cobro_rembolso_envio.created_at desc limit 1) AS fecha_pago",
                "(SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id) AS total_pagado",
            ])
            ->from('movimiento_paquete')
            ->innerJoin('envio_detalle','movimiento_paquete.paquete_id = envio_detalle.id')
            ->leftJoin('viaje_detalle','envio_detalle.id = viaje_detalle.paquete_id and viaje_detalle.tracked = movimiento_paquete.tracked')
            ->innerJoin('envio','envio_detalle.envio_id = envio.id')

            ->andWhere(["and",
                [ "=","envio.tipo_envio" , Envio::TIPO_ENVIO_TIERRA ],
                [ "=","envio.status" , Envio::STATUS_HABILITADO ],
                [ "=","envio_detalle.status" , EnvioDetalle::STATUS_HABILITADO ],
                [ "<>", "movimiento_paquete.tipo_envio", 30 ], //Modificación para permitir que muestre LAX en reportes TIE
                [ "=", "movimiento_paquete.tipo", 10 ],
                // ===================================== Filtra paquetes que ya cuenta con pago realizado ==================================================
                [ "=", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)", 10 ],

                [ ">", "if((SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id),10,0)", 0 ],
            ])
            /*->andWhere(['or',
                ["=","viaje_detalle.viaje_id" , $viaje_id ],
                ['IS', 'viaje_detalle.id', new \yii\db\Expression('null')],
            ])*/
            //->orderBy("movimiento_paquete.tracked asc")
            ->orderBy("fecha_pago, movimiento_paquete.tracked asc")
            ->groupBy("movimiento_paquete.tracked");

        return $queryPaquetes->all();
    }

    public  static function getPaquetesViaje($viaje_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $queryPaquete = (new Query())
            ->select([
                "viaje_detalle.tracked",
                "viaje_detalle.paquete_id",
                "envio_detalle.envio_id",
                "envio.folio",
            ])
            ->from('viaje_detalle')
            ->innerJoin('envio_detalle','viaje_detalle.paquete_id = envio_detalle.id')
            ->innerJoin('envio','envio_detalle.envio_id = envio.id')
             ->andWhere(['viaje_detalle.viaje_id' => $viaje_id ])
            ->orderBy("envio.id")
            ->all();


        return [
            "code" => 202,
            "message" => $queryPaquete,
        ];
    }


    public static function getSearchTrackedEnvio($tracked, $paquete_id, $viaje_id)
    {

        $query = (new Query())
            ->select([
                "COUNT(*) as count_paquete",
            ])
            ->from('viaje_detalle')
           ->andWhere(["and",
            ["=","viaje_detalle.viaje_id" , $viaje_id ],
            ["=","viaje_detalle.paquete_id" , $paquete_id ],
            ["=","viaje_detalle.tracked" , $tracked ],
        ])->all();

        return isset($query[0]["count_paquete"]) && $query[0]["count_paquete"] > 0   ? true: false;
    }

    /**************************************************************************
                                REPORTE FINANCIERO
    /***************************************************************************/
    public static function getReporteFinanciero($viaje_id, $sucursal_envia = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "viaje.id",
                    "viaje_detalle.tracked",
                    "sucursal.id as sucursal_id",
                    "sucursal.nombre as sucursal_nombre",
                    "sucursal_receptor.nombre as sucursal_receptor_nombre",
                    "sucursal_receptor.is_reenvio as sucursal_recibe_is_reenvio",
                    "viaje_detalle.paquete_id",
                    "envio_detalle.peso",
                    "envio_detalle.valor_declarado",
                    "envio_detalle.bodega_descarga",
                    "esys.direccion",
                    "estado.singular as estado",
                    "municipio.singular as municipio",
                    "estado_sucursal.singular as estado_sucursal",
                    "municipio_sucursal.singular as municipio_sucursal",
                    "esys_direccion_codigo_postal.codigo_postal as code_sucursal",
                    "code_postal.codigo_postal as code_postal",
                    "esys_sucursal.direccion as direccion_sucursal",
                    "esys.referencia",
                    "envio_detalle.is_reenvio",
                    "peso_mx as peso_unitario_mx",
                    "envio_detalle.cantidad as cantidad_paquete",
                    "(SELECT count(*) FROM viaje_detalle as viaje_detalle inner join envio_detalle on viaje_detalle.paquete_id = envio_detalle.id where envio_detalle.envio_id = envio.id  and viaje_detalle.viaje_id = viaje.id ) as paquetes_trailer",
                    "envio.id as envio_id",
                    "concat_ws(' ',trim(`receptor`.`nombre`),trim(`receptor`.`apellidos`)) AS `nombre_receptor`",
                    "receptor.telefono_movil AS `telefono_movil`",
                    "receptor.telefono AS `telefono`",
                    "envio.peso_total",
                    "(select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.status = 10 and env_detalle.envio_id = envio.id) as cantidad",
                    '(select mv.tipo_movimiento from movimiento_paquete mv where mv.tracked = viaje_detalle.tracked order by id desc limit 1 ) as tipo_movimiento_top',
                    "envio_detalle.costo_seguro",
                    "envio_detalle.observaciones",
                ])
                ->from('viaje')
                ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                ->innerJoin('envio_detalle','viaje_detalle.paquete_id = envio_detalle.id')

                ->leftJoin('esys_direccion esys','envio_detalle.id = esys.cuenta_id and esys.cuenta = 5')
                ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
                ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
                ->leftJoin('esys_direccion_codigo_postal code_postal','esys.codigo_postal_id = code_postal.id')

                ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                ->leftJoin('sucursal sucursal_receptor', 'envio_detalle.sucursal_receptor_id = sucursal_receptor.id')

                ->leftJoin('esys_direccion esys_sucursal','sucursal_receptor.id = esys_sucursal.cuenta_id and esys_sucursal.cuenta = 3')
                ->leftJoin('esys_lista_desplegable estado_sucursal','esys_sucursal.estado_id = estado_sucursal.id_2 and estado_sucursal.label = "crm_estado" ')
                ->leftJoin('esys_lista_desplegable municipio_sucursal','esys_sucursal.municipio_id = municipio_sucursal.id_2 and  estado_sucursal.id  = municipio_sucursal.param1 and municipio_sucursal.label = "crm_municipio"')
                ->leftJoin('esys_direccion_codigo_postal','esys_sucursal.codigo_postal_id = esys_direccion_codigo_postal.id')

                ->leftJoin('cliente receptor','envio_detalle.cliente_receptor_id = receptor.id')
                ->leftJoin('sucursal', 'envio.sucursal_emisor_id = sucursal.id')

                ->orderBy('sucursal.id desc, viaje_detalle.tracked asc');


        $query->andWhere(["viaje.id" => $viaje_id]);


        if ($sucursal_envia) {
            $query->andWhere(["sucursal.id" => $sucursal_envia ]);
        }

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return $query->all();
    }

}
