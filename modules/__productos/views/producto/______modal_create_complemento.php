<?php 
use yii\helpers\Html;
use app\models\promocion\PromocionDetalleComplemento;
 ?>
<div class="fade modal " id="modal-create-complemento"  tabindex="-1" role="dialog" aria-labelledby="modal-create-complemento-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Asigna complementos a tu promoción </h4>
            </div>

            <!--Modal body-->            
            <div class="modal-body">
            	<div class="panel">
                    <div class="panel-body">
		                <div class="row">
		                	<div class="col-sm-6">
		                		<label class="control-label" for="tipo_complemento">Tipo de complemento : </label>
		                		<?= Html::dropDownList('tipo_complemento', null, PromocionDetalleComplemento::$complementoList, [ 'class' => 'form-control']) ?>
		                	</div>
		                	<div class="col-sm-6">
		                		<label class="control-label" for="tipo">Aplica a: </label>
		                		<?= Html::dropDownList('tipo', null, PromocionDetalleComplemento::$tipoList, [ 'class' => 'form-control']) ?>

		                		<div class="help-block"></div>
		                	</div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-12">
		                		<div class="form-group">
		                			<?= Html::dropDownList('categoria_id', null, [], ['prompt'=>'Selecciona la categoria' ,'class' => 'form-control', "style" => 'display:none']) ?>		                			
		                		</div>
		                	</div>
		                </div>
		            </div>
		        </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Crear complemento', ['class' =>  'btn btn-primary', 'id' => 'form-cliente']) ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	var $form_complemento = {
		tipo_complemento : $('select[name = "tipo_complemento"]'),
		tipo : $('select[name = "tipo"]'),
		categoria_id : $('select[name = "categoria_id"]'),
	};
	
	$form_complemento.tipo_complemento.change(function(){
		console.log($(this).val());
	});

	$form_complemento.tipo.change(function(){
		
		if ($(this).val() == <?= PromocionDetalleComplemento::TIPO_PRODUCTO ?>)
			$form_complemento.categoria_id.show();
		else
			$form_complemento.categoria_id.hide();

	});
});
</script>
