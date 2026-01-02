<?php
namespace app\modules\reportes\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Response;
use app\models\envio\ViewEnvio;
use app\models\sucursal\ViewSucursal;
use app\models\sucursal\Sucursal;
use app\models\movimiento\MovimientoPaquete;
use app\models\viaje\Viaje;
use app\models\envio\EnvioDetalle;

/**
 * Default controller for the `clientes` module
 */
class ReporteController extends \app\controllers\AppController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionCuentas($id = false)
    {
        if ($id) {
            $Viaje = Viaje::findOne($id);

            if (isset($Viaje->id)) {
                if ($Viaje->status == Viaje::STATUS_CERRADO || $Viaje->status == Viaje::STATUS_TERMINADO) {
                    return $this->render('cuentas',[
                        "model" => $Viaje
                    ]);
                }else{
                    Yii::$app->session->setFlash('warning', "El viaje aun no se encuentra CERRADO Ó TERMINADO para continuar.");
                    return $this->render('cuentas');
                }
            }else{
                Yii::$app->session->setFlash('warning', "Ocurrio un error, verifica tu información");
                return $this->render('cuentas');
            }

        }else
            return $this->render('cuentas');
    }

    public function actionReporteCuentasSucursal($id)
    {
        $model = Viaje::findOne($id);

        $content = $this->renderPartial('_reporte_cuenta_pdf', ["model" => $model]);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
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
            'options' => ['title' => 'REPORTE DE CUENTAS'],
             // call mPDF methods on the fly
        ]);

        $pdf->marginLeft = 3;
        $pdf->marginRight = 3;

        // return the pdf output as per the destination setting
        return $pdf->render();
    }

    public function actionGetSucursalPaquete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request->get();

        if (isset($request["viaje_id"]) && isset($request["sucursal_id"]) ) {
            return [
                "code"   => 202,
                "result" => Viaje::getSucursalEnviaPaqueteAll($request["viaje_id"],$request["sucursal_id"]),
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente",
        ];

    }

     public function actionEntrega()
    {
        return $this->render('entrega');
    }

    public function actionCobro()
    {
        return $this->render('cobro');
    }

    public function actionCobroMx()
    {
        return $this->render('cobro-mx');
    }

    public function actionEstadoCuenta()
    {
        return $this->render('estado-cuenta');
    }

    public function  actionEstadoCuentaView($id, $date_range = false)
    {
        $model      = Sucursal::findOne($id);
        $date_ini  = false;
        $date_fin  = false;
        if ($date_range) {
            $date_ini = strtotime(substr($date_range, 0, 10));
            $date_fin = strtotime(substr($date_range, 13, 23)) + 86340;
        }

        return $this->render('estado-cuenta-view',[
            "model"     => $model,
            "date_fin"  => $date_fin,
            "date_ini"  => $date_ini,
        ]);
    }

    public function actionEstadoCuentaAjax($sucursal_id, $date_ini, $date_fin)
    {
        $model = Sucursal::findOne($sucursal_id);

        $content = $this->renderPartial('_estado-cuenta-pdf', ["model" => $model, "date_ini" => $date_ini, "date_fin" => $date_fin ]);


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
            'options' => ['title' => 'Estado de cuenta'],
             // call mPDF methods on the fly
            'methods' => [
                'SetHeader'=>[ 'Fecha ' . date('Y-m-d',$model->created_at)],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->setApi();
        // return the pdf output as per the destination setting
        return $pdf->render();

    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionReembolso()
    {
        return $this->render('reembolso');
    }

    public function actionGasto()
    {
        return $this->render('gasto');
    }

    public function actionComision()
    {
        return $this->render('comision');
    }

    public function actionComisionAgente()
    {
        return $this->render('comision-agente');
    }

    public function actionCobroGasto()
    {
        return $this->render('cobro-gasto');
    }

    public function actionReporteSeguimiento()
    {
        return $this->render('reporte-seguimiento');
    }

    public function actionReporteEgresosAjax()
    {
        $request        = Yii::$app->request;
        $objPHPExcel    = new \PHPExcel();
        $sheet          = 0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);


        $objPHPExcel->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFill()->getStartColor()->setRGB('727576');
        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);


        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )

        ));

        $objPHPExcel->getActiveSheet()->getStyle("D1:G1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $objPHPExcel->getActiveSheet()->getStyle('D1:G1')->getFill()->getStartColor()->setRGB('727576');
        $objPHPExcel->getActiveSheet()->getStyle('D1:G1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);



        $objPHPExcel->getActiveSheet()->getStyle('D1:G1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));



        $objPHPExcel->getActiveSheet()->setTitle('Reporte de viaje')
        ->setCellValue('A1', 'TEORICO')
        ->setCellValue('D1', 'REAL');

        $objPHPExcel->getActiveSheet()->mergeCells('D1:F1');




        $objPHPExcel->getActiveSheet()->setCellValue('A2','PAQ MEX');
        $objPHPExcel->getActiveSheet()->setCellValue('A3','PAQ LAX');
        $objPHPExcel->getActiveSheet()->setCellValue('A4','PAQ TIERRA');

        $objPHPExcel->getActiveSheet()->setCellValue('B2','0.00');
        $objPHPExcel->getActiveSheet()->setCellValue('B3','0.00');
        $objPHPExcel->getActiveSheet()->setCellValue('B4','0.00');


        $objPHPExcel->getActiveSheet()->setCellValue('D2','BILLETES');
        $objPHPExcel->getActiveSheet()->setCellValue('E2','CANTIDAD');
        $objPHPExcel->getActiveSheet()->setCellValue('F2','TOTAL');
        $objPHPExcel->getActiveSheet()->setCellValue('G2','TOTAL');

        $objPHPExcel->getActiveSheet()->setCellValue('D3','100.00');
        $objPHPExcel->getActiveSheet()->setCellValue('D4','50.00');
        $objPHPExcel->getActiveSheet()->setCellValue('D5','20.00');
        $objPHPExcel->getActiveSheet()->setCellValue('D6','10.00');
        $objPHPExcel->getActiveSheet()->setCellValue('D7','5.00');
        $objPHPExcel->getActiveSheet()->setCellValue('D8','2.00');
        $objPHPExcel->getActiveSheet()->setCellValue('D9','1.00');
        $objPHPExcel->getActiveSheet()->setCellValue('D10','0.25');
        $objPHPExcel->getActiveSheet()->setCellValue('D11','0.10');
        $objPHPExcel->getActiveSheet()->setCellValue('D12','0.05');
        $objPHPExcel->getActiveSheet()->setCellValue('D13','0.01');



        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Egresos_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');



    }

    public function actionReporteCsvSeguimiento()
    {
        $request        = Yii::$app->request;
        $objPHPExcel    = new \PHPExcel();
        $sheet          = 0;



        $objPHPExcel->setActiveSheetIndex($sheet);


        /*********************************************
                            STYLES
        /**********************************************/
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);

        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);


        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);

        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(40);

        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(30);


        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);







        $row = 1;

        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':M'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':M'.$row)->getFill()->getStartColor()->setRGB('E69138');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':M'.$row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A".$row.":M".$row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':M'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setTitle('REPORTE ADMINISTRACION')
        ->setCellValue('A'. $row, 'TRACKING #')
        ->setCellValue('B'. $row, 'SUCURSAL QUE ENVIA')
        ->setCellValue('C'. $row, 'SUCURSAL QUE RECIBE')
        ->setCellValue('D'. $row, 'CLIENTE QUE RECIBE')
        ->setCellValue('E'. $row, 'TELEFONO QUE RECIBE')
        ->setCellValue('F'. $row, 'CODIGO POSTAL')
        ->setCellValue('G'. $row, 'ESTADO')
        ->setCellValue('H'. $row, 'MUNICIPIO')
        ->setCellValue('I'. $row, 'DIRECCION')
        ->setCellValue('J'. $row, 'DESCRIPCION')
        ->setCellValue('K'. $row, 'PESO U.S.A')
        ->setCellValue('L'. $row, 'PESO M.X')
        ->setCellValue('M'. $row, 'ESTATUS');


        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);
        /****************************************************************************
                                        BODY
        /****************************************************************************/



        $request = Yii::$app->request->get();

        $UrlParams = "";
        foreach ($request as $key => $item_request) {
            $UrlParams .= $key ."=".$item_request."&";
        }

        $paqueteView    = ViewEnvio::getReportePaqueteSeguimiento(["filters" => $UrlParams ]);
        $row            = $row + 1 ;


        /****************************************************************************
                                        INDEX PAGE 1
        /***************************************************************************/
        foreach ($paqueteView["rows"] as $key => $r_Financiero) {

            $objPHPExcel->getActiveSheet()->getStyle('A'. $row .':M'.$row)->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                  )
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$r_Financiero["tracked"]);

            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$r_Financiero["sucursal_nombre"]);

            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$r_Financiero["nombre_sucursal"]);

            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$r_Financiero["nombre_receptor"]);

            $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$r_Financiero["telefono_movil"] ." / ". $r_Financiero["telefono"]);

            //$objPHPExcel->getActiveSheet()->setCellValue('C'.$row, DescargaBodega::$descargaList[$r_Financiero["bodega_descarga"]] );

            if ( $r_Financiero["sucursal_recibe_is_reenvio"] == 10 ) {
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$r_Financiero["code_postal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$r_Financiero["estado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$r_Financiero["municipio"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$r_Financiero["direccion"]);
            }else{
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$row,$r_Financiero["code_sucursal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$r_Financiero["estado_sucursal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$r_Financiero["municipio_sucursal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$r_Financiero["direccion_sucursal"]);
            }

            $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,$r_Financiero["observaciones"]);

            $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,round($r_Financiero["peso_unitario"],2) );

            $objPHPExcel->getActiveSheet()->setCellValue('L'.$row,0);

            $objPHPExcel->getActiveSheet()->setCellValue('M'.$row, MovimientoPaquete::$tipoLaxTierList[$r_Financiero["tipo_movimiento"]]);

            $row++;

        }

        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Seguimiento_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');



    }

    public function actionGetPaquetes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $paquetes = MovimientoPaquete::find()->andWhere([ "and",
            [">","fecha_entrega",strtotime(date("Y-m-d", time())."- 3 month")],
            ["<","fecha_entrega",strtotime(date("Y-m-d", time())."+ 1 month")],
        ])->all();
        return [
            "code" => 202,
            "items" => $paquetes,
        ];
    }


    public function actionGetPaquete()
    {
        $request = Yii::$app->request;
        if ($request->validateCsrfToken() && $request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ( Yii::$app->request->get('id_paquete')) {
                $MovimientoPaquete = MovimientoPaquete::findOne(Yii::$app->request->get('id_paquete'));
                if ($MovimientoPaquete) {
                    $paquete = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);
                    return [
                        "code" => 202,
                        "event" => [
                            "id" => $MovimientoPaquete->id,
                            "tracked"       => $MovimientoPaquete->tracked,
                            "cliente"       => isset($paquete->cliente_receptor_id) ? $paquete->clienteReceptor->nombreCompleto : 'N/A',
                            "sucursal"      =>  isset($paquete->sucursal_receptor_id) ? $paquete->sucursalReceptor->nombre : 'N/A',
                        ],
                    ];
                }
            }
            return [
                "code" => 10,
                "message" => "Ocurrio un error, intente nuevamente",
                "type" => "error",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//

    public function actionCobrosJsonBtt(){
        return ViewEnvio::getReporteJsonBtt(Yii::$app->request->get());
    }

    public function actionReporteComisionJsonBtt(){
        return ViewEnvio::getReporteComisionJsonBtt(Yii::$app->request->get());
    }

    public function actionComisionAgenteJsonBtt(){
        return ViewEnvio::getReporteAgenteJsonBtt(Yii::$app->request->get());
    }

    public function actionReporteSeguimientoJsonBtt(){
        return ViewEnvio::getPaqueteSeguimiento(Yii::$app->request->get());

    }

    public function actionEstadoCuentaJsonBtt(){
        return ViewSucursal::getEstadoCuentaJsonBtt(Yii::$app->request->get());
    }
}
