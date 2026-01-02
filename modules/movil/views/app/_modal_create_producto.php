<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\esys\EsysListaDesplegable;
use app\models\envio\Envio;
use app\models\esys\EsysSetting;
?>

<div class="fade modal " id="modal-create-producto"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> Agregar producto</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <?php $formProducto = ActiveForm::begin(['id' => 'form-producto']) ?>
                    <div id="error-add-producto" class="has-error" style="display: none">
                    </div>
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Información de producto</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <?= $formProducto->field($producto, 'nombre')->textInput(['maxlength' => true]) ?>
                                    <?= $formProducto->field($producto, 'unidad_medida_id')->dropDownList(EsysListaDesplegable::getItems('unidad_de_uso'), ['prompt' => '']) ?>
                                </div>
                                <div class="col-sm-6">
                                    <?= $formProducto->field($producto, 'tipo_servicio')->dropDownList(Envio::$tipoList, ['readonly' => true]) ?>
                                    <?= $formProducto->field($producto, 'categoria_id')->dropDownList([], ['prompt' => '']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="contente_lax_tierra" >
                                    <input id="demo-show-device-checkbox" name="is_impuesto" class="toggle-switch checkbox_impuesto"  type="checkbox">
                                    <label for="demo-show-device-checkbox">Aplica Impuestos</label>
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">Impuestos para servicio  <strong id="tipo_servicio_impuesto"></strong></h3>
                                        </div>
                                        <div class="panel-body">

                                            <div class="row totales cobros div_impuesto_lax" style="display: none">
                                                 <div class="col-sm-4">
                                                    <span class="label">Producto Nuevos: </span>
                                                    <span class="total monto" ><?=  EsysSetting::getImpuestoNewLax()?> %</span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <span class="label">Produtos Usados: </span>
                                                    <span class="total monto"><?=  EsysSetting::getImpuestoOldLax()?> %</span>
                                                </div>
                                            </div>

                                            <div class="row totales cobros div_impuesto_tierra" style="display: none;">
                                                 <div class="col-sm-4">
                                                    <span class="label">Producto Nuevos: </span>
                                                    <span class="total monto" ><?=  EsysSetting::getImpuestoNewTierra()?> %</span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <span class="label">Produtos Usados: </span>
                                                    <span class="total monto"><?=  EsysSetting::getImpuestoOldTierra()?> %</span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Guardar cambios', ['class' => 'btn btn-primary' , 'id' => 'send_producto']) ?>
            </div>
        </div>
    </div>
</div>

<script>

var $selectTipoServicio = $('select[name = "Producto[tipo_servicio]"]'),
    $selectCategoriaID  = $('select[name = "Producto[categoria_id]"]'),
    $selectUnidad       = $('select[name = "Producto[unidad_medida_id]"]'),
    $formNombre         = $('input[name = "Producto[nombre]"]'),
    $formNota           = $('input[name = "Producto[nota]"]'),
    $formProducto       = $('#form-producto');
    $send_producto          =  $("#send_producto"),
    $form_comentario        = $('input[type=text],textarea,input[name="comentario"]'),
    $error_add_producto      = $('#error-add-producto'),
    $modal_contacto         = $('#modal-create-producto'),
    $div_impuesto_tierra         = $('.div_impuesto_tierra'),
    $div_impuesto_lax         = $('.div_impuesto_lax'),
    tipo = {
            tierra  : <?= Envio::TIPO_ENVIO_TIERRA ?>,
            lax     : <?= Envio::TIPO_ENVIO_LAX ?>,
            mex     : <?= Envio::TIPO_ENVIO_MEX ?>,
    };

$(document).ready(function(){
    $selectTipoServicio.change(function(){
        $selectCategoriaID.html(null);

        if ($(this).val() == tipo.lax) {
            $div_impuesto_tierra.hide();
            $div_impuesto_lax.show();
        }

        if ($(this).val() == tipo.tierra) {
            $div_impuesto_tierra.show();
            $div_impuesto_lax.hide();
        }

        $.get("<?= Url::to(['categoria-ajax']) ?>",{  tipo_servicio : $(this).val() },function($categoria){
            categoriaList = $categoria;

            $.each($categoria, function(key, value){
                $selectCategoriaID.append("<option value='" + value.id + "'>" + value.singular + "</option>\n");
            });
            $selectCategoriaID.val(0).trigger('change');
        });
    });

    $send_producto.click(function(){
        if(validation_form_producto()){
            return false;
        }
        $.post("<?= Url::to(['send-producto-ajax']) ?>",  $formProducto.serialize() ,function(json){
            if (json.code == 202) {
                $.niftyNoty({
                    type: "success",
                    container : "floating",
                    title : "Guardado",
                    message : json.message,
                    closeBtn : false,
                    timer : 5000
                });
            }else{
                if (json.code == 10) {
                    $.niftyNoty({
                        type: "danger",
                        container : "floating",
                        title : "Error",
                        message : json.message,
                        closeBtn : false,
                        timer : 5000
                    });
                }
            }
            $modal_contacto.modal('hide');
        });
    });




    var validation_form_producto = function()
    {
        $error_add_producto.html('');
        switch(true){
            case !$selectUnidad.val() :
                $error_add_producto.append('<div class="help-block">* Selecciona unidad de medida</div>');
                $error_add_producto.show();
                return true;
            break;

            case !$selectCategoriaID.val() :
                $error_add_producto.append('<div class="help-block">* Selecciona una categoria</div>');
                $error_add_producto.show();
                return true;
            break;

            case !$selectCategoriaID.val() :
                $error_add_producto.append('<div class="help-block">* Selecciona una categoria</div>');
                $error_add_producto.show();
                return true;
            break;

            case !$formNombre.val() :
                $error_add_producto.append('<div class="help-block">* Ingresa un nombre para el producto</div>');
                $error_add_producto.show();
                return true;
            break;
        }
    }
});

var init_producto = function(){

    $selectTipoServicio.val(null);
    $selectCategoriaID.val(null);
    $selectUnidad.val(null);
    $formNombre.val(null);
    $formNota.val(null);

    $selectTipoServicio.val($tipo_envio.val()).trigger("change");
}
</script>
