<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\esys\EsysListaDesplegable;
use app\models\envio\Envio;
use app\models\esys\EsysSetting;
?>

<div class="fade modal inmodal " id="modal-create-producto"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Agregar producto</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <?php $formProducto = ActiveForm::begin(['id' => 'form-producto']) ?>
                    <div id="error-add-producto" class="has-error" style="display: none">
                    </div>

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

    tipo = {
            tierra  : <?= Envio::TIPO_ENVIO_TIERRA ?>,
            mex     : <?= Envio::TIPO_ENVIO_MEX ?>,
    };

$(document).ready(function(){
    $selectTipoServicio.change(function(){
        $selectCategoriaID.html(null);
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

            }else{
                if (json.code == 10) {

                }
            }
            $('#modal-create-producto').modal('hide');
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
