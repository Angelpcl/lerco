<?php
use yii\helpers\Url;
use app\models\sucursal\Sucursal;
 ?>
<script>


/*====================================================
*               Sucursales Asiganas
*====================================================*/

$sucursal_emisor_id.change(function(){
   load_sucursal_emisor();
});

/*====================================================
*    Cargamos los datos de la sucursal seleccionada
*====================================================*/
$sucursal_receptor_id.change(function(){
    $(".content_info_sucursales").html('');
    sucursalSelect = [];
    if ($(this).val().length > 0 ) {
        for (var i = 0; i < $(this).val().length; i++) {
            $.get('<?= Url::to(['sucursal-info-ajax']) ?>', { q  : $(this).val()[i] },function(json){
                if (json) {
                    tamplate_info_sucursal  = $tamplate_info_sucursal.html();
                    tamplate_info_sucursal  = tamplate_info_sucursal.replace('{{sucursal_info_id}}', json.id);
                    $(".content_info_sucursales").append(tamplate_info_sucursal);

                    $div        =  $("#sucursal_info_id_" + json.id, $(".content_info_sucursales"));
                    $('#nombre_sucursal_receptor',$div).html(json.nombre);
                    $('#encargado_sucursal_receptor',$div).html(json.encargado);
                    $('#direccion_sucursal_receptor',$div).html(json.direccion);
                    $('#telefono_sucursal_receptor',$div).html(json.telefono);
                    sucursalSelect.push(json);
                    load_sucursal_emisor_paquete();

                }
            },'json');
        }
    }
});

var load_sucursal_emisor_paquete = function(){
    $paquete_sucursal_id.html('');
    $.each(sucursalSelect, function(key, value){
        $paquete_sucursal_id.append("<option value='" + value.id + "'>" + value.nombre + " ["+ value.clave+"]</option>\n");
    });
    $paquete_sucursal_id.trigger('change');
    temp_is_reenvio = false;
    $.each(sucursalSelect, function(key,item){
        if (item.is_reenvio == 10 ){
           temp_is_reenvio = true;
        }
    });

    if (temp_is_reenvio) {
        $btnAplicaReenvio.show();
        $table_content_reenvio.show();
    }else{
        $btnAplicaReenvio.hide();
        $table_content_reenvio.hide();
    }
}

$sucursal_receptor_id.change(function(){
   $(this).val().length ? $('.next', $content_tab).show() :   $('.next', $content_tab).hide();
});

$paquete_sucursal_id.change(function(){
    $.each(sucursalSelect, function(key,item){
        if ($paquete_sucursal_id.val() == item.id) {
            if (item.is_reenvio == 10 )
                $reenvio_select_id.show();
            else
                $reenvio_select_id.hide();
        }
    });
    $reenvio_select_id.val(false);
});

/*===============================================
* Carga información de la sucursal emisor
*===============================================*/

var load_sucursal_emisor= function(){
    $.get('<?= Url::to(['sucursal-info-ajax']) ?>', { q  : $sucursal_emisor_id.val() },function(json){
        if (json) {
            $('#encargado_sucursal_emisor').html(json.encargado);
            $('#direccion_sucursal_emisor').html(json.direccion);
            $('#telefono_sucursal_emisor').html(json.telefono);
            $('.alert-tipo_sucursal').show();
            $('#tipo_sucursal').html(sucursal_tipo[json.tipo]);
            if(json.tipo ==  <?=  Sucursal::TIPO_ASOCIADA ?>){
                $('.form_emisor').hide();
                $('.form_receptor').removeClass('col-sm-6').addClass('col-sm-12');
            }else{
                $('.form_emisor').show();
                $('.form_receptor').removeClass('col-sm-12').addClass('col-sm-6');
            }
        }
    },'json');
}

/*===============================================
* Muestra la seccion de reenvio y adapta el diseño
*===============================================*/

$btnAplicaReenvio.click(function(){
    if ($isAplicaReenvio.val() == 10){
        confirm_message = confirm("Se eliminara las direcciones de reenvío capturadas, ¿ Deseas quitar el reenvio? ");

        if (confirm_message == true) {
            //$div_num_reenvio.hide();
            $form_reenvio_content.hide();
            $div_seccion_reenvio.hide();
            $isAplicaReenvio.val(null);
            $div_peso_reenvio.hide();
            renvio_array = [];
            /***********************************************
                ELIMINACION DE IDS DE REENVIO EN PAQUETES
            ***********************************************/
            $.each(paquete_array, function(key, paquete){
                if (paquete.paquete_id) {
                    paquete.reenvio_id = null;
                }
            });

            render_reenvio_template();
            render_paquete_template();

            $cliente_receptor.val().length ? $('.next', $content_tab).show() :   $('.next', $content_tab).hide();
        }

        /*if($('.form_emisor').is(':visible')){
            $('.form_emisor').removeClass('col-sm-4').addClass('col-sm-6');
            $('.form_receptor').removeClass('col-sm-4').addClass('col-sm-6');
            $form_reenvio_content.removeClass('col-sm-4');
        }else{
            $form_reenvio_content.removeClass('col-sm-6');
            $('.form_receptor').removeClass('col-sm-6').addClass('col-sm-12');
        }*/
    }
    else{
        $div_peso_reenvio.show();
        //$div_num_reenvio.show();
        $form_reenvio_content.show();
        $div_seccion_reenvio.show();
        $isAplicaReenvio.val(10);

        $('.next', $content_tab).hide();

        /*if($('.form_emisor').is(':visible')){
            $('.form_emisor').removeClass('col-sm-6').addClass('col-sm-4');
            $('.form_receptor').removeClass('col-sm-6').addClass('col-sm-4');
            $form_reenvio_content.addClass('col-sm-4');
        }else{
            $form_reenvio_content.addClass('col-sm-6');
            $('.form_receptor').removeClass('col-sm-12').addClass('col-sm-6');
        }*/
    }
});

</script>
