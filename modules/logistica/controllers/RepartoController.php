<?php
namespace app\modules\logistica\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use kartik\mpdf\Pdf;
use app\models\ruta\Ruta;
use app\models\sucursal\Sucursal;
use app\models\reparto\Reparto;
use app\models\reparto\ViewReparto;
use app\models\reparto\RepartoFila;
use app\models\ruta\FilaRuta;
use app\models\ruta\FilaPaquete;
use app\models\reparto\RepartoRecoleccion;
use app\models\reparto\RepartoDetalle;
use app\models\movimiento\MovimientoPaquete;
use app\models\Esys;

/**
 * Default controller for the `clientes` module
 */
class RepartoController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('repartoCreate'),
            'update' => Yii::$app->user->can('repartoUpdate'),
            'delete' => Yii::$app->user->can('repartoDelete'),
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

    public function actionSetStatusReparto($id, $status)
    {
        $model = $this->findModel($id);
        switch ($model->status) {
            case Reparto::STATUS_ACTIVE:
                if ($status == Reparto::STATUS_CERRADO)
                    $model->status = $status;

                if ($status == Reparto::STATUS_CANCEL)
                    $model->status = $status;

                if ($model->save())
                    Yii::$app->session->setFlash('success', "Se modificado correctamente el  reparto #" . $id);
            break;
            case Reparto::STATUS_CERRADO:
                if ($status == Reparto::STATUS_TERMINADO)
                    $model->status = $status;

                if ($status == Reparto::STATUS_ACTIVE)
                    $model->status = $status;

                if ($model->save())
                    Yii::$app->session->setFlash('success', "Se modificado correctamente el  reparto #" . $id);
            break;
            case Reparto::STATUS_TERMINADO:

                Yii::$app->session->setFlash('danger', "Se pueden realizar cambios al reparto  #" . $id);
            break;
            case Reparto::STATUS_CANCEL:

                Yii::$app->session->setFlash('danger', "Se pueden realizar cambios al reparto #" . $id);
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
        $model = new Reparto();
        if ($model->load(Yii::$app->request->post())) {
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
            return $this->render('update', [
                'model' => $model,
            ]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
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
        try{
            // Eliminamos el usuario
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente el reparto #" . $id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del reparto .');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index']);
    }


    public function actionProductoRemove($reparto_id,$paquete_id)
    {
        // Eliminamos el usuario

        $RepartoDetalle = RepartoDetalle::find()->where(['and', ["reparto_id" => $reparto_id,"paquete_id" => $paquete_id ]])->one();
        $MovimientoPaqueteArray = MovimientoPaquete::find()->where(["tracked" => $RepartoDetalle->tracked])->all();

        try{
            // Eliminamos el usuario
            if($RepartoDetalle->delete()){
                $MovimientoPaquete = new MovimientoPaquete();
                $MovimientoPaquete->paquete_id      = $RepartoDetalle->paquete_id;
                $MovimientoPaquete->tracked         = $RepartoDetalle->tracked;
                $MovimientoPaquete->tipo_envio      = $RepartoDetalle->paquete->envio->tipo_envio;
                $MovimientoPaquete->tipo_movimiento = $MovimientoPaqueteArray[count($MovimientoPaqueteArray) - 2 ]->tipo_movimiento;
                $MovimientoPaquete->tipo            = MovimientoPaquete::TIPO_PAQUETE;
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
        return $this->render('view', [
            'model' => $this->findModel($reparto_id),
            'can'   => $this->can,
        ]);
    }




    public function actionReporteHojaRutaExcel()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();

        $get = Yii::$app->request->get();


        $sheet=0;

        $row = 2;
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);

        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(18);


        $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,"FECHA");
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'. $row );

        $objPHPExcel->getActiveSheet()->mergeCells('D'.$row.':I'. $row );

        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->getRowDimension(($row + 1))->setRowHeight(25);

        $objPHPExcel->getActiveSheet()->getStyle('C'. $row .':I'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('C'. $row.':I'. $row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'borders' => array(
              'allborders' => array(
                  'style' => \PHPExcel_Style_Border::BORDER_THIN
              )
            )

        ));

        $row = $row + 1;
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,"NOMBRE DEL OPERADOR");
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'. $row );

        $objPHPExcel->getActiveSheet()->mergeCells('D'.$row.':I'. $row );

        $objPHPExcel->getActiveSheet()->getStyle('C'. $row .':I'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('C'. $row.':I'. $row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'borders' => array(
              'allborders' => array(
                  'style' => \PHPExcel_Style_Border::BORDER_THIN
              )
            )
        ));

        $row = $row + 1;

        $ViewReparto = ViewReparto::getReporteDetalleRuta($get["reparto_id"]);


         $objPHPExcel->getActiveSheet()->setTitle('HOJA DE RUTA')
        ->setCellValue('A' . $row, 'FOLIO')
        ->setCellValue('B' . $row, 'ESTADO')
        ->setCellValue('C' . $row, 'MUNICIPIO')
        ->setCellValue('D' . $row, 'DIRECCION')
        ->setCellValue('G' . $row, 'PZ')
        ->setCellValue('H' . $row, 'RECEPCION')
        ->setCellValue('I' . $row, 'TELEFONO');

        $objPHPExcel->getActiveSheet()->mergeCells('D'.$row.':F'. $row );

        $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row)->getFont()->setBold(true);
           $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row )->applyFromArray(array(
            'borders' => array(
              'allborders' => array(
                  'style' => \PHPExcel_Style_Border::BORDER_THIN
              )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        ));
        $row = $row + 1;

        $paqueteArray = [];

        foreach ($ViewReparto as $key_paquete => $item_paquete) {
            $is_add     = true;

            foreach ($paqueteArray as $key_temp => $item_paquete_temp) {
                if ($item_paquete_temp["envio_id"] == $item_paquete["envio_id"] && $item_paquete_temp["cliente_receptor_id"] == $item_paquete["cliente_receptor_id"]) {
                    array_push($paqueteArray[$key_temp]["folios"], $item_paquete['tracked']);
                    $is_add = false;
                }
            }

            if ($is_add) {
                array_push($paqueteArray,[
                    "folios"    => [ $item_paquete['tracked'] ],
                    "envio_id"  => $item_paquete['envio_id'],
                    "estado"    => $item_paquete['estado'],
                    "municipio" => $item_paquete['municipio'],
                    "direccion" => $item_paquete['direccion'],
                    "pz"        => 1,
                    "cliente_receptor_id"   => $item_paquete['cliente_receptor_id'],
                    "receptor"              => $item_paquete['nombre_receptor'],
                    "telefono"              => $item_paquete['telefono'] ." / ". $item_paquete['telefono_movil'],
                ]);

            }
        }


        foreach ($paqueteArray as $key => $itemPaquete) {
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
               $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row )->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                      'style' => \PHPExcel_Style_Border::BORDER_THIN
                  )
                )
            ));
            $objPHPExcel->getActiveSheet()->mergeCells('D'.$row.':F'. $row );

            $trackeds = "";

            $countFolio = 0;
            foreach ($itemPaquete["folios"] as $key => $item_trackend) {
                $countFolio = $countFolio + 1;
                $trackeds .= $item_trackend .",";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, "[". $trackeds ."]");
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$itemPaquete["estado"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$itemPaquete["municipio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$itemPaquete["direccion"]);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$countFolio);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$itemPaquete["receptor"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$itemPaquete["telefono"]);



            $row++;
        }

        $row =   $row + 2;

        $objPHPExcel->getActiveSheet()
        ->setCellValue('A' . $row, 'FOLIO')
        ->setCellValue('B' . $row, 'NOMBRE DE QUIEN RECIBE')
        ->setCellValue('F' . $row, 'FIRMA')
        ->setCellValue('H' . $row, 'COMENTARIOS');


        $objPHPExcel->getActiveSheet()->mergeCells('B'.$row.':E'. $row );
        $objPHPExcel->getActiveSheet()->mergeCells('F'.$row.':G'. $row );
        $objPHPExcel->getActiveSheet()->mergeCells('H'.$row.':I'. $row );

        $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row)->getFont()->setBold(true);
           $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row )->applyFromArray(array(
            'borders' => array(
              'allborders' => array(
                  'style' => \PHPExcel_Style_Border::BORDER_THIN
              )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        ));


        $row =   $row + 1;

        foreach ($paqueteArray as $key => $itemPaquete) {
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row. ':I'. $row )->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                      'style' => \PHPExcel_Style_Border::BORDER_THIN
                  )
                ),
            ));

            $trackeds = "";
            foreach ($itemPaquete["folios"] as $key => $item_trackend) {

                $trackeds .= $item_trackend .",";
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, "[". $trackeds ."]");

            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(25);

            $objPHPExcel->getActiveSheet()->mergeCells('B'.$row.':E'. $row );
            $objPHPExcel->getActiveSheet()->mergeCells('F'.$row.':G'. $row );
            $objPHPExcel->getActiveSheet()->mergeCells('H'.$row.':I'. $row );



            $row++;
        }




        header('Content-type: application/vnd.ms-excel');
        $filename = "Reporte-Hoja_Ruta_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }


    public function actionReporteRepartoAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();

        $get = Yii::$app->request->get();

        $Reparto = Reparto::findOne($get["reparto_id"]);
        $ViewRepartoRutas = ViewReparto::getReporteRepartoRutas($get);

        $sheet=0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $row = 2;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(8);

        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);


        foreach ($ViewRepartoRutas as $key => $ruta) {
            /************************************************************************
                    TITULO DE LA FILA
            /************************************************************************/
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,"PAQUETERIA");
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($row + 1),"RECEPCION DE ALMACEN A SUCURSALES");


                $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':F'. ($row + 1));


                $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(25);
                $objPHPExcel->getActiveSheet()->getRowDimension(($row + 1))->setRowHeight(25);

                $objPHPExcel->getActiveSheet()->getStyle('A'. $row .':F'. ($row + 1))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A'. $row .':F'. ($row + 1))->getFill()->getStartColor()->setRGB('000000');
                $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFill()->getStartColor()->setRGB('000000');



                $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,"CHOFER");
                $objPHPExcel->getActiveSheet()->getStyle('G'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('G'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 6,
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$ruta['chofer']);
                $objPHPExcel->getActiveSheet()->getStyle('H'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'size' => 6,
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,"CAMIONETA");
                $objPHPExcel->getActiveSheet()->getStyle('I'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('I'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('I'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 6,
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,$ruta['num_unidad']);
                $objPHPExcel->getActiveSheet()->getStyle('J'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'size' => 8,
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,"FECHA");
                $objPHPExcel->getActiveSheet()->getStyle('K'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('K'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('K'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 6,
                    )
                ));
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$row,Esys::unixTimeToString($ruta['created_at']));
                $objPHPExcel->getActiveSheet()->getStyle('L'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'size' => 8,
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('M'.$row,"FOLIO");
                $objPHPExcel->getActiveSheet()->getStyle('M'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('M'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('M'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 6,
                    )
                ));
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$row,"#". $ruta['reparto_id'] );
                $objPHPExcel->getActiveSheet()->getStyle('N'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'size' => 8,
                    )
                ));

                $objPHPExcel->getActiveSheet()->mergeCells('G'.($row + 1).':J'. ($row + 1));
                $objPHPExcel->getActiveSheet()->mergeCells('K'.($row + 1).':L'. ($row + 1));
                $objPHPExcel->getActiveSheet()->mergeCells('M'.($row + 1).':N'. ($row + 1));

                $objPHPExcel->getActiveSheet()->setCellValue('G'.($row + 1),"TITULAR DE SUCURSAL");
                $objPHPExcel->getActiveSheet()->getStyle('G'. ($row + 1) )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('G'. ($row + 1) )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('G'. ($row + 1))->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 8,
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('K'.($row + 1),"TITULAR DE SUCURSAL");
                $objPHPExcel->getActiveSheet()->getStyle('K'. ($row + 1) )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('K'. ($row + 1) )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('K'. ($row + 1))->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 8,
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('M'.($row + 1),"CHOFER RESPONSABLE");
                $objPHPExcel->getActiveSheet()->getStyle('M'. ($row + 1) )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('M'. ($row + 1) )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('M'. ($row + 1))->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 8,
                    )
                ));

            $row = $row + 2;

            /************************************************************************
                TITULO DE LA SUB FILA
            /************************************************************************/
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'. $row );
                $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(25);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,"RUTA / " . $ruta['ruta_nombre']);
                $objPHPExcel->getActiveSheet()->getStyle('A'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 6,
                    )
                ));
                $objPHPExcel->getActiveSheet()->mergeCells('C'.$row.':F'. $row );
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,"PAQUETES DE ENTREGA");
                $objPHPExcel->getActiveSheet()->getStyle('C'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('C'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('C'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 6,
                    )
                ));

                $objPHPExcel->getActiveSheet()->mergeCells('G'.$row.':H'. $row );
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,"DESCRIPCIÓN PAQUETES RECIBIDOS");
                $objPHPExcel->getActiveSheet()->getStyle('G'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('G'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('G'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 5,
                    )
                ));

                $objPHPExcel->getActiveSheet()->mergeCells('I'.$row.':J'. $row );
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,"NOMBRE Y FIRMA DE RECIBIDO");
                $objPHPExcel->getActiveSheet()->getStyle('I'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('I'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('I'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 5,
                    )
                ));

                $objPHPExcel->getActiveSheet()->mergeCells('K'.$row.':L'. $row );
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,"DESCRIPCIÓN PAQUETES DEVUELTOS");
                $objPHPExcel->getActiveSheet()->getStyle('K'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('K'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('K'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 5,
                    )
                ));

                $objPHPExcel->getActiveSheet()->mergeCells('M'.$row.':N'. $row );
                $objPHPExcel->getActiveSheet()->setCellValue('M'.$row,"NOMBRE Y FIRMA DE DEVOLUCION");
                $objPHPExcel->getActiveSheet()->getStyle('M'. $row )->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('M'. $row )->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('M'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                        'size' => 5,
                    )
                ));


            /************************************************************************
                BODY REPORTE
            /*************************************************************************/

            $ViewRepartoSucursal= ViewReparto::getReporteRepartoSucursal(["reparto_id" => $ruta['reparto_id'],"ruta_id" => $ruta['id']]);

            $row = $row + 1;

            foreach ($ViewRepartoSucursal as $key => $sucursal) {

                $ViewRepartoPaquete= ViewReparto::getReporteRepartoSucursalPaquete(["reparto_id" => $ruta['reparto_id'],"ruta_id" => $ruta['id'],'sucursal_id' => $sucursal['sucursal_id']]);

                $paquete_count = count($ViewRepartoPaquete);
                $cell_paquete  = ceil($paquete_count / 4) == 1 ? 0 : ceil($paquete_count / 4);

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'. ($row + $cell_paquete));
                /*
                $objPHPExcel->getActiveSheet()->mergeCells('G'.$row.':H'. ($row + $cell_paquete));
                $objPHPExcel->getActiveSheet()->mergeCells('I'.$row.':J'. ($row + $cell_paquete));
                $objPHPExcel->getActiveSheet()->mergeCells('K'.$row.':L'. ($row + $cell_paquete));
                $objPHPExcel->getActiveSheet()->mergeCells('M'.$row.':N'. ($row + $cell_paquete));*/
                //$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $sucursal['sucursal_nombre']);
                $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'size' => 6,
                    ),
                    'borders' => array(
                      'allborders' => array(
                          'style' => \PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                ));

                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':B'. ($row + $cell_paquete))->applyFromArray(array(
                    'borders' => array(
                      'allborders' => array(
                          'style' => \PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                ));

                $objPHPExcel->getActiveSheet()->mergeCells('G'.$row.':H'. ($row + $cell_paquete));
                $objPHPExcel->getActiveSheet()->mergeCells('I'.$row.':J'. ($row + $cell_paquete));
                $objPHPExcel->getActiveSheet()->mergeCells('K'.$row.':L'. ($row + $cell_paquete));
                $objPHPExcel->getActiveSheet()->mergeCells('M'.$row.':N'. ($row + $cell_paquete));

                $objPHPExcel->getActiveSheet()->getStyle('C'.$row.':N'. ($row + $cell_paquete))->applyFromArray(array(
                    'borders' => array(
                      'allborders' => array(
                          'style' => \PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                ));


                $count_with = 4;
                $count_add  = 0;
                $cell_enable = [
                    0 => 'C',
                    1 => 'D',
                    2 => 'E',
                    3 => 'F',
                ];


                foreach ($ViewRepartoPaquete as $key => $paquete) {
                    if ($count_add < $count_with) {
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_enable[$count_add].$row, $paquete['tracked']);
                        $objPHPExcel->getActiveSheet()->getStyle($cell_enable[$count_add]. $row)->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            ),
                            'font' => array(
                                'size' => 6,
                            )
                        ));
                        $count_add = $count_add + 1;
                    }else{
                        $count_add  = 0;
                        $row        = $row + 1;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_enable[$count_add].$row, $paquete['tracked']);
                        $objPHPExcel->getActiveSheet()->getStyle($cell_enable[$count_add]. $row)->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            ),
                            'font' => array(
                                'size' => 6,
                            )
                        ));
                        $count_add = $count_add + 1;
                    }

                }



                //$row = $row + $cell_paquete;
                $row = $row + 2;

            }

                $row = $row + 1;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':G'. $row );
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,"NOMBRE Y FIRMA DEL ENCARGADO");

                $objPHPExcel->getActiveSheet()->getStyle('A'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'size' => 6,
                    ),
                ));

                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':G'. $row)->applyFromArray(array(
                    'borders' => array(
                      'allborders' => array(
                          'style' => \PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                ));

                $objPHPExcel->getActiveSheet()->mergeCells('H'.$row.':N'. $row );
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,"NOMBRE Y FIRMA DEL OPERADOR");
                $objPHPExcel->getActiveSheet()->getStyle('H'. $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'size' => 6,
                    )
                ));

                $objPHPExcel->getActiveSheet()->getStyle('H'.$row.':N'. $row)->applyFromArray(array(
                    'borders' => array(
                      'allborders' => array(
                          'style' => \PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                ));
            $row = $row + 2;

            //$row = $row + 1;

        }

    //        ini_set('memory_limit', '-1');

        // header('Content-Type: application/vnd.ms-excel');
         header('Content-type: application/vnd.ms-excel');
         //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $filename = "Reporte-Reparto_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
        //exit;
    }


    public function actionReporteDowloandReparto()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();


        $requestGet = Yii::$app->request->get();
        $filters = [];
        $params  = "";
        foreach ($requestGet as $key => $value) {
            $params .= $key ."=". $value . "&";
        }
        $filters = [ "filters" => $params ];

        $ViewReparto =  ViewReparto::getJsonBtt($filters);


        $sheet=0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $row = 1;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);


        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':I'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':I'.$row)->getFill()->getStartColor()->setRGB('E69138');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        $objPHPExcel->getActiveSheet()->getStyle("A".$row.":I".$row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':I'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $envioArray = [];
        $repartoArray = [];
        foreach ($ViewReparto["rows"] as $key => $item_reparto) {
            array_push($repartoArray,$item_reparto["id"]);
        }

        $getPaqueteReparto = Reparto::getPaqueteRepartos($repartoArray);
        foreach ($getPaqueteReparto as $key_paquete => $item_paquete) {
            $is_add = true;

            foreach ($envioArray as $key_envio => $item_envio) {
                if ($item_envio["envio_id"] == $item_paquete["envio_id"]) {
                    $envioArray[$key_envio]["folio"]    = $envioArray[$key_envio]["folio"] ."," . $item_paquete["tracked"];
                    $envioArray[$key_envio]["pz"]       = intval($envioArray[$key_envio]["pz"]) + 1;
                    $is_add = false;
                }
            }

            if ($is_add) {
                array_push($envioArray,[
                    "envio_id"  => $item_paquete["envio_id"],
                    "folio"     => $item_paquete["tracked"],
                    "estado"    => $item_paquete["estado"],
                    "municipio" => $item_paquete["municipio"],
                    "pz"        => 1,
                    "reparto"   => $item_paquete["reparto_id"],
                    "unidad"    => $item_paquete["unidad"],
                    "chofer"    => $item_paquete["conductor"],
                    "viaje"     => $item_paquete["viaje"],
                    "fecha"     => date("d/m/Y",$item_paquete["fecha_unix"]),
                ]);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('REPORTE REPARTOS')
        ->setCellValue('A'. $row, 'FOLIO #')
        ->setCellValue('B'. $row, 'ESTADO')
        ->setCellValue('C'. $row, 'MUNICIPIO')
        ->setCellValue('D'. $row, 'PZ')
        ->setCellValue('E'. $row, 'REPARTO')
        ->setCellValue('F'. $row, 'UNIDAD')
        ->setCellValue('G'. $row, 'CHOFER')
        ->setCellValue('H'. $row, 'VIAJE')
        ->setCellValue('I'. $row, 'FECHA');

        $row = 2;
        foreach ($envioArray as $key_item => $item_envio) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$item_envio["folio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$item_envio["estado"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$item_envio["municipio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$item_envio["pz"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$item_envio["reparto"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$item_envio["unidad"]);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$item_envio["chofer"]);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$item_envio["viaje"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$item_envio["fecha"]);
            $row++;
        }

         header('Content-type: application/vnd.ms-excel');

        $filename = "Reporte-Reparto_Filter_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');

    }
    public function actionFacturasRepartoAjax($reparto_id)
    {
        $model = $this->findModel($reparto_id);


        $content= "";

        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8,
            // A4 paper format
            'format' => Pdf::FORMAT_LEGAL,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '@import url(http://fonts.googleapis.com/css?family=Bree+Serif);
            body, h1, h2, h3, h4, h5, h6{
                font-family: "Bree Serif", serif;
                                        }',
             // set mPDF properties on the fly
            'options' => ['title' => 'Ticket de envio'],
             // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>[ 'Fecha ' . date('Y-m-d',$model->created_at)],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);



        $pdf->setApi();
        $pdf_api = $pdf->getApi();
        $count_show = 0;
        foreach ($model->getSucursalReparto() as $key => $item) {
            $Sucursal  = Sucursal::findOne($item["sucursal_receptor_id"]);

            $content = $this->renderPartial('_factura', ["model" => $model, "sucursal" => $Sucursal]);
            $pdf_api->WriteHTML($content);
           // if (count($model->getSucursalReparto()) < ($key + 1) )

            $count_show = $count_show + 1;
            if (count($model->getSucursalReparto()) > $count_show)
                $pdf_api->AddPage();
        }

        /*$pdf_api->SetWatermarkImage(Yii::getAlias('@web').'/img/marca_agua_cora.png');
        $pdf_api->showWatermarkImage = true;*/


        // return the pdf output as per the destination setting
        return $pdf->render();

    }

    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionRepartosJsonBtt(){
        return ViewReparto::getJsonBtt(Yii::$app->request->get());
    }


    public function actionRepartoAddPaquete(){
        return ViewReparto::setRepartoAddPaqueteAjax(Yii::$app->request->post());
    }

    public function actionLoadRuta(){

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {
            // Obtenemos sucursal
            $ruta = Ruta::findOne($request->get('ruta_id'));

            // Devolvemos datos CHOSEN.JS
            $response = ['ruta_tipo' => $ruta->tipo];

            return $response;
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
    protected function findModel($name, $_model = 'model')
    {
        switch ($_model) {
            case 'model':
                $model = Reparto::findOne($name);
                break;

            case 'view':
                $model = ViewReparto::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
