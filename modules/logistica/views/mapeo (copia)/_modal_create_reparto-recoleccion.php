<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Esys;
 ?>
<div class="fade modal " id="modal-create-recoleccion"  tabindex="-1" role="dialog" aria-labelledby="modal-create-recoleccion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Recoleccion para la ruta <strong><span  class="label monto text-dark "> <?= Esys::fecha_en_texto($model->fecha_salida) ?> </span> </h4> </strong>
            </div>
            <!--Modal body-->
            <?php $form = ActiveForm::begin(['id' => 'form-recoleccion' ]) ?>
            <?= $form->field($model->recoleccion_ruta, 'reparto_recoleccion_array')->hiddenInput()->label(false) ?>

            <div class="modal-body">
            	<div class="panel">
                    <div class="panel-body historial-cambios nano">
                       <div class="nano-content">
                    	  	<div class="row">
                            	<div class="col-sm-12">
	                            	<div class="table-responsive">
		                                <table class="table table-striped">
		                                    <thead>
		                                        <tr>
		                                            <th style="text-align: center;">Sucursal</th>
		                                            <th style="text-align: center;">Encargado</th>
		                                            <th style="text-align: center;">Telefonos</th>
		                                            <th style="text-align: center;">Recolección</th>
		                                            <th style="text-align: center;">Cantidad de paquetes (Promedio)</th>
		                                        </tr>
		                                    </thead>
		                                    <tbody class="table_recoleccion_ruta" style="text-align: center;">

		                                    </tbody>
		                                </table>
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
                <?= Html::submitButton('Guardar', ['class' =>  'btn btn-primary', 'id' => 'form-recoleccion']) ?>
            </div>
			<?php ActiveForm::end(); ?>

        </div>
    </div>
</div>


 <div class="display-none">
     <table>

        <tbody class="template_recoleccion_ruta">
            <tr id = "recoleccion_sucursal_id_{{sucursal_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-recoleccion-sucursal"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-recoleccion-encargado"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-recoleccion-telefono"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-recoleccion-seleccion"]) ?></td>

            </tr>
        </tbody>
    </table>
</div>

<script>
$table_recoleccion_ruta    	= $('.table_recoleccion_ruta')
$template_recoleccion_ruta 	= $('.template_recoleccion_ruta'),
$inputRecoleccionArray   			= $('#repartorecoleccion-reparto_recoleccion_array');
recoleccion_ruta_array 	    =  [];
rutaID 						= 0;
repartoID				    = 0;

var init_recoleccion_sucursal = function()
{
	$table_recoleccion_ruta.html("");
	recoleccion_ruta_array = [];
	$.get("<?= Url::to(['recoleccion-ruta-ajax']) ?>",{ ruta_id :  rutaID },function(json){
		recoleccion_ruta_array = json;
		render_recoleccion_template();
	},'json');
};

var render_recoleccion_template = function(){
	$.each(recoleccion_ruta_array,function(key,item){
        if (item.id) {
			template_recoleccion_ruta = $template_recoleccion_ruta.html();
			template_recoleccion_ruta = template_recoleccion_ruta.replace("{{sucursal_id}}",item.id);
			$table_recoleccion_ruta.append(template_recoleccion_ruta);
			$tr        =  $("#recoleccion_sucursal_id_" + item.id,$table_recoleccion_ruta);
			$tr.attr("data-sucursal_id",item.id);
			$("#table-recoleccion-sucursal",$tr).html(item.nombre);
			$("#table-recoleccion-encargado",$tr).html(item.nombre_completo);
			$("#table-recoleccion-telefono",$tr).html(item.telefono +" / "+ item.telefono_movil);
			item["reparto_id"] = repartoID;
			item["ruta_id"]    = rutaID;
			if (item["recoleccion_id"]) {
				item["is_recoleccion"] = 1;
				$("#table-recoleccion-seleccion",$tr).html('<input type="checkbox" onchange="refresh_sucursal_recoleccion_change(this)" checked>' );
				$tr.append("<input type='number' class='form-control'  value = '"+ item.cantidad_paquetes +"' id = 'cantidad_paquetes_" + item.id + "'  onchange = 'refresh_cantidad_recoleccion_change(this)' />");
			}else{
				item["is_recoleccion"] = 0;
				$("#table-recoleccion-seleccion",$tr).html('<input type="checkbox" onchange="refresh_sucursal_recoleccion_change(this)"  >' );
				$tr.append("<input type='number' class='form-control'   id = 'cantidad_paquetes_" + item.id + "' onchange = 'refresh_cantidad_recoleccion_change(this)'  disabled />");
			}
        }

	});

	$inputRecoleccionArray.val(JSON.stringify(recoleccion_ruta_array));
}

var  refresh_sucursal_recoleccion_change = function(ele){
	$ele_paquete_val    = $(ele);
	$ele_sucursal        = $(ele).closest('tr');
	$ele_sucursal_id  	= $ele_sucursal.attr("data-sucursal_id");
	if ($ele_paquete_val.prop('checked')) {
		$('input[type = "number"]',$ele_sucursal).prop('disabled',false);
		$.each(recoleccion_ruta_array,function(key,sucursal){
			if (sucursal.id) {
				if (sucursal.id == $ele_sucursal_id) {
					sucursal.is_recoleccion = 1;
				}
			}
		});
	}else{
		$('input[type = "number"]',$ele_sucursal).prop('disabled',true);
		$('input[type = "number"]',$ele_sucursal).val(0).trigger('change');
		$.each(recoleccion_ruta_array,function(key,sucursal){
			if (sucursal.id) {
				if (sucursal.id == $ele_sucursal_id) {
					sucursal.is_recoleccion = 0;
				}
			}
		});
	}
	$inputRecoleccionArray.val(JSON.stringify(recoleccion_ruta_array));
}

var refresh_cantidad_recoleccion_change = function(ele){
	$ele_paquete_val    = $(ele);
	$ele_sucursal        = $(ele).closest('tr');
	$ele_sucursal_id  	= $ele_sucursal.attr("data-sucursal_id");
	$.each(recoleccion_ruta_array,function(key,sucursal){
		if (sucursal.id) {
			if (sucursal.id == $ele_sucursal_id) {
				sucursal.cantidad_paquetes = $ele_paquete_val.val();
			}
		}
	});
	$inputRecoleccionArray.val(JSON.stringify(recoleccion_ruta_array));
}

var init_ruta_recoleccion = function (reparto_id,ruta_id){
	rutaID 		= ruta_id;
	repartoID 	= reparto_id;
	ruta_select_array = [];
	init_recoleccion_sucursal();
}


</script>
