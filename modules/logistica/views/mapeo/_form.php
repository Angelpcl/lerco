<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\viaje\Viaje;
use app\assets\Nestable2Asset;
use app\models\esys\EsysSetting;
use app\models\descarga\DescargaBodega;

Nestable2Asset::register($this);

?>

<?php if (Yii::$app->user->identity->bodega_descarga_asignado): ?>
<div class="alert alert-info">
    <h2>Tu bodega asignada es : <?= DescargaBodega::$descargaList[Yii::$app->user->identity->bodega_descarga_asignado] ?></h2>
</div>
<?php else: ?>
<div class="alert alert-danger">
    <h2>No tienes bodega asignada, no podras generar tu planeacion</h2>
</div>
<?php endif ?>
<div class="logistica-reparto-form">
    <?php $form = ActiveForm::begin(['id' => 'form-reparto' ]) ?>
    <?= $form->field($model->lista_paquete_array, 'mapeo_detalle_array')->hiddenInput()->label(false) ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-7">
                    <div class="ibox">
                        <div class="ibox-content">
                            <div class="viajes_container mar-btm">
                                <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
                                <?= Html::label('Viajes disponibles', 'mapeo-viaje_names', ['class' => 'control-label']) ?>
                                <?= Select2::widget([
                                    'id' => 'mapeo-viaje_names',
                                    'name' => 'Mapeo[viaje_names]',
                                    'value' => $model->viaje_names,
                                    'data' => Viaje::getTranscursoTierra(true),
                                    'options' => [
                                        'placeholder' => 'Viajes disponibles',
                                        'multiple' => true,
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ]) ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php $count = 0; ?>
                <?php foreach (EsysListaDesplegable::getItems('mapeo',true) as $key => $mapeo): ?>
                    <div class="col-lg-4 col-sm-4 col-xs-6">
                        <div class="ibox">
                            <div class="ibox-content historial-cambios nano" style="overflow: scroll;">
                                <div class="nano-content">
                                    <h3 class="panel-title" style="display: inline-block;"><?= $mapeo->singular ?> <strong style="display: inline-block;">N° Paquetes <p style="    display: inline-block;" id="fila-count-paquete-<?= $mapeo->id ?>"></p></strong></h3>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <?= Html::input('number','number_fila', $count <=10 ? EsysSetting::getRangoFilaUno() :  EsysSetting::getRangoFilaDos(),['class' => 'form-control text-center numbers_filas','min' => 0, 'id' => 'number_fila_' . $mapeo->id]) ?>
                                        </div>
                                        <div class="col-sm-6">

                                            <?= Html::dropDownList('ruta_id', null, EsysListaDesplegable::getEstados(), ['id' => 'ruta_ini_' . $mapeo->id ,'prompt' => '--- select ---', 'class' => 'ruta_ini  max-width-170px form-control'])  ?>
                                        </div>
                                    </div>
                                    <div class="dd" id="nestable-<?= $mapeo->id ?>">
                                        <ol class='dd-list' id="dd-empty-placeholder-<?= $mapeo->id ?>">
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $count = $count + 1; ?>
                <?php endforeach ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear mapeo' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary' , 'id'=> 'btnMapeo']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>


<script>

var $selectMapeoViaje   = $("#mapeo-viaje_names"),
    viaje_array         = [],
    //fila_cantidad       = [<?= EsysSetting::getRangoFilaUno()  ?>,<?= EsysSetting::getRangoFilaDos()  ?>],
    fila_cantidad       = [],
    obj                 = '[{"id":1},{"id":2}]',
    paquete_array       = [],
    $ruta_ini_select    = $('.ruta_ini');
    ruta_ini            = [];
    ruta_select_fila    = [];
    $btnMapeo           = $('#btnMapeo'),
    $numbers_filas      = $('.numbers_filas'),
    $btnMapeoDetalle    = $('#mapeodetalle-mapeo_detalle_array'),
    count_filas         = 32,
    content_fila        = '';
    filas_get           =JSON.parse('<?= json_encode(EsysListaDesplegable::getItems('mapeo'))  ?>'),


    $(document).ready(function(){
        init_fila();
        get_number_fila();
    });

    function buildItem(item) {

        var html = "<li class='dd-item' data-paquete_id='" + item.id + "' data-trackend = '"+ item.tracked +"' data-viaje_id = '"+ item.viaje_id +"'  >";
        html += "<div class='dd-handle dd-outline dd-anim' style='padding : 2px 10px'>"+
                    "<div class='media-left' >"+
                        "<i class='pli-empty-box icon-2x' style='display: inline-block;'></i>"+
                            "<p class='text-semibold mar-no' style='    display: inline-block; font-size:10px'>"+
                                "<font style='vertical-align: inherit;'>"+
                                    "<font style='vertical-align: inherit;'>" +item.nombre+ " </font>"+
                                "</font>"+
                            "</p> "+

                    "</div>"+
                "</div> ";

        if (item.children) {

            html += "<ol class='dd-list'>";
            $.each(item.children, function (index, sub) {
                html += buildItem(sub);
            });
            html += "</ol>";

        }

        html += "</li>";

        return html;
    }

    var init_fila = function(){

        add_count  = 0;
        count = 0;
        $.each(filas_get,function(key,item){
            $('#dd-empty-placeholder-' + key).html('');

            ruta_load_ini = $('#ruta_ini_' + key).val();



            content_fila        = '';
            paquete_length  = paquete_array.length;

            content_max = fila_cantidad[count];

            count_item      = 0;
            count_paquete   = 0;
            num_paquete_fila = 0;

            //is_load = true; //is_ruta(key);



            if (add_count < paquete_length ) { // Ingresa la cantidad de paquetes que le corresponde
                if (count_paquete < paquete_length && ruta_load_ini == 0) {
                    $.each(paquete_array, function (index, item) {
                        if (count_item < Math.round(content_max)) {  //Valida que no se ingrese mas de lo calculado

                            is_add_paquete = true;

                            $.each(ruta_select_fila,function(key,fila_ruta){
                                if (item.estado_id == fila_ruta ) {
                                    is_add_paquete = false;
                                }
                            });

                            if (is_add_paquete) {
                                if (ruta_load_ini ==  0 ) {
                                    //if (  add_count <= count_paquete ) { // count_paquete el numero de paquete leidos vs add_count paquetes agregados


                                        content_fila += buildItem(item);
                                        count_item      = count_item + 1;
                                        add_count       = add_count + 1;
                                        num_paquete_fila= num_paquete_fila + 1;
                                   // }
                                    count_paquete   = count_paquete + 1; // CONTADOR DE PAQUETES EN FILA
                                }
                            }
                        }
                    });
                }else{
                    $.each(paquete_array, function (index, item) {
                        if (count_item < Math.round(content_max)) {  //Valida que no se ingrese mas de lo calculado
                            if (parseInt(ruta_load_ini) ==  parseInt(item.estado_id) ) {
                                //if (  add_count <= count_paquete ) { // count_paquete el numero de paquete leidos vs add_count paquetes agregados
                                    content_fila += buildItem(item);
                                    count_item      = count_item + 1;
                                    add_count       = add_count + 1;
                                    num_paquete_fila= num_paquete_fila + 1;
                                //}
                                count_paquete   = count_paquete + 1;
                            }
                        }
                    });
                }


            }


            $('#fila-count-paquete-' + key) .html(num_paquete_fila);
            $('#dd-empty-placeholder-' + key).html(content_fila);
            $('#nestable-' + key).nestable();
            count = count +1
        });

    };


    /*var is_ruta = function(clave){
        is_ruta = true;
        $.each(filas_get,function(key,item){
            value = $('#ruta_ini_' + key).val();
            fila_cantidad.push(value);
        });
        return is_ruta;
    };*/

    var get_number_fila = function(){
        fila_cantidad = [];
        $.each(filas_get,function(key,item){
            value = $('#number_fila_' + key).val();
            fila_cantidad.push(value);
        });
        init_fila();
    };

    $numbers_filas.change(function(){
        get_number_fila();
    });

    $selectMapeoViaje.change(function(){
        viaje_array = $(this).val();
        init_ruta_paquete();
    });

    var init_ruta_paquete = function(){
        paquete_array = [];
        if (viaje_array.length > 0 ) {

            $.get("<?= Url::to('ruta-paquete-ajax') ?>",{viajes_id: viaje_array},function(paquete_list){
                $.each(paquete_list,function(key,paquete){
                    item = {
                        "id"        : paquete.paquete_id,
                        "nombre"    : 'E: ' + ( paquete.estado_id ? paquete.estado : 'N/A' ) + ", M: "+ ( paquete.municipio_id ? paquete.municipio : 'N/A' ) + ", T: "+ paquete.tracked,
                        "tracked"   : paquete.tracked,
                        "viaje_id"  : paquete.id,
                        "ruta_id"   : paquete.ruta_id,
                        "estado_id"      : paquete.estado_id ? paquete.estado_id : 0,
                        "estado"         : paquete.estado_id ? paquete.estado : 'N/A',
                        "municipio_id"   : paquete.municipio_id ? paquete.municipio_id : 0,
                        "municipio"      : paquete.municipio_id ? paquete.municipio : 'N/A',
                    };
                    paquete_array.push(item);

                    ruta = {
                        "estado_id" : paquete.estado_id ? paquete.estado_id : 0,
                        "estado"    : paquete.estado_id ? paquete.estado : 'N/A',
                    };
                    add_ruta_array(ruta);
                });
                init_fila();

            },'json');
        }else
            init_fila();
    }

    $ruta_ini_select.change(function(){
        ruta_select_fila = [];
        $.each($(".ruta_ini"), function(ket,element){
            if (parseInt($(element).val())) {
                ruta_select_fila.push(parseInt($(element).val()));
            }
        });
        init_fila();
    });

    var add_ruta_array = function(ruta){
        is_add = true;
        $.each(ruta_ini, function(key,item){
            if (item.estado_id == ruta.estado_id)
                is_add = false;
        });

        if (is_add)
            ruta_ini.push(ruta);

        load_ruta_select();
    }

    var load_ruta_select = function(){
        $.each(filas_get,function(key,item){
            $("#ruta_ini_" + key ).html(false);
            $.each(ruta_ini,function(key2,ruta){
                $("#ruta_ini_" + key ).append("<option value='" + ruta.estado_id + "'>" + ruta.estado + "</option>\n");
            });

            $('#ruta_ini_' + key).append("<option value='0'>--seleccionar--</option>");
            $("#ruta_ini_"+ key +" option[value=0]").attr('selected', 'selected');
        });
    }

    $btnMapeo.click(function(event){
        $btnMapeoDetalle.val(false);
        event.preventDefault();
        fila_array = [];
        $.each(filas_get,function(key,item){
            fila = {
                fila : key,
                paquete: [],
            }

            paqueteList = $('#nestable-' + key).nestable('serialize');

            fila.paquete.push(paqueteList);

            fila_array.push(fila);
        });
        $btnMapeoDetalle.val(JSON.stringify(fila_array));
        $(this).submit();
    });
</script>
