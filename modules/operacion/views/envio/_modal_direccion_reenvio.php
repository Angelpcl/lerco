<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\esys\EsysListaDesplegable;
use app\models\envio\Envio;
use app\models\esys\EsysSetting;
use app\models\esys\EsysDireccionCodigoPostal;
?>

<div class="fade modal inmodal" id="modal-show-reenvio"  tabindex="-1" role="dialog" aria-labelledby="modal-show-label" style="z-index: 1000   !important;" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-truck mar-rgt-5px icon-lg"></i> Dirección de entrega</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox-content">
                    <?= Html::Button('Editar dirección de entrega', ['class' => 'btn btn-warning' ,'id' => 'btnChangeDireccion']) ?>
                    <div class="div_direccion">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <h3 class="text-main">Estado: <small id="estado_name" class="text_direccion"></small></h3>

                                </div>
                                <div class="form-group">
                                    <h3 class="text-main">Colonia: <small id="colonia_name" class="text_direccion"></small></h3>
                                </div>
                                <div class="form-group">
                                    <h3 class="text-main">Numero Interior: <small id="n_interior_name" class="text_direccion"></small></h3>
                                </div>
                                <div class="form-group">
                                    <h3 class="text-main">Codigo postal: <small id="codigo_postal_name" class="text_direccion"></small></h3>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <h3 class="text-main">Municipio: <small id="municipio_name" class="text_direccion"></small></h3>
                                </div>
                                <div class="form-group">
                                    <h3 class="text-main">Direccion: <small id="direccion_name" class="text_direccion"></small></h3>
                                </div>
                                <div class="form-group">
                                    <h3 class="text-main">Numero Exterior: <small id="n_exterior_name" class="text_direccion"></small></h3>
                                </div>
                                <div class="form-group">
                                    <h3 class="text-main">Referencia: <small id="referencia_name" class="text_direccion"></small></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="div_edit_direccion" style="display: none">
                        <?php $formCliente = ActiveForm::begin(['id' => 'form-modal-cliente' ]) ?>
                        <?= $formCliente->field($model->dir_obj, 'cuenta_id')->hiddenInput()->label(false) ?>
                        <?= $formCliente->field($model, 'id')->hiddenInput()->label(false) ?>
                        <div class="ibox">
                            <div class="ibox-title">
                                <h3 class="panel-title">Dirección MX</h3>
                            </div>
                            <div class="ibox-content">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <?= $formCliente->field($model->dir_obj, 'codigo_search')->textInput(['maxlength' => true]) ?>
                                    </div>
                                    <div id="error-codigo-postal" class="has-error" style="display: none">
                                        <div class="help-block">Codigo postal invalido, verifique nuevamente ó busque la dirección manualmente</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <?= $formCliente->field($model->dir_obj, 'estado_id')->widget(Select2::classname(), [
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
                                        <?= $formCliente->field($model->dir_obj, 'codigo_postal_id')->widget(Select2::classname(), [
                                            'language' => 'es',
                                            'data' => $model->dir_obj->codigo_postal_id ? EsysDireccionCodigoPostal::getColonia(['codigo_postal' => $model->dir_obj->codigo_search]) : [],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                            'options' => [
                                                'placeholder' => 'Selecciona la colonia'
                                            ],
                                        ])->label("Colonia") ?>
                                        <?= $formCliente->field($model->dir_obj, 'num_ext')->textInput(['maxlength' => true]) ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <?= $formCliente->field($model->dir_obj, 'municipio_id')->widget(Select2::classname(), [
                                            'language' => 'es',
                                            'data' => $model->dir_obj->estado_id? EsysListaDesplegable::getMunicipios(['estado_id' => $model->dir_obj->estado_id]): [],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                            'options' => [
                                                'placeholder' => 'Selecciona el municipio'
                                            ],
                                        ]) ?>
                                        <?= $formCliente->field($model->dir_obj, 'direccion')->textInput(['maxlength' => true]) ?>
                                        <?= $formCliente->field($model->dir_obj, 'num_int')->textInput(['maxlength' => true]) ?>
                                        <?= $formCliente->field($model->dir_obj, 'referencia')->textArea(['rows' => 6]) ?>
                                    </div>
                                </div>
                                <?= Html::Button('Editar dirección', ['class' => 'btn btn-warning btn-lg btn-block' ,'id' => 'btnSendDireccion']) ?>

                                <?= Html::Button('Editar todas las direcciones', ['class' => 'btn btn-danger btn-lg btn-block' ,'id' => 'btnSendDireccionAll']) ?>
                            </div>
                        </div>
                        <?php ActiveForm::end(); ?>
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
    var $text_direccion = $('.text_direccion'),
        $estado_name    = $('#estado_name'),
        $colonia_name       = $('#colonia_name'),
        $n_interior_name    = $('#n_interior_name'),
        $codigo_postal_name = $('#codigo_postal_name'),
        $municipio_name     = $('#municipio_name'),
        $direccion_name     = $('#direccion_name'),
        $n_exterior_name    = $('#n_exterior_name'),
        $referencia_name    = $('#referencia_name');
        $btnChangeDireccion    = $('#btnChangeDireccion');
        $div_edit_direccion    = $('.div_edit_direccion');
        $div_direccion         = $('.div_direccion');
        $btnSendDireccion      = $('#btnSendDireccion');
        $btnSendDireccionAll    = $('#btnSendDireccionAll');
        $form_cliente_content   = $('#form-modal-cliente');
        $modal = {
            $error_codigo      : $('#error-codigo-postal'),
        };
        $form_esysdireccion     = {
            $inputCuentaId     : $('#esysdireccion-cuenta_id',$form_cliente_content),
            $inputEstado       : $('#esysdireccion-estado_id',$form_cliente_content),
            $inputMunicipio    : $('#esysdireccion-municipio_id',$form_cliente_content),
            $inputCodigoSearch : $('#esysdireccion-codigo_search',$form_cliente_content),
            $inputColonia      : $('#esysdireccion-codigo_postal_id',$form_cliente_content),
            $inputDireccion    : $('#esysdireccion-direccion',$form_cliente_content),
            $inputNumeroExt    : $('#esysdireccion-num_ext',$form_cliente_content),
            $inputNumeroInt    : $('#esysdireccion-num_int',$form_cliente_content),
            $inputReferencia   : $('#esysdireccion-referencia',$form_cliente_content),

        };

        direccion_array     =  [];
        municipioSelected   = null;
        envioID             = <?= $model->id ?>;

    var init_reenvio_direccion = function($paquete_id){
        $text_direccion.html('');
        $div_direccion.show();
        $div_edit_direccion.hide();

        $.get("<?= Url::to(['show-direccion-paquete']) ?>",{ paquete_id : $paquete_id },function(direccionJson){
            if (direccionJson.code == 202) {
                $estado_name.html(direccionJson.data.estado );
                $colonia_name.html(direccionJson.data.colonia );
                $n_interior_name.html(direccionJson.data.n_interior );
                $codigo_postal_name.html(direccionJson.data.codigo_postal );
                $municipio_name.html(direccionJson.data.municipio );
                $direccion_name.html(direccionJson.data.direccion );
                $n_exterior_name.html(direccionJson.data.n_exterior );
                $referencia_name.html(direccionJson.data.referencia );
                direccion_array = direccionJson.data;
                $form_esysdireccion.$inputCuentaId.val($paquete_id);
            }else{
                $.niftyNoty({
                    type: "danger",
                    container : "floating",
                    title : "Error",
                    message : json.message,
                    closeBtn : false,
                    timer : 5000
                });
            }

        },'json');
    }

    $btnChangeDireccion.click(function(){
        $div_direccion.hide();
        $div_edit_direccion.show();

        $form_esysdireccion.$inputEstado.val(direccion_array.estado_id).trigger("change");
        $form_esysdireccion.$inputCodigoSearch.val(direccion_array.codigo_search).trigger("change");


        $form_esysdireccion.$inputDireccion.val(direccion_array.direccion);
        $form_esysdireccion.$inputNumeroExt.val(direccion_array.n_exterior);
        $form_esysdireccion.$inputNumeroInt.val(direccion_array.n_interior);
        $form_esysdireccion.$inputReferencia.val(direccion_array.referencia);

    });


    $(document).ready(function() {

        $form_esysdireccion.$inputCodigoSearch.change(function() {
            $form_esysdireccion.$inputColonia.html('');
            $form_esysdireccion.$inputEstado.val(null).trigger("change");

            var codigo_search = $form_esysdireccion.$inputCodigoSearch.val();
            if (codigo_search) {
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

                    if(direccion_array.municipio_id)
                        $form_esysdireccion.$inputColonia.val(direccion_array.cp_id).trigger("change");
                    else
                        $form_esysdireccion.$inputColonia.val(null).trigger("change");


                }, 'json');
            }
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

                    if(direccion_array.municipio_id)
                        $form_esysdireccion.$inputColonia.val(direccion_array.cp_id).trigger("change");
                    else
                        $form_esysdireccion.$inputColonia.val(null).trigger("change");

                }, 'json');
            }
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

                if( direccion_array.municipio_id )
                    $form_esysdireccion.$inputMunicipio.val(direccion_array.municipio_id).trigger("change");
                else{
                    $form_esysdireccion.$inputMunicipio.val(municipioSelected); // Select the option with a value of '1'
                    $form_esysdireccion.$inputMunicipio.trigger('change');
                }

            }, 'json');
        }
    }

    $btnSendDireccionAll.on('click', function(event){
        event.preventDefault();
        bootbox.confirm("¿Estas seguro que deseas efectar todas las  direcciones de entrega?", function(result) {
            if (result) {

                $.niftyNoty({
                    type: 'success',
                    icon : 'pli-like-2 icon-2x',
                    message : 'Se confirmo la modificación de entrega',
                    container : 'floating',
                    timer : 5000
                });
                //AJAX

                $.post('<?= Url::to(['update-direccion-all-ajax']) ?>',  $form_cliente_content.serialize() ,function($json){
                    if($json.code == 10 ){
                        $.niftyNoty({
                            type: "success",
                            container : "floating",
                            title : "Guardado",
                            message : $json.message,
                            closeBtn : false,
                            timer : 5000
                        });
                        $div_direccion.show();
                        $div_edit_direccion.hide();
                        init_reenvio_direccion($form_esysdireccion.$inputCuentaId.val());

                    }else{
                        $.niftyNoty({
                        type: "danger",
                        container : "floating",
                        title : "Error",
                        message : $json.message,
                        closeBtn : false,
                        timer : 5000
                    });
                    }

                },'json');

            }else{
                $.niftyNoty({
                    type: 'danger',
                    icon : 'pli-cross icon-2x',
                    message : 'Se cancelo la modificación de entrega.',
                    container : 'floating',
                    timer : 5000
                });
            };

        });
    });

    $btnSendDireccion.on('click', function(event){
        event.preventDefault();
        bootbox.confirm("¿Estas seguro que deseas modificar la dirección de entrega?", function(result) {
            if (result) {

                $.niftyNoty({
                    type: 'success',
                    icon : 'pli-like-2 icon-2x',
                    message : 'Se confirmo la modificación de entrega',
                    container : 'floating',
                    timer : 5000
                });
                //AJAX

                $.post('<?= Url::to(['update-direccion-ajax']) ?>',  $form_cliente_content.serialize() ,function($json){
                    if($json.code == 10 ){
                        $.niftyNoty({
                            type: "success",
                            container : "floating",
                            title : "Guardado",
                            message : $json.message,
                            closeBtn : false,
                            timer : 5000
                        });
                        $div_direccion.show();
                        $div_edit_direccion.hide();
                        init_reenvio_direccion($form_esysdireccion.$inputCuentaId.val());

                    }else{
                        $.niftyNoty({
                        type: "danger",
                        container : "floating",
                        title : "Error",
                        message : $json.message,
                        closeBtn : false,
                        timer : 5000
                    });
                    }

                },'json');

            }else{
                $.niftyNoty({
                    type: 'danger',
                    icon : 'pli-cross icon-2x',
                    message : 'Se cancelo la modificación de entrega.',
                    container : 'floating',
                    timer : 5000
                });
            };

        });
    });
</script>
