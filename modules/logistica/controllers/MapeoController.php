<?php
namespace app\modules\logistica\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\mapeo\Mapeo;
use app\models\mapeo\MapeoDetalle;
use app\models\mapeo\ViewMapeo;
use app\models\envio\EnvioDetalle;
use app\models\movimiento\MovimientoPaquete;

/*use app\models\reparto\Reparto;
use app\models\reparto\RepartoFila;
use app\models\ruta\FilaRuta;
use app\models\ruta\FilaPaquete;
use app\models\reparto\RepartoRecoleccion;*/

/**
 * Default controller for the `clientes` module
 */
class MapeoController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('mapeoCreate'),
            'update' => Yii::$app->user->can('mapeoUpdate'),
            'delete' => Yii::$app->user->can('mapeoDelete'),
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
     * Creates a new Sucursal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Mapeo();
        $model->lista_paquete_array = new MapeoDetalle();

        if ($model->load(Yii::$app->request->post()) && $model->lista_paquete_array->load(Yii::$app->request->post())) {
            $model->status = Mapeo::STATUS_ACTIVE;
            if ($model->save()) {
                if ( $model->lista_paquete_array->mapeo_detalle_array_save($model->id)) {
    	            return $this->redirect(['view',
    	                'id' => $model->id
    	            ]);
                }
        	}
        }

        return $this->render('create', [
            'model' => $model,
        ]);
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


    public function actionReporteMapeoAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());



        $sheet=0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFill()->getStartColor()->setRGB('800000');
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:C1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);



        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Descarga ')

        ->setCellValue('A1', 'Tracked')
        ->setCellValue('B1', 'Fila')
        ->setCellValue('C1', 'Comentario');

        $ViewDescargaMapeo = ViewMapeo::getReporteDescarga(Yii::$app->request->get());

        $row=2;


        foreach ($ViewDescargaMapeo as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$item["tracked"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$item["fila"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$item["observaciones"]);
            $row++;
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Descarga_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');

    }
    public function actionReporteCargaUnidadesAjax()
    {

        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());
        //$sheet = $objPHPExcel->getActiveSheet();


        $sheet=0;

        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(40);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFill()->getStartColor()->setRGB('800000');
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:R1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);



        $objPHPExcel->getActiveSheet()->setTitle('Reporte de carga de unidades ')

        ->setCellValue('A1', 'TRACKED')
        ->setCellValue('B1', 'POBLACION')
        ->setCellValue('C1', 'INTERNO')
        ->setCellValue('D1', 'RUTA')
        ->setCellValue('E1', 'FILA')
        ->setCellValue('F1', 'PESO UNITARIO')
        ->setCellValue('G1', 'REENVIO')
        ->setCellValue('H1', 'ESTADO')
        ->setCellValue('I1', 'MUNICIPIO')
        ->setCellValue('J1', 'DIRECCION')
        ->setCellValue('K1', 'PESO TOTAL')
        ->setCellValue('L1', 'PIEZAS ESCANEADAS')
        ->setCellValue('M1', 'PIEZAS POR SUCURSAL')
        ->setCellValue('N1', 'PIEZAS TOTALES')
        ->setCellValue('O1', 'RECEPTOR')
        ->setCellValue('P1', 'TELEFONO R.')
        ->setCellValue('Q1', 'ESTATUS')
        ->setCellValue('R1', 'OBSERVACIONES');


        $ViewDescargaMapeo  = ViewMapeo::getReporteCargaUnidades(Yii::$app->request->get());
        $MapeoPaquete       = [];
        $RutaList           = [];
        $row=2;

        foreach ($ViewDescargaMapeo as $key => $item) {

            $is_val                             = false;
            $item["count_item_show"]            = 0;
            $item["paquetes_envio_sucursal"]    = 0;
            $item["peso_total_sucursal"]        = 0;
            $item["peso_total_escaneadas"]      = 0;
            $count_item                         = 0;
            $count_sucursal                     = 0;
            $count_escaneadas                   = 0;
            $peso_total_sucursal                = 0;

            foreach ($MapeoPaquete as $key => $seach) {
                /*if ($item["envio_id"]  ==  $seach["envio_id"]  ){
                    $count_item = intval($seach["count_item_show"]);
                }*/
                //foreach ($MapeoPaquete as $key => $itemPaquete) {
                    if ($seach["sucursal_id"] == $item["sucursal_id"] && $item["envio_id"]  ==  $seach["envio_id"] ) {
                        $count_item     = intval($seach["count_item_show"]);
                    }
                //}
            }

            foreach ($ViewDescargaMapeo as $key => $sucursal) {
                if ($sucursal["sucursal_id"] == $item["sucursal_id"] && $sucursal["envio_id"]  ==  $item["envio_id"]) {
                    $count_sucursal         = $count_sucursal + 1;
                    $peso_total_sucursal    = $peso_total_sucursal + floatval($sucursal["peso_unitario_mx"]);
                }
            }

            foreach ($ViewDescargaMapeo as $key => $escaneado) {
                if ( $escaneado["envio_id"]  ==  $item["envio_id"]) {
                    $count_escaneadas         = $count_escaneadas + 1;
                }
            }

            $item["count_item_show"]         =  $count_item + 1;
            $item["paquetes_envio_sucursal"] =  $count_sucursal;
            $item["paquetes_envio_escaneado"]=  $count_escaneadas;
            $item["peso_total_sucursal"]     =  $peso_total_sucursal;

            array_push($MapeoPaquete, $item);

            $is_ruta = true;
            $ruta   = [
                "ruta"      => $item["ruta_nombre"],
                "ruta_id"   => $item["ruta_id"],
                "items"     => 1,
            ];


            foreach ($RutaList as $key => $itemRuta) {
                if ($itemRuta["ruta"] ==  $item["ruta_nombre"] ) {
                    $is_ruta = false;

                    $RutaList[$key]["items"] = intval($itemRuta["items"]) + 1;

                }
            }
            if ($is_ruta) {
                array_push($RutaList, $ruta);
            }
        }


        foreach ($MapeoPaquete as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$item["tracked"]);

            $objPHPExcel->getActiveSheet()->getStyle('A'. $row .':Q'.$row)->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                      'style' => \PHPExcel_Style_Border::BORDER_THIN
                  )
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$item["sucursal_nombre"]);

            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$item["count_item_show"] ." / ". $item["paquetes_envio_sucursal"]);

            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$item["ruta_nombre"]);

            $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$item["fila"]);

            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row, round(floatval($item["peso_unitario_mx"]), 2) );

            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$item["is_reenvio"] ? "SI" : "NO");
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$item["estado"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$item["municipio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,$item["is_reenvio"] ? $item["direccion"] ." Referencia: " . $item["referencia"] : '');

            $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,round($item["peso_total_sucursal"],2));


            $objPHPExcel->getActiveSheet()->setCellValue('L'.$row,$item["paquetes_envio_escaneado"]);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$row,$item["paquetes_envio_sucursal"]);





            if (intval($item["count_item_show"]) == 1 && intval($item["paquetes_envio_sucursal"]) > 1) {
                $objPHPExcel->getActiveSheet()->mergeCells('K'.$row.':K'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                $objPHPExcel->getActiveSheet()->mergeCells('L'.$row.':L'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                $objPHPExcel->getActiveSheet()->mergeCells('M'.$row.':M'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                $objPHPExcel->getActiveSheet()->mergeCells('N'.$row.':N'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                $objPHPExcel->getActiveSheet()->mergeCells('O'.$row.':O'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                $objPHPExcel->getActiveSheet()->mergeCells('P'.$row.':P'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
            }

            $objPHPExcel->getActiveSheet()->setCellValue('N'.$row,$item["cantidad"]);
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$row,$item["nombre_receptor"]);
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$row,$item["telefono_movil"]);

            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row,MovimientoPaquete::$tipoLaxTierList[EnvioDetalle::getMovimientoTop($item["tracked"])]);

            $objPHPExcel->getActiveSheet()->setCellValue('R'.$row,$item["observaciones"]);

            $objPHPExcel->getActiveSheet()->getStyle('C'.$row.':R'.$row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ));

            $row++;
        }

        $row = $row + 2;

        $objPHPExcel->getActiveSheet()->setCellValue('A'.($row + 1) , "RUTA");
        $objPHPExcel->getActiveSheet()->setCellValue('B'.($row + 1) , "PAQUETES");
        $objPHPExcel->getActiveSheet()->getRowDimension($row + 1)->setRowHeight(40);

        $objPHPExcel->getActiveSheet()->getStyle('A'. ($row + 1) . ':B'.  ($row + 1))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'. ($row + 1) . ':B'.  ($row + 1))->getFill()->getStartColor()->setRGB('800000');
        $objPHPExcel->getActiveSheet()->getStyle('A'. ($row + 1) . ':B'.  ($row + 1) )->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        $row = $row + 2;

        foreach ($RutaList as $key => $ruta) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$ruta["ruta"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$ruta["items"]);

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

            $row++;
        }

        $hojaNum = 1;
        foreach ($RutaList as $key => $ruta_hoja) {

            $objPHPExcel->createSheet($hojaNum); //Setting index when creating
            $objPHPExcel->setActiveSheetIndex($hojaNum);
            $objPHPExcel->getActiveSheet()->setTitle($ruta_hoja["ruta"] ? $ruta_hoja["ruta"] : 'NULL' );



            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);

            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);

            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(40);

            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFill()->getStartColor()->setRGB('800000');
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            // Add some data
            $objPHPExcel->getActiveSheet()->getStyle("A1:R1")->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);



            $objPHPExcel->getActiveSheet()
            ->setCellValue('A1', 'TRACKED')
            ->setCellValue('B1', 'POBLACION')
            ->setCellValue('C1', 'INTERNO')
            ->setCellValue('D1', 'RUTA')
            ->setCellValue('E1', 'FILA')
            ->setCellValue('F1', 'PESO UNITARIO')
            ->setCellValue('G1', 'REENVIO')
            ->setCellValue('H1', 'ESTADO')
            ->setCellValue('I1', 'MUNICIPIO')
            ->setCellValue('J1', 'DIRECCION')
            ->setCellValue('K1', 'PESO TOTAL')
            ->setCellValue('L1', 'PIEZAS ESCANEADAS')
            ->setCellValue('M1', 'PIEZAS POR SUCURSAL')
            ->setCellValue('N1', 'PIEZAS TOTALES')
            ->setCellValue('O1', 'RECEPTOR')
            ->setCellValue('P1', 'TELEFONO R.')
            ->setCellValue('Q1', 'ESTATUS')
            ->setCellValue('R1', 'OBSERVACIONES');

            $ViewRuta           = ViewMapeo::getReporteCargaUnidades(Yii::$app->request->get(),$ruta_hoja["ruta_id"]);
            $MapeoPaquete       = [];
            $RutaList           = [];
            $row=2;

            foreach ($ViewRuta as $key => $item) {

                $is_val                             = false;
                $item["count_item_show"]            = 0;
                $item["paquetes_envio_sucursal"]    = 0;
                $item["peso_total_sucursal"]        = 0;
                $count_item                         = 0;
                $count_sucursal                     = 0;
                $count_escaneadas                   = 0;
                $peso_total_sucursal                = 0;

                foreach ($MapeoPaquete as $key => $seach) {
                    /*if ($item["envio_id"]  ==  $seach["envio_id"]  ){
                        $count_item = intval($seach["count_item_show"]);
                    }*/
                    //foreach ($MapeoPaquete as $key => $itemPaquete) {
                        if ($seach["sucursal_id"] == $item["sucursal_id"] && $item["envio_id"]  ==  $seach["envio_id"] ) {
                            $count_item     = intval($seach["count_item_show"]);
                        }
                    //}
                }

                foreach ($ViewDescargaMapeo as $key => $sucursal) {
                    if ($sucursal["sucursal_id"] == $item["sucursal_id"] && $sucursal["envio_id"]  ==  $item["envio_id"]) {
                        $count_sucursal         = $count_sucursal + 1;
                        $peso_total_sucursal    = $peso_total_sucursal + floatval($sucursal["peso_unitario_mx"]);
                    }
                }

                foreach ($ViewDescargaMapeo as $key => $escaneado) {
                    if ( $escaneado["envio_id"]  ==  $item["envio_id"]) {
                        $count_escaneadas         = $count_escaneadas + 1;
                    }
                }

                $item["count_item_show"]         =  $count_item + 1;
                $item["paquetes_envio_sucursal"] =  $count_sucursal;
                $item["paquetes_envio_escaneado"]=  $count_escaneadas;
                $item["peso_total_sucursal"]     =  $peso_total_sucursal;

                array_push($MapeoPaquete, $item);

                $is_ruta = true;
                $ruta   = [
                    "ruta"      => $item["ruta_nombre"],
                    "ruta_id"   => $item["ruta_id"],
                    "items"     => 1,
                ];


                foreach ($RutaList as $key => $itemRuta) {
                    if ($itemRuta["ruta"] ==  $item["ruta_nombre"] ) {
                        $is_ruta = false;

                        $RutaList[$key]["items"] = intval($itemRuta["items"]) + 1;

                    }
                }
                if ($is_ruta) {
                    array_push($RutaList, $ruta);
                }
            }


            foreach ($MapeoPaquete as $item) {

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$item["tracked"]);
                $objPHPExcel->getActiveSheet()->getStyle('A'. $row .':Q'.$row)->applyFromArray(array(
                    'borders' => array(
                      'allborders' => array(
                          'style' => \PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$item["sucursal_nombre"]);

                $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$item["count_item_show"] ." / ". $item["paquetes_envio_sucursal"]);

                $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$item["ruta_nombre"]);

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$item["fila"]);

                $objPHPExcel->getActiveSheet()->setCellValue('F'.$row, round(floatval($item["peso_unitario_mx"]), 2) );

                $objPHPExcel->getActiveSheet()->setCellValue('G'.$row,$item["is_reenvio"] ? "SI" : "NO");
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$row,$item["estado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$row,$item["municipio"]);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$row,$item["is_reenvio"] ? $item["direccion"] ." Referencia: " . $item["referencia"] : '');

                $objPHPExcel->getActiveSheet()->setCellValue('K'.$row,round($item["peso_total_sucursal"],2));

                $objPHPExcel->getActiveSheet()->setCellValue('L'.$row,$item["paquetes_envio_escaneado"]);

                $objPHPExcel->getActiveSheet()->setCellValue('M'.$row,$item["paquetes_envio_sucursal"]);


                if (intval($item["count_item_show"]) == 1 && intval($item["paquetes_envio_sucursal"]) > 1) {

                    $objPHPExcel->getActiveSheet()->mergeCells('K'.$row.':K'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                    $objPHPExcel->getActiveSheet()->mergeCells('L'.$row.':L'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                    $objPHPExcel->getActiveSheet()->mergeCells('M'.$row.':M'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                    $objPHPExcel->getActiveSheet()->mergeCells('N'.$row.':N'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                    $objPHPExcel->getActiveSheet()->mergeCells('O'.$row.':O'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                    $objPHPExcel->getActiveSheet()->mergeCells('P'.$row.':P'. (($row + intval($item["paquetes_envio_sucursal"]) - 1) ));
                }

                $objPHPExcel->getActiveSheet()->setCellValue('N'.$row,$item["cantidad"]);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.$row,$item["nombre_receptor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$row,$item["telefono_movil"]);

                $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row,MovimientoPaquete::$tipoLaxTierList[EnvioDetalle::getMovimientoTop($item["tracked"])]);

                $objPHPExcel->getActiveSheet()->setCellValue('R'.$row,$item["observaciones"]);


                $objPHPExcel->getActiveSheet()->getStyle('C'.$row.':R'.$row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                ));

                $row++;
            }

            $hojaNum = $hojaNum + 1;
        }




        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-carga-unidades_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');

    }

    public function actionReportePlaneacion()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());
        //$sheet = $objPHPExcel->getActiveSheet();


        $sheet=0;

        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(40);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'TRAILER');
        $objPHPExcel->getActiveSheet()->mergeCells('B2:O2');
        $objPHPExcel->getActiveSheet()->getStyle('B2:O2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->getStartColor()->setRGB('1d3d74');
        $objPHPExcel->getActiveSheet()->getStyle('B2')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        $objPHPExcel->getActiveSheet()->getStyle("B2:O2")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B2:O2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $row = 3;

        $objPHPExcel->getActiveSheet()->getStyle('B'.$row.':O'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row.':O'.$row)->getFill()->getStartColor()->setRGB('1d3d74');
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row.':O'.$row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("B".$row.":O".$row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row.':O'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setTitle('PLANEACIÓN')
        ->setCellValue('B'. $row, 'RUTA')
        ->setCellValue('C'. $row, 'PIEZAS')
        ->setCellValue('D'. $row, 'SUCURSAL/DESTINO')
        ->setCellValue('F'. $row, 'PIEZAS X SUC')
        ->setCellValue('G'. $row, 'CHASIS')
        ->setCellValue('H'. $row, 'SALIDA')
        ->setCellValue('I'. $row, 'KM X VIAJE')
        ->setCellValue('J'. $row, 'TIEMPO X VIAJE')
        ->setCellValue('K'. $row, 'GASOLINA X VIAJE')
        ->setCellValue('L'. $row, '1RA RECARGA')
        ->setCellValue('M'. $row, '2DA RECARGA')
        ->setCellValue('N'. $row, '3RA RECARGA')
        ->setCellValue('O'. $row, '4TA RECARGA');

        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->mergeCells('D'.$row.':E'.$row);


        $ViewDescargaMapeo  = ViewMapeo::getReportePlaneacion(Yii::$app->request->get());

        $MapeoPaquete       = [];
        $RutaList           = [];
        $row = $row + 1;


        foreach ($ViewDescargaMapeo as $key => $item) {

            $is_val                             = false;
            $item["count_item_show"]            = 0;
            $item["count_item_elemt"]           = 0;
            $item["count_pz_total"]        = 0;
            $count_pz_total                = 0;
            $count_item                    = 0;
            $count_item_elemt              = 0;

            foreach ($MapeoPaquete as $key => $seach) {
                if ($seach["ruta_id"] == $item["ruta_id"]) {
                    $count_item_elemt     = intval($seach["count_item_elemt"]);
                }
            }

            foreach ($ViewDescargaMapeo as $key => $sucursal) {
                if ($sucursal["ruta_id"] == $item["ruta_id"]) {
                    $count_item             = $count_item + 1;
                    $count_pz_total         = $count_pz_total + $sucursal["mapeo_count"];
                }
            }

            $item["count_item_elemt"]   =  $count_item_elemt + 1;
            $item["count_item_show"]    =  $count_item;
            $item["count_pz_total"]     =  $count_pz_total;

            array_push($MapeoPaquete, $item);
        }

        foreach ($MapeoPaquete as $item) {

            $objPHPExcel->getActiveSheet()->getStyle('B'. $row .':O'.$row)->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                      'style' => \PHPExcel_Style_Border::BORDER_THIN
                  )
                )
            ));

            if (intval($item["count_item_elemt"]) == 1 && intval($item["count_item_show"]) > 1) {
                $objPHPExcel->getActiveSheet()->mergeCells('B'.$row.':B'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('C'.$row.':C'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('D'.$row.':D'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('G'.$row.':G'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('H'.$row.':H'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('I'.$row.':I'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('J'.$row.':J'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('K'.$row.':K'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('L'.$row.':L'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('M'.$row.':M'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('N'.$row.':N'. (($row + intval($item["count_item_show"]) -1 )));
                $objPHPExcel->getActiveSheet()->mergeCells('O'.$row.':O'. (($row + intval($item["count_item_show"]) -1 )));
            }


            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row,$item["ruta_nombre"]);

            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,$item["count_pz_total"]);

            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$item["count_item_show"]);

            $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,$item["sucursal_nombre"]);

            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row, $item["mapeo_count"] );

            $objPHPExcel->getActiveSheet()->getStyle('B'.$row.':F'.$row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ));

            $row++;
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-planeacion_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');

    }

    public function actionDelete($id)
    {

        $model        = $this->findModel($id);
        $MapeoDetalle = MapeoDetalle::find()->andWhere(["mapeo_id" => $model->id])->all();

        try{

            foreach ($MapeoDetalle as $key => $paquete) {
                $paquete->delete();
            }
            // Eliminamos el usuario
            $model->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente el mapeo #" . $id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden remover el mapeo.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->redirect(['index']);
    }

    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionMapeosJsonBtt(){
        return ViewMapeo::getJsonBtt(Yii::$app->request->get());
    }

    public function actionRutaPaqueteAjax(){
        return ViewMapeo::getRutaPaquete(Yii::$app->request->get());
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
                $model = Mapeo::findOne($name);
                break;

            case 'view':
                $model = ViewMapeo::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }
}
