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
use app\models\mapeo\MapeoDetalle;
use app\models\movimiento\MovimientoPaquete;
use app\models\user\User;
use app\models\descarga\DescargaBodega;
use app\models\esys\EsysDireccion;

class PaqueteController extends DefaultController
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

    public function actionUpdateDescargaEnvio()
    {
        $envioAll = Envio::find()->all();

        foreach ($envioAll as $key => $envio) {
            foreach ($envio->envioDetalles as $key => $envio_detalle) {
                $envio_detalle = EnvioDetalle::findOne($envio_detalle->id);
                $envio_detalle->bodega_descarga = DescargaBodega::DESCARGA_SAN_JUAN;

                $EsysDireccion = EsysDireccion::find()->andWhere([
                    "and",
                    ['=', 'cuenta_id', $envio_detalle->id],
                    ['=', 'cuenta', EsysDireccion::CUENTA_REENVIO_PAQUETE],
                    ['=', 'tipo', EsysDireccion::TIPO_PERSONAL],
                ])->one();

                if (isset($EsysDireccion->estado_id) &&  isset($EsysDireccion->municipio_id)) {

                    $DescargaBodega = DescargaBodega::find()
                        ->andWhere([
                            "and",
                            ["=", "estado_id", $EsysDireccion->estado_id],
                            ["=", "municipio_id", $EsysDireccion->municipio_id],
                            ["=", "tipo", DescargaBodega::DESCARGA_MUNICIPIO]
                        ])->one();

                    if (!isset($DescargaBodega->id))
                        $DescargaBodega = DescargaBodega::find()->andWhere(["estado_id" => $EsysDireccion->estado_id])->andWhere(["tipo" =>  DescargaBodega::DESCARGA_ESTADO])->one();


                    $envio_detalle->bodega_descarga = isset($DescargaBodega->bodega_descarga) && $DescargaBodega->bodega_descarga ? $DescargaBodega->bodega_descarga : DescargaBodega::DESCARGA_SAN_JUAN;
                }
                $envio_detalle->save();
            }
        }

        return "¡Finalizo el update!";
    }

    /*****************************************
     *  CONSULTA CAJA (LAX - TIERRA) Y CAJA
     *****************************************/
    public function actionGetLaxTierra()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user       = $this->authToken($post["token"]);
        $tracked    = isset($post["tracked"]) ? $post["tracked"] : null;
        $response   = "";

        if ($tracked) {

            $tracked_get    = trim($tracked);
            $tracked_get    = explode('/', $tracked_get);
            $clave          = explode("-", $tracked_get[0]);

            //return $clave;

            if ($clave[0] . "-"  == Envio::CLAVE_SERV_MEX) {
                $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0]])->one();
                if ($model) {
                    if ($model->envio->status != Envio::STATUS_CANCELADO) {
                        if ($model->cantidad >= intval($tracked_get[1]) && intval($tracked_get[1]) != 0) {
                            $model->tracked_movimiento = trim($tracked);
                            $MapeoDetalle = MapeoDetalle::find()->andWhere(["tracked" => $model->tracked_movimiento])->orderBy('id desc')->one();
                            if ($model->status != EnvioDetalle::STATUS_CANCELADO) {
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
                                        //"tipo_movimiento_text"   => MovimientoPaquete::$tipoLaxTierList[EnvioDetalle::getMovimientoTop($tracked)],
                                        "cantidad_piezas"   => $model->cantidad_piezas,
                                        "peso"              => round($model->peso / $model->cantidad, 2),
                                        "valoracion_paquete" => $model->valoracion_paquete,
                                        "impuesto"          => $model->impuesto,
                                        "costo_seguro"      => $model->costo_seguro,
                                        "fila"              => isset($MapeoDetalle->fila->singular) ? $MapeoDetalle->fila->singular : null,
                                        "observaciones"     => $model->observaciones,
                                        "bodega_descarga"   => $model->bodega_descarga,
                                        "bodega_descarga_text"   => $model->bodega_descarga ? DescargaBodega::$descargaList[$model->bodega_descarga] : 'N/A',
                                    ],
                                    "type" => "Success",
                                ];
                            } else
                                $response     = 'Error el paquete a sido cancelado,  intente nuevamente.';
                        } else
                            $response =  'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.';
                    }
                }
                $response =  'Error al buscar la caja, no se encontro ninguna coincidencia en el sistema.';
            }

            if ($clave[0] . "-" == CajaMex::CLAVE_CAJA_MEX) {
                $model   = CajaMex::find()->where(['folio' => $tracked_get[0]])->one();
                if ($model) {
                    $model->tracked_movimiento = trim($tracked);

                    $paquete_caja_mex = [];

                    foreach ($model->cajaDetalleMex as $key => $caja) {
                        $paquete = [
                            "tracked"   => $caja->tracked,
                            "producto"  => $caja->envioDetalle->producto->nombre,
                            "categoria" => isset($caja->envioDetalle->producto->categoria->singular) ? $caja->envioDetalle->producto->categoria->singular : null,
                            "piezas"    => $caja->envioDetalle->cantidad_piezas,
                            "peso"      => $caja->envioDetalle->peso,
                            "impuesto"      => $caja->envioDetalle->impuesto,
                        ];
                        array_push($paquete_caja_mex, $paquete);
                    }

                    return [
                        "code" => 202,
                        "name" => "Caja",
                        "data" => [
                            "id"        => $model->id,
                            "folio"     => $tracked,
                            "nombre"    => $model->nombre,
                            "nota"      => $model->nota,
                            "paquetes"  => $paquete_caja_mex,

                        ],
                        "type" => "Success",
                    ];
                } else
                    $response =  'Error al buscar la caja, no se encontro ninguna coincidencia en el sistema.';
            } elseif (isset($tracked_get[1]) &&  $tracked_get[1]) {
                $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0]])->one();
                if ($model) {
                    if ($model->envio->status != Envio::STATUS_CANCELADO) {
                        if ($model->cantidad >= intval($tracked_get[1]) && intval($tracked_get[1]) != 0) {
                            $model->tracked_movimiento = trim($tracked);
                            $MapeoDetalle = MapeoDetalle::find()->andWhere(["tracked" => $model->tracked_movimiento])->orderBy('id desc')->one();
                            if ($model->status != EnvioDetalle::STATUS_CANCELADO) {
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
                                        "tipo_movimiento_text"   => MovimientoPaquete::$tipoLaxTierList[EnvioDetalle::getMovimientoTop($tracked)],
                                        "cantidad_piezas"   => $model->cantidad_piezas,
                                        "peso"              => round($model->peso / $model->cantidad, 2),
                                        "valoracion_paquete" => $model->valoracion_paquete,
                                        "impuesto"          => $model->impuesto,
                                        "costo_seguro"      => $model->costo_seguro,
                                        "fila"              => isset($MapeoDetalle->fila->singular) ? $MapeoDetalle->fila->singular : null,
                                        "observaciones"     => $model->observaciones,
                                        "bodega_descarga"   => $model->bodega_descarga,
                                        "bodega_descarga_text"   => $model->bodega_descarga ? DescargaBodega::$descargaList[$model->bodega_descarga] : 'N/A',
                                    ],
                                    "type" => "Success",
                                ];
                            } else
                                $response     = 'Error el paquete a sido cancelado,  intente nuevamente.';
                        } else
                            $response =  'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.';
                    } else
                        $response     = 'Error el envio al que pertenece a sido cancelado, intente nuevamente.';
                } else
                    $response     = 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.';
            } else
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

    /*****************************************
     *  MOVIMIENTO CAJA (LAX - TIERRA) Y CAJA   
     *****************************************/

    public function actionMovimientoLaxTierraMex_()
    {
        $post           = Yii::$app->request->post();
        $paquete_id     = isset($post['paquete_id']) ? $post['paquete_id'] : null;
        $user           = $this->authToken($post["token"]);
        $userGet        = User::findOne($user->id);
        $response       = "";

        if ($paquete_id) {
            $MovimientoPaquete = new MovimientoPaquete();
            $MovimientoPaquete->paquete_id              = $paquete_id;
            $MovimientoPaquete->tracked                 = isset($post['tracked_movimiento']) ? $post['tracked_movimiento'] : null;
            $MovimientoPaquete->tipo_envio              = isset($post['tipo_envio']) ? $post['tipo_envio'] : null;
            $MovimientoPaquete->tipo_movimiento         = isset($post['tipo_movimiento']) ? $post['tipo_movimiento'] : null;
            $MovimientoPaquete->paqueteria              = isset($post['paqueteria']) ? $post['paqueteria'] : null;
            $MovimientoPaquete->paqueteria_no_guia      = isset($post['paqueteria_no_guia']) ? $post['paqueteria_no_guia'] : null;
            $MovimientoPaquete->fecha_entrega           = isset($post['fecha_entrega']) ? strtotime($post['fecha_entrega']) : null;
            $MovimientoPaquete->tipo                    = MovimientoPaquete::TIPO_PAQUETE;
            $MovimientoPaquete->peso_mx                 = isset($post['peso_mx']) ? $post['peso_mx'] : null;
            $MovimientoPaquete->sucursal_descarga_id    = isset($post['sucursal_descarga_id']) ? $post['sucursal_descarga_id'] : null;
            $MovimientoPaquete->created_by              = $user->id;



            /**
             * AQUI VOY A A CHAMBEAR 
             * 
             */

            /**
             * PRIMERO OBTENER LA SUCRSAL
             */
            $sucursal_id = 0;

            $modelEnvioDEtealle = EnvioDetalle::findOne($paquete_id);
            if ($modelEnvioDEtealle) {
                $modelEnvio = Envio::findOne($modelEnvioDEtealle->envio_id);
                if ($modelEnvio) {
                    $sucursal_id = $modelEnvio->sucursal_emisor_id;
                }
            }

            if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_BODEGA)
                $MovimientoPaquete->bodega_descarga         = $userGet->bodega_descarga_asignado;


            if (isset($MovimientoPaquete->tipo_movimiento) && $MovimientoPaquete->tipo_movimiento) {

                if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_movimiento)) {

                    if (! $MovimientoPaquete->canMoveToTranscurso($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_movimiento, $sucursal_id)) {
                        $response = 'Aviso,El cambio de estado no se ha realizado, el paquete aún no ha sido escaneado en  bod"Los Angeles"';
                        return [
                            "code"    => 202,
                            "name"    => "Movimiento PAQUETE",
                            "message" => $response,
                            "type"    => "Success",
                        ];
                    }



                    if ($MovimientoPaquete->validaMovimientoTranscusoApertura($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio)) {
                        $response = "Aviso, no se realizo el movimiento porque se ingreso en un Viaje y no a concluido ó aperturado para realizar movimientos";
                        return [
                            "code"    => 202,
                            "name"    => "Movimiento PAQUETE",
                            "message" => $response,
                            "type"    => "Success",
                        ];
                    }
                    switch ($MovimientoPaquete->tipo_envio) {
                        case  Envio::TIPO_ENVIO_MEX:
                            /*==============================================================
                                      *  Validamos si el paquete ya se encuentra en una caja
                                    ================================================================*/
                            if ($MovimientoPaquete->validaMovimientoTrackedMex($MovimientoPaquete->tracked, MovimientoPaquete::MEX_CAJA)) {
                                if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_CAJA) {
                                    $MovimientoPaquete->caja_id         = isset($post['caja_id']) ? $post['caja_id'] : null;
                                    if (!$MovimientoPaquete->caja_id) {
                                        $response = "Hubo un error al realizar el movimiento, debe seleccionar una caja";
                                        return [
                                            "code"    => 202,
                                            "name"    => "Movimiento PAQUETE",
                                            "message" => $response,
                                            "type"    => "Success",
                                        ];
                                    }
                                }
                                if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_TRANSCURSO) {
                                    $MovimientoPaquete->viaje_id         = isset($post['viaje_mex_id']) ? $post['viaje_mex_id'] : null;
                                    if (!$MovimientoPaquete->viaje_id) {
                                        $response = 'Hubo un error al realizar el movimiento, debe seleccionar una viaje MEX';
                                        return [
                                            "code"    => 202,
                                            "name"    => "Movimiento PAQUETE",
                                            "message" => $response,
                                            "type"    => "Success",
                                        ];
                                    }
                                }
                            } else {
                                $response = 'Aviso, No se realizo el movimiento ya que el paquete se ingreso en una Caja y no se encuentra en Apertura';
                                return [
                                    "code"    => 202,
                                    "name"    => "Movimiento PAQUETE",
                                    "message" => $response,
                                    "type"    => "Success",
                                ];
                            }

                            break;
                        case  Envio::TIPO_ENVIO_TIERRA:
                            if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO) {
                                $MovimientoPaquete->viaje_id         = isset($post['viaje_tierra_id']) ? $post['viaje_tierra_id'] : null;
                                if (!$MovimientoPaquete->viaje_id) {
                                    $response = 'Hubo un error al realizar el movimiento, debe seleccionar una viaje TIERRA';
                                    return [
                                        "code"    => 202,
                                        "name"    => "Movimiento PAQUETE",
                                        "message" => $response,
                                        "type"    => "Success",
                                    ];
                                }
                            }
                            break;

                            #case  Envio::TIPO_ENVIO_LAX:
                            #    if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO) {
                            #        $MovimientoPaquete->viaje_id         = isset($post['viaje_lax_id']) ? $post['viaje_lax_id'] : null;
                            #        if (!$MovimientoPaquete->viaje_id) {
                            #            $response = 'Hubo un error al realizar el movimiento, debe seleccionar una viaje LAX';
                            #            return [
                            #                "code"    => 202,
                            #                "name"    => "Movimiento PAQUETE",
                            #                "message" => $response,
                            #                "type"    => "Success",
                            #            ];
                            #        }
                            #    }
                            #    break;
                    }

                    if ($MovimientoPaquete->saveMovimiento(true)) {
                        if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_BODEGA) {
                            if (isset($post['peso_mx']))
                                ViajeDetalle::changePesoMx($MovimientoPaquete->tracked, $post['peso_mx']);
                        }

                        if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_ENTREGADO || $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_ENTREGADO) {
                            if ($MovimientoPaquete->validaEntregaPaquete($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio)) {
                                $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                                $Envio = Envio::findOne($EnvioDetalle->envio->id);
                                $Envio->status      = Envio::STATUS_ENTREGADO;
                                $Envio->updated_by  = $user->id;
                                $Envio->update();
                            }
                        }

                        $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                        if (isset($EnvioDetalle->envio->id)) {
                            if ($EnvioDetalle->envio->status == Envio::STATUS_ENTREGADO) {
                                $Envio = Envio::findOne($EnvioDetalle->envio->id);
                                $Envio->status      = Envio::STATUS_HABILITADO;
                                $Envio->updated_by  = $user->id;
                                $Envio->update();
                            }
                        }

                        $response = 'Se realizo correctamente el movimiento del paquete';
                        return [
                            "code"    => 202,
                            "name"    => "Movimiento PAQUETE",
                            "message" => $response,
                            "type"    => "Success",
                        ];
                    } else {
                        $response = "Warning, No se realizo el movimiento ya que el paquete no cumple con las configuraciones del sistema";
                        return [
                            "code"    => 202,
                            "name"    => "Movimiento PAQUETE",
                            "message" => $response,
                            "type"    => "Success",
                        ];
                    }
                    /*}else{
                        $response = "Warning, No se realizo el movimiento por que no cumple con las politicas de la empresa, contacta al administrador";
                        return [
                            "code"    => 202,
                            "name"    => "Movimiento PAQUETE",
                            "message" => $response,
                            "type"    => "Success",
                        ];
                    }*/
                } else {
                    $response = "Aviso, No se realizo el movimiento ya que el paquete se encuentra en ese movimiento";
                    return [
                        "code"    => 202,
                        "name"    => "Movimiento PAQUETE",
                        "message" => $response,
                        "type"    => "Success",
                    ];
                }
            } else {
                $response = "Hubo un error al realizar el movimiento, seleccione correctamente el movimiento";
                return [
                    "code"    => 202,
                    "name"    => "Movimiento PAQUETE",
                    "message" => $response,
                    "type"    => "Success",
                ];
            }
        }


        return [
            "code"    => 10,
            "name"    => "Movimiento",
            "message" => "Hubo un error al realizar el movimiento, intente nuevamente",
            "type"    => "Error",
        ];
    }

    public function actionMovimientoLaxTierraMex()
    {
        $post           = Yii::$app->request->post();
        $paquete_id     = $post['paquete_id'] ?? null;
        $user           = $this->authToken($post["token"]);
        $userGet        = User::findOne($user->id);
        $response       = "";

        $tracked = $post['tracked_movimiento'] ?? null;

        if ($tracked) {
            $tracked_get = trim($tracked);
            $tracked_get = explode('/', $tracked_get);
            $clave = explode("-", $tracked_get[0]);
            if ($clave[0] . "-"  == Envio::CLAVE_SERV_MEX) {
                //$tipoList = MovimientoPaquete::$tipoMexList;
                return $this->saveMoveMXToUsa($post);
            }
        }

        if ($paquete_id) {
            $MovimientoPaquete = new MovimientoPaquete();
            $MovimientoPaquete->setAttributes([
                'paquete_id' => $paquete_id,
                'tracked' => $post['tracked_movimiento'] ?? null,
                'tipo_envio' => $post['tipo_envio'] ?? null,
                'tipo_movimiento' => $post['tipo_movimiento'] ?? null,
                'paqueteria' => $post['paqueteria'] ?? null,
                'paqueteria_no_guia' => $post['paqueteria_no_guia'] ?? null,
                'fecha_entrega' => isset($post['fecha_entrega']) ? strtotime($post['fecha_entrega']) : null,
                'tipo' => MovimientoPaquete::TIPO_PAQUETE,
                'peso_mx' => $post['peso_mx'] ?? null,
                'sucursal_descarga_id' => $post['sucursal_descarga_id'] ?? null,
                'created_by' => $user->id
            ]);

            $sucursal_id = 0;
            $modelEnvioDetalle = EnvioDetalle::findOne($paquete_id);
            if ($modelEnvioDetalle) {
                $modelEnvio = Envio::findOne($modelEnvioDetalle->envio_id);
                if ($modelEnvio) {
                    $sucursal_id = $modelEnvio->sucursal_emisor_id;
                }
            }

            if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_BODEGA) {
                $MovimientoPaquete->bodega_descarga = $userGet->bodega_descarga_asignado;
            }

            if ($MovimientoPaquete->tipo_movimiento && !$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_movimiento)) {

                if (!$MovimientoPaquete->canMoveToTranscurso($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_movimiento, $sucursal_id)) {
                    return $this->buildResponse('Aviso,El cambio de estado no se ha realizado, el paquete aún no ha sido escaneado en bod "Los Angeles"', 202);
                }

                if ($MovimientoPaquete->validaMovimientoTranscusoApertura($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio)) {
                    return $this->buildResponse("Aviso, no se realizó el movimiento porque el paquete está en un Viaje no concluido o aperturado", 202);
                }

                switch ($MovimientoPaquete->tipo_envio) {
                    case Envio::TIPO_ENVIO_MEX:
                        if ($MovimientoPaquete->validaMovimientoTrackedMex($MovimientoPaquete->tracked, MovimientoPaquete::MEX_CAJA)) {
                            if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_CAJA) {
                                $MovimientoPaquete->caja_id = $post['caja_id'] ?? null;
                                if (!$MovimientoPaquete->caja_id) {
                                    return $this->buildResponse("Debe seleccionar una caja para realizar el movimiento", 202);
                                }
                            } elseif ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_TRANSCURSO) {
                                $MovimientoPaquete->viaje_id = $post['viaje_mex_id'] ?? null;
                                if (!$MovimientoPaquete->viaje_id) {
                                    return $this->buildResponse("Debe seleccionar un viaje MEX para realizar el movimiento", 202);
                                }
                            }
                        } else {
                            return $this->buildResponse("El paquete está en una Caja, no se puede realizar el movimiento", 202);
                        }
                        break;

                    case Envio::TIPO_ENVIO_TIERRA:
                        if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO) {
                            $MovimientoPaquete->viaje_id = $post['viaje_tierra_id'] ?? null;
                            if (!$MovimientoPaquete->viaje_id) {
                                return $this->buildResponse("Debe seleccionar un viaje TIERRA para realizar el movimiento", 202);
                            }
                        }
                        break;
                }

                if ($MovimientoPaquete->saveMovimiento(true)) {
                    if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_BODEGA && isset($post['peso_mx'])) {
                        ViajeDetalle::changePesoMx($MovimientoPaquete->tracked, $post['peso_mx']);
                    }

                    if (
                        in_array($MovimientoPaquete->tipo_movimiento, [MovimientoPaquete::MEX_ENTREGADO, MovimientoPaquete::LAX_TIER_ENTREGADO]) &&
                        $MovimientoPaquete->validaEntregaPaquete($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio)
                    ) {

                        $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                        if ($EnvioDetalle) {
                            $Envio = Envio::findOne($EnvioDetalle->envio_id);
                            if ($Envio) {
                                $Envio->status = Envio::STATUS_ENTREGADO;
                                $Envio->updated_by = $user->id;
                                $Envio->update();
                            }
                        }
                    }

                    $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                    if ($EnvioDetalle && $EnvioDetalle->envio->status == Envio::STATUS_ENTREGADO) {
                        $Envio = Envio::findOne($EnvioDetalle->envio_id);
                        if ($Envio) {
                            $Envio->status = Envio::STATUS_HABILITADO;
                            $Envio->updated_by = $user->id;
                            $Envio->update();
                        }
                    }

                    return $this->buildResponse('Se realizó correctamente el movimiento del paquete', 202);
                } else {
                    return $this->buildResponse("El movimiento no cumple con las configuraciones del sistema", 202);
                }
            } else {
                return $this->buildResponse("El paquete ya se encuentra en ese movimiento", 202);
            }
        }

        return $this->buildResponse("Debe seleccionar correctamente el movimiento", 202);
    }


    private function  saveMoveMXToUsa($post)
    {
        $post           = Yii::$app->request->post();
        $paquete_id     = $post['paquete_id'] ?? null;
        $user           = $this->authToken($post["token"]);
        $userGet        = User::findOne($user->id);
        $response       = "";

        $MovimientoPaquete = new MovimientoPaquete();
        $MovimientoPaquete->setAttributes([
            'paquete_id' => $paquete_id,
            'tracked' => $post['tracked_movimiento'] ?? null,
            'tipo_envio' => $post['tipo_envio'] ?? null,
            'tipo_movimiento' => $post['tipo_movimiento'] ?? null,
            'paqueteria' => $post['paqueteria'] ?? null,
            'paqueteria_no_guia' => $post['paqueteria_no_guia'] ?? null,
            'fecha_entrega' => isset($post['fecha_entrega']) ? strtotime($post['fecha_entrega']) : null,
            'tipo' => MovimientoPaquete::TIPO_PAQUETE,
            'peso_mx' => $post['peso_mx'] ?? null,
            'sucursal_descarga_id' => $post['sucursal_descarga_id'] ?? null,
            'created_by' => $user->id
        ]);

        if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_SUCURSAL_USA) {
            $MovimientoPaquete->bodega_descarga = $userGet->bodega_descarga_asignado;
        }


        if ($MovimientoPaquete->tipo_movimiento && !$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_movimiento)) {

           

            if ($MovimientoPaquete->validaMovimientoTranscusoApertura($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio)) {
                return $this->buildResponse("Aviso, no se realizó el movimiento porque el paquete está en un Viaje no concluido o aperturado", 202);
            }

            if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_TRANSCURSO) {
                $MovimientoPaquete->viaje_id = $post['viaje_mex_id'] ?? null;
                if (!$MovimientoPaquete->viaje_id) {
                    return $this->buildResponse("Debe seleccionar un viaje TIERRA para realizar el movimiento", 202);
                }
            }

            if ($MovimientoPaquete->saveMovimiento(true)) {
                if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_SUCURSAL_USA && isset($post['peso_mx'])) {
                    ViajeDetalle::changePesoMx($MovimientoPaquete->tracked, $post['peso_mx']);
                }

                if (
                    in_array($MovimientoPaquete->tipo_movimiento, [MovimientoPaquete::MEX_ENTREGADO, MovimientoPaquete::LAX_TIER_ENTREGADO]) &&
                    $MovimientoPaquete->validaEntregaPaquete($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio)
                ) {

                    $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                    if ($EnvioDetalle) {
                        $Envio = Envio::findOne($EnvioDetalle->envio_id);
                        if ($Envio) {
                            $Envio->status = Envio::STATUS_ENTREGADO;
                            $Envio->updated_by = $user->id;
                            $Envio->update();
                        }
                    }
                }

                $EnvioDetalle = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                if ($EnvioDetalle && $EnvioDetalle->envio->status == Envio::STATUS_ENTREGADO) {
                    $Envio = Envio::findOne($EnvioDetalle->envio_id);
                    if ($Envio) {
                        $Envio->status = Envio::STATUS_HABILITADO;
                        $Envio->updated_by = $user->id;
                        $Envio->update();
                    }
                }

                return $this->buildResponse('Se realizó correctamente el movimiento del paquete', 202);
            } else {
                return $this->buildResponse("El movimiento no cumple con las configuraciones del sistema", 202);
            }
        } else {
            return $this->buildResponse("El paquete ya se encuentra en ese movimiento", 202);
        }
    }

    private function buildResponse($message, $code = 200, $type = "Success")
    {
        return [
            "code"    => $code,
            "name"    => "Movimiento PAQUETE",
            "message" => $message,
            "type"    => $type,
        ];
    }


    /*****************************************
     *  MOVIMIENTO CAJA (LAX - TIERRA) Y CAJA
     *****************************************/
    public function actionMovimientoCaja()
    {
        $post           = Yii::$app->request->post();

        $paquete_id     = isset($post['paquete_id']) ? $post['paquete_id'] : null;
        $user           = $this->authToken($post["token"]);
        $response       = "";
        if ($paquete_id) {
            $MovimientoPaquete = new MovimientoPaquete();
            $MovimientoPaquete->paquete_id      = isset($post['paquete_id']) ? $post['paquete_id'] : null;
            $MovimientoPaquete->tracked         = isset($post['tracked_movimiento']) ? $post['tracked_movimiento'] : null;
            $MovimientoPaquete->tipo_envio      = isset($post['tipo_envio']) ? $post['tipo_envio'] : null;
            $MovimientoPaquete->tipo_movimiento = isset($post['tipo_movimiento']) ? $post['tipo_movimiento'] : null;
            $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_CAJA;
            $MovimientoPaquete->created_by      = $user->id;

            if (isset($MovimientoPaquete->tipo_movimiento) && $MovimientoPaquete->tipo_movimiento) {
                if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_movimiento)) {

                    if ($MovimientoPaquete->validaMovimientoTranscusoApertura($MovimientoPaquete->tracked, $MovimientoPaquete->tipo_envio)) {
                        $response = "Aviso, no se realizo el movimiento porque se ingreso en un Viaje y no a concluido ó aperturado para realizar movimientos";
                        return [
                            "code"    => 202,
                            "name"    => "Movimiento CAJA",
                            "message" => $response,
                            "type"    => "Success",
                        ];
                    }

                    if ($MovimientoPaquete->tipo_movimiento == MovimientoPaquete::MEX_TRANSCURSO) {
                        $MovimientoPaquete->viaje_id         = isset($post['viaje_mex_caja_id']) ? $post['viaje_mex_caja_id'] : null;
                        if (!$MovimientoPaquete->viaje_id) {
                            $response = " Hubo un error al realizar el movimiento, debe seleccionar una viaje MEX ";
                            return [
                                "code"    => 202,
                                "name"    => "Movimiento CAJA",
                                "message" => $response,
                                "type"    => "Success",
                            ];
                        }
                    }
                    if ($MovimientoPaquete->saveMovimiento())
                        $response = "Se realizo correctamente el movimiento del paquete";
                } else
                    $response = "Aviso, No se realizo el movimiento la caja se encuentra en ese movimiento";
            } else
                $response = "Hubo un error al realizar el movimiento, seleccione correctamente el movimiento";

            return [
                "code"    => 202,
                "name"    => "Movimiento CAJA",
                "message" => $response,
                "type"    => "Success",
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Movimiento",
            "message" => "Hubo un error al realizar el movimiento, intente nuevamente",
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  ESTATUS LAX -TIERRA
     *****************************************/
    public function actionEstatusLaxTierra()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user = $this->authToken($post["token"]);
        $tracked = $post['tracked'] ?? null;
        $estatus_array = [];
        //return $tracked;
        // Determina la lista a usar según el valor de 'tracked'
        $tipoList = MovimientoPaquete::$tipoLaxTierList; // Lista por defecto

        if ($tracked) {
            $tracked_get = trim($tracked);
            $tracked_get = explode('/', $tracked_get);
            $clave = explode("-", $tracked_get[0]);
            //return $clave[0];
            if ($clave[0] . "-"  == Envio::CLAVE_SERV_MEX) {
                $tipoList = MovimientoPaquete::$tipoMexList;
            }
        }
        // Construir la respuesta con la lista seleccionada
        foreach ($tipoList as $key => $item) {
            $estatus_array[] = [
                "id" => $key,
                "nombre" => $item,
            ];
        }

        return [
            "code" => 10,
            "name" => "Paquete",
            "message" => $estatus_array,
            "type" => "Success",
        ];
    }




    /*****************************************
     *  ESTATUS LAX -TIERRA
     *****************************************/
    public function actionGetBodega()
    {
        $post           = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $bodega_array  = [];
        foreach (DescargaBodega::$descargaList as $key => $item) {
            $bodega =  [
                "id"        => $key,
                "nombre"    => $item
            ];
            array_push($bodega_array, $bodega);
        }
        return [
            "code"      => 10,
            "name"      => "Paquete",
            "bodega"    => $bodega_array,
            "type"      => "Success",
        ];
    }

    /*****************************************
     *  ESTATUS MEX
     *****************************************/
    public function actionEstatusCajaMex()
    {
        $post           = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $estatus_array  = [
            [
                "id" => MovimientoPaquete::MEX_TRANSCURSO,
                "nombre" => MovimientoPaquete::$tipoMexList[MovimientoPaquete::MEX_TRANSCURSO],
            ]
        ];

        return [
            "code"    => 10,
            "name"    => "Paquete",
            "message" => $estatus_array,
            "type"    => "Success",
        ];
    }

    /*****************************************
     *  VIAJES MEX
     *****************************************/
    public function actionGetViaje()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user       = $this->authToken($post["token"]);
        $tracked    = isset($post["tracked"]) ? $post["tracked"] : null;

        if ($tracked) {
            $tracked_get    = trim($tracked);
            $tracked_get    = explode('/', $tracked_get);
            $clave          = explode("-", $tracked_get[0]);
            $viaje_array  = [];
            if ($clave[0] . "-" == CajaMex::CLAVE_CAJA_MEX) {
                foreach (Viaje::getTranscursoMex() as $key => $item) {
                    $estatus =  [
                        "id"        => $key,
                        "nombre"    => $item
                    ];
                    array_push($viaje_array, $estatus);
                }
            } elseif (isset($tracked_get[1]) &&  $tracked_get[1]) {
                $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0]])->one();
                if ($model) {
                    switch ($model->envio->tipo_envio) {
                        case Envio::TIPO_ENVIO_MEX:
                            foreach (Viaje::getTranscursoMex() as $key => $item) {
                                $estatus =  [
                                    "id"        => $key,
                                    "nombre"    => $item
                                ];
                                array_push($viaje_array, $estatus);
                            }
                            break;

                            #case Envio::TIPO_ENVIO_LAX:
                            #    foreach (Viaje::getTranscursoLax() as $key => $item) {
                            #        $estatus =  [
                            #            "id"        => $key,
                            #            "nombre"    => $item
                            #        ];
                            #        array_push($viaje_array, $estatus);
                            #    }
                            #    break;

                        case Envio::TIPO_ENVIO_TIERRA:
                            foreach (Viaje::getTranscursoTierra() as $key => $item) {
                                $estatus =  [
                                    "id"        => $key,
                                    "nombre"    => $item
                                ];
                                array_push($viaje_array, $estatus);
                            }
                            break;
                    }
                }
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
            "message" => 'El tracked es requerido',
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  PAQUETE REPARTO
     *****************************************/
    public function actionRepartoAddPaquete() {}


    /*****************************************
     *  RASTREO ENVIOS
     *****************************************/
    public function actionRastreo()
    {
        $get = Yii::$app->request->get();
        // Validamos Token

        $folio    = isset($get["folio"]) ? $get["folio"] : null;
        $response   = "";

        if ($folio) {

            $tracked_get    = trim($folio);
            $tracked_get    = explode('/', $tracked_get);
            $clave          = explode("-", $tracked_get[0]);
            $isMexToUsa = false;

            if ($clave[0] . "-" == Envio::CLAVE_SERV_MEX) {
                if (isset($clave) &&  $clave) {
                    $isMexToUsa = true;
                    //return$tracked_get;
                }
            }



            if ($clave[0] . "-" == CajaMex::CLAVE_CAJA_MEX) {
                return [
                    "code" => 202,
                    'isMexToUsa' => $isMexToUsa,
                    "name" => "Caja",
                    "message" => 'El folio que ingresaste no corresponde a un envio, si no a una caja',
                    "type"    => "Error",
                ];
            } elseif (isset($clave) &&  $clave) {

                if (isset($tracked_get[1]) &&  $tracked_get[1]) {
                    $model   = EnvioDetalle::find()->where(['tracked' => $tracked_get[0]])->one();
                    if ($model) {
                        if ($model->status != Envio::STATUS_CANCELADO) {

                            $movimintoAll = [];
                            $MovimientoPaquete = MovimientoPaquete::getMovimientoItem(trim($folio));
                            array_push($movimintoAll, $MovimientoPaquete);

                            return [

                                "code" => 202,
                                'isMexToUsa' => $isMexToUsa,
                                "name" => "Paquete",
                                "data" => $movimintoAll,
                                "type" => "Success",
                            ];
                        } else
                            $response     = 'Error el envio a sido cancelado, intente nuevamente.';
                    } else
                        $response     = 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.';
                } else {
                    $model   = Envio::find()->where(['folio' => $tracked_get])->one();
                    if ($model) {
                        if ($model->status != Envio::STATUS_CANCELADO) {

                            $envioPaquetes = [];
                            foreach ($model->envioDetalles as $key => $e_detalle) {
                                $movimintoAll = [];
                                for ($i = 0; $i < $e_detalle->cantidad; $i++) {
                                    $tracked_movimiento = $e_detalle->tracked . "/" . ($i + 1);
                                    $MovimientoPaquete = MovimientoPaquete::getMovimientoItem($tracked_movimiento);
                                    array_push($movimintoAll, $MovimientoPaquete);
                                }
                                array_push($envioPaquetes, $movimintoAll);
                            }

                            return [
                                "code" => 202,
                                'isMexToUsa' => $isMexToUsa,
                                "name" => "Envio",
                                "data" => $envioPaquetes,
                                "type" => "Success",
                            ];
                        } else
                            $response     = 'Error el envio a sido cancelado, intente nuevamente.';
                    } else
                        $response     = 'Error al buscar el paquete, no se encontro ninguna coincidencia en el sistema.';
                }
            } else
                $response = 'Debes ingresar correctamente el tracked.';

            return [
                "code" => 202,
                'isMexToUsa' => $isMexToUsa,
                "name" => "Paquete",
                "data" => $response,
                "type" => "Warning",
            ];
        }
        return [
            "code"    => 10,
            'isMexToUsa' => false,
            "name"    => "Paquete",
            "message" => 'El folio es requerido',
            "type"    => "Error",
        ];
    }
}
