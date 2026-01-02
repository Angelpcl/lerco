<?php

use yii\helpers\Html;
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\cliente\Cliente;
use kartik\select2\Select2;
use app\models\esys\EsysDireccionCodigoPostal;
use app\models\esys\EsysListaDesplegable;

?>

<div class="fade modal " id="modal-create-user"  tabindex="-1" role="dialog" aria-labelledby="modal-create-user-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Cliente <strong id="modal-title-cliente"></strong></h4>
            </div>

            <!--Modal body-->
            <?php $formCliente = ActiveForm::begin(['id' => 'form-modal-cliente' ]) ?>

            <?= $formCliente->field($model->cliente, 'id')->hiddenInput()->label(false) ?>

            <div class="modal-body">
                <div class="alert alert-warning alert-menssage" style="display: none">
                    <strong>
                        <font style="vertical-align: inherit;">
                            <font style="vertical-align: inherit;">¡Advertencia! </font>
                        </font>
                    </strong>
                    <font style="vertical-align: inherit;">
                        <font style="vertical-align: inherit;" id="alert-menssage-description"></font>
                    </font>
                </div>
                <div class="row">
                    <div class="col-lg-9">
                        <div class="panel">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-lg-5">

                                        <?= $formCliente->field($model->cliente, 'nombre')->textInput(['maxlength' => true]) ?>

                                        <?= $formCliente->field($model->cliente, 'apellidos')->textInput(['maxlength' => true]) ?>

                                        <?= $formCliente->field($model->cliente, 'telefono')->textInput(['maxlength' => true]) ?>

                                        <?= $formCliente->field($model->cliente, 'telefono_movil')->textInput(['maxlength' => true]) ?>

                                        <?= $formCliente->field($model->cliente, 'origen')->dropDownList(Cliente::$origenList); ?>
                                        <div class="alert alert-warning" id ="alert-usa" style="display: none">
                                            <strong>
                                                <font style="vertical-align: inherit;">
                                                    <font style="vertical-align: inherit;">¡Advertencia! </font>
                                                </font>
                                            </strong>
                                            <font style="vertical-align: inherit;">
                                                <font style="vertical-align: inherit;"> Captura la dirección de forma correcta antes de guardar, ya que se utilizara como referencia en el sistema.</font>
                                            </font>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div id="direccion_mx" style="display: none">
                                            <div class="panel">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title">Dirección MX</h3>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <div class="col-sm-5">
                                                            <?= $formCliente->field($model->cliente->dir_obj, 'codigo_search')->textInput(['maxlength' => true]) ?>
                                                        </div>
                                                        <div id="error-codigo-postal" class="has-error" style="display: none">
                                                            <div class="help-block">Codigo postal invalido, verifique nuevamente ó busque la dirección manualmente</div>
                                                        </div>
                                                    </div>

                                                    <?= $formCliente->field($model->cliente->dir_obj, 'estado_id')->widget(Select2::classname(), [
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

                                                    <?= $formCliente->field($model->cliente->dir_obj, 'municipio_id')->widget(Select2::classname(), [
                                                        'language' => 'es',
                                                        'data' => $model->cliente->dir_obj->estado_id? EsysListaDesplegable::getMunicipios(['estado_id' => $model->cliente->dir_obj->estado_id]): [],
                                                        'pluginOptions' => [
                                                            'allowClear' => true,
                                                        ],
                                                        'options' => [
                                                            'placeholder' => 'Selecciona el municipio'
                                                        ],
                                                    ]) ?>

                                                    <?= $formCliente->field($model->cliente->dir_obj, 'codigo_postal_id')->widget(Select2::classname(), [
                                                        'language' => 'es',
                                                        'data' => $model->cliente->dir_obj->codigo_postal_id ? EsysDireccionCodigoPostal::getColonia(['codigo_postal' => $model->cliente->dir_obj->codigo_search]) : [],
                                                        'pluginOptions' => [
                                                            'allowClear' => true,
                                                        ],
                                                        'options' => [
                                                            'placeholder' => 'Selecciona la colonia'
                                                        ],
                                                    ])->label("Colonia") ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="direccion_usa" style="display: none">
                                            <div class="panel">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title">Dirección USA</h3>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <div class="col-sm-5">
                                                            <?= $formCliente->field($model->cliente->dir_obj, 'codigo_postal_usa')->textInput(['maxlength' => true]) ?>
                                                        </div>
                                                    </div>
                                                    <?= $formCliente->field($model->cliente->dir_obj, 'estado_usa')->textInput(['maxlength' => true]) ?>
                                                    <?= $formCliente->field($model->cliente->dir_obj, 'municipio_usa')->textInput(['maxlength' => true]) ?>
                                                    <?= $formCliente->field($model->cliente->dir_obj, 'colonia_usa')->textInput(['maxlength' => true]) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="panel">
                            <div class="panel-body">
                                   <?= $formCliente->field($model->cliente->dir_obj, 'direccion')->textInput(['maxlength' => true]) ?>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <?= $formCliente->field($model->cliente->dir_obj, 'num_ext')->textInput(['maxlength' => true]) ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <?= $formCliente->field($model->cliente->dir_obj, 'num_int')->textInput(['maxlength' => true]) ?>
                                        </div>
                                    </div>
                                    <?= $formCliente->field($model->cliente->dir_obj, 'referencia')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Crear cliente', ['class' =>  'btn btn-primary', 'id' => 'form-cliente']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
<script type="text/javascript">
var $modal = {
        $error_codigo      : $('#error-codigo-postal'),
        $alertUsa          : $('#alert-usa'),
        $panel_direccion_mx : $('#direccion_mx'),
        $panel_direccion_usa : $('#direccion_usa'),
    },
    $form_cliente_content   = $('#form-modal-cliente');

    $form_esysdireccion     = {
        $inputEstado       : $('#esysdireccion-estado_id',$form_cliente_content),
        $inputMunicipio    : $('#esysdireccion-municipio_id',$form_cliente_content),
        $inputCodigoSearch : $('#esysdireccion-codigo_search',$form_cliente_content),
        $inputColonia      : $('#esysdireccion-codigo_postal_id',$form_cliente_content),
        $inputDireccion    : $('#esysdireccion-direccion',$form_cliente_content),
        $inputNumeroExt    : $('#esysdireccion-num_ext',$form_cliente_content),
        $inputNumeroInt    : $('#esysdireccion-num_int',$form_cliente_content),
        $inputReferencia   : $('#esysdireccion-referencia',$form_cliente_content),
        $inputCodigoPostalUsa   : $('#esysdireccion-codigo_postal_usa',$form_cliente_content),
        $inputEstadoUsa         : $('#esysdireccion-estado_usa',$form_cliente_content),
        $inputMunicipioUsa      : $('#esysdireccion-municipio_usa',$form_cliente_content),
        $inputColoniaUsa        : $('#esysdireccion-colonia_usa',$form_cliente_content),
    };

    $form_cliente = {
        $id         : $('#cliente-id',$form_cliente_content),
        $nombre     : $('#cliente-nombre',$form_cliente_content),
        $apellidos  : $('#cliente-apellidos',$form_cliente_content),
        $inputOrigen: $('#cliente-origen',$form_cliente_content),
        $telefono   : $('#cliente-telefono',$form_cliente_content),
        $telefono_movil : $('#cliente-telefono_movil',$form_cliente_content),

    };

    userInfo = [];



    municipioSelected  = null;

    $(document).ready(function() {

        alertWarning();

        $form_esysdireccion.$inputCodigoSearch.change(function() {
            $form_esysdireccion.$inputColonia.html('');
            $form_esysdireccion.$inputEstado.val(null).trigger("change");

            var codigo_search = $form_esysdireccion.$inputCodigoSearch.val();

            $.get('<?= Url::to('@web/municipio/codigo-postal-ajax') ?>', {'codigo_postal' : codigo_search}, function(json) {
                if(json.length > 0){
                    $modal.$error_codigo.hide();
                    $form_esysdireccion.$inputEstado.val(json[0].estado_id); // Select the option with a value of '1'
                    $form_esysdireccion.$inputEstado.trigger('change');
                    municipioSelected = json[0].municipio_id;

                    $.each(json, function(key, value){
                        $form_esysdireccion.$inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
                    });
                }
                else{
                    municipioSelected  = null;
                    $modal.$error_codigo.show();
                }

                if(userInfo.municipio_id)
                    $form_esysdireccion.$inputColonia.val(userInfo.codigo_postal_id).trigger("change");
                else
                    $form_esysdireccion.$inputColonia.val(null).trigger("change");


            }, 'json');
        });

        $form_esysdireccion.$inputMunicipio.change(function(){
            if ($form_esysdireccion.$inputEstado.val() != 0 && $form_esysdireccion.$inputMunicipio.val() != 0 && $form_esysdireccion.$inputCodigoSearch.val() == "" ) {
                $form_esysdireccion.$inputColonia.html('');
                $.get('<?= Url::to('@web/municipio/colonia-ajax') ?>', {'estado_id' : $form_esysdireccion.$inputEstado.val(), "municipio_id": $form_esysdireccion.$inputMunicipio.val()}, function(json) {
                    if(json.length > 0){
                        $.each(json, function(key, value){
                            $form_esysdireccion.$inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
                        });
                    }
                    else
                        municipioSelected  = null;

                    if(userInfo.municipio_id)
                        $form_esysdireccion.$inputColonia.val(userInfo.codigo_postal_id).trigger("change");
                    else
                        $form_esysdireccion.$inputColonia.val(null).trigger("change");

                }, 'json');
            }
        });

        $form_cliente.$inputOrigen.change(function(){
            alertWarning();
        });

    });

    /************************************
    / Estados y municipios
    /***********************************/
    function onEstadoChange() {
        var estado_id = $form_esysdireccion.$inputEstado.val();
        municipioSelected = estado_id == 0 ? null : municipioSelected;

        $form_esysdireccion.$inputMunicipio.html('');

        if (estado_id ||  municipioSelected) {
            $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : estado_id}, function(json) {
                $.each(json, function(key, value){
                    $form_esysdireccion.$inputMunicipio.append("<option value='" + key + "'>" + value + "</option>\n");
                });

                if( userInfo.municipio_id )
                    $form_esysdireccion.$inputMunicipio.val(userInfo.municipio_id).trigger("change");
                else{
                    $form_esysdireccion.$inputMunicipio.val(municipioSelected); // Select the option with a value of '1'
                    $form_esysdireccion.$inputMunicipio.trigger('change');
                }

            }, 'json');
        }
    }

    var alertWarning = function()
    {
        if($form_cliente.$inputOrigen.val() == 1)
        {
            $modal.$alertUsa.show();
            $modal.$panel_direccion_usa.show();
            $modal.$panel_direccion_mx.hide();
        }
        else
        {
            $modal.$alertUsa.hide();
            $modal.$panel_direccion_mx.show();
            $modal.$panel_direccion_usa.hide();
        }
    }

    $('#form-cliente').click(function(event){
        event.preventDefault();
        $('#alert-menssage-description').html('');
        $.post('<?= Url::to(['/crm/cliente/cliente-create-ajax']) ?>',  $form_cliente_content.serialize() ,function($json){
                if($json.code != 10 ){
                    $('.alert-menssage').show();
                    $('#alert-menssage-description').html($json.message);
                    if ($json.code == 20) {
                        contentListError = '';
                        $.each($json.data,function(key,item){
                            $.each(item,function(key2,item2){
                                contentListError += "<li>"+ item2 +"</li>";
                            });
                        });

                        $('#alert-menssage-description').append(contentListError);
                    }
                }else
                {
                    $('.alert-menssage').hide();

                    if (isReceptorCreate) {

                        $("#modal-create-user").modal("hide");
                        var data            = { id : $json.message.id, text: $json.message.text };
                        clienteReceptor     = [];
                        clienteReceptor[0]  = $json.message;
                        if (!isEmisorEdit) {
                            var newOption       = new Option(data.text, data.id, false, true);
                            $cliente_receptor.append(newOption).trigger('change');
                        }
                    }

                    if (isEmisorCreate) {

                        $("#modal-create-user").modal("hide");
                        var data            = { id : $json.message.id, text: $json.message.text };
                        clienteEmisor       = [];
                        clienteEmisor[0]    = $json.message;
                        var newOption       = new Option(data.text, data.id, false, true);
                        $cliente_emisor.append(newOption).trigger('change');
                    }
                }

        },'json');

    });

</script>
