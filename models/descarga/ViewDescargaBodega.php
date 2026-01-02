<?php
namespace app\models\descarga;

use Yii;
use yii\db\Query;
use yii\web\Response;


/**
 * This is the model class for table "view_descarga_bodega".
 *
 * @property int $id Descarga bodega
 * @property int $estado_id Estado
 * @property string|null $municipio Singular
 * @property string|null $estado Singular
 * @property int $municipio_id Municipio
 * @property int $bodega_descarga Bodega descarga
 */
class ViewDescargaBodega extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_descarga_bodega';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'estado_id', 'municipio_id', 'bodega_descarga'], 'integer'],
            [['estado_id', 'municipio_id', 'bodega_descarga'], 'required'],
            [['municipio', 'estado'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'estado_id' => 'Estado ID',
            'municipio' => 'Municipio',
            'estado' => 'Estado',
            'municipio_id' => 'Municipio ID',
            'bodega_descarga' => 'Bodega Descarga',
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
                    'estado_id',
                    'municipio',
                    'estado',
                    'municipio_id',
                    'bodega_descarga',
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);

        if (isset($filters['bodega_id']) && $filters['bodega_id'])
            $query->andWhere(['bodega_descarga' =>  $filters['bodega_id']]);


        /************************************
        / Filtramos la consulta
        /***********************************/


        if($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
                ['like', 'municipio', $search],
                ['like', 'estado', $search],
            ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
