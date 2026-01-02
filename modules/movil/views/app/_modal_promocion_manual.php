<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\esys\EsysSetting;
use app\models\envio\EnvioPromocion;

?>
<style>
    .select-promocion {
        text-decoration: underline;
        text-decoration-style: double;
        text-decoration-color: red;
    }
</style>

<div class="fade modal " id="modal-promocion-manual"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-manual-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title-manual"> </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="div_form_promocion_manual">
                    <div class="div_search_promocion_manual">
                        <div id="error-add-promocion-manual" class="has-error" style="display: none">
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <?= Html::tag('p', "Ingresa tu ID para adquirir mas beneficios",["class" => "text-main" ]) ?>
                                <div class="input-group mar-btm">
                                    <?= Html::input('text', 'code',isset($model->clienteCodigoPromocion->clave) ? $model->clienteCodigoPromocion->clave : ''  ,['class' => 'form-control','placeholder' => 'Validar código', 'id' => 'code_manual']) ?>
                                    <span class="input-group-btn">
                                        <button class="btn btn-mint" type="button" id="code_valida_manual">Validar</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="control-label" for="promocion_manual_tipo">Aplica a: </label>
                            <?= Html::dropDownList('promocion_manual_tipo', null, EnvioPromocion::$tipoList, ['class' => 'form-control', 'id' => 'promocion_manual_tipo']) ?>
                            <div class="help-block"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="div_libras_free" style="display: none">
                                <div class="row">
                                    <div class="col-sm-11">
                                        <?= Html::label('Libras Gratis :', 'promocion_manual_lb_free') ?>
                                        <div class="input-group mar-btm">
                                            <?= Html::input('number', 'promocion_manual_lb_free',0,[ 'id' => 'promocion_manual_lb_free','class' => 'form-control']) ?>
                                            <span class="input-group-addon">lb</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <?= Html::label('Libras pagadas :', 'promocion_manual_lb_pagadas') ?>
                                                <div class="input-group mar-btm">
                                                    <?= Html::input('number', 'promocion_manual_lb_pagadas',0,[ 'id' => 'promocion_manual_lb_pagadas','class' => 'form-control']) ?>
                                                    <span class="input-group-addon">lb</span>
                                                </div>

                                                <?= Html::label('Libras excedentes :', 'promocion_manual_lb_excedente') ?>
                                                <div class="input-group mar-btm">
                                                    <?= Html::input('number', 'promocion_manual_lb_excedente',0,[ 'id' => 'promocion_manual_lb_excedente','class' => 'form-control',"step" => ".01", "disabled" => true]) ?>
                                                    <span class="input-group-addon">lb</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="col-sm-6">
                                                    <?= Html::label('Precio de libra pagadas :', 'promocion_manual_precio_lb_pagadas') ?>
                                                    <div class="input-group mar-btm">
                                                        <?= Html::input('number', 'promocion_manual_precio_lb_pagadas',0,[ 'id' => 'promocion_manual_precio_lb_pagadas','class' => 'form-control',"step" => ".01"]) ?>
                                                        <span class="input-group-addon">USD</span>
                                                    </div>

                                                    <?= Html::label('Precio de libras excedentes :', 'promocion_manual_precio_lb_exedente') ?>
                                                    <div class="input-group mar-btm">
                                                        <?= Html::input('number', 'promocion_manual_precio_lb_exedente',0,[ 'id' => 'promocion_manual_precio_lb_exedente','class' => 'form-control',"step" => ".01"]) ?>
                                                        <span class="input-group-addon">USD</span>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row totales cobros">
                                    <div class="col-sm-offset-1 col-sm-3">
                                        <span class="label">Peso total</span>
                                        <span class="neto monto" style="font-size: 25px;"><strong id="promocion_manual_peso_total"> 0 </strong> lb</span>
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="label">SubTotal con promoción</span>
                                        <span class="neto monto" style="font-size: 25px;">$ <strong id="promocion_manual_total_promocion"> 0 </strong> USD</span>
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="label">SubTotal sin promoción</span>
                                        <span class="neto monto" style="font-size: 25px;">$ <strong id="promocion_manual_total_sin_promocion"> 0 </strong> USD</span>
                                    </div>
                                </div>
                            </div>
                            <div class="div_condonacion_impuesto" style="display: none">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Categoria</th>
                                                <th class="text-center">Valoración PQ</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-center">Impuesto</th>
                                                <th class="text-center">C. Impuesto</th>
                                                <th class="text-center">% D. Impuesto</th>
                                            </tr>
                                        </thead>
                                        <tbody class="content_promocion_paquete" style="text-align: center;">

                                        </tbody>
                                    </table>
                                </div>
                                 <div class="row totales cobros">
                                    <div class="col-sm-offset-3 col-sm-3">
                                        <span class="label">Impuesto con promoción</span>
                                        <span class="neto monto" style="font-size: 25px;">$ <strong id="promocion_manual_impuesto_total"> 0 </strong> USD</span>
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="label">Impuesto sin promoción</span>
                                        <span class="neto monto" style="font-size: 25px;">$ <strong id="promocion_manual_impuesto_sin_total"> 0 </strong> USD</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <?= Html::submitButton('Continuar', ['class' =>  'btn btn-primary', 'id' => 'form-promocion-manual']) ?>
            </div>
        </div>
    </div>
</div>



<div class="display-none">
    <table>
        <tbody class="template_promocion_paquete">
            <tr id = "promocion_paquete_id_{{paquete_id}}">
                <td ><?= Html::tag('p', "Categoria",["class" => "text-main" , "id"  => "table_promocion_categoria_id"]) ?></td>
                <td ><?= Html::tag('p', "V.Declarado",["class" => "text-main" , "id"  => "table_promocion_valor_declarado"]) ?></td>
                <td ><?= Html::tag('p', "Cantidad",["class" => "text-main" , "id"  => "table_promocion_cantidad"]) ?></td>
                <td ><?= Html::tag('p', "Impuesto",["class" => "text-main" , "id"  => "table_promocion_impuesto"]) ?></td>
                <td ><?= Html::tag('p', "Condonacion",["class" => "text-main" , "id"  => "table_promocion_condonacion"]) ?></td>
                <td ><?= Html::input('number',"",0,["class" => "form-control" ,"disabled" => true  , "style" => "text-align:center","id"  => "table_porcentaje_condonacion"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>
<script>

var $div_precios_libra_manual           = $(".div_precios_libra_manual"),
    $modal_title_manual                 = $(".modal-title-manual"),
    $code_valida_manual                 = $("#code_valida_manual"),
    $div_form_promocion_manual      = $(".div_form_promocion_manual"),
    $div_search_promocion_manual    = $(".div_search_promocion_manual"),
    $error_add_promocion_manual     = $('#error-add-promocion-manual'),
    $total_promocion_manual         = $('#total_promocion_manual'),

    $promocion_manual_tipo          = $('#promocion_manual_tipo'),
    $promocion_manual_peso_total    = $('#promocion_manual_peso_total'),
    $promocion_manual_total_promocion     = $('#promocion_manual_total_promocion'),
    $promocion_manual_total_sin_promocion = $('#promocion_manual_total_sin_promocion'),
    $div_condonacion_impuesto       = $('.div_condonacion_impuesto'),
    $div_libras_free                = $('.div_libras_free'),

    $promocion_manual_lb_free            = $('#promocion_manual_lb_free'),
    $promocion_manual_lb_pagadas         = $('#promocion_manual_lb_pagadas'),
    $promocion_manual_lb_excedente       = $('#promocion_manual_lb_excedente'),
    $promocion_manual_precio_lb_pagadas  = $('#promocion_manual_precio_lb_pagadas'),
    $promocion_manual_precio_lb_exedente = $('#promocion_manual_precio_lb_exedente'),


    $template_promocion_paquete         = $('.template_promocion_paquete'),
    $content_promocion_paquete                    = $('.content_promocion_paquete'),
    $promocion_manual_impuesto_sin_total          = $('#promocion_manual_impuesto_sin_total'),
    $promocion_manual_impuesto_total              = $('#promocion_manual_impuesto_total'),

    $code_manual                    = $('#code_manual');
    envio_promocion_tipo = {
        tipo_libras :<?= EnvioPromocion::TIPO_LIBRAS ?>,
        tipo_impuesto : <?= EnvioPromocion::TIPO_IMPUESTO ?>
    };

    envioPromocion = {
        libras_free : {},
        condonacion_impuesto : [],
    };

    $new_SubTotal          = 0;



$promocion_manual_tipo.change(function(){
    render_paquete_template();
    if ($(this).val() == envio_promocion_tipo.tipo_libras) {
        $div_condonacion_impuesto.hide();
        $div_libras_free.show();
        $promocion_manual_lb_free.val(envioPromocion.libras_free.lb_free ? envioPromocion.libras_free.lb_free : '');
        $promocion_manual_lb_excedente.val(envioPromocion.libras_free.lb_exedente ? envioPromocion.libras_free.lb_exedente : '');
        $promocion_manual_lb_pagadas.val( envioPromocion.libras_free.lb_pagadas ? envioPromocion.libras_free.lb_pagadas : peso_total_envio );
        $promocion_manual_precio_lb_pagadas.val(envioPromocion.libras_free.precio_lb_pagada ? envioPromocion.libras_free.precio_lb_pagada : precio_libra_actual);
        $promocion_manual_precio_lb_exedente.val(envioPromocion.libras_free.precio_lb_excedente ?envioPromocion.libras_free.precio_lb_excedente : precio_libra_actual);
        calcula_manual_lb_free();

    }else if($(this).val() == envio_promocion_tipo.tipo_impuesto){
        $div_condonacion_impuesto.show();
        $div_libras_free.hide();
        render_promocion_paquete_template();
    }
});



var calcula_manual_lb_free = function()
{
    lb_free      = $promocion_manual_lb_free.val();
    lb_pagadas   = $promocion_manual_lb_pagadas.val();
    precio_lb_pagada    = $promocion_manual_precio_lb_pagadas.val();
    precio_lb_excedente = $promocion_manual_precio_lb_exedente.val();

    $promocion_manual_lb_excedente.val(peso_total_envio - (parseFloat(lb_free) + parseFloat(lb_pagadas)) );

    $new_SubTotal = (lb_pagadas * parseFloat( precio_lb_pagada) ) + ( $promocion_manual_lb_excedente.val() * precio_lb_excedente );
    $promocion_manual_total_promocion.html( $new_SubTotal.toFixed(2)  );

}


$promocion_manual_lb_free.change(function(){
    $promocion_manual_lb_pagadas.val(peso_total_envio);
    $promocion_manual_lb_pagadas.val( (parseFloat($promocion_manual_lb_pagadas.val()) - parseFloat($(this).val())) );
    calcula_manual_lb_free();
});

$promocion_manual_lb_pagadas.change(function(){
   calcula_manual_lb_free();
});

$promocion_manual_lb_excedente.change(function(){
    calcula_manual_lb_free();
});

$promocion_manual_precio_lb_pagadas.change(function(){
    calcula_manual_lb_free();
});

$promocion_manual_precio_lb_exedente.change(function(){
    calcula_manual_lb_free();
});



$code_valida_manual.click(function(){
    $error_add_promocion_manual.html('');
    if($code_manual.val()){
        $.get("<?= Url::to(['code-promocion-ajax']) ?>",{ cliente_emisor: $cliente_emisor.val(), clave : $code_manual.val(), promocion_id : $promocion_id.val() },function(json){
            if (json.code) {
                if (json.code == 202) {
                    $error_add_promocion_manual.show();
                    $error_add_promocion_manual.removeClass('has-error').addClass('has-success');
                    $error_add_promocion_manual.append('<div class="help-block">* '+ json.message +'</div>');
                    //precio_libra_actual =  parseFloat(json.data.costo_libra_con_code);
                    //$('#peso_total').trigger('change');
                    $('#envio-codigo_promocional_id').val(json.data.code_id);

                }else{
                    $error_add_promocion_manual.removeClass('has-success').addClass('has-error');
                    $error_add_promocion_manual.show();
                    $error_add_promocion_manual.append('<div class="help-block">* '+ json.message +'</div>');
                }
            }
        });
    }else{
        $error_add_promocion_manual.removeClass('has-error').addClass('has-success');
        $error_add_promocion_manual.show();
        $error_add_promocion_manual.append('<div class="help-block">* Debes ingresar una clave</div>');
    }
});


var render_promocion_paquete_template = function()
{
    $content_promocion_paquete.html("");
    $promocion_manual_impuesto_total.html($('#impuesto_total_envio').val());
    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id) {
            if(paquete.status == 10 && paquete.is_impuesto == is_impuesto_on){
                template_paquete = $template_promocion_paquete.html();
                template_paquete = template_paquete.replace("{{paquete_id}}",paquete.paquete_id);

                $content_promocion_paquete.append(template_paquete);

                $tr        =  $("#promocion_paquete_id_" + paquete.paquete_id, $content_promocion_paquete);
                $tr.attr("data-paquete_id",paquete.paquete_id);
                $tr.attr("data-origen",paquete.origen);

                $("#table_promocion_categoria_id",$tr).html(paquete.categoria_text);
                $("#table_promocion_cantidad",$tr).html(paquete.cantidad);


                $("#table_promocion_impuesto",$tr).html(parseFloat(paquete.impuesto_total));

                $("#table_promocion_valor_declarado",$tr).html(paquete.valoracion_paquete);


                $("#table_porcentaje_condonacion",$tr).val(paquete.condonacion_porcentaje_total  && paquete.condonacion_porcentaje_total > 0 ? paquete.condonacion_porcentaje_total : 0 );

                if (paquete.condonacion_porcentaje_total  && paquete.condonacion_porcentaje_total > 0)
                    $("#table_porcentaje_condonacion",$tr).prop('disabled',false);

                $("#table_porcentaje_condonacion",$tr).attr("onchange","refresh_paquete_condonacion(this,'PORCENTAJE_DESCUENTO')");

                if (paquete.condonacion_porcentaje_total  && paquete.condonacion_porcentaje_total > 0)
                    $("#table_promocion_condonacion",$tr).html('<input type="checkbox"  checked="true" onchange="refresh_paquete_condonacion(this,' + "'CONDONACION_IMPUESTO'" + ')" >');
                else
                    $("#table_promocion_condonacion",$tr).html('<input type="checkbox"  onchange="refresh_paquete_condonacion(this,' + "'CONDONACION_IMPUESTO'" + ')" >');
            }
        }
    });
};


var refresh_paquete_condonacion = function(ele,inputChange){

    $ele_paquete_val    = $(ele);
    $ele_paquete        = $(ele).closest('tr');
    $ele_paquete_detalle_id  = $ele_paquete.attr("data-paquete_id");
    $ele_paquete_origen_id   = $ele_paquete.attr("data-origen");

    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id == $ele_paquete_detalle_id && paquete.origen == $ele_paquete_origen_id ){

            switch(inputChange){
                case 'CONDONACION_IMPUESTO':
                    if ($ele_paquete_val.prop('checked')) {
                        paquete.impuesto_total_old = paquete.impuesto_total;
                        paquete.impuesto_total = 0;
                        paquete.condonacion_porcentaje_total = 100;
                        envioPromocion.condonacion_impuesto.push(paquete);
                    }else{


                        $.each(envioPromocion.condonacion_impuesto,function(key2, condonacion){
                            if (condonacion.paquete_id == paquete.paquete_id) {
                                envioPromocion.condonacion_impuesto.splice(key2,1);
                            }
                        });

                        paquete.impuesto_total     = paquete.impuesto_total_old;
                        paquete.impuesto_total_old = 0;
                        paquete.condonacion_porcentaje_total = 0;
                    }
                break;
                case 'PORCENTAJE_DESCUENTO':


                    $.each(envioPromocion.condonacion_impuesto,function(key2, condonacion){
                            if (condonacion.paquete_id == paquete.paquete_id) {
                                envioPromocion.condonacion_impuesto.splice(key2,1);
                            }
                        });


                    paquete.impuesto_total                  = paquete.impuesto_total_old -  ((parseInt($ele_paquete_val.val()) *  paquete.impuesto_total_old ) / 100 )  ;
                    paquete.condonacion_porcentaje_total    = parseInt($ele_paquete_val.val());
                    envioPromocion.condonacion_impuesto.push(paquete);
                break;
            }
        }
    });
    render_paquete_template();
    render_promocion_paquete_template();
}



$('#form-promocion-manual').click(function(){
    $('#modal-promocion-manual').modal('hide');
    $content_tab.find('.next').show();
    if($promocion_manual_tipo.val() == envio_promocion_tipo.tipo_libras && $promocion_manual_lb_free.val() )
    {
        envioPromocion.libras_free = {
            "tipo"            : $promocion_manual_tipo.val(),
            "lb_free"            : $promocion_manual_lb_free.val(),
            "lb_pagadas"            : $promocion_manual_lb_pagadas.val(),
            "lb_exedente"            : $promocion_manual_lb_excedente.val(),
            "precio_lb_pagada"            : $promocion_manual_precio_lb_pagadas.val(),
            "precio_lb_excedente"            : $promocion_manual_precio_lb_exedente.val(),
        };


    }
    $('#subTotal_envio').val($new_SubTotal.toFixed(2));
    $inputEnvio_promocion.val(JSON.stringify(envioPromocion));
    refresh_precios_envio();

});
</script>
