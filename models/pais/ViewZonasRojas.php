<?php

namespace app\models\pais;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_zonas_rojas".
 *
 * @property int $zona_id
 * @property string $code nombre
 * @property string|null $estado estado
 * @property int $pais_id pais_id
 * @property string $pais_nombre nombre
 * @property string|null $codigo_iso CODIGO ISO
 * @property string|null $pais_imagen imagen
 */
class ViewZonasRojas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_zonas_rojas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'pais_id'], 'integer'],
            [['code', 'pais_id', 'pais_nombre'], 'required'],
            [['code'], 'string', 'max' => 50],
            [['estado'], 'string', 'max' => 200],
            [['pais_nombre'], 'string', 'max' => 100],
            [['codigo_iso'], 'string', 'max' => 10],
            [['pais_imagen'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Zona ID',
            'code' => 'Code',
            'estado' => 'Estado',
            'pais_id' => 'Pais ID',
            'pais_nombre' => 'Pais Nombre',
            'codigo_iso' => 'Codigo Iso',
            'pais_imagen' => 'Pais Imagen',
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
                'code',
                'estado',
                'pais_id',
                'pais_nombre',
                #'nota',
                #'created_at',
                #'created_by',
                #'created_by_user',
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



        if (isset($filters['pais_id']) && $filters['pais_id'])
            $query->andWhere(['pais_id' =>  $filters['pais_id']]);

        if ($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
                ['like', 'code', $search],
                ['like', 'pais_nombre', $search],
            ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
