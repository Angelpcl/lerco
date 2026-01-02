<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use app\models\reparto\Reparto;
use app\models\esys\EsysListaDesplegable;
use app\models\viaje\Viaje;
use app\models\ruta\Ruta;
?>

<div class="logistica-reparto-form">
    <?php $form = ActiveForm::begin(['id' => 'form-reparto' ]) ?>
    <div class="row">
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información generales</h5>
                </div>
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'chofer_id')->dropDownList(EsysListaDesplegable::getItems('chofer_unidad_reparto'), ['prompt' => 'Selecciona el chofer']) ?>
                            <?= $form->field($model, 'num_unidad_id')->dropDownList(EsysListaDesplegable::getItems('clave_unidad_reparto'), ['prompt' => 'Selecciona la unidad']) ?>
                            <?= $form->field($model, 'status')->dropDownList(Reparto::$statusList) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'viaje_id')->dropDownList(Viaje::getViajeTranscursoTierra(),['prompt' => 'Selecciona un viaje'])->label('Viaje') ?>

                            <?= $form->field($model, 'ruta_id')->dropDownList(Ruta::getItemsAll(),['prompt' => 'Selecciona un ruta']) ?>

                            <div class="div_ruta_nombre" style="display: none">
                                <?= $form->field($model, 'ruta_nombre')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información extra / Comentarios</h5>
                </div>
                <div class="ibox-title">
                    <?= $form->field($model, 'nota')->textarea(['rows' => 6]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear reparto' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<script>
    var $reparto_ruta_id    = $('#reparto-ruta_id'),
        ruta_base           = <?= Ruta::TIPO_BASE ?>,
        ruta_foranea        = <?= Ruta::TIPO_FORANEA ?>,
        $div_ruta_nombre    = $('.div_ruta_nombre');

    $reparto_ruta_id.change(function(){
        $.get("<?= Url::to(['load-ruta']) ?>", { ruta_id : $(this).val() },function(rutaJson){
            if (rutaJson['ruta_tipo'] == ruta_foranea )
                $div_ruta_nombre.show();
            else
                $div_ruta_nombre.hide();

        },'json');

    });

</script>
