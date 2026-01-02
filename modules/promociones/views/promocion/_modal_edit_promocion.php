<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\esys\EsysSetting;
use kartik\date\DatePicker;
?>

<div class="fade modal " id="modal-promocion"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-gift mar-rgt-5px icon-lg"></i> Activa y Edita el periodo de la promoción</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="row">
                        <div class="DateRangePicker   kv-drp-dropdown  col-sm-6">
                            <?= Html::tag('p', "Selecciona la fecha que expira la promoción ",["class" => "text-main" ]) ?>
                            <?= DatePicker::widget([
                                'name'  => 'fecha_expira',
                                'id'    => 'fecha_expira',
                                'options' => ['placeholder' => 'Fecha fin'],
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'language' => 'es',
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ])
                            ?>
                        </div>
                        <div class="col-sm-6">
                            <br>
                            <?= Html::Button('Actualizar promoción', ['class' =>  'btn btn-warning btn-lg btn-block', 'id' => 'form-promocion-especial-send']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var $form_promocion_edit_send   = $('#form-promocion-especial-send'),
        $fecha_expira               = $('#fecha_expira'),
        $modal_promocion               = $('#modal-promocion')
        $promocion_id               = <?= $model->id ?>;

    $form_promocion_edit_send.click(function(){
        $.post('<?= Url::to('update-promocion-ajax') ?>',{ promocion_id: $promocion_id,fecha_expira: $fecha_expira.val() },function(json){
            if (json.code == 202) {
                $.niftyNoty({
                    type: "success",
                    container : "floating",
                    title : "Guardado",
                    message : json.message,
                    closeBtn : false,
                    timer : 5000
                });
            }else{
                if (json.code == 10) {
                    $.niftyNoty({
                        type: "danger",
                        container : "floating",
                        title : "Error",
                        message : json.message,
                        closeBtn : false,
                        timer : 5000
                    });
                }
            }
            $modal_promocion.modal('hide');
            window.location.href = "<?= Url::to('') ?>";
        });
    });
</script>
