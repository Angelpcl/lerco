<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
//use kartik\select2\Select2;
use app\assets\BootstrapWizardAsset;
use app\assets\BootstrapValidatorAsset;
use kartik\date\DatePicker;
use app\models\envio\Envio;
use app\models\promocion\Promocion;
use app\models\promocion\PromocionDetalleComplemento;

BootstrapWizardAsset::register($this);
BootstrapValidatorAsset::register($this);
?>

<div class="promociones-promocion-form">
<?php $form = ActiveForm::begin(['id' => 'form-promocion' ]) ?>
    <div class="row">
        <div class="col-lg-10">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="panel-control">
                        <em class="text-muted">Duración de la promoción: <strong id="text_day"></strong></em>
                    </div>
                    <h5>Información de promoción</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12 eq-box-md eq-no-panel">
                            <!-- Main Form Wizard -->
                            <!--===================================================-->
                            <div id="demo-main-wz">
                                <!--nav-->
                                <div class="div_promocion_alert" style="display: none">
                                    <div class="alert alert-primary">
                                        <strong style="font-size: 16px">Promoción vigente!</strong> La promoción  <strong>"<a  target="_blank"  data-id="0" class="alert-link" id="link_promocion" ></a>"</strong> sera desahilitada en caso de generar una nueva
                                    </div>
                                </div>
                                <ul class="row wz-step wz-icon-bw wz-nav-off mar-top">
                                    <li class="col-xs-4">
                                        <a data-toggle="tab" href="#demo-main-tab1">
                                            <span class="text-primary"><i class="pli-tactic icon-2x"></i></span>
                                            <h5 class="mar-no">Promociónes Genericas / Reutilizables</h5>
                                        </a>
                                    </li>

                                    <li class="col-xs-4">
                                        <a data-toggle="tab" href="#demo-main-tab2">
                                            <span class="text-danger"><i class="pli-financial icon-2x"></i></span>
                                            <h5 class="mar-no">Promoción</h5>
                                        </a>
                                    </li>
                                    <li class="col-xs-4">
                                        <a data-toggle="tab" href="#demo-main-tab3">
                                            <span class="text-warning"><i class="pli-gears icon-2x"></i></span>
                                            <h5 class="mar-no">Detalles de la promoción</h5>
                                        </a>
                                    </li>
                                </ul>
                                <!--progress bar-->
                                <div class="progress progress-xs">
                                    <div class="progress-bar progress-bar-primary"></div>
                                </div>
                                <!--form-->
                                <?= $form->field($model->promocion_detalle, 'promocion_detalles')->hiddenInput()->label(false) ?>
                                <div class="panel-body">
                                    <div class="tab-content">
                                        <div id="demo-main-tab1" class="tab-pane">
                                            <div class="row historial-cambios nano">
                                                <div class="nano-content">
                                                    <?php if (Promocion::PromocionGenerica()): ?>
                                                        <?php foreach (Promocion::PromocionGenerica() as $key => $value): ?>
                                                            <div class="panel media middle">
                                                                <div class="media-left bg-purple pad-all">
                                                                    <i class="pli-present icon-3x"></i>
                                                                </div>
                                                                <div class="media-body pad-all">
                                                                    <div class="row">
                                                                        <div class="col-sm-6 ">
                                                                            <p class="text-2x mar-no text-semibold text-main"><?= $value->nombre ?></p>
                                                                            <p class="text-muted mar-no">Ver detalles de la promoción <?= Html::a('Aqui', ['promocion/view', 'id' => $value->id] , ['target' => '_blank']) ?></p>
                                                                        </div>
                                                                        <div class="col-sm-6 ">
                                                                            <p class="text-left"> <?= Html::button('<i class="fa fa-cloud-download mar-rgt-5px"></i>  Cargar promocion ', ['class' => 'btn btn-lg btn-purple reload_promocion', 'data-id' =>  $value->id ]) ?> </p>
                                                                            <div class="alert alert-purple alert-promocion" id="alert-promocion-<?= $value->id ?>" style="display: none ">
                                                                                <strong>Correcto !</strong> Se cargo los detalles y complementos de  "<strong><?= $value->nombre ?></strong>"
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach ?>
                                                    <?php else: ?>
                                                            <div class="ibox">
                                                                <div class="panel-body text-center bg-danger">
                                                                    <h3 class="panel-title">No se encuentra ninguna promoción para importar </h3>
                                                                    <p class="text-normal"><i class="fa fa-cloud-download icon-5x"></i></p>
                                                                </div>
                                                            </div>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="demo-main-tab2" class="tab-pane">
                                            <div class="row" id="form_promocion">
                                                <div class="col-sm-6">
                                                    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

                                                    <?= $form->field($model, 'tipo_servicio')->dropDownList([Envio::TIPO_ENVIO_TIERRA => Envio::$tipoList[Envio::TIPO_ENVIO_TIERRA],Envio::TIPO_ENVIO_LAX => Envio::$tipoList[Envio::TIPO_ENVIO_LAX]  ]) ?>

                                                    <?= $form->field($model, 'tipo')->dropDownList(Promocion::$tipoList) ?>

                                                    <?= $form->field($model, 'fecha_inicia')->widget(DatePicker::classname(), [
                                                        'options' => ['placeholder' => 'Fecha de inicio'],
                                                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                                        'language' => 'es',
                                                        'pluginOptions' => [
                                                            'autoclose' => true,
                                                            'format' => 'yyyy-mm-dd',
                                                        ]
                                                    ]) ?>
                                                    <?= $form->field($model, 'fecha_expira')->widget(DatePicker::classname(), [
                                                        'options' => ['placeholder' => 'Fecha que expira'],
                                                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                                        'language' => 'es',
                                                        'pluginOptions' => [
                                                            'autoclose' => true,
                                                            'format' => 'yyyy-mm-dd',
                                                        ]
                                                    ]) ?>

                                                    <?= $form->field($model,'promocion_img')->fileInput(["class"=>"form-control btn btn-primary"])->label(false)  ?>

                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="checkbox">
                                                                <input id="is_code_promocional" name="is_code_promocional" class="magic-checkbox" type="checkbox" checked>
                                                                <label for="is_code_promocional">¿ Aplica código promocional ? </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="checkbox">
                                                                <input id="is_generica" name="is_generica" class="magic-checkbox" type="checkbox" >
                                                                <label for="is_generica">¿ La promoción sera Generica / Reutilizable ? </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="contente-img" style="background: #00000033;">
                                                        <?php if (isset($model->promocion_img)): ?>
                                                            <?= Html::img(isset($model->promocion_img) ? '@web/uploads/'.  $model->promocion_img : false, ['alt' => 'Banner' , 'class' => 'img-responsive', "id"=> "img-baner"]) ?>
                                                        <?php else: ?>
                                                            <?= Html::img(null, ['class' => 'img-responsive', "id"=> "img-baner","style" => "box-shadow: 2px 2px 10px #666;border-radius: 5%;"]) ?>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="demo-main-tab3" class="tab-pane fade">
                                            <div class="alert alert-warning alert_promocion_manual" style="display: none">
                                                <strong>Aviso!</strong> Los beneficios se ingresaran de forma manual (Libras gratis / Condonación de impuesto) en el mostrador al momento de recibir el envio.
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-offset-3 col-sm-3">
                                                    <?= Html::button( 'Promoción Manual', ['class' =>  'btn btn-lg btn-block btn-primary', 'id' => 'promocion_manual_id']) ?>
                                                </div>
                                                <div class="col-sm-3">
                                                    <?= Html::button( 'Promoción del sistema', ['class' =>  'btn btn-lg btn-block btn-primary', 'id' => 'promocion_sistema_id']) ?>
                                                </div>
                                            </div>
                                            <?= $form->field($model, 'is_manual')->hiddenInput()->label(false) ?>

                                            <hr>
                                            <div class="div_promocion_manual" style="display: none">
                                            </div>
                                            <div class="div_promocion_basica" style="display: none">
                                                <div class="form_promocion_detalle">
                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <?= $form->field($model->promocion_detalle, 'lb_requerida')->textInput(['type' => "number"]) ?>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <?= $form->field($model->promocion_detalle, 'costo_libra_code')->textInput(['type' => "number", "step" => ".01"]) ?>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <?= $form->field($model->promocion_detalle, 'costo_libra_sin_code')->textInput(['type' => "number", "step" => ".01"]) ?>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <button type="button" style="margin-top: 15px;" id="btnAgregar-promocion"  class=" btn btn-primary" disabled="disabled">Agregar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr >
                                                                        <th class="text-center" >Libras (requeridas)</th>
                                                                        <th id="th_anexo_add" class="text-center">Costo de libra con ID </th>
                                                                        <th class="text-center">Costo de la libra sin ID</th>
                                                                        <th class="text-center">Complemento</th>
                                                                        <th class="text-center">Anexo</th>
                                                                        <th class="text-center">Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="content_promocion" style="text-align: center;">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--Footer buttons-->
                                <div class="pull-right pad-rgt mar-btm">
                                    <button type="button" class="previous btn btn-primary">Previous</button>
                                    <button type="button" class="next btn btn-primary">Next</button>
                                    <?= Html::submitButton($model->isNewRecord ? 'Crear promoción' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'finish btn btn-success' : 'btn btn-primary','disabled' => true]) ?>
                                </div>
                            </div>
                            <!--===================================================-->
                            <!-- End of Main Form Wizard -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>


 <div class="display-none">
     <table>
        <tbody class="template_promocion">
            <tr id = "promocion_id_{{promocion_id}}">
                <td  ><?= Html::input('number', "", false,["class" => "form-control" ,"style" => "text-align:center", "id"  => "table_libra_requerida"]) ?></td>
                <td id="td_anexo_add"><?= Html::input('number', "", false,["class" => "form-control table_cos_con_code" ,"style" => "text-align:center", "id"  => "table_cos_con_code", "step"=>".01"]) ?></td>
                <td ><?= Html::input('number', "", false,["class" => "form-control " ,"style" => "text-align:center", "id"  => "table_con_sin_code", "step" =>".01"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<script>

var $form_send_promocion        = $('#form-promocion'),
    $content_tab        = $('#demo-main-wz'),
    $pro_fecha_inicia   = $('#promocion-fecha_inicia'),
    $pro_fecha_expira   = $('#promocion-fecha_expira'),
    $promocion_manual_id      = $('#promocion_manual_id'),
    $promocion_sistema_id     = $('#promocion_sistema_id'),
    $alert_promocion_manual   = $('.alert_promocion_manual'),
    $div_promocion_basica     = $('.div_promocion_basica'),
    $div_promocion_manual     = $('.div_promocion_manual'),
    $is_code_promocional      = $('#is_code_promocional'),

    $tipo_servicio      = $('select[name = "Promocion[tipo_servicio]"]'),
    $form_promocion     = $('#form_promocion'),
    $text_day           = $('#text_day'),
    $content_promocion  = $(".content_promocion"),
    $btnAgregarPromocion= $('#btnAgregar-promocion'),
    $form_promocion_content = $('.form_promocion_detalle'),
    $template_promocion     = $('.template_promocion'),
    $inputPromocionDetelle  = $('#promociondetalle-promocion_detalles'),
    $inputIs_manual         = $('#promocion-is_manual'),
    $reloadPromocion        = $('.reload_promocion'),
    promocion_array      = [];
    num_anexos           = 0;
    num_anexos_opt       = 0;
    anexos_add           = 0;
    get_precio_libra     = 0;
    $form_promocion_detalle = {
        $cos_sin_code : $('#promociondetalle-costo_libra_sin_code', $form_promocion_content),
        $cos_con_code : $('#promociondetalle-costo_libra_code',$form_promocion_content ),
        $lb_requerida : $('#promociondetalle-lb_requerida', $form_promocion_content),
    };

    is_manualList = {
        on : <?= Promocion::IS_MANUAL_ON ?>,
        off : <?= Promocion::IS_MANUAL_OFF ?>
    };

var $div_promocion_alert = $('.div_promocion_alert'),
    $link_promocion      = $('#link_promocion');


$(document).on('nifty.ready', function() {
    // MAIN FORM WIZARD
    // =================================================================
    load_promocion();
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
           //$form_send_promocion.bootstrapValidator('validate');


            if(isValid === false)return false;
        }
    });
});

$(document).ready(function() {

    precio_libra_get();

    $form_promocion_content.change(function(){
        if ($form_promocion_detalle.$cos_sin_code.val() || $form_promocion_detalle.$cos_con_code.val() || $form_promocion_detalle.$lb_requerida.val() )
            $btnAgregarPromocion.prop("disabled",false);
        else
            $btnAgregarPromocion.prop("disabled",true);

    });
    /*==============================================
    // Muestra la imagen de la promoción
    ===============================================*/
    $("#promocion-promocion_img").change( function(){
        var logo =  document.getElementById('img-baner');
        logo.src = window.URL.createObjectURL(this.files[0]);
        logo.onload = function () {
              window.URL.revokeObjectURL(this.src);
        };
    });

    $tipo_servicio.change(function(){
        precio_libra_get();
    });

    /*==============================================
    // Calcula los días de la promocion
    ===============================================*/
    $form_promocion.change(function(){
        if ($pro_fecha_inicia.val() != '' && $pro_fecha_expira.val() != '' ) {
            f2 = new Date($pro_fecha_expira.val());
            f1 = new Date($pro_fecha_inicia.val());
            resta =  f2.getTime() - f1.getTime();
            $text_day.html(' ('+Math.round(resta/ (1000*60*60*24)) + ') días');
        }
    });

    /*==============================================
    // Agrega un item al array de datos
    ===============================================*/

    $btnAgregarPromocion.click(function(){

        promocion = {
            "promocion_id": promocion_array.length + 1,
            "costo_libra_sin_code" : $form_promocion_detalle.$cos_sin_code.val() ? $form_promocion_detalle.$cos_sin_code.val() : 0,
            "costo_libra_code"     : $form_promocion_detalle.$cos_con_code.val() ? $form_promocion_detalle.$cos_con_code.val(): 0,
            "lb_requerida"         : $form_promocion_detalle.$lb_requerida.val() ? $form_promocion_detalle.$lb_requerida.val(): 0,
            "anexos"               : [],
            "promocione_complemento"  : [],
            "origen"                : 1
        };
        promocion_array.push(promocion);
        render_promocion_template();
        clear_form($form_promocion_detalle);
        $(this).prop("disabled",true);
    });

    /*=============================================
    // Reload Promoción
    ===============================================*/


    $promocion_manual_id.click(function(){
        $div_promocion_manual.show();
        $div_promocion_basica.hide();
        $alert_promocion_manual.show();
        $inputIs_manual.val(is_manualList.on);
    });

    $promocion_sistema_id.click(function(){
        $div_promocion_manual.hide();
        $div_promocion_basica.show();
        $alert_promocion_manual.hide();
        $inputIs_manual.val(is_manualList.off);
    });


    $reloadPromocion.click(function(){
        promocion_id        = $(this).data("id");
        promocion_array     = [];
        load_complemento_array   = [];
        agrupa_complemento_array = [];
        complementoList     =  JSON.parse('<?= json_encode(PromocionDetalleComplemento::$complementoList) ?>'),
        tipoList            =  JSON.parse('<?= json_encode(PromocionDetalleComplemento::$tipoList) ?>'),
        productoTipoList    =  JSON.parse('<?= json_encode(PromocionDetalleComplemento::$productoTipoList) ?>');

        $('.alert-promocion').hide();

        $.get('<?= Url::to('promocion-detalle-complemento-ajax') ?>', {'promocion_id' : promocion_id }, function(json) {

            $.each(json.PromocionDetalle, function(key, item){
                if (item.id) {
                    promocion = {
                        "promocion_id"          : item.id,
                        "costo_libra_sin_code"  : item.costo_libra_sin_code,
                        "costo_libra_code"      : item.costo_libra_code,
                        "lb_requerida"          : item.lb_requerida,
                        "anexos"                : [],
                        "promocione_complemento": [],
                        "origen"                : 1,

                    };
                    promocion_array.push(promocion);
                }
            });

            $.each(json.PromocionComplemento, function(key, item){
                if (item[0]) {
                    $.each(item, function(key, promo_item){

                        if (promo_item.promocion_detalle_id) {
                            complemento = {
                                "promocion_detalle_id"  : promo_item.promocion_detalle_id,
                                "complemento_id"        : load_complemento_array.length + 1,
                                "tipo_complemento"      : promo_item.tipo_complemento,
                                "tipo_complemento_text" : promo_item.tipo_complemento ? complementoList[promo_item.tipo_complemento]  : null,
                                "cantidad_producto"     : promo_item.num_producto,
                                "tipo"                  : promo_item.is_categoria,
                                "tipo_text"             : promo_item.is_categoria     ? tipoList[promo_item.is_categoria]  : null,
                                "producto_tipo"         : promo_item.is_producto,
                                "categoria_id"          : promo_item.categoria_id,
                                "categoria_text"        : promo_item.categoria_id     ? promo_item.categoria : "N/A",
                                "producto_id"           : promo_item.producto_id,
                                "producto_text"         : promo_item.is_producto      ?  promo_item.producto : "N/A",
                                "lb_free"               : promo_item.lb_free,
                                "is_valor_paquete"      : promo_item.is_valor_paquete,
                                "valor_paquete_aprox"   : promo_item.valor_paquete_aprox,

                                "cobro_impuesto"        : promo_item.cobro_impuesto,
                                "is_envio_free"         : promo_item.is_envio_free,
                                "lbfree_check"          : promo_item.is_lb_free,
                                //"is_libras_excedente"   : promo_item.is_lbexcedente,
                                //"lbexcedente"           : promo_item.lbexcedente,
                                //"lbcosto_excedente"     : promo_item.costo_libraexcedente,
                                "origen"                : 1,
                                "status"                : 10, // El status del detalle
                                "create"                : 1,
                                "opt"                   : 1,
                            };
                            load_complemento_array.push(complemento);
                        }
                    });
                }
            });

            $.each(promocion_array,function(key,item){
                agrupa_complemento_array = [];
                $.each(load_complemento_array, function(key2 , complemento){
                    if (complemento.promocion_detalle_id == item.promocion_id) {
                        agrupa_complemento_array.push(complemento);
                    }
                });
                if (agrupa_complemento_array.length > 0) {
                    promocion_array[key].promocione_complemento = [];
                    promocion_array[key].promocione_complemento.push(agrupa_complemento_array);
                }
            });

            $("#alert-promocion-"+promocion_id).show();

            render_promocion_template();

       }, 'json');
    });

});


$is_code_promocional.change(function(){
    if (!$(this).is(':checked')) {
        $('.table_cos_con_code').prop('disabled',true);
        $form_promocion_detalle.$cos_con_code.prop('disabled',true);
    }else{
        $('.table_cos_con_code').prop('disabled',false);
        $form_promocion_detalle.$cos_con_code.prop('disabled',false);
    }
});
/**============================================================
    Refresh Array de promocion detalle
/**==========================================================*/
var refresh_promocion_change = function(ele,inputChange){
    $ele_promocion_val    = $(ele);
    $ele_promocion        = $(ele).closest('tr');
    $ele_promocion_detalle_id  = $ele_promocion.attr("data-promocion-detalle");


    $.each(promocion_array, function(key, promocion){
        if (promocion.promocion_id == $ele_promocion_detalle_id ){
            switch(inputChange){
                case 'LIBRA_REQUERIDA':
                    promocion.lb_requerida = $ele_promocion_val.val() ? $ele_promocion_val.val() : 0;
                break;
                case 'COS_CON_CODE':
                    promocion.costo_libra_code = $ele_promocion_val.val() ? $ele_promocion_val.val() : 0;
                break;
                case 'COS_SIN_CODE':
                    promocion.costo_libra_sin_code = $ele_promocion_val.val() ? $ele_promocion_val.val() : 0;
                break;

            }
        }
    });

    $inputPromocionDetelle.val(JSON.stringify(promocion_array));
    render_promocion_template();
}

 var precio_libra_get = function(){
     $.get('<?= Url::to(['/admin/configuracion/precio-libra-ajax']) ?>', {'tipo_servicio' : $tipo_servicio.val() }, function(json) {
        get_precio_libra = json;
        $form_complemento.$precio_libra_actual.html(get_precio_libra);
   }, 'json');
}

/*===============================================
// Render de la array de datos
===============================================*/

var render_promocion_template = function()
{

    $content_promocion.html("");
    sum_peso_total = 0;
    num_anexos_opt = 0;
    promocion_array.sort(function(a, b) {
      return a.lb_requerida - b.lb_requerida;
    });
    $.each(promocion_array, function(key, promocion){

        if (promocion.promocion_id) {
            template_promocion = $template_promocion.html();
            template_promocion = template_promocion.replace("{{promocion_id}}",promocion.promocion_id);

            $content_promocion.append(template_promocion);

            $tr        =  $("#promocion_id_" + promocion.promocion_id, $content_promocion);
            $tr.attr("data-promocion-detalle",promocion.promocion_id);
            $tr.attr("data-origen",promocion.origen);


            load_num_anexos(promocion.anexos);

            $("#table_libra_requerida",$tr).val(promocion.lb_requerida);
            $("#table_libra_requerida",$tr).attr("onchange","refresh_promocion_change(this,'LIBRA_REQUERIDA')");



            for (var i = 0; i < num_anexos; i++) {
                if (promocion.anexos.length) {
                    if (promocion.anexos[0][i]) {
                        categorias_name = "";
                        for (var j = 0; j < promocion.anexos[0][i].categorias.length; j++) {
                            categorias_name += promocion.anexos[0][i].categorias[j].categoria_nombre +" / ";
                        }

                        $('#td_anexo_add',$tr).before("<td><span style='display:block'><strong>Categoria: </strong>"+ categorias_name +"</span><span><strong>Libras Gratis: </strong>"+ promocion.anexos[0][i].libras_free +"<span></td>");
                    }else{
                        $('#td_anexo_add',$tr).before("<td><i class='fa fa-times' aria-hidden='true'></i></td>");
                    }
                }else{
                    $('#td_anexo_add',$tr).before("<td><i class='fa fa-times' aria-hidden='true'></i></td>");
                }
            };

            load_anexos();
            $("#table_cos_con_code",$tr).val(promocion.costo_libra_code);
            $("#table_cos_con_code",$tr).attr("onchange","refresh_promocion_change(this,'COS_CON_CODE')");

            $("#table_con_sin_code",$tr).val(promocion.costo_libra_sin_code);
            $("#table_con_sin_code",$tr).attr("onchange","refresh_promocion_change(this,'COS_SIN_CODE')");

            /*$("#table_seguro",$tr).html(paquete.seguro ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>");*/

            $tr.append("<td><button type='button' class='btn btn-primary btn-circle'  data-target='#modal-create-complemento' onclick='add_complemento("+key+","+ promocion.promocion_id+","+promocion.origen +")' data-toggle='modal' ><i class='fa fa-plus'></i></button></td>");

            $tr.append("<td><button type='button' class='btn btn-mint btn-circle'  data-target='#modal-create-anexo' onclick='add_anexo("+key+","+ promocion.promocion_id+","+promocion.origen +")' data-toggle='modal' ><i class='fa fa-plus'></i></button></td>");

            $tr.append("<td><button type='button'  class='btn btn-warning btn-circle' onclick='refresh_promocion(this)'><i class='fa fa-trash'></i></button></td>");

        }
    });


    $inputPromocionDetelle.val(JSON.stringify(promocion_array));
};

var load_num_anexos = function($anexos){
    $.each(promocion_array,function(key,promocion){
        if (promocion.anexos.length) {
            num_anexos_opt = promocion.anexos[0].length > num_anexos_opt ? promocion.anexos[0].length : num_anexos_opt;
            num_anexos     = num_anexos_opt;
        }
    });
}

var load_anexos = function(){
    //anexos_show = num_anexos - anexos_add;
    if (anexos_add < num_anexos) {
        for (var i = anexos_add; i < num_anexos; i++) {
            $('#th_anexo_add').before("<th id='th_anexo_id_"+ i +"'>Anexo <strong>"+ (i + 1) +"</strong></th>");
            anexos_add = anexos_add+1;
        }
    }else{
        for (var i = num_anexos; i < anexos_add; i++) {
            $('#th_anexo_id_' + i ).remove();
            anexos_add = anexos_add - 1;
        }
    }

}


var load_promocion = function(){
    filters = "tipo_servicio=" ;

    $.get('<?= Url::to(['promocion-info-ajax']) ?>',{ filters: filters},function(json){
        if (json.id) {
            $div_promocion_alert.show();
            $link_promocion.attr('href','<?= Url::to(['/promociones/promocion/view?id=']) ?>' +  json.id);
            $link_promocion.html(json.nombre);
            $link_promocion.data("id",json.id);
            $link_promocion.data("is_code",json.is_code_promocional);
            promocionVigente = true;
        }else
            $div_promocion_alert.hide();
    },'json');
}

/*===============================================
* Actualiza la lista de paquetes
*===============================================*/

var refresh_promocion = function(ele){
    $ele_sucursal_val = $(ele).closest('tr');

    $ele_pro_detalle_id  = $ele_sucursal_val.attr("data-promocion-detalle");
    $ele_origen_id      = $ele_sucursal_val.attr("data-origen");

    $.each(promocion_array, function(key, promocion_d){
        if (promocion_d) {
            if (promocion_d.promocion_id == $ele_pro_detalle_id) {
                promocion_array.splice(key, 1 );
                render_promocion_template();
            }
        }
    });

    $inputPromocionDetelle.val(JSON.stringify(promocion_array));
    render_promocion_template();

    $(ele).closest('tr').remove();
};

/*===============================================
* Limpia valores de un  formulario
*===============================================*/
var clear_form = function($form){
    $.each($form,function($key,$item){
        $item.val(null);
    });
};
</script>
