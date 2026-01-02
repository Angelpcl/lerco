<?php

namespace app\models\envio;

use Yii;

/**
 * This is the model class for table "detail_envio_product".
 *
 * @property int $id
 * @property int $detalle_envio_id
 * @property string $detalle_json
 */
class DetailEnvioProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'detail_envio_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['detalle_envio_id', 'detalle_json'], 'required'],
            [['detalle_envio_id'], 'integer'],
            [['detalle_json'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'detalle_envio_id' => 'Detalle Envio ID',
            'detalle_json' => 'Detalle Json',
        ];
    }

    public function getDetalleEnvio()
    {
        return $this->hasOne(DetailEnvioProduct::className(), ['id' => 'detalle_envio_id']);
    }
}
