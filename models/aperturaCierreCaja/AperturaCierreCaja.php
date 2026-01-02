<?php
namespace app\models\aperturaCierreCaja;

use Yii;
use app\models\user\User;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\envio\Envio;
/**
 * This is the model class for table "apertura_cierre_caja".
 *
 * @property int $id ID
 * @property int $fecha_apertura Fecha apertura
 * @property double $cantidad_apertura Cantidad apertura
 * @property int $fecha_cierre Fecha de cierre
 * @property double $cantidad_cierre Cantidad cierre
 * @property string $comentario_apertura Nota
 * @property string $comentario_cierre Comentario cierre
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class AperturaCierreCaja extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apertura_cierre_caja';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_apertura', 'cantidad_apertura' ], 'required'],
            [['fecha_apertura', 'fecha_cierre', 'created_at', 'created_by', 'updated_at', 'updated_by','bill_100','bill_50','bill_20','bill_10','bill_5','bill_2','bill_1'], 'integer'],
            [['cantidad_apertura', 'cantidad_cierre','change','pendiente'], 'number'],
            [['comentario_apertura', 'comentario_cierre'], 'string'],
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
            'fecha_apertura' => 'Fecha Apertura',
            'cantidad_apertura' => 'Cantidad Apertura',
            'fecha_cierre' => 'Fecha Cierre',
            'cantidad_cierre' => 'Cantidad Cierre',
            'bill_100' => 'Billete de  100',
            'bill_50' => 'Billete de  50',
            'bill_20' => 'Billete de  20',
            'bill_10' => 'Billete de  10',
            'bill_5' => 'Billete de  5',
            'bill_2' => 'Billete de  2',
            'bill_1' => 'Billete de  1',
            'change' => 'Change / Cambio',
            'comentario_apertura' => 'Comentario Apertura',
            'comentario_cierre' => 'Comentario Cierre',
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

    public function getCobroRelaciandos()
    {

        return  $CobroRembolsoEnvio = CobroRembolsoEnvio::find()
                ->innerJoin("envio","cobro_rembolso_envio.envio_id = envio.id")
                ->andWhere(['IS','`cobro_rembolso_envio`.is_cobro_mex' , new \yii\db\Expression('null') ])
                ->andWhere(["cobro_rembolso_envio.tipo" => CobroRembolsoEnvio::TIPO_COBRO ])
                ->andWhere(["<>","envio.status", Envio::STATUS_CANCELADO ])
                ->andWhere(["between","cobro_rembolso_envio.created_at",$this->fecha_apertura,$this->fecha_cierre ? $this->fecha_cierre : time()])->orderBy("cobro_rembolso_envio.id desc")->all();


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
