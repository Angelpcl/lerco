<?php

namespace app\models\mapeo;

use Yii;
use app\models\viaje\Viaje;
use app\models\user\User;
/**
 * This is the model class for table "mapeo".
 *
 * @property int $id ID
 * @property int $viaje_id Viaje ID
 * @property int $created_at Creado por
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property Viaje $viaje
 */
class Mapeo extends \yii\db\ActiveRecord
{


    const STATUS_ACTIVE     = 10;
    const STATUS_INACTIVE   = 1;



    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado / Abierto',
        self::STATUS_INACTIVE => 'Inhabilitado / Cerrado',
    ];

    public $viaje_names;
    public $lista_paquete_array;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mapeo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['nombre'], 'string', 'max' => 150],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'viaje_id' => 'Viaje ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMapeoDetalles()
    {
        return $this->hasMany(MapeoDetalle::className(), ['mapeo_id' => 'id']);
    }

     public function getPaqueteFila($fila_id,$mapeo_id)
    {
        return MapeoDetalle::find()->andWhere(["mapeo_id" => $mapeo_id])->andWhere(["fila_id" =>  $fila_id ])->orderBy('mapeo_detalle.tracked')->all();
    }

//------------------------------------------------------------------------------------------------//
// ACTIVE RECORD
//------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }else{
                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }
            return true;

        } else
            return false;
    }

}
