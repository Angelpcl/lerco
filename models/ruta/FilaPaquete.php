<?php

namespace app\models\ruta;

use Yii;

/**
 * This is the model class for table "fila_paquete".
 *
 * @property int $id ID
 * @property int $fila_ruta_id Fila ruta ID
 * @property string $tracked Tracked
 *
 * @property FilaRuta $filaRuta
 */
class FilaPaquete extends \yii\db\ActiveRecord
{

    public $reparto_paquete_array;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fila_paquete';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fila_ruta_id', 'tracked'], 'required'],
            [['fila_ruta_id','paquete_id'], 'integer'],
            [['reparto_paquete_array'], 'safe'],
            [['tracked'], 'string', 'max' => 20],
            [['fila_ruta_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilaRuta::className(), 'targetAttribute' => ['fila_ruta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fila_ruta_id' => 'Fila Ruta ID',
            'tracked' => 'Tracked',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilaRuta()
    {
        return $this->hasOne(FilaRuta::className(), ['id' => 'fila_ruta_id']);
    }

    public function getEnvioDetalleLaxTierra()
    {
        return $this->hasOne(EnvioDetalle::className(), ['id' => 'paquete_id']);
    }

    public function save_fila_paquete(){

        $paquete_array = json_decode($this->reparto_paquete_array);

        FilaPaquete::deleteAll([ "fila_ruta_id" => $this->fila_ruta_id]);

        if ($paquete_array) {
            foreach ($paquete_array as $key => $paquete) {
                $is_paquete = FilaPaquete::find()->where(["paquete_id" => $paquete->paquete_id,"tracked" => $paquete->tracked])->one();

                if (!$is_paquete) {
                    $FilaPaquete = new FilaPaquete();
                    $FilaPaquete->fila_ruta_id  = $paquete->fila_ruta_id;
                    $FilaPaquete->tracked       = $paquete->tracked;
                    $FilaPaquete->paquete_id    = $paquete->paquete_id;
                    $FilaPaquete->save();
                }
            }
        }
        return true;
    }
}
