<?php
namespace app\models\promocion;

use Yii;

/**
 * This is the model class for table "promocion_detalle".
 *
 * @property int $id ID
 * @property int $promocion_id Promocion ID
 * @property double $costo_libra_code Precio de libra con Codigo promocional
 * @property double $costo_libra_sin_code Precio de libra sin codigo promocional
 *
 * @property Promocion $promocion
 */
class PromocionDetalle extends \yii\db\ActiveRecord
{
    public $promocion_detalles;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promocion_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promocion_id','lb_requerida'], 'integer'],
            [['promocion_detalles'], 'safe'],
            [['costo_libra_code', 'costo_libra_sin_code'], 'number'],
            [['promocion_id'], 'exist', 'skipOnError' => true, 'targetClass' => Promocion::className(), 'targetAttribute' => ['promocion_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'promocion_id' => 'Promocion ID',
            'lb_requerida' => 'Libras (requeridas)',
            'costo_libra_code' => 'Costo de libra con ID',
            'costo_libra_sin_code' => 'Costo de la libra sin ID',
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvios()
    {
        return $this->hasMany(Envio::className(), ['promocion_detalle_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocion()
    {
        return $this->hasOne(Promocion::className(), ['id' => 'promocion_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionDetalleComplementos()
    {
        return $this->hasMany(PromocionDetalleComplemento::className(), ['promocion_detalle_id' => 'id']);
    }
     /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionDetalleAnexos()
    {
        return $this->hasMany(PromocionDetalleAnexo::className(), ['promocion_detalle_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionComplementos()
    {
        return $this->hasMany(PromocionComplemento::className(), ['id' => 'promocion_complemento_id'])->viaTable('promocion_detalle_complemento', ['promocion_detalle_id' => 'id']);
    }


    public function savePromocionDetalle($promocion_id)
    {
        $promocion_detalles_array = json_decode($this->promocion_detalles);


        if ($promocion_detalles_array) {
            foreach ($promocion_detalles_array as $key => $pro_detalle) {
                $PromocionDetalle = new PromocionDetalle();
                $PromocionDetalle->promocion_id     = $promocion_id;
                $PromocionDetalle->lb_requerida     = $pro_detalle->lb_requerida;
                $PromocionDetalle->costo_libra_code = $pro_detalle->costo_libra_code;
                $PromocionDetalle->costo_libra_sin_code = $pro_detalle->costo_libra_sin_code;
                $PromocionDetalle->save();

                if(isset($pro_detalle->promocione_complemento[0])){
                    foreach ($pro_detalle->promocione_complemento[0] as $key => $complememto) {
                        $PromocionComplemento = new PromocionComplemento();
                        $PromocionComplemento->lb_free          =  $complememto->lb_free ? $complememto->lb_free : NULL;
                        $PromocionComplemento->cobro_impuesto   = $complememto->cobro_impuesto ?  $complememto->cobro_impuesto : NULL;
                        $PromocionComplemento->is_envio_free    = $complememto->is_envio_free ?  $complememto->is_envio_free :NULL;

                        $PromocionComplemento->is_lb_free       =  $complememto->lbfree_check ? $complememto->lbfree_check  :NULL;

                        $PromocionComplemento->save();

                        $PromocionDetalleComplemento = new PromocionDetalleComplemento();
                        $PromocionDetalleComplemento->promocion_detalle_id      = $PromocionDetalle->id;
                        $PromocionDetalleComplemento->promocion_complemento_id  = $PromocionComplemento->id;
                        $PromocionDetalleComplemento->producto_id               = $complememto->producto_id;
                        $PromocionDetalleComplemento->categoria_id              = $complememto->categoria_id;
                        $PromocionDetalleComplemento->tipo_complemento          = $complememto->tipo_complemento;
                        $PromocionDetalleComplemento->is_categoria              = $complememto->tipo;

                        $PromocionDetalleComplemento->is_valor_paquete          = $complememto->is_valor_paquete;
                        $PromocionDetalleComplemento->valor_paquete_aprox       = $complememto->valor_paquete_aprox;
                        $PromocionDetalleComplemento->num_producto              = $complememto->cantidad_producto;

                        $PromocionDetalleComplemento->is_producto       = $complememto->producto_tipo;
                        $PromocionDetalleComplemento->save();
                    }
                }
                if (isset($pro_detalle->anexos[0])) {
                    foreach ($pro_detalle->anexos[0] as $key => $anexo) {
                        $PromocionDetalleAnexo = new PromocionDetalleAnexo();
                        $PromocionDetalleAnexo->promocion_detalle_id = $PromocionDetalle->id;
                        $PromocionDetalleAnexo->lb_free         = $anexo->libras_free;
                        $PromocionDetalleAnexo->save();

                        foreach ($anexo->categorias as $key => $categoria) {
                            $PromocionAnexoCategoria = new PromocionAnexoCategoria();
                            $PromocionAnexoCategoria->promocion_detalle_anexo_id = $PromocionDetalleAnexo->id;
                            $PromocionAnexoCategoria->categoria_id = $categoria->is_categoria == PromocionAnexoCategoria::IS_CATEGORIA_ON ? $categoria->categoria_id: null;
                            $PromocionAnexoCategoria->is_categoria = $categoria->is_categoria == PromocionAnexoCategoria::IS_CATEGORIA_ON ? $categoria->is_categoria: PromocionAnexoCategoria::IS_CATEGORIA_OFF;
                            $PromocionAnexoCategoria->save();
                        }
                    }
                }
            }
        }
        return true;
    }
}
