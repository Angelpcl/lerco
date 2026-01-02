<?php
namespace app\models\reparto;


use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\ruta\Ruta;
use app\models\ruta\FilaRuta;
use app\models\user\User;
use app\models\viaje\Viaje;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\ruta\RutaSucursal;
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

    public function getAsignarRepartoRuta($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
                ->select([
                    'ruta.id',
                    'ruta.nombre',
                    'ruta.tipo',
                ])
            ->from(Ruta::tableName());
            //->leftJoin(FilaRuta::tableName(),'fila_ruta.ruta_id = ruta.id')
            //->leftJoin(RepartoFila::tableName(),'reparto_fila.id = fila_ruta.fila_id')
            //->leftJoin(Reparto::tableName(),'reparto.id =reparto_fila.reparto_id');

        /*$query->andWhere(['and',
                ['<>', 'reparto.status', Reparto::STATUS_ACTIVE ],
                ['<>', 'reparto.status', Reparto::STATUS_CERRADO ],


        ]);*/

        /*$query->orWhere(['or',
            ['IS', 'reparto.status', new \yii\db\Expression('null')],
        ]);*/


        /*$query->andWhere(['or',
            ['<>', 'reparto_fila.reparto_id', $arr['reparto_id']],
            ['IS', 'reparto_fila.reparto_id', new \yii\db\Expression('null')]
        ]);*/

        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();

        return $query->all();
    }
/*
    public function getSucursalRutaAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    'sucursal.id',
                    'sucursal.nombre',
                    'sucursal.telefono_movil',
                    'sucursal.telefono',
                    'sucursal.nombre',
                    'reparto_recoleccion.id  as recoleccion_id',
                    'reparto_recoleccion.cantidad_paquetes',
                    'concat_ws(" ",user.nombre, user.apellidos ) as nombre_completo',
                ])
            ->from(Sucursal::tableName())
            ->leftJoin(RutaSucursal::tableName(),'ruta_sucursal.sucursal_id = sucursal.id')
            ->leftJoin(RepartoRecoleccion::tableName(),'reparto_recoleccion.sucursal_id = sucursal.id')
            ->leftJoin(User::tableName(),'user.id = sucursal.encargado_id');

        if (isset($arr['ruta_id']) && $arr['ruta_id'])
            $query->andWhere(["ruta_sucursal.ruta_id" => $arr['ruta_id']]);

        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();

        return isset($arr['ruta_id']) && $arr['ruta_id'] ? $query->all() : [];
    }*/

    /*public function getSucursalPaqueteAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                      'envio_detalle.id',
                      '`viaje_detalle`.`tracked`',
                      '(select `fr`.`id`
                            from fila_paquete  fp
                            INNER JOIN `fila_ruta` `fr` ON fp.fila_ruta_id  = fr.id
                            where fp.tracked = `viaje_detalle`.`tracked` ) AS `fila_ruta_id`',
                      '(select `esys_lista_desplegable`.`singular`
                            from fila_paquete  fp
                            INNER JOIN `fila_ruta` `fr` ON fp.fila_ruta_id = fr.id
                            INNER JOIN `reparto_fila` `rf` ON fr.fila_id = rf.id
                            INNER JOIN `esys_lista_desplegable` ON rf.nombre_id = esys_lista_desplegable.id
                            where fp.tracked = `viaje_detalle`.`tracked` ) AS `nombre_fila`',
                      'if((select count(*)  from fila_paquete where tracked = `viaje_detalle`.`tracked`) = 0 , 1,10 ) as is_fila',
                      'envio_detalle.cantidad_piezas'
                ])
            ->from('viaje')
            ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
            ->innerJoin('envio_detalle','viaje_detalle.paquete_id = envio_detalle.id')
            ->innerJoin('ruta_sucursal','envio_detalle.sucursal_receptor_id = ruta_sucursal.sucursal_id')
            ->innerJoin('fila_ruta','ruta_sucursal.ruta_id =  fila_ruta.ruta_id')


            ->andWhere(['viaje.tipo_servicio' => Envio::TIPO_ENVIO_TIERRA])
            ->andWhere(['viaje.status' => Viaje::STATUS_CERRADO]);

        if (isset($arr['sucursal_receptor_id']) && $arr['sucursal_receptor_id'])
            $query->andWhere(["envio_detalle.sucursal_receptor_id" => $arr['sucursal_receptor_id']]);

        if (isset($arr['fila_ruta_id']) && $arr['fila_ruta_id'])
            $query->andWhere(["fila_ruta.id" => $arr['fila_ruta_id']]);

        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();

        return $query->all();
    }

    public function getReporteRepartoAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                    ->select([
                        'reparto_fila.id',
                        'esys_lista_desplegable.singular as fila',
                        'esys_chofer.singular as chofer',
                        'esys_unidad.singular as unidad',
                    ])
                    ->from('reparto')
                    ->innerJoin('reparto_fila','reparto.id = reparto_fila.reparto_id')
                    ->innerJoin('esys_lista_desplegable','reparto_fila.nombre_id = esys_lista_desplegable.id')
                    ->leftJoin('esys_lista_desplegable esys_chofer' ,'reparto_fila.chofer_id = esys_chofer.id')
                    ->leftJoin('esys_lista_desplegable esys_unidad' ,'reparto_fila.num_camion_id = esys_unidad.id')
                    ->andWhere(['reparto.id' => $arr['reparto_id']]);

        return $query->all();
    }

    public function getFilaPaqueteAjax($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                    ->select([
                        "ruta.nombre",
                        "esys_lista_desplegable.singular as fila",
                        "fila_paquete.tracked",
                        "envio_detalle.cantidad_piezas",
                    ])
                    ->from('reparto_fila')
                    ->innerJoin('fila_ruta','reparto_fila.id = fila_ruta.fila_id')
                    ->innerJoin('ruta','fila_ruta.ruta_id = ruta.id')
                    ->innerJoin('esys_lista_desplegable' ,'reparto_fila.nombre_id=esys_lista_desplegable.id')
                    ->innerJoin('fila_paquete' ,'fila_ruta.id = fila_paquete.fila_ruta_id')
                    ->innerJoin('envio_detalle' ,'fila_paquete.paquete_id = envio_detalle.id')
                    ->andWhere(['reparto_fila.id' => $arr['reparto_fila_id']]);
        return $query->all();
    }*/
}
