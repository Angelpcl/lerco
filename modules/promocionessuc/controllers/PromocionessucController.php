<?php

namespace app\modules\promocionessuc\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\pago\PagoGasto;
use app\models\pago\ViewPagoGasto;
use app\models\Esys;
use app\models\pais\PaisesLatam;

use app\models\sucursal\Promociones;
use app\models\pais\ZonasRojas;
use yii\web\Response;
use app\models\pais\ViewZonasRojas;
use app\models\sucursal\ViewPromos;
use yii\web\UploadedFile;

class PromocionessucController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('promocionessucCreate'),
            'delete' => Yii::$app->user->can('promocionessucDelete'),
            'update' => Yii::$app->user->can('promocionessucUpdate'),
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
        $model = new Promociones();

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
                foreach ($errors as $err) {
                    $errorMessage .= ' (' . implode(', ', $err) . ')';
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
        $model =$this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            //$model->imagen_bandera = UploadedFile::getInstance($model, 'imagen_bandera');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Registro actualizado correctamente");
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Obtener todos los errores del modelo
                $errors = $model->getErrors();

                // Construir un mensaje de error para mostrar
                $errorMessage = 'Ocurrió un error al editar este registro.';
                foreach ($errors as $err) {
                    $errorMessage .= ' (' . implode(', ', $err) . ')';
                }
                Yii::$app->session->setFlash('danger', $errorMessage);
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

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            // Eliminamos el usuario$modl

            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente el país " . $model->nombre);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del país');
            return $this->redirect(['view', 'id' => $model->id]);
            #if ($e->getCode() === 23000) {
            ##    Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del egreso');
            ##
            ##    header("HTTP/1.0 400 Relation Restriction");
            ##} else {
            ##    throw $e;
            ##}

        }

        return $this->redirect(['index']);
    }



    public function actionPaisesJsonBtt()
    {
        return ViewPromos::getJsonBtt(Yii::$app->request->get());
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
                $model = Promociones::findOne($id);
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
