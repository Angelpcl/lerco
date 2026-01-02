<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\cobro\CobroRembolsoEnvio;

?>

<div class="fade modal " id="modal-ajuste-cobrado"  tabindex="-1" role="dialog" aria-labelledby="modal-envio-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> Ajuste Manual</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="panel">
                        <div class="panel-body">
                            <div id="error-add-paquete" class="has-error" style="display: none">
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="text-center">
                                        <h1>Total</h1>
                                        <h3><?= "$" . number_format($model->total,2) ?></h3>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-center">
                                        <h1>Cobrado</h1>
                                        <h3><?= "$" . number_format($cobroTotal,2) ?></h3>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-center">
                                        <h1>Diferencia</h1>
                                        <h3 class="text-danger"><?= "$" . number_format($model->total - $cobroTotal,2) ?></h3>
                                    </div>
                                </div>
                            </div>

                            <?php foreach ($model->cobroRembolsoEnvios as $key => $item): ?>
                                <?php if ($item->tipo == CobroRembolsoEnvio::TIPO_COBRO ): ?>
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">Cobros relacionados</h3>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row text-center">
                                                <div class="col-sm-4">
                                                    <?= CobroRembolsoEnvio::$tipoList[$item->tipo] ?>
                                                </div>
                                                <div class="col-sm-4">
                                                    <?= CobroRembolsoEnvio::$servicioList[$item->metodo_pago] ?>
                                                </div>
                                                <div class="col-sm-4">
                                                    <?=  Html::input('number','rembolso_envio',$item->cantidad,[ 'class' => 'form-control text-center','rows' => 6, 'onchange' => 'changeCobrado(this,'. $item->id .')','id' => 'cobro_envio']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif ?>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Editar cobros', ['class' => 'btn btn-primary' , 'id' => 'send_cobro']) ?>
            </div>
        </div>
    </div>
</div>

<script>

var $send_cobro             =  $("#send_cobro"),

    $error_add_paquete      = $('#error-add-paquete'),
    $modal_cobro            = $('#modal-ajuste-cobrado'),
    $cobros_array           = [],
    $envio_id = "<?=  $model->id ?>";

$(document).ready(function(){
    $send_cobro.click(function(){
        if(validation_form_seguimiento()){
        return false;
        }

        $.post("<?= Url::to(['update-cobro-ajax']) ?>",{ cobros_array : $cobros_array },function(json){
            if (json.code == 10) {
                $.niftyNoty({
                    type: "success",
                    container : "floating",
                    title : "Guardado",
                    message : json.message,
                    closeBtn : false,
                    timer : 5000
                });
            }else{
                $.niftyNoty({
                    type: "danger",
                    container : "floating",
                    title : "Error",
                    message : json.message,
                    closeBtn : false,
                    timer : 5000
                });
            }

            $modal_cobro.modal('hide');
        });
    });


    var validation_form_seguimiento = function()
    {
        $error_add_paquete.html('');
        switch(true){
            case $cobros_array.length  ==  0:
                $error_add_paquete.append('<div class="help-block">* Debes modificar algun cobro para realizar un cambio</div>');
                $error_add_paquete.show();
                return true;
            break;
        }
    }
});

var changeCobrado = function($elem,$cobro_id){
    $is_update = false;
    $.each($cobros_array, function(key,item){
        if (item.id == $cobro_id) {
            item.monto = $($elem).val() ? $($elem).val() : 0;
            $is_update = true;
        }
    });

    if (!$is_update) {
        $cobro = {
            id      : $cobro_id,
            monto   : $($elem).val() ? $($elem).val() : 0,
        };
        $cobros_array.push($cobro);
    }


}
</script>
