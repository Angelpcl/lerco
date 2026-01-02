<?php

namespace app\modules\zonas\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\pago\PagoGasto;
use app\models\pago\ViewPagoGasto;
use app\models\Esys;
use app\models\pais\PaisesLatam;

use app\models\pais\ZonasRojas;
use yii\web\Response;
use app\models\pais\ViewZonasRojas;
use yii\web\UploadedFile;

class ZonasController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'view' => Yii::$app->user->can('zonarojaView'),
            'update' => Yii::$app->user->can('zonarojaUpdate'),
            'create' => Yii::$app->user->can('zonarojaCreate'),
            'delete' => Yii::$app->user->can('zonarojaDelete'),
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'can' => $this->can
        ]);
    }

    public function actionCreate()
    {
        $model = new ZonasRojas();

        if ($model->load(Yii::$app->request->post())) {
            //$model->imagen_bandera = UploadedFile::getInstance($model, 'imagen_bandera');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Zona roja creada correctamente");
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Obtener todos los errores del modelo
                $errors = $model->getErrors();

                // Construir un mensaje de error para mostrar
                $errorMessage = 'Ocurrió un error al guardar este registro.';
                if (isset($errors['code'])) {
                    $errorMessage .= ' (' . implode(', ', $errors['code']) . ')';
                }

                Yii::$app->session->setFlash('danger', $errorMessage);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id); // Encuentra el modelo por su ID

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Zona roja actualizada correctamente");
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Obtener todos los errores del modelo
                $errors = $model->getErrors();

                // Construir un mensaje de error para mostrar
                $errorMessage = 'Ocurrió un error al guardar este registro.';
                if (isset($errors['code'])) {
                    $errorMessage .= ' (' . implode(', ', $errors['code']) . ')';
                }

                Yii::$app->session->setFlash('danger', $errorMessage);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }



    public function actionGetPais()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {


            if (isset($request->get()["pais_id"]) && $request->get()["pais_id"]) {

                $caja = PaisesLatam::findOne($request->get()["pais_id"]);


                $response = ["code" => 10, "pais" =>  $caja];
            } else
                $response = ["code" => 30, "message" => "Error al cargar los datos, verifique su información"];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            // Eliminamos el modelo
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la zona roja");
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('danger', 'No se pudo eliminar el país.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } catch (\yii\db\Exception $e) {
            // Captura excepciones específicas relacionadas con la base de datos
            if ($e->getCode() === '23000') {
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del país.');
            } else {
                Yii::$app->session->setFlash('danger', 'Ocurrió un error al intentar eliminar el país.');
            }

            return $this->redirect(['view', 'id' => $model->id]);
        } catch (\Exception $e) {
            // Captura cualquier otra excepción
            Yii::$app->session->setFlash('danger', 'Ocurrió un error inesperado.'.$e->getMessage());

            return $this->redirect(['view', 'id' => $model->id]);
        }
    }



    public function actionPaisesJsonBtt()
    {
        return ViewZonasRojas::getJsonBtt(Yii::$app->request->get());
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
                $model = ZonasRojas::findOne($id);
                break;

            case 'view':
                $model = ViewPagoGasto::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }
}
