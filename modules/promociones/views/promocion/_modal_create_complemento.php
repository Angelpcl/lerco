<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\promocion\PromocionDetalleComplemento;
use app\models\esys\EsysListaDesplegable;
use app\models\promocion\PromocionComplemento;
use app\models\esys\EsysSetting;
 ?>
<div class="fade modal " id="modal-create-complemento"  tabindex="-1" role="dialog" aria-labelledby="modal-create-complemento-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Asigna complementos a tu promoción <strong><span  class="label monto text-dark ">Costo de la libra actual: <strong id="precio_libra_actual"></strong> USD </span> </h4> </strong>
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
                    	</div>
		                <div class="row">
		                	<div class="col-sm-6">
		                		<label class="control-label" for="tipo">Aplica a: </label>
		                		<?= Html::dropDownList('tipo', null, PromocionDetalleComplemento::$tipoList, [ 'class' => 'form-control']) ?>
		                		<div class="help-block"></div>
		                	</div>
		                	<div class="col-sm-6">
		                		<div class="content_option_categoria" style="display: none">
			                		<label class="control-label" for="tipo">Selecciona una categoria: </label>
			                		<?= Html::dropDownList('categoria_id', null, EsysListaDesplegable::getItems('categoria_paquete_lax_tierra'), ['class' => 'form-control']) ?>
		                		</div>
		                	</div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-6">
		                		<div class="form-group">
									<?= Html::dropDownList('producto_tipo', null,PromocionDetalleComplemento::$productoTipoList, ['class' => 'form-control', "style" => 'display:none']) ?>
								</div>
		                	</div>
		                	<div class="col-sm-6">
		                		<?= Html::dropDownList('producto_id', null,[], ['class' => 'form-control', "style" => 'display:none']) ?>
		                	</div>
		                </div>
						<div class="row">
							<div class="col-sm-6">
								<?= Html::label('N° productos :', 'cant_producto') ?>
								<div class="input-group mar-btm">
		               				<?= Html::input('number', 'cant_producto',1,[ 'id' => 'cant_producto','class' => 'form-control']) ?>
		               				<span class="input-group-addon"><i class="fa fa-cubes "></i></span>
                                </div>
								<div class="checkbox">
		                			<?= Html::checkbox('precio_menor_check', null, ['id'=>'precio_menor_check','class' => 'magic-checkbox']); ?>
		                			   <label for="precio_menor_check"> ¿ Aplica validación en el valor de articulo  ? </label>
		                		</div>
		                		<div class="div_valor_producto" style="display: none">
									<?= Html::label('Ingresa el valor del articulo :', 'valor_paquete_aprox') ?>
									<div class="input-group mar-btm">
			               				<?= Html::input('number', 'valor_paquete_aprox',null,[ 'id' => 'valor_paquete_aprox','class' => 'form-control']) ?>
			               				<span class="input-group-addon">USD</span>
	                                </div>
	                                <strong><small>La validación del valor articulo debera ser inferior al valor ingresado para cumplir con el requerimiento </small></strong>

		                		</div>
							</div>
						</div>


		                <div class="content_option_check">
		                	<div class="alert alert-warning aviso_is_envio" style="display: none">
								<strong>Aviso!</strong> El envio del producto se realizara sin costo alguno
					        </div>
			                <div class="row">
			                	<div class="col-sm-4">
			                		<div class="checkbox">
			                			<?= Html::checkbox('lbfree_check', null, ['id'=>'lbfree_check','class' => 'magic-checkbox']); ?>
			                			   <label for="lbfree_check">Libras gratis</label>
			                		</div>
			                	</div>
			                	<div class="col-sm-4">
				                	<div class="checkbox">
		                                <?= Html::checkbox('sin_impuesto_check', null, ['id'=>'sin_impuesto_check','class' => 'magic-checkbox']); ?>
		                                <label for="sin_impuesto_check">Sin impuestos</label>
		                            </div>
			                	</div>
			                	<div class="col-sm-4">
				                	<div class="checkbox">
		                                <?= Html::checkbox('is_envio_check', null, ['id'=>'is_envio_check','class' => 'magic-checkbox']); ?>
		                                <label for="is_envio_check">Envio gratis</label>
		                            </div>
			                	</div>
			                	<?php /* ?>
			                	<div class="col-sm-3">
				                	<div class="checkbox">
	                                    <?= Html::checkbox('is_libras_excedente', null, ['id'=>'is_libras_excedente','class' => 'magic-checkbox']); ?>
	                                    <label for="is_libras_excedente">Libras exedente </label>
	                                </div>
	                            </div>
	                            */ ?>
			                </div>
		                </div>
		                <div class="row div_libras_free"  style="display: none">
		                	<div class="col-sm-6">
		                		<?= Html::label('Libras Gratis:', 'lbfree') ?>
		                		<?= Html::input('number', 'lbfree',null,[ 'id' => 'lbfree','class' => 'form-control']) ?>
		                	</div>
		                </div>
		                <?php /*
		                <div class="row div_libras_excedente" style="display: none">
		                	<div class="col-sm-6">
		                		<?= Html::label('Libras excendente :', 'lbexcedente') ?>
		                		<?= Html::input('number', 'lbexcedente',null,[ 'id' => 'lbexcedente','class' => 'form-control']) ?>
		                	</div>
		                	<div class="col-sm-6">
	                			<?= Html::label('Costo de la libra excente:', 'lbcosto_excedente') ?>
		                		<?= Html::input('number', 'lbcosto_excedente',null,[ 'id' => 'lbcosto_excedente','class' => 'form-control']) ?>
		                	</div>
		                </div>
		                */ ?>
		                <div class="row">
		                	<div class="col-sm-offset-10">
	                            <button id="btnAgregarComplemento" type="button" style="margin-top: 15px" class="btn btn-primary btn-circle" disabled><i class="fa fa-plus line icon-lg"></i></button>
							</div>
		                </div>
		                <div class="row">
                            <div class="col-sm-12">
                            	<div class="table-responsive">
	                                <table class="table table-striped">
	                                    <thead>
	                                        <tr>
	                                            <th>Tipo</th>
	                                            <th>N° Productos</th>
	                                            <th>Aplica a</th>
	                                            <th>Categoria</th>
	                                            <th>Producto</th>
	                                            <th>Aplica Valor promedio</th>
	                                            <th>Libras/gratis</th>
	                                            <th>Sin impuesto</th>
	                                            <th>Envio gratis</th>
	                                            <?php /* ?>
	                                            <th>Libra/excedente</th>
	                                            <th>Libra excedente</th>
	                                            <th>Costo de libra excedente</th>
	                                            */?>
	                                            <th>Acciones</th>
	                                        </tr>
	                                    </thead>
	                                    <tbody class="table_complemento_promocion" style="text-align: center;">

	                                    </tbody>
	                                </table>
                            	</div>
                            </div>
                        </div>
		            </div>
		        </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

 <div class="display-none">
     <table>

        <tbody class="template_complemento">
            <tr id = "complemento_id_{{complemento_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-tipo_complemento"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-num_producto"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-tipo"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-categoria_text"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-producto_text"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-valor_promedio_check"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-lbfree_check"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-sin_impuesto_check"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-is_envio_check"]) ?></td>
                <?php /* ?>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-is_libras_excedente"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-lbexcedente"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-lbcosto_excedente"]) ?></td>
                */?>
            </tr>
        </tbody>
    </table>
</div>



<script type="text/javascript">
var $form_complemento = {
	tipo_complemento : $('select[name = "tipo_complemento"]'),
	tipo 			 : $('select[name = "tipo"]'),
	categoria_id 	 : $('select[name = "categoria_id"]'),
	content_option_categoria  : $('.content_option_categoria'),
	producto_tipo 	 : $('select[name = "producto_tipo"]'),
	producto_id 	 : $('select[name = "producto_id"]'),


	lbfree_check 	 	: $('#lbfree_check'),
	sin_impuesto_check 	: $('#sin_impuesto_check'),
	precio_menor_check 	: $('#precio_menor_check'),
	is_envio_check  	: $('#is_envio_check'),
	is_libras_excedente : $('#is_libras_excedente'),
	aviso_is_envio  	: $('.aviso_is_envio'),

	table_complemento_promocion  	: $('.table_complemento_promocion'),
	template_complemento		  	: $('.template_complemento'),

	content_option_check: $('.content_option_check'),
	div_libras_free 	: $('.div_libras_free'),
	div_valor_producto 	: $('.div_valor_producto'),

	$precio_libra_actual : $('#precio_libra_actual'),


	div_libras_excedente: $('.div_libras_excedente'),
	lbfree 			 	: $('#lbfree'),
	cant_producto	 	: $('#cant_producto'),
	valor_paquete_aprox: $('#valor_paquete_aprox'),
	//lbexcedente 		: $('#lbexcedente'),
	//lbcosto_excedente 	: $('#lbcosto_excedente'),
	btnAgregarComplemento : $('#btnAgregarComplemento'),
},
tipo_servicio 		= $('#promocion-tipo_servicio'),
is_categoria = false,
is_producto = false,
promocion_detalle_id = null,
promocion_origen = null,

complemento_array   = [];

$(document).ready(function(){

	$form_complemento.tipo.change(function(){
		if ($(this).val() == <?= PromocionDetalleComplemento::TIPO_PRODUCTO ?>){
			$form_complemento.content_option_categoria.show();
			$form_complemento.producto_tipo.show();
			is_categoria = true;
			is_producto = true;
		}
		else{
			$form_complemento.content_option_categoria.hide();
			$form_complemento.producto_tipo.hide();
			$form_complemento.producto_id.hide();
			$form_complemento.div_libras_free.hide();


			is_categoria = false;
			is_producto = false;
		}
	});

	$form_complemento.producto_tipo.change(function(){
		if ($(this).val() == <?= PromocionDetalleComplemento::PRODUCTO_ELECCION  ?>){
			filters = "tipo_servicio="+ tipo_servicio.val()+"&categoria_id="+$form_complemento.categoria_id.val();
			search_producto(filters);
			$form_complemento.producto_id.show();
			is_producto = true;
		}
		else{
			$form_complemento.producto_id.html(false);
			$form_complemento.producto_id.hide();
			is_producto = false;
		}
	});

	$form_complemento.categoria_id.change(function(){
		if (is_producto) {
			filters = "tipo_servicio="+ tipo_servicio.val()+"&categoria_id="+$form_complemento.categoria_id.val();
			search_producto(filters);
			//$form_complemento.producto_id.show();
		}
	});

	$form_complemento.lbfree_check.change(function(){
		if($(this).is(':checked')){
			$form_complemento.sin_impuesto_check.prop('checked',false);
			$form_complemento.is_envio_check.prop('checked',false);
			$form_complemento.btnAgregarComplemento.prop("disabled",false);
			$form_complemento.is_libras_excedente.prop("checked",false);
			$form_complemento.div_libras_free.show();
		}else{
			$form_complemento.btnAgregarComplemento.prop("disabled",true);
			 //$(this).hide();
			 $form_complemento.div_libras_free.hide();
		}

		$form_complemento.div_libras_excedente.hide();
		$form_complemento.aviso_is_envio.hide();


	});

	$form_complemento.is_libras_excedente.change(function(){
		if($(this).is(':checked')){
			$form_complemento.sin_impuesto_check.prop('checked',false);
			$form_complemento.is_envio_check.prop('checked',false);
			$form_complemento.lbfree_check.prop('checked',false);
			$form_complemento.btnAgregarComplemento.prop("disabled",false);
			$form_complemento.div_libras_excedente.show();
		}else{
			$form_complemento.btnAgregarComplemento.prop("disabled",true);
			$form_complemento.div_libras_excedente.hide();
		}
		$form_complemento.div_libras_free.hide();


		$form_complemento.aviso_is_envio.hide();
	});

	$form_complemento.sin_impuesto_check.change(function(){
		if($(this).is(':checked')){
			$form_complemento.lbfree_check.prop('checked',false);
			$form_complemento.is_envio_check.prop('checked',false);
			$form_complemento.btnAgregarComplemento.prop("disabled",false);
			$form_complemento.is_libras_excedente.prop("checked",false);

		}else{
			$form_complemento.btnAgregarComplemento.prop("disabled",true);

		}

		$form_complemento.div_libras_free.hide();

		$form_complemento.div_libras_excedente.hide();
		$form_complemento.aviso_is_envio.hide();
	});

	$form_complemento.precio_menor_check.change(function(){
		if($(this).is(':checked')){
			$form_complemento.div_valor_producto.show();
		}else{
			$form_complemento.div_valor_producto.hide();
		}
	});


	$form_complemento.is_envio_check.change(function(){
		if($(this).is(':checked')){
			$form_complemento.lbfree_check.prop('checked',false);
			$form_complemento.sin_impuesto_check.prop('checked',false);
			$form_complemento.aviso_is_envio.show();
			$form_complemento.btnAgregarComplemento.prop("disabled",false);
			$form_complemento.is_libras_excedente.prop("checked",false);

		}else{
			$form_complemento.btnAgregarComplemento.prop("disabled",true);
			$form_complemento.aviso_is_envio.hide();

		}
		$form_complemento.div_libras_excedente.hide();
		$form_complemento.div_libras_free.hide();


	});

 	/*==============================================
    // Busca los productos relacionados con la categoria seleccionada
    ===============================================*/

	var search_producto = function(filters){
		$.get('<?= Url::to('productos-categoria-ajax') ?>', { filters: filters},function(producto){
			$form_complemento.producto_id.html('');
			if(parseInt(producto.total) > 0){
                $.each(producto.rows, function(key, value){
                    $form_complemento.producto_id.append("<option value='" + value.id + "'>" + value.nombre + "</option>\n");
                });
            }
		},'json');
		if ($form_complemento.producto_tipo.val() != <?= PromocionDetalleComplemento::PRODUCTO_ELECCION  ?>)
			is_producto = false;
	}


    /*==============================================
    // Agrega un item al array de datos (OPTION1)
    ===============================================*/

    $form_complemento.btnAgregarComplemento.click(function(){

        complemento = {
            "complemento_id"  : complemento_array.length + 1,
            "tipo_complemento" : $form_complemento.tipo_complemento.val(),
            "tipo_complemento_text" :$('option:selected',$form_complemento.tipo_complemento).text(),
            "cantidad_producto" : $form_complemento.cant_producto.val(),
            "tipo"      	  	: $form_complemento.tipo.val(),
            "producto_tipo"     : $form_complemento.producto_tipo.val(),
            "tipo_text"       	: $('option:selected',$form_complemento.tipo).text(),
            "categoria_id"    	: is_categoria ? $form_complemento.categoria_id.val() :   null,
            "categoria_text"   	: is_categoria ? $('option:selected',$form_complemento.categoria_id).text() : "N/A",

            "producto_id"   	: is_producto ?  $form_complemento.producto_id.val() : null,
            "producto_text" 	: is_producto ?  $('option:selected',$form_complemento.producto_id).text() : "N/A",

            "lb_free" 		  	: $form_complemento.lbfree.val(),

            "is_valor_paquete"	:  $form_complemento.precio_menor_check.is(':checked') ? <?= PromocionComplemento::ON_VALOR_PAQUETE  ?> : <?= PromocionComplemento::OFF_VALOR_PAQUETE  ?> ,

            "valor_paquete_aprox" :  $form_complemento.valor_paquete_aprox.val(),

            "cobro_impuesto"  	:  $form_complemento.sin_impuesto_check.is(':checked') ? <?= PromocionComplemento::ON_COBRO_IMPUESTO  ?> : <?= PromocionComplemento::OFF_COBRO_IMPUESTO  ?> ,

            "is_envio_free"   	: $form_complemento.is_envio_check.is(':checked') ? <?= PromocionComplemento::ON_ENVIO_FREE  ?> : <?= PromocionComplemento::OFF_ENVIO_FREE  ?> ,

            "lbfree_check"    	: $form_complemento.lbfree_check.is(':checked') ? <?= PromocionComplemento::ON_LBFREE  ?> : <?= PromocionComplemento::OFF_LBFREE  ?>,

            //"is_libras_excedente" : $form_complemento.is_libras_excedente.is(':checked') ? <?= PromocionComplemento::ON_LBEXCEDENTE  ?>  : <?= PromocionComplemento::OFF_LBEXCEDENTE  ?>,

            //"lbexcedente" 		: $form_complemento.lbexcedente.val(),
            //"lbcosto_excedente" : $form_complemento.lbcosto_excedente.val(),
            "origen"          : 1,
            "status"          : 10, // El status del detalle
            "create"          : 1,
            "opt"             : 1,
        };
        complemento_array.push(complemento);
        render_paquete_template();
        clear_form($form_complemento);
        $form_complemento.tipo.val(parseInt(<?=  PromocionDetalleComplemento::TIPO_GENERAL ?>));
        $form_complemento.producto_tipo.val(parseInt(<?=  PromocionDetalleComplemento::PRODUCTO_GENERAL ?>));
		$form_complemento.categoria_id[0].selectedIndex = 0;
		$form_complemento.lbfree_check.prop('checked',false);
        $form_complemento.sin_impuesto_check.prop('checked',false);
        $form_complemento.precio_menor_check.prop('checked',false);
        $form_complemento.is_libras_excedente.prop('checked',false);
        $form_complemento.is_envio_check.prop('checked',false);
        $form_complemento.tipo_complemento.val(parseInt(complemento.tipo_complemento)).trigger('change');
        $form_complemento.tipo_complemento.prop("disabled",true);
		$form_complemento.div_libras_excedente.hide();
        $form_complemento.div_valor_producto.hide();
		$form_complemento.aviso_is_envio.hide();
		$form_complemento.cant_producto.val(1),
		$form_complemento.btnAgregarComplemento.prop("disabled",true);
        $form_complemento.tipo.val(1).trigger('change');
    });
});


/*===============================================
* Rendererizado del array de datos
*===============================================*/

var render_paquete_template = function()
{
    $form_complemento.table_complemento_promocion.html("");

    $.each(complemento_array, function(key, complemento){

        if (complemento.complemento_id) {

            template_complemento = $form_complemento.template_complemento.html();
            template_complemento = template_complemento.replace("{{complemento_id}}",complemento.complemento_id);

            $form_complemento.table_complemento_promocion.append(template_complemento)
            $tr        =  $("#complemento_id_" + complemento.complemento_id,$form_complemento.table_complemento_promocion);
            $("#table-tipo_complemento",$tr).html(complemento.tipo_complemento_text);
            $("#table-tipo",$tr).html(complemento.tipo_text);
            $("#table-num_producto",$tr).html(complemento.cantidad_producto);
            $("#table-categoria_text",$tr).html(complemento.categoria_text);
            $("#table-producto_text",$tr).html(complemento.producto_text);

			$("#table-valor_promedio_check",$tr).html(complemento.is_valor_paquete == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i><p>"+ complemento.valor_paquete_aprox + " USD<p>" : "<i class='fa fa-times' aria-hidden='true'></i>");

			$("#table-lbfree_check",$tr).html(complemento.lbfree_check == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i><p>"+ complemento.lb_free +" USD<p>" : "<i class='fa fa-times' aria-hidden='true'></i>");


            $("#table-sin_impuesto_check",$tr).html(complemento.cobro_impuesto == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>");

            $("#table-is_envio_check",$tr).html(complemento.is_envio_free  == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" );

             //$("#table-is_libras_excedente",$tr).html(complemento.is_libras_excedente  == 10 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" );

			//$("#table-lbexcedente",$tr).html(complemento.lbexcedente);
			//$("#table-lbcosto_excedente",$tr).html(complemento.lbcosto_excedente);

            $tr.append("<td><button class='btn btn-warning btn-circle' type='button' onclick='refresh_complemento(this)'><i class='fa fa-trash'></i></button></td>");


            $tr.attr("data-complemento-id",complemento.complemento_id);
            $tr.attr("data-origen",complemento.origen);

        }
    });

    if (complemento_array.length > 0) {
	    promocion_array[promocion_key].promocione_complemento = [];
	    promocion_array[promocion_key].promocione_complemento.push(complemento_array);
	}

    $inputPromocionDetelle.val(JSON.stringify(promocion_array));

	//$inputProductoDetalle.val(JSON.stringify(productoDetalle_array));
};


/*===============================================
* Actualiza la lista de paquetes
*===============================================*/

var refresh_complemento = function(ele){

    $ele_sucursal_val = $(ele).closest('tr');
    $ele_complemento_id  = $ele_sucursal_val.attr("data-complemento-id");
    $ele_origen_id      = $ele_sucursal_val.attr("data-origen");

    $.each(complemento_array, function(key, complemento_d){
        if (complemento_d.complemento_id == $ele_complemento_id &&  complemento_d.origen == $ele_origen_id) {
            complemento_array.splice(key);
        }
    });

	promocion_array[promocion_key].promocione_complemento = [];
    promocion_array[promocion_key].promocione_complemento.push(complemento_array);

    $(ele).closest('tr').remove();
};

var init_complemento = function()
{
	$form_complemento.table_complemento_promocion.html("");
	complemento_array = [];

	if(promocion_array[promocion_key].promocione_complemento.length > 0){
		for (var i = 0; i < promocion_array[promocion_key].promocione_complemento[0].length; i++) {
	 		complemento_array.push(promocion_array[promocion_key].promocione_complemento[0][i]);
		}
		$form_complemento.tipo_complemento.val(parseInt(complemento_array[0].tipo_complemento)).trigger('change');
		$form_complemento.tipo_complemento.prop("disabled",true);
	}else
		$form_complemento.tipo_complemento.prop("disabled",false);

	render_paquete_template();
};

var add_complemento = function (key,promocion_detalle_id,origen){
	promocion_key = key;
	promocion_detalle_id = promocion_detalle_id;
	promocion_origen 	 = origen;
	init_complemento();
}





</script>
