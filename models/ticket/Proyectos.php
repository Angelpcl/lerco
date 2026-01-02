<?php

namespace app\models\ticket;
use yii\db\Query;
use yii\web\Response;

use Yii;

/**
 * This is the model class for table "proyectos".
 *
 * @property int $id
 * @property string|null $nombre
 * @property int|null $encargado
 * @property string|null $descripcion
 * @property int|null $created_by
 * @property int|null $created_at
 * @property int|null $updated_by
 * @property int|null $updated_at
 */
class Proyectos extends \yii\db\ActiveRecord
{

   
    //public $productos = [];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'proyectos';
    }

    public function init()
    {
        parent::init();
        // Si el modelo no tiene productos seleccionados, se inicializa como un array vacío
        if ($this->productos === null) {
            $this->productos = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['encargado', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['descripcion'], 'string'],
            [['productos'], 'safe'],
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
            'encargado' => 'Encargado',
            'descripcion' => 'Descripcion',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getCheckboxList()
    {
        // Obtener todos los registros de la tabla asociada al modelo
        return self::find()->select(['id', 'nombre'])->all();
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
                        'encargado',
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

    public static function getProyectosList()
    {
        // Obtener los proyectos de la base de datos
        $proyectos = self::find()
            ->select(['id', 'nombre'])  // Seleccionamos el id y nombre
            ->asArray()  // Obtén los resultados como un array
            ->all();  // Recupera todos los proyectos
        
        // Transformar el array para que el ID sea la clave y el nombre sea el valor
        $proyectosList = [];
        foreach ($proyectos as $proyecto) {
            $proyectosList[$proyecto['id']] = $proyecto['nombre'];
        }

        return $proyectosList;
    }

    public static function getId($id)
    {
        // Obtener los proyectos de la base de datos
        $id = self::find()
            ->select(['id'])  // Seleccionamos el id y nombre
            ->where(['id' => $id])  // Obtén los resultados como un array
            ->one();  // Recupera todos los proyectos

        return $id;
    }

    
}
