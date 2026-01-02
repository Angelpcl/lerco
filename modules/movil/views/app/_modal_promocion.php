<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\esys\EsysSetting;
?>
<style>
    .select-promocion {
        text-decoration: underline;
        text-decoration-style: double;
        text-decoration-color: red;
    }
    .card-promocion {
        position: relative;
        margin: auto;
        overflow: hidden;
        width: 100%;
        margin-bottom: 5%;
        background: #F5F5F5;
        box-shadow: 5px 5px 15px rgba(0, 0, 0, .5);
        border-radius: 10px;
    }
</style>

<div class="fade modal " id="modal-promocion"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div id="error-add-promocion_sucursal" class="has-error" style="display: none">
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::tag('p', "Ingresa tu ID si perteneces a una Sucursal ",["class" => "text-main text-dark" ]) ?>
                        <div class="input-group mar-btm">
                            <?= Html::input('text', 'code_sucursal', ''  ,['class' => 'form-control','placeholder' => 'Validar código', 'id' => 'code_sucursal']) ?>
                            <span class="input-group-btn">
                                <button class="btn btn-dark" type="button" id="code_sucursal_valida">Validar</button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="div_form_promocion_complemento">
                    <div class="div_search_promocion">
                        <div id="error-add-promocion" class="has-error" style="display: none">

                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <?= Html::tag('p', "Ingresa tu ID para adquirir mas beneficios",["class" => "text-main" ]) ?>
                                <div class="input-group mar-btm">
                                    <?= Html::input('text', 'code',isset($model->clienteCodigoPromocion->clave) ? $model->clienteCodigoPromocion->clave : ''  ,['class' => 'form-control','placeholder' => 'Validar código', 'id' => 'code']) ?>
                                    <span class="input-group-btn">
                                        <button class="btn btn-mint" type="button" id="code_valida">Validar</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Descuento en precio de Libra
                                <span class="label  text-dark">Precio actual</span>
                                <span class="neto monto" style="text-decoration: line-through;text-decoration-style: double;">$ <strong class="precio_libra_envio"> </strong> USD</span>
                            </h3>
                        </div>
                        <div class="panel-body">
                           <div class="div_precios_libra">
                           </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">Lb. Req</th>
                                    <th class="min-col text-center text-uppercase">N°/productos</th>
                                    <th class="min-col text-center text-uppercase">Aplica a</th>
                                    <th class="min-col text-center text-uppercase">Categoria</th>
                                    <th class="min-col text-center text-uppercase">Producto</th>
                                    <th class="min-col text-center text-uppercase">V. Paquete</th>
                                    <th class="min-col text-center text-uppercase">Libras/gratis</th>
                                    <th class="min-col text-center text-uppercase">Sin impuesto</th>
                                    <th class="min-col text-center text-uppercase">Envio gratis</th>
                                    <th class="min-col text-center text-uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="table_complemento_promocion" style="text-align: center;">

                            </tbody>
                        </table>
                    </div>
                    <div class="row totales cobros" id ="p_detalle_id_{{p_detalle_id}}">
                        <div class="col-sm-offset-4 col-sm-3">
                            <span class="label">Total</span>
                            <span class="neto monto" style="font-size: 25px;">$ <strong id="total_promocion"> 0 </strong> USD</span>
                        </div>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <?= Html::submitButton('Continuar', ['class' =>  'btn btn-primary', 'id' => 'form-promocion']) ?>
            </div>
        </div>
    </div>
</div>

 <div class="display-none">
     <table>

        <tbody class="template_complemento">
            <tr id = "complemento_id_{{complemento_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-lbrequired"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-num_producto"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-tipo"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-categoria_text"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-producto_text"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-valor_promedio_check"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-lbfree_check"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-sin_impuesto_check"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-is_envio_check"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="display-none">
    <div class="template_precio">
        <div class="row totales cobros card-promocion" id ="p_detalle_id_{{p_detalle_id}}" >
            <div class="pad-all">
                <div class="form-group">
                    <?= Html::button('Seleccionar Beneficios',["class" => "btn btn-primary  btn-block btn-lg  btnPromocionDetalle"]) ?>
                </div>
                <div id="div_anexo_promocion_id_{{p_anexo_detalle_id}}" style="display: none">
                    <div class="row">
                        <div class="col-sm-6" >
                            <span>Anexos (Libras otorgadas)</span>
                            <hr>
                            <div class="div_anexos_all">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pad-ver bg-trans-dark">
                <ul class="list-unstyled row text-center">
                    <li class="col-xs-3">
                        <span class="label">Precio sin ID</span>
                        <span class="impuestos monto precio_sin_id select-promocion" ></span>

                    </li>
                    <li class="col-xs-3">
                        <span class="label">Precio con ID</span>
                        <span class="total monto precion_con_id"></span>
                    </li>
                    <li class="col-xs-3">
                        <span class="label">Libras/P</span>
                        <span class="total monto libra_required"></span>
                    </li>
                    <li class="col-xs-3">
                        <span class="label">Total</span>
                        <span class="total monto precio_total_promocion">  </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="display-none">
    <div class="template_anexo">
        <div class="row div_anexo_id_{{anexo_id}}">
            <div class="col-sm-6 " >
                <h5 class="lbl_categoria_anexo"></h5>
            </div>
            <div class="col-sm-6">
                <h5 class="lbl_libras_free_anexo"></h5>
            </div>
        </div>
    </div>
</div>
<script>
var $table_complemento_promocion = $('.table_complemento_promocion'),
    $div_precios_libra           = $(".div_precios_libra"),
    $modal_title                 = $(".modal-title"),
    $code_valida                 = $("#code_valida"),
    $code_sucursal_valida        = $("#code_sucursal_valida"),
    $div_form_promocion_complemento = $(".div_form_promocion_complemento"),
    $add_complemento_promocion      = $(".add_complemento_promocion"),
    $template_complemento           = $('.template_complemento'),
    $template_precio                = $('.template_precio'),
    $template_anexo                = $('.template_anexo'),
    $div_anexos_all                = $('.div_anexos_all'),
    $div_search_promocion           = $(".div_search_promocion"),
    $error_add_promocion            = $('#error-add-promocion'),
    $error_add_promocion_sucursal            = $('#error-add-promocion_sucursal'),
    $btnComplementoGeneral          = $('#btnComplementoGeneral'),
    $total_promocion                = $('#total_promocion'),
    $promocion_complemento_id       = $("#envio-promocion_complemento_id");
    $code           = $('#code');
    $code_sucursal  = $('#code_sucursal');

    $('#form-promocion').click(function(){
       $('#modal-promocion').modal('hide');
        //$valida_promocion_envio.hide();
        $content_tab.find('.next').show();
    });

    var select_complemento = function(elem, $complemento_id, $is_general = false, $proDetalleId = false )
    {
        isGeneral         = $is_general ? true : false;
        $inputComplementoArray.val(null);
        promocionComplementoSelect = [];
        $('.div_complemento').html(' ');

        if (isGeneral) {
            $.each(promocionDetalleSelect.promocionComplemento,function(key,item){
                promocionComplementoSelect.push(item);
                $table = $('.table_complemento_promocion');
                $('#complemento_id' + '_' + item.id ).css("background","silver");
                $('.add_complemento_promocion', '#complemento_id_' + item.id ).prop("disabled",true);

            });
        }else{
            $.each(promocionDetalleSelect.promocionComplemento,function(key,item){
                if (item.id == $complemento_id)
                    promocionComplementoSelect.push(item);
            });

            //$promocion_complemento_id.val($complemento_id);
            $ele_paquete       = $(elem).closest('tr');
            $ele_paquete.css("background","silver");
            $(".add_complemento_promocion").prop("disabled",true);
        }

        $inputComplementoArray.val(JSON.stringify(promocionComplementoSelect));



        load_panel_beneficios();
        selected_styles();
    };

    $code_sucursal_valida.click(function(){
        $error_add_promocion_sucursal.html('');
        //$promocion_complemento_id.val(null);
        promocionDetalleSelect.promocionDetalle      = {};
        promocionComplementoSelect                   = [];
        promocionDetalleSelect.promocionComplemento  = [];
        promocionDetalleSelect.promocionAnexo        = [];
        if($code_sucursal.val()){
            $.get("<?= Url::to(['code-promocion-sucursal-ajax']) ?>",{ cliente_emisor: $cliente_emisor.val(), clave : $code_sucursal.val()  },function(json){
                if (json.code == 202) {
                    $error_add_promocion_sucursal.show();
                    $error_add_promocion_sucursal.removeClass('has-error').addClass('has-success');
                    $error_add_promocion_sucursal.append('<div class="help-block">* '+ json.message +'</div>');

                    $link_promocion.attr('href','<?= Url::to(['/promociones/promocion/view?id=']) ?>' +  json.data.id);
                    $link_promocion.html(json.data.nombre);
                    $link_promocion.data("id",json.data.id);
                    $link_promocion.data("is_manual",json.data.is_manual);
                    $link_promocion.data("is_code",json.data.is_code_promocional);
                    promocionVigente = true;
                    $valida_promocion_envio.trigger('click');
                }else{
                    $error_add_promocion_sucursal.removeClass('has-success').addClass('has-error');
                    $error_add_promocion_sucursal.show();
                    $error_add_promocion_sucursal.append('<div class="help-block">* '+ json.message +'</div>');
                }
            });
        }
    });


    $code_valida.click(function(){
        $error_add_promocion.html('');
        if (promocionDetalleSelect.promocionDetalle) {
            if($code.val()){
                $.get("<?= Url::to(['code-promocion-ajax']) ?>",{ cliente_emisor: $cliente_emisor.val(), clave : $code.val(), promocion_id : $promocion_id.val(), promocion_detalle_id : $promocion_detalle_id.val()  },function(json){
                    if (json.code) {
                        if (json.code == 202) {
                            $error_add_promocion.show();
                            $error_add_promocion.removeClass('has-error').addClass('has-success');
                            $error_add_promocion.append('<div class="help-block">* '+ json.message +'</div>');
                            $('.precio_sin_id').removeClass('select-promocion');
                            $('.precion_con_id').addClass('select-promocion');
                            //precio_libra_actual =  parseFloat(json.data.costo_libra_con_code);
                            isCodePromocionalSelect = true;
                            load_panel_beneficios();
                            //$('#peso_total').trigger('change');
                            $('#envio-codigo_promocional_id').val(json.data.code_id);

                        }else{
                            $error_add_promocion.removeClass('has-success').addClass('has-error');
                            $error_add_promocion.show();
                            $error_add_promocion.append('<div class="help-block">* '+ json.message +'</div>');
                            $('.precio_sin_id').addClass('select-promocion');
                            $('.precion_con_id').removeClass('select-promocion');
                        }
                    }
                });
            }else{
                $('.precio_sin_id').addClass('select-promocion');
                $('.precion_con_id').removeClass('select-promocion');
                $error_add_promocion.removeClass('has-error').addClass('has-success');
                $error_add_promocion.show();
                $error_add_promocion.append('<div class="help-block">* Debes ingresar una clave</div>');
            }
        }else{
            $('.precio_sin_id').addClass('select-promocion');
            $('.precion_con_id').removeClass('select-promocion');
            $error_add_promocion.removeClass('has-error').addClass('has-success');
            $error_add_promocion.show();
            $error_add_promocion.append('<div class="help-block">* Debes seleccionar una Promoción </div>');
        }
    });
</script>

