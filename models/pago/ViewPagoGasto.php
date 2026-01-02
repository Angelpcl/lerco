<?php
namespace app\models\pago;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_egreso".
 *
 * @property int $id ID
 * @property int $concepto_id Concepto
 * @property string $concepto Singular
 * @property int $fecha_pago Fecha pago / gasto
 * @property double $monto Monto
 * @property string $nota Nota
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string $created_by_user
 */
class ViewPagoGasto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_pago_gasto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'concepto_id', 'fecha_pago', 'created_at', 'created_by'], 'integer'],
            [['concepto_id', 'fecha_pago', 'monto', 'created_at', 'created_by'], 'required'],
            [['monto'], 'number'],
            [['nota'], 'string'],
            [['concepto'], 'string', 'max' => 128],
            [['created_by_user'], 'string', 'max' => 201],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'concepto_id' => 'Concepto ID',
            'concepto' => 'Concepto',
            'fecha_pago' => 'Fecha Pago',
            'monto' => 'Monto',
            'nota' => 'Nota',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'created_by_user' => 'Created By User',
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
                        'concepto_id',
                        'concepto',
                        'monto',
                        'fecha_pago',
                        'nota',
                        'created_at',
                        'created_by',
                        'created_by_user',
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime($filters['from_date']);
                $date_fin = strtotime($filters['to_date']);
                $query->andWhere(['between','fecha_pago', $date_ini, $date_fin]);
            }



            if (isset($filters['concepto_id']) && $filters['concepto_id'])
                $query->andWhere(['concepto_id' =>  $filters['concepto_id']]);

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'concepto', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
