<?php

namespace app\models\ticket;
use yii\db\Query;
use yii\web\Response;

use Yii;

/**
 * This is the model class for table "cliente_razon_social".
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property string|null $proyectos
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class ClienteRazonSocial extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente_razon_social';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['descripcion', 'proyectos','productos'], 'string'],
            [['created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['nombre'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            'proyectos' => 'Proyectos',
            'productos' => 'Productos',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

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
                        'descripcion',
                     
                        
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
          

    


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
            $row = [];
            foreach ($query->all() as $key => $item) {
                array_push($row, $item);
            }

        return [
            'rows'  => $row,
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getClientesList()
    {
        // Obtener los clientes de la base de datos
        $clientes = self::find()
            ->select(['id', 'nombre'])  // Seleccionamos el id y nombre
            ->asArray()  // Obtén los resultados como un array
            ->all();  // Recupera todos los clientes
        
        // Transformar el array para que el ID sea la clave y el nombre sea el valor
        $clientesList = [];
        foreach ($clientes as $proyecto) {
            $clientesList[$proyecto['id']] = $proyecto['nombre'];
        }

        return $clientesList;
    }

    public static function getId($nombre)
    {
        // Obtener los proyectos de la base de datos
        $id = self::find()
            ->select(['id'])  // Seleccionamos el id y nombre
            ->where(['nombre' => $nombre])  // Obtén los resultados como un array
            ->one();  // Recupera todos los proyectos

        return $id;
    }
    
     public static function getNombre($id)
    {
        // Obtener los proyectos de la base de datos
        $query = self::find()
            ->select(['nombre'])  // Seleccionamos el id y nombre
            ->where(['id' => $id])  // Obtén los resultados como un array
            ->one();  // Recupera todos los proyectos

        return $query;
    }

}
