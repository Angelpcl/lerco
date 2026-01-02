<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use app\models\envio\Envio;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use yii\widgets\ActiveForm;

HighchartsAsset::register($this);

/* @var $this yii\web\View */
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['historico-sucursal-json-btt']);
$bttUrlView   = Url::to(['historico-sucursal-view?id=']);
?>


<div class="clientes-historial-sucursal">
    <div class="pad-all" style="margin-bottom: 40px;">
        <div id="container-historico-sucursal" style=" height: 355px; margin: 0 auto"></div>
    </div>
    <div class="btt-toolbar">
        <div class="panel mar-btm-5px">
            <div class="panel-body pad-btm-15px">
                <div>
                    <strong class="pad-rgt">Filtrar:</strong>
                    <?=  Html::dropDownList('asignado_id', null, Cliente::getAsiganadoA(), ['prompt' => 'Tipo de asignado', 'class' => 'max-width-170px'])  ?>
                </div>
            </div>
        </div>
	</div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">

    $item_sucursal = [];
    var item_sucursal_add = function($params){

        $item_sucursal =  [];
        $item_sucursal_local = [];
        $.each($('.clientes-historial-sucursal .bootstrap-table').bootstrapTable('getData'), function(key, value) {
            if ($item_sucursal_local.length < 10 ) {
                sucursal = [value.nombre, value.n_total ? parseFloat(value.n_total) : 0];
                $item_sucursal_local[$item_sucursal_local.length] = sucursal;
            }
        });

        $item_sucursal.push($item_sucursal_local);
        loadSucursal();
    }

    $(document).ready(function(){
        var $filters = $('.btt-toolbar :input'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver cliente" class="fa fa-eye"></a>',
                ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'nombre',
                    title: 'Nombre',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'telefono',
                    title: 'Teléfono',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'telefono_movil',
                    title: 'Teléfono movil',
                    sortable: true,
                },
                {
                    field: 'encargado',
                    title: 'Encargado',
                    sortable: true,
                },
                {
                    field: 'n_envio',
                    title: 'N° de envios',
                    sortable: true,
                },
                {
                    field: 'n_paquetes',
                    title: 'N° de paquetes',
                    sortable: true,
                },
                {
                    field: 'n_peso_total',
                    title: 'Peso total (lb)',
                    sortable: true,
                },
                {
                    field: 'n_peso_total_paquete',
                    title: 'Peso total PAQUETES (lb)',
                    sortable: true,
                },

                {
                    field: 'n_total',
                    title: 'Total de envio',
                    sortable: true,
                    formatter: btf.conta.money,
                },

                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_o,
                },
                {
                    field: 'action',
                    title: 'Acciones',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay:'none',
                },
            ],
            params = {
                id      : 'clienteHistorico',
                element : '.clientes-historial-sucursal',

                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    sortName    : 'n_total',
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onLoadSuccess : function(params){
                        item_sucursal_add(params);
                    },
                    onDblClickRow : function(row, $element){
                        window.location.href = '<?= $bttUrlView ?>' + row.id;
                    },
                }
            };


        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });


</script>
<script>
    var loadSucursal = function(){
    // Apply the theme

        // Apply the theme
        Highcharts.chart('container-historico-sucursal', {
            chart: {
                type: 'column',

            },

            title: {
                text: 'Historial de envios realizados'
            },

            xAxis: {
                type: 'category',
                labels: {
                    rotation: -45,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total en envios'
                }
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                series: {
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        format: '{point.y:.1f}%'
                    },
                }
            },

            tooltip: {
                pointFormat: 'Total  <b>{point.y:.1f} USD</b>'
            },
            series: [{
                name: 'Population',
                colorByPoint: true,

                data: $item_sucursal[0],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#FFFFFF',
                    align: 'right',
                    format: '{point.y:.1f}', // one decimal
                    y: 10, // 10 pixels down from the top
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            }]
        });
    }
</script>
