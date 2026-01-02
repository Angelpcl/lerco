<?php

namespace app\models\cliente;

use Yii;

/**
 * This is the model class for table "cliente_acceso_log".
 *
 * @property int $id Id
 * @property int $acceso_id Cliente Acceso ID
 * @property int $operacion_id Operación
 * @property int $reference_id Reference Table ID
 * @property int $time_excecute Tiempo de execución (Milisegundos)
 * @property int $created_at Creado
 *
 * @property ClienteAcceso $acceso
 */
class ClienteAccesoLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente_acceso_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acceso_id', 'operacion_id', 'reference_id', 'time_excecute', 'created_at'], 'integer'],
            [['operacion_id', 'created_at'], 'required'],
            [['acceso_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClienteAcceso::className(), 'targetAttribute' => ['acceso_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'acceso_id' => 'Acceso ID',
            'operacion_id' => 'Operacion ID',
            'reference_id' => 'Reference ID',
            'time_excecute' => 'Time Excecute',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcceso()
    {
        return $this->hasOne(ClienteAcceso::className(), ['id' => 'acceso_id']);
    }
}
