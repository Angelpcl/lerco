<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use yii\widgets\ActiveForm;

HighchartsAsset::register($this);

/* @var $this yii\web\View */
?>

<div class="clientes-vendedor-cliente">
    <div class="btt-toolbar">
        <div class="panel mar-btm-5px">
            <div class="panel-body pad-btm-15px">
                <div>
                   <div class="DateRangePicker   kv-drp-dropdown  col-sm-5">
                        <?= DateRangePicker::widget([
                            'name'           => 'date_range',
                            'presetDropdown' => true,
                            'hideInput'      => true,
                            'useWithAddon'   => true,
                            'convertFormat'  => true,
                            'startAttribute' => 'from_date',
                            'endAttribute' => 'to_date',
                            'startInputOptions' => ['value' => '2019-01-01'],
                            'endInputOptions' => ['value' => '2019-12-31'],
                            'pluginOptions'  => [
                                'locale' => [
                                    'format'    => 'Y-m-d',
                                    'separator' => ' - ',
                                ],
                                'opens' => 'left',
                                "autoApply" => true,
                            ],
                        ])
                        ?>
                    </div>
                    <strong class="pad-rgt">Filtrar:</strong>

                    <?= Html::dropDownList('origen', null, Cliente::$origenList, ['prompt' => 'Tipo de origen', 'class' => 'max-width-170px']) ?>

                    <?=  Html::dropDownList('tipo_cliente', null, EsysListaDesplegable::getItems('tipo_cliente'), ['prompt' => 'Tipo de cliente', 'class' => 'max-width-170px'])  ?>

                    <?=  Html::dropDownList('status_respuesta_call', null, EsysListaDesplegable::getItems('status_respuesta_call'), ['prompt' => 'Tipo de llamada', 'class' => 'max-width-170px'])  ?>

                    <?=  Html::dropDownList('asignado_id', null, Cliente::getAsiganadoA(), ['prompt' => 'Tipo de asignado', 'class' => 'max-width-170px'])  ?>
                </div>

                <!--
                 <div class="mar-top">
                    <strong class="pad-rgt">Agrupar:</strong>

                        <?= Html::dropDownList('agrupar[fecha]', null, ['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes', 'ano' => 'Año'], ['prompt' => 'Por fecha' ,'class' => 'max-width-170px mar-rgt-5px']) ?>

                        <?= Html::checkbox("agrupar[cliente]", false, ["id" => "agrupar-cliente", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Agrupar por cliente", "agrupar-cliente", ["style" => "display:inline"]) ?>

                        <?= Html::checkbox("agrupar[vendedor]", false, ["id" => "agrupar-vendedor", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Agrupar por vendedor", "agrupar-vendedor", ["style" => "display:inline"]) ?>

                        <?= Html::checkbox("agrupar[almacen]", false, ["id" => "agrupar-almacen", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Agrupar por almacen", "agrupar-almacen", ["style" => "display:inline"]) ?>

                        <?= Html::checkbox("agrupar[formapago]", false, ["id" => "agrupar-formapago", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Agrupar por forma de pago", "agrupar-formapago", ["style" => "display:inline"]) ?>
                </div>
                -->
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-sm-4 col-sm-offset-1">
            <?=
                Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar reporte Excel', null, [
                    'class' => 'btn btn-lg btn-danger',
                    'id' => 'reporte_download',
                    'style'=> "display:none",
                ])
            ?>
            <?=
                Html::a('<i class="fa fa-cloud-download "></i> Generar reporte Asignación', null, [
                    'class' => 'btn btn-mint btn-lg',
                    'id' => 'reporte_download_asignacion',
                    'style'=> "display:none",
                ])
            ?>
        </div>
    </div>
    <div class="dashboard-index" style="margin-bottom: 15%;">
        <div id="demo-chart-cliente"></div>
    </div>
</div>

<style>
#demo-chart-cliente {
    height: 400px;
    min-width: 320px;
    margin: 0 auto;
}
.highcharts-pie-series .highcharts-point {
    stroke: #EDE;
    stroke-width: 2px;
}
.highcharts-pie-series .highcharts-data-label-connector {
    stroke: silver;
    stroke-dasharray: 2, 2;
    stroke-width: 2px;
}
</style>
<script type="text/javascript">
var categories_name = [],
    series_item     = [],
    params          = [],
    number_contacto = 0,
    $btn_toolbar    = '.clientes-vendedor-cliente .btt-toolbar';
    $reporte_download = $('#reporte_download');
    $reporte_download_asignacion = $('#reporte_download_asignacion');
$(document).ready(function(){

    $($btn_toolbar).change(function(){
        get();
        $reporte_download.show();
        $reporte_download_asignacion.show();
    });
});

var get = function(){

    params['filters']  = $($btn_toolbar + ' :input').serialize();
    $.get('<?= Url::to('gra-vendedor-cliente-ajax') ?>', { "filters": params['filters'] } , function(json) {
        categories_name = [];
        series_item     = [];
        number_contacto = 0;
        if(json.results.length > 0){
            $.each(json.results,function(key,item){
                fecha = item.fecha_dia +'-'+ item.fecha_mes +'-'+ item.fecha_ano;
                if($.inArray(fecha,categories_name) == -1){
                    categories_name.push(fecha);
                }

                if (series_item.length  != 0 ) {
                    if (search_vendedor(item.nombre_completo) == false) {
                        series = {
                            name: item.nombre_completo,
                            data: []
                        }
                        series_item.push(series);
                    }
                }else{
                    series = {
                            name: item.nombre_completo,
                            data: []
                    }
                    series_item.push(series);
                }
            });

            $.each(json.results,function(key,item){
                $.each(series_item,function(key2,item2){
                    if (item2.name.trim() == item.nombre_completo.trim()) {
                        item2.data.push(parseInt(item.count_contact));
                        number_contacto = number_contacto  + parseInt(item.count_contact);
                    }
                });
            });
        }
        loadVendedorCliente();
    }, 'json');

}
var search_vendedor = function(nombre_completo){
    var is_vendedor = false;
    $.each(series_item,function(key2,item2){
        if (item2.name.trim() == nombre_completo.trim()) { is_vendedor = true; }
    });
    return is_vendedor;
}


var loadVendedorCliente = function(){
    Highcharts.chart('demo-chart-cliente', {
        title: {
            text: 'Vendedor - Cliente'
        },
        subtitle: {
            text: 'Numero de contactos realizados: '+ number_contacto
        },
        yAxis: {
            title: {
                text: 'Numero de atenciones'
            }
        },
        xAxis: {
            categories: categories_name
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },

        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                },

            }
        },

        series: series_item,
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }
    });
}
$reporte_download.click(function(event){
    event.preventDefault();
    params['filters']  = $($btn_toolbar + ' :input').serialize();

    $.get('<?= Url::to('reporte-vendedor-cliente-ajax') ?>', { "filters": params['filters'] + "&is_reporte=true" } , function()
    {
        window.location = '<?= Url::to('reporte-vendedor-cliente-ajax') ?>?' + params['filters'] + "&is_reporte=true";
    });

});

$reporte_download_asignacion.click(function(event){
    event.preventDefault();
    params['filters']  = $($btn_toolbar + ' :input').serialize();


    window.location = '<?= Url::to('reporte-vendedor-asignacion-ajax') ?>?' + params['filters'] + "&is_reporte=true";

});


</script>
