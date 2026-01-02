<?php
namespace app\models\ruta;

use Yii;
use app\models\reparto\RepartoFila;
use app\models\reparto\ViewReparto;
use app\models\reparto\RepartoRecoleccion;
/**
 * This is the model class for table "fila_ruta".
 *
 * @property int $fila_id Fila ID
 * @property int $ruta_id Ruta ID
 *
 * @property RepartoFila $fila
 * @property Ruta $ruta
 */
class FilaRuta extends \yii\db\ActiveRecord
{
    public $ruta_fila_array;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fila_ruta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fila_id', 'ruta_id'], 'required'],
            [['fila_id', 'ruta_id'], 'integer'],
            [['ruta_fila_array'],'safe'],
            [['fila_id'], 'exist', 'skipOnError' => true, 'targetClass' => RepartoFila::className(), 'targetAttribute' => ['fila_id' => 'id']],
            [['ruta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ruta::className(), 'targetAttribute' => ['ruta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fila_id' => 'Fila ID',
            'ruta_id' => 'Ruta ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFila()
    {
        return $this->hasOne(RepartoFila::className(), ['id' => 'fila_id']);
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
    public function getFilaPaquetes()
    {
        return $this->hasMany(FilaPaquete::className(), ['fila_ruta_id' => 'id']);
    }

    public function save_fila_ruta(){
        $ruta_array = json_decode($this->ruta_fila_array);

        if ($ruta_array) {
            foreach ($ruta_array as $key => $ruta) {
                $FilaRuta = new FilaRuta();
                $FilaRuta->fila_id    = $ruta->fila_id;
                $FilaRuta->ruta_id    = $ruta->id;
                $FilaRuta->save();


                $rutaPaquete  = (new ViewReparto)->getSucursalPaqueteAjax(["fila_ruta_id" => $FilaRuta->id]);
                $countPaquete = 30;

                foreach ($rutaPaquete as $key => $paquete) {
                    $is_paquete = FilaPaquete::find()->where(["paquete_id" => $paquete["id"],"tracked" => $paquete["tracked"] ])->one();
                    if (!$is_paquete) {
                        if ($countPaquete > 0) {

                            $FilaPaquete = new FilaPaquete();
                            $FilaPaquete->fila_ruta_id  = $FilaRuta->id;
                            $FilaPaquete->tracked       = $paquete["tracked"];
                            $FilaPaquete->paquete_id    = $paquete["id"];
                            $FilaPaquete->save();

                            $countPaquete = $countPaquete - 1;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $RepartoRecoleccion  =  RepartoRecoleccion::deleteAll([ "reparto_id" => $this->fila->reparto->id , "ruta_id" => $this->ruta_id ]);
    }
}
