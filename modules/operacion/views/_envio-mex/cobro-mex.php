<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\envio\Envio;
use yii\widgets\DetailView;
use app\models\Esys;
use app\models\caja\CajaMex;
use app\models\esys\EsysSetting;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\esys\EsysListaDesplegable;
use app\models\movimiento\MovimientoPaquete;


/* @var $this yii\web\View */

$this->title =  'Cobro de envío Mex ';
$this->params['breadcrumbs'][] = 'Escaneo por Envio ';

if (isset($folio)) {

    $precioLibra = 0;
    $subtotal    = 0;
    $total       = 0;
    $peso_minimo = 4;
    foreach (Envio::$precioMexList as $key => $precio) {
        if($folio->peso_total >=floatval($precio["rango_ini"]) && $folio->peso_total <= floatval($precio["rango_fin"]) )
            $precioLibra = EsysSetting::getPrecioMex($key);
    }

    if ($precioLibra == 0  && $folio->peso_total >= floatval(Envio::$precioMexList["PRECION_MEX_5"]["rango_ini"]))
       $precioLibra = EsysSetting::getPrecioMex("PRECION_MEX_5");

    if ($precioLibra == 0  && $folio->peso_total <= floatval(Envio::$precioMexList["PRECION_MEX_1"]["rango_ini"]))
       $precioLibra = EsysSetting::getPrecioMex("PRECION_MEX_1");

    $subtotal = (floatval($folio->peso_total) <= floatval(Envio::$precioMexList["PRECION_MEX_1"]["rango_ini"]) ? floatval(Envio::$precioMexList["PRECION_MEX_1"]["rango_ini"]) : floatval($folio->peso_total) ) * floatval($precioLibra);

    $total =  floatval($subtotal) + floatval($folio->impuesto) + floatval($folio->seguro_total) + floatval($folio->costo_reenvio) ;
    $total = $folio->is_descuento_manual ==  Envio::DESCUENTO_ON ?  floatval($total) - floatval($folio->descuento_manual) : $total;
}

?>

<div class="row">
    <div class="col-md-4">
        <div class="ibox">
            <div class="ibox-title">
                <h5 >Ingresa el folio envio</h5>
            </div>
            <div class="ibox-content">
                <?php $form = ActiveForm::begin(['id' => 'form-cobro', 'action' => ['cobro-mex'] ]) ?>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3">
                            <?=  Html::input('text','folio',isset($folio) ? $folio->folio: null,[ 'class' => 'form-control','placeholder'=>'TIE-00000']); ?>
                            <br>
                            <?= Html::submitButton( '<i class="fa fa-cube"></i>  Buscar paquete', ['class' => 'btn btn-primary btn-block btn-lg']) ?>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?php if (isset($folio)): ?>
            <div class="ibox">
                <div class="ibox-content">
                    <?php $form = ActiveForm::begin(['id' => 'form-peso', 'action' => ['update-envio'] ]) ?>
                            <?= Html::hiddenInput('id', $folio->id) ?>
                            <?= Html::hiddenInput('subtotal', $subtotal) ?>
                            <?= Html::hiddenInput('total', $total) ?>
                            <?= Html::hiddenInput('precioLibra', $precioLibra) ?>
                        <div class="row">
                            <div class="col-sm-12">
                                <h3>Peso de entrega </h3>
                                <div class="row" style="border-style: double;padding: 2%;">
                                    <div class="col-sm-4">
                                        <?= $form->field($folio, 'peso_total')->textInput(['maxlength' => true]) ?>
                                    </div>
                                    <div class="col-sm-2">
                                        <?php if ($folio->status == Envio::STATUS_AUTORIZADO ): ?>
                                            <?= Html::submitButton( '  Ingresar peso', ['class' => 'btn btn-primary  btn-lg', "style" => "margin-top: 15px;"]) ?>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php ActiveForm::end(); ?>
                    <?php $form = ActiveForm::begin(['id' => 'form-metodo', 'action' => ['cobro-envio'] ]) ?>
                    <?= $form->field($folio->cobroRembolsoEnvio, 'cobroRembolsoEnvioArray')->hiddenInput()->label(false) ?>
                        <?= Html::hiddenInput('id', $folio->id) ?>
                        <?= Html::hiddenInput('subtotal', $subtotal) ?>
                        <?= Html::hiddenInput('total', $total) ?>
                        <div class="row">
                            <div class="col-sm-12">
                                <h3>Metodos de pagos</h3>
                                <div class="row" style="border-style: double;padding: 2%;">
                                    <div class="col-sm-4">

                                        <?= $form->field($folio->cobroRembolsoEnvio, 'metodo_pago')->dropDownList(CobroRembolsoEnvio::$servicioList)->label("&nbsp;") ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <?= $form->field($folio->cobroRembolsoEnvio, 'cantidad')->textInput(['maxlength' => true]) ?>
                                    </div>
                                    <div class="col-sm-2">
                                        <?php if ($folio->status == Envio::STATUS_AUTORIZADO || $folio->status == Envio::STATUS_PREPAGADO ): ?>
                                            <button  type="button"class="btn  btn-primary" id="btnAgregarMetodoPago" style="margin-top: 15px;" >Ingresar pago</button>
                                        <?php endif ?>
                                    </div>
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
                                                    <?php if (Yii::$app->user->can('admin')): ?>
                                                        <td class="text-right"  colspan="2" >
                                                            <div class="checkbox">
                                                                <?= Html::checkbox('descuento_manual_check', null, ['id'=>'descuento_manual_check','class' => 'magic-checkbox']); ?>
                                                               <label for="descuento_manual_check">Descuento</label>
                                                            </div>
                                                        </td>
                                                    <?php endif ?>
                                                    <td>
                                                        <?= Html::input('text', 'descuento_manual',null,[ 'id' => 'descuento_manual','class' => 'form-control', 'style' => 'width: 120px;', 'disabled' => true]) ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"  style="border: none" colspan="2"><span class="text-main text-semibold">Total: </span></td>
                                                    <td><strong id="total_metodo"><?= number_format($total,2)  ?> USD</strong></td>
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
                        <?php if ($folio->status == Envio::STATUS_AUTORIZADO || $folio->status == Envio::STATUS_PREPAGADO ): ?>
                            <?= Html::submitButton( '<i class="fa fa-gear"></i>  Guardar operación', ['class' => 'btn btn-primary btn-block btn-lg', "data-loading-text"=>"Cargando...", 'id' => 'btn-load-search']) ?>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <strong>Aviso!</strong> El paquete aun se encuentra como <strong><?= Envio::$statusList[$folio->status] ?></strong>
                            </div>
                        <?php endif ?>
                        <?php if ($folio->status == Envio::STATUS_ENTREGADO ): ?>
                            <?= Html::a('Imprimir Ticket', false, ['class' => 'btn btn-warning btn-lg btn-block', 'id' => 'imprimir-ticket','style'=>'    padding: 6%;'])?>
                            <?= Html::a('Rembolso Envio',false,  ['class' => 'btn btn-primary btn-lg btn-block', 'data-target' => "#modal-renvio-envio", 'data-toggle' =>"modal"  ] )?>
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
                    <h5 >Peso total / Precio de libra</h5>
                </div>
                <div class="ibox-content">
                    <div class="row text-center">
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"> <?= number_format($folio->peso_mex_sin_empaque, 2) ?> Lbs</h2>
                            <strong>PESO (RECOLECIÓN)</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"> <?= number_format($folio->peso_mex_con_empaque, 2) ?> Lbs</h2>
                            <strong>PESO (EMPAQUETADO)</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"> <?= number_format($folio->peso_total, 2) ?> Lbs</h2>
                            <strong>PESO (FINAL)</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"> $ <?= number_format($precioLibra , 2) ?> USD</h2>
                            <strong>PRECIO DE LIBRA OTORGADA</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Costos del envio</h5>
                </div>
                <div class="ibox-content">
                    <div class="row text-center">
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"> $ <?= number_format( $subtotal, 2) ?> USD</h2>
                            <strong>SUBTOTAL</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"> $ <?= number_format($folio->impuesto, 2) ?> USD</h2>
                            <strong>COSTO EXTRA</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"> $ <?= number_format($folio->seguro_total, 2) ?> USD </h2>
                            <strong>SEGURO</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"> $ <?= number_format($total, 2) ?> USD</h2>
                            <strong>TOTAL</strong>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ?>
        <?php if (isset($folio)): ?>
            <?php if ($folio->is_reenvio == Envio::REENVIO_ON): ?>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 >Aplico Reenvío</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row totales cobros">

                            <div class="col-sm-4">
                                <span class="label">Costo de reenvío: </span>
                                <span class="total monto">$ <?= number_format($folio->costo_reenvio, 2) ?> USD</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        <?php endif ?>
        <?php if (isset($folio)): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Paquetes relacionados con el envio</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">Sucursal R.</th>
                                    <th class="min-col text-center text-uppercase">Cliente R.</th>
                                    <th class="min-col text-center text-uppercase">Categoria</th>
                                    <th class="min-col text-center text-uppercase">Producto</th>
                                    <th class="min-col text-center text-uppercase">N° de piezas</th>
                                    <th class="min-col text-center text-uppercase">Cantidad de Elementos</th>
                                    <th class="min-col text-center text-uppercase">Costo extra</th>
                                    <th class="min-col text-center text-uppercase">Valor asegurado</th>
                                    <th class="min-col text-center text-uppercase">Peso</th>
                                    <th class="min-col text-center text-uppercase">Seguro</th>
                                    <th class="min-col text-center text-uppercase">Costo del seguro</th>
                                    <th class="min-col text-center text-uppercase">Estatus</th>

                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($folio->envioDetalles as $key => $item): ?>
                                    <tr>
                                        <td><?= Html::a($item->sucursalReceptor->nombre, ['/sucursales/sucursal/view', 'id' => $item->sucursalReceptor->id], ['class' => 'text-primary']) ?></td>
                                        <td><?=  Html::a($item->clienteReceptor->nombreCompleto, ['/crm/cliente/view', 'id' => $item->clienteReceptor->id], ['class' => 'text-primary']) ?></td>
                                        <td><?= isset($item->producto->categoria->singular) ? $item->producto->categoria->singular : '' ?></td>
                                        <td><?= $item->producto->nombre ?></td>
                                        <td><?= $item->cantidad ?></td>
                                        <td><?= $item->cantidad_piezas ?></td>
                                        <td><?= $item->impuesto ?> USD</td>
                                        <td><?= $item->valor_declarado ? $item->valor_declarado : 0  ?> USD</td>
                                        <td><?= $item->peso ?> Lbs</td>
                                        <td><?= $item->seguro == 1 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" ?></td>
                                        <td><?= $item->costo_seguro  ? $item->costo_seguro : 0 ?> USD</td>
                                        <td><?= EnvioDetalle::$statusList[$item->status] ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif ?>
        <?php if (isset($folio)): ?>
            <?php if ($folio->is_reenvio == Envio::REENVIO_ON): ?>
                <?php $formReenvio = ActiveForm::begin(['id' => 'form-metodo', 'action' => ['cobro-reenvio'] ]) ?>
                    <?= Html::hiddenInput('id', $folio->id) ?>

                    <div id="direccion_usa">
                         <div class="ibox">
                            <div class="ibox-title">
                                <h5 >Dirección USA</h5>
                            </div>
                            <div class="ibox-content">
                                <div class="row">
                                    <div class="col-sm-6">
                                         <div class="row">
                                            <div class="col-sm-5">
                                                <?= $formReenvio->field($folio->dir_obj, 'codigo_postal_usa')->textInput(['maxlength' => true]) ?>
                                            </div>
                                        </div>
                                        <?= $formReenvio->field($folio->dir_obj, 'estado_usa')->textInput(['maxlength' => true]) ?>
                                        <?= $formReenvio->field($folio->dir_obj, 'municipio_usa')->textInput(['maxlength' => true]) ?>
                                        <?= $formReenvio->field($folio->dir_obj, 'colonia_usa')->textInput(['maxlength' => true]) ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <?= $formReenvio->field($folio->dir_obj, 'direccion')->textInput(['maxlength' => true]) ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <?= $formReenvio->field($folio->dir_obj, 'num_ext')->textInput(['maxlength' => true]) ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $formReenvio->field($folio->dir_obj, 'num_int')->textInput(['maxlength' => true]) ?>
                                            </div>
                                        </div>
                                        <?= $formReenvio->field($folio->dir_obj, 'referencia')->textArea(['rows' => 6 ]) ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <?= $formReenvio->field($folio, 'costo_reenvio')->textInput(['maxlength' => true]) ?>
                                    </div>
                                    <div class="col-sm-2">
                                        <?= Html::submitButton( '  Guardar reenvio', ['class' => 'btn btn-primary  btn-lg', "style" => "margin-top: 15px;"]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?>
            <?php endif ?>
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

<div class="fade modal" id="modal-ticket"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-money mar-rgt-5px icon-lg"></i> Generar Ticket (Reclamación) </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="ibox">
                        <div class="ibox-content">
                            <div class="text-center">
                                <h1>Diferencia</h1>
                                <h3><?= isset($lb_dif) ? $lb_dif : 0  ?> lb</h3>
                            </div>
                            <div class="row">
                                <div class="col-sm-8 col-sm-offset-2">
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
                                            <h3 id="code-ticket"></h3>
                                        </div>
                                    </div>
                                    <div class="alert  alert-info-basic" style="display: none">
                                    </div>
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
        'total' => $total,
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
    $form_metodoPago = {
        $metodoPago : $('#cobrorembolsoenvio-metodo_pago'),
        $cantidad   : $('#cobrorembolsoenvio-cantidad'),
    };
    pesoTotal                = <?= (isset($total) ? $total : 0) ?>;
    pesoTotalFinal           = <?= (isset($total) ? $total : 0) ?>;
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

$descuento_manual_check.change(function(){
    if($(this).prop('checked')){
        $descuento_manual.prop('disabled',false);
    }
    else{
        $descuento_manual.prop('disabled',true);
        $descuento_manual.val(0);
        pesoTotalFinal = pesoTotal;
        calcula_totales(pesoTotalFinal);
    }

});

$descuento_manual.change(function(){
    pesoTotalFinal = pesoTotalFinal - parseFloat(($descuento_manual.val() ? $descuento_manual.val()  : 0 ));
    calcula_totales(pesoTotalFinal);
});

$('#imprimir-ticket').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['envio-mex/imprimir-ticket', 'id' => isset($folio->id) ? $folio->id : 0 ])  ?>",
    'imprimir',
    'width=600,height=500');
});
</script>
