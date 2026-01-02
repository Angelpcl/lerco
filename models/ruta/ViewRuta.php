<?php

namespace app\models\ruta;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\sucursal\Sucursal;
use app\models\user\User;
/**
 * This is the model class for table "view_ruta".
 *
 * @property int $id ID
 * @property string $nombre Nombre
 * @property int $tipo
 * @property int $status Estatus
 * @property string $nota Nota
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewRuta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_ruta';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'tipo' => 'Tipo',
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
                        'nombre',
                        'tipo',
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

            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['tipo' =>  $filters['tipo']]);


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

    public static function getAsignarSucursalRuta($arr)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
                ->select([
                    'sucursal.id',
                    'sucursal.nombre',
                    'concat_ws(" ",user.nombre, user.apellidos ) as nombre_completo',
                ])
            ->from(Sucursal::tableName())
            ->leftJoin(RutaSucursal::tableName(),'ruta_sucursal.sucursal_id = sucursal.id')
            ->leftJoin(User::tableName(),'user.id = sucursal.encargado_id')
            ->leftJoin(Ruta::tableName(),'ruta.id =ruta_sucursal.ruta_id');

        $query->andWhere(['or',
                ['<>', 'ruta.tipo', 10 ],
                ['IS', 'ruta.tipo', new \yii\db\Expression('null')]
        ]);

        $query->andWhere(['or',
                ['<>', 'ruta.id', $arr['ruta_id']],
                ['IS', 'ruta.id', new \yii\db\Expression('null')]
        ]);

        $query->andWhere(['sucursal.origen' => Sucursal::ORIGEN_MX]);

        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();

        return $query->all();
    }
    public function getOrdenRuta($arr)
    {

        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                    'nombre',
                    'tipo',
                    'status',
                    'nota',
            ])
            ->from(self::tableName());


        if (isset($arr['tipo']) && $arr['tipo'] == Ruta::TIPO_BASE) {
            $query->andWhere(["tipo" => $arr["tipo"]]);
            $query->andWhere(["orden" => $arr["orden"]]);
        }

        return isset($arr['tipo']) && $arr['tipo'] == Ruta::TIPO_BASE ? $query->all() : [];

    }
}
