<?php

namespace app\models\promocion;

use Yii;
use app\models\user\User;
use yii\web\UploadedFile;
/**
 * This is the model class for table "promocion".
 *
 * @property int $id ID
 * @property string $nombre Nombre
 * @property int $fecha_inicia Fecha de inicio
 * @property int $fecha_expira Fecha que expira
 * @property int $is_code_promocional Aplica codigo promocional
 * @property string $banner_imagen Banner de la promoción
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modifcado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property PromocionDetalle[] $promocionDetalles
 */
class Promocion extends \yii\db\ActiveRecord
{

    public $promocion_detalle;
    public $promocion_img;

    const STATUS_ACTIVE     = 10;
    const STATUS_INACTIVE   = 20;
    const STATUS_CANCEL     = 1;

    const IS_GENERICA = 10;
    const IS_CODE_ON = 10;
    const IS_CODE_OF = 1;


    const TIPO_GENERAL   = 10;
    const TIPO_ESPECIAL  = 20;


    public static $tipoList = [
        self::TIPO_GENERAL   => 'General ',
        self::TIPO_ESPECIAL  => 'Especial ',
    ];


    const IS_MANUAL_ON   = 10;
    const IS_MANUAL_OFF  = 1;


    public static $manualList = [
        self::IS_MANUAL_ON   => 'Manual',
        self::IS_MANUAL_OFF  => 'Sistema ',
    ];

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Inhabilitado',
        self::STATUS_CANCEL   => 'Cancelado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promocion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre','fecha_inicia','fecha_expira'],'required'],
            [['is_manual'],'required','message' => "Debes seleccionar el tipo de promoción ( Manual / Sistema)"],
            [[ 'is_code_promocional','is_generica' ,'created_at', 'created_by', 'updated_at', 'updated_by','tipo_servicio','status','tipo'], 'integer'],
            [['banner_imagen'], 'string'],
            [['promocion_img'],'file','extensions' => 'png, jpg, jpeg'],
            [['nombre'], 'string', 'max' => 150],
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
            'nombre' => 'Nombre',
            'fecha_inicia' => 'Fecha de inicio',
            'fecha_expira' => 'Fecha que expira',
            'is_code_promocional' => 'Is Code Promocional',
            'is_generica' => 'Generica',
            'tipo' => 'Tipo',
            'banner_imagen' => 'Banner Imagen',
            'created_at' => 'Created At',
            'status' => 'Estatus',
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
    public function getPromocionDetalles()
    {
        return $this->hasMany(PromocionDetalle::className(), ['promocion_id' => 'id']);
    }

    public static function PromocionGenerica()
    {
        return self::find()->where(["is_generica" => self::IS_GENERICA ])->all();
    }

    public function uploadPromocion()
    {
        isset($this->promocion_img)  ?  $this->promocion_img->saveAs('uploads/' . time() .'-'. Yii::$app->user->identity->id . '-promocion.' . $this->promocion_img->extension) : null ;

    }
    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if (isset($this->promocion_img->extension))
                $this->banner_imagen =  time() .'-'. Yii::$app->user->identity->id. '-promocion.' . $this->promocion_img->extension;


            if ($insert) {
                $this->fecha_inicia = strtotime($this->fecha_inicia);
                $this->fecha_expira = strtotime($this->fecha_expira) + 86340;
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

        if ($insert) {
            foreach (ViewPromocion::getPromocionMexAjax($this->tipo, $this->tipo_servicio) as $key => $promocion_vigentes) {
                if ($promocion_vigentes["id"] != $this->id) {
                    $Promocion = Promocion::findOne($promocion_vigentes["id"]);
                    $Promocion->status = Promocion::STATUS_INACTIVE;
                    $Promocion->update();
                }
            }
        }

        //Subimos imagenes relacionadas a la sucursal
        $this->uploadPromocion();

    }
}
