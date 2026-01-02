<?php

use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\assets\BootstrapWizardAsset;
use app\assets\BootstrapValidatorAsset;
use app\models\envio\Envio;
use app\models\esys\EsysListaDesplegable;

BootstrapWizardAsset::register($this);
BootstrapValidatorAsset::register($this);
?>

<div class="operacion-envio-form">

    <div class="row">
        <div class="col-lg-9">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información de envio</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                          <div class="col-md-12 eq-box-md eq-no-panel">
                                <!-- Main Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-main-wz">
                                    <!--nav-->
                                    <ul class="row wz-step wz-icon-bw wz-nav-off mar-top">
                                        <li class="col-xs-4">
                                            <a data-toggle="tab" href="#demo-main-tab1">
                                                <span class="text-danger"><i class="pli-conference icon-2x"></i></span>
                                                <h5 class="mar-no">Emisor / Receptor</h5>
                                            </a>
                                        </li>

                                        <li class="col-xs-4">
                                            <a data-toggle="tab" href="#demo-main-tab2">
                                                <span class="text-info"><i class="pli-suitcase icon-2x"></i></span>
                                                <h5 class="mar-no">Paquete</h5>
                                            </a>
                                        </li>
                                        <li class="col-xs-4">
                                            <a data-toggle="tab" href="#demo-main-tab4">
                                                <span class="text-success"><i class="pli-paper-plane icon-2x"></i></span>
                                                <h5 class="mar-no">Finalizar</h5>
                                            </a>
                                        </li>
                                    </ul>
                                    <!--progress bar-->
                                    <div class="progress progress-xs">
                                        <div class="progress-bar progress-bar-primary"></div>
                                    </div>
                                    <!--form-->
                                    <?php $form = ActiveForm::begin(['id' => 'form-envios' ]) ?>

                                        <div class="panel-body">
                                            <div class="tab-content">
                                                <!--First tab-->
                                                <div id="demo-main-tab1" class="tab-pane">
                                                   <div class="col-sm-6 form_emisor" >
                                                        <div class="row">
                                                            <div class="col-sm-2" style="   margin-top: 4%;">
                                                                <button  data-cliente = "Emisor" data-target="#modal-create-user" data-toggle="modal"  class="modal-create btn btn-warning btn-circle" ><i class="pli-add-user solid icon-lg"></i
                                                                ></button>
                                                            </div>
                                                            <div class="col-sm-10">
                                                                <?= $form->field($model, 'cliente_emisor_id')->widget(Select2::classname(),
                                                                    [
                                                                    'language' => 'es',
                                                                        'data' => isset($model->cliente_emisor_id->id) ? [$model->encargadoSucursal->id,$model->encargadoSucursal->nombre ." ". $model->encargadoSucursal->apellidos] : [],
                                                                        'pluginOptions' => [
                                                                            'allowClear' => true,
                                                                            'minimumInputLength' => 3,
                                                                            'language'   => [
                                                                                'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                            ],
                                                                            'ajax' => [
                                                                                'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                                                                                'dataType' => 'json',
                                                                                'cache'    => true,
                                                                                'processResults' => new JsExpression('function(data, params){ clienteEmisor = data; return {results: data} }'),
                                                                            ],
                                                                        ],
                                                                        'options' => [
                                                                            'placeholder' => 'Selecciona al usuario...',
                                                                    ],
                                                                ]) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente, 'nombre')->textInput(['maxlength' => true, "disabled" => true]) ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente, 'apellidos')->textInput(['maxlength' => true, "disabled" => true]) ?>
                                                            </div>
                                                        </div>
                                                        <?= $form->field($model->cliente, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter e-mail'),"disabled" => true]) ?>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente, 'telefono')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente, 'telefono_movil')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                            </div>
                                                        </div>
                                                        <a  href="javascript:void(0)" id="link_info-emisor" class="btn-link">Ver más +</a>
                                                        <div class="info-emisor" style="display: none">
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("Estado","estado_id") ?>
                                                                    <?= Html::textInput('estado_id',null,["disabled" => true, 'class' => 'form-control']) ?>

                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("Deleg./Mpio.","municipio_id") ?>
                                                                    <?= Html::textInput('municipio_id',null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <?= Html::label("Colonia","colonia_id") ?>
                                                                    <?= Html::textInput('colonia_id',null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <?= Html::label("Direccion","direccion_id") ?>
                                                                    <?= Html::textInput('direccion_id',null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("N° Exterior","num_exterior") ?>
                                                                    <?= Html::textInput('num_exterior',null,["disabled" => true, 'class' => 'form-control']) ?>

                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("N° Interior","num_interior") ?>
                                                                    <?= Html::textInput('num_interior',null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 form_receptor">
                                                        <div class="row">
                                                            <div class="col-sm-2" style="   margin-top: 4%;">
                                                                <button data-cliente = "Receptor" data-target="#modal-create-user" data-toggle="modal"  class=" modal-create btn btn-primary btn-circle" ><i class="pli-add-user solid icon-lg"></i
                                                                ></button>
                                                            </div>
                                                            <div class="col-sm-10">
                                                                <?= $form->field($model, 'cliente_receptor_id')->widget(Select2::classname(),
                                                                    [
                                                                    'language' => 'es',
                                                                        'data' => isset($model->cliente->id) ? [$model->encargadoSucursal->id,$model->encargadoSucursal->nombre ." ". $model->encargadoSucursal->apellidos] : [],
                                                                        'pluginOptions' => [
                                                                            'allowClear' => true,
                                                                            'minimumInputLength' => 3,
                                                                            'language'   => [
                                                                                'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                            ],
                                                                            'ajax' => [
                                                                                'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                                                                                'dataType' => 'json',
                                                                                'cache'    => true,
                                                                                'processResults' => new JsExpression('function(data, params){  clienteReceptor = data; return {results: data} }'),
                                                                            ],
                                                                        ],
                                                                        'options' => [
                                                                            'placeholder' => 'Selecciona al usuario...',
                                                                        ],
                                                                ]) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente, 'nombre')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente, 'apellidos')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                            </div>
                                                        </div>
                                                        <?= $form->field($model->cliente, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter e-mail'),"disabled" => true]) ?>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente, 'telefono')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente, 'telefono_movil')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                            </div>
                                                        </div>
                                                        <a  href="javascript:void(0)" id="link_info-receptor" class="btn-link">Ver más +</a>
                                                        <div class="info-receptor" style="display: none">
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("Estado","estado_id") ?>
                                                                    <?= Html::textInput('estado_id',null,["disabled" => true, 'class' => 'form-control']) ?>

                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("Deleg./Mpio.","municipio_id") ?>
                                                                    <?= Html::textInput('municipio_id',null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <?= Html::label("Colonia","colonia_id") ?>
                                                                    <?= Html::textInput('colonia_id',null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <?= Html::label("Direccion","direccion_id") ?>
                                                                    <?= Html::textInput('direccion_id',null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("N° Exterior","num_exterior") ?>
                                                                    <?= Html::textInput('num_exterior',null,["disabled" => true, 'class' => 'form-control']) ?>

                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("N° Interior","num_interior") ?>
                                                                    <?= Html::textInput('num_interior',null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab-->
                                                <div id="demo-main-tab2" class="tab-pane fade">
                                                    <div class="form_paquete">
                                                        <div class="row">
                                                            <div class="col-sm-3">
                                                                <?= $form->field($model->envio_detalle, 'categoria_id')->dropDownList(EsysListaDesplegable::getItems('categoria_paquete'), ['prompt' => '']) ?>
                                                            </div>
                                                            <div class="col-sm-3">
                                                                 <?= $form->field($model->envio_detalle, 'cantidad')->textInput(['type' => 'number']) ?>
                                                            </div>
                                                            <div class="col-sm-3">
                                                                <?= $form->field($model->envio_detalle, 'unidad_medida_id')->dropDownList([],['prompt' => '', 'disabled' => true]) ?>
                                                            </div>
                                                            <div class="col-sm-3">
                                                                <?= $form->field($model->envio_detalle, 'valor_declarado')->textInput(['maxlength' => true]) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">

                                                            <div class="col-sm-7">
                                                                <?= $form->field($model->envio_detalle, 'observaciones')->textArea(['maxlength' => true]) ?>
                                                            </div>

                                                            <div class="col-sm-2">
                                                                <div class="checkbox">
                                                                    <input id="seguro" class="magic-checkbox" type="checkbox">
                                                                    <label for="seguro">Seguro</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-3">
                                                                <button type="button" id="btnAgregar-paquete" class=" btn btn-primary">Agregar</button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Categoria</th>
                                                                        <th>N° de elementos</th>
                                                                        <th>Unidad</th>
                                                                        <th>Valor paquete</th>
                                                                        <th>Seguro</th>
                                                                        <th>Observación</th>
                                                                        <th>Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="content_paquete" style="text-align: center;">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab-->
                                                <div id="demo-main-tab4" class="tab-pane">
                                                    <div class="form-group">
                                                        <?= $form->field($model, 'comentarios')->textArea(['maxlength' => true]) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--Footer buttons-->
                                        <div class="pull-right pad-rgt mar-btm">
                                            <button type="button" class="previous btn btn-primary">Previous</button>
                                            <button type="button" class="next btn btn-primary">Next</button>
                                            <?= Html::submitButton($model->isNewRecord ? 'Crear envio' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'finish btn btn-success' : 'btn btn-primary','disabled' => true]) ?>
                                        </div>
                                    <?php ActiveForm::end(); ?>

                                </div>
                                <!--===================================================-->
                                <!-- End of Main Form Wizard -->
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="panel panel-info">
                <!--Panel heading-->
                <div class="panel-heading">
                    <div class="panel-control">
                        <button class="btn btn-default" data-panel="fullscreen">
                            <i class=" pli-full-screen icon-2x icon-max "></i>
                            <i class="icon-min pli-minimize icon-2x "></i>
                        </button>
                    </div>
                    <h3 class="panel-title">Nota</h3>
                </div>

                <!--Panel body-->
                <div class="panel-body">
                    <p> Tiene derecho sin costo extra a:  <p>
                    <li>2 Medicamentos </li>
                    <li>4 Doc. Originales </li>
                    <li>4 Copias de doc  </li>
                    <li>5 Cajetillas de cigarros</li>
                    <br>
                    <p> <strong>Algúnos articulos ó producto pueden generar un costo extra  </strong><p>
                </div>
            </div>
        </div>
    </div>
</div>

 <div class="display-none">
     <table>
        <tbody class="template_paquete">
            <tr id = "paquete_id_{{paquete_id}}">
                <td ><?= Html::tag('p', "Categoria",["class" => "text-main" , "id"  => "table_categoria_id"]) ?></td>
                <td ><?= Html::tag('p', "N° de elementos",["class" => "text-main" , "id"  => "table_cantidad"]) ?></td>
                <td ><?= Html::tag('p', "Unidad de medida ",["class" => "text-main" , "id"  => "table_unidad_medida"]) ?></td>
                <td ><?= Html::tag('p', "Valor",["class" => "text-main" , "id"  => "table_valor_declarado"]) ?></td>
                <td ><?= Html::tag('p', "Seguro",["class" => "text-main" , "id"  => "table_seguro"]) ?></td>
                <td ><?= Html::tag('p', "Observación",["class" => "text-main" , "id"  => "table_observacion"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>
<script>

    var $form_envios        = $('#form-envios'),
        $content_tab        = $('#demo-main-wz'),
        $cliente_emisor     = $('#envio-cliente_emisor_id'),
        $cliente_receptor   = $('#envio-cliente_receptor_id'),
        $form_emisor_content = $('.form_emisor'),
        $form_receptor_content = $('.form_receptor'),
        $form_paquete_content = $('.form_paquete'),
        $template_paquete   = $('.template_paquete'),
        $btnAgregarPaquete  =  $('#btnAgregar-paquete'),
        $content_paquete    = $(".content_paquete"),

        /**************************************************/
        /*             HIDE / SHOW INFORMACION DE CLIENTES
        /**************************************************/
        $is_div_info_emisor   = false;
        $is_div_info_receptor = false;
        $link_info_emisor     = $('#link_info-emisor');
        $link_info_receptor   = $('#link_info-receptor');
        $div_info_emisor      = $('.info-emisor');
        $div_info_receptor    = $('.info-receptor');
        $div_info_receptor.inputText = {
            $estado      : $("input[name = 'estado_id']", $div_info_receptor),
            $municipio   : $("input[name = 'municipio_id']", $div_info_receptor),
            $colonia     : $("input[name = 'colonia_id']", $div_info_receptor),
            $direccion   : $("input[name = 'direccion_id']", $div_info_receptor),
            $num_exterior: $("input[name = 'num_exterior']", $div_info_receptor),
            $num_interior: $("input[name = 'num_interior']", $div_info_receptor),

        };
        $div_info_emisor.inputText = {
            $estado      : $("input[name = 'estado_id']", $div_info_emisor),
            $municipio   : $("input[name = 'municipio_id']", $div_info_emisor),
            $colonia     : $("input[name = 'colonia_id']", $div_info_emisor),
            $direccion   : $("input[name = 'direccion_id']", $div_info_emisor),
            $num_exterior: $("input[name = 'num_exterior']", $div_info_emisor),
            $num_interior: $("input[name = 'num_interior']", $div_info_emisor),

        };
        /**************************************************/

        $form_emisor = {
            $nombre     : $('#cliente-nombre',$form_emisor_content),
            $apellidos  : $('#cliente-apellidos',$form_emisor_content),
            $email      : $('#cliente-email',$form_emisor_content),
            $telefono   : $('#cliente-telefono',$form_emisor_content),
            $telefono_movil : $('#cliente-telefono_movil',$form_emisor_content),

         };

        $form_paquete = {
            $categoria : $('#enviodetalle-categoria_id', $form_paquete_content),
            $cantidad  : $('#enviodetalle-cantidad',$form_paquete_content ),
            $valor_declarado : $('#enviodetalle-valor_declarado', $form_paquete_content),
            $unidad_medida_id : $('#enviodetalle-unidad_medida_id', $form_paquete_content),
            $observacion : $('#enviodetalle-observaciones', $form_paquete_content),
            $seguro : $('#seguro', $form_paquete_content),

         };
        $form_receptor = {
            $nombre     : $('#cliente-nombre',$form_receptor_content),
            $apellidos  : $('#cliente-apellidos',$form_receptor_content),
            $email      : $('#cliente-email',$form_receptor_content),
            $telefono   : $('#cliente-telefono',$form_receptor_content),
            $telefono_movil : $('#cliente-telefono_movil',$form_receptor_content),

         };
        paquete_array      = [];
        clienteReceptor    = [];
        clienteEmisor      = [];
        isEmisorCreate     = false;
        isReceptorCreate   = false;

$(document).on('nifty.ready', function() {

    // MAIN FORM WIZARD
    // =================================================================
    $content_tab.bootstrapWizard({
        tabClass        : 'wz-steps',
        nextSelector    : '.next',
        previousSelector    : '.previous',
        onTabClick: function(tab, navigation, index) {
            return false;
        },
        onInit : function(){
            $content_tab.find('.finish').hide().prop('disabled', true);
        },
        onTabShow: function(tab, navigation, index) {
            var $total = navigation.find('li').length;
            var $current = index+1;
            var $percent = ($current/$total) * 100;
            var wdt = 100/$total;
            var lft = wdt*index;

            $content_tab.find('.progress-bar').css({width:wdt+'%',left:lft+"%", 'position':'relative', 'transition':'all .5s'});

            // If it's the last tab then hide the last button and show the finish instead
            if($current >= $total) {
                $content_tab.find('.next').hide();
                $content_tab.find('.finish').show();
                $content_tab.find('.finish').prop('disabled', false);
            } else {
                $content_tab.find('.next').show();
                $content_tab.find('.finish').hide().prop('disabled', true);
            }
        },
        onNext: function(){
            isValid = null;
            $form_envios.bootstrapValidator('validate');


            //if(isValid === false)return false;
        }
    });
    /*********************************************
    * FORM VALIDATION
    **********************************************/
    var isValid;
    $form_envios.bootstrapValidator(
    {
        excluded: ':disabled',
        feedbackIcons: {
            valid: 'fa fa-check-circle fa-lg text-success',
            invalid: 'fa fa-times-circle fa-lg',
            validating: 'fa fa-refresh'
        },
        fields: {
            "Envio[cliente_emisor_id]": {
                validators: {
                    notEmpty: {
                        message: 'Selecciona un cliente emisor.'
                    }
                }
            },
            "Envio[cliente_receptor_id]": {
                validators: {
                    notEmpty: {
                        message: 'Selecciona un cliente receptor'
                    },
                }
            },
        }
    }).on('success.field.bv', function(e, data) {

        var $parent = data.element.parents('.form-group');
        // Remove the has-success class
        $parent.removeClass('has-success');
        //$parent.find('.form-control-feedback[data-bv-icon-for="' + data.field + '"]').hide();
    }).on('error.form.bv', function(e) {
        isValid = false;
    });

    $cliente_emisor.change(function(){
        if($(this).val() == '' || $(this).val() == null){ clear_form($form_emisor); clear_form($div_info_emisor.inputText); return false; }
        $form_emisor.$nombre.val(clienteEmisor[0].nombre);
        $form_emisor.$apellidos.val(clienteEmisor[0].apellidos);
        $form_emisor.$email.val(clienteEmisor[0].email);
        $form_emisor.$telefono.val(clienteEmisor[0].telefono);
        $form_emisor.$telefono_movil.val(clienteEmisor[0].telefono_movil);

        $div_info_emisor.inputText.$estado.val(
            clienteEmisor[0].origen == 1 ? clienteEmisor[0].estado_usa : clienteEmisor[0].estado);
        $div_info_emisor.inputText.$municipio.val(
            clienteEmisor[0].origen == 1 ? clienteEmisor[0].municipio_usa : clienteEmisor[0].municipio );
        $div_info_emisor.inputText.$colonia.val(
            clienteEmisor[0].origen == 1 ? clienteEmisor[0].colonia_usa : clienteEmisor[0].colonia );

        $div_info_emisor.inputText.$direccion.val(clienteEmisor[0].direccion);
        $div_info_emisor.inputText.$num_exterior.val(clienteEmisor[0].num_ext);
        $div_info_emisor.inputText.$num_interior.val(clienteEmisor[0].num_int);

    });

    $cliente_receptor.change(function(){
        if($(this).val() == '' || $(this).val() == null){ clear_form($form_receptor); clear_form($div_info_receptor.inputText); return false; }
        $form_receptor.$nombre.val(clienteReceptor[0].nombre);
        $form_receptor.$apellidos.val(clienteReceptor[0].apellidos);
        $form_receptor.$email.val(clienteReceptor[0].email);
        $form_receptor.$telefono.val(clienteReceptor[0].telefono);
        $form_receptor.$telefono_movil.val(clienteReceptor[0].telefono_movil);

        $div_info_receptor.inputText.$estado.val(
            clienteReceptor[0].origen == 1 ? clienteReceptor[0].estado_usa : clienteReceptor[0].estado);
        $div_info_receptor.inputText.$municipio.val(
            clienteReceptor[0].origen == 1 ? clienteReceptor[0].municipio_usa : clienteReceptor[0].municipio );
        $div_info_receptor.inputText.$colonia.val(
            clienteReceptor[0].origen == 1 ? clienteReceptor[0].colonia_usa : clienteReceptor[0].colonia );

        $div_info_receptor.inputText.$direccion.val(clienteReceptor[0].direccion);
        $div_info_receptor.inputText.$num_exterior.val(clienteReceptor[0].num_ext);
        $div_info_receptor.inputText.$num_interior.val(clienteReceptor[0].num_int);

    });

    $btnAgregarPaquete.click(function(){

        paquete = {
                "paquete_id": paquete_array.length + 1,
                "categoria_id" : $form_paquete.$categoria.val(),
                "categoria_text" : $('option:selected', $form_paquete.$categoria).text(),
                "cantidad" : $form_paquete.$cantidad.val(),
                "unidad_medida_id" : $form_paquete.$unidad_medida_id.val(),
                "unidad_medida_text" : $('option:selected', $form_paquete.$unidad_medida_id ).text(),
                "valor_declarado": $form_paquete.$valor_declarado.val(),
                "observaciones": $form_paquete.$observacion.val(),
                "seguro"    : $form_paquete.$seguro.prop('checked') ? true : false,
                "origen"    : 1
        };

        paquete_array.push(paquete);

        render_paquete_template();
    });

    var init_paquete_list = function(){

        /**
            aqui leeemos los paquetes cuando se vaya hacer una modificacion
            y los cargamos en el arreglo
        */

        render_paquete_template();
    };

    var render_paquete_template = function()
    {
        $content_paquete.html("");
        $.each(paquete_array, function(key, paquete){
            if (paquete.paquete_id) {
                template_sucursal = $template_paquete.html();
                template_sucursal = template_sucursal.replace("{{paquete_id}}",paquete.paquete_id);
                $content_paquete.append(template_sucursal);

                $tr        =  $("#paquete_id_" + paquete.paquete_id, $content_paquete);
                $("#table_categoria_id",$tr).html(paquete.categoria_text);
                $("#table_cantidad",$tr).html(paquete.cantidad);
                $("#table_unidad_medida",$tr).html(paquete.unidad_medida_text);
                $("#table_seguro",$tr).html(paquete.seguro ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>");
                $("#table_valor_declarado",$tr).html(paquete.valor_declarado);
                $("#table_observacion",$tr).html(paquete.observaciones);

                $tr.append("<td><button class='btn btn-warning btn-circle' onclick='refresh_sucursal(this)'><i class='fa fa-trash'></i></button><td>");
                //pregunta.html(pregunta_item.pregunta);
                /*position.attr("data-sucursal",sucursal.id);*/
                //position.attr("data-pregunta",pregunta_item.pregunta_id);
                //position.attr("onchange","refresh_sucursal(this)");
                //position.val(pregunta_item.position);


            }
        });
    };

    /*====================================================
    *               OPEN MODAL
    *====================================================*/

    $(".modal-create").click(function(){
        $("#modal-title-cliente").html($(this).data("cliente"));

        if ($.trim($(this).data("cliente")) == 'Emisor' ){
            isEmisorCreate = true ;
            isReceptorCreate = false;
        }else if( $.trim($(this).data("cliente")) == 'Receptor'){
            isReceptorCreate = true;
            isEmisorCreate = false;
        }
        clear_form($modal);
        clear_form($form_cliente);
        clear_form($form_esysdireccion);
    });

    /*====================================================
    *          CAMBIO EN SELECT DE CATEGORIA
    *====================================================*/
    $form_paquete.$categoria.change(function(){
        categoria_id = $(this).val();
        if (categoria_id) {
            $.get('<?= Url::to(['/operacion/envio-mex/unidad-medida-ajax']) ?>',{ categoria_id: categoria_id },function(json){
                if (json) {
                    var newOption   = new Option(json.plural, json.id, false, true);
                    $form_paquete.$unidad_medida_id.append(newOption);
                }
            },'json');
        }else
            $form_paquete.$unidad_medida_id.val(null);
    });

    /*====================================================
    *          HIDE / SHOW DIVS CON INFORMACION DE USUARIOS
    *====================================================*/
    $link_info_emisor.click(function(){
        if ($is_div_info_emisor) {
            $(this).html("Ver más + ");
            $div_info_emisor.hide(1000);
            $is_div_info_emisor = false;
        }else{
            $(this).html("Ver menos - ");
            $div_info_emisor.show(1000);
            $is_div_info_emisor = true;
        }
    });

    $link_info_receptor.click(function(){
        if ($is_div_info_receptor) {
            $(this).html("Ver más + ");
            $div_info_receptor.hide(1000);
            $is_div_info_receptor = false;
        }else{
            $(this).html("Ver menos - ");
            $div_info_receptor.show(1000);
            $is_div_info_receptor = true;

        }
    });
});

var refresh_sucursal = function(ele){
    $ele_sucursal_val = $(ele).val();
    $(ele).closest('tr').remove();

    /*$ele_sucursal_id  = $(ele).attr("data-sucursal");
    $ele_pregunta_id  = $(ele).attr("data-pregunta");

    $.each($sucursal_array, function(key, sucursal){
        if (sucursal.id == $ele_sucursal_id ) {
            $.each(sucursal.pregunta, function(key, pregunta_item){
                if (pregunta_item.pregunta_id == $ele_pregunta_id) {
                    pregunta_item.position = $ele_sucursal_val;
                }
            });
        }
    });*/
  //  elem.$inputSucursal.val(JSON.stringify($sucursal_array));
};


var clear_form = function($form){
    $.each($form,function($key,$item){
        $item.val(null);
    });
};

</script>
