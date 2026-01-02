<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\producto\Producto;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysSetting;
use app\models\pais\PaisesLatam;

?>

<div class="prductos-producto-form">
    <?php $form = ActiveForm::begin(['id' => 'form-promocion']) ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <div class="row">
        <div class="col-lg-6">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Información de producto</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'is_producto')->dropDownList(Producto::$tipoProductoList) ?>

                            <div class="div_tipo_producto">
                                <?= $form->field($model, 'unidad_medida_id')->dropDownList(EsysListaDesplegable::getItems('unidad_de_uso')) ?>
                                <?= $form->field($model, 'costo_libra')->textInput(['type' => 'number', 'style' => 'font-size: 30px;']) ?>
                            </div>

                            <div class="div_tipo_caja" style="display: none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?= $form->field($model, 'costo_total')->textInput(['type' => 'number', 'style' => 'font-size: 30px;' , 'step' => '0.01']) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?= $form->field($model, 'costo_suc')->textInput(['type' => 'number', 'style' => 'font-size: 30px;', 'step' => '0.01']) ?>
                                    </div>
                                </div>


                            </div>

                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model, 'tipo_servicio')->dropDownList(Envio::$tipoList, ['prompt' => '']) ?>

                            <?= $form->field($model, 'categoria_id')->dropDownList([], ['prompt' => '']) ?>

                            <div class="div_tipo_caja" style="display: none">
                                <?= $form->field($model, 'sucursal_id')->widget(
                                    Select2::classname(),
                                    [
                                        'language' => 'es',
                                        'value' => isset($model->sucursal_id)  && $model->sucursal_id ? [$model->sucursal->id => $model->sucursal->clave . " " . $model->sucursal->nombre] : [],
                                        'data' =>  Sucursal::getItemsUsa(),
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                        'options' => [
                                            'placeholder' => 'Sucursal',
                                        ],

                                    ]
                                ) ?>
                            </div>

                        </div>
                    </div>
                    <?= $form->field($model, 'status')->dropDownList(Producto::$statusList) ?>
                    <?= $form->field($model, 'nota')->textArea(['rows' => 6]) ?>
                </div>
            </div>

        </div>

        <div class="col-md-6">
            <div class="ibox" id="div_caja_sin_limite">
                <div class="ibox-title">CAJAS SIN LÍMITE</div>
                <div class="ibox-content">
                    <div class="row text-center">
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <h3>PAÍS</h3>
                        </div>
                        <div class="col-md-4"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'pais_id', ['labelOptions' => ['style' => 'display:none;']])->dropDownList(PaisesLatam::getPaises(), ['class' => 'form-control', 'prompt' => 'Selecciona un país']) ?>

                        </div>
                        <div class="col-md-6">
                            <?= Select2::widget([
                                'name' => 'sucursal_id', // Aquí debes asegurarte de que el nombre del campo sea correcto
                                'value' => isset($model->sucursal_id) && $model->sucursal_id ? [$model->sucursal->id => $model->sucursal->clave . " " . $model->sucursal->nombre] : [],
                                'data' => Sucursal::getItemsUsa(),
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'placeholder' => 'Sucursal', // La opción de placeholder debería ir en pluginOptions
                                ],
                                'options' => [
                                    'placeholder' => 'Sucursal',
                                ],
                            ]) ?>

                            <div class="col-md-6">
                                <?= "" //Html::img('@web/uploads/flags/default.jpeg', ['alt' => 'PAÍS', 'class' => 'img-responsive', 'style' => 'width: 100%; height: auto;']) 
                                ?>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row text-center">
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <h3>DIMENCIONES</h3>
                        </div>
                        <div class="col-md-4"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($modelCaja, 'largo')->textInput(['type' => 'number', 'style' => 'font-size: 30px;']) ?>

                        </div>
                        <div class="col-md-4">
                            <?= $form->field($modelCaja, 'ancho')->textInput(['type' => 'number', 'style' => 'font-size: 30px;']) ?>

                        </div>
                        <div class="col-md-4">
                            <?= $form->field($modelCaja, 'alto')->textInput(['type' => 'number', 'style' => 'font-size: 30px;']) ?>
                        </div>
                    </div>
                    <br>
                    <div class="row text-center">
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <h3>COSTOS</h3>
                        </div>
                        <div class="col-md-4"></div>
                    </div>
                    <div class="row">
                        <!-- Primera columna: Campo para Costo Total 1 con etiqueta -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($modelCaja, 'costo_cli')->textInput(['type' => 'number', 'style' => 'font-size: 30px;','step' => '0.01']) ?>

                            </div>
                        </div>

                        <!-- Segunda columna: Campo para Costo Total 2 con etiqueta -->
                        <div class="col-md-6">
                            <div class="form-group">
                            <?= $form->field($modelCaja, 'costo_suc')->textInput(['type' => 'number', 'style' => 'font-size: 30px;','step' => '0.01']) ?>
                                
                            </div>
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-4">



                        </div>
                        <div class="col-md-4"></div>
                    </div>

                </div>
            </div>

        </div>
    </div>



    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear producto' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>




<script>
    var $selectTipoServicio = $('select[name = "Producto[tipo_servicio]"]'),
        $selectCategoriaID = $('select[name = "Producto[categoria_id]"]'),

        $tipo_servicio_impuesto = $('#tipo_servicio_impuesto'),
        $seccionMex = $('.contente_mex'),
        $seccionTierraLax = $('.contente_lax_tierra'),
        $selectTipoProducto = $('#producto-is_producto'),
        $alertCategoria = $('.alert-categoria'),
        $is_impuesto_check = "<?= isset($model->is_impuesto) && $model->is_impuesto ? true : false ?>";
    $checkbox_impuesto = $('.checkbox_impuesto'),

        $div_impuesto_tierra = $('.div_impuesto_tierra'),
        $div_impuesto_lax = $('.div_impuesto_lax'),

        tipo = {
            tierra: <?= Envio::TIPO_ENVIO_TIERRA ?>,
            mex: <?= Envio::TIPO_ENVIO_MEX ?>,
        };

    $('#div_caja_sin_limite ').hide();
    productoDetalle_array = [];
    categoriaList = [];


    $(document).ready(function() {
        $selectTipoServicio.trigger('change');
        $selectTipoProducto.trigger('change');


        $selectCategoriaID.change(function() {
            categoriaId = $(this).val();
            $.each(categoriaList, function(key, value) {

                if (categoriaId == value.id) {
                    if (value.is_mex == 10) {
                        $('.content_info', $alertCategoria).html('');
                        $alertCategoria.show();
                        $('.content_info', $alertCategoria).append('<strong> Apartir de: ' + value.mex_required_min + ' piezas  / Costo extra: ' + value.mex_costo_extra + ' USD / Intervalo ' + value.mex_intervalo + '</strong>');
                    } else
                        $alertCategoria.hide();
                }
            });
        });
    });


    $selectTipoProducto.change(function() {
        $('.div_tipo_producto').show();
        $('#div_caja_sin_limite ').hide();
        $selectTipoServicio.val(10).attr("disabled", false);
        let val_opc = parseInt($(this).val());
        $('.div_tipo_caja').hide();
        if (val_opc == 20) {
            $('.div_tipo_producto').hide();
            $('.div_tipo_caja').show();
            $selectTipoServicio.val(10).attr("disabled", true);
        }
        if (val_opc == 30) {
            $('.div_tipo_producto').hide();
            $('.div_tipo_caja').hide();
            $selectTipoServicio.val(10).attr("disabled", true);
            $('#div_caja_sin_limite ').show();
            $('.div_tipo_caja_suc').show();

        }

    });

    $selectTipoServicio.change(function() {
        $checkbox_impuesto.prop("checked", $is_impuesto_check);
        $tipo_servicio_impuesto.html($('option:selected', $selectTipoServicio).text());

        $selectCategoriaID.html(null);
        $alertCategoria.hide();
        $selectTipoServicio.val() ? null : $seccionTierraLax.hide(), $seccionMex.hide();

        /*if ($selectTipoServicio.val() && $(this).val() != tipo.mex ) {
            $seccionTierraLax.show();
            $seccionMex.hide();

            /*if ($(this).val() == tipo.lax) {
                $div_impuesto_tierra.hide();
                $div_impuesto_lax.show();
            }*/

        /* if ($(this).val() == tipo.tierra) {
            $div_impuesto_tierra.show();
            $div_impuesto_lax.hide();
        }

    }else*/
        if ($(this).val() == tipo.mex) {
            $seccionMex.show();
            $seccionTierraLax.hide();

        }

        $.get("<?= Url::to(['categoria-ajax']) ?>", {
            tipo_servicio: $(this).val()
        }, function($categoria) {
            categoriaList = $categoria;
            $.each($categoria, function(key, value) {
                $selectCategoriaID.append("<option value='" + value.id + "'>" + value.singular + "</option>\n");
            });

            $selectCategoriaID.val(<?= isset($model->categoria_id) && $model->categoria_id ? $model->categoria_id : 0  ?>).trigger('change');
        });
    });
</script>