<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
 ?>
<div class="fade modal inmodal " id="modal-create-ruta"  tabindex="-1" role="dialog" aria-labelledby="modal-create-ruta-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Asignar sucursales a la ruta <strong><span  class="label monto text-dark "> <?= $model->nombre ?> </span> </h4> </strong>
            </div>
            <!--Modal body-->
            <?php $form = ActiveForm::begin(['id' => 'form-promocion' ]) ?>
            <?= $form->field($model->ruta_sucursal, 'ruta_sucursal_array')->hiddenInput()->label(false) ?>

            <div class="modal-body">
            	<div class="ibox">
                    <div class="ibox-content historial-cambios nano">
                       <div class="nano-content">
                    	  	<div class="row">
                            	<div class="col-sm-12">
	                            	<div class="table-responsive">
		                                <table class="table table-striped">
		                                    <thead>
		                                        <tr>
		                                            <th style="text-align: center;">Sucursal</th>
		                                            <th style="text-align: center;">Encargado</th>
		                                            <th style="text-align: center;">Seleccionar</th>
		                                        </tr>
		                                    </thead>
		                                    <tbody class="table_sucursal" style="text-align: center;">

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
                <?= Html::submitButton('Agregar', ['class' =>  'btn btn-primary', 'id' => 'form-promocion']) ?>
            </div>
			<?php ActiveForm::end(); ?>

        </div>
    </div>
</div>


 <div class="display-none">
     <table>

        <tbody class="template_sucursal">
            <tr id = "sucursal_id_{{sucursal_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-sucursal"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-encargado"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-seleccion"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
$table_sucursal 		= $('.table_sucursal')
$template_sucursal		= $('.template_sucursal'),
$inputSucursalArray   = $('#rutasucursal-ruta_sucursal_array');
sucursal_ruta_array 	=  [];
sucursal_select_array 	=  [];
rutaID 					= 0;
var init_complemento = function()
{
	$table_sucursal.html("");
	sucursal_ruta_array = [];
	$.get("<?= Url::to(['sucursal-ruta-asignar-ajax']) ?>",{ ruta_id :  rutaID },function(json){
		sucursal_ruta_array = json;
		render_sucursal_template();
	},'json');
};

var render_sucursal_template = function(){
	$.each(sucursal_ruta_array,function(key,item){
        if (item.id) {
			template_sucursal = $template_sucursal.html();
			template_sucursal = template_sucursal.replace("{{sucursal_id}}",item.id);
			$table_sucursal.append(template_sucursal);
			$tr        =  $("#sucursal_id_" + item.id,$table_sucursal);
			$tr.attr("data-sucursal_id",item.id);
			$("#table-sucursal",$tr).html(item.nombre);
			$("#table-encargado",$tr).html(item.nombre_completo);
			$("#table-seleccion",$tr).html('<input type="checkbox" onchange="refresh_paquete_change(this)" >' );
        }

	});


}

var  refresh_paquete_change = function(ele){
	$ele_paquete_val    = $(ele);
	$ele_sucursal        = $(ele).closest('tr');
	$ele_sucursal_id  	= $ele_sucursal.attr("data-sucursal_id");
	if ($ele_paquete_val.prop('checked')) {
		$.each(sucursal_ruta_array, function(key, sucursal){
			if (sucursal.id == $ele_sucursal_id) {
    			sucursal_select_array.push(sucursal);
			}
		});
	}else{
		$.each(sucursal_select_array, function(key, sucursal_remove)
		{
			if (sucursal_remove) {
				if (sucursal_remove.id == $ele_sucursal_id) {
					sucursal_select_array.splice(key,1 );
				}
			}
		});
	}
	$inputSucursalArray.val(JSON.stringify(sucursal_select_array));
}

var init_asignacion_sucursal = function (ruta_id){
	rutaID = ruta_id;
	sucursal_select_array = [];
	init_complemento();
}

</script>
