<?php
use yii\helpers\Url;
use app\models\esys\EsysSetting;
 ?>

<script>


$(function () {


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
                "peso"                  : $.trim($form_paquete.$peso.val()) ? $.trim($form_paquete.$peso.val()) : 0,
                "valor_declarado"       : $form_paquete.$valor_declarado.val(),
                "producto_id"           : selectProducto_array.id,
                "producto_text"         : selectProducto_array.text,
                "producto_tipo"         : $form_paquete.$producto_tipo.val(),
                "valoracion_paquete"    :  $form_paquete.$valoracion_paquete.val(),
                "costo_neto_extraordinario" : is_costo_extraordinario ? $form_paquete.$costo_extraordinario.val() : 0 ,
                "is_costo_extraordinario"   : is_costo_extraordinario ? true : false,

                "observaciones"         : $form_paquete.$observacion.val(),
                "seguro"                : $form_paquete.$seguro.prop('checked') ? true : false,
                "costo_seguro"          : $form_paquete.$seguro.prop('checked') && !is_costo_extraordinario ? (  Math.floor(parseFloat($form_paquete.$valor_declarado.val()) / 100) * costo_seguro_select )   : 0,
                "status"                : 10,
                "update"                : $envioID.val() ? 10 : 1,
                "origen"                : 1
        };

        paquete_array.push(paquete);

        render_paquete_template();

        clear_form($form_paquete);
        $selectProducto.val(false).trigger('change');
        //$reenvio_select_id.val(false).trigger('change');
        $form_paquete.$producto_tipo.val(tipoProducto.nuevo).trigger('change');


    });

    var validation_form_envio = function()
    {
        $error_add_paquete.html('');

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

            case !$reenvio_select_id.val() ||  $reenvio_select_id.val() == 0 :
                $error_add_paquete.append('<div class="help-block">* Debes seleccionar una dirección destino al paquete</div>');
                $error_add_paquete.show();
                return true;
            break;

            case is_costo_extraordinario :
                if (!$form_paquete.$costo_extraordinario.val()) {
                    $error_add_paquete.append('<div class="help-block">COSTO NETO es requerido para este paquete</div>');
                    $error_add_paquete.show();
                    return true;
                }
            break;

            case !$form_paquete.$valor_declarado.val():
                $error_add_paquete.append('<div class="help-block">* Valor declarado no puede ser nulo</div>');
                $error_add_paquete.show();
                return true;
            break;
        }

    }

    /*====================================================
    *               MODIFICA EL PRECIO ACTUAL
    *====================================================*/
    $('#peso_total_').change(function(){

        /** promocionVigente ? $valida_promocion_envio.show() : null;
            promocionVigente ? $content_tab.find('.next').hide() : $content_tab.find('.next').show();**/
        peso_restar =  0;
        costo_extraordinario_total =  0;
        $.each(paquete_array, function(key, paquete){
            if (paquete.paquete_id) {
                if(paquete.status == 10){
                    if (paquete.is_costo_extraordinario) {
                        peso_restar = peso_restar + parseFloat(paquete.peso);
                        costo_extraordinario_total = costo_extraordinario_total + parseFloat(paquete.costo_neto_extraordinario);
                    }
                }
            }
        });

        peso_total_sum = $(this).val() - peso_restar;

        $('#envio-subtotal').val(parseFloat( peso_total_sum * precio_libra_actual).toFixed(2) );

        $('#envio-subtotal-label').html(btf.conta.money($('#envio-subtotal').val()));

        total_envio =  parseFloat($('#envio-seguro_total').val() ? $('#envio-seguro_total').val() : 0) + parseFloat($('#envio-subtotal').val() ? $('#envio-subtotal').val() : 0);

        total_envio = total_envio +  costo_extraordinario_total;

        $('#envio-total').val(total_envio.toFixed(2));
        $('#envio-total-label').html(btf.conta.money(total_envio.toFixed(2)));

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
});

/*====================================================
*               RENDERIZA TODO LOS PAQUETE
*====================================================*/
var render_paquete_template = function()
{
    $content_paquete.html("");
    sum_peso_total  = 0;
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




                $.each(renvio_array, function(key, value){
                    $("#table_reenvio_id",$tr).append("<option value='" + value.reenvio_id + "'> Estado:  " + (value.estado_id ? value.estado_text : 'N/A') + ", Municipio: " + (value.municipio_id ? value.municipio_text : 'N/A') +", Colonia: "+ (value.colonia_id  ? value.colonia_text : 'N/A') + "</option>\n");
                });

                $("#table_reenvio_id  option[value="+ paquete.reenvio_id +"]",$tr).prop('selected', true);
                $("#table_reenvio_id",$tr).attr("onchange","refresh_paquete_change(this,'PAQUETE_REENVIO')");


                $("#table_peso",$tr).val(paquete.peso);
                $("#table_peso",$tr).attr("onchange","refresh_paquete_change(this,'PAQUETE_PESO')");
                //$("#table_impuesto",$tr).html(parseFloat(paquete.producto_detalle_impuesto));


                //$("#table_seguro",$tr).html(paquete.seguro ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>");
                $("#table_seguro",$tr).html(paquete.seguro ? '<input type="checkbox" checked="true" onchange="refresh_paquete_change(this,' + "'PAQUETE_SEGURO'" + ')" >' : '<input  type="checkbox" onchange="refresh_paquete_change(this, ' + "'PAQUETE_SEGURO'" + ')">');


                $("#table_costo_seguro",$tr).html(paquete.costo_seguro);
                $("#table_valor_declarado",$tr).val(paquete.valor_declarado);
                $("#table_valor_declarado",$tr).attr("onchange","refresh_paquete_change(this,'PAQUETE_VALOR_DECLARADO')");

                $("#table_observacion",$tr).html(paquete.observaciones);



                sum_peso_total = sum_peso_total + parseFloat(paquete.peso);

                seguro_total = seguro_total + parseFloat(paquete.costo_seguro);
                declarado_total = declarado_total + parseFloat(paquete.valor_declarado);

                $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
            }
        }
    });

    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));


    $('#envio-seguro_total').val(parseFloat(seguro_total).toFixed(2));
    $('#envio-seguro_total-label').html(btf.conta.money(seguro_total));
    $('#peso_total').val(sum_peso_total).trigger('change');
    $total_v_declarado.val(declarado_total);
    //$('#peso_total').trigger('change');
};


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
                    paquete.costo_seguro    = paquete.seguro ?   ( Math.floor(parseFloat(paquete.valor_declarado) / 100) * costo_seguro_select)    : 0;
                break;
                case 'PAQUETE_SEGURO':
                    paquete.seguro = $ele_paquete_val.prop('checked') ? true : false;

                    $ele_paquete_val.prop('checked') ? paquete.costo_seguro = (   Math.floor(parseFloat(paquete.valor_declarado) / 100  )    * costo_seguro_select ) : paquete.costo_seguro  =  0;
                break;

                case 'PAQUETE_REENVIO':
                    paquete.reenvio_id = $ele_paquete_val.val()  && $ele_paquete_val.val() != 0 ? $ele_paquete_val.val(): null;
                break;
                case 'VALOR_PAQUETE':


                    paquete.valoracion_paquete = parseFloat($ele_paquete_val.val());

                break;
            }
        }
    });

    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
}
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
                        "peso"              : item.peso ? item.peso : 0 ,
                        "valor_declarado"   : parseFloat(item.valor_declarado),
                        "producto_id"       : item.producto_id,
                        "producto_text"       : item.producto,
                        "costo_neto_extraordinario" : item.costo_neto_extraordinario,
                        "is_costo_extraordinario"   : item.is_costo_extraordinario == 10 ? true : false,
                        "producto_tipo"       : item.producto_tipo,
                        "observaciones"     : item.observaciones,
                        "seguro"            : item.seguro ? true : false,
                        "costo_seguro"      : item.costo_seguro,
                        "status"        : item.status,
                        "update"        : $envioID.val() ? 10 : 1,
                        "origen"            : 2
                    };
                }
                paquete_array.push(paquete);
            });

            $cliente_receptor.trigger('change');
            $sucursal_receptor_id.trigger('change');

            if ($isAplicaReenvio.val() == "10" ||  $isAplicaReenvio.val() == 10 ) {
                $isAplicaReenvio.val(null);
                $btnAplicaReenvio.trigger('click');
            }

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

    }
};


</script>
