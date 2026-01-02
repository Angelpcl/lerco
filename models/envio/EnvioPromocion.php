<?php
namespace app\models\envio;

use Yii;
use app\models\user\User;
use app\models\producto\Producto;
/**
 * This is the model class for table "envio_promocion".
 *
 * @property int $id ID
 * @property int $envio_id Envio ID
 * @property int $tipo Tipo ( Libras free / Condonacion de impuesto)
 * @property int $producto_id Producto ID
 * @property double $costo_libra Costo de libra
 * @property int $lb_free Libras Gratis
 * @property double $lb_pagadas Libras pagadas
 * @property double $costo_libra_excendete Costo de libra excedente
 * @property double $condonacion_impuesto Descuento impuesto
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property Envio $envio
 * @property Producto $producto
 * @property User $updatedBy
 */
class EnvioPromocion extends \yii\db\ActiveRecord
{

    const TIPO_LIBRAS       = 10;
    const TIPO_IMPUESTO     = 20;


    public static $tipoList = [
        self::TIPO_LIBRAS   => 'Libras gratis',
        self::TIPO_IMPUESTO => 'Condonacion de impuesto',
    ];

    public $envio_promocion_array;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'envio_promocion';
    }

  /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['envio_id', 'tipo'], 'required'],
            [['envio_id', 'tipo', 'producto_id', 'lb_free', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['lb_pagada', 'lb_excedente', 'costo_libra_pagada', 'costo_libra_excendete', 'condonacion_impuesto'], 'number'],
            [['envio_promocion_array'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['envio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Envio::className(), 'targetAttribute' => ['envio_id' => 'id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
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
            'tipo' => 'Tipo',
            'producto_id' => 'Producto ID',
            'lb_free' => 'Lb Free',
            'lb_pagada' => 'Lb Pagada',
            'lb_excedente' => 'Lb Excedente',
            'costo_libra_pagada' => 'Costo Libra Pagada',
            'costo_libra_excendete' => 'Costo Libra Excendete',
            'condonacion_impuesto' => 'Condonacion Impuesto',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvio()
    {
        return $this->hasOne(Envio::className(), ['id' => 'envio_id']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function savePromocionManual($envio_id)
    {
        $envio_promocion_array = json_decode($this->envio_promocion_array);
        $EnvioPromocionRemove  =  EnvioPromocion::deleteAll([ "envio_id" => $envio_id]);
        if (isset($envio_promocion_array->libras_free) && $envio_promocion_array->libras_free) {
            $EnvioPromocion = new EnvioPromocion();
            $EnvioPromocion->tipo    = isset($envio_promocion_array->libras_free->tipo) ? $envio_promocion_array->libras_free->tipo : EnvioPromocion::TIPO_LIBRAS;
            $EnvioPromocion->lb_free = isset($envio_promocion_array->libras_free->lb_free) ? $envio_promocion_array->libras_free->lb_free : 0;
            $EnvioPromocion->lb_pagada               = isset($envio_promocion_array->libras_free->lb_pagadas) ?  $envio_promocion_array->libras_free->lb_pagadas : 0;
            $EnvioPromocion->lb_excedente            = isset($envio_promocion_array->libras_free->lb_exedente) ? $envio_promocion_array->libras_free->lb_exedente : 0;
            $EnvioPromocion->costo_libra_pagada      = isset($envio_promocion_array->libras_free->precio_lb_pagada) ? $envio_promocion_array->libras_free->precio_lb_pagada : 0;
            $EnvioPromocion->costo_libra_excendete   = isset($envio_promocion_array->libras_free->precio_lb_excedente) ? $envio_promocion_array->libras_free->precio_lb_excedente :0;
            $EnvioPromocion->envio_id                = $envio_id;
            $EnvioPromocion->save();
        }

        if (isset($envio_promocion_array->condonacion_impuesto)) {
            foreach ($envio_promocion_array->condonacion_impuesto as $key => $paquete) {
                $EnvioPromocionCondonacion = new EnvioPromocion();
                $EnvioPromocionCondonacion->tipo                = EnvioPromocion::TIPO_IMPUESTO;
                $EnvioPromocionCondonacion->producto_id         = $paquete->producto_id;
                $EnvioPromocionCondonacion->condonacion_impuesto= $paquete->condonacion_porcentaje_total;
                $EnvioPromocionCondonacion->envio_id            = $envio_id;
                $EnvioPromocionCondonacion->save();
            }
        }
        return true;
    }

//------------------------------------------------------------------------------------------------//
// ACTIVE RECORD
//------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }else{
                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }
            return true;

        } else
            return false;
    }


}
