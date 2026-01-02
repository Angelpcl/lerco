<?php

namespace app\models\cliente;

use Yii;
use app\models\user\User;
use app\models\promocion\Promocion;

/**
 * This is the model class for table "cliente_codigo_promocion".
 *
 * @property int $id Id
 * @property int $promocion_id Promocion ID
 * @property int $cliente_id Cliente ID
 * @property string $clave Clave
 * @property int $tipo Tipo
 * @property int $required_autorizacion Requiere autorización
 * @property int $requiered_libras Libras requeridas
 * @property double $descuento Descuento
 * @property int $fecha_rango_ini Fecha inicial
 * @property int $fecha_rango_fin Fecha fin
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property Cliente $cliente
 * @property User $createdBy
 * @property Promocion $promocion
 * @property User $updatedBy
 */
class ClienteCodigoPromocion extends \yii\db\ActiveRecord
{


    const STATUS_ACTIVE         = 10;
    const STATUS_PROGESO        = 20;
    const STATUS_INACTIVE       = 1;
    const STATUS_USADO          = 11;
    const STATUS_NO_AUTORIZADO  = 21;

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_PROGESO  => 'Progreso',
        self::STATUS_INACTIVE => 'Inhabilitado',
        self::STATUS_USADO          => 'Usado / Utilizado',
        self::STATUS_NO_AUTORIZADO  => 'No autorizado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    const TIPO_BASIC    = 10;
    const TIPO_ESPECIAL = 20;
    const TIPO_SUCURSAL = 30;

    const CONDONACION_LIBRA     = 20;
    const CONDONACION_DINERO    = 10;

    public static $condonacionList = [
        self::CONDONACION_DINERO => 'Efectivo',
        self::CONDONACION_LIBRA   => 'Libras (lb)',
    ];


    public static $tipoList = [
        self::TIPO_BASIC   => 'Basico',
        self::TIPO_ESPECIAL => 'Especial',
        self::TIPO_SUCURSAL => 'Sucursal Especial',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

      public static $statusAlertList = [
        self::STATUS_ACTIVE   => 'purple',
        self::STATUS_PROGESO  => 'warning',
        self::STATUS_INACTIVE => 'danger',
        self::STATUS_USADO          => 'mint',
        self::STATUS_NO_AUTORIZADO  => 'danger',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente_codigo_promocion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promocion_id', 'cliente_id', 'tipo_condonacion','tipo',  'requiered_libras', 'fecha_rango_ini', 'fecha_rango_fin', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['cliente_id', 'tipo', 'status'], 'required'],
            [['descuento'], 'number'],
            [['clave'], 'string', 'max' => 8],
            [['nota'], 'string'],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['promocion_id'], 'exist', 'skipOnError' => true, 'targetClass' => Promocion::className(), 'targetAttribute' => ['promocion_id' => 'id']],
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
            'promocion_id' => 'Promocion ID',
            'cliente_id' => 'Cliente ID',
            'clave' => 'Clave',
            'tipo' => 'Tipo',
            'tipo_condonacion' => 'Tipo condonación',
            'requiered_libras' => 'Requiered Libras',
            'descuento' => 'Descuento',
            'fecha_rango_ini' => 'Fecha Rango Ini',
            'fecha_rango_fin' => 'Fecha Rango Fin',
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
    public function getCliente()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_id']);
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
    public function getPromocion()
    {
        return $this->hasOne(Promocion::className(), ['id' => 'promocion_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
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
