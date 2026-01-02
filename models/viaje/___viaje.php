<?php

namespace app\models\viaje;

use Yii;
use app\models\Esys;
use app\models\user\User;

/**
 * This is the model class for table "trailer".
 *
 * @property int $id ID
 * @property int $fecha_salida Fecha de salida
 * @property string $nombre_chofer Nombre de chofer
 * @property string $placas Placas
 * @property int $status Estatus
 * @property int $created_at Creado por
 * @property int $created_by Creado by
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Viaje extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE     = 10;
    const STATUS_CERRADO    = 20;
    const STATUS_TERMINADO  = 30;
    const STATUS_CANCEL     = 2;
    const STATUS_INACTIVE   = 1;

    public static $statusList = [
        self::STATUS_ACTIVE     => 'Habilitado',
        self::STATUS_CERRADO    => 'Cerrado / Enviado',
        self::STATUS_TERMINADO  => 'Terminado / Concluido',
        self::STATUS_CANCEL     => 'Cancelado',
        self::STATUS_INACTIVE   => 'Inhabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'viaje';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_salida'], 'required'],
            [['fecha_salida'],'safe'],
            [['status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['nombre_chofer'], 'string', 'max' => 100],
            [['placas'], 'string', 'max' => 50],
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
            'fecha_salida' => 'Fecha Salida',
            'nombre_chofer' => 'Nombre Chofer',
            'placas' => 'Placas',
            'status' => 'Status',
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

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            $this->fecha_salida = Esys::stringToTimeUnix($this->fecha_salida);
            if ($insert) {
                $this->status = self::STATUS_ACTIVE;
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
