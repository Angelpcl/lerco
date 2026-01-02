<?php
namespace app\modules\logistica\controllers;

use Yii;
use yii\web\Controller;
use app\models\ruta\Ruta;
use app\models\ruta\ViewRuta;
use app\models\ruta\RutaSucursal;


/**
 * Default controller for the `clientes` module
 */
class RutaController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('rutaCreate'),
            'update' => Yii::$app->user->can('rutaUpdate'),
            'delete' => Yii::$app->user->can('rutaDelete'),
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

        $model->ruta_sucursal  = new RutaSucursal();
        if ($model->ruta_sucursal->load(Yii::$app->request->post())) {
            $model->ruta_sucursal->ruta_id = $model->id;

            if ($model->ruta_sucursal->save_sucursal_ruta()) {
                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }

        return $this->render('view', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }

    /**
     * Creates a new Sucursal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ruta();

        if ($model->load(Yii::$app->request->post())) {
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

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la ruta #" . $id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación de la ruta.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->redirect(['index']);
    }

    public function actionDeleteSucursal($ruta_id,$sucursal_id)
    {
        $model = RutaSucursal::find()->andWhere(['and',["ruta_id" => $ruta_id], ["sucursal_id" => $sucursal_id]])->one();

        try{
            // Eliminamos el usuario
            $model->delete();

            Yii::$app->session->setFlash('success', "Se ha removido correctamente la sucursal #" . $sucursal_id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden remover la sucursal.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->redirect(['view','id' => $ruta_id]);
    }

    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionRutasJsonBtt(){
        return ViewRuta::getJsonBtt(Yii::$app->request->get());
    }

    public function actionSucursalRutaAsignarAjax(){
        return ViewRuta::getAsignarSucursalRuta(Yii::$app->request->get());
    }

    public function actionValidaOrdenAjax()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            $data       = Yii::$app->request->get();
            $response   = [];
            if (isset($data["orden"]) && isset($data["tipo"]))
                $response = ViewRuta::getOrdenRuta($data);


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
    protected function findModel($name, $_model = 'model')
    {
        switch ($_model) {
            case 'model':
                $model = Ruta::findOne($name);
                break;

            case 'view':
                $model = ViewRuta::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
