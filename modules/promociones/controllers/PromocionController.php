<?php
namespace app\modules\promociones\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\promocion\Promocion;
use app\models\promocion\ViewPromocion;
use app\models\promocion\PromocionDetalle;
use app\models\producto\ViewProducto;
/**
 * Default controller for the `admin` module
 */
class PromocionController extends \app\controllers\AppController
{
	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('promocionCreate'),
            'update' => Yii::$app->user->can('promocionUpdate'),
            'cancel' => Yii::$app->user->can('promocionCancel')
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

        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }


    public function actionCreate()
    {
        $model = new Promocion();
        $model->promocion_detalle = new PromocionDetalle();
        if ($model->load(Yii::$app->request->post()) && $model->promocion_detalle->load(Yii::$app->request->post())) {

            $model->status = Promocion::STATUS_ACTIVE;
            $model->promocion_img       = UploadedFile::getInstance($model, 'promocion_img');
            $model->is_code_promocional = isset(Yii::$app->request->post()["is_code_promocional"]) ? 10 : 1;
            $model->is_generica         = isset(Yii::$app->request->post()["is_generica"]) ? 10 : 1;

            if($model->save()){
                if ($model->is_manual == Promocion::IS_MANUAL_OFF){
                    if ($model->promocion_detalle->savePromocionDetalle($model->id)) {
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }else
                    return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        $model->status = Promocion::STATUS_CANCEL;

        $model->update();

        Yii::$app->session->setFlash('success', "Se ha cancelado correctamente la promoción #" . $id);

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
    public function actionPromocionesJsonBtt(){
        return ViewPromocion::getJsonBtt(Yii::$app->request->get());
    }

    public function actionProductosCategoriaAjax(){
        return ViewProducto::getProductoAllJsonBtt(Yii::$app->request->get());
    }

    public function actionPromocionInfoAjax(){
        return ViewPromocion::getPromocionDetalleAjax(Yii::$app->request->get());
    }

    public function actionPromocionDetalleComplementoAjax(){
        return ViewPromocion::getPromocionDetalleComplementoAjax(Yii::$app->request->get());
    }

    public function actionUpdatePromocionAjax(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            $fecha_expira = $request->post('fecha_expira');
            $promocion_id = $request->post('promocion_id');
            if ($fecha_expira && $promocion_id) {

                $model = $this->findModel($promocion_id);
                $model->status          = Promocion::STATUS_ACTIVE;
                $model->fecha_expira    =   strtotime(trim($fecha_expira));
                if ($model->update()) {
                    foreach (ViewPromocion::getPromocionMexAjax($model->tipo,$model->tipo_servicio) as $key => $promocion_vigentes) {
                        if ($promocion_vigentes["id"] != $model->id) {
                            $Promocion = Promocion::findOne($promocion_vigentes["id"]);
                            $Promocion->status = Promocion::STATUS_INACTIVE;
                            $Promocion->update();
                        }
                    }
                    return [
                        'code' => 202,
                        'message' => 'Se modifico correctamente'
                    ];
                }else{
                    return [
                        'code' => 10,
                        'message' => 'Error al modificar la promoción, intente nuevamente'
                    ];
                }

            }
            return [
                'code' => 10,
                'message' => 'La fecha es requerida, intente nuevamente'
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
                $model = Promocion::findOne($id);
                break;

            case 'view':
                $model = ViewPromocion::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }
}
