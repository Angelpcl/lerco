<?php

namespace app\models\promocion;

use Yii;
/**
 * This is the model class for table "promocion_detalle_anexo".
 *
 * @property int $id ID
 * @property int $promocion_detalle_id promocion detalle ID
 * @property int $categoria_id Categoria ID
 * @property int $is_categoria Is categoria
 * @property int $lb_free Libras Free
 *
 * @property EsysListaDesplegable $categoria
 * @property PromocionDetalle $promocionDetalle
 */
class PromocionDetalleAnexo extends \yii\db\ActiveRecord
{



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promocion_detalle_anexo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promocion_detalle_id', 'lb_free'], 'required'],
            [['promocion_detalle_id',  'lb_free'], 'integer'],
            [['promocion_detalle_id'], 'exist', 'skipOnError' => true, 'targetClass' => PromocionDetalle::className(), 'targetAttribute' => ['promocion_detalle_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'promocion_detalle_id' => 'Promocion Detalle ID',
            'categoria_id' => 'Categoria ID',

            'lb_free' => 'Lb Free',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionAnexoCategorias()
    {
        return $this->hasMany(PromocionAnexoCategoria::className(), ['promocion_detalle_anexo_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionDetalle()
    {
        return $this->hasOne(PromocionDetalle::className(), ['id' => 'promocion_detalle_id']);
    }
}
