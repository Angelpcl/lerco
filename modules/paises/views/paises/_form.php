<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\envio\Envio;
use app\models\pago\PagoGasto;
?>

<div class="pagos-pago-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información Egreso (Cobro / Pago)</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'nombre')->textInput() ?>
                            <?= $form->field($model, 'codigo_iso')->textInput() ?>
                        </div>
                        <div class="col-md-6 text-center">
                        <?= $form->field($model, 'imagen_bandera')->fileInput(["class" => "form-control btn btn-default", 'accept' => 'image/*'])->label(false) ?>
                            <?php if (isset($model->imagen) && !empty($model->imagen)) : ?>
                                <?= Html::img('@web/uploads/flags/' . $model->imagen, ['alt' => 'Bandera', 'class' => 'img-flag', 'id' => 'img-avatar']) ?>
                            <?php else : ?>
                                <?= Html::img('@web/uploads/flags/default.jpeg', ['class' => 'img-flag', 'id' => 'img-avatar']) ?>
                            <?php endif; ?>
                        </div>
                   

                    </div>
                    <div class="row">
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? 'Crear país' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                            <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>

<script>
    $(document).ready(function() {
        $("#<?= Html::getInputId($model, 'imagen_bandera') ?>").change(function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#img-avatar').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>


<style>
    .img-flag {
        width: 150px;
        /* Ajusta según tus necesidades */
        height: 100px;
        /* Ajusta según tus necesidades */
        object-fit: cover;
        /* Mantiene la proporción y recorta si es necesario */
        border: 1px solid #ddd;
        /* Agrega un borde para mayor definición */
        border-radius: 5px;
        /* Bordes redondeados */
    }

    .text-center {
        text-align: center;
        /* Centra la imagen dentro del contenedor */
    }

    .panel {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        background-color: #fff;
    }

    .panel-body {
        padding: 15px;
    }

    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
    }
</style>