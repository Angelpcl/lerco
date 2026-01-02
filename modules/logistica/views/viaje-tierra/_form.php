<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\date\DatePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\envio\Envio;

?>


<div class="logistica-viajes-tierra-form">
    <?php $form = ActiveForm::begin(['id' => 'form-viaje-tierra' ]) ?>
    <div class="row">
        <div class="col-lg-9">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información generales</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">

                            <?= $form->field($model, 'num_viaje')->textInput(['type' => 'number']) ?>

                            <?= $form->field($model, 'nombre_chofer')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'fecha_salida')->widget(DatePicker::classname(), [
                                    'options' => ['placeholder' => 'Fecha de salida'],
                                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                    'language' => 'es',
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ]
                                ]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'placas')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'transportista')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información extra / Comentarios</h5>
                </div>
                <div class="ibox-content">
                    <?= $form->field($model, 'nota')->textarea(['rows' => 6]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear viaje' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

