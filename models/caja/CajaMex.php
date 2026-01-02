<?php

namespace app\models\caja;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\user\User;
use app\models\envio\Envio;
use app\models\esys\EsysListaDesplegable;
use app\models\movimiento\MovimientoPaquete;

/**
 * This is the model class for table "caja_mex".
 *
 * @property int $id ID
 * @property int $categoria_id Categoria ID
 * @property int $envio_detalle_id Envio detalle ID
 * @property string $folio Folio
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property EsysListaDesplegable $categoria
 * @property User $createdBy
 * @property EnvioDetalle $envioDetalle
 * @property User $updatedBy
 */
class CajaMex extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE     = 10;
    const STATUS_ENTREGADO  = 20;
    const STATUS_INACTIVE   = 1;

    const CLAVE_CAJA_MEX = "CAJ-";

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado / Abierto',
        self::STATUS_ENTREGADO => 'Entregado',
        self::STATUS_INACTIVE => 'Inhabilitado / Cerrado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];
    public $tracked_movimiento;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'caja_mex';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categoria_id',  'folio', 'status'], 'required'],
            [['categoria_id',  'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['folio'], 'string', 'max' => 20],
            [['peso_aprox'], 'number'],
            [['nombre'], 'string', 'max' => 50],
            [['nota'], 'string'],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['categoria_id' => 'id']],
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
            'id' => 'ID',
            'categoria_id' => 'Categoria',
            'folio' => 'Folio',
            'nombre' => 'Nombre',
            'peso_aprox' => 'Peso aproximado',
            'status' => 'Estatus',
            'categoria.singular' => 'Categoria',
            'nota' => 'Nota',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCajaDetalleMex()
    {
        return $this->hasMany(CajaDetalleMex::className(), ['caja_mex_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'categoria_id']);
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

    public static function getCajaHabilitado()
    {
        $CajaMex = self::find()->where([ "status" => self::STATUS_ACTIVE ]);
        return ArrayHelper::map($CajaMex->all(), 'id', 'nombre');
    }

    public static function getMovimientoTop($folio)
    {
        $MovimientoPaquete = MovimientoPaquete::find()->where([ "tracked" => $folio])->orderBy("id desc")->one();
        return $MovimientoPaquete ? $MovimientoPaquete->tipo_movimiento : null;
    }

    public static function  getCajaFolio($folio)
    {
        return self::find()->where(['folio' => $folio ])->one();
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($insert){
            $MovimientoPaquete = new MovimientoPaquete();
            $MovimientoPaquete->paquete_id      = $this->id;
            $MovimientoPaquete->tracked         = $this->folio;
            $MovimientoPaquete->tipo_envio      = Envio::TIPO_ENVIO_MEX;
            $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::MEX_BODEGA;
            $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_CAJA;
            $MovimientoPaquete->save();
        }
    }

}
