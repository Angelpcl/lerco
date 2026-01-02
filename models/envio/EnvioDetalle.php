<?php

namespace app\models\envio;

use Yii;
use app\models\producto\Producto;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysSetting;
use app\models\esys\EsysCambiosLog;
use app\models\movimiento\MovimientoPaquete;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysDireccion;
use app\models\cliente\Cliente;
use app\models\descarga\DescargaBodega;
use app\models\envio\DetailEnvioProduct;
use app\models\pais\PaisesLatam;

/**
 * This is the model class for table "envio_detalle".
 *
 * @property int $id Envio detalle ID
 * @property int $envio_id Envio ID
 * @property int $categoria_id Categoria ID
 * @property int $producto_id Producto ID
 * @property string $folio Folio
 * @property double $valor_declarado Valor declarado
 * @property int $cantidad Cantidad (Cajas)
 * @property int $cantidad_piezas Cantidad de piezas a enviar
 * @property double $peso Peso
 * @property double $costo_libra Costo libra
 * @property double $precio Precio
 * @property int $status Estatus
 * @property int $seguro Seguro
 * @property string $observaciones Observaciones
 *
 * @property Producto $producto
 * @property EsysListaDesplegable $categoria
 * @property Envio $envio
 */
class EnvioDetalle extends \yii\db\ActiveRecord
{

    public $unidad_medida_id;

    public $envio_detalle_array;

    const REENVIO_ON     = 10;
    const REENVIO_OFF    = 1;

    const STATUS_HABILITADO     = 10;
    const STATUS_SOLICITADO  = 2;
    const STATUS_CANCELADO  = 1;

    public static $statusList = [
        self::STATUS_HABILITADO     => 'Habilitado',
        self::STATUS_SOLICITADO  => 'Pre habilitado',
        self::STATUS_CANCELADO => 'Cancelado',

    ];

    public $CambiosLog;

    public $tracked_movimiento;

    public $dir_obj_array;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'envio_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['envio_id', 'sucursal_receptor_id', 'cliente_receptor_id', 'status'], 'required'],
            [['envio_id', 'categoria_id', 'sucursal_receptor_id', 'cliente_receptor_id', 'producto_id', 'cantidad', 'cantidad_piezas', 'status', 'seguro', 'is_costo_extraordinario'], 'integer'],
            [['valor_declarado', 'peso', 'precio_libra_actual', 'valoracion_paquete', 'costo_neto_extraordinario', 'costo_caja_unitario', 'pais_destino_id'], 'number'],
            [['observaciones'], 'string'],
            [['tracked_movimiento'], 'string'],
            [['envio_detalle_array'], 'safe'],
            [['dir_obj_array'], 'safe'],
            [['tracked'], 'string', 'max' => 20],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['categoria_id' => 'id']],
            [['cliente_receptor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_receptor_id' => 'id']],
            [['envio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Envio::className(), 'targetAttribute' => ['envio_id' => 'id']],
            [['sucursal_receptor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_receptor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'pais_destino_id' =>  'Pais Destino',
            'id' => 'Envio detalle ID',
            'envio_id' => 'Envio ID',
            'sucursal_receptor_id' => 'Sucursal Receptor ID',
            'cliente_receptor_id' => 'Cliente Receptor ID',
            'tracked' => 'Tracked',
            'is_costo_extraordinario' => '¿ Costo extraordinario ?',
            'tracked_movimiento' => 'Tracked',
            'valor_declarado' => 'Valor declarado (USD)',
            'peso' => 'Peso (Libra)',
            'cantidad' => 'N° de piezas',
            'costo_neto_extraordinario' => 'COSTO NETO',
            'cantidad_piezas' => 'N° de elementos',
            'unidad_medida_id' => 'Unidad de medida',
            'categoria_id' => 'Categoria',
            'envio.sucursalEmisor.encargadoSucursal.nombreCompleto' => 'Encargado Sucursal',
            'envio.sucursalReceptor.encargadoSucursal.nombreCompleto' => 'Encargado Sucursal',
            'producto_id' => 'Producto',
            'precio_libra_actual' => 'Precio de libra actual',
            'status' => 'Estatus',
            'seguro' => 'Seguro',
            'observaciones' => 'Observaciones',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCajaDetalleMexes()
    {
        return $this->hasMany(CajaDetalleMex::className(), ['envio_detalle_id' => 'id']);
    }

    public function getImpresionEtiquetaAll()
    {
        return $this->hasMany(ImpresionTicketCobro::className(), ['envio_detalle_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImpresionEtiqueta()
    {
        if (Yii::$app->user->can('Encargado de sucursal Lax - Tierra')) {
            $ImpresionTicketCobro = ImpresionTicketCobro::find()->andWhere(['envio_detalle_id' => $this->id])->andWhere(['user_id' => Yii::$app->user->identity->id])->orderBy('id desc')->all();

            if (count($ImpresionTicketCobro) >= 1) {
                if ($ImpresionTicketCobro[0]->count == $this->cantidad)
                    return 20;
                else
                    return 20;
            } else
                return 10;
        }

        return 10;
    }


    public function getPais()
    {
        return $this->hasOne(PaisesLatam::className(), ['id' => 'pais_destino_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClienteReceptor()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_receptor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSucursalReceptor()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_receptor_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Producto::className(), ['id' => 'producto_id']);
    }

    public function getDetalleProducto()
    {

        return $this->hasOne(DetailEnvioProduct::className(), ['detalle_envio_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'categoria_id']);
    }

    public static function getMovimientoTop($folio)
    {
        $MovimientoPaquete = MovimientoPaquete::find()->where(["tracked" => $folio])->orderBy("id desc")->one();
        return $MovimientoPaquete ? $MovimientoPaquete->tipo_movimiento : null;
    }

    public function getDireccion()
    {
        return $this->hasOne(EsysDireccion::className(), ['cuenta_id' => 'id'])
            ->where(['cuenta' => EsysDireccion::CUENTA_REENVIO_PAQUETE, 'tipo' => EsysDireccion::TIPO_PERSONAL]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvio()
    {
        return $this->hasOne(Envio::className(), ['id' => 'envio_id']);
    }

    public static function  getEnvioDetalleFolio($folio)
    {
        return self::find()->where(['tracked' => $folio])->one();
    }

    public function saveEnvioDetalle($envio_id, $folio, $is_movil = false)
    {
        $envio_detalles_array = json_decode($this->envio_detalle_array);


        if ($envio_detalles_array) {
            $Envio = Envio::findOne($envio_id);
            $Envio->dir_obj   = $Envio->direccion;
            $this->CambiosLog = new EsysCambiosLog((new Envio));

            $count =  EnvioDetalle::find()->where(["envio_id" => $Envio->id])->count() > 0 ? EnvioDetalle::find()->where(["envio_id" => $Envio->id])->count() + 1 : 1;


            foreach ($envio_detalles_array as $key => $env_detalle) {
                //$env_detalle->origen  = 1;
                if ($env_detalle->origen  ==  1) {

                    $EnvioDetalle = new EnvioDetalle();
                    $EnvioDetalle->envio_id             = $envio_id;
                    $EnvioDetalle->pais_destino_id = $Envio->pais_destino_id;

                    $EnvioDetalle->tracked              = $folio . "-" . str_pad($count, 2, "0", STR_PAD_LEFT);
                    $EnvioDetalle->sucursal_receptor_id =  $env_detalle->sucursal_id;
                    $mCli = Cliente::findOne($env_detalle->cliente_id);
                    $EnvioDetalle->pais_destino_id = $mCli->pais ? $mCli->pais->id : null;
                    $Envio->pais_destino_id =  $EnvioDetalle->pais_destino_id;

                    $EnvioDetalle->cliente_receptor_id  =  $env_detalle->cliente_id;
                    $EnvioDetalle->categoria_id         =  $env_detalle->categoria_id;
                    $EnvioDetalle->producto_id          = $env_detalle->producto_id;

                    $EnvioDetalle->is_reenvio           = $env_detalle->reenvio_id && $env_detalle->reenvio_id != 0  ? 10 : null;

                    $EnvioDetalle->producto_tipo        = isset($env_detalle->producto_tipo) ? $env_detalle->producto_tipo : null;
                    $EnvioDetalle->costo_caja_unitario  = isset($env_detalle->precio_caja_unitario) ? $env_detalle->precio_caja_unitario : null;
                    $EnvioDetalle->valor_declarado      = $env_detalle->valor_declarado;
                    $EnvioDetalle->cantidad             = $env_detalle->cantidad;
                    $EnvioDetalle->peso                 = $env_detalle->peso;
                    $EnvioDetalle->precio_libra_actual  = $Envio->precio_libra_actual;
                    $EnvioDetalle->seguro               = $env_detalle->seguro == true ? 1 : 0;

                    $EnvioDetalle->costo_neto_extraordinario = $env_detalle->costo_neto_extraordinario;
                    $EnvioDetalle->is_costo_extraordinario  = $env_detalle->is_costo_extraordinario == true ? 10 : 0;



                    $EnvioDetalle->costo_seguro         = $env_detalle->costo_seguro;
                    $EnvioDetalle->observaciones        = $env_detalle->observaciones;

                    $EnvioDetalle->status       = self::STATUS_HABILITADO;
                    $EnvioDetalle->save();

                    $modelDetailProducts = new DetailEnvioProduct();
                    $modelDetailProducts->detalle_envio_id = $EnvioDetalle->id;
                    $modelDetailProducts->detalle_json = json_encode($env_detalle->paquete_detalle);
                    $modelDetailProducts->save();


                    $count  = $count + 1;



                    if ($env_detalle->update == 10) {
                        $modelDetailProducts = DetailEnvioProduct::find()->where(["detalle_envio_id" => $EnvioDetalle->id])->one();
                        if ($modelDetailProducts) {
                            //$modelDetailProducts->detalle_envio_id = $EnvioDetalle->id;
                            $modelDetailProducts->detalle_json = json_encode($env_detalle->paquete_detalle);
                            $modelDetailProducts->update();
                        }


                        $this->CambiosLog->updateValue('#paquete', 'old', '********* Se agrego un nuevo paquete al envío Tracked: #' . $EnvioDetalle->tracked . ' *********');
                        $this->CambiosLog->updateValue('#paquete', 'dirty', '');
                        $this->CambiosLog->createLog($envio_id);
                        if ($Envio->status != Envio::STATUS_SOLICITADO) {
                            $Envio->status =  Envio::STATUS_HABILITADO;
                            $Envio->update();
                        }

                        //$Envio->status = $is_movil ? Envio::STATUS_SOLICITADO : Envio::STATUS_HABILITADO;

                    }
                    $Envio->save();
                } elseif ($env_detalle->origen  ==  2) {
                    //throw new \yii\web\NotFoundHttpException($this->envio_detalle_array);
                    //DetailEnvioProduct::deleteAll(["detalle_envio_id" => $EnvioDetalle->id]);
                    $mEnvio = EnvioDetalle::find()->where(["envio_id" => $Envio->id])->one();
                    DetailEnvioProduct::deleteAll(["detalle_envio_id" => $mEnvio->id]);
                    EnvioDetalle::deleteAll(['envio_id' => $envio_id]);
                    $count =  EnvioDetalle::find()->where(["envio_id" => $Envio->id])->count() > 0 ? EnvioDetalle::find()->where(["envio_id" => $Envio->id])->count() + 1 : 1;

                    $EnvioDetalle = new EnvioDetalle();
                    //$EnvioDetalle = EnvioDetalle::find()->where(["envio_id" => $envio_id])->one();
                    $EnvioDetalle->envio_id             = $envio_id;
                    $EnvioDetalle->pais_destino_id = $Envio->pais_destino_id;

                    $EnvioDetalle->tracked              = $folio . "-" . str_pad($count, 2, "0", STR_PAD_LEFT);
                    $EnvioDetalle->sucursal_receptor_id =  $env_detalle->sucursal_id;
                    $mCli = Cliente::findOne($env_detalle->cliente_id);
                    $EnvioDetalle->pais_destino_id = $mCli->pais ? $mCli->pais->id : null;
                    $Envio->pais_destino_id =  $EnvioDetalle->pais_destino_id;

                    $EnvioDetalle->cliente_receptor_id  =  $env_detalle->cliente_id;
                    $EnvioDetalle->categoria_id         =  $env_detalle->categoria_id;
                    $EnvioDetalle->producto_id          = $env_detalle->producto_id;

                    $EnvioDetalle->is_reenvio           = $env_detalle->reenvio_id && $env_detalle->reenvio_id != 0  ? 10 : null;

                    $EnvioDetalle->producto_tipo        = isset($env_detalle->producto_tipo) ? $env_detalle->producto_tipo : null;
                    $EnvioDetalle->costo_caja_unitario  = isset($env_detalle->precio_caja_unitario) ? $env_detalle->precio_caja_unitario : null;
                    $EnvioDetalle->valor_declarado      = $env_detalle->valor_declarado;
                    $EnvioDetalle->cantidad             = $env_detalle->cantidad;
                    $EnvioDetalle->peso                 = $env_detalle->peso;
                    $EnvioDetalle->precio_libra_actual  = $Envio->precio_libra_actual;
                    $EnvioDetalle->seguro               = $env_detalle->seguro == true ? 1 : 0;

                    $EnvioDetalle->costo_neto_extraordinario = $env_detalle->costo_neto_extraordinario;
                    $EnvioDetalle->is_costo_extraordinario  = $env_detalle->is_costo_extraordinario == true ? 10 : 0;



                    $EnvioDetalle->costo_seguro         = $env_detalle->costo_seguro;
                    $EnvioDetalle->observaciones        = $env_detalle->observaciones;

                    $EnvioDetalle->status       = self::STATUS_HABILITADO;
                    $EnvioDetalle->save();


                    $modelDetailProducts = new DetailEnvioProduct();
                    $modelDetailProducts->detalle_envio_id = $EnvioDetalle->id;
                    $modelDetailProducts->detalle_json = json_encode($env_detalle->paquete_detalle);
                    $modelDetailProducts->save();


                    $count  = $count + 1;



                    if ($env_detalle->update == 10) {
                        $modelDetailProducts = DetailEnvioProduct::find()->where(["detalle_envio_id" => $EnvioDetalle->id])->one();
                        if ($modelDetailProducts) {
                            //$modelDetailProducts->detalle_envio_id = $EnvioDetalle->id;
                            $modelDetailProducts->detalle_json = json_encode($env_detalle->paquete_detalle);
                            $modelDetailProducts->update();
                        }


                        $this->CambiosLog->updateValue('#paquete', 'old', '********* Se agrego un nuevo paquete al envío Tracked: #' . $EnvioDetalle->tracked . ' *********');
                        $this->CambiosLog->updateValue('#paquete', 'dirty', '');
                        $this->CambiosLog->createLog($envio_id);
                        if ($Envio->status != Envio::STATUS_SOLICITADO) {
                            $Envio->status =  Envio::STATUS_HABILITADO;
                            $Envio->update();
                        }

                        //$Envio->status = $is_movil ? Envio::STATUS_SOLICITADO : Envio::STATUS_HABILITADO;

                    }
                    $Envio->save();



                    //$EnvioDetalle = self::findOne($env_detalle->paquete_id);
                    //throw new \yii\web\NotFoundHttpException(json_encode($EnvioDetalle ));


                    if ($EnvioDetalle->cantidad  != $env_detalle->cantidad) {

                        $count_ini  = $EnvioDetalle->cantidad + 1;
                        for ($i = $count_ini; $i <= $env_detalle->cantidad; $i++) {
                            $MovimientoPaquete = new MovimientoPaquete();
                            $MovimientoPaquete->paquete_id      = $EnvioDetalle->id;
                            $MovimientoPaquete->tracked         = $EnvioDetalle->tracked . "/" . $i;
                            $MovimientoPaquete->tipo_envio      = $EnvioDetalle->envio->tipo_envio;
                            $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_SUCURSAL;
                            $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                            $MovimientoPaquete->save();
                        }

                        if ($env_detalle->cantidad < $EnvioDetalle->cantidad) {
                            for ($i = $env_detalle->cantidad + 1; $i <= $EnvioDetalle->cantidad; $i++) {
                                $MovimientoPaquete = new MovimientoPaquete();
                                $MovimientoPaquete->paquete_id      = $EnvioDetalle->id;
                                $MovimientoPaquete->tracked         = $EnvioDetalle->tracked . "/" . $i;
                                $MovimientoPaquete->tipo_envio      = $EnvioDetalle->envio->tipo_envio;
                                $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_CANCEL;
                                $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                                $MovimientoPaquete->save();
                            }
                        }

                        $this->CambiosLog->updateValue('#paquete', 'old', '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' N° Elementos: ' . $EnvioDetalle->cantidad);
                        $this->CambiosLog->updateValue('#paquete', 'dirty', $env_detalle->cantidad);
                        $EnvioDetalle->cantidad = $env_detalle->cantidad;
                        $EnvioDetalle->update();
                        $this->CambiosLog->createLog($envio_id);
                    }

                    $env_detalle->seguro = $env_detalle->seguro == true ? 1 : 0;

                    if ($EnvioDetalle->seguro  !=  $env_detalle->seguro) {

                        $this->CambiosLog->updateValue('#paquete', 'old',  $EnvioDetalle->seguro == 1  ? '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' Seguro: Aplica seguro' : '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' Seguro: N/A seguro');

                        $this->CambiosLog->updateValue('#paquete', 'dirty', $env_detalle->seguro == 1  ? 'Aplica seguro' : 'N/A seguro');
                        $EnvioDetalle->seguro = $env_detalle->seguro;
                        $EnvioDetalle->update();
                        $this->CambiosLog->createLog($envio_id);
                    }
                    if ($env_detalle->status == 1) {
                        $this->CambiosLog->updateValue('#paquete', 'old', '********* Se cancelo paquete con Tracked: #' . $EnvioDetalle->tracked . ' y producto: ' . $EnvioDetalle->producto->nombre . '*********');
                        $this->CambiosLog->updateValue('#paquete', 'dirty', '');

                        for ($i = 1; $i <= $env_detalle->cantidad; $i++) {
                            $MovimientoPaquete = new MovimientoPaquete();
                            $MovimientoPaquete->paquete_id      = $EnvioDetalle->id;
                            $MovimientoPaquete->tracked         = $EnvioDetalle->tracked . "/" . $i;
                            $MovimientoPaquete->tipo_envio      = $EnvioDetalle->envio->tipo_envio;
                            $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_CANCEL;
                            $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                            $MovimientoPaquete->save();
                        }

                        $EnvioDetalle->status = self::STATUS_CANCELADO;
                        $EnvioDetalle->update();
                        $this->CambiosLog->createLog($envio_id);
                        if ($Envio->status != Envio::STATUS_SOLICITADO) {
                            $Envio->status = Envio::STATUS_HABILITADO;
                            $Envio->update();
                        }
                    } else {
                        if ($EnvioDetalle->valor_declarado  != $env_detalle->valor_declarado) {
                            $this->CambiosLog->updateValue('#paquete', 'old', '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' Valor declarado: ' . $EnvioDetalle->valor_declarado);
                            $this->CambiosLog->updateValue('#paquete', 'dirty', $env_detalle->valor_declarado);
                            $EnvioDetalle->valor_declarado = $env_detalle->valor_declarado;
                            $EnvioDetalle->update();
                            $this->CambiosLog->createLog($envio_id);
                        }
                        if ($EnvioDetalle->costo_seguro  != $env_detalle->costo_seguro) {
                            $this->CambiosLog->updateValue('#paquete', 'old', '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' Costo de seguro: ' . $EnvioDetalle->costo_seguro);
                            $this->CambiosLog->updateValue('#paquete', 'dirty', $env_detalle->costo_seguro);
                            $EnvioDetalle->costo_seguro = $env_detalle->costo_seguro;
                            $EnvioDetalle->update();
                            $this->CambiosLog->createLog($envio_id);
                        }
                    }
                    if ($EnvioDetalle->peso  !=  $env_detalle->peso) {

                        $this->CambiosLog->updateValue('#paquete', 'old', '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' Peso : ' . $EnvioDetalle->peso);
                        $this->CambiosLog->updateValue('#paquete', 'dirty', $env_detalle->peso);
                        $EnvioDetalle->peso = $env_detalle->peso;
                        $EnvioDetalle->update();
                        $this->CambiosLog->createLog($envio_id);
                    }

                    $env_detalle_reenvio_id = $env_detalle->reenvio_id &&  $env_detalle->reenvio_id != 0  ? 10 : 0;

                    if ($EnvioDetalle->is_reenvio  != $env_detalle_reenvio_id) {
                        $this->CambiosLog->updateValue('#paquete', 'old', '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' Reenvío  : ' . ($env_detalle_reenvio_id == 10 ? 'NO' : 'SI'));
                        $this->CambiosLog->updateValue('#paquete', 'dirty', $env_detalle_reenvio_id == 10 ? 'SI' : 'NO');
                        $EnvioDetalle->is_reenvio = $env_detalle_reenvio_id;
                        $EnvioDetalle->update();
                        $this->CambiosLog->createLog($envio_id);
                    }
                }

                $EnvioDetalle->save();

                $this->saveEsysDireccionPaquete($envio_id, $EnvioDetalle->id, $env_detalle->reenvio_id);
            }
        }
        return true;
    }



    public function saveEnvioDetalleMex($envio_id, $folio, $sucursal_array, $cliente_array)
    {
        $envio_detalles_array = json_decode($this->envio_detalle_array);
        if ($envio_detalles_array && isset($sucursal_array[0]) && isset($cliente_array[0])) {
            $Envio = Envio::findOne($envio_id);
            $this->CambiosLog = new EsysCambiosLog((new Envio));


            $count =  EnvioDetalle::find()->where(["envio_id" => $Envio->id])->count() > 0 ? EnvioDetalle::find()->where(["envio_id" => $Envio->id])->count() + 1 : 1;

            foreach ($envio_detalles_array as $key => $env_detalle) {




                if ($env_detalle->origen  ==  1) {
                    $EnvioDetalle = new EnvioDetalle();
                    $EnvioDetalle->envio_id             = $envio_id;
                    $EnvioDetalle->tracked              = $folio . "-" . str_pad($count, 2, "0", STR_PAD_LEFT);
                    $EnvioDetalle->sucursal_receptor_id =  $sucursal_array[0];
                    $EnvioDetalle->cliente_receptor_id  =  $cliente_array[0];
                    $EnvioDetalle->categoria_id         =  $env_detalle->categoria_id;
                    $EnvioDetalle->producto_id          = $env_detalle->producto_id;
                    $EnvioDetalle->producto_tipo        = null;
                    $EnvioDetalle->peso                 = $env_detalle->peso;
                    $EnvioDetalle->cantidad             = $env_detalle->cantidad;
                    $EnvioDetalle->observaciones        = $env_detalle->observaciones;
                    $EnvioDetalle->impuesto             = $env_detalle->producto_costo_extra;
                    $EnvioDetalle->valor_declarado      = $env_detalle->valor_declarado;
                    $EnvioDetalle->status               = self::STATUS_HABILITADO;
                    $EnvioDetalle->save();
                    $count  = $count + 1;

                    if ($env_detalle->update == 10) {
                        $this->CambiosLog->updateValue('#paquete', 'old', '********* Se agrego un nuevo paquete al envío Tracked: #' . $EnvioDetalle->tracked . ' *********');
                        $this->CambiosLog->updateValue('#paquete', 'dirty', '');
                        $this->CambiosLog->createLog($envio_id);
                        $Envio->status = Envio::STATUS_HABILITADO;
                        $Envio->update();
                    }

                    //throw new \yii\web\NotFoundHttpException(json_encode($EnvioDetalle));
                    $count_ini  = $EnvioDetalle->cantidad;// + 1;
                    //throw new \yii\web\NotFoundHttpException(json_encode($EnvioDetalle->cantidad));
                    //throw new \yii\web\NotFoundHttpException(json_encode($env_detalle->cantidad));
                    for ($i = 1; $i <= $env_detalle->cantidad; $i++) {
                        $MovimientoPaquete = new MovimientoPaquete();
                        $MovimientoPaquete->paquete_id      = $EnvioDetalle->id;
                        $MovimientoPaquete->tracked         = $EnvioDetalle->tracked . "/" . $i;
                        $MovimientoPaquete->tipo_envio      = $EnvioDetalle->envio->tipo_envio;
                        $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::MEX_SUCURSAL;
                        $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                        $save = $MovimientoPaquete->save();
                        if (!$save) {
                            throw new \yii\web\NotFoundHttpException(json_encode($MovimientoPaquete->errors));
                        }
                    }



                    //if ($env_detalle->cantidad < $EnvioDetalle->cantidad) {
                    //    for ($i = $env_detalle->cantidad + 1; $i <= $EnvioDetalle->cantidad; $i++) {
                    //        $MovimientoPaquete = new MovimientoPaquete();
                    //        $MovimientoPaquete->paquete_id      = $EnvioDetalle->id;
                    //        $MovimientoPaquete->tracked         = $EnvioDetalle->tracked . "/" . $i;
                    //        $MovimientoPaquete->tipo_envio      = $EnvioDetalle->envio->tipo_envio;
                    //        //$MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_CANCEL;
                    //        $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                    //        $MovimientoPaquete->save();
                    //    }
                    //}
                } elseif ($env_detalle->origen  ==  2) {
                    $EnvioDetalle = self::findOne($env_detalle->paquete_id);

                    if ($env_detalle->status == 1) {
                        $this->CambiosLog->updateValue('#paquete', 'old', '********* Se cancelo paquete con Tracked: #' . $EnvioDetalle->tracked . ' y producto: ' . $EnvioDetalle->producto->nombre . '*********');
                        $this->CambiosLog->updateValue('#paquete', 'dirty', '');
                        $EnvioDetalle->status = self::STATUS_CANCELADO;
                        $EnvioDetalle->update();
                        $this->CambiosLog->createLog($envio_id);
                    } else {
                        if ($EnvioDetalle->cantidad  != $env_detalle->cantidad) {


                            $this->CambiosLog->updateValue('#paquete', 'old', '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' N° Elementos: ' . $EnvioDetalle->cantidad);
                            $this->CambiosLog->updateValue('#paquete', 'dirty', $env_detalle->cantidad);
                            $EnvioDetalle->cantidad = $env_detalle->cantidad;
                            $EnvioDetalle->update();
                            $this->CambiosLog->createLog($envio_id);
                            $Envio->status = Envio::STATUS_HABILITADO;
                            $Envio->update();
                        }

                        if ($EnvioDetalle->valor_declarado  != $env_detalle->valor_declarado) {
                            $this->CambiosLog->updateValue('#paquete', 'old', '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' Valor declarado: ' . $EnvioDetalle->valor_declarado);
                            $this->CambiosLog->updateValue('#paquete', 'dirty', $env_detalle->valor_declarado);
                            $EnvioDetalle->valor_declarado = $env_detalle->valor_declarado;
                            $EnvioDetalle->update();
                            $this->CambiosLog->createLog($envio_id);
                            $Envio->status = Envio::STATUS_HABILITADO;
                            $Envio->update();
                        }
                    }
                }
            }
        }

        return true;
    }

    public function saveEsysDireccionPaquete($envio_id, $envio_detalle_id, $reenvio_id)
    {
        $dir_obj_array = json_decode($this->dir_obj_array);



        $EsysDireccion = EsysDireccion::find()->andWhere(['cuenta_id' => $envio_detalle_id])->andWhere(['cuenta' => EsysDireccion::CUENTA_REENVIO_PAQUETE, 'tipo' => EsysDireccion::TIPO_PERSONAL])->all();


        foreach ($EsysDireccion as $key => $direccion) {
            $direccion->delete();
        }

        $EnvioDetalle = EnvioDetalle::findOne($envio_detalle_id);

        if ($dir_obj_array) {
            $Envio = Envio::findOne($envio_id);
            if ($Envio->is_reenvio == Envio::REENVIO_ON) {
                foreach ($dir_obj_array as $key => $dir_obj) {
                    if ($dir_obj->reenvio_id == $reenvio_id) {
                        $EsysDireccion = new EsysDireccion();
                        $EsysDireccion->cuenta      = EsysDireccion::CUENTA_REENVIO_PAQUETE;
                        $EsysDireccion->cuenta_id   = $envio_detalle_id;
                        $EsysDireccion->tipo        = EsysDireccion::TIPO_PERSONAL;
                        $EsysDireccion->direccion   = $dir_obj->direccion;
                        $EsysDireccion->num_ext     = $dir_obj->n_exterior;
                        $EsysDireccion->num_int     = $dir_obj->n_interior;
                        $EsysDireccion->referencia  = $dir_obj->referencia;
                        $EsysDireccion->estado_id   = $dir_obj->estado_id;
                        $EsysDireccion->municipio_id = $dir_obj->municipio_id;
                        $EsysDireccion->codigo_postal_id = $dir_obj->colonia_id;
                        $EsysDireccion->save();

                        $DescargaBodega = DescargaBodega::find()->andWhere(["estado_id" => $EsysDireccion->estado_id])->andWhere(["municipio_id" => $EsysDireccion->municipio_id])->andWhere(["tipo" => DescargaBodega::DESCARGA_MUNICIPIO])->one();
                        if (!isset($DescargaBodega->id))
                            $DescargaBodega = DescargaBodega::find()->andWhere(["estado_id" => $EsysDireccion->estado_id])->andWhere(["tipo" =>  DescargaBodega::DESCARGA_ESTADO])->one();

                        $EnvioDetalle->bodega_descarga = isset($DescargaBodega->bodega_descarga) && $DescargaBodega->bodega_descarga ? $DescargaBodega->bodega_descarga : DescargaBodega::DESCARGA_SAN_JUAN;
                    }
                }
            }
        }

        $EnvioDetalle->update();

        return true;
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            if ($this->envio->tipo_envio != Envio::TIPO_ENVIO_MEX) {
                for ($i = 1; $i <= $this->cantidad; $i++) {
                    $MovimientoPaquete = new MovimientoPaquete();
                    $MovimientoPaquete->paquete_id      = $this->id;
                    $MovimientoPaquete->tracked         = $this->tracked . "/" . $i;
                    $MovimientoPaquete->tipo_envio      = $this->envio->tipo_envio;
                    $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_SUCURSAL;
                    $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                    $MovimientoPaquete->save();
                }
            }
        }
    }
}
