<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Esys;
use app\models\esys\EsysListaDesplegable;
 ?>
<div class="fade modal " id="modal-create-fila"  tabindex="-1" role="dialog" aria-labelledby="modal-create-fila-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Agregar fila a reparto <strong><span  class="label monto text-dark "> <?= Esys::fecha_en_texto($model->fecha_salida) ?> </span> </h4> </strong>
            </div>
            <!--Modal body-->
            <?php $formFila = ActiveForm::begin(['id' => 'form-fila' ]) ?>
            <div class="modal-body">
            	<div class="panel">
                    <?= $formFila->field($model->reparto_fila, 'nombre_id')->dropDownList(EsysListaDesplegable::getItems('fila_reparto'), ['prompt' => 'Selecciona la fila']) ?>
                    <?= $formFila->field($model->reparto_fila, 'chofer_id')->dropDownList(EsysListaDesplegable::getItems('chofer_unidad_reparto'), ['prompt' => 'Selecciona el chofer']) ?>
                    <?= $formFila->field($model->reparto_fila, 'num_camion_id')->dropDownList(EsysListaDesplegable::getItems('clave_unidad_reparto'), ['prompt' => 'Selecciona la unidad']) ?>
		        </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Agregar', ['class' =>  'btn btn-primary', 'id' => 'form-fila']) ?>
            </div>
			<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
