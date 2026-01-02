<?php
namespace app\models\sucursal;

use Yii;
use app\models\user\User;
use app\models\esys\EsysListaDesplegable;
/**
 * This is the model class for table "lista_precio_sucursal".
 *
 * @property int $id ID
 * @property int $sucursal_id Sucursal ID
 * @property int $rango_ini Rango Inicial
 * @property int $rango_fin Rango fin
 * @property float $precio_neto Precio Neto
 * @property float $precio_publico Precio publico
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 *
 * @property User $createdBy
 * @property Sucursal $sucursal
 * @property User $updatedBy
 */
class ListaPrecioSucursal extends \yii\db\ActiveRecord
{

    const ZONA_A    = 10;
    const ZONA_B    = 20;

    public static $zonaList = [
        self::ZONA_A   => 'ZONA A',
        self::ZONA_B   => 'ZONA B',
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lista_precio_sucursal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sucursal_id', 'estado_id','rango_ini', 'rango_fin', 'precio_neto', 'precio_publico'], 'required'],
            [['sucursal_id', 'rango_ini', 'rango_fin', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['precio_neto', 'precio_publico'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_id' => 'id']],
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
            'sucursal_id' => 'Sucursal ID',
            'estado_id' => 'Estado',
            'rango_ini' => 'Rango INICIAL',
            'rango_fin' => 'Rango FINAL',
            'precio_neto' => 'PRECIO PUEBLA EXPRESS',
            'precio_publico' => 'PRECIO PUBLICO',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }


    //------------------------------------------------------------------------------------------------//
    // RELACIONES
    //------------------------------------------------------------------------------------------------//
    public function getEstado()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id_2' => 'estado_id'])->where(['label' => 'crm_estado']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Gets query for [[Sucursal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /*public static function getZona($sucursal_id,$total_lista_precio,$precio_id =  false)
    {
        if ($precio_id) {
            $precio = self::findOne($precio_id);
            return $precio->precio_neto == $total_lista_precio ? self::ZONA_A : self::ZONA_B );
        }
        return false;
    }*/

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
