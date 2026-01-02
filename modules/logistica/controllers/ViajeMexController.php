<?php
namespace app\modules\logistica\controllers;

use Yii;
use yii\web\Controller;
use app\models\viaje\Viaje;
use app\models\viaje\ViewViaje;
use app\models\envio\Envio;
use app\models\viaje\ViajeDetalle;
use app\models\movimiento\MovimientoPaquete;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\caja\CajaMex;
use app\models\envio\EnvioDetalle;
use app\models\Esys;
/**
 * Default controller for the `clientes` module
 */
class ViajeMexController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('rutaCreate'),
            'update' => Yii::$app->user->can('rutaUpdate'),
            'delete' => Yii::$app->user->can('rutaDelete'),
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index',[
            "can" => $this->can]);
    }

     /**
     * Displays a single EsysDivisa model.
     * @param integer $name
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }
    public function actionSetStatusViaje($id, $status)
    {
        $model = $this->findModel($id);
        switch ($model->status) {
            case Viaje::STATUS_ACTIVE:
                if ($status == Viaje::STATUS_CERRADO)
                    $model->status = $status;

                if ($status == Viaje::STATUS_CANCEL)
                    $model->status = $status;

                if ($model->save())
                    Yii::$app->session->setFlash('success', "Se modificado correctamente el  Viaje #" . $id);
            break;
            case Viaje::STATUS_CERRADO:
                if ($status == Viaje::STATUS_TERMINADO)
                    $model->status = $status;

                if ($status == Viaje::STATUS_ACTIVE)
                    $model->status = $status;

                if ($model->save())
                    Yii::$app->session->setFlash('success', "Se modificado correctamente el  Viaje #" . $id);
            break;
            case Viaje::STATUS_TERMINADO:

                Yii::$app->session->setFlash('danger', "Se pueden realizar cambios al Viaje  #" . $id);
            break;
            case Viaje::STATUS_CANCEL:

                Yii::$app->session->setFlash('danger', "Se pueden realizar cambios al Viaje #" . $id);
            break;
        }

        return $this->redirect(['view','id' => $id]);
    }

    /**
     * Creates a new Sucursal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Viaje();

        if ($model->load(Yii::$app->request->post())) {
            $model->tipo_servicio  = Envio::TIPO_ENVIO_MEX;
            if ($model->save()) {
                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post())){
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionProductoRemove($viaje_id,$paquete_id, $tipo)
    {
        // Eliminamos el usuario
        $ViajeDetalle = ViajeDetalle::find()->where(['and', ["viaje_id" => $viaje_id,"paquete_id" => $paquete_id , "tipo" => $tipo]])->one();
        try{
            // Eliminamos el usuario
            if($ViajeDetalle->delete()){

                $MovimientoPaquete = new MovimientoPaquete();
                $MovimientoPaquete->paquete_id      = $ViajeDetalle->paquete_id;
                $MovimientoPaquete->tracked         = $ViajeDetalle->tracked;
                if ($MovimientoPaquete->tipo == MovimientoPaquete::TIPO_PAQUETE)
                    $MovimientoPaquete->tipo_envio      = isset($ViajeDetalle->envioDetalleLaxTierra->envio->tipo_envio) ? $ViajeDetalle->envioDetalleLaxTierra->envio->tipo_envio: null;
                else
                    $MovimientoPaquete->tipo_envio      = Envio::TIPO_ENVIO_MEX;

                $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::MEX_BODEGA;
                $MovimientoPaquete->tipo            = $ViajeDetalle->tipo;
                $MovimientoPaquete->save();

                Yii::$app->session->setFlash('success', "Se ha removido correctamente el paquete #" . $paquete_id);
            }

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden remover el paquete.');
                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->redirect(['view','id' => $viaje_id]);
    }


    /**
     * Deletes an existing Sucursal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param  integer $id The user id.
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try{
            // Eliminamos el usuario
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la ruta #" . $id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación de la ruta.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->redirect(['index']);
    }

    public function actionDeleteSucursal($ruta_id,$sucursal_id)
    {
        $model = RutaSucursal::find()->andWhere(['and',["ruta_id" => $ruta_id], ["sucursal_id" => $sucursal_id]])->one();

        try{
            // Eliminamos el usuario
            $model->delete();

            Yii::$app->session->setFlash('success', "Se ha removido correctamente la sucursal #" . $sucursal_id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden remover la sucursal.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->redirect(['view','id' => $ruta_id]);
    }

    public function actionReporteViajeAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());

        $sheet = 0;

        $objPHPExcel->setActiveSheetIndex($sheet);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

        $objPHPExcel->getActiveSheet()->setTitle('Reporte de viaje');

        $row = 1;

        foreach (ViewViaje::getReporteViajeMexEnvio(Yii::$app->request->get()) as $key => $viajeMex) {
            if ( $viajeMex["tipo"] == MovimientoPaquete::TIPO_CAJA ) {

                $CajaMex = CajaMex::findOne($viajeMex["caja_id"]);

                $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':J'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':J'.$row)->getFill()->getStartColor()->setRGB('000000');

                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':J'.$row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.$row, 'Caja #' . $CajaMex->nombre ."  [". $CajaMex->folio ."]");

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':J'.$row);
                $row ++;


                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,"Tracked");
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,"S. receptor");
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,"C. receptor");
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,"C. telefono");
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,"Producto");
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,"V. D.");
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,"Elementos");
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,"Peso");
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,"Observacion");
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,"Estatus");
               $row++;

                foreach ($CajaMex->cajaDetalleMex as $key => $caja_detalle) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$caja_detalle->tracked);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$caja_detalle->envioDetalle->sucursalReceptor->nombre);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$caja_detalle->envioDetalle->clienteReceptor->nombreCompleto);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$caja_detalle->envioDetalle->clienteReceptor->telefono ." / ".$caja_detalle->envioDetalle->clienteReceptor->telefono_movil);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$caja_detalle->envioDetalle->producto->nombre);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ),
                    ));
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$caja_detalle->envioDetalle->valor_declarado);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$caja_detalle->envioDetalle->cantidad_piezas);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$caja_detalle->envioDetalle->peso);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$caja_detalle->envioDetalle->observaciones);
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,MovimientoPaquete::$tipoMexList[EnvioDetalle::getMovimientoTop($caja_detalle->tracked)]);

                    $row++;
                }
                $row++;
            }
        }

        /*

        $ViewViajeEnvio = ViewViaje::getReporteViajeEnvio(Yii::$app->request->get());
        $row=2;

        foreach ($ViewViajeEnvio as $item) {
            $model = Envio::findOne($item["envio_id"]);
            $ViewViajePaquete = ViewViaje::getReporteViajeAjax($item);

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$ViewViajePaquete["folio"]);

            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);

            $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->getFill()->getStartColor()->setRGB('800000');
            $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$ViewViajePaquete["cliente_emisor"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$ViewViajePaquete["telefono_emisor"] ."/". $ViewViajePaquete["telefono_movil"] );
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$ViewViajePaquete["is_reenvio"] ? "SI" : "NO");

            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$ViewViajePaquete["vendedor"]);


            $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->getFill()->getStartColor()->setRGB('FB9F23');
            $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $piezasTotal = 0;
            foreach ($model->envioDetalles as $key => $e_detalle){
                $piezasTotal =  $piezasTotal + $e_detalle->cantidad;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$piezasTotal);


            $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->getFill()->getStartColor()->setRGB('800000');
            $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row,$ViewViajePaquete["peso_total"]);

            $objPHPExcel->getActiveSheet()->getStyle('Q'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('Q'. $row)->getFill()->getStartColor()->setRGB('9BD0F4');
            $objPHPExcel->getActiveSheet()->getStyle('Q'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('R'.$row,$ViewViajePaquete["precio_libra_actual"]);
            $objPHPExcel->getActiveSheet()->getStyle('R'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('R'. $row)->getFill()->getStartColor()->setRGB('9BD0F4');
            $objPHPExcel->getActiveSheet()->getStyle('R'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));


            $valoracionTotal = 0;
            foreach ($model->envioDetalles as $key => $e_detalle){
                $valoracionTotal =  $valoracionTotal + $e_detalle->valor_declarado;
            }


            $objPHPExcel->getActiveSheet()->setCellValue('S'.$row,$valoracionTotal);
            $objPHPExcel->getActiveSheet()->setCellValue('T'.$row,$ViewViajePaquete["seguro_total"]);

            $objPHPExcel->getActiveSheet()->getStyle('T'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('T'. $row)->getFill()->getStartColor()->setRGB('FC4AE7');
            $objPHPExcel->getActiveSheet()->getStyle('T'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('U'.$row,$ViewViajePaquete["costo_reenvio"] ? $ViewViajePaquete["costo_reenvio"] : 0);

            $objPHPExcel->getActiveSheet()->getStyle('U'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('U'. $row)->getFill()->getStartColor()->setRGB('F77F6C');
            $objPHPExcel->getActiveSheet()->getStyle('U'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('V'.$row,$ViewViajePaquete["subtotal"]);
            $objPHPExcel->getActiveSheet()->setCellValue('W'.$row,$ViewViajePaquete["impuesto"]);

            $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->getFill()->getStartColor()->setRGB('8648D0');
            $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));



            $cobroTotal = 0;
            foreach ($model->cobroRembolsoEnvios as $key => $cobro){
                if ($cobro->tipo == CobroRembolsoEnvio::TIPO_DEVOLUCION )
                    $cobroTotal =  $cobroTotal - $cobro->cantidad;
                else
                    $cobroTotal =  $cobroTotal + $cobro->cantidad;
            }

            $objPHPExcel->getActiveSheet()->setCellValue('X'.$row,$cobroTotal);

            $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->getFill()->getStartColor()->setRGB('DA9694');
            $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('Y'.$row,$ViewViajePaquete["total"]);

            $objPHPExcel->getActiveSheet()->getStyle('Y'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('Y'. $row)->getFill()->getStartColor()->setRGB('E26B0A');
            $objPHPExcel->getActiveSheet()->getStyle('Y'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));


            $objPHPExcel->getActiveSheet()->setCellValue('Z'.$row, Esys::fecha_en_texto($ViewViajePaquete["created_at"],true));

            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':Z'.$row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));

            $ViewViajePaqueteDetalle = ViewViaje::getReporteViajePaqueteDetalle($item);

            $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,"Tracked");
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,"S. receptor");
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,"C. receptor");
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,"C. telefono");
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$row,"Ruta");
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$row,"V. D.");
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$row,"Elementos");
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$row,"Peso");
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$row,"Observacion");


           $row++;

            foreach ($ViewViajePaqueteDetalle as $key => $paquete) {


                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':P'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':P'.$row)->getFill()->getStartColor()->setARGB('FF008000');
                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':P'.$row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$paquete["tracked"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$paquete["sucursal_receptor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,$paquete["cliente_receptor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,$paquete["telefono_cliente"] ." / ". $paquete["telefono_movil"]);

                $objPHPExcel->getActiveSheet()->setCellValue('L'.$row,$paquete["nombre_ruta"]);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.$row,$paquete["valor_declarado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$row,$paquete["cantidad_piezas"]);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.$row,$paquete["peso"]);
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$row,$paquete["observaciones"]);

                $row++;
            }

            $row++;
        }*/


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Viaje_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');

    }


    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionViajesMexJsonBtt(){
        return ViewViaje::getJsonBtt(Yii::$app->request->get());
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
    protected function findModel($name, $_model = 'model')
    {
        switch ($_model) {
            case 'model':
                $model = Viaje::findOne($name);
                break;

            case 'view':
                $model = ViewViaje::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
