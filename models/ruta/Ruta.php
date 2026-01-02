<?php

namespace app\models\ruta;

use Yii;
use app\models\user\User;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "ruta".
 *
 * @property int $id ID
 * @property string $nombre Nombre
 * @property int $tipo
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property RutaSucursal[] $rutaSucursals
 * @property Sucursal[] $sucursals
 */
class Ruta extends \yii\db\ActiveRecord
{

	const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 1;

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Inhabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    const TIPO_BASE = 10;
    const TIPO_FORANEA = 20;



    public static $tipoList = [
        self::TIPO_BASE   => 'Ruta base',
        self::TIPO_FORANEA => 'Ruta foranea',
    ];

    public $ruta_sucursal;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ruta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'tipo', 'status'], 'required'],
            ['nombre', 'unique', 'message' => "Este nombre de la ruta ya ha sido tomado."],
            [['tipo', 'status', 'created_at','orden' ,'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['nombre'], 'string', 'max' => 100],
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
            'nombre' => 'Nombre',
            'tipo' => 'Tipo',
            'orden' => 'Orden',
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

    public static function getItems()
    {
        $model = self::find()
            ->select(['id', 'nombre'])
            ->andWhere(["tipo" => self::TIPO_BASE ])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', 'nombre');
    }

    public static function getItemsAll()
    {
        $model = self::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', 'nombre');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRutaSucursals()
    {
        return $this->hasMany(RutaSucursal::className(), ['ruta_id' => 'id'])->orderBy(['orden'=> SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSucursals()
    {
        return $this->hasMany(Sucursal::className(), ['id' => 'sucursal_id'])->viaTable('ruta_sucursal', ['ruta_id' => 'id']);
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
