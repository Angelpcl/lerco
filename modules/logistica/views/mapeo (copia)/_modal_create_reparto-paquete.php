<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Esys;
 ?>
<div class="fade modal " id="modal-create-paquete"  tabindex="-1" role="dialog" aria-labelledby="modal-create-paquete-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Asignación de paquetes a fila <strong><span  class="label monto text-dark "> <?= Esys::fecha_en_texto($model->fecha_salida) ?> </span> </h4> </strong>
            </div>
            <!--Modal body-->
            <?php $form = ActiveForm::begin(['id' => 'form-paquete' ]) ?>
            <?= $form->field($model->fila_paquete, 'reparto_paquete_array')->hiddenInput()->label(false) ?>
            <?= $form->field($model->fila_paquete, 'fila_ruta_id')->hiddenInput()->label(false) ?>

            <div class="modal-body">
            	<div class="panel">
                    <div class="panel-body historial-cambios nano">
                       <div class="nano-content">
                    	  	<div class="row">
                            	<div class="col-sm-12">
	                            	<div class="table-responsive">
	                            		<div class="table_reparto_paquete"></div>
	                            	</div>
	                            </div>
                            </div>
                        </div>
		            </div>
		        </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Guardar', ['class' =>  'btn btn-primary', 'id' => 'form-paquete']) ?>
            </div>
			<?php ActiveForm::end(); ?>

        </div>
    </div>
</div>


 <div class="display-none">
    <div class="template_reparto_paquete">
		<h3 class="title title-sucursal-id_{{sucursal_title_id}}"></h3>
    	<table class="table table-striped">
            <thead>
                <tr>
                    <th style="text-align: center;">Folio</th>
                    <th style="text-align: center;">Piezas</th>
                    <th style="text-align: center;">FILA</th>
                    <th style="text-align: center;">Agregar a fila</th>
                </tr>
            </thead>
	       <tbody  class="table_paquete_{{sucursal_id}}" style="text-align: center;">
	        </tbody>
		</table>
    </div>
</div>


 <div class="display-none">
     <table>
        <tbody class="template_paquete">
            <tr id = "paquete_sucursal_id_{{paquete_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-paquete-folio"]) ?></td>
		        <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-paquete-pieza"]) ?></td>
		        <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-paquete-fila"]) ?></td>
	            <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-paquete-seleccion"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
$table_reparto_paquete    	= $('.table_reparto_paquete')
$template_reparto_paquete 	= $('.template_reparto_paquete'),
$template_paquete 			= $('.template_paquete'),
$inputRepartoPaqueteArray 	= $('#filapaquete-reparto_paquete_array');
$inputFila_ruta_id 			= $('#filapaquete-fila_ruta_id');
recoleccion_ruta_array 	    =  [];
paquete_fila_array 	    	=  [];
paquetes_array 	    		=  [];
fila_rutaID 				= 0;
rutaID 						= 0;

var init_sucursal_paquete = function()
{
	$table_reparto_paquete.html("");
	recoleccion_ruta_array = [];
	paquetes_array 		   = [];
	$.get("<?= Url::to(['recoleccion-ruta-ajax']) ?>",{ ruta_id :  rutaID },function(json){
		recoleccion_ruta_array = json;
		/*$.get("<?= Url::to(['fila-paquete-ajax']) ?>",{ fila_ruta_id :  fila_rutaID },function(json){
			recoleccion_ruta_array = json;
		},'json');*/
		render_fila_paquete_template();
	},'json');
};

var render_fila_paquete_template = function(){
	$.each(recoleccion_ruta_array,function(key,item){
        if (item.id) {
			template_reparto_paquete = $template_reparto_paquete.html();

			template_reparto_paquete = template_reparto_paquete.replace("{{sucursal_title_id}}",item.id);
			template_reparto_paquete = template_reparto_paquete.replace("{{sucursal_id}}",item.id);

			$table_reparto_paquete.append(template_reparto_paquete);

			$('.title-sucursal-id_'+item.id,$table_reparto_paquete).html(item.nombre);

			$.get("<?= Url::to(['fila-paquete-ajax']) ?>",{ sucursal_receptor_id :  item.id, fila_ruta_id: fila_rutaID },function(paquete_json){

				$.each(paquete_json,function(key,paquete){
					if (paquete.id) {
						template_paquete = $template_paquete.html();
						template_paquete = template_paquete.replace("{{paquete_id}}",paquete.id);
						$('.table_paquete_'+item.id).append(template_paquete);


						$tr  =  $("#paquete_sucursal_id_" + paquete.id,$('.table_paquete_'+item.id));

						$tr.attr("data-paquete_id",paquete.id);


						$("#table-paquete-folio",$tr).html(paquete.tracked);
						$("#table-paquete-pieza",$tr).html(paquete.cantidad_piezas);
						$("#table-paquete-fila",$tr).html(paquete.nombre_fila);

						if (paquete.is_fila == 10) {
							if (paquete.fila_ruta_id == fila_rutaID)
								$("#table-paquete-seleccion",$tr).html('<input type="checkbox" onchange="refresh_fila_paquete_change(this)" checked>' );
							else
								$("#table-paquete-seleccion",$tr).html('<input type="checkbox" onchange="refresh_fila_paquete_change(this)" disabled="true" checked>' );


							paquete_fila = {
								paquete_id 	: paquete.id,
								tracked 	: paquete.tracked,
								fila_ruta_id : fila_rutaID,
							};
							paquete_fila_array.push(paquete_fila);
							$inputRepartoPaqueteArray.val(JSON.stringify(paquete_fila_array));
						}else{
							$("#table-paquete-seleccion",$tr).html('<input type="checkbox" onchange="refresh_fila_paquete_change(this)"  >' );
						}
						paquetes_array.push(paquete);
					}
				});

			},'json');

        }

	});


}

var  refresh_fila_paquete_change = function(ele){
	$ele_paquete_val    = $(ele);
	$ele_paquete        = $(ele).closest('tr');
	$ele_paquete_id  	= $ele_paquete.attr("data-paquete_id");

	if ($ele_paquete_val.prop('checked')) {
		$.each(paquetes_array,function(key,paquete){
			if (paquete.id) {
				if (paquete.id == $ele_paquete_id) {
					paquete_fila = {
						paquete_id 	: paquete.id,
						tracked 	: paquete.tracked,
						fila_ruta_id : fila_rutaID,
					};
					paquete_fila_array.push(paquete_fila);
				}
			}
		});
	}else{

		$.each(paquete_fila_array,function(key2,paquete){
			if (paquete) {
				if (paquete.paquete_id === $ele_paquete_id) {
					paquete_fila_array.splice(key2,1);
				}
			}
		});
	}
	$inputRepartoPaqueteArray.val(JSON.stringify(paquete_fila_array));
}

var init_fila_paquete = function (fila_ruta_id, ruta_id){
	fila_rutaID = fila_ruta_id;
	rutaID 		= ruta_id;
	ruta_select_array = [];
	$inputFila_ruta_id.val(fila_ruta_id);
	init_sucursal_paquete();
}


</script>
