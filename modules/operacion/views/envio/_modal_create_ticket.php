<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\esys\EsysListaDesplegable;
use app\models\ticket\Ticket;
use app\models\envio\Envio;
use yii\web\JsExpression;

?>

<div class="fade modal inmodal " id="modal-create-ticket"  tabindex="-1" role="dialog" aria-labelledby="modal-show-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i  class="fa fa-money mar-rgt-5px icon-lg"></i> NEGOCIACIÓN / REEMBOLSO / TICKET</h4>
            </div>
            <!--Modal body-->
            <?php $form = ActiveForm::begin(['id' => 'form-ticket', 'options' => ['enctype' => 'multipart/form-data'] ]) ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-8 col-md-7 col-sm-7">
                        <div id="error-add-ticket" class="has-error" style="display: none"></div>
                        <div class="row">
                            <div class="col-lg-6">
                                <?= $form->field($model->ticket, 'tipo_id')->dropDownList(EsysListaDesplegable::getItems('tipo_ticket'), ['prompt' => 'Tipo']) ?>
                                <?= $form->field($model->ticket, 'status')->dropDownList(Ticket::$statusList) ?>
                                <?= $form->field($model->ticket, 'descripcion')->textarea(['rows' => 6,'disabled' => $model->ticket->id ? true :  false]) ?>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <?= Html::label('Buscar envío por folio','search_envio'); ?>
                                        <?= Html::input('text','search_envio',$model->folio,['id' =>'search_envio','class' => 'form-control']) ?>
                                        <?= $form->field($model->ticket, 'envio_id')->hiddenInput()->label(false) ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <?= Html::button('<i class="fa fa-search"></i> Buscar', ['id' =>'btn_search_envio','class' => 'btn  btn-primary btn-lg btn-block', "style" => "margin-top: 20px;", 'type' => 'button'] ) ?>
                                    </div>
                                </div>
                                <div class="alert alert-warning div_alert_warning_aviso" style="display: none">
                                    <strong>Aviso!</strong> No se encontro ningun envio relacionado a ese folio, intente nuevamente.
                                </div>
                                <div class="alert alert-success div_alert_success_aviso" style="display: none">
                                    <strong>Aviso!</strong> Se selecciono correctamente el envio
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-5 col-sm-5">
                        <div class="ibox">
                            <div class="ibox-title">
                                <h3 class="panel-title">Información envio</h3>
                            </div>
                            <div class="ibox-content">
                                <div class="div_search_envio" style="display: none">
                                    <div class="text-center">
                                        <?= Html::img('@web/img/profile-photos/compras.png', ["class" => "img-md img-circle mar-btm", "alt" => "Paqueteria"]) ?>

                                        <p class="text-lg text-semibold mar-no text-main" ><p id="lbl_cliente_receptor_ticket"></p></p>
                                        <p class="text-muted">Cliente Receptor</p>
                                        <p class="text-sm"><strong>Cliente Emisor: <p id="lbl_cliente_emisor_ticket"></p></strong> </p>
                                        <button class="btn btn-primary mar-ver "  type="button" ><i class="pli-suitcase icon-fw"></i>Seleccionar</button>
                                        <ul class="list-unstyled text-center bord-top pad-top mar-no row">
                                            <li class="col-xs-3">
                                                <span class="text-lg text-semibold text-main" id="lbl_peso_total_ticket"></span>
                                                <p class="text-muted mar-no">Peso total</p>
                                            </li>
                                            <li class="col-xs-3">
                                                <span class="text-lg text-semibold text-main" id="lbl_subtotal_ticket"></span>
                                                <p class="text-muted mar-no">Subtotal</p>
                                            </li>
                                            <li class="col-xs-3">
                                                <span class="text-lg text-semibold text-main" id="lbl_total_ticket"></span>
                                                <p class="text-muted mar-no">Total</p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton($model->ticket->isNewRecord ? 'Crear ticket' : 'Guardar cambios', ['class' => $model->ticket->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'btnGuardarTicket']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<script>
var $search_envio = $('#search_envio'),
    $div_search_envio = $('.div_search_envio'),
    $btn_search_envio = $('#btn_search_envio'),
    $ticket_envio_id = $('#ticket-envio_id'),
    $error_add_ticket      = $('#error-add-ticket'),
    $div_alert_warning_aviso = $('.div_alert_warning_aviso'),
    $div_alert_success_aviso = $('.div_alert_success_aviso'),
    $ticket_cliente_id       = $('#ticket-cliente_id'),
    update_envio_id          = "<?= $model->ticket->envio_id  ?>",
    tipo              = {
        tierra  : <?= Envio::TIPO_ENVIO_TIERRA ?>,
        mex     : <?= Envio::TIPO_ENVIO_MEX ?>,
    };
    is_update = <?=  $model->ticket->id ? 10 : 1 ?>,

    $link_envio_view = $('#link_envio_view');

    $(document).ready(function(){
        $btn_search_envio.trigger('click');
    });

    $btn_search_envio.click(function(){
        if ($search_envio.val()) {
            filters = "folio="+ $.trim($search_envio.val());
            $.get("<?= Url::to(['/operacion/ticket/search-envio-ajax']) ?>",{ filters : filters},function(envios){
                $ticket_envio_id.val(null);
                $div_alert_success_aviso.hide();
                if (envios.total > 0 ) {
                    $('#lbl_total_ticket').html('');
                    $('#lbl_subtotal_ticket').html('');
                    $('#lbl_peso_total_ticket').html('');
                    $('#lbl_cliente_emisor_ticket').html('');
                    $('#lbl_cliente_receptor_ticket').html('');
                    $link_envio_view.prop('href',false);
                    $div_alert_warning_aviso.hide();
                    if (envios.rows) {
                        $.each(envios.rows,function(key, envio){
                            if (envio.id) {
                                $('#lbl_total_ticket').html(btf.conta.money(envio.total));
                                $('#lbl_subtotal_ticket').html(btf.conta.money(envio.subtotal));
                                $('#lbl_peso_total_ticket').html(envio.peso_total);
                                $('#lbl_cliente_emisor_ticket').html(envio.nombre_emisor);
                                $('#lbl_cliente_receptor_ticket').html(envio.nombre_receptor);

                                $('button',$div_search_envio).attr('onclick', "get_envio("+envio.id+")");

                                if (envio.tipo_envio == tipo.tierra || envio.tipo_envio == tipo.lax )
                                    $link_envio_view.prop('href','/operacion/envio/view?id='+ envio.id);
                                if (envio.tipo_envio == tipo.mex)
                                    $link_envio_view.prop('href','/operacion/envio-mex/view?id=' + envio.id);

                                $div_search_envio.show();
                            }else{
                                $div_search_envio.hide();
                            }
                        });
                    }
                }else{
                    $div_search_envio.hide();
                    $div_alert_warning_aviso.show();
                }
                if (is_update == 10) {
                    $ticket_envio_id.val(update_envio_id);
                }
            },'json');

        }
    });

    var get_envio = function(envio_id){
        $ticket_envio_id.val(envio_id);
        $div_alert_success_aviso.show();
    }

    $('#btnGuardarTicket').on('click', function(event){
        event.preventDefault();
        $error_add_ticket.hide();
        is_envio    = $ticket_envio_id.val() ? true : false;

        if (is_envio) {
            $('#btnGuardarTicket').submit();
        }else{
            $error_add_ticket.html('');
            $error_add_ticket.append('<div class="help-block">* SELECCIONA EL ENVIO </div>');
            $error_add_ticket.show();
        }

    });



</script>
