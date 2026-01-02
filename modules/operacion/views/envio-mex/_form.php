<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\envio\Envio;
use app\models\cliente\Cliente;
use app\assets\BootboxAsset;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\esys\EsysListaDesplegable;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysSetting;
use app\models\producto\ProductoDetalle;

BootboxAsset::register($this);

?>

<style>
    .fixed-input{
        color: #fff;
        cursor: pointer;
        opacity: 1;
        padding: 10px;
        box-shadow: none;
        background: #d4a160;
    }

    .modal-backdrop{
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;;
    }
</style>


<div class="operacion-envio-form">
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-danger alert-danger-envio" style="display: none">
            </div>
            <div class="ibox">

                <div class="ibox-content">
                        <!-- Main Form Wizard -->
                        <!--===================================================-->
                            <!--form-->
                            <?php $form = ActiveForm::begin(['id' => 'form-envios']) ?>

                                <?= $form->field($model->envio_detalle, 'envio_detalle_array')->hiddenInput()->label(false) ?>

                                <?= $form->field($model, 'is_reenvio')->hiddenInput()->label(false) ?>

                                <?= $form->field($model->cobroRembolsoEnvio, 'cobroRembolsoEnvioArray')->hiddenInput()->label(false) ?>
                                <?php //$form->field($model, 'promocion_id')->hiddenInput()->label(false) ?>
                                <?php // $form->field($model, 'promocion_detalle_id')->hiddenInput()->label(false) ?>
                                <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>



                                    <div class="row">
                                        <div class="col-sm-6" >
                                            <div class="form_emisor">
                                                <div class="row">
                                                    <div class="col-sm-2" style="   margin-top: 4%;">
                                                        <button  type="button" data-cliente = "Emisor" data-target="#modal-create-user" data-toggle="modal"  class="modal-create btn  btn-circle <?= $model->cliente_receptor->id  ?  'btn-danger' : 'btn-primary' ?>" ><i id="icon_emisor" class="fa <?=  $model->cliente_receptor->id ? 'fa fa-edit' : 'fa-users'  ?>   solid icon-lg"></i
                                                        ></button>
                                                    </div>
                                                    <div class="col-sm-10">
                                                        <?= $form->field($model, 'cliente_emisor_id')->widget(Select2::classname(),
                                                            [
                                                            'language' => 'es',
                                                                'data' => isset($model->clienteEmisor->id) ? [$model->clienteEmisor->id => $model->clienteEmisor->nombre ." ". $model->clienteEmisor->apellidos] : [],
                                                                'pluginOptions' => [
                                                                    'allowClear' => true,
                                                                    'minimumInputLength' => 3,
                                                                    'language'   => [
                                                                        'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                    ],
                                                                    'ajax' => [
                                                                        'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                                                                        'dataType' => 'json',
                                                                        'cache'    => true,
                                                                        'processResults' => new JsExpression('function(data, params){ clienteEmisor = data; return {results: data} }'),
                                                                    ],
                                                                ],
                                                                'options' => [
                                                                    'placeholder' => 'Selecciona al usuario...',
                                                            ],
                                                        ]) ?>
                                                    </div>
                                                </div>
                                                <div class="row" style="border-color: #000;border-style: solid; border-width: 1px;margin: 20px;">
                                                    <div class="col-sm-12">
                                                        <ul class="list-unstyled m-t-md">
                                                            <li><h3 style="font-size: 10px"><strong>ENVIA: </strong><span class="lbl_cliente_envia_nombre"> N/A </span></h3></li>
                                                            <li>
                                                                <span class="fa fa-home m-r-xs"></span>Dirección: <label class="lbl_cliente_envia_direccion">N/A</label>
                                                            </li>
                                                            <li><h3 style="font-size: 10px"><strong>TELEFONO: </strong><span class="lbl_cliente_envia_telefono"></span></h3></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form_receptor">
                                                <div class="row">
                                                    <div class="col-sm-2">
                                                        <button data-cliente = "Receptor" type="button" data-target="#modal-create-user" data-toggle="modal"  class=" modal-create btn  btn-circle btn-primary" data-action="create" ><i id = "icon_receptor" class="fa fa-users"></i
                                                        ></button>
                                                    </div>
                                                    <div class="col-sm-10">
                                                        <?= $form->field($model, 'cliente_receptor_names[]')->widget(Select2::classname(),
                                                            [
                                                            'language' => 'es',
                                                                'data' => [],
                                                                'pluginOptions' => [
                                                                    'allowClear' => true,
                                                                    'minimumInputLength' => 3,
                                                                    'language'   => [
                                                                        'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                    ],
                                                                    'ajax' => [
                                                                        'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                                                                        'dataType' => 'json',
                                                                        'cache'    => true,
                                                                        'processResults' => new JsExpression('function(data, params){  clienteReceptor = data; return {results: data} }'),
                                                                    ],
                                                                ],
                                                                'options' => [
                                                                    'placeholder' => 'Selecciona al cliente...',
                                                                ],
                                                        ]) ?>
                                                    </div>
                                                </div>

                                                <div class="content_info_cliente">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div id="error-add-paquete" class="alert alert-danger" style="display: none"></div>


                                            <div class="row">
                                                <div class="col-sm-12">


                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <?= $form->field($model, 'sucursal_emisor_id')->dropDownList(Sucursal::getItemsMexico())->label("OFICINA QUE ENVIA") ?>

                                                        </div>
                                                        <div class="col-sm-6">
                                                            <h4>PRECIO DE LIBRA</h4>
                                                            <h2 class="precio_libra_venta">$ 0.00</h2>
                                                        </div>
                                                    </div>

                                                    <div class="form_paquete" >
                                                        <div class="alert alert-primary alert-categoria" style="display: none" >
                                                            <p>La categoria de este producto, tiene un cargo adicional : </p>
                                                            <div class="content_info"></div>
                                                        </div>
                                                        <div class="alert alert-info" id="producto_tipo" style="display: none">
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= Html::label('Producto', 'envio-producto_id', ['class' => 'control-label']) ?>

                                                                        <?= Select2::widget([
                                                                            'id' => 'enviodetalle-producto_id',
                                                                            'name' => 'EnvioDetalle[producto_id]',
                                                                            'language' => 'es',

                                                                            'pluginOptions' => [
                                                                                'allowClear' => true,
                                                                                'minimumInputLength' => 3,
                                                                                    'language'   => [
                                                                                        'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                                    ],
                                                                                    'ajax' => [
                                                                                        'url'      => Url::to(['/productos/producto/producto-tierra-ajax']),
                                                                                        'dataType' => 'json',
                                                                                        'cache'    => true,
                                                                                        'processResults' => new JsExpression('function(data, params){ searchProducto = data; return {results: data} }'),
                                                                                    ],
                                                                            ],
                                                                            'options' => [
                                                                                'placeholder' => 'Buscar producto'
                                                                            ],
                                                                        ]) ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= Html::button('<i class="fa fa-plus"></i> Crear producto', [ 'class' =>  'btn btn-small btn-primary btn-block btn-lg', 'style' => 'margin-top: 20px', 'data-target' => "#modal-create-producto", 'data-toggle' =>"modal", "onclick" => "init_producto()"]) ?>
                                                            </div>


                                                            <?php /* ?>
                                                            <div class="col-sm-4">
                                                                <?= $form->field($model->envio_detalle, 'unidad_medida_id')->dropDownList([],['prompt' => '', 'disabled' => true]) ?>
                                                            </div>

                                                            <div class="col-sm-4">
                                                                <div class="checkbox">
                                                                    <input id="seguro" class="magic-checkbox" type="checkbox" >
                                                                    <label for="seguro">Aseguranza</label>
                                                                </div>
                                                            </div>
                                                            */?>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-12 text-center">
                                                                <div class="div_paquete_cantidad" style="display: none;width: 100%;">

                                                                        <?= $form->field($model->envio_detalle, 'cantidad')->textInput(['type' => 'number', 'value' => '1', 'class' => 'text-center form-control' ]) ?>

                                                                </div>
                                                            </div>
                                                        </div>

                                                        <?php /* ?>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->envio_detalle, 'valor_declarado')->textInput(['type' => 'number' ])->label("Valor declarado (MXN)") ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->envio_detalle, 'peso')->textInput(['type' => 'number']) ?>
                                                            </div>
                                                        </div>
                                                            */?>


                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <?= $form->field($model->envio_detalle, 'observaciones')->textArea(['maxlength' => true]) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <button type="button" id="btnAgregar-paquete" class=" btn btn-block btn-lg btn-primary"><i class="fa fa-cube"></i> Agregar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div  class="fixed-input select-sucursal-recibe" style="display: none;">
                                                <div class="col-sm-12">
                                                    <h4>Sucursal que recibe</h4>
                                                    <div class="div_sucursal_receptor">
                                                        <?= $form->field($model, 'sucursal_receptor_names[]')->widget(Select2::classname(),
                                                            [
                                                        'language' => 'es',
                                                            'data' => Sucursal::getItemsUsa(),
                                                            'options' => [
                                                                'placeholder' => 'Sucursales receptor',
                                                            ],
                                                            'pluginOptions' => [
                                                                'allowClear' => true
                                                            ],

                                                        ]) ?>

                                                        <div class="form-group div_destino_receptor" style="display: none" >
                                                            <?= Html::label('DESTINOS', 'destino_receptor_id', ['class' => 'control-label']) ?>
                                                            <?= Html::dropDownList('destino_receptor_id',null,[],[ 'id' => 'destino_receptor_id','class' => 'form-control', 'style' => 'color:#000']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table " style="font-size: 9px;">
                                                    <thead>
                                                        <tr class="bg-trans-dark">
                                                            <th class="min-col text-center text-uppercase">Producto</th>
                                                            <th class="min-col text-center text-uppercase">N° de piezas</th>
                                                            <th class="min-col text-center text-uppercase">Costo extra</th>
                                                            <th class="min-col text-center text-uppercase">Valor declarado</th>
                                                            <th class="min-col text-center text-uppercase">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="content_paquete" style="text-align: center;">

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <br>
                                    <div class="row">
                                        <div class="col-sm-4 offset-sm-2">
                                            <h2 style="color: #000">VALOR DECLARADO</h2>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('number', 'valor_declarado_total', $model->valor_declarado_total,[ 'id' => 'valor_declarado_total','class' => 'form-control text-center', "style" => "font-size:24px", "autocomplete" => "off"]) ?>
                                                <span class="input-group-addon">$</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <h2 style="color: #000">PESO TOTAL DE ENVIO</h2>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('number', 'peso_mex_con_empaque', $model->peso_mex_con_empaque,[ 'id' => 'peso_mex_con_empaque','class' => 'form-control text-center', "style" => "font-size:24px","autocomplete" => "off"]) ?>
                                                <span class="input-group-addon">LB</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row totales cobros ">
                                        <div class="col-sm-4">
                                            <h2 style="color: #000">Subtotal</h2>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('number', 'subtotal_total_envio', $model->subtotal,[ 'id' => 'subtotal_total_envio','class' => 'form-control text-center', 'autocomplete' => 'off', 'readonly' => true]) ?>
                                                <span class="input-group-addon">USD</span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <h2 style="color: #000">Costo extra</h2>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('text', 'costo_extra_total_envio',null,[ 'id' => 'costo_extra_total_envio','class' => 'form-control text-center','readonly' => true]) ?>
                                                <span class="input-group-addon">USD</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <h2 style="color: #000">Total</h2>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('text', 'total_envio',null,[ 'id' => 'total_envio','class' => 'form-control text-center','readonly' => true, 'autocomplete' => 'off']) ?>
                                                <span class="input-group-addon">USD</span>
                                            </div>
                                            <br>
                                            <div class="alert alert-warning alert-descuento-total" style="display:none">
                                                <strong>SE REGISTRARA UN INDICADOR DEL AJUSTE AL TOTAL </strong>
                                            </div>
                                            <?=  $form->field($model, 'is_descuento_manual')->checkbox() ?>
                                        </div>
                                    </div>

                                    <?php if (Yii::$app->user->can('envioMexCobro')): ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h3>¿ Deseas ingresar un pago ?</h3>
                                                <div class="row" style="border-style: double;padding: 2%;">
                                                    <div class="col-sm-4">
                                                        <?= $form->field($model->cobroRembolsoEnvio, 'metodo_pago')->dropDownList(CobroRembolsoEnvio::$servicioList )->label("&nbsp;") ?>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?= $form->field($model->cobroRembolsoEnvio, 'cantidad')->textInput(['maxlength' => true]) ?>
                                                    </div>
                                                    <div class="col-sm-2">
                                                         <button  type="button"class="btn  btn-primary" id="btnAgregarMetodoPago" style="margin-top: 15px;" >Ingresar pago</button>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6" style="margin-top: 3%;">
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
                                                            <td class="text-right" style="border: none" colspan="2"><span class="text-main text-semibold">Efectivo (Cobro): </span></td>
                                                            <td><strong id= "pago_metodo_total">0 USD</strong></td>
                                                        </tr>

                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif ?>


                                    <div class="form-group">
                                        <?= $form->field($model, 'comentarios')->textArea(['maxlength' => true]) ?>
                                    </div>



                                <!--Footer buttons-->
                                    <div class="form-group">
                                        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
                                        <?= Html::submitButton($model->isNewRecord ? 'Crear envio' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'finish btn btn-success' : ' finish btn btn-primary', 'id' => 'btnSaveEnvio']) ?>
                                    </div>
                            <?php ActiveForm::end(); ?>
                        <!--===================================================-->
                        <!-- End of Main Form Wizard -->

                </div>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_paquete">
            <tr id = "paquete_id_{{paquete_id}}">
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_producto_text"]) ?></td>
                <td ><?= Html::input('number', "",false,["class" => "form-control" , "id"  => "table_cantidad","style" => "text-align:center"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_costo_extra"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_valor_declarado"]) ?></td>
            </tr>
        </tbody>
    </table>
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



<div class="display-none">
    <div class="template_info_cliente">
        <div id ="cliente_info_id_{{cliente_info_id}}">
            <div class="row" style="border-color: #000;border-style: solid; border-width: 1px;margin: 20px;">
                <button data-cliente = "Receptor"  data-action="Update" type="button" data-target="#modal-create-user" data-toggle="modal" style="position: absolute;z-index: 100;right: 15%;" class="modal-create btn  btn-circle btn-danger" ><i id = "icon_receptor"  class="fa fa-edit" ></i
                ></button>
                <div class="col-sm-12">
                    <ul class="list-unstyled m-t-md">
                        <li><h3 style="font-size: 10px"><strong>RECIBE: </strong><span class="lbl_cliente_recibe_nombre"> N/A </span></h3></li>
                        <li>
                            <span class="fa fa-home m-r-xs"></span>Dirección: <label class="lbl_cliente_recibe_direccion">N/A</label>
                        </li>
                        <li><h3 style="font-size: 10px"><strong>TELEFONO: </strong><span class="lbl_cliente_recibe_telefono"></span></h3></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-create-producto"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Agregar producto</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <?php $formProducto = ActiveForm::begin(['id' => 'form-producto']) ?>
                    <div id="error-add-producto" class="alert alert-danger" style="display: none">
                    </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <?= $formProducto->field($producto, 'nombre')->textInput(['maxlength' => true]) ?>
                                    <?= $formProducto->field($producto, 'unidad_medida_id')->dropDownList(EsysListaDesplegable::getItems('unidad_de_uso'), ['prompt' => '-- Medida --']) ?>
                                </div>
                                <div class="col-sm-6">
                                    <?= $formProducto->field($producto, 'categoria_id')->dropDownList(EsysListaDesplegable::getItems('categoria_paquete_mex'), ['prompt' => '-- Categoria --']) ?>
                                </div>
                            </div>



                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Guardar cambios', ['class' => 'btn btn-primary' , 'id' => 'send_producto']) ?>
            </div>
        </div>
    </div>
</div>


<script>

    /*******************************************
    *              Tab 1 Envio
    ********************************************/

    var $sucursal_emisor_id     = $('#envio-sucursal_emisor_id'),
        //$sucursal_receptor_id   = $('#envio-sucursal_receptor_id'),
        $tipo_envio             = $('#envio-tipo_envio'),
        $editDescuentoCheck     = $('#envio-is_descuento_manual'),
        $inputPrecioLibraActual = $('#envio-precio_libra_actual'),
        $tamplate_info_sucursal = $('.tamplate_info_sucursal'),
        $template_info_cliente  = $('.template_info_cliente'),
        $content_info_cliente   = $('.content_info_cliente'),
        $sucursal_receptor_id   = $('#envio-sucursal_receptor_names'),
        $destino_receptor_id   = $('#destino_receptor_id'),
        $inputcobroRembolsoEnvioArray = $('#cobrorembolsoenvio-cobrorembolsoenvioarray'),
        sucursal_tipo           = JSON.parse('<?= json_encode(Sucursal::$tipoList)  ?>'),
        edit_load_sucursal      = JSON.parse('<?= json_encode($model->sucursal_receptor_names)  ?>'),
        edit_load_cliente       = JSON.parse('<?= json_encode($model->cliente_receptor_names)  ?>'),
        producto_tipo           = JSON.parse('<?= json_encode(ProductoDetalle::$tipoList)  ?>'),

        estadoList                  = JSON.parse('<?= json_encode(EsysListaDesplegable::getEstados())  ?>'),
        metodoPagoList              = JSON.parse('<?= json_encode(CobroRembolsoEnvio::$servicioList)  ?>'),
        $inputEnvioDetalleArray     = $('#enviodetalle-envio_detalle_array'),
        $error_add_paquete          = $('#error-add-paquete'),
        $paquete_sucursal_id        = $("#paquete_sucursal_id"),
        $paquete_cliente_id         = $("#paquete_cliente_id"),
        //$btnAplicaReenvio           = $("#btnAplicaReenvio"),
        //$isAplicaReenvio            = $("#envio-is_reenvio"),
        $envioID                    = $('#envio-id'),
        $template_metodo_pago       = $('.template_metodo_pago'),
        $content_metodo_pago        = $(".content_metodo_pago"),
        $inputCheckRegistro         = $("#input-check-registro"),
        $btnAgregarMetodoPago       =  $('#btnAgregarMetodoPago');

    var $form_envios             = $('#form-envios'),
        $content_tab             = $('#demo-main-wz'),
        $cliente_emisor          = $('#envio-cliente_emisor_id'),
        $cliente_receptor        = $('#envio-cliente_receptor_names'),
        $form_emisor_content     = $('.form_emisor'),
        $form_receptor_content   = $('.form_receptor'),
        $form_paquete_content    = $('.form_paquete'),
        $template_paquete        = $('.template_paquete'),
        $btnAgregarPaquete       =  $('#btnAgregar-paquete'),
        $alertCategoria          = $('.alert-categoria'),
        $content_paquete         = $(".content_paquete");
        $btnSaveEnvio            = $("#btnSaveEnvio"),

        $send_producto          =  $("#send_producto"),
        $formProducto           = $('#form-producto');
        $error_add_producto     = $('#error-add-producto'),
        $selectTipoServicio = $('select[name = "Producto[tipo_servicio]"]'),
        $selectCategoriaID  = $('select[name = "Producto[categoria_id]"]'),
        $selectUnidad       = $('select[name = "Producto[unidad_medida_id]"]'),
        $formNombre         = $('input[name = "Producto[nombre]"]');

        /**************************************************/
        /*             HIDE / SHOW INFORMACION DE CLIENTES
        /**************************************************/
    var $is_div_info_emisor   = false;
        $is_div_info_receptor = false;
        $link_info_emisor     = $('#link_info-emisor');
        $link_info_receptor   = $('#link_info-receptor');
        $div_info_emisor      = $('.info-emisor');
        $div_info_receptor    = $('.info-receptor');
        $div_info_receptor.inputText = {
            $estado      : $("input[name = 'estado_id']", $div_info_receptor),
            $municipio   : $("input[name = 'municipio_id']", $div_info_receptor),
            $colonia     : $("input[name = 'colonia_id']", $div_info_receptor),
            $direccion   : $("input[name = 'direccion_id']", $div_info_receptor),
            $num_exterior: $("input[name = 'num_exterior']", $div_info_receptor),
            $num_interior: $("input[name = 'num_interior']", $div_info_receptor),
            $referencia  : $("input[name = 'referencia']", $div_info_receptor),

        };
        $div_info_emisor.inputText = {
            $estado      : $("input[name = 'estado_id']", $div_info_emisor),
            $municipio   : $("input[name = 'municipio_id']", $div_info_emisor),
            $colonia     : $("input[name = 'colonia_id']", $div_info_emisor),
            $direccion   : $("input[name = 'direccion_id']", $div_info_emisor),
            $num_exterior: $("input[name = 'num_exterior']", $div_info_emisor),
            $num_interior: $("input[name = 'num_interior']", $div_info_emisor),


        };
        /**************************************************/

        $form_emisor = {
            $nombre     : $('#cliente-nombre',$form_emisor_content),
            $apellidos  : $('#cliente-apellidos',$form_emisor_content),
            $telefono   : $('#cliente-telefono',$form_emisor_content),
            $telefono_movil : $('#cliente-telefono_movil',$form_emisor_content),
            $btn_icon  : $('button',$form_emisor_content),

         };


        $form_paquete = {
            $categoria       : $('#enviodetalle-categoria_id', $form_paquete_content),
            $producto        : $('#enviodetalle-producto_id', $form_paquete_content),
            $producto_tipo  : $('#producto_tipo', $form_paquete_content),
            $producto_costo_extra  : $('#producto_costo_extra', $form_paquete_content),
            $cantidad        : $('#enviodetalle-cantidad',$form_paquete_content ),
            $peso        : $('#enviodetalle-peso',$form_paquete_content ),
            $valor_declarado : $('#enviodetalle-valor_declarado', $form_paquete_content),
            $unidad_medida_id : $('#enviodetalle-unidad_medida_id', $form_paquete_content),
            $observacion : $('#enviodetalle-observaciones', $form_paquete_content),
            $seguro : $('#seguro', $form_paquete_content),

        };

        $form_receptor = {
            $nombre     : $('#cliente-nombre',$form_receptor_content),
            $apellidos  : $('#cliente-apellidos',$form_receptor_content),
            $telefono   : $('#cliente-telefono',$form_receptor_content),
            $telefono_movil : $('#cliente-telefono_movil',$form_receptor_content),
            $btn_icon  : $('button',$form_receptor_content),

        };

        $form_metodoPago = {
            $metodoPago : $('#cobrorembolsoenvio-metodo_pago'),
            $cantidad   : $('#cobrorembolsoenvio-cantidad'),
        };

        paquete_array      = [];
        clienteReceptor    = [];
        clienteEmisor      = [];
        metodoPago_array   = [];
        productoCategoria  = [];
        productoDetalle    = {};
        municipioList      = [];
        categoriaList       = [];
        sucursalSelect     = [];
        searchProducto     = [];
        selectProducto_array    = {};
        clienteSelect           = [];
        isEmisorCreate     = false;
        isReceptorCreate   = false;
        isEmisorCreate     = false;
        isEmisorEdit       = false;
        precioLibra        = 0;



$(function(){




    load_categoria();
    init_paquete_list();
    render_paquete_template();

    /*====================================================
    *               AGREGA UN ITEM A ARRAY
    *====================================================*/
    $btnAgregarPaquete.click(function(){
        if(validation_form_envio()){
            return false;
        }


        paquete = {
                "paquete_id"            : paquete_array.length + 1,
                "categoria_id"          : selectProducto_array.categoria_id,
                "cantidad"              : $form_paquete.$cantidad.val(),
                "producto_id"           : $form_paquete.$producto.val(),
                "producto_id_text"      : $('option:selected', $form_paquete.$producto).text(),
                "pro_costo_extra"       : 0,
                "cat_costo_extra"       : 0,
                "producto_costo_extra"  : 0,
                //"pro_costo_extra"       : pro_costo_extra,
                //"cat_costo_extra"       : cat_costo_extra,
                //"producto_costo_extra"  : costo_extra,
                "categoria_is_mex"         : selectProducto_array.is_mex,
                "categoria_intervalo"      : selectProducto_array.mex_intervalo,
                "categoria_costo_extra"    : selectProducto_array.mex_costo_extra ,
                "categoria_required_min"   : selectProducto_array.mex_required_min ,

                "precio_intervalo"      : selectProducto_array.intervalo,
                "precio_costo_extra"    : selectProducto_array.costo_extra ,
                "precio_required_min"   : selectProducto_array.required_min ,
                "observaciones"         : $form_paquete.$observacion.val(),
                "seguro"                : $form_paquete.$seguro.prop('checked') ? true : false,
                "valor_declarado"       : $form_paquete.$valor_declarado.val(),
                "peso"                  : 0, //$form_paquete.$peso.val(),
                //"costo_seguro"          : $form_paquete.$seguro.prop('checked') ? ( 0  * parseFloat($form_paquete.$valor_declarado.val())) / 100 : 0,
                "status"                : 10,
                "update"                : $envioID.val() ? 10 : 1,
                "origen"                : 1
        };

        paquete_array.push(paquete);

        render_paquete_template();

        clear_form($form_paquete);
        $form_paquete.$producto.html('');
        $form_paquete.$producto.val(null).change();
        $form_paquete.$cantidad.val(1);
        $form_paquete.$producto_tipo.hide();
        $form_paquete.$unidad_medida_id.html('');
        $alertCategoria.hide();
        $error_add_paquete.hide();
        $('#valor_declarado_total').val( $('#valor_declarado_total').val() ? $('#valor_declarado_total').val() : 0 )

    });



    var validation_form_envio = function()
    {
        $error_add_paquete.html('');
        switch(true){
            case !$form_paquete.$producto.val() :
                $error_add_paquete.append('<div class="help-block">* Selecciona un producto</div>');
                $error_add_paquete.show();
                return true;
            break;

            case !$form_paquete.$cantidad.val() :
                $error_add_paquete.append('<div class="help-block">* N° de piezas no puede ser nulo</div>');
                $error_add_paquete.show();
                return true;
            break;
            /*
            case !$form_paquete.$valor_declarado.val():
                $error_add_paquete.append('<div class="help-block">* Ingresa el valor del paquete</div>');
                $error_add_paquete.show();
                return true;
            break;*/

            /*case parseFloat($form_paquete.$valor_declarado.val()) > 999:

                $error_add_paquete.append('<div class="help-block">* El valor declarado no puede ser MAYOR A $ 1000.00</div>');
                $error_add_paquete.show();
                return true;

            break;*/

        }
    }

    /*====================================================
    *               OPEN MODAL
    *====================================================*/

    $(".modal-create").click(function(){
        $("#modal-title-cliente").html($(this).data("cliente"));

        userInfo = [];
        clear_form($modal);
        clear_form($form_cliente);
        clear_form($form_esysdireccion);
        $form_esysdireccion.$inputEstado.val(null).trigger('change');
        $form_esysdireccion.$inputMunicipio.html('');
        $form_esysdireccion.$inputColonia.html('');

        $('#form-cliente').html("Crear cliente");
        if ($.trim($(this).data("cliente")) == 'Emisor' ){
            isEmisorCreate = true ;
            isReceptorCreate = false;
            if ($cliente_emisor.val()) {
                $.get("<?= Url::to(['/crm/cliente/cliente-ajax'])  ?>",{ cliente_id: $cliente_emisor.val() }, function(cliente_json){
                    if (cliente_json) {
                        userInfo = cliente_json.results;
                        $form_cliente.$id.val(cliente_json.results.id);
                        $form_cliente.$nombre.val(cliente_json.results.nombre);
                        $form_cliente.$apellidos.val(cliente_json.results.apellidos);
                        $form_cliente.$inputOrigen.val(cliente_json.results.origen).trigger('change');
                        $form_cliente.$telefono.val(cliente_json.results.telefono);
                        $form_cliente.$telefono_movil.val(cliente_json.results.telefono_movil);
                        $form_esysdireccion.$inputDireccion.val(cliente_json.results.direccion);
                        $form_esysdireccion.$inputNumeroExt.val(cliente_json.results.num_ext);
                        $form_esysdireccion.$inputNumeroInt.val(cliente_json.results.num_int);
                        $form_esysdireccion.$inputReferencia.val(cliente_json.results.referencia);
                        $form_esysdireccion.$inputCodigoPostalUsa.val(cliente_json.results.codigo_postal_usa);
                        $form_esysdireccion.$inputEstadoUsa.val(cliente_json.results.estado_usa);
                        $form_esysdireccion.$inputMunicipioUsa.val(cliente_json.results.municipio_usa);
                        $form_esysdireccion.$inputColoniaUsa.val(cliente_json.results.colonia_usa);
                        $form_esysdireccion.$inputColonia.val(cliente_json.results.colonia);

                       if (cliente_json.results.origen == <?= Envio::ORIGEN_MX  ?> ) {
                            if (!cliente_json.results.codigo_postal)
                               $form_esysdireccion.$inputEstado.val(cliente_json.results.estado_id ? cliente_json.results.estado_id  : 0).trigger('change');
                            else
                                $form_esysdireccion.$inputCodigoSearch.val(cliente_json.results.codigo_postal).trigger('change');
                        }

                       $('#form-cliente').html("Guardar cambios");
                    }
                });
            }
        }else if( $.trim($(this).data("cliente")) == 'Receptor'){
            isReceptorCreate = true;
            isEmisorCreate = false;
        }
    });


    /*====================================================
    *   Obtiene la informacion del producto
    *====================================================*/



    $form_paquete.$producto.change(function(){
        $('.div_paquete_cantidad').hide();
        selectProducto_array = {};
        function_costo_extra();
        if ($sucursal_receptor_id.val()) {
            $.get("<?= Url::to(['get-producto-mex']) ?>", { sucursal_receptor_id : $sucursal_receptor_id.val(), producto_id : $form_paquete.$producto.val(), destino_id : $destino_receptor_id.val() }, function($responseProductoMex){

                    selectProducto_array = $responseProductoMex.producto;
                    function_costo_extra();

                    if (parseInt($responseProductoMex.producto.categoria_id) == 2677 ) {
                        $('.div_paquete_cantidad').show();
                    }else{
                        $('.div_paquete_cantidad').hide();
                    }

            },'json');
        }else{
            $form_paquete.$producto.val(null);
            alert("DEBES SELECCIONAR UNA SUCURSAL QUE RECIBE");
        }

        /*$.each(searchProducto,function(key,item){
            if (item.id) {
                if ($form_paquete.$producto.val() == item.id) {

                }
            }
        });*/

    });

});

var function_change_receptor = function(){
    bootbox.prompt("TELEFONO DEL CLIENTE RECEPTOR", function(result){
        if (result) {
            $('#envio-telefono_receptor').val(result);
            $('.lbl_cliente_recibe_telefono').html(result);
        }
    });
}


$editDescuentoCheck.change(function(){
    $('#total_envio').attr('readonly',true);
    $('.alert-descuento-total').hide();
    if ($editDescuentoCheck.is(':checked')) {
        $('#total_envio').attr('readonly',false);
        $('.alert-descuento-total').show();
    }
});

$inputCheckRegistro.change(function(){
    $('.div_created_temp').hide();
    if ($inputCheckRegistro.is(':checked')) {
        $('.div_created_temp').show();
    }
});

var mayusculas = function(element){
     element.value = element.value.toUpperCase();
}

/*============================================================================
*                           TAB EMISOR / RECEPTOR
*=============================================================================*/

var function_costo_extra = function (){
    $alertCategoria.hide();
    $form_paquete.$producto_tipo.hide();
    $form_paquete.$producto_tipo.html('');
    $form_paquete.$producto_costo_extra.val('');

    if (selectProducto_array) {
        if (selectProducto_array.is_mex == 10) {
            $alertCategoria.show();
            $('.content_info', $alertCategoria).html('');
            $('.content_info', $alertCategoria).append('<strong> Apartir de: ' + selectProducto_array.mex_required_min +' piezas  / Costo extra: ' + selectProducto_array.mex_costo_extra +' USD / Intervalo '+ selectProducto_array.mex_intervalo + '</strong>');
        }else
            $alertCategoria.hide();

        if(selectProducto_array.costo_extra){
            $form_paquete.$producto_tipo.show();
            $form_paquete.$producto_tipo.html('<strong>Aviso!</strong>  Te genera cargos extra aparti de: ' + selectProducto_array.required_min +'  <strong>'+ selectProducto_array.unidad_medida +'</strong> por cada : ' + selectProducto_array.intervalo + ' pieza más que envies' );
        }
    }
}

/*=================================
*       CARGA DATOS EMISOR
*==================================*/
$cliente_emisor.change(function(){

    if($(this).val() == '' || $(this).val() == null){
        $form_emisor.$btn_icon.removeClass('btn-danger').addClass('btn-primary');
        $('#icon_emisor').removeClass('pli-pencil').addClass('pli-add-user');
        $('#btnTelefonoEmisor').attr('disabled', true);

    }else{
        $form_emisor.$btn_icon.removeClass('btn-primary').addClass('btn-danger');
        $('#icon_emisor').removeClass('pli-pencil').addClass('pli-add-user');
        $('#btnTelefonoEmisor').attr('disabled', false);
    }

    if($(this).val() == '' || $(this).val() == null){ clear_form($form_emisor); clear_form($div_info_emisor.inputText); return false; }
    $('#icon_emisor').removeClass('pli-add-user').addClass('pli-pencil');
    $form_emisor.$btn_icon.removeClass('btn-primary').addClass('btn-danger');

    key = search_item($(this).val(),clienteEmisor);


    $('.lbl_cliente_envia_nombre').html(clienteEmisor[key].text);
    $('.lbl_cliente_envia_telefono').html(clienteEmisor[key].telefono +" / "+ clienteEmisor[key].telefono_movil);

    var_direccion = "";

    if ( clienteEmisor[key].origen == 1 )
        var_direccion +=  " USA [ " + clienteEmisor[key].estado_usa +" ";
    else
        var_direccion +=  " MEX [ " + estadoList[clienteEmisor[key].estado_id] +" ";


    $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : clienteEmisor[key].estado_id}, function(json) {
        municipioList = json;

        if ( clienteEmisor[key].origen == 1 )
            var_direccion +=  clienteEmisor[key].municipio_usa +" ";
        else
            var_direccion +=  municipioList[clienteEmisor[key].municipio_id] +" ";


        if ( clienteEmisor[key].origen == 1 )
            var_direccion +=  clienteEmisor[key].colonia_usa +" ";
        else
            var_direccion +=  clienteEmisor[key].colonia +" ";


        var_direccion += clienteEmisor[key].direccion + " ";
        var_direccion += clienteEmisor[key].num_ext + " ";
        var_direccion += clienteEmisor[key].num_int + " ]";

        $('.lbl_cliente_envia_direccion').html(var_direccion);
        $('#btnTelefonoEmisor').data( "cliente_id", clienteEmisor[key].id );

    }, 'json');

});


$('#btnTelefonoEmisor').click(function(){
    bootbox.prompt("TELEFONO DEL CLIENTE EMISOR", function(result){
        if (result) {
            $('#envio-telefono_emisor').val(result);
            $('.lbl_cliente_envia_telefono').html(result);
        }
    });
});



/*=====================================
*       CARGA DATOS RECEPTOR
*======================================*/
$cliente_receptor.change(function(){

    $content_info_cliente.html('');
    $sucursal_receptor_id.val(null).trigger('change');
    $('.select-sucursal-recibe').hide();

    clienteSelect = [];
    if ($(this).val()) {

        $.get('<?= Url::to(['cliente-info-ajax']) ?>', { q  : $(this).val() },function(json){
          if (json) {

            clienteSelect.push(json);

            $('.btnTelefonoReceptor').attr('disabled', false);
            $('.btnTelefonoReceptor').data('cliente_id', json.id);

            template_info_cliente = $template_info_cliente.html();
            template_info_cliente = template_info_cliente.replace('{{cliente_info_id}}', json.id);
            $content_info_cliente.append(template_info_cliente);
            $div_receptor        =  $("#cliente_info_id_" + json.id, $content_info_cliente );


            $('button', $div_receptor).attr("data-id", json.id);
            $("button",$div_receptor).attr("onclick","load_cliente_receptor(this)");

            $('.lbl_cliente_recibe_nombre'   , $div_receptor ).html(json.text);

            $('.lbl_cliente_recibe_telefono', $div_receptor ).html(json.telefono +" / " + json.telefono_movil);

            var_direccion_recibe = "";

            if ( json.origen == 1 )
                var_direccion_recibe +=  " USA [ " + json.estado_usa +" ";
            else
                var_direccion_recibe +=  " MEX [ " + estadoList[json.estado_id] +" ";



            $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : json.estado_id}, function($responseMunicipio) {
                municipioList = $responseMunicipio;


                if ( json.origen == 1 )
                    var_direccion_recibe +=  json.municipio_usa +" ";
                else
                    var_direccion_recibe +=  municipioList[json.municipio_id] +" ";

                if ( json.origen == 1 )
                    var_direccion_recibe +=  json.colonia_usa +" ";
                else
                    var_direccion_recibe +=  json.colonia +" ";
                var_direccion_recibe += json.direccion + " ";
                var_direccion_recibe += json.num_ext + " ";
                var_direccion_recibe += json.num_int + " ]";
                $('.lbl_cliente_recibe_direccion').html(var_direccion_recibe);

            }, 'json');

            if (!$envioID.val()) {
                $sucursal_receptor_id.val(json.sucursal_id).trigger('change');
            }
            //$('.container-color').css("background", json.sucursal_color);

          }
        });
    }
    $('.select-sucursal-recibe').show();

});


var load_cliente_receptor = function(elem){
    $("#modal-title-cliente").html($(elem).data("cliente"));
    clear_form($modal);
    clear_form($form_cliente);
    clear_form($form_esysdireccion);
    $form_esysdireccion.$inputEstado.val(null).trigger('change');
    $form_esysdireccion.$inputMunicipio.html('');
    $form_esysdireccion.$inputColonia.html('');
    $('#form-cliente').html("Crear cliente");

    if( $.trim($(elem).data("cliente")) == 'Receptor'){
      isReceptorCreate  = true;
      isEmisorCreate    = false;
      isEmisorEdit      = false;

      $is_action = $.trim($(elem).data("action"));
        if ($cliente_receptor.val().length > 0 && $is_action == 'Update' ) {
            $is_id = $.trim($(elem).data("id"));
            $.get("<?= Url::to(['/crm/cliente/cliente-ajax'])  ?>",{ cliente_id: $is_id }, function(cliente_json){
                if (cliente_json) {
                  isEmisorEdit   = true;
                  userInfo = cliente_json.results;
                  $form_cliente.$id.val(cliente_json.results.id);
                  $form_cliente.$nombre.val(cliente_json.results.nombre);
                  $form_cliente.$apellidos.val(cliente_json.results.apellidos);
                  $form_cliente.$inputOrigen.val(cliente_json.results.origen).trigger('change');
                  $form_cliente.$telefono.val(cliente_json.results.telefono);
                  $form_cliente.$telefono_movil.val(cliente_json.results.telefono_movil);
                  $form_esysdireccion.$inputDireccion.val(cliente_json.results.direccion);
                  $form_esysdireccion.$inputNumeroExt.val(cliente_json.results.num_ext);
                  $form_esysdireccion.$inputNumeroInt.val(cliente_json.results.num_int);
                  $form_esysdireccion.$inputReferencia.val(cliente_json.results.referencia);
                  $form_esysdireccion.$inputCodigoPostalUsa.val(cliente_json.results.codigo_postal_usa);
                  $form_esysdireccion.$inputEstadoUsa.val(cliente_json.results.estado_usa);
                  $form_esysdireccion.$inputMunicipioUsa.val(cliente_json.results.municipio_usa);
                  $form_esysdireccion.$inputColoniaUsa.val(cliente_json.results.colonia_usa);
                  $form_esysdireccion.$inputColonia.val(cliente_json.results.colonia);

                   if (cliente_json.results.origen == <?= Envio::ORIGEN_MX  ?> ) {
                       if (!cliente_json.results.codigo_postal)
                           $form_esysdireccion.$inputEstado.val(cliente_json.results.estado_id ? cliente_json.results.estado_id  : 0).trigger('change');
                       else
                            $form_esysdireccion.$inputCodigoSearch.val(cliente_json.results.codigo_postal).trigger('change');

                       //$form_esysdireccion.$inputColonia.val(cliente_json.results.codigo_postal_id);
                   }
                   $('#form-cliente').html("Guardar cambios");
                }
            });
        }
    }
}

/*============================================================================
*                           TAB SUCURSAL
*=============================================================================*/

  $send_producto.click(function(){
        if(validation_form_producto()){
            return false;
        }
        $.post("<?= Url::to(['send-producto-ajax']) ?>",  $formProducto.serialize() ,function(json){
            if (json.code == 202) {

            }else{
                if (json.code == 10) {

                }
            }
            $('#modal-create-producto').modal('hide');
        });
    });

    var init_producto = function(){
        $selectCategoriaID.val(null);
        $selectUnidad.val(null);
        $formNombre.val(null);
    }

    var validation_form_producto = function()
    {
        $error_add_producto.html('');
        switch(true){
            case !$selectUnidad.val() :
                $error_add_producto.append('<div class="help-block">* Selecciona unidad de medida</div>');
                $error_add_producto.show();
                return true;
            break;

            case !$selectCategoriaID.val() :
                $error_add_producto.append('<div class="help-block">* Selecciona una categoria</div>');
                $error_add_producto.show();
                return true;
            break;

            case !$formNombre.val() :
                $error_add_producto.append('<div class="help-block">* Ingresa un nombre para el producto</div>');
                $error_add_producto.show();
                return true;
            break;
        }
    }


/*==============================
*   CARGA SUCURSALES
*===============================*/
$sucursal_receptor_id.change(function(){
    $(".content_info_sucursales").html('');
    $destino_receptor_id.html(null);
    $('.div_destino_receptor').hide();
    sucursalSelect      = [];
    precioLibra         = 0;
    $('.precio_libra_venta').html(btf.conta.money(0));
    $.get('<?= Url::to(['sucursal-info-ajax']) ?>', { q  : $(this).val() },function(reSucusal){
        if (reSucusal) {
            $('.container-color').css("background", reSucusal.sucursal_color);
            sucursalSelect.push(reSucusal);

            $.each(reSucusal.lista_destino , function(key,destino){
                /*if (destino.default == 10 )
                    $destino_receptor_id.append(new Option("GENERAL", 0 ));
                else*/
                    $destino_receptor_id.append(new Option(destino.destino_text, destino.destino_id ));

                $('.div_destino_receptor').show();
                $destino_receptor_id.trigger('change');
            });


            if (reSucusal.lista_destino) {
                if (reSucusal.lista_destino.length == 0) {
                    if (precioLibra <= 0 ) {
                        $('.alert-danger-envio').show();
                        $('.alert-danger-envio').html("<strong>El precio de la libra es $0.00, contacta al administrador</strong>");
                        $btnSaveEnvio.attr('disabled',true);
                    }
                }
            }
        }
    },'json');
});

$destino_receptor_id.change(function(){
    $.get("<?= Url::to(['get-precio-libra']) ?>", {  tipo_destino : $(this).val(), sucursal_receptor_id : $sucursal_receptor_id.val() } ,function($responsePrecio){
        precioLibra = $responsePrecio.precio_libra;
        $('.alert-danger-envio').hide();
        $btnSaveEnvio.attr('disabled',false);
        $('.precio_libra_venta').html(btf.conta.money(precioLibra));
        if (precioLibra <= 0 ) {
            $('.alert-danger-envio').show();
            $('.alert-danger-envio').html("<strong>El precio de la libra es $0.00, contacta al administrador</strong>");
            $btnSaveEnvio.attr('disabled',true);
        }

    });
});

$('#subtotal_total_envio').change(function(){
    temp_total_envio = parseFloat($(this).val()) + parseFloat($('#costo_extra_total_envio').val());
    if (temp_total_envio <= 1000 ) {
        $('#total_envio').val( parseFloat($(this).val()) + parseFloat($('#costo_extra_total_envio').val()) );
        $btnSaveEnvio.show();
    }else{
        alert("EL ENVIO NO PUEDE SER MAYOR A $1000.00, VERIFICA TU INFORMACION");
        $btnSaveEnvio.hide();
    }


});


$btnSaveEnvio.click(function(event){
    event.preventDefault();
    if (paquete_array.length > 0 ) {
        if ($sucursal_emisor_id.val()) {
            total_envio = $('#total_envio').val() ? $('#total_envio').val() : 0;
            if(parseFloat(total_envio) <= 1000 ){
                $(this).submit();
            }
            else{
                alert("EL ENVIO NO PUEDE SER MAYOR A $1000.00, VERIFICA TU INFORMACION");
                $btnSaveEnvio.hide();
            }

        }else{
            alert("VERIFICA TU INFORMACION, INTENTA NUEVAMENTE");
            return false;
        }
    }else{
        alert("DEBES INGRESAR MINIMO UN PAQUETE AL ENVIO");
        return false;
    }
});

/*============================================================================
*                           TAB PAQUETE
*=============================================================================*/


var load_categoria = function (){

    filters = "tipo_servicio="+ $tipo_envio.val();
    $.get("<?= Url::to(['categoria-ajax']) ?>",{   filters: filters  },function($categoria){
        categoriaList = $categoria;
    });
}

/*=========================================
*      BUSCA UN ITEM EN EL ARRAY
*==========================================*/
var search_item  = function(id,list_array, opt = false){
    key_item = false;
    if (!opt) {
        $.each(list_array,function(key,item){
            if(item.id == id )
                key_item =  key;
        });
    }else{
        $.each(list_array,function(key,item){
            if(item.producto_detalle_id == id )
                key_item =  key;
        });
    }
    return key_item;
}

/*=========================================
* Actualiza la lista de paquetes
*=========================================*/

var refresh_paquete = function(ele){

    $ele_paquete_val = $(ele).closest('tr');

    $ele_paquete_id  = $ele_paquete_val.attr("data-paquete_id");
    $ele_origen_id   = $ele_paquete_val.attr("data-origen");

    $.each(paquete_array, function(key, paquete){
        if (paquete) {
            if (paquete.paquete_id == $ele_paquete_id && paquete.origen == $ele_origen_id ) {
                if (paquete.origen ==  1)
                    paquete_array.splice(key,1);

                if (paquete.origen == 2 )
                    paquete.status = 1;
            }
        }
    });

    $(ele).closest('tr').remove();
    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
};

var refresh_paquete_cantidad = function(ele){
    $ele_paquete_val = $(ele).val();
    $ele_paquete_id  = $(ele).attr("data-paquete_id");

    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id == $ele_paquete_id ) {
            paquete.cantidad = $ele_paquete_val;


        }
    });
    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
}


/*var refresh_paquete_valor_declarado = function(ele){
    $ele_paquete_val    = $(ele);
    $ele_paquete        = $(ele).closest('tr');
    $ele_paquete_id  = $ele_paquete.attr("data-paquete_id");
    $ele_origen_id   = $ele_paquete.attr("data-origen");

    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id == $ele_paquete_id && paquete.origen == $ele_origen_id  ) {
            if (parseFloat($ele_paquete_val.val()) > 1000)
                paquete.valor_declarado = 999;
            else
                paquete.valor_declarado = $ele_paquete_val.val();
        }
    });

    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
}*/

/*=========================================
*      RENDERIZA TODO LOS PAQUETE
*==========================================*/
var render_paquete_template = function()
{
    $content_paquete.html("");

    //valor_declarado_total = 0;
    costo_extra_total   = 0;
    //sum_peso_total      = 0;
    seguro_total        = 0;
    $.each(paquete_array, function(key, paquete){

        if (paquete.paquete_id) {
            if(paquete.status == 10 || paquete.status == 2){

                template_paquete = $template_paquete.html();
                template_paquete = template_paquete.replace("{{paquete_id}}",paquete.paquete_id);

                $content_paquete.append(template_paquete);


                cal_costo_extra = calculo_costo_extra(key, paquete.categoria_id, paquete.cantidad, paquete);
                paquete.cat_costo_extra =  cal_costo_extra || cal_costo_extra == 0 ? cal_costo_extra : paquete.cat_costo_extra;
                paquete.producto_costo_extra = parseFloat(paquete.cat_costo_extra) + parseFloat(paquete.pro_costo_extra);


                $tr        =  $("#paquete_id_" + paquete.paquete_id, $content_paquete);
                $tr.attr("data-paquete_id",paquete.paquete_id);
                $tr.attr("data-origen",paquete.origen);

                $("#table_producto_text",$tr).html(paquete.producto_id_text);
                $("#table_cantidad",$tr).val(paquete.cantidad);



                $("#table_costo_extra",$tr).html(parseFloat(paquete.producto_costo_extra));

                $("#table_peso",$tr).html(parseFloat(paquete.peso));


                $("#table_valor_declarado",$tr).html(paquete.valor_declarado);


                $("#table_observacion",$tr).html(paquete.observaciones);

                costo_extra_total   = costo_extra_total + parseFloat(paquete.producto_costo_extra);
                //seguro_total        = seguro_total + parseFloat(paquete.costo_seguro ? paquete.costo_seguro : 0);
                //sum_peso_total = sum_peso_total + parseFloat(paquete.peso);

                $("#table_cantidad",$tr).attr("data-paquete_id",paquete.paquete_id);
                $("#table_cantidad",$tr).attr("data-origen",paquete.origen);

                $("#table_cantidad",$tr).attr("onchange","refresh_paquete_cantidad(this)");

                $tr.append("<td><button  type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
            }
        }
    });

    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));

    $('#costo_extra_total_envio').val(costo_extra_total);

    //$('#valor_declarado_total').val(valor_declarado_total);
};

var calculo_costo_extra = function(key_search, $categoria_search, $cantidad_search, $getPaquete){


    $count_producto = 0;
    cat_costo_extra = 0;
    $is_add         = false;
    cantidad_array     = [];
    $.each(paquete_array, function(key, paquete){
        if(paquete.status == 10 || paquete.status == 2){
            if (key_search >= key) {
                if (paquete.categoria_id == $categoria_search) {
                    console.log("Numero de productos count: " + paquete.cantidad + "-"+paquete.categoria_text);
                    $count_producto = $count_producto + parseInt(paquete.cantidad);
                    cantidad_array.push(paquete.cantidad);
                    $is_add         = true;
                }
            }
        }
    });

    console.log("Numero de productos count: " + $count_producto);
    if ($is_add) {

        if ($getPaquete.categoria_is_mex == 10) {

            if (($count_producto - parseInt( $getPaquete.categoria_required_min) ) >= 0 ) {

                console.log("entro.....");
                console.log($count_producto - parseInt( $getPaquete.categoria_required_min) );
                console.log(".........");
                /****************************/
                // PRUEBA
                /****************************/
                $residuo     = 0;
                $count_cantidad = cantidad_array.length;
                $count_limit =  1;
                $.each(cantidad_array, function(key,cantidad){
                    if ($residuo < 0) {
                        $residuo = $residuo  + (cantidad * -1);
                    }else{
                        $residuo = $residuo + ( parseInt($getPaquete.categoria_required_min) - cantidad);
                        if ($residuo >= 0 ) {
                            $residuo = -1;
                        }
                    }
                    $count_limit = $count_limit + 1;
                });

                if (($residuo * -1) <= $cantidad_search ) {
                    console.log("entroo aqui  ? ");
                    //cantidad_costo_extra  = ( parseInt($cantidad_search)  +  $residuo );
                    cantidad_costo_extra  = ( ( parseInt($cantidad_search) + 1 )  -  parseInt($getPaquete.categoria_required_min) ); // REVISAR EL COMODIN DE MAS 1 ***URGENTE****
                    intervalo = cantidad_costo_extra / parseInt($getPaquete.categoria_intervalo);
                    console.log("la cantidad es: "+ $cantidad_search);
                    cat_costo_extra = parseFloat($getPaquete.categoria_costo_extra) * parseInt(intervalo);
                }else{
                    cantidad_costo_extra  = parseInt($cantidad_search);
                    intervalo = cantidad_costo_extra / $getPaquete.categoria_intervalo;
                    cat_costo_extra = parseFloat($getPaquete.categoria_costo_extra) * parseInt(intervalo);
                }

                /****************************/
                //
                /****************************/

                //cantidad_costo_extra  = ( parseInt($cantidad_search)  -  1 ) + parseFloat(item.mex_intervalo);


            }else{
                return $is_add;
            }
        }
        console.log("calculo final ... :" + cat_costo_extra);


        return cat_costo_extra;
    }
    return $is_add;
}


$('#peso_mex_con_empaque').change(function(){

    if (paquete_array.length > 0 ) {

        if ($('#peso_mex_con_empaque').val() >= 3 ) {
            paquete_item = 0;
            $.each(paquete_array, function(key, paquete){
                if (paquete.paquete_id) {
                    if(paquete.status == 10 || paquete.status == 2){
                        paquete_item = paquete_item + 1;
                    }
                }
            });


            $.each(paquete_array, function(key, paquete){
                if (paquete.paquete_id) {
                    if(paquete.status == 10 || paquete.status == 2){
                        paquete.peso = parseFloat($('#peso_mex_con_empaque').val()) / paquete_item ;
                    }
                }
            });

            $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));



            inputPesoTotal = $('#peso_mex_con_empaque').val() ? $('#peso_mex_con_empaque').val() : 0;

            $('#subtotal_total_envio').val(parseFloat(inputPesoTotal * precioLibra ).toFixed(2)).trigger('change');

            $('#total_envio').val( ( $('#subtotal_total_envio').val() ? parseFloat($('#subtotal_total_envio').val()) : 0 ) + parseFloat($('#costo_extra_total_envio').val()) );

            render_paquete_template();
        }else{
            alert("EL PESO DEBE SER MAYOR A 3 LB, VERIFICA TU INFORMACION");
            $(this).val(0);

            inputPesoTotal = $('#peso_mex_con_empaque').val() ? $('#peso_mex_con_empaque').val() : 0;

            $('#subtotal_total_envio').val(parseFloat(inputPesoTotal * precioLibra ).toFixed(2)).trigger('change');

            $('#total_envio').val( ( $('#subtotal_total_envio').val() ? parseFloat($('#subtotal_total_envio').val()) : 0 ) + parseFloat($('#costo_extra_total_envio').val()) );

            render_paquete_template();
        }
    }else{
        alert("DEBES INGRESAR UN PAQUETE PARA PODER INGRESAR EL PESO, VERIFICA TU INFORMACION");
        $(this).val(0);
     }
});

$('#valor_declarado_total').change(function(){
    if (parseFloat($('#valor_declarado_total').val()) < 1000 ) {

        if (paquete_array.length > 0 ) {

            paquete_item = 0;
            $.each(paquete_array, function(key, paquete){
                if (paquete.paquete_id) {
                    if(paquete.status == 10 || paquete.status == 2){
                        paquete_item = paquete_item + 1;
                    }
                }
            });

            $.each(paquete_array, function(key, paquete){
                if (paquete.paquete_id) {
                    if(paquete.status == 10 || paquete.status == 2){
                        paquete.valor_declarado = parseFloat($('#valor_declarado_total').val()) / paquete_item ;
                    }
                }
            });

            $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
            render_paquete_template();


         }else{
            alert("DEBES INGRESAR UN PAQUETE PARA PODER INGRESAR EL VALOR DECLARADO, VERIFICA TU INFORMACION");
            $(this).val(0);
         }

    }else{
        alert("EL VALOR DECLARADO NO PUEDE SER MAYOR A $1000.00, VERIFICA TU INFORMACION");
        $(this).val(0);
    }
});


/*=========================================
*      FUNCION QUE CARGA TODO EL ARRAY
*=========================================*/
var init_paquete_list = function(){

    paquete_array = [];
    if ($envioID.val()) {

        $.get('<?= Url::to('envio-detalle-ajax') ?>', {'envio' : $envioID.val() }, function(json) {
            $.each(json.rows, function(key, item){
                if (item.id) {
                    paquete = {
                        "paquete_id"    : item.id,
                        //"sucursal_id"   : item.sucursal_receptor_id,
                        //"cliente_id"    : item.cliente_receptor_id,
                        "categoria_id"  : item.categoria_id,
                        "categoria_text": item.categoria,
                        "cantidad"      : item.cantidad,
                        "categoria_is_mex"      : item.costo_extra ? 10 : 0,
                        "categoria_intervalo"      : item.intervalo,
                        "categoria_costo_extra"    : item.costo_extra,
                        "categoria_required_min"   : item.required_min,

                        "unidad_medida_id"      : item.unidad_medida_id,
                        "unidad_medida_text"    : item.unidad_medida_text,
                        "producto_id"           : item.producto_id,
                        "producto_id_text"      : item.producto,
                        "producto_detalle_id"   : item.producto_detalle_id,
                        "producto_costo_extra"  : item.impuesto,
                        "pro_costo_extra"       : 0,

                        "cat_costo_extra"       : item.impuesto,

                        "peso"      : item.peso,
                        //"precio_costo_extra"    : item.costo_extra,
                        //"precio_required_min"   : item.required_min,
                        "observaciones"         : item.observaciones,
                        //"costo_seguro"          : item.costo_seguro,
                        "seguro"                : item.seguro &&  item.seguro != 0 ? true : false,
                        "valor_declarado"       : item.valor_declarado,
                        "status"        : item.status,
                        "update"        : $envioID.val() ? 10 : 1,
                        "origen"        : 2
                    };
                    $sucursal_receptor_id.val(item.sucursal_receptor_id).trigger('change');
                }
                paquete_array.push(paquete);

            });
            render_paquete_template();
            $('#peso_mex_con_empaque').change();
        }, 'json');

        $.get('<?= Url::to('cobro-envio-ajax') ?>',{ 'envio_id': $envioID.val() },function(metodo){
            $.each(metodo.results,function(key,item){
                if (item.id) {
                    metodo = {
                        "metodo_id"         : metodoPago_array.length + 1,
                        "metodo_pago_id"    : item.metodo_pago,
                        "metodo_pago_text"  : metodoPagoList[item.metodo_pago],
                        "cantidad"          : item.cantidad,
                        "origen"            : 2,
                    };

                    metodoPago_array.push(metodo);
                    render_metodo_template();
                }
            });
        });
    }

    /*$.each(edit_load_sucursal, function(key, sucursal){
        $is_sucursal = true;
        $.each(sucursalSelect, function(key2, sucursal_Select){
            if (sucursal_Select.id == sucursal.id)
                $is_sucursal = $is_sucursal == false ? false: false;
        })
        if ($is_sucursal) {
            var newOption       = new Option(sucursal.nombre, sucursal.id, false, true);
            $sucursal_receptor_id.append(newOption);
            sucursalSelect.push(sucursal);
        }
    });*/

    $sucursal_receptor_id.trigger('change');


    $.each(edit_load_cliente, function(key, cliente){
        $is_cliente = true;
        $.each(clienteSelect, function(key2 , cliente_select){
            if (cliente_select.id == cliente.id){
                $is_cliente = $is_cliente == false ? false: false;
            }
        });

        if ($is_cliente) {
            var newOption       = new Option(cliente.nombre, cliente.id, false, true);
            $cliente_receptor.append(newOption);
            clienteSelect.push(cliente);
        }
    });

    $cliente_receptor.trigger('change');


    /*if ($isAplicaReenvio.val() == 10) {
        $isAplicaReenvio.val(null);
        $btnAplicaReenvio.trigger('click');
    }*/

};

/*===============================================
* Limpia valores de un  formulario
*===============================================*/
var clear_form = function($form){
    $.each($form,function($key,$item){
        $item.val(null);
    });
};

$btnAgregarMetodoPago.click(function(){

    if(!$form_metodoPago.$metodoPago.val() || !$form_metodoPago.$cantidad.val()){
        return false;
    }

    metodo = {
        "metodo_id"         : metodoPago_array.length + 1,
        "metodo_pago_id"    : $form_metodoPago.$metodoPago.val(),
        "metodo_pago_text"  : $('option:selected', $form_metodoPago.$metodoPago).text(),
        "cantidad"          : $form_metodoPago.$cantidad.val(),
        "origen"            : 1,
    };

    metodoPago_array.push(metodo);

    //calcula_cambio_envio();
    render_metodo_template();

});

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

            pago_total = pago_total + parseFloat(metodo.cantidad);

            if (metodo.origen != 2) {
                $tr.append("<button type='button' class='btn btn-warning btn-circle' onclick='refresh_metodo(this)'><i class='fa fa-trash'></i></button>");
            }
        }
    });

    $('#total_metodo').html("$ " + ($('#total_envio').val() ? $('#total_envio').val() : 0) );


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

</script>
