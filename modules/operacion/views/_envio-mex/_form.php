<?php

use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\models\envio\Envio;
use app\models\cliente\Cliente;
use app\assets\BootboxAsset;
use app\assets\BootstrapWizardAsset;
use app\assets\BootstrapValidatorAsset;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\esys\EsysListaDesplegable;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysSetting;
use app\models\producto\ProductoDetalle;
use app\models\promocion\Promocion;
use app\models\promocion\PromocionDetalleComplemento;

BootstrapWizardAsset::register($this);
BootstrapValidatorAsset::register($this);
BootboxAsset::register($this);

?>

<div class="operacion-envio-form">
    <div class="row">
        <div class="col-lg-10 offset-sm-1">
            <?php if ($model->created_user_by->sucursal_id  == null): ?>
                <div class="alert alert-danger">
                    <strong>Aviso!</strong> El usuario no tiene asignada ninguna sucursal por el momento, verifique mas tarde
                </div>
            <?php else: ?>
                <?php if ($model->created_user_by->sucursal->origen == Sucursal::ORIGEN_USA ): ?>
                    <div class="alert alert-danger">
                        <strong>Aviso!</strong> La sucursal asignada no corresponde al origen (MX), VERIFICA CON EL ADMINISTRADOR.
                    </div>
                <?php endif ?>
            <?php endif ?>



            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información de envio</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12 eq-box-md eq-no-panel">
                            <!-- Main Form Wizard -->
                            <!--===================================================-->

                                <?php $form = ActiveForm::begin(['id' => 'form-envios']) ?>

                                    <?= $form->field($model->envio_detalle, 'envio_detalle_array')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'is_reenvio')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'promocion_complemento_id')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'impuesto')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'seguro_total')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model->cobroRembolsoEnvio, 'cobroRembolsoEnvioArray')->hiddenInput()->label(false) ?>
                                    <?php //$form->field($model, 'promocion_id')->hiddenInput()->label(false) ?>
                                    <?php // $form->field($model, 'promocion_detalle_id')->hiddenInput()->label(false) ?>
                                    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
                                    <div id="wizard">
                                        <h1>Información de envio</h1>
                                        <div class="step-content">
                                            <div class="ibox-content">
                                                <div class="row">
                                                    <div class="col-sm-10 col-sm-offset-1">
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <h2><?= Envio::$origenList[Envio::ORIGEN_MX]  ?></h2>
                                                            </div>
                                                            <div class="col-sm-8">
                                                                <?= $form->field($model, 'tipo_envio')->dropDownList([ Envio::TIPO_ENVIO_MEX => "Mex"]) ?>
                                                            </div>
                                                        </div>
                                                        <h4>Sucursal que envía</h4>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-sm-6 text-center">
                                                                <h5>ENVIA: </h5>
                                                                <h2><?= $model->created_user_by->sucursal->nombre  ?>- [ <?= $model->created_user_by->sucursal->clave ?>]</h2>
                                                                <?php /* ?>
                                                                <?= $form->field($model, 'sucursal_emisor_id')->dropDownList($model->created_user_by->getSucursalesTierraLax())->label(false) ?>*/ ?>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="div_sucursal_receptor">
                                                                <h4>Sucursal que recibe</h4>
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
                                                                                    'url'      => Url::to(['sucursales-usa-ajax']),
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
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h1>Emisor / Receptor</h1>
                                        <div class="step-content">
                                            <div class="ibox-content">
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
                                                                    <?= Html::textInput('colonia_id',isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX ? isset($model->cliente_emisor->dir_obj->esysDireccionCodigoPostal->colonia) ? $model->cliente_emisor->dir_obj->esysDireccionCodigoPostal->colonia : '' : $model->cliente_emisor->dir_obj->colonia_usa : null,["disabled" => true, 'class' => 'form-control']) ?>
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
                                                        <?= Html::button('Aplica reenvío', ['id'=>'btnAplicaReenvio', 'class' =>  'btn btn-lg btn-primary btn-active-info btn-block',"data-toggle" => "button", 'style' => 'margin-top: 20px']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h1>Paquete</h1>
                                        <div class="step-content">
                                            <div class="ibox-content">
                                                <div id="error-add-paquete" class="has-error" style="display: none">

                                                </div>
                                                <div class="form_paquete well" style=" margin: 5%;">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <?= Html::label('Sucursal que recibe:', 'paquete_sucursal_id') ?>
                                                                    <?= Html::dropDownList('paquete_sucursal_id',null,[],[ 'id' => 'paquete_sucursal_id','class' => 'form-control']) ?>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= Html::label('Cliente que recibe:', 'paquete_cliente_id') ?>
                                                                    <?= Html::dropDownList('paquete_cliente_id',null,[],[ 'id' => 'paquete_cliente_id','class' => 'form-control']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-4">
                                                                    <?= $form->field($model->envio_detalle, 'categoria_id')->dropDownList(EsysListaDesplegable::getItems('categoria_paquete_mex'), ['prompt' => 'Selecciona la categoria']) ?>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= $form->field($model->envio_detalle, 'producto_id')->dropDownList([], ['prompt' => 'Selecciona el producto']) ?>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <?= Html::label('Costo extra:', 'producto_costo_extra') ?>
                                                                     <div class="input-group mar-btm">
                                                                        <?= Html::input('number', 'producto_costo_extra',null,[ 'id' => 'producto_costo_extra','class' => 'form-control',"disabled" => true,]) ?>
                                                                        <span class="input-group-addon">USD</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <div class="alert alert-primary alert-categoria" style="display: none" >
                                                                        <p>La categoria seleccionada ya tiene un costo extra : </p> <div class="content_info"></div>
                                                                    </div>
                                                                    <div class="alert alert-info" id="producto_tipo" style="display: none">

                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>

                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <?= $form->field($model->envio_detalle, 'cantidad')->textInput(['type' => 'number']) ?>

                                                                    <?= $form->field($model->envio_detalle, 'valor_declarado')->textInput(['maxlength' => true, 'disabled'=> true]) ?>


                                                                    <?= $form->field($model->envio_detalle, 'unidad_medida_id')->dropDownList([],['prompt' => '', 'disabled' => true]) ?>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <?= $form->field($model->envio_detalle, 'observaciones')->textArea(['maxlength' => true]) ?>

                                                                     <div class="checkbox text-center" style="margin-top: 35px;">
                                                                        <input id="seguro" class="magic-checkbox" type="checkbox" >
                                                                        <label for="seguro">Aseguranza</label>
                                                                    </div>

                                                                     <button type="button" id="btnAgregar-paquete" class=" btn btn-block btn-lg btn-primary "><i class="fa fa-cube"></i> Agregar</button>

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
                                                                        <th class="min-col text-center text-uppercase">Categoria</th>
                                                                        <th class="min-col text-center text-uppercase">Producto</th>
                                                                        <th class="min-col text-center text-uppercase">N° de piezas</th>
                                                                        <th class="min-col text-center text-uppercase">Unidad</th>
                                                                        <th class="min-col text-center text-uppercase">Costo extra</th>
                                                                        <th class="min-col text-center text-uppercase">Seguro</th>
                                                                        <th class="min-col text-center text-uppercase">Valor declarado</th>
                                                                        <th class="min-col text-center text-uppercase">Costo seguro</th>
                                                                        <th class="min-col text-center text-uppercase">Observación</th>
                                                                        <th class="min-col text-center text-uppercase">Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="content_paquete" style="text-align: center;">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>

                                                <div class="row">
                                                    <div class="col-sm-4 text-center">
                                                        <h2 class="product-main-price"  id="envio-impuesto-label">$0.00</h2>
                                                        <strong>COSTO EXTRA</strong>
                                                    </div>
                                                    <div class="col-sm-4 text-center">
                                                        <h2 class="product-main-price" id="envio-seguro_total-label">$0.00</h2>
                                                        <strong>SEGURO</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h1>Finalizar</h1>
                                        <div class="step-content">
                                            <div class="ibox-content">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <h3>¿ Deseas ingresar un pago ?</h3>
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
                                                                            <td class="text-right" style="border: none" colspan="2"><span class="text-main text-semibold">Efectivo (Cobro): </span></td>
                                                                            <td><strong id= "pago_metodo_total">0 USD</strong></td>
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
                                <?php ActiveForm::end(); ?>
                            <!--===================================================-->
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
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_categoria_id"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_producto_text"]) ?></td>
                <td ><?= Html::input('number', "",false,["class" => "form-control" , "id"  => "table_cantidad","style" => "text-align:center"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_unidad_medida"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_costo_extra"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_seguro"]) ?></td>
                <td ><?= Html::input('number', "",false,["class" => "form-control" , "id"  => "table_valor_declarado","style" => "text-align:center"]) ?></td>
                <td ><?= Html::tag('p', "",["class" => "text-main" , "id"  => "table_costo_seguro"]) ?></td>
                <td ><?= Html::tag('p', "Observación",["class" => "text-main" , "id"  => "table_observacion"]) ?></td>
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
<script>

$(function () {
    $wizard             = $("#wizard");
    $wizard.steps({
        onInit : function(event, currentIndex){

            $sucursal_emisor_id     = $('#envio-sucursal_emisor_id'),
            //$sucursal_receptor_id   = $('#envio-sucursal_receptor_id'),
            $tipo_envio             = $('#envio-tipo_envio'),
            $tamplate_info_sucursal = $('.tamplate_info_sucursal'),
            $template_info_cliente  = $('.template_info_cliente'),
            $content_info_cliente   = $('.content_info_cliente'),
            $sucursal_receptor_id   = $('#envio-sucursal_receptor_names'),
            $inputcobroRembolsoEnvioArray = $('#cobrorembolsoenvio-cobrorembolsoenvioarray'),
            sucursal_tipo           = JSON.parse('<?= json_encode(Sucursal::$tipoList)  ?>'),
            edit_load_sucursal      = JSON.parse('<?= json_encode($model->sucursal_receptor_names)  ?>'),
            edit_load_cliente       = JSON.parse('<?= json_encode($model->cliente_receptor_names)  ?>'),
            producto_tipo           = JSON.parse('<?= json_encode(ProductoDetalle::$tipoList)  ?>'),
            complementoList         = JSON.parse('<?= json_encode(PromocionDetalleComplemento::$complementoList)  ?>'),
            tipoList                = JSON.parse('<?= json_encode(PromocionDetalleComplemento::$tipoList)  ?>'),
            estadoList                  = JSON.parse('<?= json_encode(EsysListaDesplegable::getEstados())  ?>'),
            metodoPagoList              = JSON.parse('<?= json_encode(CobroRembolsoEnvio::$servicioList)  ?>'),
            $inputEnvioDetalleArray     = $('#enviodetalle-envio_detalle_array'),
            $error_add_paquete          = $('#error-add-paquete'),
            $paquete_sucursal_id        = $("#paquete_sucursal_id"),
            $paquete_cliente_id         = $("#paquete_cliente_id"),
            $btnAplicaReenvio           = $("#btnAplicaReenvio"),
            $isAplicaReenvio            = $("#envio-is_reenvio"),
            $envioID                    = $('#envio-id'),
            $template_metodo_pago       = $('.template_metodo_pago'),
            $content_metodo_pago        = $(".content_metodo_pago");
            $btnAgregarMetodoPago       =  $('#btnAgregarMetodoPago');

            $form_envios             = $('#form-envios'),
            $content_tab             = $('#demo-main-wz'),
            $cliente_emisor          = $('#envio-cliente_emisor_id'),
            $cliente_receptor        = $('#envio-cliente_receptor_names'),
            $form_emisor_content     = $('.form_emisor'),
            $form_receptor_content   = $('.form_receptor'),
            $form_paquete_content    = $('.form_paquete'),
            $template_paquete        = $('.template_paquete'),
            $btnAgregarPaquete       =  $('#btnAgregar-paquete'),
            $alertCategoria         = $('.alert-categoria'),
            $content_paquete         = $(".content_paquete"),

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
                $referencia  : $("input[name = 'referencia']", $div_info_receptor),

            };
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
                $categoria       : $('#enviodetalle-categoria_id', $form_paquete_content),
                $producto        : $('#enviodetalle-producto_id', $form_paquete_content),
                $producto_tipo  : $('#producto_tipo', $form_paquete_content),
                $producto_costo_extra  : $('#producto_costo_extra', $form_paquete_content),
                $cantidad        : $('#enviodetalle-cantidad',$form_paquete_content ),
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
                $btn_icon  : $('button',$form_receptor_content),

            };

            $form_metodoPago = {
                $metodoPago : $('#cobrorembolsoenvio-metodo_pago'),
                $cantidad   : $('#cobrorembolsoenvio-cantidad'),
            };

            paquete_array      = [];
            clienteReceptor    = [];
            clienteEmisor      = [];
            metodoPago_array   = [];
            productoCategoria  = [];
            productoDetalle    = {};
            municipioList      = [];
            categoriaList       = [];
            sucursalSelect     = [];
            clienteSelect      = [];
            isEmisorCreate     = false;
            isReceptorCreate   = false;

            //load_sucursal_emisor();
            //load_promocion();
            load_categoria();
            init_paquete_list();
            render_paquete_template();

        },

        onContentLoaded : function(event, currentIndex){
            console.log("entro en la carga");
        },

        onStepChanging: function (event, currentIndex, newIndex) { //Para validar antes de hacer un cambio entre secciones para bloquear el cambio solo es necesario return false
            console.log("entroooo");
         return true;
        },
        onStepChanged: function (event, currentIndex, priorIndex) { //Se dispara después de que el paso ha cambiado.
            console.log("entroooo2");
        },
        onCanceled: function (event) {
            console.log("entroooo3");
        },
        onFinishing: function (event, currentIndex) {
            console.log("entroooo4");
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

    })
});


$(function () {

    /*===============================================
    * Muestra la seccion de reenvio y adapta el diseño
    *===============================================*/

    $btnAplicaReenvio.click(function(){

        if ($isAplicaReenvio.val() == 10){
            $isAplicaReenvio.val(null);
        }
        else{
            $isAplicaReenvio.val(10);
        }
    });

    /*============================================================================
    *                           TAB EMISOR / RECEPTOR
    *=============================================================================*/

    /*=================================
    *       CARGA DATOS EMISOR
    *==================================*/
    $cliente_emisor.change(function(){

        if($(this).val() == '' || $(this).val() == null){
            $form_emisor.$btn_icon.removeClass('btn-danger').addClass('btn-primary');
            $('#icon_emisor').removeClass('pli-pencil').addClass('pli-add-user');
        }else{
            $form_emisor.$btn_icon.removeClass('btn-primary').addClass('btn-danger');
            $('#icon_emisor').removeClass('pli-pencil').addClass('pli-add-user');
        }

        if($(this).val() == '' || $(this).val() == null){ clear_form($form_emisor); clear_form($div_info_emisor.inputText); return false; }
        $('#icon_emisor').removeClass('pli-add-user').addClass('pli-pencil');
        $form_emisor.$btn_icon.removeClass('btn-primary').addClass('btn-danger');

        key = search_item($(this).val(),clienteEmisor);

        $form_emisor.$nombre.val(clienteEmisor[key].nombre);
        $form_emisor.$apellidos.val(clienteEmisor[key].apellidos);
        $form_emisor.$email.val(clienteEmisor[key].email);
        $form_emisor.$telefono.val(clienteEmisor[key].telefono);
        $form_emisor.$telefono_movil.val(clienteEmisor[key].telefono_movil);

        $div_info_emisor.inputText.$estado.val(
            clienteEmisor[key].origen == 1 ? clienteEmisor[key].estado_usa : estadoList[clienteEmisor[key].estado_id]);

        $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : clienteEmisor[key].estado_id}, function(json) {
            municipioList = json;
            $div_info_emisor.inputText.$municipio.val(
            clienteEmisor[key].origen == 1 ? clienteEmisor[key].municipio_usa : municipioList[clienteEmisor[key].municipio_id]);
        }, 'json');

        $div_info_emisor.inputText.$colonia.val(
            clienteEmisor[key].origen == 1 ? clienteEmisor[key].colonia_usa : clienteEmisor[key].colonia );

        $div_info_emisor.inputText.$direccion.val(clienteEmisor[key].direccion);
        $div_info_emisor.inputText.$num_exterior.val(clienteEmisor[key].num_ext);
        $div_info_emisor.inputText.$num_interior.val(clienteEmisor[key].num_int);

    });

    /*=====================================
    *       CARGA DATOS RECEPTOR
    *======================================*/
    $cliente_receptor.change(function(){

        $content_info_cliente.html('');
        if ($(this).val().length > 0 ) {
            clienteSelect = [];
            for (var i = 0; i < $(this).val().length; i++) {
                $.get('<?= Url::to(['cliente-info-ajax']) ?>', { q  : $(this).val()[i] },function(json){
                  if (json) {

                    clienteSelect.push(json);

                    template_info_cliente = $template_info_cliente.html();
                    template_info_cliente = template_info_cliente.replace('{{cliente_info_id}}', json.id);
                    $content_info_cliente.append(template_info_cliente);
                    $div_receptor        =  $("#cliente_info_id_" + json.id, $content_info_cliente );

                    $('.link_info-receptor', $div_receptor).attr("data-id", json.id);
                    $(".link_info-receptor",$div_receptor).attr("onclick","show_info_receptor(this)");
                    $('button', $div_receptor).attr("data-id", json.id);
                    $("button",$div_receptor).attr("onclick","load_cliente_receptor(this)");

                    $('#cliente-nombre'   , $div_receptor ).val(json.nombre);
                    $('#cliente-apellidos', $div_receptor ).val(json.apellidos);
                    $('#cliente-email'    , $div_receptor ).val(json.email);
                    $('#cliente-telefono', $div_receptor ).val(json.telefono);
                    $('#cliente-telefono_movil', $div_receptor ).val(json.telefono_movil);

                    $("input[name = 'estado_id']", $div_receptor).val(
                        json.origen == 1 ? json.estado_usa : estadoList[json.estado_id]);


                    $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : json.estado_id}, function(json) {
                        municipioList = json;
                        $("input[name = 'municipio_id']", $div_receptor).val(
                        json.origen == 1 ? json.municipio_usa : municipioList[json.municipio_id]);
                    }, 'json');

                    $("input[name = 'colonia_id']", $div_receptor).val(
                        json.origen == 1 ? json.colonia_usa : json.colonia );

                    $("input[name = 'direccion_id']", $div_receptor).val(json.direccion);
                    $("input[name = 'num_exterior']", $div_receptor).val(json.num_ext);
                    $("input[name = 'num_interior']", $div_receptor).val(json.num_int);
                    $("input[name = 'referencia']", $div_receptor).val(json.referencia);


                    load_cliente_emisor_paquete();
                  }
                });
            }
        }
        $(this).val().length ? $('.next', $content_tab).show() :   $('.next', $content_tab).hide();
    });


    /*============================================================================
    *                           TAB PAQUETE
    *=============================================================================*/
    var load_cliente_emisor_paquete = function(){
        $paquete_cliente_id.html('');
        $.each(clienteSelect, function(key, value){
            $paquete_cliente_id.append("<option value='" + value.id + "'>" + value.nombre + " " + value.apellidos + "</option>\n");
        });

    }

    /*====================================================
    *               AGREGA UN ITEM A ARRAY
    *====================================================*/
    $btnAgregarPaquete.click(function(){
        if(validation_form_envio()){
            return false;
        }
        /*
        costo_extra = 0;
        pro_costo_extra = 0;
        cat_costo_extra = 0;

        if (productoDetalle.producto) {

            if(parseFloat($form_paquete.$cantidad.val()) >= parseInt(productoDetalle.producto.required_min)){
                cantidad_costo_extra = (parseFloat($form_paquete.$cantidad.val())  - parseInt(productoDetalle.producto.required_min)) + parseFloat(productoDetalle.producto.intervalo);

                intervalo = cantidad_costo_extra / productoDetalle.producto.intervalo;
                costo_extra     = productoDetalle.producto.costo_extra  * parseInt(intervalo);
                pro_costo_extra = productoDetalle.producto.costo_extra  * parseInt(intervalo);
            }
        }

        $.each(categoriaList,function(key, item){
            if ($form_paquete.$categoria.val() ==  item.id) {
                if (item.is_mex == 10) {
                    cantidad_costo_extra  = (parseFloat($form_paquete.$cantidad.val())  - parseInt( item.mex_required_min)) + parseFloat(item.mex_intervalo);
                    intervalo = cantidad_costo_extra / item.mex_intervalo;
                    costo_extra     =(costo_extra + item.mex_costo_extra) * parseInt(intervalo);
                    cat_costo_extra =item.mex_costo_extra * parseInt(intervalo);
                }
            }
        });
        */

        paquete = {
                "paquete_id"            : paquete_array.length + 1,
                "sucursal_id"           : $paquete_sucursal_id.val(),
                "cliente_id"            : $paquete_cliente_id.val(),
                "categoria_id"          : $form_paquete.$categoria.val(),
                "categoria_text"        : $('option:selected', $form_paquete.$categoria).text(),
                "cantidad"              : $form_paquete.$cantidad.val(),
                "unidad_medida_id"      : $form_paquete.$unidad_medida_id.val(),
                "unidad_medida_text"    : $('option:selected', $form_paquete.$unidad_medida_id ).text(),
                "producto_id"           : $form_paquete.$producto.val(),
                "producto_id_text"      : $('option:selected', $form_paquete.$producto).text(),
                "pro_costo_extra"       : 0,
                "cat_costo_extra"       : 0,
                "producto_costo_extra"  : 0,
                //"pro_costo_extra"       : pro_costo_extra,
                //"cat_costo_extra"       : cat_costo_extra,
                //"producto_costo_extra"  : costo_extra,
                "precio_intervalo"      : productoDetalle.producto  ? productoDetalle.producto.intervalo : null,
                "precio_costo_extra"    : productoDetalle.producto  ? productoDetalle.producto.costo_extra  : null,
                "precio_required_min"   : productoDetalle.producto  ? productoDetalle.producto.required_min  : null,
                "observaciones"         : $form_paquete.$observacion.val(),
                "seguro"                : $form_paquete.$seguro.prop('checked') ? true : false,
                "valor_declarado"       : $form_paquete.$seguro.prop('checked') ? $form_paquete.$valor_declarado.val() : null,
                "costo_seguro"          : $form_paquete.$seguro.prop('checked') ? ( <?= EsysSetting::getCobroSeguroMex()  ?>  * parseFloat($form_paquete.$valor_declarado.val())) / 100 : 0,
                "status"                : 10,
                "update"                : $envioID.val() ? 10 : 1,
                "origen"                : 1
        };

        paquete_array.push(paquete);

        render_paquete_template();

        clear_form($form_paquete);
        $form_paquete.$producto.html('');
        $form_paquete.$producto_tipo.hide();
        $form_paquete.$unidad_medida_id.html('');
        $alertCategoria.hide();

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

    /*====================================================
    *               Sucursales Asiganas
    *====================================================*/

    $sucursal_emisor_id.change(function(){
       load_sucursal_emisor();
    });

       /*====================================================
    *   Obtiene los productos filtrado por categoria
    *====================================================*/
    $form_paquete.$categoria.change(function(){

        $form_paquete.$producto.html('');
        $form_paquete.$unidad_medida_id.html('');
        select_producto = 0;
        categoriaID  = $(this).val();
        load_categoria();
        $.each(categoriaList,function(key, item){
            console.log(item.id);
            if (categoriaID ==  item.id) {
                if (item.is_mex == 10) {
                    $alertCategoria.show();
                    $('.content_info', $alertCategoria).html('');
                    $('.content_info', $alertCategoria).append('<strong> Apartir de: ' + item.mex_required_min +' piezas  / Costo extra: ' + item.mex_costo_extra +' USD / Intervalo '+ item.mex_intervalo + '</strong>');
                }else
                    $alertCategoria.hide();
            }else{
                $alertCategoria.hide();
                $form_paquete.$producto_tipo.hide();
                $form_paquete.$producto_tipo.html('');
                $form_paquete.$producto_costo_extra.val('');
            }
        });

        filters = "tipo_servicio="+ $tipo_envio.val()+"&categoria_id=" + categoriaID;

        if ($(this).val()) {
            $.get('<?= Url::to('productos-categoria-ajax') ?>', { filters: filters},function(producto){
                if(parseInt(producto.total) > 0){
                    productoCategoria  = producto.rows;
                    $.each(producto.rows, function(key, value){
                        $form_paquete.$producto.append("<option value='" + value.id + "'>" + value.nombre + "</option>\n");
                        select_producto = parseInt(value.id);
                    });
                }
                $form_paquete.$producto.val(select_producto).trigger('change');
            },'json');
        }
    });

    /*====================================================
    *   Obtiene la informacion del producto
    *====================================================*/
    $form_paquete.$producto.change(function(){
        $form_paquete.$producto_tipo.html('');
        $form_paquete.$producto_costo_extra.val('');
        if ($(this).val()) {
            producto_id = $(this).val();

            key = search_item(producto_id,productoCategoria);

            var newOption   = new Option(productoCategoria[key].unidad_medida, productoCategoria[key].id, false, true);
            $form_paquete.$unidad_medida_id.append(newOption);

            keyID = search_item(parseInt($(this).val()),productoCategoria);
            productoDetalle = {};

            if (!isNaN(parseInt(keyID))) {
                productoDetalle = {
                    producto: productoCategoria[keyID]
                };

                if(productoDetalle.producto.costo_extra){

                    $form_paquete.$producto_tipo.show();
                    $form_paquete.$producto_tipo.html('<strong>Aviso!</strong>  Te generar cargos extra aparti de: ' + productoDetalle.producto.required_min +'  <strong>'+ productoCategoria[keyID].unidad_medida +'</strong> por cada : ' + productoDetalle.producto.intervalo + ' pieza más que envies' );

                    $form_paquete.$producto_costo_extra.val(productoDetalle.producto.costo_extra);
                }else
                    $form_paquete.$producto_tipo.hide();
            }
        }else
            $form_paquete.$unidad_medida_id.val(null);
    });

    /*============================================================================
    *                           TAB SUCURSAL
    *=============================================================================*/

    /*==============================
    *   CARGA SUCURSALES
    *===============================*/
    $sucursal_receptor_id.change(function(){
        $(".content_info_sucursales").html('');
        if ($(this).val().length > 0 ) {
            sucursalSelect = [];
            for (var i = 0; i < $(this).val().length; i++) {
                $.get('<?= Url::to(['sucursal-info-ajax']) ?>', { q  : $(this).val()[i] },function(json){
                    if (json) {
                        tamplate_info_sucursal  = $tamplate_info_sucursal.html();
                        tamplate_info_sucursal  = tamplate_info_sucursal.replace('{{sucursal_info_id}}', json.id);
                        $(".content_info_sucursales").append(tamplate_info_sucursal);

                        $div        =  $("#sucursal_info_id_" + json.id, $(".content_info_sucursales"));

                        $('#nombre_sucursal_receptor',$div).html(json.nombre);
                        $('#encargado_sucursal_receptor',$div).html(json.encargado);
                        $('#direccion_sucursal_receptor',$div).html(json.direccion);
                        $('#telefono_sucursal_receptor',$div).html(json.telefono);
                        sucursalSelect.push(json);
                        load_sucursal_emisor_paquete();
                    }
                },'json');
            }
        }
        $(this).val() ? $('.next', $content_tab).show() :   $('.next', $content_tab).hide();
    });

    /*====================================================
    // Cobro de aseguranza del paquete
    /*====================================================*/
    $form_paquete.$seguro.change(function(){
        $(this).prop('checked') ?  $form_paquete.$valor_declarado.prop('disabled',false) : $form_paquete.$valor_declarado.val('').prop('disabled',true);
    });


    $btnAgregarMetodoPago.click(function(){

        if(!$form_metodoPago.$metodoPago.val() || !$form_metodoPago.$cantidad.val()){
            return false;
        }

        metodo = {
            "metodo_id"         : metodoPago_array.length + 1,
            "metodo_pago_id"    : $form_metodoPago.$metodoPago.val(),
            "metodo_pago_text"  : $('option:selected', $form_metodoPago.$metodoPago).text(),
            "cantidad"          : $form_metodoPago.$cantidad.val(),
            "origen"            : 1,
        };

        metodoPago_array.push(metodo);

        //calcula_cambio_envio();
        render_metodo_template();

    });
});


var show_info_receptor = function($ele){
  $div_receptor_id   = $($ele).data("id");
  $('.info-receptor').hide();
  $div_receptor        =  $("#cliente_info_id_" + $div_receptor_id);
  if ($is_div_info_receptor) {
      $('.link_info-receptor' ,$div_receptor).html("Ver más + ");
      $('.info-receptor' ,$div_receptor).hide(1000);
      $is_div_info_receptor = false;
  }else{

      $('.link_info-receptor' ,$div_receptor).html("Ver menos - ");
      $('.info-receptor' ,$div_receptor).show(1000);
      $is_div_info_receptor = true;
  }
};

/*===============================================
* Limpia valores de un  formulario
*===============================================*/
var clear_form = function($form){
    $.each($form,function($key,$item){
        $item.val(null);
    });
};

 var load_cliente_receptor = function(elem){
        $("#modal-title-cliente").html($(elem).data("cliente"));
        clear_form($modal);
        clear_form($form_cliente);
        clear_form($form_esysdireccion);
        $form_esysdireccion.$inputEstado.val(null).trigger('change');
        $form_esysdireccion.$inputMunicipio.html('');
        $form_esysdireccion.$inputColonia.html('');
        $('#form-cliente').html("Crear cliente");

        if( $.trim($(elem).data("cliente")) == 'Receptor'){
          isReceptorCreate  = true;
          isEmisorCreate    = false;
          isEmisorEdit      = false;

          $is_action = $.trim($(elem).data("action"));
            if ($cliente_receptor.val().length > 0 && $is_action == 'Update' ) {
                $is_id = $.trim($(elem).data("id"));
                $.get("<?= Url::to(['/crm/cliente/cliente-ajax'])  ?>",{ cliente_id: $is_id }, function(cliente_json){
                    if (cliente_json) {
                      isEmisorEdit   = true;
                      userInfo = cliente_json.results;
                      $form_cliente.$id.val(cliente_json.results.id);
                      $form_cliente.$nombre.val(cliente_json.results.nombre);
                      $form_cliente.$apellidos.val(cliente_json.results.apellidos);
                      $form_cliente.$inputOrigen.val(cliente_json.results.origen).trigger('change');
                      $form_cliente.$telefono.val(cliente_json.results.telefono);
                      $form_cliente.$telefono_movil.val(cliente_json.results.telefono_movil);
                      $form_esysdireccion.$inputDireccion.val(cliente_json.results.direccion);
                      $form_esysdireccion.$inputNumeroExt.val(cliente_json.results.num_ext);
                      $form_esysdireccion.$inputNumeroInt.val(cliente_json.results.num_int);
                      $form_esysdireccion.$inputReferencia.val(cliente_json.results.referencia);
                      $form_esysdireccion.$inputCodigoPostalUsa.val(cliente_json.results.codigo_postal_usa);
                      $form_esysdireccion.$inputEstadoUsa.val(cliente_json.results.estado_usa);
                      $form_esysdireccion.$inputMunicipioUsa.val(cliente_json.results.municipio_usa);
                      $form_esysdireccion.$inputColoniaUsa.val(cliente_json.results.colonia_usa);
                      $form_esysdireccion.$inputColonia.val(cliente_json.results.colonia);

                       if (cliente_json.results.origen == <?= Envio::ORIGEN_MX  ?> ) {
                           if (!cliente_json.results.codigo_postal)
                               $form_esysdireccion.$inputEstado.val(cliente_json.results.estado_id ? cliente_json.results.estado_id  : 0).trigger('change');
                           else
                                $form_esysdireccion.$inputCodigoSearch.val(cliente_json.results.codigo_postal).trigger('change');

                           //$form_esysdireccion.$inputColonia.val(cliente_json.results.codigo_postal_id);
                       }
                       $('#form-cliente').html("Guardar cambios");
                    }
                });
            }
        }
    }


var load_categoria = function (){
    filters = "tipo_servicio="+ $tipo_envio.val();
    $.get("<?= Url::to(['categoria-ajax']) ?>",{   filters: filters  },function($categoria){
        categoriaList = $categoria;
    });
}

/*=========================================
*      FUNCION QUE CARGA TODO EL ARRAY
*=========================================*/
var init_paquete_list = function(){

    paquete_array = [];
    if ($envioID.val()) {
        $.get('<?= Url::to('envio-detalle-ajax') ?>', {'envio' : $envioID.val() }, function(json) {
            $.each(json.rows, function(key, item){
                if (item.id) {
                    paquete = {
                        "paquete_id"    : item.id,
                        "sucursal_id"   : item.sucursal_receptor_id,
                        "cliente_id"    : item.cliente_receptor_id,
                        "categoria_id"  : item.categoria_id,
                        "categoria_text": item.categoria,
                        "cantidad"      : item.cantidad,
                        "unidad_medida_id"      : item.unidad_medida_id,
                        "unidad_medida_text"    : item.unidad_medida_text,
                        "producto_id"           : item.producto_id,
                        "producto_id_text"      : item.producto,
                        "producto_detalle_id"   : item.producto_detalle_id,
                        "producto_costo_extra"  : item.impuesto,
                        "pro_costo_extra"       : 0,
                        "cat_costo_extra"       : item.impuesto,
                        "precio_intervalo"      : item.intervalo,
                        "precio_costo_extra"    : item.costo_extra,
                        "precio_required_min"   : item.required_min,
                        "observaciones"         : item.observaciones,
                        "costo_seguro"          : item.costo_seguro,
                        "seguro"                : item.seguro &&  item.seguro != 0 ? true : false,
                        "valor_declarado"       : item.valor_declarado,
                        "status"        : item.status,
                        "update"        : $envioID.val() ? 10 : 1,
                        "origen"        : 2
                    };
                }
                paquete_array.push(paquete);
            });
        render_paquete_template();
        }, 'json');

        $.get('<?= Url::to('cobro-envio-ajax') ?>',{ 'envio_id': $envioID.val() },function(metodo){
            $.each(metodo.results,function(key,item){
                if (item.id) {
                    metodo = {
                        "metodo_id"         : metodoPago_array.length + 1,
                        "metodo_pago_id"    : item.metodo_pago,
                        "metodo_pago_text"  : metodoPagoList[item.metodo_pago],
                        "cantidad"          : item.cantidad,
                        "origen"            : 2,
                    };

                    metodoPago_array.push(metodo);
                    render_metodo_template();
                }
            });
        });
    }

    $.each(edit_load_sucursal, function(key, sucursal){
        $is_sucursal = true;
        $.each(sucursalSelect, function(key2, sucursal_Select){
            if (sucursal_Select.id == sucursal.id)
                $is_sucursal = $is_sucursal == false ? false: false;
        })
        if ($is_sucursal) {
            var newOption       = new Option(sucursal.nombre, sucursal.id, false, true);
            $sucursal_receptor_id.append(newOption);
            sucursalSelect.push(sucursal);
        }
    });

    $sucursal_receptor_id.trigger('change');

    $.each(edit_load_cliente, function(key, cliente){
        $is_cliente = true;
        $.each(clienteSelect, function(key2 , cliente_select){
            if (cliente_select.id == cliente.id){
                $is_cliente = $is_cliente == false ? false: false;
            }
        });

        if ($is_cliente) {
            var newOption       = new Option(cliente.nombre, cliente.id, false, true);
            $cliente_receptor.append(newOption);
            clienteSelect.push(cliente);
        }
    });

    $cliente_receptor.trigger('change');

    if ($isAplicaReenvio.val() == 10) {
        $isAplicaReenvio.val(null);
        $btnAplicaReenvio.trigger('click');
    }
};

/*=========================================
*      RENDERIZA TODO LOS PAQUETE
*==========================================*/
var render_paquete_template = function()
{
    $content_paquete.html("");

    costo_extra_total   = 0;
    seguro_total        = 0;
    $.each(paquete_array, function(key, paquete){

        if (paquete.paquete_id) {
            if(paquete.status == 10 || paquete.status == 2){

                template_paquete = $template_paquete.html();
                template_paquete = template_paquete.replace("{{paquete_id}}",paquete.paquete_id);

                $content_paquete.append(template_paquete);


                costo_extra = 0;
                cat_costo_extra = 0;

                if(parseFloat(paquete.cantidad) >= parseInt(paquete.precio_required_min)){
                    cantidad_costo_extra = (parseFloat(paquete.cantidad)  - parseInt(paquete.precio_required_min)) + parseFloat(paquete.precio_intervalo);

                    intervalo = cantidad_costo_extra / paquete.precio_intervalo;
                    costo_extra     = paquete.precio_costo_extra  * parseInt(intervalo);
                    paquete.pro_costo_extra = paquete.precio_costo_extra  * parseInt(intervalo);
                }

                /*
                $.each(categoriaList,function(key, item){
                    if ($form_paquete.$categoria.val() ==  item.id) {
                        if (item.is_mex == 10) {
                            cantidad_costo_extra  = (parseFloat($form_paquete.$cantidad.val())  - parseInt( item.mex_required_min)) + parseFloat(item.mex_intervalo);
                            intervalo = cantidad_costo_extra / item.mex_intervalo;
                            costo_extra     =(costo_extra + item.mex_costo_extra) * parseInt(intervalo);
                            cat_costo_extra =item.mex_costo_extra * parseInt(intervalo);
                        }
                    }
                });*/


                cal_costo_extra = calculo_costo_extra(key, paquete.categoria_id, paquete.cantidad);
                paquete.cat_costo_extra =  cal_costo_extra || cal_costo_extra == 0 ? cal_costo_extra : paquete.cat_costo_extra;
                paquete.producto_costo_extra = parseFloat(paquete.cat_costo_extra) + parseFloat(paquete.pro_costo_extra);







                $tr        =  $("#paquete_id_" + paquete.paquete_id, $content_paquete);
                $tr.attr("data-paquete_id",paquete.paquete_id);
                $tr.attr("data-origen",paquete.origen);
                $("#table_categoria_id",$tr).html(paquete.categoria_text);
                $("#table_producto_text",$tr).html(paquete.producto_id_text);
                $("#table_cantidad",$tr).val(paquete.cantidad);
                $("#table_unidad_medida",$tr).html(paquete.unidad_medida_text);


                $("#table_costo_extra",$tr).html(parseFloat(paquete.producto_costo_extra));

                $("#table_seguro",$tr).html(paquete.seguro ? '<input type="checkbox" checked="true" onchange="refresh_paquete_seguro(this)" >' : '<input  type="checkbox" onchange="refresh_paquete_seguro(this)">');

                $("#table_valor_declarado",$tr).val(paquete.valor_declarado);
                $("#table_valor_declarado",$tr).attr("onchange","refresh_paquete_valor_declarado(this)");
                $("#table_costo_seguro",$tr).html(paquete.costo_seguro ? paquete.costo_seguro : 0 + " USD");
                $("#table_observacion",$tr).html(paquete.observaciones);

                costo_extra_total   = costo_extra_total + parseFloat(paquete.producto_costo_extra);
                seguro_total        = seguro_total + parseFloat(paquete.costo_seguro ? paquete.costo_seguro : 0);

                $("#table_cantidad",$tr).attr("data-paquete_id",paquete.paquete_id);
                $("#table_cantidad",$tr).attr("data-origen",paquete.origen);

                $("#table_cantidad",$tr).attr("onchange","refresh_paquete_cantidad(this)");

                $tr.append("<td><button  type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
            }
        }
    });

    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));

    $('#envio-impuesto').val(costo_extra_total);
    $('#envio-impuesto-label').html(btf.conta.money(costo_extra_total));
    $('#envio-seguro_total').val(seguro_total);
    $('#envio-seguro_total-label').html(btf.conta.money(seguro_total));
};



    var validation_form_envio = function()
    {
        $error_add_paquete.html('');
        switch(true){
            case !$paquete_sucursal_id.val() :
                $error_add_paquete.append('<div class="help-block">* Selecciona una sucursal receptor</div>');
                $error_add_paquete.show();
                return true;
            break;
            case !$paquete_cliente_id.val() :
                $error_add_paquete.append('<div class="help-block">* Selecciona un cliente receptor</div>');
                $error_add_paquete.show();
                return true;
            break;
            case !$form_paquete.$producto.val() :
                $error_add_paquete.append('<div class="help-block">* Selecciona un producto</div>');
                $error_add_paquete.show();
                return true;
            break;

            case !$form_paquete.$cantidad.val() :
                $error_add_paquete.append('<div class="help-block">* N° de piezas no puede ser nulo</div>');
                $error_add_paquete.show();
                return true;
            break;

            case !$form_paquete.$valor_declarado.val():
                if ($form_paquete.$seguro.prop('checked')) {
                    $error_add_paquete.append('<div class="help-block">* Ingresa el valor del paquete</div>');
                    $error_add_paquete.show();
                    return true;
                }
            break;

        }
    }

    /*====================================================
    *               OPEN MODAL
    *====================================================*/

    $(".modal-create").click(function(){
        $("#modal-title-cliente").html($(this).data("cliente"));

        userInfo = [];
        clear_form($modal);
        clear_form($form_cliente);
        clear_form($form_esysdireccion);
        $form_esysdireccion.$inputEstado.val(null).trigger('change');
        $form_esysdireccion.$inputMunicipio.html('');
        $form_esysdireccion.$inputColonia.html('');

        $('#form-cliente').html("Crear cliente");
        if ($.trim($(this).data("cliente")) == 'Emisor' ){
            isEmisorCreate = true ;
            isReceptorCreate = false;
            if ($cliente_emisor.val()) {
                $.get("<?= Url::to(['/crm/cliente/cliente-ajax'])  ?>",{ cliente_id: $cliente_emisor.val() }, function(cliente_json){
                    if (cliente_json) {
                        userInfo = cliente_json.results;
                       $form_cliente.$nombre.val(cliente_json.results.nombre);
                       $form_cliente.$apellidos.val(cliente_json.results.apellidos);
                       $form_cliente.$inputOrigen.val(cliente_json.results.origen).trigger('change');
                       $form_cliente.$telefono.val(cliente_json.results.telefono);
                       $form_cliente.$telefono_movil.val(cliente_json.results.telefono_movil);
                       $form_esysdireccion.$inputDireccion.val(cliente_json.results.direccion);
                       $form_esysdireccion.$inputNumeroExt.val(cliente_json.results.num_ext);
                       $form_esysdireccion.$inputNumeroInt.val(cliente_json.results.num_int);
                       $form_esysdireccion.$inputReferencia.val(cliente_json.results.referencia);
                       $form_esysdireccion.$inputCodigoPostalUsa.val(cliente_json.results.codigo_postal_usa);
                       $form_esysdireccion.$inputEstadoUsa.val(cliente_json.results.estado_usa);
                       $form_esysdireccion.$inputMunicipioUsa.val(cliente_json.results.municipio_usa);
                       $form_esysdireccion.$inputColoniaUsa.val(cliente_json.results.colonia_usa);
                       $form_esysdireccion.$inputColonia.val(cliente_json.results.colonia);

                       if (cliente_json.results.origen == <?= Envio::ORIGEN_MX  ?> ) {
                            if (!cliente_json.results.codigo_postal)
                               $form_esysdireccion.$inputEstado.val(cliente_json.results.estado_id ? cliente_json.results.estado_id  : 0).trigger('change');
                            else
                                $form_esysdireccion.$inputCodigoSearch.val(cliente_json.results.codigo_postal).trigger('change');
                        }

                       $('#form-cliente').html("Guardar cambios");
                    }
                });
            }
        }else if( $.trim($(this).data("cliente")) == 'Receptor'){
            isReceptorCreate = true;
            isEmisorCreate = false;
        }
    });













/*====================================================
*               RENDERIZA TODO LOS METODS DE PAGO
*====================================================*/
var render_metodo_template = function(){
    $content_metodo_pago.html("");
    pago_total = 0;
    $.each(metodoPago_array, function(key, metodo){
        if (metodo.metodo_id) {

            metodo.metodo_id = key + 1;

            template_metodo_pago = $template_metodo_pago.html();
            template_metodo_pago = template_metodo_pago.replace("{{metodo_id}}",metodo.metodo_id);

            $content_metodo_pago.append(template_metodo_pago);

            $tr        =  $("#metodo_id_" + metodo.metodo_id, $content_metodo_pago);
            $tr.attr("data-metodo_id",metodo.metodo_id);
            $tr.attr("data-origen",metodo.origen);

            $("#table_metodo_id",$tr).html(metodo.metodo_pago_text);
            $("#table_metodo_cantidad",$tr).html("$ " +metodo.cantidad + " USD");

            pago_total = pago_total + parseFloat(metodo.cantidad);

            if (metodo.origen != 2) {
                $tr.append("<button type='button' class='btn btn-warning btn-circle' onclick='refresh_metodo(this)'><i class='fa fa-trash'></i></button>");
            }
        }
    });

    $('#total_metodo').html("$ " + ($('#total_envio').val() ? $('#total_envio').val() : 0) );




    $('#pago_metodo_total').html("$ "+ pago_total.toFixed(2));

    $inputcobroRembolsoEnvioArray.val(JSON.stringify(metodoPago_array));
}








/*=========================================
*      BUSCA UN ITEM EN EL ARRAY
*==========================================*/
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

/*=========================================
* Actualiza la lista de paquetes
*=========================================*/

var refresh_paquete = function(ele){

    $ele_paquete_val = $(ele).closest('tr');

    $ele_paquete_id  = $ele_paquete_val.attr("data-paquete_id");
    $ele_origen_id   = $ele_paquete_val.attr("data-origen");

    $.each(paquete_array, function(key, paquete){
        if (paquete) {
            if (paquete.paquete_id == $ele_paquete_id && paquete.origen == $ele_origen_id ) {
                if (paquete.origen ==  1)
                    paquete_array.splice(key,1);

                if (paquete.origen == 2 )
                    paquete.status = 1;
            }
        }
    });

    $(ele).closest('tr').remove();
    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
};

var refresh_paquete_cantidad = function(ele){
    $ele_paquete_val = $(ele).val();
    $ele_paquete_id  = $(ele).attr("data-paquete_id");

    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id == $ele_paquete_id ) {
            paquete.cantidad = $ele_paquete_val;

            /*costo_extra = 0;
            pro_costo_extra = 0;
            cat_costo_extra = 0;

            if(parseFloat(paquete.cantidad) >= parseInt(paquete.precio_required_min)){

                cantidad_costo_extra = (parseFloat(paquete.cantidad)  - parseInt(paquete.precio_required_min)) + parseFloat(paquete.precio_intervalo);
                intervalo = cantidad_costo_extra / paquete.precio_intervalo;
                costo_extra = paquete.precio_costo_extra  * parseInt(intervalo);
                pro_costo_extra = productoDetalle.producto.costo_extra  * parseInt(intervalo);
            }


            $.each(categoriaList,function(key, item){
                if ( paquete.categoria_id ==  item.id) {
                    if (item.is_mex == 10) {
                        cantidad_costo_extra  = (parseFloat(paquete.cantidad)  - parseInt( item.mex_required_min)) + parseFloat(item.mex_intervalo);
                        intervalo = cantidad_costo_extra / item.mex_intervalo;
                        costo_extra =(costo_extra + item.mex_costo_extra) * parseInt(intervalo);
                        cat_costo_extra =item.mex_costo_extra * parseInt(intervalo);
                    }
                }
            });

            paquete.producto_costo_extra = costo_extra;
            paquete.pro_costo_extra = pro_costo_extra;
            paquete.cat_costo_extra = cat_costo_extra;*/
        }
    });
    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
}

var refresh_paquete_seguro = function(ele){

    $ele_paquete_val    = $(ele);
    $ele_paquete        = $(ele).closest('tr');

    $ele_paquete_id  = $ele_paquete.attr("data-paquete_id");
    $ele_origen_id   = $ele_paquete.attr("data-origen");

    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id == $ele_paquete_id && paquete.origen == $ele_origen_id  ) {
            paquete.seguro = $ele_paquete_val.prop('checked') ? true : false;
            !$ele_paquete_val.prop('checked') ?  paquete.valor_declarado = 0 : '';
            !$ele_paquete_val.prop('checked') ?  paquete.costo_seguro    = ( <?= EsysSetting::getCobroSeguroMex()  ?>  * parseFloat($ele_paquete_val.val()) ) / 100 : '';
        }
    });
    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
}

var refresh_paquete_valor_declarado = function(ele){
    $ele_paquete_val    = $(ele);
    $ele_paquete        = $(ele).closest('tr');
    $ele_paquete_id  = $ele_paquete.attr("data-paquete_id");
    $ele_origen_id   = $ele_paquete.attr("data-origen");

    $.each(paquete_array, function(key, paquete){
        if (paquete.paquete_id == $ele_paquete_id && paquete.origen == $ele_origen_id  ) {
            paquete.valor_declarado = $ele_paquete_val.val();
            paquete.costo_seguro    = ( <?= EsysSetting::getCobroSeguroMex()  ?>  * parseFloat($ele_paquete_val.val()) ) / 100;
        }
    });

    $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
    render_paquete_template();
}


var calculo_costo_extra = function(key_search, $categoria_search, $cantidad_search){

    $count_producto = 0;
    cat_costo_extra = 0;
    $is_add         = false;
    cantidad_array     = [];
    $.each(paquete_array, function(key, paquete){
        if(paquete.status == 10 || paquete.status == 2){
            if (key_search >= key) {
                if (paquete.categoria_id == $categoria_search) {
                    console.log("Numero de productos count: " + paquete.cantidad + "-"+paquete.categoria_text);
                    $count_producto = $count_producto + parseInt(paquete.cantidad);
                    cantidad_array.push(paquete.cantidad);
                    $is_add         = true;
                }
            }
        }
    });

    console.log("Numero de productos count: " + $count_producto);
    if ($is_add) {
        $.each(categoriaList,function(key, item){
            if ( $categoria_search ==  item.id) {
                if (item.is_mex == 10) {

                    if (($count_producto - parseInt( item.mex_required_min) ) > 0 ) {

                        console.log("entro.....");
                        console.log($count_producto - parseInt( item.mex_required_min) );
                        console.log(".........");
                        /****************************/
                        // PRUEBA
                        /****************************/
                        $residuo     = 0;
                        $count_cantidad = cantidad_array.length;
                        $count_limit =  1;
                        $.each(cantidad_array, function(key,cantidad){
                            if ($residuo < 0) {
                                $residuo = $residuo  + (cantidad * -1);
                            }else{
                                $residuo = $residuo + ( item.mex_required_min - cantidad);
                                if ($residuo >= 0 ) {
                                    $residuo = -1;
                                }
                            }
                            $count_limit = $count_limit + 1;
                        });

                        if (($residuo * -1) <= $cantidad_search ) {

                            //cantidad_costo_extra  = ( parseInt($cantidad_search)  +  $residuo );
                            cantidad_costo_extra  = ( parseInt($cantidad_search)  -  item.mex_required_min );
                            intervalo = cantidad_costo_extra / item.mex_intervalo;
                            cat_costo_extra =item.mex_costo_extra * parseInt(intervalo);
                        }else{
                            cantidad_costo_extra  = parseInt($cantidad_search);
                            intervalo = cantidad_costo_extra / item.mex_intervalo;
                            cat_costo_extra =item.mex_costo_extra * parseInt(intervalo);
                        }

                        /****************************/
                        //
                        /****************************/

                        //cantidad_costo_extra  = ( parseInt($cantidad_search)  -  1 ) + parseFloat(item.mex_intervalo);


                    }else{
                        return $is_add;
                    }
                }
            }
        });
        return cat_costo_extra;
    }
    return $is_add;
}




/*===============================================
* Carga información de la sucursal emisor
*===============================================*/

var load_sucursal_emisor= function(){
    $.get('<?= Url::to(['sucursal-info-ajax']) ?>', { q  : $sucursal_emisor_id.val() },function(json){
        if (json) {
            $('#encargado_sucursal_emisor').html(json.encargado);
            $('#direccion_sucursal_emisor').html(json.direccion);
            $('#telefono_sucursal_emisor').html(json.telefono);
            $('.alert-tipo_sucursal').show();
            $('#tipo_sucursal').html(sucursal_tipo[json.tipo]);
        }
    },'json');
}

var load_sucursal_emisor_paquete = function(){
    $paquete_sucursal_id.html('');
    $.each(sucursalSelect, function(key, value){
        $paquete_sucursal_id.append("<option value='" + value.id + "'>" + value.nombre + " ["+ value.clave+"]</option>\n");
    });
}




var refresh_metodo = function(ele){
    $ele_paquete_val = $(ele).closest('tr');

    $ele_paquete_id  = $ele_paquete_val.attr("data-metodo_id");
    $ele_origen_id   = $ele_paquete_val.attr("data-origen");

      $.each(metodoPago_array, function(key, metodo){
        if (metodo) {
            if (metodo.metodo_id == $ele_paquete_id && metodo.origen == $ele_origen_id ) {
                metodoPago_array.splice(key, 1 );
            }
        }
    });

    $(ele).closest('tr').remove();
    $inputcobroRembolsoEnvioArray.val(JSON.stringify(metodoPago_array));
    render_metodo_template();
}




</script>
