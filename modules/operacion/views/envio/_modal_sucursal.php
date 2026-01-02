<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\models\cobro\CobroRembolsoEnvio;
?>

<div class="fade modal inmodal " id="modal-sucursal"  tabindex="-1" role="dialog" aria-labelledby="modal-sucursal-label" style="z-index: 1000   !important;" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> Modificar sucursal</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div id="error-add-sucursal" class="has-error" style="display: none">
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="ibox">
                            <div class="ibox-content">
                                <?= Html::label('Sucursal que recibe:', 'sucursal_emisor_id') ?>
                                <?= Select2::widget([
                                    'id' => 'sucursal-sucursal_id',
                                    'name' => 'Sucursal[sucursal_id]',
                                    'language'  => 'es',
                                    'data'      => [],
                                    'pluginOptions' => [
                                        'allowClear'            => true,
                                        'minimumInputLength'    => 3,
                                        'language'   => [
                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                        ],
                                        'ajax' => [
                                            'url'      => Url::to(['sucursales-estado-ajax']),
                                            'dataType' => 'json',
                                            'cache'    => true,
                                            'processResults' => new JsExpression('function(data, params){ sucursal = data; return {results: data} }'),
                                        ],
                                    ],
                                    'options' => [
                                        'placeholder' => 'Selecciona la sucursal...',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Editar sucursal', ['class' => 'btn btn-primary' , 'id' => 'send_sucursal']) ?>
            </div>
        </div>
    </div>
</div>

<script>

var $error_add_paquete      = $('#error-add-sucursal'),
    $modal_sucursal         = $('#modal-sucursal'),
    $sucursal_id            = $('#sucursal-sucursal_id'),
    $send_sucursal          = $('#send_sucursal'),
    $paquete_id             = null;


$(document).ready(function(){
    $send_sucursal.click(function(){
        if ($sucursal_id.val()) {

            $.get("<?= Url::to(['sucursal-update-ajax']) ?>",{ paquete_id : $paquete_id ,sucursal_id : $sucursal_id.val() }, function($response){
                if ($response.code == 202 ) {
                    $.niftyNoty({
                        type: "success",
                        container : "floating",
                        title : "Guardado",
                        message : $response.message,
                        closeBtn : false,
                        timer : 5000
                    });

                }else{
                    $.niftyNoty({
                        type: "danger",
                        container : "floating",
                        title : "Error",
                        message : $response.message,
                        closeBtn : false,
                        timer : 5000
                    });
                }
                $modal_sucursal.modal('hide');
                window.location.href = "<?= Url::to(['view','id' => $model->id]) ?>";
            });
        }
    });
});

var init_model_sucursal = function($load_paquete_id){
    $paquete_id = $load_paquete_id;
}


</script>
