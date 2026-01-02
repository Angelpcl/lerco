<?php
namespace app\models\reparto;

use Yii;
use app\models\envio\EnvioDetalle;
use app\models\reparto\Reparto;


/**
 * This is the model class for table "reparto_detalle".
 *
 * @property int $id ID
 * @property int $reparto_id Reparto ID
 * @property string $tracked Tracked
 * @property int $paquete_id Paquete ID
 *
 * @property EnvioDetalle $paquete
 * @property Reparto $reparto
 */
class RepartoDetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparto_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reparto_id', 'tracked', 'paquete_id'], 'required'],
            [['reparto_id', 'paquete_id'], 'integer'],
            [['tracked'], 'string', 'max' => 50],
            [['paquete_id'], 'exist', 'skipOnError' => true, 'targetClass' => EnvioDetalle::className(), 'targetAttribute' => ['paquete_id' => 'id']],
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
            'reparto_id'    => 'Reparto',
            'tracked'       => 'Tracked',
            'paquete_id'    => 'Paquete',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaquete()
    {
        return $this->hasOne(EnvioDetalle::className(), ['id' => 'paquete_id']);
    }

    public static function getRepartoDetalleSucursal($reparto_id,$sucursal_id)
    {
        return RepartoDetalle::find()->innerJoin("reparto","reparto_detalle.reparto_id = reparto.id")
                ->innerJoin("envio_detalle","reparto_detalle.paquete_id = envio_detalle.id")
                ->andWhere(["reparto.id" => $reparto_id ])
                ->andWhere(["envio_detalle.sucursal_receptor_id" => $sucursal_id])
                ->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReparto()
    {
        return $this->hasOne(Reparto::className(), ['id' => 'reparto_id']);
    }
}
