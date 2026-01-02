<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
?>

<div class="fade modal " id="modal-contacto-envio"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> Agregar un comentario / nota</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="panel">
                        <div class="panel-body">
                            <div id="error-add-paquete" class="has-error" style="display: none">
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <label class="control-label" for="tipo">Estatus : </label>
                                    <?=  Html::dropDownList('tipo_respuesta_id', null, EsysListaDesplegable::getItems('contacto_seguimiento_envio'), [ 'class' => 'form-control']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <label class="control-label" for="tipo">Nota / Comentario: </label>
                                    <?=  Html::textarea('comentario',null,[ 'class' => 'form-control','rows' => 6]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Guardar cambios', ['class' => 'btn btn-primary' , 'id' => 'send_contacto_seguimiendo']) ?>
            </div>
        </div>
    </div>
</div>

<script>

var $send_contacto_seguimiendo =  $("#send_contacto_seguimiendo"),
    $form_status            = $('select[name = "tipo_respuesta_id"]'),
    $form_comentario        = $('input[type=text],textarea,input[name="comentario"]'),
    $error_add_paquete      = $('#error-add-paquete'),
    $modal_contacto         = $('#modal-contacto-envio'),
    $envio_id = "<?=  $model->id ?>";

$(document).ready(function(){
    $send_contacto_seguimiendo.click(function(){
        if(validation_form_seguimiento()){
        return false;
        }

        $.post("<?= Url::to(['send-contacto-seguimiendo']) ?>",{ envio_id : $envio_id, tipo_respuesta_id : $form_status.val(), comentario:$form_comentario.val() },function(json){
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
            case !$form_status.val() :
                $error_add_paquete.append('<div class="help-block">* Selecciona el tipo</div>');
                $error_add_paquete.show();
                return true;
            break;

            case !$form_comentario.val() :
                $error_add_paquete.append('<div class="help-block">* Ingresa una Nota/Comentario</div>');
                $error_add_paquete.show();
                return true;
            break;


        }
    }
});
</script>
