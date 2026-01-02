<?php
namespace app\modules\movil\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\envio\Envio;
use app\models\cliente\Cliente;
use app\models\user\User;
use app\models\esys\EsysDireccion;
use app\models\envio\EnvioComplementoPromocion;
use app\models\envio\EnvioDetalle;
use app\models\envio\EnvioPromocion;
use app\models\envio\ViewEnvio;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\producto\Producto;
use app\models\producto\ViewProducto;
use app\models\sucursal\ViewSucursal;
use app\models\cliente\ViewCliente;
use app\models\esys\EsysSetting;
use app\models\promocion\ViewPromocion;
use app\models\promocion\Promocion;
use app\models\cliente\ClienteCodigoPromocion;


class AppController extends  \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();
        $this->can = [
            'create' => Yii::$app->user->can('envioPrecapturaCreate'),
            'update' => Yii::$app->user->can('envioPrecapturaUpdate'),
        ];
    }


    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index',[
            "can" => $this->can
        ]);
    }

    public function actionPreEnvio()
    {
    	$this->layout = '/main-movil';

        $model = new Envio();
        $model->cliente          = new  Cliente();
        $model->cliente_emisor   = new  Cliente();
        $model->cliente_receptor = new  Cliente();

        $model->created_user_by  =  User::findOne(1);

        $producto = new Producto;

        $model->cliente->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

        $model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_REENVIO,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

        $model->envio_detalle   = new EnvioDetalle();
        $model->enviopromocion  = new EnvioPromocion();
        $model->enviopromocionComplemento  = new EnvioComplementoPromocion();
        $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();

        if ($model->load(Yii::$app->request->post()) && $model->envio_detalle->load(Yii::$app->request->post()) ) {
            //if ($model->is_reenvio == Envio::REENVIO_ON && !$model->envio_detalle->dir_obj_array)
                //$model->dir_obj->load(Yii::$app->request->post());

            $model->enviopromocion->load(Yii::$app->request->post());
            $model->enviopromocionComplemento->load(Yii::$app->request->post());


            $model->origen = Envio::ORIGEN_USA;
            $Folio =  Envio::find([ "tipo_envio" => $model->tipo_envio])->orderBy("id desc")->one();
            $Folio = isset($Folio->id) ? $Folio->id + 1 : 1;

            $model->folio       =  $model->tipo_envio == Envio::TIPO_ENVIO_TIERRA ? Envio::CLAVE_SERV_TIERRA . str_pad($Folio, 5 , "0",STR_PAD_LEFT) : Envio::CLAVE_SERV_LAX . str_pad($Folio, 5 , "0",STR_PAD_LEFT) ;
            $model->precio_libra_actual  = EsysSetting::getPrecioLibra($model->tipo_envio);
            $model->subtotal    = Yii::$app->request->post()['subTotal_envio'];
            $model->status 		= Envio::STATUS_SOLICITADO;
            $model->impuesto    = Yii::$app->request->post()['impuesto_total_envio'];
            $model->seguro_total= Yii::$app->request->post()['seguro_total_envio'];
            $model->total       = Yii::$app->request->post()['total_envio'];
            $model->peso_total      = Yii::$app->request->post()['peso_total'];
            $model->peso_reenvio    = isset(Yii::$app->request->post()['peso_reenvio']) && Yii::$app->request->post()['peso_reenvio'] ? Yii::$app->request->post()['peso_reenvio'] : null;
            $model->is_descuento_manual = isset(Yii::$app->request->post()['descuento_manual_check']) ? Envio::DESCUENTO_ON : Envio::DESCUENTO_OFF;

            $model->descuento_manual  = isset(Yii::$app->request->post()['descuento_manual_check']) ? Yii::$app->request->post()['descuento_manual'] : NULL;

            $model->is_recoleccion = isset(Yii::$app->request->post()['recoleccion_check']) ? Envio::RECOLECCION_ON : Envio::RECOLECCION_OFF;



            if($model->save()){
                $model->enviopromocion->savePromocionManual($model->id);
                $model->enviopromocionComplemento->saveComplementoEnvio($model->id);
                if($model->envio_detalle->saveEnvioDetalle($model->id,$model->folio)){

                        return $this->redirect(['view', 'id' => $model->id]);

                }
            }
        }


        return $this->render('create', [
            'model'     => $model,
            'producto'  => $producto,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Envio::findOne($id);
        $model->created_user_by =  User::findOne(Yii::$app->user->identity->id);
        $model->cliente         = new  Cliente();

        $producto = new Producto;

        $model->cliente->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

          // Cargamos datos de dirección

        $model->dir_obj   = $model->direccion;
        if ($model->dir_obj)
            $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
        else{
            $model->dir_obj = new EsysDireccion([
                'cuenta' => EsysDireccion::CUENTA_REENVIO,
                'tipo'   => EsysDireccion::TIPO_PERSONAL,
            ]);
        }

        $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();
        $model->enviopromocion     = new EnvioPromocion();
        $model->enviopromocionComplemento  = new EnvioComplementoPromocion();

        $model->cliente_emisor     = Cliente::findOne($model->cliente_emisor_id) ? Cliente::findOne($model->cliente_emisor_id) :  new  Cliente();




        $model->cliente_emisor->dir_obj = isset($model->cliente_emisor->id) ? $model->cliente_emisor->direccion :  new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);


        $model->cliente_receptor = new Cliente();
        //$model->cliente_receptor = Cliente::findOne($model->cliente_receptor_id);
        //$model->cliente_receptor->dir_obj = $model->cliente_receptor->direccion;*/

        $model->envio_detalle   = new EnvioDetalle();


        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post()) && $model->envio_detalle->load(Yii::$app->request->post()) && $model->cobroRembolsoEnvio->load(Yii::$app->request->post())) {
            if ($model->is_reenvio == Envio::REENVIO_ON)
                $model->dir_obj->load(Yii::$app->request->post());


            $model->enviopromocion->load(Yii::$app->request->post());
            $model->enviopromocionComplemento->load(Yii::$app->request->post());

            $model->precio_libra_actual  = EsysSetting::getPrecioLibra($model->tipo_envio);
            $model->status      = Envio::STATUS_SOLICITADO;
            $model->subtotal    = Yii::$app->request->post()['subTotal_envio'];
            $model->impuesto    = Yii::$app->request->post()['impuesto_total_envio'];
            $model->seguro_total= Yii::$app->request->post()['seguro_total_envio'];
            $model->total       = Yii::$app->request->post()['total_envio'];
            $model->peso_total  = Yii::$app->request->post()['peso_total'];

            $model->is_descuento_manual = isset(Yii::$app->request->post()['descuento_manual_check']) ? Envio::DESCUENTO_ON : Envio::DESCUENTO_OFF;
            $model->descuento_manual    = isset(Yii::$app->request->post()['descuento_manual_check']) ? Yii::$app->request->post()['descuento_manual'] : NULL;
            $model->peso_reenvio        = isset(Yii::$app->request->post()['peso_reenvio']) && Yii::$app->request->post()['peso_reenvio'] ? Yii::$app->request->post()['peso_reenvio'] : NULL;
            if($model->save()){
                $model->enviopromocion->savePromocionManual($model->id);
                $model->enviopromocionComplemento->saveComplementoEnvio($model->id);
                if($model->envio_detalle->saveEnvioDetalle($model->id,$model->folio,true)){
                    if($model->cobroRembolsoEnvio->saveCobroEnvio($model->id)){
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }

            }

        }

        $model->setSucursalAsignarNames();
        $model->setClienteAsignarNames();

        /**===========================================
            MOVIL REMOVE PROMOCION GUARDADA
        ==============================================**/
        if ($model->status == Envio::STATUS_SOLICITADO ) {
            $model->promocion_id                    = null;
            $model->promocion_detalle_id            = null;
            $model->promocion_complemento_id        = null;
            $model->codigo_promocional_especial_id  = null;
        }


        return $this->render('update', [
            'model' => $model,
            'can'   => $this->can,
            'producto'  => $producto,
        ]);
    }

     /**
     * Displays a single Cliente model.
     *
     * @param  integer $id The cliente id. * @return string
     *
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
    	$this->layout = '/main-movil';

        return $this->render('view', [
            'model' => Envio::findOne($id),
        ]);
    }

    public function actionImprimirTicket($id)
    {
        $model = Envio::findOne($id);
        $lengh = 370;
        $width = 72;
        $count = 0;
        $total_piezas = 0;
        foreach ($model->envioDetalles as $key => $item) {
            $count = $count + 1;
            $total_piezas = $total_piezas + $item->cantidad;
        }

        $lengh = $lengh + ($count  * 75 );
        $lengh = $lengh + ( $total_piezas * 7);

        $width= $width + ($count  * 2 );

        $content = $this->renderPartial('ticket', ["model" => $model]);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array($width, $lengh),//Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
             // set mPDF properties on the fly
            'options' => ['title' => 'Ticket de envio'],
             // call mPDF methods on the fly
            'methods' => [
                'SetHeader'=>[ 'Fecha ' . date('Y-m-d',$model->created_at) . ' /  Ticket de envio #' . $model->folio],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft = 3;
        $pdf->marginRight = 3;

        $pdf->setApi();

        /*$pdf_api = $pdf->getApi();
        $pdf_api->SetWatermarkImage(Yii::getAlias('@web').'/img/marca_agua_cora.png');
        $pdf_api->showWatermarkImage = true;*/


        // return the pdf output as per the destination setting
        return $pdf->render();

    }

    public function actionSucursalInfoAjax($q = false){
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            // Obtenemos sucursal
            $sucursal = ViewSucursal::getSucursalAjax($text);

            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $sucursal;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['results' => $sucursal];

            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionSucursalesEstadoAjax($q = false){
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            // Obtenemos sucursal
            $sucursal = ViewSucursal::getSucursalesEstadoAjax($text);

            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $sucursal;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['results' => $sucursal];


            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionClienteInfoAjax($q = false){
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            // Obtenemos sucursal
            $cliente  = ViewCliente::getClienteAjax($text,true);

            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $cliente;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['results' => $cliente];

            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionEnvioDetalleAjax(){
        return ViewEnvio::getEnvioDetalleAjax(Yii::$app->request->get());
    }

 	public function actionPrecioLibraAjax($arr = false)
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {

            $result = 0;
            $requestGet = Yii::$app->request->get();
            if (isset($requestGet["tipo_servicio"]) && $requestGet["tipo_servicio"]) {
                $result = EsysSetting::getPrecioLibra($requestGet["tipo_servicio"]);
            }
            return $result;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

 	public function actionClienteAjax($q = false, $cliente_id = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            if (is_null($text) && $cliente_id)
                $user = ViewCliente::getClienteAjax($cliente_id,true);
            else
                $user = ViewCliente::getClienteAjax($text,false);
            // Obtenemos user


            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $user;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $user];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionProductoLaxTierraAjax($q = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            $producto = ViewProducto::getProductoSeachAjax($text,true,false);
            // Obtenemos user

            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $producto;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $producto];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionEsysDireccionAjax()
    {
        return ViewEnvio::getEsysDireccionAjax(Yii::$app->request->get());
    }

    public function actionPromocionInfoAjax(){
        return ViewPromocion::getPromocionDetalleAjax(Yii::$app->request->get());
    }

    public function actionPromocionValidaAjax(){
        return ViewPromocion::getPromocionComplementoAjax(Yii::$app->request->get());
    }

    public function actionCodePromocionAjax(){
        return ViewPromocion::getCodePromocionAjax(Yii::$app->request->get());
    }

    public function actionCodePromocionSucursalAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('clave') && $request->get('cliente_emisor')) {
                // Obtenemos ViewPromocion
                $promocion =  ClienteCodigoPromocion::find()
                                ->andWhere(['tipo'          => ClienteCodigoPromocion::TIPO_SUCURSAL  ])
                                ->andWhere(['cliente_id'    => $request->get('cliente_emisor') ])
                                ->andWhere(['clave'         => $request->get('clave') ])
                                ->orderBy("id desc")->one();

                if (isset($promocion->id)) {

                    $filters = [
                        'filters' => "tipo_servicio=".Envio::TIPO_ENVIO_TIERRA."&tipo=".Promocion::TIPO_ESPECIAL."&promocion_id=".$promocion->promocion_id
                    ];

                    return [
                    "code"      => "202",
                    "message"   => "Se ingreso correctamente",
                    "data"      => ViewPromocion::getPromocionDetalleAjax($filters),
                    "type"      => "Success"
                    ];
                }

                return [
                    "code"      => "11",
                    "message"   => "Ingresa correctamente el codigo promocional, Intenta nuevamente",
                    "type"      => "Warning"
                ];

            }
            return [
                "code"      => "10",
                "message"   => "Intenta nuevamente",
                "type"      => "Error"
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionCobroEnvioAjax($q = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            $text = Yii::$app->request->get('envio_id');


            // Obtenemos ViewPromocion
            $CobroRembolsoEnvio =  CobroRembolsoEnvio::find()
                            ->andWhere(['envio_id'    => $text ])->all();

            // Devolvemos datos CHOSEN.JS
            $response = ['results' => $CobroRembolsoEnvio];

            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionValoracionHistorialAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;

        if ($request->get('producto_id') && $request->get('producto_tipo')) {

            $EnvioDetalle = EnvioDetalle::find()->andWhere([
                "producto_id"   => $request->get('producto_id'),
                "producto_tipo" => $request->get('producto_tipo')
            ])->orderBy('id desc')->one();

            return [
                "code"      => 202,
                "message"   => isset($EnvioDetalle->valoracion_paquete) ? $EnvioDetalle->valoracion_paquete : 0,
            ];
        }
    }
        public function actionPromocionEspecialAjax($q = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('cliente_id');

            }
            // Obtenemos ViewPromocion
            $promocion =  ClienteCodigoPromocion::find()
                            ->andWhere(['tipo'          => ClienteCodigoPromocion::TIPO_ESPECIAL  ])
                            ->andWhere(['status'        => ClienteCodigoPromocion::STATUS_ACTIVE ])
                            ->andWhere(['cliente_id'    => $text ])
                            ->orderBy("id desc")->one();

            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $promocion;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['results' => $promocion];

            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }



}
