<?php

namespace app\models\esys;

use Yii;
use app\models\user\User;
use app\models\envio\Envio;
/**
 * This is the model class for table "esys_setting".
 *
 * @property int $cliente_id Cliente
 * @property string $clave Clave
 * @property string $valor Valor
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class EsysSetting extends \yii\db\ActiveRecord
{

    const PRECION_MEX_1     = "PRECION_MEX_1";
    const PRECION_MEX_2     = "PRECION_MEX_2";
    const PRECION_MEX_3     = "PRECION_MEX_3";
    const PRECION_MEX_4     = "PRECION_MEX_4";
    const PRECION_MEX_5     = "PRECION_MEX_5";
    const COBRO_SEGURO_MEX  = "COBRO_SEGURO_MEX";
    const PRECIO_LIBRA_TIERRA       = "PRECIO_LIBRA_TIERRA";
    const PRECIO_LIBRA_LAX          = "PRECIO_LIBRA_LAX";
    const COBRO_SEGURO_TIERRA       = "COBRO_SEGURO_TIERRA";
    const COBRO_SEGURO_LAX          = "COBRO_SEGURO_LAX";
    const PRECIO_BASE_REENVIO_LAX_TIERRA = "PRECIO_BASE_REENVIO_LAX_TIERRA";


    const PRODUCTO_IMPUESTO_LAX_NEW     = "PRODUCTO_IMPUESTO_LAX_NEW";
    const PRODUCTO_IMPUESTO_LAX_OLD     = "PRODUCTO_IMPUESTO_LAX_OLD";
    const PRODUCTO_IMPUESTO_TIERRA_NEW  = "PRODUCTO_IMPUESTO_TIERRA_NEW";
    const PRODUCTO_IMPUESTO_TIERRA_OLD  = "PRODUCTO_IMPUESTO_TIERRA_OLD";




    const RANGO_FILA_UNO  = "RANGO_FILA_UNO";
    const RANGO_FILA_DOS  = "RANGO_FILA_DOS";






    public $esysSetting_list = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'esys_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],

            [['clave'], 'string', 'max' => 40],
            [['param1','param2'], 'string', 'max' => 20],
            [['valor'], 'string', 'max' => 250],
            [['clave'], 'unique'],
            [['esysSetting_list'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cliente_id' => 'Cliente ID',
            'clave' => 'Clave',
            'valor' => 'Valor',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public  function getConfiguracionAll()
    {
        return  EsysSetting::find()->orderBy('orden asc')->all();
    }

    public function saveConfiguracion($esysSetting_list)
    {
        foreach ($esysSetting_list["esysSetting_list"] as $key => $item) {
            $EsysSetting = EsysSetting::findOne(["clave" => $key]);
            $EsysSetting->valor = $item;
            $EsysSetting->update();
        }
    }
    public static function getPrecioLibra($tipo){
        if ($tipo == Envio::TIPO_ENVIO_TIERRA)
            return EsysSetting::findOne(["clave" => 'PRECIO_LIBRA_TIERRA'])->valor;
        elseif($tipo == Envio::TIPO_ENVIO_LAX)
            return EsysSetting::findOne(["clave" => 'PRECIO_LIBRA_lAX'])->valor;
    }

    public static function getTimeLoader(){
        return EsysSetting::findOne(["clave" => 'TIME_LOADER'])->valor;
    }

    public static function getDateSincronizado(){
        return EsysSetting::findOne(["clave" => 'DATE_SINCRONIZADO'])->valor;
    }

    public static function getPrecioBaseReenvio(){
        return EsysSetting::findOne(["clave" => 'PRECIO_BASE_REENVIO_LAX_TIERRA'])->valor;
    }

    public static function getCobroSeguroTierra(){
        return EsysSetting::findOne(["clave" => 'COBRO_SEGURO_TIERRA'])->valor;
    }

    public static function getCobroSeguroLax(){
        return EsysSetting::findOne(["clave" => 'COBRO_SEGURO_LAX'])->valor;
    }

    public static function getPrecioMex($clave){
        return EsysSetting::findOne(["clave" => $clave])->valor;
    }

    public static function getCobroSeguroMex(){
        return EsysSetting::findOne(["clave" => self::COBRO_SEGURO_MEX])->valor;
    }


    public static function getImpuestoNewLax(){
        return EsysSetting::findOne(["clave" => self::PRODUCTO_IMPUESTO_LAX_NEW])->valor;
    }
    public static function getImpuestoOldLax(){
        return EsysSetting::findOne(["clave" => self::PRODUCTO_IMPUESTO_LAX_OLD])->valor;
    }
    public static function getImpuestoNewTierra(){
        return EsysSetting::findOne(["clave" => self::PRODUCTO_IMPUESTO_TIERRA_NEW])->valor;
    }
    public static function getImpuestoOldTierra(){
        return EsysSetting::findOne(["clave" => self::PRODUCTO_IMPUESTO_TIERRA_OLD])->valor;
    }

    public static function getRangoFilaUno(){
        return EsysSetting::findOne(["clave" => self::RANGO_FILA_UNO])->valor;
    }

    public static function getRangoFilaDos(){
        return EsysSetting::findOne(["clave" => self::RANGO_FILA_DOS])->valor;
    }



    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }else{

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }

            return true;

        } else
            return false;
    }

}
