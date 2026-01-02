<?php

use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\models\cliente\Cliente;
use app\assets\BootstrapWizardAsset;
use app\assets\BootstrapValidatorAsset;
use app\assets\BootboxAsset;
use app\models\envio\Envio;
use app\models\esys\EsysListaDesplegable;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysSetting;
use app\models\producto\ProductoDetalle;
use app\models\promocion\Promocion;
use app\models\promocion\PromocionDetalleComplemento;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\esys\EsysDireccionCodigoPostal;
use app\models\producto\Producto;

BootstrapWizardAsset::register($this);
BootstrapValidatorAsset::register($this);
BootboxAsset::register($this);

?>
<style>
    label
    {
        font-size: x-small;
    }
</style>
<div class="operacion-envio-form">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12 eq-box-md eq-no-panel">
                            <!-- Main Form Wizard -->
                            <!--===================================================-->
                            <div id="demo-main-wz">
                                <!--nav-->
                                <ul class="wz-steps wz-icon-bw wz-nav-off text-lg">
                                    <li class="col-xs-3">
                                        <a data-toggle="tab" href="#demo-main-tab1">
                                            <span class="icon-wrap icon-wrap-xs icon-circle bg-dark mar-ver">
                                                <span class="wz-icon icon-txt text-bold">1</span>
                                                <i class="wz-icon-done demo-psi-like"></i>
                                            </span>
                                            <small class="wz-desc box-block text-semibold">Información de envio</small>
                                        </a>
                                    </li>
                                    <li class="col-xs-3">
                                        <a data-toggle="tab" href="#demo-main-tab2">
                                            <span class="icon-wrap icon-wrap-xs icon-circle bg-dark mar-ver">
                                                <span class="wz-icon icon-txt text-bold">2</span>
                                                <i class="wz-icon-done demo-psi-like"></i>
                                            </span>
                                            <small class="wz-desc box-block text-semibold">Emisor / Receptor</small>
                                        </a>
                                    </li>
                                    <li class="col-xs-3">
                                        <a data-toggle="tab" href="#demo-main-tab3">
                                            <span class="icon-wrap icon-wrap-xs icon-circle bg-dark mar-ver">
                                                <span class="wz-icon icon-txt text-bold">3</span>
                                                <i class="wz-icon-done demo-psi-like"></i>
                                            </span>
                                            <small class="wz-desc box-block text-semibold">Paquete</small>
                                        </a>
                                    </li>
                                    <li class="col-xs-3">
                                        <a data-toggle="tab" href="#demo-main-tab4">
                                            <span class="icon-wrap icon-wrap-xs icon-circle bg-dark mar-ver">
                                                <span class="wz-icon icon-txt text-bold">4</span>
                                                <i class="wz-icon-done demo-psi-like"></i>
                                            </span>
                                            <small class="wz-desc box-block text-semibold">Finalizar</small>
                                        </a>
                                    </li>
                                </ul>
                                <!--progress bar-->
                                <div class="progress progress-xs">
                                    <div class="progress-bar progress-bar-primary"></div>
                                </div>
                                <!--form-->
                                <?php $form = ActiveForm::begin(['id' => 'form-envios']) ?>

                                    <?= $form->field($model->envio_detalle, 'envio_detalle_array')->hiddenInput()->label(false) ?>

                                    <?= $form->field($model->enviopromocionComplemento, 'envio_complemento_promocion_array')->hiddenInput()->label(false) ?>

                                    <?= $form->field($model->cobroRembolsoEnvio, 'cobroRembolsoEnvioArray')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'promocion_complemento_id')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'promocion_id')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'codigo_promocional_id')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'codigo_promocional_especial_id')->hiddenInput()->label(false) ?>
                                      <?= $form->field($model, 'sucursal_emisor_id')->hiddenInput(['value' => 7])->label(false) ?>
                                    <?= $form->field($model, 'promocion_detalle_id')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'is_reenvio')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'costo_reenvio')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model->enviopromocion, 'envio_promocion_array')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model->envio_detalle, 'dir_obj_array')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
                                    <div class="panel-body">
                                        <div class="tab-content">
                                            <!--First tab-->
                                            <div id="demo-main-tab1" class="tab-pane">
                                                <div class="row">
                                                    <div class="col-sm-10 col-sm-offset-1">
                                                        <div class="row">
                                                            <div class="col-sm-4 text-center">
                                                                <h3><?= Envio::$origenList[Envio::ORIGEN_USA]  ?></h3>
                                                            </div>
                                                            <div class="col-sm-8">
                                                                <?= $form->field($model, 'tipo_envio')->dropDownList([ Envio::TIPO_ENVIO_TIERRA => "Tierra", Envio::TIPO_ENVIO_LAX => "LAX"]) ?>
                                                            </div>
                                                        </div>
                                                        <h4>Sucursal que recibe</h4>
                                                        <hr>
                                                        <div class="div_sucursal_receptor">
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                <?= $form->field($model, 'sucursal_receptor_names[]')->widget(Select2::classname(),
                                                                    [
                                                                    'language' => 'es',
                                                                        'data' => [],
                                                                        'pluginOptions' => [
                                                                            'allowClear' => true,
                                                                            'minimumInputLength' => 3,
                                                                            'language'   => [
                                                                                'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                            ],
                                                                            'ajax' => [
                                                                                'url'      => Url::to(['sucursales-estado-ajax']),
                                                                                'dataType' => 'json',
                                                                                'cache'    => true,
                                                                                'processResults' => new JsExpression('function(data, params){ clienteEmisor = data; return {results: data} }'),
                                                                            ],
                                                                        ],
                                                                        'options' => [
                                                                            'placeholder' => 'Selecciona la sucursal...',
                                                                            'multiple' => true,
                                                                    ],
                                                                ]) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="content_info_sucursales">

                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row ">
                                                            <div class="totales cobros pull-right " style="margin-top: 5%;">
                                                                <div class="col-sm-6 ">
                                                                    <span  class="label monto">Costo de la libra: </span>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <span class="neto monto"><strong class="precio_libra_envio"> </strong> USD</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!--Second tab-->
                                            <div id="demo-main-tab2" class="tab-pane fade">
                                                <div class="col-sm-6 form_emisor" >
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <?= $form->field($model, 'cliente_emisor_id')->widget(Select2::classname(),
                                                                [
                                                                'language' => 'es',
                                                                    'data' => isset($model->clienteEmisor->id) ? [$model->clienteEmisor->id => $model->clienteEmisor->nombre ." ". $model->clienteEmisor->apellidos] : [],
                                                                    'pluginOptions' => [
                                                                        'allowClear' => true,
                                                                        'minimumInputLength' => 3,
                                                                        'language'   => [
                                                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                        ],
                                                                        'ajax' => [
                                                                            'url'      => Url::to(['cliente-ajax']),
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
                                                        <div class="col-xs-6">
                                                            <?= $form->field($model->cliente_emisor, 'nombre')->textInput(['maxlength' => true, "disabled" => true]) ?>
                                                        </div>
                                                        <div class="col-xs-6">
                                                            <?= $form->field($model->cliente_emisor, 'apellidos')->textInput(['maxlength' => true, "disabled" => true]) ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-xs-6">
                                                            <?= $form->field($model->cliente_emisor, 'telefono')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                        </div>
                                                        <div class="col-xs-6">
                                                            <?= $form->field($model->cliente_emisor, 'telefono_movil')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                        </div>
                                                    </div>
                                                    <a  href="javascript:void(0)" id="link_info-emisor" class="btn-link">Ver más +</a>
                                                    <div class="info-emisor" style="display: none">
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= Html::label("Estado","estado_id") ?>
                                                                <?= Html::textInput('estado_id',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX ? $model->cliente_emisor->dir_obj->estado->singular : $model->cliente_emisor->dir_obj->estado_usa : null,["disabled" => true, 'class' => 'form-control']) ?>

                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= Html::label("Deleg./Mpio.","municipio_id") ?>
                                                                <?= Html::textInput('municipio_id',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX ? $model->cliente_emisor->dir_obj->municipio->singular : $model->cliente_emisor->dir_obj->municipio_usa : null,["disabled" => true, 'class' => 'form-control']) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <?= Html::label("Colonia","colonia_id") ?>
                                                                <?= Html::textInput('colonia_id',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX && isset($model->cliente_emisor->dir_obj->esysDireccionCodigoPostal) ? $model->cliente_emisor->dir_obj->esysDireccionCodigoPostal->colonia: $model->cliente_emisor->dir_obj->colonia_usa : null,["disabled" => true, 'class' => 'form-control']) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <?= Html::label("Direccion","direccion_id") ?>
                                                                <?= Html::textInput('direccion_id',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->dir_obj->direccion : null,["disabled" => true, 'class' => 'form-control']) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6 form_receptor">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <?= $form->field($model, 'cliente_receptor_names[]')->widget(Select2::classname(),
                                                                [
                                                                'language' => 'es',
                                                                    'data' => [],
                                                                    'pluginOptions' => [
                                                                        'allowClear' => true,
                                                                        'minimumInputLength' => 3,
                                                                        'language'   => [
                                                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                        ],
                                                                        'ajax' => [
                                                                            'url'      => Url::to(['cliente-ajax']),
                                                                            'dataType' => 'json',
                                                                            'cache'    => true,
                                                                            'processResults' => new JsExpression('function(data, params){  clienteReceptor = data; return {results: data} }'),
                                                                        ],
                                                                    ],
                                                                    'options' => [
                                                                        'placeholder' => 'Selecciona al cliente...',
                                                                        'multiple' => true,
                                                                    ],
                                                            ]) ?>
                                                        </div>
                                                    </div>
                                                    <div class="content_info_cliente">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-offset-3 col-sm-6">
                                                        <?= Html::button('Aplica reenvío', ['id'=>'btnAplicaReenvio', 'class' =>  'btn btn-lg btn-primary btn-active-info btn-block',"data-toggle" => "button", 'style' => 'margin-top: 20px']) ?>
                                                    </div>
                                                </div>
                                                <!-- ================================== REENVIO =============================-->
                                                <div class="row">
                                                    <div class="col-sm-6 col-sm-offset-3">
                                                        <div class="info-reenvio" style="display: none; box-shadow: 5px 10px 17px #777777;">
                                                            <div class="panel">
                                                                <div class="panel-heading">
                                                                    <h3 class="panel-title">Dirección MX</h3>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <div id="error-add-reenvio" class="has-error" style="display: none">

                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-sm-5">
                                                                            <?= $form->field($model->dir_obj, 'codigo_search')->textInput(['maxlength' => true]) ?>
                                                                        </div>
                                                                        <div id="error-codigo-postal" class="has-error" style="display: none">
                                                                            <div class="help-block">Codigo postal invalido, verifique nuevamente ó busque la dirección manualmente</div>
                                                                        </div>
                                                                    </div>
                                                                    <?= Html::label('Estado', 'envio-estado_id', ['class' => 'control-label']) ?>
                                                                    <?= Select2::widget([
                                                                        'id' => 'envio-estado_id',
                                                                        'name' => 'EsysDireccion[estado_id]',
                                                                        'language' => 'es',
                                                                        'value' => isset($model->dir_obj->estado->id) ?  $model->dir_obj->estado->id  : null,
                                                                        'data' => EsysListaDesplegable::getEstados(),
                                                                        'pluginOptions' => [
                                                                            'allowClear' => true,
                                                                        ],
                                                                        'options' => [
                                                                            'placeholder' => 'Selecciona el estado',
                                                                        ],
                                                                        'pluginEvents' => [
                                                                            "change" => "function(){ onEstadoReenvioChange() }",
                                                                        ]
                                                                    ]) ?>
                                                                    <?= Html::label('Deleg./Mpio.', 'envio-municipio_id', ['class' => 'control-label']) ?>
                                                                    <?= Select2::widget([
                                                                        'id' => 'envio-municipio_id',
                                                                        'name' => 'EsysDireccion[municipio_id]',
                                                                        'language' => 'es',
                                                                        'value' => isset($model->dir_obj->municipio_id) ?  $model->dir_obj->municipio_id  : null,
                                                                        'data' => $model->dir_obj->estado_id? EsysListaDesplegable::getMunicipios(['estado_id' => $model->dir_obj->estado_id]): [],
                                                                        'pluginOptions' => [
                                                                            'allowClear' => true,
                                                                        ],
                                                                        'options' => [
                                                                            'placeholder' => 'Selecciona el municipio'
                                                                        ],
                                                                    ]) ?>
                                                                    <?= Html::label('Colonia', 'envio-codigo_postal_id', ['class' => 'control-label']) ?>
                                                                    <?= Select2::widget([
                                                                        'id' => 'envio-codigo_postal_id',
                                                                        'name' => 'EsysDireccion[codigo_postal_id]',
                                                                        'language' => 'es',
                                                                        'value' => isset($model->dir_obj->codigo_postal_id) ?  $model->dir_obj->codigo_postal_id  : null,
                                                                        'data' => $model->dir_obj->codigo_postal_id ? EsysDireccionCodigoPostal::getColonia(['codigo_postal' => $model->dir_obj->codigo_search]) : [],
                                                                        'pluginOptions' => [
                                                                            'allowClear' => true,
                                                                        ],
                                                                        'options' => [
                                                                            'placeholder' => 'Selecciona la colonia'
                                                                        ],
                                                                    ]) ?>
                                                                    <div class="panel">
                                                                        <div class="panel-body">
                                                                            <?= $form->field($model->dir_obj, 'direccion')->textInput(['maxlength' => true]) ?>
                                                                            <div class="row">
                                                                                <div class="col-sm-6">
                                                                                    <?= $form->field($model->dir_obj, 'num_ext')->textInput(['maxlength' => true]) ?>
                                                                                </div>
                                                                                <div class="col-sm-6">
                                                                                    <?= $form->field($model->dir_obj, 'num_int')->textInput(['maxlength' => true]) ?>
                                                                                </div>
                                                                            </div>
                                                                            <?= $form->field($model->dir_obj, 'referencia')->textInput(['maxlength' => true]) ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <?= Html::button('Agregar reenvío', ['id'=>'btnAddRenvio', 'class' =>  'btn btn-lg btn-info  btn-block','style' => 'margin-top: 20px']) ?>
                                                                        </div>
                                                                    </div>
                                                                    <br>
                                                                    <br>
                                                                    <div class="alert alert-info">
                                                                        <strong>
                                                                            <font style="vertical-align: inherit;">
                                                                                <font style="vertical-align: inherit;">¡Aviso! </font>
                                                                            </font>
                                                                        </strong>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;"> El reenvío tiene un costo extra y es proporcional al peso.</font>
                                                                        </font>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                <div class="row" id='table-content-reenvio' >
                                                    <div class="col-sm-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered invoice-summary">
                                                                <thead>
                                                                    <tr class="bg-trans-dark">
                                                                        <th class="min-col text-center text-uppercase">CP</th>
                                                                        <th class="min-col text-center text-uppercase">Estado</th>
                                                                        <th class="min-col text-center text-uppercase">Municipio</th>
                                                                        <th class="min-col text-center text-uppercase">Colonia</th>
                                                                        <th class="min-col text-center text-uppercase">Dirección</th>
                                                                        <th class="min-col text-center text-uppercase">N° Interior</th>
                                                                        <th class="min-col text-center text-uppercase">N° Exterior</th>
                                                                        <th class="min-col text-center text-uppercase">Referencia</th>
                                                                        <th class="min-col text-center text-uppercase">Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="content_reenvio" style="text-align: center;">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- ======================================================================== -->
                                            </div>

                                            <!--Third tab-->
                                            <div id="demo-main-tab3" class="tab-pane">
                                                <div class="div_promocion_alert" style="display: none">
                                                    <div class="alert alert-purple">
                                                        <strong style="font-size: 16px">Promoción vigente!</strong> Ver detalles de los beneficios. <strong><a  target="_blank"  data-id="0" class="alert-link" id="link_promocion" ></a></strong>
                                                    </div>
                                                </div>
                                                <div class="div_promocion_especial_alert" style="display: none">
                                                    <div class="alert alert-warning">
                                                        <strong style="font-size: 16px"> <i class="fa fa-star"></i> Promoción especial vigente! </strong><p id="promocion_especial_text"></p>
                                                    </div>
                                                </div>
                                                <div id="error-add-paquete" class="has-error" style="display: none">
                                                </div>

                                                <div class="form_paquete well" >
                                                    <div class="row totales cobros pull-right ">
                                                        <div class="col-sm-6">
                                                            <span  class="label monto">Costo de la libra: </span>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <span class="neto monto"><strong class="precio_libra_envio"> </strong> USD</span>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="row">
                                                                <div class="col-xs-6">
                                                                    <?= Html::label('Sucursal que recibe:', 'paquete_sucursal_id') ?>
                                                                    <?= Html::dropDownList('paquete_sucursal_id',null,[],[ 'id' => 'paquete_sucursal_id','class' => 'form-control']) ?>
                                                                </div>
                                                                <div class="col-xs-6">
                                                                    <?= Html::label('Cliente que recibe:', 'paquete_cliente_id') ?>
                                                                    <?= Html::dropDownList('paquete_cliente_id',null,[],[ 'id' => 'paquete_cliente_id','class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-xs-12">
                                                                    <?= Html::label('Reenvios:', 'reenvio_select_id') ?>
                                                                    <?= Html::dropDownList('reenvio_select_id',null,[],[ 'id' => 'reenvio_select_id','class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <?php /* ?>
                                                            <div class="row">
                                                                <div class="col-sm-4">
                                                                    <?= $form->field($model->envio_detalle, 'categoria_id')->dropDownList(EsysListaDesplegable::getItems('categoria_paquete_lax_tierra'), ['prompt' => 'Selecciona la categoria']) ?>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= $form->field($model->envio_detalle, 'producto_id')->dropDownList([], ['prompt' => 'Selecciona el producto']) ?>
                                                                </div>
                                                            </div>
                                                            */?>

                                                            <div class="row">
                                                                <div class="col-xs-12">
                                                                    <?= Html::label('Producto', 'envio-producto_id', ['class' => 'control-label']) ?>

                                                                    <?= Select2::widget([
                                                                        'id' => 'envio-producto_id',
                                                                        'name' => 'Envio[producto_id]',
                                                                        'language' => 'es',

                                                                        'pluginOptions' => [
                                                                            'allowClear' => true,
                                                                            'minimumInputLength' => 3,
                                                                                'language'   => [
                                                                                    'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                                                ],
                                                                                'ajax' => [
                                                                                    'url'      => Url::to(['producto-lax-tierra-ajax']),
                                                                                    'dataType' => 'json',
                                                                                    'cache'    => true,
                                                                                    'processResults' => new JsExpression('function(data, params){ searchProducto = data; return {results: data} }'),
                                                                                ],
                                                                        ],
                                                                        'options' => [
                                                                            'placeholder' => 'Buscar producto'
                                                                        ],
                                                                    ]) ?>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-xs-6">
                                                                    <?= Html::label('Tipo de producto:', 'producto_tipo') ?>
                                                                    <?= Html::dropDownList('producto_tipo',null,Producto::$tipoList,[ 'id' => 'producto_tipo','class' => 'form-control']) ?>
                                                                </div>
                                                                <div class="col-xs-6">
                                                                    <?= Html::label('Impuesto:', 'Impuesto') ?>
                                                                    <div class="input-group mar-btm">
                                                                        <?= Html::input('number', 'producto_impuesto',null,[ 'id' => 'producto_impuesto','class' => 'form-control',"disabled" => true,]) ?>
                                                                        <span class="input-group-addon">%</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-xs-6">
                                                                    <?= $form->field($model->envio_detalle, 'cantidad')->textInput(['type' => 'number']) ?>
                                                                </div>
                                                                <div class="col-xs-6">
                                                                    <?= $form->field($model->envio_detalle, 'cantidad_piezas')->textInput(['type' => 'number']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-xs-12">
                                                                    <?= $form->field($model->envio_detalle, 'unidad_medida_id')->dropDownList([],['prompt' => '', 'disabled' => true]) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-xs-6">
                                                                    <?= $form->field($model->envio_detalle, 'valor_declarado')->textInput(['maxlength' => true]) ?>
                                                                </div>
                                                                <div class="col-xs-6">
                                                                    <?= $form->field($model->envio_detalle, 'peso')->textInput(['maxlength' => true]) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row div_valoracion_paquete" style="display: none;">
                                                                <div class="col-xs-offset-4 col-xs-4">
                                                                    <?= $form->field($model->envio_detalle, 'valoracion_paquete')->textInput(['maxlength' => true]) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-xs-12">
                                                                    <?= $form->field($model->envio_detalle, 'observaciones')->textArea(['maxlength' => true]) ?>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <div class="checkbox">
                                                                        <input id="seguro" class="magic-checkbox" type="checkbox" checked="true">
                                                                        <label for="seguro">Seguro</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-12">

                                                                    <button type="button" id="btnAgregar-paquete" class=" btn btn-block btn-lg  btn-primary"><i class="fa fa-cube"></i> Agregar </button>

                                                                    <!--<div class="row totales cobros">
                                                                        <div class="col-sm-12">
                                                                            <span class="label">Costo de paquete</span>
                                                                            <span class="neto monto">150 USD</span>
                                                                        </div>
                                                                    </div>-->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered invoice-summary">
                                                                <thead>
                                                                    <tr class="bg-trans-dark">
                                                                        <th class="min-col text-center text-uppercase">Producto</th>
                                                                        <th class="min-col text-center text-uppercase">N° de piezas</th>
                                                                        <th class="min-col text-center text-uppercase">Unidad</th>
                                                                        <th class="min-col text-center text-uppercase">V. Declarado</th>
                                                                        <th class="min-col text-center text-uppercase">Impuesto</th>
                                                                        <th class="min-col text-center text-uppercase">Peso</th>
                                                                        <th class="min-col text-center text-uppercase">Seguro</th>
                                                                        <th class="min-col text-center text-uppercase">Reenvio</th>
                                                                        <th class="min-col text-center text-uppercase">C.seguro</th>
                                                                        <th class="min-col text-center text-uppercase">Observación</th>
                                                                        <th class="min-col text-center text-uppercase" style="display: none" id="th_promocion_valor_paquete">Valoración PQ</th>
                                                                        <th class="min-col text-center text-uppercase">Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="content_paquete" style="text-align: center;">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row totales cobros ">
                                                    <div class="col-sm-offset-9 col-sm-3">
                                                        <div class="checkbox">
                                                            <?= Html::checkbox('recoleccion_check', isset($model->is_recoleccion) && $model->is_recoleccion == Envio::RECOLECCION_ON ? true : false, ['id'=>'recoleccion_check','class' => 'magic-checkbox']); ?>
                                                           <label style="font-size: medium;" for="recoleccion_check">Recolección de paquetes</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="row totales cobros ">
                                                            <div class="col-sm-offset-6 col-sm-6">
                                                                <span class="label">Total peso</span>
                                                                <div class="input-group mar-btm">
                                                                    <?= Html::input('text', 'peso_total',isset($model->peso_total) && $model->peso_total ? $model->peso_total : 0,[ 'id' => 'peso_total','class' => 'form-control']) ?>
                                                                    <span class="input-group-addon">lb</span>
                                                                </div>
                                                                <div class="div_peso_reenvio" style="display: none">
                                                                    <span class="label">Peso total de reenvío</span>
                                                                    <div class="input-group mar-btm">
                                                                        <?= Html::input('text', 'peso_reenvio',isset($model->peso_reenvio) && $model->peso_reenvio ? $model->peso_reenvio : 0,[ 'id' => 'peso_reenvio','class' => 'form-control']) ?>
                                                                        <span class="input-group-addon">lb</span>
                                                                    </div>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <?= Html::checkbox('ajuste_manual_check', null, ['id'=>'ajuste_manual_check','class' => 'magic-checkbox']); ?>
                                                                   <label for="ajuste_manual_check">Ajuste V. declarado</label>
                                                               </div>


                                                                <span class="label">Total V. declarado </span>
                                                                <div class="input-group mar-btm">
                                                                    <?= Html::input('text', 'total_v_declarado', 0,[ 'id' => 'total_v_declarado','class' => 'form-control']) ?>
                                                                    <span class="input-group-addon">USD</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!--Fourth tab-->
                                            <div id="demo-main-tab4" class="tab-pane">
                                                <div class="row">
                                                    <div class="col-xs-12">
                                                        <div class="panel panel-primary">
                                                            <div class="panel-body" style="color: black">
                                                                <li class="mar-btn div_seccion_reenvio" style="list-style:none;display: none;">
                                                                    <h4 id="title_panel_complemento">Aplico reenvío</h4>
                                                                    <hr>
                                                                    <div class="mar-btm ">
                                                                        <p>Costo base extra de reenvio por 100 lb: <strong><?= EsysSetting::getPrecioBaseReenvio()  ?> USD</strong></p>
                                                                        <p>Peso :  <strong id="lbl_peso">0</strong></p>
                                                                        <p>Costo de reenvio es proporcional al peso :  <strong id="lbl_costo_reenvio">0</strong></p>
                                                                    </div>
                                                                </li>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">

                                                        <div class="row totales cobros ">
                                                            <div class="col-sm-offset-6 col-sm-6">
                                                                <span class="label">Sub Total</span>
                                                                <div class="input-group mar-btm">
                                                                    <?= Html::input('text', 'subTotal_envio',null,[ 'id' => 'subTotal_envio','class' => 'text-right form-control','readonly'=>true]) ?>
                                                                    <span class="input-group-addon">USD</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row totales cobros ">
                                                            <div class="col-sm-offset-6 col-sm-6">
                                                                <span class="label">Impuesto</span>
                                                                <div class="input-group mar-btm">
                                                                    <?= Html::input('text', 'impuesto_total_envio',null,[ 'id' => 'impuesto_total_envio','class' => 'text-right form-control','readonly' => true]) ?>
                                                                    <span class="input-group-addon">USD</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row totales cobros ">
                                                            <div class="col-sm-offset-6 col-sm-6">
                                                                <span class="label">Seguro</span>
                                                                <div class="input-group mar-btm">
                                                                    <?= Html::input('text', 'seguro_total_envio',null,[ 'id' => 'seguro_total_envio','class' => 'text-right form-control','readonly' => true]) ?>
                                                                    <span class="input-group-addon">USD</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row totales cobros ">
                                                            <div class="col-sm-offset-6 col-sm-6">
                                                                <span class="label">Total</span>
                                                                <div class="input-group mar-btm">
                                                                    <?= Html::input('text', 'total_envio',null,[ 'id' => 'total_envio','class' => 'text-right form-control','readonly'=> true]) ?>
                                                                    <span class="input-group-addon">USD</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <?= $form->field($model, 'comentarios')->textArea(['maxlength' => true]) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--Footer buttons-->
                                    <div class="pull-right pad-rgt mar-btm">
                                        <button type="button" class="previous btn btn-primary">Previous</button>
                                        <button type="button" class="btn btn-purple valida_promocion_envio" style="display: none" >Valida promoción</button>
                                        <button type="button" class="next btn btn-primary">Next</button>
                                        <?= Html::submitButton($model->isNewRecord ? 'Crear envio' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'finish btn btn-success' : ' finish btn btn-primary','disabled' => true, 'id' => 'btnGuardarEnvio']) ?>

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
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_paquete">
            <tr id = "paquete_id_{{paquete_id}}">
                <td ><?= Html::tag('p', "Categoria",["class" => "text-main" , "id"  => "table_categoria_id"]) ?></td>
                <td ><?= Html::input('number', "",false,["class" => "form-control" , "id"  => "table_cantidad","style" => "text-align:center"]) ?></td>
                <td ><?= Html::tag('p', "Unidad de medida ",["class" => "text-main" , "id"  => "table_unidad_medida"]) ?></td>
                <td ><?= Html::input('number',"",false,["class" => "form-control" , "id"  => "table_valor_declarado", "style" => "text-align:center"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table_impuesto_total"]) ?></td>
                <td ><?= Html::input('number',"",false,["class" => "form-control" ,  "style" => "text-align:center","id"  => "table_peso"]) ?></td>
                <td ><?= Html::tag('p', "Seguro",["class" => "text-main" , "id"  => "table_seguro"]) ?></td>
                <td><?= Html::dropDownList('table_reenvio_id', null,[], [ 'id' => 'table_reenvio_id','class' => 'form-control', 'style'=>'width: 200px;', 'prompt'=> 'Selecciona reenvio', 'disabled' => 'true'])?></td>
                <td ><?= Html::tag('p', "Costo del seguro",["class" => "text-main" , "id"  => "table_costo_seguro"]) ?></td>
                <td ><?= Html::tag('p', "Observación",["class" => "text-main" , "id"  => "table_observacion"]) ?></td>
                <td class="tr_valor_paquete" style="display: none;"><?= Html::input('text', "",false,["class" => "form-control table_valor_paquete" , "id"  => "table_valor_paquete", "style" => "display:none; text-align:center"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="display-none">
    <div class="tamplate_info_sucursal">
        <div class="col-sm-3" style="    font-style: oblique" id="sucursal_info_id_{{sucursal_info_id}}">
            <p><strong>Sucursal : </strong><span id="nombre_sucursal_receptor"> </span></p>
            <p><strong>Encargado: </strong><span id="encargado_sucursal_receptor"> </span></p>
            <p><strong>Direccion: </strong><span id="direccion_sucursal_receptor"></span></p>
            <p><strong>Telefono: </strong><span id="telefono_sucursal_receptor"></span></p>
        </div>
    </div>
</div>

<div class="display-none">
    <div class="template_info_cliente">
        <div id ="cliente_info_id_{{cliente_info_id}}">
            <div class="row">
                <div class="col-xs-6">
                    <?= $form->field($model->cliente_receptor, 'nombre')->textInput(['maxlength' => true,"disabled" => true]) ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($model->cliente_receptor, 'apellidos')->textInput(['maxlength' => true,"disabled" => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <?= $form->field($model->cliente_receptor, 'telefono')->textInput(['maxlength' => true,"disabled" => true]) ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($model->cliente_receptor, 'telefono_movil')->textInput(['maxlength' => true,"disabled" => true]) ?>
                </div>
            </div>
            <a  href="javascript:void(0)"   class="btn-link link_info-receptor">Ver más +</a>
            <div class="info-receptor" style="display: none">
                <div class="row">
                    <div class="col-sm-6">
                        <?= Html::label("Estado","estado_id") ?>
                        <?= Html::textInput('estado_id',isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->origen == Cliente::ORIGEN_MX ? $model->cliente_receptor->dir_obj->estado->singular : $model->cliente_receptor->dir_obj->estado_usa : null,["disabled" => true, 'class' => 'form-control']) ?>

                    </div>
                    <div class="col-sm-6">
                        <?= Html::label("Deleg./Mpio.","municipio_id") ?>
                        <?= Html::textInput('municipio_id',isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->origen == Cliente::ORIGEN_MX ? $model->cliente_receptor->dir_obj->municipio->singular : $model->cliente_receptor->dir_obj->municipio_usa : null,["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::label("Colonia","colonia_id") ?>
                        <?= Html::textInput('colonia_id',isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->origen == Cliente::ORIGEN_MX ? $model->cliente_receptor->dir_obj->esysDireccionCodigoPostal->colonia: $model->cliente_receptor->dir_obj->colonia_usa : null,["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::label("Direccion","direccion_id") ?>
                        <?= Html::textInput('direccion_id',isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->dir_obj->direccion : null,["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::label("Referencia","referencia") ?>
                        <?= Html::textInput('referencia',isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->dir_obj->referencia : null,["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_reenvio">
            <tr id = "reenvio_id_{{reenvio_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cp"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_estado"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_municipio"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_colonia"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_direccion"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_n_interior"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_n_exterior"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_referencia"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="fade modal " id="modal-alertReenvio"  tabindex="-1" role="dialog" aria-labelledby="modal-alertReenvio-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> VERIFICA TUS DESTINOS QUE ESTEN CORRECTAMENTE INGRESADOS </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="alert alert-dark">
                                <strong>Aviso!</strong>
                                <li>UN CLIENTE RECEPTOR NO PUEDE RECIBIR PAQUETES A REENVIO Y SUCURSAL</li>
                            </div>
                            <h4>SUCURSALES DESTINO</h4>
                            <table class="table table-bordered invoice-summary">
                                <thead>
                                    <tr class="bg-trans-dark">
                                        <th class="text-center">SUCURSAL</th>
                                        <th class="text-center">ESTADO</th>
                                        <th class="text-center">REENVIO</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center" id="aviso_content_sucursal">
                                </tbody>
                            </table>
                            <h4>CLIENTES DESTINO</h4>
                            <table class="table table-bordered invoice-summary">
                                <thead>
                                    <tr class="bg-trans-dark">
                                        <th class="text-center">NOMBRE</th>
                                        <th class="text-center">APELLIDO</th>
                                        <th class="text-center">TELEFONO</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center" id="aviso_content_cliente">
                                </tbody>
                            </table>
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

<script>

    /*******************************************
    *              Tab 1 Envio
    ********************************************/

    var $sucursal_emisor_id     = $('#envio-sucursal_emisor_id'),
        //$sucursal_receptor_id   = $('#envio-sucursal_receptor_id'),

        $tipo_envio             = $('#envio-tipo_envio'),
        $tamplate_info_sucursal = $('.tamplate_info_sucursal'),
        $template_info_cliente  = $('.template_info_cliente'),
        $content_info_cliente   = $('.content_info_cliente'),
        $sucursal_receptor_id   = $('#envio-sucursal_receptor_names'),
        $codigo_promocional_especial_id = $('#envio-codigo_promocional_especial_id'),
        sucursal_tipo           = JSON.parse('<?= json_encode(Sucursal::$tipoList)  ?>'),
        edit_load_sucursal      = JSON.parse('<?= json_encode($model->sucursal_receptor_names)  ?>'),
        edit_load_cliente       = JSON.parse('<?= json_encode($model->cliente_receptor_names)  ?>'),

        producto_tipo           = JSON.parse('<?= json_encode(ProductoDetalle::$tipoList)  ?>'),
        complementoList         = JSON.parse('<?= json_encode(PromocionDetalleComplemento::$complementoList)  ?>'),
        tipoList                = JSON.parse('<?= json_encode(PromocionDetalleComplemento::$tipoList)  ?>'),
        estadoList              = JSON.parse('<?= json_encode(EsysListaDesplegable::getEstados())  ?>'),
        metodoPagoList          = JSON.parse('<?= json_encode(CobroRembolsoEnvio::$servicioList)  ?>'),
        $inputEnvioDetalleArray = $('#enviodetalle-envio_detalle_array'),
        $inputComplementoArray  = $('#enviocomplementopromocion-envio_complemento_promocion_array'),

        $inputcobroRembolsoEnvioArray = $('#cobrorembolsoenvio-cobrorembolsoenvioarray'),
        $valida_promocion_envio = $('.valida_promocion_envio'),
        $error_add_paquete      = $('#error-add-paquete'),
        $promocion_id           = $("#envio-promocion_id"),
        $promocion_detalle_id   = $("#envio-promocion_detalle_id"),
        $inputEnvio_promocion   = $("#enviopromocion-envio_promocion_array"),
        $envioID                  = $('#envio-id'),

        $error_add_reenvio      = $('#error-add-reenvio'),


        $paquete_sucursal_id    = $("#paquete_sucursal_id"),
        $paquete_cliente_id     = $("#paquete_cliente_id"),
        precio_libra_actual     = 0,

        /*========================================
                    MODULO REENVIO
        ==========================================*/
        renvio_array            = [],
        $inputcosto_reenvio     = $("#envio-costo_reenvio"),
        $btnAplicaReenvio       = $("#btnAplicaReenvio"),
        $btnAddRenvio           = $("#btnAddRenvio"),
        $isAplicaReenvio        = $("#envio-is_reenvio"),
        $content_reenvio        = $(".content_reenvio"),
        $div_seccion_reenvio    = $('.div_seccion_reenvio'),
        $form_reenvio_content   = $('.info-reenvio'),
        $template_reenvio       = $('.template_reenvio'),

        $table_content_reenvio  = $('#table-content-reenvio'),

        $reenvio_select_id      = $('#reenvio_select_id'),
        $div_peso_reenvio       = $('.div_peso_reenvio'),
        $peso_reenvio           = $('#peso_reenvio'),
        $dir_obj_array          = $('#enviodetalle-dir_obj_array'),
        precio_base_reenvio     = <?= EsysSetting::getPrecioBaseReenvio()  ?>,

        $form_esysdireccion_envio = {
            $inputEstado       : $('#envio-estado_id',$form_reenvio_content),
            $inputMunicipio    : $('#envio-municipio_id',$form_reenvio_content),
            $inputColonia      : $('#envio-codigo_postal_id',$form_reenvio_content),
            $inputCodigoSearch : $('#esysdireccion-codigo_search',$form_reenvio_content),
            $inputDireccion    : $('#esysdireccion-direccion',$form_reenvio_content),
            $inputNumeroExt    : $('#esysdireccion-num_ext',$form_reenvio_content),
            $inputNumeroInt    : $('#esysdireccion-num_int',$form_reenvio_content),
            $inputReferencia   : $('#esysdireccion-referencia',$form_reenvio_content),
        };



    var $form_envios             = $('#form-envios'),
        $content_tab             = $('#demo-main-wz'),
        $cliente_emisor          = $('#envio-cliente_emisor_id'),
        $cliente_receptor        = $('#envio-cliente_receptor_names'),
        $form_emisor_content     = $('.form_emisor'),
        $form_receptor_content   = $('.form_receptor'),
        $form_paquete_content    = $('.form_paquete'),
        $template_paquete        = $('.template_paquete'),
        $template_metodo_pago    = $('.template_metodo_pago'),
        $btnAgregarPaquete       =  $('#btnAgregar-paquete'),

        $btnAgregarMetodoPago       =  $('#btnAgregarMetodoPago'),

        $selectProducto          = $('#envio-producto_id'),

        $total_v_declarado       = $('#total_v_declarado'),
        $ajuste_manual_check     = $('#ajuste_manual_check'),

        $descuento_manual_check  =  $('#descuento_manual_check'),
        $div_descuento_manual    =  $('.div_descuento_manual'),
        $div_detalle_descuento   =  $('.div_detalle_descuento'),
        $content_paquete        = $(".content_paquete"),
        $div_valoracion_paquete        = $(".div_valoracion_paquete"),
        $content_metodo_pago    = $(".content_metodo_pago");

        $div_promocion_alert            = $('.div_promocion_alert'),
        $div_promocion_especial_alert   = $('.div_promocion_especial_alert'),
        $div_promocion_especial_info    = $('.div_promocion_especial_info'),
        $promocion_especial_text        = $('#promocion_especial_text'),
        $promocion_especial_text_info   = $('#promocion_especial_text_info'),
        $link_promocion                 = $('#link_promocion');

        /**************************************************/
        /*             HIDE / SHOW INFORMACION DE CLIENTES
        /**************************************************/
    var $is_div_info_emisor   = false;
        $is_div_info_receptor = false;
        $link_info_emisor     = $('#link_info-emisor');
        $link_info_receptor   = $('.link_info-receptor');
        $div_info_emisor      = $('.info-emisor');


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
            $btn_icon  : $('button',$form_emisor_content),

         };


        $form_paquete = {
            $categoria              : $('#enviodetalle-categoria_id', $form_paquete_content),
            $producto               : $('#enviodetalle-producto_id', $form_paquete_content),
            $producto_tipo          : $('#producto_tipo', $form_paquete_content),
            $producto_impuesto      : $('#producto_impuesto', $form_paquete_content),
            $valoracion_paquete     : $('#enviodetalle-valoracion_paquete', $form_paquete_content),
            $cantidad               : $('#enviodetalle-cantidad',$form_paquete_content ),
            $valor_declarado        : $('#enviodetalle-valor_declarado', $form_paquete_content),
            $unidad_medida_id       : $('#enviodetalle-unidad_medida_id', $form_paquete_content),
            $cantidad_piezas        : $('#enviodetalle-cantidad_piezas', $form_paquete_content),
            $peso                   : $('#enviodetalle-peso', $form_paquete_content),
            $observacion            : $('#enviodetalle-observaciones', $form_paquete_content),
            $seguro                 : $('#seguro', $form_paquete_content),

        };






        $form_metodoPago = {
            $metodoPago : $('#cobrorembolsoenvio-metodo_pago'),
            $cantidad   : $('#cobrorembolsoenvio-cantidad'),
        };

        producto_tipo_lax_impuesto = {
            nuevo : <?=  EsysSetting::getImpuestoNewLax()?>,
            usado : <?=  EsysSetting::getImpuestoOldLax()?>,
        };

        producto_tipo_tierra_impuesto = {
            nuevo : <?=  EsysSetting::getImpuestoNewTierra()?>,
            usado : <?=  EsysSetting::getImpuestoOldTierra()?>,
        };

       tipoEnvio = {
                tierra  : <?= Envio::TIPO_ENVIO_TIERRA ?>,
                lax     : <?= Envio::TIPO_ENVIO_LAX ?>,
                mex     : <?= Envio::TIPO_ENVIO_MEX ?>,
        };

        tipoProducto = {
            nuevo : <?= Producto::TIPO_NUEVO ?>,
            usado: <?= Producto::TIPO_USADO ?>,
        };

        isPromocionManual = {
            on : <?= Promocion::IS_MANUAL_ON ?>,
            off: <?= Promocion::IS_MANUAL_OFF ?>,
        };

        tipoComplemento = {
            eleccion : <?= PromocionDetalleComplemento::COMPLEMENTO_ELECCION ?>,
            general  : <?= PromocionDetalleComplemento::COMPLEMENTO_GENERAL ?>,
        };

        is_impuesto_on     = <?= Producto::IS_IMPUESTO_ON ?>;
        paquete_array      = [];
        metodoPago_array   = [];
        clienteReceptor    = [];
        clienteEmisor      = [];
        productoCategoria  = [];
        productoDetalle    = [];
        municipioList      = [];
        promocionDetalle   = [];
        promocionAnexo     = [];
        peso_paquete_array = [];
        promocionComplemento        = [];
        promocionComplementoSelect  = [];
        promocionDetalleSelect = {
            promocionComplemento : [],
            promocionAnexo       : [],
            promocionDetalle     : {},
        };
        sucursalSelect     = [];
        clienteSelect      = [];
        promoEspecial      = [];
        searchProducto     = [];
        selectProducto_array     = {};
        isEmisorCreate     = false;
        isEmisorEdit       = false;
        isCodePromocionalSelect  = false;
        isPromocionDetalleSelect = false;
        isReceptorCreate    = false;
        promocionVigente    = false;
        validaPromocion     = false;
        isGeneral           = false;
        total_envio         = 0;
        peso_total_envio    = 0;

        costo_seguro_select = 0;


</script>

<script>

$(document).on('nifty.ready', function() {
    precio_libra_get();
    load_sucursal_emisor();
    load_promocion();
    init_paquete_list();

    costo_seguro_select =  $tipo_envio.val() == tipoEnvio.tierra  ? <?= EsysSetting::getCobroSeguroTierra()  ?> : <?= EsysSetting::getCobroSeguroLax()  ?> ;


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
                $valida_promocion_envio.hide();
            } else {
                $content_tab.find('.next').show();
                $content_tab.find('.finish').hide().prop('disabled', true);
                $valida_promocion_envio.hide();

                if ($current == 3 && !validaPromocion && promocionVigente /*&& $tipo_envio.val() == tipoEnvio.tierra*/) {
                    $valida_promocion_envio.show();
                    $content_tab.find('.next').show();
                }
                if ($current == 1 )
                    $sucursal_receptor_id.val().length ? $('.next', $content_tab).show() :   $('.next', $content_tab).hide();

                if ($current == 2 ){
                    $cliente_receptor.val().length ? $('.next', $content_tab).show() :   $('.next', $content_tab).hide();
                    $isAplicaReenvio.val() == 10 && renvio_array.length == 0 ? $('.next', $content_tab).hide() :  $('.next', $content_tab).show();

                    if (temp_is_reenvio && $isAplicaReenvio.val() == '' )
                        $btnAplicaReenvio.click();
                    else{
                        if ($isAplicaReenvio.val() == 10 && temp_is_reenvio == false) {
                            $btnAplicaReenvio.click();
                        }
                    }
                }

                if ($current == 3 ) {
                    if (is_validate_envio())
                        $('.next', $content_tab).hide();
                    else
                        $('.next', $content_tab).show();
                }

            }
        },
        onNext: function(){
            isValid = null;
            //$form_envios.bootstrapValidator('validate');


            if(isValid === false)return false;
        }
    });
});

var is_validate_envio = function(){
    $array_item  = [];
    add_reenvio  = false;

    $.each(sucursalSelect,function(key,sucursal){
        if (sucursal.is_reenvio ===  null) {
            $item = {
                "sucursal"   : sucursal.nombre,
                "estado"     : sucursal.estado,
                "estado_id"  : sucursal.estado_id,
                "reenvio"    : null,
            };
            $array_item.push($item);
        }
        if (sucursal.is_reenvio == 10) {
            if (!add_reenvio)
                add_reenvio = true;
        }
    });

    if (add_reenvio) {
        $.each(renvio_array, function(key, reenvio){
            $item = {
                "sucursal"   : "Reenvío",
                "estado"     : reenvio.estado_text,
                "estado_id"  : reenvio.estado_id,
                "reenvio"    : 10,
            };
            $array_item.push($item);
        });
    }

    temp_estado = [];
    $.each($array_item,function(key, item){
        if (item.estado_id != null &&  item.estado_id != ''){
            is_count    = true;


            $.each(temp_estado,function(key, item_search){
                if (item.estado_id ==  item_search && item.reenvio != 10)
                    is_count = false;
            });

            if(is_count)
                temp_estado.push(item.estado_id);
        }
    });

    if (temp_estado.length > clienteSelect.length){
        $("#aviso_content_cliente").html(null);
        $("#aviso_content_sucursal").html(null);
        content_cliente_html = "";
        content_sucursal_html = "";

        $.each(clienteSelect,function(key,cliente){
            content_cliente_html +=  "<tr><td>" + cliente.nombre + "</td><td>" + cliente.apellidos + "</td><td>"+ cliente.telefono_movil + "</td></tr>";

        });

        $.each($array_item,function(key,sucursal){
            content_sucursal_html += "<tr><td>"+ sucursal.sucursal + "</td><td>" + sucursal.estado + "</td><td>" + (sucursal.reenvio == 10 ? 'SI' : 'NO') +"</td></tr>";
        });
        $("#aviso_content_cliente").html(content_cliente_html);
        $("#aviso_content_sucursal").html(content_sucursal_html);
        $('#modal-alertReenvio').modal('show');
        return true;
    }

    return false;
}

/*====================================================
*               BUSCA UN ITEM EN EL ARRAY
*====================================================*/
var search_item  = function(id,list_array, opt = false){
    key_item = false;
    if (!opt) {
        $.each(list_array,function(key,item){
            if(item.id == id )
                key_item =  key;
        });
    }else{
        $.each(list_array,function(key,item){
            if(item.producto_detalle_id == id )
                key_item =  key;
        });
    }
    return key_item;
}

/*===============================================
*           SEARCH PRODUCTO
===============================================*/

$selectProducto.change(function(){
    selectProducto_array = {};
    $form_paquete.$producto_impuesto.val('');
    $div_valoracion_paquete.hide();
    $form_paquete.$unidad_medida_id.html('');

    $.each(searchProducto,function(key,item){
        if ($selectProducto.val() == item.id) {
            selectProducto_array = item;
            var newOption   = new Option(selectProducto_array.unidad_medida, selectProducto_array.id, false, true);
            $form_paquete.$unidad_medida_id.append(newOption);

            $form_paquete.$producto_tipo.trigger('change');
        }
    });
});

/*===============================================
* Limpia valores de un  formulario
*===============================================*/
var clear_form = function($form){
    $.each($form,function($key,$item){
        $item.val(null);
    });
};

$tipo_envio.change(function(){

    costo_seguro_select =  $(this).val() ==  tipoEnvio.tierra ? <?= EsysSetting::getCobroSeguroTierra()  ?> : <?= EsysSetting::getCobroSeguroLax()  ?> ;
    precio_libra_get();
});

/*===============================================
* Ajuste de valor declarado
*===============================================*/
$ajuste_manual_check.change(function(){
    ajuste_valor_declarado();
    render_paquete_template();
});

$total_v_declarado.change(function(){
    ajuste_valor_declarado();
    render_paquete_template();
});

var ajuste_valor_declarado = function()
{
    if ($ajuste_manual_check.prop('checked')) {
        total_v_declarado   = $total_v_declarado.val();

        total_paquetes      = 0;
        $.each(paquete_array, function(key, paquete){
            if(paquete.status == 10){
                total_paquetes = total_paquetes + 1;
            }
        });
        v_ajustado          = total_v_declarado / total_paquetes;

        $.each(paquete_array, function(key, paquete){
            if (paquete.paquete_id) {
                if(paquete.status == 10){
                    paquete.valor_declarado = v_ajustado;
                    paquete.costo_seguro    = paquete.seguro ?  ( costo_seguro_select  * parseFloat(paquete.valor_declarado )) / 100 : 0;
                }
            }
        });
    }
}

/*===============================================
* Habilita/Deshabilita el descuento Manual
*===============================================*/

$descuento_manual_check.change(function(){
    if($(this).prop('checked')) {
        $div_descuento_manual.show();
        $('#total_envio').val(total_envio - parseFloat(($('#descuento_manual').val() ? $('#descuento_manual').val()  : 0 )));

        if ($isAplicaReenvio.val() == 10 ) {
            cal_costo_reenvio();
            new_total_reenvio = parseFloat($('#total_envio').val()) + parseFloat($inputcosto_reenvio.val());
            $('#total_envio').val(new_total_reenvio.toFixed(2));
        }

        $total_promocion.html($('#total_envio').val());
        render_metodo_template();
    }else{
        $div_descuento_manual.hide();
        $('#total_envio').val(total_envio);
        $('#peso_total').trigger('change');
    }


});

$('#descuento_manual').change(function(){
    if($(this).val()){
        $('#total_envio').val( total_envio - parseFloat($(this).val()));

        if ($isAplicaReenvio.val() == 10 ) {
            cal_costo_reenvio();
            new_total_reenvio = parseFloat($('#total_envio').val()) + parseFloat($inputcosto_reenvio.val());
            $('#total_envio').val(new_total_reenvio.toFixed(2));
        }

        $total_promocion.html($('#total_envio').val());
        render_metodo_template();
    }

});


var pesoTotal = function(){
    peso_total_envio = $('#peso_total').val();
    return peso_total_envio;
}

 var precio_libra_get = function(){
     $.get('<?= Url::to(['precio-libra-ajax']) ?>', {'tipo_servicio' : $tipo_envio.val() }, function(json) {
        precio_libra_actual = parseFloat(json);
        $('.precio_libra_envio').html(precio_libra_actual);
   }, 'json');
}


</script>

<?= $this->render('envio_js/seccion_sucursal') ?>
<?= $this->render('envio_js/seccion_emisor_receptor') ?>
<?= $this->render('envio_js/seccion_reenvio') ?>
<?= $this->render('envio_js/seccion_envio') ?>
<?= $this->render('envio_js/seccion_promocion_paquete') ?>
<?= $this->render('envio_js/seccion_metodo_pago') ?>
