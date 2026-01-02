<?php
namespace app\models\sucursal;

use Yii;
use app\models\esys\EsysListaDesplegable;

/**
 * This is the model class for table "lista_precio_mx".
 *
 * @property int $id ID
 * @property int $sucursal_envia_id Sucursal que envia
 * @property int $sucursal_recibe_id Sucursal que recibe
 * @property float|null $precio_libra Precio libra
 * @property int|null $default Tipo
 * @property int $tipo Tipo
 * @property int|null $destino_id Destino ID
 * @property int|null $categoria_id Categoria ID
 * @property int|null $required Requerido
 * @property int|null $intervalo Intervalo
 * @property float|null $impuesto Impuesto
 *
 * @property EsysListaDesplegable $categoria
 * @property EsysListaDesplegable $destino
 * @property Sucursal $sucursalEnvia
 * @property Sucursal $sucursalRecibe
 */
class ListaPrecioMx extends \yii\db\ActiveRecord
{

    const IS_DEFAULT = 10;

    const TIPO_LIBRA = 10;
    const TIPO_IMPUESTO = 20;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lista_precio_mx';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo'], 'required'],
            [['sucursal_envia_id', 'sucursal_recibe_id', 'default', 'tipo', 'destino_id', 'categoria_id', 'required', 'intervalo'], 'integer'],
            [['precio_libra', 'impuesto'], 'number'],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['categoria_id' => 'id']],
            [['destino_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['destino_id' => 'id']],
            [['sucursal_envia_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_envia_id' => 'id']],
            [['sucursal_recibe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_recibe_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sucursal_envia_id' => 'Sucursal que envia',
            'sucursal_recibe_id' => 'Sucursal Recibe ID',
            'precio_libra' => 'Precio Libra',
            'default' => 'Default',
            'tipo' => 'Tipo',
            'destino_id' => 'Destino',
            'categoria_id' => 'Categoria',
            'required' => 'Required',
            'intervalo' => 'Intervalo',
            'impuesto' => 'Costo extra',
        ];
    }

    /**
     * Gets query for [[Categoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'categoria_id']);
    }

    /**
     * Gets query for [[Destino]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDestino()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'destino_id']);
    }

    /**
     * Gets query for [[SucursalEnvia]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSucursalEnvia()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_envia_id']);
    }

    /**
     * Gets query for [[SucursalRecibe]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSucursalRecibe()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_recibe_id']);
    }
}
