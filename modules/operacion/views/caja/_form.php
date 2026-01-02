<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\esys\EsysListaDesplegable;
use app\models\caja\CajaMex;

?>

<div class="operacion-caja-form">

    <?php $form = ActiveForm::begin(['id' => 'form-caja' ]) ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información caja</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'categoria_id')->dropDownList(EsysListaDesplegable::getItems('categoria_paquete_mex'), ['prompt' => 'Selecciona la categoria']) ?>
                            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'peso_aprox')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'status')->dropDownList(CajaMex::$statusList) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información extra / Comentarios</h3>
                </div>
                <div class="panel-body">
                    <?= $form->field($model, 'nota')->textarea(['rows' => 6])->label(false) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear caja' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

