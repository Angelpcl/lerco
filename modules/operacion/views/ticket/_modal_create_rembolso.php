<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
use kartik\date\DatePicker;
use yii\web\JsExpression;
?>

<div class="fade modal " id="modal-create-renvio"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
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
                            <div id="error-create-rembolso" class="has-error" style="display: none">
                            </div>
                            <div class="text-center">
                                <h1>Monto total</h1>
                                <h3><?= "$" . number_format($total,2) ?></h3>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <?=  Html::label('Numero de rembolsos','number_rembolso'); ?>
                                    <?=  Html::input('number','number_rembolso',null,[ 'class' => 'form-control text-center', 'id' => 'number_rembolso', 'mint' => '1']); ?>
                                    <?= Html::hiddenInput('input_rembolso_array',null,['id' => 'input_rembolso_array']) ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <table class="table table-bordered invoice-summary">
                                            <thead>
                                                <tr class="bg-trans-dark">
                                                    <th class="min-col text-center text-uppercase">Cantidad</th>
                                                    <th class="min-col text-center text-uppercase">Fecha</th>
                                                </tr>
                                            </thead>
                                            <tbody class="content_rembolso" style="text-align: center;">


                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Crear rembolso', ['class' => 'btn btn-primary' , 'id' => 'crear_send_rembolso']) ?>
            </div>
        </div>
    </div>
</div>


<div class="display-none">
    <table>
        <tbody class="template_info_rembolso">
            <tr id ="rembolso_info_id_{{rembolso_info_id}}">
                <td>
                    <?=  Html::label('Numero de rembolsos','parcial_name'); ?>
                    <?=  Html::input('number','parcial_name',null,[ 'class' => 'form-control text-center', 'id' => 'parcial_name']); ?>
                </td>
                <td>
                    <?=  Html::label('Fecha','fecha_name'); ?>
                    <?=  Html::input('date','fecha_name',null,[ 'class' => 'form-control text-center', 'id' => 'fecha_name']); ?>

                </td>
            </tr>
        </tbody>
    </table>
</div>




<script>

var $crear_send_rembolso              =  $("#crear_send_rembolso"),
    $form_rembolso_envio        = $('#rembolso_envio'),
    $form_number_rembolso       = $('#number_rembolso'),
    $form_comentario_rembolso   = $('#comentario_rembolso'),
    $error_create_rembolso          = $('#error-create-rembolso'),
    $modal_contacto             = $('#modal-create-renvio'),

    $template_info_rembolso     = $('.template_info_rembolso'),
    $content_rembolso           = $('.content_rembolso'),
    $input_rembolso_array           = $('#input_rembolso_array'),

    rembolso_array              = [],
    $envio_id                   = "<?=  $folio->id ?>";
    $total_envio                = "<?= $total ?>";
    $ticket_id                  = "<?= $ticket_id ?>";

$(document).ready(function(){

    $form_number_rembolso.change(function(){
        rembolso_array = [];
        parciales = parseInt($(this).val());
        $total     = parseFloat($total_envio);
        $parcial   = $total / parciales;

        if (parciales > 0) {
            for (var i = 0; i < parciales; i++) {
                $rembolso = {
                    "id"    : rembolso_array.length + 1,
                    "monto" : $parcial.toFixed(2),
                    "fecha" : null,
                    "status": 10,
                };
                rembolso_array.push($rembolso);
            }
        }
        $input_rembolso_array.val(JSON.stringify(rembolso_array));
        render_rembolso_template();
    });




});

    $crear_send_rembolso.click(function(){

        if(validation_form_seguimiento()){
        return false;
        }

        $.post("<?= Url::to(['create-rembolso']) ?>",{ ticket_id : $ticket_id, number_rembolso : $form_number_rembolso.val(), rembolso_array : $input_rembolso_array.val() },function(json){
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
                if (json.code == 20 || json.code == 30 ) {
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
    });

    var validation_form_seguimiento = function()
    {
        $error_create_rembolso.html('');
        switch(true){
            case !$form_number_rembolso.val() :
                $error_create_rembolso.append('<div class="help-block">* Ingresa numero de rembolsos </div>');
                $error_create_rembolso.show();
                return true;
            break;

            case (rembolso_array.length > 0) :

                $.each(rembolso_array, function(key, rembolso){
                    if (rembolso.id) {
                        if (rembolso.fecha == null ) {
                            $error_create_rembolso.append('<div class="help-block">* Todos los pagos deben tener fecha, verifica nuevamente </div>');
                            $error_create_rembolso.show();
                            return true;
                        }
                    }
                });
            break;
        }
    }

  /*====================================================
    *               RENDERIZA TODO LOS METODS DE PAGO
    *====================================================*/
    var render_rembolso_template = function(){
        $content_rembolso.html("");
        count_paquetes = 0;
        is_fecha = true;
        $.each(rembolso_array, function(key, rembolso){
            if (rembolso.id) {
                if (rembolso.status == 10 ) {
                    count_paquetes = count_paquetes + 1;

                    template_info_rembolso = $template_info_rembolso.html();
                    template_info_rembolso = template_info_rembolso.replace("{{rembolso_info_id}}",count_paquetes);

                    $content_rembolso.append(template_info_rembolso);

                    $tr        =  $("#rembolso_info_id_" + count_paquetes, $content_rembolso);
                    $tr.attr("data-rembolso_id",count_paquetes);
                    $tr.attr("data-id",rembolso.id);

                    $("#parcial_name",$tr).val(rembolso.monto);
                    $("#fecha_name",$tr).val(rembolso.fecha);

                    $("#parcial_name" ,$tr).attr("onchange","refresh_rembolso_change(this,'CANTIDAD_REMBOLSO')");
                    $("#fecha_name" ,$tr).attr("onchange","refresh_rembolso_change(this,'FECHA_REMBOLSO')");

                    if (!rembolso.fecha)
                        is_fecha = false;
                }
            }
        });
        if (!is_fecha)
            $crear_send_rembolso.attr('disabled', true);
        else
            $crear_send_rembolso.attr('disabled', false);


    }

    var refresh_rembolso_change = function(ele,inputChange){


        $ele_paquete_val    = $(ele);
        $ele_paquete        = $(ele).closest('tr');
        $ele_paquete_detalle_id     = $ele_paquete.attr("data-rembolso_id");
        $ele_paquete_id             = $ele_paquete.attr("data-id");
        $.each(rembolso_array, function(key, rembolso){

            if (rembolso.id == $ele_paquete_id){
                switch(inputChange){
                    case 'CANTIDAD_REMBOLSO':
                        rembolso.monto = $ele_paquete_val.val();
                    break;
                    case 'FECHA_REMBOLSO':
                        $fecha = (new Date($ele_paquete_val.val()));
                        if (!isNaN($fecha.getDate())) {
                            $fecha.setDate($fecha.getDate() + 1 );
                            rembolso.fecha = new Date($fecha).format("Y-m-d") ? new Date($fecha).format("Y-m-d") : '';
                        }else
                            rembolso.fecha = null;

                    break;
                }

            }
        });

        $input_rembolso_array.val(JSON.stringify(rembolso_array));
        render_rembolso_template();
    }

</script>
