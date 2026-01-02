<?php

namespace app\modules\operacion\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\envio\ViewEnvio;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;

/**
 * Default controller for the `operacion` module
 */
class EmpaquetadoController extends  \app\controllers\AppController
{

    private $can;

    public function init()
    {
        parent::init();
        $this->can = [
            'empaquetado' => Yii::$app->user->can('empaquetado'),
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

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);
    }

    public function actionEmpaquetadoUpdate($id)
    {
        $model = $this->findModel($id);
        $model->dir_obj   = $model->direccion;

        if ($response = Yii::$app->request->post()) {
            if($model->status == Envio::STATUS_RECOLECTADO){
                if (isset($response['pesoItemList'])) {
                    foreach ($response['pesoItemList'] as $key => $item) {
                        $EnvioDetalle = EnvioDetalle::findOne($key);
                        $EnvioDetalle->peso             = isset($item['peso']) ? $item['peso'] : 0;
                        $EnvioDetalle->cantidad         = isset($item['cantidad']) ? $item['cantidad'] : 0;
                        $EnvioDetalle->cantidad_piezas  = isset($item['cantidad_piezas']) ? $item['cantidad_piezas'] : 0;
                        $EnvioDetalle->status           = $EnvioDetalle->status  != EnvioDetalle::STATUS_CANCELADO ?   EnvioDetalle::STATUS_HABILITADO : $EnvioDetalle->status;
                        $EnvioDetalle->update();
                    }
                }


                if (isset($response['peso_mex_con_empaque']) && $response['peso_mex_con_empaque']) {
                    $model->peso_mex_con_empaque    = $response['peso_mex_con_empaque'];
                    $model->peso_total  = floatval($model->peso_mex_con_empaque)  >= floatval($model->peso_mex_sin_empaque) ? $model->peso_mex_con_empaque : $model->peso_mex_sin_empaque;

                    $model->status      = Envio::STATUS_EMPAQUETADO;
                }

            }elseif ($model->status == Envio::STATUS_PREAUTORIZADO) {
                $model->peso_mex_sin_empaque    = $response['peso_mex_sin_empaque'];
                $model->status      = Envio::STATUS_RECOLECTADO;
            }

            if ($model->update()) {
                return $this->render('view', [
                    'model' => $this->findModel($id),
                    'can'   => $this->can,
                ]);
            }

        }

        return $this->render('empaquetado-update', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);
    }

    public function actionImprimirEtiqueta($id)
    {
        $model = EnvioDetalle::findOne($id);

        $content = $this->renderPartial('../envio-mex/etiqueta', ["model" => $model]);

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

    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//
    public function actionEnviosJsonBtt(){
        return ViewEnvio::getJsonBtt(Yii::$app->request->get());
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
