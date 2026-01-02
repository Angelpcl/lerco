<?php

namespace app\models\producto;

use Yii;
use app\models\esys\EsysListaDesplegable;


/**
 * This is the model class for table "producto_detalle".
 *
 * @property int $id ID
 * @property int $producto_id Producto ID
 * @property int $required_min Requerimiento minimo
 * @property int $tipo_valor Tipo de valor
 * @property double $costo_extra Costo extra
 * @property double $impuesto Impuesto
 * @property string $nota Nota
 *
 * @property Producto $producto
 */
class ProductoDetalle extends \yii\db\ActiveRecord
{
    const TIPO_USADO   = 20;
    const TIPO_NUEVO   = 10;
    const TIPO_NA      = 1;

    public static $tipoList = [
        self::TIPO_NA => 'N/A',
        self::TIPO_NUEVO   => 'Producto nuevo/original',
        self::TIPO_USADO => 'Producto usado/copia',
    ];

    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 1;

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Inhabilitado',
    ];


    public $producto_detalles = [];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'producto_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['producto_id', 'required_min', 'tipo_valor','status','intervalo'], 'integer'],
            [['costo_extra', 'impuesto'], 'number'],
            [['nota'], 'string'],
            [['producto_detalles'], 'safe'],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['tipo_volumen_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['tipo_volumen_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'producto_id' => 'Producto ID',
            'required_min' => 'Apartir de:',
            'tipo_valor' => 'Tipo Valor',
            'tipo_volumen_id' => 'Tamaño',
            'costo_extra' => 'Costo Extra (DLLS)',
            'impuesto' => 'Impuesto (DLLS)',
            'intervalo' => 'Intervalo',
            'nota' => 'Nota',
            'status' => 'Estatus',
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
    public function getTipoVolumen()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'tipo_volumen_id']);
    }

    public function producto_detalle_save($producto_id){

        foreach (json_decode($this->producto_detalles[0]) as $key => $p_detalle) {
            if ($p_detalle->create == 1) {
                $ProductoDetalle = new ProductoDetalle();
                $ProductoDetalle->producto_id =     $producto_id;
                $ProductoDetalle->required_min =    isset($p_detalle->required_min) ? $p_detalle->required_min : NULL;
                $ProductoDetalle->tipo_valor =      isset($p_detalle->tipo_valor) ? $p_detalle->tipo_valor : NULL;
                $ProductoDetalle->tipo_volumen_id = isset($p_detalle->tipo_volumen_id) ? $p_detalle->tipo_volumen_id : NULL;
                $ProductoDetalle->costo_extra =     isset($p_detalle->costo_extra) ? $p_detalle->costo_extra : NULL;
                $ProductoDetalle->impuesto =        isset($p_detalle->impuesto) ? $p_detalle->impuesto: NULL;
                $ProductoDetalle->intervalo =       isset($p_detalle->intervalo) ? $p_detalle->intervalo: NULL;
                $ProductoDetalle->nota =            isset($p_detalle->nota) ? $p_detalle->nota : NULL;
                $ProductoDetalle->status =          self::STATUS_ACTIVE;

                $ProductoDetalle->save();
            }elseif ($p_detalle->create == 2) {
                $ProductoDetalle = ProductoDetalle::findOne($p_detalle->productoDetalle_id);
                if ($p_detalle->status == self::STATUS_INACTIVE) {
                    $ProductoDetalle->status = self::STATUS_INACTIVE;
                    $ProductoDetalle->save();
                }
            }
        }

    }
}
