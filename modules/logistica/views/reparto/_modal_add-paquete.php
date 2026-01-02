<?php
use yii\helpers\Url;
use yii\helpers\Html;

 ?>
<div class="fade modal inmodal " id="modal-add-paquete"  tabindex="-1" role="dialog" aria-labelledby="modal-add-paquete-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Agregar paquete a reparto  </strong>
            </div>
            <!--Modal body-->

            <div class="modal-body">
            	<div class="panel">
                    <div class="row">
                        <div class="col-sm-12">
                            <div style="display: none" id="alert_info_reparto">
                                <div class="alert alert-danger">
                                    <strong>Aviso!</strong><p id="message_info_reparto"></p>
                                </div>
                            </div>
                            <div style="display: none" id="alert_info_reparto_success">
                                <div class="alert alert-mint">
                                    <strong>Aviso!</strong><p id="message_info_reparto_success"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <?= Html::label("Tracked","tracked_id") ?>
                                <?= Html::input('text', 'tracked_id', null ,[ 'id' => 'tracked_id','class' => 'form-control']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <?= Html::submitButton('Agregar', ['class' =>  'btn btn-lg btn-block btn-primary', 'id' => 'form-add-paquete']) ?>
                            </div>
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
    var $btn_add_paquete      = $('#form-add-paquete'),
        $alert_info_reparto   = $('#alert_info_reparto'),
        $message_info_reparto = $('#message_info_reparto'),
        $message_info_reparto_success = $('#message_info_reparto_success'),
        $alert_info_reparto_success   = $('#alert_info_reparto_success'),
        $tracked_id         = $('#tracked_id');
        reparto_id          = <?= $model->id  ?>

    $btn_add_paquete.click(function(){
        if (!validation_form_envio()) {
            $.post("<?= Url::to(['reparto-add-paquete'])  ?>",{ tracked : $tracked_id.val(), reparto_id : reparto_id },function(json){
                if (json.code == 202) {
                    $message_info_reparto_success.html('');
                    $alert_info_reparto_success.show();
                    $message_info_reparto_success.append(json.message);
                }else{
                    $message_info_reparto.html('');
                    $alert_info_reparto.show();
                    $message_info_reparto.append(json.message);
                }

            });
        }

    });

    var validation_form_envio = function()
    {
        $alert_info_reparto.hide();
        $message_info_reparto.html('');
        switch(true){
            case !$tracked_id.val() :
                $message_info_reparto.append('El tracked es requerido, intente nuevamente.');
                $alert_info_reparto.show();
                return true;
            break;
        }
    }
</script>
