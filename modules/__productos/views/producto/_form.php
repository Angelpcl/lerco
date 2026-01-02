<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\envio\Envio;
use app\models\producto\Producto;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysSetting;

?>

<div class="prductos-producto-form">
    <?php $form = ActiveForm::begin(['id' => 'form-promocion' ]) ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear producto' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
    </div>
    <div class="row">
        <div class="col-lg-10">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información de producto</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'unidad_medida_id')->dropDownList(EsysListaDesplegable::getItems('unidad_de_uso'), ['prompt' => '']) ?>

                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model, 'tipo_servicio')->dropDownList(Envio::$tipoList,['prompt' => '']) ?>

                            <?= $form->field($model, 'categoria_id')->dropDownList([], ['prompt' => '']) ?>

                        </div>
                    </div>
                    <?= $form->field($model, 'status')->dropDownList(Producto::$statusList) ?>
                    <?= $form->field($model, 'nota')->textArea(['rows' => 6 ]) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>




<script>
var $selectTipoServicio = $('select[name = "Producto[tipo_servicio]"]'),
    $selectCategoriaID  = $('select[name = "Producto[categoria_id]"]');


$(document).ready(function(){
    $selectTipoServicio.trigger('change');
});

$selectTipoServicio.change(function(){
    $.get("<?= Url::to(['categoria-ajax']) ?>",{  tipo_servicio : $(this).val() },function($categoria){
        $.each($categoria, function(key, value){
            $selectCategoriaID.append("<option value='" + value.id + "'>" + value.singular + "</option>\n");
        });

        $selectCategoriaID.val(<?= isset($model->categoria_id) && $model->categoria_id ? $model->categoria_id : 0  ?>).trigger('change');
    });
});

</script>
