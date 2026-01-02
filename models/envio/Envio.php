<?php

namespace app\models\envio;

use Yii;
use app\models\cliente\Cliente;
use app\models\sucursal\Sucursal;
use app\models\user\User;
use app\models\ticket\Ticket;
use app\models\promocion\PromocionComplemento;
use app\models\promocion\Promocion;
use app\models\promocion\PromocionDetalle;
use app\models\promocion\PromocionDetalleComplemento;
use app\models\esys\EsysCambiosLog;
use app\models\cliente\ClienteCodigoPromocion;
use app\models\cliente\ClienteHistoricoCall;
use app\models\esys\EsysDireccion;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\movimiento\MovimientoPaquete;
use app\models\pais\PaisesLatam;
/**
 * This is the model class for table "envio".
 *
 * @property int $id Envio ID
 * @property int $sucursal_emisor_id Sucursal emisor ID
 * @property int $sucursal_receptor_id Sucursal receptor ID
 * @property int $origen Origen
 * @property int $tipo_envio Tipo de envio
 * @property int $fecha Fecha
 * @property int $cliente_emisor_id Cliente emisor ID
 * @property int $cliente_receptor_id Cliente receptor ID
 * @property int $promocion_id Promoción ID
 * @property int $promocion_complemento_id Promocion complemento ID
 * @property int $codigo_promocional_id Código promocional ID
 * @property double $descuento_manual Descuento Manual
 * @property int $is_descuento_manual Aplica descuento manual
 * @property double $subtotal SubTotal
 * @property double $impuesto Impuesto
 * @property double $total Total
 * @property string $comentarios Comentarios  / Notas
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property Cliente $clienteEmisor
 * @property Cliente $clienteReceptor
 * @property User $createdBy
 * @property Sucursal $sucursalEmisor
 * @property Sucursal $sucursalReceptor
 * @property User $updatedBy
 */
class Envio extends \yii\db\ActiveRecord
{


    const CLAVE_SERV_TIERRA = "TIE-";
    //const CLAVE_SERV_LAX    = "AIR-";
    const CLAVE_SERV_MEX    = "MEX-";


    const ORIGEN_USA    = 1;
    const ORIGEN_MX     = 2;

    const DESCUENTO_ON     = 10;
    const DESCUENTO_OFF    = 1;

    const EFECTIVO_ON     = 10;
    const EFECTIVO_OFF    = 1;

    const RECOLECCION_ON     = 10;
    const RECOLECCION_OFF    = 1;

    const CHECK_CANCEL_ON       = 10;

    const CONVERT_ENVIO         = 10;

    const PREPAGO_ON     = 10;
    const PREPAGO_OFF    = 1;

    const REENVIO_ON     = 10;
    const REENVIO_OFF    = 1;

    //const ORIGEN_MX_USA = 3;

    const TIPO_ENVIO_TIERRA = 10;
    //const TIPO_ENVIO_LAX    = 20;
    const TIPO_ENVIO_MEX    = 30;


    const STATUS_PREPAGADO      = 40;
    const STATUS_ENTREGADO      = 30;
    const STATUS_HABILITADO     = 10;
    const STATUS_AUTORIZADO     = 20;
    const STATUS_EMPAQUETADO    = 6;
    const STATUS_RECOLECTADO    = 5;
    const STATUS_PREAUTORIZADO  = 4;
    const STATUS_NOAUTORIZADO   = 3;
    const STATUS_SOLICITADO     = 2;
    const STATUS_CANCELADO      = 1;




    public static $precioMexList = [
        "PRECION_MEX_1" => array(
            "rango_ini" => 0,
            "rango_fin" => 24,
        ),
        "PRECION_MEX_2" => array(
            "rango_ini" => 25,
            "rango_fin" => 49,
        ),
        "PRECION_MEX_3" => array(
            "rango_ini" => 50,
            "rango_fin" => 74,
        ),
        "PRECION_MEX_4" => array(
            "rango_ini" => 75,
            "rango_fin" => 99,
        ),
        "PRECION_MEX_5" => array(
            "rango_ini" => 100,
            "rango_fin" => null,
        ),
    ];

    public static $descuentoList = [
        self::DESCUENTO_ON   => 'Habilitado',
        self::DESCUENTO_OFF => 'Deshabilitado',
    ];

    public static $efectivoList = [
        self::EFECTIVO_ON   => 'SI',
        self::EFECTIVO_OFF  => 'NO',
    ];

    public static $tipoList = [
        self::TIPO_ENVIO_TIERRA   => 'TIERRA',
        //self::TIPO_ENVIO_LAX => 'AIRE',
        self::TIPO_ENVIO_MEX => 'MEX - USA',

    ];

    public static $statusList = [
        self::STATUS_PREPAGADO      => 'Prepagado / Anticipo',
        self::STATUS_ENTREGADO      => 'Entregado / Cerrado',
        self::STATUS_HABILITADO     => 'Recolectado / Recaudado',
        self::STATUS_AUTORIZADO     => 'Autorizado',
        self::STATUS_SOLICITADO     => 'Solicitado',
        self::STATUS_PREAUTORIZADO  => 'Pre Autorizado',
        self::STATUS_RECOLECTADO    => 'Recolectado',
        self::STATUS_EMPAQUETADO    => 'Empaquetado',
        self::STATUS_NOAUTORIZADO   => 'No Autorizado',
        self::STATUS_CANCELADO      => 'Cancelado',
    ];

    public static $statusAlertList = [
        self::STATUS_PREPAGADO      => 'panel-primary',
        self::STATUS_ENTREGADO      => 'panel-primary',
        self::STATUS_HABILITADO     => 'panel-success',
        self::STATUS_AUTORIZADO     => 'panel-mint',
        self::STATUS_SOLICITADO     => 'panel-warning',
        self::STATUS_PREAUTORIZADO  => 'panel-dark',
        self::STATUS_RECOLECTADO    => 'panel-mint',
        self::STATUS_EMPAQUETADO    => 'panel-success',
        self::STATUS_NOAUTORIZADO   => 'panel-danger',
        self::STATUS_CANCELADO      => 'panel-danger',
    ];

    public static $origenList = [
        self::ORIGEN_MX     => 'México - United States',
        //self::ORIGEN_MX_USA => 'México - Mexico',
        self::ORIGEN_USA    => 'United States - México',
    ];


    public $cliente;
    public $cliente_receptor;
    public $cliente_emisor;
    public $envio_detalle;
    public $list_envio_detalle;
    public $created_user_by;
    public $cobroRembolsoEnvio;
    public $enviopromocion;
    public $enviopromocionComplemento;
    public $dir_obj;
    private $CambiosLog;
    public $ticket;
    public $cliente_receptor_names  = [];
    public $sucursal_receptor_names = [];




       /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'envio';
    }

     /**
     * {@inheritdoc}
     */
     public function rules()
    {
        return [
            [['sucursal_emisor_id', 'tipo_envio',  ], 'required'],
            [['status'], 'default', 'value'=> self::STATUS_HABILITADO],
            [['sucursal_emisor_id',  'origen', 'tipo_envio',  'cliente_emisor_id',  'promocion_id', 'promocion_complemento_id','promocion_detalle_id', 'codigo_promocional_id','codigo_promocional_especial_id', 'is_check_cancel','is_descuento_manual', 'is_recoleccion','is_pago_vs_entrega','is_efectivo','is_reenvio','created_at', 'pre_created_at','created_by', 'updated_at', 'updated_by'], 'integer'],
            [['descuento_manual', 'subtotal', 'impuesto', 'total','peso_mex_con_empaque','peso_mex_sin_empaque','costo_reenvio','costo_pago_vs_entrega','seguro_total','pais_destino_id'], 'number'],
            [['comentarios'], 'string'],
            [['nota','informacion_extra'], 'string'],
            [['cliente_receptor_names'], 'each', 'rule' => ['string']],
            [['sucursal_receptor_names'], 'each', 'rule' => ['string']],
            [['cliente_emisor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_emisor_id' => 'id']],

            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['promocion_complemento_id'], 'exist', 'skipOnError' => true, 'targetClass' => PromocionComplemento::className(), 'targetAttribute' => ['promocion_complemento_id' => 'id']],
            [['promocion_id'], 'exist', 'skipOnError' => true, 'targetClass' => Promocion::className(), 'targetAttribute' => ['promocion_id' => 'id']],
            [['sucursal_emisor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_emisor_id' => 'id']],
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
            'sucursal_emisor_id' => 'Sucursal Emisor',
            'pais_destino_id' => 'Pais Destino',

            'origen' => 'Origen',
            'tipo_envio' => 'Tipo Envio',
            'cliente_emisor_id' => 'Quien envía',
            'cliente_receptor_id' => 'Quien recibe',
            'promocion_id' => 'Promocion',
            'promocion_complemento_id' => 'Promocion Complemento',
            'codigo_promocional_id' => 'Codigo Promocional',
            'codigo_promocional_especial_id' => 'Codigo de Promocional especial',
            'descuento_manual' => 'Descuento Manual',
            'is_descuento_manual' => 'Editar total',
            'is_recoleccion' => 'Recoleccion',
            'subtotal' => 'Subtotal',
            'peso_reenvio' => 'Peso reenvío',
            'peso_mex_con_empaque' => 'Peso mex  empaquetado',
            'peso_mex_sin_empaque' => 'Peso mex  sin empaquetar',
            'impuesto' => 'Impuesto',
            'total' => 'Total',
            'is_reenvio' => 'Reenvio',
            'informacion_extra' => 'Informacion  extra',
            'is_efectivo' => 'Efectivo',
            'is_check_cancel' => 'Check cancel',
            'is_convertir_lax_tie' => 'Convertir TIE',
            'costo_reenvio' => 'Costo de reenvio',
            'promocion_detalle_id' => 'Promocion Detalle ',
            'peso_total' => 'Peso total',
            'costo_pago_vs_entrega' => 'Costo contra entrega',
            'is_pago_vs_entrega' => 'Pago contra entrega',
            "precio_libra_actual" => 'Precio de Libra',
            'status' => 'Estatus',
            'sucursalEmisor.nombre' => 'Sucursal emisor',
            'sucursal_receptor_names' => 'Sucursal receptor',
            'cliente_receptor_names' => 'Quien recibe',
            'sucursalEmisor.encargadoSucursal.nombreCompleto' => 'Nombre del encargado',
            'sucursalReceptor.encargadoSucursal.nombreCompleto' => 'Nombre del encargado',
            'sucursalReceptor.nombre' => 'Sucursal receptor',
            'comentarios' => 'Comentarios',
            'nota' => 'Nota',
            'seguro_total' => 'Seguro total',
            '#paquete' => '#Paquete',
            'created_at' => 'Creado',
            'pre_created_at' => 'Precaptura Creado',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getClienteReceptor()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_receptor_id']);
    }
    
    public function getPais(){
        return $this->hasOne(PaisesLatam::className(), ['id' => 'pais_destino_id']);
    }

   /**
     * @return \yii\db\ActiveQuery
     */
    public function getClienteEmisor()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_emisor_id']);
    }

    public function getTotalPagado()
    {
        return $this->hasOne(CobroRembolsoEnvio::className(),["envio_id" => 'id'])
                ->andWhere(["cobro_rembolso_envio.tipo" => CobroRembolsoEnvio::TIPO_COBRO])->sum('cantidad');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCobroRembolsoEnvios()
    {
        return $this->hasMany(CobroRembolsoEnvio::className(), ['envio_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::className(), ['envio_id' => 'id']);
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
    public function getPromocionComplemento()
    {
        return $this->hasOne(PromocionComplemento::className(), ['id' => 'promocion_complemento_id']);
    }

    public function getPromocionComplementoDetalle()
    {
        return $this->hasOne(PromocionDetalleComplemento::className(), ['promocion_detalle_id' => 'promocion_detalle_id','promocion_complemento_id' => 'promocion_complemento_id']);
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
    public function getPromocionDetalle()
    {
        return $this->hasOne(PromocionDetalle::className(), ['id' => 'promocion_detalle_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSucursalEmisor()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_emisor_id']);
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
    public function getEnvioDetalles()
    {
        return $this->hasMany(EnvioDetalle::className(), ['envio_id' => 'id'])->
            orderBy(['status' => SORT_DESC]);;
    }

    public function getPienzasTotal()
    {
          return $this->hasOne(EnvioDetalle::className(),["envio_id" => 'id'])->andWhere(["status" => EnvioDetalle::STATUS_HABILITADO])->sum('cantidad');
    }

    public function getValorTotal()
    {
          return $this->hasOne(EnvioDetalle::className(),["envio_id" => 'id'])->andWhere(["status" => EnvioDetalle::STATUS_HABILITADO])->sum('valor_declarado');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnvioPromocion()
    {
        return $this->hasMany(EnvioPromocion::className(), ['envio_id' => 'id']);
    }

    public function getEnvioComplementoPromocion()
    {
        return $this->hasMany(EnvioComplementoPromocion::className(), ['envio_id' => 'id']);
    }

    public function getHistorialCall(){
        return $this->hasMany(ClienteHistoricoCall::className(), ['envio_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClienteCodigoPromocion()
    {
        return $this->hasOne(ClienteCodigoPromocion::className(), ['id' => 'codigo_promocional_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClienteCodigoPromocionEspecial()
    {
        return $this->hasOne(ClienteCodigoPromocion::className(), ['id' => 'codigo_promocional_especial_id']);
    }

    public function getCambiosLog()
    {
        return EsysCambioLog::find()
            ->andWhere(['or',
                ['modulo' => $this->tableName(), 'idx' => $this->id]
            ])
            ->all();
    }

    public function getDireccion()
    {
        return $this->hasOne(EsysDireccion::className(), ['cuenta_id' => 'id'])
            ->where(['cuenta' => EsysDireccion::CUENTA_REENVIO, 'tipo' => EsysDireccion::TIPO_PERSONAL]);
    }

    public function setSucursalAsignarNames()
    {
        $this->sucursal_receptor_names = [];

        foreach ($this->envioDetalles as $key => $e_detalle) {

            $is_repit = false;
            foreach ($this->sucursal_receptor_names as $key => $receptor_names) {
                if ($receptor_names == $e_detalle->sucursalReceptor->id)  {
                    $is_repit = true;
                }
            }
            if (!$is_repit) {
                $this->sucursal_receptor_names[] = [
                    "id"     => $e_detalle->sucursalReceptor->id,
                    "nombre" => $e_detalle->sucursalReceptor->nombre . " [". $e_detalle->sucursalReceptor->clave ."]",
                ];
            }
        }
    }

    public function setClienteAsignarNames()
    {
        $this->cliente_receptor_names = [];

        foreach ($this->envioDetalles as $key => $e_detalle) {

            $is_repit = false;
            foreach ($this->cliente_receptor_names as $key => $receptor_names) {
                if ($receptor_names == $e_detalle->clienteReceptor->id)  {
                    $is_repit = true;
                }
            }
            if (!$is_repit) {
                $this->cliente_receptor_names[] = [
                    "id"     => $e_detalle->clienteReceptor->id,
                    "nombre" => $e_detalle->clienteReceptor->nombre . " ". $e_detalle->clienteReceptor->apellidos,
                ];
            }
        }
    }

    public function setValidaEnvioViaje()
    {
        $is_viaje = false;
        foreach ($this->envioDetalles as $key => $paquete) {
            for ($i = 1; $i <= $paquete->cantidad; $i++) {
                $MovimientoPaquete = MovimientoPaquete::find()->where([ "tracked" => $paquete->tracked . "/" . $i ])->orderBy("id desc")->one();
                if (isset($MovimientoPaquete->id)) {
                    $is_viaje = $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO || $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_BODEGA || $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_REPARTO || $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_ENTREGADO ? true: false;
                }
            }
        }

        return $is_viaje;
    }

    public static function createImage($text, $fontSize = 30, $imgWidth = 90, $imgHeight = 200){

        $img;

        //text font path
        //$font = 'fonts/the_unseen.ttf';
        //putenv('GDFONTPATH=' . realpath('../fonts'));
        //$font = "/home/ae8aaa5/cora.paqueterialacora.com/web/fonts/open-sans/OpenSans-ExtraBold.ttf";// note that I'm using font name directly
        $font = Yii::$app->basePath."/web/fonts/open-sans/OpenSans-ExtraBold.ttf";// note that I'm using font name directly

        //create the image
        $img = imagecreatetruecolor($imgWidth, $imgHeight);

        //create some colors
        $white = imagecolorallocate($img, 255, 255, 255);
        $grey = imagecolorallocate($img, 128, 128, 128);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $imgWidth - 1, $imgHeight - 1, $white);

        $textcolor = imagecolorallocate($img, 0, 0, 0);
        //break lines
        $splitText = explode ( "n" , $text );
        $lines = count($splitText);

        foreach($splitText as $txt){
            $textBox = imagettfbbox($fontSize,90,$font,$txt);
            $textWidth = abs(max($textBox[2], $textBox[4]));
            $textHeight = abs(max($textBox[5], $textBox[7]));
            $x = (imagesx($img) - $textWidth)/2;
            $y = ((imagesy($img) + $textHeight)/2)-($lines-2)*$textHeight;
            $lines = $lines-1;

            //add some shadow to the text
            //imagettftext($img, $fontSize, 90, $x, $y, $grey, $font, $txt);

            //add the text

            imagettftext($img, $fontSize, 90, $x, 175, $black, $font, $txt);
        }
         //imagestring($img, 15, 0, 0, $text, $textcolor);

        ob_start();
        imagepng($img);
        printf('<img src="data:image/png;base64,%s"/>', base64_encode(ob_get_clean()));
        //header('Content-Type: image/png');
        //echo "<img src='" .imagepng($img,false,75)."' />";
//die();

    }



    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                /*$this->created_by = $this->created_by ? $this->created_by : null;
                $this->created_by = Yii::$app->user->identity && !$this->created_by ? Yii::$app->user->identity->id : $this->created_by;*/

                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

                if ($this->status == Envio::STATUS_SOLICITADO ){
                    if ($this->tipo_envio == self::TIPO_ENVIO_TIERRA /*||  $this->tipo_envio == self::TIPO_ENVIO_LAX */) {
                        $this->pre_created_at = time();
                        $this->created_at = 0;
                    }
                }

            }else{

                $this->CambiosLog = new EsysCambiosLog($this);
                // Remplazamos manualmente valores del log de cambios
                foreach($this->CambiosLog->getListArray() as $attribute => $value) {
                    switch ($attribute) {

                        case 'cliente_receptor_id':
                        case 'cliente_emisor_id':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', Cliente::find()->where(['id' => $value['old']])->one()->nombreCompleto);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', Cliente::find()->where(['id' => $value['dirty']])->one()->nombreCompleto);
                            break;

                        case 'sucursal_emisor_id':
                        case 'sucursal_receptor_id':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', Sucursal::find()->where(['id' => $value['old']])->one()->nombre);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', Sucursal::find()->where(['id' => $value['dirty']])->one()->nombre);
                            break;
                        case 'status':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', self::$statusList[$value['old']]);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', self::$statusList[$value['dirty']]);
                            break;

                        case 'is_efectivo':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', self::$efectivoList[$value['old']]);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', self::$efectivoList[$value['dirty']]);
                            break;

                        case 'is_descuento_manual':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', self::$descuentoList[$value['old']]);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', self::$descuentoList[$value['dirty']]);
                            break;

                        case 'promocion_detalle_id':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', "");

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', "Se realizaron cambios en detalles de la promoción");
                            break;

                        case 'promocion_complemento_id':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', "");

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', "Se realizarón cambios en los complemento de la promoción");
                            break;
                    }
                }
                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->updated_by;
            }
            if ($this->codigo_promocional_especial_id) {
                $ClienteCodigoPromocion = ClienteCodigoPromocion::findOne($this->codigo_promocional_especial_id);
                $ClienteCodigoPromocion->status = ClienteCodigoPromocion::STATUS_USADO;
                $ClienteCodigoPromocion->save();
            }
            return true;

        } else
            return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($insert){
            if ($this->is_reenvio == Envio::REENVIO_ON && $this->tipo_envio == self::TIPO_ENVIO_MEX )
                $this->dir_obj->cuenta_id = $this->id;

        }
        else{
            // Guardamos un registro de los cambios
            $this->CambiosLog->createLog($this->id);
        }

        if ($this->is_reenvio == Envio::REENVIO_ON && $this->tipo_envio == self::TIPO_ENVIO_MEX && $this->dir_obj){
            $this->dir_obj->save();
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $this->direccion->delete();

        foreach ($this->cambiosLog as $key => $value) {
           $value->delete();
        }
    }
}
