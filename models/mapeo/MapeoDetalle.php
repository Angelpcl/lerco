<?php
namespace app\models\mapeo;

use Yii;
use app\models\viaje\Viaje;
use app\models\esys\EsysListaDesplegable;

/**
 * This is the model class for table "mapeo_detalle".
 *
 * @property int $id ID
 * @property int $mapeo_id Mapeo ID
 * @property int $viaje_id Viaje ID
 * @property string $tracked Tracked
 * @property int $paquete_id Paquete ID
 *
 * @property Mapeo $mapeo
 * @property Viaje $viaje
 */
class MapeoDetalle extends \yii\db\ActiveRecord
{

    public $mapeo_detalle_array;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mapeo_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mapeo_id', 'fila_id','viaje_id', 'paquete_id'], 'integer'],
            [['tracked'], 'string', 'max' => 50],
            [['mapeo_detalle_array'], 'safe'],
            [['mapeo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mapeo::className(), 'targetAttribute' => ['mapeo_id' => 'id']],
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
            'mapeo_id' => 'Mapeo ID',
            'viaje_id' => 'Viaje ID',
            'tracked' => 'Tracked',
            'paquete_id' => 'Paquete ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFila()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'fila_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMapeo()
    {
        return $this->hasOne(Mapeo::className(), ['id' => 'mapeo_id']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViaje()
    {
        return $this->hasOne(Viaje::className(), ['id' => 'viaje_id']);
    }

    public function mapeo_detalle_array_save($mapeo_id)
    {
        $mapeo_detalle_array = json_decode($this->mapeo_detalle_array);
        if ($mapeo_detalle_array) {
            foreach ($mapeo_detalle_array as $key => $env_detalle) {
                foreach ($env_detalle->paquete[0] as $key => $paquete) {
                    $MapeoDetalle = new  MapeoDetalle();
                    $MapeoDetalle->mapeo_id = $mapeo_id;
                    $MapeoDetalle->fila_id  = $env_detalle->fila;
                    $MapeoDetalle->viaje_id = $paquete->viaje_id;
                    $MapeoDetalle->tracked  = $paquete->trackend;
                    $MapeoDetalle->paquete_id = $paquete->paquete_id;
                    $MapeoDetalle->save();

                }
            }
        }
        return true;
    }

}
