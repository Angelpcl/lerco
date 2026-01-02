<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\models\movimiento\MovimientoPaquete;
use app\models\reparto\Reparto;
use app\models\reparto\RepartoDetalle;
use app\models\envio\EnvioDetalle;
use app\models\envio\Envio;
use app\models\Esys;
class RepartoController extends DefaultController
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
     *  ESTATUS MEX
    *****************************************/
    public function actionRepartoUnidades()
    {
        $post           = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $Reparto        =  Reparto::find()->andWhere(["status" => Reparto::STATUS_ACTIVE])->all();
       $unidades_array  = [];
        foreach ($Reparto as $key => $item) {
            $unidad = [
                "id"        => $item->id,
                "nombre"    => $item->numUnidad->singular." [".$item->chofer->singular."]" ." / ". Esys::unixTimeToString($item->created_at),
            ];

            array_push($unidades_array, $unidad);
        }

        return [
            "code"    => 202,
            "name"    => "Reparto",
            "message" => $unidades_array,
            "type"    => "Success",
        ];

    }

    /*****************************************
     *  PAQUETE REPARTO
    *****************************************/
    public function actionRepartoAddPaquete()
    {

        $post           = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);

        $reparto_id = isset($post["reparto_id"]) ? $post["reparto_id"] : null;
        $tracked    = isset($post['tracked']) ? $post['tracked'] : null;
        //$peso       = isset($post['peso']) ? $post['peso'] : null;
        $errors_array               = [];
        if ($reparto_id && $tracked /*&& $peso*/) {
            //foreach ($tracked_movimiento_array as $key => $tracked) {
                $tracked_get    = trim($tracked);
                $tracked_get    = explode('/', $tracked_get);
                $clave          = explode("-",$tracked_get[0]);
                if (isset($tracked_get[1]) &&  $tracked_get[1] ) {

                    $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0] ])->one();
                    if ($model) {
                        if ($model->envio->status != Envio::STATUS_CANCELADO) {
                            if ($model->status != EnvioDetalle::STATUS_CANCELADO ) {
                                if($model->cantidad >= intval($tracked_get[1]) && intval($tracked_get[1]) != 0){
                                    $RepartoDetalle = new  RepartoDetalle();
                                    $RepartoDetalle->reparto_id  = $reparto_id;
                                    $RepartoDetalle->tracked     = $tracked;
                                    $RepartoDetalle->paquete_id  = $model->id;
                                    //$RepartoDetalle->peso_reparto  = $peso;

                                    $MovimientoPaquete = new MovimientoPaquete();
                                    $MovimientoPaquete->paquete_id      = $model->id;
                                    $MovimientoPaquete->tracked         = isset($tracked) ? $tracked : null;
                                    $MovimientoPaquete->reparto_id      = $RepartoDetalle->reparto_id;
                                    $MovimientoPaquete->tipo_envio      = $model->envio->tipo_envio;
                                    $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_REPARTO;
                                    $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                                    $MovimientoPaquete->created_by      = $user->id;

                                    if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
                                        if ($RepartoDetalle->save() && $MovimientoPaquete->save()) {
                                                $error = [
                                                    "tracked" => $tracked,
                                                    "message" => 'Se ingreso corrctamente el paquete al reparto.',
                                                ];
                                                array_push($errors_array, $error);

                                        }else{
                                            $error = [
                                                    "tracked" => $tracked,
                                                    "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
                                            ];
                                            array_push($errors_array, $error);

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
                                            "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
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
                                "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
                        ];
                        array_push($errors_array, $error);
                    }
                }else{
                    $error = [
                            "tracked" => $tracked,
                            "message" => 'Debes ingresar correctamente el tracked.',
                    ];
                    array_push($errors_array, $error);

                }
            //}
        }else{
            $error = [
                    "tracked" => null,
                    "message" => 'Todos los campos son requerido (Reparto, Tracked), intente nuevamente.',
            ];
            array_push($errors_array, $error);

        }

        return [
            "code"    => 10,
            "name"    => "Reparto",
            "message" => $errors_array,
            "type"    => "Error",
        ];

    }


    /*public function actionRepartoAddPaquete()
    {

        $post           = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);

        $reparto_id    = isset($post["reparto_id"]) ? $post["reparto_id"] : null;
        $tracked_movimiento_array   = isset($post['tracked_movimiento_array']) ? $post['tracked_movimiento_array'] : null;
        $errors_array               = [];
        if ($reparto_id && count($tracked_movimiento_array) > 0) {
            foreach ($tracked_movimiento_array as $key => $tracked) {
                $tracked_get    = trim($tracked);
                $tracked_get    = explode('/', $tracked_get);
                $clave          = explode("-",$tracked_get[0]);
                if (isset($tracked_get[1]) &&  $tracked_get[1] ) {

                    $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0] ])->one();
                    if ($model) {
                        if($model->cantidad >= intval($tracked_get[1]) && intval($tracked_get[1]) != 0){
                            $RepartoDetalle = new  RepartoDetalle();
                            $RepartoDetalle->reparto_id  = $reparto_id;
                            $RepartoDetalle->tracked     = $tracked;
                            $RepartoDetalle->paquete_id  = $model->id;

                            $MovimientoPaquete = new MovimientoPaquete();
                            $MovimientoPaquete->paquete_id      = $model->id;
                            $MovimientoPaquete->tracked         = isset($tracked) ? $tracked : null;
                            $MovimientoPaquete->reparto_id      = $RepartoDetalle->reparto_id;
                            $MovimientoPaquete->tipo_envio      = $model->envio->tipo_envio;
                            $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_REPARTO;
                            $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
                            $MovimientoPaquete->created_by      = $user->id;

                            if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
                                if ($RepartoDetalle->save() && $MovimientoPaquete->save()) {
                                        $error = [
                                            "tracked" => $tracked,
                                            "message" => 'Se ingreso corrctamente el paquete al reparto.',
                                        ];
                                        array_push($errors_array, $error);

                                }else{
                                    $error = [
                                            "tracked" => $tracked,
                                            "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
                                    ];
                                    array_push($errors_array, $error);

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
                                    "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
                            ];
                            array_push($errors_array, $error);

                        }

                    }else{
                        $error = [
                                "tracked" => $tracked,
                                "message" => 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.',
                        ];
                        array_push($errors_array, $error);
                    }
                }else{
                    $error = [
                            "tracked" => $tracked,
                            "message" => 'Debes ingresar correctamente el tracked.',
                    ];
                    array_push($errors_array, $error);

                }
            }
        }else{
            $error = [
                    "tracked" => null,
                    "message" => 'El reparto y trackeds son requerido, intente nuevamente.',
            ];
            array_push($errors_array, $error);

        }

        return [
            "code"    => 10,
            "name"    => "Reparto",
            "message" => $errors_array,
            "type"    => "Error",
        ];

    }*/

}
