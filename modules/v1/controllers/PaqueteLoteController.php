<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\models\caja\CajaMex;
use app\models\envio\EnvioDetalle;
use app\models\envio\Envio;
use app\models\viaje\Viaje;
use app\models\viaje\ViajeDetalle;
use app\models\movimiento\MovimientoPaquete;
use app\models\descarga\DescargaBodega;

class PaqueteLoteController extends DefaultController
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
    public function actionMovimientoLoteLaxTierraMex()
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
                    $MovimientoPaquete->fecha_entrega   = isset($post['fecha_entrega']) ? strtotime($post['fecha_entrega']) : null;
                    $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                    $MovimientoPaquete->created_by      = $user->id;
                    $MovimientoPaquete->bodega_descarga = isset($post['bodega_descarga_id']) ? $post['bodega_descarga_id'] : null;
                    $MovimientoPaquete->sucursal_descarga_id   = isset($post['sucursal_descarga_id']) ? $post['sucursal_descarga_id'] : null;


                    if ($EnvioDetalle->envio->status != Envio::STATUS_CANCELADO) {
                        if ($EnvioDetalle->status != EnvioDetalle::STATUS_CANCELADO ) {
                            if (isset($MovimientoPaquete->tipo_movimiento) && $MovimientoPaquete->tipo_movimiento) {

                                if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
                                    //if ($MovimientoPaquete->validaMovimientoAdmin($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
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
                                            case  Envio::TIPO_ENVIO_TIERRA:
                                                if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO){
                                                    $MovimientoPaquete->viaje_id         = isset($post['viaje_tierra_id']) ? $post['viaje_tierra_id'] : null;
                                                    if(!$MovimientoPaquete->viaje_id ){
                                                        $error = [
                                                            "tracked" => $tracked,
                                                            "message" => 'Hubo un error al realizar el movimiento, debe seleccionar una viaje TIERRA',
                                                        ];
                                                        array_push($errors_array, $error);
                                                        $is_validation = false;
                                                    }
                                                }

                                                if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_BODEGA){
                                                    if(intval($MovimientoPaquete->bodega_descarga) != intval($EnvioDetalle->bodega_descarga) ){
                                                        $error = [
                                                            "tracked" => $tracked,
                                                            "message" => 'La BODEGA no corresponde a la asignada, contacta al administrador',
                                                        ];
                                                        array_push($errors_array, $error);
                                                        $is_validation = false;
                                                    }
                                                }
                                            break;

                                            case  Envio::TIPO_ENVIO_LAX:
                                                if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO){
                                                    $MovimientoPaquete->viaje_id         = isset($post['viaje_lax_id']) ? $post['viaje_lax_id'] : null;
                                                    if(!$MovimientoPaquete->viaje_id ){
                                                        $error = [
                                                            "tracked" => $tracked,
                                                            "message" => 'Hubo un error al realizar el movimiento, debe seleccionar una viaje LAX',
                                                        ];
                                                        array_push($errors_array, $error);
                                                        $is_validation = false;
                                                    }
                                                }
                                            break;
                                        }
                                        if ($is_validation) {
                                            if ($MovimientoPaquete->saveMovimiento(true) ) {
                                                if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_ENTREGADO || $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_ENTREGADO){
                                                    if($MovimientoPaquete->validaEntregaPaquete($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio )){
                                                        $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                                                        $Envio = Envio::findOne($EnvioDetalle->envio->id);
                                                        $Envio->status      = Envio::STATUS_ENTREGADO;
                                                        $Envio->updated_by  = $user->id;
                                                        $Envio->update();
                                                    }
                                                }
                                                $error = [
                                                    "tracked" => $tracked,
                                                    "message" => 'Se realizo correctamente el movimiento del paquete',
                                                ];
                                                array_push($errors_array, $error);
                                            }else{
                                                $error = [
                                                    "tracked" => $tracked,
                                                    "message" => "Warning, No se realizo el movimiento ya que el paquete no cumple con las configuraciones del sistema",
                                                ];
                                                array_push($errors_array, $error);
                                            }
                                        }
                                    /*}else{

                                        $error = [
                                            "tracked" => $tracked,
                                            "message" => "Warning, No se realizo el movimiento por que no cumple con las politicas de la empresa, contacta al administrador",
                                        ];

                                        array_push($errors_array, $error);
                                    }*/
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
                                "message" => 'Error el paquete a sido cancelado,  intente nuevamente.',
                            ];
                            array_push($errors_array, $error);
                        }
                    }else{
                        $error = [
                            "tracked" => $tracked,
                            "message" => 'Error el envio al que pertenece a sido cancelado, intente nuevamente.',
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
     *  MOVIMIENTO CAJA (LAX - TIERRA) Y CAJA
    *****************************************/
    public function actionMovimientoLoteCaja()
    {
        $post                       = Yii::$app->request->post();
        $tracked_movimiento_array   = isset($post['tracked_movimiento_array']) ? $post['tracked_movimiento_array'] : null;
        $user                       = $this->authToken($post["token"]);
        $errors_array               = [];

        if ($post['tipo_movimiento'] && count($tracked_movimiento_array) > 0) {
            foreach ($tracked_movimiento_array as $key => $tracked) {
                $is_validation              = true;

                $CajaMex = CajaMex::getCajaFolio($tracked);

                if($CajaMex) {

                    $MovimientoPaquete = new MovimientoPaquete();
                    $MovimientoPaquete->paquete_id      = $CajaMex->id;
                    $MovimientoPaquete->tracked         = $CajaMex->folio;
                    $MovimientoPaquete->tipo_envio      = Envio::TIPO_ENVIO_MEX;
                    $MovimientoPaquete->tipo_movimiento = isset($post['tipo_movimiento']) ? $post['tipo_movimiento'] : null;
                    $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_CAJA;
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

                            if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_TRANSCURSO){
                                $MovimientoPaquete->viaje_id         = isset($post['viaje_mex_caja_id']) ? $post['viaje_mex_caja_id'] : null;
                                if(!$MovimientoPaquete->viaje_id ){
                                    $error = [
                                        "tracked" => $tracked,
                                        "message" => "Hubo un error al realizar el movimiento, debe seleccionar una viaje MEX",
                                    ];
                                    array_push($errors_array, $error);
                                    $is_validation = false;
                                }
                            }
                            if ($is_validation) {
                                if ($MovimientoPaquete->saveMovimiento()){
                                    $error = [
                                        "tracked" => $tracked,
                                        "message" => "Se realizo correctamente el movimiento del caja",
                                    ];
                                    array_push($errors_array, $error);
                                }
                            }
                        }else{
                            $error = [
                                "tracked" => $tracked,
                                "message" => "Aviso, No se realizo el movimiento la caja se encuentra en ese movimiento",
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
                        "message" => "Hubo un error al buscar el folio, intente nuevamente",
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
            "name"    => "Movimiento CAJA LOTE",
            "message" => $errors_array,
            "type"    => "Success",
        ];
    }

    /*****************************************
     *  VIAJES MEX
    *****************************************/
    public function actionGetViajeLote(){
        $post = Yii::$app->request->post();
        // Validamos Token
        $user       = $this->authToken($post["token"]);
        $tracked    = isset($post["tracked"]) ? $post["tracked"] : null;
        $tipo_envio = isset($post["tipo_envio"]) ? $post["tipo_envio"] : null;
        $viaje_array  = [];


        if ($tipo_envio) {

            switch ($tipo_envio) {
                case Envio::TIPO_ENVIO_TIERRA:
                    foreach (Viaje::getTranscursoTierra() as $key => $item) {
                        $estatus =  [
                            "id"        => $key,
                            "nombre"    => $item
                        ];
                        array_push($viaje_array, $estatus);
                    }
                break;

                case Envio::TIPO_ENVIO_LAX:
                    foreach (Viaje::getTranscursoLax() as $key => $item) {
                        $estatus =  [
                            "id"        => $key,
                            "nombre"    => $item
                        ];
                        array_push($viaje_array, $estatus);
                    }

                break;

                case Envio::TIPO_ENVIO_MEX:
                    foreach (Viaje::getTranscursoMex() as $key => $item) {
                        $estatus =  [
                            "id"        => $key,
                            "nombre"    => $item
                        ];
                        array_push($viaje_array, $estatus);
                    }
                break;
            }

            return [
                "code"    => 10,
                "name"    => "Viaje",
                "message" => $viaje_array,
                "type"    => "Success",
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Paquete",
            "message" => 'El tipo de envio es requerido',
            "type"    => "Error",
        ];
    }

    public function actionMovimientoEntregaPaquete()
    {
        $post       = Yii::$app->request->post();
        $user       = $this->authToken($post["token"]);
        // Validamos Token
        $sucursales         = isset($post['sucursales']) ? $post['sucursales'] : null;
        $errors_array       = [];

        if (count($sucursales) > 0) {
            foreach ($sucursales as $key => $sucursal) {
                $sucursal_id    = isset($sucursal["sucursal_id"])   ? $sucursal["sucursal_id"]: null;
                $paquete_array  = isset($sucursal["paquete_array"]) ? $sucursal["paquete_array"]: null;

                if ($sucursal_id) {
                    foreach ($paquete_array as $key => $paquete) {
                        $tracked_get    = trim($paquete);
                        $tracked_get    = explode('/', $tracked_get);
                        $clave          = explode("-",$tracked_get[0]);
                        $EnvioDetalle = EnvioDetalle::getEnvioDetalleFolio($tracked_get[0]);
                        if($EnvioDetalle) {

                            $MovimientoPaquete = new MovimientoPaquete();
                            $MovimientoPaquete->paquete_id      = $EnvioDetalle->id;
                            $MovimientoPaquete->tracked         = $paquete;
                            $MovimientoPaquete->tipo_envio      = $EnvioDetalle->envio->tipo_envio;
                            $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_ENTREGADO;
                            $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                            $MovimientoPaquete->created_by      = $user->id;

                            if ($EnvioDetalle->envio->status != Envio::STATUS_CANCELADO) {
                                if ($EnvioDetalle->status != EnvioDetalle::STATUS_CANCELADO ) {
                                    $MovimientoPaquete->sucursal_id = $sucursal_id;

                                    if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {

                                        if ($MovimientoPaquete->save()) {
                                            $Envio = Envio::findOne($EnvioDetalle->envio->id);
                                            $Envio->status      = Envio::STATUS_ENTREGADO;
                                            $Envio->updated_by  = $user->id;
                                            $Envio->update();
                                            array_push($errors_array, [
                                                "tracked" => $paquete,
                                                "message" => 'Se realizo correctamente el movimiento del paquete',
                                            ]);
                                        }else
                                            array_push($errors_array, [
                                                "tracked" => $paquete,
                                                "message" => 'Ocurrio un error al registrar el cambio, intente nuevamente ',
                                            ]);
                                    }else
                                        array_push($errors_array, [
                                            "tracked" => $paquete,
                                            "message" => "Aviso, No se realizo el movimiento ya que el paquete se encuentra en ese movimiento",
                                        ]);


                                }else
                                    array_push($errors_array, [
                                        "tracked" => $paquete,
                                        "message" => 'Error el paquete a sido cancelado,  intente nuevamente.',
                                    ]);
                            }else
                                array_push($errors_array, [
                                    "tracked" => $paquete,
                                    "message" => 'Error el envio al que pertenece a sido cancelado, intente nuevamente.',
                                ]);

                        }else{
                            array_push($errors_array, [
                                "tracked" => $paquete,
                                "message" => "Hubo un error al buscar el trackend, intente nuevamente",
                            ]);
                        }
                    }
                }else
                    array_push($errors_array, [
                        "tracked" => '',
                        "message" => "Error en los datos, intenta nuevamente",
                    ]);
            }

        }else
            array_push($errors_array, [
                "tracked" => '',
                "message" => "Hubo un error, existen campos vacios que son requeridos, intente nuevamente",
            ]);

        return [
            "code"    => 202,
            "name"    => "Movimiento PAQUETE LOTE",
            "message" => $errors_array,
            "type"    => "Success",
        ];


    }
}
