<?php

namespace app\models\envio;

use Yii;
use app\models\promocion\PromocionComplemento;

/**
 * This is the model class for table "envio_complemento_promocion".
 *
 * @property int $id ID
 * @property int $envio_id Envio
 * @property int $complemento_id Complemento
 *
 * @property PromocionComplemento $complemento
 * @property Envio $envio
 */
class EnvioComplementoPromocion extends \yii\db\ActiveRecord
{

    public $envio_complemento_promocion_array;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'envio_complemento_promocion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['envio_id', 'complemento_id'], 'required'],
            [['envio_id', 'complemento_id'], 'integer'],
            [['envio_complemento_promocion_array'], 'safe'],
            [['complemento_id'], 'exist', 'skipOnError' => true, 'targetClass' => PromocionComplemento::className(), 'targetAttribute' => ['complemento_id' => 'id']],
            [['envio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Envio::className(), 'targetAttribute' => ['envio_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'envio_id' => 'Envio ID',
            'complemento_id' => 'Complemento ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComplemento()
    {
        return $this->hasOne(PromocionComplemento::className(), ['id' => 'complemento_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvio()
    {
        return $this->hasOne(Envio::className(), ['id' => 'envio_id']);
    }

     public function saveComplementoEnvio($envio_id)
    {
        $envio_complemento_promocion_array  = json_decode($this->envio_complemento_promocion_array);

        $EnvioComplementoPromocion          =  EnvioComplementoPromocion::deleteAll([ "envio_id" => $envio_id]);

        if (isset($envio_complemento_promocion_array) && $envio_complemento_promocion_array) {

            foreach ($envio_complemento_promocion_array as $key => $item) {
                $EnvioComplementoPromocion = new EnvioComplementoPromocion();
                $EnvioComplementoPromocion->envio_id        = $envio_id;
                $EnvioComplementoPromocion->complemento_id  = $item->id;
                $EnvioComplementoPromocion->save();
            }
        }

        return true;
    }
}
