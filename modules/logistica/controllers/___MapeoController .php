<?php
namespace app\modules\logistica\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\reparto\Reparto;
use app\models\reparto\ViewReparto;
use app\models\reparto\RepartoFila;
use app\models\ruta\FilaRuta;
use app\models\ruta\FilaPaquete;
use app\models\reparto\RepartoRecoleccion;

/**
 * Default controller for the `clientes` module
 */
class MapeoController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
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
        $model->reparto_fila        = new RepartoFila();
        $model->fila_ruta           = new FilaRuta();
        $model->recoleccion_ruta    = new RepartoRecoleccion();
        $model->fila_paquete        = new FilaPaquete();

        if ($model->reparto_fila->load(Yii::$app->request->post())) {
            $model->reparto_fila->reparto_id = $model->id;
            $model->reparto_fila->status = RepartoFila::STATUS_ACTIVE;
            if ($model->reparto_fila->save()) {
                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }elseif($model->fila_ruta->load(Yii::$app->request->post())){
            if ($model->fila_ruta->save_fila_ruta()) {
                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }elseif($model->fila_paquete->load(Yii::$app->request->post())){


            if ($model->fila_paquete->save_fila_paquete()) {
                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }
        elseif ($model->recoleccion_ruta->load(Yii::$app->request->post())) {
            if ($model->recoleccion_ruta->save_recoleccion_ruta()) {
                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }

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
        	$model->status = Reparto::STATUS_ACTIVE;
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
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación de la sucursal.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index']);
    }

     public function actionDeleteRuta($reparto_id,$fila_id,$ruta_id)
    {
        $model = FilaRuta::find()->andWhere(['and',["fila_id" => $fila_id], ["ruta_id" => $ruta_id]])->one();


        foreach ($model->filaPaquetes as $key => $paquete) {
            $paquete->delete();
        }

        try{
            // Eliminamos el usuario
            $model->delete();

            Yii::$app->session->setFlash('success', "Se ha removido correctamente la ruta #" . $ruta_id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden remover la ruta.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->redirect(['view','id' => $reparto_id]);
    }

    public function actionDeleteFila($reparto_id,$fila_id)
    {
        $model = RepartoFila::find()->andWhere(['and',["reparto_id" => $reparto_id], ["id" => $fila_id]])->one();

        foreach ($model->filaRutas as $key => $filaRutas) {
            foreach ($filaRutas->filaPaquetes as $key => $paquete) {
                $paquete->delete();
            }
            $filaRutas->delete();
        }
        //FilaRuta::deleteAll(["fila_id" => $model->id ]);

        //RepartoRecoleccion::deleteAll([ "reparto_id" => $this->fila->reparto->id , "ruta_id" => $this->ruta_id ]);
        try{
            // Eliminamos el usuario
            $model->delete();

            Yii::$app->session->setFlash('success', "Se ha removido correctamente la fila #" . $fila_id);

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden remover la fila.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }
        return $this->redirect(['view','id' => $reparto_id]);

    }

    public function actionReporteRepartoAjax()
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();
        $ViewReparto = ViewReparto::getReporteRepartoAjax(Yii::$app->request->get());

        $sheet=0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $row = 1;
        foreach ($ViewReparto as $fila) {

            //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$fila["fila"]);

            //$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);

            //$objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':A'.($row + 2) );

            /************************************************************************
                TITULO DE LA FILA
            /************************************************************************/
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':H'.($row + 2));


            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.($row + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.($row + 2))->getFill()->getStartColor()->setARGB('009688');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.($row + 2))->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.($row + 2))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.($row + 2))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            $row = $row + 3;

            /************************************************************************
                INFORMACIÓN DE LA FILA
            /************************************************************************/

            $objPHPExcel->getActiveSheet()->setCellValue('A'. $row ,"Chofer / Conductor");
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':D'.$row );
            $objPHPExcel->getActiveSheet()->setCellValue('E'. $row ,$fila["chofer"]);
            $objPHPExcel->getActiveSheet()->mergeCells('E'.$row.':H'.$row );
            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
            $row = $row + 1;


            $objPHPExcel->getActiveSheet()->setCellValue('A'. $row ,"Unidad / Camion");
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':D'.$row );
            $objPHPExcel->getActiveSheet()->setCellValue('E'. $row ,$fila["unidad"]);
            $objPHPExcel->getActiveSheet()->mergeCells('E'.$row.':H'.$row );
            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
            $row = $row + 1;



            $objPHPExcel->getActiveSheet()->setCellValue('A'. $row ,"Ruta");
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'.$row );
            $objPHPExcel->getActiveSheet()->setCellValue('C'. $row ,"Trakend");
            $objPHPExcel->getActiveSheet()->mergeCells('C'.$row.':E'.$row );
            $objPHPExcel->getActiveSheet()->setCellValue('F'. $row ,"Piezas");
            $objPHPExcel->getActiveSheet()->mergeCells('F'.$row.':H'.$row );


            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => '000'),
                )
            ));

            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->getFont()->setBold(true);


            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
            $row = $row + 1;

            $ViewRepartoPaquete = ViewReparto::getFilaPaqueteAjax(["reparto_fila_id" => $fila["id"] ]);

            /************************************************************************
                PAQUETES QUE PERTENCEN A LA FILA
            /************************************************************************/
            $total_piezas = 0;
            foreach ($ViewRepartoPaquete as $key => $paquete)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('A'. $row ,$paquete["nombre"]);
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'.$row );
                $objPHPExcel->getActiveSheet()->setCellValue('C'. $row ,$paquete["tracked"]);
                $objPHPExcel->getActiveSheet()->mergeCells('C'.$row.':E'.$row );
                $objPHPExcel->getActiveSheet()->setCellValue('F'. $row ,$paquete["cantidad_piezas"]);
                $objPHPExcel->getActiveSheet()->mergeCells('F'.$row.':H'.$row );

                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'font' => array(
                        'color' => array('rgb' => '000'),
                    )
                ));

                $total_piezas = $total_piezas + $paquete["cantidad_piezas"];
                $row = $row + 1;
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A'. $row ,"Total de piezas");
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row );
            $objPHPExcel->getActiveSheet()->setCellValue('F'. $row ,$total_piezas);
            $objPHPExcel->getActiveSheet()->mergeCells('F'.$row.':H'.$row );

            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => '000'),
                )
            ));
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':H'.$row)->getFont()->setBold(true);

            $row = $row + 2;

        }



        /*
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Filas')

        ->setCellValue('A1', 'FIlAS')
        ->setCellValue('B1', 'Nombre del cliente')
        ->setCellValue('C1', 'Tipo de cliente ID')
        ->setCellValue('D1', 'Tipo de cliente')
        ->setCellValue('E1', 'Agente de venta')
        ->setCellValue('F1', 'Asignado ID')
        ->setCellValue('G1', 'Fecha')
        ->setCellValue('H1', 'Hora')
        ->setCellValue('I1', 'Telefono al que marco')
        ->setCellValue('J1', 'Tipo de respuesta ID')
        ->setCellValue('K1', 'Tipo de respuesta')
        ->setCellValue('L1', 'Comentario');

*/



        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-Filas_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        //ini_set('display_errors', 1);

        $objWriter->save('php://output');
        //exit;
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


    public function actionRepartoRutaAsignarAjax(){
        return ViewReparto::getAsignarRepartoRuta(Yii::$app->request->get());
    }

    public function actionRecoleccionRutaAjax(){
        return ViewReparto::getSucursalRutaAjax(Yii::$app->request->get());
    }

    public function actionFilaPaqueteAjax(){
        return ViewReparto::getSucursalPaqueteAjax(Yii::$app->request->get());
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
