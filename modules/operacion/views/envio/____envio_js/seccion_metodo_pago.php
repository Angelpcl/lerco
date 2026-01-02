<script>
$(function () {
    $btnAgregarMetodoPago.click(function(){

     	if(!$form_metodoPago.$metodoPago.val() || !$form_metodoPago.$cantidad.val()){
            return false;
        }

    	metodo = {
            "metodo_id"    		: metodoPago_array.length + 1,
            "metodo_pago_id"  	: $form_metodoPago.$metodoPago.val(),
            "metodo_pago_text"  : $('option:selected', $form_metodoPago.$metodoPago).text(),
            "cantidad"     		: $form_metodoPago.$cantidad.val(),
            "origen" 			: 1,
        };

        metodoPago_array.push(metodo);

    	calcula_cambio_envio();
        render_metodo_template();

    });



    $('#btnGuardarEnvio').on('click', function(event){
		event.preventDefault();
        bootbox.confirm("¿Estas seguro que deseas finalizar el envío?", function(result) {
            if (result) {
                $.niftyNoty({
                    type: 'success',
                    icon : 'pli-like-2 icon-2x',
                    message : 'Se confirmo la creación del el envío',
                    container : 'floating',
                    timer : 5000
                });
                $('#btnGuardarEnvio').submit();
            }else{
                $.niftyNoty({
                    type: 'danger',
                    icon : 'pli-cross icon-2x',
                    message : 'Se cancelo el envio.',
                    container : 'floating',
                    timer : 5000
                });
            };

        });
    });
});

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
    calcula_cambio_envio();
    render_metodo_template();
}


var calcula_cambio_envio = function(){
    pago_total = 0;
    $.each(metodoPago_array, function(key, metodo){
        if (metodo.metodo_id)
            pago_total = pago_total + parseFloat(metodo.cantidad);
    });

    new_cambio_metodo = pago_total - parseFloat($('#envio-total').val());


    if (metodoPago_array[0]){
        val_cambio_round = new_cambio_metodo < 0 ?  metodoPago_array[metodoPago_array.length - 1 ].cantidad : (metodoPago_array[metodoPago_array.length - 1 ].cantidad - new_cambio_metodo) < 0 ? 0 : (metodoPago_array[metodoPago_array.length - 1 ].cantidad - new_cambio_metodo);

        metodoPago_array[metodoPago_array.length - 1 ].cantidad = parseFloat(val_cambio_round).toFixed(2);
    }

    $('#cambio_metodo').html( new_cambio_metodo < 0 ? 0 : "$ " +new_cambio_metodo.toFixed(2) );
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

            pago_total = pago_total + parseFloat(metodo.cantidad);

            if (metodo.origen != 2) {
                $tr.append("<button type='button' class='btn btn-warning btn-circle' onclick='refresh_metodo(this)'><i class='fa fa-trash'></i></button>");
            }
        }
    });

    $('#total_metodo').html("$ " + $('#envio-total').val());

    balance_total = parseFloat($('#envio-total').val() - pago_total.toFixed(2));

    $('#balance_total').html("$ " + balance_total.toFixed(2));
    $('#pago_metodo_total').html("$ "+ pago_total.toFixed(2));

    $inputcobroRembolsoEnvioArray.val(JSON.stringify(metodoPago_array));
}
</script>
