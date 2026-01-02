<?php

namespace app\models\viaje;

use Yii;
use app\models\user\User;
use app\models\envio\EnvioDetalle;

/**
 * This is the model class for table "viaje_paquete_denegado".
 *
 * @property int $id ID
 * @property int $viaje_id Viaje ID
 * @property string $tracked Tracked
 * @property int $paquete_id Paquete ID
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property EnvioDetalle $paquete
 * @property Viaje $viaje
 */
class ViajePaqueteDenegado extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'viaje_paquete_denegado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['viaje_id', 'tracked'], 'required'],
            [['viaje_id', 'paquete_id', 'created_at', 'created_by'], 'integer'],
            [['tracked'], 'string', 'max' => 20],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['paquete_id'], 'exist', 'skipOnError' => true, 'targetClass' => EnvioDetalle::className(), 'targetAttribute' => ['paquete_id' => 'id']],
            [['viaje_id'], 'exist', 'skipOnError' => true, 'targetClass' => Viaje::className(), 'targetAttribute' => ['viaje_id' => 'id']],
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
            'tracked' => 'Tracked',
            'paquete_id' => 'Paquete ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
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
    public function getPaquete()
    {
        return $this->hasOne(EnvioDetalle::className(), ['id' => 'paquete_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViaje()
    {
        return $this->hasOne(Viaje::className(), ['id' => 'viaje_id']);
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }
            return true;

        } else
            return false;
    }
}
