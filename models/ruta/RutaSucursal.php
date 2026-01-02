<?php

namespace app\models\ruta;

use Yii;
use app\models\ruta\Ruta;
use app\models\sucursal\Sucursal;


/**
 * This is the model class for table "ruta_sucursal".
 *
 * @property int $ruta_id Ruta ID
 * @property int $sucursal_id Sucursal ID
 * @property int $is_solicitud_recolección Solicitud de recolección
 * @property int $fecha_aprox Fecha de recolección
 *
 * @property Ruta $ruta
 * @property Sucursal $sucursal
 */
class RutaSucursal extends \yii\db\ActiveRecord
{

    public $ruta_sucursal_array;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ruta_sucursal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ruta_id', 'sucursal_id'], 'required'],
            [['ruta_id', 'sucursal_id', 'orden'], 'integer'],
            [['ruta_sucursal_array'],'safe'],
            [['ruta_id', 'sucursal_id'], 'unique', 'targetAttribute' => ['ruta_id', 'sucursal_id']],
            [['ruta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ruta::className(), 'targetAttribute' => ['ruta_id' => 'id']],
            [['sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ruta_id' => 'Ruta ID',
            'sucursal_id' => 'Sucursal ID',
            'orden' => 'Orden',

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuta()
    {
        return $this->hasOne(Ruta::className(), ['id' => 'ruta_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
    }

    public function save_sucursal_ruta(){
        $sucursal_array = json_decode($this->ruta_sucursal_array);
        if ($sucursal_array) {
            foreach ($sucursal_array as $key => $sucursal) {
                $RutaSucursal = new RutaSucursal();
                $RutaSucursal->ruta_id       = $this->ruta_id;
                $RutaSucursal->sucursal_id   = $sucursal->id;
                $RutaSucursal->orden         = RutaSucursal::find()->andWhere(["ruta_id" =>$this->ruta_id])->orderBy("orden desc")->one()["orden"] + 1 ;
                $RutaSucursal->save();
            }
        }
        return true;
    }
}
