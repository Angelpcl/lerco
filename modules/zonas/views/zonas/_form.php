<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\pais\PaisesLatam;

?>

<div class="pagos-pago-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5> Nueva zona roja </h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-4">
                                    <?= $form->field($model, 'pais_id')->widget(Select2::classname(), [
                                        'data' => PaisesLatam::getPaises(),
                                        'options' => ['placeholder' => '--Seleccione--'],

                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'id' => 'select_pais'
                                        ],

                                    ]) ?>
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($model, 'code')->textInput() ?>
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($model, 'estado')->textInput() ?>
                                </div>
                            </div>
                            <div class="row text-center">

                            </div>
                            <div class="row text-center">
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= Html::submitButton($model->isNewRecord ? 'Crear' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                                        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
                                    </div>
                                </div>
                                <div class="col-md-4"></div>
                            </div>
                        </div>
                        <div class="col-md-4">

                            <?= Html::img('@web/uploads/flags/default.jpeg', ['class' => 'img-flag', 'id' => 'img-avatar']) ?>

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
        // Usa el id real del campo select2 para el evento change
        var selectId = '#<?= Html::getInputId($model, 'pais_id') ?>';

        // Escuchar el evento change del select2
        $(selectId).on('change', function() {
            // Obtener el valor seleccionado
            var selectedValue = $(this).val();

            // Si se seleccionó algo
            if (selectedValue) {
                $.get("<?= Url::to(['get-pais']) ?>", {
                    pais_id: selectedValue,
                }, function(response) {
                    console.log(response);
                    if (response.code == 10 && response.pais && response.pais.imagen) {
                        // Construir la URL de la imagen
                        var imageUrl = '<?= Yii::$app->request->baseUrl ?>/uploads/flags/' + response.pais.imagen;

                        // Actualizar la imagen en la vista
                        $('#img-avatar').attr('src', imageUrl);
                    } else {
                        // Manejar el caso en que no se recibe la imagen
                        $('#img-avatar').attr('src', '<?= Yii::$app->request->baseUrl ?>/uploads/flags/default.jpeg');
                    }
                }, 'json');
            } else {
                console.log("No se ha seleccionado ningún valor.");
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