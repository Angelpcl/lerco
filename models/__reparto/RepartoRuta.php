<?php

namespace app\models\reparto;

use Yii;
use app\models\ruta\Ruta;

/**
 * This is the model class for table "reparto_ruta".
 *
 * @property int $reparto_ruta_id ID
 * @property int $reparto_id Repardo ID
 * @property int $ruta_id Ruta ID
 *
 * @property RepartoRecoleccion[] $repartoRecoleccions
 * @property Reparto $reparto
 * @property Ruta $ruta
 */
class RepartoRuta extends \yii\db\ActiveRecord
{

    public $ruta_reparto_array;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparto_ruta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reparto_id', 'ruta_id'], 'required'],
            [['reparto_id', 'ruta_id'], 'integer'],
            [['ruta_reparto_array'],'safe'],
            [['reparto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reparto::className(), 'targetAttribute' => ['reparto_id' => 'id']],
            [['ruta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ruta::className(), 'targetAttribute' => ['ruta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'reparto_ruta_id' => 'Reparto Ruta ID',
            'reparto_id' => 'Reparto ID',
            'ruta_id' => 'Ruta ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepartoRecoleccions()
    {
        return $this->hasMany(RepartoRecoleccion::className(), ['reparto_ruta_id' => 'reparto_ruta_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReparto()
    {
        return $this->hasOne(Reparto::className(), ['id' => 'reparto_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuta()
    {
        return $this->hasOne(Ruta::className(), ['id' => 'ruta_id']);
    }

    public function save_reparto_ruta(){
        $ruta_array = json_decode($this->ruta_reparto_array);
        if ($ruta_array) {
            foreach ($ruta_array as $key => $ruta) {
                $RepartoRuta = new RepartoRuta();
                $RepartoRuta->reparto_id    = $this->reparto_id;
                $RepartoRuta->ruta_id       = $ruta->id;
                $RepartoRuta->save();
            }
        }

        return true;
    }
}
