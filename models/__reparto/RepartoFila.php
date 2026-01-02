<?php
namespace app\models\reparto;

use Yii;
use app\models\ruta\Ruta;
use app\models\esys\EsysListaDesplegable;
use app\models\ruta\FilaRuta;
/**
 * This is the model class for table "reparto_fila_ruta".
 *
 * @property int $id ID
 * @property int $nombre_id Nombre fila
 * @property int $reparto_id Reparto ID
 * @property int $ruta_id Ruta ID
 * @property int $chofer_id Chofer
 * @property int $num_camion_id Clave / N° de unidad
 * @property int $status Estatus
 *
 * @property EsysListaDesplegable $chofer
 * @property EsysListaDesplegable $nombre
 * @property EsysListaDesplegable $numCamion
 * @property Reparto $reparto
 * @property Ruta $ruta
 */
class RepartoFila extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE     = 10;
    const STATUS_CERRADO    = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparto_fila';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre_id', 'reparto_id', 'status'], 'required'],
            [['nombre_id', 'reparto_id', 'chofer_id', 'num_camion_id', 'status'], 'integer'],
            [['chofer_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['chofer_id' => 'id']],
            [['nombre_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['nombre_id' => 'id']],
            [['num_camion_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['num_camion_id' => 'id']],
            [['reparto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reparto::className(), 'targetAttribute' => ['reparto_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre_id' => 'Nombre',
            'reparto_id' => 'Reparto',
            'chofer_id' => 'Chofer',
            'num_camion_id' => 'Clave / N° de unidad',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilaRutas()
    {
        return $this->hasMany(FilaRuta::className(), ['fila_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChofer()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'chofer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNombre()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'nombre_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumCamion()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'num_camion_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReparto()
    {
        return $this->hasOne(Reparto::className(), ['id' => 'reparto_id']);
    }
}
