<?php
namespace app\modules\caja\controllers;

use Yii;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\aperturaCierreCaja\AperturaCierreCaja;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\aperturaCierreCaja\ViewAperturaCierreCaja;
use kartik\mpdf\Pdf;

/**
 * Default controller for the `clientes` module
 */
class AperturaCierreController extends \app\controllers\AppController
{

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

     /**
     * Displays a single EsysDivisa model.
     * @param integer $name
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model
        ]);
    }

    public function actionImprimirTicket($id)
    {
        $model = $this->findModel($id);

        $lengh = 370;
        $width = 72;

        $content = $this->renderPartial('_ticket_cobro', ["model" => $model]);

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
                'SetHeader'=>[ 'Fecha ' . date('Y-m-d',$model->created_at) ],
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

    public function actionCreateAperturaAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {


            if ( isset(Yii::$app->request->post()["monto"]) && Yii::$app->request->post()["nota"]) {


                $AperturaCierreCaja = new AperturaCierreCaja();
                $AperturaCierreCaja->cantidad_apertura    = Yii::$app->request->post()["monto"];
                $AperturaCierreCaja->fecha_apertura       = time();
                $AperturaCierreCaja->comentario_apertura  = Yii::$app->request->post()["nota"];

                if($AperturaCierreCaja->save())
                    $response = [ "code" => 10, "message" =>  "Se genero correctamente la solicitud"];
                else
                    $response = [ "code" => 20, "message" => "Error al guardar apertura de la caja, verifique su información"];
            }

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionCreateCierreAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {


            if ( isset(Yii::$app->request->post()["monto"]) && Yii::$app->request->post()["nota"] && Yii::$app->request->post()["caja_id"]) {

                $AperturaCierreCaja = AperturaCierreCaja::findOne(Yii::$app->request->post()["caja_id"]);
                $AperturaCierreCaja->cantidad_cierre    = Yii::$app->request->post()["monto"];
                $AperturaCierreCaja->fecha_cierre       = time();
                $AperturaCierreCaja->comentario_cierre  = Yii::$app->request->post()["nota"];

                $AperturaCierreCaja->bill_100   =  isset(Yii::$app->request->post()["input_val_100"]) ? Yii::$app->request->post()["input_val_100"] : 0;
                $AperturaCierreCaja->bill_50    =  isset(Yii::$app->request->post()["input_val_50"]) ? Yii::$app->request->post()["input_val_50"] : 0;
                $AperturaCierreCaja->bill_20    =  isset(Yii::$app->request->post()["input_val_20"]) ? Yii::$app->request->post()["input_val_20"] : 0;
                $AperturaCierreCaja->bill_10    =  isset(Yii::$app->request->post()["input_val_10"]) ? Yii::$app->request->post()["input_val_10"] : 0;
                $AperturaCierreCaja->bill_5     =  isset(Yii::$app->request->post()["input_val_5"]) ? Yii::$app->request->post()["input_val_5"] : 0;
                $AperturaCierreCaja->bill_2     =  isset(Yii::$app->request->post()["input_val_2"]) ? Yii::$app->request->post()["input_val_2"] : 0;
                $AperturaCierreCaja->bill_1     =  isset(Yii::$app->request->post()["input_val_1"]) ? Yii::$app->request->post()["input_val_1"] : 0;
                $AperturaCierreCaja->change     =  isset(Yii::$app->request->post()["input_val_change"]) ? Yii::$app->request->post()["input_val_change"] : 0;
                $AperturaCierreCaja->pendiente  =  isset(Yii::$app->request->post()["monto_pendiente_monto"]) ? Yii::$app->request->post()["monto_pendiente_monto"] : 0;


                if($AperturaCierreCaja->save())
                    $response = [ "code" => 10, "message" =>  "Se genero correctamente la solicitud"];
                else
                    $response = [ "code" => 20, "message" => "Error al guardar apertura de la caja, verifique su información"];
            }

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionInfoCajaAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {

            $AperturaCierreCaja = AperturaCierreCaja::find()
                ->andWhere(['IS','fecha_cierre', new \yii\db\Expression('null')  ])->orderBy("id desc")->one();

            $CobroRembolsoEnvio = 0;
            if ($AperturaCierreCaja->id)
                $CobroRembolsoEnvio = CobroRembolsoEnvio::find()
                                    ->andWhere(["tipo" => CobroRembolsoEnvio::TIPO_COBRO ])
                                    ->where(["between","created_at",$AperturaCierreCaja->fecha_apertura,time()])->sum("cantidad");
            $response = [
                "code"      => 10,
                "message"   =>  "Se genero correctamente la solicitud",
                "data"      => array(
                    'caja_id'           =>  isset($AperturaCierreCaja->id) ? $AperturaCierreCaja->id : null,
                    'efectivoInicial'   =>  isset($AperturaCierreCaja->cantidad_apertura) ? $AperturaCierreCaja->cantidad_apertura : 0,
                    'efectivoCobro'     =>  $CobroRembolsoEnvio ? $CobroRembolsoEnvio : 0,
                    'efectivoTotal'     =>  isset($AperturaCierreCaja->cantidad_apertura) ? floatval($AperturaCierreCaja->cantidad_apertura) + floatval($CobroRembolsoEnvio) : floatval($CobroRembolsoEnvio),
                ),
            ];

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
    public function actionAperturasCierresJsonBtt(){
        return ViewAperturaCierreCaja::getJsonBtt(Yii::$app->request->get());
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
    protected function findModel($name, $_model = 'model')
    {
        switch ($_model) {
            case 'model':
                $model = AperturaCierreCaja::findOne($name);
                break;

            case 'view':
                $model = ViewAperturaCierreCaja::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
