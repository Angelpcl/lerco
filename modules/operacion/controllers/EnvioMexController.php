<?php

namespace app\modules\operacion\controllers;

use Yii;
use yii\helpers\Url;
use yii\base\InvalidParamException;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use kartik\mpdf\Pdf;
use app\models\user\User;
use app\models\envio\Envio;
use app\models\ticket\Ticket;
use app\models\envio\ViewEnvio;
use app\models\cliente\Cliente;
use app\models\esys\EsysSetting;
use app\models\esys\EsysDireccion;
use app\models\envio\EnvioDetalle;
use app\models\cliente\ViewCliente;
use app\models\sucursal\ViewSucursal;
use app\models\producto\ViewProducto;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\sucursal\ListaPrecioMx;
use app\models\producto\Producto;


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
            'cancel_mex' => Yii::$app->user->can('envioMexCancel'),
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            "can" => $this->can
        ]);
    }

    public function actionCobroMex()
    {


        $response   = Yii::$app->request->post();

        $folio      = isset(Yii::$app->request->get()["folio"]) ? trim(Yii::$app->request->get()["folio"]) : null;

        if ((isset($response['Cliente']['emisor_id']) && $response['Cliente']['emisor_id'] || isset($response['Cliente']['receptor_id']) && $response['Cliente']['receptor_id']) &&  !$folio) {

            $enviosAll =  Envio::find()
                ->innerJoin("envio_detalle", "envio.id = envio_detalle.envio_id")
                ->andWhere([
                    "and",
                    ["=", "envio.status", Envio::STATUS_HABILITADO],
                    ["=", "envio.tipo_envio", Envio::TIPO_ENVIO_MEX]
                ]);

            if (isset($response['Cliente']['emisor_id']) && $response['Cliente']['emisor_id']) {
                $enviosAll->andWhere(["envio.cliente_emisor_id" => $response['Cliente']['emisor_id']]);
            }

            if (isset($response['Cliente']['receptor_id']) && $response['Cliente']['receptor_id']) {
                $enviosAll->andWhere(["envio_detalle.cliente_receptor_id" => $response['Cliente']['receptor_id']]);
            }

            return $this->render('cobro-mex', [
                "envios" => $enviosAll->all(),
            ]);
        } elseif (isset($response['Cliente']['emisor_id']) || isset($response['Cliente']['receptor_id'])) {

            Yii::$app->session->setFlash('danger', 'Debes seleccionar un Cliente para realizar la consulta, intenta nuevamente.');
            return $this->render('cobro-mex');
        }

        if ($folio) {
            if ($folio) {
                $model              = Envio::find()->where(['folio' => $folio])->one();
                if ($model) {
                    $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();

                    return $this->render('cobro-mex', [
                        "folio" => $model
                    ]);
                } else {
                    Yii::$app->session->setFlash('danger', 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.');
                }
            } else {
                Yii::$app->session->setFlash('warning', 'Debes ingresar correctamente el folio.');
            }
        }

        return $this->render('cobro-mex');
    }

    public function actionCobroReenvio()
    {
        if ($response = Yii::$app->request->post()) {
            if (isset($response["id"]) &&  $response["id"]) {
                $model = $this->findModel($response["id"]);
                $model->dir_obj     = $model->direccion;

                if ($model->is_reenvio == Envio::REENVIO_ON) {

                    if ($model->dir_obj)
                        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
                    else {
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
                    return $this->render('cobro-mex', [
                        "folio" => $model
                    ]);
                }
            }
        }
        return $this->render('cobro-mex');
    }

    public function actionUpdateEnvio()
    {
        if ($response = Yii::$app->request->post()) {
            if (isset($response["id"]) && $response["id"] && $response['Envio']['peso_total']) {
                $is_ticket = false;

                $model = $this->findModel($response["id"]);
                $is_ticket  = floatval($model->peso_total) > floatval($response['Envio']['peso_total']) ? true : false;
                $lb_dif     = round(floatval($response['Envio']['peso_total'] - floatval($model->peso_total)), 2);
                if ($model) {
                    $model->dir_obj             = $model->direccion;
                    $model->subtotal            = isset($response['Envio']['subtotal']) ? $response['Envio']['subtotal']       : 0;
                    $model->total               = isset($response['Envio']['total']) ? $response['Envio']['total']             : 0;
                    $model->peso_total          = isset($response['Envio']['peso_total']) ? $response['Envio']['peso_total']   : 0;

                    if ($model->update()) {
                        $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();
                        Yii::$app->session->setFlash('success', "Se ha modificado correctamente el peso al folio " . $model->folio);
                        return $this->render('cobro-mex', [
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

    public function actionCobroEnvio()
    {
        $response = Yii::$app->request->post();
        if (isset($response["id"]) && $response["id"]) {

            $model = $this->findModel($response["id"]);
            if (isset($response["descuento_manual_check"]) && $response["descuento_manual_check"]) {
                $envio                      = Envio::findOne($model->id);
                $envio->dir_obj             = $envio->direccion;
                $envio->is_descuento_manual = Envio::DESCUENTO_ON;
                $envio->nota                = isset($response['nota']) && $response['nota'] ? $response['nota'] : NULL;
                $envio->descuento_manual    = isset($response['descuento_manual'])  && $response['descuento_manual']  ? $response['descuento_manual']  : 0;

                if ($envio->total != $response['total'])
                    $envio->total  =  $response['total'];

                if ($envio->subtotal != $response['subtotal'])
                    $envio->subtotal  =  $response['subtotal'];

                $envio->total               = $envio->total - $envio->descuento_manual;
                $envio->save();
            } else {
                $envio = Envio::findOne($model->id);
                $envio->dir_obj             = $envio->direccion;
                $envio->nota                = isset($response['nota']) && $response['nota'] ? $response['nota'] : NULL;
                if ($envio->total != $response['total'])
                    $envio->total  =  $response['total'];

                if ($envio->subtotal != $response['subtotal'])
                    $envio->subtotal  =  $response['subtotal'];

                $envio->save();
            }

            $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();

            if ($model->cobroRembolsoEnvio->load(Yii::$app->request->post())) {
                if ($model->cobroRembolsoEnvio->saveCobroEnvio($model->id)) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }

            return $this->render('cobro-mex', [
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

            for ($i = 0; $i < $longitud; $i++) {
                $clave .= $caracteres[rand(0, strlen($caracteres) - 1)];
            }

            $model->ticket->clave = $clave;

            if ($model->ticket->save())
                return $this->redirect(['view', 'id' => $model->id]);
        } elseif ($response = Yii::$app->request->post()) {
            if (!isset($response['status'])) {
                $model->subtotal            = isset($response['subtotal']) ? $response['subtotal']       : 0;
                $model->total               = isset($response['total']) ? $response['total']             : 0;
                $model->precio_libra_actual = isset($response['precioLibra']) ? $response['precioLibra'] : 0;
                $model->status = Envio::STATUS_AUTORIZADO;
            } else
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

        $producto                   = new Producto;
        $model->envio_detalle       = new EnvioDetalle();
        $model->cobroRembolsoEnvio  = new CobroRembolsoEnvio();

        if ($model->load(Yii::$app->request->post()) && $model->envio_detalle->load(Yii::$app->request->post()) && $model->cobroRembolsoEnvio->load(Yii::$app->request->post())) {
            $model->origen      = Envio::ORIGEN_MX;
            $model->tipo_envio          = Envio::TIPO_ENVIO_MEX;
            $model->is_descuento_manual = $model->is_descuento_manual == 1 ? Envio::DESCUENTO_ON : null;

            $Folio          = Envio::find()->where(["tipo_envio" => Envio::TIPO_ENVIO_MEX])->count();
            $model->status  = Envio::STATUS_HABILITADO;


            $Folio          =  intval($Folio) + 1;

            $model->folio   = Envio::CLAVE_SERV_MEX . str_pad($Folio, 6, "0", STR_PAD_LEFT);

            //$model->folio       =  $model->created_user_by->sucursal->clave ? $model->created_user_by->sucursal->clave. "-" . str_pad($Folio, 6 , "0",STR_PAD_LEFT) : "XXX-" . str_pad($Folio, 6 , "0",STR_PAD_LEFT) ;

            $model->impuesto                = Yii::$app->request->post()['costo_extra_total_envio'];
            $model->subtotal                = Yii::$app->request->post()['subtotal_total_envio'];
            $model->peso_mex_con_empaque    = Yii::$app->request->post()['peso_mex_con_empaque'];
            $model->valor_declarado_total    = Yii::$app->request->post()['valor_declarado_total'];
            $model->total                   = Yii::$app->request->post()['total_envio'];

            $model->created_at              = isset(Yii::$app->request->post()['checkRegistro']) && $model->created_at_temp ? strtotime($model->created_at_temp) + 86340  : null;


            if ($model->save()) {
                if ($model->envio_detalle->saveEnvioDetalleMex($model->id, $model->folio, $model->sucursal_receptor_names, $model->cliente_receptor_names)) {
                    if ($model->cobroRembolsoEnvio->saveCobroEnvio($model->id, true)) {
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'producto'  => $producto,
        ]);
    }






    public function actionFormComentarioExtra()
    {

        $model = $this->findModel(Yii::$app->request->post()["Envio"]["id"]);
        $model->load(Yii::$app->request->post());

        $model->dir_obj   = $model->direccion;
        if ($model->dir_obj)
            $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
        else {
            $model->dir_obj = new EsysDireccion([
                'cuenta' => EsysDireccion::CUENTA_REENVIO,
                'tipo'   => EsysDireccion::TIPO_PERSONAL,
            ]);
        }

        if ($model->update())
            Yii::$app->session->setFlash('success', "Se actualizo correctamente : #" . $model->folio);
        else
            Yii::$app->session->setFlash('danger', "Ocurrio un error al actualizar el envio : #" . $model->folio);

        return $this->redirect(['view', 'id' => $model->id]);
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

        $producto                   = new Producto;

        //  $model->cliente_receptor = Cliente::findOne($model->cliente_receptor_id);
        //$model->cliente_receptor->dir_obj = $model->cliente_receptor->direccion;
        $model->cliente_receptor    = new Cliente();
        $model->cobroRembolsoEnvio  = new CobroRembolsoEnvio();
        $model->envio_detalle       = new EnvioDetalle();

        $model->dir_obj   = $model->direccion;

        if ($model->dir_obj)
            $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
        else {
            $model->dir_obj = new EsysDireccion([
                'cuenta' => EsysDireccion::CUENTA_REENVIO,
                'tipo'   => EsysDireccion::TIPO_PERSONAL,
            ]);
        }

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if ($model->load(Yii::$app->request->post()) && $model->envio_detalle->load(Yii::$app->request->post()) && $model->cobroRembolsoEnvio->load(Yii::$app->request->post())) {




            $model->impuesto                = Yii::$app->request->post()['costo_extra_total_envio'];
            $model->subtotal                = Yii::$app->request->post()['subtotal_total_envio'];
            $model->peso_mex_con_empaque    = Yii::$app->request->post()['peso_mex_con_empaque'];
            $model->valor_declarado_total    = Yii::$app->request->post()['valor_declarado_total'];
            $model->total                   = Yii::$app->request->post()['total_envio'];

            if ($model->save()) {
                if ($model->envio_detalle->saveEnvioDetalleMex($model->id, $model->folio, $model->sucursal_receptor_names, $model->cliente_receptor_names)) {
                    if ($model->cobroRembolsoEnvio->saveCobroEnvio($model->id, true)) {
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        }

        $model->setSucursalAsignarNames();
        $model->setClienteAsignarNames();

        return $this->render('update', [
            'model' => $model,
            'producto'  => $producto,
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
            'format' => array(45, 60), //Pdf::FORMAT_LETTER,
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

        $lengh = $lengh + ($count  * 90);
        $lengh = $lengh + ($total_piezas * 7);

        $width = $width + ($count  * 2);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            //'format' => array(72, $lengh),//Pdf::FORMAT_A4,
            'format' => array($width, $lengh), //Pdf::FORMAT_A4,
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
                'SetHeader' => ['Ticket de envio #' . $model->folio],
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
            } else {
                return [
                    'code' => 10,
                    'message' => 'Error en el registro'
                ];
            }
        }
    }

    /**
     * ===========================================================
     * 
     * ===========================================================
     */

    public function actionImprimirEtiquetasAll($id)
    {

        $model = Envio::findOne($id);

        /*$ImpresionTicketCobro = new ImpresionTicketCobro();
        $ImpresionTicketCobro->envio_detalle_id = $model->id;
        $ImpresionTicketCobro->count        = $model->cantidad;
        $ImpresionTicketCobro->user_id      = Yii::$app->user->identity->id;
        $ImpresionTicketCobro->created_at   = time();
        $ImpresionTicketCobro->save();*/

        //$content = $this->renderPartial('etiqueta', ["model" => $model]);
        $content = "";
        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array(206, 145), //Pdf::FORMAT_LETTER,
            // portrait orientation
            'orientation' => Pdf::ORIENT_LANDSCAPE,
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

        $pdf->marginLeft = 2;
        $pdf->marginRight = 2;

        $pdf->setApi();
        $pdf_api = $pdf->getApi();



        $count_show = 0;
        foreach ($model->envioDetalles as $key => $item) {

            $content = $this->renderPartial('etiqueta', ["model" => $item]);
            $pdf_api->WriteHTML($content);
            // if (count($model->getSucursalReparto()) < ($key + 1) )

            $count_show = $count_show + 1;

            if (count($model->envioDetalles) > $count_show) {

                $pdf_api->AddPage();
            }
        }

        // return the pdf output as per the destination setting
        return $pdf->render();
    }


    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionSucursalInfoAjax($q = false)
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

    public function actionSendProductoAjax($q = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $Producto = new Producto();
        if ($Producto->load(Yii::$app->request->post())) {
            //$Producto->is_impuesto = isset(Yii::$app->request->post()["is_impuesto"]) ? Producto::IS_IMPUESTO_ON : null;
            $Producto->status           = Producto::STATUS_ACTIVE;
            $Producto->tipo_servicio    = Envio::TIPO_ENVIO_MEX;
            //$Producto->is_denegado      = Producto::IS_AUTORIZAR;

            if ($Producto->save()) {
                return [
                    "code"      => 202,
                    "message"   => "Se guardo correctamente el Producto "
                ];
            }
        }
        return [
            "code"      => 10,
            "message"   => "Error al guardar el producto, intente nuevamente "
        ];
    }


    public function actionGetProductoMex()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {

            $sucursal_receptor_id       = $request->get('sucursal_receptor_id') ? $request->get('sucursal_receptor_id') :  null;
            $destino_id                 = $request->get('destino_id') ? $request->get('destino_id') :  null;
            $producto_id                = $request->get('producto_id') ? $request->get('producto_id') :  null;

            if ($sucursal_receptor_id && $producto_id) {
                $producto = ViewProducto::getProductoMexAjax($producto_id, $sucursal_receptor_id, $destino_id);

                return [
                    "code" => 202,
                    "producto" => $producto,
                ];
            }

            return [
                "code" => 10,
                "message" => "Ocurrio un error, intenta nuevamente",
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetPrecioLibra()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {

            $tipo_destino           = $request->get('tipo_destino') ? $request->get('tipo_destino') :  null;
            $sucursal_receptor_id   = $request->get('sucursal_receptor_id') ? $request->get('sucursal_receptor_id') :  null;

            if ($sucursal_receptor_id) {



                $ListaPrecioMx = ListaPrecioMx::find()->andWhere([
                    "and",
                    ["=", "sucursal_recibe_id", $sucursal_receptor_id],
                    ['=', 'sucursal_envia_id', Yii::$app->user->identity->sucursal_id],
                    ['=', 'destino_id', $tipo_destino],
                    ['=', 'tipo', ListaPrecioMx::TIPO_LIBRA],
                ])->one();


                if (!isset($ListaPrecioMx->id)) {
                    $ListaPrecioMx = ListaPrecioMx::find()->andWhere([
                        "and",
                        ["=", "sucursal_recibe_id", $sucursal_receptor_id],
                        ['=', 'default', ListaPrecioMx::IS_DEFAULT],
                        ['=', 'destino_id', $tipo_destino],
                        ['=', 'tipo', ListaPrecioMx::TIPO_LIBRA],
                    ])->one();
                }


                return [
                    "code" => 202,
                    "precio_libra" => isset($ListaPrecioMx->id) ? $ListaPrecioMx->precio_libra : 0,
                ];
            }

            return [
                "code" => 10,
                "message" => "Ocurrio un error, intenta nuevamente",
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionCreateTicketAjax()
    {
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
            for ($i = 0; $i < $longitud; $i++) {
                $clave .= $caracteres[rand(0, strlen($caracteres) - 1)];
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

    public function actionClienteInfoAjax($q = false)
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

            // Obtenemos sucursal
            $cliente  = ViewCliente::getClienteAjax($text, true);

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
    public function actionSucursalesEstadoAjax($q = false)
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

    public function actionProductosCategoriaAjax()
    {
        return ViewProducto::getProductoAllJsonBtt(Yii::$app->request->get());
    }

    /*public function actionProductoInfoAjax(){
        return ViewProducto::getProductoDetalleAjax(Yii::$app->request->get());
    }*/

    public function actionEnviosJsonBtt()
    {
        return ViewEnvio::getJsonBtt(Yii::$app->request->get());
    }

    public function actionEnvioDetalleAjax()
    {
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
                ->andWhere(['envio_id'    => $text])->all();

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
