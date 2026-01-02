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
////use app\models\producto\ProductoDetalle;
use app\models\promocion\Promocion;
use app\models\promocion\PromocionDetalleComplemento;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\esys\EsysDireccionCodigoPostal;
use app\models\producto\Producto;
use app\models\cliente\ClienteCodigoPromocion;

BootstrapWizardAsset::register($this);
BootstrapValidatorAsset::register($this);
BootboxAsset::register($this);

?>
<style>
    .modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>
<div class="operacion-envio-form">
    <div class="row">
        <div class="col-lg-10 offset-sm-1">
            <?php if ( $model->created_user_by->sucursal_id  == null): ?>
                <div class="alert alert-danger">
                    <strong>Aviso!</strong> El usuario no tiene asignada ninguna sucursal por el momento, verifique mas tarde
                </div>
            <?php endif ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información de envio</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12 eq-box-md eq-no-panel">

                            <?php $form = ActiveForm::begin(['id' => 'form-envios']) ?>

                                    <?= $form->field($model->envio_detalle, 'envio_detalle_array')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model->cobroRembolsoEnvio, 'cobroRembolsoEnvioArray')->hiddenInput()->label(false) ?>

                                    <?= $form->field($model, 'subtotal')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'seguro_total')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'total')->hiddenInput()->label(false) ?>

                                    <?= $form->field($model, 'is_reenvio')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'costo_reenvio')->hiddenInput()->label(false) ?>

                                    <?= $form->field($model->envio_detalle, 'dir_obj_array')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
                                    <!-- Main Form Wizard -->
                                    <!--===================================================-->
                                    <div id="wizard">
                                        <h1>Información de envio</h1>
                                        <div class="step-content">
                                            <div class="ibox-content">
                                                <div class="row">
                                                    <div class="col-sm-12 ">
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <h2><?= Envio::$origenList[Envio::ORIGEN_USA]  ?></h2>
                                                            </div>
                                                            <div class="col-sm-8">
                                                                <?= $form->field($model, 'tipo_envio')->dropDownList([ Envio::TIPO_ENVIO_TIERRA => "TIERRA"]) ?>
                                                            </div>
                                                        </div>
                                                        <h4>Sucursal que envía</h4>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-sm-6 text-center">
                                                                <h5>ENVIA: </h5>
                                                                <h2><?= $model->created_user_by->sucursal->nombre  ?>- [ <?= $model->created_user_by->sucursal->clave ?>]</h2>

                                                                <h2 class="product-main-price" id="title-costo-libra">
                                                                    <?= number_format(($model->created_user_by->sucursal->costo_libra ? $model->created_user_by->sucursal->costo_libra : 0 ), 2) ?> DLLs</h2>
                                                                    <strong>Costo de libra</strong>
                                                                <?php /* ?>
                                                                <?= $form->field($model, 'sucursal_emisor_id')->dropDownList($model->created_user_by->getSucursalesTierraLax())->label(false) ?>*/ ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <h4>Sucursal que recibe</h4>
                                                                <div class="div_sucursal_receptor">
                                                                    <?= $form->field($model, 'sucursal_receptor_names[]')->widget(Select2::classname(),
                                                                        [
                                                                        'language' => 'es',
                                                                            'data' => [ 2 => 'CDMX'],
                                                                            'value' => [ 2 ],
                                                                            /*'pluginOptions' => [
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
                                                                            ],*/
                                                                            /*'options' => [
                                                                                'placeholder' => 'Selecciona la sucursal...',
                                                                                //'multiple' => true,
                                                                        ],*/
                                                                    ]) ?>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <?php /* ?>
                                                        <div class="row float-right">
                                                            <div class="totales cobros float-right" style="margin-top: 5%;">
                                                                <div class="col-sm-6 ">
                                                                    <span  class="label monto" style="background-color: #fff;">Costo de la libra: </span>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <span class="neto monto"><strong class="precio_libra_envio"> </strong> USD</span>
                                                                </div>
                                                            </div>
                                                        </div><?php */ ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h1>Quien envia / Quien recibe</h1>
                                        <div class="step-content">
                                            <div class="ibox-content">
                                                 <div class="alert alert-danger div_alert_cliente_reenvio" style="display: none">
                                                    <strong>Aviso!</strong> Debes ingresar una dirección para poder continuar.
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6 form_emisor" >
                                                        <div class="row">
                                                            <div class="col-sm-2" style="   margin-top: 4%;">
                                                                <button  type="button" data-cliente = "Emisor" data-target="#modal-create-user" data-toggle="modal"  class="modal-create btn  btn-circle <?= $model->cliente_emisor->id  ?  'btn-danger' : 'btn-primary' ?>" ><i id="icon_emisor" class="fa <?=  $model->cliente_emisor->id ? 'fa-edit' : 'fa-users'  ?>"></i
                                                                ></button>
                                                            </div>
                                                            <div class="col-sm-10">
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
                                                                <?= $form->field($model->cliente_emisor, 'nombre')->textInput(['maxlength' => true, "disabled" => true]) ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente_emisor, 'apellidos')->textInput(['maxlength' => true, "disabled" => true]) ?>
                                                            </div>
                                                        </div>
                                                        <?= $form->field($model->cliente_emisor, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter e-mail'),"disabled" => true]) ?>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente_emisor, 'telefono')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cliente_emisor, 'telefono_movil')->textInput(['maxlength' => true,"disabled" => true]) ?>
                                                            </div>
                                                        </div>
                                                        <a  href="javascript:void(0)" id="link_info-emisor" class="btn-link">Ver más +</a>
                                                        <div class="info-emisor" style="display: none">
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("Estado","estado_id") ?>
                                                                    <?= Html::textInput('estado_id',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX ? isset($model->cliente_emisor->dir_obj->estado->singular) ? $model->cliente_emisor->dir_obj->estado->singular: null : $model->cliente_emisor->dir_obj->estado_usa : null,["disabled" => true, 'class' => 'form-control']) ?>

                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("Deleg./Mpio.","municipio_id") ?>
                                                                    <?= Html::textInput('municipio_id',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX ? isset($model->cliente_emisor->dir_obj->municipio->singular) ? $model->cliente_emisor->dir_obj->municipio->singular : null : $model->cliente_emisor->dir_obj->municipio_usa : null,["disabled" => true, 'class' => 'form-control']) ?>
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
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("N° Exterior","num_exterior") ?>
                                                                    <?= Html::textInput('num_exterior',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->dir_obj->num_ext : null,["disabled" => true, 'class' => 'form-control']) ?>

                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= Html::label("N° Interior","num_interior") ?>
                                                                    <?= Html::textInput('num_interior',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->dir_obj->num_int : null,["disabled" => true, 'class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-6 form_receptor">
                                                        <div class="row">
                                                            <div class="col-sm-2">
                                                                <button data-cliente = "Receptor" type="button" data-target="#modal-create-user" data-toggle="modal"  class=" modal-create btn  btn-circle btn-primary" data-action="create" ><i id = "icon_receptor" class="fa fa-users"></i
                                                                ></button>
                                                            </div>
                                                            <div class="col-sm-10">
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
                                                                                'url'      => Url::to(['/crm/cliente/cliente-ajax']),
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
                                                </div>
                                                <div class="row">
                                                    <div class="offset-md-3 col-sm-6">
                                                        <?= Html::button('Dirección de entrega', ['id'=>'btnAplicaReenvio', 'class' =>  'btn btn-lg btn-primary btn-active-info btn-block',"data-toggle" => "button", 'style' => 'margin-top: 20px']) ?>
                                                    </div>
                                                </div>
                                                <!-- ================================== REENVIO =============================-->
                                                <div class="row">
                                                    <div class="col-sm-6 offset-md-3">
                                                        <div class="info-reenvio" style="display: none; box-shadow: 5px 10px 17px #777777;">
                                                            <div class="ibox">
                                                                <div class="ibox-title">
                                                                    <h5 >Dirección MX</h5>
                                                                </div>
                                                                <div class="ibox-content">
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
                                                                            <?= $form->field($model->dir_obj, 'referencia')->textInput(['maxlength' => true]) ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <?= Html::button('Agregar dirección', ['id'=>'btnAddRenvio', 'class' =>  'btn btn-lg btn-info  btn-block','style' => 'margin-top: 20px']) ?>
                                                                        </div>
                                                                    </div>
                                                                    <br>
                                                                    <br>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                <div class="row" id="table-content-reenvio">
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
                                        </div>
                                        <h1>Paquete</h1>
                                        <div class="step-content">
                                            <div class="ibox-content form_paquete">
                                                <div id="error-add-paquete" class="has-error alert alert-danger" style="display: none">

                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="row" style="font-size: 14px;background-color: #d6d3d2;padding: 2%;">
                                                            <div class="col-sm-4">
                                                                <?= Html::label('Sucursal que recibe:', 'paquete_sucursal_id') ?>
                                                                <?= Html::dropDownList('paquete_sucursal_id',null,[],[ 'id' => 'paquete_sucursal_id','class' => 'form-control']) ?>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <?= Html::label('Cliente que recibe:', 'paquete_cliente_id') ?>
                                                                <?= Html::dropDownList('paquete_cliente_id',null,[],[ 'id' => 'paquete_cliente_id','class' => 'form-control']) ?>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <?= Html::label('Entrega:', 'reenvio_select_id') ?>
                                                                <?= Html::dropDownList('reenvio_select_id',null,[],[ 'id' => 'reenvio_select_id','class' => 'form-control']) ?>
                                                            </div>
                                                        </div>
                                                        <hr/>
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
                                                            <div class="col-sm-6">
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
                                                                                'url'      => Url::to(['/productos/producto/producto-lax-tierra-ajax']),
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
                                                            <div class="col-sm-4">
                                                             <?= Html::label('Tipo de producto:', 'producto_tipo') ?>
                                                                <?= Html::dropDownList('producto_tipo',null,Producto::$tipoList,[ 'id' => 'producto_tipo','class' => 'form-control']) ?>
                                                            </div>
                                                             <div class="col-sm-2">
                                                                <?= Html::button('<i class="fa fa-plus"></i> Crear producto', [ 'class' =>  'btn btn-small btn-primary', 'style' => 'margin-top: 20px', 'data-target' => "#modal-create-producto", 'data-toggle' =>"modal", "onclick" => "init_producto()"]) ?>
                                                            </div>
                                                        </div>

                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->envio_detalle, 'cantidad')->textInput(['type' => 'number']) ?>

                                                                <?= $form->field($model->envio_detalle, 'valor_declarado')->textInput(['type' => 'number']) ?>

                                                                <?= $form->field($model->envio_detalle, 'peso')->textInput(['type' => 'number',"step" => "0.01"]) ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->envio_detalle, 'observaciones')->textArea(['maxlength' => true, 'rows' => 4]) ?>

                                                                <div class="div_costo_neto_extraordinario" style="display: none">
                                                                    <?= $form->field($model->envio_detalle, 'costo_neto_extraordinario')->textInput(['type' => 'number' ]) ?>
                                                                </div>

                                                                <div class="checkbox text-center" style="margin-top: 35px;">
                                                                    <input id="seguro" class="magic-checkbox" type="checkbox" checked="true">
                                                                    <label for="seguro">Seguro</label>
                                                                </div>
                                                                 <button type="button" id="btnAgregar-paquete" class=" btn btn-block btn-lg  btn-primary"><i class="fa fa-cube"></i> Agregar </button>

                                                            </div>
                                                        </div>



                                                        <div class="row">
                                                            <div class="col-sm-12">


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

                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered invoice-summary">
                                                                <thead>
                                                                    <tr class="bg-trans-dark">
                                                                        <th class="min-col text-center text-uppercase">Producto</th>
                                                                        <th class="min-col text-center text-uppercase">N° de piezas</th>
                                                                        <th class="min-col text-center text-uppercase">V. Declarado</th>
                                                                        <th class="min-col text-center text-uppercase">Peso</th>
                                                                        <th class="min-col text-center text-uppercase">Seguro</th>
                                                                        <th class="min-col text-center text-uppercase">Entrega</th>
                                                                        <th class="min-col text-center text-uppercase">C.seguro</th>
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
                                                    <div class="col-sm-4">
                                                        <span >TOTAL PESO</span>
                                                        <?= Html::input('text', 'peso_total',isset($model->peso_total) && $model->peso_total ? $model->peso_total : 0,[ 'id' => 'peso_total','class' => 'form-control']) ?>
                                                    </div>

                                                    <div class="col-sm-4">
                                                        <span >TOTAL V. DECLARADO </span>
                                                        <?= Html::input('text', 'total_v_declarado', 0,[ 'id' => 'total_v_declarado','class' => 'form-control']) ?>
                                                    </div>
                                                     <div class="col-sm-4">
                                                        <div class="div_peso_reenvio" style="display: none">
                                                            <span >PESO TOTAL DE ENTREGA</span>
                                                            <?= Html::input('text', 'peso_reenvio',isset($model->peso_reenvio) && $model->peso_reenvio ? $model->peso_reenvio : 0,[ 'id' => 'peso_reenvio','class' => 'form-control']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-4 text-center">
                                                        <h2 class="product-main-price"  id="envio-subtotal-label">$0.00</h2>
                                                        <strong>SUBTOTAL</strong>
                                                    </div>
                                                    <div class="col-sm-4 text-center">
                                                        <h2 class="product-main-price" id="envio-seguro_total-label">$0.00</h2>
                                                        <strong>SEGURO</strong>
                                                    </div>
                                                    <div class="col-sm-4 text-center">
                                                        <h2 class="product-main-price" id="envio-total-label">$0.00</h2>
                                                        <strong>TOTAL</strong>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                        <h1>Finalizar</h1>
                                        <div class="step-content">
                                            <div class="ibox-content">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="ibox panel-colorful panel-warning alert-aviso-cobro" style="display: none">
                                                            <div class="ibox-title">
                                                                <h5 >¡Aviso!</h5>
                                                            </div>
                                                            <div class="ibox-content">
                                                                <strong>El total del envío no puede ser inferior al monto total ya cobrado, si requieres una modificación ó un cambio al monto ya cobrado contacta al administrador del sistema ó de lo contrario cancela el envío y captura un nuevo envio con los nuevos valores.</strong>
                                                            </div>
                                                        </div>
                                                        <h3>Metodos de pagos</h3>
                                                        <div class="row" style="border-style: double;padding: 2%;">
                                                            <div class="col-sm-4">
                                                                <?= $form->field($model->cobroRembolsoEnvio, 'metodo_pago')->dropDownList(CobroRembolsoEnvio::$servicioList )->label("&nbsp;") ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?= $form->field($model->cobroRembolsoEnvio, 'cantidad')->textInput(['maxlength' => true]) ?>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                 <button  type="button"class="btn  btn-primary" id="btnAgregarMetodoPago" style="margin-top: 15px;" >Ingresar pago</button>

                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-12" style="margin-top: 5%;">
                                                                <table class="table table-hover table-vcenter" style="background: aliceblue;">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Forma de pago</th>
                                                                            <th class="text-center">Cantidad</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="content_metodo_pago" style="text-align: center;">

                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <td class="text-right" colspan="3">

                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-right" colspan="2"><span class="text-main text-semibold">Total: </span></td>
                                                                            <td><strong id="total_metodo">0 USD</strong></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-right" style="border: none" colspan="2"><span class="text-main text-semibold">Efectivo (Cobro): </span></td>
                                                                            <td><strong id= "pago_metodo_total">0 USD</strong></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-right" style="border: none" colspan="2"><span class="text-main text-semibold">Balance (Por pagar): </span></td>
                                                                            <td class="text-danger"><strong id= "balance_total">0 USD</strong></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-right" style="border: none;" colspan="2"><span class="text-main text-semibold">Cambio: </span></td>
                                                                            <td><strong id="cambio_metodo">0 USD</strong></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
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
                                    <!--===================================================-->
                                    <!-- End of Main Form Wizard -->
                            <?php ActiveForm::end(); ?>
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
                <td ><?= Html::input('number', "",false,["class" => "form-control" , "id"  => "table_cantidad","style" => "text-align:center; width: 80px;"]) ?></td>
                <td ><?= Html::input('number',"",false,["class" => "form-control" , "id"  => "table_valor_declarado", "style" => "text-align:center"]) ?></td>
                <td ><?= Html::input('number',"",false,["class" => "form-control" ,  "style" => "text-align:center;width: 80px; ","id"  => "table_peso"]) ?></td>
                <td ><?= Html::tag('p', "Seguro",["class" => "text-main" , "id"  => "table_seguro"]) ?></td>
                <td><?= Html::dropDownList('table_reenvio_id', null,[], [ 'id' => 'table_reenvio_id','class' => 'form-control', 'style'=>'width: 200px;', 'prompt'=> 'Selecciona direccion', 'disabled' => 'true'])?></td>
                <td ><?= Html::tag('p', "Costo del seguro",["class" => "text-main" , "id"  => "table_costo_seguro"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="display-none">
    <table>
        <tbody class="template_metodo_pago">
            <tr id = "metodo_id_{{metodo_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main text-semibold" , "id"  => "table_metodo_id"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main " , "id"  => "table_metodo_cantidad","style" => "text-align:center"]) ?></td>
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
                <div class="col-sm-2">
                    <button data-cliente = "Receptor"  data-action="Update" type="button" data-target="#modal-create-user" data-toggle="modal"  class="modal-create btn  btn-circle btn-danger" ><i id = "icon_receptor"  class="fa fa-pencil-square-o"></i
                    ></button>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model->cliente_receptor, 'nombre')->textInput(['maxlength' => true,"disabled" => true]) ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model->cliente_receptor, 'apellidos')->textInput(['maxlength' => true,"disabled" => true]) ?>
                </div>
            </div>
            <?= $form->field($model->cliente_receptor, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter e-mail'),"disabled" => true])?>
            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($model->cliente_receptor, 'telefono')->textInput(['maxlength' => true,"disabled" => true]) ?>
                </div>
                <div class="col-sm-6">
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
                    <div class="col-sm-6">
                        <?= Html::label("N° Exterior","num_exterior") ?>
                        <?= Html::textInput('num_exterior',isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->dir_obj->num_ext : null,["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                    <div class="col-sm-6">
                        <?= Html::label("N° Interior","num_interior") ?>
                        <?= Html::textInput('num_interior',isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->dir_obj->num_int : null,["disabled" => true, 'class' => 'form-control']) ?>
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
                    <div class="ibox">
                        <div class="ibox-content">
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


$(function () {
    $wizard             = $("#wizard");


    $wizard.steps({
        onInit : function(event, currentIndex){


            $sucursal_emisor_id     = $('#envio-sucursal_emisor_id'),
            $tipo_envio             = $('#envio-tipo_envio'),
            $tamplate_info_sucursal = $('.tamplate_info_sucursal'),
            $template_info_cliente  = $('.template_info_cliente'),
            $content_info_cliente   = $('.content_info_cliente'),
            $sucursal_receptor_id   = $('#envio-sucursal_receptor_names');
            sucursal_tipo           = JSON.parse('<?= json_encode(Sucursal::$tipoList)  ?>'),
            edit_load_sucursal      = JSON.parse('<?= json_encode($model->sucursal_receptor_names)  ?>'),
            edit_load_cliente       = JSON.parse('<?= json_encode($model->cliente_receptor_names)  ?>'),
            tipoList                = JSON.parse('<?= json_encode(PromocionDetalleComplemento::$tipoList)  ?>'),
            estadoList              = JSON.parse('<?= json_encode(EsysListaDesplegable::getEstados())  ?>'),
            metodoPagoList          = JSON.parse('<?= json_encode(CobroRembolsoEnvio::$servicioList)  ?>'),
            $inputEnvioDetalleArray = $('#enviodetalle-envio_detalle_array'),
            $inputcobroRembolsoEnvioArray = $('#cobrorembolsoenvio-cobrorembolsoenvioarray'),

            $error_add_paquete      = $('#error-add-paquete'),
            $error_add_reenvio      = $('#error-add-reenvio'),
            $envioID                = $('#envio-id'),
            $is_permiso             = <?= Yii::$app->user->can('admin') ? 10 : 0 ?>,


            $paquete_sucursal_id    = $("#paquete_sucursal_id"),
            $paquete_cliente_id     = $("#paquete_cliente_id"),
            precio_libra_actual     = <?= $model->created_user_by->sucursal->costo_libra ? $model->created_user_by->sucursal->costo_libra : 0   ?>,


            renvio_array            = [],

            $btnAplicaReenvio       = $("#btnAplicaReenvio"),
            $btnAddRenvio           = $("#btnAddRenvio"),
            $isAplicaReenvio        = $("#envio-is_reenvio"),
            $content_reenvio        = $(".content_reenvio"),
            $div_seccion_reenvio    = $('.div_seccion_reenvio'),
            $form_reenvio_content   = $('.info-reenvio'),
            $template_reenvio       = $('.template_reenvio'),

            $table_content_reenvio  = $('#table-content-reenvio'),

            $reenvio_select_id      = $('#reenvio_select_id'),

            $peso_reenvio           = $('#peso_reenvio'),
            $dir_obj_array          = $('#enviodetalle-dir_obj_array'),


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



            $form_envios             = $('#form-envios'),

            $cliente_emisor           = $('#envio-cliente_emisor_id'),
            $cliente_receptor         = $('#envio-cliente_receptor_names'),
            $form_emisor_content      = $('.form_emisor'),
            $form_receptor_content    = $('.form_receptor'),
            $form_paquete_content     = $('.form_paquete'),
            $template_paquete         = $('.template_paquete'),
            $template_metodo_pago     = $('.template_metodo_pago'),
            $btnAgregarPaquete        =  $('#btnAgregar-paquete'),

            $btnAgregarMetodoPago     =  $('#btnAgregarMetodoPago'),

            $selectProducto           = $('#envio-producto_id'),

            $div_costo_neto_extraordinario= $('.div_costo_neto_extraordinario'),

            $total_v_declarado        = $('#total_v_declarado'),
            $div_peso_reenvio         = $('.div_peso_reenvio'),

            $div_detalle_descuento    =  $('.div_detalle_descuento'),
            $content_paquete          = $(".content_paquete"),
            $div_valoracion_paquete   = $(".div_valoracion_paquete"),
            $content_metodo_pago    = $(".content_metodo_pago");




            $is_div_info_emisor   = false;
            $is_div_info_receptor = false;
            $link_info_emisor     = $('#link_info-emisor');
            $link_info_receptor   = $('.link_info-receptor');
            $div_info_emisor      = $('.info-emisor');
            $div_alert_cliente_reenvio = $('.div_alert_cliente_reenvio');


            $div_info_emisor.inputText = {
                $estado      : $("input[name = 'estado_id']", $div_info_emisor),
                $municipio   : $("input[name = 'municipio_id']", $div_info_emisor),
                $colonia     : $("input[name = 'colonia_id']", $div_info_emisor),
                $direccion   : $("input[name = 'direccion_id']", $div_info_emisor),
                $num_exterior: $("input[name = 'num_exterior']", $div_info_emisor),
                $num_interior: $("input[name = 'num_interior']", $div_info_emisor),
            };

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
                $valoracion_paquete     : $('#enviodetalle-valoracion_paquete', $form_paquete_content),
                $cantidad               : $('#enviodetalle-cantidad',$form_paquete_content ),
                $valor_declarado        : $('#enviodetalle-valor_declarado', $form_paquete_content),
                $peso                   : $('#enviodetalle-peso', $form_paquete_content),
                $observacion            : $('#enviodetalle-observaciones', $form_paquete_content),
                $seguro                 : $('#seguro', $form_paquete_content),
                $costo_extraordinario   : $('#enviodetalle-costo_neto_extraordinario',$form_paquete_content),
            };

            $form_metodoPago = {
                $metodoPago : $('#cobrorembolsoenvio-metodo_pago'),
                $cantidad   : $('#cobrorembolsoenvio-cantidad'),
            };

            producto_tipo_lax_impuesto = {
                nuevo : 0,
                usado : 0,
            };

            producto_tipo_tierra_impuesto = {
                nuevo : 0,
                usado : 0,
            };

            tipoEnvio = {
                    tierra  : <?= Envio::TIPO_ENVIO_TIERRA ?>,
            };

            tipoProducto = {
                nuevo : <?= Producto::TIPO_NUEVO ?>,
                usado: <?= Producto::TIPO_USADO ?>,
            };



            is_impuesto_on     = <?= Producto::IS_IMPUESTO_ON ?>;
            paquete_array      = [];
            metodoPago_array   = [];
            clienteReceptor    = [];
            clienteEmisor      = [];
            productoCategoria  = [];
            productoDetalle    = [];
            municipioList      = [];


            peso_paquete_array = [];
            sucursalSelect     = [];
            clienteSelect      = [];

            searchProducto     = [];
            searchCategoriaEspecial     = [2696];
            selectProducto_array     = {};
            isEmisorCreate     = false;
            isEmisorEdit       = false;


            isReceptorCreate        = false;
            is_costo_extraordinario = false;
            total_envio         = 0;
            peso_total_envio    = 0;
            costo_seguro_select = 6;

            init_paquete_list();
            temp_is_reenvio     = true;

            $(this).steps('next');

        },

        onContentLoaded : function(event, currentIndex){
            console.log("entro en la carga");
        },

        onStepChanging: function (event, currentIndex, newIndex) { //Para validar antes de hacer un cambio entre secciones para bloquear el cambio solo es necesario return false

            // Forbid next action on "Warning" step if the user is to young
            if (newIndex === 1 )
            {
                $sucursal_receptor_id.trigger('change');

                if (temp_is_reenvio && $isAplicaReenvio.val() == '' )
                        $btnAplicaReenvio.click();
                else{
                    if ($isAplicaReenvio.val() == 10 && temp_is_reenvio == false) {
                        $btnAplicaReenvio.click();
                    }
                }

            }
            if (newIndex === 2) {
                 $sucursal_receptor_id.trigger('change');

                if (temp_is_reenvio && $isAplicaReenvio.val() == '' )
                        $btnAplicaReenvio.click();
                else{
                    if ($isAplicaReenvio.val() == 10 && temp_is_reenvio == false) {
                        $btnAplicaReenvio.click();
                    }
                }

                if ($isAplicaReenvio.val() == 10 && renvio_array.length == 0) {
                    $div_alert_cliente_reenvio.show();
                   return false;
                }else
                    $div_alert_cliente_reenvio.hide();
            }

            if (newIndex === 3 ) {
                if (paquete_array.length == 0) {
                    bootbox.alert("Debes ingresar minimo un paquete al envío !");
                    return false;
                }
            }

            return true;
        },
        onStepChanged: function (event, currentIndex, priorIndex) { //Se dispara después de que el paso ha cambiado.

        },
        onCanceled: function (event) {
        },
        onFinishing: function (event, currentIndex) {
            return true;
        },
        onFinished: function (event, currentIndex) {
            event.preventDefault();
            bootbox.confirm("¿Estas seguro que deseas finalizar el envío?", function(result) {
                if (result) {
                    $form_envios.submit();
                }
            });

        },
    });




    /*===============================================
    *           SEARCH PRODUCTO
    ===============================================*/

    $selectProducto.change(function(){
        selectProducto_array        = {};
        is_costo_extraordinario     = false;
        $div_valoracion_paquete.hide();
        $div_costo_neto_extraordinario.hide();

        $.each(searchProducto,function(key,item){
            if ($selectProducto.val() == item.id) {
                selectProducto_array = item;
                $form_paquete.$producto_tipo.trigger('change');
            }
        });

        $.each(searchCategoriaEspecial,function(key,item){
            if (item == selectProducto_array.categoria_id) {
                is_costo_extraordinario = true;
                $div_costo_neto_extraordinario.show();
            }
        });
    });

    $tipo_envio.change(function(){
        precio_libra_actual =  $(this).val() ==  tipoEnvio.tierra ? "<?= $model->created_user_by->sucursal->costo_libra  ?>" : "<?= $model->created_user_by->sucursal->costo_libra_aire  ?>";
        $('#title-costo-libra').html($(this).val() ==  tipoEnvio.tierra ? "<?= $model->created_user_by->sucursal->costo_libra  ?>" + " DLLs" : "<?= $model->created_user_by->sucursal->costo_libra_aire  ?>" + " DLLs");
    });

    /*===============================================
    * Habilita/Deshabilita el descuento Manual
    *===============================================*/

    var pesoTotal = function(){
        peso_total_envio = $('#peso_total').val();
        return peso_total_envio;
    }

});



/*===============================================
* Limpia valores de un  formulario
*===============================================*/
var clear_form = function($form){
    $.each($form,function($key,$item){
        $item.val(null);
    });
};

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



</script>

<?= $this->render('envio_js/seccion_sucursal') ?>
<?= $this->render('envio_js/seccion_emisor_receptor') ?>
<?= $this->render('envio_js/seccion_reenvio') ?>
<?= $this->render('envio_js/seccion_envio') ?>
<?php //$this->render('envio_js/seccion_promocion_paquete') ?>
<?= $this->render('envio_js/seccion_metodo_pago') ?>
