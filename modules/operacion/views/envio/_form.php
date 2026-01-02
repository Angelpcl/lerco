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
    .modal-backdrop {
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;
        ;
    }

    /* Estilo para el botón flotante */
    /* Estilo para el botón fijo */
    /* Estilo para el botón fijo */
    .btn-fixed- {
        position: -webkit-sticky;
        /* Para compatibilidad con navegadores antiguos */
        position: sticky;
        /* Fija el botón en la ventana del navegador */

        display: flex;
        /* Usa flexbox para centrar el contenido */
        align-items: center;
        /* Alinea verticalmente el contenido */
        justify-content: center;
        /* Alinea horizontalmente el contenido */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        /* Sombra para resaltar */
        font-size: 16px;
        /* Tamaño de fuente */
        z-index: 9999;
        /* Asegura que el botón esté encima de otros elementos */
        background-color: #007bff;
        /* Color de fondo */
        color: white;
        /* Color del texto */
        border: none;
        /* Sin borde */
    }

    #scrollableTable {
        max-height: 320px;
        /* Ajusta esta altura según tus necesidades */
        overflow-y: auto;
        /* Agrega barra de desplazamiento vertical si el contenido excede la altura */
    }


    /* Añadir sombra para un efecto visual */
</style>


<div class="modal fade" id="productoModal" tabindex="-1" role="dialog" aria-labelledby="productoModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="productoModalLabel">Detalle por Producto</h5>
                <button id="btnCloseModal" type="button" class="close text-white" data-dismiss="modal_" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <div class="panel sticky-panel">
                    <div class="panel-title text-center mb-4">
                        <h3 class="text-primary">DETALLE POR PRODUCTO</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive" id="scrollableTable">
                            <table class="table table-hover table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center text-uppercase">Producto</th>
                                        <th class="text-center text-uppercase">Peso</th>
                                    </tr>
                                </thead>
                                <tbody class="content_paquete_" id="contenidoProductos" style="text-align: center;">
                                    <!-- Aquí se insertarán las filas dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <div class="table-responsive w-100">
                    <table class="table table-hover table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center text-uppercase">Producto</th>
                                <th class="text-center text-uppercase">Peso total</th>
                            </tr>
                        </thead>
                        <tbody class="content_paquete_" id="contenidoProductosTotales" style="text-align: center;">
                            <!-- Aquí se insertarán las filas dinámicamente -->
                        </tbody>
                    </table>
                </div>

                <div class="w-100 d-flex justify-content-center mt-3">
                    <button id="btnCloseModalFooter" type="button" class="btn btn-lg btn-secondary" data-dismiss="modal_">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>








<div class="operacion-envio-form">
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-danger" id="msg_error" style="display: none;">
                <strong>Aviso!</strong> El lugar de entrega se encuentra en zona roja
            </div>

            <?php if ($model->created_user_by->sucursal_id  == null) : ?>
                <div class="alert alert-danger">
                    <strong>Aviso!</strong> El usuario no tiene asignada ninguna sucursal por el momento, verifique mas tarde
                </div>

                <div class="help-block_" id="msg_zona"></div>
            <?php endif ?>


            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <?php if ($pais): ?>
                            <div class="col-md-6 text-center">
                                <?php if (isset($pais->imagen) && !empty($pais->imagen)) : ?>
                                    <?= Html::img('@web/uploads/flags/' . $pais->imagen, [
                                        'alt' => $pais->nombre,
                                        'class' => 'img-flag border shadow-sm',
                                        'style' => 'border-radius: 5px; width: 200px; height: auto; object-fit: cover;'
                                    ]) ?>
                                <?php else : ?>
                                    <?= Html::img('@web/uploads/flags/default.jpeg', [
                                        'alt' => $pais->nombre,
                                        'class' => 'img-flag border shadow-sm',
                                        'style' => 'border-radius: 5px; width: 100px; height: auto; object-fit: cover;'
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 text-left">
                                <div class="col-12 text-center">
                                    <h3 class="display-4 font-weight-bold " style="color: #333;"><?= Html::encode($pais->nombre) ?></h3>

                                </div>

                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-12">

                            <?php $form = ActiveForm::begin(['id' => 'form-envios']) ?>

                            <?= $form->field($model->envio_detalle, 'envio_detalle_array')->hiddenInput()->label(false) ?>
                            <?= $form->field($model->cobroRembolsoEnvio, 'cobroRembolsoEnvioArray')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'pais_destino_id')->hiddenInput([
                                'value' => $pais ? $pais->id : null,  // Establece el valor del país si no es null, de lo contrario, deja el campo vacío.
                            ])->label(false) ?>


                            <?= $form->field($model, 'subtotal')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'seguro_total')->hiddenInput()->label(false) ?>

                            <?php if ($model->created_user_by->sucursal_id != 24) : ?>
                                <?= $form->field($model, 'total')->hiddenInput()->label(false) ?>
                            <?php endif ?>

                            <?= $form->field($model, 'is_reenvio')->hiddenInput(["value" => Envio::REENVIO_ON])->label(false) ?>
                            <?= $form->field($model, 'costo_reenvio')->hiddenInput()->label(false) ?>

                            <?= $form->field($model->envio_detalle, 'dir_obj_array')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>

                            <?= $form->field($model, 'tipo_envio')->hiddenInput(["value" => Envio::TIPO_ENVIO_TIERRA])->label(false) ?>

                            <!-- Main Form Wizard -->
                            <!--===================================================-->

                            <div class="display-none">
                                <h4>Sucursal que recibe</h4>
                                <div class="div_sucursal_receptor">
                                    <?= $form->field($model, 'sucursal_receptor_names[]')->widget(
                                        Select2::classname(),
                                        [
                                            'language' => 'es',
                                            'data' => [2 => 'CDMX'],
                                            'value' => [2],
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
                                        ]
                                    )->label(false) ?>
                                </div>
                            </div>

                            <div class="ibox-content">
                                <div class="row">
                                    <div class="col-sm-6 form_emisor">
                                        <div class="row">
                                            <div class="col-sm-2" style="   margin-top: 4%;">
                                                <button type="button" data-cliente="Emisor" data-target="#modal-create-user" data-toggle="modal" class="modal-create btn  btn-circle <?= $model->cliente_emisor->id  ?  'btn-danger' : 'btn-primary' ?>"><i id="icon_emisor" class="fa <?= $model->cliente_emisor->id ? 'fa-edit' : 'fa-users'  ?>"></i></button>
                                            </div>
                                            <div class="col-sm-10">
                                                <?= $form->field($model, 'cliente_emisor_id')->widget(
                                                    Select2::classname(),
                                                    [
                                                        'language' => 'es',
                                                        'data' => isset($model->clienteEmisor->id) ? [$model->clienteEmisor->id => $model->clienteEmisor->nombre . " " . $model->clienteEmisor->apellidos] : [],
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
                                                    ]
                                                ) ?>
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
                                        <?= $form->field($model->cliente_emisor, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter e-mail'), "disabled" => true]) ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <?= $form->field($model->cliente_emisor, 'telefono')->textInput(['maxlength' => true, "disabled" => true]) ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= $form->field($model->cliente_emisor, 'telefono_movil')->textInput(['maxlength' => true, "disabled" => true]) ?>
                                            </div>
                                        </div>
                                        <a href="javascript:void(0)" id="link_info-emisor" class="btn-link">Ver más +</a>
                                        <div class="info-emisor" style="display: none">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <?= Html::label("Estado", "estado_id") ?>
                                                    <?= Html::textInput('estado_id', isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX ? isset($model->cliente_emisor->dir_obj->estado->singular) ? $model->cliente_emisor->dir_obj->estado->singular : null : $model->cliente_emisor->dir_obj->estado_usa : null, ["disabled" => true, 'class' => 'form-control']) ?>

                                                </div>
                                                <div class="col-sm-6">
                                                    <?= Html::label("Deleg./Mpio.", "municipio_id") ?>
                                                    <?= Html::textInput('municipio_id', isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX ? isset($model->cliente_emisor->dir_obj->municipio->singular) ? $model->cliente_emisor->dir_obj->municipio->singular : null : $model->cliente_emisor->dir_obj->municipio_usa : null, ["disabled" => true, 'class' => 'form-control']) ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <?= Html::label("Colonia", "colonia_id") ?>
                                                    <?= Html::textInput('colonia_id', isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->origen == Cliente::ORIGEN_MX && isset($model->cliente_emisor->dir_obj->esysDireccionCodigoPostal) ? $model->cliente_emisor->dir_obj->esysDireccionCodigoPostal->colonia : $model->cliente_emisor->dir_obj->colonia_usa : null, ["disabled" => true, 'class' => 'form-control']) ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <?= Html::label("Direccion", "direccion_id") ?>
                                                    <?= Html::textInput('direccion_id', isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->dir_obj->direccion : null, ["disabled" => true, 'class' => 'form-control']) ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <?= Html::label("N° Exterior", "num_exterior") ?>
                                                    <?= Html::textInput('num_exterior', isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->dir_obj->num_ext : null, ["disabled" => true, 'class' => 'form-control']) ?>

                                                </div>
                                                <div class="col-sm-6">
                                                    <?= Html::label("N° Interior", "num_interior") ?>
                                                    <?= Html::textInput('num_interior', isset($model->cliente_emisor->dir_obj->id) ? $model->cliente_emisor->dir_obj->num_int : null, ["disabled" => true, 'class' => 'form-control']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 form_receptor">
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <button data-cliente="Receptor" type="button" data-target="#modal-create-user" data-toggle="modal" class=" modal-create btn  btn-circle btn-primary" data-action="create"><i id="icon_receptor" class="fa fa-users"></i></button>
                                            </div>
                                            <div class="col-sm-10">
                                                <?= $form->field($model, 'cliente_receptor_names[]')->widget(
                                                    Select2::classname(),
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
                                                                'data'     => new JsExpression('function(params) { 
                                                                    return {
                                                                        q: params.term,
                                                                         data: params, // Término de búsqueda que el usuario ingresa
                                                                         pais_id: PAIS_ID
                                                                       
                                                                    };
                                                                }'),

                                                                'processResults' => new JsExpression('function(data, params){  clienteReceptor = data; return {results: data} }'),
                                                            ],
                                                        ],
                                                        'options' => [
                                                            'placeholder' => 'Selecciona al cliente...',
                                                            'multiple' => true,
                                                        ],
                                                    ]
                                                ) ?>
                                            </div>
                                        </div>
                                        <div class="content_info_cliente">
                                        </div>
                                    </div>
                                </div>
                                <!-- ================================== REENVIO =============================-->
                                <br>
                                <h2>Direcciones de entrega</h2>
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


                            <div class="ibox-content form_paquete">
                                <div id="error-add-paquete" class="has-error alert alert-danger" style="display: none">

                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row" style="font-size: 14px;background-color: #d6d3d2;padding: 2%;">
                                            <div class="col-sm-4">
                                                <?= Html::label('Sucursal que recibe:', 'paquete_sucursal_id') ?>
                                                <?= Html::dropDownList('paquete_sucursal_id', null, [], ['id' => 'paquete_sucursal_id', 'class' => 'form-control']) ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?= Html::label('Cliente que recibe:', 'paquete_cliente_id') ?>
                                                <?= Html::dropDownList('paquete_cliente_id', null, [], ['id' => 'paquete_cliente_id', 'class' => 'form-control']) ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?= Html::label('Entrega:', 'reenvio_select_id') ?>
                                                <?= Html::dropDownList('reenvio_select_id', null, [], ['id' => 'reenvio_select_id', 'class' => 'form-control']) ?>
                                            </div>
                                        </div>
                                        <hr />
                                        <?php /* ?>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <?= $form->field($model->envio_detalle, 'categoria_id')->dropDownList(EsysListaDesplegable::getItems('categoria_paquete_lax_tierra'), ['prompt' => 'Selecciona la categoria']) ?>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?= $form->field($model->envio_detalle, 'producto_id')->dropDownList([], ['prompt' => 'Selecciona el producto']) ?>
                                                    </div>
                                                </div>
                                                */ ?>



                                        <div class="row">
                                            <?php if ($pais->id === 1): ?>
                                                <div class="col-sm-4">

                                                    <?= Html::label('TIPO:', 'producto_tipo_enviar') ?>
                                                    <?= Html::dropDownList('producto_tipo_enviar', null, [
                                                        10 => "PRECIO X LIBRA",
                                                        20 => "PRECIO X CAJA",
                                                        30 => "PRECIO X CAJA SIN LÍMITE",
                                                    ], ['id' => 'producto_tipo_enviar', 'class' => 'form-control']) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="col-sm-4">

                                                    <?= Html::label('TIPO:', 'producto_tipo_enviar') ?>
                                                    <?= Html::dropDownList('producto_tipo_enviar', null, [
                                                        //10 => "PRECIO X LIBRA",
                                                        //20 => "PRECIO X CAJA",}
                                                        30 => "PRECIO X CAJA SIN LÍMITE",
                                                    ], [
                                                        'id' => 'producto_tipo_enviar',
                                                        'class' => 'form-control',
                                                        //'prompt' => 'Selecciona el tipo de producto'
                                                    ]) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="col-sm-6">
                                                <div class="select_producto">
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
                                                <div class="select_caja" style="display: none">
                                                    <?= Html::label('Caja:', 'select_caja_id') ?>
                                                    <?= Html::dropDownList('select_caja_id', null, Producto::getCaja(), ['id' => 'select_caja_id', 'class' => 'form-control']) ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <?= Html::button('<i class="fa fa-plus"></i> Crear producto', ['class' =>  'btn btn-small btn-primary', 'style' => 'margin-top: 20px', 'data-target' => "#modal-create-producto", 'data-toggle' => "modal", "onclick" => "init_producto()"]) ?>
                                            </div>
                                        </div>

                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <?= $form->field($model->envio_detalle, 'cantidad')->textInput(['type' => 'number', 'value' => '1', 'min' => '1']) ?>

                                                <?= $form->field($model->envio_detalle, 'valor_declarado')->textInput(['type' => 'number']) ?>
                                                <div id="div_peso">
                                                    <?= $form->field($model->envio_detalle, 'peso')->textInput(['type' => 'number', "step" => "0.01"]) ?>
                                                    <?= $form->field($model->envio_detalle, 'precio_libra_actual')->textInput(['type' => 'number', "step" => "0.01", 'id' => 'precio_libra_id']) ?>
                                                </div>

                                                <div id="div_peso">

                                                </div>

                                            </div>
                                            <div class="col-sm-6">
                                                <?= $form->field($model->envio_detalle, 'observaciones')->textArea(['maxlength' => true, 'rows' => 4]) ?>

                                                <div class="div_costo_neto_extraordinario" style="display: none">
                                                    <?= $form->field($model->envio_detalle, 'costo_neto_extraordinario')->textInput(['type' => 'number']) ?>
                                                </div>

                                                <div class="checkbox text-center" style="margin-top: 35px;">
                                                    <input id="seguro" class="magic-checkbox" type="checkbox" checked="true">
                                                    <label for="seguro">Seguro</label>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12"> <button type="button" id="btnAgregar-paquete" class=" btn btn-block btn-lg  btn-primary"><i class="fa fa-cube"></i> Agregar </button></div>
                                                    <br>
                                                    <br>
                                                    <br>
                                                    <div class="col-md-12">
                                                        <button type="button" class="btn btn-primary btn-block btn-lg " id="btnVerDetalle">
                                                            Ver Detalle por Producto
                                                        </button>

                                                    </div>
                                                </div>


                                            </div>
                                        </div>



                                        <div class="row">
                                            <div class="col-sm-12">

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LISTADO DE ARTICULOS  -->
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
                                        <span>TOTAL PESO</span>
                                        <?= Html::input('text', 'peso_total', isset($model->peso_total) && $model->peso_total ? $model->peso_total : 0, [
                                            'id' => 'peso_total',
                                            'class' => 'form-control',
                                            'readonly' => true
                                        ]) ?>

                                        <?= ""//Html::input('text', 'peso_total', isset($model->peso_total) && $model->peso_total ? $model->peso_total : 0, ['id' => 'peso_total', 'class' => 'form-control']) ?>
                                    </div>

                                    <div class="col-sm-4">
                                        <span>TOTAL V. DECLARADO </span>
                                        <?= Html::input('text', 'total_v_declarado', 0, ['id' => 'total_v_declarado', 'class' => 'form-control']) ?>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="div_peso_reenvio" style="display: none">
                                            <span>PESO TOTAL DE ENTREGA</span>
                                            <?= Html::input('text', 'peso_reenvio', isset($model->peso_reenvio) && $model->peso_reenvio ? $model->peso_reenvio : 0, ['id' => 'peso_reenvio', 'class' => 'form-control']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4 text-center">
                                        <h2 class="product-main-price" id="envio-subtotal-label">$0.00</h2>
                                        <strong>SUBTOTAL</strong>
                                    </div>
                                    <div class="col-sm-4 text-center">
                                        <h2 class="product-main-price" id="envio-seguro_total-label">$0.00</h2>
                                        <strong>SEGURO</strong>
                                    </div>
                                    <div class="col-sm-4 text-center">
                                        <?php if ($model->created_user_by->sucursal_id == 24) : ?>
                                            <?= $form->field($model, 'total')->textInput(['type' => 'number'])->label(false) ?>
                                        <?php else : ?>
                                            <h2 class="product-main-price" id="envio-total-label">$0.00</h2>
                                        <?php endif ?>

                                        <strong>TOTAL</strong>
                                    </div>
                                </div>


                            </div>

                            <!-- /.box-body PAGOS -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="ibox panel-colorful panel-warning alert-aviso-cobro" style="display: none">
                                        <div class="ibox-title">
                                            <h5>¡Aviso!</h5>
                                        </div>
                                        <div class="ibox-content">
                                            <strong>El total del envío no puede ser inferior al monto total ya cobrado, si requieres una modificación ó un cambio al monto ya cobrado contacta al administrador del sistema ó de lo contrario cancela el envío y captura un nuevo envio con los nuevos valores.</strong>
                                        </div>
                                    </div>
                                    <h3>Metodos de pagos</h3>
                                    <div class="row" style="border-style: double;padding: 2%;">
                                        <div class="col-sm-4">
                                            <?= $form->field($model->cobroRembolsoEnvio, 'metodo_pago')->dropDownList(CobroRembolsoEnvio::$servicioList)->label("&nbsp;") ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <?= $form->field($model->cobroRembolsoEnvio, 'cantidad')->textInput(['maxlength' => true]) ?>
                                        </div>
                                        <div class="col-sm-2">
                                            <button type="button" class="btn  btn-primary" id="btnAgregarMetodoPago" style="margin-top: 15px;">Ingresar pago</button>

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
                                                        <td><strong id="pago_metodo_total">0 USD</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-right" style="border: none" colspan="2"><span class="text-main text-semibold">Balance (Por pagar): </span></td>
                                                        <td class="text-danger"><strong id="balance_total">0 USD</strong></td>
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

                            <div class="form-group row">

                                <?= Html::submitButton($model->isNewRecord ? 'Guardar envio' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success btn-lg btn-block offset-sm-2 col-sm-4' : 'btn btn-primary btn-lg btn-block col-sm-4', 'id' => 'btnGuardarEnvio']) ?>


                                <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white col-sm-4']) ?>



                            </div>

                            <input type="hidden" id="input_zona_roja" name='input_zona_roja' value="false">

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
            <tr id="paquete_id_{{paquete_id}}">
                <td><?= Html::tag('p', "Categoria", ["class" => "text-main", "id"  => "table_categoria_id"]) ?></td>
                <td><?= Html::input('number', "", false, ["class" => "form-control", "id"  => "table_cantidad", "style" => "text-align:center; width: 80px;"]) ?></td>
                <td><?= Html::input('number', "", false, ["class" => "form-control", "id"  => "table_valor_declarado", "style" => "text-align:center"]) ?></td>
                <td><?= Html::input('number', "", false, ["class" => "form-control",  "style" => "text-align:center;width: 80px; ", "id"  => "table_peso"]) ?></td>
                <td><?= Html::tag('p', "Seguro", ["class" => "text-main", "id"  => "table_seguro"]) ?></td>
                <td><?= Html::dropDownList('table_reenvio_id', null, [], ['id' => 'table_reenvio_id', 'class' => 'form-control', 'style' => 'width: 200px;', 'prompt' => 'Selecciona direccion', 'disabled' => 'true']) ?></td>
                <td><?= Html::tag('p', "Costo del seguro", ["class" => "text-main", "id"  => "table_costo_seguro"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="display-none">
    <table>
        <tbody class="template_metodo_pago">
            <tr id="metodo_id_{{metodo_id}}">
                <td><?= Html::tag('p', "0", ["class" => "text-main text-semibold", "id"  => "table_metodo_id"]) ?></td>
                <td><?= Html::tag('p', "", ["class" => "text-main ", "id"  => "table_metodo_cantidad", "style" => "text-align:center"]) ?></td>
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
        <div id="cliente_info_id_{{cliente_info_id}}">
            <div class="row">
                <div class="col-sm-2">
                    <button data-cliente="Receptor" data-action="Update" type="button" data-target="#modal-create-user" data-toggle="modal" class="modal-create btn  btn-circle btn-danger"><i id="icon_receptor" class="fa fa-pencil-square-o"></i></button>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model->cliente_receptor, 'nombre')->textInput(['maxlength' => true, "disabled" => true]) ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model->cliente_receptor, 'apellidos')->textInput(['maxlength' => true, "disabled" => true]) ?>
                </div>
            </div>
            <?= $form->field($model->cliente_receptor, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter e-mail'), "disabled" => true]) ?>
            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($model->cliente_receptor, 'telefono')->textInput(['maxlength' => true, "disabled" => true]) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model->cliente_receptor, 'telefono_movil')->textInput(['maxlength' => true, "disabled" => true]) ?>
                </div>
            </div>
            <a href="javascript:void(0)" class="btn-link link_info-receptor">Ver más +</a>
            <div class="info-receptor" style="display: none">
                <div class="row">
                    <div class="col-sm-6">
                        <?= Html::label("Estado", "estado_id") ?>
                        <?= Html::textInput('estado_id', isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->origen == Cliente::ORIGEN_MX ? $model->cliente_receptor->dir_obj->estado->singular : $model->cliente_receptor->dir_obj->estado_usa : null, ["disabled" => true, 'class' => 'form-control']) ?>

                    </div>
                    <div class="col-sm-6">
                        <?= Html::label("Deleg./Mpio.", "municipio_id") ?>
                        <?= Html::textInput('municipio_id', isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->origen == Cliente::ORIGEN_MX ? $model->cliente_receptor->dir_obj->municipio->singular : $model->cliente_receptor->dir_obj->municipio_usa : null, ["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::label("Colonia", "colonia_id") ?>
                        <?= Html::textInput('colonia_id', isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->origen == Cliente::ORIGEN_MX ? $model->cliente_receptor->dir_obj->esysDireccionCodigoPostal->colonia : $model->cliente_receptor->dir_obj->colonia_usa : null, ["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::label("Direccion", "direccion_id") ?>
                        <?= Html::textInput('direccion_id', isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->dir_obj->direccion : null, ["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <?= Html::label("N° Exterior", "num_exterior") ?>
                        <?= Html::textInput('num_exterior', isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->dir_obj->num_ext : null, ["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                    <div class="col-sm-6">
                        <?= Html::label("N° Interior", "num_interior") ?>
                        <?= Html::textInput('num_interior', isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->dir_obj->num_int : null, ["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::label("Referencia", "referencia") ?>
                        <?= Html::textInput('referencia', isset($model->cliente_receptor->dir_obj->id) ? $model->cliente_receptor->dir_obj->referencia : null, ["disabled" => true, 'class' => 'form-control']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_reenvio">
            <tr id="reenvio_id_{{reenvio_id}}">
                <td><?= Html::tag('p', null, ["class" => "text-main", "id"  => "table_cp"]) ?></td>
                <td><?= Html::tag('p', null, ["class" => "text-main", "id"  => "table_estado"]) ?></td>
                <td><?= Html::tag('p', null, ["class" => "text-main", "id"  => "table_municipio"]) ?></td>
                <td><?= Html::tag('p', null, ["class" => "text-main", "id"  => "table_colonia"]) ?></td>
                <td><?= Html::tag('p', null, ["class" => "text-main", "id"  => "table_direccion"]) ?></td>
                <td><?= Html::tag('p', null, ["class" => "text-main", "id"  => "table_n_interior"]) ?></td>
                <td><?= Html::tag('p', null, ["class" => "text-main", "id"  => "table_n_exterior"]) ?></td>
                <td><?= Html::tag('p', null, ["class" => "text-main", "id"  => "table_referencia"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="fade modal " id="modal-alertReenvio" tabindex="-1" role="dialog" aria-labelledby="modal-alertReenvio-label">
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

<input type="hidden" id="exist_promo" value="10">
<input type="hidden" id="exist_promo_json" value="null">



<script>
    var PAIS_ID = <?= $pais->id ?>;
    var SUBTOTAL_ENVIO = <?= $model->subtotal ? $model->subtotal : 0 ?>;
    var SUBTOTAL = 0;
    var arrayPaqueteDeatlle = [];
    var paquete_detalle_json = null;

    var exist_promo = $('#exist_promo');
    var exist_promo_json = $('#exist_promo_json');

    var sucursal_id_user = <?= yii::$app->user->identity->sucursal_id ? json_encode(yii::$app->user->identity->sucursal_id) : json_encode(null) ?>;



    $(document).ready(function() {
        $("#div_peso").hide();

        function sumatoriaTotalPaqute() {
            let arrayPaqueteDeatlle = [];
            let sum = 0;
            let cont = 0;

            paquete_array.forEach(paquete => {
                let peso_paquete = 0;
                let peso_main = Number(parseFloat(paquete.peso).toFixed(2));
                let flag = false;
                let type = 0;

                console.log('peso max', peso_main);


                paquete.paquete_detalle.forEach(detail => {
                    peso_paquete += Number(parseFloat(detail.peso_max).toFixed(2)) || 0; // Manejar valores no numéricos
                    cont++;
                    type = Number(detail.tipo_producto);
                    console.log(type);

                    // Solo agregar si type es distinto de 30
                    if (Number(parseFloat(detail.peso_max || 0).toFixed(2)) === 0 && type !== 30) {
                        arrayPaqueteDeatlle.push({
                            nombre: paquete.producto_text,
                            peso_max: peso_paquete,
                            peso: peso_main,
                            peso_paquete: peso_paquete,
                            diferencia: 0,
                            msg: "El peso no puede ser 0 lb ",
                        });
                    }
                });
                peso_paquete = Number(peso_paquete.toFixed(2));
                let dif = Number((peso_main - peso_paquete).toFixed(2));

                console.log('peso main', peso_main);
                console.log('peso paq', peso_paquete);
                render_paquete_template();


                //if (dif !== 0) {
                //    arrayPaqueteDeatlle.push({
                //        nombre: paquete.producto_text,
                //        peso_max: peso_paquete,
                //        peso: peso_main,
                //        peso_paquete: peso_paquete,
                //        diferencia: dif,
                //        msg: paquete.producto_text + " faltan " + dif + " lb",
                //    });
                //}
            });
            //console.log(arrayPaqueteDeatlle, 'arrayPaqueteDeatllesegfsdgdsfhgdfrrhg');

            if (arrayPaqueteDeatlle.length > 0) {

                console.log(arrayPaqueteDeatlle, 'arrayPaqueteDeatlle');
                const productosSinPeso = arrayPaqueteDeatlle
                    .map(producto => (producto.msg).toUpperCase())
                    .join('\n');

                // Mostrar el mensaje en el alert
                alert('LOS SIGUIENTES PRODUCTOS NO TIENEN EL PESO CONFIGURADO, POR FAVOR CONFIGURE EL PESO DE LOS PRODUCTOS CORERECTAMENTE:\n' + productosSinPeso);
                // Mostrar la tabla
                //mostrarTabla(arrayPaqueteDeatlle);
                return true;
            };
            return false;
            //console.log('suma total', sum);
            //return sum;
        }
        // Al hacer clic en el botón con id 'btnVerDetalle'
        $('#btnVerDetalle').on('click', function() {
            // Mostrar el modal usando jQuery
            $('#productoModal').modal('show');
        });


        //$('#btnCloseModal').click()

        // Al hacer clic en el botón de cerrar en el header del modal
        $('#btnCloseModal').on('click', function() {
            if (!sumatoriaTotalPaqute()) {
                $('#productoModal').modal('hide');
            }

        });

        // Al hacer clic en el botón de cerrar en el footer del modal
        $('#btnCloseModalFooter').on('click', function() {
            if (!sumatoriaTotalPaqute()) {
                $('#productoModal').modal('hide');
                console.log('cerrar');

            }
        });
        // Inicializa el modal sin mostrarlo automáticamente
        //$('#productoModal').modal({
        //    backdrop: 'static', // Evita que el modal se cierre al hacer clic fuera de él
        //    keyboard: false // Evita que el modal se cierre con la tecla ESC
        //});

        if (PAIS_ID !== 1) {
            $("#div_peso").hide();
            $('.select_caja').show();
            $('.select_producto').hide();

            cargarProductoCaja(30);

            tipo_producto_select = 30;
        }

        //console.log('suscursal_id_user', sucursal_id_user);
        //Carga las promciones, si
        $.get("<?= Url::to(['sucursal-promos']) ?>", {
            id: sucursal_id_user,
        }, function($response) {
            //console.log($response);
            exist_promo_json.val(JSON.stringify($response.model));
            exist_promo.val($response.code);
        }, 'json');


        /**
         * =======================================
         *    AQUI VERIFICA SI YA EXISTE PROMOS 
         * ==========================================
         */
        $paquete_sucursal_id.on('change', function() {
            var paquete_sucursal_id = $(this).val();
            //console.log('paquete_sucursal_id', paquete_sucursal_id);
            //$.get("<?= Url::to(['sucursal-promos']) ?>", {
            //    id: paquete_sucursal_id,
            //}, function($response) {
            //    //console.log($response);
            //    //exist_promo_json.val(JSON.stringify($response.model));
            //    //exist_promo.val($response.code);
            //}, 'json');

        });
        // Escuchar el evento change del select2
        $('#envio-producto_id').on('change', function() {
            let exite_promo = Number(exist_promo.val());
            let model = JSON.parse(exist_promo_json.val());
            // Obtener el valor seleccionado
            var selectedValue = $(this).val();

            // Si se seleccionó algo
            if (selectedValue) {
                $.get("<?= Url::to(['get-caja']) ?>", {
                    caja_id: selectedValue,
                }, function($response) {
                    if ($response.code == 10) {

                    }
                    //console.log('Costo sdvsdvpor libra', exite_promo);
                    let costo_libra_ = Number(exite_promo) != 10 ? parseFloat(model.costo_libra_peso_cli) : precio_libra_actual; // parseFloat($response.caja.producto.costo_libra);
                    //console.log($response.caja.producto.costo_libra, 'Costo por libra');
                    $("#precio_libra_id").val(Number(costo_libra_));

                }, 'json');
                ///console.log("Valor seleccionado:", selectedValue);
                // Puedes usar selectedValue para otras operaciones
                $form_paquete.$producto_caja.data("producto_id_selec", Number(selectedValue));
            } else {
                // console.log("No se ha seleccionado ningún valor.");
            }
        });
        // Escuchar el evento change del select2
        $('#select_caja_id').on('change', function() {

            // Obtener el valor seleccionado
            if (Number($("#producto_tipo_enviar").val()) == 20) {
                let exite_promo = Number(exist_promo.val());
                let model = JSON.parse(exist_promo_json.val());

                var selectedValue = $(this).val();

                // Si se seleccionó algo
                if (selectedValue) {
                    $.get("<?= Url::to(['get-caja']) ?>", {
                        caja_id: selectedValue,
                        pais_id: PAIS_ID,
                    }, function($response) {
                        if ($response.code == 10) {
                            //console.log('No se puede enviar el producto, ya que no hay cajas disponibles', );
                            //console.log('Costo sdvsdvpor libra');
                            let costo_libra_ = Number(exite_promo) != 10 ? parseFloat(model.costo_libra_caja_cli) : precio_libra_actual; // parseFloat($response.caja.producto.costo_libra);
                            //console.log($response.caja.producto.costo_libra, 'Costo por libra');
                            $("#precio_libra_id").val(Number(costo_libra_));
                        }
                        //console.log($response.caja.producto.costo_libra, 'Costo por libra');
                        //$("#precio_libra_id").val(Number($response.caja.producto.costo_libra));

                    }, 'json');
                    ///console.log("Valor seleccionado:", selectedValue);
                    // Puedes usar selectedValue para otras operaciones
                    $form_paquete.$producto_caja.data("producto_id_selec", Number(selectedValue));
                } else {
                    // console.log("No se ha seleccionado ningún valor.");
                }
            }
        });

    });

    var $sucursal_emisor_id = $('#envio-sucursal_emisor_id'),
        $tipo_envio = $('#envio-tipo_envio'),
        $tamplate_info_sucursal = $('.tamplate_info_sucursal'),
        $template_info_cliente = $('.template_info_cliente'),
        $content_info_cliente = $('.content_info_cliente'),
        $sucursal_receptor_id = $('#envio-sucursal_receptor_names'),
        sucursal_tipo = JSON.parse('<?= json_encode(Sucursal::$tipoList)  ?>'),
        edit_load_sucursal = JSON.parse('<?= json_encode($model->sucursal_receptor_names)  ?>'),
        edit_load_cliente = JSON.parse('<?= json_encode($model->cliente_receptor_names)  ?>'),
        tipoList = JSON.parse('<?= json_encode(PromocionDetalleComplemento::$tipoList)  ?>'),
        estadoList = JSON.parse('<?= json_encode(EsysListaDesplegable::getEstados())  ?>'),
        metodoPagoList = JSON.parse('<?= json_encode(CobroRembolsoEnvio::$servicioList)  ?>'),
        $inputEnvioDetalleArray = $('#enviodetalle-envio_detalle_array'),
        $inputcobroRembolsoEnvioArray = $('#cobrorembolsoenvio-cobrorembolsoenvioarray'),

        $error_add_paquete = $('#error-add-paquete'),
        $error_add_reenvio = $('#error-add-reenvio'),
        $envioID = $('#envio-id'),
        $is_permiso = <?= Yii::$app->user->can('admin') ? 10 : 0 ?>,


        $paquete_sucursal_id = $("#paquete_sucursal_id"),
        $paquete_cliente_id = $("#paquete_cliente_id"),
        precio_libra_actual = <?= $model->created_user_by->sucursal ?
                                    ($model->created_user_by->sucursal->costo_libra ? $model->created_user_by->sucursal->costo_libra : 1.1)
                                    : 1.1   ?>,

        peso_caja_total = 0,
        precio_caja_total = 0,

        renvio_array = [],

        $btnAplicaReenvio = $("#btnAplicaReenvio"),
        $btnAddRenvio = $("#btnAddRenvio"),
        $isAplicaReenvio = $("#envio-is_reenvio"),
        $content_reenvio = $(".content_reenvio"),
        $div_seccion_reenvio = $('.div_seccion_reenvio'),
        $form_reenvio_content = $('.info-reenvio'),
        $template_reenvio = $('.template_reenvio'),

        $table_content_reenvio = $('#table-content-reenvio'),

        $reenvio_select_id = $('#reenvio_select_id'),

        $peso_reenvio = $('#peso_reenvio'),
        $dir_obj_array = $('#enviodetalle-dir_obj_array'),


        $form_esysdireccion_envio = {
            $inputEstado: $('#envio-estado_id', $form_reenvio_content),
            $inputMunicipio: $('#envio-municipio_id', $form_reenvio_content),
            $inputColonia: $('#envio-codigo_postal_id', $form_reenvio_content),
            $inputCodigoSearch: $('#esysdireccion-codigo_search', $form_reenvio_content),
            $inputDireccion: $('#esysdireccion-direccion', $form_reenvio_content),
            $inputNumeroExt: $('#esysdireccion-num_ext', $form_reenvio_content),
            $inputNumeroInt: $('#esysdireccion-num_int', $form_reenvio_content),
            $inputReferencia: $('#esysdireccion-referencia', $form_reenvio_content),
        },



        $form_envios = $('#form-envios'),

        $cliente_emisor = $('#envio-cliente_emisor_id'),
        $cliente_receptor = $('#envio-cliente_receptor_names'),
        $form_emisor_content = $('.form_emisor'),
        $form_receptor_content = $('.form_receptor'),
        $form_paquete_content = $('.form_paquete'),
        $template_paquete = $('.template_paquete'),
        $template_metodo_pago = $('.template_metodo_pago'),
        $btnAgregarPaquete = $('#btnAgregar-paquete'),

        $btnAgregarMetodoPago = $('#btnAgregarMetodoPago'),

        $selectProducto = $('#envio-producto_id'),

        $div_costo_neto_extraordinario = $('.div_costo_neto_extraordinario'),

        $total_v_declarado = $('#total_v_declarado'),
        $div_peso_reenvio = $('.div_peso_reenvio'),

        $div_detalle_descuento = $('.div_detalle_descuento'),
        $content_paquete = $(".content_paquete"),
        $div_valoracion_paquete = $(".div_valoracion_paquete"),
        $content_metodo_pago = $(".content_metodo_pago"),




        $is_div_info_emisor = false,
        $is_div_info_receptor = false,
        $link_info_emisor = $('#link_info-emisor'),
        $link_info_receptor = $('.link_info-receptor'),
        $div_info_emisor = $('.info-emisor');
    $div_alert_cliente_reenvio = $('.div_alert_cliente_reenvio');


    $div_info_emisor.inputText = {
        $estado: $("input[name = 'estado_id']", $div_info_emisor),
        $municipio: $("input[name = 'municipio_id']", $div_info_emisor),
        $colonia: $("input[name = 'colonia_id']", $div_info_emisor),
        $direccion: $("input[name = 'direccion_id']", $div_info_emisor),
        $num_exterior: $("input[name = 'num_exterior']", $div_info_emisor),
        $num_interior: $("input[name = 'num_interior']", $div_info_emisor),
    };

    $form_emisor = {
        $nombre: $('#cliente-nombre', $form_emisor_content),
        $apellidos: $('#cliente-apellidos', $form_emisor_content),
        $email: $('#cliente-email', $form_emisor_content),
        $telefono: $('#cliente-telefono', $form_emisor_content),
        $telefono_movil: $('#cliente-telefono_movil', $form_emisor_content),
        $btn_icon: $('button', $form_emisor_content),
    };


    $form_paquete = {
        $categoria: $('#enviodetalle-categoria_id', $form_paquete_content),
        $producto: $('#enviodetalle-producto_id', $form_paquete_content),
        $producto_tipo: $('#producto_tipo', $form_paquete_content),
        $valoracion_paquete: $('#enviodetalle-valoracion_paquete', $form_paquete_content),
        $producto_caja: $('#select_caja_id', $form_paquete_content),
        $producto_tipo_enviar: $('#producto_tipo_enviar', $form_paquete_content),
        $cantidad: $('#enviodetalle-cantidad', $form_paquete_content),
        $valor_declarado: $('#enviodetalle-valor_declarado', $form_paquete_content),
        $peso: $('#enviodetalle-enviodetalle-peso', $form_paquete_content),
        $observacion: $('#enviodetalle-observaciones', $form_paquete_content),
        $seguro: $('#seguro', $form_paquete_content),
        $costo_extraordinario: $('#enviodetalle-costo_neto_extraordinario', $form_paquete_content),
    };

    $form_metodoPago = {
        $metodoPago: $('#cobrorembolsoenvio-metodo_pago'),
        $cantidad: $('#cobrorembolsoenvio-cantidad'),
    };

    producto_tipo_lax_impuesto = {
        nuevo: 0,
        usado: 0,
    };

    producto_tipo_tierra_impuesto = {
        nuevo: 0,
        usado: 0,
    };

    tipoEnvio = {
        tierra: <?= Envio::TIPO_ENVIO_TIERRA ?>,
    };

    tipoProducto = {
        nuevo: <?= Producto::TIPO_NUEVO ?>,
        usado: <?= Producto::TIPO_USADO ?>,
    };



    is_impuesto_on = <?= Producto::IS_IMPUESTO_ON ?>;
    paquete_array = [];
    metodoPago_array = [];
    clienteReceptor = [];
    clienteEmisor = [];
    productoCategoria = [];
    productoDetalle = [];
    municipioList = [];


    peso_paquete_array = [];
    sucursalSelect = [];
    clienteSelect = [];

    searchProducto = [];
    searchCategoriaEspecial = [2696];
    selectProducto_array = {};
    isEmisorCreate = false;
    isEmisorEdit = false;


    isReceptorCreate = false;
    is_costo_extraordinario = false;
    total_envio = 0;
    peso_total_envio = 0;
    costo_seguro_select = parseInt("<?= $model->created_user_by->sucursal_id == 24 ? 8 : 7 ?>");
    temp_is_reenvio = true;

    var tipo_producto_select = 10;
    var caja_producto_id = null;
    var caja_producto_costo = 0;


    $(function() {
        //$("#peso_total").prop("disabled", true); 

        init_paquete_list();

        $sucursal_receptor_id.trigger('change');


        $form_paquete.$producto_tipo_enviar.change(function() {
            //10 = POR LIBRA
            //20 = POR CAJA
            //30 = POR CAJA SIN LÍMITE
            $form_paquete.$producto_caja.trigger('change');
            if ($(this).val() == 10) {
                $("#div_peso").hide();
                $('.select_producto').show();
                $('.select_caja').hide();
                tipo_producto_select = 10;
            } else if ($(this).val() == 30) {
                $("#div_peso").hide();
                $('.select_caja').show();
                $('.select_producto').hide();

                cargarProductoCaja(30);

                tipo_producto_select = 30;

                //$form_paquete.$producto_caja.trigger('change');
            } else {
                tipo_producto_select = 20;
                cargarProductoCaja(20);
                //tipo_producto_select = 20;
                $('.select_caja').show();
                $("#div_peso").hide();
                $('.select_producto').hide();
                //$form_paquete.$producto_caja.trigger('change');
            }

        });



        $form_paquete.$producto_caja.change(function() {
            $.get("<?= Url::to(['get-caja']) ?>", {
                caja_id: $(this).val()
            }, function($response) {
                //console.log($response, 'erorooooo');
                if ($response.code == 10) {
                    if ($response.caja.caja) {
                        $form_paquete.$producto_caja.data("precio", Number($response.caja.caja.costo_cli));

                        caja_producto_costo = $response.caja.caja.costo_cli;
                    } else if ($response.caja.producto) {
                        $form_paquete.$producto_caja.data("precio", $response.caja.producto.costo_total);
                    }
                }
            }, 'json');
        });


        /*===============================================
        *           SEARCH PRODUCTO
        ===============================================*/

        $selectProducto.change(function() {
            selectProducto_array = {};
            is_costo_extraordinario = false;
            $div_valoracion_paquete.hide();
            $div_costo_neto_extraordinario.hide();

            $.each(searchProducto, function(key, item) {
                if ($selectProducto.val() == item.id) {
                    selectProducto_array = item;
                    $form_paquete.$producto_tipo.trigger('change');
                }
            });

            $.each(searchCategoriaEspecial, function(key, item) {
                if (item == selectProducto_array.categoria_id) {
                    is_costo_extraordinario = true;
                    $div_costo_neto_extraordinario.show();
                }
            });
        });


        /*===============================================
         * Habilita/Deshabilita el descuento Manual
         *===============================================*/

        var pesoTotal = function() {
            peso_total_envio = $('#peso_total').val();
            return peso_total_envio;
        }

    });

    /**
     * ==============================================
     *              CARGA DE CAJAS
     * ===============================================
     */

    function cargarProductoCaja(productoTipo) {
        let select = $("#select_caja_id");
        $.get("<?= Url::to(['get-productos-caja']) ?>", {
            tipo: productoTipo,
            pais_id: PAIS_ID,
        }, function(response) {
            // Limpia las opciones actuales del select
            select.empty();
            //console.log(response);
            // Verifica si hay datos en la respuesta
            if (response.caja) {
                // Agrega una opción predeterminada si lo deseas
                select.append($('<option>', {
                    value: '',
                    text: 'Selecciona una opción'
                }));

                // Recorre los datos recibidos y agrega las opciones al select
                $.each(response.caja, function(index, item) {
                    select.append($('<option>', {
                        value: index, // Asume que cada item tiene una propiedad id
                        text: item // Asume que cada item tiene una propiedad nombre
                    }));

                });
            }
        }, 'json');
    }

    /*===============================================
     * Limpia valores de un  formulario
     *===============================================*/
    var clear_form = function($form) {
        $.each($form, function($key, $item) {
            $item.val(null);
        });
    };



    /*====================================================
     *               BUSCA UN ITEM EN EL ARRAY
     *====================================================*/
    var search_item = function(id, list_array, opt = false) {
        key_item = false;
        if (!opt) {
            $.each(list_array, function(key, item) {
                if (item.id == id)
                    key_item = key;
            });
        } else {
            $.each(list_array, function(key, item) {
                if (item.producto_detalle_id == id)
                    key_item = key;
            });
        }
        return key_item;
    }
</script>

<?= $this->render('envio_js/seccion_sucursal') ?>
<?= $this->render('envio_js/seccion_emisor_receptor') ?>
<?= $this->render('envio_js/seccion_reenvio') ?>
<?= $this->render('envio_js/seccion_envio') ?>
<?php //$this->render('envio_js/seccion_promocion_paquete') 
?>
<?= $this->render('envio_js/seccion_metodo_pago') ?>