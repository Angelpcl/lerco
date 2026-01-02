<?php

namespace app\models\producto;

use Yii;
use app\models\user\User;
use app\models\esys\EsysListaDesplegable;

/**
 * This is the model class for table "producto".
 *
 * @property int $id Id
 * @property int $categoria_id Categoria ID
 * @property int $unidad_medida_id Unidad de medida ID
 * @property string $nombre Nombre
 * @property int $tipo_servicio Tipo de servicio
 * @property string $nota Nota
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property EsysListaDesplegable $tipoVolumen
 * @property EsysListaDesplegable $unidadMedida
 * @property User $updatedBy
 * @property ProductoDetalle[] $productoDetalles
 */
class Producto extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 1;

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Inhabilitado',
    ];

    const TIPO_USADO   = 10;
    const TIPO_NUEVO = 20;

    public static $tipoList = [
        self::TIPO_USADO   => 'Usado',
        self::TIPO_NUEVO => 'Nuevo',
    ];


    const IS_IMPUESTO_ON   = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'producto';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categoria_id', 'unidad_medida_id', 'tipo_servicio'], 'required'],
            [['categoria_id', 'unidad_medida_id', 'tipo_servicio',  'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['nota'], 'string'],
            [['nombre'], 'string', 'max' => 150],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['categoria_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['unidad_medida_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['unidad_medida_id' => 'id']],
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
            'unidad_medida_id' => 'Unidad Medida',
            'nombre' => 'Nombre',
            'tipo_servicio' => 'Tipo Servicio',
            'nota' => 'Nota',
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
    public function getUnidadMedida()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'unidad_medida_id']);
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


    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

    }

    public function afterDelete()
    {
        parent::afterDelete();
    }
}
