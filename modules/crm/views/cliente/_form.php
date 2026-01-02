<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\models\esys\EsysDireccionCodigoPostal;
use app\models\Esys;
use app\models\pais\PaisesLatam;
/*
use yii\web\JsExpression;
use kartik\date\DatePicker;
use app\models\user\User;
use app\models\user\UserAsignarPerfil;
*/
use app\models\cliente\Cliente;
use app\models\esys\EsysListaDesplegable;

/* @var $this yii\web\View */
/* @var $cliente app\models\cliente\User */
/* @var $form yii\widgets\ActiveForm */


?>

<div class="clientes-cliente-form">


    <?php if ($valid) : ?>
        <div class="alert alert-warning">
            <strong>Importante: </strong> La informacion de contacto coincide con <strong>[ <?= $valid->nombre  ?> ]</strong>, no debe ser igual a una sucursal
        </div>
    <?php endif ?>


    <?php $form = ActiveForm::begin(['id' => 'form-cliente']) ?>

    <?= $form->field($model, 'titulo_personal_id')->hiddenInput()->label(false) ?>

    <div class="form-group ">
        <?= Html::submitButton($model->isNewRecord ? 'Crear cliente' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'btnClienteSave']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>
    <div class="row">
        <div class="col-lg-7">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Información generales</h5>
                </div>
                <div class="ibox-content">
                    <?= $form->errorSummary($model) ?>
                    <?= $form->field($model, 'atraves_de_id')->dropDownList(EsysListaDesplegable::getItems('origen_cliente'), ['prompt' => '']) ?>
                    <div class="row">
                        <div class="col-lg-6">

                            <div class="row">
                                <div class="col-sm-4">
                                    <?= $form->field($model, 'titulo_personal_id')->dropDownList(EsysListaDesplegable::getItems('titulo_personal'), ['prompt' => ''])->label("&nbsp;") ?>
                                </div>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
                                </div>
                            </div>
                            <?= $form->field($model, 'apellidos')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter e-mail')]) ?>

                            <?= $form->field($model, 'servicio_preferente')->dropDownList(Cliente::$servicioList, ['prompt' => '']) ?>

                            <?= $form->field($model, 'tipo_cliente_id')->dropDownList(EsysListaDesplegable::getItems('tipo_cliente'), ['prompt' => '']) ?>

                            <?= $form->field($model, 'sexo')->dropDownList([10 => 'Hombre', 20 => 'Mujer',], ['prompt' => '']) ?>

                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'telefono_movil')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'asignado_id')->widget(
                                Select2::classname(),
                                [
                                    'language' => 'es',
                                    'data' => isset($model->asignado_id)  && $model->asignado_id ? [$model->asignadoCliente->id => $model->asignadoCliente->nombre . " " . $model->asignadoCliente->apellidos] : [],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 3,
                                        'language'   => [
                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                        ],
                                        'ajax' => [
                                            'url'      => Url::to(['/admin/user/user-ajax']),
                                            'dataType' => 'json',
                                            'cache'    => true,
                                            'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                        ],
                                    ],
                                    'options' => [
                                        'placeholder' => 'Selecciona al usuario...',
                                    ],

                                ]
                            ) ?>

                            <?= $form->field($model, 'medio_contacto_id')->dropDownList(EsysListaDesplegable::getItems('medio_contacto'), ['prompt' => '']) ?>

                            <?= $form->field($model, 'status_venta_id')->dropDownList(EsysListaDesplegable::getItems('status_venta'), ['prompt' => '']) ?>

                            <?= $form->field($model, 'comportamiento_id')->dropDownList(EsysListaDesplegable::getItems('comportamiento_cliente'), ['prompt' => '']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Estatus</h5>
                        </div>
                        <div class="ibox-content">
                            <?= $form->field($model, 'status')->dropDownList(Cliente::$statusList)->label(false) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Información extra / Comentarios</h5>
                        </div>
                        <div class="ibox-content">
                            <?= $form->field($model, 'notas')->textarea(['rows' => 6])->label(false); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-5">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Origen</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">

                        <?= "" // $form->field($model, 'origen')->dropDownList(Cliente::$origenList)->label(false); 
                        ?>

                        <div class="col-md-12">
                            <?= $form->field($model, 'country_id')->dropDownList(PaisesLatam::getPaises())->label(false); ?>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger" id="alert" style="display: none">
                                <strong>
                                    <font style="vertical-align: inherit;">
                                        <font style="vertical-align: inherit;">¡Advertencia! </font>
                                    </font>
                                </strong>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">La dirección proporcionada se encuentra en una zona de riesgo.</font>
                                </font>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-warning" id="alert-usa" style="display: none">
                <strong>
                    <font style="vertical-align: inherit;">
                        <font style="vertical-align: inherit;">¡Advertencia! </font>
                    </font>
                </strong>
                <font style="vertical-align: inherit;">
                    <font style="vertical-align: inherit;"> Captura la dirección de forma correcta antes de guardar, ya que se utilizara como referencia en el sistema.</font>
                </font>
            </div>
            <div class="alert alert-danger" id="alert_zona" style="display: none">
                <strong>
                    <font style="vertical-align: inherit;">
                        <font style="vertical-align: inherit;">¡Advertencia! </font>
                    </font>
                </strong>
                <font style="vertical-align: inherit;">
                    <font style="vertical-align: inherit;"> Captura la dirección de forma correcta antes de guardar, ya que se utilizara como referencia en el sistema.</font>
                </font>
            </div>
            <div id="direccion_mx" style="display: none">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Dirección MX</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-5">
                                <?= $form->field($model->dir_obj, 'codigo_search')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div id="error-codigo-postal" class="has-error" style="display: none">
                                <div class="help-block">Codigo postal invalido, verifique nuevamente ó busque la dirección manualmente</div>
                            </div>
                        </div>

                        <?= $form->field($model->dir_obj, 'estado_id')->widget(Select2::classname(), [
                            'language' => 'es',
                            'data' => EsysListaDesplegable::getEstados(),
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'options' => [
                                'placeholder' => 'Selecciona el estado',
                            ],
                            'pluginEvents' => [
                                "change" => "function(){ onEstadoChange() }",
                            ]
                        ]) ?>

                        <?= $form->field($model->dir_obj, 'municipio_id')->widget(Select2::classname(), [
                            'language' => 'es',
                            'data' => $model->dir_obj->estado_id ? EsysListaDesplegable::getMunicipios(['estado_id' => $model->dir_obj->estado_id]) : [],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'options' => [
                                'placeholder' => 'Selecciona el municipio'
                            ],
                        ]) ?>

                        <?= $form->field($model->dir_obj, 'codigo_postal_id')->widget(Select2::classname(), [
                            'language' => 'es',
                            'data' => $model->dir_obj->codigo_postal_id ? EsysDireccionCodigoPostal::getColonia(['codigo_postal' => $model->dir_obj->codigo_search]) : [],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'options' => [
                                'placeholder' => 'Selecciona la colonia'
                            ],
                        ])->label('Colonia') ?>
                    </div>
                </div>
            </div>
            <div id="direccion_usa" style="display: none">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 id="title_dir">Dirección</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-5">
                                <?= $form->field($model->dir_obj, 'codigo_postal_usa')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>
                        <?= $form->field($model->dir_obj, 'estado_usa')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model->dir_obj, 'municipio_usa')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model->dir_obj, 'colonia_usa')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-content">
                    <?= $form->field($model->dir_obj, 'direccion')->textInput(['maxlength' => true]) ?>
                    <div class="row">
                        <div class="col-sm-6">
                            <?= $form->field($model->dir_obj, 'num_ext')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model->dir_obj, 'num_int')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                    <?= $form->field($model->dir_obj, 'referencia')->textArea(['rows' => 6]) ?>
                </div>
            </div>
        </div>
    </div>



    <?php ActiveForm::end(); ?>

</div>




<script type="text/javascript">
    var $inputEstado = $('#esysdireccion-estado_id'),
        $inputMunicipio = $('#esysdireccion-municipio_id'),
        $inputCodigoSearch = $('#esysdireccion-codigo_search'),
        $inputColonia = $('#esysdireccion-codigo_postal_id'),
        $error_codigo = $('#error-codigo-postal'),
        $inputOrigen = $('#cliente-origen'),
        $inputOrigenCountry = $('#cliente-country_id'),
        $btnClienteSave = $('#btnClienteSave'),
        $alertUsa = $('#alert-usa'),
        $panel_direccion_mx = $('#direccion_mx'),
        $panel_direccion_usa = $('#direccion_usa'),

        $modal_cliente_similar = $('#modal-cliente-similar'),
        municipioSelected = null;







    $(document).ready(function() {



        alertWarning();


        $inputCodigoSearch.change(function() {
            $inputColonia.html('');
            $inputEstado.val(null).trigger("change");

            var codigo_search = $inputCodigoSearch.val();

            $.get('<?= Url::to('@web/municipio/codigo-postal-ajax') ?>', {
                'codigo_postal': codigo_search
            }, function(json) {
                if (json.length > 0) {
                    $error_codigo.hide();
                    $inputEstado.val(json[0].estado_id); // Select the option with a value of '1'
                    $inputEstado.trigger('change');
                    municipioSelected = json[0].municipio_id;

                    $.each(json, function(key, value) {
                        $inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
                    });
                } else {
                    municipioSelected = null;
                    $error_codigo.show();
                }

                $inputColonia
                    .val(null)
                    .trigger("change");

            }, 'json');
        });

        $inputOrigenCountry.change(function() {
            alertWarning();
        });

        $('#esysdireccion-codigo_search').on('input', function() {
            // Obtén el valor actual del campo
            var value = $(this).val();

            // Verifica si el valor tiene más de 5 caracteres
            if (value.length > 4) {
                // Llama a una función cuando se escriben más de 5 caracteres
                //performAction(value);
                alertWarningZonaRoja(value);
            }
        });

        // esysdireccion-codigo_postal_usa

        $('#esysdireccion-codigo_postal_usa').on('input', function() {
            // Obtén el valor actual del campo
            var value = $(this).val();

            // Verifica si el valor tiene más de 5 caracteres
            if (value.length > 4) {
                // Llama a una función cuando se escriben más de 5 caracteres
                //performAction(value);
                alertWarningZonaRoja(value);
            }
        });


        $('#cliente-country_id').on('change', function() {
            // Obtén el valor actual de los campos
            let value = $("#esysdireccion-codigo_postal_usa").val();
            let value2 = $("#esysdireccion-codigo_search").val();

            // Verifica si el valor tiene más de 4 caracteres
            if (value.length > 4) {
                // Llama a una función cuando se escriben más de 4 caracteres
                alertWarningZonaRoja(value);
            }

            if (value2.length > 4) {
                // Llama a una función cuando se escriben más de 4 caracteres
                alertWarningZonaRoja(value2);
            }
        });


        //alertWarningZonaRoja('12', '90700');




    });

    $inputMunicipio.change(function() {
        console.log("entro");
        if ($inputEstado.val() && $inputMunicipio.val()) {
            $inputColonia.html('');
            $.get('<?= Url::to('@web/municipio/colonia-ajax') ?>', {
                'estado_id': $inputEstado.val(),
                "municipio_id": $inputMunicipio.val(),
                'codigo_postal': $inputCodigoSearch.val()
            }, function(json) {
                if (json.length > 0) {
                    $.each(json, function(key, value) {
                        $inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
                    });
                } else
                    municipioSelected = null;

                $inputColonia
                    .val(null)
                    .trigger("change");

            }, 'json');
        }
    });

    $btnClienteSave.click(function(event) {
        event.preventDefault();
        let opc = $('#cliente-country_id option:selected').text();
        //console.log(opc);
        let array = opc.split('-').map(item => item.trim().toUpperCase());

        if (array == 'MEX') {
            if (!$inputMunicipio.val() || !$inputEstado.val()) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.warning('El ESTADO y MUNICIPIO son requeridos, verifica tu información');
                return false;
            }
        }
        $btnClienteSave.submit();
    })

    /************************************
    / Estados y municipios
    /***********************************/
    function onEstadoChange() {
        var estado_id = $inputEstado.val();
        municipioSelected = estado_id == 0 ? null : municipioSelected;

        $inputMunicipio.html('');

        $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {
            'estado_id': estado_id
        }, function(json) {
            $.each(json, function(key, value) {
                $inputMunicipio.append("<option value='" + key + "'>" + value + "</option>\n");
            });

            $inputMunicipio.val(municipioSelected); // Select the option with a value of '1'
            $inputMunicipio.trigger('change');

        }, 'json');

    }

    function alertWarningZonaRoja(code) {

        let pais = $("#cliente-country_id").val();
        $.post('<?= Url::to('verifica-zona') ?>', {
            'pais': pais,
            'cp': code
        }, function(response) {
            response = JSON.parse(response);
            console.log(response);

            if (response.code == 202) {
                if (response.isZonaRoja) {
                    $("#alert").show();
                } else {
                    $("#alert").hide();
                }
            }
        });
    }



    var alertWarning = function() {
        let title = $("#title_dir");
        let opc = $('#cliente-country_id option:selected').text();
        title.text('');
        title.text('Dirección  ' + $('#cliente-country_id option:selected').text())
        //console.log(opc);
        array = opc.split('-');
        if (array[1].trim().toUpperCase() != 'MEX') {
            $alertUsa.show();
            $panel_direccion_usa.show();
            $panel_direccion_mx.hide();
        } else {
            $alertUsa.hide();
            $panel_direccion_mx.show();
            $panel_direccion_usa.hide();
        }
    }
</script>