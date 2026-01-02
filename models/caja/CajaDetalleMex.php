<?php

namespace app\models\caja;

use Yii;
use app\models\envio\EnvioDetalle;

/**
 * This is the model class for table "caja_detalle_mex".
 *
 * @property int $id ID
 * @property int $caja_mex_id Caja mex ID
 * @property int $envio_detalle_id Envio detalle Id
 *
 * @property CajaMex $cajaMex
 * @property EnvioDetalle $envioDetalle
 */
class CajaDetalleMex extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'caja_detalle_mex';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['caja_mex_id', 'envio_detalle_id'], 'required'],
            [['caja_mex_id', 'envio_detalle_id'], 'integer'],
            [['caja_mex_id'], 'exist', 'skipOnError' => true, 'targetClass' => CajaMex::className(), 'targetAttribute' => ['caja_mex_id' => 'id']],
            [['tracked'], 'string', 'max' => 20],
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
            'caja_mex_id' => 'Caja Mex ID',
            'envio_detalle_id' => 'Envio Detalle ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCajaMex()
    {
        return $this->hasOne(CajaMex::className(), ['id' => 'caja_mex_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvioDetalle()
    {
        return $this->hasOne(EnvioDetalle::className(), ['id' => 'envio_detalle_id']);
    }
}
