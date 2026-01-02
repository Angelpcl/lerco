<?php

namespace app\modules\logistica\controllers;

use Yii;
use yii\web\Response;
use app\models\Esys;
use kartik\mpdf\Pdf;
use app\models\envio\Envio;
use app\models\viaje\Viaje;
use app\models\envio\ViewEnvio;
use app\models\envio\EnvioDetalle;
use app\models\viaje\ViewViaje;
use app\models\viaje\ViajeDetalle;
use yii\web\NotFoundHttpException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use app\models\descarga\DescargaBodega;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\viaje\ViajePaqueteDenegado;
use app\models\movimiento\MovimientoPaquete;
use app\models\envio\DetailEnvioProduct;

/**
 * Default controller for the `clientes` module
 */
class ViajeTierraController extends \app\controllers\AppController
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
        return $this->render('index', [
            "can" => $this->can
        ]);
    }

    /**
     * Displays a single EsysDivisa model.
     * @param integer $name
     * @return mixed
     */
    public function actionView($id)
    {
        ini_set('memory_limit', '-1');
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

        return $this->redirect(['view', 'id' => $id]);
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
            $model->tipo_servicio   = Envio::TIPO_ENVIO_TIERRA;
            if ($model->save()) {
                return $this->redirect([
                    'view',
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
        $model->fecha_salida = date("Y-m-d", $model->fecha_salida);

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionProductoRemove($viaje_id, $paquete_id, $tipo)
    {
        // Eliminamos el usuario
        $ViajeDetalle = ViajeDetalle::find()->where(['and', ["viaje_id" => $viaje_id, "paquete_id" => $paquete_id, "tipo" => $tipo]])->one();
        try {
            // Eliminamos el usuario
            if ($ViajeDetalle->delete()) {
                $MovimientoPaquete = new MovimientoPaquete();
                $MovimientoPaquete->paquete_id      = $ViajeDetalle->paquete_id;
                $MovimientoPaquete->tracked         = $ViajeDetalle->tracked;
                $MovimientoPaquete->tipo_envio      = isset($ViajeDetalle->envioDetalleLaxTierra->envio->tipo_envio) ? $ViajeDetalle->envioDetalleLaxTierra->envio->tipo_envio : null;
                $MovimientoPaquete->tipo_movimiento = MovimientoPaquete::LAX_TIER_SUCURSAL;
                $MovimientoPaquete->tipo            = $ViajeDetalle->tipo;
                $MovimientoPaquete->save();

                Yii::$app->session->setFlash('success', "Se ha removido correctamente el paquete #" . $paquete_id);
            }
        } catch (\Exception $e) {
            if ($e->getCode() === 23000) {
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden remover el paquete.');
                header("HTTP/1.0 400 Relation Restriction");
            } else {
                throw $e;
            }
        }
        return $this->redirect(['view', 'id' => $viaje_id]);
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
        try {
            // Eliminamos el usuario
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la ruta #" . $id);
        } catch (\Exception $e) {
            if ($e->getCode() === 23000) {
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación de la ruta.');

                header("HTTP/1.0 400 Relation Restriction");
            } else {
                throw $e;
            }
        }
        return $this->redirect(['index']);
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

        $objPHPExcel->getActiveSheet()->getStyle('Q1:AE1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('Q1:AE1')->getFill()->getStartColor()->setRGB('000000');
        $objPHPExcel->getActiveSheet()->getStyle('Q1:AE1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("Q1:AE1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('Q1:AE1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

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



        $row = 2;

        foreach ($ViewViajeEnvio as $item) {
            $model = Envio::findOne($item["envio_id"]);
            $ViewViajePaquete = ViewViaje::getReporteViajeAjax($item);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $ViewViajePaquete["folio"]);

            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $ViewViajePaquete["cliente_emisor"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $ViewViajePaquete["telefono_emisor"] . "/" . $ViewViajePaquete["telefono_movil"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $ViewViajePaquete["is_reenvio"] ? "SI" : "NO");

            if ($ViewViajePaquete["estado"] || $ViewViajePaquete["municipio"] || $ViewViajePaquete["direccion"] || $ViewViajePaquete["referencia"]) {
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, "Estado: " . $ViewViajePaquete["estado"] . ", Municipio  " . $ViewViajePaquete["municipio"] . ", Dirección  " . $ViewViajePaquete["direccion"] . " / Referencia: " . $ViewViajePaquete["referencia"]);
            }



            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $ViewViajePaquete["vendedor"]);


            $objPHPExcel->getActiveSheet()->getStyle('B' . $row . ':F' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $row . ':F' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('B' . $row . ':F' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $piezasTotal = 0;
            foreach ($model->envioDetalles as $key => $e_detalle) {
                if ($e_detalle->status !=  EnvioDetalle::STATUS_CANCELADO)
                    $piezasTotal =  $piezasTotal + $e_detalle->cantidad;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $piezasTotal);


            $objPHPExcel->getActiveSheet()->getStyle('G' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('G' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, round($ViewViajePaquete["peso_total_paquete"], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $ViewViajePaquete["peso_total"]);

            $objPHPExcel->getActiveSheet()->getStyle('W' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('W' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('W' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('X' . $row, $ViewViajePaquete["precio_libra_actual"]);
            $objPHPExcel->getActiveSheet()->getStyle('X' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('X' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('X' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));


            $valoracionTotal = 0;
            foreach ($model->envioDetalles as $key => $e_detalle) {
                $valoracionTotal =  $valoracionTotal + $e_detalle->valor_declarado;
            }


            $objPHPExcel->getActiveSheet()->setCellValue('Y' . $row, $valoracionTotal);
            $objPHPExcel->getActiveSheet()->setCellValue('Z' . $row, $ViewViajePaquete["seguro_total"]);

            $objPHPExcel->getActiveSheet()->getStyle('Z' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('Z' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('Z' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('AA' . $row, $ViewViajePaquete["costo_reenvio"] ? $ViewViajePaquete["costo_reenvio"] : 0);

            $objPHPExcel->getActiveSheet()->getStyle('AA' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('AA' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('AA' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('AB' . $row, $ViewViajePaquete["subtotal"]);
            $objPHPExcel->getActiveSheet()->setCellValue('AC' . $row, $ViewViajePaquete["impuesto"]);

            $objPHPExcel->getActiveSheet()->getStyle('AC' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('AC' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('AC' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));



            $cobroTotal = 0;
            foreach ($model->cobroRembolsoEnvios as $key => $cobro) {
                if ($cobro->tipo == CobroRembolsoEnvio::TIPO_DEVOLUCION)
                    $cobroTotal =  $cobroTotal - $cobro->cantidad;
                else
                    $cobroTotal =  $cobroTotal + $cobro->cantidad;
            }

            $objPHPExcel->getActiveSheet()->setCellValue('AD' . $row, $cobroTotal);

            $objPHPExcel->getActiveSheet()->getStyle('AD' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('AD' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('AD' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('AE' . $row, $ViewViajePaquete["total"]);

            $objPHPExcel->getActiveSheet()->getStyle('AE' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('AE' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('AE' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));


            $objPHPExcel->getActiveSheet()->setCellValue('AF' . $row, Esys::fecha_en_texto($ViewViajePaquete["created_at"], true));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':AF' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));

            $ViewViajePaqueteDetalle = ViewViaje::getReporteViajePaqueteDetalle($item, Yii::$app->request->get());

            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, "Tracked");
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, "S. receptor");
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, "C. receptor");
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, "C. telefono");
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, "Ruta");
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, "V. D.");
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, "Elementos");
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, "Peso");
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, "Observacion");


            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, "Reenvio");
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, "Estado");
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, "Municipio");
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, "Direccion");
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, "Referencias");


            $row++;

            foreach ($ViewViajePaqueteDetalle as $key => $paquete) {

                $paqueteDireccion = ViewViaje::getReporteViajePaqueteDetalleDireccion($paquete);
                $color = '000000';

                if ($paquete['status'] ==  EnvioDetalle::STATUS_CANCELADO)
                    $color = '800606';


                $objPHPExcel->getActiveSheet()->getStyle('H' . $row . ':U' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('H' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB($color);
                $objPHPExcel->getActiveSheet()->getStyle('H' . $row . ':U' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $paquete["tracked"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $paquete["sucursal_receptor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $paquete["cliente_receptor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $paquete["telefono_cliente"] . " / " . $paquete["telefono_movil"]);

                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $paquete["nombre_ruta"]);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $paquete["valor_declarado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $paquete["cantidad_piezas"]);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, round($paquete["peso"] / $paquete["cantidad"], 2));
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $paquete["observaciones"]);

                if (isset($paqueteDireccion["id"])) {
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, "SI");
                    $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $paqueteDireccion["estado"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $paqueteDireccion["municipio"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $paqueteDireccion["direccion"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $paqueteDireccion["referencia"]);
                } else
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, "NO");



                $row++;
            }

            $row++;
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Viaje_" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }

    /**
     * =======================================================
     * 
     * 
     * ========================================================
     */
    public function actionReporteViajeVerificacionAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        $ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());

        $sheet = 0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);

        $objPHPExcel->getActiveSheet()->setTitle('Verificación de paquetes');
        $ViewViajeEnvio = ViewViaje::getReporteViajeEnvio(Yii::$app->request->get());






        $row = 1;

        foreach ($ViewViajeEnvio as $item) {
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':E' . $row);

            $model = Envio::findOne($item["envio_id"]);

            $ViewViajePaquete = ViewViaje::getReporteViajeAjax($item);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $ViewViajePaquete["folio"]);

            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(30);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('727576');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $ViewViajePaqueteDetalle = ViewViaje::getReportePaqueteDetalleAll($item, Yii::$app->request->get());

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $ViewViajePaquete["folio"]);

            $row = $row + 1;

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Tracked");
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, "Peso (USA)");
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, "Peso (MX)");
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, "Viaje ID");
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, "Movimiento");
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ));


            $row++;
            //
            foreach ($ViewViajePaqueteDetalle as $key => $paquete) {
                //$model_envio = EnvioDetalle::findOne($paquete["id"]);
                $model_envio = DetailEnvioProduct::find()
                    ->where(['detalle_envio_id' => $paquete['id']])
                    ->one();
                
                $productos = $model_envio ? json_decode($model_envio->detalle_json, true) : null;

                $array = explode('/', $paquete['tracked']);
                $pos = $array[1] - 1;


                //echo "<pre>";
                //print_r($productos);
                //die;

                $color      = '000000';
                $viaje_id   =  isset(Yii::$app->request->get()['viaje_id']) ? Yii::$app->request->get()['viaje_id'] : null;
                if ($paquete['viaje_id'] !=  $viaje_id)
                    $color = '800606';
                if ($paquete['viaje_id'] ==  $viaje_id) {
                    $peso_usa = round($paquete["peso"] / $paquete["cantidad"], 2);
                    $peso_mx  = round($paquete["peso_mx"], 2);
                    $color = 'd6c70e';

                    if ($peso_mx  >=  ($peso_usa - 30)   &&  ($peso_usa + 30) >= $peso_mx)
                        $color      = '000000';
                }

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . $row)->getFill()->getStartColor()->setRGB($color);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));
                $is_producto = intval($paquete['is_producto']);

                //$model_envio = EnvioDetalle::findOne($paquete["id"]);
                //$productos = $model_envio->detalleProducto ? json_decode($item->detalleProducto->detalle_json, true) : null;


                try{
                    $peso = $productos ? $productos[$pos]['peso_max'] : round($paquete["peso"] / $paquete["cantidad"], 2);
                }catch(\Throwable  $e ){
                    $peso = 'Update failed';
                }
                
                //print_r($peso);
                //die;

                //$peso = round($paquete["peso"] / $paquete["cantidad"],2);

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $paquete["tracked"]);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $is_producto == 30 ? "Sin límite" : $peso);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $is_producto == 30 ? "Sin límite" : round($paquete["peso_mx"], 2));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $paquete["viaje_id"]);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, MovimientoPaquete::$tipoLaxTierList[$paquete["tipo_movimiento_top"]]);
                #
                $row++;
            }

            $row++;
        }





        header('Content-Type: application/vnd.ms-excel');

        $filename = "Verificacion_paquetes_" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }


    public function actionReporteViajeConcilacionAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());

        $sheet = 0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);


        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setRGB('000000');
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:J1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);



        $objPHPExcel->getActiveSheet()->setTitle('REPORTE DE CONCILACION')

            ->setCellValue('A1', 'TRACKING')
            ->setCellValue('B1', 'PRODUCTO')
            ->setCellValue('C1', 'SUCURSAL QUE ENVIA')
            ->setCellValue('D1', 'SUCURSAL QUE RECIBE')
            ->setCellValue('E1', 'PESO U.S.A')
            ->setCellValue('F1', 'PESO M.X')
            ->setCellValue('G1', 'ESTADO')
            ->setCellValue('H1', 'MUNICIPIO')
            ->setCellValue('I1', 'OBSERVACION')
            ->setCellValue('J1', 'ESTATUS');

        $ViewViajeConcilacion = ViewViaje::getReporteViajeConcilacion(Yii::$app->request->get());

        $row = 2;

        foreach ($ViewViajeConcilacion as $item) {

            if ($item['tipo_movimiento_top'] == MovimientoPaquete::LAX_TIER_TRANSCURSO) {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->getFill()->getStartColor()->setRGB('A40D0D');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));
            }

            if ($item['tipo_movimiento_top'] != MovimientoPaquete::LAX_TIER_TRANSCURSO) {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->getFill()->getStartColor()->setRGB('009564');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item["tracked"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $item["nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item["sucursal_emisor_nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $item["sucursal_receptor_nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, round($item["peso"] / $item["cantidad"], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, round($item["peso_reparto"], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $item["estado"]);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $item["municipio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $item["observaciones"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, MovimientoPaquete::$tipoLaxTierList[$item["tipo_movimiento_top"]]);
            $row++;
        }


        /** SE TOMARA EL ENVIO MAS PEQUEÑO DEL TRAILER  Y EL ENVIO REGISTRADO EN EL VIAJE */
        $ViewSinViajeConcilacion = ViewViaje::getPaquetesSinViaje(Yii::$app->request->get());

        $ViajeGet = Viaje::find()->andWhere(["id" => Yii::$app->request->get()["viaje_id"]])->one();

        $row = $row + 2;

        foreach ($ViewSinViajeConcilacion as $item) {

            $is_true = true;

            // VALIDAMOS QUE EL PAQUETE NO TENGA MAS DE 20 DIAS EN ESE MOVIMIENTO PARA EVITAR PAQUETES MUY ATRAZADOS.
            if ($ViajeGet) {

                $date1 = new \DateTime(date("Y-m-d", $ViajeGet->created_at));
                $date2 = new \DateTime(date("Y-m-d", $item["created_at"]));
                $diff = $date1->diff($date2);

                if ($diff->days > 20)
                    $is_true = false;
            }

            if ($is_true) {

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':K' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':K' . $row)->getFill()->getStartColor()->setRGB('DC7D00');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':K' . $row)->applyFromArray(array(
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));


                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item["tracked"]);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $item["nombre"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item["valor_declarado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, round($item["peso"] / $item["cantidad"], 2));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, round($item["mov_peso_mx"], 2));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $item["observaciones"]);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $item["sucursal_receptor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $item["estado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $item["municipio"]);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $item["direccion"]);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, MovimientoPaquete::$tipoLaxTierList[$item["tipo_movimiento_top"]]);

                $row++;
            }
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Concilacion" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }

    public function actionImprimirReetiquetasPdf($viaje_id)
    {
        $model = ViewViaje::getReporteReimpresion(Yii::$app->request->get());

        $ViajePaquete       = [];



        foreach ($model as $key => $item) {
            $is_val                     = false;
            $item["count_item_show"]            = 0;
            $item["paquetes_envio_sucursal"]    = 0;
            $count_item                 = 0;
            $count_sucursal             = 0;

            foreach ($ViajePaquete as $key => $seach) {
                if ($seach["sucursal_id"] == $item["sucursal_id"] && $item["envio_id"]  ==  $seach["envio_id"])
                    $count_item = intval($seach["count_item_show"]);
            }

            foreach ($model as $key => $sucursal) {
                if ($sucursal["sucursal_id"] == $item["sucursal_id"] && $sucursal["envio_id"]  ==  $item["envio_id"]) {
                    $count_sucursal = $count_sucursal + 1;
                }
            }
            $item["count_item_show"] =  $count_item + 1;
            $item["paquetes_envio_sucursal"] =  $count_sucursal;
            array_push($ViajePaquete, $item);
        }


        $content = "";

        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array(105, 150), //Pdf::FORMAT_LETTER,
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
        foreach ($ViajePaquete as $key => $item) {

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

    public function actionReporteViajeReetiquetasAjax()
    {

        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        //$ViewViaje = ViewViaje::getReporteViajeAjax(Yii::$app->request->get());

        $sheet = 0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);

        /*$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->getStartColor()->setRGB('800000');
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));*/

        // Add some data
        /*
        $objPHPExcel->getActiveSheet()->getStyle("A1:H1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        */

        $ViewDescargaMapeo = ViewViaje::getReporteReimpresion(Yii::$app->request->get());

        $row = 1;


        foreach ($ViewDescargaMapeo as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item["tracked"]);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $item["ruta_nombre"]);


            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item["emisor_nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $item["emisor_telefono"] . "/" . $item["emisor_telefono_movil"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $item["receptor_nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $item["receptor_telefono"] . "/" . $item["receptor_telefono_movil"]);

            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $item["producto_nombre"]);

            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $item["sucursal_receptor"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $item["cantidad"]);

            /*
            $objPHPExcel->getActiveSheet()->getStyle('C'.$row.':G'.$row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ));
            */

            $row++;
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-carga-unidades_" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }

    public function actionReporteViajeJulioAjax()
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
            $ViewViajePaqueteDetalle = ViewViaje::getReporteViajePaqueteDetalle($item, Yii::$app->request->get());

            foreach ($ViewViajePaqueteDetalle as $key => $paquete) {

                $paqueteDireccion = ViewViaje::getReporteViajePaqueteDetalleDireccion($paquete);

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $ViewViajePaquete["folio"]);

                $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $ViewViajePaquete["cliente_emisor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $ViewViajePaquete["telefono_emisor"] . "/" . $ViewViajePaquete["telefono_movil"]);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $ViewViajePaquete["is_reenvio"] ? "SI" : "NO");

                if ($ViewViajePaquete["estado"] || $ViewViajePaquete["municipio"] || $ViewViajePaquete["direccion"] || $ViewViajePaquete["referencia"]) {
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, "Estado: " . $ViewViajePaquete["estado"] . ", Municipio  " . $ViewViajePaquete["municipio"] . ", Dirección  " . $ViewViajePaquete["direccion"] . " / Referencia: " . $ViewViajePaquete["referencia"]);
                }



                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $ViewViajePaquete["vendedor"]);


                $objPHPExcel->getActiveSheet()->getStyle('B' . $row . ':F' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('B' . $row . ':F' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('B' . $row . ':F' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $piezasTotal = 0;
                foreach ($model->envioDetalles as $key => $e_detalle) {
                    $piezasTotal =  $piezasTotal + $e_detalle->cantidad;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $piezasTotal);


                $objPHPExcel->getActiveSheet()->getStyle('G' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('G' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, round($ViewViajePaquete["peso_total_paquete"], 2));
                $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $ViewViajePaquete["peso_total"]);

                $objPHPExcel->getActiveSheet()->getStyle('W' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('W' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('W' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('X' . $row, $ViewViajePaquete["precio_libra_actual"]);
                $objPHPExcel->getActiveSheet()->getStyle('X' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('X' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('X' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));


                $valoracionTotal = 0;
                foreach ($model->envioDetalles as $key => $e_detalle) {
                    $valoracionTotal =  $valoracionTotal + $e_detalle->valor_declarado;
                }


                $objPHPExcel->getActiveSheet()->setCellValue('Y' . $row, $valoracionTotal);
                $objPHPExcel->getActiveSheet()->setCellValue('Z' . $row, $ViewViajePaquete["seguro_total"]);

                $objPHPExcel->getActiveSheet()->getStyle('Z' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('Z' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('Z' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('AA' . $row, $ViewViajePaquete["costo_reenvio"] ? $ViewViajePaquete["costo_reenvio"] : 0);

                $objPHPExcel->getActiveSheet()->getStyle('AA' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('AA' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('AA' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('AB' . $row, $ViewViajePaquete["subtotal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('AC' . $row, $ViewViajePaquete["impuesto"]);

                $objPHPExcel->getActiveSheet()->getStyle('AC' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('AC' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('AC' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));



                $cobroTotal = 0;
                foreach ($model->cobroRembolsoEnvios as $key => $cobro) {
                    if ($cobro->tipo == CobroRembolsoEnvio::TIPO_DEVOLUCION)
                        $cobroTotal =  $cobroTotal - $cobro->cantidad;
                    else
                        $cobroTotal =  $cobroTotal + $cobro->cantidad;
                }

                $objPHPExcel->getActiveSheet()->setCellValue('AD' . $row, $cobroTotal);

                $objPHPExcel->getActiveSheet()->getStyle('AD' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('AD' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('AD' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('AE' . $row, $ViewViajePaquete["total"]);

                $objPHPExcel->getActiveSheet()->getStyle('AE' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('AE' . $row)->getFill()->getStartColor()->setRGB('727576');
                $objPHPExcel->getActiveSheet()->getStyle('AE' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));


                $objPHPExcel->getActiveSheet()->setCellValue('AF' . $row, Esys::fecha_en_texto($ViewViajePaquete["created_at"], true));

                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Z' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                ));


                $color = '000000';
                if ($paquete['status'] ==  EnvioDetalle::STATUS_CANCELADO)
                    $color = '800606';

                $objPHPExcel->getActiveSheet()->getStyle('H' . $row . ':U' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('H' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB($color);
                $objPHPExcel->getActiveSheet()->getStyle('H' . $row . ':U' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => 'FFFFFF'),
                    )
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $paquete["tracked"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $paquete["sucursal_receptor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $paquete["cliente_receptor"]);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $paquete["telefono_cliente"] . " / " . $paquete["telefono_movil"]);

                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $paquete["nombre_ruta"]);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $paquete["valor_declarado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $paquete["cantidad_piezas"]);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $paquete["peso"]);
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $paquete["observaciones"]);

                if (isset($paqueteDireccion["id"])) {
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, "SI");
                    $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $paqueteDireccion["estado"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $paqueteDireccion["municipio"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $paqueteDireccion["direccion"]);
                    $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $paqueteDireccion["referencia"]);
                } else
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, "NO");


                $row++;
            }

            $row++;
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Julio_" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }

    public function actionReporteViajeCheckListAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();


        $sheet = 0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);



        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFill()->getStartColor()->setRGB('000000');
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:E1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);



        $objPHPExcel->getActiveSheet()->setTitle('Reporte de CheckList')

            ->setCellValue('A1', '#')
            ->setCellValue('B1', 'Folio de envio')
            ->setCellValue('C1', 'Tracked')
            ->setCellValue('D1', 'Check')
            ->setCellValue('E1', 'Fecha realizo pago');


        $envio_ini = Yii::$app->request->get('envio_ini');
        $envio_fin = Yii::$app->request->get('envio_fin');
        $viaje_id = Yii::$app->request->get('viaje_id');

        $ViewValidaEnvios = ViewViaje::getValidaEnviosInfo($envio_ini, $envio_fin, Envio::TIPO_ENVIO_TIERRA);

        $row = 2;
        $count_remove = 1;
        foreach ($ViewValidaEnvios["message"] as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $count_remove);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $item["folio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item["tracked"]);


            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);

            if (ViewViaje::getSearchTrackedEnvio($item["tracked"], $item["paquete_id"], $viaje_id)) {
                $objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFill()->getStartColor()->setRGB('59A739');
            } else {
                $objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFill()->getStartColor()->setRGB('E02F20');
            }

            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $item["fecha_pago"] ? Esys::fecha_en_texto($item["fecha_pago"]) : '');

            /*
            $objPHPExcel->getActiveSheet()->getStyle('C'.$row.':G'.$row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ));
            */
            $count_remove++;
            $row++;
        }

        $row = $row + 2;




        $ViewPaquetes = ViewViaje::getPaquetesViaje($viaje_id);
        $count_add = 1;
        foreach ($ViewPaquetes["message"] as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $count_add);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $item["folio"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item["tracked"]);

            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);


            $objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFill()->getStartColor()->setRGB('59A739');

            $count_add++;
            $row++;
        }



        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-check-list_" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }

    public function actionReporteViajeCargaAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();


        $sheet = 0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);



        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->getStartColor()->setRGB('000000');
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A1:F1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);



        $objPHPExcel->getActiveSheet()->setTitle('Reporte de CheckList')

            ->setCellValue('A1', '#')
            ->setCellValue('B1', 'Tracked')
            ->setCellValue('C1', 'Movimiento')
            ->setCellValue('D1', 'Monto Pagado')
            ->setCellValue('E1', 'Monto Total')
            ->setCellValue('F1', 'Denegado');


        $envio_ini = Yii::$app->request->get('envio_ini');
        $envio_fin = Yii::$app->request->get('envio_fin');
        $viaje_id = Yii::$app->request->get('viaje_id');

        $viaje      = Viaje::findOne($viaje_id);
        $optEtapa   = '';
        $optEtapa   = $viaje->etapa_1 == Viaje::ETAPA_ENABLE ? 'ETAPA_UNO' : $optEtapa;
        $optEtapa   = $viaje->etapa_2 == Viaje::ETAPA_ENABLE ? 'ETAPA_DOS' : $optEtapa;
        $optEtapa   = $viaje->etapa_3 == Viaje::ETAPA_ENABLE ? 'ETAPA_TRES' : $optEtapa;
        $optEtapa   = $viaje->etapa_4 == Viaje::ETAPA_ENABLE ? 'ETAPA_CUATRO' : $optEtapa;

        $ViewValidaEnvios = ViewViaje::getReporteCarga($viaje_id);

        $row = 2;
        $count_item = 1;

        foreach ($ViewValidaEnvios as $item) {
            if (($item["viaje_id"] ==  NULL && $item["tipo_movimiento"] == MovimientoPaquete::LAX_TIER_SUCURSAL) || ($item["viaje_id"] !=  NULL)) {
                if ($item["tipo_movimiento"] != MovimientoPaquete::LAX_TIER_SUCURSAL) {
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':F' . $row)->getFill()->getStartColor()->setRGB('59A739');
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $count_item);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $item["tracked"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, MovimientoPaquete::$tipoLaxTierList[$item["tipo_movimiento"]]);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $item["total_pagado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $item["total"]);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $item["is_denegado"] > 0 ? 'DENEGADO' : '');
                $count_item++;
                $row++;
            }
        }



        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-check-list_" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }

    public function actionReporteAdministracion($viaje_id)
    {

        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        $sheet = 0;
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);

        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(40);

        $Viaje = Viaje::findOne($viaje_id);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'TRAILER #' . $Viaje->num_viaje);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:D2');
        $objPHPExcel->getActiveSheet()->setCellValue('E2', Esys::fecha_en_texto($Viaje->created_at));
        $objPHPExcel->getActiveSheet()->mergeCells('E2:O2');

        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getFill()->getStartColor()->setRGB('E69138');
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        $objPHPExcel->getActiveSheet()->getStyle("A2:O2")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $row = 3;

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->getStartColor()->setRGB('E69138');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A" . $row . ":O" . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setTitle('REPORTE ADMINISTRACION')
            ->setCellValue('A' . $row, 'TRACKING #')
            ->setCellValue('B' . $row, 'SUCURSAL QUE ENVIA')
            ->setCellValue('C' . $row, 'SUCURSAL QUE RECIBE')
            ->setCellValue('D' . $row, 'CLIENTE QUE RECIBE')
            ->setCellValue('E' . $row, 'TELEFONO QUE RECIBE')
            ->setCellValue('F' . $row, 'CODIGO POSTAL')
            ->setCellValue('G' . $row, 'ESTADO')
            ->setCellValue('H' . $row, 'MUNICIPIO')
            ->setCellValue('I' . $row, 'DIRECCION')
            ->setCellValue('J' . $row, '# TOTAL DE PAQUETES')
            ->setCellValue('K' . $row, '# PAQUETES EN TRAILER')
            ->setCellValue('L' . $row, 'DESCRIPCION')
            ->setCellValue('M' . $row, 'PESO U.S.A')
            ->setCellValue('N' . $row, 'PESO M.X')
            ->setCellValue('O' . $row, 'ESTATUS');


        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);
        /****************************************************************************
                                        BODY
        /****************************************************************************/

        $ReporteFinanciero  = ViewViaje::getReporteFinanciero($viaje_id);
        $row = 4;


        /****************************************************************************
                                        INDEX PAGE 1
        /***************************************************************************/
        foreach ($ReporteFinanciero as $key => $r_Financiero) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':K' . $row)->applyFromArray(array(
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

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $r_Financiero["tracked"]);

            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $r_Financiero["sucursal_nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $r_Financiero["sucursal_receptor_nombre"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $r_Financiero["nombre_receptor"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $r_Financiero["telefono_movil"]);

            //$objPHPExcel->getActiveSheet()->setCellValue('C'.$row, DescargaBodega::$descargaList[$r_Financiero["bodega_descarga"]] );

            if ($r_Financiero["sucursal_recibe_is_reenvio"] == 10) {
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $r_Financiero["code_postal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $r_Financiero["estado"]);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $r_Financiero["municipio"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $r_Financiero["direccion"]);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $r_Financiero["code_sucursal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $r_Financiero["estado_sucursal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $r_Financiero["municipio_sucursal"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $r_Financiero["direccion_sucursal"]);
            }


            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $r_Financiero["cantidad"]);

            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $r_Financiero["paquetes_trailer"]);

            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $r_Financiero["observaciones"]);

            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, round($r_Financiero["peso"] / $r_Financiero["cantidad_paquete"], 2));

            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $r_Financiero["peso_unitario_mx"]);

            $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, MovimientoPaquete::$tipoLaxTierList[$r_Financiero["tipo_movimiento_top"]]);

            $row++;
        }

        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Administracion_" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');
    }

    public function actionReporteEntrada($viaje_id)
    {

        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        $sheet = 0;
        $objPHPExcel->setActiveSheetIndex($sheet);


        /*********************************************
                            STYLES
        /**********************************************/
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);

        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(40);

        $Viaje = Viaje::findOne($viaje_id);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'TRAILER #' . $Viaje->num_viaje . " REPORTE DE ENTRADA");
        $objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
        $objPHPExcel->getActiveSheet()->setCellValue('F2', Esys::fecha_en_texto($Viaje->created_at));
        $objPHPExcel->getActiveSheet()->mergeCells('F2:O2');

        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getFill()->getStartColor()->setRGB('E69138');
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        $objPHPExcel->getActiveSheet()->getStyle("A2:O2")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $row = 3;

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->getStartColor()->setRGB('E69138');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A" . $row . ":O" . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setTitle('REPORTE DE ENTRADA')
            ->setCellValue('A' . $row, 'TRACKING #')
            ->setCellValue('B' . $row, 'SUCURSAL ENVÍA')
            ->setCellValue('C' . $row, 'CLIENTE R.')
            ->setCellValue('D' . $row, 'TELEFONO R.')
            ->setCellValue('E' . $row, 'BODEGA DESCARGA')
            ->setCellValue('F' . $row, 'SUCURSAL RECIBE')
            ->setCellValue('G' . $row, 'CODIGO POSTAL')
            ->setCellValue('H' . $row, 'ESTADO')
            ->setCellValue('I' . $row, 'MUNICIPIO')
            ->setCellValue('J' . $row, 'DIRECCION')
            ->setCellValue('K' . $row, 'TOTAL DE PAQUETES')
            ->setCellValue('L' . $row, 'PAQUETES EN TRAILER')
            ->setCellValue('M' . $row, 'PESO U.S.A')
            ->setCellValue('N' . $row, 'PESO M.X')
            ->setCellValue('O' . $row, 'ESTATUS');


        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);
        /****************************************************************************
                                        BODY
        /****************************************************************************/

        $ReporteFinanciero  = ViewViaje::getReporteFinanciero($viaje_id);
        $row = 4;



        /****************************************************************************
                                        INDEX PAGE 1
        /***************************************************************************/
        foreach ($ReporteFinanciero as $key => $r_Financiero) {
            if ($r_Financiero["sucursal_recibe_is_reenvio"] != 10) {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->applyFromArray(array(
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

                if ($r_Financiero["tipo_movimiento_top"] == MovimientoPaquete::LAX_TIER_TRANSCURSO) {
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->getStartColor()->setRGB('C70039');
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $r_Financiero["tracked"]);

                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $r_Financiero["sucursal_nombre"]);

                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $r_Financiero["nombre_receptor"]);

                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $r_Financiero["telefono_movil"]);

                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, DescargaBodega::$descargaList[$r_Financiero["bodega_descarga"]]);

                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $r_Financiero["sucursal_receptor_nombre"]);

                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $r_Financiero["code_sucursal"]);

                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $r_Financiero["estado_sucursal"]);

                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $r_Financiero["municipio_sucursal"]);

                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $r_Financiero["direccion_sucursal"]);

                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $r_Financiero["cantidad"]);

                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $r_Financiero["paquetes_trailer"]);

                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, round($r_Financiero["peso"] / $r_Financiero["cantidad_paquete"], 2));

                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $r_Financiero["peso_unitario_mx"]);

                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, MovimientoPaquete::$tipoLaxTierList[$r_Financiero["tipo_movimiento_top"]]);

                $row++;
            }
        }


        /****************************************************************************
                                        INDEX PAGE MULTIPLES
        /***************************************************************************/
        $hojaNum = 1;
        $objPHPExcel->createSheet($hojaNum); //Setting index when creating
        $objPHPExcel->setActiveSheetIndex($hojaNum);
        $objPHPExcel->getActiveSheet()->setTitle("REENVIO");


        /*********************************************
                            STYLES
        /**********************************************/
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(40);


        $Viaje = Viaje::findOne($viaje_id);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'TRAILER #' . $Viaje->num_viaje . " REPORTE DE ENTRADA");
        $objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
        $objPHPExcel->getActiveSheet()->setCellValue('F2', Esys::fecha_en_texto($Viaje->created_at));
        $objPHPExcel->getActiveSheet()->mergeCells('F2:O2');

        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getFill()->getStartColor()->setRGB('E69138');
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        $objPHPExcel->getActiveSheet()->getStyle("A2:O2")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $row = 3;

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->getStartColor()->setRGB('E69138');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        // Add some data
        $objPHPExcel->getActiveSheet()->getStyle("A" . $row . ":O" . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()
            ->setCellValue('A' . $row, 'TRACKING #')
            ->setCellValue('B' . $row, 'SUCURSAL ENVÍA')
            ->setCellValue('C' . $row, 'CLIENTE R.')
            ->setCellValue('D' . $row, 'TELEFONO R.')
            ->setCellValue('E' . $row, 'BODEGA DESCARGA')
            ->setCellValue('F' . $row, 'SUCURSAL RECIBE')
            ->setCellValue('G' . $row, 'CODIGO POSTAL')
            ->setCellValue('H' . $row, 'ESTADO')
            ->setCellValue('I' . $row, 'MUNICIPIO')
            ->setCellValue('J' . $row, 'DIRECCION')
            ->setCellValue('K' . $row, 'TOTAL DE PAQUETES')
            ->setCellValue('L' . $row, 'PAQUETES EN TRAILER')
            ->setCellValue('M' . $row, 'PESO U.S.A')
            ->setCellValue('N' . $row, 'PESO M.X')
            ->setCellValue('O' . $row, 'ESTATUS');

        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);
        /****************************************************************************
                                        BODY
        /****************************************************************************/

        $ReporteFinanciero  = ViewViaje::getReporteFinanciero($viaje_id);
        $row = 4;



        /****************************************************************************
                                        INDEX PAGE 1
        /***************************************************************************/
        foreach ($ReporteFinanciero as $key => $r_Financiero) {
            if ($r_Financiero["sucursal_recibe_is_reenvio"] == 10) {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->applyFromArray(array(
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


                if ($r_Financiero["tipo_movimiento_top"] == MovimientoPaquete::LAX_TIER_TRANSCURSO) {
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()->getStartColor()->setRGB('C70039');
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $r_Financiero["tracked"]);

                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $r_Financiero["sucursal_nombre"]);

                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $r_Financiero["nombre_receptor"]);

                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $r_Financiero["telefono_movil"]);

                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, DescargaBodega::$descargaList[$r_Financiero["bodega_descarga"]]);

                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $r_Financiero["sucursal_receptor_nombre"]);

                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $r_Financiero["code_postal"]);

                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $r_Financiero["estado"]);

                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $r_Financiero["municipio"]);

                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $r_Financiero["direccion"]);

                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $r_Financiero["cantidad"]);

                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $r_Financiero["paquetes_trailer"]);

                $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, round($r_Financiero["peso"] / $r_Financiero["cantidad_paquete"], 2));

                $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $r_Financiero["peso_unitario_mx"]);

                $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, MovimientoPaquete::$tipoLaxTierList[$r_Financiero["tipo_movimiento_top"]]);

                $row++;
            }
        }

        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Entrada_" . date("d-m-Y-His") . ".xls";

        header('Content-Disposition: attachment;filename=' . $filename . ' ');
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
    public function actionViajesTierraJsonBtt()
    {
        return ViewViaje::getJsonBtt(Yii::$app->request->get());
    }

    public function actionSearchEnvioAjax()
    {
        return ViewEnvio::getJsonBtt(Yii::$app->request->get());
    }

    public function actionInfoEnvioAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            $envio_ini = Yii::$app->request->get('envio_ini');
            $envio_fin = Yii::$app->request->get('envio_fin');
            $EnvioIni = Envio::findOne($envio_ini);
            $EnvioFin = Envio::findOne($envio_fin);
            if ($EnvioIni->tipo_envio == $EnvioFin->tipo_envio) {

                $ViewViaje = ViewViaje::getValidaEnvios($EnvioIni->id, $EnvioFin->id, $EnvioIni->tipo_envio);

                return $ViewViaje;
            } else {
                return [
                    "code" => 10,
                    "message" => "Los envio deben corresponder al mismo servicio",
                ];
            }
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionPaqueteEnvioAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            $envio_ini = Yii::$app->request->get('envio_ini');
            $envio_fin = Yii::$app->request->get('envio_fin');
            $EnvioIni = Envio::findOne($envio_ini);
            $EnvioFin = Envio::findOne($envio_fin);
            if ($EnvioIni->tipo_envio == $EnvioFin->tipo_envio) {

                $ViewViaje = ViewViaje::getValidaEnviosInfo($EnvioIni->id, $EnvioFin->id, $EnvioIni->tipo_envio);

                return $ViewViaje;
            } else {
                return [
                    "code" => 10,
                    "message" => "Los envio deben corresponder al mismo servicio",
                ];
            }
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }



    public function actionDenegarPaquete($viaje_id, $tracked, $paquete_id)
    {
        if (isset($viaje_id) && isset($tracked) && isset($paquete_id)) {
            $ViajePaqueteDenegado = new ViajePaqueteDenegado();
            $ViajePaqueteDenegado->viaje_id = $viaje_id;
            $ViajePaqueteDenegado->tracked  = $tracked;
            $ViajePaqueteDenegado->paquete_id = $paquete_id;
            if ($ViajePaqueteDenegado->save()) {
                Yii::$app->session->setFlash('success', 'Se DENEGO el acceso al viaje #' . $ViajePaqueteDenegado->viaje_id . ' correctamente el paquete ' . $ViajePaqueteDenegado->tracked);
                return $this->redirect(['view', 'id' => $ViajePaqueteDenegado->viaje_id]);
            }
        }

        Yii::$app->session->setFlash('danger', 'Todos los datos son requeridos, verifica la información.');
        return $this->redirect(['index']);
    }

    public function actionAprobarPaquete($viaje_id, $tracked, $paquete_id)
    {
        if (isset($viaje_id) && isset($tracked) && isset($paquete_id)) {
            $ViajePaqueteDenegado = ViajePaqueteDenegado::find()->andWhere(["tracked" => $tracked])->andWhere(["viaje_id" => $viaje_id])->one();
            if ($ViajePaqueteDenegado->delete()) {
                Yii::$app->session->setFlash('success', 'Se APROBO el acceso al viaje #' . $ViajePaqueteDenegado->viaje_id . ' correctamente el paquete ' . $ViajePaqueteDenegado->tracked);
                return $this->redirect(['view', 'id' => $ViajePaqueteDenegado->viaje_id]);
            }

            Yii::$app->session->setFlash('danger', 'Todos los datos son requeridos, verifica la información.');
            return $this->redirect(['index']);
        }
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
