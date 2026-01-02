<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use app\models\esys\EsysListaDesplegable;
?>
<div class="fade modal " id="modal-create-anexo"  tabindex="-1" role="dialog" aria-labelledby="modal-create-anexo-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> Agregar Anexo</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="panel">
                        <div class="panel-body">
                            <div id="error-add-paquete" class="has-error" style="display: none">
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <div class="perfiles_container mar-btm">
                                        <?= Html::label('Categoria', 'anexo-categorias', ['class' => 'control-label']) ?>
                                        <?= Select2::widget([
                                            'id' => 'anexo-categorias',
                                            'name' => 'Anexo[categorias_name]',
                                            'data' => EsysListaDesplegable::getItems('categoria_paquete_lax_tierra'),
                                            'value' => [0 => "Todas las categorias "],
                                            'options' => [
                                                'placeholder' => 'Categoria',
                                                'multiple' => true,
                                            ],
                                            'pluginOptions' => [
                                                'allowClear' => true
                                            ],
                                        ]) ?>
                                    </div>
                                    <?php /* ?>
                                    <label class="control-label" for="tipo">Categoria : </label>
                                    <?=  Html::dropDownList('anexo_categoria_id', null, EsysListaDesplegable::getItems('categoria_paquete_lax_tierra'), ['prompt'=>'Todos las categorias', 'class' => 'form-control']) ?>
                                    */?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <label class="control-label" for="tipo">Libras a otorgar: </label>
                                    <?=  Html::input('text',"anexo_libras_free",null,[ 'class' => 'form-control']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                        	<div class="col-sm-6 col-sm-offset-3">
                        		<?=  Html::button('Agregar anexo',[ 'class' => 'btn btn-primary btn-block btn-lg', 'id' => 'btnAgregarAnexo']); ?>
                        	</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                            	<div class="table-responsive">
	                                <table class="table table-striped">
	                                    <thead>
	                                        <tr>
	                                            <th class="text-center">Categoria</th>
	                                            <th class="text-center">Libras </th>
	                                            <th class="text-center">Acciones</th>
	                                        </tr>
	                                    </thead>
	                                    <tbody class="table_anexo_promocion" style="text-align: center;">

	                                    </tbody>
	                                </table>
                            	</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
 <div class="display-none">
     <table>
        <tbody class="template_anexo">
            <tr id = "anexo_id_{{anexo_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-anexo_categoria_text"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-anexo_libras_free"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>
<script>
var $form_anexo = {
        anexo_categorias : $('#anexo-categorias'),
		libras_free  : $('input[name = "anexo_libras_free"]')
	},
	$table_anexo_promocion  = $('.table_anexo_promocion'),
	$template_anexo  		= $('.template_anexo'),
    listCategoria           =  JSON.parse('<?= json_encode(EsysListaDesplegable::getItems('categoria_paquete_lax_tierra')) ?>'),
	$btnAgregarAnexo 		= $('#btnAgregarAnexo'),
	anexo_pdetalle_id   	= null,
	anexo_pdetalle_origen 	= null,
	anexo_promocion_key 	= null,
    is_categoria_all        = false;
	anexo_array 			= [];

$(document).ready(function(){

	$btnAgregarAnexo.click(function(){
		anexo = {
			"anexo_id": anexo_array.length + 1,
            "categorias"    : [],
			"libras_free"   : $form_anexo.libras_free.val(),
			"origen"          : 1,
		}
        categorias = $form_anexo.anexo_categorias.val();
        if (categorias.length > 0 &&  anexo.libras_free ) {
            for (var i = 0; i < categorias.length; i++) {
                categoria = {
                    categoria_nombre    : listCategoria[categorias[i]] ? listCategoria[categorias[i]] : 'Todas las categorias',
                    categoria_id        : categorias[i] ? categorias[i] : 0,
                    is_categoria        : listCategoria[categorias[i]] ? 10 : 1,
                };
                anexo.categorias.push(categoria);
            }
            anexo_array.push(anexo);
    		render_anexo_template();
    		render_promocion_template();
        }
	});
});


var render_anexo_template = function()
{
    $table_anexo_promocion.html("");

    $.each(anexo_array, function(key, anexo){

        if (anexo.anexo_id) {

            template_anexo = $template_anexo.html();
            template_anexo = template_anexo.replace("{{anexo_id}}",anexo.anexo_id);

            $table_anexo_promocion.append(template_anexo);

            $tr        =  $("#anexo_id_" + anexo.anexo_id,$table_anexo_promocion);

            categorias_name = "";
            for (var i = 0; i < anexo.categorias.length; i++) {
                categorias_name += anexo.categorias[i].categoria_nombre +" / ";
            }

            $("#table-anexo_categoria_text",$tr).html(categorias_name);

            $("#table-anexo_libras_free",$tr).html(anexo.libras_free);

            $tr.append("<td><button class='btn btn-warning btn-circle' type='button' onclick='refresh_anexo(this)'><i class='fa fa-trash'></i></button></td>");
            $tr.attr("data-anexo-id",anexo.anexo_id);
            $tr.attr("data-origen",anexo.origen);

        }
    });

    if (anexo_array.length > 0) {
	    promocion_array[anexo_promocion_key].anexos = [];
	    promocion_array[anexo_promocion_key].anexos.push(anexo_array);
	}

    $inputPromocionDetelle.val(JSON.stringify(promocion_array));

	//$inputProductoDetalle.val(JSON.stringify(productoDetalle_array));
};

var refresh_anexo = function(ele){

    $ele_sucursal_val 	= $(ele).closest('tr');
    $ele_anexo_id  		= $ele_sucursal_val.attr("data-anexo-id");


    $.each(anexo_array, function(key, anexo_d){
        if (anexo_d ) {
        	if (anexo_d.anexo_id == $ele_anexo_id)
            	anexo_array.splice(key,1);
        }
    });

	promocion_array[anexo_promocion_key].anexos = [];
    promocion_array[anexo_promocion_key].anexos.push(anexo_array);

    $(ele).closest('tr').remove();
	render_anexo_template();
	render_promocion_template();
    $inputPromocionDetelle.val(JSON.stringify(promocion_array));
};


var init_anexo = function()
{
	$table_anexo_promocion.html("");
	anexo_array = [];

    if (!is_categoria_all) {
        var newState = new Option("Todas las categorias", 0, true, true);
        $form_anexo.anexo_categorias.append(newState).trigger('change');
        is_categoria_all = true;
    }else{
        $form_anexo.anexo_categorias.val(null).trigger('change');
        $form_anexo.anexo_categorias.val(0).trigger('change');

    }


	if(promocion_array[anexo_promocion_key].anexos.length > 0){
		for (var i = 0; i < promocion_array[anexo_promocion_key].anexos[0].length; i++) {
	 		anexo_array.push(promocion_array[anexo_promocion_key].anexos[0][i]);
		}
	}
	render_anexo_template();
};


var add_anexo = function (key,promocion_detalle_id,origen){
    anexo_promocion_key = key;
	anexo_pdetalle_id     	= promocion_detalle_id;
	anexo_pdetalle_origen 	= origen;
	init_anexo();
}

</script>
