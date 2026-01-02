<?php
namespace app\modules\sucursales\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Controller;
use app\models\sucursal\Sucursal;
use app\models\sucursal\ViewSucursal;
use app\models\esys\EsysDireccion;
use app\models\sucursal\ListaPrecioMx;
use yii\web\UploadedFile;

/**
 * Default controller for the `clientes` module
 */
class SucursalController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('sucursalCreate'),
            'update' => Yii::$app->user->can('sucursalUpdate'),
            'delete' => Yii::$app->user->can('sucursalDelete'),
        ];
    }


    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index',[
        	"can" => $this->can]);
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
            'model' => $model,
            'can'   => $this->can,
            "precioMex" => new ListaPrecioMx(),
        ]);
    }

    /**
     * Creates a new Sucursal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Sucursal();

		$model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_SUCURSAL,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);


        if ($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post())) {
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

    public function actionUpdate($id)
    {

        $model = $this->findModel($id);

        // Cargamos datos de dirección
        $model->dir_obj   = $model->direccion;

        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;



        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post())){

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            return $this->render('update', [
                'model' => $model,
            ]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionImprimirQr($id)
    {
        $model = $this->findModel($id);
        $lengh = 70;
        $width = 70;

        $content = $this->renderPartial('qr', ["model" => $model]);

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
            'options' => ['title' => 'QR'],
             // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>[ ($model->pre_created_at ? 'Precaptura: ' . date('Y-m-d',$model->pre_created_at) . ' -  Fecha ' . ($model->created_at != 0  ? date('Y-m-d',$model->created_at) : 'N/A') .'<br>' : ' Fecha ' . date('Y-m-d',$model->created_at)  ) . ' /  Ticket de envio #' . $model->folio],
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



    /**
     * Deletes an existing Sucursal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param  integer $id The user id.
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);


        try{
            // Eliminamos el usuario
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la sucursal #" . $id);

        }catch(\Exception $e){
            if($e->getCode() == 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación de la sucursal.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index']);
    }


    public function actionSavePrecioMx()
    {
        $model = new ListaPrecioMx();

        if ($model->load(Yii::$app->request->post())) {

            $model->default = $model->default == 1 ? ListaPrecioMx::IS_DEFAULT : null;
            $model->tipo    = ListaPrecioMx::TIPO_LIBRA;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Se agrego correctamente');
                return $this->redirect(['view', "id" => $model->sucursal_recibe_id]);
            }

        }
        Yii::$app->session->setFlash('danger', 'Ocurrio un error, intenta nuevamente.');

        return $this->redirect(['index']);
    }

    public function actionSaveImpuestoMx()
    {
        $model = new ListaPrecioMx();

        if ($model->load(Yii::$app->request->post())) {

            $model->tipo    = ListaPrecioMx::TIPO_IMPUESTO;
            $model->default = ListaPrecioMx::IS_DEFAULT ;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Se agrego correctamente');
                return $this->redirect(['view', "id" => $model->sucursal_recibe_id]);
            }
        }

        Yii::$app->session->setFlash('danger', 'Ocurrio un error, intenta nuevamente.');
        return $this->redirect(['index']);
    }
    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionSucursalesJsonBtt(){
        return ViewSucursal::getJsonBtt(Yii::$app->request->get());
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
                $model = Sucursal::findOne($name);
                break;

            case 'view':
                $model = ViewSucursal::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
