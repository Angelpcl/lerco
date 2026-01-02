<?php

namespace app\modules\operacion\controllers;

use Yii;
use yii\web\Controller;
use app\models\envio\EnvioDetalle;
use app\models\movimiento\MovimientoPaquete;
use app\models\caja\CajaMex;
use app\models\envio\Envio;
/**
 * Default controller for the `admin` module
 */
class EscaneoBasicController extends  \app\controllers\AppController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionEscaneoPaquete($tracked)
    {
    	if (isset($tracked))
    	{
    		$tracked_get = trim($tracked);
    		$tracked_get = explode('/', $tracked_get);

            $clave = explode("-",$tracked_get[0]);

            if ($clave[0]. "-" == CajaMex::CLAVE_CAJA_MEX) {
                $model   = CajaMex::find()->where(['folio' => $tracked_get[0] ])->one();
                if ($model) {
                    $model->tracked_movimiento = trim($tracked);
                    return $this->render('index',[
                            "caja" => $model,
                            "tracked" => (new EnvioDetalle),
                            "clave_servicio" => $clave[0] . "-",
                    ]);

                }else
                    Yii::$app->session->setFlash('danger', 'Error al buscar la caja, no se encontro ninguna coincidencia en el sistema.');

            }elseif (isset($tracked_get[1]) &&  $tracked_get[1] ) {

                $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0] ])->one();
                if ($model) {
                    if($model->cantidad >= intval($tracked_get[1]) && intval($tracked_get[1]) != 0){
                        $model->tracked_movimiento = trim($tracked);
                        return $this->render('index',[
                            "tracked"           => $model,
                            "clave_servicio"    => $model->envio->tipo_envio,
                        ]);
                    }else
                        Yii::$app->session->setFlash('danger', 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.');
                }else
                    Yii::$app->session->setFlash('danger', 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.');
            }else
                Yii::$app->session->setFlash('warning', 'Debes ingresar correctamente el tracked.');
    	}
    	return $this->render('index');
    }

    public function actionMovimientoPaquete()
    {
    	if (Yii::$app->request->post()) {
            if (isset(Yii::$app->request->post()['paquete_id'])) {
                $MovimientoPaquete = new MovimientoPaquete();
                $MovimientoPaquete->paquete_id      = Yii::$app->request->post()['paquete_id'];
                $MovimientoPaquete->tracked         = Yii::$app->request->post()['tracked_movimiento'];
                $MovimientoPaquete->tipo_envio      = Yii::$app->request->post()['tipo_envio'];
                $MovimientoPaquete->tipo_movimiento = Yii::$app->request->post()['tipo_movimiento'];
                $MovimientoPaquete->fecha_entrega   = isset(Yii::$app->request->post()['fecha_entrega']) && Yii::$app->request->post()['fecha_entrega'] ? strtotime(Yii::$app->request->post()['fecha_entrega']) : null;
                $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;

                $MovimientoPaquete->paqueteria = isset(Yii::$app->request->post()['paqueteria_name']) ? Yii::$app->request->post()['paqueteria_name'] : null;
                $MovimientoPaquete->paqueteria_no_guia = isset(Yii::$app->request->post()['paqueteria_guia']) ? Yii::$app->request->post()['paqueteria_guia'] : null;

                if (isset($MovimientoPaquete->tipo_movimiento) && $MovimientoPaquete->tipo_movimiento) {
                    if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
                        //if ($MovimientoPaquete->validaMovimientoAdmin($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento) || Yii::$app->user->can('theCreator') ) {
                            if ($MovimientoPaquete->validaMovimientoTranscusoApertura($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_envio)) {
                                Yii::$app->session->setFlash('warning', 'Aviso, no se realizo el movimiento porque se ingreso en un Viaje y no a concluido ó aperturado para realizar movimientos ');
                                return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                            }
                            switch ($MovimientoPaquete->tipo_envio) {
                                case  Envio::TIPO_ENVIO_MEX:
                                        /*==============================================================
                                          *  Validamos si el paquete ya se encuentra en una caja
                                        ================================================================*/
                                        if ($MovimientoPaquete->validaMovimientoTrackedMex($MovimientoPaquete->tracked,MovimientoPaquete::MEX_CAJA)) {
                                            if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_CAJA){
                                                $MovimientoPaquete->caja_id         = isset(Yii::$app->request->post()['caja_id']) ? Yii::$app->request->post()['caja_id'] : null;
                                                if(!$MovimientoPaquete->caja_id ) {
                                                    Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, debe seleccionar una caja ');
                                                    return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                                                }
                                            }
                                            if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_TRANSCURSO){
                                                $MovimientoPaquete->viaje_id         = isset(Yii::$app->request->post()['viaje_mex_id']) ? Yii::$app->request->post()['viaje_mex_id'] : null;
                                                if(!$MovimientoPaquete->viaje_id ) {
                                                    Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, debe seleccionar una viaje MEX ');
                                                    return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                                                }
                                            }
                                        }else{
                                            Yii::$app->session->setFlash('warning', 'Aviso, No se realizo el movimiento ya que el paquete se ingreso en una Caja y no se encuentra en Apertura');
                                            return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                                        }
                                break;
                                case  Envio::TIPO_ENVIO_TIERRA:
                                    if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO){
                                        $MovimientoPaquete->viaje_id         = isset(Yii::$app->request->post()['viaje_tierra_id']) ? Yii::$app->request->post()['viaje_tierra_id'] : null;
                                        if(!$MovimientoPaquete->viaje_id ) {
                                            Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, debe seleccionar una viaje TIERRA');
                                            return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                                        }
                                    }
                                break;

                                case  Envio::TIPO_ENVIO_LAX:
                                    if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO){
                                        $MovimientoPaquete->viaje_id         = isset(Yii::$app->request->post()['viaje_lax_id']) ? Yii::$app->request->post()['viaje_lax_id'] : null;
                                        if(!$MovimientoPaquete->viaje_id ) {
                                            Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, debe seleccionar una viaje LAX');
                                            return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                                        }
                                    }
                                break;
                            }
                            if ($MovimientoPaquete->saveMovimiento()) {
                                if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_ENTREGADO || $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_ENTREGADO){
                                    if($MovimientoPaquete->validaEntregaPaquete($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio )){
                                        $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                                        $Envio = Envio::findOne($EnvioDetalle->envio->id);
                                        $Envio->status = Envio::STATUS_ENTREGADO;
                                        $Envio->update();
                                    }
                                }

                                $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                                if (isset($EnvioDetalle->envio->id)) {
                                    if ($EnvioDetalle->envio->status == Envio::STATUS_ENTREGADO) {
                                        $Envio = Envio::findOne($EnvioDetalle->envio->id);
                                        $Envio->status      = Envio::STATUS_HABILITADO;
                                        $Envio->update();
                                    }
                                }

                                Yii::$app->session->setFlash('success', 'Se realizo correctamente el movimiento del paquete');
                                return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                            }
                        /*}else{
                            Yii::$app->session->setFlash('warning', 'No se realizo el movimiento por que no cumple con las politicas de la empresa, contacta al administrador');
                            return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                        }*/
                    }else{
                        Yii::$app->session->setFlash('warning', 'Aviso, No se realizo el movimiento ya que el paquete se encuentra en ese movimiento');
                        return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                    }

                 }else{
                    Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, seleccione correctamente el movimiento ');
                    return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);

                 }
            }

    	}
        Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, intente nuevamente ');
        return $this->redirect(['index']);
    }
    public function actionMovimientoCaja()
    {
        if (Yii::$app->request->post()) {
            if (isset(Yii::$app->request->post()['paquete_id'])) {
                $MovimientoPaquete = new MovimientoPaquete();
                $MovimientoPaquete->paquete_id      = Yii::$app->request->post()['paquete_id'];
                $MovimientoPaquete->tracked         = Yii::$app->request->post()['tracked_movimiento'];
                $MovimientoPaquete->tipo_envio      = Yii::$app->request->post()['tipo_envio'];
                $MovimientoPaquete->tipo_movimiento = Yii::$app->request->post()['tipo_movimiento'];
                $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_CAJA;

                if (isset($MovimientoPaquete->tipo_movimiento) && $MovimientoPaquete->tipo_movimiento) {
                    if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {

                        if ($MovimientoPaquete->validaMovimientoTranscusoApertura($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_envio)) {
                            Yii::$app->session->setFlash('warning', 'Aviso, no se realizo el movimiento porque se ingreso en un Viaje y no a concluido ó aperturado para realizar movimientos ');
                            return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                        }

                        if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_TRANSCURSO){
                            $MovimientoPaquete->viaje_id         = isset(Yii::$app->request->post()['viaje_mex_caja_id']) ? Yii::$app->request->post()['viaje_mex_caja_id'] : null;
                            if(!$MovimientoPaquete->viaje_id ) {
                                Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, debe seleccionar una viaje MEX ');
                                return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                            }
                        }
                        if ($MovimientoPaquete->saveMovimiento()) {
                            Yii::$app->session->setFlash('success', 'Se realizo correctamente el movimiento del paquete');
                            return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                        }



                    }else{
                        Yii::$app->session->setFlash('warning', 'Aviso, No se realizo el movimiento la caja se encuentra en ese movimiento');
                        return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);
                    }

                 }else{
                    Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, seleccione correctamente el movimiento ');
                    return $this->redirect(['escaneo-paquete','tracked' => $MovimientoPaquete->tracked]);

                 }
            }
        }

        Yii::$app->session->setFlash('danger', 'Upss, Hubo un error al realizar el movimiento, intente nuevamente ');
        return $this->redirect(['index']);
    }
}
