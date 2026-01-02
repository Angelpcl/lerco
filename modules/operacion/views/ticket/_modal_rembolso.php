<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\Esys;
use app\models\cobro\CobroRembolsoEnvio;

$total_rembolsar = 0;

foreach (json_decode($model->fecha_rembolso) as $key => $item){
    $total_rembolsar = $total_rembolsar + $item->monto;
}
$total_rembolsado = 0;

$CobroRembolsoEnvio = CobroRembolsoEnvio::getRembolso($folio->id);
foreach ($CobroRembolsoEnvio as $key => $item) {
    $total_rembolsado = $total_rembolsado + $item->cantidad;
}


?>
<style>
.style_div_pagado{
    background-repeat: no-repeat;
    background-size: cover;
    margin: 5;
    background-color: #570303;
}
</style>

<div class="fade modal " id="modal-renvio-envio"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> Rembolso / nota</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="panel">
                        <div class="panel-body">
                            <div id="error-add-paquete" class="has-error" style="display: none">
                            </div>


                            <div class="row totales cobros">
                                <div class="col-sm-4">
                                    <span class="label">Monto total</span>
                                    <span class="neto monto" style="    text-decoration: line-through;text-decoration-style: double;">$ <?= number_format($total,2) ?> USD</span>
                                </div>
                                <div class="col-sm-4">
                                    <span class="label">Total a rembolsar</span>
                                    <span class="impuestos monto">$ <?= number_format($total_rembolsar, 2) ?> USD</span>
                                </div>
                                <div class="col-sm-4">
                                    <span class="label">Rembolsado </span>
                                    <span class="total monto">$ <?= number_format($total_rembolsado, 2) ?> USD</span>
                                </div>
                            </div>
                            <?php foreach (json_decode($model->fecha_rembolso) as $key => $item): ?>
                                <?php
                                    $fecha  = new DateTime($item->fecha);
                                    $fecha2 = new DateTime(date('Y-m-d',time()));
                                    $diff   = $fecha->diff($fecha2);

                                   $is_rembolso     = false;
                                   $is_comentario   = "";

                                    foreach ($CobroRembolsoEnvio as $key => $cobro) {
                                        if ($cobro->ticket_item_id == $item->id ) {
                                            $is_rembolso    = true;
                                            $is_comentario  =  $cobro->nota;
                                        }
                                    }
                                 ?>

                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                            <em class="text-muted">Días para realizar rembolso : <strong id="text_day"><?=   $diff->days ?> días </strong></em>
                                        </div>
                                        <h3 class="panel-title">Fecha de cobro <?= Esys::fecha_en_texto(strtotime($item->fecha)) ?> </h3>
                                    </div>
                                    <div class="panel-body" class="<?= $is_rembolso ? 'style_div_pagado' : ''?>">
                                        <div class="row">
                                            <div class="col-sm-6 ">
                                                <?=  Html::label('Rembolso (Cantidad)','rembolso_envio'); ?>
                                                <?=  Html::input('number','rembolso_envio',$item->monto,[ 'class' => 'form-control','rows' => 6, 'id' => 'rembolso_envio_' . $item->id, 'readonly' => true]); ?>
                                            </div>
                                            <div class="col-sm-6 ">
                                                <label class="control-label" for="tipo">Nota / Comentario: </label>
                                                <?=  Html::textarea('comentario_rembolso',$is_rembolso ? $is_comentario : NULL,[ 'class' => 'form-control','rows' => 3, 'id' =>'comentario_rembolso_' . $item->id, 'disabled' => $is_rembolso ]); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php if ($item->fecha <= date('Y-m-d',time()) && !$is_rembolso ): ?>
                                        <?= Html::submitButton('Guardar rembolso', ['class' => 'btn btn-primary btn-lg btn-block' , 'id' => 'send_rembolso', "data-loading-text"=>"Cargando...", 'onclick' => 'send_rembolso_change(this,' . $item->id . ')']) ?>
                                    <?php else: ?>
                                        <?= Html::submitButton('Guardar rembolso', ['class' => 'btn btn-dark btn-lg btn-block' , 'id' => 'send_rembolso','disabled' => true]) ?>
                                    <?php endif ?>
                                </div>
                            <?php endforeach ?>
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

var $error_add_paquete          = $('#error-add-paquete'),
    $modal_contacto             = $('#modal-renvio-envio'),
    $text_day                   = $('#text_day'),
    $envio_id = "<?=  $folio->id ?>";


    var send_rembolso_change = function($elem,$item_id){


        var btn = $($elem).button('loading')
        // business logic...

        var doSomething = setTimeout(function(){
            clearTimeout(doSomething);
            btn.button('reset')
        }, 4000);


        $.post("<?= Url::to(['send-rembolso']) ?>",
            {
                envio_id : $envio_id,
                rembolso : $('#rembolso_envio_' + $item_id ).val(),
                comentario : $('#comentario_rembolso_' + $item_id).val(),
                item_id : $item_id,
            },function(json){
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
            $modal_contacto.modal('hide');
            window.location.href = "<?= Url::to('') ?>";
        });
    };

</script>
