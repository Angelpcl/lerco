<script>
$(function () {
    /*====================================================
    *               AGREGA UN ITEM A ARRAY
    *====================================================*/
    $btnAddRenvio.click(function(){
        if(validation_form_reenvio()){
            return false;
        }
    	reenvio = {
    		"reenvio_id" 	: renvio_array.length + 1,
    		"cp" 			: $form_esysdireccion_envio.$inputCodigoSearch.val(),
    		"estado_id" 	: $form_esysdireccion_envio.$inputEstado.val(),
    		"estado_text" 	: $('option:selected', $form_esysdireccion_envio.$inputEstado ).text(),
    		"municipio_id" 	: $form_esysdireccion_envio.$inputMunicipio.val(),
    		"municipio_text": $('option:selected', $form_esysdireccion_envio.$inputMunicipio ).text(),
    		"colonia_id" 	: $form_esysdireccion_envio.$inputColonia.val(),
    		"colonia_text"	: $('option:selected', $form_esysdireccion_envio.$inputColonia ).text(),
    		"direccion" 	: $form_esysdireccion_envio.$inputDireccion.val(),
    		"n_interior" 	: $form_esysdireccion_envio.$inputNumeroInt.val(),
    		"n_exterior" 	: $form_esysdireccion_envio.$inputNumeroExt.val(),
    		"referencia" 	: $form_esysdireccion_envio.$inputReferencia.val(),
    		"status"                : 10,
            "update"                : $envioID.val() ? 10 : 1,
            "origen"                : 1

    	}

    	renvio_array.push(reenvio);

    	render_reenvio_template();
        render_paquete_template();
        clear_form($form_esysdireccion_envio);



    	$form_esysdireccion_envio.$inputEstado.val(null).trigger('change');
    	$form_esysdireccion_envio.$inputColonia.html(false);
    });


    var validation_form_reenvio = function()
    {

        $error_add_reenvio.html('');
        switch(true){
            case !$form_esysdireccion_envio.$inputEstado.val() :
                $error_add_reenvio.append('<div class="help-block">* Selecciona un estado</div>');
                $error_add_reenvio.show();
                return true;
            break;
            case !$form_esysdireccion_envio.$inputMunicipio.val() :
                $error_add_reenvio.append('<div class="help-block">* Selecciona un municipio</div>');
                $error_add_reenvio.show();
                return true;
            break;
            case !$form_esysdireccion_envio.$inputColonia.val() :
                $error_add_reenvio.append('<div class="help-block">* Selecciona una colonia</div>');
                $error_add_reenvio.show();
                return true;
            break;

            case !$form_esysdireccion_envio.$inputDireccion.val() :
                $error_add_reenvio.append('<div class="help-block">* Ingresa una direccion</div>');
                $error_add_reenvio.show();
                return true;
            break;
        }
    }

});

 var render_reenvio_template = function(){
        $content_reenvio.html("");
        load_reenvio_select();
        $.each(renvio_array, function(key, reenvio){
            if (reenvio.reenvio_id) {
                template_reenvio = $template_reenvio.html();
                template_reenvio = template_reenvio.replace("{{reenvio_id}}",reenvio.reenvio_id);

                $content_reenvio.append(template_reenvio);
                $tr        =  $("#reenvio_id_" + reenvio.reenvio_id, $content_reenvio);
                $tr.attr("data-reenvio_id",reenvio.reenvio_id);
                $tr.attr("data-origen",reenvio.origen);

                $("#table_cp",$tr).html(reenvio.cp);
                $("#table_estado",$tr).html(reenvio.estado_text);
                $("#table_municipio",$tr).html(reenvio.municipio_text);
                $("#table_colonia",$tr).html(reenvio.colonia_text);
                $("#table_direccion",$tr).html(reenvio.direccion);
                $("#table_n_interior",$tr).html(reenvio.n_interior);
                $("#table_n_exterior",$tr).html(reenvio.n_exterior);
                $("#table_referencia",$tr).html(reenvio.referencia);

                $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_envio(this)'><i class='fa fa-trash'></i></button></td>");
            }
        });

        $dir_obj_array.val(JSON.stringify(renvio_array));
    }

var load_reenvio_select = function(){
    $reenvio_select_id.html('');
    $reenvio_select_id.show();
    $.each(renvio_array, function(key, value){
        $reenvio_select_id.append("<option value='" + value.reenvio_id + "'> Estado:  " + (value.estado_id ? value.estado_text : 'N/A') + ", Municipio: " + (value.municipio_id ? value.municipio_text : 'N/A') +", Colonia: "+ (value.colonia_id  ? value.colonia_text : 'N/A') + "</option>\n");
    });

	//$reenvio_select_id.append("<option value='0'>Selecciona dirección</option>");
	$("#reenvio_select_id option[value=0]").attr('selected', 'selected');
}

var refresh_envio = function(ele){
   $ele_paquete_val = $(ele).closest('tr');

    $ele_reenvio_id  = $ele_paquete_val.attr("data-reenvio_id");
    $ele_origen_id   = $ele_paquete_val.attr("data-origen");

    $.each(renvio_array, function(key, reenvio){
        if (reenvio) {
            if (reenvio.reenvio_id == $ele_reenvio_id && reenvio.origen == $ele_origen_id ) {
                renvio_array.splice(key, 1 );
            }
        }
    });
    /***********************************************
        ELIMINACION DE ID DE REENVIO EN PAQUETE
    ***********************************************/
    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id) {
            if (paquete.reenvio_id == $ele_reenvio_id) {
                paquete.reenvio_id = null;
            }
        }
    });

    $dir_obj_array.val(JSON.stringify(renvio_array));

    render_paquete_template();
    $(ele).closest('tr').remove();
    //$inputEnvioDetalleArray.val(JSON.stringify(renvio_array));
    render_reenvio_template();


};
</script>
