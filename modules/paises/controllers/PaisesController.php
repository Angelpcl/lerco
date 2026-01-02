<?php

namespace app\modules\paises\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\pago\PagoGasto;
use app\models\pago\ViewPagoGasto;
use app\models\Esys;
use app\models\pais\PaisesLatam;
use yii\web\UploadedFile;

class PaisesController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('paisCreate'),
            'delete' => Yii::$app->user->can('paisDelete'),
            'update' => Yii::$app->user->can('paisUpdate'),
            'view' => Yii::$app->user->can('paisView'),
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
        $model = new PaisesLatam();

        if ($model->load(Yii::$app->request->post())) {
            $model->imagen_bandera = UploadedFile::getInstance($model, 'imagen_bandera');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', "País creado correctamente");
                return $this->redirect(['view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('danger', "Ocuarrio un error al guardar este registro");
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id); // Encuentra el modelo por su ID

        if ($model->load(Yii::$app->request->post())) {
            $uploadedFile = UploadedFile::getInstance($model, 'imagen_bandera');

            // Si se ha subido un nuevo archivo, reemplazar el antiguo
            if ($uploadedFile !== null) {
                $model->imagen_bandera = $uploadedFile;
            }

            // Guarda el modelo
            if ($model->save()) {
                // Si se ha subido un archivo, guardarlo en el sistema de archivos
                if ($uploadedFile !== null) {
                    $filePath = 'path/to/save/' . $uploadedFile->baseName . '.' . $uploadedFile->extension;
                    $uploadedFile->saveAs($filePath);
                }

                Yii::$app->session->setFlash('success', "País actualizado correctamente");
                return $this->redirect(['view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('danger', "Ocurrió un error al guardar este registro");
        }

        return $this->render('update', [
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
            Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del egreso');
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
        return PaisesLatam::getJsonBtt(Yii::$app->request->get());
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
                $model = PaisesLatam::findOne($id);
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
