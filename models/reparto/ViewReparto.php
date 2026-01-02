<?php
namespace app\models\reparto;


use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\user\User;
use app\models\envio\EnvioDetalle;
use app\models\movimiento\MovimientoPaquete;



/**
 * This is the model class for table "view_reparto".
 *
 * @property int $id id
 * @property int $fecha_salida
 * @property int $num_camion_id Numero de camion
 * @property string $unidad_nombre Singular
 * @property int $chofer_id
 * @property string $chofer Singular
 * @property int $status
 * @property string $nota Nota / Comentarios
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewReparto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_reparto';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha_salida' => 'Fecha Salida',
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
                        'chofer',
                        'unidad',
                        'status',
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
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);

            if (isset($filters['chofer_id']) && $filters['chofer_id'])
                $query->andWhere(['chofer_id' =>  $filters['chofer_id']]);

            if (isset($filters['unidad_id']) && $filters['unidad_id'])
                $query->andWhere(['num_unidad_id' =>  $filters['unidad_id']]);

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
    public static function setRepartoAddPaqueteAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $tracked    = isset($arr["tracked"]) ? $arr["tracked"] : null;

        if ($tracked && $arr["reparto_id"]) {
            $tracked_get    = trim($tracked);
            $tracked_get    = explode('/', $tracked_get);
            $clave          = explode("-",$tracked_get[0]);
            if (isset($tracked_get[1]) &&  $tracked_get[1] ) {

                $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0] ])->one();
                if ($model) {
                    if($model->cantidad >= intval($tracked_get[1]) && intval($tracked_get[1]) != 0){
                        $RepartoDetalle = new  RepartoDetalle();
                        $RepartoDetalle->reparto_id     = $arr["reparto_id"];
                        $RepartoDetalle->tracked        = $arr["tracked"];
                        //$RepartoDetalle->peso_reparto   = isset($arr["peso"]) ? $arr["peso"]: null;
                        $RepartoDetalle->paquete_id  = $model->id;

                        $MovimientoPaquete = new MovimientoPaquete();
                        $MovimientoPaquete->paquete_id      = $model->id;
                        $MovimientoPaquete->tracked         = isset($arr["tracked"]) ? $arr["tracked"] : null;
                        $MovimientoPaquete->reparto_id      = $RepartoDetalle->reparto_id;
                        $MovimientoPaquete->tipo_envio      = $model->envio->tipo_envio;
                        $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_REPARTO;
                        $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                        if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
                            if ($RepartoDetalle->save() && $MovimientoPaquete->save()) {
                                    return [
                                    "code" => 202,
                                    "name" => "Reparto",
                                    "message" => 'Se ingreso corrctamente el paquete al reparto.',
                                    "type" => "Success",
                                ];
                            }else{
                                return [
                                    "code" => 10,
                                    "name" => "Reparto",
                                    "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
                                    "type" => "Warning",
                                ];
                            }
                        }else{
                            return [
                                "code" => 10,
                                "name" => "Reparto",
                                "message" => "Aviso, No se realizo el movimiento ya que el paquete se encuentra en ese movimiento",
                                "type" => "Warning",
                            ];
                        }

                    }else{
                        return [
                            "code" => 10,
                            "name" => "Reparto",
                            "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
                            "type" => "Warning",
                        ];
                    }

                }else{
                    return [
                        "code" => 10,
                        "name" => "Reparto",
                        "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
                        "type" => "Warning",
                    ];
                }
            }else{
                return [
                    "code" => 10,
                    "name" => "Reparto",
                    "message" => 'Debes ingresar correctamente el tracked.',
                    "type" => "Warning",
                ];
            }
        }
        return [
            "code"    => 10,
            "name"    => "Reparto",
            "message" => 'El tracked y el reparto son requeridos',
            "type"    => "Error",
        ];

    }

    public static function getReporteRepartoRutas($arr)
    {

        $query = (new Query())
                ->select([
                    "reparto.id as reparto_id",
                    "chofer.singular as chofer",
                    "num_unidad.singular as num_unidad",
                    "reparto.created_at",
                    "ruta.id",
                    "ruta.nombre as ruta_nombre",
                ])
                ->from('reparto')
                ->leftJoin('esys_lista_desplegable chofer','reparto.chofer_id = chofer.id')
                ->leftJoin('esys_lista_desplegable num_unidad','reparto.num_unidad_id = num_unidad.id')
                ->innerJoin('reparto_detalle','reparto.id = reparto_detalle.reparto_id')
                ->innerJoin('envio_detalle','reparto_detalle.paquete_id = envio_detalle.id')
                ->leftJoin('ruta_sucursal' ,'envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
                ->leftJoin('sucursal', 'ruta_sucursal.sucursal_id = sucursal.id')
                ->leftJoin('ruta' ,'ruta_sucursal.ruta_id = ruta.id')
                ->orderBy('ruta.orden,sucursal.id desc')
                ->groupBy('ruta.id');

        if (isset($arr['reparto_id']) && $arr['reparto_id'] ) {
            $query->andWhere(["reparto.id" => $arr['reparto_id']]);
        }

        /*Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "reparto.id",
                    "reparto_detalle.tracked",
                    "sucursal.nombre as sucursal_nombre",
                    "ruta.nombre as ruta_nombre",
                    "envio_detalle.peso",
                    "envio.peso_total",
                    "(select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id) as cantidad",
                    "envio_detalle.observaciones",
                ])
                ->from('reparto')
                ->innerJoin('reparto_detalle','reparto.id = reparto_detalle.reparto_id')
                ->innerJoin('envio_detalle','reparto_detalle.paquete_id = envio_detalle.id')
                ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                ->leftJoin('ruta_sucursal' ,'envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
                ->leftJoin('sucursal', 'ruta_sucursal.sucursal_id = sucursal.id')
                ->leftJoin('ruta' ,'ruta_sucursal.ruta_id = ruta.id')
                ->orderBy('ruta.orden,sucursal.id desc');

        if (isset($arr['reparto_id']) && $arr['reparto_id'] ) {
            $query->andWhere(["reparto.id" => $arr['reparto_id']]);
        }*/

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return $query->all();
    }
    public static function getReporteRepartoSucursal($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "sucursal.id as sucursal_id",
                    "sucursal.nombre as sucursal_nombre",
                ])
                ->from('reparto')
                ->innerJoin('reparto_detalle','reparto.id = reparto_detalle.reparto_id')
                ->innerJoin('envio_detalle','reparto_detalle.paquete_id = envio_detalle.id')
                ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                ->leftJoin('ruta_sucursal' ,'envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
                ->leftJoin('sucursal', 'ruta_sucursal.sucursal_id = sucursal.id')
                ->groupBy('sucursal_id');

        if (isset($arr['reparto_id']) && $arr['reparto_id'] )
            $query->andWhere(["reparto.id" => $arr['reparto_id']]);

        if (isset($arr['ruta_id']) && $arr['ruta_id'] )
            $query->andWhere(["ruta_sucursal.ruta_id" => $arr['ruta_id']]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return $query->all();

    }
    public static function getReporteRepartoSucursalPaquete($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "reparto_detalle.tracked",
                    "envio_detalle.peso",
                    "(select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id) as cantidad",
                ])
                ->from('reparto')
                ->innerJoin('reparto_detalle','reparto.id = reparto_detalle.reparto_id')
                ->innerJoin('envio_detalle','reparto_detalle.paquete_id = envio_detalle.id')
                ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                ->leftJoin('ruta_sucursal' ,'envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
                ->leftJoin('sucursal', 'ruta_sucursal.sucursal_id = sucursal.id')
                ->orderBy('sucursal.id desc');

        if (isset($arr['reparto_id']) && $arr['reparto_id'] )
            $query->andWhere(["reparto.id" => $arr['reparto_id']]);

        if (isset($arr['ruta_id']) && $arr['ruta_id'] )
            $query->andWhere(["ruta_sucursal.ruta_id" => $arr['ruta_id']]);

         if (isset($arr['sucursal_id']) && $arr['sucursal_id'] )
            $query->andWhere(["sucursal.id" => $arr['sucursal_id']]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->all();

    }


    public static function getReporteDetalleRuta($reparto_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "reparto_detalle.tracked",
                    "envio_detalle.envio_id as envio_id",
                    "esys.direccion",
                    "estado.singular as estado",
                    "municipio.singular as municipio",
                    "code_postal.codigo_postal as code_postal",
                    'concat_ws(" ",`cliente_receptor`.`nombre`,`cliente_receptor`.`apellidos`) AS `nombre_receptor`',
                    "cliente_receptor.telefono_movil AS `telefono_movil`",
                    "cliente_receptor.telefono AS `telefono`",
                    "envio_detalle.cliente_receptor_id",

                ])
                ->from('reparto_detalle')
                ->innerJoin('envio_detalle','reparto_detalle.paquete_id = envio_detalle.id')
                ->innerJoin('cliente cliente_receptor','envio_detalle.cliente_receptor_id = cliente_receptor.id')
                ->leftJoin('esys_direccion esys','envio_detalle.id = esys.cuenta_id and esys.cuenta = 5')
                ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
                ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
                ->leftJoin('esys_direccion_codigo_postal code_postal','esys.codigo_postal_id = code_postal.id')
                ->andWhere(["reparto_detalle.reparto_id" => $reparto_id ]);


        return $query->all();

    }
}

