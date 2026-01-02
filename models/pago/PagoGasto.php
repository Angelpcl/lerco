<?php
namespace app\models\pago;

use Yii;
use app\models\esys\EsysListaDesplegable;
use app\models\user\User;

/**
 * This is the model class for table "pago_gasto".
 *
 * @property int $id ID
 * @property int $concepto_id Concepto
 * @property double $monto Monto
 * @property int $fecha_pago Fecha pago / gasto
 * @property string $nota Nota
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property EsysListaDesplegable $concepto
 * @property User $createdBy
 */
class PagoGasto extends \yii\db\ActiveRecord
{
    const TIPO_MX   = 10;
    const TIPO_USA = 1;

    public static $tipoList = [
        self::TIPO_MX   => 'Mexico',
        self::TIPO_USA => 'USA',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pago_gasto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['concepto_id', 'monto', 'fecha_pago'], 'required'],
            [['concepto_id',  'created_at', 'created_by'], 'integer'],
            [['monto'], 'number'],
            [['fecha_pago'], 'safe'],
            [['nota'], 'string'],
            [['concepto_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['concepto_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'concepto_id' => 'Concepto',
            'monto' => 'Monto',
            'fecha_pago' => 'Fecha Pago',
            'nota' => 'Nota',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConcepto()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'concepto_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
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

            }
            return true;

        } else
            return false;
    }

}
