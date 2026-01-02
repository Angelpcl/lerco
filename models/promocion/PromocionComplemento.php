<?php

namespace app\models\promocion;

use Yii;

/**
 * This is the model class for table "promocion_complemento".
 *
 * @property int $id ID
 * @property int $lb_free Libras Gratis
 * @property int $cobro_impuesto Cobro de impuesto
 *
 * @property PromocionDetalleComplemento[] $promocionDetalleComplementos
 * @property PromocionDetalle[] $promocionDetalles
 */
class PromocionComplemento extends \yii\db\ActiveRecord
{
    const ON_COBRO_IMPUESTO   = 10;
    const OFF_COBRO_IMPUESTO = 1;

    const ON_VALOR_PAQUETE   = 10;
    const OFF_VALOR_PAQUETE = 1;

    const ON_ENVIO_FREE   = 10;
    const OFF_ENVIO_FREE = 1;

    const ON_LBEXCEDENTE   = 10;
    const OFF_LBEXCEDENTE = 1;

    const ON_LBFREE   = 10;
    const OFF_LBFREE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promocion_complemento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lb_free', 'cobro_impuesto','lbexcedente','is_envio_free','is_lbexcedente','is_lb_free'], 'integer'],
            [['costo_libraexcedente' ], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lb_free' => 'Lb Free',
            'cobro_impuesto' => 'Cobro Impuesto',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionDetalleComplementos()
    {
        return $this->hasMany(PromocionDetalleComplemento::className(), ['promocion_complemento_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionDetalles()
    {
        return $this->hasMany(PromocionDetalle::className(), ['id' => 'promocion_detalle_id'])->viaTable('promocion_detalle_complemento', ['promocion_complemento_id' => 'id']);
    }
}
