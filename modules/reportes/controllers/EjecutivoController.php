<?php
namespace app\modules\reportes\controllers;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\web\Controller;
use app\models\viaje\Viaje;
use app\models\envio\Envio;
use app\models\envio\ViewEnvio;
use app\models\envio\EnvioDetalle;
use yii\web\BadRequestHttpException;
use app\models\movimiento\MovimientoPaquete;

/**
 * Default controller for the `clientes` module
 */
class EjecutivoController extends \app\controllers\AppController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionDocumentado()
    {
        return $this->render('documentado');
    }

    public function actionBodega()
    {
        return $this->render('bodega');
    }

    public function actionDescargaTrailer()
    {
        return $this->render('descarga_trailer');
    }

    public function actionEnvioMex()
    {
        return $this->render('envio_mex');
    }

    public function actionTrailerSeguimiento()
    {
        return $this->render('trailer_seguimiento');
    }

    public function actionReporteDataDocumentacion(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            parse_str(Yii::$app->request->get('filters'), $filters);

            $preventa = (new Query())
                ->select([
                    'SUM(envio.total) AS total_preventa',
                    'SUM(envio.peso_total) AS lb_preventa',
                    'SUM((SELECT  SUM(envio_detalle.cantidad) FROM envio_detalle WHERE  envio_detalle.envio_id = envio.id)) AS pz_preventa',
                ])
                ->from('envio')
                ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ])
                ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["IS NOT","envio.pre_created_at",new \yii\db\Expression('null')]);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $preventa->andWhere(['between','pre_created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $preventa->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);

            $preventaFaltante = (new Query())
                ->select([
                    'SUM(envio.total) AS total_preventa_faltante',
                    'SUM(envio.peso_total) AS lb_preventa_faltante',
                    'SUM((SELECT  SUM(envio_detalle.cantidad) FROM envio_detalle WHERE  envio_detalle.envio_id = envio.id)) AS pz_preventa_faltante',
                ])
                ->from('envio')
                ->andWhere(["envio.status" => Envio::STATUS_SOLICITADO ])
                ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["IS NOT","envio.pre_created_at",new \yii\db\Expression('null')]);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $preventaFaltante->andWhere(['between','pre_created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $preventaFaltante->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);


            $preventaTerminada = (new Query())
                ->select([
                    'SUM(envio.total) AS total_preventaTerminada',
                    'SUM(envio.peso_total) AS lb_preventaTerminada',
                    'SUM((SELECT  SUM(envio_detalle.cantidad) FROM envio_detalle WHERE  envio_detalle.envio_id = envio.id)) AS pz_preventaTerminada',
                ])
                ->from('envio')
                ->andWhere(["envio.status" => Envio::STATUS_HABILITADO ])
                //->andWhere(["=",new \yii\db\Expression("from_unixtime(envio.pre_created_at,'%Y-%d-%m')"),new \yii\db\Expression("from_unixtime(envio.created_at,'%Y-%d-%m')")])
                ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["IS NOT","envio.pre_created_at",new \yii\db\Expression('null')]);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $preventaTerminada->andWhere(['between','pre_created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $preventaTerminada->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);


            $preventaDesfasado = (new Query())
                ->select([
                    'SUM(envio.total) AS total_preventaDesfasado',
                    'SUM(envio.peso_total) AS lb_preventaDesfasado',
                    'SUM((SELECT  SUM(envio_detalle.cantidad) FROM envio_detalle WHERE  envio_detalle.envio_id = envio.id)) AS pz_preventaDesfasado',
                ])
                ->from('envio')
                ->andWhere(["envio.status" => Envio::STATUS_HABILITADO ])
                ->andWhere([">", 'envio.created_at',"envio.pre_created_at" ])
                ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["IS NOT","envio.pre_created_at",new \yii\db\Expression('null')]);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $preventaDesfasado->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $preventaDesfasado->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);


            $ventanilla = (new Query())
                ->select([
                    'SUM(envio.total) AS total_venta',
                    'SUM(envio.peso_total) AS lb_venta',
                    'SUM((SELECT  SUM(envio_detalle.cantidad) FROM envio_detalle WHERE  envio_detalle.envio_id = envio.id)) AS pz_venta',
                ])
                ->from('envio')
                ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ])
                ->andWhere(["<>","envio.tipo_envio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["IS","envio.pre_created_at",new \yii\db\Expression('null')]);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $ventanilla->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $ventanilla->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);

            $response = [
                'ventanilla'        => $ventanilla->one(),
                'preventa'          => $preventa->one(),
                'preventaTerminada' => $preventaTerminada->one(),
                'preventaDesfasado' => $preventaDesfasado->one(),
                'preventaFaltante'  => $preventaFaltante->one(),
            ];

            // Obtenemos sucursal
            //$sucursal = ViewSucursal::getSucursalesEstadoAjax($text);

            // Devolvemos datos CHOSEN.JS
            //$response = ['results' => $sucursal];


            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionReporteDataBodega(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            parse_str(Yii::$app->request->get('filters'), $filters);

            $venta = (new Query())
                ->select([
                    'SUM(envio.total) AS total_venta',
                    'SUM(envio.peso_total) AS lb_venta',
                    'SUM((SELECT  SUM(envio_detalle.cantidad) FROM envio_detalle WHERE  envio_detalle.envio_id = envio.id)) AS pz_venta',
                ])
                ->from('envio')
                ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ]);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $venta->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $venta->andWhere(['sucursal_emisor_id' =>  $filters['sucursal_id']]);

            $response = [
                'venta'          => $venta->one(),
            ];

            // Obtenemos sucursal
            //$sucursal = ViewSucursal::getSucursalesEstadoAjax($text);

            // Devolvemos datos CHOSEN.JS
            //$response = ['results' => $sucursal];


            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionReporteDataTrailer(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            parse_str(Yii::$app->request->get('filters'), $filters);

            $transcurso = (new Query())
                ->select([
                    'count(*) AS pz_venta',
                    '(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    ) as tipo_movimiento_top'
                ])
                ->from('viaje')
                ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                ->andWhere(["(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    )" => MovimientoPaquete::LAX_TIER_TRANSCURSO ]);

            if (isset($filters['viaje_id']) && $filters['viaje_id'])
                $transcurso->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);

            $transcurso->groupBy('viaje.id');


            $bodega = (new Query())
                ->select([
                    'count(*) AS pz_venta',
                    '(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    ) as tipo_movimiento_top'
                ])
                ->from('viaje')
                ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                ->andWhere(["(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    )" => MovimientoPaquete::LAX_TIER_BODEGA ]);

            if (isset($filters['viaje_id']) && $filters['viaje_id'])
                $bodega->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);

            $bodega->groupBy('viaje.id');

            $reparto = (new Query())
                ->select([
                    'count(*) AS pz_venta',
                    '(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    ) as tipo_movimiento_top'
                ])
                ->from('viaje')
                ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                ->andWhere(["(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    )" => MovimientoPaquete::LAX_TIER_REPARTO ]);

            if (isset($filters['viaje_id']) && $filters['viaje_id'])
                $reparto->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);

            $reparto->groupBy('viaje.id');

            $entregado = (new Query())
                ->select([
                    'count(*) AS pz_venta',
                    '(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    ) as tipo_movimiento_top'
                ])
                ->from('viaje')
                ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                ->andWhere(["(select mv.tipo_movimiento
                       from movimiento_paquete mv
                        where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                    )" => MovimientoPaquete::LAX_TIER_ENTREGADO ]);

            if (isset($filters['viaje_id']) && $filters['viaje_id'])
                $entregado->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);

            $entregado->groupBy('viaje.id');


            $response = [
                'transcurso'          => $transcurso->one(),
                'bodega'          => $bodega->one(),
                'reparto'          => $reparto->one(),
                'entregado'          => $entregado->one(),
            ];

            // Obtenemos sucursal
            //$sucursal = ViewSucursal::getSucursalesEstadoAjax($text);

            // Devolvemos datos CHOSEN.JS
            //$response = ['results' => $sucursal];


            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionTrailerReporte(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            parse_str(Yii::$app->request->get('filters'), $filters);

            $trailer = (new Query())
                ->select([
                    'viaje.num_viaje',
                    'viaje.nombre_chofer',
                    'viaje.id',
                    'round(sum((envio.total / (select  sum(env_det.cantidad) from envio_detalle env_det where env_det.envio_id = envio.id and env_det.status <> 1   ) )),2) as total',
                ])
                ->from('envio')
                ->innerJoin("envio_detalle","envio.id = envio_detalle.envio_id")
                ->innerJoin("viaje_detalle","envio_detalle.id = viaje_detalle.paquete_id")
                ->innerJoin("viaje","viaje_detalle.viaje_id = viaje.id")
                ->andWhere(["<>","viaje.tipo_servicio",Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["<>","envio.status",Envio::STATUS_CANCELADO ]);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $trailer->andWhere(['between','envio.created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $trailer->andWhere(['envio.sucursal_emisor_id' =>  $filters['sucursal_id']]);

            $trailer->groupBy("viaje.id");



            $response = [
                'trailer' => $trailer->all(),
            ];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionReporteDataTrailerInfo(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            parse_str(Yii::$app->request->get('filters'), $filters);

            $trailer = (new Query())
                ->select([
                    'count(*) as total_pz',
                    'round(sum((envio.peso_total / (select  sum(env_det.cantidad) from envio_detalle env_det where env_det.envio_id = envio.id and env_det.status <> 1   ) )),2) as total_lb',
                    'round(sum((envio.total / (select  sum(env_det.cantidad) from envio_detalle env_det where env_det.envio_id = envio.id and env_det.status <> 1   ) )),2) as total',
                ])
                ->from('envio')
                ->innerJoin("envio_detalle","envio.id = envio_detalle.envio_id")
                ->innerJoin("viaje_detalle","envio_detalle.id = viaje_detalle.paquete_id")
                ->innerJoin("viaje","viaje_detalle.viaje_id = viaje.id");

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $trailer->andWhere(['envio.sucursal_emisor_id' =>  $filters['sucursal_id']]);

            if (isset($filters['viaje_id']) && $filters['viaje_id'])
                $trailer->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);

            $trailer->groupBy("viaje.id");



            $response = [
                'trailer' => $trailer->one(),
            ];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionReporteDataMx(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            parse_str(Yii::$app->request->get('filters'), $filters);

            $envioMx = (new Query())
                ->select([
                    'count(*) as total_pz',
                    'round(sum(envio.peso_total),2) as total_lb',
                    'round(sum(envio.total),2) as total',
                ])
                ->from('envio')
                ->innerJoin("envio_detalle","envio.id = envio_detalle.envio_id")
                ->andWhere(["envio.tipo_envio" => Envio::TIPO_ENVIO_MEX ]);


            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $envioMx->andWhere(['between','envio.created_at', $date_ini, $date_fin]);
            }

            $response = [
                'trailer' => $envioMx->one(),
            ];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionReporteMx(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            parse_str(Yii::$app->request->get('filters'), $filters);

            $entregado = (new Query())
                ->select([
                    'round(sum(envio.total),2) as total',
                ])
                ->from('envio')
                ->innerJoin("envio_detalle","envio.id = envio_detalle.envio_id")
                ->andWhere(["envio.tipo_envio" => Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["envio.status" => Envio::STATUS_ENTREGADO ]);


            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $entregado->andWhere(['between','envio.created_at', $date_ini, $date_fin]);
            }

            $porPagar = (new Query())
                ->select([

                    'round(sum(envio.total),2) as total',
                ])
                ->from('envio')
                ->innerJoin("envio_detalle","envio.id = envio_detalle.envio_id")
                ->andWhere(["envio.tipo_envio" => Envio::TIPO_ENVIO_MEX ])
                ->andWhere(["envio.status" => Envio::STATUS_AUTORIZADO ]);


            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $porPagar->andWhere(['between','envio.created_at', $date_ini, $date_fin]);
            }


            $response = [
                'entregado' => $entregado->one(),
                'porpagar' => $porPagar->one(),
            ];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionReporteFases(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            parse_str(Yii::$app->request->get('filters'), $filters);
            $reporteFasesArray = [];
            if(isset($filters['viaje_id']) && $filters['viaje_id'] ){
                $viaje = Viaje::findOne($filters['viaje_id']);
                if (isset($viaje->id)) {
                     $queryEnvio = (new Query())
                        ->select([
                            "envio.id",
                        ])
                        ->from('viaje')
                        ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                        ->innerJoin('envio_detalle','viaje_detalle.paquete_id = envio_detalle.id')
                        ->innerJoin('envio','envio_detalle.envio_id = envio.id')
                        ->andWhere(["viaje.id" => $viaje->id])
                        ->groupBy("envio.id");

                    $queryPaquetes = (new Query())
                        ->select([
                            "movimiento_paquete.tracked",
                            "movimiento_paquete.paquete_id",
                            "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1 ) as tipo_movimiento",
                            "envio_detalle.envio_id",
                            "viaje_detalle.viaje_id",
                            "envio.folio",
                            "envio.total",
                            "(SELECT cobro_rembolso_envio.created_at FROM cobro_rembolso_envio WHERE cobro_rembolso_envio.envio_id = envio.id order by cobro_rembolso_envio.created_at desc limit 1) AS fecha_pago",
                            "(SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id) AS total_pagado",
                        ])
                        ->from('movimiento_paquete')
                        ->innerJoin('envio_detalle','movimiento_paquete.paquete_id = envio_detalle.id')
                        ->leftJoin('viaje_detalle','envio_detalle.id = viaje_detalle.paquete_id and viaje_detalle.tracked = movimiento_paquete.tracked')
                        ->innerJoin('envio','envio_detalle.envio_id = envio.id')

                        ->andWhere(["and",
                            ["between","envio.id",$viaje->envio_ini_id,$viaje->envio_fin_id],
                            [ "=","envio.tipo_envio" , Envio::TIPO_ENVIO_TIERRA ],
                            [ "=","envio.status" , Envio::STATUS_HABILITADO ],
                            [ "=","envio_detalle.status" , EnvioDetalle::STATUS_HABILITADO ],
                            [ "<>", "movimiento_paquete.tipo_envio", 30 ], //Modificación para permitir que muestre LAX en reportes TIE
                            [ "=", "movimiento_paquete.tipo", 10 ],
                            // ===================================== Filtra paquetes que ya cuenta con pago realizado ==================================================
                            [ "<>", "(select mv.tipo_movimiento  from movimiento_paquete mv where mv.tracked = movimiento_paquete.tracked order by mv.id desc limit 1)", 1 ],

                            [ ">", "if((SELECT SUM(cobro_rembolso_envio.cantidad) from cobro_rembolso_envio where cobro_rembolso_envio.envio_id = envio.id),10,0)", 0 ],
                        ])
                        ->andWhere(['or',
                            ["=","viaje_detalle.viaje_id" , $viaje->id ],
                            ['IS', 'viaje_detalle.id', new \yii\db\Expression('null')],
                        ])
                        ->orderBy("fecha_pago, movimiento_paquete.tracked asc")
                        ->limit(Viaje::CARGA_MAXIMA_TIE)
                        ->groupBy("movimiento_paquete.tracked");


                $queryTrailer = (new Query())
                        ->select([
                            "movimiento_paquete.tracked",
                            "movimiento_paquete.paquete_id",
                        ])
                        ->from('movimiento_paquete')
                        ->andWhere(["=","movimiento_paquete.viaje_id" , $viaje->id ]);
                        //->groupBy("movimiento_paquete.tracked");

                        //echo ($queryPaquetes->createCommand()->rawSql) . '<br/><br/>';

                    $queryTrailerRepeat = (new Query())
                        ->select([
                            "viaje_detalle.tracked",
                        ])
                        ->from('viaje_detalle')
                        ->andWhere(["=","viaje_detalle.viaje_id" , $viaje->id ])
                        ->groupBy("viaje_detalle.tracked")
                        ->having("count(*) > 1");

                    $transcurso = (new Query())
                        ->select([
                            'viaje_detalle.tracked'
                        ])
                        ->from('viaje')
                        ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                        ->andWhere(["(select mv.tipo_movimiento
                               from movimiento_paquete mv
                                where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                            )" => MovimientoPaquete::LAX_TIER_TRANSCURSO ]);

                    if (isset($filters['viaje_id']) && $filters['viaje_id'])
                        $transcurso->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);

                    //$transcurso->groupBy('viaje.id');

                    $bodega = (new Query())
                        ->select([
                            'viaje_detalle.tracked',
                        ])
                        ->from('viaje')
                        ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                        ->andWhere(["(select mv.tipo_movimiento
                               from movimiento_paquete mv
                                where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                            )" => MovimientoPaquete::LAX_TIER_BODEGA ])
                        ->groupBy('viaje_detalle.tracked');

                    if (isset($filters['viaje_id']) && $filters['viaje_id'])
                        $bodega->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);



                    $bodegaDescarga = (new Query())
                        ->select([
                            'movimiento_paquete.tracked',
                            'movimiento_paquete.tipo_movimiento'
                        ])
                        ->from('viaje_detalle')
                        ->innerJoin('viaje','viaje_detalle.viaje_id= viaje.id')
                        ->innerJoin('movimiento_paquete','movimiento_paquete.paquete_id = viaje_detalle.paquete_id and movimiento_paquete.tracked = viaje_detalle.tracked and  movimiento_paquete.tipo_movimiento = 30')
                        ->groupBy('movimiento_paquete.tracked');

                    if (isset($filters['viaje_id']) && $filters['viaje_id'])
                        $bodegaDescarga->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);


                    $reparto = (new Query())
                        ->select([
                            'count(*) AS pz_venta',
                            '(select mv.tipo_movimiento
                               from movimiento_paquete mv
                                where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                            ) as tipo_movimiento_top'
                        ])
                        ->from('viaje')
                        ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                        ->andWhere(["(select mv.tipo_movimiento
                               from movimiento_paquete mv
                                where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                            )" => MovimientoPaquete::LAX_TIER_REPARTO ])
                        ->groupBy('viaje_detalle.tracked');

                    if (isset($filters['viaje_id']) && $filters['viaje_id'])
                        $reparto->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);

                    $reparto->groupBy('viaje.id');

                    $entregado = (new Query())
                        ->select([
                            'count(*) AS pz_venta',
                            '(select mv.tipo_movimiento
                               from movimiento_paquete mv
                                where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                            ) as tipo_movimiento_top'
                        ])
                        ->from('viaje')
                        ->innerJoin('viaje_detalle','viaje.id = viaje_detalle.viaje_id')
                        ->andWhere(["(select mv.tipo_movimiento
                               from movimiento_paquete mv
                                where mv.tracked = viaje_detalle.tracked order by id desc limit 1
                            )" => MovimientoPaquete::LAX_TIER_ENTREGADO ])
                        ->groupBy('movimiento_paquete.tracked');

                    if (isset($filters['viaje_id']) && $filters['viaje_id'])
                        $entregado->andWhere(['viaje_detalle.viaje_id' =>  $filters['viaje_id']]);

                    $entregado->groupBy('viaje.id');

                    $item_cargado_trailer       = 0;
                    $item_no_cargado_array      = [];
                    $item_otro_trailer_array    = [];
                    foreach ($queryPaquetes->all() as $key => $paquete) {
                        if ($paquete["viaje_id"] == $viaje->id )
                            $item_cargado_trailer   = $item_cargado_trailer + 1;
                        else{
                            if (!isset($paquete["viaje_id"]))
                                array_push($item_no_cargado_array, $paquete);
                            else
                                array_push($item_otro_trailer_array, $paquete);
                        }
                    }

                    $queryTrailerArray   = $queryTrailer->all();
                    $bodegaDescargaArray = $bodegaDescarga->all();
                    $diferencia_paquete  = [];
                    foreach ($queryTrailerArray as $key => $paquete_trailer) {
                        $is_add =  true;
                        foreach ($bodegaDescargaArray as $key => $paquete_descarga) {
                            if ($paquete_trailer["tracked"] === $paquete_descarga["tracked"]) {
                                $is_add = false;
                            }
                        }

                        if ($is_add)
                            array_push($diferencia_paquete, $paquete_trailer);
                    }

                    $reporteFasesArray = [
                        "viaje_envio_ini" => Envio::findOne($viaje->envio_ini_id)->folio,
                        "viaje_envio_fin" => Envio::findOne($viaje->envio_fin_id)->folio,
                        "ties" => $queryEnvio->all() ? count($queryEnvio->all()) : 0,
                        "paquete"   => count($queryPaquetes->all()),
                        "paquete_este_trailer" => intval(count($queryPaquetes->all())) - intval(count($item_otro_trailer_array)),
                        "paquetes_otro_trailer" => $item_otro_trailer_array,
                        "propuestos" => intval(count($queryPaquetes->all())) - intval(count($item_otro_trailer_array)),
                        "carga_trailer" => $item_cargado_trailer,
                        "carga_trailer_all" => count($queryTrailerArray),
                        "diferencia_paquete" => $diferencia_paquete,
                        "paquete_repeat" => $queryTrailerRepeat->all() ? $queryTrailerRepeat->all() : [],
                        "no_cargados_trailer" => $item_no_cargado_array,

                        //'transcurso'        => $transcurso->one(),
                        'bodegaDescarga'    => $bodegaDescargaArray ? $bodegaDescargaArray : [],
                        'bodega'            => $bodega->all(),
                        'reparto'           => $reparto->one(),
                        'entregado'         => $entregado->one(),
                    ];
                }
                //echo ($queryPaquetes->createCommand()->rawSql) . '<br/><br/>';
            }

            $response = [
                'reporteFasesArray' => $reporteFasesArray,
            ];

            return $response;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }



    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//

    public function actionPaquetesCheckJsonBtt(){
        return ViewEnvio::getViajeCheckList(Yii::$app->request->get());
    }

    public function actionDocumentadoJsonBtt(){
        return ViewEnvio::getPaqueteDocumentado(Yii::$app->request->get());
    }

    public function actionBodegaJsonBtt(){
        return ViewEnvio::getPaqueteBodega(Yii::$app->request->get());
    }

    public function actionDescargaTrailerJsonBtt(){
        return ViewEnvio::getDescargaTrailer(Yii::$app->request->get());
    }

    public function actionPaquetesAdeudoJsonBtt(){
        return ViewEnvio::getDescargaTrailer(Yii::$app->request->get());
    }

    public function actionEnvioMexJsonBtt(){
        return ViewEnvio::getEnvioMexCobros(Yii::$app->request->get());
    }
}
