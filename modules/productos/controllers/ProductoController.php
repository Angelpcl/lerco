<?php

namespace app\modules\productos\controllers;

use Yii;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\producto\Producto;
use app\models\producto\ViewProducto;
use app\models\esys\EsysListaDesplegable;
use app\models\envio\Envio;
use app\models\producto\CajaSinLimite;

/**
 * Default controller for the `clientes` module
 */
class ProductoController extends \app\controllers\AppController
{

    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            "create" => Yii::$app->user->can('productoCreate'),
            "update" => Yii::$app->user->can('productoUpdate'),
            "delete" => Yii::$app->user->can('productoDelete'),
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */

    public function actionIndex()
    {
        return $this->render('index', [
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
        $model = new Producto();
        $modelCaja = new CajaSinLimite();

        if ($model->load(Yii::$app->request->post())) {
            //print_r(Yii::$app->request->post());
            //die;
            // Verifica que las constantes estén definidas correctamente
            if ($model->is_producto == Producto::TIPO_CAJA || $model->is_producto == Producto::TIPO_CAJA_SIN_LIMITE) {
                $model->tipo_servicio = Envio::TIPO_ENVIO_TIERRA;
                $model->unidad_medida_id = 2510;
            }

            if ($model->is_producto == Producto::TIPO_CAJA_SIN_LIMITE) {
                $post  = Yii::$app->request->post()['CajaSinLimite'];
                $modelCaja->largo =  $post['largo'];
                $modelCaja->ancho =  $post['ancho'];
                $modelCaja->alto =  $post['alto'];
                $modelCaja->costo_cli =  $post['costo_cli'];
                $modelCaja->costo_suc =  $post['costo_suc'];

                ///$modelCaja->producto_id = $model->id; // Asegúrate de que la propiedad exista en el modelo
                $modelCaja->save();
                $model->is_caja_sin_limite_id = $modelCaja->id;
                $model->sucursal_id = Yii::$app->request->post()['sucursal_id'];
            }
            //sucursal_id

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelCaja' => $modelCaja,
        ]);
    }


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->is_caja_sin_limite_id) {
            $modelCaja = CajaSinLimite::findOne($model->is_caja_sin_limite_id);
        } else {
            $modelCaja = new CajaSinLimite();
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->is_impuesto = isset(Yii::$app->request->post()["is_impuesto"]) ? Producto::IS_IMPUESTO_ON : null;

            if ($model->is_producto == Producto::TIPO_CAJA) {
                if($model->is_caja_sin_limite_id){
                    $modelCaja = CajaSinLimite::findOne($model->is_caja_sin_limite_id);
                    $modelCaja->delete();
                    $model->is_caja_sin_limite_id = null;
                }
            }

            if ($model->is_producto == Producto::TIPO_CAJA_SIN_LIMITE) {
                $post  = Yii::$app->request->post()['CajaSinLimite'];
                $modelCaja->largo =  $post['largo'];
                $modelCaja->ancho =  $post['ancho'];
                $modelCaja->alto =  $post['alto'];
                $modelCaja->costo_cli =  $post['costo_cli'];
                $modelCaja->costo_suc =  $post['costo_suc'];

                ///$modelCaja->producto_id = $model->id; // Asegúrate de que la propiedad exista en el modelo
                $modelCaja->save();
                $model->is_caja_sin_limite_id = $modelCaja->id;
                $model->sucursal_id = Yii::$app->request->post()['sucursal_id'];
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'model' => $model,
            'modelCaja' => $modelCaja,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            // Eliminamos el cliente
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente al cliente #" . $id);
        } catch (\Exception $e) {
            if ($e->getCode() === 23000) {
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del Producto.');

                header("HTTP/1.0 400 Relation Restriction");
            } else {
                throw $e;
            }
        }

        return $this->redirect(['index', 'tab' => 'index']);
    }

    public function actionProductoLaxTierraAjax($q = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;
            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            $producto = ViewProducto::getProductoSeachAjax($text, true, false);
            // Obtenemos user

            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $producto;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $producto];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionProductoTierraAjax($q = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;
            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            $producto = ViewProducto::getProductoSeachAjax($text, false, true);
            // Obtenemos user

            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $producto;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $producto];

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
    public function actionProductosJsonBtt()
    {
        return ViewProducto::getJsonBtt(Yii::$app->request->get());
    }


    public function actionCategoriaAjax($q = false)
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            if ($q) {
                $text = $q;
            } else {
                $text = Yii::$app->request->get('tipo_servicio');
            }

            if ($text == Envio::TIPO_ENVIO_MEX)
                $categoria = EsysListaDesplegable::getItems('categoria_paquete_mex', true);
            else
                $categoria = EsysListaDesplegable::getItems('categoria_paquete_lax_tierra', true);

            return $categoria;
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
                $model = Producto::findOne($id);
                break;

            case 'view':
                $model = ViewProducto::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }
}
