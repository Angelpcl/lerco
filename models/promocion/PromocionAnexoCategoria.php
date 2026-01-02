<?php

namespace app\models\promocion;

use Yii;
use app\models\esys\EsysListaDesplegable;

/**
 * This is the model class for table "promocion_anexo_categoria".
 *
 * @property int $id ID
 * @property int $promocion_detalle_anexo_id Promocion detalle anexo ID
 * @property int $categoria_id Categoria ID
 * @property int $is_categoria Is categoria
 *
 * @property EsysListaDesplegable $categoria
 * @property PromocionDetalleAnexo $promocionDetalleAnexo
 */
class PromocionAnexoCategoria extends \yii\db\ActiveRecord
{

    const IS_CATEGORIA_ON     = 10;
    const IS_CATEGORIA_OFF    = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promocion_anexo_categoria';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promocion_detalle_anexo_id', 'is_categoria'], 'required'],
            [['promocion_detalle_anexo_id', 'is_categoria'], 'integer'],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['categoria_id' => 'id']],
            [['promocion_detalle_anexo_id'], 'exist', 'skipOnError' => true, 'targetClass' => PromocionDetalleAnexo::className(), 'targetAttribute' => ['promocion_detalle_anexo_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'promocion_detalle_anexo_id' => 'Promocion Detalle Anexo ID',
            'categoria_id' => 'Categoria ID',
            'is_categoria' => 'Is Categoria',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'categoria_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionDetalleAnexo()
    {
        return $this->hasOne(PromocionDetalleAnexo::className(), ['id' => 'promocion_detalle_anexo_id']);
    }
}
