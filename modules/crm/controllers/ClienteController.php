<?php
namespace app\modules\crm\controllers;

use Yii;
use yii\base\Model;
use yii\helpers\Html;
use yii\base\InvalidParamException;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\Esys;
use app\models\cliente\Cliente;
use app\models\cliente\ClienteOmitRegister;
use app\models\cliente\ClienteHistoricoCall;
use app\models\cliente\ViewCliente;
use app\models\promocion\ViewPromocion;
use app\models\cliente\ClienteCodigoPromocion;
use app\models\sucursal\Sucursal;
use app\models\sucursal\ViewSucursal;
use app\models\esys\EsysDireccion;
use app\models\pais\ZonasRojas;

/*
use app\models\user\User;
use app\models\user\ViewUser;
use app\models\user\SignupForm;
use app\models\user\LoginForm;
use app\models\user\AccountActivation;
use app\models\user\ResetPasswordForm;
use app\models\user\PasswordResetRequestForm;
use app\models\user\ChangePassword;
use app\models\user\UserAsignarPerfil;
use app\models\esys\EsysDireccion;
use app\models\esys\EsysAcceso;
*/

/**
 * ClienteController implements the CRUD actions for Cliente model.
 */
class ClienteController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('clienteCreate'),
            'update' => Yii::$app->user->can('clienteUpdate'),
            'delete' => Yii::$app->user->can('clienteDelete'),
            'delete' => Yii::$app->user->can('clienteDelete'),
            'updateAgente'      => Yii::$app->user->can('agenteVenta'),
            'historicoCliente'  => Yii::$app->user->can('historicoCliente'),
            'exportCliente'     => Yii::$app->user->can('exportCliente'),
            'historicoPromocion'     => Yii::$app->user->can('historicoPromocion'),
            'historicoSucursal'     => Yii::$app->user->can('historicoSucursal'),
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

        return $this->render('index', [
            'can' => $this->can,
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
        return $this->render('view', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);
    }

    public function actionHistoricoSucursalView($id)
    {
        return $this->render('historico-sucursal-view', [
            'model' => Sucursal::findOne($id),
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
        $model      = new Cliente();
        $model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);



        if ($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post()) ) {
            $post = Yii::$app->request->post();
            //print_r($post);die;
            $pais_id = $post['Cliente']['country_id'];
            $cp = $post['EsysDireccion']['codigo_search'];
    
            // Encuentra la zona roja basada en el país y el código postal
            $zonaRoja = ZonasRojas::find()->where(['pais_id' => $pais_id, 'code' => $cp])->one();

            //print_r($zonaRoja);die;
            
            $model->is_zona_riesgo = $zonaRoja ?Cliente::IS_ZONA_RIESGO: Cliente::IS_NOT_ZONA_RIESGO ;
            
            

            $valid = Cliente::validInfoCreate($model->telefono_movil, $model->telefono);
            if ((isset($valid->telefono) && $valid->telefono) || (isset($valid->telefono_movil) && $valid->telefono_movil) ) {
                return $this->render('create', [
                    'model'     => $model,
                    'valid'     => $valid,
                ]);
            }
            if ($model->validate()) {
                if($model->save()){
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }

        }


        return $this->render('create', [
            'model'     => $model,
        ]);
    }

    public function actionImportCsv()
    {
        $model = new Cliente();

        if(Yii::$app->request->post())
            $model->importCVSFile();

        return $this->render('import_csv', [
            "model" => $model,
        ]);
    }

    /**
     * Updates an existing Cliente and Role models.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param  integer $id The cliente id.
     * @return string|\yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model      = $this->findModel($id);


        $model->dir_obj   = $model->direccion;

        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post()) ){

            if ($model->validate()) {

               $valid = Cliente::validInfoCreate($model->telefono_movil, $model->telefono);
                if ((isset($valid->telefono) && $valid->telefono) || (isset($valid->telefono_movil) && $valid->telefono_movil) ) {
                    return $this->render('create', [
                        'model'     => $model,
                        'valid'     => $valid,
                    ]);
                }
                if($model->save())
                    return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionUpdateAgente($id)
    {

        $model = $this->findModel($id);
        $model->cliente_call = new  ClienteHistoricoCall();

        $model->dir_obj   = $model->direccion;

        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post()) && $model->cliente_call->load(Yii::$app->request->post())){
            $model->cliente_call->cliente_id = $model->id;
            $model->cliente_call->tipo = ClienteHistoricoCall::TIPO_CLIENTE;
            $model->cliente_call->save();
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update-agente', [
            'model' => $model,
        ]);

    }

    /**
     * Deletes an existing Cliente model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param  integer $id The cliente id.
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try{
            // Eliminamos el cliente
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente al cliente #" . $id);

        }catch(\Exception $e){
            if($e->getCode() == 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del cliente.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index', 'tab' => 'index']);
    }

    public function actionHistorialCambios($id)
    {
        $model = $this->findModel($id);

        return $this->render("historial-cambios", [
            'model' => $model,
        ]);
    }


//------------------------------------------------------------------------------------------------//
// BootstrapTable list
//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionClientesJsonBtt(){
        return ViewCliente::getJsonBtt(Yii::$app->request->get());
    }

    public function actionHistoricoVentasJsonBtt(){
        return ViewCliente::getHistoricoVentasJsonBtt(Yii::$app->request->get());
    }

    public function actionHistoricoSucursalJsonBtt(){
        return ViewCliente::getHistoricoSucursalJsonBtt(Yii::$app->request->get());
    }

    public function actionHistoricoPromocionJsonBtt(){
        return ViewCliente::getHistoricoPromocionJsonBtt(Yii::$app->request->get());
    }

    public function actionPromocionInfoAjax(){
        return ViewPromocion::getPromocionDetalleAjax(Yii::$app->request->get());
    }

    public function actionPromocionSucursalInfoAjax(){
        return ViewPromocion::getPromocionDetalleAjax(Yii::$app->request->get());
    }

    public function actionSucursalHistoricoJsonBtt(){
        return ViewSucursal::getSucursalHistoricoAjax(Yii::$app->request->get());
    }

    public function actionClienteCodigoAjax(){
        return ViewCliente::getClienteCodigoAjax(Yii::$app->request->get());
    }

    public function actionClienteAjax($q = false, $cliente_id = false,$pais_id=false)
    {
        $request = Yii::$app->request;
        //$pais_id = Yii::$app->request->get('pais_id');
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            if (is_null($text) && $cliente_id)
                $user = ViewCliente::getClienteAjax($cliente_id,true,$pais_id);
            else
                $user = ViewCliente::getClienteAjax($text,false,$pais_id);
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

    public function actionClienteCreateAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {

            if (isset(Yii::$app->request->post()["Cliente"]["id"]) && Yii::$app->request->post()["Cliente"]["id"]) {
                $model =  $this->findModel(Yii::$app->request->post()["Cliente"]["id"]);
                $model->dir_obj   = $model->direccion;
            }else{
                $model = new Cliente();
                $model->dir_obj = new EsysDireccion([
                    'cuenta' => EsysDireccion::CUENTA_CLIENTE,
                    'tipo'   => EsysDireccion::TIPO_PERSONAL,
                ]);
            }

            if($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post()) ){
                $model->status = Cliente::STATUS_ACTIVE;
               if ($model->validate()) {
                // Guardar cliente
                    if($model->save())
                        $response = [ "code" => 10, "message" =>  ViewCliente::getClienteAjax($model->id,true)];
                }else
                    $response = [ "code" => 20, "message" => "Error al guardar cliente, verifique su información", "data" => $model->errors];
            }else
                $response = [ "code" => 30, "message" => "Error al cargar los datos del cliente, verifique su información"];


            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionPromocionCreateBasicAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {


            if ( isset(Yii::$app->request->post()["id"]) && Yii::$app->request->post()["id"]) {
                $cliente_id = Yii::$app->request->post()["id"];
                $promocion_id = Yii::$app->request->post()["promocion_id"];
                $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $caracteres .= "1234567890";
                $clave = "";
                $longitud = 8;
                for($i=0; $i<$longitud; $i++){
                    $clave.=$caracteres[rand(0,strlen($caracteres)-1)];
                }

                $ClienteCodigoPromocion = new ClienteCodigoPromocion();
                $ClienteCodigoPromocion->promocion_id   = $promocion_id;
                $ClienteCodigoPromocion->cliente_id     = $cliente_id;
                $ClienteCodigoPromocion->clave          = $clave;
                $ClienteCodigoPromocion->tipo           = ClienteCodigoPromocion::TIPO_BASIC;
                $ClienteCodigoPromocion->status         = ClienteCodigoPromocion::STATUS_ACTIVE;

                if ($ClienteCodigoPromocion->validate()) {

                    if($ClienteCodigoPromocion->save())
                        $response = [ "code" => 10, "message" =>  $ClienteCodigoPromocion->clave];
                }else
                    $response = [ "code" => 20, "message" => "Error al guardar promocion, verifique su información"];
            }

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }
    public function actionPromocionCreateSucursalAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {


            if ( isset(Yii::$app->request->post()["id"]) && Yii::$app->request->post()["id"]) {
                $cliente_id = Yii::$app->request->post()["id"];
                $promocion_id = Yii::$app->request->post()["promocion_id"];
                $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $caracteres .= "1234567890";
                $clave = "";
                $longitud = 8;
                for($i=0; $i<$longitud; $i++){
                    $clave.=$caracteres[rand(0,strlen($caracteres)-1)];
                }

                $ClienteCodigoPromocion = new ClienteCodigoPromocion();
                $ClienteCodigoPromocion->promocion_id   = $promocion_id;
                $ClienteCodigoPromocion->cliente_id     = $cliente_id;
                $ClienteCodigoPromocion->clave          = $clave;
                $ClienteCodigoPromocion->tipo           = ClienteCodigoPromocion::TIPO_SUCURSAL;
                $ClienteCodigoPromocion->status         = ClienteCodigoPromocion::STATUS_ACTIVE;

                if ($ClienteCodigoPromocion->validate()) {

                    if($ClienteCodigoPromocion->save())
                        $response = [ "code" => 10, "message" =>  $ClienteCodigoPromocion->clave];
                }else
                    $response = [ "code" => 20, "message" => "Error al guardar promocion, verifique su información"];
            }

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    public function actionPromocionCreateEspecialAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {


            if ( isset(Yii::$app->request->post()["id"]) && Yii::$app->request->post()["id"]) {
                $cliente_id = Yii::$app->request->post()["id"];
                $fecha_ini  = strtotime(substr(Yii::$app->request->post()["date_range"], 0, 10));
                $fecha_fin  = strtotime(substr(Yii::$app->request->post()["date_range"], 13, 23)) + 86340;

                $ClienteCodigoPromocion = new ClienteCodigoPromocion();
                $ClienteCodigoPromocion->cliente_id     = $cliente_id;
                $ClienteCodigoPromocion->requiered_libras   = Yii::$app->request->post()["requiered_libras"];
                $ClienteCodigoPromocion->descuento          = Yii::$app->request->post()["descuento"];
                $ClienteCodigoPromocion->fecha_rango_ini    = $fecha_ini;
                $ClienteCodigoPromocion->fecha_rango_fin    = $fecha_fin;
                $ClienteCodigoPromocion->tipo_condonacion   = Yii::$app->request->post()["tipo_condonacion"];

                $ClienteCodigoPromocion->tipo           = ClienteCodigoPromocion::TIPO_ESPECIAL;
                $ClienteCodigoPromocion->status         = ClienteCodigoPromocion::STATUS_PROGESO;

                if ($ClienteCodigoPromocion->validate()) {
                    if($ClienteCodigoPromocion->save())
                        $response = [ "code" => 10, "message" =>  "Se genero correctamente la solicitud"];
                }else
                    $response = [ "code" => 20, "message" => "Error al guardar promocion, verifique su información"];
            }

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGraVendedorClienteAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {
                $requestGet = Yii::$app->request->get();
                // Obtenemos data
                $ClienteVendedor = ViewCliente::getClienteVendedorAjax($requestGet);

                // Devolvemos datos CHOSEN.JS
            $response = [ 'results' => $ClienteVendedor];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionReporteVendedorAsignacionAjax()
    {
        $request = Yii::$app->request;
        //Yii::$app->response->format = Response::FORMAT_JSON;

        $objPHPExcel = new \PHPExcel();

        $requestGet = Yii::$app->request->get();

        $filters = [];
        $params  = "";
        foreach ($requestGet as $key => $value) {
            $params .= $key ."=". $value . "&";
        }

        $filters = [ "filters" => $params ];

        $ClienteVendedor = ViewCliente::getClienteAsignacionAjax($filters);



        $sheet=0;
        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);




        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('009688');
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:D1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Asignacion')

        ->setCellValue('A1', 'Asigando a (OLD)')
        ->setCellValue('B1', 'Asigando a (NEW)')
        ->setCellValue('C1', 'Fecha de cambio')
        ->setCellValue('D1', 'Cambio realizado por');

         $row=2;

         foreach ($ClienteVendedor as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$item["asigando_old"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$item["asigando_new"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$item["fecha_cambio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$item['cambio_realizado_por']);
            $row++;
        }

        header('Content-Type: application/vnd.ms-excel');

         $filename = "Vendedor-Cliente_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
           $objWriter->save('php://output');
    }

    public function actionReporteVendedorClienteAjax()
    {
        $request = Yii::$app->request;
        //Yii::$app->response->format = Response::FORMAT_JSON;

        $objPHPExcel = new \PHPExcel();

        $requestGet = Yii::$app->request->get();

        $filters = [];
        $params  = "";
        foreach ($requestGet as $key => $value) {
            $params .= $key ."=". $value . "&";
        }

        $filters = [ "filters" => $params ];
        $ClienteVendedor = ViewCliente::getClienteVendedorAjax($filters);



        $sheet=0;
        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(60);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);



        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->getStartColor()->setARGB('009688');
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:L1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setTitle('Reporte de agentes de ventas')

        ->setCellValue('A1', 'Cliente ID')
        ->setCellValue('B1', 'Nombre del cliente')
        ->setCellValue('C1', 'Tipo de cliente ID')
        ->setCellValue('D1', 'Tipo de cliente')
        ->setCellValue('E1', 'Agente de venta')
        ->setCellValue('F1', 'Asignado ID')
        ->setCellValue('G1', 'Fecha')
        ->setCellValue('H1', 'Hora')
        ->setCellValue('I1', 'Telefono al que marco')
        ->setCellValue('J1', 'Tipo de respuesta ID')
        ->setCellValue('K1', 'Tipo de respuesta')
        ->setCellValue('L1', 'Comentario');

         $row=2;

         foreach ($ClienteVendedor as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$item["clie_id"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$item["cliente_nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$item["clie_tipo_id"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$item['clie_tipo']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$item['nombre_completo']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$item['asignado_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$item['fecha']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$item['hora']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,isset($item['telefono']) ? $item['telefono'] : '');

            $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,isset($item['status_call_id']) ? $item['status_call_id'] : '');
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,isset($item['tipo_respuesta']) ? $item['tipo_respuesta'] : '');

            $objPHPExcel->getActiveSheet()->setCellValue('L'.$row,isset($item['comentario']) ? $item['comentario'] : '');
            $row++;
        }

        header('Content-Type: application/vnd.ms-excel');

         $filename = "Vendedor-Cliente_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
           $objWriter->save('php://output');

    }


    /**
     * ========================================================
     *                  AJAX VERIFICA ZONA ROJA
     * ========================================================
     */

     public function actionVerificaZona()
{
    // Obtén los datos enviados a través del POST
    $post = Yii::$app->request->post();

    // Verifica que los datos existan
    if (isset($post['pais']) && isset($post['cp'])) {
        $pais_id = $post['pais'];
        $cp = $post['cp'];

        // Encuentra la zona roja basada en el país y el código postal
        $zonaRoja = ZonasRojas::find()->where(['pais_id' => $pais_id, 'code' => $cp])->one();

        // Devuelve una respuesta JSON
        return json_encode([
            'code' => 202,
            'isZonaRoja' => $zonaRoja ? true : false,
        ]);
    } else {
        // Si los datos no están presentes, devuelve un error
        Yii::$app->response->statusCode = 400; // Bad Request
        return json_encode([
            'code' => 400,
            'message' => 'Datos inválidos'
        ]);
    }
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
                $model = Cliente::findOne($id);
                break;

            case 'view':
                $model = ViewCliente::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }

}
