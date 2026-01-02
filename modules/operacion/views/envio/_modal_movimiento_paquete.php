<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="fade modal inmodal " id="modal-show-movimiento"  tabindex="-1" role="dialog" aria-labelledby="modal-show-label" style="width: 100%">
    <div class="modal-dialog modal-lg" style="width: 90%;height: 100%;max-width: 100%;">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-cubes mar-rgt-5px icon-lg"></i> Movimientos realizados del paquete </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row contente_paquete">
                </div>
			</div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="display-none">
	<div class="template_movimiento" id="movimiento_id_{{movimiento_id}}">
		<div class="vertical-timeline-block">
	        <div class="vertical-timeline-icon navy-bg">
	            <i class="fa {{icon_cubes}} icon-2x"></i>
	        </div>
	        <div class="vertical-timeline-content">
	            <h5><a href="#" class="{{text_alert}}">{{movimiento_name}} <small><strong></strong>{{trackend}}</small></a></h5>
	            <small style="font-size: 10px">{{movimiento_descripcion}}</small>
                {{link}}
                <span class="vertical-date">
                    Fecha <br>
                    <small>{{time_movimiento}}</small>
                </span>
	        </div>
	    </div>
	</div>
</div>

<div class="display-none">
	<div class="template_paquete" >
        <div class="col-sm-4">
        	<div class="" id="paquete_id_{{movimiento_paquete_id}}">
	            <!-- Timeline header -->
	            <div class="timeline-header">
	                <div class="timeline-header-title">PAQUETE</div>
	            </div>
	            <div id="vertical-timeline"  class="vertical-container dark-timeline contente_movimiento_paquete_{{content_paquete_id}}">
	            </div>
	        </div>
        </div>
	</div>
</div>

<script>

	var icons = {
		1 : "fa fa-close",
        2 : "fa fa-file-text-o",
        10: "fa fa-cube",
     	20: "fa fa-plane",
     	30: "fa fa-road",
     	40: "fa fa-truck",
     	45: "fa fa-truck",
     	50: "fa fa-truck",
     	55: "fa fa-truck",
     	60: "fa-flag-checkered",
	},
 	text_message = {
		1 : "text-danger",
        2 : "text-mint",
        10: "text-warning",
     	20: "text-dark",
     	30: "text-primary",
     	40: "text-purple",
     	45: "text-purple",
     	50: "text-purple",
     	55: "text-purple",
     	60: "text-mint",
	},
	movimiento_text =  {
		1 : 'Cancelado',
        2 : 'Documentado',
        10: 'Sucursal (USA)',
     	20: 'Transcurso (MX)',
     	30: 'Bodega (MX)',
     	40: 'Reparto',
     	45: 'Entrega programada',
     	50: 'Reenvio (Entrega a domicilio) ',
     	55: 'Entregado por : (Paqueteria)',
     	60: 'Entregado',
	},
	movimiento_descrip_text =  {
		1 : 'El paquete se encuentra CANCELADO',
        2 : 'El paquete se encuentra DOCUMENTADO',
        10: 'El paquete se encuentra en la sucursal de origen',
     	20: 'El paquete se encuentra en transcurso a MX',
     	30: 'El paquete se encuentra en bodega MX',
     	40: 'El paquete se encuentra en reparto',
     	45: 'El paquete se encuentra en Entrega programada',
     	50: 'El paquete se enuentra en Reenvio (Entrega a domicilio) ',
     	55: 'El paquete se encuentra en reparto por Paqueteria externa',
     	60: 'El paquete ya fue Entregado',
	},
	//$contente_movimiento_paquete 	= $('.contente_movimiento_paquete'),
	$contente_paquete 				= $('.contente_paquete'),
	$template_movimiento 	= $('.template_movimiento'),
	$template_paquete 		= $('.template_paquete'),
	$movimiento_array 		= [];

 	var init_reenvio = function($paquete_id){
        $text_direccion.html('');
        $.get("<?= Url::to(['show-movimiento-paquete']) ?>",{ paquete_id : $paquete_id },function(movimientoJson){
            if (movimientoJson.code == 202) {
                $movimiento_array = movimientoJson.data;
                render_metodo_template();
            }

        },'json');
    }


/*====================================================
*               RENDERIZA MOVIMIENTOS
*====================================================*/
var render_metodo_template = function(){
	$contente_paquete.html("");
	$.each($movimiento_array, function(key, movimiento){
		if (movimiento) {

			template_paquete = $template_paquete.html();
            template_paquete = template_paquete.replace("{{movimiento_paquete_id}}",(key + 1));
            template_paquete = template_paquete.replace("{{content_paquete_id}}",(key + 1));


            $contente_paquete.append(template_paquete);
           	$.each(movimiento,function(key2, paquete_movimiento){
				if (paquete_movimiento) {
					template_movimiento = $template_movimiento.html();
            		template_movimiento = template_movimiento.replace("{{movimiento_id}}",(key2 + 1));
            		template_movimiento = template_movimiento.replace("{{time_movimiento}}",btf.time.date(paquete_movimiento.created_at) );
            		template_movimiento = template_movimiento.replace("{{trackend}}",paquete_movimiento.tracked);
            		template_movimiento = template_movimiento.replace("{{movimiento_name}}",movimiento_text[paquete_movimiento.tipo_movimiento]);
            		template_movimiento = template_movimiento.replace("{{movimiento_descripcion}}",movimiento_descrip_text[paquete_movimiento.tipo_movimiento] + ( paquete_movimiento.tipo_movimiento == 30 ? "<strong> Peso Mx: "+ paquete_movimiento.peso_mx +" lbs</strong>" : '') + (paquete_movimiento.tipo_movimiento == 10 ? "<strong> Peso Usa: "+ paquete_movimiento.peso_usa +" lbs</strong>" : '' ) + (paquete_movimiento.tipo_movimiento == 45 ? "<strong> FECHA PROGRAMADA PARA EL: "+ ( new Date(paquete_movimiento.fecha_entrega * 1000).format("Y-m-d") ) +" </strong>" : '' ));

                    if (paquete_movimiento.tipo_movimiento == 20 || paquete_movimiento.tipo_movimiento == 40) {
                        if (paquete_movimiento.tipo_movimiento == 20)
                            template_movimiento = template_movimiento.replace("{{link}}","<a href='"+ "<?= Url::to(['/logistica/viaje-tierra/view?id=']) ?>"+ paquete_movimiento.viaje_id +"' class='btn btn-sm btn-primary'>Ver más</a>");
                        if (paquete_movimiento.tipo_movimiento == 40)
                            template_movimiento = template_movimiento.replace("{{link}}","<a href='"+ "<?= Url::to(['/logistica/reparto/view?id=']) ?>"+ paquete_movimiento.reparto_id +"' class='text-main text-semibold'>Ver más</a>");

                    }else
                        template_movimiento = template_movimiento.replace("{{link}}","");

            		template_movimiento = template_movimiento.replace("{{icon_cubes}}",icons[paquete_movimiento.tipo_movimiento]);
            		template_movimiento = template_movimiento.replace("{{text_alert}}",text_message[paquete_movimiento.tipo_movimiento]);
            		//$("#paquete_id_" + (key + 1), $content_metodo_pago);
 					$(".contente_movimiento_paquete_" + (key + 1), $contente_paquete).append(template_movimiento);
				}
           });
		}
	});

}
</script>
