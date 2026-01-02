<?php
namespace app\models\aperturaCierreCaja;

use Yii;
use yii\db\Query;
use yii\web\Response;
/**
 * This is the model class for table "view_apertura_cierre_caja".
 *
 * @property int $id ID
 * @property int $fecha_apertura Fecha apertura
 * @property double $cantidad_apertura Cantidad apertura
 * @property int $fecha_cierre Fecha de cierre
 * @property double $cantidad_cierre Cantidad cierre
 * @property string $comentario_apertura Nota
 * @property string $comentario_cierre Comentario cierre
 * @property int $created_at Creado
 * @property string $created_by_user
 * @property string $updated_by_user
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 */
class ViewAperturaCierreCaja extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_apertura_cierre_caja';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha_apertura' => 'Fecha Apertura',
            'cantidad_apertura' => 'Cantidad Apertura',
            'fecha_cierre' => 'Fecha Cierre',
            'cantidad_cierre' => 'Cantidad Cierre',
            'comentario_apertura' => 'Comentario Apertura',
            'comentario_cierre' => 'Comentario Cierre',
            'created_at' => 'Created At',
            'created_by_user' => 'Created By User',
            'updated_by_user' => 'Updated By User',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
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
                        'fecha_apertura' ,
                        'cantidad_apertura' ,
                        'fecha_cierre' ,
                        'cantidad_cierre' ,
                        'comentario_apertura' ,
                        'comentario_cierre' ,
                        'created_at' ,
                        'created_by_user' ,
                        'updated_by_user' ,
                        'created_by' ,
                        'updated_at' ,
                        'updated_by' ,
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


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'fecha_apertura', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
