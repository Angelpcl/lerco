<?php

namespace app\models\reparto;

use Yii;
use app\models\esys\EsysListaDesplegable;
use app\models\user\User;
use app\models\Esys;
use app\models\viaje\Viaje;

/**
 * This is the model class for table "reparto".
 *
 * @property int $id id
 * @property int $fecha_salida
 * @property int $num_camion_id Numero de camion
 * @property int $chofer_id
 * @property int $status
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property EsysListaDesplegable $chofer
 * @property User $createdBy
 * @property EsysListaDesplegable $numCamion
 * @property User $updatedBy
 * @property RepartoRuta[] $repartoRutas
 */
class Reparto extends \yii\db\ActiveRecord
{


    const STATUS_ACTIVE     = 10;
    const STATUS_CERRADO    = 20;
    const STATUS_TERMINADO  = 30;
    const STATUS_CANCEL     = 2;
    const STATUS_INACTIVE   = 1;

    public static $statusList = [
        self::STATUS_ACTIVE     => 'Habilitado',
        self::STATUS_CERRADO    => 'Cerrado / Enviado',
        self::STATUS_TERMINADO  => 'Terminado / Concluido',
        self::STATUS_CANCEL     => 'Cancelado',
        self::STATUS_INACTIVE   => 'Inhabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];


    public $fila_ruta;
    public $reparto_fila;
    public $fila_paquete;
    public $recoleccion_ruta;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_salida', 'status'], 'required'],
            [['status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['nota'], 'string'],
            [['viaje_id'], 'exist', 'skipOnError' => true, 'targetClass' => Viaje::className(), 'targetAttribute' => ['viaje_id' => 'id']],
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
            'viaje_id' => 'Viaje',
            'fecha_salida' => 'Fecha Salida',
            'status' => 'Status',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepartoFila()
    {
        return $this->hasMany(RepartoFila::className(), ['reparto_id' => 'id']);
    }


    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            $this->fecha_salida = Esys::stringToTimeUnix($this->fecha_salida);
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
