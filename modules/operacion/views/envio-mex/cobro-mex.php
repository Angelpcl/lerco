<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\envio\Envio;
use yii\widgets\DetailView;
use app\models\Esys;
use app\models\caja\CajaMex;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\models\esys\EsysSetting;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\esys\EsysListaDesplegable;
use app\models\movimiento\MovimientoPaquete;

/* @var $this yii\web\View */

$this->title =  'Cobro de envío Mex ';
$this->params['breadcrumbs'][] = 'Escaneo por Envio ';



?>

<div class="ibox">
    <div class="ibox-title">
        <h5 >BUSCAR ENVIO</h5>
    </div>
    <div class="ibox-content">
        <?php $form = ActiveForm::begin(['id' => 'form-cobro', 'action' => ['cobro-mex'] ]) ?>
        <div class="row">
            <div class="col-sm-4">
                <?= Html::label('QUIEN ENVIA:', 'cliente-emisor_id') ?>
                <?= Select2::widget([
                    'id' => 'cliente-emisor_id',
                    'name' => 'Cliente[emisor_id]',
                    'language'  => 'es',
                    'pluginOptions' => [
                        'allowClear'            => true,
                        'minimumInputLength'    => 3,
                        'language'   => [
                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                        ],
                        'ajax' => [
                            'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                            'dataType' => 'json',
                            'cache'    => true,
                            'processResults' => new JsExpression('function(data, params){ return {results: data} }'),
                        ],
                    ],
                    'options' => [
                            'placeholder' => 'Selecciona al cliente...',
                    ],
                ]) ?>

            </div>
            <div class="col-sm-4">
                <?= Html::label('QUIEN RECIBE:', 'cliente-receptor_id') ?>
                <?= Select2::widget([
                    'id' => 'cliente-receptor_id',
                    'name' => 'Cliente[receptor_id]',
                    'language'  => 'es',

                    'pluginOptions' => [
                        'allowClear'            => true,
                        'minimumInputLength'    => 3,
                        'language'   => [
                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                        ],
                        'ajax' => [
                            'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                            'dataType' => 'json',
                            'cache'    => true,
                            'processResults' => new JsExpression('function(data, params){ return {results: data} }'),
                        ],
                    ],
                    'options' => [
                            'placeholder' => 'Selecciona al cliente...',
                    ],
                ]) ?>
            </div>
            <div class="col-sm-4">
                <?= Html::submitButton( '<i class="fa fa-cube"></i>  BUSCAR ENVIO', ['class' => 'btn btn-primary btn-block btn-lg', 'style' => 'margin-top: 10%']) ?>
            </div>
        </div>
        <?php if (isset($envios)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th class="min-col text-center text-uppercase">Folio</th>
                        <th class="min-col text-center text-uppercase">Sucursal E.</th>
                        <th class="min-col text-center text-uppercase">Sucursal R.</th>
                        <th class="min-col text-center text-uppercase">Cliente E.</th>
                        <th class="min-col text-center text-uppercase">Cliente R.</th>
                        <th class="min-col text-center text-uppercase">Costo extra</th>
                        <th class="min-col text-center text-uppercase">Valor asegurado</th>
                        <th class="min-col text-center text-uppercase">Peso</th>
                        <th class="min-col text-center text-uppercase">SELECCIONAR</th>

                    </tr>
                </thead>
                <?php foreach ($envios as $key => $envio): ?>
                    <tr class="text-center">
                        <td><?= $envio->folio  ?></td>
                        <td><?= $envio->sucursalEmisor->nombre  ?></td>
                        <td><?= isset($envio->envioDetalles[0]) ?  $envio->envioDetalles[0]->sucursalReceptor->nombre : 'N/A' ?></td>
                        <td><?= $envio->clienteEmisor->nombreCompleto ?></td>
                        <td><?=  isset($envio->envioDetalles) ? $envio->envioDetalles[0]->clienteReceptor->nombreCompleto : 'N/A' ?></td>
                        <td><?= $envio->peso_mex_con_empaque ?> Lbs</td>
                        <td><?= $envio->impuesto ?></td>
                        <td><?= $envio->total ?></td>
                        <td><?= Html::a('SELECCIONAR', ['cobro-mex', 'folio' => $envio->folio], [
                                'class' => 'btn btn-danger',
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </table>
        <?php endif ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-5">

        <?php if (isset($folio)): ?>
        <div class="ibox">
            <div class="ibox-title">
                <h5 >FOLIO DE ENVIO</h5>
            </div>
            <div class="ibox-content">
                <?=  Html::input('text','folio',isset($folio) ? $folio->folio: null,[ 'class' => 'form-control','placeholder'=>'TIE-00000','autocomplete' => 'off']); ?>
            </div>
        </div>
        <?php endif ?>
        <?php if (isset($folio)): ?>
            <div class="ibox">
                <div class="ibox-content">
                    <?php $form = ActiveForm::begin(['id' => 'form-peso', 'action' => ['update-envio'] ]) ?>
                            <?= Html::hiddenInput('id', $folio->id) ?>
                            <div class="row">
                                <div class="col-sm-12">
                                    <h3>PESO DE ENTREGA </h3>
                                    <div class="row" style="border-style: double;padding: 2%;">
                                        <div class="col-sm-6">
                                            <?= $form->field($folio, 'peso_total')->textInput(['type' => 'number','class' => 'form-control text-center']) ?>

                                        </div>
                                        <div class="col-sm-6">
                                            <?= Html::submitButton( 'GUARDAR', ['class' => 'btn btn-primary btn-block btn-lg', "style" => "margin-top: 15px;"]) ?>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <?= $form->field($folio, 'subtotal')->textInput(['type' => 'number','class' => 'form-control text-center']) ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $form->field($folio, 'total')->textInput(['type' => 'number','class' => 'form-control text-center', 'readonly' => 'readonly']) ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php ActiveForm::end(); ?>
                    <?php $form = ActiveForm::begin(['id' => 'form-metodo', 'action' => ['cobro-envio'] ]) ?>
                    <?= $form->field($folio->cobroRembolsoEnvio, 'cobroRembolsoEnvioArray')->hiddenInput()->label(false) ?>
                        <?= Html::hiddenInput('id', $folio->id) ?>
                        <?= Html::hiddenInput('subtotal', $folio->subtotal) ?>
                        <?= Html::hiddenInput('total', $folio->total) ?>
                        <div class="row">
                            <div class="col-sm-12">
                                <h3>Metodos de pagos</h3>
                                <div class="row" style="border-style: double;padding: 2%;">
                                    <div class="col-sm-6">

                                        <?= $form->field($folio->cobroRembolsoEnvio, 'metodo_pago')->dropDownList(CobroRembolsoEnvio::$servicioList)->label("&nbsp;") ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <?= $form->field($folio->cobroRembolsoEnvio, 'cantidad')->textInput(['type' => 'number','autocomplete' => 'off']) ?>
                                    </div>
                                    <button  type="button"class="btn  btn-primary btn-block" id="btnAgregarMetodoPago" style="margin-top: 15px;" >Ingresar pago</button>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12" style="margin-top: 5%;">
                                        <table class="table table-hover table-vcenter" style="background: aliceblue;">
                                            <thead>
                                                <tr>
                                                    <th>Forma de pago</th>
                                                    <th class="text-center">Cantidad</th>
                                                </tr>
                                            </thead>
                                            <tbody class="content_metodo_pago" style="text-align: center;">

                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="text-right"  style="border: none" colspan="2"><span class="text-main text-semibold">Total: </span></td>
                                                    <td><strong id="total_metodo"><?= number_format($folio->total,2)  ?> USD</strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right" style="border: none" colspan="2"><span class="text-main text-semibold">Efectivo (Cobro): </span></td>
                                                    <td><strong id= "pago_metodo_total">0 USD</strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right" style="border: none" colspan="2"><span class="text-main text-semibold">Balance: </span></td>
                                                    <td  class="text-danger"><strong id= "balance_total">0 USD</strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right" style="border: none;" colspan="2"><span class="text-main text-semibold">Cambio: </span></td>
                                                    <td><strong id="cambio_metodo">0 USD</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <label for="descuento_manual_check">Nota / Comentario</label>
                                                        <?= Html::textarea('nota',$folio->nota,[ 'id' => 'nota','class' => 'form-control', 'rows' => '3']) ?>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($folio->status != Envio::STATUS_ENTREGADO ): ?>
                            <?= Html::submitButton( '<i class="fa fa-gear"></i>  Guardar operación', ['class' => 'btn btn-primary btn-block btn-lg', "data-loading-text"=>"Cargando...", 'id' => 'btn-load-search']) ?>

                        <?php endif ?>

                        <?php if ($folio->status == Envio::STATUS_ENTREGADO ): ?>
                            <?= Html::a('Imprimir Ticket', false, ['class' => 'btn btn-warning btn-lg btn-block', 'id' => 'imprimir-ticket','style'=>'    padding: 6%;'])?>

                            <?php /* ?>
                            <?= Html::a('Rembolso Envio',false,  ['class' => 'btn btn-primary btn-lg btn-block', 'data-target' => "#modal-renvio-envio", 'data-toggle' =>"modal"  ] )?>
                            */?>
                        <?php endif ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        <?php endif ?>
    </div>
    <div class="col-md-7">
        <?php if (isset($folio)): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >PESO</h5>
                </div>
                <div class="ibox-content">
                    <div class="row totales cobros text-center">
                        <div class="col-sm-6">
                            <h2>PESO (MX)</h2>
                            <span class="neto monto"> <?= number_format($folio->peso_mex_con_empaque, 2) ?> Lbs</span>
                        </div>
                        <div class="col-sm-6">
                            <h2>PESO DE ENTREGA</h2>
                            <span class="neto monto"> <?= number_format($folio->peso_total, 2) ?> Lbs</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >COSTOS DEL ENVIO</h5>
                </div>
                <div class="ibox-content">
                    <div class="row totales cobros text-center">
                        <div class="col-sm-4">
                            <h2>SUBTOTAL</h2>
                            <span class="neto monto">$ <?= number_format( $folio->subtotal, 2) ?> USD</span>
                        </div>
                        <div class="col-sm-4">
                            <h2>IMPUESTO</h2>
                            <span class="impuestos monto">$ <?= number_format($folio->impuesto, 2) ?> USD</span>
                        </div>
                        <div class="col-sm-4">
                            <h2>TOTAL</h2>
                            <span class="total monto">$ <?= number_format($folio->total, 2) ?> USD</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <?php if (isset($folio)): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 ># PAQUETES</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary" style="font-size: 10px">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">Sucursal R.</th>
                                    <th class="min-col text-center text-uppercase">Cliente R.</th>
                                    <th class="min-col text-center text-uppercase">Categoria</th>
                                    <th class="min-col text-center text-uppercase">Producto</th>
                                    <th class="min-col text-center text-uppercase">N° de piezas</th>
                                    <th class="min-col text-center text-uppercase">Costo extra</th>
                                    <th class="min-col text-center text-uppercase">Valor asegurado</th>
                                    <th class="min-col text-center text-uppercase">Peso</th>
                                    <th class="min-col text-center text-uppercase">Estatus</th>

                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($folio->envioDetalles as $key => $item): ?>
                                    <tr>
                                        <td><?= Html::a($item->sucursalReceptor->nombre, ['/sucursales/sucursal/view', 'id' => $item->sucursalReceptor->id], ['class' => 'text-primary']) ?></td>
                                        <td><?=  Html::a($item->clienteReceptor->nombreCompleto, ['/crm/cliente/view', 'id' => $item->clienteReceptor->id], ['class' => 'text-primary']) ?></td>
                                        <td><?= isset($item->producto->categoria->singular ) ? $item->producto->categoria->singular  : ''?></td>
                                        <td><?= $item->producto->nombre ?></td>
                                        <td><?= $item->cantidad ?></td>
                                        <td><?= $item->impuesto ?> USD</td>
                                        <td><?= $item->valor_declarado ? $item->valor_declarado : 0  ?> USD</td>
                                        <td><?= $item->peso ?> Lbs</td>
                                        <td><?= EnvioDetalle::$statusList[$item->status] ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif ?>

    </div>
</div>


<div class="display-none">
    <table>
        <tbody class="template_metodo_pago">
            <tr id = "metodo_id_{{metodo_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main text-semibold" , "id"  => "table_metodo_id"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main " , "id"  => "table_metodo_cantidad","style" => "text-align:center"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="fade modal inmodal" id="modal-ticket"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-money mar-rgt-5px icon-lg"></i> Generar Ticket (Reclamación) </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="text-center">
                            <h1>Diferencia</h1>
                            <h3><?= isset($lb_dif) ? $lb_dif : 0  ?> lb</h3>
                        </div>
                        <div class="row">
                            <div class="col-sm-8 offset-sm-2">
                                <div class="form-group">
                                    <?= Html::label('Tipo:', 'tipo_ticket_id') ?>
                                    <?= Html::dropDownList('tipo_ticket_id',null,EsysListaDesplegable::getItems('tipo_ticket'),[ 'id' => 'tipo_ticket_id','class' => 'form-control']) ?>
                                </div>
                                <div class="form-group">
                                    <label class="control-label" for="tipo">Nota / Comentario: </label>
                                    <?=  Html::textarea('comentario_ticket','SE GENERO TICKET POR LIBRAS FALTANTES ' .  ( isset($lb_dif) ? $lb_dif : 0 ) ,[ 'class' => 'form-control','rows' => 6, 'id' =>'comentario_ticket', 'style' => 'border-color: #d32f2f;'   ] ); ?>
                                </div>
                                <div class="form-group">
                                    <?= Html::submitButton('Generar Ticket', ['class' => 'btn btn-primary btn-lg btn-block' , 'id' => 'send_ticket']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert-ticket" style="margin-top: 2%; display: none">
                                    <div class="alert alert-mint ">
                                        <strong> FOLIO DE SEGUIMIENTO:</strong>
                                    </div>
                                    <div class="promo_basica" style=" background: #bcc7b17a;margin-top: 2%;padding: 2px;text-align:center; background-image: url(/img/code_promocion.png);">
                                        <h2 id="code-ticket"></h2>
                                    </div>
                                </div>
                                <div class="alert  alert-info-basic" style="display: none">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Cerrar</button>
            </div>
        </div>
    </div>
</div>




<?php if (isset($folio)): ?>
    <?= $this->render('_modal_rembolso', [
        'folio' => $folio,
        'total' => $folio->total,
    ]) ?>
<?php endif ?>

<script>
var $template_metodo_pago         = $('.template_metodo_pago'),
    $btnAgregarMetodoPago         = $('#btnAgregarMetodoPago'),
    $inputcobroRembolsoEnvioArray = $('#cobrorembolsoenvio-cobrorembolsoenvioarray'),
    $content_metodo_pago          = $(".content_metodo_pago"),
    $descuento_manual             = $('#descuento_manual'),
    $descuento_manual_check       = $('#descuento_manual_check'),
    $modalTicket                  = $('#modal-ticket'),
    $alertTicket                  = $('.alert-ticket'),
    $codeTicket                   = $('#code-ticket'),
    $sendTicket                   = $('#send_ticket'),
    $modalShow                    = <?= isset($is_ticket) && $is_ticket ? 10 : 20  ?>,
    tipo_cobro                    = <?= CobroRembolsoEnvio::TIPO_COBRO  ?>,
    tipo_rembolso                 = <?= CobroRembolsoEnvio::TIPO_DEVOLUCION  ?>,
    metodoPagoList                = JSON.parse('<?= json_encode(CobroRembolsoEnvio::$servicioList)  ?>'),
    $envioID                      = <?= isset($folio->id) ? $folio->id : 0 ?>,
    $inpuestoEnvio                      = <?= isset($folio->impuesto) && $folio->impuesto ? $folio->impuesto : 0 ?>,
    $form_metodoPago = {
        $metodoPago : $('#cobrorembolsoenvio-metodo_pago'),
        $cantidad   : $('#cobrorembolsoenvio-cantidad'),
    };
    pesoTotal                = <?= (isset($folio->total) ? $folio->total : 0) ?>;
    pesoTotalFinal           = <?= (isset($folio->total) ? $folio->total : 0) ?>;
    metodoPago_array    = [];


$(document).ready(function() {
    init_paquete_list();
    if ($modalShow == 10 )
        $modalTicket.modal('show');
});

$(document).on('nifty.ready', function() {
    $('#btn-load-search').on('click', function (event) {
        event.preventDefault();
        $(this).prop("disabled", true);

        var btn = $(this).button('loading')
        // business logic...

        var doSomething = setTimeout(function(){
            clearTimeout(doSomething);
            btn.button('reset')
        }, 4000);

        $(this).submit();
    });
});

var init_paquete_list = function(){

    $.get('<?= Url::to('cobro-envio-ajax') ?>',{ 'envio_id': $envioID },function(metodo){
        $.each(metodo.results,function(key,item){
            if (item.id) {
                metodo = {
                    "metodo_id"         : metodoPago_array.length + 1,
                    "metodo_pago_id"    : item.metodo_pago,
                    "tipo"              : item.tipo,
                    "metodo_pago_text"  : metodoPagoList[item.metodo_pago],
                    "cantidad"          : item.tipo ==  tipo_rembolso ?  (item.cantidad * -1) : item.cantidad,
                    "origen"            : 2,
                };

                metodoPago_array.push(metodo);
                render_metodo_template();
            }
        });
    });
};


$sendTicket.click(function(){
    if ($envioID) {
        $.post("<?= Url::to(['create-ticket-ajax'])  ?>",{ envio_id: $envioID, tipo: $('#tipo_ticket_id').val(),comentario : $('#comentario_ticket').val()  },function(jsonResponse){
            $sendTicket.prop("disabled", true);
            if (jsonResponse.code = 202) {
                $alertTicket.show();
                $codeTicket.html(jsonResponse.clave);
            }else{
                $.niftyNoty({
                    type: 'danger',
                    icon : 'pli-cross icon-2x',
                    message : 'Ocurrio un error al generar el Ticket, intenta nuevamente',
                    container : 'floating',
                    timer : 5000
                });
            }
        },'json');
    }
});

$btnAgregarMetodoPago.click(function(){

    if(!$form_metodoPago.$metodoPago.val() || !$form_metodoPago.$cantidad.val()){
        return false;
    }

    metodo = {
        "metodo_id"         : metodoPago_array.length + 1,
        "metodo_pago_id"    : $form_metodoPago.$metodoPago.val(),
        "metodo_pago_text"  : $('option:selected', $form_metodoPago.$metodoPago).text(),
        "cantidad"          : parseFloat($form_metodoPago.$cantidad.val()),
        "origen"            : 1,
    };

    metodoPago_array.push(metodo);

    calcula_totales(pesoTotalFinal);

});

var calcula_totales  = function(){

    pago_total = 0;
    $.each(metodoPago_array, function(key, metodo){
        if (metodo.metodo_id)
            pago_total = pago_total + parseFloat(metodo.cantidad);
    });

    new_cambio_metodo = pago_total - parseFloat(pesoTotalFinal);

    if (metodoPago_array[0] )
        metodoPago_array[metodoPago_array.length - 1 ].cantidad =  new_cambio_metodo < 0 ?  metodoPago_array[metodoPago_array.length - 1 ].cantidad : (metodoPago_array[metodoPago_array.length - 1 ].cantidad - new_cambio_metodo) < 0 ? 0 : (metodoPago_array[metodoPago_array.length - 1 ].cantidad - new_cambio_metodo);

    $('#cambio_metodo').html( new_cambio_metodo < 0 ? 0 : "$ " +new_cambio_metodo.toFixed(2) );
    render_metodo_template();
}

/*====================================================
*               RENDERIZA TODO LOS METODS DE PAGO
*====================================================*/
var render_metodo_template = function(){
    $content_metodo_pago.html("");
    pago_total = 0;
    $.each(metodoPago_array, function(key, metodo){
        if (metodo.metodo_id) {



            metodo.metodo_id = key + 1;
            template_metodo_pago = $template_metodo_pago.html();
            template_metodo_pago = template_metodo_pago.replace("{{metodo_id}}",metodo.metodo_id);
            $content_metodo_pago.append(template_metodo_pago);

            $tr        =  $("#metodo_id_" + metodo.metodo_id, $content_metodo_pago);
            $tr.attr("data-metodo_id",metodo.metodo_id);
            $tr.attr("data-origen",metodo.origen);



            $("#table_metodo_id",$tr).html(metodo.metodo_pago_text);
            $("#table_metodo_cantidad",$tr).html("$ " +metodo.cantidad + " USD");

            if (metodo.tipo == tipo_rembolso)
                $tr.addClass('danger');

            if (metodo.origen != 2 )
                $tr.append("<button type='button' class='btn btn-warning btn-circle' onclick='refresh_metodo(this)'><i class='fa fa-trash'></i></button>");
            pago_total = pago_total + parseFloat(metodo.cantidad);
        }
    });

    $('#total_metodo').html("$ " + pesoTotalFinal);
    $('#balance_total').html("$ " + ( pesoTotalFinal - pago_total.toFixed(2)));
    $('#pago_metodo_total').html("$ "+ pago_total.toFixed(2));

    $inputcobroRembolsoEnvioArray.val(JSON.stringify(metodoPago_array));
}

var refresh_metodo = function(ele){
    $ele_paquete_val = $(ele).closest('tr');

    $ele_paquete_id  = $ele_paquete_val.attr("data-metodo_id");
    $ele_origen_id   = $ele_paquete_val.attr("data-origen");

      $.each(metodoPago_array, function(key, metodo){
        if (metodo) {
            if (metodo.metodo_id == $ele_paquete_id && metodo.origen == $ele_origen_id ) {
                metodoPago_array.splice(key, 1 );
            }
        }
    });

    $(ele).closest('tr').remove();
    $inputcobroRembolsoEnvioArray.val(JSON.stringify(metodoPago_array));
    render_metodo_template();
}


$('#envio-subtotal').change(function(){
    $('#envio-total').val( parseFloat($(this).val()) + parseFloat($inpuestoEnvio)  );
})


$('#imprimir-ticket').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['envio-mex/imprimir-ticket', 'id' => isset($folio->id) ? $folio->id : 0 ])  ?>",
    'imprimir',
    'width=600,height=500');
});
</script>
