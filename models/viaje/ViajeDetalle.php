<?php

namespace app\models\viaje;

use Yii;
use app\models\envio\EnvioDetalle;
use app\models\caja\CajaMex;
use app\models\movimiento\MovimientoPaquete;

/**
 * This is the model class for table "viaje_detalle".
 *
 * @property int $id ID
 * @property int $viaje_id Viaje ID
 * @property int $paquete_id Envio detalle ID
 * @property string $tracked Tracked
 * @property int $tipo Tipo
 *
 * @property Viaje $viaje
 */
class ViajeDetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'viaje_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['viaje_id',  'tracked', 'tipo'], 'required'],
            [['viaje_id', 'paquete_id','caja_id', 'tipo'], 'integer'],
            [['peso_mx'], 'number'],
            [['tracked'], 'string', 'max' => 20],
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
            'paquete_id' => 'Envio Detalle ID',
            'caja_id' => 'Caja ID',
            'tracked' => 'Tracked',
            'tipo' => 'Tipo',
            'peso_mx' => 'Peso',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViaje()
    {
        return $this->hasOne(Viaje::className(), ['id' => 'viaje_id']);
    }

    public function getEnvioDetalleLaxTierra()
    {
        return $this->hasOne(EnvioDetalle::className(), ['id' => 'paquete_id']);
    }
    public function getEnvioDetalleMex()
    {
        return $this->hasOne(EnvioDetalle::className(), ['id' => 'paquete_id']);
    }
    public function getCajaMex(){
        return $this->hasOne(CajaMex::className(), ['id' => 'caja_id' ]);
    }

    public static function changePesoMx($tracked,$peso)
    {
        $ViajeDetalle = ViajeDetalle::find()->andWhere([ 'tracked' => $tracked ])->one();
        $ViajeDetalle->peso_mx = $peso;
        $ViajeDetalle->update();

        return true;
    }
}
