<?php

namespace app\modules\operacion\controllers;

use Yii;
use app\models\sucursal\ViewPromos;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use kartik\mpdf\Pdf;
use app\models\user\User;
use app\models\envio\Envio;
use app\models\envio\EnvioComplementoPromocion;
use app\models\envio\ViewEnvio;
use app\models\cliente\Cliente;
use app\models\esys\EsysSetting;
use app\models\envio\EnvioDetalle;
use app\models\envio\EnvioPromocion;
use app\models\esys\EsysDireccion;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysCambiosLog;
use app\models\sucursal\ViewSucursal;
use app\models\producto\ViewProducto;
use app\models\producto\Producto;
use app\models\promocion\ViewPromocion;
use app\models\cliente\ClienteCodigoPromocion;
use app\models\movimiento\MovimientoPaquete;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\ViewCliente;
use app\models\Esys;
use app\models\promocion\Promocion;
use app\models\ticket\Ticket;
use app\models\envio\ImpresionTicketCobro;
use app\models\viaje\ViajeDetalle;
use app\models\pais\PaisesLatam;

/**
 * Default controller for the `clientes` module
 */
class EnvioController extends  \app\controllers\AppController
{

    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create_basic' => Yii::$app->user->can('envioBasicCreate'),
            'update_basic' => Yii::$app->user->can('envioBasicUpdate'),
            'descuentoVictor' => Yii::$app->user->can('descuentoVictor'),
            'delete_basic' => Yii::$app->user->can('envioBasicDelete'),
            'cancel_basic' => Yii::$app->user->can('envioBasicCancel'),
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
        $model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_REENVIO_PAQUETE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

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
    public function actionCreate($pais = null)
    {
        if (!isset($pais) || empty($pais)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona un país antes de continuar.');
            return $this->redirect(['/site/index']); // Redirige al home (ajusta la ruta según sea necesario)
        } else if (!PaisesLatam::findOne($pais)) {
            Yii::$app->session->setFlash('warning', 'País no encontrado');
            return $this->redirect(['/site/index']); // Redirige al home (ajusta la ruta según sea necesario)
        }

        $modelPais = PaisesLatam::findOne($pais);
        //print_r($modelPais);die;
        $model = new Envio();
        $model->cliente          = new  Cliente();
        $model->cliente_emisor   = new  Cliente();
        $model->cliente_receptor = new  Cliente();

        $model->created_user_by  =  User::findOne(Yii::$app->user->identity->id);

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


        $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();

        ini_set('memory_limit', '-1');
        if ($model->load(Yii::$app->request->post()) && $model->envio_detalle->load(Yii::$app->request->post()) && $model->cobroRembolsoEnvio->load(Yii::$app->request->post())) {

            //print_r(Yii::$app->request->post());
            //die;
            //


            #$model->pais_destino_id = $model->pais_destino_id
            #    ? $model->pais_destino_id
            #    : ($model->cliente->pais ? $model->cliente->pais->id : null);
            #
            //if ($model->is_reenvio == Envio::REENVIO_ON && !$model->envio_detalle->dir_obj_array)
            //$model->dir_obj->load(Yii::$app->request->post());

            $model->is_zona_riesgo = Yii::$app->request->post()["input_zona_roja"] == 'true' ? 1 : 0;
            $model->origen = Envio::ORIGEN_USA;
            $Folio =  Envio::find(["tipo_envio" => $model->tipo_envio])->orderBy("id desc")->one();
            $Folio = isset($Folio->id) ? $Folio->id + 1 : 1;

            $model->folio       =  $model->created_user_by->sucursal->clave ? $model->created_user_by->sucursal->clave . "-" . str_pad($Folio, 6, "0", STR_PAD_LEFT) : "XXX-" . str_pad($Folio, 6, "0", STR_PAD_LEFT);


            $model->precio_libra_actual = $model->tipo_envio == Envio::TIPO_ENVIO_TIERRA  ? $model->created_user_by->sucursal->costo_libra   : $model->created_user_by->sucursal->costo_libra_aire;




            $model->peso_total          = Yii::$app->request->post()['peso_total'];

            $model->sucursal_emisor_id  = $model->created_user_by->sucursal_id;

            $model->peso_reenvio        = isset(Yii::$app->request->post()['peso_reenvio']) && Yii::$app->request->post()['peso_reenvio'] ? Yii::$app->request->post()['peso_reenvio'] : null;

            if ($model->save()) {
                //echo '<pre>';
                //print_r($model->cliente_id);
                //die;
                if ($model->envio_detalle->saveEnvioDetalle($model->id, $model->folio)) {
                    if ($model->cobroRembolsoEnvio->saveCobroEnvio($model->id)) {
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        }
        return $this->render('create', [
            'pais' => $modelPais,
            'model'     => $model,
            'can'   => $this->can,
            'producto'  => $producto,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);


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
        else {
            $model->dir_obj = new EsysDireccion([
                'cuenta' => EsysDireccion::CUENTA_REENVIO,
                'tipo'   => EsysDireccion::TIPO_PERSONAL,
            ]);
        }

        $model->cobroRembolsoEnvio = new CobroRembolsoEnvio();



        $model->cliente_emisor     = Cliente::findOne($model->cliente_emisor_id) ? Cliente::findOne($model->cliente_emisor_id) :  new  Cliente();




        $model->cliente_emisor->dir_obj = isset($model->cliente_emisor->id) ? $model->cliente_emisor->direccion :  new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);


        $model->cliente_receptor = new Cliente();
        //$model->cliente_receptor = Cliente::findOne($model->cliente_receptor_id);
        //$model->cliente_receptor->dir_obj = $model->cliente_receptor->direccion;*/

        $model->envio_detalle   = new EnvioDetalle();
        //echo '<pre>';
        //print_r($model);die;





        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if ($model->load(Yii::$app->request->post()) && $model->envio_detalle->load(Yii::$app->request->post()) && $model->cobroRembolsoEnvio->load(Yii::$app->request->post())) {
            if ($model->is_reenvio == Envio::REENVIO_ON)
                $model->dir_obj->load(Yii::$app->request->post());


            //$model->precio_libra_actual  =  $model->tipo_envio == Envio::TIPO_ENVIO_TIERRA  ? $model->created_user_by->sucursal->costo_libra   : $model->created_user_by->sucursal->costo_libra_aire;
            //echo '<pre>';
            //$post = Yii::$app->request->post();
            ////print_r($post);die;
            //print_r(json_decode($post['EnvioDetalle']['envio_detalle_array'])) ;die;
            $model->pais_destino_id = $model->pais_destino_id
                ? $model->pais_destino_id
                : ($model->cliente->pais ? $model->cliente->pais->id : null);
            

            
            $model->peso_total  = Yii::$app->request->post()['peso_total'];

            if ($model->status == Envio::STATUS_SOLICITADO) {
                $model->status     = Envio::STATUS_HABILITADO;
                $model->created_at = time();
            }

            $model->peso_reenvio        = isset(Yii::$app->request->post()['peso_reenvio']) && Yii::$app->request->post()['peso_reenvio'] ? Yii::$app->request->post()['peso_reenvio'] : NULL;
            


            if ($model->save()) {
                if ($model->envio_detalle->saveEnvioDetalle($model->id, $model->folio)) {
                    if ($model->cobroRembolsoEnvio->saveCobroEnvio($model->id)) {
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        }

        $model->setSucursalAsignarNames();
        $model->setClienteAsignarNames();

        return $this->render('update', [
            'model' => $model,
            'can'   => $this->can,
            'producto'  => $producto,
        ]);
    }

    public function actionImprimirTicket($id)
    {
        $model = $this->findModel($id);
        $longitud_base = 520; // Longitud base para encabezados y pies de página en píxeles
        $longitud_extra_por_elemento = 0.3; // Altura adicional por cada detalle de envío no cancelado en píxeles
        $width = 80;
        $count = 0;
        $total_piezas = 0;

        foreach ($model->envioDetalles as $item) {
            if ($item->status != EnvioDetalle::STATUS_CANCELADO) {
                $count++;
                $total_piezas += $item->cantidad;
                $longitud_base += $longitud_extra_por_elemento; // Incrementa la longitud del ticket por cada detalle no cancelado
            }
        }

        $lengh = $longitud_base; // Establece la longitud final del ticket

        // Ahora usa $lengh y $width para la impresión


        //$lengh += $count * 10;

        //$lengh = $lengh + ($count  * 90);
        //$lengh = $lengh + ($total_piezas * 7);
        //
        //$width = $width + ($count  * 2);

        $content = $this->renderPartial('ticket', ["model" => $model]);
        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
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
                'SetHeader' => [($model->pre_created_at ? 'Precaptura: ' . date('Y-m-d', $model->pre_created_at) . ' -  Fecha ' . ($model->created_at != 0  ? date('Y-m-d', $model->created_at) : 'N/A') . '<br>' : ' Fecha ' . date('Y-m-d', $model->created_at)) . ' /  Ticket de envio #' . $model->folio],
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

    public function actionImprimirTicketComprimido($id)
    {
        $model = $this->findModel($id);
        $lengh = 370;
        $width = 72;
        $count = 0;
        $total_piezas = 0;
        foreach ($model->envioDetalles as $key => $item) {
            $count = $count + 1;
            $total_piezas = $total_piezas + $item->cantidad;
        }

        $lengh = $lengh + ($count  * 75);
        //$lengh = $lengh + ( $total_piezas * 7);

        $width = $width + ($count  * 2);

        $content = $this->renderPartial('ticket', ["model" => $model, "is_comprimido" => true]);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
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
                'SetHeader' => [($model->pre_created_at ? 'Precaptura: ' . date('Y-m-d', $model->pre_created_at) . ' -  Fecha ' . ($model->created_at != 0  ? date('Y-m-d', $model->created_at) : 'N/A') . '<br>' : ' Fecha ' . date('Y-m-d', $model->created_at)) . ' /  Ticket de envio #' . $model->folio],
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

    public function actionImprimirEtiqueta($id)
    {

        $model = EnvioDetalle::findOne($id);

        $ImpresionTicketCobro = new ImpresionTicketCobro();
        $ImpresionTicketCobro->envio_detalle_id = $model->id;
        $ImpresionTicketCobro->count        = $model->cantidad;
        $ImpresionTicketCobro->user_id      = Yii::$app->user->identity->id;
        $ImpresionTicketCobro->created_at   = time();
        $ImpresionTicketCobro->save();

        $content = $this->renderPartial('etiqueta', ["model" => $model]);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array(215, 145), //Pdf::FORMAT_LETTER,
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


        // return the pdf output as per the destination setting
        return $pdf->render();
    }

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


    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->status = Envio::STATUS_CANCELADO;

        $model->update();

        Yii::$app->session->setFlash('success', "Se ha cancelado en envio #" . $model->folio);

        return $this->redirect(['index']);
    }


    public function actionGetCaja()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {


            if (isset($request->get()["caja_id"]) && $request->get()["caja_id"]) {
                //$pais_id = $request->get()["pais_id"];

                $caja = Producto::getProductoFull($request->get()["caja_id"]);


                $response = ["code" => 10, "caja" =>  $caja];
            } else
                $response = ["code" => 30, "message" => "Error al cargar los datos, verifique su información"];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    /**
     * =================================================================================================
     *                   NUEVOS CAMBIOS 
     * =================================================================================================
     */
    public function actionGetProductosCaja()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = [];

        if ($request->validateCsrfToken() && $request->isAjax) {

            $tipo = intval($request->get()["tipo"]);
            $pais = intval($request->get()["pais_id"]);
            if ($tipo) {
                switch ($tipo) {
                    case 20:
                        $caja = Producto::getCaja($tipo);
                        $response = ["code" => 10, "caja" =>  $caja];
                        break;
                    case 30:
                        $caja = Producto::getCajaSinLimitePais($tipo, $pais);
                        $response = ["code" => 10, "caja" =>  $caja];
                        break;

                    default:
                        $response = ["code" => 30, "message" => "Error al cargar los datos, verifique su información"];
                        break;
                }
                //$caja = Producto::getCaja($tipo);


                $response = ["code" => 10, "caja" =>  $caja];
            } else
                $response = ["code" => 30, "message" => "Error al cargar los datos, verifique su información"];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
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

    public function actionSucursalPromos()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            $id = Yii::$app->request->get('id');

            return ViewPromos::getPromosBySuc($id);
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    public function actionClienteZona()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            $code = Yii::$app->request->get('code');
            $cliente_id = Yii::$app->request->get('cliente_id');
            $model = Cliente::findOne($cliente_id);

            $country = $model->country_id;
            return [
                'code'  => 202,
                'is_zona_roja' => Cliente::isZonaRiesgo($country, $code),
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

    public function actionEnviosJsonBtt()
    {
        return ViewEnvio::getJsonBtt(Yii::$app->request->get());
    }

    public function actionEnviosRecibidosJsonBtt()
    {
        return ViewEnvio::getRecibidosJsonBtt(Yii::$app->request->get());
    }

    public function actionEnvioDetalleAjax()
    {
        return ViewEnvio::getEnvioDetalleAjax(Yii::$app->request->get());
    }

    public function actionEsysDireccionAjax()
    {
        return ViewEnvio::getEsysDireccionAjax(Yii::$app->request->get());
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

    public function actionCategoriaAjax($q = false)
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            if ($q) {
                $text = $q;
            } else {
                $text = Yii::$app->request->get('tipo_servicio');
            }

            if ($text == Envio::TIPO_ENVIO_MEX)
                $categoria = EsysListaDesplegable::getItems('categoria_paquete_mex', true);
            else
                $categoria = EsysListaDesplegable::getItems('categoria_paquete_lax_tierra', true);

            return $categoria;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionSendProductoAjax($q = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $Producto = new Producto();
        if ($Producto->load(Yii::$app->request->post())) {
            $Producto->is_impuesto = isset(Yii::$app->request->post()["is_impuesto"]) ? Producto::IS_IMPUESTO_ON : null;
            $Producto->status = Producto::STATUS_ACTIVE;
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

    public function actionSucursalUpdateAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('paquete_id') && $request->get('sucursal_id')) {
                // Obtenemos ViewPromocion
                $EnvioDetalle =  EnvioDetalle::findOne($request->get('paquete_id'));

                if (isset($EnvioDetalle->id)) {

                    $sucursalReceptor = Sucursal::findOne($EnvioDetalle->sucursal_receptor_id);
                    $sucursalRequest  = Sucursal::findOne($request->get('sucursal_id'));


                    $EnvioDetalle->CambiosLog = new EsysCambiosLog((new Envio));


                    if ($sucursalReceptor->is_reenvio == EnvioDetalle::REENVIO_ON) {
                        if ($sucursalRequest->is_reenvio != EnvioDetalle::REENVIO_ON) {


                            $EnvioDetalle->CambiosLog->updateValue('#paquete', 'old', '********* Se modifico el  paquete Tracked: #' . $EnvioDetalle->tracked . ' Reenvío  : SI');

                            $EnvioDetalle->CambiosLog->updateValue('#paquete', 'dirty', 'NO');

                            $EnvioDetalle->is_reenvio = null;

                            $EsysDireccion = EsysDireccion::find()->andWhere(['cuenta_id' => $EnvioDetalle->id])->andWhere(['cuenta' => EsysDireccion::CUENTA_REENVIO_PAQUETE, 'tipo' => EsysDireccion::TIPO_PERSONAL])->all();

                            foreach ($EsysDireccion as $key => $direccion) {
                                $direccion->delete();
                            }

                            $EnvioDetalle->CambiosLog->createLog($EnvioDetalle->envio->id);
                        }
                    }

                    $EnvioDetalle->CambiosLog->updateValue('#paquete', 'old', '** ' . $EnvioDetalle->sucursalReceptor->nombre);

                    $EnvioDetalle->sucursal_receptor_id = $request->get('sucursal_id');

                    $EnvioDetalle->CambiosLog->updateValue('#paquete', 'dirty', $sucursalRequest->nombre);


                    if ($EnvioDetalle->update()) {
                        $EnvioDetalle->CambiosLog->createLog($EnvioDetalle->envio->id);
                        return [
                            "code"      => 202,
                            "message"   => "Se realizo la operación correctamente",
                            "type"      => "Success"
                        ];
                    }
                }

                return [
                    "code"      => 10,
                    "message"   => "Ingresa correctamente el paquete, Intenta nuevamente",
                    "type"      => "Warning"
                ];
            }
            return [
                "code"      => 10,
                "message"   => "Ocurrio un error, Intenta nuevamente",
                "type"      => "Error"
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionShowDireccionPaquete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('paquete_id')) {
                // Obtenemos ViewPromocion

                $EsysDireccion =  EsysDireccion::find()
                    ->andWhere(['cuenta_id'     => $request->get('paquete_id')])
                    ->andWhere(['cuenta'        => EsysDireccion::CUENTA_REENVIO_PAQUETE])
                    ->andWhere(['tipo'          => EsysDireccion::TIPO_PERSONAL])->one();

                if (isset($EsysDireccion->id)) {

                    $direccion = [
                        "estado"    => isset($EsysDireccion->estado) ? $EsysDireccion->estado->singular : null,
                        "estado_id" => isset($EsysDireccion->estado_id) ? $EsysDireccion->estado_id : null,
                        "municipio" => isset($EsysDireccion->municipio) ? $EsysDireccion->municipio->singular : null,
                        "municipio_id" => isset($EsysDireccion->municipio_id) ? $EsysDireccion->municipio_id : null,
                        "colonia"   => isset($EsysDireccion->esysDireccionCodigoPostal) ? $EsysDireccion->esysDireccionCodigoPostal->colonia : null,
                        "codigo_search"   => isset($EsysDireccion->esysDireccionCodigoPostal) ? $EsysDireccion->esysDireccionCodigoPostal->codigo_postal : null,
                        "cp_id"     => isset($EsysDireccion->esysDireccionCodigoPostal) ? $EsysDireccion->esysDireccionCodigoPostal->id : null,
                        "direccion" => $EsysDireccion->direccion,
                        "n_exterior" => $EsysDireccion->num_ext,
                        "n_interior" => $EsysDireccion->num_int,
                        "codigo_postal" => isset($EsysDireccion->esysDireccionCodigoPostal) ? $EsysDireccion->esysDireccionCodigoPostal->codigo_postal : null,
                        "referencia" => $EsysDireccion->referencia,

                    ];

                    return [
                        "code"      => "202",
                        "message"   => "Success",
                        "data"      => $direccion,
                        "type"      => "Success"
                    ];
                }

                return [
                    "code"      => "11",
                    "message"   => "No se encontro ninguna dirección de reenvio",
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

    public function actionUpdateDireccionAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {



            if (isset(Yii::$app->request->post()["EsysDireccion"]["cuenta_id"]) && Yii::$app->request->post()["EsysDireccion"]["cuenta_id"]) {
                $model =  EsysDireccion::find()
                    ->andWhere(["cuenta_id" => Yii::$app->request->post()["EsysDireccion"]["cuenta_id"]])
                    ->andWhere(['cuenta' => EsysDireccion::CUENTA_REENVIO_PAQUETE])
                    ->andWhere(['tipo'   => EsysDireccion::TIPO_PERSONAL])->one();
                if ($model->load(Yii::$app->request->post())) {
                    // Guardar cliente
                    $model->is_check = null;
                    if ($model->save())
                        $response = ["code" => 10, "message" =>  "Se modifico correctamente la dirección de reenvío"];
                    else
                        $response = ["code" => 20, "message" =>  "Error al modificar la dirección, intente nuevamente"];
                }
            } else
                $response = ["code" => 30, "message" => "Error al cargar los datos de reenvio, verifique su información"];


            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionUpdateDireccionAllAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {

            if (isset(Yii::$app->request->post()["Envio"]["id"]) && Yii::$app->request->post()["Envio"]["id"]) {

                $Envio = Envio::findOne(Yii::$app->request->post()["Envio"]["id"]);
                $dataDireccion = Yii::$app->request->post();
                foreach ($Envio->envioDetalles as $key => $envioDetalles) {
                    if ($envioDetalles->is_reenvio == EnvioDetalle::REENVIO_ON) {

                        $dataDireccion["EsysDireccion"]["cuenta_id"] = $envioDetalles->id;
                        $model =  EsysDireccion::find()
                            ->andWhere(["cuenta_id" => $dataDireccion["EsysDireccion"]["cuenta_id"]])
                            ->andWhere(['cuenta' => EsysDireccion::CUENTA_REENVIO_PAQUETE])
                            ->andWhere(['tipo'   => EsysDireccion::TIPO_PERSONAL])->one();

                        $model->load($dataDireccion);
                        $model->is_check = null;
                        $model->save();
                    }
                }
                $response = ["code" => 10, "message" =>  "Se modifico correctamente la direcciones de reenvío"];
            } else
                $response = ["code" => 30, "message" => "Error al cargar los datos de reenvio, verifique su información"];


            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionUpdateCobroAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {


            if (isset(Yii::$app->request->post()["cobros_array"]) && Yii::$app->request->post()["cobros_array"]) {
                $cobros_array = Yii::$app->request->post()["cobros_array"];
                foreach ($cobros_array as $key => $cobro) {
                    $CobroRembolsoEnvio =  CobroRembolsoEnvio::findOne($cobro["id"]);
                    $CobroRembolsoEnvio->cantidad = $cobro["monto"];
                    $CobroRembolsoEnvio->save();
                }
                $response = ["code" => 10, "message" =>  "Se modifico correctamente los cobros del envio"];
            } else
                $response = ["code" => 30, "message" => "Error al cargar los datos de los cobros, verifique su información"];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionShowMovimientoPaquete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('paquete_id')) {
                // Obtenemos ViewPromocion

                $EnvioDetalle =  EnvioDetalle::findOne($request->get('paquete_id'));

                if (isset($EnvioDetalle->id)) {
                    $movimintoAll = [];
                    $movimintoAllPaquetes = [];
                    for ($i = 0; $i < $EnvioDetalle->cantidad; $i++) {
                        $tracked_movimiento = $EnvioDetalle->tracked . "/" . ($i + 1);
                        $MovimientoPaquete = MovimientoPaquete::getMovimientoItem($tracked_movimiento);
                        array_push($movimintoAll, $MovimientoPaquete);
                    }

                    foreach ($movimintoAll as $key => $item) {
                        $paquete = [];

                        foreach ($item as $key2 => $movimiento) {
                            $movimientoItem = [];
                            $movimientoItem["id"]           = $movimiento->id;
                            $movimientoItem["paquete_id"]   = $movimiento->paquete_id;
                            $movimientoItem["tracked"]      = $movimiento->tracked;
                            $movimientoItem["tipo"]         = $movimiento->tipo;
                            $movimientoItem["tipo_envio"]   = $movimiento->tipo_envio;
                            $movimientoItem["tipo_movimiento"] = $movimiento->tipo_movimiento;
                            $movimientoItem["caja_id"]      = $movimiento->caja_id;
                            $movimientoItem["fecha_entrega"]      = $movimiento->fecha_entrega;
                            $movimientoItem["reparto_id"]   = $movimiento->reparto_id;
                            $movimientoItem["viaje_id"]     = $movimiento->viaje_id;
                            $movimientoItem["created_at"]   = $movimiento->created_at;
                            $movimientoItem["created_by"]   = $movimiento->created_by;

                            if ($movimiento->tipo_movimiento == MovimientoPaquete::LAX_TIER_SUCURSAL) {
                                $EnvioDetalle = EnvioDetalle::find()->andWhere(['id' => $movimiento->paquete_id])->one();
                                $movimientoItem["peso_usa"]        =  isset($EnvioDetalle->id) ? round($EnvioDetalle->peso / $EnvioDetalle->cantidad, 2) : 0;
                            }

                            if ($movimiento->tipo_movimiento == MovimientoPaquete::LAX_TIER_BODEGA) {
                                $ViajeDetalle = ViajeDetalle::find()->andWhere(['tracked' => $movimiento->tracked])->one();
                                $movimientoItem["peso_mx"]        =  isset($ViajeDetalle->id) ? round($ViajeDetalle->peso_mx, 2) : 0;
                            }
                            array_push($paquete, $movimientoItem);
                        }
                        array_push($movimintoAllPaquetes, $paquete);
                    }

                    return [
                        "code"      => "202",
                        "message"   => "Success",
                        "data"      => $movimintoAllPaquetes,
                        "type"      => "Success"
                    ];
                }

                return [
                    "code"      => "11",
                    "message"   => "No se encontro ningun paquete, intente nuevamente",
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
