<?php

namespace app\models\sucursal;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\web\Response;
use app\models\user\User;
use app\models\esys\EsysDireccion;
use app\models\ruta\RutaSucursal;
use app\models\ruta\Ruta;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;
use app\models\cliente\Cliente;
use app\models\movimiento\MovimientoPaquete;
/**
 * This is the model class for table "Sucursal".
 *
 * @property int $id
 * @property string $nombre
 * @property string $encargado
 * @property string $email
 * @property string $direccion
 * @property string $colonia
 * @property string $cp
 * @property string $tels
 * @property int $estado_id
 * @property int $municipio_id
 * @property string $lat
 * @property string $lng
 * @property int $on_web
 * @property int $factura
 * @property string $facturacion_serie
 *
 * @property Clientes-vendedores[] $clientes-vendedores
 * @property Compras[] $compras
 * @property Existencias[] $existencias
 * @property Productos[] $productos
 * @property Pedidos[] $pedidos
 * @property RegistroDeMovimientos[] $registroDeMovimientos
 * @property Traspasos[] $traspasos
 */
class Sucursal extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 1;

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Inhabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    const TIPO_BODEGA = 1;
    const TIPO_OFICINA = 2;
    const TIPO_ASOCIADA = 3;

    const ORIGEN_USA = 1;
    const ORIGEN_MX = 2;


    public static $tipoList = [
        self::TIPO_BODEGA   => 'Bodega',
        self::TIPO_OFICINA => 'Oficina / Sucursal',
        self::TIPO_ASOCIADA => 'Asociada',
    ];

    public static $origenList = [
        self::ORIGEN_MX   => 'México',
        self::ORIGEN_USA  => 'United States',
    ];


    public $dir_obj;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sucursal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['is_reenvio'], 'integer'],
            [['costo_libra','costo_libra_aire'], 'number'],
            [['clave'], 'string', 'max' => 3, 'min' => 3],
            [['nombre'], 'string', 'max' => 200],
            [['telefono'], 'string', 'max' => 100],
            [['telefono_movil'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 50],
            [['informacion', 'comentarios'], 'string'],
            [['nombre'], 'unique'],
            [['origen','tipo'],'integer'],
            [['clave'],  'unique'],
            [['clave','nombre'],'required'],
            [['encargado_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['encargado_id' => 'id']],
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
            'clave' => 'Clave',
            'nombre' => 'Nombre de sucursal',
            'encargado_id' => 'Encargado',
            'email' => 'Email',
            'tipo' => 'Tipo de sucursal',
            'origen' => 'Origen',
            'costo_libra' => 'Costo libra TIERRA',
            'costo_libra_aire' => 'Costo libra AIRE',
            'telefono' => 'Telefono',
            'telefono_movil' => 'Telefono movil',
            'status' => 'Estado',
            'is_reenvio' => 'Is reenvio',

        ];
    }

      /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvios()
    {
        return $this->hasMany(Envio::className(), ['sucursal_emisor_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvioDetalles()
    {
        if (Yii::$app->user->can('agenteVenta'))
            return EnvioDetalle::find()
                ->innerJoin("envio","envio_detalle.envio_id = envio.id")
                ->innerJoin("cliente", "envio.cliente_emisor_id = cliente.id")
                ->innerJoin("sucursal","envio_detalle.sucursal_receptor_id = sucursal.id")
                ->andWhere(['cliente.asignado_id' =>  Yii::$app->user->identity->id ])
                ->andWhere(['cliente.status' =>  Cliente::STATUS_ACTIVE ])
                ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ])
                ->andWhere(["<>","envio_detalle.status",EnvioDetalle::STATUS_CANCELADO ])
                ->andWhere(['sucursal_receptor_id' => $this->id] )->all();
        else
            return EnvioDetalle::find()
                    ->innerJoin("envio","envio_detalle.envio_id = envio.id")
                    ->innerJoin("cliente", "envio.cliente_emisor_id = cliente.id")
                    ->innerJoin("sucursal","envio_detalle.sucursal_receptor_id = sucursal.id")
                    ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                    ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ])
                    ->andWhere(["<>","envio_detalle.status",EnvioDetalle::STATUS_CANCELADO ])
                    ->andWhere(['sucursal_receptor_id' => $this->id ])->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepartoRecoleccions()
    {
        return $this->hasMany(RepartoRecoleccion::className(), ['sucursal_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRutaSucursals()
    {
        return $this->hasOne(RutaSucursal::className(), ['sucursal_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRutas()
    {
        return $this->hasMany(Ruta::className(), ['id' => 'ruta_id'])->viaTable('ruta_sucursal', ['sucursal_id' => 'id']);
    }


    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getListaPrecioLibraMx()
    {
        return $this->hasMany(ListaPrecioMx::className(), ['sucursal_recibe_id' => 'id'])
        ->andOnCondition(['tipo' => ListaPrecioMx::TIPO_LIBRA ])->orderBy([ 'lista_precio_mx.sucursal_recibe_id' => SORT_DESC ]);
    }

    public function getListaPrecioImpuestoMx()
    {
        return $this->hasMany(ListaPrecioMx::className(), ['sucursal_recibe_id' => 'id'])
        ->andOnCondition(['tipo' => ListaPrecioMx::TIPO_IMPUESTO ])->orderBy([ 'lista_precio_mx.sucursal_recibe_id' => SORT_DESC ]);
    }

    public function getEncargadoSucursal()
    {
        return $this->hasOne(User::className(), ['id' => 'encargado_id']);
    }

    public function getNumpzEntregado()
    {
        if (Yii::$app->user->can('agenteVenta'))
            return   EnvioDetalle::find()
                        ->innerJoin("envio","envio_detalle.envio_id = envio.id")
                        ->innerJoin("cliente", "envio.cliente_emisor_id = cliente.id")
                        ->innerJoin("sucursal","envio_detalle.sucursal_receptor_id = sucursal.id")
                        ->andWhere(['cliente.asignado_id' =>  Yii::$app->user->identity->id ])
                        ->andWhere(['cliente.status' =>  Cliente::STATUS_ACTIVE ])
                        ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                        ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ])
                        ->andWhere(["<>","envio_detalle.status",EnvioDetalle::STATUS_CANCELADO ])
                        ->andWhere(['sucursal_receptor_id' => $this->id] )->sum("cantidad");
        else
            return EnvioDetalle::find()
                    ->innerJoin("envio","envio_detalle.envio_id = envio.id")
                    ->innerJoin("cliente", "envio.cliente_emisor_id = cliente.id")
                    ->innerJoin("sucursal","envio_detalle.sucursal_receptor_id = sucursal.id")
                    ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                    ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ])
                    ->andWhere(["<>","envio_detalle.status",EnvioDetalle::STATUS_CANCELADO ])
                    ->andWhere(['sucursal_receptor_id' => $this->id ])->sum("cantidad");
    }



    public static function getItems()
    {
        $model = self::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', 'nombre');
    }

    public static function getItemsMexico()
    {
        $model = self::find()
            ->select(['id', 'nombre'])
            ->andWhere(["origen" => self::ORIGEN_MX ])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', 'nombre');
    }

    public static function getItemsAll()
    {
        $model = self::find()
            ->select(['id', 'nombre'])
            //->andWhere(["origen" => self::ORIGEN_MX ])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', 'nombre');
    }

    public static function getItemsUsa()
    {
        $model = self::find()
            ->select(['id', 'nombre'])
            ->andWhere(["origen" => self::ORIGEN_USA ])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', 'nombre');
    }


    public static function getAlmacenActivo()
    {
        $model = self::find()
                    ->select(['id','nombre'])
                    ->where(['=','status',self::STATUS_ACTIVE])
                    ->orderBy('nombre');

        return ArrayHelper::map($model->all(),'id',function($value){
                return $value->nombre .' ['.$value->id.']';
        });
    }

    public function getDireccion()
    {
        return $this->hasOne(EsysDireccion::className(), ['cuenta_id' => 'id'])
            ->where(['cuenta' => EsysDireccion::CUENTA_SUCURSAL, 'tipo' => EsysDireccion::TIPO_PERSONAL]);
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
        if($insert)
            $this->dir_obj->cuenta_id = $this->id;
        // Guardar dirección
        $this->dir_obj->save();
    }
    public function afterDelete()
    {
        parent::afterDelete();

        $this->direccion->delete();
    }

}
