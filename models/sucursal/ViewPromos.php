<?php

namespace app\models\sucursal;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_promos".
 *
 * @property int $id
 * @property string $fecha_inicio fecha de inicio
 * @property string $fecha_fin fecha de termino
 * @property int $status status
 * @property string|null $nombre Nombre
 * @property string $sucursal_nombre
 * @property int $created_at Creado
 * @property int|null $created_by Creado por
 * @property int|null $updated_at Modificado
 */
class ViewPromos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_promos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'created_by', 'updated_at'], 'integer'],
            [['fecha_inicio', 'fecha_fin'], 'safe'],
            [['status', 'sucursal_nombre', 'created_at'], 'required'],
            [['nombre'], 'string', 'max' => 100],
            [['sucursal_nombre'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha_inicio' => 'Fecha Inicio',
            'fecha_fin' => 'Fecha Fin',
            'status' => 'Status',
            'nombre' => 'Nombre',
            'sucursal_nombre' => 'Sucursal Nombre',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
        ];
    }


    public static function getPromosBySuc($id_su)
    {
        $model = Promociones::find()
            ->where(['status' => Promociones::STATUS_ACTIVE])
            ->andWhere(['sucursal_id' => $id_su])
            ->one();

        return [
            'existPromo' => !$model ? false : true,
            'code' => !$model ? 10 : 202,
            'model' => $model,
        ];
    }


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

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                //"SQL_CALC_FOUND_ROWS. `id`",
                'id',
                'fecha_inicio',
                'fecha_fin',
                'status',
                'nombre',
                'sucursal_nombre',
                'created_at',
                'created_by',
                //'created_by_user',
            ])
            ->from(self::tableName())
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/

        if (isset($filters['date_range']) && $filters['date_range']) {
            $date_ini = strtotime($filters['from_date']);
            $date_fin = strtotime($filters['to_date']);
            $query->andWhere(['between', 'fecha_pago', $date_ini, $date_fin]);
        }



        //if (isset($filters['pais_id']) && $filters['pais_id'])
        //    $query->andWhere(['pais_id' =>  $filters['pais_id']]);

        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
                ['like', 'nombre', $search],
                ['like', 'sucursal_nombre', $search],
            ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
