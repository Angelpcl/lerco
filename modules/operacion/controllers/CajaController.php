<?php

namespace app\modules\operacion\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\caja\CajaMex;
use app\models\caja\CajaDetalleMex;
use app\models\caja\ViewCaja;
use app\models\movimiento\MovimientoPaquete;
/**
 * Default controller for the `operacion` module
 */
class CajaController extends \app\controllers\AppController
{


 	private $can;

    public function init()
    {
        parent::init();
        $this->can = [
            'create' => Yii::$app->user->can('cajaCreate'),
            'update' => Yii::$app->user->can('cajaUpdate'),
            'delete' => Yii::$app->user->can('cajaDelete'),
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index',[
        	'can' => $this->can
        ]);
    }

    public function actionCreate()
    {
        $model = new CajaMex();

        if ($model->load(Yii::$app->request->post())) {
            $Folio          = CajaMex::find()->orderBy("id desc")->one();
            $Folio          = isset($Folio->id) ? $Folio->id + 1 : 1;
            $model->folio   = CajaMex::CLAVE_CAJA_MEX . str_pad($Folio, 5 , "0",STR_PAD_LEFT);
            if ($model->save()) {

                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post())){
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }



    public function actionCerrarCaja($id)
    {
        $model = $this->findModel($id);
        $model->status = CajaMex::STATUS_INACTIVE;

        $model->update();

        Yii::$app->session->setFlash('success', "Se ha cerrado la caja #" . $model->folio);

        return $this->redirect(['view',
            'id' => $model->id
        ]);


    }

    public function actionProductoRemove($caja_id,$paquete_id)
    {
        // Eliminamos el usuario
        $CajaDetalleMex = CajaDetalleMex::find()->where(['and', ["caja_mex_id" => $caja_id,"envio_detalle_id" => $paquete_id ]])->one();
        try{
            // Eliminamos el usuario
            if($CajaDetalleMex->delete()){
                $MovimientoPaquete = new MovimientoPaquete();
                $MovimientoPaquete->paquete_id      = $CajaDetalleMex->envio_detalle_id;
                $MovimientoPaquete->tracked         = $CajaDetalleMex->tracked;
                $MovimientoPaquete->tipo_envio      = $CajaDetalleMex->envioDetalle->envio->tipo_envio;
                $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::MEX_BODEGA;
                $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                $MovimientoPaquete->save();

                Yii::$app->session->setFlash('success', "Se ha removido correctamente el paquete #" . $paquete_id);
            }

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden remover el paquete.');
                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->render('view', [
            'model' => $this->findModel($caja_id),
            'can'   => $this->can,
        ]);
    }

    public function actionDelete($id)
    {
        try{
            // Eliminamos el usuario
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la caja #" . $id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación de la caja (Movimientos o Paquetes ).');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index']);
    }

    public function actionImprimirEtiqueta($id)
    {
        $model = $this->findModel($id);

        $content = $this->renderPartial('etiqueta', ["model" => $model]);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array(85,65),//Pdf::FORMAT_LETTER,
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

        $pdf->marginLeft = 1;
        $pdf->marginRight = 1;

        // return the pdf output as per the destination setting
        return $pdf->render();

    }

    public function actionCajasJsonBtt(){
        return ViewCaja::getJsonBtt(Yii::$app->request->get());
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
                $model = CajaMex::findOne($id);
                break;

            case 'view':
                $model = ViewCajaMex::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }
}
