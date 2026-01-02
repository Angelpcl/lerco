<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
?>

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
                            <div class="text-center">
                                <h1>Monto total</h1>
                                <h3><?= "$" . number_format($total,2) ?></h3>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <?=  Html::label('Rembolso (Cantidad)','rembolso_envio'); ?>
                                    <?=  Html::input('number','rembolso_envio',null,[ 'class' => 'form-control','rows' => 6, 'id' => 'rembolso_envio']); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <label class="control-label" for="tipo">Nota / Comentario: </label>
                                    <?=  Html::textarea('comentario_rembolso',null,[ 'class' => 'form-control','rows' => 6, 'id' =>'comentario_rembolso']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Guardar rembolso', ['class' => 'btn btn-primary' , 'id' => 'send_rembolso']) ?>
            </div>
        </div>
    </div>
</div>

<script>

var $send_rembolso              =  $("#send_rembolso"),
    $form_rembolso_envio        = $('#rembolso_envio'),
    $form_comentario_rembolso   = $('#comentario_rembolso'),
    $error_add_paquete          = $('#error-add-paquete'),
    $modal_contacto            = $('#modal-renvio-envio'),
    $envio_id = "<?=  $folio->id ?>";

$(document).ready(function(){
    $send_rembolso.click(function(){
        if(validation_form_seguimiento()){
        return false;
        }

        $.post("<?= Url::to(['send-rembolso']) ?>",{ envio_id : $envio_id, rembolso : $form_rembolso_envio.val(), comentario : $form_comentario_rembolso.val() },function(json){
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
        });
    });

    var validation_form_seguimiento = function()
    {
        $error_add_paquete.html('');
        switch(true){
            case !$form_rembolso_envio.val() :
                $error_add_paquete.append('<div class="help-block">* Ingresa una cantidad a rembolsar</div>');
                $error_add_paquete.show();
                return true;
            break;

            case !$form_comentario_rembolso.val() :
                $error_add_paquete.append('<div class="help-block">* Ingresa una Nota/Comentario</div>');
                $error_add_paquete.show();
                return true;
            break;


        }
    }
});
</script>
