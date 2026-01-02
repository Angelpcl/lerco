<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\models\ruta\Ruta;
?>

<div class="logistica-ruta-form">
    <?php $form = ActiveForm::begin(['id' => 'form-sucursal' ]) ?>
    <div class="row">
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información generales</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'status')->dropDownList(Ruta::$statusList) ?>
                            <?= $form->field($model, 'tipo')->dropDownList(Ruta::$tipoList) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'orden')->textInput(['type' => 'number']) ?>

                            <div class="content_alert_warning">

                            </div>



                            <div class="alert alert-success div_alert_message_success" style="display: none;">
                                <strong>
                                    <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">¡ Orden abierto! </font></font>
                                </strong>
                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Has seleccionado un lugar disponible.</font></font>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="panel-heading">
                    <h5 >Información extra / Comentarios</h5>
                </div>
                <div class="ibox-content">
                    <?= $form->field($model, 'nota')->textarea(['rows' => 6]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear ruta' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<div class="display-none">
    <div class="template_alert">
        <div class="alert alert-warning div_alert_message_warning" >
            <strong>
                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">¡ Orden utilizado por la ruta <h5>{{ruta_nombre}}</h5>! </font></font>
            </strong>
            <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Has seleccionado un lugar no disponible.</font></font>
        </div>
    </div>
</div>
<script>
    var $ruta_orden     = $('#ruta-orden'),
        $ruta_tipo      = $('#ruta-tipo'),
        $template_alert = $('.template_alert'),
        $content_alert_warning = $('.content_alert_warning');

    $ruta_orden.change(function(){
        $('.div_alert_message_success').hide();
        $content_alert_warning.html('');
        $.get("<?= Url::to(['valida-orden-ajax']) ?>",{orden: $(this).val(), tipo: $ruta_tipo.val() },function(json){
            if (json.length > 0) {
                $.each(json,function(key,ruta){
                    if (ruta) {
                        template_alert = $template_alert.html();
                        template_alert = template_alert.replace("{{ruta_nombre}}",ruta.nombre);
                        $content_alert_warning.append(template_alert);

                    }
                })

            }else
                $('.div_alert_message_success').show();
        });
    });
</script>
