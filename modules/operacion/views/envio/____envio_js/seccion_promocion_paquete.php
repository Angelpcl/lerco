<?php
use yii\helpers\Url;
use app\models\promocion\Promocion;
use app\models\esys\EsysSetting;
?>
<script>

$(function () {
    /*========================================================
    * Carga promocion vigente y cambia dependiendo al servicio
    *=========================================================*/

    var load_promocion = function(){

        filters = "";
        if ($envioID.val()){
            if ($promocion_id.val()) {
                filters = "promocion_id=" + $promocion_id.val();
            }else{
                filters = "tipo_servicio="+ $tipo_envio.val() + "&tipo="+ <?= Promocion::TIPO_GENERAL  ?>;
            }
        }
        else{
            filters = "tipo_servicio="+ $tipo_envio.val() + "&tipo="+ <?= Promocion::TIPO_GENERAL  ?>;
        }

        $.get('<?= Url::to(['promocion-info-ajax']) ?>',{ filters: filters},function(json){
            if (json.id) {
                $div_promocion_alert.show();
                $link_promocion.attr('href','<?= Url::to(['/promociones/promocion/view?id=']) ?>' +  json.id);
                $link_promocion.html(json.nombre);
                $link_promocion.data("id",json.id);
                $link_promocion.data("is_manual",json.is_manual);
                $link_promocion.data("is_code",json.is_code_promocional);
                promocionVigente = true;
            }else{
                $div_promocion_alert.hide();
                promocionVigente = false;
            }
        },'json');
    }
    /*========================================================
    * Evento que realiza la consulta de los detalles y complementos de la promoción
    *=========================================================*/

    $valida_promocion_envio.click(function(){
        if(promocionVigente){
            if ( pesoTotal() != 0) {
                $modal_title.html('');
                $table_complemento_promocion.html('');
                $div_precios_libra.html('');
                $('.div_complemento').html(' ');
                promocionDetalle     = [];
                promocionAnexo       = [];
                promocionComplemento  = [];
                $promocion_id.val($link_promocion.data("id"));

                if ($link_promocion.data("is_code") == <?=  Promocion::IS_CODE_OF  ?>)
                    $div_search_promocion.hide();
                if ($link_promocion.data("is_manual") == isPromocionManual.off)
                    load_promocion_sistema();
                if ($link_promocion.data("is_manual") == isPromocionManual.on)
                    load_promocion_manual();
            }
        }
    });

    /*============================================================
    * Carga promocion SISTEMA
    *=============================================================*/
    var load_promocion_sistema = function(){
        filters = "promocion_id="+ $link_promocion.data("id")  + "&peso_requerido=" + pesoTotal();
        $.get('<?= Url::to(['promocion-valida-ajax']) ?>',{ filters: filters, paquetePeso : peso_paquete_array },function(json){

            $('#modal-promocion').modal('show');
            if (json.PromocionDetalle.length > 0) {
                $div_form_promocion_complemento.show();
                $modal_title.html('¡Aplicaste una promoción vigente! ' );
                //$count_promocion_detalle_show = 2;
                $.each(json.PromocionDetalle, function(key, p_detalle){
                    if (p_detalle.id /*&& $count_promocion_detalle_show > 0*/) {
                        template_precio = $template_precio.html();
                        template_precio = template_precio.replace("{{p_detalle_id}}",p_detalle.id);
                        template_precio = template_precio.replace("{{p_anexo_detalle_id}}",p_detalle.id);
                        $div_precios_libra.append(template_precio);

                        $div_precio        =  $("#p_detalle_id_" + p_detalle.id,$div_precios_libra);

                        $('.precio_sin_id',$div_precio).html('$ '+ p_detalle.costo_libra_sin_code + ' USD');
                        $('.precion_con_id',$div_precio).html('$ ' + p_detalle.costo_libra_code + ' USD');
                        $('.libra_required',$div_precio).html( p_detalle.lb_requerida + ' lb');




                        $('.btnPromocionDetalle',$div_precio).attr("onclick","refresh_promocion_deatelle_select(this)");
                        $('.btnPromocionDetalle',$div_precio).attr("data-p_detalle_id",p_detalle.id);

                        //$count_promocion_detalle_show = $count_promocion_detalle_show - 1;
                        promocionDetalle.push(p_detalle);
                    }
                });

                $.each(json.PromocionAnexo,function(key,p_anexo){
                    if (p_anexo.length > 0) {
                        $.each(p_anexo,function(key2,anexo){
                            if (anexo.id) {

                                $anexo = $('#div_anexo_promocion_id_' + anexo.producto_detalle_id);
                                template_anexo = $template_anexo.html();
                                template_anexo = template_anexo.replace("{{anexo_id}}",anexo.id);
                                $('.div_anexos_all',$anexo).append(template_anexo);

                                $div_anexo = $(".div_anexo_id_" + anexo.id , $anexo);

                                categoria_nombre = '';
                                $.each(anexo.categoria, function(key, categorias){
                                    $.each(categorias, function(key, categoria){

                                        categoria_nombre += categoria.is_categoria == 10 ? categoria.categoria + " - " : 'Todas las categorias - ';
                                    });
                                });

                                $('.lbl_categoria_anexo',$div_anexo).html(categoria_nombre);


                                $('.lbl_libras_free_anexo',$div_anexo).html(anexo.lb_free + " lb");
                                $anexo.show();

                            }
                        });
                        promocionAnexo.push(p_anexo);
                    }
                });

                isCodePromocionalSelect = false;
                load_panel_beneficios();

                $code_valida.trigger('click');
                //$('#peso_total').trigger('change');
                $.each(json.PromocionComplemento,function(key,p_complemento){
                    if (p_complemento.length > 0 ) {
                        $.each(p_complemento,function(key,item){

                            key = search_item(item.producto_detalle_id,promocionDetalle);
                            if (!isNaN(parseInt(key))) {
                                template_complemento = $template_complemento.html();
                                template_complemento = template_complemento.replace("{{complemento_id}}",item.id);
                                $table_complemento_promocion.append(template_complemento);

                                $tr        =  $("#complemento_id_" + item.id,$table_complemento_promocion);

                                $("#table-lbrequired",$tr).html(promocionDetalle[key].lb_requerida);

                                $("#table-num_producto",$tr).html(item.num_producto);

                                $("#table-tipo",$tr).html(tipoList[item.is_categoria]);
                                $("#table-categoria_text",$tr).html(item.categoria_id ? item.categoria : 'N/A');
                                $("#table-producto_text",$tr).html(item.producto_id ?  item.producto : 'N/A');

                                $("#table-valor_promedio_check",$tr).html(item.is_valor_paquete == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i><p>"+ item.valor_paquete_aprox + " USD<p>" : "<i class='fa fa-times' aria-hidden='true'></i>");


                                $("#table-lbfree_check",$tr).html(item.is_lb_free == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" + "<p>" + item.lb_free + " lb</p>" : "<i class='fa fa-times' aria-hidden='true'></i>");

                                $("#table-sin_impuesto_check",$tr).html(item.cobro_impuesto == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>");

                                $("#table-is_envio_check",$tr).html(item.is_envio_free  == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" );

                                //btn_disabled_select = $promocion_complemento_id.val() == item.id  ? 'disabled' : !isPromocionDetalleSelect ?  'disabled' : '';

                                if (item.tipo_complemento == tipoComplemento.eleccion)
                                    $tr.append("<td><button class='btn btn-warning add_complemento_promocion'  type='button' onclick='select_complemento(this, "+ item.id +")'>Aplicar a promocion</button></td>");
                                else if (item.tipo_complemento == tipoComplemento.general)
                                    $tr.append("<td><button class='btn btn-primary add_complemento_promocion'  type='button' onclick='select_complemento(this, "+ item.id +",true,"+ promocionDetalle[key].id +")'>Aplicar a todos los complementos "+ promocionDetalle[key].lb_requerida +"</button></td>");


                            }
                        });
                        promocionComplemento.push(p_complemento);
                    }
                });


                $.each(json.PromocionDetalle, function(key, p_detalle){
                    if (p_detalle.id ) {
                        $div_precio        =  $("#p_detalle_id_" + p_detalle.id);
                        total_con_promocion = valida_promocion_total(p_detalle.id);
                        $('.precio_total_promocion',$div_precio).html(total_con_promocion + ' USD');
                    }
                });

                selected_styles();
                $envioID.val() && $promocion_detalle_id.val() ? select_promocion_detalle(parseInt($promocion_detalle_id.val())) : '';



            }else{
                $div_form_promocion_complemento.hide();
                $modal_title.html('No cumple con lo requerimientos de la promoción vigente ');
            }
        },'json');
    }

    /*============================================================
    * Carga promocion MANUAL
    *=============================================================*/
    var load_promocion_manual = function(){
        $('#modal-promocion-manual').modal('show');
        $modal_title_manual.html('Ingresa los beneficios correspondientes de la promoción vigente ! ' );
        $promocion_id.val($link_promocion.data("id"));
        render_paquete_template();
        $promocion_manual_peso_total.html(peso_total_envio);
        $promocion_manual_total_promocion.html($('#subTotal_envio').val());
        $promocion_manual_total_sin_promocion.html($('#subTotal_envio').val());
        $promocion_manual_impuesto_sin_total.html($('#impuesto_total_envio').val());
        $promocion_manual_tipo.trigger('change');
    }

    /****************************************************
    * Evento inicializa las variables cuando se selecciona una promoción
    ****************************************************/
    var refresh_promocion_deatelle_select = function(elem){

        $promocion_complemento_id.val(null);
        promocionDetalleSelect.promocionDetalle         = {};
        promocionComplementoSelect                      = [];
        promocionDetalleSelect.promocionComplemento     = [];
        promocionDetalleSelect.promocionAnexo       = [];
        $ele_p_detalle_id                           = $(elem).attr("data-p_detalle_id");

        select_promocion_detalle($ele_p_detalle_id);
    }


    /***************************************************
    * Calculamos el total de las promociones otorgadas
    ***************************************************/
    var valida_promocion_total = function($ele_p_detalle_id){
        cal_promocionDetalle    = [];
        cal_promocionAnexo      = [];
        cal_peso_total_envio    = peso_total_envio;
        key = search_item($ele_p_detalle_id,promocionDetalle);
        cal_promocionDetalle    = promocionDetalle[key];

        $.each(promocionAnexo, function(key_anexos, p_anexos){
            $.each(p_anexos, function(key_anexo, anexo){
                if (anexo.producto_detalle_id  ==  cal_promocionDetalle.id) {
                    cal_promocionAnexo.push(anexo);
                }
            });
        });

        if (cal_promocionAnexo.length > 0) {
            cal_lb_disponible = parseFloat(pesoTotal()) -  parseFloat(cal_promocionDetalle.lb_requerida);
            cal_lb_recolectado       =  0;
            cal_lb_promocion         =  0;


            $.each(cal_promocionAnexo,function(key, anexo){
                cal_lb_promocion = cal_lb_promocion + parseInt(anexo.lb_free);
            });

            $.each(paquete_array,function(key,paquete){

                is_aplica = false
                $.each(cal_promocionAnexo,function(key, anexo){
                    $.each(anexo.categoria, function(key2,categorias){
                        $.each(categorias, function(key3,categoria){
                            if (paquete.categoria_id && paquete.categoria_id  ==  categoria.categoria_id ){
                                is_aplica = true;
                            }
                            if (parseInt(categoria.is_categoria) == 1) {
                                is_aplica = true;
                            }
                        });
                    });
                });

                if (is_aplica) {
                    cal_lb_recolectado = cal_lb_recolectado + parseFloat(paquete.peso);
                }

            });

            cal_lb_recolectadas_sobrante = cal_lb_recolectado >=  cal_lb_promocion ?   cal_lb_recolectado  - cal_lb_promocion : 0;

            cal_librasSobrantes    = cal_lb_disponible  > cal_lb_promocion  ?  (cal_lb_disponible  - cal_lb_recolectado) + cal_lb_recolectadas_sobrante: 0;

            cal_peso_descuento    = (pesoTotal() -  cal_lb_disponible) + cal_librasSobrantes ;

            cal_peso_total_envio   = cal_peso_descuento;
        }


        cal_precio_libra_promocion_select =  isCodePromocionalSelect ? cal_promocionDetalle.costo_libra_code : cal_promocionDetalle.costo_libra_sin_code;

        precio_libra_promocion =  parseFloat(cal_peso_total_envio) * parseFloat(cal_precio_libra_promocion_select);


        cal_total_envio = precio_libra_promocion + parseFloat($('#impuesto_total_envio').val() ? $('#impuesto_total_envio').val() : 0);

        cal_total_envio = cal_total_envio + parseFloat($('#seguro_total_envio').val() ? $('#seguro_total_envio').val() : 0);

        if($descuento_manual_check.prop('checked'))
            cal_total_envio = cal_total_envio - parseFloat(($('#descuento_manual').val() ? $('#descuento_manual').val()  : 0 ));
        else
            cal_total_envio = cal_total_envio.toFixed(2);


        if (promoEspecial) {
            if(parseInt($('#peso_total').val()) >= parseInt(promoEspecial.requiered_libras)){
                $div_promocion_especial_info.show();
                $codigo_promocional_especial_id.val(promoEspecial.id);
                if (promoEspecial.tipo_condonacion == isPromocionEspecial.efectivo) {
                    new_total_especial = parseFloat(cal_total_envio) - parseFloat((promoEspecial.descuento ? promoEspecial.descuento  : 0 ));
                    cal_total_envio = new_total_especial.toFixed(2);
                }

                if (promoEspecial.tipo_condonacion == isPromocionEspecial.libras) {
                    libra_promoEspecial =  parseFloat(cal_peso_total_envio) - parseFloat(promoEspecial.descuento);
                    precio_libra_promocion_select =  isCodePromocionalSelect ? promocionDetalleSelect.promocionDetalle.costo_libra_code : promocionDetalleSelect.promocionDetalle.costo_libra_sin_code;
                    libra_promoEspecial = libra_promoEspecial * (precio_libra_promocion_select ? precio_libra_promocion_select : precio_libra_actual)

                    cal_total_envio = cal_total_envio - (libra_promoEspecial - $('#subTotal_envio').val());
                    $('#subTotal_envio').val(libra_promoEspecial.toFixed(2));


                }
            }else{
                $div_promocion_especial_info.hide();
                $codigo_promocional_especial_id.val(null);
            }
        }


        if ($isAplicaReenvio.val() == 10 ) {
            cal_costo_reenvio();
            new_total_reenvio = parseFloat(cal_total_envio) + parseFloat($inputcosto_reenvio.val());
            cal_total_envio   =  new_total_reenvio.toFixed(2);
        }

        if ($('#prepago_check').is(':checked') ) {
            $costo_prepago = parseFloat($('#peso_total').val()) * .10;
            $('#envio-costo_pago_vs_entrega').val(parseFloat($costo_prepago).toFixed(2));
            $('#costo_prepago').html(parseFloat($costo_prepago).toFixed(2));
            new_total_prepago = parseFloat($('#total_envio').val()) + parseFloat($('#envio-costo_pago_vs_entrega').val());
            cal_total_envio = new_total_prepago.toFixed(2);
        }

        return cal_total_envio;
    }

    /***************************************************
    * Carga en las variables la promocion que selecciono
    ***************************************************/

    var  select_promocion_detalle = function($ele_p_detalle_id){

        isPromocionDetalleSelect                    = true;
        promocionDetalleSelect.promocionComplemento = [];

        key = search_item($ele_p_detalle_id,promocionDetalle);

        promocionDetalleSelect.promocionDetalle     = promocionDetalle[key];

        $promocion_detalle_id.val(promocionDetalleSelect.promocionDetalle.id);


        $.each(promocionComplemento, function(key_complemto, p_complemento){
            $.each(p_complemento, function(key_complemto, complemento){
                if (complemento.producto_detalle_id  ==  promocionDetalleSelect.promocionDetalle.id) {
                    promocionDetalleSelect.promocionComplemento.push(complemento);
                }
            });
        });

        $.each(promocionAnexo, function(key_anexos, p_anexos){
            $.each(p_anexos, function(key_anexo, anexo){
                if (anexo.producto_detalle_id  ==  promocionDetalleSelect.promocionDetalle.id) {
                    promocionDetalleSelect.promocionAnexo.push(anexo);
                }
            });
        });




        selected_styles();
        load_panel_beneficios();
    }
    /******************************************************
    * Realiza el cambio de diseño cuando se hace la seleccion
    ******************************************************/
    var selected_styles = function(){
        $.each(promocionDetalle, function(key, p_detalle){
            $('#p_detalle_id_' + p_detalle.id).css({
                "border-style": '',
                "padding"     : '',
                "background"  : '',
            });
        });

        $('button','.table_complemento_promocion').prop('disabled',true);
        $('button','.table_complemento_promocion').addClass('btn-dark');
        $('tr','.table_complemento_promocion').css("background","");

        if (isPromocionDetalleSelect) {
            $('#p_detalle_id_' + promocionDetalleSelect.promocionDetalle.id).css({
                "border-style": "dashed",
                "padding"     : "1%",
                "background"  : "mintcream",
            });

            $.each(promocionDetalleSelect.promocionComplemento, function(key,complemento){
                $('button','#complemento_id_' + complemento.id).prop('disabled',false);
                $('button','#complemento_id_' + complemento.id).removeClass('btn-dark').addClass('btn-warning');
                $tr =  $("#complemento_id_" + complemento.id);
                $tr.css("background","");

                if (isGeneral) {
                    $tr        =  $("#complemento_id_" + complemento.id);
                    $tr.css("background","silver");
                }else{

                    $.each(promocionComplementoSelect,function(key,item){
                        $tr        =  $("#complemento_id_" + item.id);
                        $tr.css("background","silver");
                        //$promocion_complemento_id.val()  == complemento.id ?  $tr.css("background","silver") : $tr.css("background","");

                    });
                }

            });
        }
    }

    /***********************************************************
    * Carga los beneficios que se le otorgo dependiendo del complemento
    ************************************************************/
    var load_panel_beneficios = function(){
        if (isPromocionDetalleSelect) { /* Validamos que ya exista una promoción seleccionada */

            render_paquete_template();

            pesoTotal();

            $('.div_detalle_descuento').show();

            $('#title_panel_promocion').html( isCodePromocionalSelect ? 'Aplico codigo promocional (ID)' :  'No aplico codigo promocional (ID)');

            load_promocion_detalle();

            if (promocionDetalleSelect.promocionAnexo.length > 0) {

                lb_disponible = parseFloat(pesoTotal()) -  parseFloat(promocionDetalleSelect.promocionDetalle.lb_requerida);
                lb_recolectado =  0;
                lb_promocion =  0;

                $.each(promocionDetalleSelect.promocionAnexo,function(key, anexo){
                    lb_promocion = lb_promocion + parseInt(anexo.lb_free);
                });

                $.each(paquete_array,function(key,paquete){

                    is_aplica = false
                    $.each(promocionDetalleSelect.promocionAnexo,function(key, anexo){
                        $.each(anexo.categoria, function(key2,categorias){
                            $.each(categorias, function(key3,categoria){
                                if (paquete.categoria_id && paquete.categoria_id  ==  categoria.categoria_id ){
                                    is_aplica = true;
                                }
                                if (parseInt(categoria.is_categoria) == 1) {
                                    is_aplica = true;
                                }
                            });
                        });
                    });

                    if (is_aplica) {
                        lb_recolectado = lb_recolectado + parseFloat(paquete.peso);
                    }

                });

                lb_recolectadas_sobrante = lb_recolectado >=  lb_promocion ?   lb_recolectado  - lb_promocion : 0;

                librasSobrantes    = lb_disponible  > lb_promocion  ?  (lb_disponible  - lb_recolectado) + lb_recolectadas_sobrante: 0;

                peso_descuento    = (pesoTotal() -  lb_disponible) + librasSobrantes ;

                peso_total_envio   = peso_descuento;

                load_promocion_detalle();
            }


            if (promocionComplementoSelect.length > 0 ) {
            //if ($promocion_complemento_id.val()) {
                $('#title_panel_complemento').html("Complemento de promoción aplicado");
                $('.div_complemento').html(' ');
                $.each(promocionComplementoSelect,function(key,complementoSelect){
                    //complementoID = $promocion_complemento_id.val();
                    complementoID = complementoSelect.id;
                    $.each(promocionDetalleSelect.promocionComplemento,function(key, complemento){
                        if (complemento.id == complementoID) {
                            //$('#lbl_total_producto').html(complemento.num_producto);
                            //$('#lbl_categoria').html(complemento.categoria_id ? complemento.categoria : 'Aplica a todas las categorias');
                            //$('#lbl_producto').html(complemento.producto_id ? complemento.producto : 'Aplica a todos los produtos ');
                            $('.table_valor_paquete').show();
                            $('.tr_valor_paquete').show();
                            $('#th_promocion_valor_paquete').show();

                            if (parseInt(complemento.is_lb_free)  == 10)
                                load_promocion_libras_free(complemento);
                            if (parseInt(complemento.cobro_impuesto) == 10)
                                load_promocion_sin_impuesto(complemento);
                            if (parseInt(complemento.is_envio_free)  == 10)
                                load_promocion_envio_free(complemento);
                        }
                    });
                });

            }else{
                $('.div_complemento').html('');
                $('#title_panel_complemento').html("No aplico ningún complemento");
            }
        }
    }

    /*********************************************
    * Carga el detalle de la promocion seleccionda
    *********************************************/
    var load_promocion_detalle = function(){

        //$('#lbl_total_libra').html(peso_total_envio + " lb x (Libra actual " + precio_libra_actual +") = "+ (parseFloat(peso_total_envio) * parseFloat(precio_libra_actual) ) + " USD" );

        precio_libra_promocion_select =  isCodePromocionalSelect ? promocionDetalleSelect.promocionDetalle.costo_libra_code : promocionDetalleSelect.promocionDetalle.costo_libra_sin_code;

        $('#lbl_precio_lb').html( precio_libra_promocion_select + " USD");
        $('#lbl_lb_aplicado').html(promocionDetalleSelect.promocionDetalle.lb_requerida + " lb");

        $('#lbl_lb_cobradas').html(peso_total_envio + " lb");

        precio_libra_promocion =  parseFloat(peso_total_envio) * parseFloat(precio_libra_promocion_select);

    //    $('#lbl_lb_promocion').html("( Total de libras ) " + parseFloat(peso_total_envio)  +" lb x ( Precio de libra otorgada  "  + precio_libra_promocion_select+ ") =  " + precio_libra_promocion.toFixed(2)  + " USD");

        $('#subTotal_envio').val(precio_libra_promocion.toFixed(2));

        refresh_precios_envio();
    }
    /**********************************************
    * Calcula el precio total de Impuestos, Subtotal, Descuento.
    ***********************************************/
    var refresh_precios_envio = function(){

        total_envio = parseFloat($('#subTotal_envio').val()) + parseFloat($('#impuesto_total_envio').val() ? $('#impuesto_total_envio').val() : 0);

        total_envio = total_envio + parseFloat($('#seguro_total_envio').val() ? $('#seguro_total_envio').val() : 0);

        if($descuento_manual_check.prop('checked'))
            $('#total_envio').val(total_envio - parseFloat(($('#descuento_manual').val() ? $('#descuento_manual').val()  : 0 )));
        else
            $('#total_envio').val(total_envio.toFixed(2));

        if (promoEspecial) {
            if(parseInt($('#peso_total').val()) >= parseInt(promoEspecial.requiered_libras)){
                $div_promocion_especial_info.show();
                $codigo_promocional_especial_id.val(promoEspecial.id);
                if (promoEspecial.tipo_condonacion == isPromocionEspecial.efectivo) {
                    new_total_especial =parseFloat($('#total_envio').val()) - parseFloat((promoEspecial.descuento ? promoEspecial.descuento  : 0 ));
                    $promocion_especial_text_info.html("Descuento aplicado: " + total_envio.toFixed(2) + " USD - " + promoEspecial.descuento +" USD" );
                    $('#total_envio').val(new_total_especial.toFixed(2));
                }
                if (promoEspecial.tipo_condonacion == isPromocionEspecial.libras) {
                    peso_total_envio = peso_total_envio > 0 ? peso_total_envio : pesoTotal();
                    libra_promoEspecial =  parseFloat(peso_total_envio) - parseFloat(promoEspecial.descuento);
                    precio_libra_promocion_select =  isCodePromocionalSelect ? promocionDetalleSelect.promocionDetalle.costo_libra_code : promocionDetalleSelect.promocionDetalle.costo_libra_sin_code;
                    libra_promoEspecial = libra_promoEspecial * (precio_libra_promocion_select ? precio_libra_promocion_select : precio_libra_actual)
                    total_envio = total_envio - ($('#subTotal_envio').val() - libra_promoEspecial);

                    $('#total_envio').val(total_envio);

                    $('#subTotal_envio').val(libra_promoEspecial.toFixed(2));

                    $promocion_especial_text_info.html("Descuento aplicado: " + promoEspecial.descuento + " LB - de  " + peso_total_envio +" USD = "+ ( peso_total_envio - promoEspecial.descuento ));
                }
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

        if ($('#prepago_check').is(':checked') ) {
            $costo_prepago = parseFloat($('#peso_total').val()) * .10;
            $('#envio-costo_pago_vs_entrega').val(parseFloat($costo_prepago).toFixed(2));
            $('#costo_prepago').html(parseFloat($costo_prepago).toFixed(2));
            new_total_prepago = parseFloat($('#total_envio').val()) + parseFloat($('#envio-costo_pago_vs_entrega').val());
            $('#total_envio').val(new_total_prepago.toFixed(2));
        }


        $total_promocion.html($('#total_envio').val());
        render_metodo_template();

    }

    /****************************************************
    * Realiza el descuendo de libras gratis
    ****************************************************/
    var load_promocion_libras_free = function(complemento)
    {

        librasDisponible  = 0;
        $('.div_complemento').append('<p>Complemento / Beneficio: <strong id="lbl_complemento">Libras gratis ' + complemento.lb_free + ' lb </strong></p>');
        //$('#text_beneficio_complemento').show();
        //$('#lbl_beneficio_complemento').html('');
        //$('#lbl_libras_pagar').html('');
        //$('#text_libras_pagar').show();
        $('#lbl_valor_lb').html('');
        if (parseInt(complemento.is_categoria) == 10 && parseInt(complemento.is_producto) == 1 ) {
            $.each(paquete_array,function(key,paquete){
                if (paquete.categoria_id && paquete.categoria_id  ==  complemento.categoria_id ){
                    if (parseInt(complemento.is_valor_paquete) == 10) {
                        $('#lbl_valor_lb').html("<i class='fa fa-check-square-o' aria-hidden='true'></i> menor a" + complemento.valor_paquete_aprox);
                        if (parseInt(complemento.valor_paquete_aprox)  >=  parseInt(paquete.valoracion_paquete) )
                            librasDisponible  = librasDisponible + parseFloat(paquete.peso);
                    }else
                        librasDisponible  = librasDisponible + parseFloat(paquete.peso);
                }
            });

            librasSobrantes     = librasDisponible >= complemento.lb_free ?   librasDisponible - complemento.lb_free : 0;
            $peso_descuento     = (pesoTotal() -  librasDisponible) + librasSobrantes;
            peso_total_envio    = $peso_descuento < parseFloat(promocionDetalleSelect.promocionDetalle.lb_requerida)  ? parseFloat(promocionDetalleSelect.promocionDetalle.lb_requerida) : $peso_descuento;

            $('.div_complemento').append( '<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">'+ 'Total de libras disponibles a la promoción ('+ complemento.categoria+ ') ' +  librasDisponible +  ' - ' + complemento.lb_free +'  = ' +  librasSobrantes.toFixed(2)+' </strong></p>' );

            $('.div_complemento').append( '<p id="text_libras_pagar">Total de libras a pagar: <strong id="lbl_libras_pagar">'+ peso_total_envio + ' </strong></p>'  );
        }
        else if ( parseInt(complemento.is_categoria) == 10 && parseInt(complemento.is_producto) == 10) {
            $.each(paquete_array,function(key,paquete){
                if (paquete.producto_id && paquete.producto_id  ==  complemento.producto_id ){
                    if (parseInt(complemento.is_valor_paquete) == 10){
                        $('#lbl_valor_lb').html("<i class='fa fa-check-square-o' aria-hidden='true'></i> menor a" + complemento.valor_paquete_aprox);
                        if (parseInt(complemento.valor_paquete_aprox)  >=  parseInt(paquete.valoracion_paquete) )
                            librasDisponible  = librasDisponible + parseFloat(paquete.peso);
                    }
                    else
                        librasDisponible  = librasDisponible + parseFloat(paquete.peso);
                }
            });

            librasSobrantes     = librasDisponible >= complemento.lb_free ?   librasDisponible - complemento.lb_free : 0;
            $peso_descuento = (pesoTotal() -  librasDisponible) + librasSobrantes;

            peso_total_envio = $peso_descuento < parseFloat(promocionDetalleSelect.promocionDetalle.lb_requerida)  ? parseFloat(promocionDetalleSelect.promocionDetalle.lb_requerida) : $peso_descuento;

            $('.div_complemento').append( '<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">'+ 'Total de libras disponibles a la promoción ('+ complemento.producto+ ') ' +  librasDisponible +  ' - ' + complemento.lb_free +'  = ' +  librasSobrantes.toFixed(2) +' </strong></p>' );



            //peso_total_envio    = parseFloat(promocionDetalle.PromocionDetalle.lb_requerida) + librasSobrantes ;
            $('.div_complemento').append( '<p id="text_libras_pagar">Total de libras a pagar: <strong id="lbl_libras_pagar"> '+ peso_total_envio+'</strong></p>' );

        }else if(parseInt(complemento.is_categoria) == 1 && parseInt(complemento.is_producto) == 1) {
            pesoTotalEnvio = 0;

            if (parseInt(complemento.is_valor_paquete) == 10) {
                $('#lbl_valor_lb').html("<i class='fa fa-check-square-o' aria-hidden='true'></i> menor a" + complemento.valor_paquete_aprox);
                $.each(paquete_array,function(key,paquete){
                    if (parseInt(complemento.valor_paquete_aprox)  >=  parseInt(paquete.valoracion_paquete) ) {
                        pesoTotalEnvio  = pesoTotalEnvio + parseFloat(paquete.peso);
                    }
                });
            }else{
                pesoTotalEnvio = parseFloat($('#peso_total').val());
            }
            if (pesoTotalEnvio > 0) {
                librasDisponible    =  pesoTotalEnvio - promocionDetalleSelect.promocionDetalle.lb_requerida;
                librasSobrantes     = parseInt(librasDisponible) >= parseInt(complemento.lb_free) ?   librasDisponible - complemento.lb_free : 0;
                peso_total_envio    = parseFloat(promocionDetalleSelect.promocionDetalle.lb_requerida) + librasSobrantes ;
                $('.div_complemento').append( '<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento"> '+ 'Total de libras disponibles a la promoción ' + $('#peso_total').val() +  ' - ' + complemento.lb_free  +'  = ' + peso_total_envio.toFixed(2) +'</strong></p>');

            }

            $('.div_complemento').append( '<p id="text_libras_pagar">Total de libras a pagar: <strong id="lbl_libras_pagar">'+peso_total_envio+' </strong></p>' );
        }

        load_promocion_detalle();

    }
    /***************************************************
    * Realiza el descuento cuando el envio es gratis
    ***************************************************/
    var load_promocion_envio_free = function(complemento){
        $('.div_complemento').append('<p>Complemento / Beneficio: <strong id="lbl_complemento">Envio gratis</strong></p>');
        //$('#text_beneficio_complemento').show();
        //$('#lbl_beneficio_complemento').html('');
        //$('#text_libras_pagar').show();
        $('#lbl_valor_lb').html('');
        if (parseInt(complemento.is_categoria) == 10 && parseInt(complemento.is_producto) == 1 ) {
            impuestoDisponible  = 0;
            librasDisponible    = 0;
            totalImpuesto       = 0;
            $.each(paquete_array,function(key,paquete){
                totalImpuesto   =  totalImpuesto +  parseFloat(paquete.impuesto_total);
                if (paquete.categoria_id && paquete.categoria_id  ==  complemento.categoria_id ){
                    count           = complemento.num_producto;
                    if (parseInt(complemento.is_valor_paquete) == 10) {
                        $('#lbl_valor_lb').html("<i class='fa fa-check-square-o' aria-hidden='true'></i> menor a " + complemento.valor_paquete_aprox);
                        if (parseInt(complemento.valor_paquete_aprox)  >=  parseInt(paquete.valoracion_paquete) ) {
                            if (count > 0 ) {
                                impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                                librasDisponible    = librasDisponible + parseFloat(paquete.peso);
                                count = count - 1;
                            }
                        }
                    }else{
                        $('#lbl_valor_lb').html("<i class='fa fa-times' aria-hidden='true'></i>" );
                        if (count > 0 ) {
                            impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                            librasDisponible    = librasDisponible + parseFloat(paquete.peso);
                            count = count - 1;
                        }
                    }
                }
            });

            new_impuestoTotal   =  totalImpuesto - impuestoDisponible;
            peso_total_envio    =  peso_total_envio - librasDisponible;


            if (impuestoDisponible != 0 ) {
                $('.div_complemento').append( '<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">'+ 'N° productos  ('+ complemento.num_producto+ ') / (Total de impuestos)  '+ totalImpuesto   + ' Cantidad de impuesto a descontar: ' + impuestoDisponible + ' = ' + new_impuestoTotal.toFixed(2) + ' USD' +' </strong></p>');

                $('.div_complemento').append('<p id="text_libras_pagar">Total de libras a pagar: <strong id="lbl_libras_pagar"> ' +peso_total_envio +'</strong></p>'  );
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));

            }else{
                $('.div_complemento').append('<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">No se aplico ningun descuento al total del impuesto </strong></p>');
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));
            }

        }
        else if ( parseInt(complemento.is_categoria) == 10 && parseInt(complemento.is_producto) == 10) {
            impuestoDisponible  = 0;
            librasDisponible    = 0;
            totalImpuesto       = 0;
             $.each(paquete_array,function(key,paquete){
                totalImpuesto   =  totalImpuesto +  parseFloat(paquete.impuesto_total);
                if (paquete.producto_id && paquete.producto_id  ==  complemento.producto_id ){
                    count           = complemento.num_producto;
                    if (parseInt(complemento.is_valor_paquete) == 10) {
                        $('#lbl_valor_lb').html("<i class='fa fa-check-square-o' aria-hidden='true'></i> menor a " + complemento.valor_paquete_aprox);
                        if (parseInt(complemento.valor_paquete_aprox)  >=  parseInt(paquete.valoracion_paquete) ) {
                            if (count > 0 ) {
                                impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                                librasDisponible    = librasDisponible + parseFloat(paquete.peso);
                                count = count - 1;
                            }
                        }
                    }else{
                        $('#lbl_valor_lb').html("<i class='fa fa-times' aria-hidden='true'></i>" );
                        if (count > 0 ) {
                            impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                            librasDisponible    = librasDisponible + parseFloat(paquete.peso);
                            count = count - 1;
                        }
                    }
                }
            });

            new_impuestoTotal   =  totalImpuesto - impuestoDisponible;
            peso_total_envio    =  peso_total_envio - librasDisponible;

            if (impuestoDisponible != 0 ) {
                $('.div_complemento').append( '<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">' + 'N° productos  ('+ complemento.num_producto+ ') / (Total de impuestos)  '+ totalImpuesto   + ' Cantidad de impuesto a descontar: ' + impuestoDisponible + ' = ' + new_impuestoTotal.toFixed(2) + ' USD'+ ' </strong></p>');
                $('.div_complemento').append('<p id="text_libras_pagar">Total de libras a pagar: <strong id="lbl_libras_pagar"> ' + peso_total_envio +'</strong></p>' );
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));

            }else{
                $('.div_complemento').append('<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">No se aplico ningun descuento al total del impuesto </strong></p>');
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));
                $('.div_complemento').append( '<p id="text_libras_pagar">Total de libras a pagar: <strong id="lbl_libras_pagar">' + peso_total_envio +' </strong></p>' );
            }

        }

        load_promocion_detalle();
    }
    /******************************************************************
    * Realiza el descuento cuando sin impuesto
    ******************************************************************/
    var load_promocion_sin_impuesto  = function(complemento){
        $('.div_complemento').append('<p>Complemento / Beneficio: <strong id="lbl_complemento">Cobro sin impuesto</strong></p>');
        //$('#text_beneficio_complemento').show();
        //$('#lbl_beneficio_complemento').html('');
        //$('#text_libras_pagar').hide();
        $('#lbl_valor_lb').html('');
        if (parseInt(complemento.is_categoria) == 10 && parseInt(complemento.is_producto) == 1 ) {
            impuestoDisponible  = 0;
            totalImpuesto       = 0;
            count               = complemento.num_producto;
            $.each(paquete_array,function(key,paquete){


                totalImpuesto   =  totalImpuesto +  parseFloat(paquete.impuesto_total);
                if (paquete.categoria_id && paquete.categoria_id  ==  complemento.categoria_id ){
                    if (parseInt(complemento.is_valor_paquete) == 10) {
                        $('#lbl_valor_lb').html("<i class='fa fa-check-square-o' aria-hidden='true'></i> menor a " + complemento.valor_paquete_aprox);
                        if (parseInt(complemento.valor_paquete_aprox)  >=  parseInt(paquete.valoracion_paquete) ) {
                            if (count > 0 && paquete.is_impuesto == is_impuesto_on ) {
                                impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                                count = count - 1;
                            }
                        }
                    }else{
                        $('#lbl_valor_lb').html("<i class='fa fa-times' aria-hidden='true'></i>" );
                        if (count > 0 && paquete.is_impuesto == is_impuesto_on ) {
                            impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                            count = count - 1;
                        }
                    }
                }

            });

            new_impuestoTotal =  totalImpuesto - impuestoDisponible;
            if (impuestoDisponible != 0 ) {
                $('.div_complemento').append( '<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">' + 'N° productos  ('+ complemento.num_producto+ ') / (Total de impuestos)  '+ totalImpuesto   + ' Cantidad de impuesto a descontar: ' + impuestoDisponible + ' = ' + new_impuestoTotal.toFixed(2) + ' USD'+' </strong></p>');
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));

            }else{
                $('.div_complemento').append('<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento"> No se aplico ningun descuento al total del impuesto </strong></p>');
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));
            }

        }
        else if ( parseInt(complemento.is_categoria) == 10 && parseInt(complemento.is_producto) == 10) {
            impuestoDisponible  = 0;
            totalImpuesto       = 0;
            count               = complemento.num_producto;
            $.each(paquete_array,function(key,paquete){
                totalImpuesto   =  totalImpuesto +  parseFloat(paquete.impuesto_total);
                if (paquete.producto_id && paquete.producto_id  ==  complemento.producto_id ){
                    if (parseInt(complemento.is_valor_paquete) == 10) {
                        $('#lbl_valor_lb').html("<i class='fa fa-check-square-o' aria-hidden='true'></i> menor a " + complemento.valor_paquete_aprox);
                        if (parseInt(complemento.valor_paquete_aprox)  >=  parseInt(paquete.valoracion_paquete) ) {
                            if (count > 0  && paquete.is_impuesto == is_impuesto_on ) {
                                impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                                count = count - 1;
                            }
                        }
                    }else{
                        $('#lbl_valor_lb').html("<i class='fa fa-times' aria-hidden='true'></i>" );
                        if (count > 0 && paquete.is_impuesto == is_impuesto_on ) {
                            impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                            count = count - 1;
                        }
                    }
                }
            });

            new_impuestoTotal =  totalImpuesto - impuestoDisponible;

            if (impuestoDisponible != 0 ) {
                $('.div_complemento').append( '<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">' + 'N° productos  ('+ complemento.num_producto+ ') / (Total de impuestos)  '+ totalImpuesto   + ' Cantidad de impuesto a descontar: ' + impuestoDisponible + ' = ' + new_impuestoTotal.toFixed(2) + 'USD' + ' </strong></p>');
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));

            }else{
                $('.div_complemento').append('<p id="text_beneficio_complemento">Detalles del beneficio : <strong id="lbl_beneficio_complemento">No se aplico ningun descuento al total del impuesto </strong></p>');
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));
            }


        }else if(parseInt(complemento.is_categoria) == 1 && parseInt(complemento.is_producto) == 1) {

            impuestoDisponible  = 0;
            totalImpuesto       = 0;
            count               = complemento.num_producto;
            $.each(paquete_array,function(key,paquete){

                totalImpuesto   =  totalImpuesto +  parseFloat(paquete.impuesto_total);
                if (parseInt(complemento.is_valor_paquete) == 10) {
                    $('#lbl_valor_lb').html("<i class='fa fa-check-square-o' aria-hidden='true'></i> menor a " + complemento.valor_paquete_aprox);
                    if (parseInt(complemento.valor_paquete_aprox)  >=  parseInt(paquete.valoracion_paquete) ) {
                        if (count > 0  && paquete.is_impuesto == is_impuesto_on ) {

                            impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                            count = count - 1;

                        }
                    }
                }else{
                    $('#lbl_valor_lb').html("<i class='fa fa-times' aria-hidden='true'></i>" );
                    if (count > 0  && paquete.is_impuesto == is_impuesto_on ) {
                        impuestoDisponible  = impuestoDisponible + parseFloat(paquete.impuesto_total);
                        count = count - 1;
                    }
                }
            });

            /***********************************************************************
            SOLO TOMARA EL 50% DE LA MITAD DEL IMPUESTO OTORGADO PARA PROMOCION ACTUAL
            **********************************************************************/
            //impuestoDisponible = impuestoDisponible / 2 ;
            /******************************************************************************/
            new_impuestoTotal =  totalImpuesto - impuestoDisponible;

            if (impuestoDisponible != 0 ) {
                $('.div_complemento').append( "N° productos  ("+ complemento.num_producto+ ") / (Total de impuestos)  "+ totalImpuesto   + " Cantidad de impuesto a descontar: " + impuestoDisponible + " = " + new_impuestoTotal.toFixed(2) + "USD");
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));
            }else{
                $('.div_complemento').append("No se aplico ningun descuento al total del impuesto");
                $('#impuesto_total_envio').val(new_impuestoTotal.toFixed(2));
            }
        }
        load_promocion_detalle();
    }
});
</script>
