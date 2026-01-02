<?php

namespace app\models\promocion;

use Yii;
use app\models\producto\Producto;
use app\models\esys\EsysListaDesplegable;


/**
 * This is the model class for table "promocion_detalle_complemento".
 *
 * @property int $promocion_detalle_id Promocion detalle ID
 * @property int $promocion_complemento_id Promocion complemento ID
 * @property int $tipo Tipo
 * @property int $categoria_id Categoria ID
 *
 * @property PromocionComplemento $promocionComplemento
 * @property PromocionDetalle $promocionDetalle
 */
class PromocionDetalleComplemento extends \yii\db\ActiveRecord
{


    const TIPO_PRODUCTO   = 10;
    const TIPO_GENERAL    = 1;

    const COMPLEMENTO_ELECCION   = 10;
    const COMPLEMENTO_GENERAL    = 1;


    const PRODUCTO_ELECCION    = 10;
    const PRODUCTO_GENERAL   = 1;

    public static $tipoList = [
        self::TIPO_GENERAL   => 'Aplica a toda las categorias',
        self::TIPO_PRODUCTO  => 'Aplica a una categoria',
    ];

    public static $complementoList = [
        self::COMPLEMENTO_ELECCION  => 'Complemento de elección',
        self::COMPLEMENTO_GENERAL   => 'Complemento general',
    ];

    public static $productoTipoList = [
        self::PRODUCTO_GENERAL   => 'Aplica a todos los productos',
        self::PRODUCTO_ELECCION  => 'Aplica a un producto',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promocion_detalle_complemento';
    }

        /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promocion_detalle_id', 'promocion_complemento_id'], 'required'],
            [['promocion_detalle_id', 'promocion_complemento_id', 'producto_id', 'categoria_id', 'tipo_complemento', 'is_categoria', 'is_producto'], 'integer'],
            [['promocion_detalle_id', 'promocion_complemento_id'], 'unique', 'targetAttribute' => ['promocion_detalle_id', 'promocion_complemento_id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['categoria_id' => 'id']],
            [['promocion_complemento_id'], 'exist', 'skipOnError' => true, 'targetClass' => PromocionComplemento::className(), 'targetAttribute' => ['promocion_complemento_id' => 'id']],
            [['promocion_detalle_id'], 'exist', 'skipOnError' => true, 'targetClass' => PromocionDetalle::className(), 'targetAttribute' => ['promocion_detalle_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'promocion_detalle_id' => 'Promocion Detalle ID',
            'promocion_complemento_id' => 'Promocion Complemento ID',
            'producto_id' => 'Producto ID',
            'categoria_id' => 'Categoria ID',
            'tipo_complemento' => 'Tipo Complemento',
            'is_categoria' => 'Is Categoria',
            'is_producto' => 'Is Producto',
        ];
    }
       /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Producto::className(), ['id' => 'producto_id']);
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
    public function getPromocionComplemento()
    {
        return $this->hasOne(PromocionComplemento::className(), ['id' => 'promocion_complemento_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionDetalle()
    {
        return $this->hasOne(PromocionDetalle::className(), ['id' => 'promocion_detalle_id']);
    }
}
