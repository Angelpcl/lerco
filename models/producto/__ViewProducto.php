<?php

namespace app\models\producto;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\producto\Producto;
use app\models\envio\Envio;
/**
 * This is the model class for table "view_producto".
 *
 * @property int $id Id
 * @property string $nombre Nombre
 * @property int $tipo_servicio Tipo de servicio
 * @property string $nota Nota
 * @property int $status Estatus
 * @property string $unidad_medida Singular
 * @property string $categoria Singular
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewProducto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_producto';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'tipo_servicio' => 'Tipo Servicio',
            'nota' => 'Nota',
            'status' => 'Status',
            'unidad_medida' => 'Unidad Medida',
            'categoria' => 'Categoria',
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
                    'tipo_servicio',
                    'nota',
                    'status',
                    'unidad_medida',
                    'categoria',
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
            if (isset($filters['tipo_servicio']) && $filters['tipo_servicio'])
                $query->andWhere(['tipo_servicio' =>  $filters['tipo_servicio']]);

            if (isset($filters['categoria_id']) && $filters['categoria_id'])
                $query->andWhere(['categoria_id' =>  $filters['categoria_id']]);


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'nombre', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getProductoSeachAjax($q,$lax_tierra = false,$mex = false)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "`id`",
                "case tipo_servicio
                    when '10' then  CONCAT_WS(' ', `nombre`,'[TIERRA]')
                    when '20' then  CONCAT_WS(' ', `nombre`,'[LAX]')
                    when '30' then  CONCAT_WS(' ', `nombre`,'[MEX]')
                END AS `text`",
                "categoria_id",
                'tipo_servicio',
                'nota',
                'status',
                'categoria',
                'created_at',
                'created_by',
                'created_by_user',
                'updated_at',
                'updated_by',
                'updated_by_user',

            ])
            ->from(self::tableName())
            ->orderBy('id desc')
            ->limit(50);

            $query->andWhere(['like', 'nombre', $q]);

            if ($lax_tierra)
                $query->andWhere(['or',
                    ['tipo_servicio' => Envio::TIPO_ENVIO_TIERRA],
                    ['tipo_servicio' => Envio::TIPO_ENVIO_LAX]
                ]);
            elseif ($mex)
                $query->andWhere(['tipo_servicio' => Envio::TIPO_ENVIO_MEX ]);

        return $query->all();
    }


    public static function getProductoAllJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    "SQL_CALC_FOUND_ROWS `id`",
                    'nombre',
                    'tipo_servicio',
                    'nota',
                    'status',
                    'unidad_medida',
                    'categoria',
                    'created_at',
                    'created_by',
                    'created_by_user',
                    'updated_at',
                    'updated_by',
                    'updated_by_user',
                ])
                ->from(self::tableName())
                ->orderBy('nombre desc');



        /************************************
        / Filtramos la consulta
        /***********************************/
            if (isset($filters['tipo_servicio']) && $filters['tipo_servicio'])
                $query->andWhere(['tipo_servicio' =>  $filters['tipo_servicio']]);

            if (isset($filters['categoria_id']) && $filters['categoria_id'])
                $query->andWhere(['categoria_id' =>  $filters['categoria_id']]);



        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    /*public static function getProductoDetalleAjax($arr){
        Yii::$app->response->format = Response::FORMAT_JSON;


        $query = (new Query())
            ->select([
             "producto.id",
             "producto.tipo_servicio",
             "producto_detalle.id as producto_detalle_id",
             "producto_detalle.tipo_volumen_id",
             "tipo_volumen.singular as tipo_volumen_text",
             "producto_detalle.required_min",
             "producto_detalle.tipo_valor",
             "producto_detalle.costo_extra",
             "producto_detalle.impuesto",
             "producto_detalle.intervalo",
             "producto_detalle.nota",
             "producto_detalle.status",

            ])
            ->from(Producto::tableName())
            ->innerJoin("producto_detalle","producto.id = producto_detalle.producto_id")
            ->leftJoin("esys_lista_desplegable as tipo_volumen","producto_detalle.tipo_volumen_id = tipo_volumen.id")
            ->andWhere(['producto.id' =>  $arr['producto']])
            ->orderBy("producto_detalle_id asc");

        return [
            'rows'  => $query->all()
        ];
    }*/
}
