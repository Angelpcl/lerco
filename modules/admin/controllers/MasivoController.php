<?php
namespace app\modules\admin\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use app\models\envio\EnvioDetalle;
use app\models\envio\Envio;
use app\models\viaje\Viaje;
use app\models\viaje\ViajeDetalle;
use app\models\movimiento\MovimientoPaquete;

/**
 * HistorialDeAccesoController implements the CRUD actions for EsysAcceso model.
 */
class MasivoController extends \app\controllers\AppController
{
    /**
     * Lists all EsysAcceso models.
     * @return mixed
     */
    public function actionChange()
    {


        if (Yii::$app->request->post() && isset($_FILES['csv_file']) ) {

            $errors_array   = [];
            $trackend_array = [];
            $post           = Yii::$app->request->post();
            if (($gestor = fopen($_FILES["csv_file"]["tmp_name"], 'r')) !== FALSE) {
                while (!feof($gestor)) {
                    $row = fgetcsv($gestor, 1000, ',');
                    if (isset($row[0])) {
                        $trackend_array[] = $row[0];
                    }

                }
                fclose($gestor);

                foreach ($trackend_array as $key => $tracked) {
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



                        if ($EnvioDetalle->envio->status != Envio::STATUS_CANCELADO) {
                            if ($EnvioDetalle->status != EnvioDetalle::STATUS_CANCELADO ) {
                                if (isset($MovimientoPaquete->tipo_movimiento) && $MovimientoPaquete->tipo_movimiento) {

                                    if (!$MovimientoPaquete->validaMovimientoTracked($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
                                        if ($MovimientoPaquete->validaMovimientoAdmin($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_movimiento)) {
                                            if ($MovimientoPaquete->validaMovimientoTranscusoApertura($MovimientoPaquete->tracked,$MovimientoPaquete->tipo_envio)) {

                                                $error = [
                                                    "code" => 10,
                                                    "tracked" => $tracked,
                                                    "message" => "Aviso, no se realizo el movimiento porque se ingreso en un Viaje y no a concluido ó aperturado para realizar movimientos",
                                                ];
                                                array_push($errors_array, $error);
                                                $is_validation = false;
                                            }
                                            switch ($MovimientoPaquete->tipo_envio) {
                                                case  Envio::TIPO_ENVIO_TIERRA:
                                                    if ( $MovimientoPaquete->tipo_movimiento == MovimientoPaquete::LAX_TIER_TRANSCURSO){
                                                        $MovimientoPaquete->viaje_id         = isset($post['viaje_tierra_id']) ? $post['viaje_tierra_id'] : null;
                                                        if(!$MovimientoPaquete->viaje_id ){
                                                            $error = [
                                                                "code" => 10,
                                                                "tracked" => $tracked,
                                                                "message" => 'Hubo un error al realizar el movimiento, debe seleccionar una viaje TIERRA',
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
                                                        "code" => 202,
                                                        "tracked" => $tracked,
                                                        "message" => 'Se realizo correctamente el movimiento del paquete',
                                                    ];
                                                    array_push($errors_array, $error);
                                                }else{
                                                    $error = [
                                                        "code" => 10,
                                                        "tracked" => $tracked,
                                                        "message" => "Warning, No se realizo el movimiento ya que el paquete no cumple con las configuraciones del sistema",
                                                    ];
                                                    array_push($errors_array, $error);
                                                }
                                            }
                                        }else{

                                            $error = [
                                                "code" => 10,
                                                "tracked" => $tracked,
                                                "message" => "Warning, No se realizo el movimiento por que no cumple con las politicas de la empresa, contacta al administrador",
                                            ];

                                            array_push($errors_array, $error);
                                        }
                                    }else{
                                        $error = [
                                            "code" => 10,
                                            "tracked" => $tracked,
                                            "message" => "Aviso, No se realizo el movimiento ya que el paquete se encuentra en ese movimiento",
                                        ];
                                        array_push($errors_array, $error);
                                    }
                                }else{
                                    $error = [
                                        "code" => 10,
                                        "tracked" => $tracked,
                                        "message" => "Hubo un error al realizar el movimiento, seleccione correctamente el movimiento",
                                    ];
                                    array_push($errors_array, $error);
                                }
                            }else{
                                $error = [
                                    "code" => 10,
                                    "tracked" => $tracked,
                                    "message" => 'Error el paquete a sido cancelado,  intente nuevamente.',
                                ];
                                array_push($errors_array, $error);
                            }
                        }else{
                            $error = [
                                "code" => 10,
                                "tracked" => $tracked,
                                "message" => 'Error el envio al que pertenece a sido cancelado, intente nuevamente.',
                            ];
                            array_push($errors_array, $error);
                        }

                    }else{
                        $error = [
                            "code" => 10,
                            "tracked" => $tracked,
                            "message" => "Hubo un error al buscar el trackend, intente nuevamente",
                        ];
                        array_push($errors_array, $error);
                    }
                }
            }

            return $this->render('change',[ "errors" => $errors_array ]);
        }

        return $this->render('change',[ "errors" => false ]);
    }


//------------------------------------------------------------------------------------------------//
// BootstrapTable list
//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionHistorialDeAccesosJsonBtt()
    {
        //return ViewAcceso::getJsonBtt(Yii::$app->request->get());
    }

}
