<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\esys\EsysSetting;
use kartik\daterange\DateRangePicker;
use app\models\cliente\ClienteCodigoPromocion;
?>

<div class="fade modal " id="modal-promocion"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-gift mar-rgt-5px icon-lg"></i> Condonación especial</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert-promocion-especial" style="margin-top: 2%; ">
                            <div class="alert alert-warning " >
                                <strong> Información :</strong>  La condonación especial, otorga un descuento directo al precio total del envio.
                            </div>
                            <div class="row">
                                <div class="col-sm-8">
                                    <?= Html::tag('p', "Descuento a solicitar",["class" => "text-main" ]) ?>
                                    <?= Html::input('number', 'descuento',null,['class' => 'form-control','placeholder' => 'Descuento']) ?>
                                </div>
                                <div class="col-sm-4">
                                    <?= Html::tag('p', "Tipo de condonación",["class" => "text-main" ]) ?>
                                    <?=  Html::dropDownList('tipo_condonacion', null, ClienteCodigoPromocion::$condonacionList, [ 'class' => 'form-control max-width-170px']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="DateRangePicker   kv-drp-dropdown  col-sm-6">
                                    <?= Html::tag('p', "Selecciona un rango de fecha el que enviara su paquete ",["class" => "text-main" ]) ?>
                                    <?= DateRangePicker::widget([
                                        'name'           => 'date_range_promo',
                                        //'presetDropdown' => true,
                                        'hideInput'      => true,
                                        'useWithAddon'   => true,
                                        'convertFormat'  => true,
                                        'startAttribute' => 'from_date',
                                        'endAttribute' => 'to_date',
                                        'startInputOptions' => ['value' => '2019-01-01'],
                                        'endInputOptions' => ['value' => '2019-12-31'],
                                        'pluginOptions'  => [
                                            'locale' => [
                                                'format'    => 'Y-m-d',
                                                'separator' => ' - ',
                                            ],
                                            'opens' => 'left',
                                            "autoApply" => true,
                                        ],
                                    ])
                                    ?>
                                </div>
                                <div class="col-sm-6">
                                    <?= Html::tag('p', "Nota / Descripcion ",["class" => "text-main" ]) ?>
                                    <?= Html::textarea("nota_ticket",null,['class' => 'form-control','id' =>'nota_ticket']) ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <?= Html::Button('Solicitar promoción', ['class' =>  'btn btn-warning btn-lg btn-block', 'id' => 'form-promocion-especial-send']) ?>
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
    var $formPromocionEspecialSend  = $('#form-promocion-especial-send'),
        $alertInfoEspecial      = $('.alert-info-especial'),
        $alertPromocionEspecial = $('.alert-promocion-especial');

    $formPromocionEspecialSend.click(function(){
        $nota_ticket        =  $('#nota_ticket');
        $descuento          =  $('input[name="descuento"]');
        $date_range         =  $('input[name="date_range_promo"]');
        $tipo_condonacion   =  $('select[name="tipo_condonacion"]');

        if ( $descuento.val() && $date_range.val()) {
            $.post('<?= Url::to(["promocion-create-especial-ajax"])  ?>',{
                id                  : <?= $cliente_emisor_id ?>,
                ticket_id           : <?= $model->id ?>,
                nota_ticket         : $nota_ticket.val(),
                descuento           : $descuento.val(),
                date_range          : $date_range.val(),
                tipo_condonacion    : $tipo_condonacion.val(),
                 },function(json){
                if (json.code == 10) {
                    $.niftyNoty({
                        type: 'success',
                        icon : 'pli-like-2 icon-2x',
                        message : json.message,
                        container : 'floating',
                        timer : 5000
                    });
                    $('#modal-promocion').modal('hide');
                }else{
                    $.niftyNoty({
                        type: 'danger',
                        icon : 'pli-cross icon-2x',
                        message : json.message,
                        container : 'floating',
                        timer : 5000
                    });
                }
            });
        }else{
           $.niftyNoty({
                    type: 'danger',
                    icon : 'pli-cross icon-2x',
                    message : 'Todos los datos son requeridos, intente nuevamente',
                    container : 'floating',
                    timer : 5000
                });
        }
    });



</script>

