<?php

namespace app\models\pais;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "paises_latam".
 *
 * @property int $id
 * @property string $nombre nombre
 * @property string|null $codigo_iso CODIGO ISO
 * @property int|null $created_at created_at
 * @property int|null $created_by created_by
 * @property int|null $updated_at updated_at
 * @property int|null $updated_by updated_by
 */
class PaisesLatam extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'paises_latam';
    }

    /**
     * {@inheritdoc}
     */

    public $imagen_bandera;
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['nombre', 'imagen'], 'string', 'max' => 100],
            [['codigo_iso'], 'string', 'max' => 10],
            [['imagen_bandera'], 'file', 'extensions' => 'png, jpg, jpeg'],
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
            'codigo_iso' => 'Código',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }



    public function upload()
    {
        // Asegúrate de que el directorio de destino exista
        $directory = 'uploads/flags/';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true); // Crea el directorio si no existe
        }

        // Verifica si se ha cargado un archivo
        if ($this->imagen_bandera) {
            // Construye la ruta completa con el nombre del archivo
            $filePath = $directory . "/" . $this->imagen;

            // Mueve el archivo cargado a la ruta de destino
            if ($this->imagen_bandera->saveAs($filePath)) {
                return true;
            } else {
            }
        } else {
        }

        return false;
    }


    public static function getPaisName($id)
    {
        $model = self::findOne($id);
        if ($model) {
            return $model->nombre . " - " . $model->codigo_iso;
        }
    }

    public  function getNombreCompleto()
    {


        return $this->nombre . " - " . $this->codigo_iso;
    }

    public static function getPaises()
    {
        $models = self::find()->all();
        $response = [];
        foreach ($models as $model) {
            $response[$model->id] = $model->nombre . " - " . $model->codigo_iso;
        }
        return $response;
    }




    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            } else {
                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            }
            if ($this->imagen_bandera) {
                $this->imagen = Yii::$app->security->generateRandomString() . '.' . $this->imagen_bandera->extension;
                $this->upload();
            }
            return true;
        } else
            return false;
    }

    public static function getJsonBtt($arr)
    {
        // La respuesta será en formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort']) ? $arr['sort'] : 'id';
        $order   = isset($arr['order']) ? $arr['order'] : 'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset']) ? $arr['offset'] : 0;
        $limit   = isset($arr['limit']) ? $arr['limit'] : 50;

        $search = isset($arr['search']) ? $arr['search'] : false;
        parse_str($arr['filters'], $filters);

        /************************************
    / Preparamos la consulta
    /***********************************/
        $query = (new Query())
            ->select([
                't.id',
                't.nombre',
                't.created_at',
                't.imagen',
                'u.username AS created_by_name',  // Suponiendo que 'username' es el campo en la tabla user
            ])
            ->from(self::tableName() . ' t')
            ->leftJoin('user u', 't.created_by = u.id') // Ajusta 'user' y 'id' si es necesario
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);

        /************************************
    / Filtramos la consulta
    /***********************************/
        if ($search) {
            $query->andFilterWhere([
                'or',
                ['like', 't.id', $search],
                ['like', 't.concepto', $search],
            ]);
        }

        // Ejecutar la consulta para obtener los resultados
        $rows = $query->all();

        // Obtener el total de filas sin LIMIT
        $totalQuery = (new Query())
            ->select('COUNT(*)')
            ->from(self::tableName() . ' t')
            ->leftJoin('user u', 't.created_by = u.id');

        if ($search) {
            $totalQuery->andFilterWhere([
                'or',
                ['like', 't.id', $search],
                ['like', 't.concepto', $search],
            ]);
        }

        $total = $totalQuery->scalar();

        return [
            'rows'  => $rows,
            'total' => $total,
        ];
    }
}
