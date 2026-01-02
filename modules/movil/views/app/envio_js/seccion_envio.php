<?php
use yii\helpers\Url;
use app\models\esys\EsysSetting;
 ?>

<script>
/*====================================================
*               FUNCION QUE CARGA TODO EL ARRAY
*====================================================*/
var init_paquete_list = function(){

    paquete_array = [];
    metodoPago_array = [];
    if ($envioID.val()) {

        $.get('<?= Url::to('envio-detalle-ajax') ?>', {'envio' : $envioID.val() }, function(json) {
            $.each(json.rows, function(key, item){
                if (item.id) {
                    paquete = {
                        "paquete_id"    : item.id,
                        "sucursal_id"   : item.sucursal_receptor_id,
                        "cliente_id"    : item.cliente_receptor_id,
                        "categoria_id"  : item.categoria_id,
                        "categoria_text": item.categoria,
                        "cantidad"      : item.cantidad,
                        "reenvio_id"    : item.reenvio_id,
                        "cantidad_piezas"   : item.cantidad_piezas,
                        "peso"              : item.peso ? item.peso : 0 ,
                        "unidad_medida_id"  : item.unidad_medida_id,
                        "unidad_medida_text": item.unidad_medida_text,
                        "valor_declarado"   : item.valor_declarado,
                        "producto_id"       : item.producto_id,
                        "producto_text"       : item.producto,
                        "is_impuesto"           : parseInt(item.is_impuesto),
                        "producto_tipo"       : item.producto_tipo,
                        "valoracion_paquete"  : item.valoracion_paquete,
                        //"producto_detalle_impuesto" : item.impuesto,
                        "impuesto_total"        : item.cantidad ? parseFloat( item.impuesto ? item.impuesto : 0) * item.cantidad : null,
                        "observaciones"     : item.observaciones,
                        "seguro"            : item.seguro ? true : false,
                        "costo_seguro"      : item.costo_seguro,
                        "status"        : item.status,
                        "valor_paquete" : null,
                        "update"        : $envioID.val() ? 10 : 1,
                        "origen"            : 2
                    };
                }
                paquete_array.push(paquete);
            });
        render_paquete_template();
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

        $.get('<?= Url::to('esys-direccion-ajax') ?>', { 'envio_id': $envioID.val() },function(esysDireccionJson){
            if (esysDireccionJson.rows){
                $.each(esysDireccionJson.rows,function(key, item){
                    reenvio = {
                            "reenvio_id"    : parseInt(item.id),
                            "cp"            : item.codigo_postal,
                            "estado_id"     : item.estado_id,
                            "estado_text"   : item.estado,
                            "municipio_id"  : item.municipio_id,
                            "municipio_text": item.municipio,
                            "colonia_id"    : item.colonia_id,
                            "colonia_text"  : item.colonia,
                            "direccion"     : item.direccion,
                            "n_interior"    : item.num_int,
                            "n_exterior"    : item.num_ext,
                            "referencia"    : item.referencia,
                            "status"        : 10,
                            "update"        : $envioID.val() ? 10 : 1,
                            "origen"        : 2

                    }

                    renvio_array.push(reenvio);
                    render_reenvio_template();
                    render_paquete_template();
                });
            }else{
              renvio_array = [];
            }
        });

        $.get('<?= Url::to('promocion-especial-ajax') ?>', { cliente_id : $cliente_emisor.val() },function(proEspecialJson){
            if (proEspecialJson.results){
              $div_promocion_especial_alert.show();
              promoEspecial = proEspecialJson.results;
              $promocion_especial_text.html("La promoción especial otorga un descuento de " + promoEspecial.descuento + " USD por un envio mayor o igual a " + promoEspecial.requiered_libras + " lb" );
            }else{
              $div_promocion_especial_alert.hide();
              $promocion_especial_text.html("");
              promoEspecial = [];
            }
        });

        /*$.get('<?= Url::to('envio-promocion-manual-ajax') ?>', { 'envio_id': $envioID.val() },function(envioPromocionManual){
            envioPromocion.libras_free = {
                //"tipo"            : envioPromocionManual.libras_free.tipo,
                //"lb_free"            : envioPromocionManual.libras_free.lb_free,
                //"lb_pagadas"            : envioPromocionManual.libras_free.lb_pagada,
                //"lb_exedente"            : envioPromocionManual.libras_free.lb_excedente,
                //"precio_lb_pagada"            : envioPromocionManual.libras_free.costo_libra_pagada,
                //"precio_lb_excedente"            : envioPromocionManual.libras_free.costo_libra_excendete,
            };
        });*/

        $.each(edit_load_sucursal, function(key, sucursal){
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
        });

        $sucursal_receptor_id.trigger('change');

        $.each(edit_load_cliente, function(key, cliente){
            $is_cliente = true;
            $.each(clienteSelect, function(key2 , cliente_select){
                if (cliente_select.id == cliente.id)
                    $is_cliente = $is_cliente == false ? false: false;
            });

            if ($is_cliente) {
                var newOption       = new Option(cliente.nombre, cliente.id, false, true);
                $cliente_receptor.append(newOption);
                clienteSelect.push(cliente);
            }
        });

        $cliente_receptor.trigger('change');

        if ($isAplicaReenvio.val() == 10) {
            $isAplicaReenvio.val(null);
            $btnAplicaReenvio.trigger('click');
        }

    }
};

/*====================================================
*               AGREGA UN ITEM A ARRAY
*====================================================*/
$btnAgregarPaquete.click(function(){
    if(validation_form_envio()){
        return false;
    }
    //key = search_item($form_paquete.$producto.val(),productoCategoria);

    paquete = {
            "paquete_id"            : paquete_array.length + 1,
            "sucursal_id"           : $paquete_sucursal_id.val(),
            "cliente_id"            : $paquete_cliente_id.val(),
            "reenvio_id"            : $reenvio_select_id.val() && $reenvio_select_id.val() != 0  ? $reenvio_select_id.val() : null,
            "categoria_id"          : selectProducto_array.categoria_id,
            "categoria_text"        : selectProducto_array.categoria,
            "cantidad"              : $form_paquete.$cantidad.val(),
            "cantidad_piezas"       : $form_paquete.$cantidad_piezas.val(),
            "peso"                  : $.trim($form_paquete.$peso.val()) ? $.trim($form_paquete.$peso.val()) : 0,
            "unidad_medida_id"      : $form_paquete.$unidad_medida_id.val(),
            "unidad_medida_text"    : $('option:selected', $form_paquete.$unidad_medida_id ).text(),
            "valor_declarado"       : $form_paquete.$valor_declarado.val(),
            "producto_id"           : selectProducto_array.id,
            "producto_text"         : selectProducto_array.text,

            "producto_tipo"         : $form_paquete.$producto_tipo.val(),
            "is_impuesto"           : parseInt(selectProducto_array.is_impuesto),
            "valoracion_paquete"    : parseInt(selectProducto_array.is_impuesto) ? $form_paquete.$valoracion_paquete.val() : 0,
            "impuesto_total"        : parseInt(selectProducto_array.is_impuesto) ? (parseInt($form_paquete.$producto_impuesto.val()) * parseFloat($form_paquete.$valoracion_paquete.val()) ) / 100 : 0,
            "observaciones"         : $form_paquete.$observacion.val(),
            "seguro"                : $form_paquete.$seguro.prop('checked') ? true : false,
            "costo_seguro"          : $form_paquete.$seguro.prop('checked') ? ( costo_seguro_select  * parseFloat($form_paquete.$valor_declarado.val())) / 100 : 0,
            "status"                : 10,
            "update"                : $envioID.val() ? 10 : 1,
            "origen"                : 1
    };

    paquete_array.push(paquete);

    render_paquete_template();

    clear_form($form_paquete);
    $selectProducto.val(false).trigger('change');
    $form_paquete.$producto_tipo.val(tipoProducto.nuevo).trigger('change');
    $form_paquete.$unidad_medida_id.html('');
});

var validation_form_envio = function()
{
    $error_add_paquete.html('');

    is_alert = false;
    $.each(paquete_array,function(key, paquete){
        if (paquete.status == 10) {
            if (parseInt($paquete_cliente_id.val()) ==  parseInt(paquete.cliente_id) ){
                if (paquete.reenvio_id != null) {
                    if (paquete.reenvio_id != $reenvio_select_id.val() ) {
                        is_alert = true;
                    }
                }else if ($reenvio_select_id.val()) {
                    is_alert = true;
                }
            }
        }
    });

    if (is_alert) {
        $.niftyNoty({
            type: 'warning',
            icon : 'pli-like-2 icon-2x',
            message : 'Ocurrio un error con '+ $('option:selected', $paquete_cliente_id ).text() +" no se puede relaccionar con este envío, selecciona otro cliente para seguir ingresando mas paquetes",
            container : 'floating',
            timer : 5000
        });
        return true;
    }

    switch(true){
        case !$paquete_sucursal_id.val() :
            $error_add_paquete.append('<div class="help-block">* Selecciona una sucursal receptor</div>');
            $error_add_paquete.show();
            return true;
        break;
        case !$paquete_cliente_id.val() :
            $error_add_paquete.append('<div class="help-block">* Selecciona un cliente receptor</div>');
            $error_add_paquete.show();
            return true;
        break;
        case !$selectProducto.val() :
            $error_add_paquete.append('<div class="help-block">* Selecciona un producto</div>');
            $error_add_paquete.show();
            return true;
        break;

        case !$form_paquete.$cantidad.val() :
            $error_add_paquete.append('<div class="help-block">* N° de piezas no puede ser nulo</div>');
            $error_add_paquete.show();
            return true;
        break;
        case !$form_paquete.$cantidad_piezas.val() :
            $error_add_paquete.append('<div class="help-block">* Cantidad de elementos no puede ser nulo</div>');
            $error_add_paquete.show();
            return true;
        break;

        case !$form_paquete.$valor_declarado.val():
            $error_add_paquete.append('<div class="help-block">* Valor declarado no puede ser nulo</div>');
            $error_add_paquete.show();
            return true;
        break;

        case $form_paquete.$producto_impuesto.val() != '':

            if (parseInt($form_paquete.$producto_impuesto.val()) != 0) {
                if (!$form_paquete.$valoracion_paquete.val()) {
                    $error_add_paquete.append('<div class="help-block">* La valoración no puede ser nulo</div>');
                    $error_add_paquete.show();
                    return true;
                }
            }
           return false;
        break;

    }
}

/*====================================================
*               RENDERIZA TODO LOS PAQUETE
*====================================================*/
var render_paquete_template = function()
{
    $content_paquete.html("");
    sum_peso_total  = 0;
    impuesto_total  = 0;
    seguro_total    = 0;
    declarado_total = 0;
    peso_paquete_array = [];

    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id) {
            if(paquete.status == 10){

                if (paquete.peso) {
                    is_paquetePeso = false;
                    $.each(peso_paquete_array,function(key,paquetePeso){
                      if (paquetePeso.categoria_id == paquete.categoria_id){
                            is_paquetePeso = true;
                            paquetePeso.peso = parseFloat(paquetePeso.peso) + parseFloat(paquete.peso);
                        }
                    });

                    if (!is_paquetePeso) {
                        peso_paquete = {
                            categoria_id : paquete.categoria_id,
                            peso : parseFloat(paquete.peso),
                        };
                        peso_paquete_array.push(peso_paquete);
                    }
                }

                template_sucursal = $template_paquete.html();
                template_sucursal = template_sucursal.replace("{{paquete_id}}",paquete.paquete_id);

                $content_paquete.append(template_sucursal);

                $tr        =  $("#paquete_id_" + paquete.paquete_id, $content_paquete);
                $tr.attr("data-paquete_id",paquete.paquete_id);
                $tr.attr("data-origen",paquete.origen);



                $("#table_categoria_id",$tr).html(paquete.producto_text);
                $("#table_cantidad",$tr).val(paquete.cantidad);
                $("#table_cantidad",$tr).attr("onchange","refresh_paquete_change(this,'PAQUETE_CANTIDAD')");


                if(paquete.is_impuesto){
                    $('.table_valor_paquete').show();
                    $('.tr_valor_paquete').show();
                    $('#th_promocion_valor_paquete').show();
                    $("#table_valor_paquete",$tr).val(paquete.valoracion_paquete);
                    $("#table_valor_paquete",$tr).attr("onchange","refresh_paquete_change(this,'VALOR_PAQUETE')");
                }

                $.each(renvio_array, function(key, value){
                    $("#table_reenvio_id",$tr).append("<option value='" + value.reenvio_id + "'> Estado:  " + (value.estado_id ? value.estado_text : 'N/A') + ", Municipio: " + (value.municipio_id ? value.municipio_text : 'N/A') +", Colonia: "+ (value.colonia_id  ? value.colonia_text : 'N/A') + "</option>\n");
                });

                $("#table_reenvio_id  option[value="+ paquete.reenvio_id +"]",$tr).prop('selected', true);
                $("#table_reenvio_id",$tr).attr("onchange","refresh_paquete_change(this,'PAQUETE_REENVIO')");

                $("#table_unidad_medida",$tr).html(paquete.unidad_medida_text);
                $("#table_peso",$tr).val(paquete.peso);
                $("#table_peso",$tr).attr("onchange","refresh_paquete_change(this,'PAQUETE_PESO')");
                //$("#table_impuesto",$tr).html(parseFloat(paquete.producto_detalle_impuesto));
                $("#table_impuesto_total",$tr).html(parseFloat(paquete.impuesto_total));

                //$("#table_seguro",$tr).html(paquete.seguro ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>");
                $("#table_seguro",$tr).html(paquete.seguro ? '<input type="checkbox" checked="true" onchange="refresh_paquete_change(this,' + "'PAQUETE_SEGURO'" + ')" >' : '<input  type="checkbox" onchange="refresh_paquete_change(this, ' + "'PAQUETE_SEGURO'" + ')">');


                $("#table_costo_seguro",$tr).html(paquete.costo_seguro);
                $("#table_valor_declarado",$tr).val(paquete.valor_declarado);
                $("#table_valor_declarado",$tr).attr("onchange","refresh_paquete_change(this,'PAQUETE_VALOR_DECLARADO')");

                $("#table_observacion",$tr).html(paquete.observaciones);
                // # EDITT //sum_peso_total = sum_peso_total + parseFloat(paquete.peso);
                impuesto_total = impuesto_total + parseFloat(paquete.impuesto_total);
                seguro_total = seguro_total + parseFloat(paquete.costo_seguro);
                declarado_total = declarado_total + parseFloat(paquete.valor_declarado);
                $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
            }
        }
    });

    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));

    $('#impuesto_total_envio').val(impuesto_total);
    $('#seguro_total_envio').val(seguro_total);
    //$('#peso_total').val(sum_peso_total).trigger('change');
    $total_v_declarado.val(declarado_total);
    $('#peso_total').trigger('change');
};

/*====================================================
*   Obtiene los productos filtrado por categoria
*====================================================*/
/*
$form_paquete.$categoria.change(function(){
    filters = "tipo_servicio="+ $tipo_envio.val()+"&categoria_id=" + $(this).val();
    $form_paquete.$producto.html('');
    $form_paquete.$unidad_medida_id.html('');
    select_producto = 0;
    if ($(this).val()) {
        $.get('<?= Url::to('productos-categoria-ajax') ?>', { filters: filters},function(producto){
            if(parseInt(producto.total) > 0){
                productoCategoria  = producto.rows;
                $.each(producto.rows, function(key, value){
                    $form_paquete.$producto.append("<option value='" + value.id + "'>" + value.nombre + "</option>\n");
                    select_producto = parseInt(value.id);
                });
            }
            $form_paquete.$producto.val(select_producto).trigger('change');
        },'json');
    }
});
*/
/*====================================================
*   Obtiene la informacion del producto
*====================================================*/
/*$form_paquete.$producto.change(function(){
    $form_paquete.$producto_impuesto.val('');
    $div_valoracion_paquete.hide();
    if ($(this).val()) {

        key = search_item($(this).val(),productoCategoria);
        var newOption   = new Option(productoCategoria[key].unidad_medida, productoCategoria[key].id, false, true);
        $form_paquete.$unidad_medida_id.append(newOption);

        $form_paquete.$producto_tipo.change().trigger('change');

    }else{
        $form_paquete.$unidad_medida_id.val(null);
    }
});
*/


/*====================================================
*   Cargamos el impuesto correspondiente
*====================================================*/
$form_paquete.$producto_tipo.change(function(){

    //producto_id  = $selectProducto.val();
    //key = search_item(producto_id,productoCategoria);

    if (selectProducto_array) {
        if(selectProducto_array.is_impuesto == is_impuesto_on){
            $div_valoracion_paquete.show();
            valoracion_producto();
            if ($tipo_envio.val() == tipoEnvio.tierra) {
                if ($(this).val() == tipoProducto.nuevo ) {
                    $form_paquete.$producto_impuesto.val(producto_tipo_tierra_impuesto.nuevo);
                }
                if ($(this).val() == tipoProducto.usado ) {
                    $form_paquete.$producto_impuesto.val(producto_tipo_tierra_impuesto.usado);
                }
            }
            else if ($tipo_envio.val() == tipoEnvio.lax) {
                if ($(this).val() == tipoProducto.nuevo ) {
                    $form_paquete.$producto_impuesto.val(producto_tipo_lax_impuesto.nuevo);
                }
                if ($(this).val() == tipoProducto.usado ) {
                    $form_paquete.$producto_impuesto.val(producto_tipo_lax_impuesto.usado);
                }
            }else
                $form_paquete.$producto_impuesto.val(0);
        }else
            $form_paquete.$producto_impuesto.val(0);
    }else
        $form_paquete.$producto_impuesto.val(0);
});

/******************************************************************
* Obtiene la ultima valoracion del producto seleccionado
******************************************************************/
var valoracion_producto = function (){
    if ($selectProducto.val() && $form_paquete.$producto_tipo.val()) {
        $form_paquete.$valoracion_paquete.val(0);
        $.get('<?= Url::to('valoracion-historial-ajax') ?>',
        {
            producto_id   : $selectProducto.val() ,
            producto_tipo : $form_paquete.$producto_tipo.val()
        },function(json_valoracion_producto){
            if (json_valoracion_producto.message) {
               $form_paquete.$valoracion_paquete.val(json_valoracion_producto.message);
            }
        });
    }
}

/*===============================================
* Actualiza la lista de paquetes
*===============================================*/

var refresh_paquete = function(ele){
   $ele_paquete_val = $(ele).closest('tr');

    $ele_paquete_id  = $ele_paquete_val.attr("data-paquete_id");
    $ele_origen_id   = $ele_paquete_val.attr("data-origen");

    $.each(paquete_array, function(key, paquete){
        if (paquete) {
            if (paquete.paquete_id == $ele_paquete_id && paquete.origen == $ele_origen_id ) {
                if (paquete.origen ==  1)
                    paquete_array.splice(key, 1 );

                if (paquete.origen == 2 )
                    paquete.status = 1;
            }
        }
    });

    $(ele).closest('tr').remove();
    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
};

var refresh_paquete_change = function(ele,inputChange){

    $ele_paquete_val    = $(ele);
    $ele_paquete        = $(ele).closest('tr');
    $ele_paquete_detalle_id  = $ele_paquete.attr("data-paquete_id");
    $ele_paquete_origen_id   = $ele_paquete.attr("data-origen");

    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id == $ele_paquete_detalle_id && paquete.origen == $ele_paquete_origen_id ){

            switch(inputChange){
                case 'PAQUETE_CANTIDAD':
                    paquete.cantidad = $ele_paquete_val.val();
                    //paquete.impuesto_total = paquete.producto_detalle_impuesto ? paquete.cantidad * paquete.producto_detalle_impuesto : null;
                break;
                case 'PAQUETE_PESO':
                    paquete.peso = $ele_paquete_val.val();
                    //paquete.impuesto_total = paquete.producto_detalle_impuesto ? paquete.cantidad * paquete.producto_detalle_impuesto : null;
                break;
                case 'PAQUETE_VALOR_DECLARADO':
                    paquete.valor_declarado = $ele_paquete_val.val();
                    paquete.costo_seguro    = paquete.seguro ?  ( costo_seguro_select  * parseFloat(paquete.valor_declarado )) / 100 : 0;
                break;
                case 'PAQUETE_SEGURO':
                    paquete.seguro = $ele_paquete_val.prop('checked') ? true : false;

                    $ele_paquete_val.prop('checked') ? paquete.costo_seguro = ( costo_seguro_select  * parseFloat(paquete.valor_declarado )) / 100  : paquete.costo_seguro  =  0;
                break;

                case 'PAQUETE_REENVIO':
                    paquete.reenvio_id = $ele_paquete_val.val()  && $ele_paquete_val.val() != 0 ? $ele_paquete_val.val(): null;
                break;
                case 'VALOR_PAQUETE':

                    if ($tipo_envio.val() == tipoEnvio.tierra && paquete.is_impuesto == is_impuesto_on) {
                        if (paquete.producto_tipo == tipoProducto.nuevo )
                            paquete.impuesto_total = (parseInt(producto_tipo_tierra_impuesto.nuevo) * parseFloat($ele_paquete_val.val())) / 100;
                        if (paquete.producto_tipo == tipoProducto.usado )
                            paquete.impuesto_total = (parseInt(producto_tipo_tierra_impuesto.usado) * parseFloat($ele_paquete_val.val())) / 100;

                    }
                    else if ($tipo_envio.val() == tipoEnvio.lax && paquete.is_impuesto == is_impuesto_on) {

                        if (paquete.producto_tipo == tipoProducto.nuevo )
                            paquete.impuesto_total = (parseInt(producto_tipo_lax_impuesto.nuevo) * parseFloat($ele_paquete_val.val())) / 100;
                        if (paquete.producto_tipo == tipoProducto.usado )
                            paquete.impuesto_total = (parseInt(producto_tipo_lax_impuesto.usado) * parseFloat($ele_paquete_val.val())) / 100;
                    }
                    paquete.valoracion_paquete = parseFloat($ele_paquete_val.val());

                break;
            }
        }
    });

    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
}



/*====================================================
*               MODIFICA EL PRECIO ACTUAL
*====================================================*/
$('#peso_total').change(function(){

    /** promocionVigente ? $valida_promocion_envio.show() : null;
        promocionVigente ? $content_tab.find('.next').hide() : $content_tab.find('.next').show();**/

    $('#subTotal_envio').val(parseFloat($(this).val()) * precio_libra_actual );

    var val_subtotal_round = parseFloat($('#subTotal_envio').val());
    $('#subTotal_envio').val(val_subtotal_round.toFixed(2));

    total_envio = parseFloat($('#subTotal_envio').val()) + parseFloat($('#impuesto_total_envio').val() ? $('#impuesto_total_envio').val() : 0);

    total_envio = total_envio + parseFloat($('#seguro_total_envio').val() ? $('#seguro_total_envio').val() : 0);
    if($descuento_manual_check.prop('checked'))
        $('#total_envio').val(total_envio - parseFloat(($('#descuento_manual').val() ? $('#descuento_manual').val()  : 0 )));
    else
        $('#total_envio').val(total_envio.toFixed(2));

    if (promoEspecial) {
        if(parseInt($(this).val()) >= parseInt(promoEspecial.requiered_libras)){
            $div_promocion_especial_info.show();
            $codigo_promocional_especial_id.val(promoEspecial.id);
            new_total_especial =parseFloat($('#total_envio').val()) - parseFloat((promoEspecial.descuento ? promoEspecial.descuento  : 0 ));
            $promocion_especial_text_info.html("Descuento aplicado: " + total_envio.toFixed(2) + " USD - " + promoEspecial.descuento +" USD" );
            $('#total_envio').val(new_total_especial.toFixed(2));
        }else{
            $div_promocion_especial_info.hide();
            $codigo_promocional_especial_id.val(null);
        }
    }

    if ($isAplicaReenvio.val() == 10 ) {
        cal_costo_reenvio();
        new_total_reenvio = parseFloat($('#total_envio').val()) + parseFloat($inputcosto_reenvio.val());
        $('#total_envio').val(new_total_reenvio.toFixed(2));
    }

    var val_total_round = parseFloat($('#total_envio').val());

    $('#total_envio').val(val_total_round.toFixed(2));

    $total_promocion.html(val_total_round.toFixed(2));

    render_metodo_template();
});


var cal_costo_reenvio = function(){
    peso_reenvio_total = $peso_reenvio.val() ? $peso_reenvio.val() : 0;
    if ($peso_reenvio.val() > 100){
        opera_costo_reenvio = ((parseInt(precio_base_reenvio) / 100 )  * peso_reenvio_total );
        $inputcosto_reenvio.val(opera_costo_reenvio.toFixed(2));
    }
    else if($peso_reenvio.val() > 0)
        $inputcosto_reenvio.val(precio_base_reenvio);
    else
        $inputcosto_reenvio.val(0);

    $('#lbl_peso').html( peso_reenvio_total + " lb");
    $('#lbl_costo_reenvio').html($inputcosto_reenvio.val() + " USD");
}

$peso_reenvio.change(function(){
    $('#peso_total').trigger('change');
});

</script>
