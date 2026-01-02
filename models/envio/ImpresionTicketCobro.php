<?php

namespace app\models\envio;

use Yii;
use app\models\user\User;

/**
 * This is the model class for table "impresion_ticket_cobro".
 *
 * @property int $id ID
 * @property int $envio_detalle_id Paquete
 * @property int $count Count
 * @property int $user_id User
 * @property int $created_at Creado
 *
 * @property User $user
 * @property EnvioDetalle $envioDetalle
 */
class ImpresionTicketCobro extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'impresion_ticket_cobro';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['envio_detalle_id', 'count', 'user_id', 'created_at'], 'required'],
            [['envio_detalle_id', 'count', 'user_id', 'created_at'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['envio_detalle_id'], 'exist', 'skipOnError' => true, 'targetClass' => EnvioDetalle::className(), 'targetAttribute' => ['envio_detalle_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'envio_detalle_id' => 'Envio Detalle ID',
            'count' => 'Count',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvioDetalle()
    {
        return $this->hasOne(EnvioDetalle::className(), ['id' => 'envio_detalle_id']);
    }
}
