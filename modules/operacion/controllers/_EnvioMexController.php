<?php

namespace app\modules\operacion\controllers;

use Yii;
use yii\helpers\Url;
use yii\base\InvalidParamException;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use kartik\mpdf\Pdf;
use app\models\envio\Envio;
use app\models\cliente\Cliente;
use app\models\envio\EnvioDetalle;
use app\models\esys\EsysDireccion;
use app\models\user\User;
use app\models\sucursal\ViewSucursal;
use app\models\producto\ViewProducto;
use app\models\promocion\ViewPromocion;
use app\models\envio\ViewEnvio;
use app\models\esys\EsysSetting;
use app\models\cliente\ClienteHistoricoCall;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\cliente\ViewCliente;
use app\models\ticket\Ticket;



/**
 * Default controller for the `clientes` module
 */
class EnvioMexController extends  \app\controllers\AppController
{

	 private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create_mex' => Yii::$app->user->can('envioMexCreate'),
            'update_mex' => Yii::$app->user->can('envioMexUpdate'),
            'delete_mex' => Yii::$app->user->can('envioMexDelete'),
            'cancel_mex' => Yii::$app->user->can('envioMexCancel'),
            'seguimiento' => Yii::$app->user->can('seguimiento'),
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

    public function actionCobroMex()
    {
        if (isset(Yii::$app->request->post()['folio']))
        {
            $folio = trim(Yii::$app->request->post()['folio']);
            if ($folio ) {
                $model              = Envio::find()->where(['folio' => $folio ])->one();
                if ($model) {

                    $model->dir_obj     = $model->direccion;

                    if ($model->dir_obj)
                        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
                    else{
                        $model->dir_obj = new EsysDireccion([
                            'cuenta' => EsysDireccion::CUENTA_REENVIO,
                            'tipo'   => EsysDireccion::TIPO_PERSONAL,
                        ]);
                    }

                    $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();
                    return $this->render('cobro-mex',[
                        "folio" => $model
                    ]);
                }else
                    Yii::$app->session->setFlash('danger', 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.');
            }else
                Yii::$app->session->setFlash('warning', 'Debes ingresar correctamente el folio.');

        }
        return $this->render('cobro-mex');
    }

    public function actionCobroReenvio()
    {
        if ($response = Yii::$app->request->post()) {
            if (isset($response["id"]) &&  $response["id"]) {
                $model = $this->findModel($response["id"]);
                $model->dir_obj     = $model->direccion;

                if ($model->is_reenvio == Envio::REENVIO_ON){

                    if ($model->dir_obj)
                        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
                    else{
                        $model->dir_obj = new EsysDireccion([
                            'cuenta' => EsysDireccion::CUENTA_REENVIO,
                            'tipo'   => EsysDireccion::TIPO_PERSONAL,
                        ]);
                    }
                    $model->dir_obj->load(Yii::$app->request->post());
                }
                $model->load(Yii::$app->request->post());
                if ($model->update()) {
                    $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();
                    return $this->render('cobro-mex',[
                        "folio" => $model
                    ]);
                }
            }
        }
        return $this->render('cobro-mex');

    }

    public function actionUpdateEnvio(){
        if ($response = Yii::$app->request->post()) {
            if ( isset($response["id"]) && $response["id"] && $response['Envio']['peso_total']) {
                $is_ticket = false;
                $model = $this->findModel($response["id"]);
                $is_ticket  = floatval($model->peso_total) > floatval($response['Envio']['peso_total']) ? true : false;
                $lb_dif     = round( floatval($response['Envio']['peso_total'] - floatval($model->peso_total)),2);
                if ($model) {
                    $model->dir_obj             = $model->direccion;
                    $model->subtotal            = isset($response['subtotal']) ? $response['subtotal']       : 0;
                    $model->total               = isset($response['total']) ? $response['total']             : 0;
                    $model->precio_libra_actual = isset($response['precioLibra']) ? $response['precioLibra'] : 0;
                    $model->peso_total          = isset($response['Envio']['peso_total']) ? $response['Envio']['peso_total']   : 0;

                    if ($model->update()) {
                        $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();
                        $model->dir_obj            = $model->direccion;
                        if ($model->dir_obj)
                            $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
                        else{
                            $model->dir_obj = new EsysDireccion([
                                'cuenta' => EsysDireccion::CUENTA_REENVIO,
                                'tipo'   => EsysDireccion::TIPO_PERSONAL,
                            ]);
                        }
                        Yii::$app->session->setFlash('success', "Se ha modificado correctamente el peso al folio " . $model->folio);
                        return $this->render('cobro-mex',[
                            "folio"     => $model,
                            "is_ticket" => $is_ticket,
                            "lb_dif"    => $lb_dif,
                        ]);
                    }
                }
            }
        }
        return $this->redirect('cobro-mex');
    }

    public function actionCobroEnvio(){
        $response = Yii::$app->request->post();
        if (isset($response["id"]) && $response["id"]) {

            $model = $this->findModel($response["id"]);
            if (isset($response["descuento_manual_check"]) && $response["descuento_manual_check"] ) {
                $envio                      = Envio::findOne($model->id);
                $envio->dir_obj             = $envio->direccion;
                $envio->is_descuento_manual = Envio::DESCUENTO_ON;
                $envio->nota                = isset($response['nota']) && $response['nota'] ? $response['nota'] : NULL;
                $envio->descuento_manual    = isset($response['descuento_manual'])  && $response['descuento_manual']  ? $response['descuento_manual']  : 0 ;

                if ($envio->total != $response['total'] )
                    $envio->total  =  $response['total'];

                if ($envio->subtotal != $response['subtotal'] )
                    $envio->subtotal  =  $response['subtotal'];

                $envio->total               = $envio->total - $envio->descuento_manual;
                $envio->save();
            }else{
                $envio = Envio::findOne($model->id);
                $envio->dir_obj             = $envio->direccion;
                $envio->nota                = isset($response['nota']) && $response['nota'] ? $response['nota'] : NULL;
                if ($envio->total != $response['total'] )
                    $envio->total  =  $response['total'];

                if ($envio->subtotal != $response['subtotal'] )
                    $envio->subtotal  =  $response['subtotal'];

                $envio->save();
            }

            $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();

            if($model->cobroRembolsoEnvio->load(Yii::$app->request->post())){
                if($model->cobroRembolsoEnvio->saveCobroEnvio($model->id)){
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }

            return $this->render('cobro-mex',[
                "folio" => $model
            ]);
        }
        return $this->redirect('cobro-mex');
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
        $model = $this->findModel($id);
        $model->ticket = new Ticket();

        $model->dir_obj   = $model->direccion;

        if ($model->ticket->load(Yii::$app->request->post())) {

            $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $caracteres .= "1234567890";
            $clave      = "";
            $longitud   = 6;
            $model->ticket->ticket_evidencia_array       = null;

            for($i=0; $i<$longitud; $i++){
                $clave.=$caracteres[rand(0,strlen($caracteres)-1)];
            }

            $model->ticket->clave = $clave;

            if ($model->ticket->save())
                return $this->redirect(['view', 'id' => $model->id]);
        }elseif ($response = Yii::$app->request->post()) {
            if(!isset($response['status'])){
                $model->subtotal            = isset($response['subtotal']) ? $response['subtotal']       : 0;
                $model->total               = isset($response['total']) ? $response['total']             : 0;
                $model->precio_libra_actual = isset($response['precioLibra']) ? $response['precioLibra'] : 0;
                $model->status = Envio::STATUS_AUTORIZADO;
            }else
                $model->status = Envio::STATUS_PREAUTORIZADO;

            if ($model->update()) {
                return $this->render('view', [
                    'model' => $model,
                    'can'   => $this->can,
                ]);
            }
        }



        return $this->render('view', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }

        /**
     * Creates a new Cliente model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Envio();
        $model->cliente          = new  Cliente();
        $model->cliente_emisor   = new  Cliente();
        $model->cliente_receptor = new  Cliente();
        $model->created_user_by  =  User::findOne(Yii::$app->user->identity->id);

        $model->cliente->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

        $model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_REENVIO,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

        $model->envio_detalle   = new EnvioDetalle();
        $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();

        if ($model->load(Yii::$app->request->post()) && $model->envio_detalle->load(Yii::$app->request->post()) && $model->cobroRembolsoEnvio->load(Yii::$app->request->post()) ) {
            $model->origen  = Envio::ORIGEN_MX;
            $Folio          = Envio::find()->where([ "tipo_envio" => $model->tipo_envio])->orderBy("id desc")->one();
            $model->status  = Envio::STATUS_SOLICITADO;

            $model->sucursal_emisor_id  = $model->created_user_by->sucursal_id;

            //$model->is_efectivo = isset(Yii::$app->request->post()['efectivo_check']) ? Envio::EFECTIVO_ON : Envio::EFECTIVO_OFF;

            $Folio          = isset($Folio->id) ? $Folio->id + 1 : 1;
            $model->folio   = Envio::CLAVE_SERV_MEX . str_pad($Folio, 6 , "0",STR_PAD_LEFT);

           // $model->folio       =  $model->created_user_by->sucursal->clave ? $model->created_user_by->sucursal->clave. "-" . str_pad($Folio, 6 , "0",STR_PAD_LEFT) : "XXX-" . str_pad($Folio, 6 , "0",STR_PAD_LEFT) ;

            //$model->impuesto    = Yii::$app->request->post()['costo_extra_total_envio'];
            //$model->seguro_total= Yii::$app->request->post()['seguro_total_envio'];

            if($model->save()){
                if($model->envio_detalle->saveEnvioDetalleMex($model->id,$model->folio)){
                    if($model->cobroRembolsoEnvio->saveCobroEnvio($model->id,true)){
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->created_user_by =  User::findOne(Yii::$app->user->identity->id);
        $model->cliente         = new  Cliente();

        $model->cliente->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);


        $model->cliente_emisor          = Cliente::findOne($model->cliente_emisor_id) ? Cliente::findOne($model->cliente_emisor_id) :  new  Cliente();

        $model->cliente_emisor->dir_obj = isset($model->cliente_emisor->id) ? $model->cliente_emisor->direccion :  new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);


        //  $model->cliente_receptor = Cliente::findOne($model->cliente_receptor_id);
        //$model->cliente_receptor->dir_obj = $model->cliente_receptor->direccion;
        $model->cliente_receptor    = new Cliente();
        $model->cobroRembolsoEnvio  = new CobroRembolsoEnvio();
        $model->envio_detalle       = new EnvioDetalle();

        $model->dir_obj   = $model->direccion;

        if ($model->dir_obj)
            $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
        else{
            $model->dir_obj = new EsysDireccion([
                'cuenta' => EsysDireccion::CUENTA_REENVIO,
                'tipo'   => EsysDireccion::TIPO_PERSONAL,
            ]);
        }

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post()) && $model->envio_detalle->load(Yii::$app->request->post()) && $model->cobroRembolsoEnvio->load(Yii::$app->request->post()) ){
            $model->impuesto    = Yii::$app->request->post()['costo_extra_total_envio'];
            $model->seguro_total= Yii::$app->request->post()['seguro_total_envio'];
            $model->is_efectivo = isset(Yii::$app->request->post()['efectivo_check']) ? Envio::EFECTIVO_ON : Envio::EFECTIVO_OFF;
            if($model->save()){
                if($model->envio_detalle->saveEnvioDetalleMex($model->id,$model->folio)){
                    if($model->cobroRembolsoEnvio->saveCobroEnvio($model->id,true)){
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        }

        $model->setSucursalAsignarNames();
        $model->setClienteAsignarNames();

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->status = Envio::STATUS_CANCELADO;
        $model->dir_obj   = $model->direccion;
        $model->update();

        Yii::$app->session->setFlash('success', "Se ha cancelado en envio #" . $model->folio);

        return $this->redirect(['index']);
    }

    public function actionImprimirEtiqueta($id)
    {
        $model = EnvioDetalle::findOne($id);

        $content = $this->renderPartial('etiqueta', ["model" => $model]);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array(45,60),//Pdf::FORMAT_LETTER,
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

        ]);

        $pdf->marginLeft = -5;
        $pdf->marginRight = -5;

        // return the pdf output as per the destination setting
        return $pdf->render();

    }

    public function actionImprimirTicket($id)
    {
        $model = $this->findModel($id);
        $content = $this->renderPartial('ticket', ["model" => $model]);

        $lengh = 370;
        $width = 72;
        $count = 0;
        $total_piezas = 0;
        foreach ($model->envioDetalles as $key => $item) {
            $count = $count + 1;
            $total_piezas = $total_piezas + $item->cantidad;
        }

        $lengh = $lengh + ($count  * 90 );
        $lengh = $lengh + ( $total_piezas * 7);

        $width= $width + ($count  * 2 );

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            //'format' => array(72, $lengh),//Pdf::FORMAT_A4,
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
                'SetHeader'=>['Ticket de envio #' . $model->folio],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft    = 3;
        $pdf->marginRight   = 3;

        $pdf->setApi();

        /*$pdf_api = $pdf->getApi();
        $pdf_api->SetWatermarkImage( Url::to(['@web/img/marca_agua_cora.png']));
        $pdf_api->showWatermarkImage = true;*/

        // return the pdf output as per the destination setting
        return $pdf->render();

    }

    public function actionSendContactoSeguimiendo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ClienteHistoricoCall = new  ClienteHistoricoCall();

        if ($seg = Yii::$app->request->post()) {
            $Envio          = Envio::findOne($seg["envio_id"]);
            $Envio->status  = Envio::STATUS_NOAUTORIZADO;
            $Envio->update();
            $ClienteHistoricoCall->envio_id         = $seg["envio_id"];
            $ClienteHistoricoCall->tipo_respuesta_id = $seg["tipo_respuesta_id"];
            $ClienteHistoricoCall->comentario       = $seg["comentario"];
            $ClienteHistoricoCall->tipo             = ClienteHistoricoCall::TIPO_SEGUIMIENTO;
            if ($ClienteHistoricoCall->save()) {
                return [
                    'code' => 202,
                    'message' => 'Se registro correctamente'
                ];
            }else
                return [
                    'code' => 10,
                    'message' => 'Error en el registro'
                ];

        }
    }

    public function actionSendRembolso()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($response = Yii::$app->request->post()) {
            $model = $this->findModel($response["envio_id"]);
            $CobroRembolsoEnvio  =  new CobroRembolsoEnvio();
            $CobroRembolsoEnvio->envio_id       = $model->id;
            $CobroRembolsoEnvio->tipo           = CobroRembolsoEnvio::TIPO_DEVOLUCION;
            $CobroRembolsoEnvio->metodo_pago    = CobroRembolsoEnvio::COBRO_EFECTIVO;
            $CobroRembolsoEnvio->cantidad       = $response["rembolso"];
            if ($CobroRembolsoEnvio->save()) {
                return [
                    'code' => 202,
                    'message' => 'Se registro correctamente'
                ];
            }else{
                return [
                    'code' => 10,
                    'message' => 'Error en el registro'
                ];
            }
        }
    }


    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
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

    public function actionCreateTicketAjax(){
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            $response = Yii::$app->request->post();

            $ticket = new Ticket();
            $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $caracteres .= "1234567890";
            $clave      = "";
            $longitud   = 6;
            $ticket->ticket_evidencia_array       = null;
            for($i=0; $i < $longitud; $i++){
                $clave.=$caracteres[rand(0,strlen($caracteres)-1)];
            }

            $ticket->clave       = $clave;
            $ticket->tipo_id     = $response["tipo"];
            $ticket->envio_id    = $response["envio_id"];
            $ticket->descripcion = $response["comentario"];
            $ticket->status      = Ticket::STATUS_ACTIVE;
            if ($ticket->save()) {
                return [
                    "code" => 202,
                    "clave" => $ticket->clave,
                    "type" => "success",
                ];
            }
            return [
                "code" => 10,
                "message" => "Ocurrio un error, intenta nuevamente",
                "type" => "success",
            ];

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

     public function actionSucursalesUsaAjax($q = false){
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
            $sucursal = ViewSucursal::getSucursalesUsaAjax($text);

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

    public function actionProductosCategoriaAjax(){
        return ViewProducto::getProductoAllJsonBtt(Yii::$app->request->get());
    }

    /*public function actionProductoInfoAjax(){
        return ViewProducto::getProductoDetalleAjax(Yii::$app->request->get());
    }*/

    public function actionEnviosJsonBtt(){
        return ViewEnvio::getJsonBtt(Yii::$app->request->get());
    }

    public function actionEnvioDetalleAjax(){
        return ViewEnvio::getEnvioDetalleAjax(Yii::$app->request->get());
    }

    public function actionCategoriaAjax()
    {
        return ViewEnvio::getCategoriaInfoMexAjax(Yii::$app->request->get());
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

    //------------------------------------------------------------------------------------------------//
    // HELPERS
    //------------------------------------------------------------------------------------------------//
    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @return Model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $_model = 'model')
    {
        switch ($_model) {
            case 'model':
                $model = Envio::findOne($id);
                break;

            case 'view':
                $model = ViewEnvio::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }

}
