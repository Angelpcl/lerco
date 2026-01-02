<?php

namespace app\models\reparto;

use Yii;
use app\models\ruta\Ruta;
use app\models\sucursal\Sucursal;
use app\models\ruta\FilaRuta;

/**
 * This is the model class for table "reparto_recoleccion".
 *
 * @property int $id ID
 * @property int $reparto_ruta_id Reparto ruta ID
 * @property int $sucursal_id sucursal ID
 * @property int $cantidad_paquetes Numero de paquetes
 *
 * @property RepartoRuta $repartoRuta
 * @property Sucursal $sucursal
 */
class RepartoRecoleccion extends \yii\db\ActiveRecord
{

    public $reparto_recoleccion_array;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparto_recoleccion';
    }


    public function rules()
    {
        return [
            [['ruta_id', 'reparto_id','sucursal_id'], 'required'],
            [['reparto_recoleccion_array'],'safe'],
            [['ruta_id', 'sucursal_id', 'reparto_id', 'cantidad_paquetes'], 'integer'],
            [['reparto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reparto::className(), 'targetAttribute' => ['reparto_id' => 'id']],
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
            'id' => 'ID',
            'ruta_id' => 'Ruta ID',
            'sucursal_id' => 'Sucursal ID',
            'reparto_id' => 'Reparto ID',
            'cantidad_paquetes' => 'Cantidad Paquetes',
        ];
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
    }

    public function save_recoleccion_ruta(){
        $recoleccion_array = json_decode($this->reparto_recoleccion_array);
        if ($recoleccion_array) {
            foreach ($recoleccion_array as $key => $recoleccion) {
                if ($recoleccion->is_recoleccion && !$recoleccion->recoleccion_id ) {

                    $RepartoRecoleccion = new RepartoRecoleccion();
                    $RepartoRecoleccion->reparto_id         = $recoleccion->reparto_id;
                    $RepartoRecoleccion->ruta_id            = $recoleccion->ruta_id;
                    $RepartoRecoleccion->sucursal_id        = $recoleccion->id;
                    $RepartoRecoleccion->cantidad_paquetes  = $recoleccion->cantidad_paquetes ? $recoleccion->cantidad_paquetes : 0;
                    $RepartoRecoleccion->save();

                }
                if ($recoleccion->is_recoleccion && $recoleccion->recoleccion_id ) {
                    $RepartoRecoleccion = RepartoRecoleccion::findOne($recoleccion->recoleccion_id);
                    $RepartoRecoleccion->cantidad_paquetes = $recoleccion->cantidad_paquetes ? $recoleccion->cantidad_paquetes : 0;
                    $RepartoRecoleccion->update();
                }
                if (!$recoleccion->is_recoleccion && $recoleccion->recoleccion_id) {
                    $RepartoRecoleccion = RepartoRecoleccion::findOne($recoleccion->recoleccion_id);
                    $RepartoRecoleccion->delete();
                }

            }
        }
        return true;
    }

}
