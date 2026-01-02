<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Esys;
 ?>
<div class="fade modal " id="modal-create-reparto"  tabindex="-1" role="dialog" aria-labelledby="modal-create-reparto-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Asignar ruta a Fila <strong><span  class="label monto text-dark "> <?= Esys::fecha_en_texto($model->fecha_salida) ?> </span> </h4> </strong>
            </div>
            <!--Modal body-->
            <?php $form = ActiveForm::begin(['id' => 'form-reparto' ]) ?>
            <?= $form->field($model->fila_ruta, 'ruta_fila_array')->hiddenInput()->label(false) ?>

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
		                                            <th style="text-align: center;">Ruta</th>
		                                            <th style="text-align: center;">Tipo de ruta</th>
		                                            <th style="text-align: center;">Seleccionar</th>
		                                        </tr>
		                                    </thead>
		                                    <tbody class="table_ruta" style="text-align: center;">
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
                <?= Html::submitButton('Agregar', ['class' =>  'btn btn-primary', 'id' => 'form-reparto']) ?>
            </div>
			<?php ActiveForm::end(); ?>

        </div>
    </div>
</div>


 <div class="display-none">
     <table>

        <tbody class="template_ruta">
            <tr id = "sucursal_id_{{sucursal_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-sucursal"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-encargado"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-seleccion"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
$table_ruta 			= $('.table_ruta')
$template_ruta			= $('.template_ruta'),
$inputRutaArray   		= $('#filaruta-ruta_fila_array');
reparto_ruta_array 		=  [];
ruta_select_array 		=  [];
repartoID 				= 0;
filaID 					= 0;
var init_complemento = function()
{
	$table_ruta.html("");
	reparto_ruta_array = [];
	$.get("<?= Url::to(['reparto-ruta-asignar-ajax']) ?>",{ reparto_id :  repartoID },function(json){
		reparto_ruta_array = json;
		render_ruta_template();
	},'json');
};

var render_ruta_template = function(){
	$.each(reparto_ruta_array,function(key,item){
        if (item.id) {
			template_ruta = $template_ruta.html();
			template_ruta = template_ruta.replace("{{sucursal_id}}",item.id);
			$table_ruta.append(template_ruta);
			$tr        =  $("#sucursal_id_" + item.id,$table_ruta);
			$tr.attr("data-sucursal_id",item.id);
			$("#table-sucursal",$tr).html(item.nombre);
			$("#table-encargado",$tr).html(btf.tipo.tipo_ruta(item.tipo));
			$("#table-seleccion",$tr).html('<input type="checkbox" onchange="refresh_paquete_change(this)" >' );
        }
	});
}

var refresh_paquete_change = function(ele){
	$ele_paquete_val    = $(ele);
	$ele_sucursal       = $(ele).closest('tr');
	$ele_sucursal_id  	= $ele_sucursal.attr("data-sucursal_id");
	if ($ele_paquete_val.prop('checked')) {
		$.each(reparto_ruta_array, function(key, sucursal){
			if (sucursal.id == $ele_sucursal_id) {
				sucursal["fila_id"] = filaID;
    			ruta_select_array.push(sucursal);
			}
		});
	}else{
		$.each(ruta_select_array, function(key, ruta_remove)
		{
			if (ruta_remove) {
				if (ruta_remove.id == $ele_sucursal_id) {
					ruta_select_array.splice(key,1 );
				}
			}
		});
	}
	$inputRutaArray.val(JSON.stringify(ruta_select_array));
}

var init_asignacion_ruta_fila = function (reparto_id,fila_id){
	repartoID = reparto_id;
	filaID    = fila_id;
	ruta_select_array = [];
	recoleccion_ruta_array = [];
	init_complemento();
}


</script>
