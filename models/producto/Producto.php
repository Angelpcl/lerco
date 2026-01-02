<?php

namespace app\models\producto;

use Yii;
use yii\db\Expression;
use app\models\user\User;
use yii\helpers\ArrayHelper;
use app\models\esys\EsysListaDesplegable;
use app\models\pais\PaisesLatam;
use app\models\sucursal\Sucursal;

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

    const TIPO_PRODUCTO     = 10;
    const TIPO_CAJA         = 20;
    const TIPO_CAJA_SIN_LIMITE         = 30;

    public static $tipoList = [
        self::TIPO_USADO   => 'Usado',
        self::TIPO_NUEVO => 'Nuevo',
    ];

    public static $tipoProductoList = [
        self::TIPO_PRODUCTO            => 'PRODUCTO',
        self::TIPO_CAJA                => 'CAJA',
        self::TIPO_CAJA_SIN_LIMITE     => 'CAJA SIN LIMITE',
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
            [['categoria_id', 'unidad_medida_id', 'tipo_servicio', 'is_impuesto', 'required_min', 'intervalo', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'is_producto', 'sucursal_id'], 'integer'],
            [['nota'], 'string'],
            [['costo_extra', 'impuesto_old', 'costo_suc', 'pais_id'], 'number'],
            [['costo_total', 'is_caja_sin_limite_id','costo_libra'], 'number'],
            [['nombre'], 'string', 'max' => 150],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['categoria_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['unidad_medida_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['unidad_medida_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],

            // Regla de validación condicional
            [['pais_id'], 'required', 'when' => function ($model) {
                return $model->is_producto == self::TIPO_CAJA_SIN_LIMITE;
            }, 'whenClient' => "function (attribute, value) {
            return $('#producto-is_producto').val() == '" . self::TIPO_CAJA_SIN_LIMITE . "';}"],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'costo_libra' => 'Costo Libra',
            'pais_id' => 'País',
            'costo_suc' => ' TOTAL A COBRAR  EN SUCURSAL',
            'id' => 'ID',
            'categoria_id' => 'Categoria',
            'unidad_medida_id' => 'Unidad Medida',
            'nombre' => 'Nombre',
            'tipo_servicio' => 'Tipo Servicio',
            'is_producto' => 'TIPO',
            'sucursal_id' => 'SUCURSAL',
            'costo_total' => 'TOTAL A COBRAR (POR UNIDAD)',
            'nota' => 'Nota',
            'is_impuesto' => 'Is Impuesto',
            'costo_extra' => 'Costo Extra',
            'required_min' => 'Required Min',
            'impuesto_old' => 'Impuesto Old',
            'intervalo' => 'Intervalo',
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

    public function getCajaSinLimite()
    {
        return $this->hasOne(CajaSinLimite::className(), ['id' => 'is_caja_sin_limite_id']);
    }
    public function getPais()
    {
        return $this->hasOne(PaisesLatam::className(), ['id' => 'pais_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
    }

    public static function getCaja($tipo = null)
    {
        $data = self::find()->where(["sucursal_id" => Yii::$app->user->identity->sucursal_id])
            ->orderBy('nombre');

        if ($tipo) {
            $data->andWhere(["is_producto" => $tipo]);
        }

        $data = $data->all();


        $response = [];

        foreach ($data as $model) {
            $nombre = $model->nombre;

            $nombre .= $model->pais ? " [" . $model->pais->nombreCompleto . "]" : "";
            if ($model->is_caja_sin_limite_id) {

                $modelCaja = CajaSinLimite::findOne($model->is_caja_sin_limite_id);
                $nombre .= " de " . $modelCaja->ancho . " * " . $modelCaja->alto . " * " . $modelCaja->largo;
                $nombre .= " [ PRECIO POR UNIDAD: $" . $modelCaja->costo_cli . "] ";
            }
            #$data[$key]['nombre'] = $value['nombre'].'[ PRECIO POR UNIDAD: $'. $value['costo_total']. ']';
            $nombre = strtoupper($nombre);
            $response[$model->id] = $nombre;
        }
        return $response;

        $model = self::find()
            ->select([
                'id',
                new Expression("CONCAT_WS(' ', `nombre`, '[ PRECIO POR UNIDAD: $', `costo_total`, ']') as nombre"),
            ])
            ->andWhere(["sucursal_id" => Yii::$app->user->identity->sucursal_id])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', 'nombre');
    }

    public static function getCajaSinLimitePais($tipo = null,$pais_id=null)
    {
        $data = self::find()//->where(["sucursal_id" => Yii::$app->user->identity->sucursal_id])
            ->orderBy('nombre');

        if ($tipo) {
            $data->andWhere(["is_producto" => $tipo]);
        }
        if ($pais_id) {
            $data->andWhere(["pais_id" => $pais_id]);
        }

        $data = $data->all();


        $response = [];

        foreach ($data as $model) {
            $nombre = $model->nombre;

            $nombre .= $model->pais ? " [" . $model->pais->nombreCompleto . "]" : "";
            if ($model->is_caja_sin_limite_id) {

                $modelCaja = CajaSinLimite::findOne($model->is_caja_sin_limite_id);
                $nombre .= " de " . $modelCaja->ancho . " * " . $modelCaja->alto . " * " . $modelCaja->largo;
                $nombre .= " [ PRECIO POR UNIDAD: $" . $modelCaja->costo_cli . "] ";
            }
            #$data[$key]['nombre'] = $value['nombre'].'[ PRECIO POR UNIDAD: $'. $value['costo_total']. ']';
            $nombre = strtoupper($nombre);
            $response[$model->id] = $nombre;
        }
        return $response;

        $model = self::find()
            ->select([
                'id',
                new Expression("CONCAT_WS(' ', `nombre`, '[ PRECIO POR UNIDAD: $', `costo_total`, ']') as nombre"),
            ])
            //->andWhere(["sucursal_id" => Yii::$app->user->identity->sucursal_id])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', 'nombre');
    }

    public static function getProductoFull($id)
    {
        $response = [];
        $data = self::findOne($id);
        $response = [
            'producto' => $data,
        ];
        if($data->is_caja_sin_limite_id) {
            $modelCaja = CajaSinLimite::findOne($data->is_caja_sin_limite_id);
            $response['caja'] = $modelCaja;
        }
        else{
            $response['caja'] = null;
        }
        return $response;
        

    }

    public function getNombreTipo(){
        $nombre = $this->nombre;
        switch ($this->is_producto) {
            case 30:
                $nombre.= " DE". $this->cajaSinLimite->ancho . " * " . $this->cajaSinLimite->alto . " * " . $this->cajaSinLimite->largo;;
                break;
            
            default:
                # code...
                break;
        }
        $nombre = $this->nombre;
        return strtoupper($nombre);
    }


    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            } else {

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
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
