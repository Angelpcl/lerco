<?php
namespace app\modules\logistica\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use kartik\mpdf\Pdf;
use app\models\viaje\Viaje;
use app\models\viaje\ViewViaje;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;
use app\models\viaje\ViajeDetalle;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\movimiento\MovimientoPaquete;
use app\models\Esys;

/**
 * Default controller for the `clientes` module
 */
class ViajeLaxController extends \app\controllers\AppController
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
            $model->tipo_servicio  = Envio::TIPO_ENVIO_LAX;
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
                $MovimientoPaquete->tipo_envio      = isset($ViajeDetalle->envioDetalleLaxTierra->envio->tipo_envio) ? $ViajeDetalle->envioDetalleLaxTierra->envio->tipo_envio: null;
                $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_SUCURSAL;
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

/*    public function actionReporteViajeAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());



        $sheet=0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(40);

        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(40);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFill()->getStartColor()->setRGB('000000');
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:P1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->getStyle('Q1:Z1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('Q1:Z1')->getFill()->getStartColor()->setRGB('000000');
        $objPHPExcel->getActiveSheet()->getStyle('Q1:Z1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("Q1:Z1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('Q1:Z1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setTitle('Reporte de viaje')

        ->setCellValue('A1', 'Folio de venta')
        ->setCellValue('B1', 'Cliente emisor')
        ->setCellValue('C1', 'Telefono emisor')
        ->setCellValue('D1', 'Reenvio')
        ->setCellValue('E1', 'Dirección')
        ->setCellValue('F1', 'Vendedor')
        ->setCellValue('G1', 'Total piezas')
        ->setCellValue('H1', 'Paquetes en viaje')

        ->setCellValue('Q1', 'Peso total')
        ->setCellValue('R1', 'Costo lb')
        ->setCellValue('S1', 'Valor del paquete total')
        ->setCellValue('T1', 'Costo seguro total')
        ->setCellValue('U1', 'Costo de reenvio')
        ->setCellValue('V1', 'Subtotal')
        ->setCellValue('W1', 'Impuesto total')
        ->setCellValue('X1', 'Total abonado')
        ->setCellValue('Y1', 'Total')
        ->setCellValue('Z1', 'Fecha');

        $objPHPExcel->getActiveSheet()->mergeCells('H1:P1');


        $ViewViajeEnvio = ViewViaje::getReporteViajeEnvio(Yii::$app->request->get());



        $row=2;

        foreach ($ViewViajeEnvio as $item) {
            $model = Envio::findOne($item["envio_id"]);
            $ViewViajePaquete = ViewViaje::getReporteViajeAjax($item);

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$ViewViajePaquete["folio"]);

            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);

            $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->getFill()->getStartColor()->setRGB('727576');
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


            if ($ViewViajePaquete["estado"] || $ViewViajePaquete["municipio"] || $ViewViajePaquete["direccion"] || $ViewViajePaquete["referencia"]) {
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, "Estado: ".$ViewViajePaquete["estado"] .", Municipio  ". $ViewViajePaquete["municipio"] .", Dirección  ". $ViewViajePaquete["direccion"] ." / Referencia: ". $ViewViajePaquete["referencia"]);
            }

            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$ViewViajePaquete["vendedor"]);


            $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->getFill()->getStartColor()->setRGB('727576');
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
                if ($e_detalle->status !=  EnvioDetalle::STATUS_CANCELADO)
                    $piezasTotal =  $piezasTotal + $e_detalle->cantidad;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$piezasTotal);


            $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->getFill()->getStartColor()->setRGB('727576');
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
            $objPHPExcel->getActiveSheet()->getStyle('Q'. $row)->getFill()->getStartColor()->setRGB('727576');
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
            $objPHPExcel->getActiveSheet()->getStyle('R'. $row)->getFill()->getStartColor()->setRGB('727576');
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
            $objPHPExcel->getActiveSheet()->getStyle('T'. $row)->getFill()->getStartColor()->setRGB('727576');
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
            $objPHPExcel->getActiveSheet()->getStyle('U'. $row)->getFill()->getStartColor()->setRGB('727576');
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
            $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->getFill()->getStartColor()->setRGB('727576');
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
            $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->getFill()->getStartColor()->setRGB('727576');
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
            $objPHPExcel->getActiveSheet()->getStyle('Y'. $row)->getFill()->getStartColor()->setRGB('727576');
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
                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':P'.$row)->getFill()->getStartColor()->setARGB('000000');
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
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Viaje_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');

    }
*/

    public function actionReporteViajeAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());

        $sheet=0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(40);

        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(40);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFill()->getStartColor()->setRGB('000000');
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:P1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->getStyle('Q1:AF1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('Q1:AF1')->getFill()->getStartColor()->setRGB('000000');
        $objPHPExcel->getActiveSheet()->getStyle('Q1:AF1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("Q1:AF1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('Q1:AF1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setTitle('Reporte de viaje')

        ->setCellValue('A1', 'Folio de venta')
        ->setCellValue('B1', 'Cliente emisor')
        ->setCellValue('C1', 'Telefono emisor')
        ->setCellValue('D1', 'Reenvio')
        //->setCellValue('E1', 'Dirección')
        ->setCellValue('F1', 'Vendedor')
        ->setCellValue('G1', 'Total piezas')
        ->setCellValue('H1', 'Paquetes en viaje')

        ->setCellValue('V1', 'Peso total (PAQUETES)')
        ->setCellValue('W1', 'Peso total')
        ->setCellValue('X1', 'Costo lb')
        ->setCellValue('Y1', 'Valor del paquete total')
        ->setCellValue('Z1', 'Costo seguro total')
        ->setCellValue('AA1', 'Costo de reenvio')
        ->setCellValue('AB1', 'Subtotal')
        ->setCellValue('AC1', 'Impuesto total')
        ->setCellValue('AD1', 'Total abonado')
        ->setCellValue('AE1', 'Total')
        ->setCellValue('AF1', 'Fecha');

        $objPHPExcel->getActiveSheet()->mergeCells('H1:U1');


        $ViewViajeEnvio = ViewViaje::getReporteViajeEnvio(Yii::$app->request->get());



        $row=2;

        foreach ($ViewViajeEnvio as $item) {
            $model = Envio::findOne($item["envio_id"]);
            $ViewViajePaquete = ViewViaje::getReporteViajeAjax($item);

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$ViewViajePaquete["folio"]);

            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);

            $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->getFill()->getStartColor()->setRGB('727576');
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

            if ($ViewViajePaquete["estado"] || $ViewViajePaquete["municipio"] || $ViewViajePaquete["direccion"] || $ViewViajePaquete["referencia"]) {
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, "Estado: ".$ViewViajePaquete["estado"] .", Municipio  ". $ViewViajePaquete["municipio"] .", Dirección  ". $ViewViajePaquete["direccion"] ." / Referencia: ". $ViewViajePaquete["referencia"]);
            }



            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$ViewViajePaquete["vendedor"]);


            $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->getFill()->getStartColor()->setRGB('727576');
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
                if ($e_detalle->status !=  EnvioDetalle::STATUS_CANCELADO)
                    $piezasTotal =  $piezasTotal + $e_detalle->cantidad;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$piezasTotal);


            $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('V'.$row,round($ViewViajePaquete["peso_total_paquete"],2));
            $objPHPExcel->getActiveSheet()->setCellValue('W'.$row,$ViewViajePaquete["peso_total"]);

            $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('X'.$row,$ViewViajePaquete["precio_libra_actual"]);
            $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->applyFromArray(array(
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


            $objPHPExcel->getActiveSheet()->setCellValue('Y'.$row,$valoracionTotal);
            $objPHPExcel->getActiveSheet()->setCellValue('Z'.$row,$ViewViajePaquete["seguro_total"]);

            $objPHPExcel->getActiveSheet()->getStyle('Z'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('Z'. $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('Z'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('AA'.$row,$ViewViajePaquete["costo_reenvio"] ? $ViewViajePaquete["costo_reenvio"] : 0);

            $objPHPExcel->getActiveSheet()->getStyle('AA'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('AA'. $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('AA'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('AB'.$row,$ViewViajePaquete["subtotal"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AC'.$row,$ViewViajePaquete["impuesto"]);

            $objPHPExcel->getActiveSheet()->getStyle('AC'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('AC'. $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('AC'. $row)->applyFromArray(array(
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

            $objPHPExcel->getActiveSheet()->setCellValue('AD'.$row,$cobroTotal);

            $objPHPExcel->getActiveSheet()->getStyle('AD'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('AD'. $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('AD'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('AE'.$row,$ViewViajePaquete["total"]);

            $objPHPExcel->getActiveSheet()->getStyle('AE'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('AE'. $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('AE'. $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));


            $objPHPExcel->getActiveSheet()->setCellValue('AF'.$row, Esys::fecha_en_texto($ViewViajePaquete["created_at"],true));

            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':AF'.$row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));

            $ViewViajePaqueteDetalle = ViewViaje::getReporteViajePaqueteDetalle($item,Yii::$app->request->get());

            $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,"Tracked");
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,"S. receptor");
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,"C. receptor");
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,"C. telefono");
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$row,"Ruta");
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$row,"V. D.");
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$row,"Elementos");
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$row,"Peso");
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$row,"Observacion");


            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row,"Reenvio");
            $objPHPExcel->getActiveSheet()->setCellValue('R'.$row,"Estado");
            $objPHPExcel->getActiveSheet()->setCellValue('S'.$row,"Municipio");
            $objPHPExcel->getActiveSheet()->setCellValue('T'.$row,"Direccion");
            $objPHPExcel->getActiveSheet()->setCellValue('U'.$row,"Referencias");


           $row++;

            foreach ($ViewViajePaqueteDetalle as $key => $paquete) {

                $paqueteDireccion = ViewViaje::getReporteViajePaqueteDetalleDireccion($paquete);

                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':U'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':U'.$row)->getFill()->getStartColor()->setARGB('000000');
                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':U'.$row)->applyFromArray(array(
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

                if (isset($paqueteDireccion["id"])) {
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row,"SI");
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$row,$paqueteDireccion["estado"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$row,$paqueteDireccion["municipio"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$row,$paqueteDireccion["direccion"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$row,$paqueteDireccion["referencia"]);
                }else
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row,"NO");



                $row++;
            }

            $row++;
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Viaje_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');

    }

    public function actionImprimirReetiquetasPdf($viaje_id)
    {
        $model = ViewViaje::getReporteReimpresion(Yii::$app->request->get());


        $content = "";

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array(100,150),//Pdf::FORMAT_LETTER,
            // portrait orientation
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
             // set mPDF properties on the fly
            'options' => ['title' => 'Reetiquetas de envio'],
             // call mPDF methods on the fly
        ]);

        $pdf->marginLeft = 3;
        $pdf->marginRight = 3;

        $pdf->setApi();
        $pdf_api = $pdf->getApi();


        $count_show = 0;
        foreach ($model as $key => $item) {

            $content = $this->renderPartial('reetiqueta', ["model" => $item]);
            $pdf_api->WriteHTML($content);
           // if (count($model->getSucursalReparto()) < ($key + 1) )

            $count_show = $count_show + 1;

            if (count($model) > $count_show)
                $pdf_api->AddPage();
        }

        // return the pdf output as per the destination setting
        return $pdf->render();
    }

    public function actionReporteViajeJulioAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());

        $sheet=0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(40);

        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(40);




        $objPHPExcel->getActiveSheet()->setTitle('Reporte Julio');


        $ViewViajeEnvio = ViewViaje::getReporteViajeEnvio(Yii::$app->request->get());

        $row = 1;

        foreach ($ViewViajeEnvio as $item) {
            $model = Envio::findOne($item["envio_id"]);
            $ViewViajePaquete = ViewViaje::getReporteViajeAjax($item);
            //$ViewViajePaqueteDetalle = ViewViaje::getReporteViajePaqueteDetalle($item);
            $ViewViajePaqueteDetalle = ViewViaje::getReporteViajePaqueteDetalle($item,Yii::$app->request->get());

            foreach ($ViewViajePaqueteDetalle as $key => $paquete) {

                $paqueteDireccion = ViewViaje::getReporteViajePaqueteDetalleDireccion($paquete);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$ViewViajePaquete["folio"]);

                $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);

                $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->getFill()->getStartColor()->setRGB('727576');
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

                if ($ViewViajePaquete["estado"] || $ViewViajePaquete["municipio"] || $ViewViajePaquete["direccion"] || $ViewViajePaquete["referencia"]) {
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, "Estado: ".$ViewViajePaquete["estado"] .", Municipio  ". $ViewViajePaquete["municipio"] .", Dirección  ". $ViewViajePaquete["direccion"] ." / Referencia: ". $ViewViajePaquete["referencia"]);
                }



                $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$ViewViajePaquete["vendedor"]);


                $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('B'. $row.':F'. $row)->getFill()->getStartColor()->setRGB('727576');
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
                $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));
                $objPHPExcel->getActiveSheet()->setCellValue('V'.$row,round($ViewViajePaquete["peso_total_paquete"],2));
                $objPHPExcel->getActiveSheet()->setCellValue('W'.$row,$ViewViajePaquete["peso_total"]);

                $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('W'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('X'.$row,$ViewViajePaquete["precio_libra_actual"]);
                $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('X'. $row)->applyFromArray(array(
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


                $objPHPExcel->getActiveSheet()->setCellValue('Y'.$row,$valoracionTotal);
                $objPHPExcel->getActiveSheet()->setCellValue('Z'.$row,$ViewViajePaquete["seguro_total"]);

                $objPHPExcel->getActiveSheet()->getStyle('Z'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('Z'. $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('Z'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('AA'.$row,$ViewViajePaquete["costo_reenvio"] ? $ViewViajePaquete["costo_reenvio"] : 0);

                $objPHPExcel->getActiveSheet()->getStyle('AA'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('AA'. $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('AA'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('AB'.$row,$ViewViajePaquete["subtotal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('AC'.$row,$ViewViajePaquete["impuesto"]);

                $objPHPExcel->getActiveSheet()->getStyle('AC'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('AC'. $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('AC'. $row)->applyFromArray(array(
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

                $objPHPExcel->getActiveSheet()->setCellValue('AD'.$row,$cobroTotal);

                $objPHPExcel->getActiveSheet()->getStyle('AD'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('AD'. $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('AD'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('AE'.$row,$ViewViajePaquete["total"]);

                $objPHPExcel->getActiveSheet()->getStyle('AE'. $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('AE'. $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('AE'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));


                $objPHPExcel->getActiveSheet()->setCellValue('AF'.$row, Esys::fecha_en_texto($ViewViajePaquete["created_at"],true));

                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':Z'.$row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                ));


                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':U'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':U'.$row)->getFill()->getStartColor()->setARGB('000000');
                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':U'.$row)->applyFromArray(array(
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

                 if (isset($paqueteDireccion["id"])) {
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row,"SI");
                    $objPHPExcel->getActiveSheet()->setCellValue('R'.$row,$paqueteDireccion["estado"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$row,$paqueteDireccion["municipio"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$row,$paqueteDireccion["direccion"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$row,$paqueteDireccion["referencia"]);
                }else
                    $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row,"NO");


                $row++;
            }

            $row++;
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Julio_".date("d-m-Y-His").".xls";

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
    public function actionViajesLaxJsonBtt(){
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
