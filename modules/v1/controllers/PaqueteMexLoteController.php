<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\models\caja\CajaMex;
use app\models\envio\EnvioDetalle;
use app\models\envio\Envio;
use app\models\viaje\Viaje;
use app\models\movimiento\MovimientoPaquete;

class PaqueteMexLoteController extends DefaultController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                // restrict access to
                'Origin' => ['*'],
                // Allow only POST and PUT methods
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Request-Headers' => ['*'],
                // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Allow-Origin' => ['*'],
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ];

        return $behaviors;
    }

    /*****************************************
     *  MOVIMIENTO CAJA (LAX - TIERRA) Y CAJA
    *****************************************/
    public function actionMovimientoLoteMex()
    {
        $post                       = Yii::$app->request->post();
        $tracked_movimiento_array   = isset($post['tracked_movimiento_array']) ? $post['tracked_movimiento_array'] : null;
        $user                       = $this->authToken($post["token"]);
        $errors_array               = [];

        if ($post['tipo_movimiento'] && count($tracked_movimiento_array) > 0) {
            foreach ($tracked_movimiento_array as $key => $tracked) {
                $is_validation              = true;

                $tracked_get    = trim($tracked);
                $tracked_get    = explode('/', $tracked_get);
                $clave          = explode("-",$tracked_get[0]);

                $EnvioDetalle = EnvioDetalle::getEnvioDetalleFolio($tracked_get[0]);

                if($EnvioDetalle) {

                    $MovimientoPaquete = new MovimientoPaquete();
                    $MovimientoPaquete->paquete_id      = $EnvioDetalle->id;
                    $MovimientoPaquete->tracked         = $tracked;
                    $MovimientoPaquete->tipo_envio      = $EnvioDetalle->envio->tipo_envio;
                    $MovimientoPaquete->tipo_movimiento = isset($post['tipo_movimiento']) ? $post['tipo_movimiento'] : null;
                    $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                    $MovimientoPaquete->created_by      = $user->id;



                    if (isset($MovimientoPaquete->tipo_movimiento) && $MovimientoPaquete->tipo_movimiento) {

                        if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
                            if ($MovimientoPaquete->validaMovimientoTranscusoApertura($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_envio)) {

                                $error = [
                                    "tracked" => $tracked,
                                    "message" => "Aviso, no se realizo el movimiento porque se ingreso en un Viaje y no a concluido ó aperturado para realizar movimientos",
                                ];
                                array_push($errors_array, $error);
                                $is_validation = false;
                            }
                            switch ($MovimientoPaquete->tipo_envio) {
                                case  Envio::TIPO_ENVIO_MEX:
                                        /*==============================================================
                                          *  Validamos si el paquete ya se encuentra en una caja
                                        ================================================================*/
                                        if ($MovimientoPaquete->validaMovimientoTrackedMex($MovimientoPaquete->tracked,MovimientoPaquete::MEX_CAJA)) {
                                            if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_CAJA){
                                                $MovimientoPaquete->caja_id         = isset($post['caja_id']) ? $post['caja_id'] : null;
                                                if(!$MovimientoPaquete->caja_id ){

                                                    $error = [
                                                        "tracked" => $tracked,
                                                        "message" => "Hubo un error al realizar el movimiento, debe seleccionar una caja",
                                                    ];
                                                    array_push($errors_array, $error);
                                                    $is_validation = false;
                                                }
                                            }
                                            if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_TRANSCURSO){
                                                $MovimientoPaquete->viaje_id         = isset($post['viaje_mex_id']) ? $post['viaje_mex_id'] : null;
                                                if(!$MovimientoPaquete->viaje_id ){
                                                    $error = [
                                                        "tracked" => $tracked,
                                                        "message" => 'Hubo un error al realizar el movimiento, debe seleccionar una viaje MEX',
                                                    ];
                                                    array_push($errors_array, $error);
                                                    $is_validation = false;
                                                }
                                            }
                                        }else{
                                            $error = [
                                                "tracked" => $tracked,
                                                "message" => 'Aviso, No se realizo el movimiento ya que el paquete se ingreso en una Caja y no se encuentra en Apertura',
                                            ];
                                            array_push($errors_array, $error);
                                            $is_validation = false;
                                        }

                                break;
                            }
                            if ($is_validation) {
                                if ($MovimientoPaquete->saveMovimiento() ) {
                                    if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_ENTREGADO || $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_ENTREGADO){
                                        if($MovimientoPaquete->validaEntregaPaquete($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio )){
                                            $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                                            $Envio = Envio::findOne($EnvioDetalle->envio->id);
                                            $Envio->status = Envio::STATUS_ENTREGADO;
                                            $Envio->update();
                                        }
                                    }
                                    $error = [
                                        "tracked" => $tracked,
                                        "message" => 'Se realizo correctamente el movimiento del paquete',
                                    ];
                                    array_push($errors_array, $error);
                                }
                            }
                        }else{
                            $error = [
                                "tracked" => $tracked,
                                "message" => "Aviso, No se realizo el movimiento ya que el paquete se encuentra en ese movimiento",
                            ];
                            array_push($errors_array, $error);
                        }
                    }else{
                        $error = [
                            "tracked" => $tracked,
                            "message" => "Hubo un error al realizar el movimiento, seleccione correctamente el movimiento",
                        ];
                        array_push($errors_array, $error);
                    }
                }else{
                    $error = [
                        "tracked" => $tracked,
                        "message" => "Hubo un error al buscar el trackend, intente nuevamente",
                    ];
                    array_push($errors_array, $error);
                }
            }
        }else{
            $error = [
                "tracked" => '',
                "message" => "Hubo un error, existen campos vacios que son requeridos, intente nuevamente",
            ];
            array_push($errors_array, $error);
        }
        return [
            "code"    => 202,
            "name"    => "Movimiento PAQUETE LOTE",
            "message" => $errors_array,
            "type"    => "Success",
        ];
    }



    /*****************************************
     *  VIAJES MEX
    *****************************************/
    public function actionGetCaja()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user       = $this->authToken($post["token"]);

        return [
            "code"    => 10,
            "name"    => "Caja",
            "message" => CajaMex::find()->where(["status" => CajaMex::STATUS_ACTIVE ])->all(),
            "type"    => "Success",
        ];
    }
    /*****************************************
     *  CONSULTA MEX
    *****************************************/
    public function actionGetMex()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user       = $this->authToken($post["token"]);
        $tracked    = isset($post["tracked"]) ? $post["tracked"] : null;
        $response   = "";

        if ($tracked) {

            $tracked_get    = trim($tracked);
            $tracked_get    = explode('/', $tracked_get);
            $clave          = explode("-",$tracked_get[0]);

            if (isset($tracked_get[1]) &&  $tracked_get[1] ) {

                $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0] ])->one();
                if ($model) {
                    if($model->cantidad >= intval($tracked_get[1]) && intval($tracked_get[1]) != 0){
                        $model->tracked_movimiento = trim($tracked);
                        return [
                            "code" => 202,
                            "name" => "Paquete",
                            "data" => [
                                "id"        => $model->id,
                                "tracked"   => $tracked,
                                "envio_id"  => $model->envio_id,
                                "tipo_envio"  => $model->envio->tipo_envio,
                                "cliente_receptor"  => $model->clienteReceptor->nombreCompleto,
                                "sucursal_receptor" => $model->sucursalReceptor->nombre,
                                "categoria" => isset($model->producto->categoria->singular) ? $model->producto->categoria->singular : null,
                                "producto"  => $model->producto->nombre,
                                "valor_declarado"   => $model->valor_declarado,
                                "cantidad"          => $model->cantidad,
                                "tipo_movimiento"   => EnvioDetalle::getMovimientoTop($tracked),
                                "tipo_movimiento_text"   => MovimientoPaquete::$tipoMexList[EnvioDetalle::getMovimientoTop($tracked)],
                                "cantidad_piezas"   => $model->cantidad_piezas,
                                "peso"              => $model->peso,
                                "valoracion_paquete"=> $model->valoracion_paquete,
                                "impuesto"          => $model->impuesto,
                                "costo_seguro"      => $model->costo_seguro,
                                "observaciones"     => $model->observaciones,
                            ],
                            "type" => "Success",
                        ];
                    }else
                        $response =  'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.';
                }else
                    $response     = 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.';
            }else
                $response = 'Debes ingresar correctamente el tracked.';

            return [
                "code" => 202,
                "name" => "Paquete",
                "data" => $response,
                "type" => "Warning",
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Paquete",
            "message" => 'El tracked es requerido',
            "type"    => "Error",
        ];
    }

}
