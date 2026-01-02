<?php

namespace app\models\mapeo;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\envio\EnvioDetalle;
/**
 * This is the model class for table "view_mapeo".
 *
 * @property int $id ID
 * @property int $fecha_expired Fecha expiración
 * @property int $status Estatus
 * @property int $created_at Creado por
 * @property int $created_by Creado por
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewMapeo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_mapeo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'fecha_expired', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['status', 'created_at', 'created_by'], 'required'],
            [['created_by_user', 'updated_by_user'], 'string', 'max' => 201],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha_expired' => 'Fecha Expired',
            'status' => 'Status',
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
                        'nombre',
                        'fecha_expired',
                        'status',
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

    public static function getRutaPaquete($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "viaje.id",
                    "viaje_detalle.paquete_id",
                    "viaje_detalle.tracked",
                    "viaje_detalle.tipo",
                    "esys.estado_id as estado_id",
                    "esys.municipio_id as municipio_id",
                    "estado.singular as estado",
                    "municipio.singular as municipio",
                    "envio_detalle.sucursal_receptor_id",
                    "ruta_sucursal.ruta_id",
                    new \yii\db\Expression("20 as origen"),
                    "ruta.nombre"
                ])
                ->from('viaje')
                ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                ->innerJoin('envio_detalle','viaje_detalle.paquete_id = envio_detalle.id')
                ->leftJoin('esys_direccion esys','envio_detalle.id = esys.cuenta_id and esys.cuenta = 5')
                ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')
                ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
                ->leftJoin('ruta_sucursal' ,'envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
                ->leftJoin('ruta' ,'ruta_sucursal.ruta_id = ruta.id')
                //->andWhere(['envio_detalle.status' => EnvioDetalle::STATUS_HABILITADO ])
                ->orderBy('ruta.orden, envio_detalle.envio_id, viaje_detalle.tracked asc');

        if (isset($arr['viajes_id']) && count($arr['viajes_id']) > 0 ) {
            $viajes_id = [];

            foreach ($arr['viajes_id'] as $key => $item) {
                array_push($viajes_id, $item);
            }
            $query->orWhere(["viaje.id" => $viajes_id]);
        }

        $query->andWhere(['envio_detalle.bodega_descarga' =>  Yii::$app->user->identity->bodega_descarga_asignado ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return $query->all();
    }

    public static function getReporteDescarga($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "mapeo.id",
                    "mapeo_detalle.tracked",
                    "mapeo_detalle.paquete_id",
                    "esys_lista_desplegable.singular as fila",
                    /*"case envio.tipo_envio
                            when '10' then CONCAT_WS(' ','','[TIERRA]')
                            when '20' then  CONCAT_WS(' ','','[LAX]')
                            when '30' then  CONCAT_WS(' ','','[MEX]')
                    END AS tipo_servicio",*/
                    "envio_detalle.observaciones",
                ])
                ->from('mapeo')
                ->innerJoin('mapeo_detalle','mapeo.id = mapeo_detalle.mapeo_id')
                ->innerJoin('envio_detalle','mapeo_detalle.paquete_id = envio_detalle.id')
                ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                ->innerJoin('esys_lista_desplegable','mapeo_detalle.fila_id = esys_lista_desplegable.id')
                //->andWhere(['envio_detalle.status' => EnvioDetalle::STATUS_HABILITADO ])
                ->orderBy('envio_detalle.envio_id desc');

        if (isset($arr['mapeo_id']) && $arr['mapeo_id'] ) {
            $query->orWhere(["mapeo.id" => $arr['mapeo_id']]);
        }

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return $query->all();
    }

    public static function getReporteCargaUnidades($arr, $ruta_id = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    "mapeo.id",
                    "mapeo_detalle.tracked",
                    "sucursal.id as sucursal_id",
                    "sucursal.nombre as sucursal_nombre",
                    "mapeo_detalle.paquete_id",
                    "esys_lista_desplegable.singular as fila",
                    "ruta.nombre as ruta_nombre",
                    "ruta.id as ruta_id",
                    "envio_detalle.peso",
                    "esys.direccion",
                    "estado.singular as estado",
                    "municipio.singular as municipio",
                    "esys.referencia",
                    "envio_detalle.is_reenvio",
                    "(SELECT peso_mx FROM viaje_detalle where viaje_detalle.viaje_id = mapeo_detalle.viaje_id and tipo = 10 and viaje_detalle.paquete_id = envio_detalle.id  and viaje_detalle.tracked = mapeo_detalle.tracked limit 1 ) as peso_unitario_mx",
                    "envio_detalle.cantidad as cantidad_paquete",
                    "envio.id as envio_id",
                    "concat_ws(' ',trim(`receptor`.`nombre`),trim(`receptor`.`apellidos`)) AS `nombre_receptor`",
                    "receptor.telefono_movil AS `telefono_movil`",
                    "(SELECT count(*) FROM mapeo_detalle as env_mapeo inner join envio_detalle on env_mapeo.paquete_id = envio_detalle.id where envio_detalle.envio_id = envio.id  and env_mapeo.mapeo_id = mapeo.id ) as paquetes_envio_mapeo",

                    "envio.peso_total",
                    "(select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.status = 10 and env_detalle.envio_id = envio.id) as cantidad",
                    "envio_detalle.observaciones",
                ])
                ->from('mapeo')
                ->innerJoin('mapeo_detalle','mapeo.id = mapeo_detalle.mapeo_id')
                ->innerJoin('envio_detalle','mapeo_detalle.paquete_id = envio_detalle.id')
                ->leftJoin('esys_direccion esys','envio_detalle.id = esys.cuenta_id and esys.cuenta = 5')

                ->leftJoin('esys_lista_desplegable estado','esys.estado_id = estado.id_2 and estado.label = "crm_estado" ')

                ->leftJoin('esys_lista_desplegable municipio','esys.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')

                ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                ->leftJoin('cliente receptor','envio_detalle.cliente_receptor_id = receptor.id')
                ->leftJoin('ruta_sucursal' ,'envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
                ->leftJoin('sucursal', 'ruta_sucursal.sucursal_id = sucursal.id')
                ->leftJoin('ruta' ,'ruta_sucursal.ruta_id = ruta.id')
                ->innerJoin('esys_lista_desplegable','mapeo_detalle.fila_id = esys_lista_desplegable.id')
                ->orderBy('ruta.orden,sucursal.id desc, mapeo_detalle.tracked asc');

        if (isset($arr['mapeo_id']) && $arr['mapeo_id'] ) {
            $query->orWhere(["mapeo.id" => $arr['mapeo_id']]);
        }

        if ($ruta_id) {
            $query->andWhere(["ruta.id" => $ruta_id ]);
        }

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return $query->all();
    }

    public static function getReportePlaneacion($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
        ->select([
            'count(*) as mapeo_count',
            "sucursal.id as sucursal_id",
            "sucursal.nombre as sucursal_nombre",
            "ruta.nombre as ruta_nombre",
            "ruta.id as ruta_id",
            "envio_detalle.cantidad as cantidad_paquete",
            "envio.id as envio_id",
            "(SELECT count(*) FROM mapeo_detalle as env_mapeo inner join envio_detalle on env_mapeo.paquete_id = envio_detalle.id where envio_detalle.envio_id = envio.id  and env_mapeo.mapeo_id = mapeo.id ) as paquetes_envio_mapeo",
            "(select sum(env_detalle.cantidad) from envio_detalle env_detalle where env_detalle.envio_id = envio.id) as cantidad",
        ])
        ->from('mapeo')
        ->innerJoin('mapeo_detalle','mapeo.id = mapeo_detalle.mapeo_id')
        ->innerJoin('envio_detalle','mapeo_detalle.paquete_id = envio_detalle.id')
        ->innerJoin('envio','envio_detalle.envio_id = envio.id')
        ->leftJoin('ruta_sucursal' ,'envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
        ->leftJoin('sucursal', 'ruta_sucursal.sucursal_id = sucursal.id')
        ->leftJoin('ruta' ,'ruta_sucursal.ruta_id = ruta.id')
        ->orderBy('ruta.orden,sucursal.id desc')
        ->groupBy('sucursal.id');

        if (isset($arr['mapeo_id']) && $arr['mapeo_id'] ) {
            $query->orWhere(["mapeo.id" => $arr['mapeo_id']]);
        }

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();
        return $query->all();
    }


    

}
